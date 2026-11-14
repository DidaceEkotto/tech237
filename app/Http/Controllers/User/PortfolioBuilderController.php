<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PortfolioMedia;
use App\Models\PortfolioPage;
use App\Models\PortfolioPageRevision;
use App\Models\PortfolioSite;
use App\Models\PortfolioTemplate;
use App\Models\PortfolioThemePart;
use App\Services\Portfolio\DynamicDataHydrator;
use App\Services\Portfolio\PageTreeValidator;
use App\Services\Portfolio\SitePublisherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PortfolioBuilderController extends Controller
{
    public function editor(
        PortfolioSite $site,
        Request $request,
        DynamicDataHydrator $hydrator,
        SitePublisherService $publisher
    ) {
        Gate::authorize('view', $site);

        $user = $request->user();
        $pages = $site->pages()->orderBy('sort_order')->get();

        if ($pages->isEmpty()) {
            $pages = collect([
                $site->pages()->create([
                    'title' => 'Accueil',
                    'slug' => 'accueil',
                    'is_homepage' => true,
                    'sort_order' => 0,
                    'status' => 'draft',
                    'draft_tree' => $publisher->buildStarterHomeTree($user),
                ]),
            ]);
        }

        $requestedPageId = (int) $request->query('page', 0);
        $activePage = $requestedPageId
            ? ($pages->firstWhere('id', $requestedPageId) ?? $pages->first())
            : ($pages->firstWhere('is_homepage', true) ?? $pages->first());

        $revisions = $activePage
            ? $activePage->revisions()->select('id', 'portfolio_page_id', 'label', 'created_at')->limit(15)->get()
            : collect();

        $themeParts = $site->themeParts()->get()->keyBy('type');
        $media = PortfolioMedia::where('user_id', $user->id)->latest()->limit(60)->get();
        $templates = PortfolioTemplate::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('is_public', true);
        })->latest()->get();

        return Inertia::render('User/Portfolio/Editor', [
            'site' => $site,
            'pages' => $pages->values(),
            'activePage' => $activePage,
            'revisions' => $revisions,
            'themeParts' => $themeParts,
            'media' => $media,
            'templates' => $templates,
            'hydratedData' => $hydrator->hydrateForUser($user),
            'portfolioUser' => $user->only(['id', 'name', 'username', 'avatar', 'headline', 'bio', 'location', 'email', 'xp']),
        ]);
    }

    public function savePage(PortfolioPage $page, Request $request, SitePublisherService $publisher)
    {
        Gate::authorize('update', $page->site);

        $validated = $request->validate([
            'tree' => ['required', 'array'],
            'label' => ['nullable', 'string', 'max:120'],
        ]);

        $updated = $publisher->savePageDraft(
            $page,
            $request->user(),
            $validated['tree'],
            $validated['label'] ?? 'Sauvegarde manuelle'
        );

        return response()->json([
            'page' => $updated,
            'revisions' => $updated->revisions()->select('id', 'portfolio_page_id', 'label', 'created_at')->limit(15)->get(),
            'message' => 'Brouillon enregistré.',
        ]);
    }

    public function restoreRevision(
        PortfolioPage $page,
        PortfolioPageRevision $revision,
        Request $request,
        SitePublisherService $publisher
    ) {
        Gate::authorize('update', $page->site);

        if ($revision->portfolio_page_id !== $page->id) {
            abort(404);
        }

        $updated = $publisher->savePageDraft(
            $page,
            $request->user(),
            $revision->tree_snapshot ?? [],
            'Restauration #' . $revision->id
        );

        return response()->json([
            'page' => $updated,
            'revisions' => $updated->revisions()->select('id', 'portfolio_page_id', 'label', 'created_at')->limit(15)->get(),
            'message' => 'Révision restaurée.',
        ]);
    }

    public function publish(PortfolioSite $site, SitePublisherService $publisher)
    {
        Gate::authorize('update', $site);

        $published = $publisher->publishSite($site);

        return response()->json([
            'site' => $published->load('pages'),
            'message' => 'Site publié avec succès !',
        ]);
    }

    public function unpublish(PortfolioSite $site)
    {
        Gate::authorize('update', $site);

        $site->update(['is_published' => false]);

        return response()->json([
            'site' => $site->fresh(),
            'message' => 'Site repassé en brouillon.',
        ]);
    }

    public function saveGlobalSettings(PortfolioSite $site, Request $request)
    {
        Gate::authorize('update', $site);

        $validated = $request->validate([
            'global_settings' => ['required', 'array'],
        ]);

        $site->update(['global_settings' => $validated['global_settings']]);

        return response()->json([
            'site' => $site->fresh(),
            'message' => 'Paramètres globaux enregistrés.',
        ]);
    }

    public function saveThemePart(PortfolioSite $site, Request $request, PageTreeValidator $validator)
    {
        Gate::authorize('update', $site);

        $validated = $request->validate([
            'type' => ['required', 'in:header,footer,popup'],
            'name' => ['nullable', 'string', 'max:120'],
            'tree' => ['required', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $cleanTree = $validator->sanitizeTree($validated['tree']);

        $part = PortfolioThemePart::updateOrCreate(
            ['portfolio_site_id' => $site->id, 'type' => $validated['type']],
            [
                'name' => $validated['name'] ?? ucfirst($validated['type']) . ' Global',
                'tree' => $cleanTree,
                'is_active' => $validated['is_active'] ?? true,
            ]
        );

        return response()->json([
            'themePart' => $part,
            'message' => ucfirst($validated['type']) . ' global enregistré.',
        ]);
    }
}
