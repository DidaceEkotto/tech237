<?php

namespace App\Livewire\Admin\Kanban;

use App\Models\KanbanBoard;
use App\Models\KanbanCard;
use Livewire\Component;

class Show extends Component
{
    public int $boardId;
    public string $viewMode = 'board'; // 'board' or 'list'
    public ?int $selectedCardId = null;
    public string $cardSearch = '';
    public string $cardPriorityFilter = '';

    public function mount(int $id): void
    {
        $this->boardId = $id;
    }

    public function selectCard(int $cardId): void
    {
        $this->selectedCardId = $cardId;
    }

    public function closeCardModal(): void
    {
        $this->selectedCardId = null;
    }

    public function render()
    {
        $board = KanbanBoard::with([
            'user',
            'project',
            'columns.cards' => function ($q) {
                $q->orderBy('order');
            },
            'columns.cards.assignee',
            'members.user',
            'activities.user',
        ])->findOrFail($this->boardId);

        $selectedCard = null;
        if ($this->selectedCardId) {
            $selectedCard = KanbanCard::with([
                'column',
                'board',
                'assignee',
                'comments.user',
                'activities.user',
            ])->where('board_id', $this->boardId)->find($this->selectedCardId);
        }

        // For list view:
        $cardsQuery = KanbanCard::with(['column', 'assignee'])
            ->where('board_id', $this->boardId);

        if (!empty($this->cardSearch)) {
            $s = trim($this->cardSearch);
            $cardsQuery->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        if (!empty($this->cardPriorityFilter)) {
            $cardsQuery->where('priority', $this->cardPriorityFilter);
        }

        $allCardsList = $cardsQuery->orderBy('order')->get();

        $totalCards = $board->columns->flatMap->cards->count();
        $completedCards = $board->columns->flatMap->cards->where('is_completed', true)->count();
        $totalHoursSpent = $board->columns->flatMap->cards->sum('spent_hours');
        $totalHoursEstimated = $board->columns->flatMap->cards->sum('estimated_hours');

        return view('livewire.admin.kanban.show', [
            'board' => $board,
            'selectedCard' => $selectedCard,
            'allCardsList' => $allCardsList,
            'totalCards' => $totalCards,
            'completedCards' => $completedCards,
            'totalHoursSpent' => $totalHoursSpent,
            'totalHoursEstimated' => $totalHoursEstimated,
        ])->layout('layouts.admin', ['title' => 'Tableau : ' . $board->title . ' (Lecture Admin)']);
    }
}
