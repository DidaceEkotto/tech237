<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Interviews Exclusives</h3>
            <p class="text-muted small mb-0">Entretiens avec acteurs, créateurs, réalisateurs et personnalités de la pop-culture.</p>
        </div>
        <a href="{{ route('admin.interviews.create') }}" class="btn btn-melty shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Rédiger une Interview
        </a>
    </div>

    <!-- Search -->
    <div class="card card-melty mb-4">
        <div class="card-body p-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Rechercher par nom de la personnalité ou titre...">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card card-melty">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">Photo</th>
                        <th>Personnalité & Rôle</th>
                        <th>Titre de l'interview</th>
                        <th>Vidéo</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($interviews as $i)
                        <tr>
                            <td>
                                @if($i->cover_image)
                                    <img src="{{ asset($i->cover_image) }}" class="rounded shadow-sm" style="width: 48px; height: 48px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                        <i class="bi bi-person-video2 fs-4"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $i->interviewee_name }}</div>
                                <small class="text-muted">{{ $i->interviewee_role ?? 'Invité' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $i->title_fr }}</div>
                                <small class="text-muted">Par {{ $i->author->name ?? 'Admin' }}</small>
                            </td>
                            <td>
                                @if($i->video_url)
                                    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-youtube me-1"></i>Vidéo liée</span>
                                @else
                                    <span class="text-muted small">Texte</span>
                                @endif
                            </td>
                            <td>
                                @if($i->status === 'published')
                                    <span class="badge bg-success-subtle text-success">Publié</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Brouillon</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.interviews.edit', $i->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-danger" 
                                    onclick="confirmStaticAction('Supprimer l\'interview', 'Supprimer définitivement l\'interview de \'{{ addslashes($i->interviewee_name) }}\' ?', function() { @this.call('delete', {{ $i->id }}); })"
                                    title="Supprimer"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-mic fs-1 d-block mb-2 text-secondary"></i>
                                Aucune interview enregistrée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $interviews->links() }}
        </div>
    </div>
</div>
