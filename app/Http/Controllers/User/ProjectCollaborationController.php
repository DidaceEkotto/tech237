<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectInvitation;
use App\Models\ProjectCollaborator;
use App\Models\CollaborationBlock;
use App\Models\ProjectTimeline;
use App\Models\ProjectImage;
use App\Models\UserMessage;

class ProjectCollaborationController extends Controller
{
    /**
     * Dedicated page to manage project collaborators and invitations
     */
    public function manageCollaborators(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $project = Project::with([
            'startup',
            'activeCollaborators.user',
            'invitations' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        if ((int)$project->user_id !== (int)$user->id) {
            abort(403, "Vous n'êtes pas autorisé à gérer les collaborateurs de ce projet.");
        }

        // Auto-update expired invitations if needed
        foreach ($project->invitations as $inv) {
            if ($inv->status === 'pending' && $inv->isExpired()) {
                $inv->update(['status' => 'expired']);
            }
        }

        return Inertia::render('User/Projects/Collaborators', [
            'project' => $project,
            'collaborators' => $project->activeCollaborators,
            'invitations' => $project->invitations,
        ]);
    }

    /**
     * Search registered users for email autocompletion
     */
    public function searchUsers(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([], 401);
        }

        $query = trim((string)$request->input('q', ''));
        if (mb_strlen($query) < 1) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%")
                  ->orWhere('first_name', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'first_name', 'email', 'avatar')
            ->limit(10)
            ->get();

        $results = $users->filter(function ($u) use ($user) {
            return !CollaborationBlock::isBlocked($user->id, $u->id, $u->email);
        })->map(function ($u) {
            $avatar = $u->avatar;
            if ($avatar && !str_starts_with($avatar, 'http') && !str_starts_with($avatar, '/')) {
                $avatar = '/' . $avatar;
            }
            return [
                'id' => $u->id,
                'name' => trim(($u->first_name ?? '') . ' ' . ($u->name ?? '')),
                'email' => $u->email,
                'avatar' => $avatar,
            ];
        })->values();

        return response()->json($results);
    }

    /**
     * Send a collaboration invitation to an email address (48h validity)
     */
    public function invite(Request $request, int $projectId)
    {
        $user = Auth::guard('web')->user();
        if ($user->isBanned()) {
            return back()->withErrors(['error' => 'Votre compte est restreint.']);
        }

        $project = Project::where('user_id', $user->id)->findOrFail($projectId);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'nullable|string|max:80',
            'message' => 'nullable|string|max:1000',
        ], [
            'email.required' => "L'adresse email du collaborateur est obligatoire.",
            'email.email' => "Veuillez saisir une adresse email valide.",
        ]);

        $email = strtolower(trim($validated['email']));

        // Cannot invite self
        if ($email === strtolower(trim($user->email))) {
            return back()->withErrors(['email' => "Vous ne pouvez pas vous inviter vous-même."]);
        }

        // Check if user exists
        $targetUser = User::where('email', $email)->first();

        // Check if blocked in either direction
        if (CollaborationBlock::isBlocked($user->id, $targetUser?->id, $email)) {
            return back()->withErrors([
                'email' => "Impossible d'envoyer l'invitation : un blocage de collaboration existe entre vous et cet utilisateur."
            ]);
        }

        // Check if already an active collaborator
        if ($targetUser && $project->activeCollaborators()->where('user_id', $targetUser->id)->exists()) {
            return back()->withErrors([
                'email' => "Cette personne est déjà collaboratrice active sur ce projet."
            ]);
        }

        // Check for existing pending invitation within 48h
        $existingInvitation = ProjectInvitation::where('project_id', $project->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            // Update token & extend 48h
            $existingInvitation->update([
                'token' => bin2hex(random_bytes(32)),
                'role' => $validated['role'] ?: 'Contributeur & Dev',
                'message' => $validated['message'] ?: null,
                'expires_at' => now()->addHours(48),
                'invited_user_id' => $targetUser?->id,
            ]);
            $invitation = $existingInvitation;
        } else {
            // Create brand new invitation
            $invitation = ProjectInvitation::create([
                'project_id' => $project->id,
                'inviter_id' => $user->id,
                'invited_user_id' => $targetUser?->id,
                'email' => $email,
                'token' => bin2hex(random_bytes(32)),
                'role' => $validated['role'] ?: 'Contributeur & Dev',
                'message' => $validated['message'] ?: null,
                'status' => 'pending',
                'expires_at' => now()->addHours(48),
            ]);
        }

        $inviteUrl = route('invitations.show', ['token' => $invitation->token]);

        // Send Email with project info and logo
        try {
            Mail::send('emails.project_invitation', [
                'project' => $project,
                'inviter' => $user,
                'invitation' => $invitation,
                'inviteUrl' => $inviteUrl,
            ], function ($msg) use ($email, $project) {
                $msg->to($email)
                    ->subject("Invitation à collaborer sur le projet {$project->name} (Tech237)");
            });
        } catch (\Throwable $e) {
            Log::error("Erreur envoi email invitation projet: " . $e->getMessage());
        }

        // If target user is already registered, send in-app message
        if ($targetUser) {
            try {
                UserMessage::create([
                    'user_id' => $targetUser->id,
                    'sender_id' => $user->id,
                    'subject' => "🤝 Invitation à collaborer sur {$project->name}",
                    'message' => "Bonjour {$targetUser->name}, {$user->name} vous invite à collaborer sur le projet \"{$project->name}\". Vous pouvez accepter ou refuser l'invitation depuis votre onglet Projets ou via l'email reçu. L'invitation est valable pendant 48 heures.",
                ]);
            } catch (\Throwable $e) {
                Log::error("Erreur création notification message invitation: " . $e->getMessage());
            }
        }

        return back()->with('success', "Invitation envoyée avec succès à {$email} ! Le lien est valable pendant 48 heures.");
    }

    /**
     * Public page to view an invitation by token
     */
    public function showInvitation(string $token)
    {
        $invitation = ProjectInvitation::with(['project.startup', 'inviter', 'project.activeCollaborators.user'])
            ->where('token', $token)
            ->firstOrFail();

        // Check expiration
        $isExpired = $invitation->isExpired();
        if ($isExpired && $invitation->status === 'pending') {
            $invitation->update(['status' => 'expired']);
        }

        $authUser = Auth::guard('web')->user();

        // Check if current user is blocked
        $isBlocked = false;
        if ($authUser) {
            $isBlocked = CollaborationBlock::isBlocked($authUser->id, $invitation->inviter_id, $authUser->email);
        }

        return Inertia::render('Projects/Invitation', [
            'invitation' => $invitation,
            'project' => $invitation->project,
            'inviter' => $invitation->inviter,
            'isExpired' => $isExpired,
            'isBlocked' => $isBlocked,
            'authUser' => $authUser,
        ]);
    }

    /**
     * Accept the collaboration invitation
     */
    public function acceptInvitation(Request $request, string $token)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login')->with('info', "Veuillez vous connecter ou créer un compte pour accepter l'invitation.");
        }

        $invitation = ProjectInvitation::with('project.user', 'inviter')->where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            return back()->withErrors(['error' => "Cette invitation a expiré (validité 48 heures dépassée)."]);
        }

        if ($invitation->status !== 'pending') {
            return back()->withErrors(['error' => "Cette invitation n'est plus active (déjà acceptée ou refusée)."]);
        }

        // Check block
        if (CollaborationBlock::isBlocked($user->id, $invitation->inviter_id, $user->email)) {
            return back()->withErrors(['error' => "Action impossible en raison d'un blocage de collaboration."]);
        }

        // Accept invitation
        $invitation->update([
            'status' => 'accepted',
            'invited_user_id' => $user->id,
            'accepted_at' => now(),
        ]);

        // Add or reactivate collaborator (keeps previous history if they were re-invited!)
        $collaborator = ProjectCollaborator::updateOrCreate(
            [
                'project_id' => $invitation->project_id,
                'user_id' => $user->id,
            ],
            [
                'invitation_id' => $invitation->id,
                'role' => $invitation->role ?: 'Contributeur & Dev',
                'status' => 'active',
                'joined_at' => now(),
                'revoked_at' => null,
            ]
        );

        $project = $invitation->project;
        $owner = $invitation->inviter;

        // Send Email to project owner
        try {
            Mail::send('emails.project_collaboration_accepted', [
                'project' => $project,
                'collaborator' => $user,
            ], function ($msg) use ($owner, $project, $user) {
                $msg->to($owner->email)
                    ->subject("🎉 {$user->name} a rejoint votre projet {$project->name} (Tech237)");
            });
        } catch (\Throwable $e) {
            Log::error("Erreur envoi email acceptation collaboration: " . $e->getMessage());
        }

        // Send in-app notification to project owner
        try {
            UserMessage::create([
                'user_id' => $owner->id,
                'sender_id' => $user->id,
                'subject' => "🤝 Nouveau collaborateur sur {$project->name}",
                'message' => "Bonne nouvelle ! {$user->name} ({$user->email}) a accepté votre invitation et collabore désormais sur votre projet {$project->name}. Vous pourrez valider ses propositions d'étapes Devlogs et d'images.",
            ]);
        } catch (\Throwable $e) {
            Log::error("Erreur création message acceptation: " . $e->getMessage());
        }

        return redirect()->route('user.dashboard', ['section' => 'projects'])->with('success', "Félicitations ! Vous collaborez désormais sur le projet {$project->name}.");
    }

    /**
     * Decline an invitation (the owner may invite them again later if needed)
     */
    public function declineInvitation(Request $request, string $token)
    {
        $invitation = ProjectInvitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending' || $invitation->isExpired()) {
            return back()->withErrors(['error' => "Cette invitation n'est plus active."]);
        }

        $user = Auth::guard('web')->user();

        $invitation->update([
            'status' => 'declined',
            'invited_user_id' => $user?->id ?? $invitation->invited_user_id,
            'declined_at' => now(),
        ]);

        return redirect()->route('home')->with('info', "Vous avez décliné l'invitation à collaborer.");
    }

    /**
     * Block all future collaboration: "Je ne souhaite jamais collaborer avec vous"
     * Neither party can invite the other anymore.
     */
    public function blockCollaboration(Request $request, string $token)
    {
        $invitation = ProjectInvitation::with('inviter')->where('token', $token)->firstOrFail();
        $user = Auth::guard('web')->user();

        // Mark invitation as declined
        $invitation->update([
            'status' => 'declined',
            'invited_user_id' => $user?->id ?? $invitation->invited_user_id,
            'declined_at' => now(),
        ]);

        if ($user) {
            // Block bidirectional
            CollaborationBlock::firstOrCreate([
                'blocker_id' => $user->id,
                'blocked_id' => $invitation->inviter_id,
            ], [
                'blocked_email' => $invitation->inviter->email,
                'reason' => 'Refus définitif de collaboration',
            ]);
        } else {
            // Block by email for guest
            if ($invitation->invited_user_id) {
                CollaborationBlock::firstOrCreate([
                    'blocker_id' => $invitation->invited_user_id,
                    'blocked_id' => $invitation->inviter_id,
                ], [
                    'blocked_email' => $invitation->inviter->email,
                    'reason' => 'Refus définitif de collaboration',
                ]);
            }
        }

        return redirect()->route('home')->with('info', "Vous avez bloqué toute future demande de collaboration avec ce créateur.");
    }

    /**
     * Project owner cancels/revokes collaboration of a collaborator
     */
    public function revokeCollaborator(Request $request, int $projectId, int $collaboratorId)
    {
        $user = Auth::guard('web')->user();
        $project = Project::where('user_id', $user->id)->findOrFail($projectId);

        $collab = ProjectCollaborator::where('project_id', $project->id)
            ->where('id', $collaboratorId)
            ->firstOrFail();

        $collabUser = $collab->user;

        $collab->update([
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);

        // Send in-app notice to the collaborator
        try {
            UserMessage::create([
                'user_id' => $collab->user_id,
                'sender_id' => $user->id,
                'subject' => "Fin de collaboration sur {$project->name}",
                'message' => "Votre statut de collaborateur sur le projet {$project->name} a été révoqué par le créateur. Vos contributions historiques validées restent conservées sur le projet.",
            ]);
        } catch (\Throwable $e) {
            Log::error("Erreur message révocation: " . $e->getMessage());
        }

        return back()->with('success', "La collaboration avec {$collabUser->name} a été annulée. Le projet a été retiré de son espace.");
    }

    /**
     * Project owner validates or rejects a collaborator's Devlog timeline entry
     */
    public function validateTimeline(Request $request, int $timelineId)
    {
        $user = Auth::guard('web')->user();
        $timeline = ProjectTimeline::with('project')->findOrFail($timelineId);

        // Ensure current user is the project owner
        if ((int)$timeline->project->user_id !== (int)$user->id) {
            return back()->withErrors(['error' => "Action non autorisée. Vous n'êtes pas le propriétaire de ce projet."]);
        }

        $action = $request->input('action'); // 'approve' or 'reject'

        if ($action === 'approve') {
            $timeline->update(['status' => 'approved']);
            \App\Services\BookmarkNotificationService::notifyProjectDevlog($timeline->project, $timeline);
            return back()->with('success', "L'étape du Devlog a été validée et est désormais visible publiquement !");
        } else {
            $timeline->update(['status' => 'rejected']);
            return back()->with('info', "L'étape du Devlog a été rejetée.");
        }
    }

    /**
     * Project owner validates or rejects a collaborator's uploaded gallery image
     */
    public function validateImage(Request $request, int $imageId)
    {
        $user = Auth::guard('web')->user();
        $image = ProjectImage::with('project')->findOrFail($imageId);

        // Ensure current user is the project owner
        if ((int)$image->project->user_id !== (int)$user->id) {
            return back()->withErrors(['error' => "Action non autorisée. Vous n'êtes pas le propriétaire de ce projet."]);
        }

        $action = $request->input('action'); // 'approve' or 'reject'

        if ($action === 'approve') {
            $image->update(['status' => 'approved']);
            return back()->with('success', "L'image a été validée et apparaît désormais dans la galerie publique !");
        } else {
            $image->update(['status' => 'rejected']);
            return back()->with('info', "L'image a été rejetée.");
        }
    }
}
