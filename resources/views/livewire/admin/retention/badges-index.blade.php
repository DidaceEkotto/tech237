<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i>Niveaux XP &amp; Badges de la Communauté</h4>
            <p class="text-muted small mb-0">Définissez vos paliers d'expérience (XP), attribuez des badges d'honneur et suivez les rangs des membres.</p>
        </div>
        
        <div class="d-flex gap-2 align-items-center">
            @if($activeTab === 'members')
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm rounded-pill px-3" placeholder="Rechercher un membre (nom, email)..." style="min-width: 260px;">
            @else
                <button wire:click="openCreateLevelModal" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-xs">
                    <i class="bi bi-plus-circle me-1"></i> Nouveau Palier d'XP
                </button>
                <button wire:click="recalculateAllUserRanks" wire:confirm="Voulez-vous recalculer les rangs de tous les utilisateurs selon les paliers définis ?" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs">
                    <i class="bi bi-arrow-clockwise me-1"></i> Actualiser les rangs
                </button>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <button wire:click="switchTab('members')" class="nav-link rounded-pill px-4 fw-bold {{ $activeTab === 'members' ? 'active bg-dark' : 'bg-white text-muted border' }}">
                <i class="bi bi-people-fill me-1"></i> Membres &amp; Progression ({{ $users->total() }})
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="switchTab('levels')" class="nav-link rounded-pill px-4 fw-bold {{ $activeTab === 'levels' ? 'active bg-success text-white' : 'bg-white text-muted border' }}">
                <i class="bi bi-trophy-fill me-1 text-warning"></i> Configuration des Paliers XP ({{ count($xpLevels) }})
            </button>
        </li>
    </ul>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- TAB 1: MEMBERS & RANKS -->
    @if($activeTab === 'members')
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Membre</th>
                            <th>Niveau / Rang Actuel</th>
                            <th>Points XP</th>
                            <th>Badges Obtenus</th>
                            <th class="text-end pe-4">Actions Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($u->avatar)
                                            <img src="{{ str_starts_with($u->avatar, '/') ? $u->avatar : '/' . $u->avatar }}" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                {{ strtoupper(substr($u->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <strong class="d-block text-dark">{{ $u->name }}</strong>
                                            <small class="text-muted">{{ $u->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold">
                                        {{ $u->rank_title ?? 'Novice Tech 🌱' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-success fs-6">{{ $u->xp ?? 0 }} XP</span>
                                        <button wire:click="addXpToUser({{ $u->id }}, 50)" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0" title="Ajouter +50 XP bonus">
                                            +50 XP
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 300px;">
                                        @forelse($u->badges as $b)
                                            <span class="badge rounded-pill text-white px-2 py-1 small d-inline-flex align-items-center gap-1" style="background-color: {{ $b->color }};" title="{{ $b->description }}">
                                                <i class="bi {{ $b->icon }}"></i>
                                                <span>{{ $b->name }}</span>
                                                <button wire:click="revokeBadge({{ $b->id }})" wire:confirm="Retirer ce badge à l'utilisateur ?" class="btn-close btn-close-white ms-1" style="font-size: 8px;"></button>
                                            </span>
                                        @empty
                                            <small class="text-muted fst-italic">Aucun badge</small>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="openAssignModal({{ $u->id }})" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs">
                                        <i class="bi bi-award me-1"></i> Attribuer un badge
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    Aucun membre trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-3 border-top">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    <!-- TAB 2: CONFIGURATION OF XP LEVELS / TIERS -->
    @else
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Système d'attribution automatique par palier</h5>
                            <p class="text-muted small mb-0">
                                Dès qu'un membre accumule le nombre d'XP requis (par ses POCs, devlogs, commentaires, votes ou résolutions forum), 
                                le titre honorifique lui est automatiquement assigné, et une <strong>modale sonore de célébration</strong> apparaît sur son écran !
                            </p>
                        </div>
                        <button wire:click="openCreateLevelModal" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Ajouter un palier
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Ordre</th>
                                    <th>Nom du Palier</th>
                                    <th>Seuil XP Requis</th>
                                    <th>Icône / Badge</th>
                                    <th>Description / Objectif</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($xpLevels as $lvl)
                                    <tr>
                                        <td class="ps-3 fw-bold text-muted">#{{ $lvl->order ?? $loop->iteration }}</td>
                                        <td>
                                            <span class="badge px-3 py-2 rounded-pill fw-bold text-white shadow-xs" style="background-color: {{ $lvl->color ?? '#00A859' }};">
                                                <i class="bi {{ $lvl->badge_icon ?? 'bi-award' }} me-1"></i> {{ $lvl->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="fs-6 text-dark">{{ $lvl->min_xp }} XP</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                <i class="bi {{ $lvl->badge_icon ?? 'bi-award' }} me-1"></i> {{ $lvl->badge_icon }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $lvl->description ?? 'Aucune description' }}</small>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button wire:click="editLevel({{ $lvl->id }})" class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1">
                                                <i class="bi bi-pencil me-1"></i> Modifier
                                            </button>
                                            <button wire:click="deleteLevel({{ $lvl->id }})" wire:confirm="Supprimer ce palier XP ?" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Aucun palier personnalisé configuré.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: ADD / EDIT XP LEVEL -->
    @if($showLevelModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-trophy-fill text-warning me-2"></i>
                            {{ $editingLevelId ? 'Modifier le Palier d\'XP' : 'Nouveau Palier d\'XP' }}
                        </h5>
                        <button type="button" wire:click="closeLevelModal" class="btn-close"></button>
                    </div>

                    <form wire:submit.prevent="saveLevel">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom du palier (ex: Novice Tech 🌱, Super Contributeur 🚀) <span class="text-danger">*</span></label>
                            <input type="text" wire:model="levelName" class="form-control" placeholder="ex: Super Contributeur" required maxlength="100">
                            @error('levelName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Seuil d'XP requis <span class="text-danger">*</span></label>
                                <input type="number" wire:model="levelMinXp" class="form-control" placeholder="ex: 100" min="0" required>
                                @error('levelMinXp') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Ordre d'affichage</label>
                                <input type="number" wire:model="levelOrder" class="form-control" placeholder="1, 2, 3..." min="1">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Icône Bootstrap Icons</label>
                                <input type="text" wire:model="levelBadgeIcon" class="form-control font-monospace" placeholder="bi-award, bi-star-fill...">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Couleur du Badge</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" wire:model="levelColor" class="form-control form-control-color rounded-circle" style="width: 40px; height: 40px;">
                                    <span class="small font-monospace">{{ $levelColor }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Description / Récompense du palier</label>
                            <textarea wire:model="levelDescription" class="form-control" rows="2" placeholder="Objectif atteint, privilèges ou mot d'encouragement..."></textarea>
                        </div>

                        <!-- Live Preview -->
                        <div class="p-3 bg-light rounded-3 mb-4 text-center">
                            <small class="text-muted d-block mb-1">Aperçu du badge :</small>
                            <span class="badge px-3 py-2 rounded-pill fw-bold text-white shadow-xs" style="background-color: {{ $levelColor }};">
                                <i class="bi {{ $levelBadgeIcon }} me-1"></i> {{ $levelName ?: 'Nouveau Palier' }}
                            </span>
                            <span class="d-block small text-muted mt-1">{{ $levelMinXp }} XP requis</span>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" wire:click="closeLevelModal" class="btn btn-light rounded-pill px-4">Annuler</button>
                            <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                                Enregistrer le palier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: ASSIGN BADGE -->
    @if($showAssignModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-award-fill text-warning me-2"></i>Attribuer un Badge d'Honneur</h5>
                        <button type="button" wire:click="closeAssignModal" class="btn-close"></button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Sélectionnez le badge à attribuer :</label>
                        <select wire:model="selectedBadgeCode" class="form-select">
                            @foreach($availableBadges as $code => $info)
                                <option value="{{ $code }}">{{ $info['name'] }} &mdash; {{ $info['description'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-info small p-3 rounded-3 mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        L'attribution d'un badge accorde automatiquement <strong>+25 XP</strong> au membre et fait évoluer son rang honorifique.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" wire:click="closeAssignModal" class="btn btn-light rounded-pill px-4">Annuler</button>
                        <button type="button" wire:click="assignBadge" class="btn btn-success rounded-pill px-4 fw-bold">
                            Confirmer et Décerner
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
