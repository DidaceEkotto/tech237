<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PortfolioPage;
use App\Models\PortfolioSite;
use App\Services\Portfolio\SitePublisherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PortfolioSiteController extends Controller
{
    public function index(Request $request, SitePublisherService $publisher)
    {
        $user = $request->user();
        $publisher->ensurePrimarySiteForUser($user);

        $sites = PortfolioSite::where('user_id', $user->id)
            ->with(['pages' => fn ($q) => $q->orderBy('sort_order')])
            ->orderByDesc('is_primary')
            ->latest()
            ->get();

        return response()->json(['sites' => $sites]);
    }

    public function store(Request $request, SitePublisherService $publisher)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'starter_preset' => ['nullable', 'string', 'max:60'],
        ]);

        $user = $request->user();
        $baseSlug = Str::slug($validated['name']) ?: 'site';
        $slug = $this->uniqueSlug($baseSlug);

        $isFirst = PortfolioSite::where('user_id', $user->id)->count() === 0;

        $site = PortfolioSite::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_primary' => $isFirst,
            'is_published' => false,
            'global_settings' => [
                'primary_color' => '#4f46e5',
                'secondary_color' => '#0f172a',
                'accent_color' => '#f97316',
                'background_color' => '#ffffff',
                'font_heading' => 'Plus Jakarta Sans',
                'font_body' => 'Inter',
                'container_width' => 1180,
                'border_radius' => 16,
            ],
        ]);

        PortfolioPage::create([
            'portfolio_site_id' => $site->id,
            'title' => 'Accueil',
            'slug' => 'accueil',
            'is_homepage' => true,
            'sort_order' => 0,
            'status' => 'draft',
            'draft_tree' => $publisher->buildStarterHomeTree($user),
        ]);

        return response()->json([
            'site' => $site->load('pages'),
            'message' => 'Site créé avec succès.',
        ], 201);
    }

    public function update(PortfolioSite $site, Request $request)
    {
        Gate::authorize('update', $site);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        if (!empty($validated['is_primary'])) {
            PortfolioSite::where('user_id', $site->user_id)
                ->where('id', '!=', $site->id)
                ->update(['is_primary' => false]);
        }

        $site->update($validated);

        return response()->json([
            'site' => $site->fresh('pages'),
            'message' => 'Site mis à jour.',
        ]);
    }

    public function duplicate(PortfolioSite $site, Request $request)
    {
        Gate::authorize('update', $site);

        $copy = $site->replicate(['slug', 'is_primary', 'is_published', 'published_at']);
        $copy->name = $site->name . ' (Copie)';
        $copy->slug = $this->uniqueSlug(Str::slug($copy->name) ?: 'site-copie');
        $copy->is_primary = false;
        $copy->is_published = false;
        $copy->published_at = null;
        $copy->save();

        foreach ($site->pages as $page) {
            $pageCopy = $page->replicate(['published_tree']);
            $pageCopy->portfolio_site_id = $copy->id;
            $pageCopy->status = 'draft';
            $pageCopy->save();
        }

        foreach ($site->themeParts as $part) {
            $partCopy = $part->replicate();
            $partCopy->portfolio_site_id = $copy->id;
            $partCopy->save();
        }

        return response()->json([
            'site' => $copy->load('pages'),
            'message' => 'Site dupliqué.',
        ], 201);
    }

    public function destroy(PortfolioSite $site, Request $request)
    {
        Gate::authorize('delete', $site);

        $total = PortfolioSite::where('user_id', $site->user_id)->count();
        if ($total <= 1) {
            return response()->json([
                'message' => 'Vous devez conserver au moins un site principal.',
            ], 422);
        }

        $wasPrimary = $site->is_primary;
        $userId = $site->user_id;
        $site->delete();

        if ($wasPrimary) {
            PortfolioSite::where('user_id', $userId)->latest()->first()?->update(['is_primary' => true]);
        }

        return response()->json(['message' => 'Site supprimé.']);
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $counter = 1;
        while (PortfolioSite::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }
}
