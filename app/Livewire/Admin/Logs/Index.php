<?php

namespace App\Livewire\Admin\Logs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdminLog;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $actionFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function clearOldLogs()
    {
        // Keep logs for audit but allow super admin to purge if needed
        AdminLog::where('created_at', '<', now()->subDays(60))->delete();
        session()->flash('success', "Les journaux d'activité antérieurs à 60 jours ont été purgés.");
    }

    public function render()
    {
        $logs = AdminLog::with('admin')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('description', 'like', "%{$this->search}%")
                        ->orWhere('admin_email', 'like', "%{$this->search}%")
                        ->orWhere('ip_address', 'like', "%{$this->search}%")
                        ->orWhere('url', 'like', "%{$this->search}%");
                });
            })
            ->when($this->actionFilter, fn($q) => $q->where('action', $this->actionFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.logs.index', compact('logs'))
            ->layout('layouts.admin', ['title' => 'Journaux d\'Activité & Audit']);
    }
}
