<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\GameReview;
use App\Models\AppReview;
use App\Models\Dossier;

class SearchController extends Controller
{
    public function autocomplete(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([
                'articles' => [],
                'games' => [],
                'apps' => [],
                'dossiers' => [],
            ]);
        }

        $articles = Article::with('category')
            ->where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('title_fr', 'like', "%{$q}%")
                      ->orWhere('title_en', 'like', "%{$q}%")
                      ->orWhere('summary_fr', 'like', "%{$q}%");
            })
            ->latest('published_at')
            ->take(5)
            ->get();

        $games = GameReview::where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('game_name', 'like', "%{$q}%")
                      ->orWhere('platform_category', 'like', "%{$q}%");
            })
            ->latest()
            ->take(4)
            ->get();

        $apps = AppReview::where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('app_name', 'like', "%{$q}%")
                      ->orWhere('category_name', 'like', "%{$q}%");
            })
            ->latest()
            ->take(4)
            ->get();

        $dossiers = Dossier::where('title_fr', 'like', "%{$q}%")
            ->orWhere('title_en', 'like', "%{$q}%")
            ->take(3)
            ->get();

        return response()->json([
            'articles' => $articles,
            'games' => $games,
            'apps' => $apps,
            'dossiers' => $dossiers,
        ]);
    }
}
