<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Article;
use App\Models\Category;
use App\Models\Rubrique;
use App\Models\Dossier;
use App\Models\Comment;

class ArticleController extends Controller
{
    public function show(string $slug)
    {
        $article = Article::with(['category', 'author', 'tags', 'dossier'])
            ->where('slug', $slug)
            ->firstOrFail();

        $article->increment('views_count');
        $article->date_formatted = $article->published_at ? $article->published_at->translatedFormat('d F Y à H:i') : $article->created_at->translatedFormat('d F Y');

        // 4 latest articles by the same author for the author card
        $authorArticles = Article::where('admin_id', $article->admin_id)
            ->where('id', '!=', $article->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->take(4)
            ->get();

        // Previous and Next articles for navigation
        $prevArticle = Article::where('id', '<', $article->id)
            ->where('status', 'published')
            ->latest('id')
            ->first(['id', 'slug', 'title_fr', 'title_en', 'image']);

        $nextArticle = Article::where('id', '>', $article->id)
            ->where('status', 'published')
            ->oldest('id')
            ->first(['id', 'slug', 'title_fr', 'title_en', 'image']);

        $relatedArticles = Article::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->take(4)
            ->get();

        // Approved comments with replies
        $comments = Comment::where('article_id', $article->id)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->with(['user', 'replies' => function ($q) {
                $q->where('status', 'approved')->with('user')->latest();
            }])
            ->latest()
            ->get();

        return Inertia::render('Articles/Show', [
            'article' => $article,
            'authorArticles' => $authorArticles,
            'prevArticle' => $prevArticle,
            'nextArticle' => $nextArticle,
            'relatedArticles' => $relatedArticles,
            'comments' => $comments,
        ]);
    }

    public function byCategory(string $slug)
    {
        if ($slug === 'applications') {
            $category = Category::with('children')->firstOrCreate(['slug' => 'applications'], [
                'name_fr' => 'Applications',
                'name_en' => 'Applications',
                'color' => '#10B981',
                'icon' => 'bi-phone',
                'order' => 6,
            ]);
        } else {
            $category = Category::with('children')
                ->where('slug', $slug)
                ->firstOrFail();
        }

        $categoryIds = [$category->id];
        foreach ($category->children as $child) {
            $categoryIds[] = $child->id;
        }

        $articles = Article::with(['category', 'author'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(12)
            ->through(function ($art) {
                $art->date_formatted = $art->published_at ? $art->published_at->diffForHumans() : $art->created_at->diffForHumans();
                return $art;
            });

        return Inertia::render('Articles/ByCategory', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }

    public function byDossier(string $slug)
    {
        $dossier = Dossier::where('slug', $slug)->firstOrFail();

        $articles = Article::with(['category', 'author'])
            ->where('dossier_id', $dossier->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->get()
            ->map(function ($art) {
                $art->date_formatted = $art->published_at ? $art->published_at->diffForHumans() : $art->created_at->diffForHumans();
                return $art;
            });

        return Inertia::render('Articles/ByDossier', [
            'dossier' => $dossier,
            'articles' => $articles,
        ]);
    }

    public function byRubrique(string $slug)
    {
        $rubrique = Rubrique::with(['categories.children', 'dossiers'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $categoryIds = $rubrique->categories->pluck('id')->toArray();

        $query = Article::with(['category', 'author'])
            ->where('status', 'published');

        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        $articles = $query->latest('published_at')
            ->paginate(12)
            ->through(function ($art) {
                $art->date_formatted = $art->published_at ? $art->published_at->diffForHumans() : $art->created_at->diffForHumans();
                return $art;
            });

        $trendingArticles = Article::where('status', 'published')
            ->latest('views_count')
            ->take(5)
            ->get();

        return Inertia::render('Articles/ByRubrique', [
            'rubrique' => $rubrique,
            'articles' => $articles,
            'trendingArticles' => $trendingArticles,
        ]);
    }

    public function dossiersIndex(Request $request)
    {
        $letter = strtoupper((string) $request->query('letter', ''));
        $query = Dossier::withCount('articles');
        if (\Illuminate\Support\Facades\Schema::hasColumn('dossiers', 'is_active')) {
            $query->where('is_active', true);
        }

        if ($letter === '0-9') {
            $query->where(function($q) {
                for ($i = 0; $i <= 9; $i++) {
                    $q->orWhere('title_fr', 'LIKE', $i . '%');
                }
            });
        } elseif (!empty($letter) && strlen($letter) === 1 && ctype_alpha($letter)) {
            $query->where(function($q) use ($letter) {
                $q->where('title_fr', 'LIKE', '%' . $letter . '%')
                  ->orWhere('title_fr', 'LIKE', '%' . strtolower($letter) . '%');
            });
        }

        $dossiers = $query->orderBy('title_fr')->get();

        return Inertia::render('Dossiers/Index', [
            'dossiers' => $dossiers,
            'currentLetter' => $letter,
        ]);
    }
}
