<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\DevJam;
use App\Models\DevJamCreatorBlock;
use App\Models\DevJamMessage;
use App\Models\DevJamOutcomeVote;
use App\Models\DevJamPoll;
use App\Models\DevJamPollOption;
use App\Models\DevJamPollVote;
use App\Models\DevJamSubmission;
use App\Models\DevJamVote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DevJamController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'all'); // all | official | community
        $userId = Auth::guard('web')->id();

        $query = DevJam::with(['creatorUser:id,name,first_name,username,avatar', 'creatorAdmin:id,name'])
            ->withCount(['submissions', 'messages'])
            ->where(function ($q) use ($userId) {
                $q->whereNotIn('status', ['pending_validation', 'rejected']);
                if ($userId) {
                    $q->orWhere('creator_user_id', $userId);
                }
            });

        if (in_array($mode, ['official', 'community'], true)) {
            $query->where('mode', $mode);
        }

        $blockedCreatorIds = $userId
            ? DevJamCreatorBlock::where('blocked_user_id', $userId)->pluck('creator_user_id')->all()
            : [];

        $jams = $query->get()->map(function (DevJam $jam) use ($blockedCreatorIds) {
            $jam->effective_status = $jam->computedStatus();
            $jam->online_count = $this->getJamOnlineCount($jam);
            $jam->hearts_count = (int) ($jam->hearts_count ?? 0);
            $jam->popularity_score = ($jam->online_count * 5)
                + ((int) $jam->submissions_count * 3)
                + ((int) $jam->messages_count * 2)
                + $jam->hearts_count;
            $jam->is_blocked_for_me = $jam->creator_user_id && in_array($jam->creator_user_id, $blockedCreatorIds, true);
            return $jam;
        })->sortByDesc(function (DevJam $jam) {
            $statusWeight = $jam->effective_status === 'active' ? 2 : ($jam->effective_status === 'upcoming' ? 1 : 0);
            return [
                $jam->is_featured ? 1 : 0,
                $statusWeight,
                $jam->popularity_score,
                $jam->id,
            ];
        })->values();

        $activeJams = $jams->where('effective_status', 'active')->values();
        $popularJams = $activeJams->count() > 1
            ? $activeJams->sortByDesc('popularity_score')->take(3)->values()
            : collect([]);

        return Inertia::render('DevJams/Index', [
            'jams' => $jams,
            'popularJams' => $popularJams,
            'modeFilter' => $mode,
            'counts' => [
                'all' => $jams->count(),
                'official' => DevJam::where('mode', 'official')->whereNotIn('status', ['pending_validation', 'rejected'])->count(),
                'community' => DevJam::where('mode', 'community')->whereNotIn('status', ['pending_validation', 'rejected'])->count(),
            ],
        ]);
    }

    public function liveStats()
    {
        $jams = DevJam::withCount(['submissions', 'messages'])
            ->whereNotIn('status', ['pending_validation', 'rejected'])
            ->get()
            ->mapWithKeys(function (DevJam $jam) {
                $online = $this->getJamOnlineCount($jam);
                $hearts = (int) ($jam->hearts_count ?? 0);
                return [
                    $jam->id => [
                        'effective_status' => $jam->computedStatus(),
                        'submissions_count' => (int) $jam->submissions_count,
                        'messages_count' => (int) $jam->messages_count,
                        'hearts_count' => $hearts,
                        'online_count' => $online,
                        'popularity_score' => ($online * 5) + ((int) $jam->submissions_count * 3) + ((int) $jam->messages_count * 2) + $hearts,
                    ],
                ];
            });

        return response()->json(['stats' => $jams]);
    }

    public function createCommunity()
    {
        return Inertia::render('DevJams/Create');
    }

    public function storeCommunity(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'theme' => ['nullable', 'string', 'max:140'],
            'description' => ['required', 'string', 'max:2000'],
            'rules' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'prize_text' => ['nullable', 'string', 'max:255'],
            'allow_external_links' => ['boolean'],
        ]);

        $user = Auth::guard('web')->user();
        $baseSlug = Str::slug($validated['title']) ?: 'dev-jam';
        $slug = $baseSlug;
        $counter = 1;
        while (DevJam::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $startsAt = \Carbon\Carbon::parse($validated['starts_at']);
        $status = $startsAt->isFuture() ? 'upcoming' : 'active';

        $jam = DevJam::create([
            'slug' => $slug,
            'title' => $validated['title'],
            'theme' => $validated['theme'] ?? null,
            'description' => $validated['description'],
            'rules' => $validated['rules'] ?? null,
            'mode' => 'community',
            'status' => $status,
            'creator_user_id' => $user->id,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'prize_text' => $validated['prize_text'] ?? null,
            'allow_external_links' => $validated['allow_external_links'] ?? true,
        ]);

        return redirect()->route('devjams.show', $jam->slug)
            ->with('success', 'Votre Dev Jam communautaire est en ligne !');
    }

    public function show(Request $request, string $slug)
    {
        $jam = DevJam::with([
            'creatorUser:id,name,first_name,username,avatar',
            'creatorAdmin:id,name',
            'submissions' => fn ($q) => $q->with('user:id,name,first_name,username,avatar')->orderByDesc('votes_count')->latest(),
        ])->where('slug', $slug)->firstOrFail();

        $user = Auth::guard('web')->user();
        if (in_array($jam->status, ['pending_validation', 'rejected'], true) && (!$user || $jam->creator_user_id !== $user->id)) {
            abort(404);
        }
        if ($this->isBlockedFromJam($jam, $user)) {
            abort(403, 'Vous avez été bloqué par le créateur de cette Jam et ne pouvez plus accéder à ses Dev Jams.');
        }

        $jam->effective_status = $jam->computedStatus();

        $mySubmission = $user
            ? $jam->submissions->firstWhere('user_id', $user->id)
            : null;

        $votedSubmissionIds = $user
            ? DevJamVote::where('dev_jam_id', $jam->id)->where('user_id', $user->id)->pluck('submission_id')->values()
            : collect([]);

        $messages = DevJamMessage::with('user:id,name,username,avatar')
            ->where('dev_jam_id', $jam->id)
            ->latest('id')
            ->limit(80)
            ->get()
            ->reverse()
            ->values();

        $presence = $this->touchPresenceAndSnapshot($jam, $request);
        $collab = $this->collaborationSnapshot($jam, $user?->id, false);
        $replyMap = Cache::get("dev_jam:{$jam->id}:reply_map", []);
        $messages->transform(function ($m) use ($replyMap) {
            if (isset($replyMap[$m->id])) {
                $m->reply_preview = $replyMap[$m->id];
            }
            return $m;
        });

        return Inertia::render('DevJams/Show', [
            'jam' => $jam,
            'mySubmission' => $mySubmission,
            'votedSubmissionIds' => $votedSubmissionIds,
            'initialMessages' => $messages,
            'isJamCreator' => $user && $jam->creator_user_id === $user->id,
            'initialOnlineCount' => $presence['online_count'],
            'initialOnlineUsers' => $presence['online_users'],
            'initialPolls' => $this->formatPolls($jam, $user?->id),
            'initialCollaborators' => $collab['collaborators'],
            'initialLiveCode' => $collab['live_code'],
            'initialPinnedMessage' => Cache::get("dev_jam:{$jam->id}:pinned_msg"),
            'initialHeartsCount' => (int) ($jam->hearts_count ?? 0),
            'initialRaisedHands' => $this->getRaisedHands($jam),
            'initialMessageReactions' => Cache::get("dev_jam:{$jam->id}:msg_reactions", []),
            'initialSprint' => $this->getActiveSprint($jam),
            'initialCreatorActiveTab' => Cache::get("dev_jam:{$jam->id}:creator_tab", 'html'),
            'initialOutcomeVotes' => $this->getOutcomeVotesSnapshot($jam, $user?->id),
        ]);
    }

    public function saveSubmission(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();
        if ($this->isBlockedFromJam($jam, $user)) {
            return response()->json(['message' => 'Accès refusé par le créateur de cette Jam.'], 403);
        }
        $effective = $jam->computedStatus();
        if ($effective === 'suspended') {
            return response()->json(['message' => 'Cette Dev Jam a été suspendue car le créateur a quitté la session.'], 423);
        }
        if (in_array($effective, ['ended', 'rejected'], true)) {
            return response()->json(['message' => 'Cette Dev Jam est terminée.'], 422);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:1000'],
            'submission_type' => ['required', 'in:studio,external'],
            'html_code' => ['nullable', 'string', 'max:200000'],
            'css_code' => ['nullable', 'string', 'max:200000'],
            'js_code' => ['nullable', 'string', 'max:200000'],
            'external_repo_url' => ['nullable', 'url', 'max:500'],
            'external_demo_url' => ['nullable', 'url', 'max:500'],
        ]);

        if ($validated['submission_type'] === 'external' && !$jam->allow_external_links) {
            return response()->json(['message' => 'Cette Jam accepte uniquement le code dans l’éditeur intégré Tech237.'], 422);
        }

        $user = Auth::guard('web')->user();

        $submission = DevJamSubmission::updateOrCreate(
            ['dev_jam_id' => $jam->id, 'user_id' => $user->id],
            [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'submission_type' => $validated['submission_type'],
                'html_code' => $validated['html_code'] ?? '',
                'css_code' => $validated['css_code'] ?? '',
                'js_code' => $validated['js_code'] ?? '',
                'external_repo_url' => $validated['external_repo_url'] ?? null,
                'external_demo_url' => $validated['external_demo_url'] ?? null,
            ]
        );

        return response()->json([
            'submission' => $submission->load('user:id,name,username,avatar'),
            'message' => 'Votre participation a été enregistrée !',
        ]);
    }

    public function voteSubmission(string $slug, DevJamSubmission $submission)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        if ($submission->dev_jam_id !== $jam->id) {
            abort(404);
        }

        $user = Auth::guard('web')->user();
        if ($submission->user_id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas voter pour votre propre projet.'], 422);
        }

        $existing = DevJamVote::where('dev_jam_id', $jam->id)
            ->where('submission_id', $submission->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $voted = false;
        } else {
            DevJamVote::create([
                'dev_jam_id' => $jam->id,
                'submission_id' => $submission->id,
                'user_id' => $user->id,
            ]);
            $voted = true;
        }

        $count = DevJamVote::where('submission_id', $submission->id)->count();
        $submission->update(['votes_count' => $count]);

        return response()->json([
            'voted' => $voted,
            'votes_count' => $count,
        ]);
    }

    public function fetchMessages(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $afterId = (int) $request->query('after_id', 0);

        $messages = DevJamMessage::with('user:id,name,username,avatar')
            ->where('dev_jam_id', $jam->id)
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(50)
            ->get();

        $presence = $this->touchPresenceAndSnapshot($jam, $request);
        $userId = Auth::guard('web')->id();
        $collab = $this->collaborationSnapshot($jam, $userId, true);
        $replyMap = Cache::get("dev_jam:{$jam->id}:reply_map", []);
        $messages->transform(function ($m) use ($replyMap) {
            if (isset($replyMap[$m->id])) {
                $m->reply_preview = $replyMap[$m->id];
            }
            return $m;
        });

        return response()->json([
            'messages' => $messages,
            'online_count' => $presence['online_count'],
            'online_users' => $presence['online_users'],
            'typing_users' => $presence['typing_users'],
            'polls' => $this->formatPolls($jam, $userId),
            'collaborators' => $collab['collaborators'],
            'pending_invites_for_me' => $collab['pending_invites_for_me'],
            'collab_notifications_for_me' => $collab['collab_notifications_for_me'],
            'live_code' => $collab['live_code'],
            'pinned_message' => Cache::get("dev_jam:{$jam->id}:pinned_msg"),
            'hearts_count' => (int) ($jam->hearts_count ?? 0),
            'raised_hands' => $this->getRaisedHands($jam),
            'message_reactions' => Cache::get("dev_jam:{$jam->id}:msg_reactions", []),
            'sprint' => $this->getActiveSprint($jam),
            'creator_active_tab' => Cache::get("dev_jam:{$jam->id}:creator_tab", 'html'),
            'effective_status' => $jam->computedStatus(),
            'is_suspended' => (bool) $jam->is_suspended,
            'outcome_votes' => $this->getOutcomeVotesSnapshot($jam, $userId),
        ]);
    }

    public function addHeart(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();
        if ($this->isBlockedFromJam($jam, $user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }
        if ($jam->computedStatus() === 'suspended') {
            return response()->json(['message' => 'Cette Dev Jam est suspendue.'], 423);
        }
        $delta = max(1, min(20, (int) $request->input('count', 1)));
        $jam->increment('hearts_count', $delta);
        $jam->refresh();

        return response()->json([
            'hearts_count' => (int) $jam->hearts_count,
        ]);
    }

    public function updateTyping(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();
        $isTyping = $request->boolean('is_typing', true);

        $typingKey = "dev_jam:{$jam->id}:typing";
        $typing = Cache::get($typingKey, []);
        $now = time();

        foreach ($typing as $uid => $entry) {
            if (($entry['expires_at'] ?? 0) < $now) {
                unset($typing[$uid]);
            }
        }

        if ($isTyping) {
            $typing[$user->id] = [
                'id' => $user->id,
                'name' => $user->name,
                'expires_at' => $now + 6,
            ];
        } else {
            unset($typing[$user->id]);
        }

        Cache::put($typingKey, $typing, now()->addSeconds(30));
        $presence = $this->touchPresenceAndSnapshot($jam, $request);

        return response()->json($presence);
    }

    public function postMessage(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();
        if ($this->isBlockedFromJam($jam, $user)) {
            return response()->json(['message' => 'Vous avez été bloqué par le créateur de cette Jam.'], 403);
        }
        if ($jam->computedStatus() === 'suspended') {
            return response()->json(['message' => 'Cette Dev Jam est suspendue : le chat est verrouillé.'], 423);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'submission_id' => ['nullable', 'integer'],
            'reply_to_id' => ['nullable', 'integer'],
        ]);

        $submissionId = $validated['submission_id'] ?? null;
        if ($submissionId) {
            $belongs = DevJamSubmission::where('id', $submissionId)->where('dev_jam_id', $jam->id)->exists();
            if (!$belongs) {
                $submissionId = null;
            }
        }

        $user = Auth::guard('web')->user();
        $msg = DevJamMessage::create([
            'dev_jam_id' => $jam->id,
            'submission_id' => $submissionId,
            'user_id' => $user->id,
            'message' => trim(strip_tags($validated['message'])),
        ]);

        $replyPreview = null;
        if (!empty($validated['reply_to_id'])) {
            $parentMsg = DevJamMessage::with('user:id,name')
                ->where('id', (int) $validated['reply_to_id'])
                ->where('dev_jam_id', $jam->id)
                ->first();
            if ($parentMsg) {
                $replyPreview = [
                    'id' => $parentMsg->id,
                    'author' => $parentMsg->user?->name ?? 'Participant',
                    'excerpt' => mb_strimwidth($parentMsg->message, 0, 90, '...'),
                ];
                $replyMapKey = "dev_jam:{$jam->id}:reply_map";
                $replyMap = Cache::get($replyMapKey, []);
                $replyMap[$msg->id] = $replyPreview;
                if (count($replyMap) > 250) {
                    $replyMap = array_slice($replyMap, -200, null, true);
                }
                Cache::put($replyMapKey, $replyMap, now()->addDays(7));
            }
        }

        // Clear typing status immediately once message is sent
        $typingKey = "dev_jam:{$jam->id}:typing";
        $typing = Cache::get($typingKey, []);
        unset($typing[$user->id]);
        Cache::put($typingKey, $typing, now()->addSeconds(30));

        $loadedMsg = $msg->load('user:id,name,username,avatar');
        if ($replyPreview) {
            $loadedMsg->reply_preview = $replyPreview;
        }

        return response()->json([
            'message' => $loadedMsg,
        ]);
    }

    public function deleteMessage(string $slug, DevJamMessage $message)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        if ($message->dev_jam_id !== $jam->id) {
            abort(404);
        }

        $user = Auth::guard('web')->user();
        if ($message->user_id !== $user->id && $jam->creator_user_id !== $user->id) {
            abort(403);
        }

        $message->delete();

        return response()->json(['deleted' => true]);
    }

    public function pinMessage(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();

        if ($jam->creator_user_id !== $user->id) {
            abort(403);
        }

        $messageId = $request->input('message_id');
        $key = "dev_jam:{$jam->id}:pinned_msg";

        if (!$messageId) {
            Cache::forget($key);
            return response()->json(['pinned_message' => null]);
        }

        $msg = DevJamMessage::with('user:id,name,username')
            ->where('dev_jam_id', $jam->id)
            ->findOrFail($messageId);

        $pinned = [
            'id' => $msg->id,
            'message' => $msg->message,
            'author' => $msg->user?->name ?? 'Créateur',
        ];
        Cache::put($key, $pinned, now()->addDays(7));

        return response()->json(['pinned_message' => $pinned]);
    }

    private function touchPresenceAndSnapshot(DevJam $jam, Request $request): array
    {
        $user = Auth::guard('web')->user();
        $now = time();

        $onlineKey = "dev_jam:{$jam->id}:online";
        $online = Cache::get($onlineKey, []);

        foreach ($online as $k => $entry) {
            if (($entry['last_seen'] ?? 0) < $now - 20) {
                unset($online[$k]);
            }
        }

        $visitorKey = $user
            ? 'u:' . $user->id
            : 'g:' . sha1(($request->session()->getId() ?: $request->ip()) . '|' . ($request->userAgent() ?? ''));

        $online[$visitorKey] = [
            'id' => $user?->id,
            'name' => $user?->name,
            'username' => $user?->username,
            'last_seen' => $now,
        ];

        Cache::put($onlineKey, $online, now()->addSeconds(60));

        $typingKey = "dev_jam:{$jam->id}:typing";
        $typing = Cache::get($typingKey, []);
        $typingUsers = [];
        $typingDirty = false;

        foreach ($typing as $uid => $entry) {
            if (($entry['expires_at'] ?? 0) < $now) {
                unset($typing[$uid]);
                $typingDirty = true;
                continue;
            }
            if (!$user || (int) $uid !== (int) $user->id) {
                $typingUsers[] = [
                    'id' => (int) $uid,
                    'name' => $entry['name'],
                ];
            }
        }

        if ($typingDirty) {
            Cache::put($typingKey, $typing, now()->addSeconds(30));
        }

        $onlineUsers = collect($online)
            ->filter(fn ($entry) => !empty($entry['id']) && !empty($entry['name']))
            ->map(fn ($entry) => [
                'id' => (int) $entry['id'],
                'name' => $entry['name'],
                'username' => $entry['username'] ?? null,
            ])
            ->values()
            ->all();

        return [
            'online_count' => count($online),
            'online_users' => $onlineUsers,
            'typing_users' => $typingUsers,
        ];
    }

    public function createPoll(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();

        if ($jam->creator_user_id !== $user->id) {
            return response()->json(['message' => 'Seul le créateur de la Jam peut créer un sondage.'], 403);
        }

        $hasOpenPoll = DevJamPoll::where('dev_jam_id', $jam->id)
            ->where('is_closed', false)
            ->exists();

        if ($hasOpenPoll) {
            return response()->json([
                'message' => 'Veuillez d’abord clôturer le sondage en cours avant d’en créer un nouveau.',
            ], 422);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:280'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*' => ['required', 'string', 'min:1', 'max:120'],
        ]);

        $poll = DevJamPoll::create([
            'dev_jam_id' => $jam->id,
            'creator_user_id' => $user->id,
            'question' => trim($validated['question']),
            'is_closed' => false,
        ]);

        foreach ($validated['options'] as $label) {
            DevJamPollOption::create([
                'poll_id' => $poll->id,
                'label' => trim($label),
                'votes_count' => 0,
            ]);
        }

        return response()->json([
            'polls' => $this->formatPolls($jam, $user->id),
            'message' => 'Sondage publié !',
        ]);
    }

    public function votePoll(Request $request, string $slug, DevJamPoll $poll)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        if ($poll->dev_jam_id !== $jam->id) {
            abort(404);
        }

        if ($poll->is_closed) {
            return response()->json(['message' => 'Ce sondage est clôturé.'], 422);
        }

        $validated = $request->validate([
            'option_id' => ['required', 'integer'],
        ]);

        $option = DevJamPollOption::where('id', $validated['option_id'])
            ->where('poll_id', $poll->id)
            ->firstOrFail();

        $user = Auth::guard('web')->user();

        $alreadyVoted = DevJamPollVote::where('poll_id', $poll->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyVoted) {
            return response()->json([
                'message' => 'Vous avez déjà voté à ce sondage (1 seul vote autorisé par personne).',
                'polls' => $this->formatPolls($jam, $user->id),
            ], 422);
        }

        DevJamPollVote::create([
            'poll_id' => $poll->id,
            'option_id' => $option->id,
            'user_id' => $user->id,
        ]);

        $option->update([
            'votes_count' => DevJamPollVote::where('option_id', $option->id)->count(),
        ]);

        return response()->json([
            'polls' => $this->formatPolls($jam, $user->id),
            'message' => 'Vote enregistré !',
        ]);
    }

    public function toggleClosePoll(string $slug, DevJamPoll $poll)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        if ($poll->dev_jam_id !== $jam->id) {
            abort(404);
        }

        $user = Auth::guard('web')->user();
        if ($jam->creator_user_id !== $user->id) {
            abort(403);
        }

        $poll->update(['is_closed' => !$poll->is_closed]);

        return response()->json([
            'polls' => $this->formatPolls($jam, $user->id),
        ]);
    }

    public function inviteCollaborator(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();

        if ($jam->creator_user_id !== $user->id) {
            return response()->json(['message' => 'Seul le créateur de la Jam peut inviter un co-éditeur.'], 403);
        }

        $validated = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $target = User::findOrFail($validated['target_user_id']);
        if ($target->id === $user->id) {
            return response()->json(['message' => 'Vous êtes déjà le créateur de cette Jam.'], 422);
        }

        $collabKey = "dev_jam:{$jam->id}:collab";
        $state = Cache::get($collabKey, [
            'collaborators' => [],
            'invites' => [],
            'notifications' => [],
        ]);

        if (isset($state['collaborators'][$target->id])) {
            return response()->json(['message' => "{$target->name} collabore déjà sur votre code."], 422);
        }

        $inviteId = Str::uuid()->toString();
        $state['invites'][$target->id] = [
            'id' => $inviteId,
            'from_user_id' => $user->id,
            'from_user_name' => $user->name,
            'to_user_id' => $target->id,
            'to_user_name' => $target->name,
            'created_at' => time(),
        ];

        Cache::put($collabKey, $state, now()->addHours(12));

        return response()->json([
            'message' => "Invitation envoyée à {$target->name} !",
        ]);
    }

    public function respondCollaborationInvite(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'accept' => ['required', 'boolean'],
        ]);

        $collabKey = "dev_jam:{$jam->id}:collab";
        $state = Cache::get($collabKey, [
            'collaborators' => [],
            'invites' => [],
            'notifications' => [],
        ]);

        $invite = $state['invites'][$user->id] ?? null;
        if (!$invite) {
            return response()->json(['message' => 'Invitation expirée ou introuvable.'], 404);
        }

        unset($state['invites'][$user->id]);

        $accept = (bool) $validated['accept'];
        $inviterId = (int) $invite['from_user_id'];

        if ($accept) {
            $state['collaborators'][$user->id] = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'joined_at' => time(),
            ];
            $state['notifications'][$inviterId][] = [
                'id' => Str::uuid()->toString(),
                'type' => 'accepted',
                'text' => "{$user->name} a accepté votre invitation et peut maintenant manipuler le code !",
            ];
        } else {
            $state['notifications'][$inviterId][] = [
                'id' => Str::uuid()->toString(),
                'type' => 'declined',
                'text' => "{$user->name} a refusé votre invitation à coder.",
            ];
        }

        Cache::put($collabKey, $state, now()->addHours(12));
        $collab = $this->collaborationSnapshot($jam, $user->id, false);

        return response()->json([
            'accepted' => $accept,
            'collaborators' => $collab['collaborators'],
            'live_code' => $collab['live_code'],
            'message' => $accept
                ? 'Invitation acceptée ! Vous pouvez maintenant éditer le code ensemble.'
                : 'Invitation refusée.',
        ]);
    }

    public function revokeCollaborator(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();

        if ($jam->creator_user_id !== $user->id) {
            abort(403);
        }

        $targetUserId = $request->input('user_id');
        $collabKey = "dev_jam:{$jam->id}:collab";
        $state = Cache::get($collabKey, [
            'collaborators' => [],
            'invites' => [],
            'notifications' => [],
        ]);

        if ($targetUserId) {
            $uid = (int) $targetUserId;
            if (isset($state['collaborators'][$uid])) {
                unset($state['collaborators'][$uid]);
                $state['notifications'][$uid][] = [
                    'id' => Str::uuid()->toString(),
                    'type' => 'revoked',
                    'text' => 'Le créateur a révoqué votre accès co-éditeur sur cette Jam.',
                ];
            }
            unset($state['invites'][$uid]);
        } else {
            foreach (array_keys($state['collaborators']) as $uid) {
                $state['notifications'][$uid][] = [
                    'id' => Str::uuid()->toString(),
                    'type' => 'revoked',
                    'text' => 'Le créateur a révoqué votre accès co-éditeur sur cette Jam.',
                ];
            }
            $state['collaborators'] = [];
            $state['invites'] = [];
        }

        Cache::put($collabKey, $state, now()->addHours(12));

        return response()->json([
            'collaborators' => array_values($state['collaborators']),
            'message' => 'Accès co-éditeur révoqué.',
        ]);
    }

    public function syncLiveCode(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();
        if ($this->isBlockedFromJam($jam, $user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }
        $effective = $jam->computedStatus();
        if ($effective === 'suspended') {
            return response()->json(['message' => 'Cette Dev Jam est suspendue.'], 423);
        }
        if (in_array($effective, ['ended', 'rejected'], true)) {
            return response()->json(['message' => 'Le temps de la Jam est écoulé : mode lecture seule.'], 422);
        }

        $collabKey = "dev_jam:{$jam->id}:collab";
        $state = Cache::get($collabKey, ['collaborators' => []]);
        $isCreator = $jam->creator_user_id === $user->id;
        $isCollaborator = isset($state['collaborators'][$user->id]);

        if (!$isCreator && !$isCollaborator) {
            return response()->json(['message' => 'Non autorisé à modifier le code partagé.'], 403);
        }

        $validated = $request->validate([
            'html_code' => ['nullable', 'string', 'max:200000'],
            'css_code' => ['nullable', 'string', 'max:200000'],
            'js_code' => ['nullable', 'string', 'max:200000'],
        ]);

        $liveCode = [
            'html_code' => $validated['html_code'] ?? '',
            'css_code' => $validated['css_code'] ?? '',
            'js_code' => $validated['js_code'] ?? '',
            'updated_by' => $user->id,
            'updated_by_name' => $user->name,
            'updated_at' => microtime(true),
        ];

        Cache::put("dev_jam:{$jam->id}:live_code", $liveCode, now()->addHours(12));

        return response()->json(['ok' => true, 'live_code' => $liveCode]);
    }

    private function formatPolls(DevJam $jam, ?int $userId): array
    {
        $polls = DevJamPoll::with('options')
            ->where('dev_jam_id', $jam->id)
            ->latest('id')
            ->get();

        $userVotes = $userId
            ? DevJamPollVote::whereIn('poll_id', $polls->pluck('id'))
                ->where('user_id', $userId)
                ->pluck('option_id', 'poll_id')
            : collect([]);

        return $polls->map(function (DevJamPoll $poll) use ($userVotes) {
            $totalVotes = (int) $poll->options->sum('votes_count');
            return [
                'id' => $poll->id,
                'question' => $poll->question,
                'is_closed' => (bool) $poll->is_closed,
                'total_votes' => $totalVotes,
                'user_voted_option_id' => $userVotes->get($poll->id),
                'options' => $poll->options->map(function (DevJamPollOption $opt) use ($totalVotes) {
                    $pct = $totalVotes > 0 ? (int) round(($opt->votes_count / $totalVotes) * 100) : 0;
                    return [
                        'id' => $opt->id,
                        'label' => $opt->label,
                        'votes_count' => (int) $opt->votes_count,
                        'percentage' => $pct,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    private function collaborationSnapshot(DevJam $jam, ?int $userId, bool $consumeNotifications): array
    {
        $collabKey = "dev_jam:{$jam->id}:collab";
        $state = Cache::get($collabKey, [
            'collaborators' => [],
            'invites' => [],
            'notifications' => [],
        ]);

        $pendingInvitesForMe = [];
        $notificationsForMe = [];

        if ($userId) {
            if (isset($state['invites'][$userId])) {
                $pendingInvitesForMe[] = $state['invites'][$userId];
            }
            if (!empty($state['notifications'][$userId])) {
                $notificationsForMe = $state['notifications'][$userId];
                if ($consumeNotifications) {
                    unset($state['notifications'][$userId]);
                    Cache::put($collabKey, $state, now()->addHours(12));
                }
            }
        }

        return [
            'collaborators' => array_values($state['collaborators'] ?? []),
            'pending_invites_for_me' => $pendingInvitesForMe,
            'collab_notifications_for_me' => $notificationsForMe,
            'live_code' => Cache::get("dev_jam:{$jam->id}:live_code"),
        ];
    }

    public function toggleHandRaise(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();
        $key = "dev_jam:{$jam->id}:hands";
        $hands = Cache::get($key, []);
        $targetUserId = (int) $request->input('user_id', $user->id);

        // Only creator can dismiss another user's raised hand
        if ($targetUserId !== $user->id && $jam->creator_user_id !== $user->id) {
            abort(403);
        }

        if ($request->boolean('lower', false) || ($targetUserId === $user->id && isset($hands[$user->id]))) {
            unset($hands[$targetUserId]);
            $raised = false;
        } else {
            $hands[$user->id] = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'raised_at' => time(),
            ];
            $raised = true;
        }

        Cache::put($key, $hands, now()->addHours(6));

        return response()->json([
            'raised' => $raised,
            'raised_hands' => $this->getRaisedHands($jam),
        ]);
    }

    public function reactMessage(Request $request, string $slug, DevJamMessage $message)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        if ($message->dev_jam_id !== $jam->id) {
            abort(404);
        }

        $validated = $request->validate([
            'emoji' => ['required', 'string', 'in:👍,🔥,🚀,💡'],
        ]);

        $userId = Auth::guard('web')->id();
        $emoji = $validated['emoji'];
        $key = "dev_jam:{$jam->id}:msg_reactions";
        $all = Cache::get($key, []);
        $msgReactions = $all[$message->id] ?? [];
        $users = $msgReactions[$emoji] ?? [];

        if (in_array($userId, $users, true)) {
            $users = array_values(array_filter($users, fn ($id) => (int) $id !== (int) $userId));
        } else {
            $users[] = (int) $userId;
        }

        if (empty($users)) {
            unset($msgReactions[$emoji]);
        } else {
            $msgReactions[$emoji] = $users;
        }

        if (empty($msgReactions)) {
            unset($all[$message->id]);
        } else {
            $all[$message->id] = $msgReactions;
        }

        Cache::put($key, $all, now()->addDays(7));

        return response()->json([
            'message_reactions' => $all,
        ]);
    }

    public function updateSprint(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();
        if ($jam->creator_user_id !== $user->id) {
            abort(403, 'Seul le créateur de la Jam peut piloter le Sprint.');
        }

        $key = "dev_jam:{$jam->id}:sprint";
        if ($request->boolean('stop', false)) {
            Cache::forget($key);
            return response()->json(['sprint' => null]);
        }

        $validated = $request->validate([
            'duration_min' => ['required', 'integer', 'min:1', 'max:120'],
            'label' => ['nullable', 'string', 'max:60'],
        ]);

        $duration = (int) $validated['duration_min'];
        $sprint = [
            'label' => trim((string) ($validated['label'] ?? '')) ?: "Sprint {$duration} min",
            'duration_min' => $duration,
            'started_at' => (int) round(microtime(true) * 1000),
            'ends_at' => (int) round((microtime(true) + ($duration * 60)) * 1000),
        ];

        Cache::put($key, $sprint, now()->addMinutes($duration + 30));

        return response()->json(['sprint' => $sprint]);
    }

    public function updateCreatorTab(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();
        if ($jam->creator_user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'tab' => ['required', 'in:html,css,js'],
        ]);

        Cache::put("dev_jam:{$jam->id}:creator_tab", $validated['tab'], now()->addHours(12));

        return response()->json([
            'creator_active_tab' => $validated['tab'],
        ]);
    }

    private function getRaisedHands(DevJam $jam): array
    {
        $key = "dev_jam:{$jam->id}:hands";
        $hands = Cache::get($key, []);
        $cutoff = time() - 7200;
        $cleaned = array_filter($hands, fn ($h) => ($h['raised_at'] ?? 0) >= $cutoff);
        if (count($cleaned) !== count($hands)) {
            Cache::put($key, $cleaned, now()->addHours(6));
        }

        return array_values($cleaned);
    }

    private function getActiveSprint(DevJam $jam): ?array
    {
        $key = "dev_jam:{$jam->id}:sprint";
        $sprint = Cache::get($key);
        if (!$sprint || !isset($sprint['ends_at'])) {
            return null;
        }
        // Keep sprint visible for 30s after expiration so participants see "00:00 Terminé!"
        if (((int) $sprint['ends_at'] + 30000) < (int) round(microtime(true) * 1000)) {
            Cache::forget($key);
            return null;
        }

        return $sprint;
    }

    public function suspendJam(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();

        if (!$user || $jam->creator_user_id !== $user->id) {
            return response()->json(['message' => 'Seul le créateur peut suspendre cette Jam.'], 403);
        }

        if (!$jam->is_suspended && $jam->computedStatus() !== 'ended') {
            $jam->is_suspended = true;
            $jam->suspended_at = now();
            $jam->save();
        }

        return response()->json([
            'suspended' => true,
            'effective_status' => $jam->computedStatus(),
        ]);
    }

    public function voteOutcome(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();

        if (!$user) {
            return response()->json(['message' => 'Connexion requise pour voter.'], 401);
        }
        if ($this->isBlockedFromJam($jam, $user)) {
            return response()->json(['message' => 'Vous avez été bloqué par le créateur de cette Jam.'], 403);
        }
        if ($jam->computedStatus() === 'suspended') {
            return response()->json(['message' => 'Cette Jam est suspendue et verrouillée.'], 423);
        }
        if ($jam->computedStatus() !== 'ended' && (!$jam->ends_at || now()->lt($jam->ends_at))) {
            return response()->json(['message' => 'Le vote final Oui/Non est disponible une fois la Jam terminée.'], 422);
        }

        $validated = $request->validate([
            'vote' => ['required', 'in:yes,no'],
        ]);

        DevJamOutcomeVote::updateOrCreate(
            ['dev_jam_id' => $jam->id, 'user_id' => $user->id],
            ['vote' => $validated['vote']]
        );

        return response()->json([
            'outcome_votes' => $this->getOutcomeVotesSnapshot($jam, $user->id),
        ]);
    }

    private function isBlockedFromJam(DevJam $jam, ?User $user): bool
    {
        if (!$user || !$jam->creator_user_id || $jam->creator_user_id === $user->id) {
            return false;
        }

        return DevJamCreatorBlock::where('creator_user_id', $jam->creator_user_id)
            ->where('blocked_user_id', $user->id)
            ->exists();
    }

    private function getOutcomeVotesSnapshot(DevJam $jam, ?int $userId): array
    {
        $yes = DevJamOutcomeVote::where('dev_jam_id', $jam->id)->where('vote', 'yes')->count();
        $no = DevJamOutcomeVote::where('dev_jam_id', $jam->id)->where('vote', 'no')->count();
        $userVote = $userId
            ? DevJamOutcomeVote::where('dev_jam_id', $jam->id)->where('user_id', $userId)->value('vote')
            : null;

        return [
            'yes' => $yes,
            'no' => $no,
            'total' => $yes + $no,
            'user_vote' => $userVote,
        ];
    }

    private function getJamOnlineCount(DevJam $jam): int
    {
        $presence = Cache::get("dev_jam:{$jam->id}:presence", []);
        if (!is_array($presence) || empty($presence)) {
            return 0;
        }
        $now = time();
        $active = array_filter($presence, fn ($entry) => ($entry['expires_at'] ?? 0) >= $now);
        return count($active);
    }
}
