<div>
    <!-- Header & Statistics -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-black mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-kanban text-primary"></i> Tableaux Kanban &amp; Roadmaps (Lecture Admin)
            </h3>
            <p class="text-muted small mb-0">
                Supervision en lecture seule de tous les tableaux Kanban, sprints agiles et roadmaps publiques créés par les membres de Tech237.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-kanban me-1 text-primary"></i> Total Tableaux : <strong>{{ $totalBoards }}</strong>
            </span>
            <span class="badge bg-info-subtle text-info border border-info border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-card-checklist me-1"></i> Total Tâches : <strong>{{ $totalCards }}</strong>
            </span>
            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-check2-all me-1"></i> Tâches Terminées : <strong>{{ $completedCards }}</strong>
            </span>
            <span class="badge bg-warning-subtle text-dark border border-warning border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-globe me-1 text-warning"></i> Roadmaps Publiques : <strong>{{ $publicBoards }}</strong>
            </span>
        </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="card card-melty shadow-sm mb-4">
        <div class="card-body p-3 bg-light bg-opacity-50">
            <div class="row g-2 align-items-center">
                <!-- Search input -->
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control border-start-0" 
                            placeholder="Rechercher par titre de tableau, projet, auteur (nom ou email)..." 
                            wire:model.live.debounce.300ms="search"
                        >
                        @if(!empty($search))
                            <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" wire:click="$set('search', '')">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Template Filter -->
                <div class="col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="templateFilter">
                        <option value="">Tous les modèles de tableau</option>
                        <option value="standard">Standard (Trello)</option>
                        <option value="scrum">Scrum / Agile Sprint</option>
                        <option value="bug_tracker">Bug &amp; Issue Tracker</option>
                        <option value="product_launch">Lancement Produit / MVP</option>
                    </select>
                </div>

                <!-- Visibility Filter -->
                <div class="col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="visibilityFilter">
                        <option value="">Toutes les visibilités</option>
                        <option value="public">Roadmaps Publiques</option>
                        <option value="private">Tableaux Privés</option>
                    </select>
                </div>

                <!-- Reset button -->
                <div class="col-md-1 text-end">
                    <button 
                        type="button" 
                        wire:click="$set('search', ''); $set('templateFilter', ''); $set('visibilityFilter', '');" 
                        class="btn btn-sm btn-light border w-100" 
                        title="Réinitialiser les filtres"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Boards Table -->
    <div class="card card-melty shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Tableau Kanban</th>
                        <th>Auteur / Propriétaire</th>
                        <th>Projet associé</th>
                        <th>Modèle</th>
                        <th class="text-center">Colonnes</th>
                        <th class="text-center">Tâches</th>
                        <th>Progression</th>
                        <th class="text-center">Visibilité</th>
                        <th>Création</th>
                        <th class="text-end" style="width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($boards as $b)
                        @php
                            $progress = $b->cards_count > 0 ? round(($b->completed_cards_count / $b->cards_count) * 100) : 0;
                            $templateLabels = [
                                'standard' => ['label' => 'Standard', 'class' => 'bg-secondary-subtle text-secondary'],
                                'scrum' => ['label' => 'Scrum Sprint', 'class' => 'bg-primary-subtle text-primary'],
                                'bug_tracker' => ['label' => 'Bug Tracker', 'class' => 'bg-danger-subtle text-danger'],
                                'product_launch' => ['label' => 'Lancement MVP', 'class' => 'bg-success-subtle text-success'],
                            ];
                            $tpl = $templateLabels[$b->template] ?? ['label' => ucfirst($b->template ?: 'Standard'), 'class' => 'bg-light text-dark'];
                        @endphp
                        <tr>
                            <td class="text-muted extra-small">#{{ $b->id }}</td>
                            <td>
                                <a href="{{ route('admin.kanban.show', $b->id) }}" class="fw-bold text-dark text-decoration-none d-block hover-underline">
                                    {{ $b->title }}
                                </a>
                                @if($b->description)
                                    <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                        {{ $b->description }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($b->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-dark fw-bold extra-small" style="width: 30px; height: 30px; overflow: hidden;">
                                            @if($b->user->avatar)
                                                <img src="{{ asset($b->user->avatar) }}" alt="{{ $b->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                {{ strtoupper(substr($b->user->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark extra-small">{{ $b->user->name }}</div>
                                            <div class="text-muted extra-small">{{ $b->user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted extra-small">Utilisateur supprimé</span>
                                @endif
                            </td>
                            <td>
                                @if($b->project)
                                    <span class="badge bg-light text-dark border extra-small fw-semibold d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-folder-check text-primary"></i>
                                        {{ Str::limit($b->project->title, 20) }}
                                    </span>
                                @else
                                    <span class="text-muted extra-small">Indépendant</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $tpl['class'] }} border extra-small">
                                    {{ $tpl['label'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border extra-small fw-bold">
                                    {{ $b->columns->count() }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border extra-small fw-bold">
                                    {{ $b->cards_count }}
                                </span>
                            </td>
                            <td>
                                <div style="min-width: 110px;">
                                    <div class="d-flex justify-content-between extra-small mb-1">
                                        <span class="text-muted">{{ $b->completed_cards_count }}/{{ $b->cards_count }}</span>
                                        <span class="fw-bold {{ $progress === 100 ? 'text-success' : 'text-primary' }}">{{ $progress }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div 
                                            class="progress-bar {{ $progress === 100 ? 'bg-success' : 'bg-primary' }}" 
                                            role="progressbar" 
                                            style="width: {{ $progress }}%;"
                                        ></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($b->is_public)
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill extra-small">
                                        <i class="bi bi-globe me-1"></i>Publique
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill extra-small">
                                        <i class="bi bi-lock-fill me-1"></i>Privé
                                    </span>
                                @endif
                            </td>
                            <td class="extra-small text-muted">
                                {{ $b->created_at ? $b->created_at->format('d/m/Y') : '—' }}
                            </td>
                            <td class="text-end">
                                <a 
                                    href="{{ route('admin.kanban.show', $b->id) }}" 
                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-xs"
                                >
                                    <i class="bi bi-eye-fill"></i>
                                    <span>Consulter</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="bi bi-kanban fs-1 d-block mb-2 opacity-50"></i>
                                <span>Aucun tableau Kanban ne correspond aux critères sélectionnés.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($boards->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="extra-small text-muted">
                    Affichage de {{ $boards->firstItem() }} à {{ $boards->lastItem() }} sur {{ $boards->total() }} tableaux
                </div>
                <div>
                    {{ $boards->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
