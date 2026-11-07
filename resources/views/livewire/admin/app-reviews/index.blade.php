<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Tests d'Applications</h3>
            <p class="text-muted small mb-0">Bancs d'essai d'applications mobiles, web et desktop avec notation UI, UX, prise en main et note finale sur 20.</p>
        </div>
        <a href="{{ route('admin.app-reviews.create') }}" class="btn btn-melty shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Nouveau Test d'Application
        </a>
    </div>

    <!-- Search Bar -->
    <div class="card card-melty mb-4">
        <div class="card-body p-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Rechercher une application testée...">
            </div>
        </div>
    </div>

    <!-- App Reviews Table -->
    <div class="card card-melty">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">Logo / App</th>
                        <th>Nom de l'application</th>
                        <th>Store / Catégorie</th>
                        <th>Notes Détails (UI/UX/Prise en main)</th>
                        <th>Note Finale</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $r)
                        <tr>
                            <td>
                                @if($r->cover_image)
                                    <img src="{{ asset($r->cover_image) }}" class="rounded shadow-sm" style="width: 48px; height: 48px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                        <i class="bi bi-app fs-4"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $r->app_name }}</div>
                                <div class="small text-muted">Auteur: {{ $r->author->name ?? 'Admin' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi {{ $r->category_logo ?: 'bi-shop' }} me-1"></i>{{ $r->category_name }}
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-muted">UI:</span> <strong>{{ $r->score_ui }}/20</strong> |
                                    <span class="text-muted">UX:</span> <strong>{{ $r->score_ux }}/20</strong> |
                                    <span class="text-muted">Prise en main:</span> <strong>{{ $r->score_handling }}/20</strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger fs-6 px-3 py-2 rounded-pill">
                                    {{ $r->final_score }} / 20
                                </span>
                            </td>
                            <td>
                                @if($r->status === 'published')
                                    <span class="badge bg-success-subtle text-success">Publié</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Brouillon</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.app-reviews.edit', $r->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-danger" 
                                    onclick="confirmStaticAction('Supprimer le test', 'Supprimer définitivement le test de \'{{ addslashes($r->app_name) }}\' ?', function() { @this.call('delete', {{ $r->id }}); })"
                                    title="Supprimer"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-phone fs-1 d-block mb-2 text-secondary"></i>
                                Aucun test d'application enregistré.
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
