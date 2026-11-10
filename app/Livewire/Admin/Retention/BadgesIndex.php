<?php

namespace App\Livewire\Admin\Retention;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\AdminLog;
use App\Models\XpLevel;
use Illuminate\Support\Facades\Schema;

class BadgesIndex extends Component
{
    use WithPagination;

    public string $activeTab = 'members'; // 'members' or 'levels'
    public string $search = '';
    public ?int $selectedUserId = null;
    public string $selectedBadgeCode = 'pioneer';
    public int $xpToAdd = 50;
    public bool $showAssignModal = false;

    // XP Levels / Tiers management
    public bool $showLevelModal = false;
    public ?int $editingLevelId = null;
    public string $levelName = '';
    public int $levelMinXp = 20;
    public string $levelBadgeIcon = 'bi-award';
    public string $levelColor = '#00A859';
    public string $levelDescription = '';
    public int $levelOrder = 1;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
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

    // --- XP Levels / Tiers CRUD ---

    public function openCreateLevelModal()
    {
        $this->editingLevelId = null;
        $this->levelName = '';
        $this->levelMinXp = 20;
        $this->levelBadgeIcon = 'bi-award';
        $this->levelColor = '#00A859';
        $this->levelDescription = '';
        $this->levelOrder = (XpLevel::count() + 1);
        $this->showLevelModal = true;
    }

    public function editLevel(int $id)
    {
        $level = XpLevel::findOrFail($id);
        $this->editingLevelId = $level->id;
        $this->levelName = $level->name;
        $this->levelMinXp = (int)$level->min_xp;
        $this->levelBadgeIcon = $level->badge_icon ?: 'bi-award';
        $this->levelColor = $level->color ?: '#00A859';
        $this->levelDescription = $level->description ?? '';
        $this->levelOrder = (int)$level->order;
        $this->showLevelModal = true;
    }

    public function closeLevelModal()
    {
        $this->showLevelModal = false;
        $this->editingLevelId = null;
    }

    public function saveLevel()
    {
        $this->validate([
            'levelName' => 'required|string|max:100',
            'levelMinXp' => 'required|integer|min:0',
            'levelBadgeIcon' => 'nullable|string|max:50',
            'levelColor' => 'nullable|string|max:30',
            'levelDescription' => 'nullable|string|max:500',
        ]);

        if ($this->editingLevelId) {
            $level = XpLevel::findOrFail($this->editingLevelId);
            $level->update([
                'name' => $this->levelName,
                'min_xp' => $this->levelMinXp,
                'badge_icon' => $this->levelBadgeIcon ?: 'bi-award',
                'color' => $this->levelColor ?: '#00A859',
                'description' => $this->levelDescription,
                'order' => $this->levelOrder,
            ]);
            AdminLog::record('xp_level_updated', "Palier XP modifié : {$this->levelName} ({$this->levelMinXp} XP)");
            session()->flash('success', "Le palier XP '{$this->levelName}' a été mis à jour avec succès.");
        } else {
            XpLevel::create([
                'name' => $this->levelName,
                'min_xp' => $this->levelMinXp,
                'badge_icon' => $this->levelBadgeIcon ?: 'bi-award',
                'color' => $this->levelColor ?: '#00A859',
                'description' => $this->levelDescription,
                'order' => $this->levelOrder,
            ]);
            AdminLog::record('xp_level_created', "Nouveau palier XP créé : {$this->levelName} ({$this->levelMinXp} XP)");
            session()->flash('success', "Nouveau palier XP '{$this->levelName}' créé avec succès.");
        }

        $this->closeLevelModal();
    }

    public function deleteLevel(int $id)
    {
        $level = XpLevel::findOrFail($id);
        $name = $level->name;
        $level->delete();

        AdminLog::record('xp_level_deleted', "Palier XP supprimé : {$name}");
        session()->flash('success', "Le palier XP '{$name}' a été supprimé.");
    }

    public function recalculateAllUserRanks()
    {
        $users = User::all();
        $count = 0;
        foreach ($users as $u) {
            $matchingTier = XpLevel::getLevelForXp($u->xp ?? 0);
            if ($matchingTier && $u->rank_title !== $matchingTier->name) {
                $u->update(['rank_title' => $matchingTier->name]);
                $count++;
            }
        }

        AdminLog::record('xp_ranks_recalculated', "Recalcul des rangs pour {$count} utilisateurs");
        session()->flash('success', "Rangs recalculés avec succès ! {$count} membre(s) mis à jour selon les paliers actuels.");
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
        $xpLevels = XpLevel::getAllTiers();

        return view('livewire.admin.retention.badges-index', compact('users', 'availableBadges', 'xpLevels'))
            ->layout('layouts.admin', ['title' => 'Niveaux XP & Badges Communautaires']);
    }
}
