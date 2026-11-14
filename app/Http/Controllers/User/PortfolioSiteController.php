<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PortfolioPage;
use App\Models\PortfolioSite;
use App\Services\Portfolio\SiteExportService;
use App\Services\Portfolio\SitePublisherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'site_type' => ['nullable', 'in:onepage,multipage'],
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
            'site_type' => $validated['site_type'] ?? 'onepage',
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

        if ($site->is_published && isset($validated['name']) && $validated['name'] !== $site->name) {
            return response()->json([
                'message' => 'Le nom d’un site déjà publié en ligne est verrouillé. Dépubliez-le d’abord pour le modifier.',
            ], 422);
        }

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

    public function migrateType(PortfolioSite $site, Request $request)
    {
        Gate::authorize('update', $site);

        $validated = $request->validate([
            'target_type' => ['required', 'in:onepage,multipage'],
        ]);

        $targetType = $validated['target_type'];
        if ($site->site_type === $targetType) {
            return response()->json(['site' => $site->load('pages'), 'message' => 'Le site est déjà dans ce mode.']);
        }

        $pages = $site->pages()->orderBy('sort_order')->get();
        $home = $pages->firstWhere('is_homepage', true) ?? $pages->first();

        if ($targetType === 'onepage' && $home) {
            $mergedSections = $home->draft_tree['sections'] ?? [];
            foreach ($pages as $p) {
                if ($p->id === $home->id) {
                    continue;
                }
                foreach (($p->draft_tree['sections'] ?? []) as $sec) {
                    if (empty($sec['advanced']['customId'])) {
                        $sec['advanced']['customId'] = $p->slug;
                    }
                    $mergedSections[] = $sec;
                }
            }
            $home->update([
                'draft_tree' => ['schema_version' => 1, 'sections' => $mergedSections],
            ]);
            $site->pages()->where('id', '!=', $home->id)->delete();
        } elseif ($targetType === 'multipage' && $home) {
            $sections = $home->draft_tree['sections'] ?? [];
            if (count($sections) > 1) {
                $firstSection = array_shift($sections);
                $home->update([
                    'draft_tree' => ['schema_version' => 1, 'sections' => [$firstSection]],
                ]);
                $order = 1;
                foreach ($sections as $idx => $sec) {
                    $title = $sec['label'] ?: ('Page ' . ($idx + 2));
                    $baseSlug = Str::slug($sec['advanced']['customId'] ?? $title) ?: ('page-' . ($idx + 2));
                    $slug = $baseSlug;
                    $c = 1;
                    while ($site->pages()->where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $c++;
                    }
                    $site->pages()->create([
                        'title' => $title,
                        'slug' => $slug,
                        'is_homepage' => false,
                        'sort_order' => $order++,
                        'status' => 'draft',
                        'draft_tree' => ['schema_version' => 1, 'sections' => [$sec]],
                    ]);
                }
            }
        }

        $site->update(['site_type' => $targetType]);

        return response()->json([
            'site' => $site->fresh('pages'),
            'message' => $targetType === 'onepage'
                ? 'Site migré en mode One-Page avec succès.'
                : 'Site migré en mode Multi-Pages avec succès.',
        ]);
    }

    public function stats(PortfolioSite $site)
    {
        Gate::authorize('view', $site);

        $daily = $site->visits()
            ->select('visited_date', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT visitor_hash) as uniques'))
            ->groupBy('visited_date')
            ->orderByDesc('visited_date')
            ->limit(14)
            ->get();

        $topPages = $site->visits()
            ->select('page_slug', DB::raw('COUNT(*) as views'))
            ->groupBy('page_slug')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        return response()->json([
            'views_count' => $site->views_count,
            'unique_visitors_count' => $site->unique_visitors_count,
            'daily' => $daily,
            'top_pages' => $topPages,
        ]);
    }

    public function exportZip(PortfolioSite $site, Request $request, SiteExportService $exporter)
    {
        Gate::authorize('view', $site);

        $format = in_array($request->query('format'), ['html', 'php', 'laravel'], true)
            ? $request->query('format')
            : 'html';

        $zipPath = $exporter->export($site, $format);
        $content = file_get_contents($zipPath);
        @unlink($zipPath);

        if ($content === false) {
            abort(500, 'Impossible de lire l’archive ZIP générée.');
        }

        return response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$site->slug}-{$format}.zip\"",
            'Content-Length' => (string) strlen($content),
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
