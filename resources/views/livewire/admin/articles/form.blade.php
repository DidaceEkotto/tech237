<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">{{ $articleId ? 'Modifier l\'article' : 'Rédiger un article' }}</h3>
            <p class="text-muted small mb-0">Éditeur bilingue français & anglais complet style WordPress avec gestion médias locaux.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Retour à la liste
            </a>
            <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#previewArticleModal">
                <i class="bi bi-eye me-1"></i> Prévisualiser
            </button>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form wire:submit.prevent="save">
        <div class="row g-4">
            
            <!-- Left Main Column (Titles & WYSIWYG Editors) -->
            <div class="col-lg-8">
                
                <!-- Language Switcher Tabs for French and English -->
                <div class="card card-melty mb-4" wire:ignore.self>
                    <div class="card-header bg-light p-2" wire:ignore.self>
                        <ul class="nav nav-pills card-header-pills" id="articleLangTab" role="tablist" wire:ignore.self>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold" id="fr-tab" data-bs-toggle="pill" data-bs-target="#tab-fr" type="button" role="tab">
                                    <span class="me-1">🇫🇷</span> Version Française (Obligatoire)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold" id="en-tab" data-bs-toggle="pill" data-bs-target="#tab-en" type="button" role="tab">
                                    <span class="me-1">🇬🇧</span> English Version (International)
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        <div class="tab-content" id="articleLangContent" wire:ignore.self>
                            
                            <!-- TAB 1: FRENCH -->
                            <div class="tab-pane fade show active" id="tab-fr" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Titre de l'article (Français) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg @error('title_fr') is-invalid @enderror" placeholder="ex: GTA 6 : Nouvelles révélations explosives..." wire:model.live="title_fr" required>
                                    @error('title_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Chapeau / Description courte (Français)</label>
                                    <textarea class="form-control" rows="3" placeholder="Bref résumé accrocheur pour les listes et les réseaux sociaux..." wire:model="summary_fr"></textarea>
                                </div>

                                <div class="mb-3" wire:ignore>
                                    <label class="form-label fw-bold">Corps de l'article (Français) - Éditeur WYSIWYG <span class="text-danger">*</span></label>
                                    <textarea id="editor_fr" class="form-control">{{ $content_fr }}</textarea>
                                </div>
                                @error('content_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <!-- TAB 2: ENGLISH -->
                            <div class="tab-pane fade" id="tab-en" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Article Title (English)</label>
                                    <input type="text" class="form-control form-control-lg" placeholder="e.g. GTA 6: Explosive new gameplay details..." wire:model="title_en">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Summary / Excerpt (English)</label>
                                    <textarea class="form-control" rows="3" placeholder="Brief summary for international readers..." wire:model="summary_en"></textarea>
                                </div>

                                <div class="mb-3" wire:ignore>
                                    <label class="form-label fw-bold">Article Content (English) - WYSIWYG Editor</label>
                                    <textarea id="editor_en" class="form-control">{{ $content_en }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Sidebar Column (Publish settings, Category, Dossier, Tags, Featured Image) -->
            <div class="col-lg-4">
                
                <!-- Publication Box -->
                <div class="card card-melty mb-4">
                    <div class="card-header card-header-melty">
                        <i class="bi bi-send-check text-danger me-2"></i>État & Publication
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Slug URL personnalisé</label>
                            <input type="text" class="form-control form-control-sm @error('slug') is-invalid @enderror" wire:model="slug">
                            @error('slug') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Statut de l'article</label>
                            @php
                                $isEditor = auth('admin')->user()->isEditor();
                            @endphp

                            @if($isEditor)
                                <div class="alert alert-warning small p-2 mb-2">
                                    <i class="bi bi-shield-exclamation me-1"></i>En tant que rédacteur, votre article sera soumis pour review et validé par un administrateur supérieur.
                                </div>
                                <select class="form-select form-select-sm" wire:model="status">
                                    <option value="pending_review">Soumettre pour Review (Validation)</option>
                                    <option value="draft">Brouillon privé</option>
                                </select>
                            @else
                                <select class="form-select form-select-sm" wire:model="status">
                                    <option value="published">Publié immédiatement</option>
                                    <option value="pending_review">En attente de validation</option>
                                    <option value="draft">Brouillon</option>
                                    <option value="rejected">Rejeté</option>
                                </select>
                            @endif
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="isFeatured" wire:model="is_featured">
                            <label class="form-check-label small" for="isFeatured">Mettre à la Une (Hero)</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="isTrending" wire:model="is_trending">
                            <label class="form-check-label small" for="isTrending">Tendance / Breaking news</label>
                        </div>

                        <div class="d-grid gap-2 pt-2 border-top">
                            @if(auth('admin')->user()->canValidateArticles())
                                <button type="button" wire:click="save('published')" class="btn btn-melty fw-bold">
                                    <i class="bi bi-check-circle me-1"></i> {{ $articleId ? 'Mettre à jour & Publier' : 'Publier directement' }}
                                </button>
                            @else
                                <button type="button" wire:click="save('pending_review')" class="btn btn-warning fw-bold text-dark">
                                    <i class="bi bi-hourglass-split me-1"></i> Soumettre pour Validation
                                </button>
                            @endif

                            <button type="button" wire:click="save('draft')" class="btn btn-light border btn-sm text-secondary">
                                <i class="bi bi-save me-1"></i> Enregistrer en brouillon
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Category & Dossier Box -->
                <div class="card card-melty mb-4">
                    <div class="card-header card-header-melty">
                        <i class="bi bi-grid text-danger me-2"></i>Classement & Dossiers
                    </div>
                    <div class="card-body">
                        
                        <!-- Rubrique (Placed before category selection) -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                <i class="bi bi-collection-fill text-danger me-1"></i>Rubrique associée
                            </label>
                            <select class="form-select form-select-sm @error('rubrique_id') is-invalid @enderror" wire:model.live="rubrique_id">
                                <option value="">-- Sélectionner une rubrique (Optionnel) --</option>
                                @foreach($rubriques as $rub)
                                    <option value="{{ $rub->id }}">{{ $rub->name_fr }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">L'article apparaîtra directement dans cette rubrique thématique.</small>
                            @error('rubrique_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catégorie principale</label>
                            <select class="form-select form-select-sm @error('category_id') is-invalid @enderror" wire:model="category_id">
                                <option value="">Sélectionner une catégorie...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name_fr }}</option>
                                    @foreach($cat->children as $sub)
                                        <option value="{{ $sub->id }}">&nbsp;&nbsp;↳ {{ $sub->name_fr }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('category_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                <i class="bi bi-folder-fill text-warning me-1"></i>Dossier thématique (Optionnel)
                            </label>
                            <select class="form-select form-select-sm" wire:model="dossier_id">
                                <option value="">Aucun dossier particulier</option>
                                @foreach($dossiers as $dos)
                                    <option value="{{ $dos->id }}">{{ $dos->title_fr }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Ex: Dossier ChatGPT pour regrouper tous les articles IA.</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Tags associés</label>
                            <div class="d-flex flex-wrap gap-1 p-2 bg-light rounded border" style="max-height: 160px; overflow-y: auto;">
                                @foreach($tags as $t)
                                    <div class="form-check form-check-inline small m-1">
                                        <input class="form-check-input" type="checkbox" id="tag_{{ $t->id }}" value="{{ $t->id }}" wire:model="selectedTags">
                                        <label class="form-check-label" for="tag_{{ $t->id }}">#{{ $t->name_fr }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Featured Image Upload (stored in storage/images/) -->
                <div class="card card-melty mb-4">
                    <div class="card-header card-header-melty">
                        <i class="bi bi-image text-danger me-2"></i>Image de couverture
                    </div>
                    <div class="card-body text-center">
                        @if ($featured_image)
                            <div class="mb-3">
                                <img src="{{ $featured_image->temporaryUrl() }}" class="img-fluid rounded shadow-sm" style="max-height: 180px; width: 100%; object-fit: cover;">
                                <div class="small text-success mt-1"><i class="bi bi-check-circle me-1"></i>Nouvelle image sélectionnée</div>
                            </div>
                        @elseif($existing_image)
                            <div class="mb-3">
                                <img src="{{ asset($existing_image) }}" class="img-fluid rounded shadow-sm" style="max-height: 180px; width: 100%; object-fit: cover;">
                                <div class="small text-muted mt-1">Image actuelle (stockée dans storage/images/)</div>
                            </div>
                        @endif

                        <input type="file" class="form-control form-control-sm" wire:model="featured_image" accept="image/*">
                        @error('featured_image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        <small class="text-muted d-block mt-2">Formats acceptés: JPG, PNG, WEBP, SVG (Max: 10 Mo)</small>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <!-- Static Preview Modal -->
    <div class="modal fade" id="previewArticleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-eye text-primary me-2"></i>Prévisualisation de l'article</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="border rounded-4 p-4 bg-white">
                        <span class="badge bg-danger mb-2">Aperçu direct</span>
                        <h1 class="fw-bold mb-3">{{ $title_fr ?: 'Titre de l\'article' }}</h1>
                        @if($summary_fr)
                            <p class="lead text-muted">{{ $summary_fr }}</p>
                        @endif
                        <hr>
                        <div class="article-content-preview">
                            {!! $content_fr ?: '<p class="text-muted fst-italic">Le contenu apparaîtra ici...</p>' !!}
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fermer la prévisualisation</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initSummernoteEditor(elementId, wireProp) {
            $(elementId).summernote({
                placeholder: 'Rédigez votre article comme sur Microsoft Word / WordPress...',
                tabsize: 2,
                height: 380,
                toolbar: [
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
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
                        uploadSummernoteImage(files[0], elementId, wireProp);
                    }
                }
            });
        }

        // Handle Image Upload directly inside Summernote to storage/images/
        function uploadSummernoteImage(file, editorId, wireProp) {
            let data = new FormData();
            data.append("image", file);
            data.append("_token", "{{ csrf_token() }}");

            fetch("{{ route('admin.images.upload') }}", {
                method: "POST",
                body: data
            })
            .then(res => res.json())
            .then(response => {
                if (response.url) {
                    $(editorId).summernote('insertImage', response.url);
                    @this.set(wireProp, $(editorId).summernote('code'));
                } else {
                    alert('Erreur lors du téléversement de l\'image.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erreur réseau lors de l\'envoi de l\'image.');
            });
        }

        initSummernoteEditor('#editor_fr', 'content_fr');
        initSummernoteEditor('#editor_en', 'content_en');
    });
</script>
@endpush
