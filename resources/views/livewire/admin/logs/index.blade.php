<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Journaux d'Activité / Logs Système</h3>
            <p class="text-muted small mb-0">Traçabilité complète des actions : moments de connexion, pages visitées, créations, modifications, IP et user-agents.</p>
        </div>
        @if(auth('admin')->user()->isSuperAdmin())
            <button 
                type="button" 
                class="btn btn-outline-secondary btn-sm"
                onclick="confirmStaticAction('Purger les vieux logs', 'Supprimer les journaux d\'audit de plus de 60 jours ?', function() { @this.call('clearOldLogs'); })"
            >
                <i class="bi bi-eraser me-1"></i> Purger les anciens logs (>60 jours)
            </button>
        @endif
    </div>

    <!-- Filters -->
    <div class="card card-melty mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Rechercher par action, email admin, IP, URL...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="actionFilter" class="form-select">
                        <option value="">Toutes les actions</option>
                        <option value="login">Connexions (login)</option>
                        <option value="login_failed">Échecs de connexion (login_failed)</option>
                        <option value="account_auto_banned">Bannissements automatiques</option>
                        <option value="visit_page">Visites de pages (visit_page)</option>
                        <option value="article_created">Création d'articles</option>
                        <option value="article_updated">Modification d'articles</option>
                        <option value="article_validated">Validation d'articles</option>
                        <option value="article_deleted">Suppression d'articles</option>
                        <option value="settings_updated">Mise à jour des paramètres</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card card-melty">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 font-monospace small">
                <thead class="table-light">
                    <tr>
                        <th style="width: 130px;">Date & Heure</th>
                        <th>Action</th>
                        <th>Administrateur</th>
                        <th>Description / URL</th>
                        <th>Méthode</th>
                        <th>Adresse IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $l)
                        <tr>
                            <td class="text-nowrap text-secondary">
                                {{ $l->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td>
                                @if(str_contains($l->action, 'banned') || str_contains($l->action, 'failed') || str_contains($l->action, 'deleted'))
                                    <span class="badge bg-danger-subtle text-danger">{{ $l->action }}</span>
                                @elseif(str_contains($l->action, 'login') || str_contains($l->action, 'validated') || str_contains($l->action, 'created'))
                                    <span class="badge bg-success-subtle text-success">{{ $l->action }}</span>
                                @elseif(str_contains($l->action, 'visit'))
                                    <span class="badge bg-light text-secondary border">{{ $l->action }}</span>
                                @else
                                    <span class="badge bg-info-subtle text-info">{{ $l->action }}</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ $l->admin_email ?? ($l->admin->email ?? 'Système / Anonyme') }}</strong>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 320px;" title="{{ $l->description ?: $l->url }}">
                                    {{ $l->description ?: $l->url }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-dark">{{ $l->method }}</span>
                            </td>
                            <td>
                                <code class="text-primary">{{ $l->ip_address }}</code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-1 d-block mb-2 text-secondary"></i>
                                Aucun log trouvé pour cette recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
