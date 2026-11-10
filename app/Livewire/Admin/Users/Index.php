<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\AdminLog;

class Index extends Component
{
    public ?int $adminId = null;
    public string $name = '';
    public string $gender = 'Homme';
    public string $email = '';
    public string $phone = '';
    public string $birth_date = '';
    public string $city = '';
    public string $role = 'admin'; // super_admin, admin, editor, moderator, recruiter_admin, events_manager, analyst, tester_reviews
    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'gender' => 'required|string|in:Homme,Femme,Autre',
            'email' => 'required|email|unique:admins,email,' . $this->adminId,
            'phone' => 'nullable|string|max:30',
            'birth_date' => 'nullable|date',
            'city' => 'nullable|string|max:100',
            'role' => 'required|in:super_admin,admin,editor,moderator,recruiter_admin,events_manager,analyst,tester_reviews',
        ];
    }

    public function resetFields()
    {
        $this->reset(['adminId', 'name', 'gender', 'email', 'phone', 'birth_date', 'city', 'isEditing']);
        $this->gender = 'Homme';
        $this->role = 'admin';
    }

    public function save()
    {
        $this->validate();
        $current = Auth::guard('admin')->user();

        if ($this->adminId) {
            $adm = Admin::findOrFail($this->adminId);
            $adm->update([
                'name' => $this->name,
                'gender' => $this->gender,
                'email' => $this->email,
                'phone' => $this->phone ?: null,
                'birth_date' => $this->birth_date ?: null,
                'city' => $this->city ?: null,
                'role' => $this->role,
            ]);
            AdminLog::record('admin_updated', "Mise à jour des informations de l'admin {$adm->email}");
            session()->flash('success', "Informations de l'administrateur mises à jour.");
        } else {
            // Create new admin with default password 000000 and default_password = 0
            $adm = Admin::create([
                'name' => $this->name,
                'gender' => $this->gender,
                'email' => $this->email,
                'phone' => $this->phone ?: null,
                'birth_date' => $this->birth_date ?: null,
                'city' => $this->city ?: null,
                'password' => Hash::make('000000'),
                'role' => $this->role,
                'default_password' => 0, // Flag: must change password on first login
                'failed_login_attempts' => 0,
                'is_banned' => false,
            ]);

            AdminLog::record('admin_created', "Création du compte administrateur {$adm->email} avec rôle {$adm->role} et mot de passe par défaut 000000", $current);
            session()->flash('success', "Le compte administrateur a été créé. Son mot de passe initial est 000000 (six zéros).");
        }

        $this->resetFields();
    }

    public function edit(int $id)
    {
        $adm = Admin::findOrFail($id);
        $this->adminId = $adm->id;
        $this->name = $adm->name;
        $this->gender = $adm->gender ?? 'Homme';
        $this->email = $adm->email;
        $this->phone = $adm->phone ?? '';
        $this->birth_date = $adm->birth_date ? $adm->birth_date->format('Y-m-d') : '';
        $this->city = $adm->city ?? '';
        $this->role = $adm->role;
        $this->isEditing = true;
    }

    public function resetPasswordToDefault(int $id)
    {
        $adm = Admin::findOrFail($id);
        $adm->update([
            'password' => Hash::make('000000'),
            'default_password' => 0, // Reset to 0 so alert banner shows again
            'failed_login_attempts' => 0,
        ]);

        AdminLog::record('password_reset_default', "Réinitialisation du mot de passe de {$adm->email} à 000000");
        session()->flash('success', "Le mot de passe de {$adm->name} a été réinitialisé à '000000' (default_password = 0).");
    }

    public function banAdmin(int $id)
    {
        $current = Auth::guard('admin')->user();
        if ($id === $current->id) {
            session()->flash('error', "Vous ne pouvez pas bannir votre propre compte !");
            return;
        }

        $adm = Admin::findOrFail($id);
        $adm->update([
            'is_banned' => true,
            'banned_at' => now(),
        ]);

        AdminLog::record('admin_banned', "Bannissement manuel de l'administrateur {$adm->email} par {$current->email}");
        session()->flash('success', "Le compte de {$adm->name} a été banni. Il se trouve désormais dans la section 'Admins Bannis' du Super Admin.");
    }

    public function deleteAdmin(int $id)
    {
        $current = Auth::guard('admin')->user();
        if ($id === $current->id) {
            session()->flash('error', "Vous ne pouvez pas supprimer votre propre compte !");
            return;
        }

        $adm = Admin::findOrFail($id);
        $email = $adm->email;
        $adm->delete();

        AdminLog::record('admin_deleted', "Suppression définitive de l'administrateur {$email}");
        session()->flash('success', "Le compte {$email} a été définitivement supprimé.");
    }

    public function render()
    {
        $admins = Admin::latest()->get();
        return view('livewire.admin.users.index', compact('admins'))
            ->layout('layouts.admin', ['title' => 'Gestion des Administrateurs & Rôles']);
    }
}
