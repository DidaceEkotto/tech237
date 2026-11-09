<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Article;
use App\Models\GameReview;
use App\Models\AppReview;
use App\Models\Interview;
use App\Models\Dossier;
use App\Models\Slider;
use App\Models\Category;
use App\Models\Entrepreneur;
use App\Models\Conference;
use App\Models\Startup;
use App\Models\Project;

class HomeController extends Controller
{
    public function index()
    {
        // Sliders for the carousel
        $sliders = Slider::where('is_active', true)->orderBy('order')->get();

        // Hero Articles
        $heroArticles = Article::with(['category', 'author'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(5)
            ->get()
            ->map(function ($art) {
                $art->date_formatted = $art->published_at ? $art->published_at->diffForHumans() : $art->created_at->diffForHumans();
                return $art;
            });

        if ($heroArticles->count() < 5) {
            $extra = Article::with(['category', 'author'])
                ->where('status', 'published')
                ->whereNotIn('id', $heroArticles->pluck('id'))
                ->latest('published_at')
                ->take(5 - $heroArticles->count())
                ->get()
                ->map(function ($art) {
                    $art->date_formatted = $art->published_at ? $art->published_at->diffForHumans() : $art->created_at->diffForHumans();
                    return $art;
                });
            $heroArticles = $heroArticles->concat($extra);
        }

        $latestArticles = Article::with(['category', 'author'])
            ->where('status', 'published')
            ->latest('published_at')
            ->take(8)
            ->get()
            ->map(function ($art) {
                $art->date_formatted = $art->published_at ? $art->published_at->diffForHumans() : $art->created_at->diffForHumans();
                return $art;
            });

        $trendingArticles = Article::with(['category'])
            ->where('status', 'published')
            ->orderByDesc('views_count')
            ->take(6)
            ->get();

        // Categories / Rubriques sections (Séries, Cinéma, High-Tech, Gaming)
        $seriesArticles = Article::whereHas('category', fn($q) => $q->where('slug', 'series'))
            ->where('status', 'published')
            ->latest('published_at')
            ->take(4)
            ->get();

        $cinemaArticles = Article::whereHas('category', fn($q) => $q->where('slug', 'cinema'))
            ->where('status', 'published')
            ->latest('published_at')
            ->take(4)
            ->get();

        $techArticles = Article::whereHas('category', fn($q) => $q->whereIn('slug', ['high-tech', 'tech-ia']))
            ->where('status', 'published')
            ->latest('published_at')
            ->take(4)
            ->get();

        $gamingArticles = Article::whereHas('category', fn($q) => $q->where('slug', 'gaming'))
            ->where('status', 'published')
            ->latest('published_at')
            ->take(4)
            ->get();

        $gameReviews = GameReview::where('status', 'published')
            ->latest()
            ->take(4)
            ->get();

        $appReviews = AppReview::where('status', 'published')
            ->latest()
            ->take(4)
            ->get();

        $interviews = Interview::where('status', 'published')
            ->latest()
            ->take(4)
            ->get();

        $dailyInterview = Interview::where('status', 'published')
            ->latest()
            ->first();

        $dossiers = Dossier::take(6)->get();

        // 1. Entrepreneur of the month (or fallback to most read article)
        $entrepreneurOfMonth = Entrepreneur::with(['startup.projects'])
            ->where('is_active', true)
            ->latest()
            ->first();

        $mostReadArticle = Article::with(['category', 'author'])
            ->where('status', 'published')
            ->orderByDesc('views_count')
            ->first();

        // 2. Events & Conferences (conditional: only if they exist)
        $upcomingEvents = Conference::where('status', 'approved')
            ->latest('event_date')
            ->take(3)
            ->get();

        // 3. Startups & what project they are currently developing (conditional: only if they exist)
        $featuredStartups = Startup::where('status', 'approved')
            ->with(['projects' => fn($q) => $q->where('status', 'published')->latest()])
            ->latest()
            ->take(4)
            ->get();
        if ($featuredStartups->isEmpty()) {
            $featuredStartups = Startup::with(['projects' => fn($q) => $q->where('status', 'published')->latest()])
                ->latest()
                ->take(4)
                ->get();
        }

        // 4. Featured Projects (conditional: only if they exist)
        $featuredProjects = Project::where('status', 'published')
            ->with(['startup', 'programmingLanguages'])
            ->latest()
            ->take(4)
            ->get();

        $activePoll = \App\Models\TechPoll::where('is_active', true)
            ->with('options')
            ->latest()
            ->first();

        if ($activePoll) {
            $activePoll->user_has_voted = $activePoll->hasUserVoted(auth()->id(), request()->ip());
        }

        return Inertia::render('Home', [
            'activePoll' => $activePoll,
            'sliders' => $sliders,
            'entrepreneurOfMonth' => $entrepreneurOfMonth,
            'mostReadArticle' => $mostReadArticle,
            'upcomingEvents' => $upcomingEvents,
            'featuredStartups' => $featuredStartups,
            'featuredProjects' => $featuredProjects,
            'heroArticles' => $heroArticles,
            'latestArticles' => $latestArticles,
            'trendingArticles' => $trendingArticles,
            'seriesArticles' => $seriesArticles,
            'cinemaArticles' => $cinemaArticles,
            'techArticles' => $techArticles,
            'gamingArticles' => $gamingArticles,
            'gameReviews' => $gameReviews,
            'appReviews' => $appReviews,
            'interviews' => $interviews,
            'dailyInterview' => $dailyInterview,
            'dossiers' => $dossiers,
        ]);
    }
}
