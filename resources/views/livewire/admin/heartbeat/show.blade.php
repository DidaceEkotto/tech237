<div>
    <!-- Navigation & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="mb-2">
                <a href="{{ route('admin.heartbeat.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la surveillance
                </a>
            </div>
            <h3 class="fw-black mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-activity text-danger"></i> Moniteur : {{ $monitor->name }}
            </h3>
            <div class="d-flex align-items-center gap-2 text-muted small flex-wrap">
                <a href="{{ $monitor->url }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-semibold text-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> {{ $monitor->url }}
                </a>
                <span>&bull;</span>
                <span>Propriétaire : <strong>{{ $monitor->user?->name ?? 'Utilisateur Inconnu' }}</strong> ({{ $monitor->user?->email }})</span>
                @if($monitor->project)
                    <span>&bull;</span>
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-folder2 me-1 text-primary"></i> Projet : {{ $monitor->project->title ?? $monitor->project->name }}
                    </span>
                @endif
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">
                    <i class="bi bi-eye me-1"></i> Mode Lecture Admin
                </span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if(!$monitor->is_active)
                <span class="badge bg-secondary px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-pause-circle me-1"></i> Moniteur En Pause
                </span>
            @elseif($monitor->is_currently_down)
                <span class="badge bg-danger px-3 py-2 rounded-pill fs-6 animate__animated animate__pulse animate__infinite">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> EN PANNE (DOWN)
                </span>
            @else
                <span class="badge bg-success px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-check-circle-fill me-1"></i> EN LIGNE (UP)
                </span>
            @endif
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Disponibilité</span>
                        <div class="bg-success-subtle text-success p-2 rounded-3">
                            <i class="bi bi-shield-check fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-black mb-1 text-dark">
                        {{ number_format($monitor->uptime_percentage ?? 100, 2) }}%
                    </h3>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar {{ ($monitor->uptime_percentage ?? 100) > 98 ? 'bg-success' : (($monitor->uptime_percentage ?? 100) > 90 ? 'bg-warning' : 'bg-danger') }}"
                             role="progressbar"
                             style="width: {{ $monitor->uptime_percentage ?? 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Temps de Réponse</span>
                        <div class="bg-info-subtle text-info p-2 rounded-3">
                            <i class="bi bi-speedometer2 fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-black mb-1 text-dark">
                        {{ $monitor->last_response_time_ms ? $monitor->last_response_time_ms . ' ms' : 'N/A' }}
                    </h3>
                    <p class="text-muted small mb-0">
                        Moyenne globale : <strong class="text-dark">{{ $avgLatency }} ms</strong>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Vérifications</span>
                        <div class="bg-primary-subtle text-primary p-2 rounded-3">
                            <i class="bi bi-check2-all fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-black mb-1 text-dark">
                        {{ number_format($monitor->total_checks) }}
                    </h3>
                    <p class="text-muted small mb-0">
                        <span class="text-success fw-semibold"><i class="bi bi-check-lg"></i> {{ $monitor->successful_checks }}</span>
                        &bull;
                        <span class="text-danger fw-semibold"><i class="bi bi-x-lg"></i> {{ $monitor->failed_checks }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Fréquence de Ping</span>
                        <div class="bg-warning-subtle text-warning p-2 rounded-3">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-black mb-1 text-dark">
                        {{ $monitor->check_interval_minutes }} min
                    </h3>
                    <p class="text-muted small mb-0">
                        Méthode : <span class="badge bg-dark">{{ $monitor->http_method ?? 'GET' }}</span> (Attendu : {{ $monitor->expected_status_code ?? 200 }})
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Diagnostic Details & Alerts Section -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card card-melty shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle text-primary"></i> Configuration &amp; Santé du Service
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Dernier test exécuté :</span>
                            <span class="fw-semibold text-dark">
                                {{ $monitor->last_checked_at ? $monitor->last_checked_at->format('d/m/Y H:i:s') . ' (' . $monitor->last_checked_at->diffForHumans() . ')' : 'Jamais' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Prochain test prévu :</span>
                            <span class="fw-semibold text-dark">
                                {{ $monitor->next_check_at ? $monitor->next_check_at->format('d/m/Y H:i:s') . ' (' . $monitor->next_check_at->diffForHumans() . ')' : 'Immédiat' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Dernier statut HTTP :</span>
                            <span>
                                @if($monitor->last_status_code)
                                    <span class="badge {{ $monitor->last_status_code >= 200 && $monitor->last_status_code < 400 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $monitor->last_status_code }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Aucun</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Délai d'expiration (Timeout) :</span>
                            <span class="fw-semibold text-dark">{{ $monitor->timeout_seconds ?? 10 }} secondes</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Statut public (Roadmap / Showcase) :</span>
                            <span>
                                @if($monitor->is_public)
                                    <span class="badge bg-info-subtle text-info border"><i class="bi bi-globe me-1"></i> Public</span>
                                @else
                                    <span class="badge bg-light text-muted border"><i class="bi bi-lock me-1"></i> Privé</span>
                                @endif
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-melty shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-bell text-warning"></i> Alertes Email &amp; Historique des Pannes
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Notifications activées :</span>
                            <span>
                                @if($monitor->notify_email)
                                    <span class="badge bg-success-subtle text-success border"><i class="bi bi-check-circle me-1"></i> Activées</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-muted border"><i class="bi bi-dash-circle me-1"></i> Désactivées</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Destinataire d'alerte :</span>
                            <span class="fw-semibold text-dark">
                                {{ $monitor->effective_email ?: 'Aucun email défini' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Dernière alerte envoyée :</span>
                            <span class="fw-semibold text-dark">
                                {{ $monitor->down_alert_sent_at ? $monitor->down_alert_sent_at->format('d/m/Y H:i:s') : 'Aucune' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Dernier incident enregistré :</span>
                            <span class="text-danger fw-semibold">
                                {{ $monitor->last_down_at ? $monitor->last_down_at->format('d/m/Y H:i:s') : 'Aucun incident' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Dernier rétablissement :</span>
                            <span class="text-success fw-semibold">
                                {{ $monitor->last_up_at ? $monitor->last_up_at->format('d/m/Y H:i:s') : 'Stable' }}
                            </span>
                        </li>
                    </ul>

                    @if($monitor->last_error_message)
                        <div class="alert alert-danger-subtle border border-danger border-opacity-25 rounded-3 mt-3 mb-0 p-2 small">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                            <strong>Dernière erreur relevée :</strong>
                            <div class="font-monospace mt-1 text-danger-emphasis">{{ $monitor->last_error_message }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Logs & Ping Checks History -->
    <div class="card card-melty shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-secondary"></i> Journal des Tests de Disponibilité
                </h5>
                <span class="text-muted small">Historique chronologique des sondes envoyées par le planificateur</span>
            </div>

            <!-- Filter tabs -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" wire:click="$set('statusFilter', 'all')" class="btn {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Tous ({{ $monitor->total_checks }})
                </button>
                <button type="button" wire:click="$set('statusFilter', 'up')" class="btn {{ $statusFilter === 'up' ? 'btn-success' : 'btn-outline-secondary' }}">
                    <i class="bi bi-check-circle me-1"></i> Succès ({{ $monitor->successful_checks }})
                </button>
                <button type="button" wire:click="$set('statusFilter', 'down')" class="btn {{ $statusFilter === 'down' ? 'btn-danger' : 'btn-outline-secondary' }}">
                    <i class="bi bi-x-circle me-1"></i> Échecs ({{ $monitor->failed_checks }})
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 py-3" style="width: 180px;">Date &amp; Heure</th>
                            <th style="width: 120px;">Statut</th>
                            <th style="width: 120px;">Code HTTP</th>
                            <th style="width: 150px;">Latence</th>
                            <th style="width: 150px;">IP Serveur</th>
                            <th>Détails &amp; Message d'erreur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checks as $check)
                            <tr>
                                <td class="ps-3 py-3 text-nowrap">
                                    <div class="fw-semibold text-dark">
                                        {{ $check->checked_at ? $check->checked_at->format('d/m/Y H:i:s') : 'N/A' }}
                                    </div>
                                    <span class="text-muted small">
                                        {{ $check->checked_at ? $check->checked_at->diffForHumans() : '' }}
                                    </span>
                                </td>
                                <td>
                                    @if($check->is_up)
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-check-circle-fill me-1"></i> UP
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-exclamation-octagon-fill me-1"></i> DOWN
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($check->status_code)
                                        <span class="badge {{ $check->status_code >= 200 && $check->status_code < 400 ? 'bg-light text-success border border-success' : 'bg-light text-danger border border-danger' }} font-monospace px-2 py-1">
                                            {{ $check->status_code }}
                                        </span>
                                    @else
                                        <span class="text-muted small">0 (N/A)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($check->response_time_ms)
                                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                            {{ $check->response_time_ms }} ms
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-muted small font-monospace">
                                    {{ $check->ip_address ?: 'Inconnue' }}
                                </td>
                                <td>
                                    @if($check->error_message)
                                        <span class="text-danger small font-monospace">
                                            <i class="bi bi-exclamation-triangle me-1"></i> {{ $check->error_message }}
                                        </span>
                                    @elseif($check->is_up)
                                        <span class="text-success small">
                                            <i class="bi bi-check2 text-success me-1"></i> Réponse HTTP valide reçue
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-clock-history text-muted fs-1 mb-2 d-block"></i>
                                        <h6 class="text-dark fw-bold">Aucune vérification enregistrée</h6>
                                        <p class="text-muted small mb-0">Ce moniteur n'a pas encore de sondes correspondant aux filtres sélectionnés.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($checks->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    Affichage de {{ $checks->firstItem() }} à {{ $checks->lastItem() }} sur {{ $checks->total() }} vérifications
                </span>
                <div>
                    {{ $checks->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
