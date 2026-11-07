<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Membres & Utilisateurs Inscrits</h3>
            <p class="text-muted small mb-0">Liste complète des utilisateurs du front, porteurs de projets, startups, statut et bannissement.</p>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control form-control-sm" placeholder="Rechercher par nom, email, téléphone..." wire:model.live.debounce.300ms="search">
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-melty">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Téléphone & Indicatif</th>
                        <th>Genre & Âge</th>
                        <th>Situation Matrimoniale</th>
                        <th>Startups / Projets</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($u->avatar)
                                        <img src="{{ asset($u->avatar) }}" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 13px;">
                                            {{ strtoupper(substr($u->name ?: 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $u->full_name }}</div>
                                        <small class="text-muted">{{ $u->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $u->country_code }}</span>
                                <span class="small">{{ $u->phone ?: 'Non renseigné' }}</span>
                            </td>
                            <td>
                                <div>{{ $u->gender ?: 'N/A' }}</div>
                                <small class="text-muted">
                                    @if($u->age) {{ $u->age }} ans @else Date de naiss. non spécifiée @endif
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ $u->marital_status ?: 'Célibataire' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary me-1">{{ $u->startups_count }} startup(s)</span>
                                <span class="badge bg-info-subtle text-info">{{ $u->projects_count }} projet(s)</span>
                            </td>
                            <td>
                                @if($u->status === 'banned')
                                    <span class="badge bg-danger-subtle text-danger" title="{{ $u->ban_reason }}">
                                        <i class="bi bi-slash-circle me-1"></i> Banni
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-check-circle me-1"></i> Actif
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($u->status === 'banned')
                                    <button class="btn btn-sm btn-outline-success" wire:click="unbanUser({{ $u->id }})">
                                        <i class="bi bi-shield-check me-1"></i> Débannir
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-outline-danger" wire:click="$set('selectedUserId', {{ $u->id }})">
                                        <i class="bi bi-slash-circle me-1"></i> Bannir
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aucun utilisateur enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Ban User Modal -->
    @if($selectedUserId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-shield-slash me-2"></i>Bannir l'utilisateur</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('selectedUserId', null)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted small">
                            L'utilisateur verra immédiatement une banderole rouge sur tout son espace lui notifiant le blocage de ses actions.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Motif du bannissement <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="3" placeholder="Ex: Non-respect des conditions d'utilisation, publication de contenu inapproprié..." wire:model="banReason"></textarea>
                            @error('banReason') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('selectedUserId', null)">Annuler</button>
                        <button type="button" class="btn btn-danger" wire:click="banUser({{ $selectedUserId }})">Confirmer le bannissement</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
