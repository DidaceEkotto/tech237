<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i>Badges & Gamification Communautaire</h4>
            <p class="text-muted small mb-0">Attribuez des distinctions honorifiques, des XP et gérez les rangs des membres les plus fidèles.</p>
        </div>
        <div class="d-flex gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm rounded-pill px-3" placeholder="Rechercher un membre (nom, email)..." style="min-width: 260px;">
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Users & Badges Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Membre</th>
                        <th>Niveau / Rang</th>
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

    <!-- Modal Assign Badge -->
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
