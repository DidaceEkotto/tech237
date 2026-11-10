<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-buildings text-primary me-2"></i>Partenaires & Entreprises Tech
            </h1>
            <p class="text-muted small mb-0">Gérez les logos des entreprises et sponsors de l'écosystème défilant au bas du site.</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Ajouter un partenaire
        </button>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Total Partenaires</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h4>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-buildings fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Actifs en vitrine</span>
                        <h4 class="fw-bold mb-0 text-success">{{ $stats['active'] }}</h4>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-check-circle fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Masqués</span>
                        <h4 class="fw-bold mb-0 text-secondary">{{ $stats['inactive'] }}</h4>
                    </div>
                    <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-eye-slash fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Search -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
        <div class="input-group">
            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control bg-light border-0" placeholder="Rechercher une entreprise ou un lien..." wire:model.live.debounce.300ms="search">
        </div>
    </div>

    <!-- Modal Form (Create/Edit) -->
    @if($isCreating)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1055;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi bi-buildings text-primary me-2"></i>
                            {{ $editingPartnerId ? 'Modifier le partenaire' : 'Nouveau partenaire' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="cancel"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nom de l'entreprise <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" placeholder="ex: MTN Cameroon, Orange, Activa..." wire:model="name" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Lien de redirection (Optionnel)</label>
                                <input type="url" class="form-control @error('website_url') is-invalid @enderror" placeholder="ex: https://entreprise.com ou /entrepreneurs" wire:model="website_url">
                                <small class="text-muted extra-small">Si externe, s'ouvre dans un nouvel onglet. Si interne, redirige sur le site.</small>
                                @error('website_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Logo de l'entreprise <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" wire:model="logo" accept="image/*">
                                @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                @if($logo)
                                    <div class="mt-2 p-2 bg-light rounded text-center border">
                                        <small class="text-muted d-block mb-1">Aperçu du nouveau logo :</small>
                                        <img src="{{ $logo->temporaryUrl() }}" style="max-height: 50px; max-width: 160px; object-fit: contain;">
                                    </div>
                                @elseif($currentLogo)
                                    <div class="mt-2 p-2 bg-light rounded text-center border">
                                        <small class="text-muted d-block mb-1">Logo actuel :</small>
                                        <img src="{{ asset($currentLogo) }}" style="max-height: 50px; max-width: 160px; object-fit: contain;">
                                    </div>
                                @endif
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">Ordre d'affichage</label>
                                    <input type="number" class="form-control" wire:model="order" min="0">
                                </div>
                                <div class="col-6 d-flex align-items-center pt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="activeSwitch" wire:model="is_active">
                                        <label class="form-check-label small fw-semibold" for="activeSwitch">Actif (Visible)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" wire:click="cancel">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <i class="bi bi-check-lg me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Partners Grid -->
    <div class="row g-3">
        @forelse($partners as $partner)
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden d-flex flex-column justify-content-between p-3 text-center">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge {{ $partner->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill small">
                                {{ $partner->is_active ? 'Visible' : 'Masqué' }}
                            </span>
                            <span class="badge bg-light text-muted border small">Ordre #{{ $partner->order }}</span>
                        </div>

                        <div class="d-flex align-items-center justify-content-center p-3 bg-light rounded-3 mb-3" style="height: 90px;">
                            <img src="{{ asset($partner->logo) }}" alt="{{ $partner->name }}" style="max-height: 60px; max-width: 100%; object-fit: contain;">
                        </div>

                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $partner->name }}">{{ $partner->name }}</h6>
                        @if($partner->website_url)
                            <a href="{{ $partner->website_url }}" target="_blank" class="extra-small text-primary text-decoration-none d-block text-truncate mb-2" style="font-size: 11px;">
                                <i class="bi bi-box-arrow-up-right me-1"></i>{{ $partner->website_url }}
                            </a>
                        @else
                            <span class="text-muted extra-small d-block mb-2" style="font-size: 11px;">Aucun lien</span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center justify-content-center gap-1 border-top pt-2 mt-2">
                        <button wire:click="toggleActive({{ $partner->id }})" class="btn btn-sm btn-light rounded-circle" title="Basculer visibilité">
                            <i class="bi {{ $partner->is_active ? 'bi-eye-slash text-muted' : 'bi-eye text-success' }}"></i>
                        </button>
                        <button wire:click="editPartner({{ $partner->id }})" class="btn btn-sm btn-light rounded-circle" title="Modifier">
                            <i class="bi bi-pencil-square text-primary"></i>
                        </button>
                        <button onclick="confirm('Supprimer ce partenaire ?') || event.stopImmediatePropagation()" wire:click="deletePartner({{ $partner->id }})" class="btn btn-sm btn-light rounded-circle" title="Supprimer">
                            <i class="bi bi-trash text-danger"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <i class="bi bi-buildings text-muted fs-1 mb-3"></i>
                    <h5 class="fw-bold text-dark">Aucun partenaire enregistré</h5>
                    <p class="text-muted small mb-3">Ajoutez vos premières entreprises et sponsors partenaires pour les afficher au bas du site.</p>
                    <div>
                        <button wire:click="openCreateModal" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-plus-lg me-1"></i> Ajouter un partenaire
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $partners->links() }}
    </div>
</div>
