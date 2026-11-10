<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KanbanBoard;
use App\Models\KanbanCard;
use App\Models\KanbanColumn;
use App\Models\Project;
use App\Models\ProjectCollaborator;
use App\Models\ProjectTimeline;
use App\Models\User;
use App\Services\BookmarkNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Boards list
        $boards = KanbanBoard::with(['project', 'columns.cards'])
            ->where(function ($q) use ($user, $collabProjectIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('project_id', $collabProjectIds);
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

        return Inertia::render('User/Kanban/Index', [
            'boards' => $boards,
            'myProjects' => $myProjects,
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
            'columns' => function ($q) {
                $q->orderBy('order');
            },
            'columns.cards' => function ($q) {
                $q->with('assignee:id,name,first_name,avatar')->orderBy('order');
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

        if ($board->project_id && $board->project) {
            // Project owner
            if ($board->project->user_id !== $user?->id) {
                $projOwner = User::find($board->project->user_id);
                if ($projOwner) {
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

        return Inertia::render('User/Kanban/Show', [
            'board' => $board,
            'canEdit' => $canEdit,
            'teamMembers' => $teamMembers->values(),
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

        return redirect()->route('user.kanban.show', $board->id)->with('success', "Tableau Kanban « {$board->title} » créé avec succès !");
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

        $card->update([
            'column_id' => $targetColumn->id,
            'order' => $newOrder,
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
