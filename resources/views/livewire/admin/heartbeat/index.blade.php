<div>
    <!-- Header & Statistics -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-black mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-activity text-danger"></i> Surveillance Uptime &amp; Heartbeat (Lecture Admin)
            </h3>
            <p class="text-muted small mb-0">
                Supervision de l'état de santé, des pings réguliers et des alertes de panne des sites web et POCs hébergés par les membres.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-hdd-network me-1 text-primary"></i> Sites surveillés : <strong>{{ $totalMonitors }}</strong>
            </span>
            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-check-circle-fill me-1"></i> En Ligne (UP) : <strong>{{ $upCount }}</strong>
            </span>
            @if($downCount > 0)
                <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-bold shadow-xs">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> En Panne (DOWN) : <strong>{{ $downCount }}</strong>
                </span>
            @endif
            <span class="badge bg-info-subtle text-info border border-info border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-speedometer2 me-1"></i> Latence Moyenne : <strong>{{ $avgResponseTime }} ms</strong>
            </span>
            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-shield-check me-1"></i> Uptime Global : <strong>{{ $avgUptime }}%</strong>
            </span>
        </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="card card-melty shadow-sm mb-4">
        <div class="card-body p-3 bg-light bg-opacity-50">
            <div class="row g-2 align-items-center">
                <!-- Search -->
                <div class="col-md-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control border-start-0" 
                            placeholder="Rechercher par nom du site, URL (https://...), membre ou email..." 
                            wire:model.live.debounce.300ms="search"
                        >
                        @if(!empty($search))
                            <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" wire:click="$set('search', '')">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-4">
                    <select class="form-select form-select-sm" wire:model.live="statusFilter">
                        <option value="">Tous les statuts de santé</option>
                        <option value="up">🟢 En ligne (Opérationnel)</option>
                        <option value="down">🔴 Hors ligne (En panne)</option>
                        <option value="pending">🟡 En attente du premier ping</option>
                    </select>
                </div>

                <!-- Reset -->
                <div class="col-md-1 text-end">
                    <button 
                        type="button" 
                        wire:click="$set('search', ''); $set('statusFilter', '');" 
                        class="btn btn-sm btn-light border w-100" 
                        title="Réinitialiser"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitors Table -->
    <div class="card card-melty shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Site Web / Projet</th>
                        <th>Propriétaire</th>
                        <th class="text-center">Santé Uptime</th>
                        <th class="text-center">Temps Réponse</th>
                        <th class="text-center">Taux Uptime</th>
                        <th class="text-center">Intervalle</th>
                        <th>Alertes Email</th>
                        <th>Dernier Ping</th>
                        <th class="text-end" style="width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monitors as $m)
                        @php
                            $isDown = $m->is_currently_down || $m->status === 'down';
                            $isUp = $m->status === 'up' && !$m->is_currently_down;
                        @endphp
                        <tr class="{{ $isDown ? 'table-danger' : '' }}">
                            <td class="text-muted extra-small">#{{ $m->id }}</td>
                            <td>
                                <div class="fw-bold text-dark small d-flex align-items-center gap-1">
                                    <span class="d-inline-block rounded-circle {{ $isUp ? 'bg-success' : ($isDown ? 'bg-danger' : 'bg-secondary') }}" style="width: 8px; height: 8px;"></span>
                                    <span>{{ $m->name }}</span>
                                </div>
                                <div class="extra-small">
                                    <a href="{{ $m->url }}" target="_blank" class="text-decoration-none text-muted font-monospace hover-underline">
                                        {{ Str::limit($m->url, 35) }} <i class="bi bi-box-arrow-up-right extra-small"></i>
                                    </a>
                                </div>
                            </td>
                            <td>
                                @if($m->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-dark fw-bold extra-small" style="width: 26px; height: 26px; overflow: hidden;">
                                            @if($m->user->avatar)
                                                <img src="{{ asset($m->user->avatar) }}" alt="{{ $m->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                {{ strtoupper(substr($m->user->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark extra-small">{{ $m->user->name }}</div>
                                            <div class="text-muted extra-small">{{ $m->user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted extra-small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isUp)
                                    <span class="badge bg-success-subtle text-success border border-success rounded-pill extra-small fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i>En Ligne ({{ $m->last_status_code ?: 200 }})
                                    </span>
                                @elseif($isDown)
                                    <span class="badge bg-danger text-white rounded-pill extra-small fw-bold animate-pulse">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>En Panne ({{ $m->last_status_code ?: 'Err' }})
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill extra-small">
                                        En attente
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($m->last_response_time_ms)
                                    <span class="badge bg-light text-dark border extra-small fw-semibold">
                                        {{ $m->last_response_time_ms }} ms
                                    </span>
                                @else
                                    <span class="text-muted extra-small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $m->uptime_percentage >= 99 ? 'bg-success-subtle text-success' : ($m->uptime_percentage >= 95 ? 'bg-warning-subtle text-dark' : 'bg-danger-subtle text-danger') }} border extra-small fw-bold">
                                    {{ $m->total_checks > 0 ? $m->uptime_percentage . '%' : '100%' }}
                                </span>
                            </td>
                            <td class="text-center extra-small text-muted">
                                {{ $m->check_interval_minutes }} min
                            </td>
                            <td class="extra-small">
                                @if($m->notify_email)
                                    <span class="badge bg-light text-dark border extra-small" title="Destinataire : {{ $m->email_recipient ?: ($m->user->email ?? '') }}">
                                        <i class="bi bi-envelope-check text-success me-1"></i>{{ Str::limit($m->email_recipient ?: ($m->user->email ?? 'Actif'), 20) }}
                                    </span>
                                @else
                                    <span class="text-muted">Désactivé</span>
                                @endif
                            </td>
                            <td class="extra-small text-muted">
                                {{ $m->last_checked_at ? $m->last_checked_at->diffForHumans() : 'Jamais' }}
                            </td>
                            <td class="text-end">
                                <a 
                                    href="{{ route('admin.heartbeat.show', $m->id) }}" 
                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-xs"
                                >
                                    <i class="bi bi-graph-up"></i>
                                    <span>Logs &amp; Santé</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-activity fs-1 d-block mb-2 opacity-50"></i>
                                <span>Aucun site ou projet sous surveillance Heartbeat ne correspond à votre recherche.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($monitors->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="extra-small text-muted">
                    Affichage de {{ $monitors->firstItem() }} à {{ $monitors->lastItem() }} sur {{ $monitors->total() }} sites
                </div>
                <div>
                    {{ $monitors->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
