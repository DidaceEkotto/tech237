<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Gestion des Tags</h3>
            <p class="text-muted small mb-0">Mots-clés pour relier les articles et dynamiser les recherches.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Column -->
        <div class="col-lg-4">
            <div class="card card-melty">
                <div class="card-header card-header-melty">
                    <i class="bi bi-hash text-danger me-2"></i>{{ $isEditing ? 'Modifier le Tag' : 'Nouveau Tag' }}
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom du Tag (Français) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('name_fr') is-invalid @enderror" placeholder="ex: ChatGPT" wire:model.live="name_fr" required>
                            @error('name_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Name (English)</label>
                            <input type="text" class="form-control form-control-sm" placeholder="e.g. ChatGPT" wire:model="name_en">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Slug URL</label>
                            <input type="text" class="form-control form-control-sm @error('slug') is-invalid @enderror" wire:model="slug" required>
                            @error('slug') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-melty flex-grow-1">
                                <i class="bi bi-check2-circle me-1"></i> {{ $isEditing ? 'Mettre à jour' : 'Ajouter' }}
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
                                <th>Tag</th>
                                <th>Slug</th>
                                <th>Articles associés</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tags as $t)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                                            #{{ $t->name_fr }}
                                        </span>
                                    </td>
                                    <td><code>/tag/{{ $t->slug }}</code></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $t->articles_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        <button wire:click="edit({{ $t->id }})" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmStaticAction('Supprimer le tag', 'Supprimer définitivement le tag #{{ addslashes($t->name_fr) }} ?', function() { @this.call('delete', {{ $t->id }}); })"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Aucun tag trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
