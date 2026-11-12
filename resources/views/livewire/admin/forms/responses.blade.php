<div>
    <!-- Top Bar Navigation & Info -->
    <div class="card card-melty shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('admin.forms.index') }}" class="btn btn-sm btn-light rounded-circle p-2 text-muted shadow-xs" title="Retour aux formulaires">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-black mb-0 text-dark">Réponses collectées : {{ $form->title }}</h4>
                            <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-1 extra-small fw-bold">
                                {{ $totalResponsesCount }} participation(s)
                            </span>
                            <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill extra-small fw-bold">
                                <i class="bi bi-eye-fill me-1"></i>Lecture Seule Admin
                            </span>
                        </div>
                        <div class="extra-small text-muted mt-1 d-flex align-items-center gap-2 flex-wrap">
                            <span>Créé par : <strong>{{ $form->user->name ?? 'Tech237' }}</strong></span>
                            <span>•</span>
                            <span>Lien : <code>/f/{{ $form->slug }}</code></span>
                            <span>•</span>
                            <span>{{ $form->fields->count() }} question(s) dans le formulaire</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="exportCsv" 
                        class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-2"
                        title="Télécharger toutes les réponses au format tableur CSV / Excel"
                    >
                        <i class="bi bi-file-earmark-excel-fill"></i>
                        <span>Exporter en CSV</span>
                    </button>
                    <a 
                        href="{{ route('admin.forms.show', $form->id) }}" 
                        class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold shadow-xs"
                    >
                        <i class="bi bi-ui-checks me-1"></i>
                        <span>Voir la structure</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Responses Table Card -->
    <div class="card card-melty shadow-sm">
        <!-- Search filter toolbar -->
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
                            placeholder="Rechercher par email du participant ou adresse IP..." 
                            wire:model.live.debounce.300ms="search"
                        >
                        @if(!empty($search))
                            <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" wire:click="$set('search', '')">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Participant / Email</th>
                        <th>Date de soumission</th>
                        <th>Temps de réponse</th>
                        <th>Adresse IP</th>
                        <th>Aperçu des réponses</th>
                        <th class="text-end" style="width: 140px;">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responses as $idx => $resp)
                        @php
                            $answers = is_array($resp->answers) ? $resp->answers : [];
                            $firstAnswer = reset($answers);
                            if (is_array($firstAnswer)) $firstAnswer = implode(', ', $firstAnswer);
                        @endphp
                        <tr>
                            <td class="text-muted extra-small">
                                {{ ($responses->currentPage() - 1) * $responses->perPage() + $idx + 1 }}
                            </td>
                            <td>
                                @if($resp->user)
                                    <div class="fw-bold text-dark extra-small">{{ $resp->user->name }}</div>
                                    <div class="text-muted extra-small">{{ $resp->user->email }}</div>
                                @elseif($resp->respondent_email)
                                    <span class="fw-semibold text-dark extra-small">{{ $resp->respondent_email }}</span>
                                @else
                                    <span class="badge bg-light text-muted extra-small">Anonyme</span>
                                @endif
                            </td>
                            <td class="extra-small text-muted">
                                {{ $resp->created_at ? $resp->created_at->format('d/m/Y à H:i:s') : '—' }}
                            </td>
                            <td class="extra-small text-muted">
                                @if($resp->completed_in_seconds)
                                    {{ $resp->completed_in_seconds }} s ({{ round($resp->completed_in_seconds / 60, 1) }} min)
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="extra-small text-muted font-monospace">
                                {{ $resp->ip_address ?: '—' }}
                            </td>
                            <td class="extra-small text-muted">
                                <span class="d-block text-truncate" style="max-width: 280px;">
                                    {{ count($answers) }} réponse(s) saisie(s)
                                </span>
                            </td>
                            <td class="text-end">
                                <button 
                                    type="button" 
                                    wire:click="selectResponse({{ $resp->id }})" 
                                    class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-bold shadow-xs"
                                >
                                    <i class="bi bi-eye-fill me-1"></i> Voir tout
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                <span>Aucune réponse n'a encore été enregistrée pour ce formulaire.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($responses->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="extra-small text-muted">
                    Affichage de {{ $responses->firstItem() }} à {{ $responses->lastItem() }} sur {{ $responses->total() }} réponses
                </div>
                <div>
                    {{ $responses->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- READ-ONLY SUBMISSION DETAIL MODAL -->
    @if($selectedResponse)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1055;">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header bg-light border-bottom p-3">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Détails de la soumission #{{ $selectedResponse->id }}</h6>
                            <div class="extra-small text-muted">
                                Reçue le {{ $selectedResponse->created_at ? $selectedResponse->created_at->format('d/m/Y à H:i:s') : '—' }}
                                @if($selectedResponse->completed_in_seconds)
                                    • Complétée en {{ $selectedResponse->completed_in_seconds }} secondes
                                @endif
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeResponseModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <!-- Respondent Identification -->
                        <div class="p-3 bg-light rounded-3 border mb-4 extra-small">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <span class="text-muted d-block">Participant / Email :</span>
                                    <strong class="text-dark">{{ $selectedResponse->respondent_email ?: ($selectedResponse->user->email ?? 'Visiteur anonyme') }}</strong>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted d-block">Adresse IP :</span>
                                    <code class="text-dark">{{ $selectedResponse->ip_address ?: 'Non renseignée' }}</code>
                                </div>
                            </div>
                        </div>

                        <!-- Answers Map -->
                        <h6 class="fw-bold text-dark small mb-3">Réponses aux questions :</h6>
                        @php
                            $answers = is_array($selectedResponse->answers) ? $selectedResponse->answers : [];
                        @endphp

                        <div class="d-flex flex-column gap-3">
                            @foreach($form->fields as $idx => $field)
                                @php
                                    $ans = $answers[$field->id] ?? ($answers[$field->name] ?? null);
                                @endphp
                                <div class="p-3 bg-white rounded-3 border shadow-xs">
                                    <div class="d-flex align-items-center justify-content-between mb-1 extra-small text-muted">
                                        <span class="fw-bold text-primary">Question #{{ $idx + 1 }} • {{ $field->type }}</span>
                                        @if($field->is_required)
                                            <span class="badge bg-danger-subtle text-danger extra-small">Obligatoire</span>
                                        @endif
                                    </div>
                                    <div class="fw-bold text-dark small mb-2">{{ $field->label }}</div>

                                    <div class="p-2 bg-light rounded-2 small text-dark">
                                        @if($ans === null || $ans === '')
                                            <em class="text-muted">Aucune réponse fournie.</em>
                                        @elseif(is_array($ans))
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($ans as $val)
                                                    <span class="badge bg-dark text-white px-2 py-1 extra-small">{{ $val }}</span>
                                                @endforeach
                                            </div>
                                        @elseif($field->type === 'rating')
                                            <div class="text-warning fw-bold fs-6">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <i class="bi bi-star{{ $s <= (int)$ans ? '-fill' : '' }}"></i>
                                                @endfor
                                                <span class="text-muted extra-small ms-1">({{ $ans }}/5)</span>
                                            </div>
                                        @elseif($field->type === 'code_snippet')
                                            <pre class="bg-dark text-light p-2 rounded-2 extra-small mb-0 font-monospace" style="max-height: 200px; overflow-y: auto;">{{ $ans }}</pre>
                                        @elseif(is_string($ans) && (str_starts_with($ans, 'http://') || str_starts_with($ans, 'https://')))
                                            <a href="{{ $ans }}" target="_blank" class="text-primary text-decoration-none fw-semibold">
                                                {{ $ans }} <i class="bi bi-box-arrow-up-right extra-small"></i>
                                            </a>
                                        @else
                                            <span>{{ $ans }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top p-2">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" wire:click="closeResponseModal">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
