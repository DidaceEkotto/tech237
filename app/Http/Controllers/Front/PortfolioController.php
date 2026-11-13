<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Poc;
use App\Models\XpLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    public function show(Request $request, string $username)
    {
        $cleanUsername = ltrim(trim($username), '@');

        $user = User::where('username', $cleanUsername)->first();

        if (!$user && is_numeric($cleanUsername)) {
            $user = User::find($cleanUsername);
        }

        if (!$user) {
            abort(404, 'Ce profil / portfolio est introuvable.');
        }

        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            abort(404, 'Ce compte utilisateur n\'est plus disponible.');
        }

        $isOwner = Auth::guard('web')->check() && Auth::guard('web')->id() === $user->id;

        if (!$user->portfolio_published && !$isOwner) {
            abort(404, 'Ce portfolio est actuellement en mode privé.');
        }

        // Increment views count for visitors
        if (!$isOwner) {
            $user->increment('portfolio_views');
        }

        // Calculate rank position
        $rankPosition = User::where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'banned');
            })
            ->where('xp', '>', $user->xp ?? 0)
            ->count() + 1;

        $currentTier = XpLevel::getLevelForXp($user->xp ?? 0);
        $nextTier = XpLevel::getNextLevelForXp($user->xp ?? 0);

        // Projects
        $projects = Project::where('user_id', $user->id)
            ->where('status', 'published')
            ->latest()
            ->get();

        // POCs
        $pocs = Poc::where('user_id', $user->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->get();

        // Badges
        $badges = $user->badges()->latest()->get();

        // Optional Tech237 Forms & Heartbeat Monitors for portfolio widgets
        $publicForms = \App\Models\Form::where('user_id', $user->id)
            ->where('status', 'published')
            ->select('id', 'title', 'slug', 'description', 'theme_color')
            ->latest()
            ->get();

        $publicMonitors = \App\Models\HeartbeatMonitor::where('user_id', $user->id)
            ->where('is_active', true)
            ->select('id', 'name', 'url', 'is_currently_down', 'uptime_percentage', 'last_response_time_ms', 'last_checked_at')
            ->latest()
            ->get();

        return Inertia::render('Portfolio/Show', [
            'portfolioUser' => $user,
            'projects' => $projects,
            'pocs' => $pocs,
            'badges' => $badges,
            'publicForms' => $publicForms,
            'publicMonitors' => $publicMonitors,
            'currentTier' => $currentTier,
            'nextTier' => $nextTier,
            'rankPosition' => $rankPosition,
            'isOwner' => $isOwner,
        ]);
    }
}
