<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\XpLevel;
use App\Models\Poc;
use App\Models\Project;
use App\Models\ForumTopic;
use App\Models\UserBadge;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class CommunityController extends Controller
{
    /**
     * Candy Crush-Style Game Roadmap of Winners / Champions
     */
    public function map(Request $request)
    {
        $levels = XpLevel::getAllTiers();

        // Get ranked active users
        $users = User::where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'banned');
            })
            ->orderBy('xp', 'desc')
            ->select('id', 'name', 'avatar', 'xp', 'rank_title', 'created_at')
            ->get();

        // Group users by their matching level tier
        $levelsWithPlayers = $levels->map(function ($lvl) use ($users) {
            $matchingPlayers = $users->filter(function ($u) use ($lvl) {
                $uTier = XpLevel::getLevelForXp($u->xp ?? 0);
                return $uTier && (int)$uTier->min_xp === (int)$lvl->min_xp;
            })->values();

            $lvlObj = is_array($lvl) ? (object)$lvl : $lvl;
            $lvlObj->players = $matchingPlayers;
            $lvlObj->players_count = $matchingPlayers->count();
            return $lvlObj;
        });

        $currentUser = Auth::user();
        $currentUserRank = null;
        if ($currentUser) {
            $currentUserPosition = $users->search(fn($u) => $u->id === $currentUser->id);
            $currentUserRank = [
                'position' => $currentUserPosition !== false ? ($currentUserPosition + 1) : count($users),
                'xp' => $currentUser->xp ?? 0,
                'tier' => XpLevel::getLevelForXp($currentUser->xp ?? 0),
                'next_tier' => XpLevel::getNextLevelForXp($currentUser->xp ?? 0),
            ];
        }

        $totalXp = $users->sum('xp');

        return Inertia::render('Gamification/Map', [
            'levels' => $levelsWithPlayers,
            'currentUserRank' => $currentUserRank,
            'totalXp' => $totalXp,
            'totalPlayers' => $users->count(),
            'topThree' => $users->take(3),
            'allUsers' => $users,
        ]);
    }

    /**
     * Public Community Member Profile / Wall
     */
    public function profile(Request $request, $id)
    {
        $user = User::with(['badges'])->findOrFail($id);

        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            abort(404, 'Ce compte utilisateur n\'est plus disponible.');
        }

        // Calculate global rank position
        $rankPosition = User::where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'banned');
            })
            ->where('xp', '>', $user->xp ?? 0)
            ->count() + 1;

        // Current level tier and next tier
        $currentTier = XpLevel::getLevelForXp($user->xp ?? 0);
        $nextTier = XpLevel::getNextLevelForXp($user->xp ?? 0);

        // Public Projects
        $projects = Project::where('user_id', $user->id)
            ->where('status', 'published')
            ->latest()
            ->take(8)
            ->get();

        // Public POCs (Mini-actus)
        $pocs = Poc::where('user_id', $user->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->take(8)
            ->get();

        // Public Forum Contributions
        $forumTopics = ForumTopic::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        $stats = [
            'total_xp' => $user->xp ?? 0,
            'rank_position' => $rankPosition,
            'projects_count' => $projects->count(),
            'pocs_count' => $pocs->count(),
            'badges_count' => $user->badges->count(),
            'forum_count' => $forumTopics->count(),
        ];

        return Inertia::render('User/PublicProfile', [
            'profileUser' => $user,
            'currentTier' => $currentTier,
            'nextTier' => $nextTier,
            'stats' => $stats,
            'projects' => $projects,
            'pocs' => $pocs,
            'forumTopics' => $forumTopics,
            'badges' => $user->badges,
        ]);
    }

    /**
     * Send direct contact email to a community member
     */
    public function sendContactEmail(Request $request, $id)
    {
        $recipient = User::findOrFail($id);

        if (empty($recipient->email)) {
            return response()->json([
                'error' => 'Ce membre ne dispose pas d\'une adresse email valide pour recevoir des messages.',
            ], 422);
        }

        $validated = $request->validate([
            'sender_name' => 'required|string|max:120',
            'sender_email' => 'required|email|max:180',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:10|max:5000',
        ], [
            'sender_name.required' => 'Votre nom et prénom sont obligatoires.',
            'sender_email.required' => 'Votre adresse email est obligatoire.',
            'sender_email.email' => 'Veuillez saisir une adresse email valide.',
            'subject.required' => 'Le sujet de votre message est obligatoire.',
            'message.required' => 'Le contenu de votre message est obligatoire.',
            'message.min' => 'Votre message doit contenir au moins 10 caractères.',
        ]);

        try {
            $siteSetting = SiteSetting::current();
            $siteName = $siteSetting->site_name ?? 'Tech237';
            $siteLogo = $siteSetting->logo ? (str_starts_with($siteSetting->logo, 'http') ? $siteSetting->logo : asset($siteSetting->logo)) : asset('assets/images/logo.png');

            $data = [
                'recipient' => $recipient,
                'sender_name' => $validated['sender_name'],
                'sender_email' => $validated['sender_email'],
                'subject' => $validated['subject'],
                'message_content' => $validated['message'],
                'site_name' => $siteName,
                'site_logo' => $siteLogo,
            ];

            Mail::send('emails.community_profile_contact', $data, function ($message) use ($recipient, $validated, $siteName) {
                $message->to($recipient->email, $recipient->name)
                    ->replyTo($validated['sender_email'], $validated['sender_name'])
                    ->subject("[{$siteName}] Nouveau message de {$validated['sender_name']} : {$validated['subject']}");
            });

            return response()->json([
                'success' => true,
                'message' => 'Votre email a été envoyé avec succès à ' . $recipient->name . ' !',
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'envoi du message au membre ' . $recipient->id . ': ' . $e->getMessage());
            return response()->json([
                'error' => 'Impossible d\'envoyer le message pour le moment. Veuillez réessayer plus tard.',
            ], 500);
        }
    }
}
