<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\GameReview;

class GameReviewController extends Controller
{
    public function index()
    {
        $reviews = GameReview::with('author')
            ->where('status', 'published')
            ->latest()
            ->get();

        return Inertia::render('GameReviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    public function show(string $slug)
    {
        $review = GameReview::with('author')
            ->where('slug', $slug)
            ->firstOrFail();

        $review->increment('views_count');

        $relatedReviews = GameReview::where('id', '!=', $review->id)
            ->where('status', 'published')
            ->latest()
            ->take(2)
            ->get();

        return Inertia::render('GameReviews/Show', [
            'review' => $review,
            'relatedReviews' => $relatedReviews,
        ]);
    }
}
