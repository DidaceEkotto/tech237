<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-briefcase-fill text-danger me-2"></i>Offres d'Emploi & Recrutement IT</h3>
            <p class="text-muted small mb-0">Supervisez les offres de recrutement tech, examinez les candidatures, téléchargez les CVs et planifiez des entretiens d'embauche.</p>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-melty">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Poste & Entreprise</th>
                        <th>Domaine IT</th>
                        <th>Contrat & Ville</th>
                        <th>Salaire</th>
                        <th>Candidatures</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $j)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $j->title }}</div>
                                <small class="text-muted"><i class="bi bi-building me-1"></i>{{ $j->company_name }}</small>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger">{{ $j->it_domain }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $j->contract_type }}</span>
                                <small class="text-muted ms-1">{{ $j->city }}</small>
                            </td>
                            <td>
                                <small class="fw-semibold text-dark">{{ $j->salary_range ?: 'Non spécifié' }}</small>
                            </td>
                            <td>
                                <button type="button" wire:click="viewApplications({{ $j->id }})" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-person-lines-fill me-1"></i> {{ $j->applications->count() }} candidat(s)
                                </button>
                            </td>
                            <td>
                                @if($j->status === 'approved')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Approuvé (En ligne)
                                    </span>
                                @elseif($j->status === 'rejected')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i>Rejeté
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-hourglass-split me-1"></i>En attente de validation
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($j->status === 'pending_validation')
                                    <button type="button" wire:click="approveJob({{ $j->id }})" class="btn btn-sm btn-success me-1" title="Approuver l'offre">
                                        <i class="bi bi-check-lg me-1"></i>Approuver
                                    </button>
                                    <button type="button" wire:click="rejectJob({{ $j->id }})" class="btn btn-sm btn-outline-danger me-1" title="Rejeter l'offre">
                                        <i class="bi bi-x-lg me-1"></i>Rejeter
                                    </button>
                                @else
                                    <button type="button" wire:click="togglePublish({{ $j->id }})" class="btn btn-sm btn-light border me-1" title="Changer le statut">
                                        <i class="bi {{ $j->status === 'approved' ? 'bi-pause-circle text-warning' : 'bi-play-circle text-success' }}"></i>
                                    </button>
                                @endif
                                <button type="button" wire:click="delete({{ $j->id }})" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Supprimer cette offre ?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Aucune offre d'emploi pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $jobs->links() }}
        </div>
    </div>

    <!-- CANDIDATES MODAL -->
    @if($selectedJob)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-people text-danger me-2"></i>Candidatures reçues : {{ $selectedJob->title }}
                            </h5>
                            <p class="text-muted small mb-0">{{ $selectedJob->company_name }} &bull; {{ $selectedJob->city }} ({{ $selectedJob->contract_type }})</p>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeApplicationsModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        @if(session()->has('app_success'))
                            <div class="alert alert-success py-2">
                                <i class="bi bi-check-circle me-1"></i>{{ session('app_success') }}
                            </div>
                        @endif

                        <div class="table-responsive border rounded-3 bg-white">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Candidat</th>
                                        <th>Contact</th>
                                        <th>CV Téléchargeable</th>
                                        <th>Score QCM</th>
                                        <th>Statut</th>
                                        <th class="text-end">Décision & Entretien</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedJob->applications as $app)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $app->applicant_name }}</div>
                                                <small class="text-muted">Postulé le {{ $app->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold">{{ $app->applicant_email }}</div>
                                                @if($app->applicant_phone)
                                                    <small class="text-muted">{{ $app->applicant_phone }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($app->cv_path)
                                                    <a href="{{ asset($app->cv_path) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                        <i class="bi bi-file-earmark-pdf me-1"></i> Voir le CV (Word/PDF)
                                                    </a>
                                                @else
                                                    <span class="text-muted small">Aucun CV joint</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($app->test_score !== null)
                                                    <span class="badge bg-success-subtle text-success fs-6">{{ $app->test_score }}%</span>
                                                @else
                                                    <span class="badge bg-light text-muted border">Sans QCM</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($app->status === 'interview_scheduled')
                                                    <span class="badge bg-success"><i class="bi bi-calendar-check me-1"></i>Entretien planifié</span>
                                                @elseif($app->status === 'rejected')
                                                    <span class="badge bg-danger">Refusée</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Nouvelle</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <button type="button" wire:click="openInterviewModal({{ $app->id }})" class="btn btn-sm btn-primary rounded-pill px-3 me-1">
                                                    <i class="bi bi-calendar-plus me-1"></i> Planifier Entretien
                                                </button>
                                                <button type="button" wire:click="rejectApplication({{ $app->id }})" class="btn btn-sm btn-light border text-danger rounded-pill px-3">
                                                    Rejeter
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Aucun candidat pour le moment.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeApplicationsModal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- SCHEDULE INTERVIEW MODAL (With Date & Time Picker blocking past dates) -->
    @if($selectedApplication)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-calendar2-week text-primary me-2"></i>Convocation à l'entretien
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeInterviewModal"></button>
                    </div>
                    <form wire:submit.prevent="scheduleInterview">
                        <div class="modal-body p-4">
                            <p class="text-muted small mb-3">
                                Vous planifiez un entretien pour <strong>{{ $selectedApplication->applicant_name }}</strong>. 
                                Un email d'invitation avec date, heure et lieu sera automatiquement expédié.
                            </p>

                            <!-- Date & Time input with min attribute set to current moment to block past dates -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Date & Heure de l'entretien (Bloqué dans le passé) <span class="text-danger">*</span></label>
                                <input type="datetime-local" min="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm @error('interview_date') is-invalid @enderror" wire:model="interview_date" required>
                                @error('interview_date') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Type d'entretien</label>
                                <select class="form-select form-select-sm" wire:model="interview_type">
                                    <option value="in_person">En présentiel (Face à face physique)</option>
                                    <option value="online">En ligne (Visioconférence Google Meet / Zoom)</option>
                                    <option value="phone">Téléphonique</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Lieu exact ou Lien de visioconférence <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" placeholder="ex: Salle de réunion Melty Akwa Douala" wire:model="interview_location" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Instructions particulières au candidat</label>
                                <textarea class="form-control form-control-sm" rows="3" placeholder="Documents à apporter, code vestimentaire, préparatifs..." wire:model="interview_instructions"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-3" wire:click="closeInterviewModal">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-send-check me-1"></i> Valider et envoyer l'invitation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
