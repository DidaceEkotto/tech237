<template>
  <div 
    class="melty-wrapper min-vh-100 d-flex flex-column"
    :style="backgroundAdStyle"
  >
    <!-- Background Ad Clickable Header/Gutters if active -->
    <a 
      v-if="backgroundAd && backgroundAd.link_url" 
      :href="backgroundAd.link_url" 
      target="_blank" 
      class="background-ad-gutter-link"
      title="Partenaire Melty"
    ></a>

    <!-- 1. Top Bar: Socials, Newsletter button, Search, Language, Single Connexion / Profile -->
    <div class="top-nav-bar py-2 border-bottom bg-white" style="position: relative; z-index: 1040;">
      <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
        
        <!-- Left: Hamburger (mobile), Melty Logo & Socials -->
        <div class="d-flex align-items-center gap-3">
          <!-- Mobile Hamburger Button -->
          <button 
            @click="isMobileMenuOpen = true" 
            class="btn btn-sm btn-light border d-lg-none d-flex align-items-center justify-content-center p-2 rounded-3" 
            aria-label="Ouvrir le menu"
          >
            <i class="bi bi-list fs-4 text-dark"></i>
          </button>

          <Link href="/" class="navbar-brand me-3 d-flex align-items-center gap-2">
            <img :src="'/' + (siteSettings.logo || 'assets/images/logo.png')" alt="Tech237 Logo" height="46" style="max-width: 220px; object-fit: contain;">
          </Link>

          <!-- Social Icons -->
          <div class="d-none d-md-flex align-items-center gap-2 text-dark">
            <a :href="siteSettings.social_facebook || '#'" target="_blank" class="social-top-link" title="Facebook"><i class="bi bi-facebook"></i></a>
            <a :href="siteSettings.social_twitter || '#'" target="_blank" class="social-top-link" title="X (Twitter)"><i class="bi bi-twitter-x"></i></a>
            <a :href="siteSettings.social_instagram || '#'" target="_blank" class="social-top-link" title="Instagram"><i class="bi bi-instagram"></i></a>
            <a :href="siteSettings.social_tiktok || '#'" target="_blank" class="social-top-link" title="TikTok"><i class="bi bi-tiktok"></i></a>
            <a :href="siteSettings.social_youtube || '#'" target="_blank" class="social-top-link" title="YouTube"><i class="bi bi-youtube"></i></a>
          </div>
        </div>

        <!-- Right: Newsletter pill, Search Bar, Locale, Single Connexion / Profile Dropdown -->
        <div class="d-flex align-items-center gap-2">
          
          <!-- Newsletter Trigger Pill -->
          <a href="#newsletterSection" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold d-none d-sm-inline-flex align-items-center gap-1">
            <i class="bi bi-send text-success"></i> Newsletter
          </a>

          <!-- Search Bar Trigger -->
          <button @click="openSearchModal" class="btn btn-sm btn-light border rounded-pill px-3 py-1 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-search"></i>
            <span class="small d-none d-md-inline">{{ locale === 'en' ? 'Search...' : 'Rechercher...' }}</span>
          </button>

          <!-- Language Switcher (FR / EN) -->
          <div class="btn-group btn-group-sm rounded-pill overflow-hidden border">
            <button 
              @click="switchLocale('fr')" 
              class="btn btn-sm py-1 px-2 fw-bold"
              :class="locale === 'fr' ? 'btn-dark text-white' : 'btn-light text-muted'"
            >
              FR
            </button>
            <button 
              @click="switchLocale('en')" 
              class="btn btn-sm py-1 px-2 fw-bold"
              :class="locale === 'en' ? 'btn-dark text-white' : 'btn-light text-muted'"
            >
              EN
            </button>
          </div>

          <!-- Single User Auth Button / Profile Dropdown -->
          <div v-if="authUser" class="dropdown position-relative" style="z-index: 1050;">
            <button class="btn btn-sm btn-dark rounded-pill dropdown-toggle d-flex align-items-center gap-2 px-3 py-1" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle text-danger"></i>
              <span class="d-none d-sm-inline small fw-bold">{{ authUser.first_name || authUser.name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="position: absolute; z-index: 1060;">
              <li class="px-3 py-1 small text-muted">Connecté en tant que <strong>{{ authUser.email }}</strong></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <Link href="/espace-utilisateur" class="dropdown-item py-2">
                  <i class="bi bi-speedometer2 me-2 text-danger"></i> Mon Tableau de bord
                </Link>
              </li>
              <li>
                <Link href="/espace-utilisateur?section=startups" class="dropdown-item py-2">
                  <i class="bi bi-briefcase me-2 text-primary"></i> Mes Startups
                </Link>
              </li>
              <li>
                <Link href="/espace-utilisateur?section=projects" class="dropdown-item py-2">
                  <i class="bi bi-kanban me-2 text-success"></i> Mes Projets
                </Link>
              </li>
              <li>
                <Link href="/espace-utilisateur?section=messages" class="dropdown-item py-2">
                  <i class="bi bi-envelope me-2 text-warning"></i> Messages
                </Link>
              </li>
              <li>
                <Link href="/espace-utilisateur?section=profile" class="dropdown-item py-2">
                  <i class="bi bi-person me-2 text-secondary"></i> Mon Profil
                </Link>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <button type="button" @click="showLogoutConfirmModal = true" class="dropdown-item text-danger py-2">
                  <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                </button>
              </li>
            </ul>
          </div>
          <div v-else>
            <!-- Single Connexion Link as requested -->
            <Link href="/login" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold">
              <i class="bi bi-person-fill me-1"></i> Connexion
            </Link>
          </div>

          <!-- Bouton Nous Soutenir placé immédiatement après le lien de connexion / profil -->
          <Link href="/nous-soutenir" class="btn btn-sm rounded-pill px-3 fw-bold text-white shadow-sm d-flex align-items-center gap-1" style="background-color: var(--tech-green);">
            <i class="bi bi-heart-fill text-white"></i>
            <span class="d-none d-sm-inline">Nous Soutenir</span>
            <span class="d-inline d-sm-none">Soutenir</span>
          </Link>

        </div>
      </div>
    </div>

    <!-- Header Top Ad Banner (Leaderboard) -->
    <div v-if="headerTopAd" class="header-top-banner bg-light border-bottom text-center py-2">
      <div class="container">
        <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 9px; letter-spacing: 1px;">Sponsor &bull; Annonce Partenaire</div>
        <a v-if="headerTopAd.image" :href="headerTopAd.link_url || '#'" target="_blank" rel="noopener noreferrer">
          <img :src="'/' + headerTopAd.image" :alt="headerTopAd.title || 'Publicité'" class="img-fluid rounded-2 shadow-sm" style="max-height: 90px; width: auto; object-fit: contain;">
        </a>
        <div v-else-if="headerTopAd.custom_code" v-html="headerTopAd.custom_code"></div>
      </div>
    </div>

    <!-- 2. Main Desktop Navigation Bar with Dynamic Rubriques & Dropdowns -->
    <header class="melty-main-nav py-2 sticky-top bg-white border-bottom shadow-sm d-none d-lg-block">
      <div class="container d-flex align-items-center justify-content-between">
        
        <nav class="d-flex align-items-center gap-3 flex-wrap">
          <Link href="/" class="nav-item-link" :class="{ 'active': $page.url === '/' }">
            {{ locale === 'en' ? 'Home' : 'Accueil' }}
          </Link>
          
          <!-- DYNAMIC RUBRIQUES & CATEGORIES DROPDOWNS -->
          <template v-for="rub in navRubriques" :key="rub.id">
            <!-- If Rubrique has sub-categories, render dropdown -->
            <div v-if="rub.categories && rub.categories.length > 0" class="dropdown d-inline-block">
              <a 
                class="nav-item-link dropdown-toggle" 
                href="#" 
                role="button" 
                data-bs-toggle="dropdown"
                :class="{ 'active': $page.url.startsWith('/rubrique/' + rub.slug) || rub.categories.some(c => $page.url.startsWith('/category/' + c.slug)) }"
              >
                <i v-if="rub.icon" :class="'bi ' + rub.icon + ' me-1 text-danger'"></i>
                {{ locale === 'en' ? (rub.name_en || rub.name_fr) : rub.name_fr }}
              </a>
              <ul class="dropdown-menu shadow border-0 mt-2">
                <li>
                  <Link :href="'/rubrique/' + rub.slug" class="dropdown-item py-2 fw-bold text-danger">
                    <i class="bi bi-collection-play me-2"></i> {{ locale === 'en' ? 'All in ' + (rub.name_en || rub.name_fr) : 'Tout dans ' + rub.name_fr }}
                  </Link>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li v-for="cat in rub.categories" :key="cat.id">
                  <Link :href="'/category/' + cat.slug" class="dropdown-item py-2">
                    <i :class="'bi ' + (cat.icon || 'bi-tag') + ' me-2'" :style="{ color: cat.color || '#FF004B' }"></i>
                    {{ locale === 'en' ? (cat.name_en || cat.name_fr) : cat.name_fr }}
                  </Link>
                </li>
              </ul>
            </div>

            <!-- Single Rubrique Link without subcategories -->
            <Link 
              v-else 
              :href="'/rubrique/' + rub.slug" 
              class="nav-item-link" 
              :class="{ 'active': $page.url === '/rubrique/' + rub.slug }"
            >
              <i v-if="rub.icon" :class="'bi ' + rub.icon + ' me-1 text-danger'"></i>
              {{ locale === 'en' ? (rub.name_en || rub.name_fr) : rub.name_fr }}
            </Link>
          </template>

          <!-- Reviews (Tests Jeux & Apps) -->
          <div class="dropdown d-inline-block">
            <a class="nav-item-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" :class="{ 'active': $page.url.startsWith('/tests-') || $page.url.startsWith('/test-') }">
              Tests
            </a>
            <ul class="dropdown-menu shadow border-0 mt-2">
              <li>
                <Link href="/tests-jeux-video" class="dropdown-item py-2">
                  <i class="bi bi-controller me-2 text-danger"></i> Tests Jeux Vidéo
                </Link>
              </li>
              <li>
                <Link href="/tests-applications" class="dropdown-item py-2">
                  <i class="bi bi-phone me-2 text-primary"></i> Tests Applications
                </Link>
              </li>
            </ul>
          </div>

          <!-- Forum Link -->
          <Link href="/forum" class="nav-item-link" :class="{ 'active': $page.url.startsWith('/forum') }">
            <i class="bi bi-chat-dots-fill me-1 text-danger"></i> Forum
          </Link>

          <!-- Conférences & Événements -->
          <Link href="/conferences" class="nav-item-link" :class="{ 'active': $page.url.startsWith('/conference') }">
            <i class="bi bi-calendar-event me-1"></i> Événements
          </Link>

          <!-- Offres d'Emploi IT -->
          <Link href="/emplois" class="nav-item-link" :class="{ 'active': $page.url.startsWith('/emploi') }">
            <i class="bi bi-briefcase-fill me-1"></i> Emplois
          </Link>

          <!-- Fusion menu "Startup" avec sous-menu Startup (/startups), Projets (/projets), Entrepreneur (/entrepreneur-du-mois) -->
          <div class="dropdown d-inline-block">
            <a 
              class="nav-item-link dropdown-toggle" 
              href="#" 
              role="button" 
              data-bs-toggle="dropdown" 
              :class="{ 'active': $page.url.startsWith('/startup') || $page.url.startsWith('/projet') || $page.url.startsWith('/entrepreneur') }"
            >
              <i class="bi bi-building me-1" style="color: var(--tech-green);"></i> Startup
            </a>
            <ul class="dropdown-menu shadow border-0 mt-2">
              <li>
                <Link href="/startups" class="dropdown-item py-2 fw-semibold" :class="{ 'text-success fw-bold': $page.url.startsWith('/startup') }">
                  <i class="bi bi-building me-2" style="color: var(--tech-green);"></i> Startup
                </Link>
              </li>
              <li>
                <Link href="/projets" class="dropdown-item py-2 fw-semibold" :class="{ 'text-success fw-bold': $page.url.startsWith('/projet') }">
                  <i class="bi bi-rocket-takeoff-fill me-2" style="color: var(--tech-green);"></i> Projets
                </Link>
              </li>
              <li>
                <Link href="/entrepreneur-du-mois" class="dropdown-item py-2 fw-semibold" :class="{ 'text-success fw-bold': $page.url.startsWith('/entrepreneur') }">
                  <i class="bi bi-star-fill me-2 text-warning"></i> Entrepreneur
                </Link>
              </li>
            </ul>
          </div>
        </nav>
      </div>
    </header>

    <!-- 3. Sub-Topics / Trending Dossiers Bar -->
    <div class="sub-topics-bar py-1 bg-light border-bottom">
      <div class="container d-flex align-items-center">
        <span class="small fw-bold text-muted me-2 text-uppercase" style="font-size: 11px;">
          <i class="bi bi-fire text-danger me-1"></i>{{ locale === 'en' ? 'HOT DOSSIERS:' : 'EN TENDANCE :' }}
        </span>
        <div class="d-flex align-items-center flex-nowrap overflow-auto py-1 gap-2">
          <Link 
            v-for="d in featuredDossiers" 
            :key="d.id" 
            :href="'/dossier/' + d.slug" 
            class="topic-pill"
          >
            #{{ locale === 'en' ? (d.title_en || d.title_fr) : d.title_fr }}
          </Link>
          <Link href="/projets" class="topic-pill bg-danger-subtle text-danger fw-bold">
            🚀 #Startups-Cameroun
          </Link>
        </div>
      </div>
    </div>

    <!-- 4. Mobile Offcanvas / Sliding Drawer Menu with Smooth Transition & Close Button (X) -->
    <transition name="drawer-fade">
      <div v-if="isMobileMenuOpen" class="mobile-drawer-overlay" @click.self="isMobileMenuOpen = false">
        <div class="mobile-drawer-panel shadow-lg">
          <!-- Drawer Header with Logo and prominent X close button -->
          <div class="d-flex align-items-center justify-content-between p-3 border-bottom bg-white">
            <img :src="'/' + (siteSettings.logo || 'assets/images/logo.png')" alt="Tech237" height="38" style="max-width: 180px; object-fit: contain;">
            <button 
              @click="isMobileMenuOpen = false" 
              class="btn btn-sm btn-light rounded-circle p-2 d-flex align-items-center justify-content-center border"
              aria-label="Fermer le menu"
              style="width: 36px; height: 36px;"
            >
              <i class="bi bi-x-lg fs-5 text-danger fw-bold"></i>
            </button>
          </div>

          <!-- Drawer Body Nav Links -->
          <div class="p-3 overflow-y-auto" style="max-height: calc(100vh - 80px);">
            <div class="list-group list-group-flush mb-4">
              <Link href="/" class="list-group-item list-group-item-action py-2 fw-bold border-0" @click="isMobileMenuOpen = false">
                <i class="bi bi-house me-2 text-danger"></i> {{ locale === 'en' ? 'Home' : 'Accueil' }}
              </Link>

              <!-- Dynamic Rubriques & Categories in Mobile Drawer -->
              <template v-for="rub in navRubriques" :key="'mob-' + rub.id">
                <div v-if="rub.categories && rub.categories.length > 0" class="border-bottom py-1">
                  <div class="d-flex align-items-center justify-content-between px-3 py-2 fw-bold text-dark">
                    <span>
                      <i v-if="rub.icon" :class="'bi ' + rub.icon + ' me-2 text-danger'"></i>
                      {{ locale === 'en' ? (rub.name_en || rub.name_fr) : rub.name_fr }}
                    </span>
                    <Link :href="'/rubrique/' + rub.slug" class="small text-danger text-decoration-none" @click="isMobileMenuOpen = false">
                      Voir tout
                    </Link>
                  </div>
                  <div class="ps-4">
                    <Link 
                      v-for="cat in rub.categories" 
                      :key="'mob-cat-' + cat.id"
                      :href="'/category/' + cat.slug"
                      class="list-group-item list-group-item-action py-2 small border-0 text-muted"
                      @click="isMobileMenuOpen = false"
                    >
                      <i :class="'bi ' + (cat.icon || 'bi-tag') + ' me-2'" :style="{ color: cat.color || '#FF004B' }"></i>
                      {{ locale === 'en' ? (cat.name_en || cat.name_fr) : cat.name_fr }}
                    </Link>
                  </div>
                </div>

                <Link 
                  v-else 
                  :href="'/rubrique/' + rub.slug" 
                  class="list-group-item list-group-item-action py-2 fw-bold border-0" 
                  @click="isMobileMenuOpen = false"
                >
                  <i v-if="rub.icon" :class="'bi ' + rub.icon + ' me-2 text-danger'"></i>
                  {{ locale === 'en' ? (rub.name_en || rub.name_fr) : rub.name_fr }}
                </Link>
              </template>

              <!-- Reviews & Interviews -->
              <Link href="/tests-jeux-video" class="list-group-item list-group-item-action py-2 fw-bold border-0" @click="isMobileMenuOpen = false">
                <i class="bi bi-controller me-2 text-danger"></i> Tests Jeux Vidéo
              </Link>
              <Link href="/tests-applications" class="list-group-item list-group-item-action py-2 fw-bold border-0" @click="isMobileMenuOpen = false">
                <i class="bi bi-phone me-2 text-primary"></i> Tests Applications
              </Link>

              <!-- Additional Modules -->
              <Link href="/forum" class="list-group-item list-group-item-action py-2 fw-bold border-0" @click="isMobileMenuOpen = false">
                <i class="bi bi-chat-dots-fill me-2 text-danger"></i> Forum Communautaire
              </Link>
              <Link href="/conferences" class="list-group-item list-group-item-action py-2 fw-bold border-0" @click="isMobileMenuOpen = false">
                <i class="bi bi-calendar-event me-2 text-info"></i> Conférences & Événements
              </Link>
              <Link href="/emplois" class="list-group-item list-group-item-action py-2 fw-bold border-0" @click="isMobileMenuOpen = false">
                <i class="bi bi-briefcase-fill me-2 text-secondary"></i> Offres d'Emploi IT
              </Link>
              <!-- Menu Fusionné "Startup" dans la version mobile (responsive) avec les mêmes sous-menus -->
              <div class="border-bottom py-1">
                <div 
                  class="d-flex align-items-center justify-content-between px-3 py-2 fw-bold text-dark" 
                  style="cursor: pointer;"
                  @click="isMobileStartupOpen = !isMobileStartupOpen"
                >
                  <span>
                    <i class="bi bi-building me-2" style="color: var(--tech-green);"></i> Startup
                  </span>
                  <i class="bi" :class="isMobileStartupOpen ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 13px;"></i>
                </div>
                <div v-show="isMobileStartupOpen" class="ps-4">
                  <Link href="/startups" class="list-group-item list-group-item-action py-2 small border-0 fw-semibold" @click="isMobileMenuOpen = false">
                    <i class="bi bi-building me-2" style="color: var(--tech-green);"></i> Startup
                  </Link>
                  <Link href="/projets" class="list-group-item list-group-item-action py-2 small border-0 fw-semibold" @click="isMobileMenuOpen = false">
                    <i class="bi bi-rocket-takeoff-fill me-2" style="color: var(--tech-green);"></i> Projets
                  </Link>
                  <Link href="/entrepreneur-du-mois" class="list-group-item list-group-item-action py-2 small border-0 fw-semibold" @click="isMobileMenuOpen = false">
                    <i class="bi bi-star-fill me-2 text-warning"></i> Entrepreneur
                  </Link>
                </div>
              </div>
              
              <!-- Prominent "Nous Soutenir" Link in Mobile Drawer -->
              <div class="p-2 mt-2">
                <Link href="/nous-soutenir" class="btn w-100 rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 text-white shadow-sm" style="background-color: var(--tech-green);" @click="isMobileMenuOpen = false">
                  <i class="bi bi-heart-fill text-white fs-5"></i> Nous Soutenir
                </Link>
              </div>
            </div>

            <!-- Mobile Auth & Profile Section -->
            <div class="card bg-light border-0 p-3 mb-3">
              <div v-if="authUser">
                <div class="fw-bold mb-1">{{ authUser.first_name || authUser.name }}</div>
                <div class="small text-muted mb-3">{{ authUser.email }}</div>
                <div class="d-grid gap-2">
                  <Link href="/espace-utilisateur" class="btn btn-sm btn-dark rounded-pill" @click="isMobileMenuOpen = false">
                    <i class="bi bi-speedometer2 me-1"></i> Mon Espace
                  </Link>
                  <button @click="logoutUser" class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                  </button>
                </div>
              </div>
              <div v-else class="d-grid gap-2">
                <Link href="/login" class="btn btn-dark rounded-pill fw-bold" @click="isMobileMenuOpen = false">
                  <i class="bi bi-person-fill me-1"></i> Connexion
                </Link>
              </div>
            </div>

            <!-- Socials -->
            <div class="d-flex justify-content-center gap-3 py-2 text-muted">
              <a :href="siteSettings.social_facebook || '#'" target="_blank" class="text-dark fs-5"><i class="bi bi-facebook"></i></a>
              <a :href="siteSettings.social_twitter || '#'" target="_blank" class="text-dark fs-5"><i class="bi bi-twitter-x"></i></a>
              <a :href="siteSettings.social_instagram || '#'" target="_blank" class="text-dark fs-5"><i class="bi bi-instagram"></i></a>
              <a :href="siteSettings.social_youtube || '#'" target="_blank" class="text-dark fs-5"><i class="bi bi-youtube"></i></a>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- 5. Main Page View Content Slot with Wallpaper Overlay Container when active -->
    <main class="flex-grow-1" :class="{ 'container-fluid px-lg-5 my-4': backgroundAd }">
      <div :class="{ 'bg-white rounded-4 shadow-lg p-3 p-md-4 border': backgroundAd }">
        <slot />
      </div>
    </main>

    <!-- Footer Banner Ad (Bas de page) -->
    <div v-if="footerBannerAd" class="footer-banner-container bg-light border-top border-bottom py-3 text-center my-4">
      <div class="container">
        <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 9px; letter-spacing: 1px;">Sponsor &bull; Partenaire Tech237</div>
        <a v-if="footerBannerAd.image" :href="footerBannerAd.link_url || '#'" target="_blank" rel="noopener noreferrer">
          <img :src="'/' + footerBannerAd.image" :alt="footerBannerAd.title || 'Publicité'" class="img-fluid rounded-3 shadow-sm" style="max-height: 120px; width: auto; object-fit: contain;">
        </a>
        <div v-else-if="footerBannerAd.custom_code" v-html="footerBannerAd.custom_code"></div>
      </div>
    </div>

    <!-- 6. Tech237 Footer with Brand Identity -->
    <footer class="tech237-footer mt-5 pt-5 pb-4">
      <div class="container">
        
        <!-- Big Tech237 Logo & Slogan -->
        <div class="mb-5 d-flex align-items-center justify-content-between flex-wrap gap-4 pb-4 border-bottom border-secondary border-opacity-25">
          <div>
            <Link href="/" class="d-inline-block mb-2">
              <img :src="'/' + (siteSettings.logo || 'assets/images/logo.png')" alt="Tech237" height="60" style="max-width: 280px; object-fit: contain;">
            </Link>
            <p class="text-light opacity-75 small mb-0 font-monospace">
              🇨🇲 Le hub technologique, média et incubateur d'innovations au Cameroun.
            </p>
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: var(--tech-green); color: #fff;">
              <i class="bi bi-rocket-takeoff-fill me-1"></i> #Innovation237
            </span>
            <span class="badge bg-light text-dark px-3 py-2 rounded-pill fw-bold">
              Tech &bull; Startups &bull; Code
            </span>
          </div>
        </div>

        <div class="row g-4 mb-5">
          
          <!-- Column 1: Légal -->
          <div class="col-6 col-md-3">
            <h6 class="fw-bold text-white mb-3 text-uppercase small">Légal</h6>
            <ul class="list-unstyled small text-light opacity-75">
              <li class="mb-2"><a href="#" class="text-decoration-none">Gestion des cookies</a></li>
              <li class="mb-2"><Link href="/conditions-generales" class="text-decoration-none">Mentions Légales</Link></li>
              <li class="mb-2"><Link href="/nous-soutenir" class="text-decoration-none">Nous contacter & Partenariats</Link></li>
              <li class="mb-2"><Link href="/politique-de-confidentialite" class="text-decoration-none">Politique de confidentialité</Link></li>
              <li class="mb-2"><Link href="/projets" class="text-decoration-none">Projets OHADA</Link></li>
            </ul>
          </div>

          <!-- Column 2: Informations -->
          <div class="col-6 col-md-3">
            <h6 class="fw-bold text-white mb-3 text-uppercase small">Écosystème Tech237</h6>
            <ul class="list-unstyled small text-light opacity-75">
              <li class="mb-2"><Link href="/projets" class="text-decoration-none">Showcase Startups</Link></li>
              <li class="mb-2"><Link href="/emplois" class="text-decoration-none">Offres d'Emploi IT</Link></li>
              <li class="mb-2"><Link href="/conferences" class="text-decoration-none">Événements & Meetups</Link></li>
              <li class="mb-2"><Link href="/forum" class="text-decoration-none">Forum Communautaire</Link></li>
              <li class="mb-2"><Link href="/entrepreneur-du-mois" class="text-decoration-none">Entrepreneurs du mois</Link></li>
            </ul>
          </div>

          <!-- Column 3: Index des dossiers (#0_9 A-Z) -->
          <div class="col-12 col-md-2">
            <h6 class="fw-bold text-white mb-3 text-uppercase small">Index des dossiers</h6>
            <div class="d-flex flex-wrap gap-2 small font-monospace">
              <Link href="/index-dossiers?letter=0-9" class="text-light opacity-75 text-decoration-none">#0_9</Link>
              <Link 
                v-for="letter in letters" 
                :key="letter" 
                :href="'/index-dossiers?letter=' + letter" 
                class="text-uppercase text-light opacity-75 text-decoration-none"
              >
                {{ letter }}
              </Link>
            </div>
          </div>

          <!-- Column 4: Newsletter Box with Emerald Green Button -->
          <div class="col-12 col-md-4" id="newsletterSection">
            <h6 class="fw-bold text-white mb-2 text-uppercase small">Restez à la pointe de la Tech au Cameroun</h6>
            <p class="small text-light opacity-75 mb-3">
              Recevez les analyses de startups, nouveaux projets, opportunités d'emploi IT et tutoriels chaque semaine.
            </p>
            <form @submit.prevent="submitNewsletter">
              <div class="input-group mb-2">
                <input 
                  type="email" 
                  v-model="newsletterEmail" 
                  class="form-control rounded-start-pill bg-white border-0 px-3 py-2 text-dark" 
                  placeholder="Votre adresse email pro ou perso..." 
                  required
                >
                <button 
                  type="submit" 
                  class="btn rounded-end-pill px-4 fw-bold text-white d-flex align-items-center gap-1 shadow-sm"
                  style="background-color: var(--tech-green);"
                  :disabled="isSubmittingNewsletter"
                >
                  <i class="bi bi-send-fill"></i> S'inscrire
                </button>
              </div>
              <div v-if="newsletterMessage" class="small text-success fw-bold">
                {{ newsletterMessage }}
              </div>
            </form>
          </div>

        </div>

        <!-- Bottom Copyright Line -->
        <div class="d-flex align-items-center justify-content-between pt-4 border-top border-secondary border-opacity-25 flex-wrap gap-3 small text-light opacity-75">
          <div class="d-flex align-items-center gap-2">
            <span>© 2026 <strong>Tech237</strong> &bull; Là où les idées deviennent des projets.</span>
            <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill extra-small">🇨🇲 Made in Cameroon</span>
          </div>
          <div>
            Portail Numérique & Innovation Technologique
          </div>
          <div class="d-flex align-items-center gap-3">
            <a :href="siteSettings.social_facebook || '#'" target="_blank" class="text-light opacity-75"><i class="bi bi-facebook fs-6"></i></a>
            <a :href="siteSettings.social_twitter || '#'" target="_blank" class="text-light opacity-75"><i class="bi bi-twitter-x fs-6"></i></a>
            <a :href="siteSettings.social_instagram || '#'" target="_blank" class="text-light opacity-75"><i class="bi bi-instagram fs-6"></i></a>
            <a :href="siteSettings.social_tiktok || '#'" target="_blank" class="text-light opacity-75"><i class="bi bi-tiktok fs-6"></i></a>
            <a :href="siteSettings.social_youtube || '#'" target="_blank" class="text-light opacity-75"><i class="bi bi-youtube fs-6"></i></a>
          </div>
        </div>

      </div>
    </footer>

    <!-- 7. Floating Scroll-to-Top Button -->
    <transition name="fade">
      <button 
        v-if="showScrollTop" 
        @click="scrollToTop" 
        class="scroll-to-top-btn shadow-lg"
        title="Remonter en haut"
        aria-label="Remonter en haut"
      >
        <i class="bi bi-arrow-up fs-5"></i>
      </button>
    </transition>

    <!-- 8. First-Visit Welcome Modal Ad (modal_popup) -->
    <div 
      v-if="isPopupOpen && popupAd" 
      class="popup-ad-modal-backdrop"
      @click.self="closePopupAd"
    >
      <div class="popup-ad-modal-dialog">
        <!-- Off-Frame Circle Close Button on Top-Right -->
        <button 
          @click="closePopupAd" 
          class="popup-close-circle-btn" 
          title="Fermer la publicité"
          aria-label="Fermer"
        >
          <i class="bi bi-x-lg"></i>
        </button>

        <div class="popup-ad-modal-content">
          <a v-if="popupAd.link_url" :href="popupAd.link_url" target="_blank" @click="closePopupAd">
            <img :src="'/' + popupAd.image" :alt="popupAd.title" class="img-fluid rounded-4 shadow-lg w-100 object-fit-cover">
          </a>
          <img v-else :src="'/' + popupAd.image" :alt="popupAd.title" class="img-fluid rounded-4 shadow-lg w-100 object-fit-cover">
        </div>
      </div>
    </div>

    <!-- 9. Fullscreen Search Modal -->
    <div v-if="isSearchOpen" class="fullscreen-search-modal" @click.self="closeSearchModal">
      <div class="container py-5">
        <div class="d-flex justify-content-end mb-4">
          <button @click="closeSearchModal" class="btn btn-outline-light rounded-circle p-2" style="width: 44px; height: 44px;">
            <i class="bi bi-x-lg fs-5"></i>
          </button>
        </div>

        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="position-relative mb-4">
              <input 
                ref="searchInputRef"
                type="text" 
                v-model="searchQuery" 
                @input="onSearchInput"
                class="search-input-lg" 
                :placeholder="locale === 'en' ? 'Type at least 3 characters (e.g. AI, Web, Dev, Fintech)...' : 'Tapez au moins 3 caractères (ex: IA, Web, Dev, Fintech)...'"
              >
              <div v-if="isSearching" class="position-absolute end-0 top-50 translate-middle-y text-danger pe-3">
                <div class="spinner-border spinner-border-sm" role="status"></div>
              </div>
            </div>

            <!-- Trending Searches (Visible when input is empty) -->
            <div v-if="!searchQuery" class="mb-4">
              <div class="small fw-bold text-uppercase text-white-50 mb-3 d-flex align-items-center">
                <i class="bi bi-fire text-danger me-2"></i> Recherches populaires &amp; en tendance :
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button 
                  v-for="term in trendingSearches" 
                  :key="term"
                  type="button" 
                  class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 extra-small"
                  @click="selectTrendingSearch(term)"
                >
                  <i class="bi bi-search me-1 text-success"></i> {{ term }}
                </button>
              </div>
            </div>

            <!-- Minimum 3 characters hint when typing -->
            <div v-else-if="searchQuery.trim().length < 3" class="text-white-50 small mb-4 text-center">
              <i class="bi bi-info-circle me-1 text-warning"></i>
              Tapez au moins <strong>3 caractères</strong> pour lancer la recherche (actuel : {{ searchQuery.trim().length }}/3)...
            </div>
          </div>
        </div>

        <!-- Live Instant Search Results -->
        <div v-if="searchResults && hasSearchResults" class="row justify-content-center">
          <div class="col-lg-10 row g-4 text-white">
            <div v-if="searchResults.articles && searchResults.articles.length" class="col-md-6 col-lg-4">
              <h6 class="text-danger fw-bold text-uppercase border-bottom border-secondary pb-2">Articles</h6>
              <div class="list-group list-group-flush">
                <Link 
                  v-for="a in searchResults.articles" 
                  :key="a.id" 
                  :href="'/article/' + a.slug" 
                  @click="closeSearchModal"
                  class="list-group-item list-group-item-action bg-transparent text-white border-secondary px-0 py-2"
                >
                  <div class="fw-semibold small">{{ locale === 'en' ? (a.title_en || a.title_fr) : a.title_fr }}</div>
                </Link>
              </div>
            </div>

            <div v-if="searchResults.games && searchResults.games.length" class="col-md-6 col-lg-4">
              <h6 class="text-danger fw-bold text-uppercase border-bottom border-secondary pb-2">Jeux Vidéo</h6>
              <div class="list-group list-group-flush">
                <Link 
                  v-for="g in searchResults.games" 
                  :key="g.id" 
                  :href="'/test-jeu/' + g.slug" 
                  @click="closeSearchModal"
                  class="list-group-item list-group-item-action bg-transparent text-white border-secondary px-0 py-2 d-flex justify-content-between align-items-center"
                >
                  <div class="fw-semibold small">{{ g.game_name }}</div>
                  <span class="badge bg-danger fw-bold">{{ g.final_score }}/20</span>
                </Link>
              </div>
            </div>

            <div v-if="searchResults.apps && searchResults.apps.length" class="col-md-6 col-lg-4">
              <h6 class="text-danger fw-bold text-uppercase border-bottom border-secondary pb-2">Applications</h6>
              <div class="list-group list-group-flush">
                <Link 
                  v-for="ap in searchResults.apps" 
                  :key="ap.id" 
                  :href="'/test-application/' + ap.slug" 
                  @click="closeSearchModal"
                  class="list-group-item list-group-item-action bg-transparent text-white border-secondary px-0 py-2 d-flex justify-content-between align-items-center"
                >
                  <div class="fw-semibold small">{{ ap.app_name }}</div>
                  <span class="badge bg-warning text-dark fw-bold">{{ ap.final_score }}/20</span>
                </Link>
              </div>
            </div>

            <div v-if="searchResults.dossiers && searchResults.dossiers.length" class="col-md-6 col-lg-4">
              <h6 class="text-danger fw-bold text-uppercase border-bottom border-secondary pb-2">Dossiers</h6>
              <div class="list-group list-group-flush">
                <Link 
                  v-for="d in searchResults.dossiers" 
                  :key="d.id" 
                  :href="'/dossier/' + d.slug" 
                  @click="closeSearchModal"
                  class="list-group-item list-group-item-action bg-transparent text-white border-secondary px-0 py-2"
                >
                  <div class="fw-semibold small">{{ locale === 'en' ? (d.title_en || d.title_fr) : d.title_fr }}</div>
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- No Results Found Message -->
        <div v-else-if="searchResults && !hasSearchResults && !isSearching" class="row justify-content-center">
          <div class="col-lg-8 text-center py-5">
            <div class="rounded-circle bg-white bg-opacity-10 d-inline-flex align-items-center justify-content-center p-4 mb-3">
              <i class="bi bi-search fs-1 text-white-50"></i>
            </div>
            <h4 class="fw-bold text-white mb-2">Aucun résultats trouvé pour votre recherche</h4>
            <p class="text-white-50 small mb-0">
              Aucun article, test ou dossier ne correspond aux termes recherchés. Essayez avec d'autres mots-clés.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- 10. Lightweight Snowfall Effect Overlay -->
    <div v-if="siteSettings.snow_effect_enabled" class="snowfall-container" aria-hidden="true">
      <div 
        v-for="flake in snowflakes" 
        :key="flake.id" 
        class="snowflake"
        :style="{
          left: flake.left + '%',
          animationDuration: flake.duration + 's',
          animationDelay: flake.delay + 's',
          width: flake.size + 'px',
          height: flake.size + 'px',
          opacity: flake.opacity
        }"
      >
        <img src="/assets/images/snowflake.png" alt="Snow" class="w-100 h-100 object-fit-contain">
      </div>
    </div>

    <!-- 11. STATIC LOGOUT CONFIRMATION MODAL -->
    <div v-if="showLogoutConfirmModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.65); z-index: 1070;" @click.self="showLogoutConfirmModal = false">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow p-3">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold text-dark fs-6">
              <i class="bi bi-box-arrow-right text-danger me-2"></i>Déconnexion
            </h5>
            <button type="button" class="btn-close" @click="showLogoutConfirmModal = false"></button>
          </div>
          <div class="modal-body py-3">
            <p class="small text-muted mb-0">Êtes-vous sûr de vouloir vous déconnecter de votre compte Tech237 ?</p>
          </div>
          <div class="modal-footer border-0 pt-0 d-flex gap-2">
            <button type="button" class="btn btn-light btn-sm rounded-pill flex-grow-1" @click="showLogoutConfirmModal = false">Annuler</button>
            <button type="button" class="btn btn-danger btn-sm rounded-pill flex-grow-1 fw-bold" @click="confirmLogout">Oui, me déconnecter</button>
          </div>
        </div>
      </div>
    </div>

    <!-- 12. ANIMATED FRONT-END PAGE LOADER -->
    <transition name="fade-loader">
      <div 
        v-if="showPageLoader && !isUserDashboardRoute" 
        class="position-fixed top-0 start-0 w-100 h-100 bg-white d-flex flex-column align-items-center justify-content-center page-initial-loader"
        style="z-index: 9999;"
      >
        <div class="text-center p-4">
          <img 
            :src="'/' + (siteSettings.logo || 'assets/images/logo.png')" 
            alt="Tech237" 
            class="loader-logo mb-3"
            style="max-width: 260px; height: auto;"
          >
          <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
            <div class="spinner-grow spinner-grow-sm text-success" role="status"></div>
            <span class="fw-bold fs-6 text-dark tracking-wide">Tech237</span>
            <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
          </div>
          <p class="text-muted small fst-italic mb-0 animate-pulse">
            Là où les idées deviennent des projets
          </p>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()

const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']

const snowflakes = ref(
  Array.from({ length: 24 }, (_, i) => ({
    id: i,
    left: Math.floor(Math.random() * 96) + 1,
    duration: (6 + Math.random() * 8).toFixed(1),
    delay: (-(Math.random() * 8)).toFixed(1),
    size: Math.floor(16 + Math.random() * 22),
    opacity: (0.35 + Math.random() * 0.55).toFixed(2),
  }))
)

const siteSettings = computed(() => page.props.siteSettings || {})
const navRubriques = computed(() => page.props.navRubriques || [])
const featuredDossiers = computed(() => page.props.featuredDossiers || [])
const locale = computed(() => page.props.currentLocale || 'fr')
const authUser = computed(() => page.props.auth?.user || null)

// Ad Banners: Wallpaper Background Skinning, Welcome Modal Popup, Header Top & Footer
const backgroundAd = computed(() => page.props.adBanners?.background?.[0] || null)
const popupAd = computed(() => page.props.adBanners?.modal_popup?.[0] || null)
const headerTopAd = computed(() => page.props.adBanners?.header_top?.[0] || null)
const footerBannerAd = computed(() => page.props.adBanners?.footer_banner?.[0] || null)

const backgroundAdStyle = computed(() => {
  if (backgroundAd.value && backgroundAd.value.image) {
    return {
      backgroundImage: `linear-gradient(rgba(15, 15, 20, 0.65), rgba(15, 15, 20, 0.85)), url('/${backgroundAd.value.image}')`,
      backgroundAttachment: 'fixed',
      backgroundPosition: 'top center',
      backgroundSize: 'cover',
      backgroundRepeat: 'no-repeat',
      backgroundColor: '#121212'
    }
  }
  return {
    backgroundColor: '#ffffff'
  }
})

// Mobile Drawer State
const isMobileMenuOpen = ref(false)
const isMobileStartupOpen = ref(true)

// Popup Ad State (Shown once per day)
const isPopupOpen = ref(false)
onMounted(() => {
  if (popupAd.value) {
    const today = new Date().toISOString().slice(0, 10)
    const seenKey = 'melty_popup_seen_' + today
    if (!localStorage.getItem(seenKey)) {
      setTimeout(() => {
        isPopupOpen.value = true
      }, 1000)
    }
  }

  window.addEventListener('scroll', handleScroll)
})

function closePopupAd() {
  isPopupOpen.value = false
  const today = new Date().toISOString().slice(0, 10)
  localStorage.setItem('melty_popup_seen_' + today, '1')
}

// Scroll-To-Top Button Logic
const showScrollTop = ref(false)
function handleScroll() {
  showScrollTop.value = window.scrollY > 300
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

// Front-End Page Loader state
const showPageLoader = ref(false)
const isUserDashboardRoute = computed(() => {
  return typeof window !== 'undefined' && window.location.pathname.startsWith('/espace-utilisateur')
})

onMounted(() => {
  if (!isUserDashboardRoute.value) {
    showPageLoader.value = true
    setTimeout(() => {
      showPageLoader.value = false
    }, 600)
  }
})

// Search modal state
const isSearchOpen = ref(false)
const searchQuery = ref('')
const searchResults = ref(null)
const isSearching = ref(false)
const searchInputRef = ref(null)
let debounceTimeout = null

const hasSearchResults = computed(() => {
  if (!searchResults.value) return false
  return (searchResults.value.articles && searchResults.value.articles.length > 0) ||
         (searchResults.value.games && searchResults.value.games.length > 0) ||
         (searchResults.value.apps && searchResults.value.apps.length > 0) ||
         (searchResults.value.dossiers && searchResults.value.dossiers.length > 0)
})

const trendingSearches = [
  'Fintech Cameroun',
  'Startups Douala',
  'Intelligence Artificielle',
  'Développement Web & Mobile',
  'Recrutement Développeur',
  'Événements & Meetups',
  'Financement de projet',
]

function openSearchModal() {
  isSearchOpen.value = true
  nextTick(() => {
    if (searchInputRef.value) searchInputRef.value.focus()
  })
}

function closeSearchModal() {
  isSearchOpen.value = false
  searchQuery.value = ''
  searchResults.value = null
}

function selectTrendingSearch(term) {
  searchQuery.value = term
  onSearchInput()
}

function onSearchInput() {
  clearTimeout(debounceTimeout)
  if (!searchQuery.value || searchQuery.value.trim().length < 3) {
    searchResults.value = null
    isSearching.value = false
    return
  }

  isSearching.value = true
  // User requested: 1000ms debounce before results display
  debounceTimeout = setTimeout(async () => {
    try {
      const res = await fetch(`/api/search-autocomplete?q=${encodeURIComponent(searchQuery.value.trim())}`)
      searchResults.value = await res.json()
    } catch (e) {
      console.error(e)
    } finally {
      isSearching.value = false
    }
  }, 1000)
}

// Newsletter subscription
const newsletterEmail = ref('')
const isSubmittingNewsletter = ref(false)
const newsletterMessage = ref('')

async function submitNewsletter() {
  if (!newsletterEmail.value) return
  isSubmittingNewsletter.value = true
  try {
    const res = await fetch('/api/newsletter/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ email: newsletterEmail.value }),
    })
    const data = await res.json()
    newsletterMessage.value = data.message || 'Merci pour votre inscription !'
    newsletterEmail.value = ''
  } catch (e) {
    newsletterMessage.value = 'Inscription prise en compte !'
    newsletterEmail.value = ''
  } finally {
    isSubmittingNewsletter.value = false
  }
}

// Global Locale Switch (translates whole site & articles)
function switchLocale(loc) {
  router.post(`/locale/${loc}`, {}, {
    preserveState: false,
    preserveScroll: true,
  })
}

// Logout Confirmation Modal
const showLogoutConfirmModal = ref(false)

function logoutUser() {
  showLogoutConfirmModal.value = true
}

function confirmLogout() {
  showLogoutConfirmModal.value = false
  router.post('/logout')
}
</script>

<style scoped>
.social-top-link {
  color: #333;
  font-size: 15px;
  text-decoration: none;
  transition: color 0.2s ease;
}
.social-top-link:hover {
  color: #FF004B;
}
.nav-item-link {
  color: #111;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 13px;
  letter-spacing: 0.5px;
  text-decoration: none;
  padding: 6px 10px;
  border-radius: 6px;
  transition: all 0.2s ease;
}
.nav-item-link:hover, .nav-item-link.active {
  color: #FF004B;
  background-color: #fff0f3;
}
.topic-pill {
  color: #555;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 20px;
  padding: 3px 12px;
  font-size: 11px;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
}
.topic-pill:hover {
  background: #FF004B;
  color: #fff;
  border-color: #FF004B;
}

/* Mobile Offcanvas Drawer */
.mobile-drawer-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 99990;
  display: flex;
}
.mobile-drawer-panel {
  width: 82%;
  max-width: 320px;
  height: 100vh;
  background-color: #ffffff;
  overflow-y: auto;
  animation: slideInLeft 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes slideInLeft {
  from { transform: translateX(-100%); }
  to { transform: translateX(0); }
}

/* Scroll To Top Button */
.scroll-to-top-btn {
  position: fixed;
  bottom: 28px;
  right: 28px;
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background-color: #111;
  color: #fff;
  border: 2px solid #FF004B;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 9998;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.scroll-to-top-btn:hover {
  background-color: #FF004B;
  transform: translateY(-4px);
}

/* First-Visit Popup Ad Modal */
.popup-ad-modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(4px);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.popup-ad-modal-dialog {
  position: relative;
  max-width: 600px;
  width: 100%;
}
.popup-close-circle-btn {
  position: absolute;
  top: -20px;
  right: -20px;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background-color: #FF004B;
  color: #ffffff;
  border: 3px solid #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  cursor: pointer;
  box-shadow: 0 6px 16px rgba(0,0,0,0.4);
  z-index: 10;
  transition: transform 0.2s ease;
}
.popup-close-circle-btn:hover {
  transform: scale(1.1);
}

.fullscreen-search-modal {
  position: fixed;
  inset: 0;
  background: rgba(10, 10, 10, 0.95);
  backdrop-filter: blur(8px);
  z-index: 99999;
  overflow-y: auto;
}
.search-input-lg {
  width: 100%;
  background: transparent;
  border: none;
  border-bottom: 2px solid #FF004B;
  color: #fff;
  font-size: 28px;
  font-weight: 700;
  padding: 12px 0;
  outline: none;
}
.search-input-lg::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.snowfall-container {
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  pointer-events: none;
  z-index: 99998;
  overflow: hidden;
}

.snowflake {
  position: absolute;
  top: -45px;
  animation-name: snowfall-anim;
  animation-timing-function: linear;
  animation-iteration-count: infinite;
  will-change: transform;
}

@keyframes snowfall-anim {
  0% {
    transform: translateY(0) rotate(0deg) translateX(0);
  }
  50% {
    transform: translateY(50vh) rotate(180deg) translateX(25px);
  }
  100% {
    transform: translateY(105vh) rotate(360deg) translateX(-15px);
  }
}

.hover-danger:hover {
  color: #FF004B !important;
  font-weight: 700;
}

/* Page Initial Loader Styles */
.page-initial-loader {
  transition: opacity 0.4s ease, visibility 0.4s ease;
}
.loader-logo {
  animation: logoPulse 1.2s infinite alternate ease-in-out;
}
@keyframes logoPulse {
  0% { transform: scale(0.96); opacity: 0.85; }
  100% { transform: scale(1.02); opacity: 1; }
}
.fade-loader-enter-active, .fade-loader-leave-active {
  transition: opacity 0.4s ease;
}
.fade-loader-enter-from, .fade-loader-leave-to {
  opacity: 0;
}
</style>
