<div>
    <!-- Header & Statistics -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-black mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-ui-checks text-success"></i> Formulaires &amp; Questionnaires (Lecture Admin)
            </h3>
            <p class="text-muted small mb-0">
                Supervision de l'ensemble des formulaires créés par les membres, leurs questions, règles de diffusion et réponses collectées.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-ui-checks me-1 text-primary"></i> Total Formulaires : <strong>{{ $totalForms }}</strong>
            </span>
            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-broadcast me-1"></i> En Ligne : <strong>{{ $publishedForms }}</strong>
            </span>
            <span class="badge bg-info-subtle text-info border border-info border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-bar-chart-fill me-1"></i> Réponses Collectées : <strong>{{ $totalResponses }}</strong>
            </span>
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-eye-fill me-1"></i> Total Vues : <strong>{{ $totalViews }}</strong>
            </span>
        </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="card card-melty shadow-sm mb-4">
        <div class="card-body p-3 bg-light bg-opacity-50">
            <div class="row g-2 align-items-center">
                <!-- Search -->
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control border-start-0" 
                            placeholder="Rechercher par titre, slug, auteur (nom ou email)..." 
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
                <div class="col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="statusFilter">
                        <option value="">Tous les statuts</option>
                        <option value="published">🟢 En ligne (Accepte les réponses)</option>
                        <option value="draft">🟡 Brouillon</option>
                        <option value="closed">🔴 Fermé</option>
                    </select>
                </div>

                <!-- Display Mode Filter -->
                <div class="col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="modeFilter">
                        <option value="">Tous les modes d'affichage</option>
                        <option value="classic">Mode Classique (Google Forms)</option>
                        <option value="conversation">Mode Conversationnel (Typeform)</option>
                    </select>
                </div>

                <!-- Reset -->
                <div class="col-md-1 text-end">
                    <button 
                        type="button" 
                        wire:click="$set('search', ''); $set('statusFilter', ''); $set('modeFilter', '');" 
                        class="btn btn-sm btn-light border w-100" 
                        title="Réinitialiser les filtres"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forms Table -->
    <div class="card card-melty shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Titre du Formulaire</th>
                        <th>Créateur</th>
                        <th class="text-center">Statut</th>
                        <th>Mode</th>
                        <th class="text-center">Questions</th>
                        <th class="text-center">Réponses</th>
                        <th class="text-center">Vues</th>
                        <th>Date de création</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $f)
                        @php
                            $statusBadge = match($f->status) {
                                'published' => ['label' => 'En ligne', 'class' => 'bg-success text-white'],
                                'closed' => ['label' => 'Fermé', 'class' => 'bg-danger-subtle text-danger border border-danger'],
                                default => ['label' => 'Brouillon', 'class' => 'bg-warning-subtle text-dark border border-warning'],
                            };
                        @endphp
                        <tr>
                            <td class="text-muted extra-small">#{{ $f->id }}</td>
                            <td>
                                <a href="{{ route('admin.forms.show', $f->id) }}" class="fw-bold text-dark text-decoration-none d-block hover-underline">
                                    {{ $f->title }}
                                </a>
                                <div class="extra-small text-muted font-monospace">
                                    /f/{{ $f->slug }}
                                </div>
                            </td>
                            <td>
                                @if($f->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-dark fw-bold extra-small" style="width: 28px; height: 28px; overflow: hidden;">
                                            @if($f->user->avatar)
                                                <img src="{{ asset($f->user->avatar) }}" alt="{{ $f->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                {{ strtoupper(substr($f->user->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark extra-small">{{ $f->user->name }}</div>
                                            <div class="text-muted extra-small">{{ $f->user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted extra-small">Utilisateur supprimé</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusBadge['class'] }} rounded-pill extra-small">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </td>
                            <td>
                                @if($f->display_mode === 'conversation')
                                    <span class="badge bg-primary-subtle text-primary border border-primary extra-small">
                                        <i class="bi bi-chat-square-dots me-1"></i>Typeform
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border extra-small">
                                        <i class="bi bi-file-earmark-text me-1"></i>Classique
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border extra-small fw-bold">
                                    {{ $f->fields_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $f->responses_count > 0 ? 'bg-info-subtle text-info fw-bold' : 'bg-light text-muted' }} extra-small">
                                    {{ $f->responses_count }}
                                </span>
                            </td>
                            <td class="text-center text-muted extra-small">
                                {{ $f->views_count }}
                            </td>
                            <td class="extra-small text-muted">
                                {{ $f->created_at ? $f->created_at->format('d/m/Y') : '—' }}
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a 
                                        href="{{ route('admin.forms.show', $f->id) }}" 
                                        class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 fw-semibold"
                                        title="Voir les questions et la structure"
                                    >
                                        <i class="bi bi-eye"></i> Structure
                                    </a>
                                    <a 
                                        href="{{ route('admin.forms.responses', $f->id) }}" 
                                        class="btn btn-xs btn-outline-success rounded-pill px-2 py-1 fw-bold"
                                        title="Consulter les réponses des participants"
                                    >
                                        <i class="bi bi-bar-chart-fill"></i> Réponses ({{ $f->responses_count }})
                                    </a>
                                    <a 
                                        href="{{ url('/f/' . $f->slug) }}" 
                                        target="_blank" 
                                        class="btn btn-xs btn-light border rounded-circle p-1 text-muted"
                                        title="Ouvrir le lien public"
                                        style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;"
                                    >
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-ui-checks fs-1 d-block mb-2 opacity-50"></i>
                                <span>Aucun formulaire ne correspond aux critères sélectionnés.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($forms->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="extra-small text-muted">
                    Affichage de {{ $forms->firstItem() }} à {{ $forms->lastItem() }} sur {{ $forms->total() }} formulaires
                </div>
                <div>
                    {{ $forms->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
