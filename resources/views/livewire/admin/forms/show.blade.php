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
                            <h4 class="fw-black mb-0 text-dark">{{ $form->title }}</h4>
                            @php
                                $statusBadge = match($form->status) {
                                    'published' => ['label' => 'En ligne', 'class' => 'bg-success text-white'],
                                    'closed' => ['label' => 'Fermé', 'class' => 'bg-danger-subtle text-danger border border-danger'],
                                    default => ['label' => 'Brouillon', 'class' => 'bg-warning-subtle text-dark border border-warning'],
                                };
                            @endphp
                            <span class="badge {{ $statusBadge['class'] }} rounded-pill px-2 py-1 extra-small">
                                {{ $statusBadge['label'] }}
                            </span>
                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 extra-small">
                                Mode : {{ $form->display_mode === 'conversation' ? 'Typeform' : 'Classique' }}
                            </span>
                            <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill extra-small fw-bold">
                                <i class="bi bi-eye-fill me-1"></i>Mode Lecture Seule
                            </span>
                        </div>
                        <div class="extra-small text-muted mt-1 d-flex align-items-center gap-2 flex-wrap">
                            <span>Lien public : <code class="text-dark">/f/{{ $form->slug }}</code></span>
                            <span>•</span>
                            @if($form->user)
                                <span>Créé par : <strong>{{ $form->user->name }}</strong> ({{ $form->user->email }})</span>
                                <span>•</span>
                            @endif
                            <span>Le {{ $form->created_at ? $form->created_at->format('d/m/Y à H:i') : '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a 
                        href="{{ route('admin.forms.responses', $form->id) }}" 
                        class="btn btn-sm btn-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2 shadow-xs"
                    >
                        <i class="bi bi-bar-chart-fill"></i>
                        <span>Consulter les Réponses ({{ $form->responses_count }})</span>
                    </a>
                    <a 
                        href="{{ url('/f/' . $form->slug) }}" 
                        target="_blank" 
                        class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1 shadow-xs"
                    >
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Voir la page publique</span>
                    </a>
                </div>
            </div>

            @if($form->description)
                <div class="p-3 bg-light rounded-3 border small text-muted mb-3">
                    {{ $form->description }}
                </div>
            @endif

            <!-- Form Rules & Settings Summary -->
            <div class="row g-2 pt-3 border-top extra-small text-muted">
                <div class="col-6 col-md-3">
                    <span class="d-block text-muted">Connexion requise :</span>
                    <strong class="text-dark">{{ $form->requires_login ? 'Oui (membres uniquement)' : 'Non (public anonyme)' }}</strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="d-block text-muted">Réponse unique par user :</span>
                    <strong class="text-dark">{{ $form->one_response_per_user ? 'Oui (1 vote max)' : 'Non' }}</strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="d-block text-muted">Quota max de réponses :</span>
                    <strong class="text-dark">{{ $form->max_responses ?: 'Illimité' }}</strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="d-block text-muted">Date d'expiration :</span>
                    <strong class="text-dark">{{ $form->expires_at ? $form->expires_at->format('d/m/Y à H:i') : 'Aucune' }}</strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="d-block text-muted">Email alerte admin :</span>
                    <strong class="text-dark">{{ $form->notify_email_on_response ? ($form->notification_email ?: 'Oui') : 'Désactivé' }}</strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="d-block text-muted">Confirmation répondant :</span>
                    <strong class="text-dark">{{ $form->send_respondent_confirmation ? 'Accusé par email envoyé' : 'Non' }}</strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="d-block text-muted">Couleur de thème :</span>
                    <span class="d-inline-flex align-items-center gap-1">
                        <span class="d-inline-block rounded-circle border" style="width: 12px; height: 12px; background-color: {{ $form->theme_color ?: '#00A859' }};"></span>
                        <code class="text-dark">{{ $form->theme_color ?: '#00A859' }}</code>
                    </span>
                </div>
                <div class="col-6 col-md-3">
                    <span class="d-block text-muted">Lien de redirection :</span>
                    <strong class="text-dark text-truncate d-block">{{ $form->redirect_url ?: 'Aucune redirection' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions / Fields Structure -->
    <div class="card card-melty shadow-sm">
        <div class="card-header card-header-melty d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-list-check text-primary fs-5"></i>
                <span class="fw-bold">Structure des Questions &amp; Champs ({{ $form->fields->count() }})</span>
            </div>
            <span class="badge bg-light text-dark border extra-small">Ordre séquentiel</span>
        </div>

        <div class="card-body p-4">
            <div class="d-flex flex-column gap-3">
                @forelse($form->fields as $idx => $field)
                    <div class="card border rounded-4 shadow-xs p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white rounded-pill px-2 py-1 extra-small fw-bold">
                                    #{{ $idx + 1 }}
                                </span>
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 extra-small fw-semibold">
                                    Type : <strong>{{ $field->type }}</strong>
                                </span>
                                @if($field->is_required)
                                    <span class="badge bg-danger-subtle text-danger extra-small rounded-pill">
                                        Obligatoire
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted extra-small rounded-pill">
                                        Facultatif
                                    </span>
                                @endif
                                @if($field->conditional_logic && !empty($field->conditional_logic['enabled']))
                                    <span class="badge bg-info-subtle text-info extra-small rounded-pill">
                                        <i class="bi bi-diagram-3-fill me-1"></i>Condition active
                                    </span>
                                @endif
                            </div>
                            <span class="text-muted extra-small font-monospace">name: {{ $field->name }}</span>
                        </div>

                        <!-- Question Label -->
                        <h6 class="fw-bold text-dark mb-1">{{ $field->label }}</h6>

                        @if($field->placeholder || $field->help_text)
                            <div class="extra-small text-muted mb-2">
                                @if($field->placeholder)
                                    <span class="me-3">Indication : <em>"{{ $field->placeholder }}"</em></span>
                                @endif
                                @if($field->help_text)
                                    <span>Aide : <em>{{ $field->help_text }}</em></span>
                                @endif
                            </div>
                        @endif

                        <!-- Options for Select, Radio, Checkbox -->
                        @if(is_array($field->options) && count($field->options) > 0)
                            <div class="mt-2 p-3 bg-light rounded-3 border extra-small">
                                <strong class="text-dark d-block mb-1">Choix possibles :</strong>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($field->options as $opt)
                                        <span class="badge bg-white text-dark border px-2 py-1">
                                            {{ $opt }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Conditional Logic display -->
                        @if($field->conditional_logic && !empty($field->conditional_logic['enabled']))
                            <div class="mt-2 p-2 bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3 extra-small text-muted">
                                <i class="bi bi-funnel-fill text-info me-1"></i>
                                Règle conditionnelle : Affiché si le champ cible <code>{{ $field->conditional_logic['target_field_id'] ?? '—' }}</code> {{ $field->conditional_logic['operator'] ?? 'est égal à' }} <strong>"{{ $field->conditional_logic['value'] ?? '' }}"</strong>.
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        Ce formulaire ne contient aucune question.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
