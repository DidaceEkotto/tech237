<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Paramètres du Site</h3>
            <p class="text-muted small mb-0">Configuration générale, identité de marque, liens sociaux, modes de soutien et pages légales.</p>
        </div>
        <button type="button" wire:click="save" class="btn btn-melty shadow-sm">
            <i class="bi bi-check-circle me-1"></i> Enregistrer les modifications
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit.prevent="save">
        
        <!-- Tabs for Settings Navigation -->
        <div class="card card-melty mb-4">
            <div class="card-header bg-white p-2">
                <ul class="nav nav-pills card-header-pills" id="settingsTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-general" type="button">
                            <i class="bi bi-sliders me-1"></i> Général & Marque
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-social" type="button">
                            <i class="bi bi-share me-1"></i> Réseaux Sociaux
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-seo" type="button">
                            <i class="bi bi-search me-1"></i> Référencement SEO
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-support" type="button">
                            <i class="bi bi-heart-fill text-danger me-1"></i> Nous Soutenir (OM / MoMo / RIB)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-legal" type="button">
                            <i class="bi bi-shield-check me-1"></i> Pages Légales (WYSIWYG FR/EN)
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="settingsTabContent">

                    <!-- TAB 1: GENERAL & IDENTITY -->
                    <div class="tab-pane fade show active" id="tab-general">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nom du site <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model="site_name" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Principal <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" wire:model="primary_email" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Secondaire (Optionnel)</label>
                                    <input type="email" class="form-control" wire:model="secondary_email">
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Téléphone WhatsApp <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" wire:model="whatsapp_phone" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Téléphone Secondaire</label>
                                        <input type="text" class="form-control" wire:model="secondary_phone">
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Ville du Siège <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <select class="form-select select2-city" wire:model="city" required>
                                                <option value="">Sélectionnez une ville...</option>
                                                @foreach(\App\Models\City::active()->get() as $c)
                                                    <option value="{{ $c->name }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Localisation / Adresse <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" wire:model="location" required>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label fw-bold">Boîte Postale (Facultatif)</label>
                                    <input type="text" class="form-control" placeholder="ex: BP 1234 Paris" wire:model="po_box">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Logo preview & upload -->
                                <div class="card bg-light border p-3 mb-4">
                                    <label class="form-label fw-bold d-block">Logo du site</label>
                                    <div class="p-3 bg-dark text-center rounded mb-2" style="background-color: #111116 !important;">
                                        @if($new_logo)
                                            <img src="{{ $new_logo->temporaryUrl() }}" alt="Nouveau Logo" height="42">
                                        @elseif($existing_logo)
                                            <img src="{{ asset($existing_logo) }}" alt="Logo actuel" height="42">
                                        @endif
                                    </div>
                                    <input type="file" class="form-control form-control-sm" wire:model="new_logo" accept="image/*">
                                    <small class="text-muted mt-1 d-block">Format recommandé: SVG vectoriel ou PNG transparent.</small>
                                </div>

                                <!-- SEO Image -->
                                <div class="card bg-light border p-3">
                                    <label class="form-label fw-bold d-block">Image de partage OpenGraph / SEO</label>
                                    @if($new_seo_image)
                                        <img src="{{ $new_seo_image->temporaryUrl() }}" class="img-fluid rounded mb-2" style="max-height: 120px; object-fit: cover;">
                                    @elseif($existing_seo_image)
                                        <img src="{{ asset($existing_seo_image) }}" class="img-fluid rounded mb-2" style="max-height: 120px; object-fit: cover;">
                                    @endif
                                    <input type="file" class="form-control form-control-sm" wire:model="new_seo_image" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: SOCIAL NETWORKS -->
                    <div class="tab-pane fade" id="tab-social">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-facebook text-primary me-2"></i>Page Facebook</label>
                                <input type="url" class="form-control" placeholder="https://facebook.com/..." wire:model="social_facebook">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-twitter-x text-dark me-2"></i>Compte X (Twitter)</label>
                                <input type="url" class="form-control" placeholder="https://x.com/..." wire:model="social_twitter">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-instagram text-danger me-2"></i>Instagram</label>
                                <input type="url" class="form-control" placeholder="https://instagram.com/..." wire:model="social_instagram">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-tiktok text-dark me-2"></i>TikTok</label>
                                <input type="url" class="form-control" placeholder="https://tiktok.com/@..." wire:model="social_tiktok">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-youtube text-danger me-2"></i>Chaîne YouTube</label>
                                <input type="url" class="form-control" placeholder="https://youtube.com/@..." wire:model="social_youtube">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-linkedin text-primary me-2"></i>LinkedIn</label>
                                <input type="url" class="form-control" placeholder="https://linkedin.com/company/..." wire:model="social_linkedin">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-telegram text-info me-2"></i>Canal Telegram</label>
                                <input type="url" class="form-control" placeholder="https://t.me/..." wire:model="social_telegram">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: SEO META -->
                    <div class="tab-pane fade" id="tab-seo">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">🇫🇷 Référencement Français</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Mots-clés SEO (FR)</label>
                                    <input type="text" class="form-control" placeholder="melty, pop culture, séries, gaming, cinéma, actualité youth..." wire:model="meta_keywords_fr">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Meta Description (FR)</label>
                                    <textarea class="form-control" rows="4" placeholder="Description pour les moteurs de recherche..." wire:model="meta_description_fr"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">🇬🇧 International SEO (English)</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">SEO Keywords (EN)</label>
                                    <input type="text" class="form-control" placeholder="pop culture, gaming reviews, tv shows, movies..." wire:model="meta_keywords_en">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Meta Description (EN)</label>
                                    <textarea class="form-control" rows="4" placeholder="Search engine description..." wire:model="meta_description_en"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: SUPPORT & DONATIONS (ORANGE MONEY / MTN / RIB) -->
                    <div class="tab-pane fade" id="tab-support">
                        <div class="alert alert-light border small mb-4">
                            <i class="bi bi-info-circle-fill text-danger me-2"></i>
                            Ces informations apparaîtront directement sur la page publique <strong>"Nous soutenir"</strong> accessible à tous les visiteurs du site.
                        </div>

                        <div class="row g-4">
                            <!-- Orange Money -->
                            <div class="col-md-6">
                                <div class="card border p-3 rounded-3" style="border-left: 4px solid #FF6600 !important;">
                                    <h6 class="fw-bold" style="color: #FF6600;">
                                        <i class="bi bi-wallet2 me-2"></i>Orange Money
                                    </h6>
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold">Nom complet du compte</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="ex: Melty Média" wire:model="orange_money_name">
                                    </div>
                                    <div>
                                        <label class="form-label small fw-bold">Numéro de téléphone Orange</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="ex: +237 690 00 00 00" wire:model="orange_money_phone">
                                    </div>
                                </div>
                            </div>

                            <!-- MTN Mobile Money -->
                            <div class="col-md-6">
                                <div class="card border p-3 rounded-3" style="border-left: 4px solid #FFCC00 !important;">
                                    <h6 class="fw-bold text-dark">
                                        <i class="bi bi-phone-vibrate me-2 text-warning"></i>MTN Mobile Money
                                    </h6>
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold">Nom complet du compte</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="ex: Melty Média" wire:model="mtn_money_name">
                                    </div>
                                    <div>
                                        <label class="form-label small fw-bold">Numéro de téléphone MTN</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="ex: +237 670 00 00 00" wire:model="mtn_money_phone">
                                    </div>
                                </div>
                            </div>

                            <!-- RIB / Virement Bancaire -->
                            <div class="col-12">
                                <div class="card border p-3 rounded-3" style="border-left: 4px solid #0066FF !important;">
                                    <h6 class="fw-bold text-primary">
                                        <i class="bi bi-bank me-2"></i>Coordonnées Bancaires (RIB / IBAN / Swift)
                                    </h6>
                                    <label class="form-label small fw-bold">Détails bancaires pour virement</label>
                                    <textarea class="form-control font-monospace" rows="4" placeholder="IBAN: FR76 ...&#10;BIC/SWIFT: ...&#10;Banque: ...&#10;Titulaire: Melty" wire:model="rib_details"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: LEGAL (TERMS & PRIVACY WYSIWYG) -->
                    <div class="tab-pane fade" id="tab-legal">
                        <div class="row g-4">
                            <div class="col-12">
                                <h5 class="fw-bold border-bottom pb-2 mb-3">Conditions Générales d'Utilisation (CGU)</h5>
                                <div class="row g-3">
                                    <div class="col-md-6" wire:ignore>
                                        <label class="form-label small fw-bold">CGU en Français (WYSIWYG)</label>
                                        <textarea id="tos_editor_fr" class="form-control">{{ $terms_of_service_fr }}</textarea>
                                    </div>
                                    <div class="col-md-6" wire:ignore>
                                        <label class="form-label small fw-bold">Terms of Service in English (WYSIWYG)</label>
                                        <textarea id="tos_editor_en" class="form-control">{{ $terms_of_service_en }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <h5 class="fw-bold border-bottom pb-2 mb-3">Politique de Confidentialité</h5>
                                <div class="row g-3">
                                    <div class="col-md-6" wire:ignore>
                                        <label class="form-label small fw-bold">Politique en Français (WYSIWYG)</label>
                                        <textarea id="privacy_editor_fr" class="form-control">{{ $privacy_policy_fr }}</textarea>
                                    </div>
                                    <div class="col-md-6" wire:ignore>
                                        <label class="form-label small fw-bold">Privacy Policy in English (WYSIWYG)</label>
                                        <textarea id="privacy_editor_en" class="form-control">{{ $privacy_policy_en }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-melty px-4 fw-bold">
                    <i class="bi bi-save me-1"></i> Enregistrer tous les paramètres
                </button>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initSettingsSummernote(elId, wireProp) {
            if ($(elId).length) {
                $(elId).summernote({
                    placeholder: 'Rédigez le texte légal...',
                    tabsize: 2,
                    height: 250,
                    toolbar: [
                        ['style', ['style', 'bold', 'italic', 'underline']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
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

        initSettingsSummernote('#tos_editor_fr', 'terms_of_service_fr');
        initSettingsSummernote('#tos_editor_en', 'terms_of_service_en');
        initSettingsSummernote('#privacy_editor_fr', 'privacy_policy_fr');
        initSettingsSummernote('#privacy_editor_en', 'privacy_policy_en');
    });
</script>
@endpush
