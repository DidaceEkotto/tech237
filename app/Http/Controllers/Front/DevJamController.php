<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\DevJam;
use App\Models\DevJamMessage;
use App\Models\DevJamSubmission;
use App\Models\DevJamVote;
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

        return Inertia::render('DevJams/Show', [
            'jam' => $jam,
            'mySubmission' => $mySubmission,
            'votedSubmissionIds' => $votedSubmissionIds,
            'initialMessages' => $messages,
            'isJamCreator' => $user && $jam->creator_user_id === $user->id,
            'initialOnlineCount' => $presence['online_count'],
            'initialOnlineUsers' => $presence['online_users'],
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

        return response()->json([
            'messages' => $messages,
            'online_count' => $presence['online_count'],
            'online_users' => $presence['online_users'],
            'typing_users' => $presence['typing_users'],
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
}
