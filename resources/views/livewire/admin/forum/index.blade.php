<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-chat-dots-fill text-danger me-2"></i>Modération & Gestion du Forum</h3>
            <p class="text-muted small mb-0">Contrôlez les discussions, les signalements d'abus et l'organisation des catégories.</p>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Navigation Tabs for Moderation -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-4 shadow-sm border">
        <li class="nav-item">
            <button class="nav-link {{ $tab === 'reports' ? 'active' : '' }} fw-bold" wire:click="$set('tab', 'reports')">
                <i class="bi bi-shield-exclamation text-danger me-1"></i> Signalements en attente
                @if(count($reports) > 0)
                    <span class="badge bg-danger ms-1">{{ count($reports) }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $tab === 'topics' ? 'active' : '' }} fw-bold" wire:click="$set('tab', 'topics')">
                <i class="bi bi-chat-left-text me-1"></i> Tous les Sujets (Topics)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $tab === 'categories' ? 'active' : '' }} fw-bold" wire:click="$set('tab', 'categories')">
                <i class="bi bi-folder2-open me-1"></i> Catégories du Forum
            </button>
        </li>
    </ul>

    <!-- TAB 1: REPORTS -->
    @if($tab === 'reports')
        <div class="card card-melty">
            <div class="card-header card-header-melty">
                <i class="bi bi-flag text-danger me-2"></i>Signalements de modération
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Signalé par</th>
                            <th>Motif</th>
                            <th>Contenu ciblé</th>
                            <th>Auteur du contenu</th>
                            <th class="text-end">Actions de modération</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $r)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $r->reporter ? $r->reporter->name : 'Anonyme' }}</div>
                                    <small class="text-muted">{{ $r->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger">{{ $r->reason }}</span>
                                    @if($r->details)
                                        <div class="small text-muted mt-1">{{ Str::limit($r->details, 60) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($r->post)
                                        <div class="small text-dark border-start border-3 border-danger ps-2 py-1 bg-light rounded">
                                            {!! Str::limit(strip_tags($r->post->content), 100) !!}
                                        </div>
                                    @elseif($r->topic)
                                        <div class="fw-bold small">{{ $r->topic->title }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($r->post && $r->post->user)
                                        <span class="badge bg-light text-dark border">{{ $r->post->user->name }}</span>
                                    @elseif($r->topic && $r->topic->user)
                                        <span class="badge bg-light text-dark border">{{ $r->topic->user->name }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" wire:click="approveReportAndDeletePost({{ $r->id }})" class="btn btn-sm btn-danger rounded-pill px-3 me-1" onclick="return confirm('Confirmer la suppression de ce message abusif ?')">
                                        <i class="bi bi-trash me-1"></i> Supprimer le message
                                    </button>
                                    <button type="button" wire:click="dismissReport({{ $r->id }})" class="btn btn-sm btn-light border rounded-pill px-3">
                                        Ignorer
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                    Aucun signalement en attente. Le forum est propre et sain !
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 2: TOPICS -->
    @if($tab === 'topics')
        <div class="card card-melty">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sujet</th>
                            <th>Catégorie</th>
                            <th>Auteur</th>
                            <th>Réponses</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topics as $top)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $top->title }}</div>
                                    <small class="text-muted">Créé {{ $top->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $top->category->color ?? '#6c757d' }}; color: white;">
                                        <i class="bi {{ $top->category->icon ?? 'bi-chat' }} me-1"></i>{{ $top->category->name_fr ?? 'Général' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold small">{{ $top->user ? $top->user->name : 'Utilisateur' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $top->posts_count }} rép.</span>
                                </td>
                                <td>
                                    @if($top->is_solved)
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Résolu</span>
                                    @endif
                                    @if($top->is_pinned)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-pin-angle-fill me-1"></i>Épinglé</span>
                                    @endif
                                    @if($top->is_closed)
                                        <span class="badge bg-dark"><i class="bi bi-lock-fill me-1"></i>Fermé</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" wire:click="toggleTopicPin({{ $top->id }})" class="btn btn-sm btn-light border me-1" title="Épingler/Désépingler">
                                        <i class="bi {{ $top->is_pinned ? 'bi-pin-fill text-warning' : 'bi-pin' }}"></i>
                                    </button>
                                    <button type="button" wire:click="toggleTopicClose({{ $top->id }})" class="btn btn-sm btn-light border me-1" title="Verrouiller/Déverrouiller">
                                        <i class="bi {{ $top->is_closed ? 'bi-lock-fill text-danger' : 'bi-unlock' }}"></i>
                                    </button>
                                    <button type="button" wire:click="deleteTopic({{ $top->id }})" class="btn btn-sm btn-light border text-danger" title="Supprimer" onclick="return confirm('Supprimer ce sujet ?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Aucun sujet créé pour l'instant.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $topics->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 3: CATEGORIES -->
    @if($tab === 'categories')
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card card-melty">
                    <div class="card-header card-header-melty">
                        {{ $isEditingCat ? 'Modifier la Catégorie' : 'Créer une Catégorie Forum' }}
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveCategory">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nom Français <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" placeholder="ex: Développement Web" wire:model.live="cat_name_fr" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nom Anglais</label>
                                <input type="text" class="form-control form-control-sm" placeholder="ex: Web Development" wire:model="cat_name_en">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Slug</label>
                                <input type="text" class="form-control form-control-sm" wire:model="cat_slug" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-7">
                                    <label class="form-label small fw-bold">Icône Bootstrap</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="bi-laptop" wire:model="cat_icon" required>
                                </div>
                                <div class="col-5">
                                    <label class="form-label small fw-bold">Couleur</label>
                                    <input type="color" class="form-control form-control-color form-control-sm w-100" wire:model="cat_color">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Description</label>
                                <textarea class="form-control form-control-sm" rows="2" wire:model="cat_description"></textarea>
                            </div>
                            <button type="submit" class="btn btn-melty w-100">
                                <i class="bi bi-check2 me-1"></i> {{ $isEditingCat ? 'Mettre à jour' : 'Enregistrer' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-melty">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Catégorie</th>
                                    <th>Sujets</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $c)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; background-color: {{ $c->color }};">
                                                    <i class="bi {{ $c->icon }}"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $c->name_fr }}</div>
                                                    <small class="text-muted">/{{ $c->slug }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $c->topics_count }} sujet(s)</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $c->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $c->is_active ? 'Active' : 'Désactivée' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" wire:click="editCategory({{ $c->id }})" class="btn btn-sm btn-light border me-1"><i class="bi bi-pencil"></i></button>
                                            <button type="button" wire:click="deleteCategory({{ $c->id }})" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Supprimer cette catégorie ?')"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Aucune catégorie pour l'instant.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
