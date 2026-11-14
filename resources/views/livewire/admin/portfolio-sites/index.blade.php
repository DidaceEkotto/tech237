<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1">Sites &amp; Portfolios Utilisateurs (Lecture seule)</h3>
            <p class="text-muted small mb-0">Supervision globale des sites créés avec le Studio Website Builder.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="text-muted small">Total Sites</div>
                <div class="fs-3 fw-bold">{{ $summary['total_sites'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="text-muted small">Sites Publiés</div>
                <div class="fs-3 fw-bold text-success">{{ $summary['published_sites'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="text-muted small">Vues Cumulées</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($summary['total_views']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="text-muted small">Visiteurs Uniques</div>
                <div class="fs-3 fw-bold text-info">{{ number_format($summary['total_uniques']) }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Rechercher par nom de site, slug ou utilisateur..." />
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="all">Tous les statuts</option>
                        <option value="published">Publiés</option>
                        <option value="draft">Brouillons</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Site</th>
                            <th>Propriétaire</th>
                            <th>Type</th>
                            <th>Pages</th>
                            <th>Statut</th>
                            <th>Vues / Uniques</th>
                            <th>Lien</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sites as $site)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $site->name }}</div>
                                    <small class="text-muted">/{{ $site->slug }}</small>
                                </td>
                                <td>
                                    <div>{{ $site->user->name ?? '—' }}</div>
                                    <small class="text-muted">@{{ $site->user->username ?? 'n/a' }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $site->site_type }}</span></td>
                                <td>{{ $site->pages_count }}</td>
                                <td>
                                    @if($site->is_published)
                                        <span class="badge bg-success">Publié</span>
                                    @else
                                        <span class="badge bg-secondary">Brouillon</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ number_format($site->views_count) }}</strong>
                                    <small class="text-muted">({{ number_format($site->unique_visitors_count) }} uniques)</small>
                                </td>
                                <td>
                                    @if($site->is_published)
                                        <a href="{{ url('/site/' . $site->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                            Voir
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Aucun site trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sites->links() }}
        </div>
    </div>
</div>
