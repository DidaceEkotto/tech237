<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\XpLevel;
use App\Models\Poc;
use App\Models\Project;
use App\Models\ForumTopic;
use App\Models\UserBadge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CommunityController extends Controller
{
    /**
     * Candy Crush-Style Game Roadmap of Winners / Champions
     */
    public function map(Request $request)
    {
        $levels = XpLevel::getAllTiers();

        // Get ranked active users
        $users = User::where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'banned');
            })
            ->orderBy('xp', 'desc')
            ->select('id', 'name', 'avatar', 'xp', 'rank_title', 'created_at')
            ->get();

        // Group users by their matching level tier
        $levelsWithPlayers = $levels->map(function ($lvl) use ($users) {
            $matchingPlayers = $users->filter(function ($u) use ($lvl) {
                $uTier = XpLevel::getLevelForXp($u->xp ?? 0);
                return $uTier && (int)$uTier->min_xp === (int)$lvl->min_xp;
            })->values();

            $lvlObj = is_array($lvl) ? (object)$lvl : $lvl;
            $lvlObj->players = $matchingPlayers;
            $lvlObj->players_count = $matchingPlayers->count();
            return $lvlObj;
        });

        $currentUser = Auth::user();
        $currentUserRank = null;
        if ($currentUser) {
            $currentUserPosition = $users->search(fn($u) => $u->id === $currentUser->id);
            $currentUserRank = [
                'position' => $currentUserPosition !== false ? ($currentUserPosition + 1) : count($users),
                'xp' => $currentUser->xp ?? 0,
                'tier' => XpLevel::getLevelForXp($currentUser->xp ?? 0),
                'next_tier' => XpLevel::getNextLevelForXp($currentUser->xp ?? 0),
            ];
        }

        $totalXp = $users->sum('xp');

        return Inertia::render('Gamification/Map', [
            'levels' => $levelsWithPlayers,
            'currentUserRank' => $currentUserRank,
            'totalXp' => $totalXp,
            'totalPlayers' => $users->count(),
            'topThree' => $users->take(3),
            'allUsers' => $users,
        ]);
    }

    /**
     * Public Community Member Profile / Wall
     */
    public function profile(Request $request, $id)
    {
        $user = User::with(['badges'])->findOrFail($id);

        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            abort(404, 'Ce compte utilisateur n\'est plus disponible.');
        }

        // Calculate global rank position
        $rankPosition = User::where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'banned');
            })
            ->where('xp', '>', $user->xp ?? 0)
            ->count() + 1;

        // Current level tier and next tier
        $currentTier = XpLevel::getLevelForXp($user->xp ?? 0);
        $nextTier = XpLevel::getNextLevelForXp($user->xp ?? 0);

        // Public Projects
        $projects = Project::where('user_id', $user->id)
            ->where('status', 'published')
            ->latest()
            ->take(8)
            ->get();

        // Public POCs (Mini-actus)
        $pocs = Poc::where('user_id', $user->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->take(8)
            ->get();

        // Public Forum Contributions
        $forumTopics = ForumTopic::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        $stats = [
            'total_xp' => $user->xp ?? 0,
            'rank_position' => $rankPosition,
            'projects_count' => $projects->count(),
            'pocs_count' => $pocs->count(),
            'badges_count' => $user->badges->count(),
            'forum_count' => $forumTopics->count(),
        ];

        return Inertia::render('User/PublicProfile', [
            'profileUser' => $user,
            'currentTier' => $currentTier,
            'nextTier' => $nextTier,
            'stats' => $stats,
            'projects' => $projects,
            'pocs' => $pocs,
            'forumTopics' => $forumTopics,
            'badges' => $user->badges,
        ]);
    }
}
