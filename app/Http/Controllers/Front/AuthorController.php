<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Admin;
use App\Models\Article;

class AuthorController extends Controller
{
    public function show(int $id)
    {
        $author = Admin::findOrFail($id);

        $articles = Article::with('category')
            ->where('admin_id', $author->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(12)
            ->through(function ($art) {
                $art->date_formatted = $art->published_at ? $art->published_at->diffForHumans() : $art->created_at->diffForHumans();
                return $art;
            });

        return Inertia::render('Authors/Show', [
            'author' => [
                'id' => $author->id,
                'name' => $author->name,
                'role' => $author->role,
                'email' => $author->email,
                'bio' => $author->bio,
                'avatar' => $author->avatar,
                'city' => $author->city,
                'social_twitter' => $author->social_twitter,
                'social_linkedin' => $author->social_linkedin,
                'social_facebook' => $author->social_facebook,
                'social_instagram' => $author->social_instagram,
                'social_tiktok' => $author->social_tiktok,
            ],
            'articles' => $articles,
        ]);
    }
}
