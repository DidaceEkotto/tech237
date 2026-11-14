<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\PortfolioSite;
use App\Models\User;
use App\Models\Project;
use App\Models\Poc;
use App\Models\XpLevel;
use App\Services\Portfolio\DynamicDataHydrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    public function show(Request $request, string $username, ?string $pageSlug = null, ?DynamicDataHydrator $hydrator = null)
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

        // Check if user has a published PortfolioSite (or owner is previewing a builder site)
        $previewSiteId = $isOwner ? (int) $request->query('preview_site', 0) : 0;
        $builderSite = null;

        if ($previewSiteId > 0) {
            $builderSite = PortfolioSite::where('user_id', $user->id)
                ->where('id', $previewSiteId)
                ->with(['pages' => fn ($q) => $q->orderBy('sort_order'), 'themeParts'])
                ->first();
        } else {
            $builderSite = PortfolioSite::where('user_id', $user->id)
                ->where('is_published', true)
                ->with(['pages' => fn ($q) => $q->orderBy('sort_order'), 'themeParts'])
                ->orderByDesc('is_primary')
                ->first();
        }

        if ($builderSite && $builderSite->pages->isNotEmpty()) {
            $activePage = $pageSlug
                ? $builderSite->pages->firstWhere('slug', $pageSlug)
                : ($builderSite->pages->firstWhere('is_homepage', true) ?? $builderSite->pages->first());

            if ($activePage) {
                $tree = $previewSiteId > 0
                    ? ($activePage->draft_tree ?? $activePage->published_tree)
                    : $activePage->published_tree;

                if (!empty($tree) && !empty($tree['sections'])) {
                    $hydratorInstance = $hydrator ?? app(DynamicDataHydrator::class);
                    $themeParts = $builderSite->themeParts->where('is_active', true)->keyBy('type');

                    return Inertia::render('Portfolio/PublicSite', [
                        'portfolioUser' => $user,
                        'site' => $builderSite,
                        'pages' => $builderSite->pages->where('show_in_menu', true)->values(),
                        'activePage' => $activePage,
                        'tree' => $tree,
                        'headerPart' => $themeParts->get('header'),
                        'footerPart' => $themeParts->get('footer'),
                        'hydratedData' => $hydratorInstance->hydrateForUser($user),
                        'currentTier' => $currentTier,
                        'nextTier' => $nextTier,
                        'rankPosition' => $rankPosition,
                        'isOwner' => $isOwner,
                        'isPreview' => $previewSiteId > 0,
                    ]);
                }
            }
        }

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
