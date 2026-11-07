<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Gestion des Articles</h3>
            <p class="text-muted small mb-0">Rédigez, modifiez, prévisualisez et validez les articles du site.</p>
        </div>
        <a href="{{ route('admin.articles.create') }}" class="btn btn-melty shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Rédiger un nouvel article
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="card card-melty mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Rechercher par titre (FR ou EN)...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="published">Publiés</option>
                        <option value="pending_review">En attente de validation</option>
                        <option value="draft">Brouillons</option>
                        <option value="rejected">Rejetés</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="categoryFilter" class="form-select">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name_fr }}</option>
                            @foreach($cat->children as $sub)
                                <option value="{{ $sub->id }}">&nbsp;&nbsp;↳ {{ $sub->name_fr }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <button wire:click="$set('search', ''); $set('statusFilter', ''); $set('categoryFilter', '')" class="btn btn-outline-secondary w-100" title="Réinitialiser filtres">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Articles Table -->
    <div class="card card-melty">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Titre / Dossier</th>
                        <th>Catégorie</th>
                        <th>Auteur</th>
                        <th>Statut</th>
                        <th>Vues</th>
                        <th class="text-end" style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $art)
                        <tr>
                            <td>
                                @if($art->featured_image)
                                    <img src="{{ asset($art->featured_image) }}" alt="Thumbnail" class="rounded" style="width: 60px; height: 42px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 42px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $art->title_fr }}</div>
                                @if($art->title_en)
                                    <small class="text-muted d-block fst-italic">EN: {{ Str::limit($art->title_en, 50) }}</small>
                                @endif
                                @if($art->dossier)
                                    <span class="badge bg-secondary-subtle text-secondary small mt-1">
                                        <i class="bi bi-folder2-open me-1"></i>{{ $art->dossier->title_fr }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $art->category->color ?? '#6c757d' }};">
                                    {{ $art->category->name_fr ?? 'Non classé' }}
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $art->author->name ?? 'Admin' }}</div>
                                <span class="badge bg-light text-dark border small">{{ $art->author->role ?? 'admin' }}</span>
                            </td>
                            <td>
                                @if($art->status === 'published')
                                    <span class="badge bg-success-subtle text-success p-2">
                                        <i class="bi bi-check-circle me-1"></i>Publié
                                    </span>
                                @elseif($art->status === 'pending_review')
                                    <span class="badge bg-warning-subtle text-warning p-2">
                                        <i class="bi bi-hourglass-split me-1"></i>En review
                                    </span>
                                @elseif($art->status === 'draft')
                                    <span class="badge bg-secondary-subtle text-secondary p-2">
                                        <i class="bi bi-file-earmark me-1"></i>Brouillon
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger p-2">
                                        <i class="bi bi-x-circle me-1"></i>Rejeté
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-muted"><i class="bi bi-eye me-1"></i>{{ number_format($art->views_count) }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <!-- Validate button for Admins/SuperAdmin if pending review -->
                                    @if($art->status === 'pending_review' && auth('admin')->user()->canValidateArticles())
                                        <button wire:click="validateArticle({{ $art->id }})" class="btn btn-outline-success" title="Valider et Publier l'article">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    @endif

                                    <a href="{{ route('admin.articles.edit', $art->id) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        onclick="confirmStaticAction('Supprimer l\'article', 'Êtes-vous sûr de vouloir supprimer définitivement \'{{ addslashes($art->title_fr) }}\' ?', function() { @this.call('confirmDelete', {{ $art->id }}); @this.call('deleteArticle'); })"
                                        title="Supprimer"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-newspaper fs-1 d-block mb-2 text-secondary"></i>
                                Aucun article trouvé pour ces critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $articles->links() }}
        </div>
    </div>
</div>
