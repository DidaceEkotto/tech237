<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">{{ $interviewId ? 'Modifier l\'Interview' : 'Rédiger une Interview' }}</h3>
            <p class="text-muted small mb-0">Entretien bilingue français & anglais, vidéo YouTube et mise en forme WYSIWYG.</p>
        </div>
        <a href="{{ route('admin.interviews.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

    <form wire:submit.prevent="save">
        <div class="row g-4">
            
            <!-- Main Content (8 cols) -->
            <div class="col-lg-8">
                
                <div class="card card-melty mb-4">
                    <div class="card-header bg-light p-2">
                        <ul class="nav nav-pills card-header-pills" id="interviewLangTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold" id="interview-fr-tab" data-bs-toggle="pill" data-bs-target="#tab-int-fr" type="button">
                                    🇫🇷 Interview en Français
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold" id="interview-en-tab" data-bs-toggle="pill" data-bs-target="#tab-int-en" type="button">
                                    🇬🇧 Interview in English
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        <div class="tab-content">
                            
                            <!-- FR -->
                            <div class="tab-pane fade show active" id="tab-int-fr">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nom de la personnalité <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('interviewee_name') is-invalid @enderror" placeholder="ex: Guillermo del Toro" wire:model="interviewee_name" required>
                                        @error('interviewee_name') <div class="text-danger small">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Rôle / Titre / Statut</label>
                                        <input type="text" class="form-control" placeholder="ex: Réalisateur & Scénariste" wire:model="interviewee_role">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Titre de l'interview (Français) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg @error('title_fr') is-invalid @enderror" placeholder="ex: Rencontre exclusive avec..." wire:model.live="title_fr" required>
                                    @error('title_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Chapeau / Introduction (Français)</label>
                                    <textarea class="form-control" rows="2" placeholder="Brève introduction..." wire:model="summary_fr"></textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-bold mb-0"><i class="bi bi-chat-quote-fill text-danger me-1"></i>Questions & Réponses de l'Interview (Français)</label>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" wire:click="addQaBlock">
                                            <i class="bi bi-plus-circle me-1"></i> Ajouter une question
                                        </button>
                                    </div>
                                    <p class="text-muted small mb-3">Ajoutez chaque question posée et la réponse de l'invité. Elles seront magnifiquement présentées sous forme de dialogue interactif en front.</p>

                                    @foreach($qa_blocks as $idx => $block)
                                        <div class="card mb-3 border shadow-sm rounded-3">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                                <span class="fw-bold small text-dark"><i class="bi bi-question-circle text-danger me-1"></i>Question #{{ $idx + 1 }}</span>
                                                @if(count($qa_blocks) > 1)
                                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none" wire:click="removeQaBlock({{ $idx }})">
                                                        <i class="bi bi-trash me-1"></i>Supprimer ce bloc
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="mb-3">
                                                    <label class="form-label extra-small fw-bold text-muted mb-1">Question (Journaliste Melty)</label>
                                                    <input type="text" class="form-control form-control-sm" placeholder="ex: Quelle a été l'inspiration principale pour ce projet ?" wire:model="qa_blocks.{{ $idx }}.question_fr">
                                                </div>
                                                <div>
                                                    <label class="form-label extra-small fw-bold text-muted mb-1">Réponse (Personnalité)</label>
                                                    <textarea class="form-control form-control-sm" rows="4" placeholder="Réponse détaillée..." wire:model="qa_blocks.{{ $idx }}.answer_fr"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mb-3" wire:ignore>
                                    <label class="form-label fw-bold">Texte libre complémentaire / Conclusion (WYSIWYG FR)</label>
                                    <textarea id="interview_editor_fr" class="form-control">{{ $content_fr }}</textarea>
                                </div>
                                @error('content_fr') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <!-- EN -->
                            <div class="tab-pane fade" id="tab-int-en">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Interview Title (English)</label>
                                    <input type="text" class="form-control form-control-lg" placeholder="e.g. Exclusive chat with..." wire:model="title_en">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Summary / Intro (English)</label>
                                    <textarea class="form-control" rows="2" placeholder="Short intro..." wire:model="summary_en"></textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-bold mb-0"><i class="bi bi-chat-quote-fill text-danger me-1"></i>Questions & Answers (English)</label>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" wire:click="addQaBlock">
                                            <i class="bi bi-plus-circle me-1"></i> Add Question
                                        </button>
                                    </div>
                                    <p class="text-muted small mb-3">Translate or provide the Q&A in English for global readers.</p>

                                    @foreach($qa_blocks as $idx => $block)
                                        <div class="card mb-3 border shadow-sm rounded-3">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                                <span class="fw-bold small text-dark"><i class="bi bi-question-circle text-danger me-1"></i>Question #{{ $idx + 1 }}</span>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="mb-3">
                                                    <label class="form-label extra-small fw-bold text-muted mb-1">Question (Melty Journalist)</label>
                                                    <input type="text" class="form-control form-control-sm" placeholder="e.g. What inspired you to take on this challenge?" wire:model="qa_blocks.{{ $idx }}.question_en">
                                                </div>
                                                <div>
                                                    <label class="form-label extra-small fw-bold text-muted mb-1">Answer (Personality)</label>
                                                    <textarea class="form-control form-control-sm" rows="4" placeholder="Detailed answer..." wire:model="qa_blocks.{{ $idx }}.answer_en"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mb-3" wire:ignore>
                                    <label class="form-label fw-bold">Interview Transcript / Concluding Remarks (WYSIWYG EN)</label>
                                    <textarea id="interview_editor_en" class="form-control">{{ $content_en }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar (4 cols) -->
            <div class="col-lg-4">
                
                <div class="card card-melty mb-4">
                    <div class="card-header card-header-melty">
                        <i class="bi bi-camera-video text-danger me-2"></i>Vidéo & Médias
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Lien Vidéo YouTube (Intégration)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-youtube text-danger"></i></span>
                                <input type="url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." wire:model="video_url">
                            </div>
                            <small class="text-muted">Si renseigné, le lecteur vidéo apparaîtra en haut de l'interview.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Slug URL</label>
                            <input type="text" class="form-control form-control-sm" wire:model="slug">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Photo de la personnalité / Couverture</label>
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
                            <i class="bi bi-check-circle me-1"></i> {{ $interviewId ? 'Mettre à jour' : 'Publier l\'interview' }}
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
        function initInterviewSummernote(elId, wireProp) {
            $(elId).summernote({
                placeholder: 'Transcription de l\'interview (Questions Melty / Réponses)...',
                tabsize: 2,
                height: 380,
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

        initInterviewSummernote('#interview_editor_fr', 'content_fr');
        initInterviewSummernote('#interview_editor_en', 'content_en');
    });
</script>
@endpush
