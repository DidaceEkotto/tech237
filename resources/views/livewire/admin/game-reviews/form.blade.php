<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">{{ $reviewId ? 'Modifier le Test Jeu Vidéo' : 'Nouveau Test Jeu Vidéo' }}</h3>
            <p class="text-muted small mb-0">Évaluation complète du gameplay, des graphismes, de l'accessibilité, de la bande sonore et note finale sur 20.</p>
        </div>
        <a href="{{ route('admin.game-reviews.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

    <form wire:submit.prevent="save">
        <div class="row g-4">
            
            <!-- Left Main Column -->
            <div class="col-lg-8">
                
                <div class="card card-melty mb-4">
                    <div class="card-header bg-light p-2">
                        <ul class="nav nav-pills card-header-pills" id="gameLangTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold" id="game-fr-tab" data-bs-toggle="pill" data-bs-target="#tab-game-fr" type="button">
                                    🇫🇷 Test Jeu Vidéo (Français)
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold" id="game-en-tab" data-bs-toggle="pill" data-bs-target="#tab-game-en" type="button">
                                    🇬🇧 Game Review (English)
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        <div class="tab-content">
                            
                            <!-- FR TAB -->
                            <div class="tab-pane fade show active" id="tab-game-fr">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nom du jeu vidéo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg @error('game_name') is-invalid @enderror" placeholder="ex: Grand Theft Auto VI, Zelda, Cyberpunk..." wire:model.live="game_name" required>
                                    @error('game_name') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Chapeau / Résumé (Français)</label>
                                    <textarea class="form-control" rows="2" placeholder="Verdict rapide..." wire:model="summary_fr"></textarea>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-success">
                                            <i class="bi bi-plus-circle-fill me-1"></i>Points Forts (Un par ligne)
                                        </label>
                                        <textarea class="form-control" rows="4" placeholder="• Graphismes spectaculaires&#10;• Scénario prenant&#10;• Bande son magistrale" wire:model="pros_fr"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-danger">
                                            <i class="bi bi-dash-circle-fill me-1"></i>Points Faibles (Un par ligne)
                                        </label>
                                        <textarea class="form-control" rows="4" placeholder="• Quelques bugs mineurs&#10;• Durée de vie un peu courte" wire:model="cons_fr"></textarea>
                                    </div>
                                </div>

                                <div class="mb-3" wire:ignore>
                                    <label class="form-label fw-bold">Critique & Test approfondi (WYSIWYG FR) <span class="text-danger">*</span></label>
                                    <textarea id="game_editor_fr" class="form-control">{{ $content_fr }}</textarea>
                                </div>
                                @error('content_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <!-- EN TAB -->
                            <div class="tab-pane fade" id="tab-game-en">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Summary / Excerpt (English)</label>
                                    <textarea class="form-control" rows="2" placeholder="Quick verdict..." wire:model="summary_en"></textarea>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-success">Pros (English)</label>
                                        <textarea class="form-control" rows="3" placeholder="• Great art direction&#10;• Fluid controls" wire:model="pros_en"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-danger">Cons (English)</label>
                                        <textarea class="form-control" rows="3" placeholder="• Minor frame drops" wire:model="cons_en"></textarea>
                                    </div>
                                </div>

                                <div class="mb-3" wire:ignore>
                                    <label class="form-label fw-bold">In-Depth Review (WYSIWYG EN)</label>
                                    <textarea id="game_editor_en" class="form-control">{{ $content_en }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Sidebar Column -->
            <div class="col-lg-4">
                
                <!-- Gaming Rating Breakdown -->
                <div class="card card-melty mb-4">
                    <div class="card-header card-header-melty bg-danger text-white">
                        <i class="bi bi-controller me-2"></i>Critères de Notation Gaming
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold d-flex justify-content-between">
                                <span>Gameplay / Mécaniques</span>
                                <span class="badge bg-secondary">{{ $score_gameplay }} / 20</span>
                            </label>
                            <input type="range" class="form-range" min="0" max="20" step="0.5" wire:model.live="score_gameplay" wire:change="calculateFinalScore">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold d-flex justify-content-between">
                                <span>Graphismes & Technique</span>
                                <span class="badge bg-secondary">{{ $score_graphics }} / 20</span>
                            </label>
                            <input type="range" class="form-range" min="0" max="20" step="0.5" wire:model.live="score_graphics" wire:change="calculateFinalScore">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold d-flex justify-content-between">
                                <span>Accessibilité & Prise en main</span>
                                <span class="badge bg-secondary">{{ $score_accessibility }} / 20</span>
                            </label>
                            <input type="range" class="form-range" min="0" max="20" step="0.5" wire:model.live="score_accessibility" wire:change="calculateFinalScore">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold d-flex justify-content-between">
                                <span>Bande Sonore & Doublage</span>
                                <span class="badge bg-secondary">{{ $score_soundtrack }} / 20</span>
                            </label>
                            <input type="range" class="form-range" min="0" max="20" step="0.5" wire:model.live="score_soundtrack" wire:change="calculateFinalScore">
                        </div>

                        <hr>

                        <div class="p-3 bg-light rounded-3 text-center mb-3 border">
                            <span class="small text-muted fw-bold d-block text-uppercase mb-1">Note Finale Globale</span>
                            <div class="display-5 fw-bold text-danger">{{ $final_score }}<small class="fs-6 text-muted">/20</small></div>
                            <div class="mt-2">
                                <input type="number" step="0.5" min="0" max="20" class="form-control form-control-sm text-center fw-bold mx-auto" style="max-width: 120px;" wire:model.live="final_score">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Platform Category -->
                <div class="card card-melty mb-4">
                    <div class="card-header card-header-melty">
                        <i class="bi bi-dpad text-danger me-2"></i>Plateforme & Médias
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Plateformes disponibles (Multi-sélection) <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(['PlayStation 5', 'PlayStation 4', 'Xbox Series S & X', 'Xbox One', 'Nintendo Switch', 'Nintendo Switch 2', 'PC', 'Mac/Linux', 'Mobile'] as $plat)
                                    <label class="form-check form-check-inline border rounded px-2 py-1 bg-white small m-0">
                                        <input class="form-check-input" type="checkbox" value="{{ $plat }}" wire:model="platforms">
                                        {{ $plat }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Slug URL</label>
                            <input type="text" class="form-control form-control-sm" wire:model="slug">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Jacquette / Image du jeu</label>
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
                            <i class="bi bi-check-circle me-1"></i> {{ $reviewId ? 'Mettre à jour' : 'Publier le test jeu vidéo' }}
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
        function initGameSummernote(elId, wireProp) {
            $(elId).summernote({
                placeholder: 'Rédigez la critique détaillée du jeu vidéo...',
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

        initGameSummernote('#game_editor_fr', 'content_fr');
        initGameSummernote('#game_editor_en', 'content_en');
    });
</script>
@endpush
