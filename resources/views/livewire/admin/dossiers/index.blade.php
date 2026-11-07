<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Dossiers Thématiques (Hubs de Contenu)</h3>
            <p class="text-muted small mb-0">Regroupez des séries d'articles autour d'un sujet fort (ex: ChatGPT & IA, Marvel MCU, GTA 6...).</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Column -->
        <div class="col-lg-4">
            <div class="card card-melty">
                <div class="card-header card-header-melty">
                    <i class="bi bi-folder-plus text-danger me-2"></i>{{ $isEditing ? 'Modifier le Dossier' : 'Nouveau Dossier' }}
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Titre du dossier (Français) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('title_fr') is-invalid @enderror" placeholder="ex: ChatGPT & Révolution IA" wire:model.live="title_fr" required>
                            @error('title_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Associer à une Rubrique (Optionnel)</label>
                            <select class="form-select form-select-sm" wire:model="rubrique_id">
                                <option value="">-- Aucune (Dossier Général) --</option>
                                @foreach($rubriques as $rub)
                                    <option value="{{ $rub->id }}">{{ $rub->name_fr }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted extra-small" style="font-size: 11px;">Ce dossier s'affichera sur la page de cette rubrique en front.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Title (English)</label>
                            <input type="text" class="form-control form-control-sm" placeholder="e.g. ChatGPT & AI Revolution" wire:model="title_en">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Slug URL</label>
                            <input type="text" class="form-control form-control-sm @error('slug') is-invalid @enderror" wire:model="slug" required>
                            @error('slug') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description (Français)</label>
                            <textarea class="form-control form-control-sm" rows="3" placeholder="Présentation du dossier..." wire:model="description_fr"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description (English)</label>
                            <textarea class="form-control form-control-sm" rows="2" placeholder="Overview of this topic..." wire:model="description_en"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Image de couverture</label>
                            @if($cover_image)
                                <img src="{{ $cover_image->temporaryUrl() }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @elseif($existing_image)
                                <img src="{{ asset($existing_image) }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @endif
                            <input type="file" class="form-control form-control-sm" wire:model="cover_image" accept="image/*">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="isFeaturedDossier" wire:model="is_featured">
                            <label class="form-check-label small" for="isFeaturedDossier">Mettre en avant sur la page d'accueil</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-melty flex-grow-1">
                                <i class="bi bi-check2-circle me-1"></i> {{ $isEditing ? 'Mettre à jour' : 'Enregistrer' }}
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
                                <th style="width: 70px;">Cover</th>
                                <th>Titre / Sujet</th>
                                <th>Articles associés</th>
                                <th>À la une</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dossiers as $d)
                                <tr>
                                    <td>
                                        @if($d->cover_image)
                                            <img src="{{ asset($d->cover_image) }}" class="rounded" style="width: 50px; height: 35px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 35px;">
                                                <i class="bi bi-folder"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $d->title_fr }}</div>
                                        <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                            <small class="text-muted">Slug: /dossier/{{ $d->slug }}</small>
                                            @if($d->rubrique)
                                                <span class="badge bg-danger-subtle text-danger"><i class="bi bi-collection-play me-1"></i>{{ $d->rubrique->name_fr }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                            {{ $d->articles_count }} article(s)
                                        </span>
                                    </td>
                                    <td>
                                        @if($d->is_featured)
                                            <span class="badge bg-success-subtle text-success">Oui</span>
                                        @else
                                            <span class="badge bg-light text-muted border">Non</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button wire:click="edit({{ $d->id }})" class="btn btn-sm btn-outline-primary me-1" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmStaticAction('Supprimer le dossier', 'Supprimer définitivement le dossier \'{{ addslashes($d->title_fr) }}\' ?', function() { @this.call('delete', {{ $d->id }}); })"
                                            title="Supprimer"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucun dossier créé pour l'instant.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
