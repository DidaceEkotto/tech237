<?php

namespace App\Livewire\Admin\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\AdminLog;
use App\Services\ImageService;

class Index extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $gender = 'Homme';
    public string $email = '';
    public string $phone = '';
    public string $birth_date = '';
    public string $city = '';
    public string $bio = '';
    public ?string $social_facebook = '';
    public ?string $social_twitter = '';
    public ?string $social_linkedin = '';
    public ?string $social_tiktok = '';
    public ?string $social_instagram = '';
    public $new_avatar = null;
    public ?string $avatar = null;

    // Password Update Fields
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount()
    {
        $admin = Auth::guard('admin')->user();
        $this->name = $admin->name;
        $this->gender = $admin->gender ?? 'Homme';
        $this->email = $admin->email;
        $this->phone = $admin->phone ?? '';
        $this->birth_date = $admin->birth_date ? $admin->birth_date->format('Y-m-d') : '';
        $this->city = $admin->city ?? '';
        $this->bio = $admin->bio ?? '';
        $this->social_facebook = $admin->social_facebook ?? '';
        $this->social_twitter = $admin->social_twitter ?? '';
        $this->social_linkedin = $admin->social_linkedin ?? '';
        $this->social_tiktok = $admin->social_tiktok ?? '';
        $this->social_instagram = $admin->social_instagram ?? '';
        $this->avatar = $admin->avatar;
    }

    public function updateProfile()
    {
        $admin = Auth::guard('admin')->user();

        $this->validate([
            'name' => 'required|string|max:100',
            'gender' => 'required|string|in:Homme,Femme,Autre',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'phone' => 'nullable|string|max:30',
            'birth_date' => 'nullable|date',
            'city' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:500',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
            'social_tiktok' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'new_avatar' => 'nullable|image|max:5120',
        ]);

        $avatarPath = $this->avatar;
        if ($this->new_avatar) {
            $avatarPath = ImageService::saveImage($this->new_avatar, 'avatar');
        }

        $admin->update([
            'name' => $this->name,
            'gender' => $this->gender,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'birth_date' => $this->birth_date ?: null,
            'city' => $this->city ?: null,
            'bio' => $this->bio,
            'social_facebook' => $this->social_facebook ?: null,
            'social_twitter' => $this->social_twitter ?: null,
            'social_linkedin' => $this->social_linkedin ?: null,
            'social_tiktok' => $this->social_tiktok ?: null,
            'social_instagram' => $this->social_instagram ?: null,
            'avatar' => $avatarPath,
        ]);

        $this->avatar = $avatarPath;
        $this->new_avatar = null;

        AdminLog::record('profile_updated', "Mise à jour du profil administrateur {$admin->email}", $admin);
        session()->flash('profile_success', "Votre profil a été mis à jour avec succès.");
    }

    public function updatePassword()
    {
        $admin = Auth::guard('admin')->user();

        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($this->current_password, $admin->password)) {
            $this->addError('current_password', 'Le mot de passe actuel est incorrect.');
            return;
        }

        $admin->update([
            'password' => Hash::make($this->new_password),
            'default_password' => 1,
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        AdminLog::record('password_changed', "Changement du mot de passe pour {$admin->email}", $admin);
        session()->flash('password_success', "Votre mot de passe a été modifié avec succès. L'alerte de mot de passe par défaut est maintenant levée.");
    }

    public function render()
    {
        return view('livewire.admin.profile.index')
            ->layout('layouts.admin', ['title' => 'Mon Profil']);
    }
}
