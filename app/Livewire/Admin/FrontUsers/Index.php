<?php

namespace App\Livewire\Admin\FrontUsers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\AdminLog;

class Index extends Component
{
    use WithPagination;

    public ?int $selectedUserId = null;
    public string $banReason = '';
    public string $search = '';

    public function banUser(int $id)
    {
        $this->validate([
            'banReason' => 'required|string|min:4|max:500',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'status' => 'banned',
            'ban_reason' => $this->banReason,
        ]);

        AdminLog::record('user_banned', "Bannissement de l'utilisateur {$user->email}. Motif: {$this->banReason}");
        session()->flash('success', "Le compte de {$user->full_name} ({$user->email}) a été banni.");
        $this->selectedUserId = null;
        $this->banReason = '';
    }

    public function unbanUser(int $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'status' => 'active',
            'ban_reason' => null,
        ]);

        AdminLog::record('user_unbanned', "Rétablissement du compte utilisateur {$user->email}");
        session()->flash('success', "Le compte de {$user->full_name} a été réactivé.");
    }

    public function render()
    {
        $users = User::withCount(['startups', 'projects'])
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('first_name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.front-users.index', compact('users'))
            ->layout('layouts.admin', ['title' => 'Gestion des Utilisateurs Inscrits']);
    }
}
