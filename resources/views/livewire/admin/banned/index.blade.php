<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger">Réservé Super Admin</span>
                <h3 class="fw-bold mb-0">Admins Bannis & Rétablissement des Comptes</h3>
            </div>
            <p class="text-muted small mb-0">
                Cet espace est strictement réservé au Super Administrateur. Seul cet écran permet de lever les bannissements et rétablir l'accès des comptes bloqués après plus de 5 tentatives infructueuses ou décision disciplinaire.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-melty border-danger">
        <div class="card-header bg-danger-subtle text-danger fw-bold d-flex align-items-center">
            <i class="bi bi-shield-slash fs-5 me-2"></i>Liste des Administrateurs Actuellement Bannis
            <span class="badge bg-danger ms-auto">{{ $bannedAdmins->count() }} compte(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Administrateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Tentatives erronées</th>
                        <th>Date de bannissement</th>
                        <th class="text-end">Rétablissement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bannedAdmins as $b)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $b->name }}</div>
                            </td>
                            <td>
                                <code>{{ $b->email }}</code>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $b->role }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger px-2 py-1">
                                    {{ $b->failed_login_attempts }} échec(s)
                                </span>
                            </td>
                            <td>
                                <span class="small text-muted">
                                    <i class="bi bi-calendar-x me-1"></i>
                                    {{ $b->banned_at ? $b->banned_at->format('d/m/Y H:i') : 'Récemment' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button 
                                    type="button" 
                                    class="btn btn-success btn-sm rounded-pill px-3 fw-bold"
                                    onclick="confirmStaticAction('Rétablir l\'administrateur', 'Rétablir l\'accès au compte pour {{ addslashes($b->name) }} ({{ addslashes($b->email) }}) ? Le compteur d\'échecs sera réinitialisé à 0.', function() { @this.call('restoreAdmin', {{ $b->id }}); }, false)"
                                >
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Rétablir le compte
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-check fs-1 d-block mb-2 text-success"></i>
                                Aucun compte administrateur n'est actuellement banni. Le système est au vert.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
