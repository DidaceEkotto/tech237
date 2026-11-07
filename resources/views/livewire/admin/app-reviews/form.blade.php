<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">{{ $reviewId ? 'Modifier le Test Application' : 'Nouveau Test Application' }}</h3>
            <p class="text-muted small mb-0">Évaluation détaillée de l'UI, de l'UX, de la prise en main et note finale sur 20.</p>
        </div>
        <a href="{{ route('admin.app-reviews.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

    <form wire:submit.prevent="save">
        <div class="row g-4">
            
            <!-- Left Main Column -->
            <div class="col-lg-8">
                
                <div class="card card-melty mb-4">
                    <div class="card-header bg-light p-2">
                        <ul class="nav nav-pills card-header-pills" id="appReviewLangTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold" id="app-fr-tab" data-bs-toggle="pill" data-bs-target="#tab-app-fr" type="button">
                                    🇫🇷 Test en Français (Obligatoire)
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold" id="app-en-tab" data-bs-toggle="pill" data-bs-target="#tab-app-en" type="button">
                                    🇬🇧 Review in English
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        <div class="tab-content">
                            
                            <!-- French Content -->
                            <div class="tab-pane fade show active" id="tab-app-fr">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nom de l'application <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg @error('app_name') is-invalid @enderror" placeholder="ex: ChatGPT Mobile, Notion, TikTok..." wire:model.live="app_name" required>
                                    @error('app_name') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Résumé / Verdict rapide (Français)</label>
                                    <textarea class="form-control" rows="2" placeholder="Verdict express..." wire:model="summary_fr"></textarea>
                                </div>

                                <div class="mb-3" wire:ignore>
                                    <label class="form-label fw-bold">Test complet & Analyse détaillée (WYSIWYG FR) <span class="text-danger">*</span></label>
                                    <textarea id="app_editor_fr" class="form-control">{{ $content_fr }}</textarea>
                                </div>
                                @error('content_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <!-- English Content -->
                            <div class="tab-pane fade" id="tab-app-en">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Quick verdict / Summary (English)</label>
                                    <textarea class="form-control" rows="2" placeholder="Brief summary in English..." wire:model="summary_en"></textarea>
                                </div>

                                <div class="mb-3" wire:ignore>
                                    <label class="form-label fw-bold">Full In-depth Review (WYSIWYG EN)</label>
                                    <textarea id="app_editor_en" class="form-control">{{ $content_en }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Stores Links -->
                <div class="card card-melty mb-4">
                    <div class="card-header card-header-melty">
                        <i class="bi bi-link-45deg text-danger me-2"></i>Liens de téléchargement & Accès officiel
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Google Play Store</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-google-play"></i></span>
                                    <input type="url" class="form-control" placeholder="https://play.google.com/store/apps/..." wire:model="playstore_url">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Apple App Store</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-apple"></i></span>
                                    <input type="url" class="form-control" placeholder="https://apps.apple.com/app/..." wire:model="appstore_url">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Site Web Officiel</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                    <input type="url" class="form-control" placeholder="https://mon-application.com" wire:model="website_url">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Huawei AppGallery</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-bag-check"></i></span>
                                    <input type="url" class="form-control" placeholder="https://appgallery.huawei.com/app/..." wire:model="appgallery_url">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Sidebar Column (Scores & Store Settings) -->
            <div class="col-lg-4">
                
                <!-- Rating System -->
                <div class="card card-melty mb-4">
                    <div class="card-header card-header-melty bg-danger text-white">
                        <i class="bi bi-star-fill me-2"></i>Barème de Notation sur 20
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold d-flex justify-content-between">
                                <span>Note UI (Design / Interface)</span>
                                <span class="badge bg-secondary">{{ $score_ui }} / 20</span>
                            </label>
                            <input type="range" class="form-range" min="0" max="20" step="0.5" wire:model.live="score_ui" wire:change="calculateFinalScore">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold d-flex justify-content-between">
                                <span>Note UX (Expérience Utilisateur)</span>
                                <span class="badge bg-secondary">{{ $score_ux }} / 20</span>
                            </label>
                            <input type="range" class="form-range" min="0" max="20" step="0.5" wire:model.live="score_ux" wire:change="calculateFinalScore">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold d-flex justify-content-between">
                                <span>Note Prise en Main (Ergonomie)</span>
                                <span class="badge bg-secondary">{{ $score_handling }} / 20</span>
                            </label>
                            <input type="range" class="form-range" min="0" max="20" step="0.5" wire:model.live="score_handling" wire:change="calculateFinalScore">
                        </div>

                        <hr>

                        <div class="p-3 bg-light rounded-3 text-center mb-3 border">
                            <span class="small text-muted fw-bold d-block text-uppercase mb-1">Note Finale Globale</span>
                            <div class="display-5 fw-bold text-danger">{{ $final_score }}<small class="fs-6 text-muted">/20</small></div>
                            <small class="text-secondary">(Moyenne pondérée ou personnalisable)</small>
                            <div class="mt-2">
                                <input type="number" step="0.5" min="0" max="20" class="form-control form-control-sm text-center fw-bold mx-auto" style="max-width: 120px;" wire:model.live="final_score">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Category & Logo Box -->
                <div class="card card-melty mb-4">
                    <div class="card-header card-header-melty">
                        <i class="bi bi-tag text-danger me-2"></i>Catégorie & Store
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Plateformes & Catégories (Multi-sélection)</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(['Android', 'iOS', 'Web', 'Windows', 'macOS', 'Huawei AppGallery'] as $plat)
                                    <label class="form-check form-check-inline border rounded px-2 py-1 bg-white small m-0">
                                        <input class="form-check-input" type="checkbox" value="{{ $plat }}" wire:model="platforms">
                                        {{ $plat }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Icône associée</label>
                            <select class="form-select form-select-sm" wire:model="category_logo">
                                <option value="bi-google-play">Google Play (bi-google-play)</option>
                                <option value="bi-apple">Apple (bi-apple)</option>
                                <option value="bi-globe">Web / Navigateur (bi-globe)</option>
                                <option value="bi-laptop">Desktop / Ordinateur (bi-laptop)</option>
                                <option value="bi-phone">Mobile général (bi-phone)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Slug URL</label>
                            <input type="text" class="form-control form-control-sm" wire:model="slug">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Image / Logo de l'application</label>
                            @if($cover_image)
                                <img src="{{ $cover_image->temporaryUrl() }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @elseif($existing_image)
                                <img src="{{ asset($existing_image) }}" class="img-fluid rounded mb-2" style="max-height: 120px; width: 100%; object-fit: cover;">
                            @endif
                            <input type="file" class="form-control form-control-sm" wire:model="cover_image" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Statut</label>
                            <select class="form-select form-select-sm" wire:model="status">
                                <option value="published">Publié</option>
                                <option value="draft">Brouillon</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-melty w-100 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> {{ $reviewId ? 'Mettre à jour le test' : 'Publier le test' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initAppSummernote(elId, wireProp) {
            $(elId).summernote({
                placeholder: 'Rédigez l\'analyse de l\'application...',
                tabsize: 2,
                height: 350,
                toolbar: [
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview']]
                ],
                callbacks: {
                    onChange: function(contents) {
                        @this.set(wireProp, contents, false);
                    },
                    onKeydown: function(e) {
                        if (e.keyCode === 13) {
                            e.stopPropagation();
                        }
                    },
                    onImageUpload: function(files) {
                        let data = new FormData();
                        data.append("image", files[0]);
                        data.append("_token", "{{ csrf_token() }}");

                        fetch("{{ route('admin.images.upload') }}", {
                            method: "POST",
                            body: data
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.url) {
                                $(elId).summernote('insertImage', res.url);
                                @this.set(wireProp, $(elId).summernote('code'));
                            }
                        });
                    }
                }
            });
        }

        initAppSummernote('#app_editor_fr', 'content_fr');
        initAppSummernote('#app_editor_en', 'content_en');
    });
</script>
@endpush
