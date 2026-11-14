<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PortfolioPage;
use App\Models\PortfolioSite;
use App\Services\Portfolio\PageTreeValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PortfolioPageController extends Controller
{
    public function store(PortfolioSite $site, Request $request, PageTreeValidator $validator)
    {
        Gate::authorize('update', $site);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'show_in_menu' => ['sometimes', 'boolean'],
        ]);

        $baseSlug = Str::slug($validated['slug'] ?? $validated['title']) ?: 'page';
        $slug = $baseSlug;
        $i = 1;
        while ($site->pages()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $maxOrder = (int) $site->pages()->max('sort_order');

        $page = $site->pages()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'is_homepage' => false,
            'sort_order' => $maxOrder + 1,
            'status' => 'draft',
            'draft_tree' => $validator->emptyTree(),
        ]);

        return response()->json([
            'page' => $page,
            'message' => 'Page créée.',
        ], 201);
    }

    public function update(PortfolioPage $page, Request $request)
    {
        Gate::authorize('update', $page->site);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:120'],
            'slug' => ['sometimes', 'string', 'max:120'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'is_homepage' => ['sometimes', 'boolean'],
            'show_in_menu' => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['slug'])) {
            $cleanSlug = Str::slug($validated['slug']) ?: $page->slug;
            $exists = $page->site->pages()
                ->where('slug', $cleanSlug)
                ->where('id', '!=', $page->id)
                ->exists();
            if ($exists) {
                return response()->json(['message' => 'Ce slug est déjà utilisé sur ce site.'], 422);
            }
            $validated['slug'] = $cleanSlug;
        }

        if (!empty($validated['is_homepage'])) {
            $page->site->pages()->where('id', '!=', $page->id)->update(['is_homepage' => false]);
        }

        $page->update($validated);

        return response()->json(['page' => $page->fresh()]);
    }

    public function destroy(PortfolioPage $page)
    {
        Gate::authorize('update', $page->site);

        if ($page->site->pages()->count() <= 1) {
            return response()->json(['message' => 'Impossible de supprimer la dernière page du site.'], 422);
        }

        $wasHomepage = $page->is_homepage;
        $site = $page->site;
        $page->delete();

        if ($wasHomepage) {
            $site->pages()->orderBy('sort_order')->first()?->update(['is_homepage' => true]);
        }

        return response()->json(['message' => 'Page supprimée.']);
    }

    public function reorder(PortfolioSite $site, Request $request)
    {
        Gate::authorize('update', $site);

        $validated = $request->validate([
            'page_ids' => ['required', 'array'],
            'page_ids.*' => ['integer'],
        ]);

        foreach ($validated['page_ids'] as $index => $pageId) {
            $site->pages()->where('id', $pageId)->update(['sort_order' => $index]);
        }

        return response()->json(['pages' => $site->pages()->orderBy('sort_order')->get()]);
    }
}
