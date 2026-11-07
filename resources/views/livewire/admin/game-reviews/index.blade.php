<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Tests Jeux Vidéo</h3>
            <p class="text-muted small mb-0">Évaluations gaming : gameplay, graphismes, accessibilité, bande sonore, plateformes, points forts & faibles.</p>
        </div>
        <a href="{{ route('admin.game-reviews.create') }}" class="btn btn-melty shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Nouveau Test de Jeu Vidéo
        </a>
    </div>

    <!-- Filters -->
    <div class="card card-melty mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Rechercher un jeu vidéo testé...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="platformFilter" class="form-select">
                        <option value="">Toutes les plateformes</option>
                        <option value="PlayStation 5">PlayStation 5</option>
                        <option value="PlayStation 4">PlayStation 4</option>
                        <option value="Xbox Series S & X">Xbox Series S & X</option>
                        <option value="Xbox One">Xbox One</option>
                        <option value="Nintendo Switch">Nintendo Switch</option>
                        <option value="Nintendo Switch 2">Nintendo Switch 2</option>
                        <option value="PC">PC</option>
                        <option value="Linux ou Mac">Linux ou Mac</option>
                        <option value="Mobile">Mobile (iOS / Android)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Game Reviews Table -->
    <div class="card card-melty">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">Cover</th>
                        <th>Jeu</th>
                        <th>Plateforme</th>
                        <th>Scores (Gameplay, Graphismes, Access., Audio)</th>
                        <th>Note Finale</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $gr)
                        <tr>
                            <td>
                                @if($gr->cover_image)
                                    <img src="{{ asset($gr->cover_image) }}" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                        <i class="bi bi-controller fs-4"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $gr->game_name }}</div>
                                <small class="text-muted">Par {{ $gr->author->name ?? 'Admin' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi {{ $gr->platform_logo ?: 'bi-controller' }} me-1"></i>{{ $gr->platform_category }}
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-muted">Gameplay:</span> <strong>{{ $gr->score_gameplay }}/20</strong> |
                                    <span class="text-muted">Graphismes:</span> <strong>{{ $gr->score_graphics }}/20</strong><br>
                                    <span class="text-muted">Access.:</span> <strong>{{ $gr->score_accessibility }}/20</strong> |
                                    <span class="text-muted">Audio:</span> <strong>{{ $gr->score_soundtrack }}/20</strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger fs-6 px-3 py-2 rounded-pill">
                                    {{ $gr->final_score }} / 20
                                </span>
                            </td>
                            <td>
                                @if($gr->status === 'published')
                                    <span class="badge bg-success-subtle text-success">Publié</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Brouillon</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.game-reviews.edit', $gr->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-danger" 
                                    onclick="confirmStaticAction('Supprimer le test jeu vidéo', 'Supprimer définitivement le test de \'{{ addslashes($gr->game_name) }}\' ?', function() { @this.call('delete', {{ $gr->id }}); })"
                                    title="Supprimer"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-controller fs-1 d-block mb-2 text-secondary"></i>
                                Aucun test de jeu vidéo trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
