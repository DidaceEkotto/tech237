<?php

namespace App\Livewire\Admin\AppReviews;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AppReview;
use App\Models\AdminLog;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $reviewToDelete = null;

    protected $paginationTheme = 'bootstrap';

    public function delete(int $id)
    {
        $review = AppReview::findOrFail($id);
        $title = $review->app_name;
        $review->delete();

        AdminLog::record('app_review_deleted', "Suppression du test d'application '{$title}'");
        session()->flash('success', "Le test de l'application '{$title}' a été supprimé.");
    }

    public function render()
    {
        $reviews = AppReview::with('author')
            ->when($this->search, function ($q) {
                $q->where('app_name', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.app-reviews.index', compact('reviews'))
            ->layout('layouts.admin', ['title' => 'Tests d\'Applications (Mobile, Web, Desktop)']);
    }
}
