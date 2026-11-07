<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Catégories & Sous-catégories</h3>
            <p class="text-muted small mb-0">Organisez l'arborescence des rubriques de Melty avec couleurs et icônes.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Column -->
        <div class="col-lg-4">
            <div class="card card-melty">
                <div class="card-header card-header-melty">
                    <i class="bi bi-tag-fill text-danger me-2"></i>{{ $isEditing ? 'Modifier la Catégorie' : 'Nouvelle Catégorie' }}
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Rubrique associée</label>
                            <select class="form-select form-select-sm" wire:model="rubrique_id">
                                <option value="">-- Aucune Rubrique (Indépendante) --</option>
                                @foreach($rubriques as $rub)
                                    <option value="{{ $rub->id }}">{{ $rub->name_fr }} ({{ $rub->slug }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Associez cette catégorie à une rubrique pour l'organiser.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catégorie Parente (Optionnel pour sous-catégorie)</label>
                            <select class="form-select form-select-sm" wire:model="parent_id">
                                <option value="">-- Catégorie Principale (Niveau 1) --</option>
                                @foreach($parentOptions as $p)
                                    <option value="{{ $p->id }}">{{ $p->name_fr }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom (Français) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('name_fr') is-invalid @enderror" placeholder="ex: Séries" wire:model.live="name_fr" required>
                            @error('name_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Name (English)</label>
                            <input type="text" class="form-control form-control-sm" placeholder="e.g. TV Shows" wire:model="name_en">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Slug URL</label>
                            <input type="text" class="form-control form-control-sm @error('slug') is-invalid @enderror" wire:model="slug" required>
                            @error('slug') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Couleur Badge</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" wire:model="color" title="Choisir une couleur">
                                    <input type="text" class="form-control" wire:model="color">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Icône Bootstrap</label>
                                <input type="text" class="form-control form-control-sm" placeholder="bi-tv" wire:model="icon">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Ordre d'affichage</label>
                                <input type="number" class="form-control form-control-sm" wire:model="order">
                            </div>
                            <div class="col-6 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="isActiveCat" wire:model="is_active">
                                    <label class="form-check-label small" for="isActiveCat">Actif</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-melty flex-grow-1">
                                <i class="bi bi-check2-circle me-1"></i> {{ $isEditing ? 'Mettre à jour' : 'Créer' }}
                            </button>
                            @if($isEditing)
                                <button type="button" wire:click="resetFields" class="btn btn-light border">Annuler</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- List Column -->
        <div class="col-lg-8">
            <div class="card card-melty">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rubrique</th>
                                <th>Badge</th>
                                <th>Articles</th>
                                <th>Ordre</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mainCategories as $cat)
                                <tr class="table-light">
                                    <td>
                                        <div class="fw-bold fs-6">
                                            <i class="bi {{ $cat->icon }} me-1" style="color: {{ $cat->color }};"></i>
                                            {{ $cat->name_fr }}
                                        </div>
                                        <small class="text-muted">Slug: /category/{{ $cat->slug }}</small>
                                    </td>
                                    <td>
                                        <span class="badge text-white" style="background-color: {{ $cat->color }};">
                                            {{ $cat->name_fr }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $cat->articles_count }}</span>
                                    </td>
                                    <td>{{ $cat->order }}</td>
                                    <td class="text-end">
                                        <button wire:click="edit({{ $cat->id }})" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmStaticAction('Supprimer la catégorie', 'Supprimer définitivement la rubrique \'{{ addslashes($cat->name_fr) }}\' ?', function() { @this.call('delete', {{ $cat->id }}); })"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Subcategories -->
                                @foreach($cat->children as $sub)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-muted me-2">↳</span>
                                            <i class="bi {{ $sub->icon }} me-1 small"></i>
                                            <span class="fw-semibold">{{ $sub->name_fr }}</span>
                                            <small class="text-muted d-block ps-3">Slug: /category/{{ $sub->slug }}</small>
                                        </td>
                                        <td>
                                            <span class="badge text-white small" style="background-color: {{ $sub->color }};">
                                                {{ $sub->name_fr }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $sub->articles_count }}</span>
                                        </td>
                                        <td>{{ $sub->order }}</td>
                                        <td class="text-end">
                                            <button wire:click="edit({{ $sub->id }})" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmStaticAction('Supprimer la sous-catégorie', 'Supprimer définitivement la sous-rubrique \'{{ addslashes($sub->name_fr) }}\' ?', function() { @this.call('delete', {{ $sub->id }}); })"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucune catégorie trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
