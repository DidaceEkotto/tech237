<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\DevJam;
use App\Models\DevJamMessage;
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

        $query = DevJam::with(['creatorUser:id,name,username,avatar', 'creatorAdmin:id,name'])
            ->withCount(['submissions', 'messages'])
            ->where(function ($q) use ($userId) {
                $q->whereNotIn('status', ['pending_validation', 'rejected']);
                if ($userId) {
                    $q->orWhere('creator_user_id', $userId);
                }
            })
            ->orderByDesc('is_featured')
            ->latest();

        if (in_array($mode, ['official', 'community'], true)) {
            $query->where('mode', $mode);
        }

        $jams = $query->get()->map(function (DevJam $jam) {
            $jam->effective_status = $jam->computedStatus();
            return $jam;
        });

        return Inertia::render('DevJams/Index', [
            'jams' => $jams,
            'modeFilter' => $mode,
            'counts' => [
                'all' => $jams->count(),
                'official' => DevJam::where('mode', 'official')->whereNotIn('status', ['pending_validation', 'rejected'])->count(),
                'community' => DevJam::where('mode', 'community')->whereNotIn('status', ['pending_validation', 'rejected'])->count(),
            ],
        ]);
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
            'creatorUser:id,name,username,avatar',
            'creatorAdmin:id,name',
            'submissions' => fn ($q) => $q->with('user:id,name,username,avatar')->orderByDesc('votes_count')->latest(),
        ])->where('slug', $slug)->firstOrFail();

        $user = Auth::guard('web')->user();
        if (in_array($jam->status, ['pending_validation', 'rejected'], true) && (!$user || $jam->creator_user_id !== $user->id)) {
            abort(404);
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
        ]);
    }

    public function saveSubmission(Request $request, string $slug)
    {
        $jam = DevJam::where('slug', $slug)->firstOrFail();
        $effective = $jam->computedStatus();
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

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'submission_id' => ['nullable', 'integer'],
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

        // Clear typing status immediately once message is sent
        $typingKey = "dev_jam:{$jam->id}:typing";
        $typing = Cache::get($typingKey, []);
        unset($typing[$user->id]);
        Cache::put($typingKey, $typing, now()->addSeconds(30));

        return response()->json([
            'message' => $msg->load('user:id,name,username,avatar'),
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
}
