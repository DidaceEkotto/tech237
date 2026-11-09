<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AppReview;
use App\Models\Comment;
use App\Services\UniqueViewService;

class AppReviewController extends Controller
{
    public function index()
    {
        $reviews = AppReview::with('author')
            ->where('status', 'published')
            ->latest()
            ->get();

        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        $isBookmarked = false;
        if ($user) {
            $isBookmarked = \App\Models\UserBookmark::where('user_id', $user->id)
                ->where('bookmarkable_type', 'apps_rubrique')
                ->exists();
        }

        return Inertia::render('AppReviews/Index', [
            'reviews' => $reviews,
            'isBookmarked' => $isBookmarked,
        ]);
    }

    public function show(string $slug, Request $request)
    {
        $review = AppReview::with('author')
            ->where('slug', $slug)
            ->firstOrFail();

        UniqueViewService::recordView($review, $request);

        $relatedReviews = AppReview::where('id', '!=', $review->id)
            ->where('status', 'published')
            ->latest()
            ->take(2)
            ->get();

        $prevReview = AppReview::where('status', 'published')
            ->where('id', '<', $review->id)
            ->latest('id')
            ->first();

        $nextReview = AppReview::where('status', 'published')
            ->where('id', '>', $review->id)
            ->oldest('id')
            ->first();

        $authorReviews = [];
        if ($review->admin_id) {
            $authorReviews = AppReview::where('admin_id', $review->admin_id)
                ->where('status', 'published')
                ->where('id', '!=', $review->id)
                ->latest()
                ->take(4)
                ->get();
        }

        // Approved comments with replies
        $comments = Comment::where('app_review_id', $review->id)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->with(['user', 'replies' => function ($q) {
                $q->where('status', 'approved')->with('user')->latest();
            }])
            ->latest()
            ->get();

        $isBookmarked = auth()->check() ? \App\Models\UserBookmark::where('user_id', auth()->id())
            ->where('bookmarkable_type', 'app_review')
            ->where('bookmarkable_id', $review->id)
            ->exists() : false;

        return Inertia::render('AppReviews/Show', [
            'review' => $review,
            'isBookmarked' => $isBookmarked,
            'relatedReviews' => $relatedReviews,
            'prevReview' => $prevReview,
            'nextReview' => $nextReview,
            'authorReviews' => $authorReviews,
            'comments' => $comments,
        ]);
    }
}
