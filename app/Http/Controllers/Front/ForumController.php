<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\ForumPost;
use App\Models\ForumLike;
use App\Models\ForumReport;
use App\Models\User;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $categories = ForumCategory::where('is_active', true)
            ->withCount('topics')
            ->orderBy('order')
            ->get();

        $topicsQuery = ForumTopic::with(['category', 'user' => function($q) {
                $q->withCount('forumTopics');
            }])
            ->withCount('posts')
            ->orderByDesc('is_pinned')
            ->latest('updated_at');

        if ($search) {
            $topicsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $topicsQuery->where('forum_category_id', $categoryId);
        }

        $topics = $topicsQuery->paginate(15)->withQueryString();

        $hasSolvedCol = \Illuminate\Support\Facades\Schema::hasColumn('forum_topics', 'is_solved');
        $solvedCount = $hasSolvedCol
            ? ForumTopic::where('is_solved', true)->count()
            : (\Illuminate\Support\Facades\Schema::hasColumn('forum_topics', 'is_resolved') ? ForumTopic::where('is_resolved', true)->count() : 0);

        $stats = [
            'total_topics' => ForumTopic::count(),
            'total_posts' => ForumPost::count(),
            'total_solved' => $solvedCount,
        ];

        return Inertia::render('Forum/Index', [
            'categories' => $categories,
            'topics' => $topics,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
            ],
        ]);
    }

    public function show(string $categorySlug, string $topicSlug, Request $request)
    {
        $topic = ForumTopic::with(['category', 'user' => function($q) {
                $q->withCount('forumTopics');
            }])
            ->where('slug', $topicSlug)
            ->firstOrFail();

        $topic->increment('views_count');

        $userId = $request->user('web')?->id;

        // Load posts with like status and author info
        $posts = ForumPost::with(['user' => function($q) {
                $q->withCount('forumTopics');
            }])
            ->where('forum_topic_id', $topic->id)
            ->orderByDesc('likes_count')
            ->latest('created_at')
            ->get()
            ->map(function ($post) use ($userId) {
                $post->user_has_liked = $userId ? ForumLike::where('forum_post_id', $post->id)->where('user_id', $userId)->exists() : false;
                return $post;
            });

        $relatedTopics = ForumTopic::where('forum_category_id', $topic->forum_category_id)
            ->where('id', '!=', $topic->id)
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Forum/Show', [
            'topic' => $topic,
            'posts' => $posts,
            'relatedTopics' => $relatedTopics,
            'canSolve' => $userId && $userId === $topic->user_id,
        ]);
    }

    public function storeTopic(Request $request)
    {
        $validated = $request->validate([
            'forum_category_id' => 'required|exists:forum_categories,id',
            'title' => 'required|string|max:200',
            'content' => 'required|string',
        ]);

        $slug = Str::slug($validated['title']) . '-' . Str::random(5);

        $topic = ForumTopic::create([
            'forum_category_id' => $validated['forum_category_id'],
            'user_id' => $request->user('web')->id,
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
        ]);

        return redirect()->route('forum.show', [
            'categorySlug' => $topic->category->slug,
            'topicSlug' => $topic->slug,
        ])->with('success', 'Votre sujet a été publié avec succès.');
    }

    public function reply(Request $request, int $topicId)
    {
        $topic = ForumTopic::with('category')->findOrFail($topicId);

        if ($topic->is_solved || $topic->is_closed) {
            return back()->with('error', 'Ce sujet est résolu ou fermé, aucune nouvelle réponse n\'est permise.');
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|image|max:10240',
        ]);

        $content = $validated['content'];
        if ($request->hasFile('image')) {
            $path = \App\Services\ImageService::saveImage($request->file('image'), 'forum_reply');
            $content .= "\n\n" . '<figure class="my-3 text-center"><img src="/' . $path . '" class="img-fluid rounded-4 shadow-sm" style="max-height: 480px;" /></figure>';
        }

        ForumPost::create([
            'forum_topic_id' => $topic->id,
            'user_id' => $request->user('web')->id,
            'content' => $content,
        ]);

        $topic->touch(); // updates updated_at

        return back()->with('success', 'Votre réponse a été ajoutée.');
    }

    public function toggleSolve(Request $request, int $topicId)
    {
        $topic = ForumTopic::findOrFail($topicId);

        if ($request->user('web')->id !== $topic->user_id) {
            return back()->with('error', 'Seul l\'auteur du sujet peut marquer la discussion comme résolue.');
        }

        $newStatus = !$topic->is_solved;
        $topic->update([
            'is_solved' => $newStatus,
            'is_closed' => $newStatus, // closes replies when solved as requested
        ]);

        $msg = $newStatus ? 'Le sujet a été marqué comme résolu ! Les réponses sont désormais closes.' : 'Le sujet a été réouvert.';
        return back()->with('success', $msg);
    }

    public function toggleLike(Request $request, int $postId)
    {
        $userId = $request->user('web')->id;
        $post = ForumPost::findOrFail($postId);

        $existing = ForumLike::where('forum_post_id', $postId)->where('user_id', $userId)->first();
        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
        } else {
            ForumLike::create([
                'forum_post_id' => $postId,
                'user_id' => $userId,
            ]);
            $post->increment('likes_count');
        }

        return back();
    }

    public function report(Request $request)
    {
        $validated = $request->validate([
            'forum_topic_id' => 'nullable|exists:forum_topics,id',
            'forum_post_id' => 'nullable|exists:forum_posts,id',
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string',
        ]);

        ForumReport::create([
            'reporter_user_id' => $request->user('web')?->id,
            'forum_topic_id' => $validated['forum_topic_id'] ?? null,
            'forum_post_id' => $validated['forum_post_id'] ?? null,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Merci pour votre signalement. Notre équipe de modération va examiner ce message.');
    }

    public function userTopics(int $userId)
    {
        $author = User::withCount(['forumTopics', 'projects', 'startups'])->findOrFail($userId);

        $topics = ForumTopic::with(['category'])
            ->where('user_id', $userId)
            ->withCount('posts')
            ->latest()
            ->paginate(10);

        $posts = ForumPost::with(['topic.category'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10);

        return Inertia::render('Forum/UserTopics', [
            'author' => $author,
            'topics' => $topics,
            'posts' => $posts,
        ]);
    }
}
