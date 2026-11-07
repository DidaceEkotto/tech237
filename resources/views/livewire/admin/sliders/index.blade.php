<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Sliders Accueil</h3>
            <p class="text-muted small mb-0">Carrousels promotionnels de la une avec visuels, titres bilingues et liens d'action.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-4">
            <div class="card card-melty">
                <div class="card-header card-header-melty">
                    <i class="bi bi-images text-danger me-2"></i>{{ $isEditing ? 'Modifier le Slide' : 'Nouveau Slide' }}
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Titre (Français) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" placeholder="ex: L'actualité pop culture en direct" wire:model="title_fr" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Titre (English)</label>
                            <input type="text" class="form-control form-control-sm" placeholder="e.g. Breaking pop culture live" wire:model="title_en">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Sous-titre (Français)</label>
                            <input type="text" class="form-control form-control-sm" placeholder="ex: Séries, gaming, blockbusters..." wire:model="subtitle_fr">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Sous-titre (English)</label>
                            <input type="text" class="form-control form-control-sm" placeholder="e.g. Movies, games & more" wire:model="subtitle_en">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Lien URL de redirection</label>
                            <input type="text" class="form-control form-control-sm" placeholder="ex: /articles ou https://..." wire:model="link_url">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Texte Bouton FR</label>
                                <input type="text" class="form-control form-control-sm" wire:model="button_text_fr">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Button Text EN</label>
                                <input type="text" class="form-control form-control-sm" wire:model="button_text_en">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Image de fond (Grande taille)</label>
                            @if($image)
                                <img src="{{ $image->temporaryUrl() }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @elseif($existing_image)
                                <img src="{{ asset($existing_image) }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @endif
                            <input type="file" class="form-control form-control-sm" wire:model="image" accept="image/*">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Ordre</label>
                                <input type="number" class="form-control form-control-sm" wire:model="order">
                            </div>
                            <div class="col-6 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="isActiveSlider" wire:model="is_active">
                                    <label class="form-check-label small" for="isActiveSlider">Slide actif</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-melty flex-grow-1">
                                <i class="bi bi-check2-circle me-1"></i> {{ $isEditing ? 'Mettre à jour' : 'Ajouter Slide' }}
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
                                <th>Titre / Lien</th>
                                <th>Ordre</th>
                                <th>État</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sliders as $s)
                                <tr>
                                    <td>
                                        <img src="{{ asset($s->image) }}" class="rounded shadow-sm" style="width: 70px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $s->title_fr }}</div>
                                        <small class="text-muted">{{ $s->link_url ?? 'Sans lien' }}</small>
                                    </td>
                                    <td>{{ $s->order }}</td>
                                    <td>
                                        @if($s->is_active)
                                            <span class="badge bg-success-subtle text-success">Actif</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button wire:click="edit({{ $s->id }})" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmStaticAction('Supprimer le slide', 'Supprimer définitivement ce slide ?', function() { @this.call('delete', {{ $s->id }}); })"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucun slide enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
