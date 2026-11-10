<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KanbanBoard;
use App\Models\KanbanBoardMember;
use App\Models\KanbanCard;
use App\Models\KanbanCardComment;
use App\Models\KanbanCardActivity;
use App\Models\KanbanColumn;
use App\Models\Project;
use App\Models\ProjectCollaborator;
use App\Models\ProjectTimeline;
use App\Models\User;
use App\Services\BookmarkNotificationService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class KanbanController extends Controller
{
    /**
     * List user boards (own boards + project boards where user is collaborator)
     */
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();

        // Project IDs where user is active collaborator
        $collabProjectIds = ProjectCollaborator::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('project_id');

        // Own projects
        $myProjects = Project::where('user_id', $user->id)
            ->select('id', 'name', 'slug', 'logo')
            ->get();

        $directBoardIds = KanbanBoardMember::where('user_id', $user->id)->pluck('board_id');

        // Boards list
        $boards = KanbanBoard::with(['project', 'columns.cards', 'members.user'])
            ->where(function ($q) use ($user, $collabProjectIds, $directBoardIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('project_id', $collabProjectIds)
                  ->orWhereIn('id', $directBoardIds);
            })
            ->latest('updated_at')
            ->get()
            ->map(function ($b) {
                $totalCards = 0;
                $doneCards = 0;
                foreach ($b->columns as $col) {
                    $cnt = $col->cards->count();
                    $totalCards += $cnt;
                    if ($col->is_done_column || str_contains(strtolower($col->title), 'termin') || str_contains(strtolower($col->title), 'done')) {
                        $doneCards += $cnt;
                    }
                }
                $b->total_cards = $totalCards;
                $b->done_cards = $doneCards;
                $b->progress = $totalCards > 0 ? round(($doneCards / $totalCards) * 100) : 0;
                return $b;
            });

        // Trigger automated overdue alert check in background
        try {
            \Illuminate\Support\Facades\Artisan::call('kanban:check-overdue');
        } catch (\Throwable $e) {}

        $registeredUsers = User::where('id', '!=', $user->id)
            ->select('id', 'name', 'first_name', 'email', 'avatar')
            ->take(100)
            ->get();

        return Inertia::render('User/Kanban/Index', [
            'boards' => $boards,
            'myProjects' => $myProjects,
            'registeredUsers' => $registeredUsers,
        ]);
    }

    /**
     * Display Kanban Guide / Onboarding Tutorial
     */
    public function guide()
    {
        return Inertia::render('User/Kanban/Guide');
    }

    /**
     * Shortcut for a specific project: opens or auto-generates project board
     */
    public function forProject(Request $request, int $projectId)
    {
        $user = Auth::guard('web')->user();
        $project = Project::findOrFail($projectId);

        // Security check
        $isOwner = $project->isOwner($user);
        $isCollab = $project->isCollaborator($user);

        if (!$isOwner && !$isCollab) {
            return redirect()->route('user.dashboard')->withErrors(['error' => "Accès non autorisé à ce projet."]);
        }

        // Find existing board or create one
        $board = KanbanBoard::where('project_id', $project->id)->first();

        if (!$board) {
            $board = KanbanBoard::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'title' => "Tableau & Roadmap • {$project->name}",
                'description' => "Suivi des sprints, tâches et développements pour le projet {$project->name}.",
                'is_public' => false,
            ]);

            // Create default columns
            $this->createDefaultColumns($board->id);
        }

        return redirect()->route('user.kanban.show', $board->id);
    }

    /**
     * Show a single board with full interactive Drag & Drop data
     */
    public function show(Request $request, int $boardId)
    {
        $user = Auth::guard('web')->user();

        $board = KanbanBoard::with([
            'project',
            'members.user:id,name,first_name,avatar,email',
            'activities.user:id,name,first_name,avatar',
            'columns' => function ($q) {
                $q->orderBy('order');
            },
            'columns.cards' => function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('is_archived')->orWhere('is_archived', false);
                })->with([
                    'assignee:id,name,first_name,avatar',
                    'comments.user:id,name,first_name,avatar',
                    'activities.user:id,name,first_name,avatar',
                ])->orderBy('order');
            },
            'user:id,name,first_name,avatar',
        ])->findOrFail($boardId);

        // Security check
        if (!$board->canAccess($user)) {
            abort(403, "Vous n'avez pas l'autorisation d'accéder à ce tableau.");
        }

        $canEdit = $board->canEdit($user);

        // Compute eligible team members for card assignment
        $teamMembers = collect();
        if ($user) {
            $teamMembers->push([
                'id' => $user->id,
                'name' => $user->first_name ? $user->first_name . ' ' . $user->name : $user->name,
                'avatar' => $user->avatar,
            ]);
        }

        // Direct board members
        foreach ($board->members as $m) {
            if ($m->user && !$teamMembers->contains('id', $m->user->id)) {
                $teamMembers->push([
                    'id' => $m->user->id,
                    'name' => $m->user->first_name ? $m->user->first_name . ' ' . $m->user->name : $m->user->name,
                    'avatar' => $m->user->avatar,
                ]);
            }
        }

        if ($board->project_id && $board->project) {
            // Project owner
            if ($board->project->user_id !== $user?->id) {
                $projOwner = User::find($board->project->user_id);
                if ($projOwner && !$teamMembers->contains('id', $projOwner->id)) {
                    $teamMembers->push([
                        'id' => $projOwner->id,
                        'name' => $projOwner->first_name ? $projOwner->first_name . ' ' . $projOwner->name : $projOwner->name,
                        'avatar' => $projOwner->avatar,
                    ]);
                }
            }

            // Project collaborators
            $collabs = ProjectCollaborator::with('user')
                ->where('project_id', $board->project_id)
                ->where('status', 'active')
                ->get();

            foreach ($collabs as $c) {
                if ($c->user && !$teamMembers->contains('id', $c->user->id)) {
                    $teamMembers->push([
                        'id' => $c->user->id,
                        'name' => $c->user->first_name ? $c->user->first_name . ' ' . $c->user->name : $c->user->name,
                        'avatar' => $c->user->avatar,
                    ]);
                }
            }
        }

        $registeredUsers = User::where('id', '!=', $user->id)
            ->select('id', 'name', 'first_name', 'email', 'avatar')
            ->take(100)
            ->get();

        $archivedCards = KanbanCard::where('board_id', $board->id)
            ->where('is_archived', true)
            ->with([
                'assignee:id,name,first_name,avatar',
                'column:id,title,color',
                'comments.user:id,name,first_name,avatar',
                'activities.user:id,name,first_name,avatar',
            ])
            ->latest('archived_at')
            ->get();

        return Inertia::render('User/Kanban/Show', [
            'board' => $board,
            'canEdit' => $canEdit,
            'teamMembers' => $teamMembers->values(),
            'registeredUsers' => $registeredUsers,
            'archivedCards' => $archivedCards,
        ]);
    }

    /**
     * Store a new board
     */
    public function storeBoard(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'project_id' => 'nullable|exists:projects,id',
            'is_public' => 'boolean',
        ]);

        if (!empty($validated['project_id'])) {
            $project = Project::findOrFail($validated['project_id']);
            $isOwner = $project->isOwner($user);
            $isCollab = $project->isCollaborator($user);

            if (!$isOwner && !$isCollab) {
                return back()->withErrors(['error' => "Action non autorisée pour ce projet."]);
            }
        }

        $board = KanbanBoard::create([
            'user_id' => $user->id,
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public' => $request->boolean('is_public', false),
        ]);

        $this->createDefaultColumns($board->id);

        // Add contributors if selected during creation
        if ($request->has('contributor_ids') && is_array($request->input('contributor_ids'))) {
            foreach ($request->input('contributor_ids') as $cid) {
                if ($cid && (int)$cid !== (int)$user->id) {
                    KanbanBoardMember::firstOrCreate([
                        'board_id' => $board->id,
                        'user_id' => (int)$cid,
                    ], ['role' => 'member']);
                }
            }
        }

        // Log initial activity
        KanbanCardActivity::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'type' => 'board_created',
            'description' => "Tableau créé par {$user->name}",
        ]);

        return redirect()->route('user.kanban.show', $board->id)->with('success', "Tableau Kanban « {$board->title} » créé avec succès !");
    }

    /**
     * Add a collaborator/contributor to the board
     */
    public function addMember(Request $request, int $boardId)
    {
        $user = Auth::guard('web')->user();
        $board = KanbanBoard::findOrFail($boardId);

        if (!$board->canEdit($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string|in:member,admin,viewer',
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        if ((int)$targetUser->id === (int)$board->user_id) {
            return back()->withErrors(['error' => "Le propriétaire du tableau est déjà membre d'office."]);
        }

        KanbanBoardMember::firstOrCreate([
            'board_id' => $board->id,
            'user_id' => $targetUser->id,
        ], [
            'role' => $validated['role'] ?? 'member',
        ]);

        KanbanCardActivity::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'type' => 'member_added',
            'description' => "{$targetUser->name} a été ajouté(e) aux contributeurs du tableau.",
        ]);

        return back()->with('success', "{$targetUser->name} a été ajouté(e) avec succès aux contributeurs du tableau !");
    }

    /**
     * Remove a collaborator from the board
     */
    public function removeMember(Request $request, int $boardId, int $userId)
    {
        $user = Auth::guard('web')->user();
        $board = KanbanBoard::findOrFail($boardId);

        if (!$board->canEdit($user)) {
            abort(403);
        }

        $targetUser = User::findOrFail($userId);

        KanbanBoardMember::where('board_id', $board->id)
            ->where('user_id', $targetUser->id)
            ->delete();

        KanbanCardActivity::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'type' => 'member_removed',
            'description' => "{$targetUser->name} a été retiré(e) des contributeurs du tableau.",
        ]);

        return back()->with('success', "{$targetUser->name} a été retiré(e) des contributeurs du tableau.");
    }

    /**
     * Upload an image attachment to a card (images only)
     */
    public function uploadCardAttachment(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with('board')->findOrFail($cardId);

        if (!$card->board->canEdit($user)) {
            abort(403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ], [
            'image.required' => 'Veuillez sélectionner une image.',
            'image.image' => 'Seules les images sont autorisées comme pièces jointes.',
            'image.mimes' => 'Formats autorisés : JPG, PNG, WEBP, GIF.',
            'image.max' => 'L\'image ne doit pas dépasser 10 Mo.',
        ]);

        $file = $request->file('image');
        $path = ImageService::saveImage($file, 'kanban_attachments');

        $attachments = $card->attachments ?: [];
        $newAttachment = [
            'id' => (string) Str::uuid(),
            'url' => $path,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'uploaded_at' => now()->toIso8601String(),
            'uploader_name' => $user->name,
            'uploader_id' => $user->id,
        ];
        $attachments[] = $newAttachment;

        $card->update(['attachments' => $attachments]);

        KanbanCardActivity::create([
            'board_id' => $card->board_id,
            'card_id' => $card->id,
            'user_id' => $user->id,
            'type' => 'attachment_added',
            'description' => "{$user->name} a ajouté l'image « {$file->getClientOriginalName()} »",
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'attachment' => $newAttachment, 'attachments' => $attachments]);
        }

        return back()->with('success', "Image attachée avec succès à la tâche !");
    }

    /**
     * Delete an attachment from a card
     */
    public function deleteCardAttachment(Request $request, int $cardId, string $attachmentId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with('board')->findOrFail($cardId);

        if (!$card->board->canEdit($user)) {
            abort(403);
        }

        $attachments = collect($card->attachments ?: [])
            ->reject(fn($a) => ($a['id'] ?? '') === $attachmentId)
            ->values()
            ->all();

        $card->update(['attachments' => $attachments]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'attachments' => $attachments]);
        }

        return back()->with('success', "Pièce jointe supprimée.");
    }

    /**
     * Add a comment to a card with activity logging & collaborator mention
     */
    public function addCardComment(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with('board')->findOrFail($cardId);

        if (!$card->board->canAccess($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'comment' => 'required|string|min:1|max:3000',
        ]);

        $comment = KanbanCardComment::create([
            'card_id' => $card->id,
            'user_id' => $user->id,
            'comment' => $validated['comment'],
        ]);

        KanbanCardActivity::create([
            'board_id' => $card->board_id,
            'card_id' => $card->id,
            'user_id' => $user->id,
            'type' => 'comment_added',
            'description' => "{$user->name} a commenté : " . Str::limit($validated['comment'], 60),
        ]);

        $comment->load('user:id,name,first_name,avatar');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'comment' => $comment]);
        }

        return back()->with('success', "Commentaire publié avec succès !");
    }

    /**
     * Fetch comments and activities for a card (for real-time instant chat without page reload)
     */
    public function getCardComments(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with([
            'board',
            'comments.user:id,name,first_name,avatar',
            'activities.user:id,name,first_name,avatar',
        ])->findOrFail($cardId);

        if (!$card->board->canAccess($user)) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'card_id' => $card->id,
            'is_completed' => (bool)$card->is_completed,
            'is_archived' => (bool)$card->is_archived,
            'attachments' => $card->attachments ?: [],
            'comments' => $card->comments,
            'activities' => $card->activities()->latest()->take(25)->get(),
        ]);
    }

    /**
     * Toggle card completion state (lock / unlock modifications)
     */
    public function toggleCardCompletion(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with('board')->findOrFail($cardId);

        if (!$card->board->canEdit($user)) {
            abort(403);
        }

        $newState = !$card->is_completed;
        $card->update(['is_completed' => $newState]);

        $actionDesc = $newState 
            ? "{$user->name} a marqué la tâche comme terminée (verrouillée)" 
            : "{$user->name} a rouvert la tâche (déverrouillée pour modification)";

        KanbanCardActivity::create([
            'board_id' => $card->board_id,
            'card_id' => $card->id,
            'user_id' => $user->id,
            'type' => $newState ? 'card_completed' : 'card_reopened',
            'description' => $actionDesc,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'is_completed' => $newState, 'card' => $card]);
        }

        return back()->with('success', $actionDesc);
    }

    /**
     * Archive a card (hides from board, accessible in archives)
     */
    public function archiveCard(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with(['board', 'column'])->findOrFail($cardId);

        if (!$card->board->canEdit($user)) {
            abort(403);
        }

        $card->update([
            'is_archived' => true,
            'is_completed' => true,
            'archived_at' => now(),
        ]);

        KanbanCardActivity::create([
            'board_id' => $card->board_id,
            'card_id' => $card->id,
            'user_id' => $user->id,
            'type' => 'card_archived',
            'description' => "{$user->name} a archivé la tâche « {$card->title} »",
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'card' => $card]);
        }

        return back()->with('success', "Tâche « {$card->title} » archivée avec succès.");
    }

    /**
     * Unarchive a card (restores back to board column)
     */
    public function unarchiveCard(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with(['board', 'column'])->findOrFail($cardId);

        if (!$card->board->canEdit($user)) {
            abort(403);
        }

        $card->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        KanbanCardActivity::create([
            'board_id' => $card->board_id,
            'card_id' => $card->id,
            'user_id' => $user->id,
            'type' => 'card_unarchived',
            'description' => "{$user->name} a désarchivé la tâche « {$card->title} »",
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'card' => $card]);
        }

        return back()->with('success', "Tâche « {$card->title} » restaurée dans sa colonne.");
    }

    /**
     * Update board settings
     */
    public function updateBoard(Request $request, int $boardId)
    {
        $user = Auth::guard('web')->user();
        $board = KanbanBoard::findOrFail($boardId);

        if (!$board->canEdit($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ]);

        $board->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public' => $request->boolean('is_public'),
        ]);

        return back()->with('success', "Paramètres du tableau mis à jour !");
    }

    /**
     * Delete board
     */
    public function deleteBoard(Request $request, int $boardId)
    {
        $user = Auth::guard('web')->user();
        $board = KanbanBoard::findOrFail($boardId);

        // Only board creator or project owner can delete
        if ($board->user_id !== $user->id && $board->project?->user_id !== $user->id) {
            abort(403, "Action réservée au propriétaire du tableau.");
        }

        $board->delete();

        return redirect()->route('user.kanban.index')->with('success', "Le tableau a été supprimé.");
    }

    /**
     * Add a column to board
     */
    public function storeColumn(Request $request, int $boardId)
    {
        $user = Auth::guard('web')->user();
        $board = KanbanBoard::findOrFail($boardId);

        if (!$board->canEdit($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:60',
            'color' => 'nullable|string|max:20',
            'is_done_column' => 'boolean',
        ]);

        $maxOrder = KanbanColumn::where('board_id', $board->id)->max('order') ?? 0;

        $column = KanbanColumn::create([
            'board_id' => $board->id,
            'title' => $validated['title'],
            'color' => $validated['color'] ?: '#3b82f6',
            'order' => $maxOrder + 1,
            'is_done_column' => $request->boolean('is_done_column', false),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'column' => $column]);
        }

        return back()->with('success', "Colonne « {$column->title} » ajoutée.");
    }

    /**
     * Update column
     */
    public function updateColumn(Request $request, int $columnId)
    {
        $user = Auth::guard('web')->user();
        $column = KanbanColumn::with('board')->findOrFail($columnId);

        if (!$column->board->canEdit($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:60',
            'color' => 'nullable|string|max:20',
            'is_done_column' => 'boolean',
        ]);

        $column->update([
            'title' => $validated['title'],
            'color' => $validated['color'] ?: $column->color,
            'is_done_column' => $request->boolean('is_done_column', $column->is_done_column),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'column' => $column]);
        }

        return back()->with('success', "Colonne mise à jour.");
    }

    /**
     * Delete column
     */
    public function deleteColumn(Request $request, int $columnId)
    {
        $user = Auth::guard('web')->user();
        $column = KanbanColumn::with('board')->findOrFail($columnId);

        if (!$column->board->canEdit($user)) {
            abort(403);
        }

        $column->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Colonne supprimée.");
    }

    /**
     * Store new card in column
     */
    public function storeCard(Request $request, int $columnId)
    {
        $user = Auth::guard('web')->user();
        $column = KanbanColumn::with('board')->findOrFail($columnId);

        if (!$column->board->canEdit($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'labels' => 'nullable|array',
            'checklist' => 'nullable|array',
        ]);

        $maxOrder = KanbanCard::where('column_id', $column->id)->max('order') ?? 0;

        $card = KanbanCard::create([
            'column_id' => $column->id,
            'board_id' => $column->board_id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'labels' => $validated['labels'] ?? [],
            'checklist' => $validated['checklist'] ?? [],
            'order' => $maxOrder + 1,
        ]);

        $card->load('assignee:id,name,first_name,avatar');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'card' => $card]);
        }

        return back()->with('success', "Carte ajoutée.");
    }

    /**
     * Update card details
     */
    public function updateCard(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with('board')->findOrFail($cardId);

        if (!$card->board->canEdit($user)) {
            abort(403);
        }

        // Lock check: completed tasks cannot be modified unless explicitly unlocked
        if ($card->is_completed && !$request->boolean('force_unlock')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => "Cette tâche est marquée comme terminée et est verrouillée en lecture seule. Rouvrez-la pour apporter des modifications.",
                ], 422);
            }
            return back()->withErrors(['error' => "Cette tâche est marquée comme terminée et est verrouillée en lecture seule."]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'labels' => 'nullable|array',
            'checklist' => 'nullable|array',
        ]);

        $card->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? $card->priority,
            'due_date' => $validated['due_date'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'labels' => $validated['labels'] ?? $card->labels,
            'checklist' => $validated['checklist'] ?? $card->checklist,
        ]);

        $card->load('assignee:id,name,first_name,avatar');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'card' => $card]);
        }

        return back()->with('success', "Tâche mise à jour.");
    }

    /**
     * Drag & Drop Move Card (move across columns or reorder)
     */
    public function moveCard(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with(['board', 'column'])->findOrFail($cardId);

        if (!$card->board->canEdit($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'column_id' => 'required|exists:kanban_columns,id',
            'order' => 'required|integer|min:0',
        ]);

        $targetColumnId = (int)$validated['column_id'];
        $newOrder = (int)$validated['order'];

        // Ensure column belongs to the same board
        $targetColumn = KanbanColumn::where('id', $targetColumnId)
            ->where('board_id', $card->board_id)
            ->firstOrFail();

        $isCompleted = (bool)$targetColumn->is_done_column;

        $card->update([
            'column_id' => $targetColumn->id,
            'order' => $newOrder,
            'is_completed' => $isCompleted ? true : $card->is_completed,
        ]);

        return response()->json([
            'success' => true,
            'is_done_column' => $targetColumn->is_done_column,
            'message' => 'Carte déplacée avec succès.',
        ]);
    }

    /**
     * Delete card
     */
    public function deleteCard(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with('board')->findOrFail($cardId);

        if (!$card->board->canEdit($user)) {
            abort(403);
        }

        $card->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Tâche supprimée.");
    }

    /**
     * Magic Bridge: Convert completed Kanban Card into Project Devlog
     */
    public function publishToDevlog(Request $request, int $cardId)
    {
        $user = Auth::guard('web')->user();
        $card = KanbanCard::with(['board.project'])->findOrFail($cardId);

        if (!$card->board->project_id || !$card->board->project) {
            return back()->withErrors(['error' => "Ce tableau n'est rattaché à aucun projet public."]);
        }

        $project = $card->board->project;
        $isOwner = $project->isOwner($user);
        $isCollab = $project->isCollaborator($user);

        if (!$isOwner && !$isCollab) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'solution_applied' => 'nullable|string',
        ]);

        $status = $isOwner ? 'approved' : 'pending';

        $timeline = ProjectTimeline::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? $card->description,
            'solution_applied' => $validated['solution_applied'] ?? null,
            'event_date' => now(),
            'status' => $status,
        ]);

        $card->update(['devlog_published' => true]);

        if ($isOwner) {
            BookmarkNotificationService::notifyProjectDevlog($project, $timeline);
            $msg = "L'étape a été publiée sur le Devlog du projet et les abonnés ont été notifiés par email !";
        } else {
            $msg = "L'étape de Devlog a été soumise au propriétaire du projet pour validation.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Public Roadmap page (if board is public)
     */
    public function publicRoadmap(string $slug)
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        $board = KanbanBoard::with([
            'columns' => function ($q) {
                $q->orderBy('order');
            },
            'columns.cards' => function ($q) {
                $q->with('assignee:id,name,first_name,avatar')->orderBy('order');
            },
            'project',
        ])
        ->where('project_id', $project->id)
        ->where('is_public', true)
        ->first();

        return Inertia::render('Projects/Roadmap', [
            'project' => $project,
            'board' => $board,
        ]);
    }

    /**
     * Helper to populate default 4 Trello-style columns
     */
    private function createDefaultColumns(int $boardId): void
    {
        $defaults = [
            ['title' => 'Backlog & Idées', 'color' => '#64748b', 'order' => 1, 'is_done_column' => false],
            ['title' => 'À faire', 'color' => '#3b82f6', 'order' => 2, 'is_done_column' => false],
            ['title' => 'En cours', 'color' => '#f59e0b', 'order' => 3, 'is_done_column' => false],
            ['title' => 'Terminé', 'color' => '#00A859', 'order' => 4, 'is_done_column' => true],
        ];

        foreach ($defaults as $col) {
            KanbanColumn::create(array_merge($col, ['board_id' => $boardId]));
        }
    }
}
