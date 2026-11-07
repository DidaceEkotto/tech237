<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Analytics & Trafic Visiteurs</h3>
            <p class="text-muted small mb-0">Suivi des visites en temps réel sur le site public, classement par pays, sessions et historique par IP.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark px-3 py-2 fs-6">
                <i class="bi bi-eye-fill text-danger me-1"></i> {{ number_format($totalVisits, 0, ',', ' ') }} visites enregistrées
            </span>
        </div>
    </div>

    <!-- Period Comparison KPIs -->
    <div class="row g-3 mb-4">
        <!-- Day comparison -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-melty h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Aujourd'hui</span>
                        <h3 class="fw-bold my-1 text-dark">{{ number_format($todayCount, 0, ',', ' ') }}</h3>
                        <small class="text-muted">Hier: {{ number_format($yesterdayCount, 0, ',', ' ') }}</small>
                    </div>
                    <span class="badge {{ $dayDiffPercent >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-6">
                        <i class="bi {{ $dayDiffPercent >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ $dayDiffPercent > 0 ? '+' : '' }}{{ $dayDiffPercent }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Week comparison -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-melty h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Cette Semaine</span>
                        <h3 class="fw-bold my-1 text-dark">{{ number_format($thisWeekCount, 0, ',', ' ') }}</h3>
                        <small class="text-muted">Semaine passée: {{ number_format($lastWeekCount, 0, ',', ' ') }}</small>
                    </div>
                    <span class="badge {{ $weekDiffPercent >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-6">
                        <i class="bi {{ $weekDiffPercent >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ $weekDiffPercent > 0 ? '+' : '' }}{{ $weekDiffPercent }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Month comparison -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-melty h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Ce Mois</span>
                        <h3 class="fw-bold my-1 text-dark">{{ number_format($thisMonthCount, 0, ',', ' ') }}</h3>
                        <small class="text-muted">Mois dernier: {{ number_format($lastMonthCount, 0, ',', ' ') }}</small>
                    </div>
                    <span class="badge {{ $monthDiffPercent >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-6">
                        <i class="bi {{ $monthDiffPercent >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ $monthDiffPercent > 0 ? '+' : '' }}{{ $monthDiffPercent }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Minimum & Average Time -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-melty h-100 p-3 bg-light">
                <span class="text-muted small fw-bold text-uppercase">Temps sur la Plateforme</span>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div>
                        <div class="text-muted small">Temps Minimum :</div>
                        <span class="fw-bold fs-5 text-dark">{{ $minDuration }}s</span>
                    </div>
                    <div class="border-start ps-3">
                        <div class="text-muted small">Temps Moyen :</div>
                        <span class="fw-bold fs-5 text-danger">{{ gmdate('i\m s\s', $avgDuration) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart & Top Countries Row -->
    <div class="row g-4 mb-4">
        <!-- Daily Visits Chart (CSS Bar Chart) -->
        <div class="col-lg-8">
            <div class="card card-melty h-100">
                <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bar-chart-line-fill text-danger me-2"></i>Visites Quotidiennes (14 derniers jours)</span>
                    <span class="badge bg-light text-dark border">Évolution quotidienne</span>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-end justify-content-between gap-2" style="height: 220px; border-bottom: 2px solid #e9ecef;">
                        @foreach($dailyStats as $stat)
                            <div class="d-flex flex-column align-items-center flex-grow-1 h-100 justify-content-end" title="{{ $stat['label'] }}: {{ $stat['count'] }} visites">
                                <span class="small fw-bold text-muted mb-1" style="font-size: 10px;">{{ $stat['count'] }}</span>
                                <div class="w-100 rounded-top" style="height: {{ $stat['height'] }}%; background: linear-gradient(180deg, #FF004B 0%, #ff4b7d 100%); min-height: 4px; transition: height 0.3s ease;"></div>
                                <span class="text-secondary mt-2 text-nowrap" style="font-size: 10px;">{{ $stat['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Countries -->
        <div class="col-lg-4">
            <div class="card card-melty h-100">
                <div class="card-header card-header-melty">
                    <i class="bi bi-globe-americas text-danger me-2"></i>Classement des Pays Visiteurs
                </div>
                <div class="card-body p-3">
                    @forelse($topCountries as $c)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small">
                                    <span class="badge bg-light text-dark border me-1">{{ $c->country_code }}</span>
                                    {{ $c->country }}
                                </span>
                                <span class="small text-muted fw-bold">{{ $c->count }} ({{ $c->percent }}%)</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $c->percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small text-center py-4">Aucune donnée géographique.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- IP Tracking Table -->
    <div class="card card-melty">
        <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
            <span><i class="bi bi-laptop text-danger me-2"></i>Visiteurs Récents (Cliquez sur une IP pour voir l'historique complet)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Adresse IP</th>
                        <th>Localisation</th>
                        <th>Pages Vues</th>
                        <th>Temps Total Passé</th>
                        <th>Dernière Visite</th>
                        <th class="text-end">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visitorIps as $v)
                        <tr wire:click="selectIp('{{ $v->ip_address }}')" style="cursor: pointer;" class="{{ $selectedIp === $v->ip_address ? 'table-warning' : '' }}">
                            <td>
                                <span class="badge bg-dark-subtle text-dark border font-monospace px-2 py-1">
                                    <i class="bi bi-broadcast me-1 text-danger"></i> {{ $v->ip_address }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $v->country }}</span>
                                @if($v->city) <small class="text-muted">({{ $v->city }})</small> @endif
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">{{ $v->total_views }} page(s)</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ gmdate('i\m s\s', $v->total_time) }}</span>
                            </td>
                            <td class="small text-muted">
                                {{ \Carbon\Carbon::parse($v->last_seen)->diffForHumans() }}
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click.stop="selectIp('{{ $v->ip_address }}')">
                                    <i class="bi bi-search me-1"></i> Voir parcours
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Aucune visite enregistrée pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $visitorIps->links() }}
        </div>
    </div>

    <!-- Selected IP Details Modal / Panel -->
    @if($selectedIp)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-diagram-3-fill text-danger me-2"></i>
                            Parcours du visiteur IP: <span class="font-monospace text-warning">{{ $selectedIp }}</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="clearSelectedIp"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-muted small">Temps total passé sur la plateforme :</span>
                                <h4 class="fw-bold mb-0 text-danger">{{ gmdate('i\m s\s', $selectedIpTotalTime ?? 0) }}</h4>
                            </div>
                            <span class="badge bg-secondary px-3 py-2">{{ count($selectedIpLogs) }} pages visitées</span>
                        </div>

                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Chronologie des pages visitées :</h6>
                        <div class="list-group">
                            @foreach($selectedIpLogs as $log)
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div class="me-3">
                                        <div class="fw-semibold text-truncate" style="max-width: 500px;" title="{{ $log->url }}">
                                            <i class="bi bi-link me-1 text-primary"></i> {{ $log->url }}
                                        </div>
                                        <small class="text-muted">
                                            Route: <code>{{ $log->route_name ?: 'direct' }}</code> &bull; 
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-clock me-1 text-danger"></i> {{ $log->duration_seconds }}s
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="clearSelectedIp">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
