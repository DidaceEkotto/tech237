<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">{{ $entrepreneurId ? 'Modifier Entrepreneur' : 'Nouvel Entrepreneur du mois' }}</h3>
            <p class="text-muted small mb-0">Rédigez son histoire, ses difficultés rencontrées, sa vision tech et créez sa galerie photos.</p>
        </div>
        <a href="{{ route('admin.entrepreneurs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

    <form wire:submit.prevent="save">
        @if($errors->any())
            <div class="alert alert-danger mb-4 shadow-sm">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Erreur lors de l'enregistrement :</h6>
                <ul class="mb-0 small">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row g-4">
            <!-- Left (8 cols): Content, Journey, Challenges, Vision, Video, Gallery -->
            <div class="col-lg-8">
                <div class="card card-melty mb-4">
                    <div class="card-header bg-light p-2">
                        <ul class="nav nav-pills card-header-pills" id="entrepreneurLangTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold" data-bs-toggle="pill" data-bs-target="#ent-fr" type="button">
                                    🇫🇷 Version Française
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold" data-bs-toggle="pill" data-bs-target="#ent-en" type="button">
                                    🇬🇧 English Version
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        <div class="tab-content">
                            <!-- FR -->
                            <div class="tab-pane fade show active" id="ent-fr">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-7">
                                        <label class="form-label fw-bold">Nom complet de l'entrepreneur <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" placeholder="ex: Jean-Paul Kamga" wire:model="name" required>
                                        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Rôle / Titre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" placeholder="ex: Fondateur & CEO chez PayCam" wire:model="role_or_title" required>
                                        @error('role_or_title') <div class="text-danger small">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mb-4" wire:ignore>
                                    <label class="form-label fw-bold">Biographie & Présentation courte (Français) <span class="text-danger">*</span></label>
                                    <textarea id="bio_editor_fr" class="form-control" rows="3" placeholder="Qui est-il / elle ? D'où vient-il / elle ?...">{!! $bio_fr !!}</textarea>
                                </div>
                                @error('bio_fr') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

                                <div class="mb-4" wire:ignore>
                                    <label class="form-label fw-bold">Son Parcours & Ses Débuts (Français) <span class="text-danger">*</span></label>
                                    <textarea id="journey_editor_fr" class="form-control" rows="4" placeholder="Comment tout a commencé, études, premiers projets, déclic...">{!! $journey_fr !!}</textarea>
                                </div>
                                @error('journey_fr') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

                                <div class="mb-4" wire:ignore>
                                    <label class="form-label fw-bold">Difficultés & Obstacles surmontés (Français) <span class="text-danger">*</span></label>
                                    <textarea id="challenges_editor_fr" class="form-control" rows="4" placeholder="Les moments durs : financements, doutes, échecs, solutions trouvées...">{!! $challenges_fr !!}</textarea>
                                </div>
                                @error('challenges_fr') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

                                <div class="mb-4" wire:ignore>
                                    <label class="form-label fw-bold">Vision & Conseils pour les jeunes tech (Français) <span class="text-danger">*</span></label>
                                    <textarea id="vision_editor_fr" class="form-control" rows="4" placeholder="Quels conseils donnerait-il / elle à un jeune voulant se lancer ?...">{!! $vision_fr !!}</textarea>
                                </div>
                                @error('vision_fr') <div class="text-danger small mb-3">{{ $message }}</div> @enderror
                            </div>

                            <!-- EN -->
                            <div class="tab-pane fade" id="ent-en">
                                <div class="mb-4" wire:ignore>
                                    <label class="form-label fw-bold">Bio & Overview (English)</label>
                                    <textarea id="bio_editor_en" class="form-control" rows="3" placeholder="Short intro in English...">{!! $bio_en !!}</textarea>
                                </div>

                                <div class="mb-4" wire:ignore>
                                    <label class="form-label fw-bold">Journey & Beginnings (English)</label>
                                    <textarea id="journey_editor_en" class="form-control" rows="4" placeholder="How it started...">{!! $journey_en !!}</textarea>
                                </div>

                                <div class="mb-4" wire:ignore>
                                    <label class="form-label fw-bold">Challenges & Overcoming Difficulties (English)</label>
                                    <textarea id="challenges_editor_en" class="form-control" rows="4" placeholder="Hard times, funding hurdles...">{!! $challenges_en !!}</textarea>
                                </div>

                                <div class="mb-4" wire:ignore>
                                    <label class="form-label fw-bold">Vision & Advice for Young Tech Builders (English)</label>
                                    <textarea id="vision_editor_en" class="form-control" rows="4" placeholder="Key insights and advice...">{!! $vision_en !!}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Video Presentation -->
                        <div class="mt-4 pt-4 border-top">
                            <label class="form-label fw-bold"><i class="bi bi-youtube text-danger me-1"></i> Vidéo de Présentation (Lien YouTube)</label>
                            <input type="url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." wire:model="video_url">
                            <small class="text-muted">Si renseignée, la vidéo sera affichée dans un lecteur immersif en début d'article.</small>
                        </div>
                    </div>
                </div>

                <!-- Gallery Section (Galerie Photos pour Lightbox) -->
                <div class="card card-melty mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="bi bi-images text-success me-2"></i>Galerie Photos de l'Entrepreneur (Lightbox)</span>
                        <span class="badge bg-secondary">{{ count($gallery) }} photo(s) actuelle(s)</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ajouter des photos à la galerie</label>
                            <div class="d-flex gap-2">
                                <input type="file" class="form-control" wire:model="newGalleryImages" multiple accept="image/*">
                                <button type="button" wire:click="uploadGalleryImages" wire:loading.attr="disabled" class="btn btn-success text-nowrap">
                                    <span wire:loading.remove wire:target="uploadGalleryImages"><i class="bi bi-cloud-arrow-up me-1"></i> Ajouter à la galerie</span>
                                    <span wire:loading wire:target="uploadGalleryImages"><i class="spinner-border spinner-border-sm me-1"></i> Envoi...</span>
                                </button>
                            </div>
                            <small class="text-muted">Sélectionnez une ou plusieurs photos et cliquez sur "Ajouter à la galerie". Elles s'enregistrent immédiatement sans avoir besoin de valider tout le formulaire.</small>
                            @if(session()->has('gallery_success'))
                                <div class="alert alert-success alert-dismissible fade show mt-2 py-2 small" role="alert">
                                    <i class="bi bi-check-circle me-1"></i> {{ session('gallery_success') }}
                                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                        </div>

                        <!-- Preview Existing Gallery -->
                        @if(!empty($gallery))
                            <div class="row g-3 mt-2">
                                @foreach($gallery as $idx => $img)
                                    <div class="col-4 col-md-3 position-relative">
                                        <div class="rounded-3 overflow-hidden border shadow-sm position-relative" style="height: 110px;">
                                            <img src="/{{ $img }}" class="w-100 h-100 object-fit-cover" alt="Galerie {{ $idx }}">
                                            <button type="button" wire:click="removeGalleryImage({{ $idx }})" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" title="Supprimer cette photo">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted small p-3 bg-light rounded text-center">
                                Aucune image dans la galerie pour l'instant. Ajoutez-en pour enrichir le portrait de l'entrepreneur !
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right (4 cols): Meta, Startup, Photo, Socials, Status -->
            <div class="col-lg-4">
                <div class="card card-melty mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-gear-fill me-2"></i>Paramètres &amp; Publication
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Période / Mois à l'honneur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" placeholder="ex: Novembre 2026" wire:model="month_year" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Startup associée</label>
                            <select class="form-select form-select-sm" wire:model="startup_id">
                                <option value="">-- Aucune startup (Indépendant) --</option>
                                @foreach($startups as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->city ?? 'Cameroun' }})</option>
                                @endforeach
                            </select>
                            @error('startup_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <!-- Photo Principale -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Photo Principale / Portrait</label>
                            @if($photo)
                                <div class="mb-2 text-center">
                                    <img src="{{ $photo->temporaryUrl() }}" class="rounded-3 border shadow-sm" style="max-height: 140px; max-width: 100%; object-fit: cover;">
                                    <div class="badge bg-success mt-1 d-inline-block">Nouvelle photo sélectionnée</div>
                                </div>
                            @elseif($existing_photo)
                                <div class="mb-2 text-center">
                                    <img src="/{{ $existing_photo }}" class="rounded-3 border shadow-sm" style="max-height: 140px; max-width: 100%; object-fit: cover;">
                                </div>
                            @endif
                            <input type="file" class="form-control form-control-sm" wire:model.live="photo" accept="image/*">
                            <div wire:loading wire:target="photo" class="text-primary small mt-1">
                                <i class="spinner-border spinner-border-sm me-1"></i> Chargement de la prévisualisation...
                            </div>
                            @error('photo') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <!-- Statut -->
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="is_current_month" wire:model="is_current_month">
                            <label class="form-check-label small fw-bold" for="is_current_month">
                                Marquer comme Entrepreneur du mois Actuel
                            </label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_published" wire:model="is_published">
                            <label class="form-check-label small fw-bold" for="is_published">
                                Publié et visible en ligne
                            </label>
                        </div>

                        <hr>

                        <!-- Réseaux Sociaux -->
                        <h6 class="fw-bold small mb-2"><i class="bi bi-share me-1"></i> Liens Sociaux & Web</h6>
                        <div class="mb-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-linkedin text-primary"></i></span>
                                <input type="url" class="form-control" placeholder="https://linkedin.com/in/..." wire:model="social_linkedin">
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-twitter-x text-dark"></i></span>
                                <input type="url" class="form-control" placeholder="https://x.com/..." wire:model="social_twitter">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-globe text-secondary"></i></span>
                                <input type="url" class="form-control" placeholder="https://mon-site.com" wire:model="social_website">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-melty py-2 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> {{ $entrepreneurId ? 'Mettre à jour l\'Entrepreneur' : 'Enregistrer et Publier' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initEntrepreneurSummernote(elementId, wireProp) {
            $(elementId).summernote({
                placeholder: 'Rédigez avec mise en forme, vidéos, listes, images...',
                tabsize: 2,
                height: 250,
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
                    onImageUpload: function(files) {
                        uploadSummernoteImage(files[0], elementId, wireProp);
                    }
                }
            });
        }

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

        initEntrepreneurSummernote('#bio_editor_fr', 'bio_fr');
        initEntrepreneurSummernote('#journey_editor_fr', 'journey_fr');
        initEntrepreneurSummernote('#challenges_editor_fr', 'challenges_fr');
        initEntrepreneurSummernote('#vision_editor_fr', 'vision_fr');

        initEntrepreneurSummernote('#bio_editor_en', 'bio_en');
        initEntrepreneurSummernote('#journey_editor_en', 'journey_en');
        initEntrepreneurSummernote('#challenges_editor_en', 'challenges_en');
        initEntrepreneurSummernote('#vision_editor_en', 'vision_en');

        $('form').on('submit', function() {
            @this.set('bio_fr', $('#bio_editor_fr').summernote('code'), false);
            @this.set('journey_fr', $('#journey_editor_fr').summernote('code'), false);
            @this.set('challenges_fr', $('#challenges_editor_fr').summernote('code'), false);
            @this.set('vision_fr', $('#vision_editor_fr').summernote('code'), false);
            @this.set('bio_en', $('#bio_editor_en').summernote('code'), false);
            @this.set('journey_en', $('#journey_editor_en').summernote('code'), false);
            @this.set('challenges_en', $('#challenges_editor_en').summernote('code'), false);
            @this.set('vision_en', $('#vision_editor_en').summernote('code'), false);
        });
    });
</script>
@endpush
