<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-arrow-up-circle-fill text-success me-2"></i>Product Hunt Tech237 - Upvotes des Projets</h4>
            <p class="text-muted small mb-0">Classement en temps réel des projets tech les plus soutenus par la communauté camerounaise.</p>
        </div>
        <div class="d-flex gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm rounded-pill px-3" placeholder="Rechercher un projet..." style="min-width: 220px;">
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <span class="text-muted small fw-bold text-uppercase">Total Votes Enregistrés</span>
                <h3 class="fw-black text-success my-1">{{ number_format($totalUpvotes) }}</h3>
                <small class="text-muted">Soutiens communautaires cumulés</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">🏆 Projet #1 en tête du classement</span>
                    <h5 class="fw-bold text-dark my-1">{{ $topProject?->name ?? 'Aucun projet' }}</h5>
                    <small class="text-success fw-bold"><i class="bi bi-arrow-up-circle me-1"></i>{{ $topProject?->upvotes_count ?? 0 }} votes</small>
                </div>
                @if($topProject)
                    <a href="{{ route('projects.show', $topProject->slug) }}" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                        Voir la fiche <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Projects Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Rang</th>
                        <th>Projet Tech</th>
                        <th>Porteur / Créateur</th>
                        <th>Vues</th>
                        <th>Upvotes (Votes)</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $index => $project)
                        <tr>
                            <td class="ps-4">
                                <span class="badge {{ $index === 0 ? 'bg-warning text-dark' : ($index === 1 ? 'bg-secondary text-white' : ($index === 2 ? 'bg-danger-subtle text-danger' : 'bg-light text-muted border')) }} rounded-circle p-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800;">
                                    #{{ $projects->firstItem() + $index }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($project->logo)
                                        <img src="{{ str_starts_with($project->logo, '/') ? $project->logo : '/' . $project->logo }}" class="rounded-3 border" style="width: 38px; height: 38px; object-fit: contain;">
                                    @else
                                        <div class="rounded-3 bg-light border text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            {{ strtoupper(substr($project->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong class="d-block text-dark">{{ $project->name }}</strong>
                                        <small class="text-muted">{{ strtoupper($project->project_type) }} @if($project->startup) &bull; {{ $project->startup->name }} @endif</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="text-dark fw-bold d-block">{{ $project->user?->name ?? 'Anonyme' }}</small>
                                <small class="text-muted">{{ $project->user?->email }}</small>
                            </td>
                            <td>
                                <span class="small text-muted"><i class="bi bi-eye me-1"></i>{{ $project->views_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-6 fw-bold">
                                    <i class="bi bi-arrow-up-circle-fill me-1"></i>{{ $project->upvotes_count }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('projects.show', $project->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1">
                                    <i class="bi bi-eye"></i> Fiche
                                </a>
                                <button wire:click="resetProjectUpvotes({{ $project->id }})" wire:confirm="Êtes-vous sûr de vouloir réinitialiser les votes de ce projet à 0 ?" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Réinitialiser les votes">
                                    <i class="bi bi-arrow-counterclockwise"></i> Remettre à 0
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Aucun projet tech trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($projects->hasPages())
            <div class="p-3 border-top">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>
