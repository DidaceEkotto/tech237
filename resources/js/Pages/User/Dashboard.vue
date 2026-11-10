<template>
  <FrontLayout>
    <Head>
      <title>Mon Espace Membre & Projets - Tech237</title>
      <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="container py-4 my-2">
      
      <!-- Banned Account Alert Banner -->
      <div v-if="isBanned" class="alert alert-danger shadow-sm border-2 border-danger p-4 rounded-4 mb-4" role="alert">
        <div class="d-flex align-items-center gap-3">
          <i class="bi bi-shield-slash-fill fs-1 text-danger"></i>
          <div>
            <h4 class="alert-heading fw-bold mb-1">Votre compte a été restreint</h4>
            <p class="mb-1">
              L'accès à la création de projets, startups et offres d'emploi est suspendu.
            </p>
            <div class="small fw-semibold text-danger-emphasis">
              <strong>Motif :</strong> {{ banReason || 'Non-respect des règles de la communauté.' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Flash Success Message -->
      <div v-if="$page.props.flash?.success" class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ $page.props.flash.success }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>

      <!-- Mobile Top Drawer Trigger (d-lg-none) -->
      <div class="d-lg-none mb-3 d-flex justify-content-between align-items-center bg-white p-3 rounded-4 shadow-sm border">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 14px;">
            {{ (user.name || 'U').charAt(0).toUpperCase() }}
          </div>
          <div>
            <div class="fw-bold text-dark small">{{ user.first_name }} {{ user.name }}</div>
            <div class="text-muted" style="font-size: 11px;">Espace Membre & Recrutement</div>
          </div>
        </div>
        <button class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" @click="mobileDrawerOpen = true">
          <i class="bi bi-list me-1"></i> Menu Espace
        </button>
      </div>

      <!-- Layout: Vertical Sidebar (Left) + Content Area (Right) -->
      <div class="row g-4">
        
        <!-- Left Vertical Sidebar Navigation (Desktop) with fixed z-index underneath header -->
        <div class="col-lg-3 d-none d-lg-block">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-sticky" style="top: 85px; z-index: 10;">
            <!-- User Mini Profile Header -->
            <div class="p-4 bg-dark text-white text-center border-bottom">
              <div class="position-relative d-inline-block mb-3">
                <img 
                  v-if="user.avatar" 
                  :src="user.avatar.startsWith('/') ? user.avatar : '/' + user.avatar" 
                  :alt="user.name" 
                  class="rounded-circle object-fit-cover shadow border border-3 border-danger" 
                  style="width: 72px; height: 72px;"
                >
                <div 
                  v-else 
                  class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center shadow mx-auto" 
                  style="width: 72px; height: 72px; font-size: 26px;"
                >
                  {{ (user.name || 'U').charAt(0).toUpperCase() }}
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-white">{{ user.first_name }} {{ user.name }}</h5>
              <div class="small text-white-50 text-truncate mb-2">{{ user.email }}</div>

              <!-- Gamification Rank & XP -->
              <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-2">
                <span class="badge rounded-pill px-2 py-1 bg-warning text-dark fw-bold" style="font-size: 11px;">
                  <i class="bi bi-award-fill me-1"></i>{{ user.rank_title || 'Novice' }}
                </span>
                <span class="badge rounded-pill px-2 py-1 bg-light text-dark fw-bold border" style="font-size: 11px;">
                  ⚡ {{ user.xp || 0 }} XP
                </span>
              </div>

              <!-- Mini Badges preview if any -->
              <div v-if="badges && badges.length" class="d-flex justify-content-center gap-1 flex-wrap pt-1">
                <span 
                  v-for="b in badges.slice(0, 4)" 
                  :key="b.id" 
                  class="badge bg-white text-warning border p-1 rounded-circle"
                  :title="b.badge_name + ' : ' + b.description"
                  style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;"
                >
                  <i :class="b.badge_icon || 'bi bi-award'"></i>
                </span>
              </div>
            </div>

            <!-- Vertical Navigation Menu Links -->
            <div class="list-group list-group-flush p-2">
              <button 
                @click="setSection('dashboard')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'dashboard' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-speedometer2 me-2 fs-5"></i>
                <span>Tableau de bord</span>
              </button>

              <button 
                @click="setSection('startups')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'startups' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-briefcase me-2 fs-5"></i>
                <span>Mes Startups</span>
                <span class="badge rounded-pill ms-auto" :class="currentSection === 'startups' ? 'bg-white text-danger' : 'bg-secondary-subtle text-secondary'">
                  {{ startups.length }}
                </span>
              </button>

              <button 
                @click="setSection('projects')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'projects' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-folder2-open me-2 fs-5"></i>
                <span>Mes Projets</span>
                <span class="badge rounded-pill ms-auto" :class="currentSection === 'projects' ? 'bg-white text-danger' : 'bg-secondary-subtle text-secondary'">
                  {{ projects.length }}
                </span>
              </button>

              <Link 
                href="/espace-utilisateur/kanban" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1 text-dark text-decoration-none"
              >
                <i class="bi bi-kanban me-2 fs-5 text-primary"></i>
                <span>Tableaux Kanban</span>
                <span class="badge bg-primary-subtle text-primary rounded-pill ms-auto extra-small">Trello</span>
              </Link>

              <!-- JOBS MENU (USER REQUEST) -->
              <button 
                @click="setSection('jobs')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'jobs' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-briefcase-fill me-2 fs-5"></i>
                <span>Offres d'Emploi</span>
                <span class="badge rounded-pill ms-auto" :class="currentSection === 'jobs' ? 'bg-white text-danger' : 'bg-secondary-subtle text-secondary'">
                  {{ (jobOffers || []).length }}
                </span>
              </button>

              <!-- CONFERENCES & EVENTS MENU -->
              <button 
                @click="setSection('conferences')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'conferences' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-calendar-event me-2 fs-5"></i>
                <span>Événements & Conf.</span>
                <span class="badge rounded-pill ms-auto" :class="currentSection === 'conferences' ? 'bg-white text-danger' : 'bg-secondary-subtle text-secondary'">
                  {{ (conferences || []).length }}
                </span>
              </button>

              <button 
                @click="setSection('messages')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'messages' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-envelope me-2 fs-5"></i>
                <span>Messages</span>
                <span v-if="localUnreadMessagesCount > 0" class="badge bg-warning text-dark rounded-pill ms-auto">
                  {{ localUnreadMessagesCount }}
                </span>
              </button>

              <button 
                @click="setSection('comments')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'comments' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-chat-dots me-2 fs-5"></i>
                <span>Commentaires</span>
                <span v-if="unreadCommentsCount > 0" class="badge bg-danger rounded-pill ms-auto">
                  {{ unreadCommentsCount }}
                </span>
              </button>

              <button 
                @click="setSection('documents')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'documents' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-file-earmark-lock me-2 fs-5"></i>
                <span>Documents & CNI</span>
                <span class="badge rounded-pill ms-auto" :class="currentSection === 'documents' ? 'bg-white text-danger' : 'bg-secondary-subtle text-secondary'">
                  {{ documents.length }}
                </span>
              </button>

              <button 
                @click="setSection('bookmarks')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'bookmarks' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-bookmark-heart me-2 fs-5"></i>
                <span>Mes Favoris</span>
                <span class="badge rounded-pill ms-auto" :class="currentSection === 'bookmarks' ? 'bg-white text-danger' : 'bg-secondary-subtle text-secondary'">
                  {{ (bookmarks || []).length }}
                </span>
              </button>

              <button 
                @click="setSection('portfolio')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'portfolio' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-briefcase me-2 fs-5 text-warning"></i>
                <span>Mon CV &amp; Portfolio</span>
                <span class="badge bg-warning text-dark rounded-pill extra-small ms-auto fw-bold">Nouveau</span>
              </button>

              <button 
                @click="setSection('profile')" 
                class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
                :class="currentSection === 'profile' ? 'bg-danger text-white' : 'text-dark'"
              >
                <i class="bi bi-person-gear me-2 fs-5"></i>
                <span>Mon Profil</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Right Main View Section Content -->
        <div class="col-lg-9">
          
          <!-- 1. DASHBOARD OVERVIEW -->
          <div v-if="currentSection === 'dashboard'">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                  <h4 class="fw-bold mb-1 text-dark">Tableau de bord Général</h4>
                  <p class="text-muted small mb-0">Bienvenue dans votre espace créateur, recruteur et porteur de projets Tech237.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                  <Link href="/espace-utilisateur/jobs/create" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">
                    <i class="bi bi-briefcase me-1"></i> Publier un job
                  </Link>
                  <Link href="/espace-utilisateur/startups/create" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                    <i class="bi bi-building-add me-1"></i> Créer une startup
                  </Link>
                  <Link href="/espace-utilisateur/projets/create" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Nouveau projet
                  </Link>
                </div>
              </div>

              <!-- KPIs Grid -->
              <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                  <div class="p-3 bg-light rounded-4 border">
                    <div class="text-muted small fw-bold">Startups Enregistrées</div>
                    <div class="h3 fw-bold my-1 text-dark">{{ startups.length }}</div>
                    <span class="badge bg-success-subtle text-success small">OHADA Conforme</span>
                  </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="p-3 bg-light rounded-4 border">
                    <div class="text-muted small fw-bold">Projets Déployés</div>
                    <div class="h3 fw-bold my-1 text-primary">{{ projects.length }}</div>
                    <span class="badge bg-primary-subtle text-primary small">Web, Mobile, Jeux</span>
                  </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="p-3 bg-light rounded-4 border">
                    <div class="text-muted small fw-bold">Offres d'Emploi IT</div>
                    <div class="h3 fw-bold my-1 text-danger">{{ (jobOffers || []).length }}</div>
                    <span class="badge bg-danger-subtle text-danger small">{{ totalApplicantsCount }} candidature(s)</span>
                  </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="p-3 bg-light rounded-4 border">
                    <div class="text-muted small fw-bold">Messages Administrateur</div>
                    <div class="h3 fw-bold my-1 text-warning">{{ messages.length }}</div>
                    <span class="badge bg-warning-subtle text-warning small">{{ localUnreadMessagesCount }} non lu(s)</span>
                  </div>
                </div>
              </div>

              <!-- ANALYTICS CURVE CHART (Jour / Semaine / Mois) -->
              <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light border">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                  <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up text-danger me-2"></i>Statistiques & Visibilité des Projets</h5>
                    <span class="small text-muted">Vues cumulées et interactions sur vos projets</span>
                  </div>
                  <div class="btn-group btn-group-sm">
                    <button 
                      type="button" 
                      class="btn rounded-pill px-3 me-1" 
                      :class="chartPeriod === 'day' ? 'btn-danger text-white' : 'btn-outline-dark'"
                      @click="changePeriod('day')"
                    >
                      Jour
                    </button>
                    <button 
                      type="button" 
                      class="btn rounded-pill px-3 me-1" 
                      :class="chartPeriod === 'week' ? 'btn-danger text-white' : 'btn-outline-dark'"
                      @click="changePeriod('week')"
                    >
                      Semaine
                    </button>
                    <button 
                      type="button" 
                      class="btn rounded-pill px-3" 
                      :class="chartPeriod === 'month' ? 'btn-danger text-white' : 'btn-outline-dark'"
                      @click="changePeriod('month')"
                    >
                      Mois
                    </button>
                  </div>
                </div>
                
                <div class="bg-white rounded-3 p-3 border" style="height: 280px; position: relative;">
                  <canvas ref="chartCanvasRef" id="userAnalyticsChart" style="width: 100%; height: 100%;"></canvas>
                </div>
              </div>

              <!-- Fast actions cards -->
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="p-4 rounded-4 bg-dark text-white h-100 d-flex flex-column justify-content-between">
                    <div>
                      <h5 class="fw-bold mb-2">Recrutez des Développeurs & Experts IT</h5>
                      <p class="small text-white-50">
                        Diffusez vos opportunités professionnelles avec test d'évaluation en ligne et planifiez les entretiens.
                      </p>
                    </div>
                    <div>
                      <button @click="setSection('jobs')" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                        Gérer mes offres d'emploi &rarr;
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="p-4 rounded-4 h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, #FF004B 0%, #FF6600 100%); color: white;">
                    <div>
                      <h5 class="fw-bold mb-2">Partagez vos Développements</h5>
                      <p class="small text-white-50">
                        Mettez en avant les bugs résolus, les architectures mises en place et vos démonstrations vidéo.
                      </p>
                    </div>
                    <div>
                      <button @click="setSection('projects')" class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-danger">
                        Voir mes projets &rarr;
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. MES STARTUPS -->
          <div v-else-if="currentSection === 'startups'">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                  <h4 class="fw-bold mb-1 text-dark">Mes Startups Juridiques (OHADA)</h4>
                  <p class="text-muted small mb-0">SARL, SA, SAS, EURL enregistrées et examinées par Tech237 Cameroon.</p>
                </div>
                <Link href="/espace-utilisateur/startups/create" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                  <i class="bi bi-plus-lg me-1"></i> Créer une startup
                </Link>
              </div>

              <div v-if="startups.length" class="row g-3">
                <div v-for="st in startups" :key="st.id" class="col-md-6">
                  <div class="card border shadow-sm rounded-4 h-100 p-3 bg-light">
                    <div class="d-flex align-items-center gap-3 mb-3">
                      <div class="rounded-3 border overflow-hidden p-1 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px;">
                        <img 
                          v-if="st.logo" 
                          :src="st.logo.startsWith('/') ? st.logo : '/' + st.logo" 
                          :alt="st.name" 
                          class="w-100 h-100 object-fit-contain rounded-2"
                        >
                        <div v-else class="fw-bold text-danger fs-5">
                          {{ (st.name || 'S').charAt(0).toUpperCase() }}
                        </div>
                      </div>
                      <div class="flex-grow-1 text-truncate">
                        <h6 class="fw-bold mb-0 text-dark text-truncate">{{ st.name }}</h6>
                        <span class="badge bg-secondary-subtle text-secondary small">{{ st.legal_type }} &bull; {{ st.city }}</span>
                      </div>
                    </div>

                    <p class="small text-muted mb-3 line-clamp-2">{{ stripHtml(st.description) }}</p>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                      <span 
                        class="badge"
                        :class="st.status === 'approved' ? 'bg-success' : (st.status === 'pending_validation' ? 'bg-warning text-dark' : 'bg-danger')"
                      >
                        {{ st.status === 'approved' ? 'Validée' : (st.status === 'pending_validation' ? 'En examen (<= 2 j)' : 'Rejetée') }}
                      </span>

                      <div class="btn-group btn-group-sm">
                        <Link :href="'/espace-utilisateur/startups/' + st.id + '/edit'" class="btn btn-outline-dark rounded-pill px-3">
                          <i class="bi bi-pencil me-1"></i> Modifier
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                <i class="bi bi-building fs-1 d-block mb-2 text-muted"></i>
                <h6 class="fw-bold text-dark">Aucune startup enregistrée</h6>
                <p class="small mb-3">Enregistrez votre entreprise pour débloquer la levée de fonds et la validation officielle.</p>
                <Link href="/espace-utilisateur/startups/create" class="btn btn-danger btn-sm rounded-pill px-4">
                  Créer ma première startup
                </Link>
              </div>
            </div>
          </div>

          <!-- 3. MES PROJETS -->
          <div v-else-if="currentSection === 'projects'">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                  <h4 class="fw-bold mb-1 text-dark">Projets Tech & Collaborations</h4>
                  <p class="text-muted small mb-0">Gérez vos créations, invitez des collaborateurs et contribuez aux projets de l'écosystème.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <Link href="/espace-utilisateur/kanban" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                    <i class="bi bi-kanban me-1"></i> Tableaux Kanban
                  </Link>
                  <Link href="/espace-utilisateur/projets/create" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> Créer un projet
                  </Link>
                </div>
              </div>

              <!-- Projects Sub-Tabs Navigation -->
              <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3">
                <li class="nav-item">
                  <button 
                    class="nav-link rounded-pill fw-bold px-3 py-2 btn-sm d-flex align-items-center gap-2"
                    :class="projectSubTab === 'owned' ? 'btn-danger text-white' : 'text-dark bg-light'"
                    @click="projectSubTab = 'owned'"
                  >
                    <i class="bi bi-folder-fill"></i>
                    <span>Mes Projets Créateur</span>
                    <span class="badge rounded-pill" :class="projectSubTab === 'owned' ? 'bg-white text-danger' : 'bg-secondary-subtle text-dark'">
                      {{ projects.length }}
                    </span>
                  </button>
                </li>
                <li class="nav-item">
                  <button 
                    class="nav-link rounded-pill fw-bold px-3 py-2 btn-sm d-flex align-items-center gap-2"
                    :class="projectSubTab === 'collaborative' ? 'btn-danger text-white' : 'text-dark bg-light'"
                    @click="projectSubTab = 'collaborative'"
                  >
                    <i class="bi bi-people-fill"></i>
                    <span>Projets Collaboratifs</span>
                    <span class="badge rounded-pill" :class="projectSubTab === 'collaborative' ? 'bg-white text-danger' : 'bg-secondary-subtle text-dark'">
                      {{ (collaborativeProjects || []).length }}
                    </span>
                  </button>
                </li>
                <li class="nav-item">
                  <button 
                    class="nav-link rounded-pill fw-bold px-3 py-2 btn-sm d-flex align-items-center gap-2"
                    :class="projectSubTab === 'invitations' ? 'btn-danger text-white' : ((pendingInvitations || []).length ? 'btn-outline-warning text-dark' : 'text-dark bg-light')"
                    @click="projectSubTab = 'invitations'"
                  >
                    <i class="bi bi-envelope-paper-fill"></i>
                    <span>Invitations Reçues</span>
                    <span v-if="(pendingInvitations || []).length" class="badge rounded-pill bg-warning text-dark fw-bold">
                      {{ (pendingInvitations || []).length }}
                    </span>
                    <span v-else class="badge rounded-pill bg-secondary-subtle text-muted">0</span>
                  </button>
                </li>
                <li class="nav-item" v-if="((pendingTimelines || []).length + (pendingImages || []).length) > 0">
                  <button 
                    class="nav-link rounded-pill fw-bold px-3 py-2 btn-sm d-flex align-items-center gap-2"
                    :class="projectSubTab === 'validations' ? 'btn-danger text-white' : 'btn-outline-danger text-danger'"
                    @click="projectSubTab = 'validations'"
                  >
                    <i class="bi bi-shield-check"></i>
                    <span>Validations en attente</span>
                    <span class="badge rounded-pill bg-danger text-white">
                      {{ (pendingTimelines || []).length + (pendingImages || []).length }}
                    </span>
                  </button>
                </li>
              </ul>

              <!-- SUBTAB 1: OWNED PROJECTS -->
              <div v-show="projectSubTab === 'owned'">
                <div v-if="projects.length" class="row g-3">
                  <div v-for="proj in projects" :key="proj.id" class="col-md-6">
                    <div class="card border shadow-sm rounded-4 h-100 p-3 bg-light d-flex flex-column">
                      <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 border overflow-hidden p-1 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px;">
                          <img 
                            v-if="proj.logo" 
                            :src="proj.logo.startsWith('/') ? proj.logo : '/' + proj.logo" 
                            :alt="proj.name" 
                            class="w-100 h-100 object-fit-contain rounded-2"
                          >
                          <div v-else class="fw-bold text-primary fs-5">
                            {{ (proj.name || 'P').charAt(0).toUpperCase() }}
                          </div>
                        </div>
                        <div class="flex-grow-1 text-truncate">
                          <h6 class="fw-bold mb-0 text-dark text-truncate">{{ proj.name }}</h6>
                          <span class="badge bg-secondary-subtle text-secondary small text-uppercase">{{ proj.project_type }}</span>
                          <span v-if="proj.startup" class="small text-muted ms-1">({{ proj.startup.name }})</span>
                        </div>
                      </div>

                      <p class="small text-muted mb-3 line-clamp-2">{{ stripHtml(proj.description) }}</p>

                      <div class="d-flex align-items-center justify-content-between small text-muted mb-3 flex-wrap gap-2">
                        <span><i class="bi bi-eye me-1"></i>{{ proj.views_count || 0 }} vues</span>
                        <span><i class="bi bi-clock-history me-1"></i><strong>{{ (proj.timelines || []).length }}</strong> étape(s)</span>
                        <span class="text-success"><i class="bi bi-people-fill me-1"></i><strong>{{ (proj.active_collaborators || []).length }}</strong> collab.</span>
                      </div>

                      <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto gap-2 flex-wrap">
                        <div class="d-flex gap-1 flex-wrap">
                          <Link :href="'/espace-utilisateur/projets/' + proj.id + '/kanban'" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Tableau Kanban">
                            <i class="bi bi-kanban me-1"></i> Kanban
                          </Link>
                          <Link :href="'/espace-utilisateur/projets/' + proj.id + '/etapes'" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Journal Devlog">
                            <i class="bi bi-journal-check me-1"></i> Devlog
                          </Link>
                          <Link :href="'/espace-utilisateur/projets/' + proj.id + '/galerie'" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Galerie photos">
                            <i class="bi bi-images me-1"></i> Galerie
                          </Link>
                          <Link :href="'/espace-utilisateur/projets/' + proj.id + '/collaborateurs'" class="btn btn-sm btn-outline-success rounded-pill px-2" title="Inviter &amp; gérer les collaborateurs">
                            <i class="bi bi-person-plus-fill me-1"></i> Inviter
                          </Link>
                        </div>

                        <div class="d-flex gap-1">
                          <Link :href="'/projet/' + proj.slug" target="_blank" class="btn btn-sm btn-light border rounded-circle" title="Voir fiche publique">
                            <i class="bi bi-box-arrow-up-right"></i>
                          </Link>
                          <Link :href="'/espace-utilisateur/projets/' + proj.id + '/edit'" class="btn btn-sm btn-dark rounded-pill px-3">
                            <i class="bi bi-pencil me-1"></i> Modifier
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                  <i class="bi bi-rocket-takeoff fs-1 d-block mb-2 text-muted"></i>
                  <h6 class="fw-bold text-dark">Aucun projet tech créé</h6>
                  <p class="small mb-3">Publiez votre application web, mobile ou votre jeu vidéo.</p>
                  <Link href="/espace-utilisateur/projets/create" class="btn btn-danger btn-sm rounded-pill px-4">
                    Créer mon premier projet
                  </Link>
                </div>
              </div>

              <!-- SUBTAB 2: COLLABORATIVE PROJECTS -->
              <div v-show="projectSubTab === 'collaborative'">
                <div v-if="(collaborativeProjects || []).length" class="row g-3">
                  <div v-for="proj in collaborativeProjects" :key="proj.id" class="col-md-6">
                    <div class="card border shadow-sm rounded-4 h-100 p-3 bg-light d-flex flex-column border-start border-4 border-success">
                      <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 border overflow-hidden p-1 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px;">
                          <img 
                            v-if="proj.logo" 
                            :src="proj.logo.startsWith('/') ? proj.logo : '/' + proj.logo" 
                            :alt="proj.name" 
                            class="w-100 h-100 object-fit-contain rounded-2"
                          >
                          <div v-else class="fw-bold text-success fs-5">
                            {{ (proj.name || 'P').charAt(0).toUpperCase() }}
                          </div>
                        </div>
                        <div class="flex-grow-1 text-truncate">
                          <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="fw-bold mb-0 text-dark text-truncate">{{ proj.name }}</h6>
                            <span class="badge bg-success-subtle text-success small">Collaborateur</span>
                          </div>
                          <small class="text-muted">
                            <i class="bi bi-person-circle me-1"></i>Porté par <strong>{{ proj.user?.full_name || proj.user?.name }}</strong>
                          </small>
                        </div>
                      </div>

                      <p class="small text-muted mb-3 line-clamp-2">{{ stripHtml(proj.description) }}</p>

                      <div class="alert alert-info py-2 px-3 rounded-3 small mb-3 border-0">
                        <i class="bi bi-shield-lock me-1"></i>Vous pouvez consigner des Devlogs et ajouter des photos. Vos soumissions seront validées par le créateur.
                      </div>

                      <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto gap-2 flex-wrap">
                        <div class="d-flex gap-2 flex-wrap">
                          <Link :href="'/espace-utilisateur/projets/' + proj.id + '/kanban'" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-kanban me-1"></i> Kanban
                          </Link>
                          <Link :href="'/espace-utilisateur/projets/' + proj.id + '/etapes'" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                            <i class="bi bi-journal-plus me-1"></i> Ajouter Devlog
                          </Link>
                          <Link :href="'/espace-utilisateur/projets/' + proj.id + '/galerie'" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-images me-1"></i> Galerie Photos
                          </Link>
                        </div>

                        <Link :href="'/projet/' + proj.slug" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3" title="Voir la fiche publique">
                          <i class="bi bi-box-arrow-up-right me-1"></i> Fiche publique
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                  <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                  <h6 class="fw-bold text-dark">Aucun projet collaboratif</h6>
                  <p class="small mb-0">Lorsqu'un créateur vous invitera à collaborer sur son projet et que vous aurez accepté, il apparaîtra ici.</p>
                </div>
              </div>

              <!-- SUBTAB 3: PENDING INVITATIONS RECEIVED -->
              <div v-show="projectSubTab === 'invitations'">
                <div v-if="(pendingInvitations || []).length" class="d-flex flex-column gap-3">
                  <div v-for="inv in pendingInvitations" :key="inv.id" class="card border shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                      <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 border overflow-hidden p-1 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; min-width: 56px;">
                          <img 
                            v-if="inv.project?.logo" 
                            :src="inv.project.logo.startsWith('/') ? inv.project.logo : '/' + inv.project.logo" 
                            :alt="inv.project.name" 
                            class="w-100 h-100 object-fit-contain rounded-2"
                          >
                          <div v-else class="fw-bold text-primary fs-4">
                            {{ (inv.project?.name || 'P').charAt(0).toUpperCase() }}
                          </div>
                        </div>
                        <div>
                          <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h5 class="fw-bold mb-0 text-dark">{{ inv.project?.name }}</h5>
                            <span class="badge bg-danger-subtle text-danger small text-uppercase">{{ inv.project?.project_type }}</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis small">
                              <i class="bi bi-hourglass-split me-1"></i>Expire le {{ formatDateTime(inv.expires_at) }}
                            </span>
                          </div>
                          <div class="small text-muted mb-2">
                            Invitation envoyée par <strong>{{ inv.inviter?.full_name || inv.inviter?.name }}</strong> ({{ inv.inviter?.email }})
                            &bull; Rôle proposé : <strong>{{ inv.role || 'Collaborateur' }}</strong>
                          </div>
                          <p v-if="inv.message" class="small fst-italic text-secondary mb-0 p-2 bg-light rounded-3 border-start border-3 border-primary">
                            « {{ inv.message }} »
                          </p>
                        </div>
                      </div>

                      <!-- Action buttons with static modals -->
                      <div class="d-flex gap-2 flex-wrap align-items-center">
                        <button @click="openInvitationActionModal(inv, 'accept')" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                          <i class="bi bi-check-lg me-1"></i> Accepter
                        </button>
                        <button @click="openInvitationActionModal(inv, 'decline')" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                          <i class="bi bi-x-lg me-1"></i> Refuser
                        </button>
                        <button @click="openInvitationActionModal(inv, 'block')" class="btn btn-outline-danger btn-sm rounded-pill px-3" title="Bloquer définitivement">
                          <i class="bi bi-shield-x me-1"></i> Ne plus jamais collaborer
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                  <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                  <h6 class="fw-bold text-dark">Aucune invitation reçue</h6>
                  <p class="small mb-0">Vous n'avez aucune invitation en attente pour le moment.</p>
                </div>
              </div>

              <!-- SUBTAB 4: PENDING VALIDATIONS FOR OWNER -->
              <div v-show="projectSubTab === 'validations'">
                <div v-if="((pendingTimelines || []).length + (pendingImages || []).length) > 0" class="d-flex flex-column gap-4">
                  
                  <!-- Pending Devlog Steps -->
                  <div v-if="(pendingTimelines || []).length">
                    <h5 class="fw-bold text-dark mb-3">
                      <i class="bi bi-journal-check text-danger me-2"></i>Étapes Devlog en attente de validation ({{ pendingTimelines.length }})
                    </h5>
                    <div class="d-flex flex-column gap-3">
                      <div v-for="t in pendingTimelines" :key="t.id" class="card border rounded-4 p-3 bg-light shadow-sm">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                          <div>
                            <span class="badge bg-secondary-subtle text-secondary small me-2">Projet : {{ t.project?.name }}</span>
                            <h6 class="fw-bold mb-0 text-dark d-inline">{{ t.title }}</h6>
                          </div>
                          <span class="small text-muted">{{ formatDate(t.event_date || t.created_at) }}</span>
                        </div>
                        <div class="small text-muted mb-2">
                          <i class="bi bi-person-fill text-primary me-1"></i>Soumis par le collaborateur : <strong>{{ t.user?.full_name || t.user?.name }}</strong> ({{ t.user?.email }})
                        </div>
                        <p v-if="t.description" class="small text-secondary mb-2">{{ t.description }}</p>
                        <div v-if="t.problem_encountered || t.solution_applied" class="row g-2 mb-3">
                          <div v-if="t.problem_encountered" class="col-md-6">
                            <div class="p-2 bg-white rounded border small">
                              <strong class="text-danger d-block">Défi :</strong> {{ t.problem_encountered }}
                            </div>
                          </div>
                          <div v-if="t.solution_applied" class="col-md-6">
                            <div class="p-2 bg-white rounded border small">
                              <strong class="text-success d-block">Solution :</strong> {{ t.solution_applied }}
                            </div>
                          </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                          <button @click="validateTimelineAction(t.id, 'approve')" class="btn btn-sm btn-success rounded-pill px-4 fw-bold">
                            <i class="bi bi-check-lg me-1"></i> Valider & Publier
                          </button>
                          <button @click="validateTimelineAction(t.id, 'reject')" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                            <i class="bi bi-x-lg me-1"></i> Rejeter
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Pending Images -->
                  <div v-if="(pendingImages || []).length">
                    <h5 class="fw-bold text-dark mb-3">
                      <i class="bi bi-images text-danger me-2"></i>Photos de galerie en attente ({{ pendingImages.length }})
                    </h5>
                    <div class="row g-3">
                      <div v-for="im in pendingImages" :key="im.id" class="col-md-4">
                        <div class="card border rounded-4 overflow-hidden bg-white shadow-sm h-100">
                          <img :src="'/' + im.image_path" class="w-100" style="height: 160px; object-fit: cover;">
                          <div class="p-3 d-flex flex-column gap-2 flex-grow-1">
                            <div>
                              <small class="badge bg-secondary-subtle text-secondary small d-inline-block mb-1">Projet : {{ im.project?.name }}</small>
                              <div class="fw-bold small text-dark">{{ im.caption || 'Sans légende' }}</div>
                              <small class="text-muted d-block" style="font-size: 11px;">Par {{ im.user?.name }}</small>
                            </div>
                            <div class="d-flex gap-2 mt-auto pt-2 border-top">
                              <button @click="validateImageAction(im.id, 'approve')" class="btn btn-sm btn-success rounded-pill w-100 fw-bold py-1">
                                Valider
                              </button>
                              <button @click="validateImageAction(im.id, 'reject')" class="btn btn-sm btn-outline-danger rounded-pill w-100 py-1">
                                Rejeter
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
                <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                  <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success"></i>
                  <h6 class="fw-bold text-dark">Tout est à jour !</h6>
                  <p class="small mb-0">Aucun devlog ni photo n'est en attente de validation.</p>
                </div>
              </div>

            </div>
          </div>

          <!-- 4. MES OFFRES D'EMPLOI IT (USER REQUEST) -->
          <div v-else-if="currentSection === 'jobs'">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                  <h4 class="fw-bold mb-1 text-dark">Mes Offres d'Emploi IT</h4>
                  <p class="text-muted small mb-0">Publiez des offres d'emploi, examinez les évaluations en ligne et convoquez les candidats en entretien physique.</p>
                </div>
                <Link href="/espace-utilisateur/jobs/create" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                  <i class="bi bi-plus-lg me-1"></i> Créer une offre d'emploi
                </Link>
              </div>

              <div v-if="jobOffers && jobOffers.length" class="d-flex flex-column gap-3">
                <div v-for="job in jobOffers" :key="job.id" class="card border shadow-sm rounded-4 p-4 bg-light">
                  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                      <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-danger text-uppercase">{{ job.contract_type }}</span>
                        <span 
                          class="badge"
                          :class="job.status === 'approved' ? 'bg-success' : (job.status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark')"
                        >
                          {{ job.status === 'approved' ? 'Validée & Publiée' : (job.status === 'rejected' ? 'Rejetée' : 'En cours de validation') }}
                        </span>
                        <span v-if="job.has_online_test" class="badge bg-primary-subtle text-primary border border-primary">
                          <i class="bi bi-patch-question-fill me-1"></i>Test d'évaluation en ligne actif
                        </span>
                      </div>
                      <h5 class="fw-bold text-dark mb-1">{{ job.title }}</h5>
                      <div class="small text-muted d-flex align-items-center gap-3 flex-wrap">
                        <span><i class="bi bi-geo-alt me-1 text-danger"></i>{{ job.city }}</span>
                        <span><i class="bi bi-cash-stack me-1 text-success"></i>{{ job.salary_type === 'fixed' && job.salary_amount ? Number(job.salary_amount).toLocaleString() + ' FCFA' : 'À négocier' }}</span>
                        <span v-if="job.deadline_date"><i class="bi bi-calendar-event me-1"></i>Date limite : {{ formatDate(job.deadline_date) }}</span>
                      </div>
                    </div>

                    <!-- Real-Time Candidates Counter Badge -->
                    <div class="text-end">
                      <span class="badge bg-danger text-white fs-6 px-3 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-people-fill"></i>
                        <strong>{{ job.applications_count || 0 }}</strong> candidat(s)
                      </span>
                    </div>
                  </div>

                  <!-- Domains badges -->
                  <div v-if="job.domain && job.domain.length" class="d-flex flex-wrap gap-1 mb-3">
                    <span v-for="d in job.domain" :key="d" class="badge bg-white text-secondary border small">
                      <i class="bi bi-terminal me-1 text-danger"></i>{{ d }}
                    </span>
                  </div>

                  <!-- Locked alert if applications received -->
                  <div v-if="(job.applications_count || 0) > 0" class="alert alert-warning py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-warning fs-5"></i>
                    <span><strong>Information :</strong> Cette offre a déjà reçu des candidatures ({{ job.applications_count }} candidat{{ job.applications_count > 1 ? 's' : '' }}). Conformément aux règles de recrutement, elle ne peut plus être modifiée afin de garantir l'équité du processus.</span>
                  </div>

                  <p class="small text-muted mb-3 line-clamp-2">{{ job.short_description }}</p>

                  <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                      <Link :href="'/espace-utilisateur/jobs/' + job.id + '/candidats'" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">
                        <i class="bi bi-person-lines-fill me-1"></i> Voir les candidatures ({{ job.applications_count || 0 }})
                      </Link>

                      <Link v-if="job.has_online_test" :href="'/espace-utilisateur/jobs/' + job.id + '/questions'" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-question-circle me-1"></i> Questions du test ({{ (job.questions || []).length }} Q)
                      </Link>
                    </div>

                    <div class="d-flex gap-1 align-items-center">
                      <Link v-if="job.status === 'approved'" :href="'/emploi/' + (job.slug || job.id)" target="_blank" class="btn btn-sm btn-light border rounded-circle" title="Voir l'offre publique">
                        <i class="bi bi-box-arrow-up-right"></i>
                      </Link>
                      <button v-if="(job.applications_count || 0) > 0" type="button" class="btn btn-sm btn-secondary rounded-pill px-3 opacity-75" disabled title="Modification impossible : des candidats ont déjà postulé à cette offre">
                        <i class="bi bi-lock-fill me-1"></i> Non modifiable
                      </button>
                      <Link v-else :href="'/espace-utilisateur/jobs/' + job.id + '/edit'" class="btn btn-sm btn-dark rounded-pill px-3">
                        <i class="bi bi-pencil me-1"></i> Modifier
                      </Link>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                <i class="bi bi-briefcase fs-1 d-block mb-2 text-muted"></i>
                <h6 class="fw-bold text-dark">Aucune offre d'emploi créée</h6>
                <p class="small mb-3">Recrutez les meilleurs développeurs, ingénieurs réseaux et experts IT de la communauté Tech237.</p>
                <Link href="/espace-utilisateur/jobs/create" class="btn btn-danger btn-sm rounded-pill px-4">
                  Créer ma première offre d'emploi
                </Link>
              </div>
            </div>
          </div>

          <!-- 4.2 CONFERENCES & EVENTS SECTION -->
          <div v-else-if="currentSection === 'conferences'">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                  <h4 class="fw-bold mb-1 text-dark">Mes Conférences & Événements</h4>
                  <p class="text-muted small mb-0">Organisez des meetups, hackathons ou conférences tech et gérez les inscriptions.</p>
                </div>
                <Link href="/espace-utilisateur/conferences/create" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                  <i class="bi bi-plus-circle"></i>
                  <span>Créer un Événement</span>
                </Link>
              </div>

              <!-- Filter Bar (Date & Name) with Datepicker -->
              <div class="row g-2 mb-4 p-3 bg-light rounded-3">
                <div class="col-md-5">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input 
                      type="text" 
                      class="form-control" 
                      v-model="conferenceSearchName" 
                      placeholder="Filtrer par nom d'événement..."
                    >
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-calendar text-muted"></i></span>
                    <input 
                      type="date" 
                      class="form-control" 
                      v-model="conferenceFilterDate" 
                      placeholder="Filtrer par date"
                    >
                  </div>
                </div>
                <div class="col-md-3 d-flex align-items-center">
                  <button 
                    v-if="conferenceSearchName || conferenceFilterDate" 
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3 w-100" 
                    @click="conferenceSearchName = ''; conferenceFilterDate = ''"
                  >
                    <i class="bi bi-x-circle me-1"></i> Réinitialiser
                  </button>
                </div>
              </div>

              <div v-if="filteredUserConferences.length" class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Événement</th>
                      <th>Date & Lieu</th>
                      <th>Inscriptions</th>
                      <th>Statut</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in filteredUserConferences" :key="c.id">
                      <td>
                        <div class="fw-bold text-dark">{{ c.title_fr }}</div>
                        <small class="text-muted" v-if="c.title_en">{{ c.title_en }}</small>
                      </td>
                      <td>
                        <div class="small fw-semibold">{{ c.event_date ? c.event_date.slice(0, 10) : '' }}</div>
                        <small class="text-muted">{{ c.location || c.city }}</small>
                      </td>
                      <td>
                        <Link 
                          v-if="c.accepts_registrations"
                          :href="'/espace-utilisateur/conferences/' + c.id + '/participants'" 
                          class="btn btn-sm btn-outline-primary rounded-pill px-3"
                        >
                          <i class="bi bi-people-fill me-1"></i> {{ c.registrations_count || 0 }} inscrit(s)
                        </Link>
                        <span v-else class="badge bg-light text-muted border">Inscriptions fermées</span>
                      </td>
                      <td>
                        <span v-if="c.status === 'approved'" class="badge bg-success">
                          <i class="bi bi-check-circle me-1"></i>Approuvé & Publique
                        </span>
                        <span v-else-if="c.status === 'rejected'" class="badge bg-danger">
                          <i class="bi bi-x-circle me-1"></i>Refusé
                        </span>
                        <span v-else class="badge bg-warning text-dark">
                          <i class="bi bi-hourglass-split me-1"></i>En cours de validation
                        </span>
                      </td>
                      <td class="text-end">
                        <div class="d-inline-flex gap-1">
                          <a 
                            v-if="c.status === 'approved'"
                            :href="'/conference/' + c.slug" 
                            target="_blank" 
                            class="btn btn-sm btn-light border" 
                            title="Voir sur le site"
                          >
                            <i class="bi bi-box-arrow-up-right"></i>
                          </a>
                          <Link 
                            :href="'/espace-utilisateur/conferences/' + c.id + '/participants'" 
                            class="btn btn-sm btn-light border text-primary" 
                            title="Gérer les inscrits & envoyer billets"
                            v-if="c.accepts_registrations"
                          >
                            <i class="bi bi-envelope-check"></i>
                          </Link>
                          <Link 
                            :href="'/espace-utilisateur/conferences/' + c.id + '/edit'" 
                            class="btn btn-sm btn-light border" 
                            title="Modifier"
                          >
                            <i class="bi bi-pencil"></i>
                          </Link>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                <i class="bi bi-calendar-event fs-1 d-block mb-2 text-muted"></i>
                <h6 class="fw-bold text-dark">Aucune conférence trouvée</h6>
                <p class="small mb-3">Organisez vos propres événements technologiques et sommets sur Tech237.</p>
                <Link href="/espace-utilisateur/conferences/create" class="btn btn-danger btn-sm rounded-pill px-4">
                  Créer un événement
                </Link>
              </div>

            </div>
          </div>

          <!-- 5. MESSAGES & DISCUSSION THREADS (Back and forth messaging) -->
          <div v-else-if="currentSection === 'messages'">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
              
              <!-- Header -->
              <div class="p-3 p-md-4 border-bottom bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  <h4 class="fw-bold mb-1 text-dark d-flex align-items-center">
                    <i class="bi bi-chat-left-text-fill text-success me-2"></i>Messagerie Privée &amp; Échanges
                  </h4>
                  <p class="text-muted small mb-0">Discutez en continu avec les fondateurs, recruteurs et l'équipe Tech237.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                    {{ conversations.length }} discussion(s)
                  </span>
                  <button 
                    type="button" 
                    @click="openNewMessageModal" 
                    class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm"
                  >
                    <i class="bi bi-pencil-square me-1"></i> Nouveau message
                  </button>
                </div>
              </div>

              <div v-if="conversations.length" class="row g-0">
                <!-- Left: Conversations List -->
                <div 
                  class="col-md-5 col-lg-4 border-end bg-light" 
                  :class="{ 'd-none d-md-block': mobileShowThread && activeConversation }"
                  style="max-height: 680px; overflow-y: auto;"
                >
                  <!-- Search & Filter bar -->
                  <div class="p-2 border-bottom bg-white sticky-top shadow-xs">
                    <div class="input-group input-group-sm mb-2">
                      <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                      </span>
                      <input 
                        type="text" 
                        v-model="conversationSearch" 
                        class="form-control border-start-0 bg-light" 
                        placeholder="Rechercher une discussion..."
                      >
                      <button 
                        v-if="conversationSearch" 
                        type="button" 
                        @click="conversationSearch = ''" 
                        class="btn btn-light border border-start-0 text-muted"
                      >
                        <i class="bi bi-x"></i>
                      </button>
                    </div>

                    <!-- Filter pills -->
                    <div class="d-flex gap-1 overflow-x-auto pb-1" style="scrollbar-width: none;">
                      <button 
                        type="button" 
                        @click="conversationFilterTab = 'all'" 
                        class="btn btn-xs rounded-pill py-0 px-2 extra-small text-nowrap"
                        :class="conversationFilterTab === 'all' ? 'btn-dark' : 'btn-light text-muted border'"
                      >
                        Toutes ({{ conversations.length }})
                      </button>
                      <button 
                        type="button" 
                        @click="conversationFilterTab = 'unread'" 
                        class="btn btn-xs rounded-pill py-0 px-2 extra-small text-nowrap"
                        :class="conversationFilterTab === 'unread' ? 'btn-warning text-dark fw-bold' : 'btn-light text-muted border'"
                      >
                        Non lues ({{ unreadConversationsCount }})
                      </button>
                      <button 
                        type="button" 
                        @click="conversationFilterTab = 'admin'" 
                        class="btn btn-xs rounded-pill py-0 px-2 extra-small text-nowrap"
                        :class="conversationFilterTab === 'admin' ? 'btn-danger text-white' : 'btn-light text-muted border'"
                      >
                        Support Tech237
                      </button>
                      <button 
                        type="button" 
                        @click="conversationFilterTab = 'user'" 
                        class="btn btn-xs rounded-pill py-0 px-2 extra-small text-nowrap"
                        :class="conversationFilterTab === 'user' ? 'btn-success text-white' : 'btn-light text-muted border'"
                      >
                        Membres
                      </button>
                    </div>
                  </div>

                  <!-- Conversations list items -->
                  <div class="list-group list-group-flush">
                    <button 
                      v-for="c in filteredConversations" 
                      :key="c.key"
                      type="button"
                      @click="selectConversation(c)"
                      class="list-group-item list-group-item-action p-3 border-bottom d-flex align-items-center gap-3 transition-all"
                      :class="activeConversation?.key === c.key ? 'bg-white border-start border-4 border-success shadow-sm' : ''"
                    >
                      <!-- Avatar with Initials Fallback -->
                      <div class="position-relative flex-shrink-0">
                        <div 
                          v-if="hasCustomAvatar(c.avatar)"
                          class="rounded-circle border overflow-hidden" 
                          style="width: 46px; height: 46px; min-width: 46px;"
                        >
                          <img 
                            :src="c.avatar.startsWith('/') ? c.avatar : '/' + c.avatar" 
                            class="w-100 h-100 object-fit-cover" 
                            :alt="c.name"
                          >
                        </div>
                        <div 
                          v-else 
                          class="rounded-circle fw-bold text-white d-flex align-items-center justify-content-center shadow-sm"
                          :style="{
                            width: '46px',
                            height: '46px',
                            minWidth: '46px',
                            backgroundColor: getAvatarBg(c.name),
                            fontSize: '15px'
                          }"
                        >
                          {{ getInitials(c.name) }}
                        </div>
                        <span v-if="c.type === 'admin'" class="position-absolute bottom-0 end-0 badge rounded-pill bg-danger p-1" style="font-size: 8px;" title="Administrateur">
                          <i class="bi bi-shield-fill"></i>
                        </span>
                      </div>

                      <div class="flex-grow-1 text-truncate text-start">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <strong class="text-dark small text-truncate">{{ c.name }}</strong>
                          <small class="text-muted extra-small ms-1">
                            {{ c.lastMessage?.created_at ? new Date(c.lastMessage.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : '' }}
                          </small>
                        </div>
                        <div class="text-muted extra-small text-truncate d-flex align-items-center gap-1">
                          <span v-if="c.lastMessage?.sender_id === user.id" class="text-muted">
                            <i v-if="c.lastMessage.is_read" class="bi bi-check-all text-success"></i>
                            <i v-else class="bi bi-check"></i>
                          </span>
                          <span>{{ c.lastMessage?.subject || c.lastMessage?.message }}</span>
                        </div>
                      </div>

                      <span v-if="c.unreadCount > 0" class="badge bg-warning text-dark rounded-pill">
                        {{ c.unreadCount }}
                      </span>
                    </button>

                    <div v-if="filteredConversations.length === 0" class="text-center py-4 text-muted small">
                      <i class="bi bi-search d-block mb-1 fs-5"></i>
                      Aucune discussion ne correspond à ce filtre.
                    </div>
                  </div>
                </div>

                <!-- Right: Active Chat Thread & Composer -->
                <div 
                  class="col-md-7 col-lg-8 d-flex flex-column bg-white" 
                  :class="{ 'd-none d-md-flex': !mobileShowThread && !activeConversation }"
                  style="height: 680px;"
                >
                  <div v-if="activeConversation" class="d-flex flex-column h-100">
                    
                    <!-- Partner Top Header -->
                    <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                      <div class="d-flex align-items-center gap-2">
                        <!-- Mobile back button -->
                        <button 
                          type="button" 
                          @click="mobileShowThread = false" 
                          class="btn btn-sm btn-outline-secondary rounded-pill d-md-none me-1"
                        >
                          <i class="bi bi-arrow-left"></i>
                        </button>

                        <!-- Avatar with Initials Fallback -->
                        <div 
                          v-if="hasCustomAvatar(activeConversation.avatar)"
                          class="rounded-circle border overflow-hidden" 
                          style="width: 40px; height: 40px; min-width: 40px;"
                        >
                          <img 
                            :src="activeConversation.avatar.startsWith('/') ? activeConversation.avatar : '/' + activeConversation.avatar" 
                            class="w-100 h-100 object-fit-cover" 
                            :alt="activeConversation.name"
                          >
                        </div>
                        <div 
                          v-else 
                          class="rounded-circle fw-bold text-white d-flex align-items-center justify-content-center shadow-sm"
                          :style="{
                            width: '40px',
                            height: '40px',
                            minWidth: '40px',
                            backgroundColor: getAvatarBg(activeConversation.name),
                            fontSize: '14px'
                          }"
                        >
                          {{ getInitials(activeConversation.name) }}
                        </div>

                        <div>
                          <div class="fw-bold text-dark small mb-0">{{ activeConversation.name }}</div>
                          <span class="badge extra-small" :class="activeConversation.type === 'admin' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'">
                            {{ activeConversation.type === 'admin' ? 'Administrateur Tech237' : 'Membre Communauté' }}
                          </span>
                        </div>
                      </div>

                      <div class="d-flex align-items-center gap-2">
                        <small class="text-muted extra-small">
                          {{ activeConversation.messages.length }} message(s) échangé(s)
                        </small>
                      </div>
                    </div>

                    <!-- Message Bubble Stream -->
                    <div class="p-3 flex-grow-1 overflow-y-auto d-flex flex-column gap-3 bg-white" id="chatStreamBox">
                      <div 
                        v-for="m in activeConversation.messages" 
                        :key="m.id"
                        class="d-flex flex-column"
                        :class="m.sender_id === user.id ? 'align-items-end' : 'align-items-start'"
                      >
                        <div 
                          class="rounded-4 p-3 shadow-sm position-relative group-bubble"
                          :style="{
                            maxWidth: '78%',
                            backgroundColor: m.sender_id === user.id ? 'var(--tech-green)' : '#F3F4F6',
                            color: m.sender_id === user.id ? '#FFFFFF' : '#1F2937'
                          }"
                        >
                          <!-- Bubble Header -->
                          <div 
                            class="d-flex align-items-center justify-content-between gap-3 mb-1 border-bottom pb-1" 
                            :style="{ borderColor: m.sender_id === user.id ? 'rgba(255,255,255,0.2)' : '#E5E7EB' }"
                          >
                            <strong class="extra-small">{{ m.sender_id === user.id ? 'Moi' : activeConversation.name }}</strong>
                            
                            <div class="d-flex align-items-center gap-1 extra-small opacity-75">
                              <span>{{ m.created_at ? new Date(m.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : '' }}</span>
                              
                              <!-- Read Status Checkmark (WhatsApp style) -->
                              <span v-if="m.sender_id === user.id" class="ms-1" :title="m.is_read ? 'Message lu par le destinataire' : 'Message envoyé (en attente de lecture)'">
                                <i v-if="m.is_read" class="bi bi-check-all text-info fs-6 fw-bold"></i>
                                <i v-else class="bi bi-check fs-6"></i>
                              </span>

                              <!-- Delete message button for sender -->
                              <button 
                                v-if="m.sender_id === user.id"
                                type="button"
                                @click="confirmDeleteMessage(m.id)"
                                class="btn btn-link btn-sm p-0 ms-1 text-white opacity-50 hover-opacity-100"
                                title="Supprimer ce message"
                              >
                                <i class="bi bi-trash3" style="font-size: 11px;"></i>
                              </button>
                            </div>
                          </div>
                          
                          <!-- Subject -->
                          <div v-if="m.subject" class="fw-bold extra-small mb-1" :style="{ color: m.sender_id === user.id ? '#D1FAE5' : '#0B2559' }">
                            {{ m.subject }}
                          </div>

                          <!-- Text message with clickable links opening in new tab -->
                          <div class="small leading-relaxed" style="white-space: pre-line;" v-html="formatMessageWithLinks(m.message || m.content)"></div>

                          <!-- Attachment Preview -->
                          <div v-if="m.attachment_path" class="mt-2 pt-2 border-top" :style="{ borderColor: m.sender_id === user.id ? 'rgba(255,255,255,0.2)' : '#E5E7EB' }">
                            <!-- Image Attachment -->
                            <div v-if="m.attachment_type === 'image'" class="rounded-3 overflow-hidden border">
                              <a :href="'/' + m.attachment_path" target="_blank" title="Agrandir l'image">
                                <img :src="'/' + m.attachment_path" class="img-fluid w-100" style="max-height: 240px; object-fit: cover;" alt="Photo jointe">
                              </a>
                            </div>

                            <!-- Document Attachment -->
                            <a 
                              v-else 
                              :href="'/' + m.attachment_path" 
                              target="_blank" 
                              class="d-flex align-items-center gap-2 p-2 rounded-3 text-decoration-none shadow-xs"
                              :class="m.sender_id === user.id ? 'bg-white text-dark' : 'bg-white text-dark border'"
                            >
                              <i class="bi bi-file-earmark-arrow-down-fill fs-3 text-primary"></i>
                              <div class="overflow-hidden">
                                <strong class="extra-small d-block text-truncate">{{ m.attachment_name || 'Document joint' }}</strong>
                                <small class="text-muted" style="font-size: 11px;">Cliquer pour télécharger</small>
                              </div>
                            </a>
                          </div>

                          <!-- Reactions display -->
                          <div v-if="m.reactions && Object.keys(m.reactions).length > 0" class="d-flex flex-wrap gap-1 mt-2">
                            <button
                              type="button"
                              v-for="(userIds, emoji) in m.reactions"
                              :key="emoji"
                              @click="toggleReaction(m.id, emoji)"
                              class="btn btn-xs rounded-pill py-0 px-2 extra-small d-inline-flex align-items-center gap-1 border shadow-xs"
                              :class="userIds.includes(user.id) ? 'btn-light border-success text-success fw-bold' : 'btn-light text-dark'"
                              style="font-size: 11px;"
                            >
                              <span>{{ emoji }}</span>
                              <span>{{ userIds.length }}</span>
                            </button>
                          </div>
                        </div>

                        <!-- Quick Reactions Toolbar underneath message bubble -->
                        <div class="d-flex align-items-center gap-1 mt-1 px-1">
                          <button 
                            type="button" 
                            v-for="em in ['👍', '❤️', '🚀', '🔥', '👏']" 
                            :key="em"
                            @click="toggleReaction(m.id, em)"
                            class="btn btn-link btn-sm p-0 extra-small text-decoration-none opacity-75 hover-opacity-100"
                            style="font-size: 13px;"
                            :title="'Réagir avec ' + em"
                          >
                            {{ em }}
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Thread Reply Composer (If partner is registered user or admin) -->
                    <div v-if="activeConversation.canReply !== false" class="p-3 border-top bg-light">
                      <form @submit.prevent="sendThreadReply">
                        <div class="input-group input-group-sm mb-2">
                          <span class="input-group-text bg-white border-0 fw-bold extra-small text-muted">Sujet :</span>
                          <input type="text" class="form-control form-control-sm border-0" v-model="threadReplySubject" placeholder="Sujet de votre réponse..." required>
                        </div>

                        <!-- WYSIWYG quick toolbar + Attachment paperclip -->
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                          <div class="d-flex align-items-center gap-1">
                            <button type="button" @click="insertThreadFormat('**', '**')" class="btn btn-sm btn-white border extra-small px-2 py-0" title="Gras"><strong>B</strong></button>
                            <button type="button" @click="insertThreadFormat('*', '*')" class="btn btn-sm btn-white border extra-small px-2 py-0" title="Italique"><em>I</em></button>
                            <button type="button" @click="insertThreadFormat('`', '`')" class="btn btn-sm btn-white border extra-small px-2 py-0" title="Code"><code>&lt;&gt;</code></button>
                            <button type="button" @click="insertThreadFormat('> ', '')" class="btn btn-sm btn-white border extra-small px-2 py-0" title="Citation"><i class="bi bi-quote"></i></button>
                          </div>

                          <!-- Attachment trigger -->
                          <div class="d-flex align-items-center gap-2">
                            <button 
                              type="button" 
                              @click="triggerReplyFileInput" 
                              class="btn btn-sm btn-white border extra-small px-2 py-1 text-muted d-flex align-items-center gap-1"
                              title="Joindre un fichier ou une capture d'écran"
                            >
                              <i class="bi bi-paperclip"></i>
                              <span>Joindre un fichier</span>
                            </button>
                            <input 
                              type="file" 
                              ref="replyFileInput" 
                              @change="onReplyFileSelect" 
                              accept="image/*,.pdf,.doc,.docx,.txt,.zip" 
                              class="d-none"
                            >
                          </div>
                        </div>

                        <!-- Attachment preview chip -->
                        <div v-if="replyAttachment" class="alert alert-success p-2 small mb-2 d-flex align-items-center justify-content-between rounded-3">
                          <div class="d-flex align-items-center gap-2 text-truncate">
                            <i class="bi bi-paperclip fs-5 text-success"></i>
                            <span class="text-truncate fw-bold">{{ replyAttachment.name }}</span>
                            <small class="text-muted">({{ (replyAttachment.size / 1024 / 1024).toFixed(2) }} Mo)</small>
                          </div>
                          <button type="button" @click="cancelReplyAttachment" class="btn-close btn-sm"></button>
                        </div>

                        <!-- Textarea with Enter to submit -->
                        <div class="mb-2">
                          <textarea 
                            id="threadReplyInput"
                            v-model="threadReplyText"
                            @keydown.enter.exact.prevent="sendThreadReply"
                            class="form-control" 
                            rows="3" 
                            placeholder="Rédigez votre réponse ici... (Appuyez sur Entrée pour envoyer, Shift+Entrée pour nouvelle ligne)"
                            required
                          ></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                          <small class="text-muted extra-small">
                            <i class="bi bi-keyboard me-1"></i> <strong>Entrée</strong> pour envoyer &bull; <strong>Shift+Entrée</strong> pour saut de ligne
                          </small>
                          <button 
                            type="submit" 
                            class="btn rounded-pill px-4 fw-bold btn-sm text-white shadow-sm"
                            style="background-color: var(--tech-green);"
                            :disabled="isSendingReply"
                          >
                            <span v-if="isSendingReply" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi bi-send-fill me-1"></i> Répondre
                          </button>
                        </div>
                      </form>
                    </div>

                    <!-- Friendly Alert Card if sender is not registered on the platform -->
                    <div v-else class="p-4 border-top bg-light-subtle">
                      <div class="alert alert-warning border-0 rounded-4 shadow-xs mb-0 p-3 d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-25 p-2 text-warning d-flex align-items-center justify-content-center flex-shrink-0 mt-1">
                          <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        </div>
                        <div>
                          <strong class="d-block text-dark small mb-1">Réponse directe indisponible sur la plateforme</strong>
                          <p class="text-muted extra-small mb-2">
                            Cet interlocuteur ne possède pas de compte utilisateur actif sur Tech237 ou ce message provient d'une notification automatique du système. Vous ne pouvez donc pas lui répondre directement via la messagerie interne.
                          </p>
                          <div v-if="activeConversation.guestSenderEmail" class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-white text-dark border extra-small">
                              <i class="bi bi-envelope me-1 text-primary"></i> {{ activeConversation.guestSenderEmail }}
                            </span>
                            <a :href="'mailto:' + activeConversation.guestSenderEmail + '?subject=' + encodeURIComponent(threadReplySubject)" class="btn btn-xs btn-primary rounded-pill px-3 py-1 fw-bold text-decoration-none shadow-xs">
                              <i class="bi bi-reply-fill me-1"></i> Répondre par Email externe &rarr;
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>

                  <div v-else class="text-center py-5 my-auto text-muted">
                    <i class="bi bi-chat-square-dots fs-1 d-block mb-2 text-muted"></i>
                    Sélectionnez une discussion à gauche pour afficher les messages.
                  </div>

                </div>
              </div>

              <!-- Empty State -->
              <div v-else class="text-center py-5 text-muted bg-light rounded-4 m-4">
                <i class="bi bi-chat-heart fs-1 d-block mb-2 text-success"></i>
                <h5 class="fw-bold text-dark">Aucune conversation en cours</h5>
                <p class="small text-muted mb-3">Les messages reçus ou envoyés aux startups et membres apparaîtront ici.</p>
                <button type="button" @click="openNewMessageModal" class="btn btn-success rounded-pill px-4 btn-sm fw-bold shadow-sm">
                  <i class="bi bi-pencil-square me-1"></i> Écrire un message
                </button>
              </div>

            </div>
          </div>

          <!-- 6. COMMENTAIRES -->
          <div v-else-if="currentSection === 'comments'">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <h4 class="fw-bold mb-1 text-dark">Mes Commentaires</h4>
              <p class="text-muted small mb-4">Historique de vos interventions et retours sur les articles et projets.</p>

              <div v-if="comments.length" class="d-flex flex-column gap-3">
                <div v-for="c in comments" :key="c.id" class="p-3 rounded-3 bg-light border">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge bg-secondary-subtle text-secondary small">
                      <i class="bi bi-chat-quote me-1"></i>
                      {{ c.article?.title_fr || c.project?.name || 'Publication' }}
                    </span>
                    <small class="text-muted">{{ c.created_at ? c.created_at.substring(0, 10) : '' }}</small>
                  </div>
                  <p class="small text-dark mb-0">{{ c.content }}</p>
                </div>
              </div>
              <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                <i class="bi bi-chat-square-text fs-1 d-block mb-2 text-muted"></i>
                Vous n'avez pas encore posté de commentaire.
              </div>
            </div>
          </div>

          <!-- 7. DOCUMENTS & CNI -->
          <div v-else-if="currentSection === 'documents'">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                  <h4 class="fw-bold mb-1 text-dark">Documents & Pièces Justificatives</h4>
                  <p class="text-muted small mb-0">CNI, RCCM, Statuts notariés pour validation de vos startups.</p>
                </div>
              </div>

              <div v-if="documents.length" class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th>Document</th>
                      <th>Type</th>
                      <th>Date</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="doc in documents" :key="doc.id">
                      <td class="fw-semibold text-dark">
                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>{{ doc.original_name || doc.name }}
                      </td>
                      <td><span class="badge bg-light text-dark border">{{ doc.document_type }}</span></td>
                      <td class="small text-muted">{{ doc.created_at ? doc.created_at.substring(0, 10) : '' }}</td>
                      <td class="text-end">
                        <a :href="'/' + doc.file_path" target="_blank" class="btn btn-sm btn-light border rounded-circle me-1" title="Télécharger">
                          <i class="bi bi-download"></i>
                        </a>
                        <button @click="deleteDoc(doc.id)" class="btn btn-sm btn-light border text-danger rounded-circle" title="Supprimer">
                          <i class="bi bi-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                <i class="bi bi-shield-check fs-1 d-block mb-2 text-muted"></i>
                Aucun document téléversé pour le moment.
              </div>
            </div>
          </div>

          <!-- 8. MON PROFIL -->
          <div v-else-if="currentSection === 'profile'">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
              <h4 class="fw-bold mb-1 text-dark">Paramètres de Mon Profil</h4>
              <p class="text-muted small mb-4">Mettez à jour vos informations personnelles et votre avatar.</p>

              <form @submit.prevent="updateProfileSubmit">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Nom de famille <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" v-model="profileForm.name" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Prénom</label>
                    <input type="text" class="form-control" v-model="profileForm.first_name">
                  </div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-4">
                    <label class="form-label small fw-bold">Genre</label>
                    <select class="form-select" v-model="profileForm.gender">
                      <option value="Homme">Homme</option>
                      <option value="Femme">Femme</option>
                      <option value="Autre">Autre</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-bold">Indicatif Pays</label>
                    <input type="text" class="form-control" v-model="profileForm.country_code">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-bold">Téléphone</label>
                    <input type="tel" class="form-control" v-model="profileForm.phone">
                  </div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Date de naissance</label>
                    <input type="date" class="form-control" v-model="profileForm.birth_date">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Statut Matrimonial</label>
                    <select class="form-select" v-model="profileForm.marital_status">
                      <option value="Célibataire">Célibataire</option>
                      <option value="Marié(e)">Marié(e)</option>
                      <option value="Divorcé(e)">Divorcé(e)</option>
                      <option value="Autre">Autre</option>
                    </select>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label small fw-bold">Biographie / Présentation</label>
                  <textarea class="form-control" rows="3" v-model="profileForm.bio" placeholder="Présentez-vous à la communauté..."></textarea>
                </div>

                <div class="mb-4">
                  <label class="form-label small fw-bold">Changer la photo de profil (Avatar)</label>
                  <input type="file" class="form-control form-control-sm" @change="e => profileForm.avatar = e.target.files[0]" accept="image/*">
                </div>

                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" :disabled="profileForm.processing">
                    <span v-if="profileForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                    Mettre à jour mon profil
                  </button>
                </div>
              </form>
            </div>

            <!-- Préférences d'Alertes & Opportunités -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-bell-fill text-danger fs-4"></i>
                <h4 class="fw-bold mb-0 text-dark">Préférences d'Alertes & Opportunités</h4>
              </div>
              <p class="text-muted small mb-4">Recevez automatiquement des alertes ciblées pour ne manquer aucune opportunité tech au Cameroun.</p>

              <form @submit.prevent="updateAlertsSubmit">
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                      <div>
                        <div class="fw-bold text-dark small"><i class="bi bi-briefcase text-success me-2"></i>Alertes Offres d'Emploi IT</div>
                        <div class="text-muted extra-small">Notification dès qu'un poste correspond à vos compétences.</div>
                      </div>
                      <div class="form-check form-switch fs-5 mb-0">
                        <input class="form-check-input" type="checkbox" v-model="alertForm.alert_jobs">
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                      <div>
                        <div class="fw-bold text-dark small"><i class="bi bi-calendar-event text-danger me-2"></i>Conférences & Événements</div>
                        <div class="text-muted extra-small">Rappels des webinaires, hackathons et meetups Tech237.</div>
                      </div>
                      <div class="form-check form-switch fs-5 mb-0">
                        <input class="form-check-input" type="checkbox" v-model="alertForm.alert_conferences">
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                      <div>
                        <div class="fw-bold text-dark small"><i class="bi bi-rocket-takeoff text-primary me-2"></i>Lancements de Projets Tech</div>
                        <div class="text-muted extra-small">Restez informé des innovations des startups camerounaises.</div>
                      </div>
                      <div class="form-check form-switch fs-5 mb-0">
                        <input class="form-check-input" type="checkbox" v-model="alertForm.alert_projects">
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                      <div>
                        <div class="fw-bold text-dark small"><i class="bi bi-newspaper text-warning me-2"></i>Newsletter Hebdomadaire</div>
                        <div class="text-muted extra-small">Le condensé hebdomadaire des levées de fonds et analyses tech.</div>
                      </div>
                      <div class="form-check form-switch fs-5 mb-0">
                        <input class="form-check-input" type="checkbox" v-model="alertForm.alert_newsletter">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <span v-if="alertSavedMessage" class="text-success small fw-bold">
                    <i class="bi bi-check-circle-fill me-1"></i> Préférences enregistrées avec succès !
                  </span>
                  <div class="ms-auto">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm" :disabled="alertForm.processing">
                      <span v-if="alertForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                      Enregistrer mes préférences
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <!-- Badges Communautaires & Progression -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                  <i class="bi bi-trophy-fill text-warning fs-4"></i>
                  <h4 class="fw-bold mb-0 text-dark">Badges Communautaires &amp; Niveau Tech237</h4>
                </div>
                <div class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold fs-6">
                  {{ user.rank_title || 'Novice' }} &bull; {{ user.xp || 0 }} XP
                </div>
              </div>
              <p class="text-muted small mb-4">Gagnez des points d'XP en publiant des projets, en votant aux sondages quotidiens, en commentant et en contribuant activement à l'écosystème numérique camerounais.</p>

              <div v-if="badges && badges.length > 0" class="row g-3">
                <div v-for="b in badges" :key="b.id" class="col-sm-6 col-md-4">
                  <div class="p-3 bg-light rounded-4 border text-center h-100 d-flex flex-column justify-content-center align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-20 text-warning d-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px; font-size: 24px;">
                      <i :class="b.badge_icon || 'bi bi-award-fill'"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">{{ b.badge_name }}</h6>
                    <small class="text-muted">{{ b.description }}</small>
                    <span class="badge bg-white text-secondary border rounded-pill mt-2 extra-small">
                      Obtenu le {{ formatDate(b.awarded_at) }}
                    </span>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4 bg-light rounded-4 border">
                <i class="bi bi-star text-muted fs-2 mb-2 d-block opacity-50"></i>
                <div class="fw-bold text-dark mb-1">Aucun badge débloqué pour l'instant</div>
                <p class="text-muted small mb-0">Participez aux discussions, upvotez les projets et répondez aux sondages pour débloquer vos premiers badges !</p>
              </div>
            </div>
          </div>

          <!-- 9. MES FAVORIS -->
          <div v-else-if="currentSection === 'bookmarks'">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                  <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-bookmark-heart text-danger"></i> Mes Favoris & Sauvegardes
                  </h4>
                  <p class="text-muted small mb-0">Retrouvez facilement les articles, tests, projets tech et opportunités d'emploi que vous avez mis de côté.</p>
                </div>
                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">
                  {{ (bookmarks || []).length }} élément(s)
                </span>
              </div>

              <!-- Filter pills -->
              <div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <button 
                  @click="bookmarkFilter = 'all'" 
                  class="btn btn-sm rounded-pill px-3 fw-bold"
                  :class="bookmarkFilter === 'all' ? 'btn-danger' : 'btn-outline-secondary'"
                >
                  Tous ({{ (bookmarks || []).length }})
                </button>
                <button 
                  @click="bookmarkFilter = 'article'" 
                  class="btn btn-sm rounded-pill px-3 fw-bold"
                  :class="bookmarkFilter === 'article' ? 'btn-danger' : 'btn-outline-secondary'"
                >
                  Articles
                </button>
                <button 
                  @click="bookmarkFilter = 'project'" 
                  class="btn btn-sm rounded-pill px-3 fw-bold"
                  :class="bookmarkFilter === 'project' ? 'btn-danger' : 'btn-outline-secondary'"
                >
                  Projets Tech
                </button>
                <button 
                  @click="bookmarkFilter = 'job'" 
                  class="btn btn-sm rounded-pill px-3 fw-bold"
                  :class="bookmarkFilter === 'job' ? 'btn-danger' : 'btn-outline-secondary'"
                >
                  Offres d'emploi
                </button>
                <button 
                  @click="bookmarkFilter = 'game_review'" 
                  class="btn btn-sm rounded-pill px-3 fw-bold"
                  :class="bookmarkFilter === 'game_review' ? 'btn-danger' : 'btn-outline-secondary'"
                >
                  Tests Jeux
                </button>
                <button 
                  @click="bookmarkFilter = 'app_review'" 
                  class="btn btn-sm rounded-pill px-3 fw-bold"
                  :class="bookmarkFilter === 'app_review' ? 'btn-danger' : 'btn-outline-secondary'"
                >
                  Tests Apps
                </button>
              </div>

              <!-- Bookmarks List -->
              <div v-if="filteredBookmarks.length > 0" class="row g-3">
                <div v-for="b in filteredBookmarks" :key="b.id" class="col-md-6">
                  <div class="card h-100 border rounded-4 shadow-xs overflow-hidden p-3 bg-light bg-opacity-50 d-flex flex-column justify-content-between">
                    <div>
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge rounded-pill px-2 py-1 text-uppercase fw-bold" :class="getBookmarkBadgeClass(b.type)" style="font-size: 10px;">
                          {{ b.type_label }}
                        </span>
                        <small class="text-muted extra-small">Ajouté le {{ b.created_at }}</small>
                      </div>

                      <div class="d-flex gap-3 align-items-start my-2">
                        <div v-if="b.image" class="rounded-3 overflow-hidden border bg-white flex-shrink-0" style="width: 60px; height: 60px;">
                          <img :src="b.image.startsWith('/') ? b.image : '/' + b.image" class="w-100 h-100 object-fit-cover" :alt="b.title">
                        </div>
                        <div v-else class="rounded-3 bg-white border d-flex align-items-center justify-content-center text-muted flex-shrink-0" style="width: 60px; height: 60px;">
                          <i :class="getBookmarkIcon(b.type)" class="fs-4 text-secondary"></i>
                        </div>
                        <div class="flex-grow-1">
                          <h6 class="fw-bold mb-1 text-dark line-clamp-2">
                            <a :href="b.url" class="text-dark text-decoration-none hover-danger">{{ b.title }}</a>
                          </h6>
                        </div>
                      </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                      <a :href="b.url" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold">
                        Consulter <i class="bi bi-arrow-right ms-1"></i>
                      </a>
                      <button 
                        @click="removeBookmark(b)" 
                        class="btn btn-sm btn-link text-danger text-decoration-none p-0 d-flex align-items-center gap-1"
                        title="Retirer des favoris"
                      >
                        <i class="bi bi-trash3"></i>
                        <span>Retirer</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else class="text-center py-5">
                <div class="rounded-circle bg-light d-inline-flex p-4 text-muted mb-3">
                  <i class="bi bi-bookmark-x fs-1 text-danger opacity-50"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Aucun favori dans cette catégorie</h5>
                <p class="text-muted small mb-4" style="max-width: 480px; margin: 0 auto;">
                  Explorez les articles, les applications, les projets tech et les offres d'emploi sur Tech237 et cliquez sur "Sauvegarder" pour les retrouver ici en un clic.
                </p>
                <Link href="/" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                  Découvrir les actualités
                </Link>
              </div>
            </div>
          </div>

          <!-- 10. MON CV & PORTFOLIO TECH UNIQUE -->
          <div v-else-if="currentSection === 'portfolio'">
            
            <!-- Toast Feedback Alert -->
            <div v-if="portfolioToastMsg" class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm p-3 mb-4 d-flex align-items-center justify-content-between" role="alert">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <span class="fw-bold">{{ portfolioToastMsg }}</span>
              </div>
              <button type="button" class="btn-close" @click="portfolioToastMsg = ''"></button>
            </div>

            <!-- Top Hero Banner -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white position-relative overflow-hidden">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                  <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2 text-white fw-bold extra-small" style="background: linear-gradient(135deg, #0B2559 0%, #00A859 100%);">
                    <i class="bi bi-briefcase-fill"></i>
                    <span>Studio Portfolio &amp; CV Partageable</span>
                  </div>
                  <h3 class="fw-bold mb-1 text-dark">Mon Portfolio Tech &amp; CV Unique</h3>
                  <p class="text-muted small mb-0">
                    Personnalisez l'ordre de chaque bloc, le thème et le style pour que votre portfolio soit unique au monde. Partagez votre lien vérifié Tech237 pour séduire recruteurs et clients.
                  </p>
                </div>

                <!-- Live Status & Save Button -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="badge rounded-pill px-3 py-2 fw-bold extra-small" :class="portfolioForm.portfolio_published ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="bi" :class="portfolioForm.portfolio_published ? 'bi-globe2' : 'bi-eye-slash'"></i>
                    {{ portfolioForm.portfolio_published ? 'En ligne (Public)' : 'Mode Privé' }}
                  </span>

                  <button 
                    type="button" 
                    @click="savePortfolioSettings" 
                    class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
                    :disabled="isSavingPortfolio"
                  >
                    <span v-if="isSavingPortfolio" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="bi bi-check2-circle fs-6"></i>
                    <span>{{ isSavingPortfolio ? 'Enregistrement...' : 'Enregistrer' }}</span>
                  </button>
                </div>
              </div>

              <!-- Shareable Link Pill Box -->
              <div class="p-3 rounded-4 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 min-w-0">
                  <span class="rounded-circle bg-white text-primary p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
                    <i class="bi bi-link-45deg fs-5"></i>
                  </span>
                  <div class="min-w-0">
                    <small class="text-muted extra-small d-block fw-bold text-uppercase">Votre lien public vérifié :</small>
                    <span class="fw-bold text-dark text-truncate d-block">{{ portfolioShareUrl }}</span>
                  </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                  <button 
                    type="button" 
                    @click="copyPortfolioUrl" 
                    class="btn btn-sm btn-white bg-white border rounded-pill px-3 fw-bold text-dark shadow-2xs d-inline-flex align-items-center gap-1"
                  >
                    <i class="bi" :class="isPortfolioCopied ? 'bi-check2 text-success' : 'bi-clipboard'"></i>
                    <span>{{ isPortfolioCopied ? 'Copié !' : 'Copier le lien' }}</span>
                  </button>

                  <button 
                    type="button" 
                    @click="openPortfolioPublic" 
                    class="btn btn-sm btn-dark rounded-pill px-3 fw-bold shadow-2xs d-inline-flex align-items-center gap-1"
                  >
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Voir en direct</span>
                  </button>
                </div>
              </div>

              <!-- Sub-Tabs Navigation -->
              <ul class="nav nav-pills mt-4 gap-2 border-top pt-3 flex-wrap">
                <li class="nav-item">
                  <button 
                    type="button" 
                    @click="portfolioSubTab = 'layout'" 
                    class="nav-link rounded-pill px-4 fw-bold"
                    :class="portfolioSubTab === 'layout' ? 'active bg-success text-white' : 'text-muted'"
                  >
                    <i class="bi bi-palette-fill me-1"></i> 1. Disposition des Blocs &amp; Thème
                  </button>
                </li>
                <li class="nav-item">
                  <button 
                    type="button" 
                    @click="portfolioSubTab = 'content'" 
                    class="nav-link rounded-pill px-4 fw-bold"
                    :class="portfolioSubTab === 'content' ? 'active bg-success text-white' : 'text-muted'"
                  >
                    <i class="bi bi-pencil-square me-1"></i> 2. Contenu du CV &amp; Parcours
                  </button>
                </li>
                <li class="nav-item">
                  <button 
                    type="button" 
                    @click="portfolioSubTab = 'share'" 
                    class="nav-link rounded-pill px-4 fw-bold"
                    :class="portfolioSubTab === 'share' ? 'active bg-success text-white' : 'text-muted'"
                  >
                    <i class="bi bi-share-fill me-1"></i> 3. Partage Viral &amp; Export PDF
                  </button>
                </li>
              </ul>

            </div>

            <!-- TAB 1: 🎨 DISPOSITION DES BLOCS & THEME -->
            <div v-if="portfolioSubTab === 'layout'" class="d-flex flex-column gap-4">
              
              <!-- 1.1 Thèmes & Palettes Visuelles -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h4 class="h6 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                  <i class="bi bi-palette text-success"></i> Thème Visuel &amp; Ambiance
                </h4>
                <p class="text-muted small mb-4">
                  Choisissez une identité graphique forte. Même avec 1 000 développeurs inscrits, votre portfolio aura un rendu distinctif.
                </p>

                <div class="row g-3 mb-4">
                  <!-- Theme 1: Emerald -->
                  <div class="col-6 col-md-4">
                    <div 
                      class="p-3 rounded-4 border cursor-pointer h-100 transition shadow-2xs position-relative"
                      :class="portfolioForm.portfolio_layout.theme === 'emerald' ? 'border-success border-2 bg-success-subtle bg-opacity-10' : 'bg-white'"
                      @click="portfolioForm.portfolio_layout.theme = 'emerald'; portfolioForm.portfolio_layout.accent_color = '#00A859'"
                    >
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background-color: #00A859;"></span>
                        <strong class="small text-dark">Émeraude Tech237</strong>
                      </div>
                      <small class="text-muted extra-small d-block">Vert signature, frais, moderne et optimisé pour le dev 237.</small>
                    </div>
                  </div>

                  <!-- Theme 2: Dark Cyberpunk -->
                  <div class="col-6 col-md-4">
                    <div 
                      class="p-3 rounded-4 border cursor-pointer h-100 transition shadow-2xs position-relative"
                      :class="portfolioForm.portfolio_layout.theme === 'dark' ? 'border-success border-2 bg-dark text-white' : 'bg-dark text-white opacity-85'"
                      @click="portfolioForm.portfolio_layout.theme = 'dark'; portfolioForm.portfolio_layout.accent_color = '#10B981'"
                    >
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background-color: #10B981;"></span>
                        <strong class="small text-white">Dark Cyberpunk</strong>
                      </div>
                      <small class="text-white-50 extra-small d-block">Fond sombre ardoise, contrastes néon, look geek élégant.</small>
                    </div>
                  </div>

                  <!-- Theme 3: Navy Corporate -->
                  <div class="col-6 col-md-4">
                    <div 
                      class="p-3 rounded-4 border cursor-pointer h-100 transition shadow-2xs position-relative"
                      :class="portfolioForm.portfolio_layout.theme === 'navy' ? 'border-primary border-2 bg-primary-subtle bg-opacity-10' : 'bg-white'"
                      @click="portfolioForm.portfolio_layout.theme = 'navy'; portfolioForm.portfolio_layout.accent_color = '#2563EB'"
                    >
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background-color: #2563EB;"></span>
                        <strong class="small text-dark">Bleu Nuit Tech</strong>
                      </div>
                      <small class="text-muted extra-small d-block">Bleu cobalt &amp; indigo, sérieux, adapté aux profils corporate.</small>
                    </div>
                  </div>

                  <!-- Theme 4: Sunset Gold -->
                  <div class="col-6 col-md-4">
                    <div 
                      class="p-3 rounded-4 border cursor-pointer h-100 transition shadow-2xs position-relative"
                      :class="portfolioForm.portfolio_layout.theme === 'sunset' ? 'border-warning border-2 bg-warning-subtle bg-opacity-15' : 'bg-white'"
                      @click="portfolioForm.portfolio_layout.theme = 'sunset'; portfolioForm.portfolio_layout.accent_color = '#F59E0B'"
                    >
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background-color: #F59E0B;"></span>
                        <strong class="small text-dark">Ambre &amp; Sunset</strong>
                      </div>
                      <small class="text-muted extra-small d-block">Tons dorés et solaires, énergique et chaleureux.</small>
                    </div>
                  </div>

                  <!-- Theme 5: Purple Innovator -->
                  <div class="col-6 col-md-4">
                    <div 
                      class="p-3 rounded-4 border cursor-pointer h-100 transition shadow-2xs position-relative"
                      :class="portfolioForm.portfolio_layout.theme === 'purple' ? 'border-purple border-2' : 'bg-white'"
                      style="border-color: #8B5CF6;"
                      @click="portfolioForm.portfolio_layout.theme = 'purple'; portfolioForm.portfolio_layout.accent_color = '#8B5CF6'"
                    >
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background-color: #8B5CF6;"></span>
                        <strong class="small text-dark">Royal Purple</strong>
                      </div>
                      <small class="text-muted extra-small d-block">Violet futuriste, idéal pour UI/UX, Web3 et créatifs.</small>
                    </div>
                  </div>

                  <!-- Theme 6: Minimalist Pure -->
                  <div class="col-6 col-md-4">
                    <div 
                      class="p-3 rounded-4 border cursor-pointer h-100 transition shadow-2xs position-relative"
                      :class="portfolioForm.portfolio_layout.theme === 'minimal' ? 'border-dark border-2 bg-light' : 'bg-white'"
                      @click="portfolioForm.portfolio_layout.theme = 'minimal'; portfolioForm.portfolio_layout.accent_color = '#18181b'"
                    >
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="rounded-circle d-inline-block bg-dark" style="width: 18px; height: 18px;"></span>
                        <strong class="small text-dark">Minimal Noir &amp; Blanc</strong>
                      </div>
                      <small class="text-muted extra-small d-block">Épure absolue sans distractions, focus total sur le texte et le code.</small>
                    </div>
                  </div>
                </div>

                <!-- Custom Accent Color Picker & Card Style -->
                <div class="row g-4 pt-3 border-top">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark mb-2">Couleur d'accentuation personnalisée</label>
                    <div class="d-flex align-items-center gap-2">
                      <input type="color" v-model="portfolioForm.portfolio_layout.accent_color" class="form-control form-control-color rounded-circle p-1" style="width: 44px; height: 44px;" />
                      <input type="text" v-model="portfolioForm.portfolio_layout.accent_color" class="form-control form-control-sm font-monospace rounded-3" style="max-width: 120px;" />
                      <div class="d-flex gap-1 ms-2">
                        <button type="button" @click="portfolioForm.portfolio_layout.accent_color = '#00A859'" class="rounded-circle border-0 p-2" style="background: #00A859; width: 22px; height: 22px;"></button>
                        <button type="button" @click="portfolioForm.portfolio_layout.accent_color = '#2563EB'" class="rounded-circle border-0 p-2" style="background: #2563EB; width: 22px; height: 22px;"></button>
                        <button type="button" @click="portfolioForm.portfolio_layout.accent_color = '#F59E0B'" class="rounded-circle border-0 p-2" style="background: #F59E0B; width: 22px; height: 22px;"></button>
                        <button type="button" @click="portfolioForm.portfolio_layout.accent_color = '#8B5CF6'" class="rounded-circle border-0 p-2" style="background: #8B5CF6; width: 22px; height: 22px;"></button>
                        <button type="button" @click="portfolioForm.portfolio_layout.accent_color = '#DC2626'" class="rounded-circle border-0 p-2" style="background: #DC2626; width: 22px; height: 22px;"></button>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark mb-2">Style des bordures de cartes</label>
                    <div class="btn-group w-100" role="group">
                      <button 
                        type="button" 
                        class="btn btn-sm btn-outline-secondary"
                        :class="{ 'active': portfolioForm.portfolio_layout.card_style === 'rounded' }"
                        @click="portfolioForm.portfolio_layout.card_style = 'rounded'"
                      >
                        Arrondi doux (24px)
                      </button>
                      <button 
                        type="button" 
                        class="btn btn-sm btn-outline-secondary"
                        :class="{ 'active': portfolioForm.portfolio_layout.card_style === 'sharp' }"
                        @click="portfolioForm.portfolio_layout.card_style = 'sharp'"
                      >
                        Moderne net (6px)
                      </button>
                      <button 
                        type="button" 
                        class="btn btn-sm btn-outline-secondary"
                        :class="{ 'active': portfolioForm.portfolio_layout.card_style === 'bordered' }"
                        @click="portfolioForm.portfolio_layout.card_style = 'bordered'"
                      >
                        Contour marqué
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Block Variants (Hero, Projects, Skills) -->
                <div class="row g-4 pt-4 mt-2 border-top">
                  <div class="col-md-4">
                    <label class="form-label small fw-bold text-dark mb-1">Variante d'En-tête (Hero)</label>
                    <select v-model="portfolioForm.portfolio_layout.hero_style" class="form-select form-select-sm rounded-3">
                      <option value="centered">Centré moderne</option>
                      <option value="split">Asymétrique (2 colonnes)</option>
                      <option value="terminal">Console bash / Geek</option>
                      <option value="minimal">Minimaliste sobre</option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label small fw-bold text-dark mb-1">Variante d'affichage des Projets</label>
                    <select v-model="portfolioForm.portfolio_layout.projects_style" class="form-select form-select-sm rounded-3">
                      <option value="cards">Grille de cartes 2x2</option>
                      <option value="timeline">Chronologie verticale</option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label small fw-bold text-dark mb-1">Variante d'affichage Compétences</label>
                    <select v-model="portfolioForm.portfolio_layout.skills_style" class="form-select form-select-sm rounded-3">
                      <option value="chips">Pastilles / Chips colorées</option>
                      <option value="bars">Barres de progression</option>
                      <option value="categorized">Grille par domaine</option>
                    </select>
                  </div>
                </div>

              </div>

              <!-- 1.2 Organisateur & Ordonnancement des Blocs (Le cœur de la personnalisation) -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                  <div>
                    <h4 class="h6 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                      <i class="bi bi-layers-half text-primary"></i> Organisateur &amp; Ordre d'affichage des Blocs
                    </h4>
                    <p class="text-muted small mb-0">
                      Montez ou descendez les blocs avec les boutons <strong>▲ / ▼</strong>. Activez ou masquez les sections selon vos priorités.
                    </p>
                  </div>

                  <button type="button" @click="resetBlocksOrder" class="btn btn-sm btn-outline-secondary rounded-pill px-3 extra-small">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Ordre par défaut
                  </button>
                </div>

                <!-- Blocks List with Up/Down Controls -->
                <div class="d-flex flex-column gap-2 mt-3">
                  <div 
                    v-for="(blk, bIdx) in portfolioForm.portfolio_layout.blocks" 
                    :key="blk.id"
                    class="d-flex align-items-center justify-content-between p-3 rounded-4 border transition"
                    :class="blk.enabled ? 'bg-white shadow-2xs' : 'bg-light opacity-60'"
                  >
                    <!-- Left: Position & Name -->
                    <div class="d-flex align-items-center gap-3">
                      <!-- Position Badge -->
                      <span class="badge rounded-circle bg-dark text-white fw-bold d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                        #{{ bIdx + 1 }}
                      </span>

                      <!-- Block Icon & Label -->
                      <div class="d-flex align-items-center gap-2">
                        <i class="bi fs-5 text-secondary" :class="blk.icon || 'bi-square'"></i>
                        <div>
                          <strong class="d-block small text-dark">{{ blk.name }}</strong>
                          <span class="extra-small text-muted">{{ blk.desc || 'Bloc de présentation' }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- Right Controls: Move Up/Down + Toggle Switch -->
                    <div class="d-flex align-items-center gap-2">
                      <!-- Move Up -->
                      <button 
                        type="button" 
                        class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;"
                        :disabled="bIdx === 0"
                        @click="moveBlockUp(bIdx)"
                        title="Monter ce bloc"
                      >
                        <i class="bi bi-arrow-up text-dark small"></i>
                      </button>

                      <!-- Move Down -->
                      <button 
                        type="button" 
                        class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;"
                        :disabled="bIdx === portfolioForm.portfolio_layout.blocks.length - 1"
                        @click="moveBlockDown(bIdx)"
                        title="Descendre ce bloc"
                      >
                        <i class="bi bi-arrow-down text-dark small"></i>
                      </button>

                      <div class="vr mx-1" style="height: 24px;"></div>

                      <!-- Enabled Toggle Switch -->
                      <div class="form-check form-switch mb-0">
                        <input 
                          class="form-check-input cursor-pointer" 
                          type="checkbox" 
                          v-model="blk.enabled"
                          :id="'blk_toggle_' + blk.id"
                        />
                        <label class="form-check-label extra-small fw-semibold ms-1 cursor-pointer" :for="'blk_toggle_' + blk.id">
                          {{ blk.enabled ? 'Affiché' : 'Masqué' }}
                        </label>
                      </div>
                    </div>

                  </div>
                </div>

              </div>

            </div>

            <!-- TAB 2: ✍️ CONTENU DU CV & PARCOURS -->
            <div v-else-if="portfolioSubTab === 'content'" class="d-flex flex-column gap-4">
              
              <!-- 2.1 Identifiant Unique & En-tête -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h4 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                  <i class="bi bi-person-badge text-primary"></i> Identifiant &amp; En-tête du Profil
                </h4>

                <div class="row g-3">
                  <!-- Username input -->
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">
                      Pseudo public pour votre lien (@username) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text bg-light fw-bold text-muted">tech237.net/@</span>
                      <input 
                        type="text" 
                        v-model="portfolioForm.username" 
                        class="form-control font-monospace" 
                        placeholder="didace-dev" 
                        required 
                        maxlength="50"
                      />
                    </div>
                    <small class="text-muted extra-small">Uniquement lettres minuscules, chiffres, tirets et underscores.</small>
                  </div>

                  <!-- Headline -->
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Titre Professionnel (Headline)</label>
                    <input 
                      type="text" 
                      v-model="portfolioForm.headline" 
                      class="form-control" 
                      placeholder="ex: Lead Developer Fullstack Laravel &amp; Vue.js | Mobile Flutter" 
                      maxlength="150"
                    />
                  </div>

                  <!-- Location -->
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Ville &amp; Pays</label>
                    <input 
                      type="text" 
                      v-model="portfolioForm.location" 
                      class="form-control" 
                      placeholder="ex: Douala, Cameroun / Remote" 
                      maxlength="100"
                    />
                  </div>

                  <!-- Availability -->
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Disponibilité actuelle</label>
                    <select v-model="portfolioForm.availability" class="form-select">
                      <option value="open">🟢 Ouvert aux opportunités (Freelance &amp; CDI)</option>
                      <option value="freelance">⚡ Disponible pour missions freelance</option>
                      <option value="full_time">🎯 En recherche active d'un CDI / Emploi</option>
                      <option value="not_available">⚪ Actuellement en poste (Non disponible)</option>
                    </select>
                  </div>

                  <!-- Bio -->
                  <div class="col-12">
                    <label class="form-label small fw-bold text-dark">Présentation &amp; Biographie (Bio)</label>
                    <textarea 
                      v-model="portfolioForm.bio" 
                      class="form-control" 
                      rows="4" 
                      placeholder="Décrivez votre parcours, vos technologies de prédilection, les défis techniques que vous aimez résoudre..."
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- 2.2 Compétences Techniques (Skills) -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                  <div>
                    <h4 class="h6 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                      <i class="bi bi-cpu text-success"></i> Compétences &amp; Stack Technique
                    </h4>
                    <p class="text-muted small mb-0">Ajoutez les technos que vous maîtrisez pour que les recruteurs vous repèrent en un clin d'œil.</p>
                  </div>
                </div>

                <!-- Add Skill input -->
                <div class="input-group mb-3">
                  <input 
                    type="text" 
                    v-model="newSkillName" 
                    @keyup.enter="addSkill" 
                    class="form-control" 
                    placeholder="Ajouter une compétence (ex: Docker, GraphQL, Flutter...)" 
                  />
                  <select v-model="newSkillCategory" class="form-select" style="max-width: 140px;">
                    <option value="Backend">Backend</option>
                    <option value="Frontend">Frontend</option>
                    <option value="Mobile">Mobile</option>
                    <option value="DevOps">DevOps</option>
                    <option value="Database">Database</option>
                    <option value="Outils">Outils</option>
                  </select>
                  <button type="button" @click="addSkill" class="btn btn-success fw-bold px-4">
                    + Ajouter
                  </button>
                </div>

                <!-- Quick add presets -->
                <div class="d-flex align-items-center gap-1 flex-wrap mb-3">
                  <span class="extra-small text-muted me-1">Suggestions rapides :</span>
                  <button 
                    v-for="s in ['PHP', 'Laravel', 'Vue.js', 'React', 'Flutter', 'Docker', 'Python', 'Node.js', 'PostgreSQL', 'TailwindCSS', 'TypeScript', 'Git']" 
                    :key="s"
                    type="button" 
                    @click="addQuickSkill(s, 'Tech')"
                    class="btn btn-xs btn-light border rounded-pill px-2 py-0 extra-small"
                  >
                    + {{ s }}
                  </button>
                </div>

                <!-- Live Skills Tags -->
                <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded-4 min-h-60">
                  <span 
                    v-for="(sk, skIdx) in portfolioForm.skills" 
                    :key="skIdx"
                    class="badge bg-white text-dark border rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-2xs fs-6"
                  >
                    <span>{{ typeof sk === 'object' ? sk.name : sk }}</span>
                    <small v-if="typeof sk === 'object' && sk.category" class="text-muted extra-small">({{ sk.category }})</small>
                    <i @click="removeSkill(skIdx)" class="bi bi-x-circle text-danger cursor-pointer fs-6"></i>
                  </span>
                  <span v-if="!portfolioForm.skills.length" class="text-muted extra-small my-auto">
                    Aucune compétence ajoutée. Tapez un nom ci-dessus pour commencer !
                  </span>
                </div>
              </div>

              <!-- 2.3 Réseaux Sociaux & Liens Professionnels -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h4 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                  <i class="bi bi-globe2 text-primary"></i> Réseaux Sociaux &amp; Contact Direct
                </h4>

                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-github me-1"></i> Profil GitHub</label>
                    <input type="url" v-model="portfolioForm.social_links.github" class="form-control form-control-sm" placeholder="https://github.com/mon-compte" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-linkedin me-1 text-primary"></i> Profil LinkedIn</label>
                    <input type="url" v-model="portfolioForm.social_links.linkedin" class="form-control form-control-sm" placeholder="https://linkedin.com/in/mon-profil" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-twitter-x me-1"></i> Twitter / X</label>
                    <input type="url" v-model="portfolioForm.social_links.twitter" class="form-control form-control-sm" placeholder="https://x.com/mon-pseudo" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-globe me-1 text-success"></i> Site Web / Blog</label>
                    <input type="url" v-model="portfolioForm.social_links.website" class="form-control form-control-sm" placeholder="https://monsite.cm" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-whatsapp me-1 text-success"></i> Numéro WhatsApp Pro</label>
                    <input type="text" v-model="portfolioForm.social_links.whatsapp" class="form-control form-control-sm" placeholder="ex: 237699000000" />
                  </div>
                </div>
              </div>

              <!-- 2.4 Expériences Professionnelles -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h4 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-briefcase text-warning"></i> Expériences Professionnelles
                  </h4>
                  <button type="button" @click="addExperience" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                    + Ajouter une expérience
                  </button>
                </div>

                <div v-if="portfolioForm.experiences.length" class="d-flex flex-column gap-3">
                  <div 
                    v-for="(exp, eIdx) in portfolioForm.experiences" 
                    :key="eIdx"
                    class="p-3 rounded-4 border bg-light position-relative"
                  >
                    <button type="button" @click="removeExperience(eIdx)" class="btn btn-sm btn-link text-danger position-absolute top-0 end-0 mt-2 me-2" title="Supprimer">
                      <i class="bi bi-trash"></i>
                    </button>
                    <div class="row g-2">
                      <div class="col-md-6">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Poste occupé</label>
                        <input type="text" v-model="exp.role" class="form-control form-control-sm" placeholder="ex: Développeur Backend Laravel" />
                      </div>
                      <div class="col-md-6">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Entreprise / Client</label>
                        <input type="text" v-model="exp.company" class="form-control form-control-sm" placeholder="ex: Tech Solutions SARL" />
                      </div>
                      <div class="col-md-6">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Période</label>
                        <input type="text" v-model="exp.period" class="form-control form-control-sm" placeholder="ex: Jan 2024 - Présent (1 an)" />
                      </div>
                      <div class="col-md-6">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Localisation</label>
                        <input type="text" v-model="exp.location" class="form-control form-control-sm" placeholder="ex: Douala / Remote" />
                      </div>
                      <div class="col-12">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Réalisations &amp; Technologies clés</label>
                        <textarea v-model="exp.description" class="form-control form-control-sm" rows="2" placeholder="Conception d'APIs REST, mise en place de la passerelle Mobile Money, gain de 40% en performance..."></textarea>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-3 text-muted extra-small">
                  Aucune expérience saisie. Cliquez sur "+ Ajouter une expérience" pour enrichir votre CV.
                </div>
              </div>

              <!-- 2.5 Formations & Certifications -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h4 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-mortarboard text-info"></i> Formations &amp; Diplômes
                  </h4>
                  <button type="button" @click="addEducation" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
                    + Ajouter une formation
                  </button>
                </div>

                <div v-if="portfolioForm.educations.length" class="d-flex flex-column gap-3">
                  <div 
                    v-for="(edu, dIdx) in portfolioForm.educations" 
                    :key="dIdx"
                    class="p-3 rounded-4 border bg-light position-relative"
                  >
                    <button type="button" @click="removeEducation(dIdx)" class="btn btn-sm btn-link text-danger position-absolute top-0 end-0 mt-2 me-2" title="Supprimer">
                      <i class="bi bi-trash"></i>
                    </button>
                    <div class="row g-2">
                      <div class="col-md-5">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Diplôme / Certification</label>
                        <input type="text" v-model="edu.degree" class="form-control form-control-sm" placeholder="ex: Licence Informatique / Certif AWS" />
                      </div>
                      <div class="col-md-5">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Établissement / École</label>
                        <input type="text" v-model="edu.institution" class="form-control form-control-sm" placeholder="ex: Université de Yaoundé I" />
                      </div>
                      <div class="col-md-2">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Année</label>
                        <input type="text" v-model="edu.year" class="form-control form-control-sm" placeholder="ex: 2023" />
                      </div>
                      <div class="col-12">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Détails / Mention</label>
                        <input type="text" v-model="edu.description" class="form-control form-control-sm" placeholder="ex: Spécialité Génie Logiciel" />
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-3 text-muted extra-small">
                  Aucune formation enregistrée. Cliquez sur "+ Ajouter une formation".
                </div>
              </div>

              <!-- 2.6 Sections Personnalisées Libres (Custom Blocks) -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <h4 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                      <i class="bi bi-stars text-warning"></i> Blocs Libres Personnalisés
                    </h4>
                    <small class="text-muted extra-small">Ajoutez des sections libres à votre guise : Services &amp; Tarifs, Recommandations, etc.</small>
                  </div>
                  <button type="button" @click="addCustomSection" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 fw-bold">
                    + Ajouter un bloc libre
                  </button>
                </div>

                <div v-if="portfolioForm.custom_sections.length" class="d-flex flex-column gap-3">
                  <div 
                    v-for="(cSec, csIdx) in portfolioForm.custom_sections" 
                    :key="csIdx"
                    class="p-3 rounded-4 border bg-light position-relative"
                  >
                    <button type="button" @click="removeCustomSection(csIdx)" class="btn btn-sm btn-link text-danger position-absolute top-0 end-0 mt-2 me-2" title="Supprimer">
                      <i class="bi bi-trash"></i>
                    </button>
                    <div class="row g-2">
                      <div class="col-md-8">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Titre de la section</label>
                        <input type="text" v-model="cSec.title" class="form-control form-control-sm" placeholder="ex: Mes Services de Développement Freelance" />
                      </div>
                      <div class="col-md-4">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Icône Bootstrap</label>
                        <input type="text" v-model="cSec.icon" class="form-control form-control-sm font-monospace" placeholder="bi-stars" />
                      </div>
                      <div class="col-12">
                        <label class="form-label extra-small fw-bold text-dark mb-1">Contenu texte / descriptif</label>
                        <textarea v-model="cSec.content" class="form-control form-control-sm" rows="3" placeholder="Détaillez vos offres, conditions, méthodologie ou recommandations..."></textarea>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-3 text-muted extra-small">
                  Aucun bloc libre personnalisé. Vous pouvez en créer pour vous démarquer.
                </div>
              </div>

            </div>

            <!-- TAB 3: 🚀 PARTAGE VIRAL & EXPORT PDF -->
            <div v-else-if="portfolioSubTab === 'share'" class="d-flex flex-column gap-4">
              
              <!-- Viral Social Media Banner -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center gap-3 mb-4">
                  <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-share-fill fs-4"></i>
                  </div>
                  <div>
                    <h4 class="h5 fw-bold text-dark mb-1">Viralité &amp; Référencement Réseaux Sociaux (SEO)</h4>
                    <p class="text-muted small mb-0">
                      Les balises <strong>Open Graph</strong> pour WhatsApp, LinkedIn et Twitter sont prêtes à l'emploi. Dès que vous partagez votre lien, les réseaux affichent une carte de prévisualisation élégante avec votre nom, votre titre et le logo officiel Tech237.
                    </p>
                  </div>
                </div>

                <!-- Direct Social Share Buttons -->
                <div class="row g-3 mb-4">
                  <!-- WhatsApp -->
                  <div class="col-md-4">
                    <a :href="whatsappPortfolioShareUrl" target="_blank" class="btn btn-success w-100 py-3 rounded-4 shadow-sm fw-bold d-flex align-items-center justify-content-center gap-2" style="background-color: #25D366; border-color: #25D366;">
                      <i class="bi bi-whatsapp fs-5"></i>
                      <span>Partager sur WhatsApp</span>
                    </a>
                  </div>

                  <!-- LinkedIn -->
                  <div class="col-md-4">
                    <a :href="linkedinPortfolioShareUrl" target="_blank" class="btn btn-primary w-100 py-3 rounded-4 shadow-sm fw-bold d-flex align-items-center justify-content-center gap-2" style="background-color: #0A66C2; border-color: #0A66C2;">
                      <i class="bi bi-linkedin fs-5"></i>
                      <span>Partager sur LinkedIn</span>
                    </a>
                  </div>

                  <!-- Twitter / X -->
                  <div class="col-md-4">
                    <a :href="twitterPortfolioShareUrl" target="_blank" class="btn btn-dark w-100 py-3 rounded-4 shadow-sm fw-bold d-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-twitter-x fs-5"></i>
                      <span>Partager sur X (Twitter)</span>
                    </a>
                  </div>
                </div>

                <!-- OpenGraph Meta Tags Preview Card -->
                <div class="p-4 rounded-4 bg-light border">
                  <span class="badge bg-secondary-subtle text-secondary rounded-pill extra-small fw-bold mb-2">Aperçu de la carte WhatsApp / LinkedIn :</span>
                  <div class="card border rounded-3 p-3 bg-white shadow-2xs max-w-500">
                    <div class="d-flex align-items-center gap-3">
                      <img 
                        v-if="user.avatar" 
                        :src="user.avatar.startsWith('/') ? user.avatar : '/' + user.avatar" 
                        class="rounded-circle border" 
                        style="width: 54px; height: 54px; object-fit: cover;" 
                        :alt="user.name"
                      />
                      <div v-else class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        {{ (user.name || 'U').charAt(0) }}
                      </div>
                      <div class="min-w-0">
                        <strong class="d-block text-dark text-truncate small mb-0">{{ user.name }} &bull; Portfolio Tech237</strong>
                        <span class="extra-small text-muted d-block text-truncate">{{ portfolioForm.headline || 'Développeur & Bâtisseur Tech au Cameroun' }}</span>
                        <span class="extra-small text-success fw-bold">tech237.net/@{{ portfolioForm.username || 'profil' }}</span>
                      </div>
                    </div>
                  </div>
                </div>

              </div>

              <!-- Export CV PDF Card -->
              <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                      <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                    </div>
                    <div>
                      <h4 class="h6 fw-bold text-dark mb-1">Télécharger &amp; Imprimer votre CV au format A4</h4>
                      <p class="text-muted small mb-0">
                        Votre page portfolio contient une feuille de style d'impression qui masque automatiquement les boutons pour générer un CV au format papier ou PDF parfait.
                      </p>
                    </div>
                  </div>

                  <a :href="portfolioShareUrl" target="_blank" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-bold shadow-2xs">
                    <i class="bi bi-download me-1"></i> Ouvrir &amp; Exporter PDF
                  </a>
                </div>
              </div>

            </div>

            <!-- Sticky Bottom Action Bar -->
            <div class="card border-0 shadow-lg rounded-4 p-3 bg-white sticky-bottom mt-4" style="z-index: 1020;">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="form-check form-switch mb-0">
                  <input 
                    class="form-check-input cursor-pointer" 
                    type="checkbox" 
                    v-model="portfolioForm.portfolio_published" 
                    id="portfolio_published_check"
                  />
                  <label class="form-check-label small fw-bold text-dark cursor-pointer ms-1" for="portfolio_published_check">
                    Portfolio Public (Accessible à tous via <code>/@{{ portfolioForm.username || 'pseudo' }}</code>)
                  </label>
                </div>

                <div class="d-flex align-items-center gap-2">
                  <button 
                    type="button" 
                    @click="openPortfolioPublic" 
                    class="btn btn-sm btn-light border rounded-pill px-3 fw-bold"
                  >
                    Aperçu
                  </button>

                  <button 
                    type="button" 
                    @click="savePortfolioSettings" 
                    class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
                    :disabled="isSavingPortfolio"
                  >
                    <span v-if="isSavingPortfolio" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="bi bi-check2-circle fs-6"></i>
                    <span>{{ isSavingPortfolio ? 'Enregistrement...' : 'Enregistrer mon Portfolio' }}</span>
                  </button>
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>

    </div>

    <!-- RESPONSIVE MOBILE DRAWER (Sliding Left-to-Right) -->
    <div 
      v-if="mobileDrawerOpen" 
      class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" 
      style="z-index: 1040;" 
      @click="mobileDrawerOpen = false"
    ></div>

    <div 
      class="position-fixed top-0 start-0 h-100 bg-white shadow-lg d-lg-none d-flex flex-column"
      :style="{
        width: '300px',
        maxWidth: '85vw',
        zIndex: 1050,
        transform: mobileDrawerOpen ? 'translateX(0)' : 'translateX(-100%)',
        transition: 'transform 0.3s cubic-bezier(0.16, 1, 0.3, 1)'
      }"
    >
      <div class="p-3 bg-dark text-white d-flex justify-content-between align-items-center">
        <div class="fw-bold"><i class="bi bi-person-circle text-danger me-2"></i>Mon Espace</div>
        <button class="btn btn-outline-light btn-sm rounded-circle" @click="mobileDrawerOpen = false">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="p-3 border-bottom text-center bg-light">
        <div class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 56px; height: 56px; font-size: 20px;">
          {{ (user.name || 'U').charAt(0).toUpperCase() }}
        </div>
        <div class="fw-bold text-dark">{{ user.first_name }} {{ user.name }}</div>
        <div class="small text-muted text-truncate">{{ user.email }}</div>
      </div>

      <div class="list-group list-group-flush p-2 flex-grow-1 overflow-y-auto">
        <button 
          @click="selectMobileSection('dashboard')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'dashboard' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-speedometer2 me-2"></i> Tableau de bord
        </button>
        <button 
          @click="selectMobileSection('startups')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'startups' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-briefcase me-2"></i> Mes Startups ({{ startups.length }})
        </button>
        <button 
          @click="selectMobileSection('projects')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'projects' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-kanban me-2"></i> Mes Projets ({{ projects.length }})
        </button>
        <button 
          @click="selectMobileSection('jobs')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'jobs' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-briefcase-fill me-2"></i> Offres d'Emploi ({{ (jobOffers || []).length }})
        </button>
        <button 
          @click="selectMobileSection('conferences')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'conferences' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-calendar-event me-2"></i> Événements & Conf. ({{ (conferences || []).length }})
        </button>
        <button 
          @click="selectMobileSection('messages')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'messages' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-envelope me-2"></i> Messages
        </button>
        <button 
          @click="selectMobileSection('comments')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'comments' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-chat-dots me-2"></i> Commentaires
        </button>
        <button 
          @click="selectMobileSection('documents')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'documents' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-file-earmark-lock me-2"></i> Documents & CNI
        </button>
        <button 
          @click="selectMobileSection('bookmarks')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'bookmarks' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-bookmark-heart me-2"></i> Mes Favoris ({{ (bookmarks || []).length }})
        </button>
        <button 
          @click="selectMobileSection('portfolio')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'portfolio' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-briefcase me-2 text-warning"></i> Mon CV &amp; Portfolio
        </button>
        <button 
          @click="selectMobileSection('profile')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'profile' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-person-gear me-2"></i> Mon Profil
        </button>
      </div>
    </div>





    <!-- STATIC CONFIRMATION MODAL: RECEIVED INVITATION ACTION -->
    <div v-if="invitationActionModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;" @click.self="invitationActionModal = null">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 p-4 text-center bg-white">
          
          <!-- Accept Modal Content -->
          <div v-if="invitationActionModal === 'accept'">
            <div class="rounded-circle bg-success-subtle text-success mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
              <i class="bi bi-people-fill fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Accepter la collaboration ?</h5>
            <p class="small text-muted mb-4">
              Vous allez rejoindre le projet <strong>{{ targetInvitation?.project?.name }}</strong> porté par <strong>{{ targetInvitation?.inviter?.name }}</strong>. 
              Vous pourrez dès lors consigner des étapes Devlog et ajouter des photos.
            </p>
            <div class="d-flex justify-content-center gap-2">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="invitationActionModal = null">Annuler</button>
              <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" :disabled="isCollabProcessing" @click="submitInvitationAction">
                Accepter et Rejoindre
              </button>
            </div>
          </div>

          <!-- Decline Modal Content -->
          <div v-else-if="invitationActionModal === 'decline'">
            <div class="rounded-circle bg-secondary-subtle text-secondary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
              <i class="bi bi-x-circle-fill fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Refuser l'invitation ?</h5>
            <p class="small text-muted mb-4">
              Cette invitation deviendra inutilisable. Le créateur pourra toujours vous réinviter à l'avenir si vous changez d'avis.
            </p>
            <div class="d-flex justify-content-center gap-2">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="invitationActionModal = null">Annuler</button>
              <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" :disabled="isCollabProcessing" @click="submitInvitationAction">
                Refuser l'invitation
              </button>
            </div>
          </div>

          <!-- Block Modal Content -->
          <div v-else-if="invitationActionModal === 'block'">
            <div class="rounded-circle bg-danger-subtle text-danger mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
              <i class="bi bi-shield-slash-fill fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Bloquer toute collaboration future ?</h5>
            <div class="alert alert-danger p-3 small text-start mb-3 border-0 rounded-3">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>
              <strong>Attention :</strong> Si vous confirmez, <strong>{{ targetInvitation?.inviter?.name }}</strong> ne pourra plus jamais vous envoyer d'invitations sur aucun projet, et vous ne pourrez plus non plus lui en envoyer.
            </div>
            <div class="d-flex justify-content-center gap-2">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="invitationActionModal = null">Annuler</button>
              <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" :disabled="isCollabProcessing" @click="submitInvitationAction">
                Ne plus jamais collaborer
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- MODAL: NOUVEAU MESSAGE PRIVE -->
    <div 
      v-if="showNewMessageModal" 
      class="modal fade show d-block" 
      tabindex="-1" 
      style="background-color: rgba(0,0,0,0.6); z-index: 1060;" 
      @click.self="showNewMessageModal = false"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-white">
          <div class="modal-header bg-dark text-white p-3 p-md-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-pencil-square fs-6"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold mb-0">Nouveau message privé</h5>
                <small class="text-white-50">Écrivez à un membre ou contactez l'équipe Tech237</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="showNewMessageModal = false"></button>
          </div>

          <div class="modal-body p-4">
            <form @submit.prevent="submitNewMessage">
              
              <!-- Recipient Selector with Autocomplete -->
              <div class="mb-3 position-relative" ref="newMsgAutocompleteContainer">
                <label class="form-label small fw-bold text-dark">Destinataire <span class="text-danger">*</span></label>
                
                <!-- If recipient already selected -->
                <div v-if="newMsgSelectedRecipient" class="p-2 border rounded-3 bg-light d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <div 
                      v-if="hasCustomAvatar(newMsgSelectedRecipient.avatar)"
                      class="rounded-circle border overflow-hidden" 
                      style="width: 36px; height: 36px;"
                    >
                      <img :src="newMsgSelectedRecipient.avatar.startsWith('/') ? newMsgSelectedRecipient.avatar : '/' + newMsgSelectedRecipient.avatar" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div 
                      v-else 
                      class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs"
                      :style="{ width: '36px', height: '36px', backgroundColor: getAvatarBg(newMsgSelectedRecipient.name), fontSize: '13px' }"
                    >
                      {{ getInitials(newMsgSelectedRecipient.name) }}
                    </div>
                    <div>
                      <strong class="d-block small text-dark">{{ newMsgSelectedRecipient.name }}</strong>
                      <small class="text-muted" style="font-size: 11px;">{{ newMsgSelectedRecipient.email }}</small>
                    </div>
                  </div>
                  <button type="button" @click="newMsgSelectedRecipient = null" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0 extra-small">
                    Changer
                  </button>
                </div>

                <!-- Recipient Search input -->
                <div v-else>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input 
                      type="text" 
                      class="form-control border-start-0" 
                      v-model="newMsgSearchQuery" 
                      @input="handleNewMsgSearch"
                      @focus="handleNewMsgFocus"
                      placeholder="Tapez le nom ou l'email d'un membre..."
                      autocomplete="off"
                    >
                    <button 
                      type="button" 
                      @click="selectSupportRecipient" 
                      class="btn btn-outline-danger btn-sm px-3"
                      title="Contacter le support Tech237 directement"
                    >
                      <i class="bi bi-shield-fill me-1"></i> Support Tech237
                    </button>
                  </div>

                  <!-- Autocomplete results dropdown -->
                  <div 
                    v-if="showNewMsgDropdown && newMsgSearchResults.length > 0" 
                    class="autocomplete-dropdown shadow-lg rounded-3 border bg-white mt-1 py-1 position-absolute w-100"
                    style="z-index: 1070; max-height: 220px; overflow-y: auto;"
                  >
                    <div class="px-3 py-1 small text-muted text-uppercase fw-bold border-bottom" style="font-size: 10px;">
                      Membres trouvés (cliquez pour sélectionner)
                    </div>
                    <button
                      type="button"
                      v-for="u in newMsgSearchResults"
                      :key="u.id"
                      class="dropdown-item px-3 py-2 d-flex align-items-center gap-3 text-start border-0 bg-transparent w-100 cursor-pointer"
                      @click="selectNewMsgRecipient(u)"
                    >
                      <div 
                        v-if="hasCustomAvatar(u.avatar)"
                        class="rounded-circle border overflow-hidden flex-shrink-0" 
                        style="width: 32px; height: 32px;"
                      >
                        <img :src="u.avatar.startsWith('/') ? u.avatar : '/' + u.avatar" class="w-100 h-100 object-fit-cover">
                      </div>
                      <div 
                        v-else 
                        class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs"
                        :style="{ width: '32px', height: '32px', backgroundColor: getAvatarBg(u.name), fontSize: '12px' }"
                      >
                        {{ getInitials(u.name) }}
                      </div>
                      <div class="flex-grow-1 overflow-hidden">
                        <strong class="d-block small text-dark text-truncate mb-0">{{ u.name }}</strong>
                        <small class="text-muted text-truncate d-block" style="font-size: 11px;">{{ u.email }}</small>
                      </div>
                      <i class="bi bi-plus-circle text-success small"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Subject -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Sujet du message <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="newMsgSubject" 
                  placeholder="ex: Question sur le projet Tech Pay, opportunité..."
                  required
                >
              </div>

              <!-- Message Text -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Message <span class="text-danger">*</span></label>
                <textarea 
                  class="form-control" 
                  rows="4" 
                  v-model="newMsgText" 
                  placeholder="Écrivez votre message..."
                  required
                ></textarea>
              </div>

              <!-- Optional Attachment -->
              <div class="mb-4">
                <label class="form-label small fw-bold text-dark mb-1">Pièce jointe (facultatif)</label>
                <div class="d-flex align-items-center gap-2">
                  <button 
                    type="button" 
                    @click="triggerNewMsgFileInput" 
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                  >
                    <i class="bi bi-paperclip me-1"></i> Choisir un fichier
                  </button>
                  <span v-if="newMsgAttachment" class="badge bg-light text-dark border p-2 d-flex align-items-center gap-2">
                    <span class="text-truncate" style="max-width: 200px;">{{ newMsgAttachment.name }}</span>
                    <button type="button" @click="newMsgAttachment = null" class="btn-close btn-sm" style="font-size: 8px;"></button>
                  </span>
                  <input 
                    type="file" 
                    ref="newMsgFileInput" 
                    @change="onNewMsgFileSelect" 
                    accept="image/*,.pdf,.doc,.docx,.txt,.zip" 
                    class="d-none"
                  >
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Images, PDF, Word, TXT, ZIP jusqu'à 10 Mo.</small>
              </div>

              <!-- Footer Buttons -->
              <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <button type="button" class="btn btn-light rounded-pill px-4" @click="showNewMessageModal = false">Annuler</button>
                <button 
                  type="submit" 
                  class="btn btn-success rounded-pill px-4 fw-bold shadow-sm"
                  :disabled="isSendingNewMsg || (!newMsgSelectedRecipient && !newMsgIsSupport)"
                >
                  <span v-if="isSendingNewMsg" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-send-fill me-1"></i>
                  Envoyer le message
                </button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  user: { type: Object, required: true },
  startups: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  pocs: { type: Array, default: () => [] },
  collaborativeProjects: { type: Array, default: () => [] },
  pendingInvitations: { type: Array, default: () => [] },
  pendingTimelines: { type: Array, default: () => [] },
  pendingImages: { type: Array, default: () => [] },
  jobOffers: { type: Array, default: () => [] },
  conferences: { type: Array, default: () => [] },
  documents: { type: Array, default: () => [] },
  messages: { type: Array, default: () => [] },
  unreadMessagesCount: { type: Number, default: 0 },
  comments: { type: Array, default: () => [] },
  unreadCommentsCount: { type: Number, default: 0 },
  bookmarks: { type: Array, default: () => [] },
  badges: { type: Array, default: () => [] },
  alertPreference: { type: Object, default: () => ({}) },
  isBanned: { type: Boolean, default: false },
  banReason: { type: String, default: null },
  activeSection: { type: String, default: 'dashboard' },
  stats: { type: Object, default: () => ({}) },
})

// Bookmarks Logic
const bookmarkFilter = ref('all')
const filteredBookmarks = computed(() => {
  if (bookmarkFilter.value === 'all') return props.bookmarks || []
  return (props.bookmarks || []).filter(b => b.type === bookmarkFilter.value)
})

function getBookmarkBadgeClass(type) {
  return {
    'article': 'bg-primary text-white',
    'project': 'bg-success text-white',
    'job': 'bg-danger text-white',
    'game_review': 'bg-info text-dark',
    'app_review': 'bg-secondary text-white',
  }[type] || 'bg-secondary text-white'
}

function getBookmarkIcon(type) {
  return {
    'article': 'bi bi-file-text',
    'project': 'bi bi-rocket-takeoff',
    'job': 'bi bi-briefcase',
    'game_review': 'bi bi-controller',
    'app_review': 'bi bi-phone',
  }[type] || 'bi bi-bookmark'
}

function removeBookmark(bookmark) {
  router.post('/espace-utilisateur/favoris/toggle', {
    item_type: bookmark.type,
    item_id: bookmark.item_id,
  }, {
    preserveScroll: true,
  })
}

// Alert Preferences Logic
const alertSavedMessage = ref(false)
const alertForm = useForm({
  alert_jobs: props.alertPreference?.alert_jobs ?? true,
  alert_conferences: props.alertPreference?.alert_conferences ?? true,
  alert_projects: props.alertPreference?.alert_projects ?? true,
  alert_newsletter: props.alertPreference?.alert_newsletter ?? true,
})

function updateAlertsSubmit() {
  alertForm.post('/espace-utilisateur/alertes', {
    preserveScroll: true,
    onSuccess: () => {
      alertSavedMessage.value = true
      setTimeout(() => {
        alertSavedMessage.value = false
      }, 4000)
    }
  })
}

function stripHtml(text) {
  if (!text) return ''
  return text.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim()
}

function formatDate(val) {
  if (!val) return ''
  const d = new Date(val)
  if (isNaN(d.getTime())) return val
  return d.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

function formatDateTime(val) {
  if (!val) return ''
  const d = new Date(val)
  if (isNaN(d.getTime())) return val
  return d.toLocaleString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// --------------------------------------------------------------------------
// PROJECT COLLABORATION & INVITATION SYSTEM
// --------------------------------------------------------------------------
const projectSubTab = ref('owned')
const invitationActionModal = ref(null)
const targetInvitation = ref(null)
const isCollabProcessing = ref(false)

function copyInviteLink(token) {
  const url = `${window.location.origin}/invitations/${token}`
  navigator.clipboard.writeText(url)
  alert('Lien d\'invitation copié dans le presse-papier !')
}

function openInvitationActionModal(inv, action) {
  targetInvitation.value = inv
  invitationActionModal.value = action
}

function submitInvitationAction() {
  if (!targetInvitation.value || !invitationActionModal.value) return
  isCollabProcessing.value = true
  const token = targetInvitation.value.token
  const action = invitationActionModal.value

  const endpointMap = {
    accept: `/invitations/${token}/accepter`,
    decline: `/invitations/${token}/refuser`,
    block: `/invitations/${token}/bloquer`,
  }

  router.post(endpointMap[action], {}, {
    onFinish: () => {
      isCollabProcessing.value = false
      invitationActionModal.value = null
      targetInvitation.value = null
    }
  })
}

function validateTimelineAction(timelineId, action) {
  if (confirm(action === 'approve' ? 'Voulez-vous valider et publier cette étape Devlog sur la fiche publique ?' : 'Voulez-vous rejeter cette proposition ?')) {
    router.post(`/espace-utilisateur/timelines/${timelineId}/valider`, { action })
  }
}

function validateImageAction(imageId, action) {
  if (confirm(action === 'approve' ? 'Voulez-vous valider et publier cette photo dans la galerie publique ?' : 'Voulez-vous rejeter cette photo ?')) {
    router.post(`/espace-utilisateur/images/${imageId}/valider`, { action })
  }
}

const currentSection = ref(props.activeSection || 'dashboard')
const mobileDrawerOpen = ref(false)
const chartPeriod = ref('day')
const chartCanvasRef = ref(null)

// Conferences Filtering State
const conferenceSearchName = ref('')
const conferenceFilterDate = ref('')

const filteredUserConferences = computed(() => {
  return (props.conferences || []).filter(c => {
    const matchesName = !conferenceSearchName.value ||
      (c.title_fr && c.title_fr.toLowerCase().includes(conferenceSearchName.value.toLowerCase())) ||
      (c.title_en && c.title_en.toLowerCase().includes(conferenceSearchName.value.toLowerCase()))

    const matchesDate = !conferenceFilterDate.value ||
      (c.event_date && c.event_date.startsWith(conferenceFilterDate.value))

    return matchesName && matchesDate
  })
})

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search)
  const sec = urlParams.get('section')
  if (sec) {
    currentSection.value = sec
  }
  nextTick(() => {
    renderChart()
  })
})

function setSection(sec) {
  currentSection.value = sec
  const url = new URL(window.location)
  url.searchParams.set('section', sec)
  window.history.replaceState({}, '', url)
  if (sec === 'dashboard') {
    nextTick(() => renderChart())
  }
}

function selectMobileSection(sec) {
  setSection(sec)
  mobileDrawerOpen.value = false
}

function changePeriod(period) {
  chartPeriod.value = period
  renderChart()
}

function renderChart() {
  const canvas = chartCanvasRef.value || document.getElementById('userAnalyticsChart')
  if (!canvas) return

  const chartData = props.stats?.charts?.[chartPeriod.value] || {
    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
    views: [15, 25, 45, 30, 60, 75, 70],
    interactions: [2, 5, 8, 4, 11, 14, 9]
  }

  if (window.MeltyChart && typeof window.MeltyChart.drawLineChart === 'function') {
    window.MeltyChart.drawLineChart('userAnalyticsChart', chartData.labels, chartData.views, {
      strokeColor: '#E11D48',
      fillColor: 'rgba(225, 29, 72, 0.2)'
    })
  } else {
    const ctx = canvas.getContext('2d')
    const dpr = window.devicePixelRatio || 1
    const rect = canvas.getBoundingClientRect()
    canvas.width = rect.width * dpr
    canvas.height = rect.height * dpr
    ctx.scale(dpr, dpr)

    const w = rect.width
    const h = rect.height
    const padding = { top: 20, right: 20, bottom: 35, left: 35 }
    const chartW = w - padding.left - padding.right
    const chartH = h - padding.top - padding.bottom

    ctx.clearRect(0, 0, w, h)
    const data = chartData.views
    const maxVal = Math.max(...data, 10)

    ctx.strokeStyle = '#e2e8f0'
    ctx.lineWidth = 1
    ctx.fillStyle = '#64748b'
    ctx.font = '11px Poppins, sans-serif'
    ctx.textAlign = 'right'

    for (let i = 0; i <= 4; i++) {
      const y = padding.top + chartH - (i / 4) * chartH
      ctx.beginPath()
      ctx.moveTo(padding.left, y)
      ctx.lineTo(w - padding.right, y)
      ctx.stroke()
      ctx.fillText(Math.round((maxVal * i) / 4), padding.left - 6, y + 4)
    }

    const points = data.map((val, idx) => ({
      x: padding.left + (idx / (data.length - 1)) * chartW,
      y: padding.top + chartH - (val / maxVal) * chartH,
      val
    }))

    const grad = ctx.createLinearGradient(0, padding.top, 0, padding.top + chartH)
    grad.addColorStop(0, 'rgba(225, 29, 72, 0.35)')
    grad.addColorStop(1, 'rgba(225, 29, 72, 0.0)')

    ctx.beginPath()
    ctx.moveTo(points[0].x, padding.top + chartH)
    points.forEach((pt, i) => {
      if (i === 0) ctx.lineTo(pt.x, pt.y)
      else {
        const prev = points[i - 1]
        ctx.bezierCurveTo((prev.x + pt.x) / 2, prev.y, (prev.x + pt.x) / 2, pt.y, pt.x, pt.y)
      }
    })
    ctx.lineTo(points[points.length - 1].x, padding.top + chartH)
    ctx.closePath()
    ctx.fillStyle = grad
    ctx.fill()

    ctx.beginPath()
    points.forEach((pt, i) => {
      if (i === 0) ctx.moveTo(pt.x, pt.y)
      else {
        const prev = points[i - 1]
        ctx.bezierCurveTo((prev.x + pt.x) / 2, prev.y, (prev.x + pt.x) / 2, pt.y, pt.x, pt.y)
      }
    })
    ctx.strokeStyle = '#E11D48'
    ctx.lineWidth = 3
    ctx.stroke()

    ctx.textAlign = 'center'
    points.forEach((pt, i) => {
      ctx.beginPath()
      ctx.arc(pt.x, pt.y, 4, 0, Math.PI * 2)
      ctx.fillStyle = '#FFFFFF'
      ctx.fill()
      ctx.strokeStyle = '#E11D48'
      ctx.lineWidth = 2
      ctx.stroke()

      ctx.fillStyle = '#1e293b'
      ctx.font = 'bold 10px Poppins, sans-serif'
      ctx.fillText(pt.val, pt.x, pt.y - 8)

      ctx.fillStyle = '#64748b'
      ctx.font = '11px Poppins, sans-serif'
      if (chartData.labels[i]) {
        ctx.fillText(chartData.labels[i], pt.x, padding.top + chartH + 18)
      }
    })
  }
}

const totalApplicantsCount = computed(() => {
  let count = 0
  for (const j of (props.jobOffers || [])) {
    count += (j.applications_count || 0)
  }
  return count
})

// Profile Form
const profileForm = useForm({
  name: props.user.name || '',
  first_name: props.user.first_name || '',
  gender: props.user.gender || 'Homme',
  country_code: props.user.country_code || '+237',
  phone: props.user.phone || '',
  birth_date: props.user.birth_date ? props.user.birth_date.substring(0, 10) : '',
  marital_status: props.user.marital_status || 'Célibataire',
  bio: props.user.bio || '',
  avatar: null,
})

function updateProfileSubmit() {
  profileForm.post('/espace-utilisateur/profil', {
    preserveScroll: true,
  })
}

function markAsRead(id) {
  router.post(`/espace-utilisateur/messages/${id}/read`, {}, {
    preserveScroll: true,
  })
}

// --------------------------------------------------------------------------
// INTERACTIVE MESSAGING & DISCUSSION THREADS (Enhanced)
// --------------------------------------------------------------------------
const conversationSearch = ref('')
const conversationFilterTab = ref('all') // 'all', 'unread', 'admin', 'user'
const activeConversationKey = ref(null)
const mobileShowThread = ref(false)
const threadReplyText = ref('')
const threadReplySubject = ref('')
const replyAttachment = ref(null)
const replyFileInput = ref(null)
const isSendingReply = ref(false)

// New Message Modal State
const showNewMessageModal = ref(false)
const newMsgSearchQuery = ref('')
const newMsgSearchResults = ref([])
const showNewMsgDropdown = ref(false)
const newMsgSelectedRecipient = ref(null)
const newMsgIsSupport = ref(false)
const newMsgSubject = ref('')
const newMsgText = ref('')
const newMsgAttachment = ref(null)
const newMsgFileInput = ref(null)
const isSendingNewMsg = ref(false)
const newMsgAutocompleteContainer = ref(null)
let newMsgDebounceTimer = null

// Avatar fallback helpers
function hasCustomAvatar(avatar) {
  if (!avatar) return false
  if (typeof avatar !== 'string') return false
  const lower = avatar.toLowerCase()
  if (lower.includes('default-avatar') || lower.includes('default.jpg') || lower.includes('placeholder')) return false
  return true
}

function getInitials(name) {
  if (!name) return 'U'
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

function formatMessageWithLinks(text) {
  if (!text) return ''
  const escaped = String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')

  const urlRegex = /(https?:\/\/[^\s<]+|www\.[^\s<]+)/gi
  return escaped.replace(urlRegex, (url) => {
    const href = url.startsWith('http') ? url : `https://${url}`
    return `<a href="${href}" target="_blank" rel="noopener noreferrer" class="fw-bold text-decoration-underline text-break" style="color: inherit;">${url} <i class="bi bi-box-arrow-up-right extra-small"></i></a>`
  })
}

function getAvatarBg(name) {
  const colors = [
    '#00A859', '#10B981', '#059669', '#3B82F6', '#2563EB',
    '#6366F1', '#8B5CF6', '#EC4899', '#F59E0B', '#EF4444', '#14B8A6'
  ]
  if (!name) return colors[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash) % colors.length]
}

const conversations = computed(() => {
  const map = {}
  const authId = props.user?.id

  ;(props.messages || []).forEach(msg => {
    let partnerKey = null
    let partnerName = 'Membre Tech237'
    let partnerAvatar = null
    let partnerType = 'user'
    let rawPartnerId = null
    let canReply = true
    let guestSenderEmail = null

    if (msg.admin || msg.is_support) {
      partnerKey = 'support_tech237'
      partnerName = msg.admin ? (msg.admin.name || 'Support Tech237') : 'Support Tech237'
      partnerAvatar = msg.admin?.avatar || null
      partnerType = 'admin'
      canReply = true
    } else if (msg.sender && msg.sender.id !== authId) {
      partnerKey = 'user_' + msg.sender.id
      partnerName = msg.sender.name
      partnerAvatar = msg.sender.avatar || null
      rawPartnerId = msg.sender.id
      canReply = true
    } else if (msg.user && msg.user.id !== authId) {
      partnerKey = 'user_' + msg.user.id
      partnerName = msg.user.name
      partnerAvatar = msg.user.avatar || null
      rawPartnerId = msg.user.id
      canReply = true
    } else if (!msg.sender && !msg.admin) {
      // Guest or external contact without a registered account
      const emailMatch = (msg.message || '').match(/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/);
      guestSenderEmail = emailMatch ? emailMatch[1] : null;
      partnerKey = 'guest_' + msg.id;
      partnerName = msg.sender_name || (guestSenderEmail ? 'Visiteur (' + guestSenderEmail + ')' : 'Contact Externe');
      partnerType = 'guest';
      canReply = false;
    } else {
      partnerKey = 'support_general'
      partnerName = 'Équipe Tech237'
      partnerType = 'admin'
      canReply = true
    }

    if (!map[partnerKey]) {
      map[partnerKey] = {
        key: partnerKey,
        rawId: rawPartnerId,
        name: partnerName,
        avatar: partnerAvatar,
        type: partnerType,
        canReply: canReply,
        guestSenderEmail: guestSenderEmail,
        messages: [],
        unreadCount: 0,
        lastMessage: msg,
      }
    }

    map[partnerKey].messages.push(msg)
    if (!msg.is_read && msg.user_id === authId) {
      map[partnerKey].unreadCount++
    }
  })

  const list = Object.values(map)
  list.forEach(c => {
    c.messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
    c.lastMessage = c.messages[c.messages.length - 1]
  })

  // Sort by latest message date descending
  list.sort((a, b) => {
    const timeA = a.lastMessage?.created_at ? new Date(a.lastMessage.created_at).getTime() : 0
    const timeB = b.lastMessage?.created_at ? new Date(b.lastMessage.created_at).getTime() : 0
    return timeB - timeA
  })

  return list
})

const localUnreadMessagesCount = ref(props.unreadMessagesCount || 0)
watch(() => props.unreadMessagesCount, (val) => {
  localUnreadMessagesCount.value = val || 0
})

const unreadConversationsCount = computed(() => {
  return conversations.value.filter(c => c.unreadCount > 0).length
})

const filteredConversations = computed(() => {
  let list = conversations.value

  if (conversationFilterTab.value === 'unread') {
    list = list.filter(c => c.unreadCount > 0)
  } else if (conversationFilterTab.value === 'admin') {
    list = list.filter(c => c.type === 'admin')
  } else if (conversationFilterTab.value === 'user') {
    list = list.filter(c => c.type === 'user')
  }

  const q = conversationSearch.value.trim().toLowerCase()
  if (q) {
    list = list.filter(c => 
      c.name.toLowerCase().includes(q) ||
      (c.lastMessage?.subject && c.lastMessage.subject.toLowerCase().includes(q)) ||
      (c.lastMessage?.message && c.lastMessage.message.toLowerCase().includes(q))
    )
  }

  return list
})

const activeConversation = computed(() => {
  if (!activeConversationKey.value && conversations.value.length > 0) {
    return conversations.value[0]
  }
  return conversations.value.find(c => c.key === activeConversationKey.value) || conversations.value[0] || null
})

function selectConversation(c) {
  if (!c) return
  activeConversationKey.value = c.key
  mobileShowThread.value = true
  const lastSub = c.lastMessage?.subject || 'Message Tech237'
  threadReplySubject.value = lastSub.startsWith('Re:') ? lastSub : ('Re: ' + lastSub)
  
  // If this conversation has unread messages, mark them as read immediately both locally and in backend
  const unreadInThread = (c.messages || []).filter(m => !m.is_read && m.user_id === props.user?.id).length
  if (unreadInThread > 0 || c.unreadCount > 0) {
    c.messages.forEach(m => {
      if (m.user_id === props.user?.id) {
        m.is_read = true
      }
    })
    c.unreadCount = 0
    localUnreadMessagesCount.value = Math.max(0, localUnreadMessagesCount.value - unreadInThread)

    // Mark thread read on server
    fetch('/espace-utilisateur/messages/thread-read', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        is_support: c.key === 'support_tech237',
        partner_id: c.rawId,
      })
    }).then(r => r.json()).then(data => {
      if (data && typeof data.unreadMessagesCount === 'number') {
        localUnreadMessagesCount.value = data.unreadMessagesCount
      }
    }).catch(err => {
      console.error('Error marking thread read:', err)
    })
  }

  scrollToBottom()
}

function scrollToBottom() {
  nextTick(() => {
    const box = document.getElementById('chatStreamBox')
    if (box) {
      box.scrollTop = box.scrollHeight
    }
  })
}

// Reply Attachment Handlers
function triggerReplyFileInput() {
  if (replyFileInput.value) replyFileInput.value.click()
}

function onReplyFileSelect(e) {
  if (e.target.files && e.target.files.length > 0) {
    replyAttachment.value = e.target.files[0]
  }
}

function cancelReplyAttachment() {
  replyAttachment.value = null
  if (replyFileInput.value) replyFileInput.value.value = ''
}

function sendThreadReply() {
  if (!threadReplyText.value.trim() || !activeConversation.value) return
  isSendingReply.value = true

  const formData = new FormData()
  if (activeConversation.value.rawId) {
    formData.append('recipient_id', activeConversation.value.rawId)
  }
  if (activeConversation.value.type === 'admin' || !activeConversation.value.rawId || activeConversation.value.key === 'support_tech237') {
    formData.append('is_support', '1')
  }
  formData.append('subject', threadReplySubject.value || 'Re: Message Tech237')
  formData.append('message', threadReplyText.value)
  if (replyAttachment.value) {
    formData.append('attachment', replyAttachment.value)
  }

  router.post('/espace-utilisateur/messages/envoyer', formData, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      threadReplyText.value = ''
      cancelReplyAttachment()
      isSendingReply.value = false
      scrollToBottom()
    },
    onError: () => {
      isSendingReply.value = false
    }
  })
}

function insertThreadFormat(before, after) {
  const el = document.getElementById('threadReplyInput')
  if (!el) return
  const start = el.selectionStart
  const end = el.selectionEnd
  const selected = threadReplyText.value.substring(start, end)
  threadReplyText.value = threadReplyText.value.substring(0, start) + before + selected + after + threadReplyText.value.substring(end)
}

function toggleReaction(msgId, emoji) {
  router.post(`/espace-utilisateur/messages/${msgId}/reaction`, { emoji }, {
    preserveScroll: true,
  })
}

function confirmDeleteMessage(msgId) {
  if (confirm('Voulez-vous vraiment supprimer ce message ?')) {
    router.delete(`/espace-utilisateur/messages/${msgId}`, {
      preserveScroll: true,
    })
  }
}

// New Message Modal Handlers
function openNewMessageModal() {
  newMsgSearchQuery.value = ''
  newMsgSearchResults.value = []
  newMsgSelectedRecipient.value = null
  newMsgIsSupport.value = false
  newMsgSubject.value = ''
  newMsgText.value = ''
  newMsgAttachment.value = null
  showNewMessageModal.value = true
}

function handleNewMsgSearch() {
  const q = newMsgSearchQuery.value.trim()
  if (newMsgDebounceTimer) clearTimeout(newMsgDebounceTimer)
  if (q.length < 2) {
    newMsgSearchResults.value = []
    showNewMsgDropdown.value = false
    return
  }
  newMsgDebounceTimer = setTimeout(async () => {
    try {
      const res = await fetch(`/api/users/search?q=${encodeURIComponent(q)}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      })
      if (res.ok) {
        newMsgSearchResults.value = await res.json()
        showNewMsgDropdown.value = true
      }
    } catch (e) {}
  }, 250)
}

function handleNewMsgFocus() {
  if (newMsgSearchResults.value.length > 0) {
    showNewMsgDropdown.value = true
  }
}

function selectNewMsgRecipient(u) {
  newMsgSelectedRecipient.value = u
  newMsgIsSupport.value = false
  showNewMsgDropdown.value = false
}

function selectSupportRecipient() {
  newMsgSelectedRecipient.value = {
    id: null,
    name: 'Support Tech237 (Admin)',
    email: 'admin@tech237.cm',
    avatar: null
  }
  newMsgIsSupport.value = true
  showNewMsgDropdown.value = false
}

function triggerNewMsgFileInput() {
  if (newMsgFileInput.value) newMsgFileInput.value.click()
}

function onNewMsgFileSelect(e) {
  if (e.target.files && e.target.files.length > 0) {
    newMsgAttachment.value = e.target.files[0]
  }
}

function submitNewMessage() {
  if (!newMsgSubject.value.trim() || !newMsgText.value.trim()) return
  isSendingNewMsg.value = true

  const formData = new FormData()
  if (newMsgSelectedRecipient.value?.id) {
    formData.append('recipient_id', newMsgSelectedRecipient.value.id)
  }
  if (newMsgIsSupport.value || !newMsgSelectedRecipient.value?.id) {
    formData.append('is_support', '1')
  }
  formData.append('subject', newMsgSubject.value)
  formData.append('message', newMsgText.value)
  if (newMsgAttachment.value) {
    formData.append('attachment', newMsgAttachment.value)
  }

  router.post('/espace-utilisateur/messages/envoyer', formData, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      showNewMessageModal.value = false
      isSendingNewMsg.value = false
      if (newMsgIsSupport.value || !newMsgSelectedRecipient.value?.id) {
        activeConversationKey.value = 'support_tech237'
      } else if (newMsgSelectedRecipient.value?.id) {
        activeConversationKey.value = 'user_' + newMsgSelectedRecipient.value.id
      }
      mobileShowThread.value = true
      scrollToBottom()
    },
    onError: () => {
      isSendingNewMsg.value = false
    }
  })
}

// Background soft polling when viewing messages section
let messagesPollingInterval = null

onMounted(() => {
  if (props.activeSection === 'messages') {
    startMessagesPolling()
    scrollToBottom()
  }
})

watch(() => currentSection.value, (newSec) => {
  if (newSec === 'messages') {
    startMessagesPolling()
    if (activeConversation.value) {
      selectConversation(activeConversation.value)
    }
    scrollToBottom()
  } else {
    stopMessagesPolling()
  }
}, { immediate: true })

function startMessagesPolling() {
  stopMessagesPolling()
  messagesPollingInterval = setInterval(() => {
    if (currentSection.value === 'messages' && !isSendingReply.value && !isSendingNewMsg.value) {
      router.reload({
        only: ['messages', 'unreadMessagesCount'],
        preserveScroll: true,
      })
    }
  }, 12000)
}

function stopMessagesPolling() {
  if (messagesPollingInterval) {
    clearInterval(messagesPollingInterval)
    messagesPollingInterval = null
  }
}

onBeforeUnmount(() => {
  stopMessagesPolling()
  if (newMsgDebounceTimer) clearTimeout(newMsgDebounceTimer)
})

function deleteDoc(id) {
  if (confirm('Confirmer la suppression de ce document ?')) {
    router.delete(`/espace-utilisateur/documents/${id}`, {
      preserveScroll: true,
    })
  }
}

// --------------------------------------------------------------------------
// MON CV & PORTFOLIO TECH CUSTOMIZER
// --------------------------------------------------------------------------
const portfolioSubTab = ref('layout')
const isSavingPortfolio = ref(false)
const portfolioToastMsg = ref('')
const isPortfolioCopied = ref(false)
const newSkillName = ref('')
const newSkillCategory = ref('Backend')

const defaultBlocksList = [
  { id: 'hero', name: 'En-tête & Présentation', enabled: true, icon: 'bi-person-badge', desc: 'Photo, nom, titre pro, localisation et disponibilité' },
  { id: 'about', name: 'À propos & Parcours', enabled: true, icon: 'bi-person-lines-fill', desc: 'Présentation détaillée et biographie de profil' },
  { id: 'skills', name: 'Compétences & Stack technique', enabled: true, icon: 'bi-cpu', desc: 'Technologies, langages et outils maîtrisés' },
  { id: 'projects', name: 'Projets Tech en vedette', enabled: true, icon: 'bi-rocket-takeoff', desc: 'Projets livrés et publiés sur Tech237' },
  { id: 'pocs', name: 'Mini-Actus & POCs Tech237', enabled: true, icon: 'bi-lightning-charge', desc: 'Veille technologique et articles courts partagés' },
  { id: 'experiences', name: 'Expériences professionnelles', enabled: true, icon: 'bi-briefcase', desc: 'Postes occupés, missions et réalisations en entreprise' },
  { id: 'educations', name: 'Formations & Diplômes', enabled: true, icon: 'bi-mortarboard', desc: 'Études supérieures, certifications et diplômes' },
  { id: 'badges', name: 'Trophées & Badges Tech237', enabled: true, icon: 'bi-trophy', desc: 'Niveau XP et distinctions officielles gagnées sur Tech237' },
  { id: 'custom', name: 'Sections personnalisées libres', enabled: true, icon: 'bi-stars', desc: 'Blocs libres : services, tarifs, recommandations, etc.' },
  { id: 'contact', name: 'Coordonnées & Réseaux', enabled: true, icon: 'bi-envelope', desc: 'Liens vers GitHub, LinkedIn, WhatsApp et email' },
]

const initialUserLayout = props.user.portfolio_layout || {}

const portfolioForm = ref({
  username: props.user.username || '',
  headline: props.user.headline || '',
  location: props.user.location || '',
  availability: props.user.availability || 'open',
  bio: props.user.bio || '',
  portfolio_published: props.user.portfolio_published !== false,
  skills: Array.isArray(props.user.skills) ? [...props.user.skills] : [],
  social_links: props.user.social_links || { github: '', linkedin: '', twitter: '', website: '', whatsapp: '' },
  experiences: Array.isArray(props.user.experiences) ? JSON.parse(JSON.stringify(props.user.experiences)) : [],
  educations: Array.isArray(props.user.educations) ? JSON.parse(JSON.stringify(props.user.educations)) : [],
  custom_sections: Array.isArray(props.user.custom_sections) ? JSON.parse(JSON.stringify(props.user.custom_sections)) : [],
  portfolio_layout: {
    theme: initialUserLayout.theme || 'emerald',
    card_style: initialUserLayout.card_style || 'rounded',
    accent_color: initialUserLayout.accent_color || '#00A859',
    hero_style: initialUserLayout.hero_style || 'centered',
    projects_style: initialUserLayout.projects_style || 'cards',
    skills_style: initialUserLayout.skills_style || 'chips',
    blocks: Array.isArray(initialUserLayout.blocks) && initialUserLayout.blocks.length > 0 
      ? JSON.parse(JSON.stringify(initialUserLayout.blocks)) 
      : JSON.parse(JSON.stringify(defaultBlocksList)),
  }
})

// Block reordering & toggling
function moveBlockUp(idx) {
  if (idx > 0) {
    const list = portfolioForm.value.portfolio_layout.blocks
    const item = list.splice(idx, 1)[0]
    list.splice(idx - 1, 0, item)
  }
}

function moveBlockDown(idx) {
  const list = portfolioForm.value.portfolio_layout.blocks
  if (idx < list.length - 1) {
    const item = list.splice(idx, 1)[0]
    list.splice(idx + 1, 0, item)
  }
}

function toggleBlock(idx) {
  const b = portfolioForm.value.portfolio_layout.blocks[idx]
  b.enabled = !b.enabled
}

function resetBlocksOrder() {
  portfolioForm.value.portfolio_layout.blocks = JSON.parse(JSON.stringify(defaultBlocksList))
}

// Skills handling
function addSkill() {
  if (!newSkillName.value.trim()) return
  const s = newSkillName.value.trim()
  const exists = portfolioForm.value.skills.some(x => (typeof x === 'object' ? x.name : x).toLowerCase() === s.toLowerCase())
  if (!exists) {
    portfolioForm.value.skills.push({
      name: s,
      category: newSkillCategory.value || 'Général',
      level: 'Avancé',
    })
  }
  newSkillName.value = ''
}

function addQuickSkill(name, cat) {
  const exists = portfolioForm.value.skills.some(x => (typeof x === 'object' ? x.name : x).toLowerCase() === name.toLowerCase())
  if (!exists) {
    portfolioForm.value.skills.push({
      name,
      category: cat || 'Tech',
      level: 'Avancé',
    })
  }
}

function removeSkill(idx) {
  portfolioForm.value.skills.splice(idx, 1)
}

// Experiences handling
function addExperience() {
  portfolioForm.value.experiences.push({
    role: '',
    company: '',
    location: '',
    period: '',
    description: '',
  })
}

function removeExperience(idx) {
  portfolioForm.value.experiences.splice(idx, 1)
}

// Educations handling
function addEducation() {
  portfolioForm.value.educations.push({
    degree: '',
    institution: '',
    year: '',
    description: '',
  })
}

function removeEducation(idx) {
  portfolioForm.value.educations.splice(idx, 1)
}

// Custom Sections handling
function addCustomSection() {
  portfolioForm.value.custom_sections.push({
    title: '',
    icon: 'bi-stars',
    content: '',
  })
}

function removeCustomSection(idx) {
  portfolioForm.value.custom_sections.splice(idx, 1)
}

// URLs and Sharing
const portfolioShareUrl = computed(() => {
  const pseudo = portfolioForm.value.username || props.user.username || 'profil'
  if (typeof window !== 'undefined') {
    return `${window.location.origin}/@${pseudo}`
  }
  return `https://tech237.net/@${pseudo}`
})

const whatsappPortfolioShareUrl = computed(() => {
  const text = encodeURIComponent(`Découvrez mon portfolio tech et mes projets sur Tech237 : ${portfolioShareUrl.value}`)
  return `https://api.whatsapp.com/send?text=${text}`
})

const linkedinPortfolioShareUrl = computed(() => {
  return `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(portfolioShareUrl.value)}`
})

const twitterPortfolioShareUrl = computed(() => {
  const text = encodeURIComponent(`Mon portfolio tech & projets sur @Tech237 : ${portfolioShareUrl.value}`)
  return `https://twitter.com/intent/tweet?text=${text}`
})

function copyPortfolioUrl() {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(portfolioShareUrl.value)
    isPortfolioCopied.value = true
    setTimeout(() => { isPortfolioCopied.value = false }, 2500)
  }
}

function openPortfolioPublic() {
  window.open(portfolioShareUrl.value, '_blank')
}

// Save Portfolio to API
function savePortfolioSettings() {
  isSavingPortfolio.value = true
  fetch('/espace-utilisateur/portfolio/save', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify(portfolioForm.value)
  }).then(async r => {
    const data = await r.json().catch(() => ({}))
    if (!r.ok) {
      throw new Error(data.message || 'Erreur lors de l\'enregistrement du portfolio.')
    }
    return data
  }).then(data => {
    if (data && data.success) {
      portfolioToastMsg.value = data.message || 'Portfolio enregistré avec succès !'
      if (data.user) {
        Object.assign(props.user, data.user)
      }
      setTimeout(() => { portfolioToastMsg.value = '' }, 4000)
    }
  }).catch(err => {
    alert(err.message || 'Une erreur est survenue lors de l\'enregistrement.')
  }).finally(() => {
    isSavingPortfolio.value = false
  })
}
</script>

<style scoped>
.shadow-xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.cursor-pointer {
  cursor: pointer;
}
.hover-opacity-100:hover {
  opacity: 1 !important;
}
.extra-small {
  font-size: 11px;
}
</style>
