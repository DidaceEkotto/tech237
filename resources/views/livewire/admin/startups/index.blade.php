<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Validation & Conformité des Startups</h3>
            <p class="text-muted small mb-0">Vérification de la conformité juridique (OHADA / Cameroun : SARL, SA, SAS, EURL, ETS, GIE, RCCM, NIU, CNI).</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm {{ $filterStatus === 'all' ? 'btn-dark' : 'btn-outline-dark' }}" wire:click="$set('filterStatus', 'all')">Toutes</button>
            <button class="btn btn-sm {{ $filterStatus === 'pending_validation' ? 'btn-warning' : 'btn-outline-warning' }}" wire:click="$set('filterStatus', 'pending_validation')">
                En attente ({{ $pendingCount }})
            </button>
            <button class="btn btn-sm {{ $filterStatus === 'approved' ? 'btn-success' : 'btn-outline-success' }}" wire:click="$set('filterStatus', 'approved')">Approuvées</button>
            <button class="btn btn-sm {{ $filterStatus === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}" wire:click="$set('filterStatus', 'rejected')">Rejetées</button>
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
                        <th>Startup</th>
                        <th>Fondateur</th>
                        <th>Forme Juridique</th>
                        <th>RCCM / NIU</th>
                        <th>Financement</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($startups as $st)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($st->logo)
                                        <img src="{{ asset($st->logo) }}" class="rounded border" style="width: 38px; height: 38px; object-fit: cover;">
                                    @else
                                        <div class="rounded bg-light text-muted border d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                            {{ strtoupper(substr($st->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $st->name }}</div>
                                        <small class="text-muted">{{ $st->city }} &bull; {{ $st->employee_count }} employé(s)</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $st->user->full_name ?? $st->user->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $st->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $st->legal_type }}</span>
                            </td>
                            <td>
                                <div class="small font-monospace">RCCM: {{ $st->rccm_number ?: 'Non renseigné' }}</div>
                                <div class="small text-muted font-monospace">NIU: {{ $st->niu_number ?: 'Non renseigné' }}</div>
                            </td>
                            <td>
                                @if($st->needs_funding)
                                    <span class="badge bg-info-subtle text-info">
                                        Recherche: {{ number_format($st->funding_amount, 0, ',', ' ') }} FCFA
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border">Autofinancé</span>
                                @endif
                            </td>
                            <td>
                                @if($st->status === 'approved')
                                    <span class="badge bg-success-subtle text-success">Approuvée</span>
                                @elseif($st->status === 'pending_validation')
                                    <span class="badge bg-warning-subtle text-warning">En attente (<= 2 jours)</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger" title="{{ $st->rejection_reason }}">Rejetée</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-dark" wire:click="selectStartup({{ $st->id }})">
                                    <i class="bi bi-eye me-1"></i> Examiner dossier
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aucun dossier de startup trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $startups->links() }}
        </div>
    </div>

    <!-- Review Dossier Modal -->
    @if($selectedStartup)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-briefcase-fill text-danger me-2"></i>
                            Dossier Juridique : {{ $selectedStartup->name }} (Forme : {{ $selectedStartup->legal_type }})
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('selectedStartupId', null)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning d-flex align-items-center mb-3">
                            <i class="bi bi-clock-history fs-4 me-2"></i>
                            <div>
                                <strong>Règle de traitement Melty Cameroon :</strong>
                                <span>La réponse doit être rendue dans un délai maximum de 2 jours ouvrés à compter de la soumission.</span>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Numéro RCCM (Registre du Commerce)</label>
                                <div class="p-2 bg-light rounded font-monospace">{{ $selectedStartup->rccm_number ?: 'Aucun RCCM fourni' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Numéro d'Identifiant Unique (NIU)</label>
                                <div class="p-2 bg-light rounded font-monospace">{{ $selectedStartup->niu_number ?: 'Aucun NIU fourni' }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small text-muted fw-bold">Description / Activité principale</label>
                            <div class="p-3 bg-light rounded">{{ $selectedStartup->description ?: 'Aucune description rédigée.' }}</div>
                        </div>

                        <!-- Associated Projects -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-kanban text-primary me-1"></i>
                                Projets Associés à cette Startup ({{ $selectedStartup->projects->count() }}) :
                            </h6>
                            @forelse($selectedStartup->projects as $proj)
                                <div class="card mb-2 border shadow-none bg-light p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($proj->logo)
                                                <img src="{{ asset($proj->logo) }}" alt="{{ $proj->name }}" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                                            @else
                                                <div class="rounded bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px;">
                                                    {{ strtoupper(substr($proj->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $proj->name }}</h6>
                                                <div class="small text-muted">
                                                    <span class="badge bg-secondary-subtle text-secondary me-1">{{ strtoupper($proj->project_type) }}</span>
                                                    <span>{{ $proj->timelines->count() }} étape(s) journal de bord</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ route('projects.show', $proj->slug) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Voir le projet public
                                            </a>
                                        </div>
                                    </div>
                                    @if($proj->description)
                                        <p class="small text-muted mt-2 mb-0 text-truncate">{{ $proj->description }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="p-3 text-muted border rounded small bg-white">Aucun projet associé à cette startup pour l'instant.</div>
                            @endforelse
                        </div>

                        <!-- Submitted Documents & CNI -->
                        <h6 class="fw-bold mb-2">Pièces Justificatives Téléversées :</h6>
                        <div class="list-group mb-4">
                            @forelse($selectedStartup->documents as $doc)
                                <a href="{{ asset($doc->file_path) }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark-check text-primary me-2"></i>
                                        <strong class="text-uppercase">{{ str_replace('_', ' ', $doc->type) }}</strong>
                                        <span class="text-muted small ms-2">({{ $doc->title ?: 'Document sans libellé' }})</span>
                                    </div>
                                    <span class="btn btn-sm btn-outline-primary">Voir la pièce</span>
                                </a>
                            @empty
                                <div class="p-3 text-muted border rounded small">Aucun document juridique ou CNI téléversé pour l'instant.</div>
                            @endforelse
                        </div>

                        <!-- Decision Section -->
                        <div class="card p-3 bg-light border">
                            <h6 class="fw-bold text-dark mb-3">Décision de Conformité & Validation :</h6>
                            
                            @if($selectedStartup->status === 'approved')
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check2-all me-1"></i> Cette startup a été validée le {{ $selectedStartup->validated_at ? $selectedStartup->validated_at->format('d/m/Y à H:i') : '' }}.
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Motif en cas de rejet (sera notifié au créateur) :</label>
                                    <textarea class="form-control form-control-sm" rows="3" placeholder="Ex: Veuillez fournir la copie scannée lisible du RCCM ou le recto/verso de votre CNI..." wire:model="rejectionReason"></textarea>
                                    @error('rejectionReason') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success flex-grow-1" wire:click="approve({{ $selectedStartup->id }})">
                                        <i class="bi bi-check-circle me-1"></i> Approuver la Startup
                                    </button>
                                    <button type="button" class="btn btn-danger flex-grow-1" wire:click="reject({{ $selectedStartup->id }})">
                                        <i class="bi bi-x-circle me-1"></i> Rejeter avec ce motif
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="$set('selectedStartupId', null)">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
