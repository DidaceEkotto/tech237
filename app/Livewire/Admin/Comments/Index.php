<?php

namespace App\Livewire\Admin\Comments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Comment;
use App\Models\AdminLog;

class Index extends Component
{
    use WithPagination;

    public string $filterStatus = 'pending';
    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setFilter(string $status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function approve(int $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'approved']);

        AdminLog::record('comment_approved', "Validation du commentaire #{$comment->id} de l'utilisateur {$comment->user?->name}");
        session()->flash('success', "Commentaire approuvé avec succès et visible sur le site.");
    }

    public function reject(int $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'rejected']);

        AdminLog::record('comment_rejected', "Rejet du commentaire #{$comment->id}");
        session()->flash('success', "Commentaire rejeté.");
    }

    public function delete(int $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        AdminLog::record('comment_deleted', "Suppression du commentaire #{$id}");
        session()->flash('success', "Commentaire supprimé définitivement.");
    }

    public function approveAllPending()
    {
        $count = Comment::where('status', 'pending')->update(['status' => 'approved']);
        AdminLog::record('comments_bulk_approved', "Approbation groupée de {$count} commentaires.");
        session()->flash('success', "{$count} commentaires en attente ont été approuvés.");
    }

    public function render()
    {
        $comments = Comment::with(['user', 'article', 'interview', 'project', 'parent.user'])
            ->when($this->filterStatus !== 'all', fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->where('content', 'like', "%{$this->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"));
            })
            ->latest()
            ->paginate(15);

        $pendingCount = Comment::where('status', 'pending')->count();
        $approvedCount = Comment::where('status', 'approved')->count();
        $rejectedCount = Comment::where('status', 'rejected')->count();

        return view('livewire.admin.comments.index', compact('comments', 'pendingCount', 'approvedCount', 'rejectedCount'))
            ->layout('layouts.admin', ['title' => 'Modération des Commentaires']);
    }
}
