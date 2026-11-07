<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Entrepreneur du mois</h3>
            <p class="text-muted small mb-0">Mettez en lumière les figures tech d'Afrique et du Cameroun, leur parcours, leurs difficultés et leurs conseils.</p>
        </div>
        <a href="{{ route('admin.entrepreneurs.create') }}" class="btn btn-melty">
            <i class="bi bi-plus-circle me-1"></i> Nouveau portrait
        </a>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-melty">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">Photo</th>
                        <th>Nom & Titre</th>
                        <th>Startup liée</th>
                        <th>Mois / Édition</th>
                        <th>À la une</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entrepreneurs as $e)
                        <tr>
                            <td>
                                @if($e->photo)
                                    <img src="{{ asset($e->photo) }}" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-warning-subtle text-warning fw-bold d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        {{ strtoupper(substr($e->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $e->name }}</div>
                                <small class="text-muted">{{ $e->role_or_title }}</small>
                            </td>
                            <td>
                                @if($e->startup)
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-briefcase text-primary me-1"></i>{{ $e->startup->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">Aucune startup liée</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger">{{ $e->month_year }}</span>
                            </td>
                            <td>
                                <button type="button" wire:click="toggleCurrent({{ $e->id }})" class="btn btn-sm {{ $e->is_current_month ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3">
                                    <i class="bi {{ $e->is_current_month ? 'bi-check-circle-fill' : 'bi-circle' }} me-1"></i>
                                    {{ $e->is_current_month ? 'En vedette' : 'Mettre en vedette' }}
                                </button>
                            </td>
                            <td>
                                @if($e->is_published)
                                    <span class="badge bg-success">Publié</span>
                                @else
                                    <span class="badge bg-secondary">Brouillon</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.entrepreneurs.edit', $e->id) }}" class="btn btn-sm btn-light border text-primary me-1" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" wire:click="delete({{ $e->id }})" class="btn btn-sm btn-light border text-danger" title="Supprimer" onclick="return confirm('Supprimer ce portrait ?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-badge fs-1 d-block mb-2 text-warning"></i>
                                Aucun portrait d'entrepreneur créé pour l'instant.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
