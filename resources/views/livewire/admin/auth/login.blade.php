<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5 col-xl-4">

        <div class="text-center mb-4">
            <img src="{{ asset('assets/images/logo-tech237.png') }}" alt="Melty Logo" height="48" class="mb-2">
            <h4 class="text-white fw-bold mt-2">Backoffice Tech237</h4>
            <p class="text-secondary small">Authentification réservée aux administrateurs</p>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #1a1b26;">
            <div class="card-body p-4 p-md-5">

                @if ($errorMessage)
                    <div class="alert alert-danger d-flex align-items-center rounded-3 small p-3 mb-4" role="alert">
                        <i class="bi bi-shield-x fs-4 me-2 flex-shrink-0"></i>
                        <div>{{ $errorMessage }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger d-flex align-items-center rounded-3 small p-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-octagon-fill fs-5 me-2 flex-shrink-0"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <form wire:submit.prevent="authenticate" autocomplete="off">

                    <!-- Fake inputs to strictly prevent browser autofill -->
                    <input type="text" style="display:none">
                    <input type="password" style="display:none">

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label class="form-label text-white-50 small fw-semibold">Adresse Email Administrateur</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white-50">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input
                                type="email"
                                class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror"
                                placeholder="ex: admin@admin.test"
                                wire:model="email"
                                autocomplete="off"
                                required
                            >
                        </div>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- Password Field with Toggle Show/Hide -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label text-white-50 small fw-semibold">Mot de passe</label>
                            <button
                                type="button"
                                class="btn btn-link btn-sm text-decoration-none text-white-50 p-0"
                                wire:click="togglePasswordVisibility"
                            >
                                <i class="bi {{ $showPassword ? 'bi-eye-slash' : 'bi-eye' }} me-1"></i>
                                {{ $showPassword ? 'Masquer' : 'Afficher' }}
                            </button>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white-50">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input
                                type="{{ $showPassword ? 'text' : 'password' }}"
                                class="form-control bg-dark text-white border-secondary @error('password') is-invalid @enderror"
                                placeholder="••••••"
                                wire:model="password"
                                autocomplete="off"
                                required
                            >
                        </div>
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- Dynamic Math CAPTCHA Security Challenge -->
                    <div class="mb-4 p-3 rounded-3" style="background-color: #242636; border: 1px dashed rgba(255,255,255,0.15);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-danger-subtle text-danger fw-bold">Question Antispam</span>
                            <button type="button" wire:click="generateCaptcha" class="btn btn-sm btn-link text-white-50 p-0 text-decoration-none" title="Changer le calcul">
                                <i class="bi bi-arrow-clockwise me-1"></i>Nouveau calcul
                            </button>
                        </div>
                        <label class="form-label text-white fw-bold mb-2">
                            Combien font : <span class="text-warning fs-5">{{ $num1 }} + {{ $num2 }} = ?</span>
                        </label>
                        <input
                            type="number"
                            class="form-control bg-dark text-white border-secondary text-center fw-bold fs-5 @error('userCaptcha') is-invalid @enderror"
                            placeholder="Votre réponse"
                            wire:model="userCaptcha"
                            autocomplete="off"
                            required
                        >
                        @error('userCaptcha') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-melty w-100 py-2 fw-bold rounded-3 shadow">
                        <span wire:loading.remove wire:target="authenticate">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter à l'administration
                        </span>
                        <span wire:loading wire:target="authenticate">
                            <span class="spinner-border spinner-border-sm me-2"></span>Vérification sécurisée...
                        </span>
                    </button>

                    <div class="mt-4 text-center">
                        <small class="text-white-50">
                            <i class="bi bi-shield-check text-success me-1"></i>
                            Protection anti-bruteforce active (bannissement après 5 échecs).
                        </small>
                    </div>

                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="/" class="text-white-50 text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Retour au site public Tech237.com
            </a>
        </div>

    </div>
</div>
