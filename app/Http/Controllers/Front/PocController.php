<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Poc;
use App\Models\PocUpvote;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PocController extends Controller
{
    /**
     * Display listing of POCs (Mini actualités communautaires)
     */
    public function index(Request $request)
    {
        $tag = $request->query('tag');
        $search = $request->query('q');
        $sort = $request->query('sort', 'recent'); // recent, popular

        $query = Poc::published()->with(['user']);

        if ($tag && $tag !== 'all') {
            $query->where('tag', $tag);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($sort === 'popular') {
            $query->orderByDesc('upvotes_count')->latest('published_at');
        } else {
            $query->latest('published_at')->latest('id');
        }

        $pocs = $query->paginate(12)->withQueryString();

        $userId = Auth::id();
        $ip = $request->ip();
        $guestToken = $request->cookie('tech237_guest_token') ?: $request->input('guest_token');

        // Append has_upvoted flag for each POC
        $pocs->getCollection()->transform(function ($poc) use ($userId, $guestToken, $ip) {
            $poc->has_upvoted = $poc->hasUserUpvoted($userId, $guestToken, $ip);
            return $poc;
        });

        // Top trending / popular POCs for sidebar
        $popularPocs = Poc::published()
            ->with('user')
            ->orderByDesc('upvotes_count')
            ->take(5)
            ->get();

        // Stats summary
        $totalPocs = Poc::published()->count();

        return Inertia::render('Pocs/Index', [
            'pocs' => $pocs,
            'popularPocs' => $popularPocs,
            'currentTag' => $tag ?: 'all',
            'currentSort' => $sort,
            'searchQuery' => $search ?: '',
            'totalPocs' => $totalPocs,
        ]);
    }

    /**
     * Show form to propose a new POC
     */
    public function create()
    {
        return Inertia::render('Pocs/Create');
    }

    /**
     * Store newly created POC
     * User requested: "quand on fais un poc par défaut il apparait mais on peut lui supprimer après via l'espace admin"
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:5|max:200',
            'content' => 'required|string|min:10',
            'tag' => 'nullable|string|max:50',
            'link_url' => 'nullable|url|max:500',
            'cover_image' => 'nullable|image|max:4096', // Max 4MB
            'author_name' => 'nullable|string|max:100',
        ]);

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = 'poc_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/images'), $filename);
            $coverImagePath = 'storage/images/' . $filename;
        }

        $user = Auth::user();

        // Plain text summary for card and SEO
        $plainText = trim(strip_tags($validated['content']));
        $summary = Str::limit($plainText, 180);

        $poc = Poc::create([
            'user_id' => $user?->id,
            'author_name' => $user ? ($user->first_name ? $user->first_name . ' ' . $user->name : $user->name) : ($validated['author_name'] ?? 'Contributeur Tech'),
            'title' => $validated['title'],
            'slug' => Poc::generateUniqueSlug($validated['title']),
            'summary' => $summary,
            'content' => $validated['content'],
            'cover_image' => $coverImagePath,
            'link_url' => $validated['link_url'] ?? null,
            'tag' => $validated['tag'] ?: 'general',
            'status' => 'published', // Published by default as requested
            'published_at' => now(),
            'upvotes_count' => 0,
            'views_count' => 0,
        ]);

        // Gamification: Give +5 XP to the author if logged in
        if ($user) {
            $user->addXp(5);
        }

        return redirect()->route('pocs.show', $poc->slug ?: $poc->id)
            ->with('success', 'Votre mini-actualité (POC) a été publiée avec succès ! 🎉' . ($user ? ' +5 XP gagnés' : ''));
    }

    /**
     * Display a single POC with rendered WYSIWYG content
     */
    public function show(Request $request, string $slug)
    {
        $poc = Poc::where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? (int)$slug : 0)
            ->with(['user', 'comments.user', 'comments.replies.user'])
            ->firstOrFail();

        // Only allow published POCs unless admin is viewing
        if ($poc->status !== 'published') {
            $admin = Auth::guard('admin')->user();
            if (!$admin && $poc->user_id !== Auth::id()) {
                abort(404);
            }
        }

        \App\Services\UniqueViewService::recordView($poc, $request);

        $userId = Auth::id();
        $ip = $request->ip();
        $guestToken = $request->cookie('tech237_guest_token') ?: $request->input('guest_token');
        $poc->has_upvoted = $poc->hasUserUpvoted($userId, $guestToken, $ip);

        // Related POCs
        $relatedPocs = Poc::published()
            ->where('id', '!=', $poc->id)
            ->where('tag', $poc->tag)
            ->latest('published_at')
            ->take(4)
            ->get();

        return Inertia::render('Pocs/Show', [
            'poc' => $poc,
            'relatedPocs' => $relatedPocs,
        ]);
    }

    /**
     * Toggle upvote on a POC (anti-duplicate)
     */
    public function toggleUpvote(Request $request, int $id)
    {
        $poc = Poc::published()->findOrFail($id);

        $userId = Auth::id();
        $ip = $request->ip();
        $guestToken = $request->input('guest_token') ?: $request->cookie('tech237_guest_token');

        if (!$guestToken) {
            $guestToken = bin2hex(random_bytes(16));
        }

        $query = PocUpvote::where('poc_id', $poc->id);
        if ($userId) {
            $existing = (clone $query)->where('user_id', $userId)->first();
        } else {
            $existing = (clone $query)->where('guest_token', $guestToken)->first();
            if (!$existing && $ip && !in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
                $existing = (clone $query)->whereNull('user_id')->where('ip_address', $ip)->first();
            }
        }

        if ($existing) {
            $existing->delete();
            $poc->decrement('upvotes_count');
            $upvoted = false;
            $msg = 'Vote retiré.';
        } else {
            PocUpvote::create([
                'poc_id' => $poc->id,
                'user_id' => $userId,
                'guest_token' => $guestToken,
                'ip_address' => $ip,
            ]);
            $poc->increment('upvotes_count');
            $upvoted = true;
            $msg = 'Merci pour votre vote ! 🔥';

            if ($userId) {
                Auth::user()->addXp(2);
            }
            if ($poc->user && $poc->user_id !== $userId) {
                $poc->user->addXp(3);
            }
        }

        $poc->refresh();

        $cookieGuest = cookie('tech237_guest_token', $guestToken, 60 * 24 * 365, null, null, false, false);

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'upvoted' => $upvoted,
                'upvotes_count' => $poc->upvotes_count,
                'message' => $msg,
            ])->withCookie($cookieGuest);
        }

        return back()->with('success', $msg)->withCookie($cookieGuest);
    }

    /**
     * Add comment to a POC (Must have an active user account on site)
     */
    public function storeComment(Request $request, int $id)
    {
        $poc = Poc::published()->findOrFail($id);

        $user = Auth::user();
        if (!$user) {
            return back()->with('error', 'Vous devez avoir un compte actif sur le site pour commenter ce POC.');
        }

        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            return back()->with('error', 'Votre compte est restreint. Vous ne pouvez pas publier de commentaire.');
        }

        if (isset($user->is_active) && !$user->is_active) {
            return back()->with('error', 'Votre compte est inactif ou en attente d\'activation.');
        }

        $validated = $request->validate([
            'content' => 'required|string|min:3|max:1500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        Comment::create([
            'poc_id' => $poc->id,
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'status' => 'approved',
        ]);

        $user->addXp(3); // +3 XP reward for community engagement

        return back()->with('success', 'Votre commentaire a bien été publié ! (+3 XP) 🎉');
    }
}
