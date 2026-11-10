<div>
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-chat-dots-fill text-info me-2"></i>Messagerie &amp; Communications Administrateur</h4>
            <p class="text-muted small mb-0">Communiquez directement avec vos membres, diffusez des annonces officielles et répondez aux sollicitations.</p>
        </div>
        
        <div class="d-flex gap-2 align-items-center">
            @if($viewMode !== 'compose')
                <button wire:click="openCompose('all')" class="btn btn-success rounded-pill px-3 fw-bold shadow-xs">
                    <i class="bi bi-broadcast me-1"></i> Diffuser une annonce générale
                </button>
                <button wire:click="openCompose('specific')" class="btn btn-dark rounded-pill px-3 fw-bold shadow-xs">
                    <i class="bi bi-pencil-square me-1"></i> Nouveau message ciblé
                </button>
            @else
                <button wire:click="$set('viewMode', 'threads')" class="btn btn-outline-secondary rounded-pill px-3 shadow-xs">
                    <i class="bi bi-arrow-left me-1"></i> Retour aux discussions
                </button>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">Total Messages Échangés</div>
                        <div class="h3 fw-bold my-1 text-dark">{{ $stats['total_messages'] }}</div>
                        <span class="small text-muted">Sur la plateforme</span>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 fs-3">
                        <i class="bi bi-chat-left-text-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">Messages Non Lus</div>
                        <div class="h3 fw-bold my-1 text-warning">{{ $stats['unread_messages'] }}</div>
                        <span class="small text-warning fw-semibold">En attente de réponse</span>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 fs-3">
                        <i class="bi bi-envelope-exclamation-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">Annonces Officielles</div>
                        <div class="h3 fw-bold my-1 text-success">{{ $stats['admin_broadcasts'] }}</div>
                        <span class="small text-success fw-semibold">Diffusions de l'équipe</span>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 fs-3">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">Membres Inscrits</div>
                        <div class="h3 fw-bold my-1 text-primary">{{ $stats['total_users'] }}</div>
                        <span class="small text-muted">Audience potentielle</span>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- VIEW MODE: COMPOSE MESSAGE -->
    @if($viewMode === 'compose')
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h5 class="fw-bold text-dark mb-3 border-bottom pb-3">
                <i class="bi bi-send-fill text-success me-2"></i>
                {{ $recipientType === 'all' ? 'Diffusion d\'un message à l\'ensemble de la communauté' : 'Envoi d\'un message ciblé à un ou plusieurs membres' }}
            </h5>

            <form wire:submit.prevent="sendMessage">
                <!-- Target Audience Selector -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark">Destinataires du message <span class="text-danger">*</span></label>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="card p-3 border rounded-4 cursor-pointer h-100 {{ $recipientType === 'all' ? 'border-success bg-success-subtle bg-opacity-10' : 'bg-light' }}">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" wire:model.live="recipientType" value="all" class="form-check-input mt-0">
                                    <div>
                                        <strong class="d-block text-dark">Tous les utilisateurs actifs</strong>
                                        <small class="text-muted">Diffusion globale à l'ensemble des {{ $stats['total_users'] }} membres du site.</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="card p-3 border rounded-4 cursor-pointer h-100 {{ $recipientType === 'specific' ? 'border-primary bg-primary-subtle bg-opacity-10' : 'bg-light' }}">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" wire:model.live="recipientType" value="specific" class="form-check-input mt-0">
                                    <div>
                                        <strong class="d-block text-dark">Membres spécifiques</strong>
                                        <small class="text-muted">Sélectionnez un ou plusieurs membres par nom ou email.</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Specific user selection -->
                @if($recipientType === 'specific')
                    <div class="mb-4 p-3 bg-light rounded-4 border">
                        <label class="form-label small fw-bold text-dark">Rechercher et sélectionner des membres :</label>
                        <input type="text" wire:model.live.debounce.300ms="userSearch" class="form-control rounded-pill px-3 mb-2" placeholder="Taper un nom ou une adresse email...">

                        <!-- Selected users chips -->
                        @if(!empty($selectedUserIds))
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @foreach($selectedUserIds as $uId)
                                    @php $uObj = \App\Models\User::find($uId); @endphp
                                    @if($uObj)
                                        <span class="badge bg-dark text-white rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                                            <span>{{ $uObj->name }} ({{ $uObj->email }})</span>
                                            <button type="button" wire:click="toggleUserSelection({{ $uId }})" class="btn-close btn-close-white" style="font-size: 8px;"></button>
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <!-- Search results table / list -->
                        <div class="list-group rounded-3 overflow-hidden shadow-xs" style="max-height: 220px; overflow-y: auto;">
                            @forelse($usersForSelection as $u)
                                <button type="button" wire:click="toggleUserSelection({{ $u->id }})" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 {{ in_array($u->id, $selectedUserIds) ? 'active bg-success border-success text-white' : '' }}">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-secondary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="d-block small">{{ $u->name }}</strong>
                                            <small class="{{ in_array($u->id, $selectedUserIds) ? 'text-white opacity-75' : 'text-muted' }}">{{ $u->email }}</small>
                                        </div>
                                    </div>
                                    <span>
                                        @if(in_array($u->id, $selectedUserIds))
                                            <i class="bi bi-check-circle-fill fs-5"></i>
                                        @else
                                            <i class="bi bi-plus-circle text-muted fs-5"></i>
                                        @endif
                                    </span>
                                </button>
                            @empty
                                <div class="p-3 text-center text-muted small">
                                    {{ $userSearch ? 'Aucun utilisateur trouvé.' : 'Commencez à saisir un nom ou email ci-dessus.' }}
                                </div>
                            @endforelse
                        </div>
                        @error('selectedUserIds') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Subject -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Sujet du message <span class="text-danger">*</span></label>
                    <input type="text" wire:model="subject" class="form-control rounded-3" placeholder="ex: Annonce officielle : Lancement du Hackathon Tech237" required maxlength="200">
                    @error('subject') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Message Content -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Contenu du message <span class="text-danger">*</span></label>
                    <textarea wire:model="messageContent" class="form-control rounded-3" rows="6" placeholder="Rédigez votre message officiel..." required></textarea>
                    <div class="form-text extra-small">Les liens web (http/https) inclus dans votre texte seront automatiquement transformés en liens cliquables ouvrables dans un nouvel onglet.</div>
                    @error('messageContent') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Attachments (Images, PDF, docx strictly as requested) -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark mb-1">
                        Pièces jointes autorisées (Images, PDF, .docx uniquement)
                    </label>
                    <input type="file" wire:model="attachments" multiple accept="image/*,.pdf,.docx" class="form-control rounded-3">
                    <div class="form-text extra-small">
                        Formats acceptés : <strong>Images (JPG, PNG, WEBP), PDF, Word (.docx)</strong>. Taille max : 15 Mo par fichier.
                    </div>
                    @error('attachments.*') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Email Copy Checkbox -->
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="sendEmailSwitch" wire:model="sendEmailCopy">
                    <label class="form-check-label fw-bold small text-dark" for="sendEmailSwitch">
                        Envoyer également une copie de ce message par email aux destinataires
                    </label>
                    <div class="text-muted extra-small">
                        Garantit une délivrabilité maximale même si l'utilisateur ne s'est pas connecté récemment.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <button type="button" wire:click="$set('viewMode', 'threads')" class="btn btn-light rounded-pill px-4">Annuler</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm" wire:loading.attr="disabled">
                        <span wire:loading wire:target="sendMessage" class="spinner-border spinner-border-sm me-1"></span>
                        <i wire:loading.remove wire:target="sendMessage" class="bi bi-send-fill me-1"></i>
                        Envoyer le message
                    </button>
                </div>
            </form>
        </div>

    <!-- VIEW MODE: THREADS / CHAT INBOX -->
    @else
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="row g-0">
                
                <!-- Left Column: Discussions List -->
                <div class="col-md-5 col-lg-4 border-end bg-light" style="min-height: 600px; max-height: 750px; overflow-y: auto;">
                    <div class="p-3 border-bottom bg-white sticky-top">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm rounded-pill px-3" placeholder="Filtrer les discussions...">
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse($threads as $t)
                            @php $latestMsg = $t->messages->first(); @endphp
                            <button 
                                type="button" 
                                wire:click="selectThread({{ $t->id }})" 
                                class="list-group-item list-group-item-action p-3 d-flex align-items-start gap-2 border-bottom {{ $activeUserId === $t->id ? 'bg-white border-start border-4 border-success shadow-xs' : '' }}"
                            >
                                @if($t->avatar)
                                    <img src="{{ str_starts_with($t->avatar, '/') ? $t->avatar : '/' . $t->avatar }}" class="rounded-circle border flex-shrink-0" style="width: 42px; height: 42px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-dark text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; font-size: 14px;">
                                        {{ strtoupper(substr($t->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="flex-grow-1 text-truncate text-start">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-1 text-truncate">
                                            <strong class="text-dark small text-truncate">{{ $t->name }}</strong>
                                            @if(($t->support_count ?? 0) > 0)
                                                <span class="badge bg-danger text-white rounded-pill extra-small" style="font-size: 9px;"><i class="bi bi-headset"></i> Support</span>
                                            @endif
                                        </div>
                                        <small class="text-muted extra-small">
                                            {{ $latestMsg?->created_at ? $latestMsg->created_at->format('d/m H:i') : '' }}
                                        </small>
                                    </div>
                                    <div class="text-muted extra-small text-truncate">
                                        {{ $latestMsg?->subject ?: $latestMsg?->message }}
                                    </div>
                                </div>

                                @if($t->unread_count > 0)
                                    <span class="badge bg-warning text-dark rounded-pill">{{ $t->unread_count }}</span>
                                @endif
                            </button>
                        @empty
                            <div class="p-5 text-center text-muted small">
                                <i class="bi bi-chat-square-dots fs-2 text-secondary d-block mb-2"></i>
                                Aucune discussion trouvée.
                            </div>
                        @endforelse
                    </div>

                    @if($threads->hasPages())
                        <div class="p-2 border-top bg-white">
                            {{ $threads->links() }}
                        </div>
                    @endif
                </div>

                <!-- Right Column: Active Thread Stream & Composer -->
                <div class="col-md-7 col-lg-8 d-flex flex-column bg-white" style="height: 750px;">
                    @if($activeUser)
                        <!-- Thread Header -->
                        <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                @if($activeUser->avatar)
                                    <img src="{{ str_starts_with($activeUser->avatar, '/') ? $activeUser->avatar : '/' . $activeUser->avatar }}" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 14px;">
                                        {{ strtoupper(substr($activeUser->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <strong class="d-block text-dark small mb-0">{{ $activeUser->name }}</strong>
                                    <small class="text-muted">{{ $activeUser->email }} &bull; Rang : {{ $activeUser->rank_title ?? 'Novice Tech' }}</small>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.front-users.index') }}?search={{ urlencode($activeUser->email) }}" class="btn btn-xs btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-person-gear me-1"></i> Fiche Membre
                                </a>
                            </div>
                        </div>

                        <!-- Chat Bubbles -->
                        <div class="p-3 flex-grow-1 overflow-y-auto d-flex flex-column gap-3 bg-white" style="background-color: #fafbfc !important;">
                            @forelse($activeMessages as $m)
                                @php $isAdminMsg = !is_null($m->admin_id); @endphp
                                <div class="d-flex flex-column {{ $isAdminMsg ? 'align-items-end' : 'align-items-start' }}">
                                    <div 
                                        class="rounded-4 p-3 shadow-sm position-relative" 
                                        style="max-width: 80%; background-color: {{ $isAdminMsg ? '#0B2559' : '#FFFFFF' }}; color: {{ $isAdminMsg ? '#FFFFFF' : '#1F2937' }}; border: {{ $isAdminMsg ? 'none' : '1px solid #E5E7EB' }};"
                                    >
                                        <div class="d-flex align-items-center justify-content-between gap-3 mb-1 border-bottom pb-1" style="border-color: {{ $isAdminMsg ? 'rgba(255,255,255,0.2)' : '#E5E7EB' }}; font-size: 11px;">
                                            <div>
                                                <strong>{{ $isAdminMsg ? 'Équipe Tech237 (Admin)' : $activeUser->name }}</strong>
                                                @if($m->is_support && !$isAdminMsg)
                                                    <span class="badge bg-warning text-dark rounded-pill ms-1" style="font-size: 9px;"><i class="bi bi-headset"></i> Support</span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center gap-1 opacity-75">
                                                <span>{{ $m->created_at->format('d/m/Y H:i') }}</span>
                                                <button wire:click="deleteMessage({{ $m->id }})" wire:confirm="Supprimer ce message ?" class="btn btn-link btn-sm p-0 ms-1 text-danger" title="Supprimer">
                                                    <i class="bi bi-trash3" style="font-size: 10px;"></i>
                                                </button>
                                            </div>
                                        </div>

                                        @if($m->subject)
                                            <div class="fw-bold extra-small mb-1" style="color: {{ $isAdminMsg ? '#93C5FD' : '#0B2559' }};">
                                                {{ $m->subject }}
                                            </div>
                                        @endif

                                        <div class="small leading-relaxed" style="white-space: pre-line;">
                                            {!! preg_replace('/(https?:\/\/[^\s<]+|www\.[^\s<]+)/i', '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-decoration-underline fw-bold" style="color: inherit;">$1 <i class="bi bi-box-arrow-up-right extra-small"></i></a>', e($m->message)) !!}
                                        </div>

                                        @if($m->attachment_path)
                                            <div class="mt-2 pt-2 border-top" style="border-color: {{ $isAdminMsg ? 'rgba(255,255,255,0.2)' : '#E5E7EB' }};">
                                                @if($m->attachment_type === 'image')
                                                    <a href="{{ asset($m->attachment_path) }}" target="_blank" class="d-block rounded-3 overflow-hidden border">
                                                        <img src="{{ asset($m->attachment_path) }}" class="img-fluid w-100" style="max-height: 200px; object-fit: cover;" alt="Image jointe">
                                                    </a>
                                                @else
                                                    <a href="{{ asset($m->attachment_path) }}" target="_blank" class="d-flex align-items-center gap-2 p-2 rounded-3 bg-white text-dark border text-decoration-none shadow-xs">
                                                        <i class="bi bi-file-earmark-arrow-down-fill fs-3 text-primary"></i>
                                                        <div class="overflow-hidden">
                                                            <strong class="extra-small d-block text-truncate">{{ $m->attachment_name ?: 'Pièce jointe' }}</strong>
                                                            <small class="text-muted">Cliquer pour télécharger</small>
                                                        </div>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted small">Aucun message échangé avec cet utilisateur.</div>
                            @endforelse
                        </div>

                        <!-- Thread Reply Composer -->
                        <div class="p-3 border-top bg-light">
                            <form wire:submit.prevent="sendReplyInThread">
                                <div class="mb-2">
                                    <textarea 
                                        wire:model="threadReplyText" 
                                        class="form-control rounded-3" 
                                        rows="3" 
                                        placeholder="Répondre à {{ $activeUser->name }}..."
                                        required
                                    ></textarea>
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <input type="file" wire:model="threadReplyAttachment" accept="image/*,.pdf,.docx" class="form-control form-control-sm" style="max-width: 250px;">
                                        @error('threadReplyAttachment') <span class="text-danger extra-small">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm" wire:loading.attr="disabled">
                                        <span wire:loading wire:target="sendReplyInThread" class="spinner-border spinner-border-sm me-1"></span>
                                        <i wire:loading.remove wire:target="sendReplyInThread" class="bi bi-send-fill me-1"></i> Répondre
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <!-- No thread selected placeholder -->
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center p-5 text-muted">
                            <div class="rounded-circle bg-light p-4 mb-3 text-primary fs-1">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Sélectionnez une discussion</h5>
                            <p class="small text-muted mb-4" style="max-width: 380px;">
                                Choisissez un fil de discussion dans la colonne de gauche ou lancez une nouvelle diffusion générale vers tous les membres.
                            </p>
                            <button wire:click="openCompose('all')" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-broadcast me-1"></i> Diffuser un message général
                            </button>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    @endif
</div>
