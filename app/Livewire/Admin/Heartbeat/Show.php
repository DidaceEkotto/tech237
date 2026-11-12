<?php

namespace App\Livewire\Admin\Heartbeat;

use App\Models\HeartbeatCheck;
use App\Models\HeartbeatMonitor;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public int $monitorId;
    public string $statusFilter = 'all'; // all, up, down

    public function mount(int $id)
    {
        $this->monitorId = $id;
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $monitor = HeartbeatMonitor::with(['user', 'project'])->findOrFail($this->monitorId);

        $checksQuery = HeartbeatCheck::where('heartbeat_monitor_id', $this->monitorId);

        if ($this->statusFilter === 'up') {
            $checksQuery->where('is_up', true);
        } elseif ($this->statusFilter === 'down') {
            $checksQuery->where('is_up', false);
        }

        $checks = $checksQuery->orderByDesc('checked_at')->orderByDesc('id')->paginate(25);

        // Recent latency chart data or summary metrics
        $recentChecks = HeartbeatCheck::where('heartbeat_monitor_id', $this->monitorId)
            ->whereNotNull('checked_at')
            ->orderByDesc('checked_at')
            ->take(30)
            ->get()
            ->reverse();

        $avgLatency = $monitor->checks()->avg('response_time_ms') ?? 0;

        return view('livewire.admin.heartbeat.show', [
            'monitor' => $monitor,
            'checks' => $checks,
            'recentChecks' => $recentChecks,
            'avgLatency' => round($avgLatency),
        ])->layout('layouts.admin');
    }
}
