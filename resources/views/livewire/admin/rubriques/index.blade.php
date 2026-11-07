<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Rubriques du Site</h3>
            <p class="text-muted small mb-0">Créez et organisez les grandes rubriques éditoriales (ex: High-Tech, Cinéma, Séries, Gaming...) et associez-y vos catégories.</p>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Form Column -->
        <div class="col-lg-4">
            <div class="card card-melty">
                <div class="card-header card-header-melty">
                    <i class="bi bi-folder-plus text-danger me-2"></i>{{ $isEditing ? 'Modifier la Rubrique' : 'Créer une Rubrique' }}
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom Français <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('name_fr') is-invalid @enderror" placeholder="ex: High-Tech, Cinéma, Gaming..." wire:model.live="name_fr" required>
                            @error('name_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom Anglais</label>
                            <input type="text" class="form-control form-control-sm" placeholder="ex: Tech, Movies, Gaming..." wire:model="name_en">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Slug URL <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('slug') is-invalid @enderror" wire:model="slug" required>
                            @error('slug') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="form-label small fw-bold">Icône Bootstrap</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi {{ $icon ?: 'bi-collection-play' }}"></i></span>
                                    <input type="text" class="form-control" placeholder="bi-laptop" wire:model.live="icon">
                                </div>
                            </div>
                            <div class="col-5">
                                <label class="form-label small fw-bold">Ordre</label>
                                <input type="number" min="0" class="form-control form-control-sm" wire:model="order">
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="rubriqueActive" wire:model="is_active">
                            <label class="form-check-label small fw-bold" for="rubriqueActive">Rubrique active sur le site</label>
                        </div>

                        <!-- Image Bannière de la Rubrique (Front Hero Background) -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Image Bannière (Optionnelle)</label>
                            <input type="file" class="form-control form-control-sm @error('banner') is-invalid @enderror" wire:model="banner" accept="image/*">
                            <div class="extra-small text-muted mt-1" style="font-size: 11px;">Affichée en arrière-plan avec un joli dégradé sur le front de la rubrique.</div>
                            @error('banner') <div class="text-danger small">{{ $message }}</div> @enderror

                            <!-- Preview -->
                            <div wire:loading wire:target="banner" class="text-primary small mt-1">
                                <span class="spinner-border spinner-border-sm me-1"></span> Chargement de l'image...
                            </div>
                            @if($banner)
                                <div class="mt-2 position-relative d-inline-block w-100">
                                    <img src="{{ $banner->temporaryUrl() }}" class="img-thumbnail rounded-3 shadow-sm w-100" style="max-height: 100px; object-fit: cover;">
                                    <span class="badge bg-success position-absolute top-0 end-0 m-2">Nouvelle sélection</span>
                                </div>
                            @elseif($banner_image)
                                <div class="mt-2 position-relative d-inline-block w-100">
                                    <img src="/{{ $banner_image }}" class="img-thumbnail rounded-3 shadow-sm w-100" style="max-height: 100px; object-fit: cover;">
                                    <span class="badge bg-secondary position-absolute top-0 end-0 m-2">Image actuelle</span>
                                </div>
                            @endif
                        </div>

                        <!-- Personnalisation visuelle & Thème de la rubrique (Demandé par l'utilisateur) -->
                        <div class="border rounded-3 p-3 bg-light mb-3">
                            <h6 class="fw-bold small text-dark mb-2 d-flex align-items-center">
                                <i class="bi bi-palette text-danger me-2"></i>Personnalisation Visuelle Front
                            </h6>
                            <p class="text-muted extra-small mb-3" style="font-size: 11px;">Configurez le design unique de cette rubrique pour qu'elle se démarque.</p>

                            <!-- Style de mise en page / Layout -->
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold mb-1">Disposition de la grille</label>
                                <select class="form-select form-select-sm" wire:model="options.layout">
                                    <option value="grid_standard">Grille 3 Colonnes (Standard Melty)</option>
                                    <option value="grid_large">Grandes Cartes Immersives (Full Width)</option>
                                    <option value="grid_masonry">Style Asymétrique / Masonry Magazine</option>
                                    <option value="grid_list">Liste Média Horizontale (Image gauche)</option>
                                </select>
                            </div>

                            <!-- Style de la bannière d'en-tête -->
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold mb-1">Style En-tête / Bannière</label>
                                <select class="form-select form-select-sm" wire:model="options.banner_style">
                                    <option value="hero_gradient">Bannière Hero Dégradé Sombre Immersif</option>
                                    <option value="minimal">Minimaliste Épuré & Typographie Bold</option>
                                    <option value="boxed_badge">Boxed avec Grand Badge Coloré</option>
                                </select>
                            </div>

                            <!-- Style des Cartes d'articles -->
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold mb-1">Style des Cartes</label>
                                <select class="form-select form-select-sm" wire:model="options.card_style">
                                    <option value="rounded_shadow">Coins Arrondis + Ombre Flottante Douce</option>
                                    <option value="flat_bordered">Flat Minimaliste avec Bordure Fine</option>
                                    <option value="dark_mode">Thème Sombre Contrasté (Night Edition)</option>
                                    <option value="zoom_hover">Zoom Cinématique au survol</option>
                                </select>
                            </div>

                            <!-- Couleur d'accentuation -->
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold mb-1">Couleur d'accentuation</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color form-control-sm" style="width: 42px; height: 32px;" wire:model="options.accent_color">
                                    <input type="text" class="form-control form-control-sm" placeholder="#FF004B" wire:model="options.accent_color">
                                </div>
                            </div>

                            <!-- Options à cocher multiples -->
                            <div class="mt-3 pt-2 border-top">
                                <label class="form-label extra-small fw-bold mb-2">Composants à activer :</label>
                                
                                <div class="form-check form-check-sm mb-1">
                                    <input class="form-check-input" type="checkbox" id="optAudio" wire:model="options.show_audio_player">
                                    <label class="form-check-label extra-small" for="optAudio">Lecteur Audio Narrateur IA</label>
                                </div>

                                <div class="form-check form-check-sm mb-1">
                                    <input class="form-check-input" type="checkbox" id="optDossiers" wire:model="options.show_dossiers_carousel">
                                    <label class="form-check-label extra-small" for="optDossiers">Carrousel Dossiers Thématiques liés</label>
                                </div>

                                <div class="form-check form-check-sm mb-1">
                                    <input class="form-check-input" type="checkbox" id="optPills" wire:model="options.show_filter_pills">
                                    <label class="form-check-label extra-small" for="optPills">Pilules de filtres par catégorie</label>
                                </div>

                                <div class="form-check form-check-sm">
                                    <input class="form-check-input" type="checkbox" id="optSidebar" wire:model="options.show_trending_sidebar">
                                    <label class="form-check-label extra-small" for="optSidebar">Barre latérale Top Tendances</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-melty flex-grow-1">
                                <i class="bi bi-check2-circle me-1"></i> {{ $isEditing ? 'Mettre à jour' : 'Enregistrer la rubrique' }}
                            </button>
                            @if($isEditing)
                                <button type="button" wire:click="resetFields" class="btn btn-light border">Annuler</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Column -->
        <div class="col-lg-8">
            <div class="card card-melty">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">Ordre</th>
                                <th>Rubrique</th>
                                <th>Catégories associées</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rubriques as $rub)
                                <tr>
                                    <td><span class="badge bg-light text-dark border">{{ $rub->order }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($rub->banner_image)
                                                <img src="/{{ $rub->banner_image }}" class="rounded-2 border shadow-sm" style="width: 42px; height: 32px; object-fit: cover;" alt="Bannière">
                                            @else
                                                <i class="bi {{ $rub->icon }} fs-5 text-danger"></i>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">{{ $rub->name_fr }}</div>
                                                <small class="text-muted">{{ $rub->name_en ?: 'Sans traduction' }} &bull; /{{ $rub->slug }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $rub->categories_count }} catégorie(s)</span>
                                    </td>
                                    <td>
                                        @if($rub->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button wire:click="edit({{ $rub->id }})" class="btn btn-outline-primary" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="confirmStaticAction('Supprimer la rubrique', 'Voulez-vous supprimer la rubrique \'{{ addslashes($rub->name_fr) }}\' ? Les catégories associées seront conservées.', function() { @this.call('delete', {{ $rub->id }}); })"
                                                title="Supprimer"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Aucune rubrique créée pour le moment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
