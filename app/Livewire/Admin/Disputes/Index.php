<?php

namespace App\Livewire\Admin\Disputes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Dispute;
use App\Models\AdminLog;

class Index extends Component
{
    use WithPagination;

    public ?int $selectedDisputeId = null;
    public string $resolutionNotes = '';

    public function resolve(int $id)
    {
        $this->validate(['resolutionNotes' => 'required|string|min:4']);
        $d = Dispute::findOrFail($id);
        $d->update([
            'status' => 'resolved',
            'resolution_notes' => $this->resolutionNotes,
        ]);
        AdminLog::record('dispute_resolved', "Litige #{$d->id} résolu. Notes: {$this->resolutionNotes}");
        session()->flash('success', "Le litige #{$d->id} a été marqué comme résolu.");
        $this->selectedDisputeId = null;
        $this->resolutionNotes = '';
    }

    public function dismiss(int $id)
    {
        $d = Dispute::findOrFail($id);
        $d->update([
            'status' => 'dismissed',
            'resolution_notes' => 'Signalement classé sans suite après vérification administrative.',
        ]);
        AdminLog::record('dispute_dismissed', "Litige #{$d->id} classé sans suite.");
        session()->flash('success', "Le litige #{$d->id} a été classé sans suite.");
    }

    public function render()
    {
        $disputes = Dispute::with('user')->latest()->paginate(15);
        $pendingCount = Dispute::where('status', 'pending')->count();
        return view('livewire.admin.disputes.index', compact('disputes', 'pendingCount'))
            ->layout('layouts.admin', ['title' => 'Modération des Litiges & Signalements']);
    }
}
