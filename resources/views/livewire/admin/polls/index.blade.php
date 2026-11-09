<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Sondages Tech Quotidiens (Polls)</h4>
            <p class="text-muted small mb-0">Pilotez les questions interactives affichées en page d'accueil pour stimuler la rétention quotidienne.</p>
        </div>
        @if(!$isCreating)
            <button wire:click="startCreate" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Créer un nouveau sondage
            </button>
        @endif
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Create Form Card -->
    @if($isCreating)
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-pencil-square me-2"></i>Nouveau Sondage Communautaire</h5>
            
            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Question du sondage <span class="text-danger">*</span></label>
                    <input type="text" wire:model="question" class="form-control @error('question') is-invalid @enderror" placeholder="ex: Pour vos API de paiement au Cameroun : MTN MoMo ou Orange Money ?">
                    @error('question') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Description / Contexte (facultatif)</label>
                    <textarea wire:model="description" class="form-control" rows="2" placeholder="Précisez le contexte du sondage pour les développeurs..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Options de réponse <span class="text-danger">*</span></label>
                    <div class="d-flex flex-column gap-2">
                        @foreach($options as $index => $opt)
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 fw-bold small text-muted">{{ $index + 1 }}</span>
                                <input type="text" wire:model="options.{{ $index }}" class="form-control" placeholder="Libellé de l'option (ex: MTN MoMo API)">
                                @if(count($options) > 2)
                                    <button type="button" wire:click="removeOption({{ $index }})" class="btn btn-outline-danger" title="Retirer cette option">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                            @error('options.'.$index) <div class="text-danger small">{{ $message }}</div> @enderror
                        @endforeach
                    </div>

                    @if(count($options) < 6)
                        <button type="button" wire:click="addOption" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                            <i class="bi bi-plus-circle me-1"></i> Ajouter une option
                        </button>
                    @endif
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" wire:model="is_active" id="activePollSwitch">
                    <label class="form-check-label fw-bold small" for="activePollSwitch">
                        Activer immédiatement comme Sondage du Jour sur la page d'accueil
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" wire:click="cancel" class="btn btn-light rounded-pill px-4">Annuler</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Publier le sondage
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Polls List -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Question</th>
                        <th>Options & Votes</th>
                        <th>Total Votes</th>
                        <th>Statut</th>
                        <th>Date de création</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($polls as $poll)
                        <tr>
                            <td class="ps-4" style="max-width: 300px;">
                                <strong class="d-block text-dark">{{ $poll->question }}</strong>
                                @if($poll->description)
                                    <small class="text-muted d-block text-truncate">{{ $poll->description }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1" style="min-width: 220px;">
                                    @php $total = $poll->total_votes; @endphp
                                    @foreach($poll->options as $opt)
                                        @php $pct = $total > 0 ? round(($opt->votes_count / $total) * 100) : 0; @endphp
                                        <div>
                                            <div class="d-flex justify-content-between small text-muted mb-0">
                                                <span>{{ $opt->label }}</span>
                                                <strong class="text-dark">{{ $opt->votes_count }} ({{ $pct }}%)</strong>
                                            </div>
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-success" style="width: {{ $pct }}%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi bi-people me-1"></i>{{ $poll->total_votes }} votes
                                </span>
                            </td>
                            <td>
                                @if($poll->is_active)
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-broadcast me-1"></i>Actif (Page d'accueil)
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill">
                                        Inactif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $poll->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="toggleActive({{ $poll->id }})" class="btn btn-sm btn-outline-{{ $poll->is_active ? 'warning' : 'success' }} rounded-pill px-3 me-1" title="Changer le statut actif">
                                    {{ $poll->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                                <button wire:click="delete({{ $poll->id }})" wire:confirm="Confirmer la suppression de ce sondage ?" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-pie-chart fs-1 d-block mb-2 text-muted"></i>
                                Aucun sondage pour le moment. Cliquez sur "Créer un nouveau sondage" pour lancer le premier débat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($polls->hasPages())
            <div class="p-3 border-top">
                {{ $polls->links() }}
            </div>
        @endif
    </div>
</div>
