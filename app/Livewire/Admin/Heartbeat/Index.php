<?php

namespace App\Livewire\Admin\Heartbeat;

use App\Models\HeartbeatMonitor;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = HeartbeatMonitor::with(['user', 'project']);

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('url', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        if ($this->statusFilter === 'up') {
            $query->where('status', 'up')->where('is_currently_down', false);
        } elseif ($this->statusFilter === 'down') {
            $query->where(function ($q) {
                $q->where('status', 'down')->orWhere('is_currently_down', true);
            });
        } elseif ($this->statusFilter === 'pending') {
            $query->where('status', 'pending');
        }

        $monitors = $query->latest()->paginate(15);

        // Stats summary
        $totalMonitors = HeartbeatMonitor::count();
        $upCount = HeartbeatMonitor::where('status', 'up')->where('is_currently_down', false)->count();
        $downCount = HeartbeatMonitor::where(function ($q) {
            $q->where('status', 'down')->orWhere('is_currently_down', true);
        })->count();
        $avgUptime = HeartbeatMonitor::where('total_checks', '>', 0)->avg('uptime_percentage') ?: 100;
        $avgResponseTime = HeartbeatMonitor::where('total_checks', '>', 0)->avg('last_response_time_ms') ?: 0;

        return view('livewire.admin.heartbeat.index', [
            'monitors' => $monitors,
            'totalMonitors' => $totalMonitors,
            'upCount' => $upCount,
            'downCount' => $downCount,
            'avgUptime' => round($avgUptime, 1),
            'avgResponseTime' => round($avgResponseTime),
        ])->layout('layouts.admin', ['title' => 'Surveillance Heartbeat (Uptime Monitoring)']);
    }
}
