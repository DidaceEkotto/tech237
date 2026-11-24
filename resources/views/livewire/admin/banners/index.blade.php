<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Bannières Publicitaires (Ad Management)</h3>
            <p class="text-muted small mb-0">Insérez et gérez la publicité affichée à travers les différentes pages du site (Header, Sidebar, In-feed, etc.).</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-4">
            <div class="card card-melty">
                <div class="card-header card-header-melty">
                    <i class="bi bi-badge-ad text-danger me-2"></i>{{ $isEditing ? 'Modifier la Bannière' : 'Nouvelle Régie Pub' }}
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom / Titre interne de la campagne <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" placeholder="ex: Promo Jeux Vidéo Black Friday" wire:model="title" required>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Emplacement / Position d'affichage <span class="text-danger">*</span></label>
                                <span class="badge bg-primary-subtle text-primary rounded-pill">{{ count($positions) }} sélectionné(s)</span>
                            </div>
                            @php
                                $positionGroups = [
                                    'Bannières Standard' => [
                                        'header_top' => 'Header Top (Haut de page - Leaderboard)',
                                        'sidebar' => 'Sidebar (Colonne latérale - Pavé 300x250)',
                                        'home_middle' => "Home Middle (Milieu de la page d'accueil)",
                                        'in_article' => "In-Article (Intégré dans le corps de l'article)",
                                        'footer_banner' => 'Footer Banner (Bas de page)',
                                        'background' => 'Habillage de fond (Wallpaper Background Skinning)',
                                        'modal_popup' => 'Modal Popup de Bienvenue (1ère visite du jour)',
                                    ],
                                    'Fonds Publicitaires Hero / En-tête (Superposition dégradée)' => [
                                        'hero_games' => 'Hero - Page Tests Jeux Vidéo (/tests-jeux-video)',
                                        'hero_apps' => 'Hero - Page Tests Applications (/tests-applications)',
                                        'hero_pocs' => 'Hero - Page POCs (Micro-actus & Découvertes)',
                                        'hero_forum' => 'Hero - Page Forum (Discussions & Communauté)',
                                        'hero_conferences' => 'Hero - Page Conférences (Agenda & Événements)',
                                        'hero_jobs' => 'Hero - Page Emplois (Carrières & Recrutement IT)',
                                        'hero_startups' => 'Hero - Page Startups (Écosystème 237)',
                                        'hero_projects' => 'Hero - Page Projets (Showcase Développeurs)',
                                        'hero_entrepreneur' => 'Hero - Page Entrepreneur du Mois',
                                    ],
                                ];
                            @endphp
                            <div class="border rounded p-2 bg-light-subtle" style="max-height: 230px; overflow-y: auto;">
                                @foreach($positionGroups as $groupLabel => $options)
                                    <div class="small fw-bold text-uppercase text-muted px-1 mt-1 mb-1" style="font-size: 11px;">{{ $groupLabel }}</div>
                                    @foreach($options as $value => $label)
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" id="pos_{{ $value }}" value="{{ $value }}" wire:model.live="positions">
                                            <label class="form-check-label small" for="pos_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                            @error('positions')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Lien URL de redirection</label>
                            <input type="url" class="form-control form-control-sm" placeholder="https://..." wire:model="link_url">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Visuel Publicitaire (Image)</label>
                            @if($image)
                                <img src="{{ $image->temporaryUrl() }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @elseif($existing_image)
                                <img src="{{ asset($existing_image) }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @endif
                            <input type="file" class="form-control form-control-sm" wire:model="image" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ou Code HTML / Script Publicitaire personnalisé</label>
                            <textarea class="form-control form-control-sm font-monospace" rows="3" placeholder="<!-- Script AdSense / Custom HTML -->" wire:model="custom_code"></textarea>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="isActiveBanner" wire:model="is_active">
                            <label class="form-check-label small" for="isActiveBanner">Bannière active</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-melty flex-grow-1">
                                <i class="bi bi-check2-circle me-1"></i> {{ $isEditing ? 'Mettre à jour' : 'Enregistrer la pub' }}
                            </button>
                            @if($isEditing)
                                <button type="button" wire:click="resetFields" class="btn btn-light border">Annuler</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- List -->
        <div class="col-lg-8">
            <div class="card card-melty">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">Visuel</th>
                                <th>Campagne</th>
                                <th>Emplacement</th>
                                <th>État</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $b)
                                <tr>
                                    <td>
                                        @if($b->image)
                                            <img src="{{ asset($b->image) }}" class="rounded shadow-sm" style="width: 70px; height: 40px; object-fit: cover;">
                                        @else
                                            <span class="badge bg-light text-dark border">Code HTML</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $b->title }}</div>
                                        <small class="text-muted text-truncate d-block" style="max-width: 250px;">{{ $b->link_url ?? 'Lien direct' }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($b->positions as $pos)
                                                <span class="badge bg-light text-primary border">{{ $pos }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        @if($b->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Désactivée</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button wire:click="edit({{ $b->id }})" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmStaticAction('Supprimer la publicité', 'Supprimer définitivement la campagne \'{{ addslashes($b->title) }}\' ?', function() { @this.call('delete', {{ $b->id }}); })"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucune bannière publicitaire créée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
