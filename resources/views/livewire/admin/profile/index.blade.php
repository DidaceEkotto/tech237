<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Mon Profil Administrateur</h3>
            <p class="text-muted small mb-0">Modifiez vos informations personnelles, votre avatar et votre mot de passe de connexion.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Profile Info -->
        <div class="col-lg-6">
            <div class="card card-melty h-100">
                <div class="card-header card-header-melty">
                    <i class="bi bi-person-badge text-danger me-2"></i>Informations Personnelles
                </div>
                <div class="card-body p-4">
                    @if (session('profile_success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3 small" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('profile_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form wire:submit.prevent="updateProfile">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if($new_avatar)
                                    <img src="{{ $new_avatar->temporaryUrl() }}" class="rounded-circle shadow" style="width: 90px; height: 90px; object-fit: cover;">
                                @elseif($avatar)
                                    <img src="{{ asset($avatar) }}" class="rounded-circle shadow" style="width: 90px; height: 90px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow mx-auto fw-bold fs-2" style="width: 90px; height: 90px;">
                                        {{ strtoupper(substr($name ?: 'A', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2">
                                <label class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-camera me-1"></i> Changer l'avatar
                                    <input type="file" wire:model="new_avatar" accept="image/*" class="d-none">
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" required>
                            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Adresse Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" required>
                            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Genre (Sexe)</label>
                                <select class="form-select" wire:model="gender">
                                    <option value="Homme">Homme</option>
                                    <option value="Femme">Femme</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Téléphone</label>
                                <input type="text" class="form-control" placeholder="+33 6..." wire:model="phone">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Date de naissance</label>
                                <input type="text" class="form-control jquery-datepicker" id="profile_birth_date" placeholder="AAAA-MM-JJ" wire:model="birth_date">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Ville</label>
                                <input type="text" class="form-control" placeholder="Paris, Yaoundé..." wire:model="city">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Bio / Présentation</label>
                            <textarea class="form-control" rows="3" placeholder="Quelques mots sur vous..." wire:model="bio"></textarea>
                        </div>

                        <!-- Réseaux Sociaux -->
                        <div class="border rounded-3 p-3 bg-light mb-3">
                            <h6 class="fw-bold small text-dark mb-3"><i class="bi bi-share me-1 text-danger"></i>Réseaux Sociaux Professionnels</h6>
                            
                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="bi bi-facebook text-primary"></i></span>
                                    <input type="url" class="form-control" placeholder="https://facebook.com/..." wire:model="social_facebook">
                                </div>
                                @error('social_facebook') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="bi bi-twitter-x text-dark"></i></span>
                                    <input type="url" class="form-control" placeholder="https://x.com/..." wire:model="social_twitter">
                                </div>
                                @error('social_twitter') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="bi bi-linkedin text-info"></i></span>
                                    <input type="url" class="form-control" placeholder="https://linkedin.com/in/..." wire:model="social_linkedin">
                                </div>
                                @error('social_linkedin') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="bi bi-instagram text-danger"></i></span>
                                    <input type="url" class="form-control" placeholder="https://instagram.com/..." wire:model="social_instagram">
                                </div>
                                @error('social_instagram') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="bi bi-tiktok text-dark"></i></span>
                                    <input type="url" class="form-control" placeholder="https://tiktok.com/@..." wire:model="social_tiktok">
                                </div>
                                @error('social_tiktok') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-melty w-100 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Enregistrer les informations
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-lg-6">
            <div class="card card-melty h-100">
                <div class="card-header card-header-melty">
                    <i class="bi bi-shield-lock text-danger me-2"></i>Changement du Mot de Passe
                </div>
                <div class="card-body p-4">
                    @if (session('password_success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3 small" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('password_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(auth('admin')->user()->default_password === 0)
                        <div class="alert alert-warning small p-3 mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Votre mot de passe actuel est celui par défaut (000000). Renseignez un nouveau mot de passe personnalisé pour désactiver la bannière d'alerte.
                        </div>
                    @endif

                    <form wire:submit.prevent="updatePassword" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Mot de passe actuel <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" placeholder="••••••" wire:model="current_password" required>
                            @error('current_password') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nouveau mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Au moins 6 caractères" wire:model="new_password" required>
                            @error('new_password') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Confirmer le nouveau mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" placeholder="Répétez le nouveau mot de passe" wire:model="new_password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 fw-bold py-2">
                            <i class="bi bi-key-fill me-1"></i> Mettre à jour le mot de passe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
