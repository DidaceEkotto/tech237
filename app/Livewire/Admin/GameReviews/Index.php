<?php

namespace App\Livewire\Admin\GameReviews;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GameReview;
use App\Models\AdminLog;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $platformFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function delete(int $id)
    {
        $gr = GameReview::findOrFail($id);
        $name = $gr->game_name;
        $gr->delete();

        AdminLog::record('game_review_deleted', "Suppression du test jeu vidéo '{$name}'");
        session()->flash('success', "Le test de '{$name}' a été supprimé.");
    }

    public function render()
    {
        $reviews = GameReview::with('author')
            ->when($this->search, fn($q) => $q->where('game_name', 'like', "%{$this->search}%"))
            ->when($this->platformFilter, fn($q) => $q->where('platform_category', $this->platformFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.game-reviews.index', compact('reviews'))
            ->layout('layouts.admin', ['title' => 'Tests Jeux Vidéo']);
    }
}
