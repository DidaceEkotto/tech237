<?php

namespace App\Livewire\Admin\Retention;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\AdminLog;

class BadgesIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $selectedUserId = null;
    public string $selectedBadgeCode = 'pioneer';
    public int $xpToAdd = 50;
    public bool $showAssignModal = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAssignModal(int $userId)
    {
        $this->selectedUserId = $userId;
        $this->selectedBadgeCode = 'pioneer';
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->selectedUserId = null;
    }

    public function assignBadge()
    {
        if (!$this->selectedUserId) return;

        $user = User::findOrFail($this->selectedUserId);
        $available = UserBadge::availableBadges();

        if (!isset($available[$this->selectedBadgeCode])) {
            return;
        }

        $badgeInfo = $available[$this->selectedBadgeCode];

        UserBadge::updateOrCreate(
            [
                'user_id' => $user->id,
                'badge_code' => $this->selectedBadgeCode,
            ],
            [
                'name' => $badgeInfo['name'],
                'description' => $badgeInfo['description'],
                'icon' => $badgeInfo['icon'],
                'color' => $badgeInfo['color'],
                'awarded_by' => 'admin',
            ]
        );

        $user->addXp(25);

        AdminLog::record('badge_awarded', "Badge '{$badgeInfo['name']}' attribué à {$user->name}");
        session()->flash('success', "Le badge '{$badgeInfo['name']}' a été attribué à {$user->name} (+25 XP).");

        $this->closeAssignModal();
    }

    public function revokeBadge(int $badgeId)
    {
        $badge = UserBadge::with('user')->findOrFail($badgeId);
        $userName = $badge->user?->name ?? 'Utilisateur';
        $badgeName = $badge->name;
        $badge->delete();

        AdminLog::record('badge_revoked', "Badge '{$badgeName}' retiré à {$userName}");
        session()->flash('success', "Le badge '{$badgeName}' a été retiré de {$userName}.");
    }

    public function addXpToUser(int $userId, int $points)
    {
        $user = User::findOrFail($userId);
        $user->addXp($points);

        AdminLog::record('xp_adjusted', "Ajout de {$points} XP à {$user->name}");
        session()->flash('success', "{$points} XP attribués à {$user->name} (Nouveau rang : {$user->rank_title}).");
    }

    public function render()
    {
        $users = User::with('badges')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('xp', 'desc')
            ->paginate(15);

        $availableBadges = UserBadge::availableBadges();

        return view('livewire.admin.retention.badges-index', compact('users', 'availableBadges'))
            ->layout('layouts.admin', ['title' => 'Badges & Gamification Communautaire']);
    }
}
