<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Mini-Actualités Communautaires (POCs)
            </h1>
            <p class="text-muted small mb-0">Supervisez, publiez, modérez et supprimez les brèves et actualités soumises par les membres.</p>
        </div>
        <div>
            <a href="{{ route('pocs.index') }}" target="_blank" class="btn btn-outline-dark rounded-pill px-3 btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i> Voir le flux Front
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Total POCs</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['total']) }}</h4>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-lightning-charge fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">En Ligne (Publiés)</span>
                        <h4 class="fw-bold mb-0 text-success">{{ number_format($stats['published']) }}</h4>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-check-circle fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Total Upvotes</span>
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($stats['total_upvotes']) }}</h4>
                    </div>
                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-fire fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Lectures / Vues</span>
                        <h4 class="fw-bold mb-0 text-primary">{{ number_format($stats['total_views']) }}</h4>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-eye fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash message -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-0" placeholder="Rechercher par titre, auteur, contenu..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select bg-light border-0" wire:model.live="tagFilter">
                        <option value="">Toutes les thématiques</option>
                        <option value="general">Général Tech</option>
                        <option value="ia">Intelligence Artificielle</option>
                        <option value="fintech">Fintech & Mobile Money</option>
                        <option value="dev">Dev & Open Source</option>
                        <option value="startup">Startups & Levées</option>
                        <option value="cybersec">Cybersécurité</option>
                        <option value="mobile">Mobile & Télécoms</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select bg-light border-0" wire:model.live="statusFilter">
                        <option value="">Tous les statuts</option>
                        <option value="published">Publiés en direct</option>
                        <option value="draft">Brouillons / Masqués</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Titre & Aperçu</th>
                        <th>Auteur</th>
                        <th>Thème</th>
                        <th>Statut</th>
                        <th>Upvotes / Vues</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pocs as $poc)
                        <tr>
                            <td class="ps-4" style="max-width: 320px;">
                                <div class="d-flex align-items-center gap-3">
                                    @if($poc->cover_image)
                                        <img src="{{ asset($poc->cover_image) }}" class="rounded-3 object-fit-cover shadow-sm flex-shrink-0" style="width: 48px; height: 48px;" alt="Cover">
                                    @else
                                        <div class="rounded-3 bg-light text-warning d-flex align-items-center justify-content-center flex-shrink-0 border" style="width: 48px; height: 48px;">
                                            <i class="bi bi-lightning-charge fs-5"></i>
                                        </div>
                                    @endif
                                    <div class="text-truncate">
                                        <a href="{{ route('pocs.show', $poc->slug ?: $poc->id) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-danger d-block text-truncate">
                                            {{ $poc->title }}
                                        </a>
                                        <small class="text-muted d-block text-truncate" style="font-size: 11px;">
                                            {{ $poc->summary ?: Str::limit(strip_tags($poc->content), 80) }}
                                        </small>
                                        @if($poc->link_url)
                                            <a href="{{ $poc->link_url }}" target="_blank" class="extra-small text-danger text-decoration-none" style="font-size: 10px;">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Lien source
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($poc->user)
                                    <div class="d-flex align-items-center gap-2">
                                        @if($poc->user->avatar)
                                            <img src="{{ asset($poc->user->avatar) }}" class="rounded-circle border" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 30px; height: 30px; font-size: 11px;">
                                                {{ strtoupper(substr($poc->user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="fw-semibold small d-block text-dark">{{ $poc->user->name }}</span>
                                            <small class="text-muted extra-small" style="font-size: 10px;">{{ $poc->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="small text-muted">{{ $poc->author_name ?: 'Invité' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">
                                    #{{ strtoupper($poc->tag) }}
                                </span>
                            </td>
                            <td>
                                @if($poc->status === 'published')
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill small">
                                        <i class="bi bi-check-circle me-1"></i>Publié
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill small">
                                        <i class="bi bi-eye-slash me-1"></i>Masqué
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-danger fw-bold"><i class="bi bi-fire me-1"></i>{{ $poc->upvotes_count }}</span>
                                    <span class="text-muted ms-2"><i class="bi bi-eye me-1"></i>{{ $poc->views_count }}</span>
                                </div>
                            </td>
                            <td class="small text-muted">
                                {{ $poc->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('pocs.show', $poc->slug ?: $poc->id) }}" target="_blank" class="btn btn-sm btn-light border" title="Voir sur le site">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm {{ $poc->status === 'published' ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                        title="{{ $poc->status === 'published' ? 'Masquer' : 'Publier' }}"
                                        wire:click="toggleStatus({{ $poc->id }})"
                                    >
                                        <i class="bi {{ $poc->status === 'published' ? 'bi-eye-slash' : 'bi-check-lg' }}"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-danger" 
                                        title="Supprimer définitivement"
                                        wire:click="confirmDelete({{ $poc->id }})"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-lightning-charge fs-1 d-block mb-2 text-warning"></i>
                                <strong>Aucun POC trouvé.</strong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pocs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $pocs->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL CONFIRM DELETE -->
    @if($pocToDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-body text-center p-4">
                        <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex p-3 fs-2 mb-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Confirmer la suppression ?</h5>
                        <p class="text-muted small mb-4">Cette action est irréversible. Le POC ainsi que ses upvotes et commentaires seront définitivement supprimés.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('pocToDelete', null)">
                                Annuler
                            </button>
                            <button type="button" class="btn btn-danger rounded-pill px-4" wire:click="deletePoc">
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
