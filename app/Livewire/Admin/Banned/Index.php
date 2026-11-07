<?php

namespace App\Livewire\Admin\Banned;

use Livewire\Component;
use App\Models\Admin;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public function mount()
    {
        $current = Auth::guard('admin')->user();
        if (!$current || !$current->isSuperAdmin()) {
            abort(403, 'Accès réservé exclusivement au Super Administrateur.');
        }
    }

    public function restoreAdmin(int $id)
    {
        $current = Auth::guard('admin')->user();
        if (!$current || !$current->isSuperAdmin()) {
            session()->flash('error', 'Action non autorisée.');
            return;
        }

        $adm = Admin::findOrFail($id);
        $adm->update([
            'is_banned' => false,
            'banned_at' => null,
            'failed_login_attempts' => 0,
        ]);

        AdminLog::record('admin_unbanned_restored', "Rétablissement du compte banni de {$adm->email} par le Super Admin {$current->email}", $current);
        session()->flash('success', "Le compte de {$adm->name} ({$adm->email}) a été réactivé avec succès. L'administrateur peut à nouveau se connecter.");
    }

    public function render()
    {
        $bannedAdmins = Admin::where('is_banned', true)->latest()->get();
        return view('livewire.admin.banned.index', compact('bannedAdmins'))
            ->layout('layouts.admin', ['title' => 'Espace Exclusif Super Admin - Admins Bannis']);
    }
}
