<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Tableau de bord</h3>
            <p class="text-muted small mb-0">Bienvenue sur le centre de contrôle éditorial et opérationnel de Melty.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.articles.create') }}" class="btn btn-melty shadow-sm">
                <i class="bi bi-pencil-square me-1"></i> Rédiger un article
            </a>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i> Paramètres
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold">Articles Publiés</span>
                        <h3 class="fw-bold my-1 text-dark">{{ $stats['articles_published'] }}</h3>
                        <small class="text-secondary">{{ $stats['articles_count'] }} articles au total</small>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-newspaper"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold">En Attente de Review</span>
                        <h3 class="fw-bold my-1 text-warning">{{ $stats['articles_pending'] }}</h3>
                        <small class="text-secondary">Validation hiérarchique</small>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold">Startups & Projets</span>
                        <h3 class="fw-bold my-1 text-primary">{{ $stats['startups_count'] }} / {{ $stats['projects_count'] }}</h3>
                        <small class="text-secondary">Écosystème OHADA & Tech</small>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-briefcase"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold">Commentaires en Attente</span>
                        <h3 class="fw-bold my-1 {{ $stats['pending_comments_count'] > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $stats['pending_comments_count'] }}
                        </h3>
                        <small class="text-secondary">Modération communautaire</small>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-chat-square-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card card-melty h-100">
                <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-graph-up text-danger me-2"></i>Fréquentation du Site (Derniers 7 jours)</span>
                    <a href="{{ route('admin.analytics.index') }}" class="btn btn-sm btn-link text-decoration-none">Détails Analytics</a>
                </div>
                <div class="card-body p-3">
                    <canvas id="trafficTrendChart" style="width: 100%; height: 260px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-melty h-100">
                <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Articles par Catégorie</span>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-link text-decoration-none">Catégories</a>
                </div>
                <div class="card-body p-3">
                    <canvas id="categoriesBarChart" style="width: 100%; height: 260px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="row g-4">
        <!-- Recent Articles -->
        <div class="col-lg-8">
            <div class="card card-melty h-100">
                <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-newspaper text-danger me-2"></i>Derniers Articles Rédigés</span>
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-sm btn-link text-decoration-none">Voir tout</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Titre</th>
                                <th>Catégorie</th>
                                <th>Auteur</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentArticles as $art)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-truncate" style="max-width: 280px;" title="{{ $art->title_fr }}">
                                            {{ $art->title_fr }}
                                        </div>
                                        <small class="text-muted">{{ $art->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $art->category->color ?? '#6c757d' }};">
                                            {{ $art->category->name_fr ?? 'Général' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small">{{ $art->author->name ?? 'Admin' }}</span>
                                    </td>
                                    <td>
                                        @if($art->status === 'published')
                                            <span class="badge bg-success-subtle text-success">Publié</span>
                                        @elseif($art->status === 'pending_review')
                                            <span class="badge bg-warning-subtle text-warning">En review</span>
                                        @elseif($art->status === 'draft')
                                            <span class="badge bg-secondary-subtle text-secondary">Brouillon</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Rejeté</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.articles.edit', $art->id) }}" class="btn btn-sm btn-light border" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucun article enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Activity Logs -->
        <div class="col-lg-4">
            <div class="card card-melty h-100">
                <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-shield-check text-success me-2"></i>Journal d'Audit Récent</span>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-sm btn-link text-decoration-none">Tous les logs</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentLogs as $log)
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-light text-dark border">{{ $log->action }}</span>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </div>
                                <div class="small fw-semibold text-truncate">{{ $log->description ?? $log->url }}</div>
                                <div class="small text-muted d-flex justify-content-between mt-1">
                                    <span>{{ $log->admin_email ?? 'Système' }}</span>
                                    <code>{{ $log->ip_address }}</code>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">Aucune activité enregistrée.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const visitLabels = @json($visitLabels);
        const visitCounts = @json($visitCounts);
        const catLabels = @json($catLabels);
        const catCounts = @json($catCounts);
        const catColors = @json($catColors);

        if (window.MeltyChart) {
            MeltyChart.drawLineChart('trafficTrendChart', visitLabels, visitCounts, {
                strokeColor: '#E11D48',
                fillColor: 'rgba(225, 29, 72, 0.15)'
            });

            MeltyChart.drawBarChart('categoriesBarChart', catLabels, catCounts, catColors);

            window.addEventListener('resize', function() {
                MeltyChart.drawLineChart('trafficTrendChart', visitLabels, visitCounts);
                MeltyChart.drawBarChart('categoriesBarChart', catLabels, catCounts, catColors);
            });
        }
    });
</script>
@endpush
