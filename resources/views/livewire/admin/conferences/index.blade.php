<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-calendar-event text-danger me-2"></i>Conférences & Événements Tech</h3>
            <p class="text-muted small mb-0">Organisez les sommets, hackathons, webinaires et gérez les inscriptions avec envoi d'invitations par email.</p>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Form (4 cols) -->
        <div class="col-lg-4">
            <div class="card card-melty">
                <div class="card-header card-header-melty">
                    <i class="bi bi-calendar-plus text-danger me-2"></i>{{ $isEditing ? 'Modifier la Conférence' : 'Créer une Conférence' }}
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Titre de l'événement <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('title') is-invalid @enderror" placeholder="ex: Sommet Tech Cameroun 2026" wire:model.live="title" required>
                            @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Slug URL <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model="slug" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Date de début <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control form-control-sm" wire:model="start_date" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Date de fin</label>
                                <input type="datetime-local" class="form-control form-control-sm" wire:model="end_date">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Format</label>
                                <select class="form-select form-select-sm" wire:model="location_type">
                                    <option value="in_person">Présentiel</option>
                                    <option value="online">En ligne / Webinaire</option>
                                    <option value="hybrid">Hybride</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Ville <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" placeholder="Douala, Yaoundé..." wire:model="city" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Lieu / Adresse exacte</label>
                            <input type="text" class="form-control form-control-sm" placeholder="ex: Krystal Palace Hotel, Akwa" wire:model="venue_address">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Places Max <span class="text-danger">*</span></label>
                                <input type="number" min="1" class="form-control form-control-sm" wire:model="max_attendees" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Statut</label>
                                <select class="form-select form-select-sm" wire:model="status">
                                    <option value="approved">Approuvée & Publique</option>
                                    <option value="pending_validation">En attente de validation</option>
                                    <option value="rejected">Refusée</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Image / Affiche de l'événement</label>
                            @if($banner_image)
                                <img src="{{ $banner_image->temporaryUrl() }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @elseif($existing_banner)
                                <img src="{{ asset($existing_banner) }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @endif
                            <input type="file" class="form-control form-control-sm" wire:model="banner_image" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description complète de l'événement <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-sm" rows="4" placeholder="Objectifs, programme, intervenants attendus..." wire:model="description" required></textarea>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="isOfficialCheck" wire:model="is_official">
                            <label class="form-check-label small fw-bold" for="isOfficialCheck">Badge Officiel Melty</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="acceptsRegCheck" wire:model="accepts_registrations">
                            <label class="form-check-label small fw-bold" for="acceptsRegCheck">Accepter les inscriptions en ligne</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-melty flex-grow-1">
                                <i class="bi bi-check2-circle me-1"></i> {{ $isEditing ? 'Mettre à jour' : 'Publier la conférence' }}
                            </button>
                            @if($isEditing)
                                <button type="button" wire:click="resetForm" class="btn btn-light border">Annuler</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table (8 cols) -->
        <div class="col-lg-8">
            <div class="card card-melty">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Événement</th>
                                <th>Date & Lieu</th>
                                <th>Type</th>
                                <th>Inscriptions</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($conferences as $c)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($c->banner_image)
                                                <img src="{{ asset($c->banner_image) }}" class="rounded" style="width: 48px; height: 36px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 36px;">
                                                    <i class="bi bi-calendar-event"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">{{ $c->title }}</div>
                                                <small class="text-muted">/conferences/{{ $c->slug }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ $c->start_date ? $c->start_date->format('d/m/Y H:i') : '' }}</div>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $c->city }}</small>
                                    </td>
                                    <td>
                                        @if($c->user_id)
                                            <span class="badge bg-primary-subtle text-primary border">
                                                <i class="bi bi-person-fill me-1"></i>Créé par Membre : {{ $c->user->name ?? 'Utilisateur' }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-shield-check me-1"></i>Officielle Melty Admin
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" wire:click="viewRegistrations({{ $c->id }})" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0">
                                            <i class="bi bi-people-fill me-1"></i>{{ $c->registrations->count() }} / {{ $c->max_attendees }}
                                        </button>
                                    </td>
                                    <td>
                                        @if($c->status === 'approved')
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Validée & En ligne</span>
                                        @elseif($c->status === 'pending_validation')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>En attente de validation</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Refusée</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($c->status === 'pending_validation')
                                            <button type="button" wire:click="approve({{ $c->id }})" class="btn btn-sm btn-success me-1" title="Valider et publier">
                                                <i class="bi bi-check-lg me-1"></i>Valider
                                            </button>
                                            <button type="button" wire:click="reject({{ $c->id }})" class="btn btn-sm btn-outline-danger me-1" title="Refuser">
                                                <i class="bi bi-x-lg me-1"></i>Refuser
                                            </button>
                                        @else
                                            <button type="button" wire:click="toggleApprove({{ $c->id }})" class="btn btn-sm btn-light border me-1" title="Modifier la visibilité">
                                                <i class="bi {{ $c->status === 'approved' ? 'bi-pause-circle text-warning' : 'bi-play-circle text-success' }}"></i>
                                            </button>
                                        @endif

                                        <button type="button" wire:click="viewRegistrations({{ $c->id }})" class="btn btn-sm btn-light border me-1" title="Voir les inscrits & billets">
                                            <i class="bi bi-people"></i>
                                        </button>
                                        <button type="button" wire:click="delete({{ $c->id }})" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Supprimer cet événement ?')"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Aucune conférence trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- PARTICIPANTS & CONFIRMATION EMAILS MODAL (Requested with bulk progress bar) -->
    @if($selectedConf)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-people-fill text-danger me-2"></i>Participants inscrits : {{ $selectedConf->title }}
                            </h5>
                            <p class="text-muted small mb-0">{{ $selectedConf->registrations->count() }} inscription(s) enregistrée(s) sur {{ $selectedConf->max_attendees }} places disponibles.</p>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeRegistrationsModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        @if(session()->has('reg_success'))
                            <div class="alert alert-success py-2">
                                <i class="bi bi-check-circle me-1"></i>{{ session('reg_success') }}
                            </div>
                        @endif

                        <!-- Bulk Email Action Card with Progress Bar -->
                        <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold mb-1"><i class="bi bi-send text-primary me-2"></i>Envoi groupé des emails de confirmation</h6>
                                    <small class="text-muted">Envoyer automatiquement l'invitation et le billet électronique à tous les participants qui ne l'ont pas encore reçu.</small>
                                </div>
                                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" wire:click="sendBulkConfirmations">
                                    <i class="bi bi-envelope-check me-1"></i> Envoyer les billets à tous
                                </button>
                            </div>

                            @if($isSendingBulk)
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between small fw-bold mb-1">
                                        <span>Progression de l'envoi</span>
                                        <span>{{ $emailsSentCount }} emails envoyés sur {{ $totalEmailsToSend }}</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: {{ $totalEmailsToSend > 0 ? ($emailsSentCount / $totalEmailsToSend * 100) : 100 }}%;"></div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Participants Table -->
                        <div class="table-responsive border rounded-3 bg-white">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Participant</th>
                                        <th>Email / WhatsApp</th>
                                        <th>Sexe & Ville</th>
                                        <th>Date Inscription</th>
                                        <th>Statut Envoi Billet</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedConf->registrations as $reg)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $reg->first_name }} {{ $reg->last_name }}</div>
                                                @if($reg->bio)
                                                    <small class="text-muted">{{ Str::limit($reg->bio, 40) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="small fw-semibold">{{ $reg->email }}</div>
                                                @if($reg->phone_whatsapp)
                                                    <small class="text-success"><i class="bi bi-whatsapp me-1"></i>{{ $reg->phone_whatsapp }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $reg->gender ?? 'N/A' }}</span>
                                                <span class="small text-muted ms-1">{{ $reg->city }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $reg->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                @if($reg->email_sent_at)
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="bi bi-check2-all me-1"></i>Envoyé ({{ $reg->email_sent_at->format('d/m H:i') }})
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">Non envoyé</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <button type="button" wire:click="sendSingleConfirmation({{ $reg->id }})" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    <i class="bi bi-send me-1"></i> Envoyer billet
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Aucune inscription pour l'instant.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeRegistrationsModal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
