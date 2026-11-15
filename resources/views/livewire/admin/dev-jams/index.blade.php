<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Dev Jams Studio (Officielles &amp; Communautaires)</h3>
            <p class="text-muted small mb-0">Lancez des Dev Jams Officielles Tech237 et modérez les Jams créées par la communauté.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('devjams.index') }}" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i>Voir sur le site
            </a>
            <button wire:click="$toggle('showCreateForm')" class="btn btn-melty btn-sm">
                <i class="bi bi-plus-circle me-1"></i>{{ $showCreateForm ? 'Fermer' : 'Créer une Jam Officielle' }}
            </button>
        </div>
    </div>

    @if($showCreateForm)
        <div class="card card-melty mb-4">
            <div class="card-header card-header-melty">
                <i class="bi bi-trophy-fill text-warning me-2"></i>Nouvelle Dev Jam Officielle Tech237
            </div>
            <div class="card-body">
                <form wire:submit.prevent="createOfficialJam" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Titre de la Jam <span class="text-danger">*</span></label>
                        <input type="text" wire:model="title" class="form-control form-control-sm" placeholder="ex: Tech237 Web Jam #1 — Outils du quotidien" required>
                        @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Thème / Contrainte</label>
                        <input type="text" wire:model="theme" class="form-control form-control-sm" placeholder="ex: Productivité locale, Mini-jeu 60s...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Date de début <span class="text-danger">*</span></label>
                        <input type="datetime-local" wire:model="starts_at" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Date de fin <span class="text-danger">*</span></label>
                        <input type="datetime-local" wire:model="ends_at" class="form-control form-control-sm" required>
                        @error('ends_at') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Récompense / Prix</label>
                        <input type="text" wire:model="prize_text" class="form-control form-control-sm" placeholder="ex: Badge Or + Mise en avant + 50 000 FCFA">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-3 pb-1">
                        <label class="form-check-label small">
                            <input type="checkbox" wire:model="is_featured" class="form-check-input me-1"> Mettre en avant
                        </label>
                        <label class="form-check-label small">
                            <input type="checkbox" wire:model="allow_external_links" class="form-check-input me-1"> Autoriser liens GitHub/Démo externes
                        </label>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Description <span class="text-danger">*</span></label>
                        <textarea wire:model="description" rows="3" class="form-control form-control-sm" required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Règlement &amp; Critères</label>
                        <textarea wire:model="rules" rows="2" class="form-control form-control-sm" placeholder="1 projet par participant, code original..."></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-melty px-4">
                            <i class="bi bi-rocket-takeoff me-1"></i>Publier la Dev Jam Officielle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="mb-3 d-flex gap-2">
        <button wire:click="$set('modeFilter', 'all')" class="btn btn-sm {{ $modeFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">Toutes</button>
        <button wire:click="$set('modeFilter', 'official')" class="btn btn-sm {{ $modeFilter === 'official' ? 'btn-danger' : 'btn-outline-danger' }}">Officielles Tech237</button>
        <button wire:click="$set('modeFilter', 'community')" class="btn btn-sm {{ $modeFilter === 'community' ? 'btn-primary' : 'btn-outline-primary' }}">Communautaires</button>
    </div>

    <div class="card card-melty">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Jam</th>
                        <th>Mode</th>
                        <th>Créateur</th>
                        <th>Période</th>
                        <th>Participation</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jams as $jam)
                        <tr>
                            <td>
                                <a href="{{ route('devjams.show', $jam->slug) }}" target="_blank" class="fw-bold text-decoration-none text-dark">
                                    {{ $jam->title }}
                                </a>
                                @if($jam->theme)
                                    <div class="small text-muted">Thème : {{ $jam->theme }}</div>
                                @endif
                            </td>
                            <td>
                                @if($jam->mode === 'official')
                                    <span class="badge bg-danger">Officielle Tech237</span>
                                @else
                                    <span class="badge bg-primary">Communautaire</span>
                                @endif
                            </td>
                            <td class="small">
                                {{ $jam->mode === 'official' ? ($jam->creatorAdmin->name ?? 'Admin Tech237') : ($jam->creatorUser->name ?? 'Membre') }}
                            </td>
                            <td class="small text-muted">
                                {{ $jam->starts_at?->format('d/m/Y') }} → {{ $jam->ends_at?->format('d/m/Y') }}
                            </td>
                            <td class="small">
                                <span class="badge bg-light text-dark border">{{ $jam->submissions_count }} projets</span>
                                <span class="badge bg-light text-dark border">{{ $jam->messages_count }} msgs</span>
                            </td>
                            <td>
                                <select class="form-select form-select-sm" style="width: 140px;" wire:change="setStatus({{ $jam->id }}, $event.target.value)">
                                    <option value="upcoming" @selected($jam->status === 'upcoming')>À venir</option>
                                    <option value="active" @selected($jam->status === 'active')>En cours</option>
                                    <option value="voting" @selected($jam->status === 'voting')>Votes</option>
                                    <option value="ended" @selected($jam->status === 'ended')>Terminée</option>
                                    <option value="rejected" @selected($jam->status === 'rejected')>Rejetée</option>
                                </select>
                            </td>
                            <td class="text-end">
                                <button wire:click="toggleFeatured({{ $jam->id }})" class="btn btn-sm {{ $jam->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}" title="Mettre en avant">
                                    <i class="bi bi-star-fill"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="confirmStaticAction('Supprimer la Dev Jam', 'Supprimer définitivement {{ addslashes($jam->title) }} ?', function() { @this.call('deleteJam', {{ $jam->id }}); })"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Aucune Dev Jam enregistrée pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $jams->links() }}
        </div>
    </div>
</div>
