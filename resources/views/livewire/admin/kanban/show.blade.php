<div>
    <!-- Top Bar Navigation & Info -->
    <div class="card card-melty shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('admin.kanban.index') }}" class="btn btn-sm btn-light rounded-circle p-2 text-muted shadow-xs" title="Retour aux tableaux">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-black mb-0 text-dark">{{ $board->title }}</h4>
                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 extra-small">
                                Modèle : {{ ucfirst(str_replace('_', ' ', $board->template ?: 'standard')) }}
                            </span>
                            @if($board->is_public)
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill extra-small">
                                    <i class="bi bi-globe me-1"></i>Roadmap Publique
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill extra-small">
                                    <i class="bi bi-lock-fill me-1"></i>Privé
                                </span>
                            @endif
                            <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill extra-small fw-bold">
                                <i class="bi bi-eye-fill me-1"></i>Mode Lecture Seule
                            </span>
                        </div>
                        <div class="extra-small text-muted mt-1 d-flex align-items-center gap-2 flex-wrap">
                            @if($board->project)
                                <span>Projet : <strong>{{ $board->project->title }}</strong></span>
                                <span>•</span>
                            @endif
                            @if($board->user)
                                <span>Créé par : <strong>{{ $board->user->name }}</strong> ({{ $board->user->email }})</span>
                                <span>•</span>
                            @endif
                            <span>Le {{ $board->created_at ? $board->created_at->format('d/m/Y à H:i') : '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- View Switcher -->
                <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border shadow-xs" role="group">
                    <button 
                        type="button" 
                        class="btn rounded-pill px-3 fw-bold {{ $viewMode === 'board' ? 'btn-dark text-white' : 'btn-light text-muted' }}" 
                        wire:click="$set('viewMode', 'board')"
                    >
                        <i class="bi bi-kanban me-1"></i> Tableau
                    </button>
                    <button 
                        type="button" 
                        class="btn rounded-pill px-3 fw-bold {{ $viewMode === 'list' ? 'btn-dark text-white' : 'btn-light text-muted' }}" 
                        wire:click="$set('viewMode', 'list')"
                    >
                        <i class="bi bi-list-task me-1"></i> Liste ({{ $totalCards }})
                    </button>
                </div>
            </div>

            @if($board->description)
                <p class="text-muted small mb-3 border-top pt-3">
                    {{ $board->description }}
                </p>
            @endif

            <!-- Quick Stats -->
            @php
                $progress = $totalCards > 0 ? round(($completedCards / $totalCards) * 100) : 0;
            @endphp
            <div class="row g-2 pt-3 border-top align-items-center">
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded-3 text-center border">
                        <span class="text-muted extra-small d-block">Tâches totales</span>
                        <strong class="fs-6 text-dark">{{ $totalCards }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded-3 text-center border">
                        <span class="text-muted extra-small d-block">Tâches terminées</span>
                        <strong class="fs-6 text-success">{{ $completedCards }} ({{ $progress }}%)</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded-3 text-center border">
                        <span class="text-muted extra-small d-block">Temps estimé</span>
                        <strong class="fs-6 text-info">{{ $totalHoursEstimated }} h</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded-3 text-center border">
                        <span class="text-muted extra-small d-block">Temps passé</span>
                        <strong class="fs-6 text-primary">{{ $totalHoursSpent }} h</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW 1: KANBAN COLUMNS (BOARD) -->
    @if($viewMode === 'board')
        <div class="kanban-board-wrapper pb-4" style="overflow-x: auto;">
            <div class="d-flex align-items-start gap-3" style="min-width: 100%; width: max-content;">
                @forelse($board->columns as $col)
                    <div class="kanban-col-card bg-light rounded-4 border p-3 shadow-xs" style="width: 320px; min-width: 320px;">
                        <!-- Column Header -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: {{ $col->color ?: '#0B2559' }};"></span>
                                <h6 class="fw-bold mb-0 text-dark small">{{ $col->title }}</h6>
                            </div>
                            <span class="badge bg-white text-dark border rounded-pill px-2 py-1 extra-small fw-bold">
                                {{ $col->cards->count() }}
                            </span>
                        </div>

                        <!-- Cards in Column -->
                        <div class="d-flex flex-column gap-2" style="min-height: 100px;">
                            @forelse($col->cards as $card)
                                @php
                                    $prioClasses = [
                                        'urgent' => 'bg-danger text-white',
                                        'high' => 'bg-danger-subtle text-danger border border-danger',
                                        'medium' => 'bg-warning-subtle text-dark border border-warning',
                                        'low' => 'bg-info-subtle text-info border border-info',
                                    ];
                                    $prioClass = $prioClasses[$card->priority] ?? 'bg-light text-muted border';
                                    $prioLabel = [
                                        'urgent' => 'Urgente',
                                        'high' => 'Haute',
                                        'medium' => 'Moyenne',
                                        'low' => 'Basse',
                                    ][$card->priority] ?? ucfirst($card->priority ?: 'Normale');

                                    $checklist = is_array($card->checklist) ? $card->checklist : [];
                                    $checkDone = count(array_filter($checklist, fn($it) => !empty($it['completed'])));
                                    $checkTotal = count($checklist);
                                @endphp
                                <div 
                                    class="card border-0 rounded-3 shadow-xs p-3 bg-white cursor-pointer hover-card transition"
                                    wire:click="selectCard({{ $card->id }})"
                                    style="cursor: pointer;"
                                    title="Cliquer pour afficher les détails de la tâche"
                                >
                                    <!-- Priority & Completion -->
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge {{ $prioClass }} extra-small rounded-pill">
                                            {{ $prioLabel }}
                                        </span>
                                        @if($card->is_completed)
                                            <span class="badge bg-success text-white rounded-pill extra-small">
                                                <i class="bi bi-check-circle-fill"></i> Fait
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Card Title -->
                                    <div class="fw-bold text-dark small mb-2 {{ $card->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                        {{ $card->title }}
                                    </div>

                                    <!-- Meta Badges & Assignee -->
                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top extra-small text-muted flex-wrap gap-1">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($card->due_date)
                                                <span class="d-inline-flex align-items-center gap-1 {{ $card->due_date->isPast() && !$card->is_completed ? 'text-danger fw-bold' : '' }}">
                                                    <i class="bi bi-calendar-event"></i>
                                                    {{ $card->due_date->format('d/m') }}
                                                </span>
                                            @endif
                                            @if($checkTotal > 0)
                                                <span class="d-inline-flex align-items-center gap-1 {{ $checkDone === $checkTotal ? 'text-success fw-bold' : '' }}">
                                                    <i class="bi bi-check2-square"></i>
                                                    {{ $checkDone }}/{{ $checkTotal }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($card->assignee)
                                            <div class="d-inline-flex align-items-center gap-1" title="Assigné à : {{ $card->assignee->name }}">
                                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-dark fw-bold" style="width: 20px; height: 20px; font-size: 10px; overflow: hidden;">
                                                    @if($card->assignee->avatar)
                                                        <img src="{{ asset($card->assignee->avatar) }}" alt="{{ $card->assignee->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                    @else
                                                        {{ strtoupper(substr($card->assignee->name, 0, 1)) }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted extra-small">
                                    Aucune tâche dans cette colonne.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        Ce tableau ne comporte aucune colonne.
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- VIEW 2: LIST VIEW -->
        <div class="card card-melty shadow-sm">
            <!-- Filter toolbar -->
            <div class="card-body p-3 bg-light border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control border-start-0" 
                                placeholder="Rechercher une tâche..." 
                                wire:model.live.debounce.300ms="cardSearch"
                            >
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" wire:model.live="cardPriorityFilter">
                            <option value="">Toutes les priorités</option>
                            <option value="urgent">Urgente</option>
                            <option value="high">Haute</option>
                            <option value="medium">Moyenne</option>
                            <option value="low">Basse</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tâche</th>
                            <th>Colonne / Statut</th>
                            <th>Priorité</th>
                            <th>Échéance</th>
                            <th>Assigné à</th>
                            <th>Checklist</th>
                            <th>Temps (h)</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allCardsList as $c)
                            @php
                                $checklist = is_array($c->checklist) ? $c->checklist : [];
                                $checkDone = count(array_filter($checklist, fn($it) => !empty($it['completed'])));
                                $checkTotal = count($checklist);
                            @endphp
                            <tr class="{{ $c->is_completed ? 'table-light' : '' }}">
                                <td>
                                    <div class="fw-bold text-dark small {{ $c->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                        {{ $c->title }}
                                    </div>
                                    @if($c->description)
                                        <div class="text-muted extra-small text-truncate" style="max-width: 300px;">
                                            {{ Str::limit($c->description, 60) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border extra-small">
                                        {{ $c->column->title ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $pClass = [
                                            'urgent' => 'bg-danger text-white',
                                            'high' => 'bg-danger-subtle text-danger',
                                            'medium' => 'bg-warning-subtle text-dark',
                                            'low' => 'bg-info-subtle text-info',
                                        ][$c->priority] ?? 'bg-light text-muted';
                                    @endphp
                                    <span class="badge {{ $pClass }} extra-small rounded-pill">
                                        {{ ucfirst($c->priority ?: 'Normale') }}
                                    </span>
                                </td>
                                <td class="extra-small text-muted">
                                    {{ $c->due_date ? $c->due_date->format('d/m/Y') : '—' }}
                                </td>
                                <td>
                                    @if($c->assignee)
                                        <span class="extra-small fw-semibold text-dark">{{ $c->assignee->name }}</span>
                                    @else
                                        <span class="text-muted extra-small">Non assigné</span>
                                    @endif
                                </td>
                                <td class="extra-small text-muted">
                                    {{ $checkTotal > 0 ? "{$checkDone}/{$checkTotal}" : '—' }}
                                </td>
                                <td class="extra-small text-muted">
                                    {{ $c->spent_hours ?: 0 }} / {{ $c->estimated_hours ?: 0 }}
                                </td>
                                <td class="text-end">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 extra-small fw-semibold"
                                        wire:click="selectCard({{ $c->id }})"
                                    >
                                        <i class="bi bi-eye"></i> Détails
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Aucune tâche trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- READ-ONLY CARD DETAIL MODAL -->
    @if($selectedCard)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1055;">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header bg-light border-bottom p-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill extra-small">
                                Colonne : {{ $selectedCard->column->title ?? '—' }}
                            </span>
                            @if($selectedCard->is_completed)
                                <span class="badge bg-success rounded-pill extra-small">
                                    <i class="bi bi-check-circle-fill me-1"></i>Terminée
                                </span>
                            @endif
                        </div>
                        <button type="button" class="btn-close" wire:click="closeCardModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <h5 class="fw-bold text-dark mb-3">{{ $selectedCard->title }}</h5>

                        <!-- Meta Info Row -->
                        <div class="row g-2 mb-4 p-3 bg-light rounded-3 border extra-small">
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Priorité :</span>
                                <strong class="text-dark">{{ ucfirst($selectedCard->priority ?: 'Normale') }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Date d'échéance :</span>
                                <strong class="text-dark">{{ $selectedCard->due_date ? $selectedCard->due_date->format('d/m/Y') : 'Non définie' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Assigné à :</span>
                                <strong class="text-dark">{{ $selectedCard->assignee->name ?? 'Non assigné' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Temps passé / estimé :</span>
                                <strong class="text-dark">{{ $selectedCard->spent_hours ?: 0 }}h / {{ $selectedCard->estimated_hours ?: 0 }}h</strong>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark small mb-2">Description</h6>
                            @if($selectedCard->description)
                                <div class="p-3 bg-white rounded-3 border small" style="white-space: pre-line;">
                                    {{ $selectedCard->description }}
                                </div>
                            @else
                                <span class="text-muted extra-small">Aucune description détaillée.</span>
                            @endif
                        </div>

                        <!-- Checklist -->
                        @php
                            $checklist = is_array($selectedCard->checklist) ? $selectedCard->checklist : [];
                        @endphp
                        @if(count($checklist) > 0)
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark small mb-2">Checklist de sous-tâches</h6>
                                <div class="d-flex flex-column gap-2 p-3 bg-light rounded-3 border">
                                    @foreach($checklist as $item)
                                        <div class="d-flex align-items-center gap-2 extra-small">
                                            <input 
                                                type="checkbox" 
                                                disabled 
                                                class="form-check-input mt-0" 
                                                {{ !empty($item['completed']) ? 'checked' : '' }}
                                            >
                                            <span class="{{ !empty($item['completed']) ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                                {{ $item['text'] ?? $item['title'] ?? 'Sous-tâche' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Activity Log -->
                        @if($selectedCard->activities && $selectedCard->activities->count() > 0)
                            <div>
                                <h6 class="fw-bold text-dark small mb-2">Historique d'activité de la tâche</h6>
                                <div class="p-3 bg-light rounded-3 border extra-small d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($selectedCard->activities as $act)
                                        <div class="d-flex align-items-center justify-content-between text-muted">
                                            <span>
                                                <i class="bi bi-clock-history me-1 text-primary"></i>
                                                <strong>{{ $act->user->name ?? 'Système' }}</strong> : {{ $act->activity ?? $act->description }}
                                            </span>
                                            <span class="text-muted extra-small">
                                                {{ $act->created_at ? $act->created_at->diffForHumans() : '' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer bg-light border-top p-2">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" wire:click="closeCardModal">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
