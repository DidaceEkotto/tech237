<?php

namespace App\Livewire\Admin\Articles;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Article;
use App\Models\Category;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $categoryFilter = '';
    public ?int $articleToDelete = null;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function validateArticle(int $id)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin->canValidateArticles()) {
            session()->flash('error', 'Vous n\'avez pas les droits nécessaires pour valider des articles.');
            return;
        }

        $article = Article::findOrFail($id);
        $article->update([
            'status' => 'published',
            'published_at' => now(),
            'reviewed_by' => $currentAdmin->id,
        ]);

        AdminLog::record('article_validated', "Validation et publication de l'article '{$article->title_fr}'", $currentAdmin);
        session()->flash('success', "L'article '{$article->title_fr}' a été validé et publié avec succès.");
    }

    public function confirmDelete(int $id)
    {
        $this->articleToDelete = $id;
    }

    public function deleteArticle()
    {
        if (!$this->articleToDelete) return;

        $article = Article::findOrFail($this->articleToDelete);
        $title = $article->title_fr;
        $article->delete();

        AdminLog::record('article_deleted', "Suppression de l'article '{$title}'");
        session()->flash('success', "L'article '{$title}' a été supprimé.");
        $this->articleToDelete = null;
    }

    public function render()
    {
        $query = Article::with(['author', 'category', 'dossier'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title_fr', 'like', "%{$this->search}%")
                        ->orWhere('title_en', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->categoryFilter, function ($q) {
                $q->where('category_id', $this->categoryFilter);
            })
            ->latest();

        $articles = $query->paginate(10);
        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('livewire.admin.articles.index', compact('articles', 'categories'))
            ->layout('layouts.admin', ['title' => 'Gestion des Articles']);
    }
}
