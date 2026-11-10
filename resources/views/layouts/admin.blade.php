<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Administration' }} | Tech237 Backoffice</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/logo.svg') }}">

    <!-- 100% Offline Local Assets: Poppins font, Bootstrap 5, Bootstrap Icons, Summernote, jQuery UI, Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/css/poppins.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/summernote-bs5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    @livewireStyles
</head>
<body>

    @php
        $admin = auth('admin')->user();
        $siteSetting = \App\Models\SiteSetting::current();
        $bannedAdminsCount = \App\Models\Admin::where('is_banned', true)->count();
        $pendingArticlesCount = \App\Models\Article::where('status', 'pending_review')->count();
    @endphp

    <!-- Mobile Overlay Backdrop -->
    <div id="sidebarBackdrop" class="sidebar-backdrop d-none" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="brand">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none">
                <img src="{{ asset('assets/images/logo-tech237.png') }}" alt="Tech237 Admin" height="34">
            </a>
            <button class="btn btn-sm btn-link text-white-50 d-lg-none" onclick="toggleSidebar()">
                <i class="bi bi-x-lg fs-5"></i>
            </button>
        </div>

        <nav class="py-3">
            @if($admin && $admin->role === 'tester_reviews')
                <div class="nav-heading">Espace Testeur Spécialisé</div>
                <a href="{{ route('admin.app-reviews.index') }}" class="nav-link {{ request()->routeIs('admin.app-reviews.*') ? 'active' : '' }}">
                    <i class="bi bi-phone text-primary"></i> Tests Applications
                </a>
                <a href="{{ route('admin.game-reviews.index') }}" class="nav-link {{ request()->routeIs('admin.game-reviews.*') ? 'active' : '' }}">
                    <i class="bi bi-controller text-danger"></i> Tests Jeux Vidéo
                </a>

                <div class="nav-heading">Mon Compte</div>
                <a href="{{ route('admin.profile.index') }}" class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> Mon Profil
                </a>
            @else
            <div class="nav-heading">Principal</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Tableau de bord
            </a>
            <a href="{{ route('admin.my-stats') }}" class="nav-link {{ request()->routeIs('admin.my-stats') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow text-danger"></i> Mes Statistiques
            </a>

            <div class="nav-heading">Éditorial</div>
            <a href="{{ route('admin.rubriques.index') }}" class="nav-link {{ request()->routeIs('admin.rubriques.*') ? 'active' : '' }}">
                <i class="bi bi-collection-play"></i> Rubriques
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Catégories & Sous-cat
            </a>
            <a href="{{ route('admin.dossiers.index') }}" class="nav-link {{ request()->routeIs('admin.dossiers.*') ? 'active' : '' }}">
                <i class="bi bi-folder-fill"></i> Dossiers (ex: ChatGPT)
            </a>
            <a href="{{ route('admin.tags.index') }}" class="nav-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
                <i class="bi bi-hash"></i> Tags
            </a>
            <a href="{{ route('admin.articles.index') }}" class="nav-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
                <i class="bi bi-newspaper"></i> Articles
                @if($pendingArticlesCount > 0)
                    <span class="badge bg-warning text-dark ms-auto" title="En attente de validation">{{ $pendingArticlesCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.entrepreneurs.index') }}" class="nav-link {{ request()->routeIs('admin.entrepreneurs.*') ? 'active' : '' }}">
                <i class="bi bi-star-fill text-warning"></i> Entrepreneur du mois
            </a>

            <div class="nav-heading">Tests, Événements & Forum</div>
            <a href="{{ route('admin.app-reviews.index') }}" class="nav-link {{ request()->routeIs('admin.app-reviews.*') ? 'active' : '' }}">
                <i class="bi bi-phone"></i> Tests Applications
            </a>
            <a href="{{ route('admin.game-reviews.index') }}" class="nav-link {{ request()->routeIs('admin.game-reviews.*') ? 'active' : '' }}">
                <i class="bi bi-controller"></i> Tests Jeux Vidéo
            </a>
            <a href="{{ route('admin.interviews.index') }}" class="nav-link {{ request()->routeIs('admin.interviews.*') ? 'active' : '' }}">
                <i class="bi bi-mic"></i> Interviews Q&A
            </a>
            <a href="{{ route('admin.conferences.index') }}" class="nav-link {{ request()->routeIs('admin.conferences.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i> Conférences & Événements
                @php $pendingConferencesCount = \App\Models\Conference::where('status', 'pending_validation')->count(); @endphp
                @if($pendingConferencesCount > 0)
                    <span class="badge bg-warning text-dark ms-auto" title="En attente de validation">{{ $pendingConferencesCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.jobs.index') }}" class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">
                <i class="bi bi-briefcase-fill"></i> Offres d'Emploi IT
                @php $pendingJobsCount = \App\Models\JobOffer::where('status', 'pending_validation')->count(); @endphp
                @if($pendingJobsCount > 0)
                    <span class="badge bg-warning text-dark ms-auto" title="En attente de validation">{{ $pendingJobsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.forum.index') }}" class="nav-link {{ request()->routeIs('admin.forum.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots-fill"></i> Modération Forum
                @php $pendingForumReports = \App\Models\ForumReport::where('status', 'pending')->count(); @endphp
                @if($pendingForumReports > 0)
                    <span class="badge bg-danger ms-auto" title="Signalements forum en attente">{{ $pendingForumReports }}</span>
                @endif
            </a>

            <div class="nav-heading">Startups & Projets (OHADA)</div>
            <a href="{{ route('admin.startups.index') }}" class="nav-link {{ request()->routeIs('admin.startups.*') ? 'active' : '' }}">
                <i class="bi bi-briefcase"></i> Startups & Validations
                @php $pendingStartupsCount = \App\Models\Startup::where('status', 'pending_validation')->count(); @endphp
                @if($pendingStartupsCount > 0)
                    <span class="badge bg-warning text-dark ms-auto">{{ $pendingStartupsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.front-users.index') }}" class="nav-link {{ request()->routeIs('admin.front-users.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Membres & Porteurs
            </a>
            <a href="{{ route('admin.comments.index') }}" class="nav-link {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}">
                <i class="bi bi-chat-square-text"></i> Commentaires
                @php $pendingCommentsCount = \App\Models\Comment::where('status', 'pending')->count(); @endphp
                @if($pendingCommentsCount > 0)
                    <span class="badge bg-warning text-dark ms-auto" title="Commentaires en attente">{{ $pendingCommentsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.disputes.index') }}" class="nav-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
                <i class="bi bi-shield-exclamation"></i> Litiges & Modération
                @php $pendingDisputesCount = \App\Models\Dispute::where('status', 'pending')->count(); @endphp
                @if($pendingDisputesCount > 0)
                    <span class="badge bg-danger ms-auto">{{ $pendingDisputesCount }}</span>
                @endif
            </a>

            <div class="nav-heading">Marketing & Statistiques</div>
            <a href="{{ route('admin.analytics.index') }}" class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Analytics Visiteurs
            </a>
            @if($admin && $admin->isAdmin())
                <a href="{{ route('admin.writers.overview') }}" class="nav-link {{ request()->routeIs('admin.writers.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack text-success"></i> Rémunération Rédacteurs
                </a>
            @endif
            <a href="{{ route('admin.sliders.index') }}" class="nav-link {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}">
                <i class="bi bi-images"></i> Sliders Accueil
            </a>
            <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                <i class="bi bi-badge-ad"></i> Bannières Pub
            </a>
            <a href="{{ route('admin.partners.index') }}" class="nav-link {{ request()->routeIs('admin.partners.*') ? 'active' : '' }}">
                <i class="bi bi-buildings"></i> Partenaires & Entreprises
            </a>
            <a href="{{ route('admin.newsletters.index') }}" class="nav-link {{ request()->routeIs('admin.newsletters.*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper"></i> Newsletters & Inscrits
            </a>

            <div class="nav-heading">Rétention & Communauté</div>
            <a href="{{ route('admin.pocs.index') }}" class="nav-link {{ request()->routeIs('admin.pocs.*') ? 'active' : '' }}">
                <i class="bi bi-lightning-charge-fill text-warning"></i> Mini-Actus (POCs)
                @php
                    $totalPocsAdminCount = 0;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('pocs')) {
                            $totalPocsAdminCount = \App\Models\Poc::count();
                        }
                    } catch (\Throwable $e) {
                        $totalPocsAdminCount = 0;
                    }
                @endphp
                <span class="badge bg-secondary ms-auto">{{ $totalPocsAdminCount }}</span>
            </a>
            <a href="{{ route('admin.messages.index') }}" class="nav-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots-fill text-info"></i> Messagerie Communautaire
                @php
                    $unreadAdminMessagesCount = 0;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('user_messages')) {
                            $unreadAdminMessagesCount = \App\Models\UserMessage::where('is_read', false)
                                ->where(function($q) {
                                    $q->whereNull('admin_id')->orWhere('is_support', true);
                                })->count();
                        }
                    } catch (\Throwable $e) {}
                @endphp
                @if($unreadAdminMessagesCount > 0)
                    <span class="badge bg-warning text-dark ms-auto">{{ $unreadAdminMessagesCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.polls.index') }}" class="nav-link {{ request()->routeIs('admin.polls.*') ? 'active' : '' }}">
                <i class="bi bi-pie-chart-fill text-primary"></i> Sondages Tech (Polls)
            </a>
            <a href="{{ route('admin.retention.upvotes') }}" class="nav-link {{ request()->routeIs('admin.retention.upvotes*') ? 'active' : '' }}">
                <i class="bi bi-arrow-up-circle-fill text-success"></i> Upvotes Projets Tech
            </a>
            <a href="{{ route('admin.retention.badges') }}" class="nav-link {{ request()->routeIs('admin.retention.badges*') ? 'active' : '' }}">
                <i class="bi bi-award-fill text-warning"></i> Niveaux XP &amp; Badges
            </a>

            <div class="nav-heading">Gestion & Sécurité</div>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Admins & Rôles
            </a>

            <!-- Super Admin Exclusive Menu for Banned Admins -->
            @if($admin && $admin->isSuperAdmin())
                <a href="{{ route('admin.banned.index') }}" class="nav-link {{ request()->routeIs('admin.banned.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-slash text-danger"></i>
                    <span>Admins Bannis</span>
                    @if($bannedAdminsCount > 0)
                        <span class="badge bg-danger ms-auto">{{ $bannedAdminsCount }}</span>
                    @endif
                </a>
            @endif

            <a href="{{ route('admin.logs.index') }}" class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Journaux d'activité / Logs
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Paramètres du site
            </a>
            <a href="{{ route('admin.profile.index') }}" class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i> Mon Profil
            </a>
            @endif
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary btn-sm me-3 d-lg-none" onclick="toggleSidebar()">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-md-block">
                    <span class="text-muted small">Espace d'administration sécurisé</span>
                    <span class="badge bg-dark ms-2">{{ config('app.name', 'Tech237') }}</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                @php $snowActive = \App\Models\SiteSetting::current()->snow_effect_enabled; @endphp
                <form action="{{ route('admin.toggle-snow') }}" method="POST" class="d-inline mb-0">
                    @csrf
                    <button type="submit" class="btn btn-sm rounded-pill px-3 {{ $snowActive ? 'btn-info text-white shadow-sm' : 'btn-outline-secondary' }}" title="Activer / Désactiver l'effet de chute de neige sur le site">
                        <i class="bi bi-snow2 me-1"></i>
                        <span class="d-none d-sm-inline">{{ $snowActive ? 'Neige Active ❄' : 'Faire tomber la neige' }}</span>
                    </button>
                </form>

                <a href="/" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Voir le site
                </a>

                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-pill dropdown-toggle d-flex align-items-center gap-2 border px-3 py-1" type="button" data-bs-toggle="dropdown">
                        @if(!empty($admin->avatar))
                            <img src="{{ asset($admin->avatar) }}" alt="{{ $admin->name }}" class="rounded-circle object-fit-cover shadow-sm" style="width: 28px; height: 28px;">
                        @else
                            <div class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                                {{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}
                            </div>
                        @endif
                        <span class="fw-semibold small d-none d-sm-inline">{{ $admin->name ?? 'Admin' }}</span>
                        <span class="badge bg-secondary small">{{ $admin->role ?? 'admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li>
                            <h6 class="dropdown-header">{{ $admin->email ?? '' }}</h6>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('admin.profile.index') }}"><i class="bi bi-person me-2"></i>Mon Profil</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <!-- Trigger Static Logout Modal -->
                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#staticLogoutModal">
                                <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Body Content -->
        <div class="admin-content">

            <!-- Default Password Warning Alert Banner -->
            @if($admin && $admin->default_password === 0)
                <div class="default-pwd-alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-white"></i>
                        <div>
                            <h5 class="mb-1 fw-bold text-white">Sécurité requise : Mot de passe par défaut actif</h5>
                            <p class="mb-0 text-white-50 small">
                                Vous utilisez toujours le mot de passe initial à six zéros (000000). Veuillez définir votre mot de passe personnalisé dès maintenant pour sécuriser votre compte.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.profile.index') }}" class="btn btn-light fw-bold text-danger px-3 py-2 rounded-pill shadow-sm text-nowrap ms-3">
                        <i class="bi bi-shield-lock me-1"></i> Modifier mon mot de passe
                    </a>
                </div>
            @endif

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

    <!-- Static Confirmation Modal for Logout -->
    <div class="modal fade" id="staticLogoutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Confirmation de déconnexion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <div class="mb-3">
                        <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex p-3">
                            <i class="bi bi-shield-exclamation fs-1"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-2">Êtes-vous sûr de vouloir vous déconnecter ?</h5>
                    <p class="text-muted small mb-0">Votre session administrateur sera fermée de manière sécurisée.</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger rounded-pill px-4">
                            Oui, se déconnecter
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Generic Static Confirmation Modal for Actions (Delete / Ban) -->
    <div class="modal fade" id="staticActionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="actionModalTitle">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <div class="mb-3">
                        <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex p-3" id="actionModalIconWrapper">
                            <i class="bi bi-question-circle fs-1" id="actionModalIcon"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-2" id="actionModalHeadline">Voulez-vous vraiment effectuer cette action ?</h5>
                    <p class="text-muted small mb-0" id="actionModalMessage">Cette opération peut modifier définitivement les données.</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="actionModalConfirmBtn">Confirmer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 100% Offline Scripts: jQuery, jQuery UI, Select2, Bootstrap Bundle, Summernote -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/melty-charts.js') }}"></script>
    <script src="{{ asset('assets/js/summernote-bs5.min.js') }}"></script>

    <script>
        // Polyfill jQuery.now for Summernote if required by any legacy plugin
        if (!jQuery.now) {
            jQuery.now = Date.now || function() { return new Date().getTime(); };
        }

        // Global WYSIWYG Enter key behavior: Strictly insert newline, never submit parent forms or switch tabs
        $(document).on('keydown', '.note-editable', function(e) {
            if (e.keyCode === 13 || e.which === 13) {
                e.stopPropagation();
            }
        });

        // Initialize jQuery UI Datepicker for fields with class .jquery-datepicker
        $(function() {
            // Tab persistence with URL hash and localStorage
            const pageTabKey = 'melty_admin_active_tab_' + window.location.pathname;
            const savedHash = window.location.hash || localStorage.getItem(pageTabKey);
            if (savedHash && savedHash.startsWith('#')) {
                const targetTrigger = document.querySelector(`[data-bs-target="${savedHash}"], [href="${savedHash}"][data-bs-toggle="tab"], [href="${savedHash}"][data-bs-toggle="pill"]`);
                if (targetTrigger) {
                    const bsTab = bootstrap.Tab.getOrCreateInstance(targetTrigger);
                    bsTab.show();
                }
            }

            document.querySelectorAll('[data-bs-toggle="tab"], [data-bs-toggle="pill"]').forEach(function(tabTrigger) {
                tabTrigger.addEventListener('shown.bs.tab', function(e) {
                    const target = e.target.getAttribute('data-bs-target') || e.target.getAttribute('href');
                    if (target && target.startsWith('#')) {
                        localStorage.setItem(pageTabKey, target);
                        history.replaceState(null, null, target);
                    }
                });
            });

            if ($.fn.datepicker) {
                $('.jquery-datepicker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+0'
                });
            }
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('d-none');
        }

        // Global Helper to trigger static action modal
        function confirmStaticAction(title, message, onConfirm, isDanger = true) {
            document.getElementById('actionModalTitle').innerText = title;
            document.getElementById('actionModalHeadline').innerText = title;
            document.getElementById('actionModalMessage').innerText = message;

            const btn = document.getElementById('actionModalConfirmBtn');
            btn.className = isDanger ? 'btn btn-danger rounded-pill px-4' : 'btn btn-primary rounded-pill px-4';

            const modalEl = document.getElementById('staticActionModal');
            const modal = new bootstrap.Modal(modalEl);

            btn.onclick = function() {
                modal.hide();
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
            };
            modal.show();
        }
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
