<?php

namespace App\Livewire\Admin\Pocs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Poc;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tagFilter = '';
    public string $statusFilter = '';
    public ?int $pocToDelete = null;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTagFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id)
    {
        $poc = Poc::findOrFail($id);
        $newStatus = $poc->status === 'published' ? 'draft' : 'published';
        $poc->update([
            'status' => $newStatus,
            'published_at' => $newStatus === 'published' ? ($poc->published_at ?: now()) : $poc->published_at,
        ]);

        $admin = Auth::guard('admin')->user();
        AdminLog::record('poc_status_updated', "Changement statut POC #{$poc->id} vers '{$newStatus}'", $admin);
        session()->flash('success', "Le POC #{$poc->id} a été passé en '{$newStatus}'.");
    }

    public function confirmDelete(int $id)
    {
        $this->pocToDelete = $id;
    }

    public function deletePoc()
    {
        if (!$this->pocToDelete) return;

        $poc = Poc::findOrFail($this->pocToDelete);
        $title = $poc->title;
        $poc->delete();

        $admin = Auth::guard('admin')->user();
        AdminLog::record('poc_deleted', "Suppression du POC '{$title}'", $admin);

        $this->pocToDelete = null;
        session()->flash('success', "Le POC '{$title}' a été supprimé définitivement.");
    }

    public function render()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('pocs')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {}
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('pocs')) {
            return view('livewire.admin.pocs.index', [
                'pocs' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'stats' => ['total' => 0, 'published' => 0, 'draft' => 0, 'total_upvotes' => 0, 'total_views' => 0],
            ])->layout('layouts.admin');
        }

        $query = Poc::with(['user', 'comments'])->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('author_name', 'like', "%{$this->search}%")
                  ->orWhere('content', 'like', "%{$this->search}%");
            });
        }

        if ($this->tagFilter) {
            $query->where('tag', $this->tagFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $pocs = $query->paginate(15);

        $stats = [
            'total' => Poc::count(),
            'published' => Poc::where('status', 'published')->count(),
            'draft' => Poc::where('status', 'draft')->count(),
            'total_upvotes' => Poc::sum('upvotes_count'),
            'total_views' => Poc::sum('views_count'),
        ];

        return view('livewire.admin.pocs.index', [
            'pocs' => $pocs,
            'stats' => $stats,
        ])->layout('layouts.admin');
    }
}
