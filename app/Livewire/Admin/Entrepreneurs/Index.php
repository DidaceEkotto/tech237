<?php

namespace App\Livewire\Admin\Entrepreneurs;

use Livewire\Component;
use App\Models\Entrepreneur;
use App\Models\AdminLog;

class Index extends Component
{
    public function toggleCurrent(int $id)
    {
        $e = Entrepreneur::findOrFail($id);
        if (!$e->is_current_month) {
            // Unset current month for all others
            Entrepreneur::where('is_current_month', true)->update(['is_current_month' => false]);
            $e->update(['is_current_month' => true, 'is_published' => true]);
            AdminLog::record('entrepreneur_spotlight', "Désignation de '{$e->name}' comme Entrepreneur du mois");
            session()->flash('success', "'{$e->name}' est maintenant l'Entrepreneur du mois à la une.");
        } else {
            $e->update(['is_current_month' => false]);
            session()->flash('success', "'{$e->name}' n'est plus à la une.");
        }
    }

    public function delete(int $id)
    {
        $e = Entrepreneur::findOrFail($id);
        $name = $e->name;
        $e->delete();
        AdminLog::record('entrepreneur_deleted', "Suppression du profil de '{$name}'");
        session()->flash('success', "Le profil de '{$name}' a été supprimé.");
    }

    public function render()
    {
        $entrepreneurs = Entrepreneur::with('startup')->latest()->get();
        return view('livewire.admin.entrepreneurs.index', compact('entrepreneurs'))
            ->layout('layouts.admin', ['title' => 'Entrepreneur du mois']);
    }
}
