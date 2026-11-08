<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\GameReview;
use App\Models\Comment;
use App\Services\UniqueViewService;

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

    public function show(string $slug, Request $request)
    {
        $review = GameReview::with('author')
            ->where('slug', $slug)
            ->firstOrFail();

        UniqueViewService::recordView($review, $request);

        $relatedReviews = GameReview::where('id', '!=', $review->id)
            ->where('status', 'published')
            ->latest()
            ->take(2)
            ->get();

        // Approved comments with replies
        $comments = Comment::where('game_review_id', $review->id)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->with(['user', 'replies' => function ($q) {
                $q->where('status', 'approved')->with('user')->latest();
            }])
            ->latest()
            ->get();

        return Inertia::render('GameReviews/Show', [
            'review' => $review,
            'relatedReviews' => $relatedReviews,
            'comments' => $comments,
        ]);
    }
}
