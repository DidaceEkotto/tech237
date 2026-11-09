<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\ProjectUpvote;
use App\Models\TechPoll;
use App\Models\TechPollOption;
use App\Models\TechPollVote;
use App\Models\UserBookmark;
use App\Models\UserAlertPreference;
use App\Models\Article;
use App\Models\GameReview;
use App\Models\AppReview;
use App\Models\JobOffer;

class RetentionController extends Controller
{
    /**
     * Toggle upvote on a tech project (Product Hunt style)
     */
    public function toggleUpvote(Request $request, int $projectId)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Veuillez vous connecter pour voter.'], 401);
            }
            return redirect()->route('login')->with('info', 'Connectez-vous pour voter pour ce projet.');
        }

        $project = Project::where('status', 'published')->findOrFail($projectId);

        $existing = ProjectUpvote::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $project->decrement('upvotes_count');
            $upvoted = false;
            $message = 'Votre vote a été retiré.';
        } else {
            ProjectUpvote::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);
            $project->increment('upvotes_count');
            $upvoted = true;
            $message = 'Merci pour votre vote ! 🎉';

            // Gamification: Give 2 XP to the voter, and 5 XP to the project owner!
            $user->addXp(2);
            if ($project->user && $project->user->id !== $user->id) {
                $project->user->addXp(5);
            }
        }

        $project->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'upvoted' => $upvoted,
                'upvotes_count' => $project->upvotes_count,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Vote on the active daily tech poll
     */
    public function votePoll(Request $request, int $pollId)
    {
        $poll = TechPoll::where('is_active', true)->findOrFail($pollId);

        $validated = $request->validate([
            'option_id' => 'required|exists:tech_poll_options,id',
            'guest_token' => 'nullable|string|max:64',
        ]);

        $option = TechPollOption::where('tech_poll_id', $poll->id)->findOrFail($validated['option_id']);

        $user = Auth::guard('web')->user();
        $ip = $request->ip();
        $guestToken = $validated['guest_token'] ?? $request->cookie('tech237_guest_token');

        // Check if already voted
        if ($poll->hasUserVoted($user?->id, $ip, $guestToken)) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => 'Vous avez déjà voté pour ce sondage.'], 422);
            }
            return back()->withErrors(['error' => 'Vous avez déjà voté pour ce sondage.']);
        }

        if (!$guestToken) {
            $guestToken = bin2hex(random_bytes(16));
        }

        $voteData = [
            'tech_poll_id' => $poll->id,
            'tech_poll_option_id' => $option->id,
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'user_agent' => substr((string)$request->userAgent(), 0, 500),
        ];

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('tech_poll_votes', 'guest_token')) {
                $voteData['guest_token'] = $guestToken;
            }
        } catch (\Throwable $e) {}

        TechPollVote::create($voteData);

        $option->increment('votes_count');

        if ($user) {
            $user->addXp(3);
        }

        try {
            session()->put('voted_poll_' . $poll->id, true);
        } catch (\Throwable $e) {}

        $poll->load('options');
        $totalVotes = (int)$poll->options->sum('votes_count');

        $pollFormatted = [
            'id' => $poll->id,
            'question' => $poll->question,
            'description' => $poll->description,
            'total_votes' => $totalVotes,
            'has_voted' => true,
            'options' => $poll->options->map(function ($opt) use ($totalVotes) {
                return [
                    'id' => $opt->id,
                    'label' => $opt->label,
                    'votes_count' => $opt->votes_count,
                    'percentage' => $totalVotes > 0 ? round(($opt->votes_count / $totalVotes) * 100) : 0,
                ];
            }),
        ];

        $responseMessage = 'Votre vote a bien été enregistré !' . ($user ? ' +3 XP gagnés 🎯' : '');

        $cookiePoll = cookie('voted_poll_' . $poll->id, '1', 60 * 24 * 365, null, null, false, false);
        $cookieGuest = cookie('tech237_guest_token', $guestToken, 60 * 24 * 365, null, null, false, false);

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->isJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => $responseMessage,
                'poll' => $pollFormatted,
            ])->withCookie($cookiePoll)->withCookie($cookieGuest);
        }

        return back()->with('success', $responseMessage)->withCookie($cookiePoll)->withCookie($cookieGuest);
    }

    /**
     * Toggle bookmarking an item (Article, Game Review, App Review, Project, Job)
     */
    public function toggleBookmark(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Veuillez vous connecter pour sauvegarder en favoris.'], 401);
            }
            return redirect()->route('login')->with('info', 'Connectez-vous pour ajouter cet élément à vos favoris.');
        }

        $type = $request->input('type') ?? $request->input('item_type');
        $id = $request->input('id') ?? $request->input('item_id');

        $validator = \Illuminate\Support\Facades\Validator::make([
            'type' => $type,
            'id' => $id,
        ], [
            'type' => 'required|in:article,game_review,app_review,project,job',
            'id' => 'required|integer',
        ]);

        $validated = $validator->validate();

        $type = $validated['type'];
        $id = (int)$validated['id'];

        $existing = UserBookmark::where('user_id', $user->id)
            ->where('bookmarkable_type', $type)
            ->where('bookmarkable_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
            $bookmarked = false;
            $message = 'Retiré de vos favoris.';
        } else {
            UserBookmark::create([
                'user_id' => $user->id,
                'bookmarkable_type' => $type,
                'bookmarkable_id' => $id,
            ]);
            $bookmarked = true;
            $message = 'Ajouté à vos favoris avec succès !';
            $user->addXp(1);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'bookmarked' => $bookmarked,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update user alert preferences
     */
    public function updateAlertPreferences(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'alert_jobs' => 'boolean',
            'alert_conferences' => 'boolean',
            'alert_projects' => 'boolean',
            'alert_newsletter' => 'boolean',
        ]);

        UserAlertPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'alert_jobs' => $request->boolean('alert_jobs', true),
                'alert_conferences' => $request->boolean('alert_conferences', true),
                'alert_projects' => $request->boolean('alert_projects', true),
                'alert_newsletter' => $request->boolean('alert_newsletter', true),
            ]
        );

        return back()->with('success', 'Vos préférences de notifications et alertes ont été mises à jour !');
    }

    /**
     * API returning active poll details with percentages
     */
    public function getActivePoll(Request $request)
    {
        $poll = TechPoll::with('options')
            ->where('is_active', true)
            ->latest()
            ->first();

        if (!$poll) {
            return response()->json(['poll' => null]);
        }

        $user = Auth::guard('web')->user();
        $ip = $request->ip();
        $guestToken = $request->input('guest_token') ?? $request->cookie('tech237_guest_token');
        $hasVoted = $poll->hasUserVoted($user?->id, $ip, $guestToken);

        $totalVotes = (int)$poll->options->sum('votes_count');

        $options = $poll->options->map(function ($opt) use ($totalVotes) {
            $percentage = $totalVotes > 0 ? round(($opt->votes_count / $totalVotes) * 100) : 0;
            return [
                'id' => $opt->id,
                'label' => $opt->label,
                'votes_count' => $opt->votes_count,
                'percentage' => $percentage,
            ];
        });

        return response()->json([
            'poll' => [
                'id' => $poll->id,
                'question' => $poll->question,
                'description' => $poll->description,
                'total_votes' => $totalVotes,
                'has_voted' => $hasVoted,
                'options' => $options,
            ]
        ]);
    }
}
