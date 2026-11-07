<?php

namespace App\Livewire\Admin\Startups;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Startup;
use App\Models\AdminLog;

class Index extends Component
{
    use WithPagination;

    public ?int $selectedStartupId = null;
    public string $rejectionReason = '';
    public string $filterStatus = 'all';

    public function selectStartup(int $id)
    {
        $this->selectedStartupId = $id;
        $this->rejectionReason = '';
    }

    public function approve(int $id)
    {
        $startup = Startup::findOrFail($id);
        $startup->update([
            'status' => 'approved',
            'validated_at' => now(),
            'rejection_reason' => null,
        ]);

        AdminLog::record('startup_approved', "Validation de la startup '{$startup->name}' (RCCM: {$startup->rccm_number})");
        session()->flash('success', "La startup '{$startup->name}' a été approuvée avec succès.");
        $this->selectedStartupId = null;
    }

    public function reject(int $id)
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5|max:1000',
        ]);

        $startup = Startup::findOrFail($id);
        $startup->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
        ]);

        AdminLog::record('startup_rejected', "Rejet de la startup '{$startup->name}'. Motif: {$this->rejectionReason}");
        session()->flash('success', "La startup '{$startup->name}' a été rejetée avec motif transmis.");
        $this->selectedStartupId = null;
        $this->rejectionReason = '';
    }

    public function render()
    {
        $startups = Startup::with(['user', 'documents', 'projects'])
            ->when($this->filterStatus !== 'all', fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(10);

        $selectedStartup = $this->selectedStartupId ? Startup::with(['user', 'documents', 'projects'])->find($this->selectedStartupId) : null;

        $pendingCount = Startup::where('status', 'pending_validation')->count();

        return view('livewire.admin.startups.index', compact('startups', 'selectedStartup', 'pendingCount'))
            ->layout('layouts.admin', ['title' => 'Validation des Startups (Droit Camerounais / OHADA)']);
    }
}
