<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-chat-square-text text-danger me-2"></i>Modération des Commentaires</h4>
            <p class="text-muted small mb-0">Contrôlez et validez les commentaires et réponses déposés par les utilisateurs de la communauté.</p>
        </div>
        @if($pendingCount > 0)
            <button class="btn btn-success btn-sm rounded-pill px-3 shadow-sm" wire:click="approveAllPending" wire:confirm="Êtes-vous sûr de vouloir approuver tous les commentaires en attente ?">
                <i class="bi bi-check-all me-1"></i> Tout approuver ({{ $pendingCount }})
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-7">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-sm {{ $filterStatus === 'pending' ? 'btn-danger' : 'btn-outline-secondary' }}" wire:click="setFilter('pending')">
                            En attente 
                            <span class="badge {{ $filterStatus === 'pending' ? 'bg-white text-danger' : 'bg-danger text-white' }} ms-1">{{ $pendingCount }}</span>
                        </button>
                        <button type="button" class="btn btn-sm {{ $filterStatus === 'approved' ? 'btn-danger' : 'btn-outline-secondary' }}" wire:click="setFilter('approved')">
                            Approuvés ({{ $approvedCount }})
                        </button>
                        <button type="button" class="btn btn-sm {{ $filterStatus === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' }}" wire:click="setFilter('rejected')">
                            Rejetés ({{ $rejectedCount }})
                        </button>
                        <button type="button" class="btn btn-sm {{ $filterStatus === 'all' ? 'btn-danger' : 'btn-outline-secondary' }}" wire:click="setFilter('all')">
                            Tous
                        </button>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Rechercher par auteur, email ou contenu..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments List -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 220px;">Auteur</th>
                        <th>Commentaire</th>
                        <th>Cible & Sujet</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="text-end" style="width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comments as $comment)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($comment->user?->avatar)
                                        <img src="{{ asset($comment->user->avatar) }}" alt="{{ $comment->user->name }}" class="rounded-circle object-fit-cover" style="width: 34px; height: 34px;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 13px;">
                                            {{ strtoupper(substr($comment->user?->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold small">{{ $comment->user?->name ?? 'Utilisateur supprimé' }}</div>
                                        <div class="text-muted small" style="font-size: 11px;">{{ $comment->user?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($comment->parent_id)
                                    <div class="mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary small">
                                            <i class="bi bi-reply-fill me-1"></i>En réponse à {{ $comment->parent?->user?->name ?? 'un commentaire' }}
                                        </span>
                                    </div>
                                @endif
                                <div class="text-break small" style="max-width: 480px;">
                                    {{ $comment->content }}
                                </div>
                            </td>
                            <td>
                                @if($comment->article)
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-newspaper me-1"></i>Article
                                    </span>
                                    <div class="small text-truncate mt-1" style="max-width: 200px;" title="{{ $comment->article->title }}">
                                        {{ $comment->article->title }}
                                    </div>
                                @elseif($comment->interview)
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-mic me-1"></i>Interview
                                    </span>
                                    <div class="small text-truncate mt-1" style="max-width: 200px;" title="{{ $comment->interview->title }}">
                                        {{ $comment->interview->title }}
                                    </div>
                                @elseif($comment->project)
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-kanban me-1"></i>Projet
                                    </span>
                                    <div class="small text-truncate mt-1" style="max-width: 200px;" title="{{ $comment->project->title }}">
                                        {{ $comment->project->title }}
                                    </div>
                                @else
                                    <span class="text-muted small">Contenu général</span>
                                @endif
                            </td>
                            <td>
                                @if($comment->status === 'approved')
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>Approuvé</span>
                                @elseif($comment->status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle me-1"></i>Rejeté</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning"><i class="bi bi-clock me-1"></i>En attente</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $comment->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @if($comment->status !== 'approved')
                                        <button class="btn btn-outline-success" wire:click="approve({{ $comment->id }})" title="Approuver">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    @endif
                                    @if($comment->status !== 'rejected')
                                        <button class="btn btn-outline-warning" wire:click="reject({{ $comment->id }})" title="Rejeter">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    @endif
                                    <button class="btn btn-outline-danger" wire:click="delete({{ $comment->id }})" wire:confirm="Confirmer la suppression définitive ?" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-chat-dots fs-3 d-block mb-2 text-muted"></i>
                                Aucun commentaire trouvé dans cette catégorie.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($comments->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $comments->links() }}
            </div>
        @endif
    </div>
</div>
