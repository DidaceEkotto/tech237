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
              <div class="small text-white-50 text-truncate">{{ user.email }}</div>
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
                <i class="bi bi-kanban me-2 fs-5"></i>
                <span>Mes Projets</span>
                <span class="badge rounded-pill ms-auto" :class="currentSection === 'projects' ? 'bg-white text-danger' : 'bg-secondary-subtle text-secondary'">
                  {{ projects.length }}
                </span>
              </button>

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
                <span v-if="unreadMessagesCount > 0" class="badge bg-warning text-dark rounded-pill ms-auto">
                  {{ unreadMessagesCount }}
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
                    <span class="badge bg-warning-subtle text-warning small">{{ unreadMessagesCount }} non lu(s)</span>
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
                  <h4 class="fw-bold mb-1 text-dark">Mes Projets Tech & Produits</h4>
                  <p class="text-muted small mb-0">Applications web, mobiles, logiciels desktop et jeux vidéo.</p>
                </div>
                <Link href="/espace-utilisateur/projets/create" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                  <i class="bi bi-plus-lg me-1"></i> Créer un projet
                </Link>
              </div>

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

                    <p class="small text-muted mb-3 line-clamp-2">{{ proj.description }}</p>

                    <div class="d-flex align-items-center gap-3 small text-muted mb-3">
                      <span><i class="bi bi-eye me-1"></i>{{ proj.views_count || 0 }} vues</span>
                      <span><i class="bi bi-clock-history me-1"></i><strong>{{ (proj.timelines || []).length }}</strong> étape(s)</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto gap-2 flex-wrap">
                      <div class="d-flex gap-1 flex-wrap">
                        <Link :href="'/espace-utilisateur/projets/' + proj.id + '/etapes'" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                          <i class="bi bi-journal-check me-1"></i> Devlog
                        </Link>
                        <Link :href="'/espace-utilisateur/projets/' + proj.id + '/galerie'" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Galerie photos">
                          <i class="bi bi-images me-1"></i> Galerie
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
                <h6 class="fw-bold text-dark">Aucun projet tech publié</h6>
                <p class="small mb-3">Publiez votre application web, mobile ou votre jeu vidéo.</p>
                <Link href="/espace-utilisateur/projets/create" class="btn btn-danger btn-sm rounded-pill px-4">
                  Créer mon premier projet
                </Link>
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
              <div class="p-4 border-bottom bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  <h4 class="fw-bold mb-1 text-dark d-flex align-items-center">
                    <i class="bi bi-chat-left-text-fill text-success me-2"></i>Messagerie Privée &amp; Échanges
                  </h4>
                  <p class="text-muted small mb-0">Discutez en continu avec les fondateurs, recruteurs et l'équipe Tech237.</p>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                  {{ conversations.length }} conversation(s)
                </span>
              </div>

              <div v-if="conversations.length" class="row g-0">
                <!-- Left: Conversations List -->
                <div class="col-md-5 col-lg-4 border-end bg-light" style="max-height: 650px; overflow-y: auto;">
                  <div class="list-group list-group-flush">
                    <button 
                      v-for="c in conversations" 
                      :key="c.key"
                      type="button"
                      @click="selectConversation(c)"
                      class="list-group-item list-group-item-action p-3 border-bottom d-flex align-items-center gap-3 transition-all"
                      :class="activeConversation?.key === c.key ? 'bg-white border-start border-4 border-success shadow-sm' : ''"
                    >
                      <div class="position-relative flex-shrink-0">
                        <img 
                          :src="'/' + c.avatar" 
                          class="rounded-circle border" 
                          style="width: 46px; height: 46px; object-fit: cover;"
                          alt="Avatar"
                        >
                        <span v-if="c.type === 'admin'" class="position-absolute bottom-0 end-0 badge rounded-pill bg-danger p-1" style="font-size: 8px;">
                          <i class="bi bi-shield-fill"></i>
                        </span>
                      </div>

                      <div class="flex-grow-1 text-truncate text-start">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <strong class="text-dark small text-truncate">{{ c.name }}</strong>
                          <small class="text-muted extra-small ms-1">{{ c.lastMessage?.created_at ? c.lastMessage.created_at.substring(0, 10) : '' }}</small>
                        </div>
                        <div class="text-muted extra-small text-truncate">
                          {{ c.lastMessage?.subject || c.lastMessage?.message }}
                        </div>
                      </div>

                      <span v-if="c.unreadCount > 0" class="badge bg-warning text-dark rounded-pill">
                        {{ c.unreadCount }}
                      </span>
                    </button>
                  </div>
                </div>

                <!-- Right: Active Chat Thread & WYSIWYG Composer -->
                <div class="col-md-7 col-lg-8 d-flex flex-column bg-white" style="height: 650px;">
                  
                  <div v-if="activeConversation" class="d-flex flex-column h-100">
                    <!-- Partner Top Header -->
                    <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center gap-2">
                        <img :src="'/' + activeConversation.avatar" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;">
                        <div>
                          <div class="fw-bold text-dark small mb-0">{{ activeConversation.name }}</div>
                          <span class="badge extra-small" :class="activeConversation.type === 'admin' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'">
                            {{ activeConversation.type === 'admin' ? 'Administrateur Tech237' : 'Membre Communauté' }}
                          </span>
                        </div>
                      </div>
                      <small class="text-muted extra-small">
                        {{ activeConversation.messages.length }} message(s) échangé(s)
                      </small>
                    </div>

                    <!-- Message Bubble Stream -->
                    <div class="p-3 flex-grow-1 overflow-y-auto d-flex flex-column gap-3 bg-white" id="chatStreamBox">
                      <div 
                        v-for="m in activeConversation.messages" 
                        :key="m.id"
                        class="d-flex"
                        :class="m.sender_id === user.id ? 'justify-content-end' : 'justify-content-start'"
                      >
                        <div 
                          class="rounded-4 p-3 shadow-sm"
                          :style="{
                            maxWidth: '78%',
                            backgroundColor: m.sender_id === user.id ? 'var(--tech-green)' : '#F3F4F6',
                            color: m.sender_id === user.id ? '#FFFFFF' : '#1F2937'
                          }"
                        >
                          <div class="d-flex align-items-center justify-content-between gap-2 mb-1 border-bottom pb-1" :style="{ borderColor: m.sender_id === user.id ? 'rgba(255,255,255,0.2)' : '#E5E7EB' }">
                            <strong class="extra-small">{{ m.sender_id === user.id ? 'Moi' : activeConversation.name }}</strong>
                            <small class="extra-small opacity-75">{{ m.created_at ? new Date(m.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : '' }}</small>
                          </div>
                          
                          <div v-if="m.subject" class="fw-bold extra-small mb-1" :style="{ color: m.sender_id === user.id ? '#D1FAE5' : '#0B2559' }">
                            {{ m.subject }}
                          </div>

                          <div class="small leading-relaxed" style="white-space: pre-line;">{{ m.message || m.content }}</div>
                        </div>
                      </div>
                    </div>

                    <!-- Thread Reply Composer (WYSIWYG tools) -->
                    <div class="p-3 border-top bg-light">
                      <form @submit.prevent="sendThreadReply">
                        <div class="input-group input-group-sm mb-2">
                          <span class="input-group-text bg-white border-0 fw-bold extra-small text-muted">Sujet :</span>
                          <input type="text" class="form-control form-control-sm border-0" v-model="threadReplySubject" placeholder="Sujet de votre réponse..." required>
                        </div>

                        <!-- WYSIWYG quick toolbar -->
                        <div class="d-flex align-items-center gap-1 mb-2">
                          <button type="button" @click="insertThreadFormat('**', '**')" class="btn btn-sm btn-white border extra-small px-2 py-0" title="Gras"><strong>B</strong></button>
                          <button type="button" @click="insertThreadFormat('*', '*')" class="btn btn-sm btn-white border extra-small px-2 py-0" title="Italique"><em>I</em></button>
                          <button type="button" @click="insertThreadFormat('`', '`')" class="btn btn-sm btn-white border extra-small px-2 py-0" title="Code"><code>&lt;&gt;</code></button>
                          <button type="button" @click="insertThreadFormat('> ', '')" class="btn btn-sm btn-white border extra-small px-2 py-0" title="Citation"><i class="bi bi-quote"></i></button>
                        </div>

                        <div class="mb-2">
                          <textarea 
                            id="threadReplyInput"
                            v-model="threadReplyText"
                            class="form-control" 
                            rows="3" 
                            placeholder="Rédigez votre réponse ici. Vous pouvez échanger indéfiniment..."
                            required
                          ></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                          <small class="text-muted extra-small">
                            <i class="bi bi-arrow-repeat me-1"></i> Réponse instantanée enregistrée dans le fil
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
                <p class="small text-muted mb-0">Les messages reçus ou envoyés aux startups et membres apparaîtront ici.</p>
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
          @click="selectMobileSection('profile')" 
          class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold d-flex align-items-center mb-1"
          :class="currentSection === 'profile' ? 'bg-danger text-white' : 'text-dark'"
        >
          <i class="bi bi-person-gear me-2"></i> Mon Profil
        </button>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  user: { type: Object, required: true },
  startups: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  jobOffers: { type: Array, default: () => [] },
  conferences: { type: Array, default: () => [] },
  documents: { type: Array, default: () => [] },
  messages: { type: Array, default: () => [] },
  unreadMessagesCount: { type: Number, default: 0 },
  comments: { type: Array, default: () => [] },
  unreadCommentsCount: { type: Number, default: 0 },
  isBanned: { type: Boolean, default: false },
  banReason: { type: String, default: null },
  activeSection: { type: String, default: 'dashboard' },
  stats: { type: Object, default: () => ({}) },
})

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

// Interactive Messaging Threads (Continuous two-way messaging)
const activeConversationKey = ref(null)
const threadReplyText = ref('')
const threadReplySubject = ref('')
const isSendingReply = ref(false)

const conversations = computed(() => {
  const map = {}
  const authId = props.user?.id

  ;(props.messages || []).forEach(msg => {
    let partnerKey = null
    let partnerName = 'Membre Tech237'
    let partnerAvatar = 'assets/images/default-avatar.png'
    let partnerType = 'user'
    let rawPartnerId = null

    if (msg.admin) {
      partnerKey = 'admin_' + msg.admin.id
      partnerName = msg.admin.name || 'Support Tech237'
      partnerAvatar = msg.admin.avatar || 'assets/images/author-default.png'
      partnerType = 'admin'
    } else if (msg.sender && msg.sender.id !== authId) {
      partnerKey = 'user_' + msg.sender.id
      partnerName = msg.sender.name
      partnerAvatar = msg.sender.avatar || 'assets/images/default-avatar.png'
      rawPartnerId = msg.sender.id
    } else if (msg.user && msg.user.id !== authId) {
      partnerKey = 'user_' + msg.user.id
      partnerName = msg.user.name
      partnerAvatar = msg.user.avatar || 'assets/images/default-avatar.png'
      rawPartnerId = msg.user.id
    } else {
      partnerKey = 'support_general'
      partnerName = 'Équipe Tech237'
      partnerType = 'admin'
    }

    if (!map[partnerKey]) {
      map[partnerKey] = {
        key: partnerKey,
        rawId: rawPartnerId,
        name: partnerName,
        avatar: partnerAvatar,
        type: partnerType,
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

  return list
})

const activeConversation = computed(() => {
  if (!activeConversationKey.value && conversations.value.length > 0) {
    return conversations.value[0]
  }
  return conversations.value.find(c => c.key === activeConversationKey.value) || conversations.value[0] || null
})

function selectConversation(c) {
  activeConversationKey.value = c.key
  const lastSub = c.lastMessage?.subject || 'Message Tech237'
  threadReplySubject.value = lastSub.startsWith('Re:') ? lastSub : ('Re: ' + lastSub)
  
  c.messages.forEach(m => {
    if (!m.is_read && m.user_id === props.user?.id) {
      markAsRead(m.id)
    }
  })
}

function sendThreadReply() {
  if (!threadReplyText.value.trim() || !activeConversation.value) return
  isSendingReply.value = true

  router.post('/espace-utilisateur/messages/envoyer', {
    recipient_id: activeConversation.value.rawId || undefined,
    subject: threadReplySubject.value || 'Re: Message Tech237',
    message: threadReplyText.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      threadReplyText.value = ''
      isSendingReply.value = false
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

function deleteDoc(id) {
  if (confirm('Confirmer la suppression de ce document ?')) {
    router.delete(`/espace-utilisateur/documents/${id}`, {
      preserveScroll: true,
    })
  }
}
</script>
