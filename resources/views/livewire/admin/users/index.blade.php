<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Administrateurs & Rôles</h3>
            <p class="text-muted small mb-0">Gestion des accès à l'espace d'administration, attribution des rôles et réinitialisation de mot de passe.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-4">
            <div class="card card-melty">
                <div class="card-header card-header-melty">
                    <i class="bi bi-person-plus-fill text-danger me-2"></i>{{ $isEditing ? 'Modifier l\'Administrateur' : 'Créer un Administrateur' }}
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" placeholder="ex: Jean Dupont" wire:model="name" required>
                            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Genre (Sexe)</label>
                                <select class="form-select form-select-sm" wire:model="gender">
                                    <option value="Homme">Homme</option>
                                    <option value="Femme">Femme</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Téléphone</label>
                                <input type="text" class="form-control form-control-sm" placeholder="+33 6..." wire:model="phone">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Date de naissance</label>
                                <input type="text" class="form-control form-control-sm jquery-datepicker" id="admin_birth_date" placeholder="AAAA-MM-JJ" wire:model="birth_date">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Ville</label>
                                <input type="text" class="form-control form-control-sm" placeholder="Paris, Yaoundé..." wire:model="city">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Adresse Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" placeholder="ex: redacteur@tech237.cm" wire:model="email" required>
                            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Rôle & Permissions <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" wire:model="role" required>
                                <option value="super_admin">Super Administrateur (Droits ultimes & Rétablissement)</option>
                                <option value="admin">Administrateur (Gestion complète et validation des articles)</option>
                                <option value="editor">Rédacteur (Articles soumis à validation préalable)</option>
                                <option value="moderator">Modérateur (Modération du Forum et des Commentaires)</option>
                                <option value="recruiter_admin">Responsable Recrutement (Gestion des offres d'emploi IT et tests)</option>
                                <option value="events_manager">Gestionnaire Événements (Validation des conférences et meetups)</option>
                                <option value="analyst">Analyste Données (Statistiques, audience et reporting)</option>
                                <option value="tester_reviews">Testeur (Tests Applications & Tests Jeux Vidéo uniquement)</option>
                            </select>
                        </div>

                        @if(!$isEditing)
                            <div class="alert alert-info small p-2 mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Par défaut, le mot de passe est défini à <strong>000000</strong> (six zéros). L'administrateur aura une bannière lui ordonnant de le modifier à sa première connexion.
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-melty flex-grow-1">
                                <i class="bi bi-check2-circle me-1"></i> {{ $isEditing ? 'Mettre à jour' : 'Créer le compte' }}
                            </button>
                            @if($isEditing)
                                <button type="button" wire:click="resetFields" class="btn btn-light border">Annuler</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Admins Table -->
        <div class="col-lg-8">
            <div class="card card-melty">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Administrateur</th>
                                <th>Rôle</th>
                                <th>Mot de passe</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admins as $adm)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $adm->name }}</div>
                                        <small class="text-muted">{{ $adm->email }}</small>
                                    </td>
                                    <td>
                                        @if($adm->role === 'super_admin')
                                            <span class="badge bg-danger">Super Admin</span>
                                        @elseif($adm->role === 'admin')
                                            <span class="badge bg-primary">Admin</span>
                                        @elseif($adm->role === 'editor')
                                            <span class="badge bg-secondary">Rédacteur</span>
                                        @elseif($adm->role === 'moderator')
                                            <span class="badge bg-warning text-dark">Modérateur</span>
                                        @elseif($adm->role === 'recruiter_admin')
                                            <span class="badge bg-info text-dark">Recrutement IT</span>
                                        @elseif($adm->role === 'events_manager')
                                            <span class="badge bg-success">Événements</span>
                                        @elseif($adm->role === 'analyst')
                                            <span class="badge bg-dark">Analyste</span>
                                        @elseif($adm->role === 'tester_reviews')
                                            <span class="badge bg-primary text-white"><i class="bi bi-controller me-1"></i>Testeur Apps & Jeux</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $adm->role }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($adm->default_password === 0)
                                            <span class="badge bg-warning-subtle text-warning" title="Mot de passe par défaut 000000 actif">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Défaut (000000)
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="bi bi-shield-check me-1"></i>Personnalisé
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($adm->is_banned)
                                            <span class="badge bg-danger-subtle text-danger">Banni</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">Actif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Reset to default password 000000 -->
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-warning" 
                                                onclick="confirmStaticAction('Réinitialiser le mot de passe', 'Réinitialiser le mot de passe de {{ addslashes($adm->name) }} à six zéros (000000) ? Le champ default_password sera remis à 0.', function() { @this.call('resetPasswordToDefault', {{ $adm->id }}); }, false)"
                                                title="Réinitialiser mot de passe à 000000"
                                            >
                                                <i class="bi bi-key"></i>
                                            </button>

                                            <!-- Edit -->
                                            <button wire:click="edit({{ $adm->id }})" class="btn btn-outline-primary" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <!-- Ban (if not already banned and not current) -->
                                            @if(!$adm->is_banned && $adm->id !== auth('admin')->id())
                                                <button 
                                                    type="button" 
                                                    class="btn btn-outline-danger" 
                                                    onclick="confirmStaticAction('Bannir l\'administrateur', 'Bannir le compte {{ addslashes($adm->email) }} ? L\'utilisateur ne pourra plus se connecter.', function() { @this.call('banAdmin', {{ $adm->id }}); })"
                                                    title="Bannir"
                                                >
                                                    <i class="bi bi-shield-slash"></i>
                                                </button>
                                            @endif

                                            <!-- Delete (if not current) -->
                                            @if($adm->id !== auth('admin')->id())
                                                <button 
                                                    type="button" 
                                                    class="btn btn-outline-secondary" 
                                                    onclick="confirmStaticAction('Supprimer le compte', 'Supprimer définitivement l\'administrateur {{ addslashes($adm->email) }} ?', function() { @this.call('deleteAdmin', {{ $adm->id }}); })"
                                                    title="Supprimer"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucun administrateur trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
