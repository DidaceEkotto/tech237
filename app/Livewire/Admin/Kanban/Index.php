<?php

namespace App\Livewire\Admin\Kanban;

use App\Models\KanbanBoard;
use App\Models\KanbanCard;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $templateFilter = '';
    public string $visibilityFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTemplateFilter(): void
    {
        $this->resetPage();
    }

    public function updatingVisibilityFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = KanbanBoard::with(['user', 'project', 'columns'])
            ->withCount([
                'cards',
                'members',
                'cards as completed_cards_count' => function ($q) {
                    $q->where('is_completed', true);
                }
            ]);

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%")
                         ->orWhere('username', 'like', "%{$s}%");
                  })
                  ->orWhereHas('project', function ($pq) use ($s) {
                      $pq->where('title', 'like', "%{$s}%");
                  });
            });
        }

        if (!empty($this->templateFilter)) {
            $query->where('template', $this->templateFilter);
        }

        if ($this->visibilityFilter === 'public') {
            $query->where('is_public', true);
        } elseif ($this->visibilityFilter === 'private') {
            $query->where('is_public', false);
        }

        $boards = $query->latest()->paginate(15);

        // Stats summary
        $totalBoards = KanbanBoard::count();
        $totalCards = KanbanCard::count();
        $completedCards = KanbanCard::where('is_completed', true)->count();
        $publicBoards = KanbanBoard::where('is_public', true)->count();

        return view('livewire.admin.kanban.index', [
            'boards' => $boards,
            'totalBoards' => $totalBoards,
            'totalCards' => $totalCards,
            'completedCards' => $completedCards,
            'publicBoards' => $publicBoards,
        ])->layout('layouts.admin', ['title' => 'Gestion des Tableaux Kanban & Roadmaps']);
    }
}
