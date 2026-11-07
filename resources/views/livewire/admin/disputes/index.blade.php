<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Litiges & Modération de Contenu</h3>
            <p class="text-muted small mb-0">Gestion des signalements sur les projets, startups ou contenus signalés par la communauté.</p>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2">
            <i class="bi bi-shield-exclamation me-1"></i> {{ $pendingCount }} litige(s) en attente
        </span>
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
                        <th>#</th>
                        <th>Élément Signalé</th>
                        <th>Auteur du Signalement</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $disp)
                        <tr>
                            <td><span class="badge bg-light text-dark border">#{{ $disp->id }}</span></td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">{{ $disp->target_type }}</span>
                                <span class="small font-monospace ms-1">ID: {{ $disp->target_id }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $disp->user->full_name ?? 'Anonyme / Visiteur' }}</div>
                                <small class="text-muted">{{ $disp->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $disp->reason }}</div>
                                <small class="text-muted text-truncate d-block" style="max-width: 300px;">{{ $disp->details }}</small>
                            </td>
                            <td>
                                @if($disp->status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning">En cours</span>
                                @elseif($disp->status === 'resolved')
                                    <span class="badge bg-success-subtle text-success">Résolu</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Classé</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $disp->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                @if($disp->status === 'pending')
                                    <button class="btn btn-sm btn-outline-success me-1" wire:click="$set('selectedDisputeId', {{ $disp->id }})">
                                        <i class="bi bi-check2"></i> Résoudre
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" wire:click="dismiss({{ $disp->id }})">
                                        <i class="bi bi-x"></i> Classer
                                    </button>
                                @else
                                    <small class="text-muted fst-italic">Traité</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aucun signalement ou litige enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $disputes->links() }}
        </div>
    </div>

    @if($selectedDisputeId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-shield-check me-2"></i>Résoudre le litige #{{ $selectedDisputeId }}</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('selectedDisputeId', null)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Notes de résolution / Action entreprise <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="3" placeholder="Ex: Contenu vérifié et conforme, ou avertissement envoyé..." wire:model="resolutionNotes"></textarea>
                            @error('resolutionNotes') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('selectedDisputeId', null)">Annuler</button>
                        <button type="button" class="btn btn-success" wire:click="resolve({{ $selectedDisputeId }})">Confirmer la résolution</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
