<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Newsletters & Gestion des Inscrits</h3>
            <p class="text-muted small mb-0">Consultez les nouveaux abonnés avec leur adresse IP, rédigez et envoyez des e-mails en FR/EN via l'éditeur WYSIWYG.</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="switchTab('compose')" class="btn btn-melty shadow-sm">
                <i class="bi bi-pencil-square me-1"></i> Rédiger une Newsletter
            </button>
        </div>
    </div>

    <!-- Navigation Pills -->
    <div class="card card-melty mb-4">
        <div class="card-header bg-white p-2">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <button wire:click="switchTab('subscribers')" class="nav-link {{ $activeTab === 'subscribers' ? 'active bg-danger' : '' }} fw-semibold">
                        <i class="bi bi-people me-1"></i> Abonnés Inscrits & IPs
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="switchTab('compose')" class="nav-link {{ $activeTab === 'compose' ? 'active bg-danger' : '' }} fw-semibold">
                        <i class="bi bi-envelope-plus me-1"></i> Rédiger Newsletter (WYSIWYG FR/EN)
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="switchTab('history')" class="nav-link {{ $activeTab === 'history' ? 'active bg-danger' : '' }} fw-semibold">
                        <i class="bi bi-clock-history me-1"></i> Historique des envois & Bénéficiaires
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- TAB 1: SUBSCRIBERS WITH IP ADDRESSES -->
    @if($activeTab === 'subscribers')
        <div class="card card-melty">
            <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people text-danger me-2"></i>Liste des Abonnés à la Newsletter</span>
                <button wire:click="switchTab('compose')" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-send me-1"></i> Rédiger un email à tous les inscrits
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Email de l'abonné</th>
                            <th>Adresse IP enregistrée</th>
                            <th>Date d'inscription</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $sub)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <i class="bi bi-envelope-at me-2 text-danger"></i>{{ $sub->email }}
                                </td>
                                <td>
                                    <code class="bg-light text-primary px-2 py-1 rounded border">{{ $sub->ip_address ?? '127.0.0.1' }}</code>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ $sub->subscribed_at ? $sub->subscribed_at->format('d/m/Y H:i') : $sub->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td>
                                    @if($sub->is_active)
                                        <span class="badge bg-success-subtle text-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Désabonné</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-danger" 
                                        onclick="confirmStaticAction('Désinscrire l\'abonné', 'Supprimer l\'adresse {{ addslashes($sub->email) }} de la newsletter ?', function() { @this.call('deleteSubscriber', {{ $sub->id }}); })"
                                        title="Supprimer"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-envelope-x fs-1 d-block mb-2 text-secondary"></i>
                                    Aucun abonné enregistré pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">
                {{ $subscribers->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 2: COMPOSE NEWSLETTER (WYSIWYG FR / EN) -->
    @if($activeTab === 'compose')
        <div class="card card-melty">
            <div class="card-header card-header-melty">
                <i class="bi bi-envelope-paper-fill text-danger me-2"></i>Rédiger et Expédier une Newsletter
            </div>
            <div class="card-body p-4">
                <form wire:submit.prevent="sendNewsletter">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <h6 class="fw-bold mb-3">🇫🇷 Version Française (Obligatoire)</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Objet du message (Français) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('subject_fr') is-invalid @enderror" placeholder="ex: Melty Hebdo : Les pépites ciné & tech de la semaine" wire:model="subject_fr" required>
                                    @error('subject_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3" wire:ignore>
                                    <label class="form-label small fw-bold">Corps de la newsletter (WYSIWYG FR) <span class="text-danger">*</span></label>
                                    <textarea id="nl_editor_fr" class="form-control">{{ $content_fr }}</textarea>
                                </div>
                                @error('content_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <h6 class="fw-bold mb-3">🇬🇧 Version Anglaise (Optionnelle)</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Email Subject (English)</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="e.g. Melty Weekly: Top movie & gaming highlights" wire:model="subject_en">
                                </div>
                                <div class="mb-3" wire:ignore>
                                    <label class="form-label small fw-bold">Newsletter Content (WYSIWYG EN)</label>
                                    <textarea id="nl_editor_en" class="form-control">{{ $content_en }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>L'email sera expédié à tous les inscrits actifs.
                        </span>
                        <button type="submit" class="btn btn-melty px-4 py-2 fw-bold">
                            <i class="bi bi-send-fill me-2"></i>Envoyer la newsletter maintenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- TAB 3: HISTORY OF SENT NEWSLETTERS -->
    @if($activeTab === 'history')
        <div class="card card-melty">
            <div class="card-header card-header-melty">
                <i class="bi bi-clock-history text-danger me-2"></i>Historique des Newsletters Expédiées
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Objet de la Newsletter</th>
                            <th>Expéditeur</th>
                            <th>Bénéficiaires (Destinataires)</th>
                            <th>Date d'expédition</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($newsletters as $nl)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $nl->subject_fr }}</div>
                                    @if($nl->subject_en)
                                        <small class="text-muted d-block">EN: {{ $nl->subject_en }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="small">{{ $nl->sender->name ?? 'Administrateur' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-6">
                                        <i class="bi bi-people-fill me-1"></i>{{ $nl->recipients_count }} abonné(s)
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ $nl->sent_at ? $nl->sent_at->format('d/m/Y H:i') : $nl->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    Aucune newsletter envoyée pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initNlSummernote(elId, wireProp) {
            if ($(elId).length) {
                $(elId).summernote({
                    placeholder: 'Rédigez la newsletter à envoyer aux abonnés...',
                    tabsize: 2,
                    height: 280,
                    toolbar: [
                        ['style', ['style', 'bold', 'italic', 'underline']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture']],
                        ['view', ['codeview']]
                    ],
                    callbacks: {
                        onChange: function(contents) {
                            @this.set(wireProp, contents);
                        }
                    }
                });
            }
        }

        initNlSummernote('#nl_editor_fr', 'content_fr');
        initNlSummernote('#nl_editor_en', 'content_en');
    });
</script>
@endpush
