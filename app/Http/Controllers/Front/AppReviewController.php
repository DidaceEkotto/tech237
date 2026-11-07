<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\AppReview;

class AppReviewController extends Controller
{
    public function index()
    {
        $reviews = AppReview::with('author')
            ->where('status', 'published')
            ->latest()
            ->get();

        return Inertia::render('AppReviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    public function show(string $slug)
    {
        $review = AppReview::with('author')
            ->where('slug', $slug)
            ->firstOrFail();

        $review->increment('views_count');

        $relatedReviews = AppReview::where('id', '!=', $review->id)
            ->where('status', 'published')
            ->latest()
            ->take(2)
            ->get();

        return Inertia::render('AppReviews/Show', [
            'review' => $review,
            'relatedReviews' => $relatedReviews,
        ]);
    }
}
