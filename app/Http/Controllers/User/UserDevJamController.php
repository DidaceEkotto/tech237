<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DevJam;
use App\Models\DevJamCreatorBlock;
use App\Models\DevJamMessage;
use App\Models\DevJamOutcomeVote;
use App\Models\DevJamPoll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class UserDevJamController extends Controller
{
    public function show(string $slug)
    {
        $user = Auth::guard('web')->user();

        $jam = DevJam::with([
            'submissions' => fn ($q) => $q->with('user:id,name,first_name,username,avatar')->orderByDesc('votes_count')->latest(),
        ])
            ->withCount(['submissions', 'messages', 'polls'])
            ->where('slug', $slug)
            ->where('creator_user_id', $user->id)
            ->firstOrFail();

        $jam->effective_status = $jam->computedStatus();

        $messages = DevJamMessage::with('user:id,name,first_name,username,avatar')
            ->where('dev_jam_id', $jam->id)
            ->latest('id')
            ->get();

        $polls = DevJamPoll::with('options')
            ->where('dev_jam_id', $jam->id)
            ->latest('id')
            ->get()
            ->map(function (DevJamPoll $poll) {
                $totalVotes = (int) $poll->options->sum('votes_count');
                return [
                    'id' => $poll->id,
                    'question' => $poll->question,
                    'is_closed' => (bool) $poll->is_closed,
                    'total_votes' => $totalVotes,
                    'created_at' => $poll->created_at?->toISOString(),
                    'options' => $poll->options->map(fn ($opt) => [
                        'id' => $opt->id,
                        'label' => $opt->label,
                        'votes_count' => (int) $opt->votes_count,
                        'percent' => $totalVotes > 0 ? (int) round(($opt->votes_count / $totalVotes) * 100) : 0,
                    ])->values(),
                ];
            });

        $msgReactions = Cache::get("dev_jam:{$jam->id}:msg_reactions", []);
        $totalMessageReactions = 0;
        if (is_array($msgReactions)) {
            foreach ($msgReactions as $emojiMap) {
                if (is_array($emojiMap)) {
                    foreach ($emojiMap as $uids) {
                        $totalMessageReactions += is_array($uids) ? count($uids) : 0;
                    }
                }
            }
        }

        $outcomeYes = DevJamOutcomeVote::where('dev_jam_id', $jam->id)->where('vote', 'yes')->count();
        $outcomeNo = DevJamOutcomeVote::where('dev_jam_id', $jam->id)->where('vote', 'no')->count();

        $participantIds = collect()
            ->merge($messages->pluck('user_id'))
            ->merge($jam->submissions->pluck('user_id'))
            ->merge(DevJamOutcomeVote::where('dev_jam_id', $jam->id)->pluck('user_id'))
            ->filter(fn ($id) => $id && (int) $id !== (int) $user->id)
            ->unique()
            ->values();

        $participants = User::whereIn('id', $participantIds)
            ->select('id', 'name', 'first_name', 'username', 'avatar')
            ->get();

        $blockedUsers = DevJamCreatorBlock::with('blockedUser:id,name,first_name,username,avatar')
            ->where('creator_user_id', $user->id)
            ->latest()
            ->get();

        $presence = Cache::get("dev_jam:{$jam->id}:presence", []);
        $now = time();
        $onlineCount = is_array($presence)
            ? count(array_filter($presence, fn ($e) => ($e['expires_at'] ?? 0) >= $now))
            : 0;

        return Inertia::render('User/DevJamManage', [
            'jam' => $jam,
            'messages' => $messages,
            'polls' => $polls,
            'participants' => $participants,
            'blockedUsers' => $blockedUsers,
            'blockedUserIds' => $blockedUsers->pluck('blocked_user_id')->values(),
            'messageReactions' => $msgReactions,
            'stats' => [
                'online_count' => $onlineCount,
                'participants_count' => $participants->count(),
                'submissions_count' => (int) $jam->submissions_count,
                'messages_count' => (int) $jam->messages_count,
                'polls_count' => (int) $jam->polls_count,
                'hearts_count' => (int) ($jam->hearts_count ?? 0),
                'message_reactions_count' => $totalMessageReactions,
                'outcome_yes' => $outcomeYes,
                'outcome_no' => $outcomeNo,
                'outcome_total' => $outcomeYes + $outcomeNo,
            ],
        ]);
    }

    public function blockUser(Request $request, string $slug)
    {
        $user = Auth::guard('web')->user();
        $jam = DevJam::where('slug', $slug)->where('creator_user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $targetId = (int) $validated['user_id'];
        if ($targetId === $user->id) {
            return back()->with('error', 'Vous ne pouvez pas vous bloquer vous-même.');
        }

        DevJamCreatorBlock::firstOrCreate(
            ['creator_user_id' => $user->id, 'blocked_user_id' => $targetId],
            ['reason' => $validated['reason'] ?? null]
        );

        // Revoke any active collaboration access on this jam
        $collabKey = "dev_jam:{$jam->id}:collab";
        $state = Cache::get($collabKey);
        if (is_array($state)) {
            unset($state['collaborators'][$targetId], $state['invites'][$targetId]);
            Cache::put($collabKey, $state, now()->addHours(12));
        }

        return back()->with('success', 'Utilisateur bloqué pour toutes vos Dev Jams.');
    }

    public function unblockUser(Request $request, string $slug)
    {
        $user = Auth::guard('web')->user();
        DevJam::where('slug', $slug)->where('creator_user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        DevJamCreatorBlock::where('creator_user_id', $user->id)
            ->where('blocked_user_id', (int) $validated['user_id'])
            ->delete();

        return back()->with('success', 'Utilisateur débloqué.');
    }
}
