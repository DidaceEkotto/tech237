<template>
  <div 
    class="portfolio-root min-vh-100 position-relative overflow-x-hidden" 
    :class="[
      'theme-' + currentTheme, 
      'card-style-' + currentCardStyle,
      'font-' + currentFont,
      'bg-pattern-' + currentPattern,
      'structure-' + currentStructure,
      'heading-style-' + designEffects.heading_style,
      'btn-style-' + designEffects.button_style,
      'card-border-' + designEffects.card_border_effect,
      (customCursorEnabled || designEffects.cursor_effect === 'magnetic_ring' || designEffects.cursor_effect === 'glow_trail') ? 'custom-cursor-active' : '',
      customColorPalette.enabled ? 'custom-palette-active' : '',
      printSettingsConfig.density === 'compact' ? 'print-compact' : ''
    ]" 
    :style="cssVars"
    @mousemove="handleMouseMove"
  >
    <!-- FLOATING MESH GLOW ORBS (Design Effect) -->
    <div v-if="designEffects.floating_orbs" class="portfolio-floating-orbs pointer-events-none d-print-none">
      <div class="mesh-orb orb-1" :style="{ background: accentColor }"></div>
      <div class="mesh-orb orb-2"></div>
      <div class="mesh-orb orb-3" :style="{ background: accentColor }"></div>
    </div>

    <!-- MOUSE SPOTLIGHT HALO -->
    <div 
      v-if="spotlightEnabled || designEffects.cursor_effect === 'spotlight'" 
      class="portfolio-spotlight-layer position-fixed top-0 start-0 w-100 h-100 pointer-events-none d-print-none"
      :style="spotlightStyle"
    ></div>

    <!-- CUSTOM TECH CURSOR RING / GLOW TRAIL (Desktop) -->
    <div 
      v-if="customCursorEnabled || designEffects.cursor_effect === 'magnetic_ring' || designEffects.cursor_effect === 'glow_trail'" 
      class="portfolio-custom-cursor pointer-events-none d-none d-lg-block d-print-none"
      :class="designEffects.cursor_effect === 'glow_trail' ? 'cursor-glow-trail' : ''"
      :style="customCursorStyle"
    ></div>
    <Head>
      <title>{{ seoTitle }}</title>
      <meta name="description" :content="seoDescription">
      <meta property="og:title" :content="seoTitle">
      <meta property="og:description" :content="seoDescription">
      <meta property="og:type" content="profile">
      <meta property="og:url" :content="shareUrl">
      <meta property="og:image" :content="formatImageUrl(portfolioUser.avatar || '/assets/images/logo.png')">
      <meta property="og:image:secure_url" :content="formatImageUrl(portfolioUser.avatar || '/assets/images/logo.png')">
      <meta property="profile:username" :content="portfolioUser.username">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:title" :content="seoTitle">
      <meta name="twitter:description" :content="seoDescription">
    </Head>

    <!-- CUSTOM BACKGROUND IMAGE LAYER -->
    <div 
      v-if="bgImageUrl" 
      class="portfolio-custom-bg-layer position-fixed top-0 start-0 w-100 h-100 pointer-events-none d-print-none"
      :style="customBgStyle"
    ></div>

    <!-- PRELOADER SPLASH SCREEN -->
    <transition name="preloader-fade">
      <div 
        v-if="showPreloader" 
        class="preloader-overlay position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center d-print-none"
        :class="'theme-' + currentTheme"
      >
        <!-- Custom User Image/Logo Preloader -->
        <div v-if="preloaderConfig.custom_image || preloaderConfig.style === 'custom_image'" class="text-center p-4">
          <img 
            :src="formatImageUrl(preloaderConfig.custom_image || portfolioUser.avatar || '/assets/images/logo.png')" 
            :alt="portfolioUser.name" 
            class="preloader-custom-img mb-3 rounded-circle shadow-lg border border-3 border-white object-fit-cover" 
            style="width: 80px; height: 80px;"
          >
          <h3 class="fw-bold fs-5 text-dark mb-1">{{ portfolioUser.name }}</h3>
          <p class="text-muted small mb-0">{{ preloaderConfig.text || 'Chargement du portfolio...' }}</p>
        </div>

        <div v-else-if="preloaderConfig.style === 'pulse_logo'" class="text-center p-4">
          <img src="/assets/images/logo.png" alt="Tech237" class="preloader-pulse-logo mb-3" style="height: 68px; object-fit: contain;">
          <h3 class="fw-bold fs-5 text-dark mb-1">{{ portfolioUser.name }}</h3>
          <p class="text-muted small mb-0">{{ preloaderConfig.text || 'Chargement du portfolio...' }}</p>
        </div>

        <div v-else-if="preloaderConfig.style === 'terminal'" class="p-4 rounded-4 bg-dark text-success font-monospace shadow-lg" style="max-width: 460px; width: 90%;">
          <div class="mb-2 text-white-50 extra-small">&gt; boot system --user={{ portfolioUser.username }}</div>
          <div class="mb-2 text-white-50 extra-small">&gt; initializing stack &amp; projects...</div>
          <div class="fw-bold text-success">&gt; {{ preloaderConfig.text || 'Portfolio ready.' }} <span class="blink-cursor">_</span></div>
        </div>

        <div v-else-if="preloaderConfig.style === 'progress_bar'" class="text-center p-4" style="max-width: 340px; width: 85%;">
          <div class="fw-bold fs-6 text-dark mb-2">{{ preloaderConfig.text || 'Chargement du profil...' }}</div>
          <div class="progress rounded-pill shadow-xs" style="height: 8px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated rounded-pill w-100" :style="{ backgroundColor: accentColor }"></div>
          </div>
        </div>

        <div v-else-if="preloaderConfig.style === 'dots'" class="text-center p-4">
          <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
            <span class="bounce-dot dot-1" :style="{ backgroundColor: accentColor }"></span>
            <span class="bounce-dot dot-2" :style="{ backgroundColor: accentColor }"></span>
            <span class="bounce-dot dot-3" :style="{ backgroundColor: accentColor }"></span>
          </div>
          <div class="fw-bold fs-6 text-dark">{{ preloaderConfig.text || 'Chargement en cours...' }}</div>
        </div>

        <div v-else class="text-center p-4">
          <div class="spinner-border text-success mb-3" style="width: 3.2rem; height: 3.2rem;" :style="{ color: accentColor + ' !important' }" role="status"></div>
          <div class="fw-bold fs-6 text-dark">{{ preloaderConfig.text || 'Chargement du portfolio...' }}</div>
        </div>
      </div>
    </transition>

    <!-- AMBIENT PARTICLES OVERLAY (Snow, Confetti, Stars, Matrix, Bubbles) -->
    <div v-if="ambientEffect !== 'none'" class="ambient-effects-layer pointer-events-none d-print-none" :class="'ambient-' + ambientEffect">
      <!-- SNOW EFFECT (Christmas & Holidays) -->
      <template v-if="ambientEffect === 'snow'">
        <div v-for="n in 35" :key="'snow-' + n" class="snowflake" :style="getSnowflakeStyle(n)">❄</div>
      </template>

      <!-- CONFETTI EFFECT -->
      <template v-if="ambientEffect === 'confetti'">
        <div v-for="n in 30" :key="'confetti-' + n" class="confetti-piece" :style="getConfettiStyle(n)"></div>
      </template>

      <!-- STARS EFFECT -->
      <template v-if="ambientEffect === 'stars'">
        <div v-for="n in 25" :key="'star-' + n" class="ambient-star" :style="getStarStyle(n)">✦</div>
      </template>

      <!-- MATRIX EFFECT -->
      <template v-if="ambientEffect === 'matrix'">
        <div v-for="n in 18" :key="'matrix-' + n" class="matrix-column" :style="getMatrixStyle(n)">10101100</div>
      </template>

      <!-- BUBBLES EFFECT -->
      <template v-if="ambientEffect === 'bubbles'">
        <div v-for="n in 20" :key="'bubble-' + n" class="ambient-bubble" :style="getBubbleStyle(n)"></div>
      </template>
    </div>

    <!-- PRINT-ONLY QR CODE HEADER -->
    <div v-if="printSettingsConfig.include_qr_header && qrCodeDataUrl" class="d-none d-print-flex align-items-center justify-content-between border-bottom pb-3 mb-3 px-4 pt-3">
      <div>
        <h3 class="h5 fw-bold mb-0">{{ portfolioUser.name }}</h3>
        <div class="small text-muted">{{ portfolioUser.headline || '@' + portfolioUser.username }}</div>
        <div class="extra-small text-muted">{{ shareUrl }}</div>
      </div>
      <img :src="qrCodeDataUrl" alt="QR Portfolio" style="width: 68px; height: 68px;" />
    </div>

    <!-- TOP ANNOUNCEMENT / PROMO BAR -->
    <div
      v-if="announcementBar.enabled && !announcementDismissed && announcementBar.text"
      class="portfolio-announcement-bar py-2 px-3 d-print-none position-relative z-30"
      :style="announcementBarStyle"
    >
      <div class="container d-flex align-items-center justify-content-center gap-3 position-relative" :style="{ maxWidth: '1320px' }">
        <div class="overflow-hidden flex-grow-1 text-center">
          <div :class="announcementBar.scrolling ? 'announcement-marquee' : 'd-inline-flex align-items-center justify-content-center gap-2 flex-wrap'">
            <i v-if="announcementBar.icon" class="bi" :class="announcementBar.icon"></i>
            <span class="small fw-semibold">{{ announcementBar.text }}</span>
            <a
              v-if="announcementBar.cta_label && announcementBar.cta_url"
              :href="announcementBar.cta_url"
              class="btn btn-xs btn-light rounded-pill px-3 py-0 fw-bold text-dark ms-2 shadow-2xs"
            >
              {{ announcementBar.cta_label }}
            </a>
          </div>
        </div>
        <button
          v-if="announcementBar.dismissible !== false"
          type="button"
          @click="announcementDismissed = true"
          class="btn btn-sm p-0 border-0 opacity-75 hover-opacity-100"
          :style="{ color: announcementBar.text_color || '#FFFFFF' }"
          aria-label="Fermer l'annonce"
        >
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
    </div>

    <!-- TOP WEBSITE NAVBAR (Real Personal Website Header + Mobile Menu) -->
    <header 
      v-if="!isBlankCanvasMode || !blankCanvasConfig.hide_default_navbar"
      class="portfolio-nav d-print-none z-20"
      :class="[
        navbarPositionClass,
        websiteNavbar.enabled ? 'website-nav-mode nav-style-' + websiteNavbar.style : 'py-2 px-3 px-md-4 shadow-2xs backdrop-blur'
      ]"
    >
      <div class="container" :style="{ maxWidth: websiteNavbar.enabled ? '1320px' : undefined }">
        <div 
          class="d-flex align-items-center justify-content-between gap-2"
          :class="websiteNavbar.enabled && websiteNavbar.style === 'floating_pill' ? 'website-floating-pill-bar px-3 px-md-4 py-2 shadow-sm border my-2' : 'py-2 px-3'"
          :style="navbarInlineStyle"
        >
          <!-- Left: Owner Brand Logo ONLY (Custom Logo -> Avatar -> Initials; no username text) -->
          <div class="d-flex align-items-center gap-2 min-w-0">
            <a 
              href="#top" 
              @click.prevent="navigateToTargetBlock('hero', 'home')" 
              class="d-flex align-items-center gap-2 text-decoration-none min-w-0"
            >
              <img 
                v-if="websiteNavbar.logo_url" 
                :src="formatImageUrl(websiteNavbar.logo_url)" 
                :alt="portfolioUser.name" 
                class="rounded-circle border object-fit-cover flex-shrink-0 shadow-2xs"
                style="width: 38px; height: 38px;"
              />
              <img 
                v-else-if="portfolioUser.avatar" 
                :src="formatImageUrl(portfolioUser.avatar)" 
                :alt="portfolioUser.name" 
                class="rounded-circle border object-fit-cover flex-shrink-0 shadow-2xs"
                style="width: 38px; height: 38px;"
              />
              <div 
                v-else 
                class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0 extra-small"
                style="width: 38px; height: 38px;"
                :style="{ backgroundColor: accentColor }"
              >
                {{ getInitials(portfolioUser.name) }}
              </div>
            </a>
          </div>

          <!-- Center: Real Website Navigation Links with Accordion Sub-menus -->
          <nav v-if="websiteNavbar.enabled && websiteNavbar.menu_type !== 'always_hamburger_drawer'" class="d-none d-lg-flex align-items-center gap-1 position-relative">
            <div
              v-for="item in websiteNavItems"
              :key="item.id"
              class="position-relative"
            >
              <button
                type="button"
                @click="item.children && item.children.length ? toggleAccordionMenu(item.id) : navigateToTargetBlock(item.target_block || 'hero', item.id)"
                class="btn btn-sm rounded-pill px-3 py-1 fw-semibold transition d-inline-flex align-items-center gap-1 website-nav-link"
                :class="isWebsiteNavItemActive(item.id) ? 'active text-white shadow-2xs' : ''"
                :style="[
                  isWebsiteNavItemActive(item.id) ? { backgroundColor: accentColor } : (websiteNavbar.bg_type !== 'auto' ? { color: websiteNavbar.text_color } : {}),
                  { fontSize: navbarFontSizePx }
                ]"
              >
                <i class="bi" :class="item.icon"></i>
                <span>{{ item.label }}</span>
                <i
                  v-if="item.children && item.children.length"
                  class="bi bi-chevron-down extra-small ms-1 transition"
                  :style="{ transform: openAccordionMenuId === item.id ? 'rotate(180deg)' : 'rotate(0deg)' }"
                ></i>
              </button>

              <!-- Desktop Accordion / Dropdown Sub-menu -->
              <transition name="accordion-slide">
                <div
                  v-if="item.children && item.children.length && openAccordionMenuId === item.id"
                  class="position-absolute start-0 mt-2 p-2 rounded-4 shadow-lg border bg-white d-flex flex-column gap-1"
                  style="min-width: 220px; z-index: 1050;"
                >
                  <button
                    type="button"
                    @click="navigateToTargetBlock(item.target_block || 'hero', item.id)"
                    class="btn btn-sm btn-light text-start rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 text-dark"
                    :style="{ fontSize: navbarFontSizePx }"
                  >
                    <i class="bi" :class="item.icon" :style="{ color: accentColor }"></i>
                    <span>{{ item.label }} (Principal)</span>
                  </button>
                  <button
                    v-for="sub in item.children"
                    :key="sub.id"
                    type="button"
                    @click="navigateToTargetBlock(sub.target_block || 'about', sub.id)"
                    class="btn btn-sm btn-light text-start rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 text-dark"
                    :style="{ fontSize: navbarFontSizePx }"
                  >
                    <i class="bi" :class="sub.icon || 'bi-arrow-return-right'" :style="{ color: accentColor }"></i>
                    <span>{{ sub.label }}</span>
                  </button>
                </div>
              </transition>
            </div>
          </nav>

          <!-- Right: Utility & Primary Website CTA (Partager & vCard removed per user request) -->
          <div class="d-flex align-items-center gap-2">

            <!-- Download CV Button -->
            <a 
              v-if="cvFileUrl" 
              :href="cvFileUrl" 
              target="_blank" 
              download
              class="btn btn-sm btn-nav-action rounded-pill px-2 px-md-3 fw-semibold d-none d-sm-inline-flex align-items-center gap-1 text-decoration-none"
              title="Télécharger mon CV"
            >
              <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
              <span class="d-none d-xl-inline">CV</span>
            </a>

            <!-- Command Palette Ctrl+K Trigger -->
            <button
              v-if="commandPaletteConfig.enabled && commandPaletteConfig.show_in_navbar !== false"
              type="button"
              @click="commandPaletteOpen = true"
              class="btn btn-sm btn-nav-action rounded-pill px-2 px-md-3 fw-semibold d-inline-flex align-items-center gap-2 border shadow-2xs"
              title="Recherche rapide (Ctrl+K)"
            >
              <i class="bi bi-search"></i>
              <span v-if="commandPaletteConfig.shortcut_hint !== false" class="d-none d-xl-inline extra-small opacity-75 border rounded px-1">Ctrl+K</span>
            </button>

            <!-- Primary Website CTA Button -->
            <button 
              v-if="websiteNavbar.enabled && isBlockEnabled('contact')"
              type="button"
              @click="navigateWebsitePage('contact', 'portfolioContactSection')"
              class="btn btn-sm rounded-pill px-3 py-1 fw-bold text-white shadow-sm d-none d-md-inline-flex align-items-center gap-1"
              :style="{ backgroundColor: accentColor }"
            >
              <span>{{ websiteNavbar.cta_text || 'Me contacter' }}</span>
              <i class="bi bi-arrow-up-right extra-small"></i>
            </button>

            <!-- Owner Quick Edit Shortcut -->
            <Link 
              v-if="isOwner" 
              href="/espace-utilisateur?section=portfolio" 
              class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold shadow-2xs d-inline-flex align-items-center gap-1"
              title="Modifier ce site portfolio"
            >
              <i class="bi bi-sliders"></i>
              <span class="d-none d-sm-inline">Personnaliser</span>
            </Link>

            <!-- Hamburger Menu Trigger Button (Desktop & Mobile according to menu_type & hamburger_style) -->
            <button
              v-if="websiteNavbar.enabled"
              type="button"
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="btn btn-sm btn-nav-action border shadow-2xs d-inline-flex align-items-center justify-content-center gap-1"
              :class="[
                websiteNavbar.menu_type === 'desktop_links_mobile_drawer' ? 'd-lg-none' : '',
                websiteNavbar.hamburger_style === 'pill_with_label' ? 'rounded-pill px-3 py-1 fw-bold' : 'rounded-circle p-0'
              ]"
              :style="websiteNavbar.hamburger_style === 'pill_with_label' ? {} : { width: '38px', height: '38px' }"
              aria-label="Menu de navigation"
            >
              <template v-if="mobileMenuOpen">
                <i class="bi bi-x-lg fs-6"></i>
                <span v-if="websiteNavbar.hamburger_style === 'pill_with_label'" class="small">Fermer</span>
              </template>
              <template v-else-if="websiteNavbar.hamburger_style === 'grid_dots'">
                <i class="bi bi-grid-3x3-gap-fill fs-6"></i>
              </template>
              <template v-else-if="websiteNavbar.hamburger_style === 'modern_stagger'">
                <i class="bi bi-filter-right fs-5"></i>
              </template>
              <template v-else-if="websiteNavbar.hamburger_style === 'pill_with_label'">
                <i class="bi bi-list fs-5"></i>
                <span class="small">Menu</span>
              </template>
              <template v-else>
                <i class="bi bi-list fs-5"></i>
              </template>
            </button>
          </div>
        </div>

        <!-- Top Dropdown Navigation Drawer (When drawer_side === 'top_dropdown') -->
        <div v-if="websiteNavbar.enabled && mobileMenuOpen && websiteNavbar.drawer_side === 'top_dropdown'" class="py-3 px-3 mb-2 rounded-4 border shadow-lg content-card">
          <div class="d-flex flex-column gap-1">
            <button
              v-for="item in websiteNavItems"
              :key="'mob-' + item.id"
              type="button"
              @click="navigateWebsitePage(item.id, item.anchor); mobileMenuOpen = false"
              class="btn btn-sm rounded-3 py-2 px-3 text-start fw-bold d-flex align-items-center justify-content-between"
              :class="isWebsiteNavItemActive(item.id) ? 'text-white' : 'text-dark'"
              :style="isWebsiteNavItemActive(item.id) ? { backgroundColor: accentColor } : {}"
            >
              <span class="d-flex align-items-center gap-2">
                <i class="bi" :class="item.icon"></i>
                {{ item.label }}
              </span>
              <i class="bi bi-chevron-right extra-small"></i>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- LATERAL OFFCANVAS HAMBURGER DRAWER (Left, Right or Fullscreen Overlay) -->
    <div
      v-if="websiteNavbar.enabled && mobileMenuOpen && websiteNavbar.drawer_side !== 'top_dropdown'"
      class="portfolio-offcanvas-backdrop position-fixed top-0 start-0 w-100 h-100 d-print-none"
      style="z-index: 1090; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px);"
      @click.self="mobileMenuOpen = false"
    >
      <aside
        class="portfolio-offcanvas-drawer bg-white text-dark shadow-lg d-flex flex-column justify-content-between p-4"
        :class="[
          websiteNavbar.drawer_side === 'left' ? 'drawer-side-left' : (websiteNavbar.drawer_side === 'fullscreen' ? 'drawer-side-fullscreen' : 'drawer-side-right')
        ]"
      >
        <div>
          <!-- Drawer Header with Profile -->
          <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
            <div class="d-flex align-items-center gap-2 min-w-0">
              <img
                v-if="portfolioUser.avatar"
                :src="formatImageUrl(portfolioUser.avatar)"
                :alt="portfolioUser.name"
                class="rounded-circle border object-fit-cover flex-shrink-0"
                style="width: 44px; height: 44px;"
              />
              <div
                v-else
                class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                style="width: 44px; height: 44px;"
                :style="{ backgroundColor: accentColor }"
              >
                {{ getInitials(portfolioUser.name) }}
              </div>
              <div class="min-w-0">
                <strong class="d-block text-dark text-truncate">{{ websiteNavbar.brand_name || portfolioUser.name }}</strong>
                <span class="extra-small text-muted d-block text-truncate">{{ websiteNavbar.brand_subtitle || portfolioUser.headline || 'Portfolio Officiel' }}</span>
              </div>
            </div>
            <button type="button" @click="mobileMenuOpen = false" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <!-- Drawer Navigation Links (True Accordion Support) -->
          <div class="d-flex flex-column gap-2">
            <div v-for="item in websiteNavItems" :key="'drawer-' + item.id" class="d-flex flex-column gap-1">
              <button
                type="button"
                @click="item.children && item.children.length ? toggleAccordionMenu(item.id) : (navigateToTargetBlock(item.target_block || 'hero', item.id), mobileMenuOpen = false)"
                class="btn rounded-4 py-2 px-3 text-start fw-bold d-flex align-items-center justify-content-between transition"
                :class="isWebsiteNavItemActive(item.id) ? 'text-white shadow-sm' : 'btn-light text-dark border'"
                :style="[isWebsiteNavItemActive(item.id) ? { backgroundColor: accentColor } : {}, { fontSize: navbarFontSizePx }]"
              >
                <span class="d-flex align-items-center gap-2">
                  <i class="bi fs-5" :class="item.icon"></i>
                  <span>{{ item.label }}</span>
                </span>
                <i
                  class="bi fs-5 transition"
                  :class="item.children && item.children.length ? 'bi-chevron-down' : 'bi-arrow-right-short'"
                  :style="item.children && item.children.length && openAccordionMenuId === item.id ? { transform: 'rotate(180deg)' } : {}"
                ></i>
              </button>

              <transition name="accordion-slide">
                <div v-if="item.children && item.children.length && openAccordionMenuId === item.id" class="ms-3 ps-2 border-start border-2 d-flex flex-column gap-1 py-1" :style="{ borderColor: accentColor }">
                  <button
                    type="button"
                    @click="navigateToTargetBlock(item.target_block || 'hero', item.id); mobileMenuOpen = false"
                    class="btn btn-sm btn-light text-start rounded-3 py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                  >
                    <i class="bi" :class="item.icon" :style="{ color: accentColor }"></i>
                    <span>{{ item.label }}</span>
                  </button>
                  <button
                    v-for="sub in item.children"
                    :key="'dsub-' + sub.id"
                    type="button"
                    @click="navigateToTargetBlock(sub.target_block || 'about', sub.id); mobileMenuOpen = false"
                    class="btn btn-sm btn-light text-start rounded-3 py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                  >
                    <i class="bi" :class="sub.icon || 'bi-arrow-return-right'" :style="{ color: accentColor }"></i>
                    <span>{{ sub.label }}</span>
                  </button>
                </div>
              </transition>
            </div>
          </div>
        </div>

        <!-- Drawer Footer: Quick Actions & Socials -->
        <div class="pt-3 border-top mt-4">
          <div class="d-grid gap-2 mb-3">
            <button
              v-if="isBlockEnabled('contact')"
              type="button"
              @click="navigateWebsitePage('contact', 'portfolioContactSection'); mobileMenuOpen = false"
              class="btn rounded-pill py-2 fw-bold text-white shadow-sm"
              :style="{ backgroundColor: accentColor }"
            >
              <i class="bi bi-send-fill me-1"></i> {{ websiteNavbar.cta_text || 'Me contacter' }}
            </button>
            <div class="d-flex gap-2">
              <button v-if="vcardEnabled" type="button" @click="mobileMenuOpen = false; openVcardModal()" class="btn btn-sm btn-outline-dark rounded-pill flex-grow-1 fw-semibold">
                <i class="bi bi-qr-code me-1"></i> vCard
              </button>
              <a v-if="cvFileUrl" :href="cvFileUrl" target="_blank" download class="btn btn-sm btn-outline-danger rounded-pill flex-grow-1 fw-semibold text-decoration-none text-center">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> CV PDF
              </a>
            </div>
          </div>

          <div v-if="hasSocialLinks" class="d-flex align-items-center justify-content-center gap-2">
            <a v-if="socialLinks.github" :href="socialLinks.github" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;"><i class="bi bi-github"></i></a>
            <a v-if="socialLinks.linkedin" :href="socialLinks.linkedin" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;"><i class="bi bi-linkedin text-primary"></i></a>
            <a v-if="socialLinks.twitter" :href="socialLinks.twitter" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;"><i class="bi bi-twitter-x"></i></a>
            <a v-if="socialLinks.website" :href="socialLinks.website" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;"><i class="bi bi-globe2 text-info"></i></a>
          </div>
        </div>
      </aside>
    </div>

    <!-- TOP HERO BANNER SLIDESHOW / CAROUSEL (Custom Width, Height & Border Radius) -->
    <div 
      v-if="isSlideshowVisible" 
      class="portfolio-top-slideshow position-relative overflow-hidden d-print-none shadow-sm"
      :style="slideshowContainerStyle"
    >
      <div 
        v-for="(slide, sIdx) in slideshowImages" 
        :key="sIdx"
        class="slideshow-item position-absolute top-0 start-0 w-100 h-100"
        :style="{
          opacity: currentSlideIndex === sIdx ? '1' : '0',
          zIndex: currentSlideIndex === sIdx ? '2' : '1',
          pointerEvents: currentSlideIndex === sIdx ? 'auto' : 'none',
          transition: 'opacity 0.6s ease-in-out'
        }"
      >
        <component
          :is="slide.link ? 'a' : 'div'"
          :href="slide.link || undefined"
          :target="slide.link ? '_blank' : undefined"
          class="d-block w-100 h-100 position-relative text-decoration-none"
        >
          <img 
            :src="formatImageUrl(slide.image || slide.url || slide)" 
            :alt="slide.caption || 'Slide Tech237'" 
            class="w-100 h-100 object-fit-cover"
          />
          <div v-if="slide.caption" class="slideshow-caption position-absolute bottom-0 start-0 w-100 p-3 p-md-4 text-white text-center" style="background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.7) 100%);">
            <p class="mb-0 fs-6 fw-bold text-shadow">{{ slide.caption }}</p>
          </div>
        </component>
      </div>

      <!-- Navigation Arrows -->
      <button 
        v-if="slideshowImages.length > 1" 
        type="button" 
        @click="prevSlide" 
        class="slideshow-arrow prev btn btn-sm btn-dark bg-opacity-50 text-white rounded-circle position-absolute top-50 start-0 translate-middle-y ms-3 z-3 d-flex align-items-center justify-content-center"
        style="width: 40px; height: 40px;"
      >
        <i class="bi bi-chevron-left fs-5"></i>
      </button>
      <button 
        v-if="slideshowImages.length > 1" 
        type="button" 
        @click="nextSlide" 
        class="slideshow-arrow next btn btn-sm btn-dark bg-opacity-50 text-white rounded-circle position-absolute top-50 end-0 translate-middle-y me-3 z-3 d-flex align-items-center justify-content-center"
        style="width: 40px; height: 40px;"
      >
        <i class="bi bi-chevron-right fs-5"></i>
      </button>

      <!-- Dots Indicator -->
      <div v-if="slideshowImages.length > 1" class="position-absolute bottom-0 start-50 translate-middle-x mb-2 z-3 d-flex gap-2">
        <span 
          v-for="(_, dIdx) in slideshowImages" 
          :key="dIdx" 
          @click="currentSlideIndex = dIdx" 
          class="cursor-pointer rounded-pill transition"
          :style="{
            width: currentSlideIndex === dIdx ? '24px' : '8px',
            height: '8px',
            backgroundColor: currentSlideIndex === dIdx ? (accentColor || '#FFFFFF') : 'rgba(255,255,255,0.5)'
          }"
        ></span>
      </div>
    </div>

    <!-- MAIN BODY CONTAINER WITH PAGE ENTRY ANIMATION -->
    <div :class="['position-relative z-1', 'page-anim-' + pageAnimation]">

      <!-- MODE A: CV SPLIT LAYOUT (Left or Right Column) -->
      <main v-if="currentStructure === 'cv_split' || currentStructure === 'cv_split_right'" class="container py-4 py-md-5">
        <div class="row g-4" :class="currentStructure === 'cv_split_right' ? 'flex-row-reverse' : ''">
          
          <!-- Sticky Sidebar Profile Column (~35%) -->
          <aside class="col-12 col-lg-4">
            <div class="card p-4 rounded-4 shadow-sm border-0 content-card sticky-lg-top" style="top: 80px;">
              
              <!-- Avatar -->
              <div class="text-center mb-3">
                <div class="d-inline-block position-relative mb-2">
                  <img 
                    v-if="portfolioUser.avatar" 
                    :src="formatImageUrl(portfolioUser.avatar)" 
                    :alt="portfolioUser.name"
                    class="border border-4 border-white shadow-md hero-avatar mx-auto"
                    :class="[avatarShapeClass, avatarBorderClass]"
                  />
                  <div 
                    v-else 
                    class="border border-4 border-white shadow-md text-white fw-bold d-flex align-items-center justify-content-center hero-avatar mx-auto"
                    :class="[avatarShapeClass, avatarBorderClass]"
                    :style="{ backgroundColor: accentColor }"
                  >
                    {{ getInitials(portfolioUser.name) }}
                  </div>
                  <span v-if="showOnlinePulse" class="avatar-online-pulse-badge" title="En ligne & Actif"></span>
                </div>
                
                <h1 class="h4 fw-bold mb-1 user-name-heading">{{ portfolioUser.name }}</h1>
                <p class="small fw-semibold mb-2" :style="{ color: accentColor }">
                  <span>{{ displayedHeadline }}</span>
                  <span v-if="typewriterEnabled" class="typewriter-cursor">|</span>
                </p>
                
                <span class="badge rounded-pill px-3 py-1 availability-pill" :class="getAvailabilityClass(portfolioUser.availability)">
                  <span class="status-pulse-dot me-1"></span>
                  {{ getAvailabilityLabel(portfolioUser.availability) }}
                </span>

                <!-- Audio Pitch Button in Sidebar -->
                <div v-if="audioPitchEnabled && effectiveAudioPitchText" class="mt-2">
                  <button 
                    type="button" 
                    @click="handleAudioPitchClick" 
                    class="btn btn-xs rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-2xs"
                    :style="{ backgroundColor: accentColor, color: '#FFFFFF' }"
                  >
                    <i class="bi" :class="isPlaying && !isPaused ? 'bi-pause-circle-fill' : 'bi-mic-fill'"></i>
                    <span>{{ isPlaying && !isPaused ? 'Pause Pitch Vocal' : (isPaused ? 'Reprendre Pitch' : 'Écouter mon Pitch') }}</span>
                  </button>
                </div>
              </div>

              <!-- Quick Meta Details -->
              <div class="pt-3 border-top mb-3">
                <!-- Location & Google Maps -->
                <div v-if="portfolioUser.location" class="d-flex align-items-center gap-2 small text-muted mb-2">
                  <i class="bi bi-geo-alt-fill text-danger"></i>
                  <a v-if="googleMapsUrl" :href="googleMapsUrl" target="_blank" class="text-decoration-none text-muted fw-semibold">
                    {{ portfolioUser.location }} <i class="bi bi-box-arrow-up-right extra-small text-primary ms-1"></i>
                  </a>
                  <span v-else>{{ portfolioUser.location }}</span>
                </div>

                <div v-if="yearsOfExperience" class="d-flex align-items-center gap-2 small text-muted mb-2">
                  <i class="bi bi-briefcase-fill text-primary"></i>
                  <span><strong>Expérience :</strong> {{ yearsOfExperience }}</span>
                </div>

                <div class="d-flex align-items-center gap-2 small text-muted mb-2">
                  <i class="bi bi-trophy-fill text-warning"></i>
                  <span>{{ portfolioUser.rank_title || currentTier?.name || 'Novice Tech' }} &bull; {{ portfolioUser.xp || 0 }} XP</span>
                </div>

                <div v-if="portfolioUser.phone && portfolioUser.availability !== 'not_available'" class="d-flex align-items-center gap-2 small text-muted mb-2">
                  <i class="bi bi-whatsapp text-success"></i>
                  <span>{{ portfolioUser.phone }}</span>
                </div>
              </div>

              <!-- Skills in Sidebar -->
              <div v-if="portfolioUser.skills && portfolioUser.skills.length && isBlockEnabled('skills')" class="pt-3 border-top mb-3">
                <h3 class="h6 fw-bold mb-2">Compétences clés</h3>
                <div class="d-flex flex-wrap gap-1">
                  <span 
                    v-for="(sk, skIdx) in portfolioUser.skills" 
                    :key="skIdx"
                    class="badge rounded-pill px-2 py-1 extra-small fw-semibold shadow-2xs"
                    :style="{ backgroundColor: getSkillBg(skIdx, sk), color: getSkillColor(skIdx, sk) }"
                  >
                    <i v-if="typeof sk === 'object' && sk.icon" class="bi me-1" :class="sk.icon"></i>
                    {{ typeof sk === 'object' ? sk.name : sk }}
                    <small v-if="typeof sk === 'object' && sk.level" class="opacity-75 ms-1">&bull; {{ sk.level }}</small>
                  </span>
                </div>
              </div>

              <!-- Social links in Sidebar -->
              <div v-if="hasSocialLinks" class="pt-3 border-top d-flex gap-2 justify-content-center">
                <a v-if="socialLinks.github" :href="socialLinks.github" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                  <i class="bi bi-github"></i>
                </a>
                <a v-if="socialLinks.linkedin" :href="socialLinks.linkedin" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                  <i class="bi bi-linkedin text-primary"></i>
                </a>
                <a v-if="socialLinks.twitter" :href="socialLinks.twitter" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                  <i class="bi bi-twitter-x"></i>
                </a>
                <a v-if="socialLinks.website" :href="socialLinks.website" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                  <i class="bi bi-globe2 text-info"></i>
                </a>
              </div>

              <!-- CTA in Sidebar -->
              <div class="pt-3 border-top mt-3 d-grid gap-2 d-print-none">
                <a 
                  v-if="portfolioUser.phone && portfolioUser.availability !== 'not_available'" 
                  :href="'https://wa.me/' + cleanPhone(portfolioUser.phone)" 
                  target="_blank" 
                  class="btn btn-success rounded-pill fw-bold btn-sm py-2"
                >
                  <i class="bi bi-whatsapp me-1"></i> WhatsApp Direct
                </a>

                <!-- Download CV Button -->
                <a 
                  v-if="cvFileUrl" 
                  :href="cvFileUrl" 
                  target="_blank" 
                  download 
                  class="btn btn-outline-dark rounded-pill btn-sm py-2 fw-semibold text-decoration-none"
                >
                  <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Télécharger CV PDF
                </a>
                <button v-else type="button" @click="printCV" class="btn btn-outline-secondary rounded-pill btn-sm py-2">
                  <i class="bi bi-download me-1"></i> Imprimer CV PDF
                </button>
              </div>

            </div>
          </aside>

          <!-- Main Stream Column (~65%) -->
          <section class="col-12 col-lg-8 d-flex flex-column gap-4">
            
            <!-- Bio / About -->
            <div v-if="portfolioUser.bio && isBlockActiveInTab('about')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-lines-fill" :style="{ color: accentColor }"></i> À propos de moi
              </h2>
              <div class="text-content leading-relaxed mb-0" v-html="formatHtml(portfolioUser.bio)"></div>
            </div>

            <!-- YouTube Video Showcase in CV Split -->
            <div v-if="youtubeEmbedUrl && isBlockActiveInTab('video')" class="card p-4 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-play-btn-fill text-danger"></i> Vidéo de Présentation &amp; Pitch
              </h2>
              <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-xs border">
                <iframe :src="youtubeEmbedUrl" title="Vidéo présentation portfolio" allowfullscreen></iframe>
              </div>
            </div>

            <!-- Experiences -->
            <div v-if="portfolioUser.experiences && portfolioUser.experiences.length && isBlockActiveInTab('experiences')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-briefcase-fill" :style="{ color: accentColor }"></i> Parcours Professionnel
              </h2>
              <div class="timeline-stream d-flex flex-column gap-4">
                <div v-for="(exp, eIdx) in portfolioUser.experiences" :key="eIdx" class="d-flex gap-3 position-relative">
                  <div class="timeline-dot rounded-circle flex-shrink-0 mt-1" :style="{ backgroundColor: accentColor }"></div>
                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                      <strong class="text-dark">{{ exp.role }} &bull; <span class="fw-semibold text-muted">{{ exp.company }}</span></strong>
                      <span class="badge bg-light text-muted border extra-small">{{ exp.period }}</span>
                    </div>
                    <div v-if="exp.description" class="small text-muted mb-0 leading-relaxed mt-2" v-html="formatHtml(exp.description)"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Projects -->
            <div v-if="projects && projects.length && isBlockActiveInTab('projects')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-4 d-flex align-items-center justify-content-between">
                <span class="d-flex align-items-center gap-2">
                  <i class="bi bi-rocket-takeoff-fill" :style="{ color: accentColor }"></i> Projets &amp; Réalisations
                </span>
                <span class="badge bg-primary-subtle text-primary rounded-pill extra-small">{{ projects.length }} projet(s)</span>
              </h2>

              <!-- Slider or Multi-Column Grid -->
              <div v-if="projectsStyle === 'carousel'">
                <div v-if="projects[currentProjectSlide]" class="p-3 rounded-4 bg-light border">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-3">
                      <img 
                        v-if="projects[currentProjectSlide].logo" 
                        :src="formatImageUrl(projects[currentProjectSlide].logo)" 
                        class="rounded-3 border" 
                        style="width: 48px; height: 48px; object-fit: contain;" 
                        :alt="projects[currentProjectSlide].name"
                      />
                      <div>
                        <h3 class="h6 fw-bold mb-0">{{ projects[currentProjectSlide].name }}</h3>
                        <small class="text-muted extra-small">{{ projects[currentProjectSlide].category }}</small>
                      </div>
                    </div>
                    <span class="badge bg-white text-muted border extra-small">{{ currentProjectSlide + 1 }} / {{ projects.length }}</span>
                  </div>
                  <div class="small text-muted mb-3" v-html="formatHtml(projects[currentProjectSlide].pitch || projects[currentProjectSlide].description)"></div>
                  <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <div class="d-flex gap-1">
                      <button type="button" @click="prevProject" class="btn btn-xs btn-outline-secondary rounded-circle px-2 py-1"><i class="bi bi-chevron-left"></i></button>
                      <button type="button" @click="nextProject" class="btn btn-xs btn-outline-secondary rounded-circle px-2 py-1"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <a :href="'/projet/' + (projects[currentProjectSlide].slug || projects[currentProjectSlide].id)" target="_blank" class="btn btn-xs btn-dark rounded-pill px-3 fw-bold">Voir fiche &rarr;</a>
                  </div>
                </div>
              </div>
              <div v-else class="row g-3">
                <div v-for="proj in projects" :key="proj.id" :class="projectColsClass">
                  <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-between">
                    <div>
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <img 
                          v-if="proj.logo" 
                          :src="formatImageUrl(proj.logo)" 
                          class="rounded-2 border flex-shrink-0" 
                          style="width: 36px; height: 36px; object-fit: contain;" 
                          :alt="proj.name"
                        />
                        <div v-else class="rounded-2 text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;" :style="{ backgroundColor: accentColor }">
                          <i class="bi bi-rocket-takeoff" style="font-size: 14px;"></i>
                        </div>
                        <div class="min-w-0">
                          <strong class="d-block small text-dark text-truncate">{{ proj.name }}</strong>
                          <span class="extra-small text-muted">{{ proj.category }}</span>
                        </div>
                      </div>
                      <div class="extra-small text-muted mb-2 leading-relaxed" v-html="formatHtml(proj.pitch || proj.description)"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                      <span class="extra-small text-muted"><i class="bi bi-eye me-1"></i>{{ proj.views_count || 0 }}</span>
                      <a :href="'/projet/' + (proj.slug || proj.id)" target="_blank" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-0 extra-small">Fiche</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Educations -->
            <div v-if="portfolioUser.educations && portfolioUser.educations.length && isBlockActiveInTab('educations')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-mortarboard-fill" :style="{ color: accentColor }"></i> Formations &amp; Certifications
              </h2>
              <div class="row g-3">
                <div v-for="(edu, dIdx) in portfolioUser.educations" :key="dIdx" class="col-md-6">
                  <div class="p-3 rounded-3 bg-light border h-100">
                    <span class="badge bg-white text-dark border extra-small mb-1">{{ edu.year }}</span>
                    <strong class="d-block small text-dark fw-bold">{{ edu.degree }}</strong>
                    <span class="d-block extra-small text-muted mb-1">{{ edu.institution }}</span>
                    <div v-if="edu.description" class="extra-small text-muted mb-0" v-html="formatHtml(edu.description)"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Custom sections -->
            <div v-for="(sec, cIdx) in portfolioUser.custom_sections" :key="cIdx" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi" :class="sec.icon || 'bi-stars'" :style="{ color: accentColor }"></i> {{ sec.title }}
              </h2>
              <div class="text-content leading-relaxed" v-html="formatHtml(sec.content)"></div>
            </div>

            <!-- Now Section in CV Split -->
            <div v-if="hasNowSection && isBlockActiveInTab('now')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-compass-fill" :style="{ color: accentColor }"></i> En ce moment (Now)
              </h2>
              <div class="row g-3">
                <div v-if="nowSection.project" class="col-md-4">
                  <div class="p-3 rounded-4 bg-light border h-100">
                    <span class="badge bg-primary-subtle text-primary extra-small mb-1">🔨 Projet actuel</span>
                    <p class="small fw-semibold text-dark mb-0">{{ nowSection.project }}</p>
                  </div>
                </div>
                <div v-if="nowSection.learning" class="col-md-4">
                  <div class="p-3 rounded-4 bg-light border h-100">
                    <span class="badge bg-success-subtle text-success extra-small mb-1">📚 En apprentissage</span>
                    <p class="small fw-semibold text-dark mb-0">{{ nowSection.learning }}</p>
                  </div>
                </div>
                <div v-if="nowSection.goal" class="col-md-4">
                  <div class="p-3 rounded-4 bg-light border h-100">
                    <span class="badge bg-warning-subtle text-warning-emphasis extra-small mb-1">🎯 Objectif</span>
                    <p class="small fw-semibold text-dark mb-0">{{ nowSection.goal }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- GitHub Live Stats in CV Split -->
            <div v-if="hasGithubStats && isBlockActiveInTab('github')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h2 class="h5 fw-bold mb-0 d-flex align-items-center gap-2">
                  <i class="bi bi-github"></i> Activité Open-Source &amp; GitHub
                </h2>
                <a :href="'https://github.com/' + githubUsername" target="_blank" class="btn btn-xs btn-dark rounded-pill px-3 extra-small fw-bold">
                  @{{ githubUsername }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
              </div>
              <div v-if="githubProfile" class="row g-3 mb-3">
                <div class="col-4">
                  <div class="p-3 rounded-4 bg-light border text-center">
                    <strong class="fs-5 fw-black d-block" :style="{ color: accentColor }">{{ githubProfile.public_repos || 0 }}</strong>
                    <span class="extra-small text-muted">Dépôts Publics</span>
                  </div>
                </div>
                <div class="col-4">
                  <div class="p-3 rounded-4 bg-light border text-center">
                    <strong class="fs-5 fw-black d-block" :style="{ color: accentColor }">{{ githubTotalStars }}</strong>
                    <span class="extra-small text-muted">Stars cumulées</span>
                  </div>
                </div>
                <div class="col-4">
                  <div class="p-3 rounded-4 bg-light border text-center">
                    <strong class="fs-5 fw-black d-block" :style="{ color: accentColor }">{{ githubProfile.followers || 0 }}</strong>
                    <span class="extra-small text-muted">Followers</span>
                  </div>
                </div>
              </div>
              <div v-if="githubTopLanguages.length" class="mb-3">
                <span class="extra-small fw-bold text-muted d-block mb-2">Langages dominants :</span>
                <div class="d-flex flex-wrap gap-2">
                  <span v-for="lang in githubTopLanguages" :key="lang.name" class="badge bg-light text-dark border rounded-pill px-3 py-1 extra-small fw-semibold">
                    <i class="bi bi-code-slash me-1" :style="{ color: accentColor }"></i>{{ lang.name }} ({{ lang.percent }}%)
                  </span>
                </div>
              </div>
              <div v-if="githubRepos.length" class="row g-2">
                <div v-for="repo in githubRepos.slice(0, 4)" :key="repo.id" class="col-md-6">
                  <a :href="repo.html_url" target="_blank" class="p-3 rounded-3 bg-light border d-block text-decoration-none h-100 transition-hover">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <strong class="small text-dark text-truncate"><i class="bi bi-journal-code me-1"></i>{{ repo.name }}</strong>
                      <span class="badge bg-white text-dark border extra-small">⭐ {{ repo.stargazers_count || 0 }}</span>
                    </div>
                    <p class="extra-small text-muted mb-1 text-truncate">{{ repo.description || 'Dépôt open-source sur GitHub' }}</p>
                    <span v-if="repo.language" class="badge bg-secondary-subtle text-secondary extra-small">{{ repo.language }}</span>
                  </a>
                </div>
              </div>
            </div>

            <!-- Certifications in CV Split -->
            <div v-if="certificationsList.length && isBlockActiveInTab('certifications')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-patch-check-fill" :style="{ color: accentColor }"></i> Certifications Vérifiées &amp; Prix
              </h2>
              <div class="row g-3">
                <div v-for="(cert, cIdx) in certificationsList" :key="cIdx" class="col-md-6">
                  <div class="p-3 rounded-4 bg-light border h-100 d-flex flex-column justify-content-between">
                    <div>
                      <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge bg-info-subtle text-info-emphasis extra-small"><i class="bi bi-award me-1"></i>{{ cert.issuer || 'Certificat Officiel' }}</span>
                        <span v-if="cert.year" class="extra-small text-muted fw-bold">{{ cert.year }}</span>
                      </div>
                      <strong class="d-block small text-dark fw-bold mt-1">{{ cert.title }}</strong>
                    </div>
                    <div v-if="cert.credential_url" class="mt-2 pt-2 border-top">
                      <a :href="cert.credential_url" target="_blank" class="extra-small fw-bold text-decoration-none" :style="{ color: accentColor }">
                        Vérifier l'authenticité <i class="bi bi-box-arrow-up-right ms-1"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pricing Packages in CV Split -->
            <div v-if="pricingPackagesList.length && isBlockActiveInTab('pricing')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-tags-fill" :style="{ color: accentColor }"></i> Mes Offres &amp; Grille Tarifaire
              </h2>
              <div class="row g-3">
                <div v-for="(pkg, pIdx) in pricingPackagesList" :key="pIdx" class="col-md-6">
                  <div class="p-4 rounded-4 border h-100 d-flex flex-column justify-content-between position-relative" :class="pkg.is_popular ? 'bg-white shadow-sm border-2' : 'bg-light'" :style="pkg.is_popular ? { borderColor: accentColor } : {}">
                    <div>
                      <span v-if="pkg.is_popular" class="badge rounded-pill text-white extra-small position-absolute top-0 end-0 m-3" :style="{ backgroundColor: accentColor }">⭐ Populaire</span>
                      <h3 class="h6 fw-bold text-dark mb-1">{{ pkg.title }}</h3>
                      <div class="my-2">
                        <span class="fs-4 fw-black" :style="{ color: accentColor }">{{ pkg.price }}</span>
                        <span v-if="pkg.unit" class="extra-small text-muted ms-1">{{ pkg.unit }}</span>
                      </div>
                      <ul class="list-unstyled extra-small text-muted d-flex flex-column gap-1 mb-3">
                        <li v-for="(feat, fIdx) in parsePackageFeatures(pkg.features)" :key="fIdx" class="d-flex align-items-start gap-2">
                          <i class="bi bi-check-circle-fill text-success mt-1"></i>
                          <span>{{ feat }}</span>
                        </li>
                      </ul>
                    </div>
                    <button type="button" @click="selectPackageForContact(pkg)" class="btn btn-sm rounded-pill w-100 fw-bold text-white" :style="{ backgroundColor: accentColor }">
                      Commander ce pack &rarr;
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Testimonials in CV Split -->
            <div v-if="testimonialsList.length && isBlockActiveInTab('testimonials')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-chat-quote-fill" :style="{ color: accentColor }"></i> Témoignages &amp; Recommandations
              </h2>
              <div class="row g-3">
                <div v-for="(testi, tIdx) in testimonialsList" :key="tIdx" class="col-md-6">
                  <div class="p-3 rounded-4 bg-light border h-100 d-flex flex-column justify-content-between">
                    <div>
                      <div class="text-warning extra-small mb-2">
                        <i v-for="star in (Number(testi.rating) || 5)" :key="star" class="bi bi-star-fill me-1"></i>
                      </div>
                      <p class="small text-dark fst-italic mb-3">« {{ testi.comment }} »</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                      <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center extra-small flex-shrink-0" style="width: 32px; height: 32px;" :style="{ backgroundColor: accentColor }">
                        {{ getInitials(testi.author_name) }}
                      </div>
                      <div class="min-w-0">
                        <strong class="d-block extra-small text-dark text-truncate">{{ testi.author_name }}</strong>
                        <span class="extra-small text-muted d-block text-truncate">{{ testi.author_role }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact Block in CV Split -->
            <div v-if="isBlockActiveInTab('contact')" id="portfolioContactSection" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
              <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-envelope-fill" :style="{ color: accentColor }"></i> Me Contacter Directement
              </h2>
              <contact-form-component 
                :recipient-username="portfolioUser.username" 
                :accent-color="accentColor" 
              />
            </div>

          </section>

        </div>
      </main>

      <!-- MODE B: STANDARD, BENTO, TERMINAL & ALL OTHER 28 STRUCTURES -->
      <main v-else class="container py-4 py-md-5" :style="{ maxWidth: containerMaxWidth }">
        
        <!-- Terminal Super Window Wrapper (If Structure === 'terminal') -->
        <div v-if="currentStructure === 'terminal'" class="terminal-super-window rounded-4 shadow-lg mb-4 overflow-hidden border border-secondary border-opacity-50">
          <div class="terminal-bar bg-dark px-3 py-2 d-flex align-items-center justify-content-between border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2">
              <span class="rounded-circle bg-danger d-inline-block" style="width: 12px; height: 12px;"></span>
              <span class="rounded-circle bg-warning d-inline-block" style="width: 12px; height: 12px;"></span>
              <span class="rounded-circle bg-success d-inline-block" style="width: 12px; height: 12px;"></span>
            </div>
            <span class="font-monospace extra-small text-white-50">bash — {{ portfolioUser.username }}@tech237:~$ portfolio</span>
            <span class="badge bg-secondary extra-small font-monospace">ONLINE</span>
          </div>
        </div>

        <!-- Code Editor IDE Window Wrapper (If Structure === 'code_editor') -->
        <div v-if="currentStructure === 'code_editor'" class="ide-super-window rounded-4 shadow-lg mb-4 overflow-hidden border border-secondary border-opacity-50">
          <div class="ide-tab-bar bg-dark px-3 py-2 d-flex align-items-center justify-content-between border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-primary-subtle text-primary extra-small font-monospace"><i class="bi bi-file-code me-1"></i>portfolio.vue</span>
              <span class="badge bg-secondary-subtle text-muted extra-small font-monospace d-none d-sm-inline">skills.json</span>
              <span class="badge bg-secondary-subtle text-muted extra-small font-monospace d-none d-sm-inline">projects.ts</span>
            </div>
            <span class="badge bg-success-subtle text-success extra-small font-monospace"><i class="bi bi-check2-all me-1"></i>0 Errors</span>
          </div>
        </div>

        <!-- BLANK CANVAS PAGE BUILDER WIDGETS (When Feuille Blanche is active) -->
        <div v-if="isBlankCanvasMode" class="mb-4">
          <!-- Owner Floating Studio Bar on Blank Canvas -->
          <div v-if="isOwner" class="p-3 mb-4 rounded-4 bg-dark text-white d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-sm d-print-none border border-secondary border-opacity-50">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-primary text-white rounded-pill px-3 py-1 extra-small fw-bold">📄 Mode Feuille Blanche Actif</span>
              <span class="extra-small text-white-50">{{ blankCanvasWidgets.length }} bloc(s) personnalisé(s) actif(s)</span>
            </div>
            <a href="/espace-utilisateur?section=portfolio" class="btn btn-xs btn-success rounded-pill px-3 py-1 fw-bold text-white text-decoration-none">
              <i class="bi bi-sliders me-1"></i> Gérer mes Blocs Feuille Blanche
            </a>
          </div>

          <!-- Empty Blank Sheet Notice (when 0 widgets and default hero hidden) -->
          <div v-if="blankCanvasWidgets.length === 0 && blankCanvasConfig.hide_default_hero" class="p-5 rounded-4 border border-2 border-dashed text-center content-card my-4">
            <i class="bi bi-file-earmark-plus display-4 d-block mb-3" :style="{ color: accentColor }"></i>
            <h2 class="h4 fw-bold mb-2">Feuille Blanche Vierge</h2>
            <p class="text-muted small mb-3 mx-auto" style="max-width: 520px;">
              Cet espace a été réinitialisé à zéro pour une création 100 % sur-mesure. Ajoutez vos bannières Hero, textes, grilles de cartes, images, terminaux de code et sections depuis votre studio.
            </p>
            <a v-if="isOwner" href="/espace-utilisateur?section=portfolio" class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm" :style="{ backgroundColor: accentColor }">
              <i class="bi bi-plus-circle me-1"></i> Ajouter mon premier bloc
            </a>
          </div>

          <!-- Render Custom Blank Canvas Widgets -->
          <div v-if="customOnlyCanvasWidgets.length > 0" class="row g-4">
            <div 
              v-for="(w, wIdx) in customOnlyCanvasWidgets" 
              :key="w.id || wIdx" 
              :class="getCanvasWidgetColClass(w.width)"
            >
              <!-- Spacer / Divider Widget -->
              <div v-if="w.type === 'spacer_divider'" :style="{ padding: ((w.spacer_height || 48) / 2) + 'px 0' }">
                <div v-if="w.divider_style === 'neon_line'" class="section-divider-neon" :style="{ '--accent-color': accentColor }"></div>
                <div v-else-if="w.divider_style === 'tech_dots'" class="d-flex align-items-center justify-content-center gap-3">
                  <span class="rounded-circle" :style="{ width: '8px', height: '8px', backgroundColor: accentColor }"></span>
                  <span class="rounded-circle" :style="{ width: '12px', height: '12px', backgroundColor: accentColor }"></span>
                  <span class="rounded-circle" :style="{ width: '8px', height: '8px', backgroundColor: accentColor }"></span>
                </div>
                <div v-else-if="w.divider_style === 'svg_wave'" class="text-center overflow-hidden" style="height: 32px;">
                  <svg viewBox="0 0 1200 60" preserveAspectRatio="none" class="w-100 h-100">
                    <path d="M0,30 C300,60 900,0 1200,30 L1200,60 L0,60 Z" :fill="accentColor" fill-opacity="0.18"></path>
                  </svg>
                </div>
              </div>

              <!-- Standard Custom Canvas Widget Card -->
              <div 
                v-else 
                class="h-100 position-relative overflow-hidden transition"
                :class="[getCanvasWidgetBoxClass(w), getCanvasWidgetPaddingClass(w)]"
                :style="getCanvasWidgetInlineStyle(w)"
              >
                <!-- 1. CUSTOM HERO WIDGET -->
                <div v-if="w.type === 'custom_hero'">
                  <div class="row align-items-center g-4" :class="w.hero_layout === 'split_left' ? 'flex-row-reverse' : ''">
                    <div :class="w.image_url && w.hero_layout !== 'centered' ? 'col-lg-7' : 'col-12'" :style="{ textAlign: w.hero_layout === 'centered' ? 'center' : (w.text_align || 'left') }">
                      <span v-if="w.badge" class="badge rounded-pill px-3 py-1 fw-bold extra-small mb-3" :style="{ backgroundColor: accentColor, color: '#FFFFFF' }">
                        {{ w.badge }}
                      </span>
                      <h1 class="display-5 fw-bold mb-3">{{ w.title }}</h1>
                      <p v-if="w.subtitle" class="fs-5 opacity-85 mb-4 leading-relaxed">{{ w.subtitle }}</p>
                      <div class="d-flex flex-wrap gap-2" :class="w.hero_layout === 'centered' || w.text_align === 'center' ? 'justify-content-center' : ''">
                        <a v-if="w.cta_primary_text" :href="w.cta_primary_link || '#portfolioContactSection'" class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm" :style="{ backgroundColor: accentColor }">
                          {{ w.cta_primary_text }}
                        </a>
                        <a v-if="w.cta_secondary_text" :href="w.cta_secondary_link || '#'" class="btn btn-outline-light border rounded-pill px-4 py-2 fw-bold">
                          {{ w.cta_secondary_text }}
                        </a>
                      </div>
                    </div>
                    <div v-if="w.image_url" :class="w.hero_layout === 'centered' ? 'col-12 mt-3 text-center' : 'col-lg-5 text-center'">
                      <img :src="formatImageUrl(w.image_url)" class="img-fluid rounded-4 shadow-lg object-fit-cover" style="max-height: 360px;" :alt="w.title" />
                    </div>
                  </div>
                </div>

                <!-- 2. RICH TEXT WIDGET -->
                <div v-else-if="w.type === 'rich_text'" :style="{ textAlign: w.text_align || 'left' }">
                  <h2 v-if="w.title" class="h4 fw-bold mb-1">{{ w.title }}</h2>
                  <p v-if="w.subtitle" class="small opacity-75 mb-3">{{ w.subtitle }}</p>
                  <blockquote v-if="w.quote" class="p-3 rounded-4 mb-3 fst-italic fw-semibold border-start border-4" :style="{ borderColor: accentColor + ' !important', backgroundColor: 'rgba(16,185,129,0.06)' }">
                    « {{ w.quote }} »
                  </blockquote>
                  <div class="leading-relaxed" :class="w.columns === 2 ? 'about-two-columns' : ''" v-html="formatHtml(w.content)"></div>
                </div>

                <!-- 3. FEATURE CARDS WIDGET -->
                <div v-else-if="w.type === 'feature_cards'" :style="{ textAlign: w.text_align || 'left' }">
                  <h2 v-if="w.title" class="h4 fw-bold mb-1">{{ w.title }}</h2>
                  <p v-if="w.subtitle" class="small opacity-75 mb-4">{{ w.subtitle }}</p>
                  <div class="row g-3">
                    <div v-for="(c, cIdx) in (w.cards || [])" :key="cIdx" :class="w.columns === 4 ? 'col-sm-6 col-lg-3' : (w.columns === 2 ? 'col-md-6' : 'col-md-4')">
                      <div class="p-3 p-md-4 rounded-4 border h-100 elem-anim-lift bg-white bg-opacity-10">
                        <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-3 text-white shadow-2xs" :style="{ width: '44px', height: '44px', backgroundColor: c.color || accentColor }">
                          <i class="bi fs-5" :class="c.icon || 'bi-stars'"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-2">{{ c.title }}</h3>
                        <p class="small opacity-80 mb-0 leading-relaxed">{{ c.text }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 4. IMAGE BANNER WIDGET -->
                <div v-else-if="w.type === 'image_banner'" :style="{ textAlign: w.text_align || 'center' }">
                  <h2 v-if="w.title" class="h5 fw-bold mb-3">{{ w.title }}</h2>
                  <div v-if="w.image_url" class="rounded-4 overflow-hidden shadow-sm">
                    <img :src="formatImageUrl(w.image_url)" class="w-100 object-fit-cover" :style="{ height: w.image_height && w.image_height !== 'auto' ? w.image_height : 'auto' }" :alt="w.caption || w.title" />
                  </div>
                  <p v-if="w.caption" class="extra-small opacity-75 mt-2 mb-0 fst-italic">{{ w.caption }}</p>
                </div>

                <!-- 5. CTA BANNER WIDGET -->
                <div v-else-if="w.type === 'cta_banner'" :style="{ textAlign: w.text_align || 'center' }">
                  <h2 class="h3 fw-bold mb-2">{{ w.title }}</h2>
                  <p v-if="w.subtitle" class="mb-4 opacity-90">{{ w.subtitle }}</p>
                  <a v-if="w.button_text" :href="w.button_link || '#portfolioContactSection'" class="btn btn-light rounded-pill px-4 py-2 fw-bold shadow-sm">
                    {{ w.button_text }} <i class="bi bi-arrow-right ms-1"></i>
                  </a>
                </div>

                <!-- 6. STATS COUNTER WIDGET -->
                <div v-else-if="w.type === 'stats_counter'" :style="{ textAlign: w.text_align || 'center' }">
                  <h2 v-if="w.title" class="h5 fw-bold mb-3">{{ w.title }}</h2>
                  <div class="row g-3 justify-content-center">
                    <div v-for="(st, stIdx) in (w.stats || [])" :key="stIdx" class="col-6 col-md-3">
                      <div class="p-3 rounded-4 border text-center h-100 elem-anim-lift">
                        <i v-if="st.icon" class="bi fs-4 d-block mb-1" :class="st.icon" :style="{ color: st.color || accentColor }"></i>
                        <div class="fs-2 fw-black" :style="{ color: st.color || accentColor }">{{ st.value }}</div>
                        <div class="extra-small fw-semibold opacity-80">{{ st.label }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 7. CODE TERMINAL WIDGET -->
                <div v-else-if="w.type === 'code_terminal'">
                  <h2 v-if="w.title" class="h5 fw-bold mb-3">{{ w.title }}</h2>
                  <div class="rounded-4 overflow-hidden shadow-lg border border-secondary border-opacity-25" :style="{ backgroundColor: w.terminal_theme === 'matrix_green' ? '#021a09' : '#0F172A' }">
                    <div class="px-3 py-2 bg-black bg-opacity-50 d-flex align-items-center justify-content-between border-bottom border-secondary border-opacity-25">
                      <div class="d-flex gap-2">
                        <span class="rounded-circle bg-danger d-inline-block" style="width: 11px; height: 11px;"></span>
                        <span class="rounded-circle bg-warning d-inline-block" style="width: 11px; height: 11px;"></span>
                        <span class="rounded-circle bg-success d-inline-block" style="width: 11px; height: 11px;"></span>
                      </div>
                      <span class="extra-small font-monospace text-white-50">{{ w.terminal_title || 'terminal.sh' }}</span>
                      <span></span>
                    </div>
                    <pre class="p-3 p-md-4 mb-0 font-monospace small overflow-auto" :style="{ color: w.terminal_theme === 'matrix_green' ? '#4ADE80' : (w.terminal_theme === 'cyberpunk' ? '#38BDF8' : '#E2E8F0') }"><code>{{ w.code }}</code></pre>
                  </div>
                </div>

                <!-- 8. VIDEO EMBED WIDGET -->
                <div v-else-if="w.type === 'video_embed'">
                  <h2 v-if="w.title" class="h5 fw-bold mb-3">{{ w.title }}</h2>
                  <div v-if="extractYouTubeEmbedUrl(w.video_url)" class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm">
                    <iframe :src="extractYouTubeEmbedUrl(w.video_url)" allowfullscreen></iframe>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bento Grid or Single Stream -->
        <div :class="isBentoGrid ? 'row g-4' : 'd-flex flex-column gap-4'">
          
          <template v-for="block in orderedBlocks" :key="block.id">
            
            <div 
              v-if="block.enabled && shouldRenderBlock(block.id) && isBlockActiveInTab(block.id)" 
              :id="'block-' + block.id"
              :class="[getBlockGridClass(block), 'block-model-' + getBlockModel(block.id)]"
            >
              <!-- 1. HERO / HEADER BLOCK -->
              <section v-if="block.id === 'hero'" id="block-hero" class="portfolio-block h-100" :class="'hero-model-' + getBlockModel('hero')">

                <!-- NEW HERO MODEL: IMPACT SPLIT / IMPACT SPLIT REVERSE (Style ONG / Agence Moderne) -->
                <div
                  v-if="getBlockModel('hero') === 'impact_split' || getBlockModel('hero') === 'impact_split_reverse'"
                  class="hero-box p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden h-100 border-0"
                  :style="{ background: `linear-gradient(135deg, ${accentColor}12 0%, #FFFFFF 55%, #FEF9C3 100%)` }"
                >
                  <div class="row align-items-center g-4 g-lg-5" :class="getBlockModel('hero') === 'impact_split_reverse' ? 'flex-lg-row-reverse' : ''">
                    <div class="col-lg-6 text-center text-lg-start">
                      <span class="badge bg-white text-dark border shadow-2xs rounded-pill px-3 py-2 mb-3 d-inline-flex align-items-center gap-2 extra-small fw-bold">
                        <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px;" :style="{ backgroundColor: accentColor }"></span>
                        <span>{{ portfolioUser.rank_title || 'Profil Certifié Tech237' }} &bull; {{ portfolioUser.location || 'Tech & Innovation' }}</span>
                      </span>
                      <h1 class="display-5 fw-black text-dark mb-3 lh-sm">
                        {{ portfolioUser.name }} — <span :style="{ color: accentColor }">{{ displayedHeadline || 'Créons votre prochain succès digital' }}</span>
                      </h1>
                      <p v-if="portfolioUser.bio" class="text-muted mb-4 leading-relaxed">
                        {{ stripHtmlTags(portfolioUser.bio).slice(0, 220) }}{{ stripHtmlTags(portfolioUser.bio).length > 220 ? '...' : '' }}
                      </p>
                      <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-2 mb-4 d-print-none">
                        <button
                          type="button"
                          @click="navigateToTargetBlock('contact', 'contact')"
                          class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2"
                          :style="{ backgroundColor: accentColor }"
                        >
                          <i class="bi bi-heart-fill"></i>
                          <span>{{ websiteNavbar.cta_text || 'Démarrer un projet' }}</span>
                        </button>
                        <button
                          v-if="projects && projects.length"
                          type="button"
                          @click="navigateToTargetBlock('projects', 'projects')"
                          class="btn btn-outline-success rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2"
                        >
                          <span>Découvrir mes réalisations</span>
                          <i class="bi bi-arrow-right"></i>
                        </button>
                      </div>
                      <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-2 extra-small text-muted">
                        <div class="d-flex">
                          <span class="rounded-circle text-white fw-bold d-inline-flex align-items-center justify-content-center border border-2 border-white" style="width: 28px; height: 28px; background: #16A34A;">{{ getInitials(portfolioUser.name) }}</span>
                          <span class="rounded-circle text-white fw-bold d-inline-flex align-items-center justify-content-center border border-2 border-white ms-n2" style="width: 28px; height: 28px; background: #F59E0B;">★</span>
                          <span class="rounded-circle text-white fw-bold d-inline-flex align-items-center justify-content-center border border-2 border-white ms-n2" style="width: 28px; height: 28px; background: #0284C7;">✓</span>
                        </div>
                        <span><strong>{{ projects?.length || 10 }}+ projets</strong> &bull; {{ portfolioUser.xp || 100 }} XP sur Tech237</span>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="position-relative p-2">
                        <img
                          v-if="portfolioUser.avatar"
                          :src="formatImageUrl(portfolioUser.avatar)"
                          :alt="portfolioUser.name"
                          class="w-100 rounded-4 shadow-lg object-fit-cover"
                          style="height: 360px;"
                        />
                        <div v-else class="w-100 rounded-4 shadow-lg d-flex align-items-center justify-content-center text-white display-1 fw-black" style="height: 360px;" :style="{ backgroundColor: accentColor }">
                          {{ getInitials(portfolioUser.name) }}
                        </div>
                        <!-- Floating Badge Top-Right -->
                        <div class="position-absolute top-0 end-0 m-3 bg-white rounded-4 shadow p-2 px-3 d-flex align-items-center gap-2">
                          <span class="rounded-circle text-white d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" :style="{ backgroundColor: accentColor }">
                            <i class="bi bi-lightning-charge-fill"></i>
                          </span>
                          <div class="lh-1">
                            <strong class="extra-small d-block text-dark">Impact 100% Terrain</strong>
                            <span class="text-muted" style="font-size: 10px;">{{ getAvailabilityLabel(portfolioUser.availability) }}</span>
                          </div>
                        </div>
                        <!-- Floating Badge Bottom-Left -->
                        <div class="position-absolute bottom-0 start-0 m-3 bg-white rounded-4 shadow p-2 px-3 d-flex align-items-center gap-2">
                          <i class="bi bi-patch-check-fill fs-5" :style="{ color: accentColor }"></i>
                          <div class="lh-1">
                            <strong class="extra-small d-block text-dark">{{ portfolioUser.name }}</strong>
                            <span class="text-muted" style="font-size: 10px;">{{ yearsOfExperience || 'Expertise Confirmée' }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Variant: Agency Landing 2-Columns (Real Website Hero) -->
                <div v-else-if="heroStyle === 'agency_landing'" class="hero-box hero-agency-landing p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden h-100">
                  <div class="hero-glow-orb"></div>
                  <div class="row align-items-center g-4 g-lg-5 position-relative z-1">
                    <!-- Left Column: Marketing Pitch, Headline, CTAs -->
                    <div class="col-lg-7 text-center text-lg-start">
                      <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 availability-pill" :class="getAvailabilityClass(portfolioUser.availability)">
                        <span class="status-pulse-dot"></span>
                        <span>{{ getAvailabilityLabel(portfolioUser.availability) }}</span>
                      </div>

                      <h1 class="display-5 fw-black mb-2 user-name-heading lh-sm">
                        {{ portfolioUser.name }}
                      </h1>

                      <p class="fs-4 fw-bold mb-3 user-headline-text" :style="{ color: accentColor }">
                        <span>{{ displayedHeadline }}</span>
                        <span v-if="typewriterEnabled" class="typewriter-cursor">|</span>
                      </p>

                      <p v-if="portfolioUser.bio" class="text-muted mb-4 leading-relaxed" style="font-size: 1.02rem;">
                        {{ stripHtmlTags(portfolioUser.bio).slice(0, 240) }}{{ stripHtmlTags(portfolioUser.bio).length > 240 ? '...' : '' }}
                      </p>

                      <!-- Primary & Secondary Website CTA Buttons -->
                      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-2 mb-4 d-print-none">
                        <button 
                          type="button"
                          @click="navigateWebsitePage('contact', 'portfolioContactSection')"
                          class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2"
                          :style="{ backgroundColor: accentColor }"
                        >
                          <i class="bi bi-send-fill"></i>
                          <span>{{ websiteNavbar.cta_text || 'Démarrer un projet' }}</span>
                        </button>

                        <button 
                          v-if="projects && projects.length"
                          type="button"
                          @click="navigateWebsitePage('projects', 'block-projects')"
                          class="btn btn-outline-dark rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2"
                        >
                          <i class="bi bi-grid-1x2-fill"></i>
                          <span>Voir mes réalisations ({{ projects.length }})</span>
                        </button>

                        <button 
                          v-if="audioPitchEnabled && effectiveAudioPitchText" 
                          type="button" 
                          @click="handleAudioPitchClick" 
                          class="btn btn-light border rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-2xs"
                        >
                          <i class="bi text-success" :class="isPlaying && !isPaused ? 'bi-pause-circle-fill' : 'bi-mic-fill'"></i>
                          <span>{{ isPlaying && !isPaused ? 'Pause Pitch' : (isPaused ? 'Reprendre' : 'Pitch Vocal') }}</span>
                        </button>

                        <button 
                          v-if="vcardEnabled" 
                          type="button" 
                          @click="openVcardModal" 
                          class="btn btn-light border rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-2xs"
                        >
                          <i class="bi bi-qr-code text-primary"></i>
                          <span>vCard</span>
                        </button>
                      </div>

                      <!-- Quick Meta Badges -->
                      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-2">
                        <span v-if="portfolioUser.location" class="badge rounded-pill px-3 py-2 meta-pill">
                          <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ portfolioUser.location }}
                        </span>
                        <span v-if="yearsOfExperience" class="badge rounded-pill px-3 py-2 meta-pill">
                          <i class="bi bi-briefcase-fill text-primary me-1"></i>{{ yearsOfExperience }}
                        </span>
                        <span class="badge rounded-pill px-3 py-2 meta-pill">
                          <i class="bi bi-trophy-fill text-warning me-1"></i>{{ portfolioUser.rank_title || 'Membre Vérifié' }} &bull; {{ portfolioUser.xp || 0 }} XP
                        </span>
                      </div>
                    </div>

                    <!-- Right Column: Visual Showcase Card with Floating Badges -->
                    <div class="col-lg-5 text-center">
                      <div class="agency-hero-visual-wrapper position-relative d-inline-block p-4">
                        <div class="agency-visual-backdrop rounded-4" :style="{ backgroundColor: accentColor + '18', borderColor: accentColor + '40' }"></div>
                        <div class="position-relative d-inline-block">
                          <img 
                            v-if="portfolioUser.avatar" 
                            :src="formatImageUrl(portfolioUser.avatar)" 
                            :alt="portfolioUser.name"
                            class="border border-4 border-white shadow-lg agency-hero-avatar"
                            :class="[avatarShapeClass, avatarBorderClass]"
                          />
                          <div 
                            v-else 
                            class="border border-4 border-white shadow-lg text-white fw-bold d-flex align-items-center justify-content-center agency-hero-avatar mx-auto"
                            :class="[avatarShapeClass, avatarBorderClass]"
                            :style="{ backgroundColor: accentColor }"
                          >
                            {{ getInitials(portfolioUser.name) }}
                          </div>
                          <span v-if="showOnlinePulse" class="avatar-online-pulse-badge" title="En ligne & Actif"></span>
                        </div>

                        <!-- Floating Badge 1: Top-Right -->
                        <div class="agency-floating-badge badge-top-right bg-white text-dark shadow rounded-4 px-3 py-2 border d-none d-sm-flex align-items-center gap-2">
                          <i class="bi bi-patch-check-fill fs-5 text-success"></i>
                          <div class="text-start lh-1">
                            <strong class="d-block extra-small">Profil Vérifié</strong>
                            <span class="text-muted" style="font-size: 10px;">Tech237 Official</span>
                          </div>
                        </div>

                        <!-- Floating Badge 2: Bottom-Left -->
                        <div class="agency-floating-badge badge-bottom-left bg-white text-dark shadow rounded-4 px-3 py-2 border d-none d-sm-flex align-items-center gap-2">
                          <i class="bi bi-code-slash fs-5" :style="{ color: accentColor }"></i>
                          <div class="text-start lh-1">
                            <strong class="d-block extra-small">{{ (portfolioUser.skills || []).length || '+5' }} Compétences</strong>
                            <span class="text-muted" style="font-size: 10px;">{{ (projects || []).length }} Projet(s) publiés</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Variant: Centered (Default) -->
                <div v-else-if="heroStyle === 'centered'" class="hero-box hero-centered text-center p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden h-100">
                  <div class="hero-glow-orb"></div>
                  <div class="position-relative z-1">
                    <!-- Avatar with Shape, Border & Online Pulse -->
                    <div class="d-inline-block position-relative mb-3">
                      <img 
                        v-if="portfolioUser.avatar" 
                        :src="formatImageUrl(portfolioUser.avatar)" 
                        :alt="portfolioUser.name"
                        class="border border-4 border-white shadow-md hero-avatar"
                        :class="[avatarShapeClass, avatarBorderClass]"
                      />
                      <div 
                        v-else 
                        class="border border-4 border-white shadow-md text-white fw-bold d-flex align-items-center justify-content-center hero-avatar mx-auto"
                        :class="[avatarShapeClass, avatarBorderClass]"
                        :style="{ backgroundColor: accentColor }"
                      >
                        {{ getInitials(portfolioUser.name) }}
                      </div>

                      <!-- Online Pulse or Verified Check -->
                      <span v-if="showOnlinePulse" class="avatar-online-pulse-badge" title="En ligne & Actif"></span>
                      <span v-else class="position-absolute bottom-0 end-0 badge rounded-circle bg-success border border-2 border-white p-1" title="Membre vérifié Tech237">
                        <i class="bi bi-check-lg text-white" style="font-size: 11px;"></i>
                      </span>
                    </div>

                    <!-- Name & Rank -->
                    <h1 class="display-6 fw-bold mb-1 user-name-heading">{{ portfolioUser.name }}</h1>
                    
                    <!-- Headline / Subtitle (with Typewriter if enabled) -->
                    <p class="fs-5 fw-semibold mb-2 user-headline-text" :style="{ color: accentColor }">
                      <span>{{ displayedHeadline }}</span>
                      <span v-if="typewriterEnabled" class="typewriter-cursor">|</span>
                    </p>

                    <!-- Location, Availability & Experience Pills -->
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 mb-3">
                      <span v-if="portfolioUser.location" class="badge rounded-pill px-3 py-2 meta-pill">
                        <i class="bi bi-geo-alt-fill me-1 text-danger"></i>
                        <a v-if="googleMapsUrl" :href="googleMapsUrl" target="_blank" class="text-decoration-none text-dark">
                          {{ portfolioUser.location }} <i class="bi bi-box-arrow-up-right extra-small text-primary ms-1"></i>
                        </a>
                        <span v-else>{{ portfolioUser.location }}</span>
                      </span>

                      <!-- Years of Experience Badge -->
                      <span v-if="yearsOfExperience" class="badge rounded-pill px-3 py-2 meta-pill">
                        <i class="bi bi-briefcase-fill me-1 text-primary"></i> {{ yearsOfExperience }}
                      </span>
                      
                      <span class="badge rounded-pill px-3 py-2 availability-pill" :class="getAvailabilityClass(portfolioUser.availability)">
                        <span class="status-pulse-dot me-1"></span>
                        {{ getAvailabilityLabel(portfolioUser.availability) }}
                      </span>

                      <span class="badge rounded-pill px-3 py-2 meta-pill" :style="{ color: currentTier?.color || '#00A859' }">
                        <i class="bi" :class="currentTier?.badge_icon || 'bi-trophy-fill'"></i>
                        {{ portfolioUser.rank_title || currentTier?.name || 'Novice Tech' }} &bull; {{ portfolioUser.xp || 0 }} XP
                      </span>
                    </div>

                    <!-- Contact & Social CTA Buttons -->
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 mt-3 d-print-none">
                      <!-- Audio Pitch Vocal Button -->
                      <button 
                        v-if="audioPitchEnabled && effectiveAudioPitchText" 
                        type="button" 
                        @click="handleAudioPitchClick" 
                        class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2"
                        :style="{ backgroundColor: accentColor }"
                      >
                        <i class="bi" :class="isPlaying && !isPaused ? 'bi-pause-circle-fill' : 'bi-mic-fill'"></i>
                        <span>{{ isPlaying && !isPaused ? 'Pause Pitch Vocal' : (isPaused ? 'Reprendre Pitch' : 'Écouter mon Pitch') }}</span>
                      </button>

                      <a 
                        v-if="portfolioUser.phone && portfolioUser.availability !== 'not_available'" 
                        :href="'https://wa.me/' + cleanPhone(portfolioUser.phone)" 
                        target="_blank"
                        class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2"
                        style="background-color: #25D366;"
                      >
                        <i class="bi bi-whatsapp"></i>
                        <span>WhatsApp Direct</span>
                      </a>

                      <Link 
                        v-if="$page.props.auth?.user && $page.props.auth.user.id !== portfolioUser.id" 
                        :href="'/espace-utilisateur?section=messages&recipient=' + portfolioUser.id" 
                        class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2"
                        :style="{ backgroundColor: accentColor }"
                      >
                        <i class="bi bi-chat-left-dots-fill"></i>
                        <span>Envoyer un message</span>
                      </Link>

                      <!-- Digital Business Card (.vcf) -->
                      <button 
                        v-if="vcardEnabled" 
                        type="button" 
                        @click="openVcardModal" 
                        class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2"
                      >
                        <i class="bi bi-person-vcard-fill"></i>
                        <span>Fiche Contact (.vcf)</span>
                      </button>

                      <!-- Download CV Button -->
                      <a 
                        v-if="cvFileUrl" 
                        :href="cvFileUrl" 
                        target="_blank" 
                        download 
                        class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 text-decoration-none shadow-sm"
                      >
                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                        <span>Télécharger CV</span>
                      </a>
                      <button 
                        v-else 
                        type="button" 
                        @click="printCV" 
                        class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1"
                      >
                        <i class="bi bi-download"></i>
                        <span>Télécharger CV</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Variant: Split (Asymmetrical 2 columns) -->
                <div v-else-if="heroStyle === 'split'" class="hero-box hero-split p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden h-100">
                  <div class="row align-items-center g-4">
                    <div class="col-md-4 text-center text-md-start">
                      <div class="d-inline-block position-relative">
                        <img 
                          v-if="portfolioUser.avatar" 
                          :src="formatImageUrl(portfolioUser.avatar)" 
                          :alt="portfolioUser.name"
                          class="border border-4 border-white shadow-md hero-avatar-sq"
                          :class="[avatarShapeClass, avatarBorderClass]"
                        />
                        <div 
                          v-else 
                          class="border border-4 border-white shadow-md text-white fw-bold d-flex align-items-center justify-content-center hero-avatar-sq mx-auto mx-md-0"
                          :class="[avatarShapeClass, avatarBorderClass]"
                          :style="{ backgroundColor: accentColor }"
                        >
                          {{ getInitials(portfolioUser.name) }}
                        </div>
                        <span v-if="showOnlinePulse" class="avatar-online-pulse-badge" title="En ligne & Actif"></span>
                      </div>
                    </div>
                    <div class="col-md-8 text-center text-md-start">
                      <span class="badge rounded-pill px-3 py-1 availability-pill mb-2" :class="getAvailabilityClass(portfolioUser.availability)">
                        <span class="status-pulse-dot me-1"></span>
                        {{ getAvailabilityLabel(portfolioUser.availability) }}
                      </span>
                      <h1 class="display-6 fw-bold mb-1 user-name-heading">{{ portfolioUser.name }}</h1>
                      <p class="fs-5 fw-semibold mb-2 user-headline-text" :style="{ color: accentColor }">
                        <span>{{ displayedHeadline }}</span>
                        <span v-if="typewriterEnabled" class="typewriter-cursor">|</span>
                      </p>
                      <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center justify-content-md-start mb-3">
                        <span v-if="portfolioUser.location" class="meta-pill-text small">
                          <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                          <a v-if="googleMapsUrl" :href="googleMapsUrl" target="_blank" class="text-decoration-none text-muted">
                            {{ portfolioUser.location }}
                          </a>
                          <span v-else>{{ portfolioUser.location }}</span>
                        </span>
                        <span v-if="yearsOfExperience" class="meta-pill-text small">
                          <i class="bi bi-briefcase-fill text-primary me-1"></i>{{ yearsOfExperience }}
                        </span>
                        <span class="meta-pill-text small">
                          <i class="bi bi-shield-check text-success me-1"></i>Membre #{{ rankPosition }} Tech237
                        </span>
                        <span class="meta-pill-text small fw-bold" :style="{ color: accentColor }">
                          ⚡ {{ portfolioUser.xp || 0 }} XP
                        </span>
                      </div>
                      <div class="d-flex gap-2 flex-wrap justify-content-center justify-content-md-start d-print-none">
                        <button 
                          v-if="audioPitchEnabled && effectiveAudioPitchText" 
                          type="button" 
                          @click="handleAudioPitchClick" 
                          class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2"
                          :style="{ backgroundColor: accentColor }"
                        >
                          <i class="bi" :class="isPlaying && !isPaused ? 'bi-pause-circle-fill' : 'bi-mic-fill'"></i>
                          <span>{{ isPlaying && !isPaused ? 'Pause Pitch' : (isPaused ? 'Reprendre' : 'Écouter Pitch') }}</span>
                        </button>
                        <a 
                          v-if="portfolioUser.phone" 
                          :href="'https://wa.me/' + cleanPhone(portfolioUser.phone)" 
                          target="_blank" 
                          class="btn btn-success rounded-pill px-4 py-2 fw-bold text-white shadow-sm"
                        >
                          <i class="bi bi-whatsapp me-1"></i> WhatsApp
                        </a>
                        <button 
                          v-if="vcardEnabled" 
                          type="button" 
                          @click="openVcardModal" 
                          class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold"
                        >
                          <i class="bi bi-person-vcard-fill me-1"></i> Fiche .vcf
                        </button>
                        <a 
                          v-if="cvFileUrl" 
                          :href="cvFileUrl" 
                          target="_blank" 
                          download 
                          class="btn btn-outline-dark rounded-pill px-3 py-2 fw-semibold"
                        >
                          <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Télécharger CV
                        </a>
                        <button v-else type="button" @click="printCV" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                          <i class="bi bi-download me-1"></i> Exporter PDF
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Variant: Terminal Hero -->
                <div v-else-if="heroStyle === 'terminal'" class="hero-box hero-terminal p-4 p-md-5 rounded-4 shadow-sm bg-dark text-white font-monospace position-relative h-100">
                  <div class="terminal-dots d-flex gap-2 mb-3">
                    <span class="rounded-circle bg-danger d-inline-block" style="width: 10px; height: 10px;"></span>
                    <span class="rounded-circle bg-warning d-inline-block" style="width: 10px; height: 10px;"></span>
                    <span class="rounded-circle bg-success d-inline-block" style="width: 10px; height: 10px;"></span>
                  </div>
                  <div class="terminal-prompt text-success mb-2">&gt; dev.inspect(user="{{ portfolioUser.username }}")</div>
                  <div class="ps-3 border-start border-success border-2 mb-3">
                    <div class="fs-4 fw-bold text-white mb-1">{{ portfolioUser.name }}</div>
                    <div class="text-info mb-1">// {{ displayedHeadline }}<span v-if="typewriterEnabled" class="typewriter-cursor">|</span></div>
                    <div class="text-white-50 small" v-if="portfolioUser.location">📍 Location: {{ portfolioUser.location }}</div>
                    <div class="text-white-50 small" v-if="yearsOfExperience">💼 Expérience: {{ yearsOfExperience }}</div>
                    <div class="text-warning small">⚡ Rank: {{ portfolioUser.rank_title || 'Novice Tech' }} ({{ portfolioUser.xp || 0 }} XP)</div>
                  </div>
                  <div class="terminal-prompt text-success">&gt; dev.status &rarr; <span class="text-warning">{{ getAvailabilityLabel(portfolioUser.availability) }}</span></div>
                </div>

                <!-- Variant: Cover Banner with Floating Avatar -->
                <div v-else-if="heroStyle === 'cover_banner'" class="hero-box hero-cover-banner rounded-4 shadow-sm position-relative overflow-hidden h-100">
                  <div class="hero-cover-bg py-5 px-4 text-center text-white" :style="{ backgroundColor: accentColor }">
                    <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold extra-small mb-2">Portfolio Officiel Tech237</span>
                    <h1 class="display-6 fw-bold mb-1 text-white">{{ portfolioUser.name }}</h1>
                    <p class="fs-6 opacity-90 mb-0">
                      <span>{{ displayedHeadline }}</span>
                      <span v-if="typewriterEnabled" class="typewriter-cursor">|</span>
                    </p>
                  </div>
                  <div class="p-4 text-center position-relative content-card">
                    <img 
                      v-if="portfolioUser.avatar" 
                      :src="formatImageUrl(portfolioUser.avatar)" 
                      :alt="portfolioUser.name"
                      class="border border-4 border-white shadow-md hero-avatar mx-auto mb-2"
                      :class="[avatarShapeClass, avatarBorderClass]"
                      style="margin-top: -65px;"
                    />
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 mb-2">
                      <span v-if="portfolioUser.location" class="badge rounded-pill px-3 py-1 meta-pill"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ portfolioUser.location }}</span>
                      <span v-if="yearsOfExperience" class="badge rounded-pill px-3 py-1 meta-pill"><i class="bi bi-briefcase-fill text-primary me-1"></i>{{ yearsOfExperience }}</span>
                      <span class="badge rounded-pill px-3 py-1 availability-pill" :class="getAvailabilityClass(portfolioUser.availability)">{{ getAvailabilityLabel(portfolioUser.availability) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Variant: Cyberpunk Sci-Fi HUD -->
                <div v-else-if="heroStyle === 'cyberpunk'" class="hero-box hero-cyberpunk p-4 p-md-5 rounded-4 shadow-sm bg-dark text-white position-relative overflow-hidden border border-emerald-500 h-100">
                  <div class="cyberpunk-scanline"></div>
                  <div class="position-relative z-1 d-flex flex-column align-items-center text-center">
                    <div class="extra-small text-emerald-400 font-monospace mb-2">[ IDENT_SYS_V2.37 // OPERATOR_PROFILE ]</div>
                    <img 
                      v-if="portfolioUser.avatar" 
                      :src="formatImageUrl(portfolioUser.avatar)" 
                      :alt="portfolioUser.name"
                      class="border border-2 border-emerald-400 shadow hero-avatar mb-3"
                      :class="[avatarShapeClass, avatarBorderClass]"
                    />
                    <h1 class="display-6 fw-black text-white text-uppercase tracking-wider mb-1">{{ portfolioUser.name }}</h1>
                    <p class="text-emerald-400 font-monospace mb-2">{{ displayedHeadline }}<span v-if="typewriterEnabled" class="typewriter-cursor">|</span></p>
                    <div class="d-flex gap-2 flex-wrap justify-content-center">
                      <span class="badge bg-emerald-950 text-emerald-300 border border-emerald-600 extra-small font-monospace">XP: {{ portfolioUser.xp || 0 }}</span>
                      <span v-if="yearsOfExperience" class="badge bg-emerald-950 text-emerald-300 border border-emerald-600 extra-small font-monospace">EXP: {{ yearsOfExperience }}</span>
                      <span class="badge bg-emerald-950 text-emerald-300 border border-emerald-600 extra-small font-monospace">STATUS: {{ getAvailabilityLabel(portfolioUser.availability) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Variant: Executive C-Level Recruiter -->
                <div v-else-if="heroStyle === 'executive'" class="hero-box hero-executive p-4 p-md-5 rounded-4 shadow-sm border border-2 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pb-3 border-bottom mb-3">
                    <div class="d-flex align-items-center gap-3">
                      <img 
                        v-if="portfolioUser.avatar" 
                        :src="formatImageUrl(portfolioUser.avatar)" 
                        :alt="portfolioUser.name"
                        class="border hero-avatar"
                        :class="[avatarShapeClass, avatarBorderClass]"
                        style="width: 72px; height: 72px;"
                      />
                      <div>
                        <h1 class="h3 fw-bold mb-0 text-dark">{{ portfolioUser.name }}</h1>
                        <p class="small text-muted mb-0 fw-bold">{{ displayedHeadline }}<span v-if="typewriterEnabled" class="typewriter-cursor">|</span></p>
                      </div>
                    </div>
                    <span class="badge rounded-pill px-3 py-2 availability-pill" :class="getAvailabilityClass(portfolioUser.availability)">
                      {{ getAvailabilityLabel(portfolioUser.availability) }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span v-if="yearsOfExperience" class="small text-muted fw-bold"><i class="bi bi-briefcase text-primary me-1"></i>{{ yearsOfExperience }}</span>
                    <span v-if="portfolioUser.location" class="small text-muted"><i class="bi bi-geo-alt text-danger me-1"></i>{{ portfolioUser.location }}</span>
                    <span class="small text-muted"><i class="bi bi-trophy text-warning me-1"></i>{{ portfolioUser.rank_title || 'Novice Tech' }} ({{ portfolioUser.xp || 0 }} XP)</span>
                  </div>
                </div>

                <!-- Variant: Minimal (Default Fallback) -->
                <div v-else class="hero-box hero-minimal p-4 p-md-5 rounded-4 shadow-sm border content-card h-100">
                  <div class="d-flex align-items-center gap-4 flex-wrap">
                    <img 
                      v-if="portfolioUser.avatar" 
                      :src="formatImageUrl(portfolioUser.avatar)" 
                      :alt="portfolioUser.name"
                      class="border hero-avatar"
                      :class="[avatarShapeClass, avatarBorderClass]"
                    />
                    <div>
                      <h1 class="h3 fw-bold mb-1 user-name-heading">{{ portfolioUser.name }}</h1>
                      <p class="text-muted fw-semibold mb-2">{{ displayedHeadline }}<span v-if="typewriterEnabled" class="typewriter-cursor">|</span></p>
                      <div class="d-flex gap-2 flex-wrap">
                        <span v-if="yearsOfExperience" class="badge bg-light text-dark border extra-small">{{ yearsOfExperience }}</span>
                        <span class="badge bg-light text-dark border extra-small">{{ getAvailabilityLabel(portfolioUser.availability) }}</span>
                        <span v-if="portfolioUser.location" class="badge bg-light text-muted border extra-small">{{ portfolioUser.location }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- WEBSITE KPI STATS STRIP (Right under Hero) -->
                <div v-if="kpiStatsConfig.enabled && kpiStatsItems.length" class="row g-3 mt-3 d-print-none">
                  <div v-for="(kpi, kIdx) in kpiStatsItems" :key="kIdx" class="col-6 col-md-3">
                    <div
                      class="rounded-4 p-3 text-center h-100 transition-hover"
                      :class="[
                        kpiStatsDesign.style === 'minimal' ? 'bg-transparent border' : 'card border-0 shadow-sm content-card',
                        kpiStatsDesign.style === 'floating_cards' ? 'shadow' : '',
                        kpiStatsDesign.style === 'neon_circles' ? 'border border-opacity-25' : ''
                      ]"
                    >
                      <div v-if="kpi.icon" class="mb-1">
                        <span
                          class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          :style="{
                            width: kpiStatsDesign.style === 'neon_circles' ? '44px' : '32px',
                            height: kpiStatsDesign.style === 'neon_circles' ? '44px' : '32px',
                            backgroundColor: (kpi.color || accentColor) + '18',
                            color: kpi.color || accentColor
                          }"
                        >
                          <i class="bi" :class="kpi.icon"></i>
                        </span>
                      </div>
                      <div class="fs-3 fw-black mb-1" :style="{ color: kpi.color || accentColor }">{{ kpi.value }}</div>
                      <div class="extra-small fw-semibold text-muted">{{ kpi.label }}</div>
                    </div>
                  </div>
                </div>

                <!-- INFINITE TECH MARQUEE RIBBON -->
                <div v-if="techMarqueeEnabled && portfolioUser.skills && portfolioUser.skills.length && isBlockEnabled('skills')" class="tech-marquee-container rounded-4 border shadow-2xs mt-3 py-2 px-3 overflow-hidden content-card d-print-none">
                  <div class="tech-marquee-track d-flex align-items-center gap-4">
                    <span 
                      v-for="(sk, mIdx) in marqueeSkillsLoop" 
                      :key="'mq-' + mIdx" 
                      class="d-inline-flex align-items-center gap-2 small fw-bold text-nowrap"
                    >
                      <i class="bi bi-Stars text-warning" :style="{ color: accentColor }">✦</i>
                      <span>{{ typeof sk === 'object' ? sk.name : sk }}</span>
                      <span v-if="typeof sk === 'object' && sk.level" class="badge bg-light text-muted border extra-small">{{ sk.level }}</span>
                    </span>
                  </div>
                </div>

              </section>

              <!-- 2. ABOUT / BIO BLOCK (100% Customizable Layout, Text Align, Quote, Image, Ideas & Live Owner Edit) -->
              <section v-else-if="block.id === 'about' && (liveBioText || (aboutDesign.ideas && aboutDesign.ideas.length))" class="portfolio-block h-100">
                <div 
                  class="p-4 p-md-5 rounded-4 h-100 transition"
                  :class="[
                    aboutDesign.box_style === 'minimal' ? 'bg-transparent border-0 shadow-none' : (aboutDesign.box_style === 'glass' ? 'content-card backdrop-blur border shadow-sm' : 'card shadow-sm border-0 content-card'),
                    'anim-entry-' + (aboutDesign.animation || 'fade_up')
                  ]"
                  :style="aboutDesign.box_style === 'accent_border' ? { borderLeft: '5px solid ' + accentColor + ' !important' } : {}"
                >
                  <!-- Section Header + Owner Direct Edit Toggle -->
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3" :class="aboutDesign.text_align === 'center' ? 'justify-content-center text-center' : ''">
                    <div>
                      <div class="d-flex align-items-center gap-2" :class="aboutDesign.text_align === 'center' ? 'justify-content-center' : ''">
                        <span class="section-icon-badge" :style="{ color: accentColor }">
                          <i class="bi bi-person-lines-fill fs-5"></i>
                        </span>
                        <h2 class="h5 fw-bold mb-0 section-heading">{{ aboutDesign.title || 'À propos de moi' }}</h2>
                      </div>
                      <p v-if="aboutDesign.subtitle" class="extra-small text-muted mb-0 mt-1">{{ aboutDesign.subtitle }}</p>
                    </div>

                    <button
                      v-if="isOwner"
                      type="button"
                      @click="showInlineBioEditor = !showInlineBioEditor"
                      class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 extra-small fw-bold d-print-none"
                    >
                      <i class="bi" :class="showInlineBioEditor ? 'bi-x-lg' : 'bi-pencil-square'"></i>
                      <span class="ms-1">{{ showInlineBioEditor ? 'Fermer l\'éditeur' : 'Modifier mon À Propos (Table users)' }}</span>
                    </button>
                  </div>

                  <!-- Inline Live Bio Editor for Owner (Directly updates users table) -->
                  <div v-if="isOwner && showInlineBioEditor" class="p-3 rounded-4 border bg-light mb-4 d-print-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <strong class="small text-dark"><i class="bi bi-database-fill-check text-success me-1"></i> Édition directe (Synchronisée avec la table <code>users</code>)</strong>
                      <span v-if="inlineBioSavedMsg" class="badge bg-success-subtle text-success extra-small">{{ inlineBioSavedMsg }}</span>
                    </div>
                    <textarea v-model="liveBioText" class="form-control form-control-sm mb-2" rows="4" placeholder="Modifiez votre présentation ici..."></textarea>
                    <div class="d-flex justify-content-end gap-2">
                      <button type="button" @click="saveInlineBioToUsersTable" :disabled="isSavingInlineBio" class="btn btn-xs btn-success rounded-pill px-3 py-1 fw-bold extra-small">
                        <span v-if="isSavingInlineBio" class="spinner-border spinner-border-sm me-1" style="width: 10px; height: 10px;"></span>
                        <i v-else class="bi bi-check2-circle me-1"></i> Enregistrer dans la table users
                      </button>
                    </div>
                  </div>

                  <!-- Highlight Quote Banner (if set or layout === editorial_quote) -->
                  <blockquote 
                    v-if="aboutDesign.highlight_quote" 
                    class="p-3 p-md-4 rounded-4 mb-4 border-start border-4 fst-italic fw-semibold"
                    :style="{ borderColor: accentColor + ' !important', backgroundColor: accentColor + '10' }"
                  >
                    <i class="bi bi-quote fs-3 me-2" :style="{ color: accentColor }"></i>
                    <span>{{ aboutDesign.highlight_quote }}</span>
                  </blockquote>

                  <!-- Layout: Split Image Left or Right -->
                  <div v-if="(aboutDesign.layout_style === 'split_image_left' || aboutDesign.layout_style === 'split_image_right') && aboutDesign.image_url" class="row align-items-center g-4" :class="aboutDesign.layout_style === 'split_image_right' ? 'flex-md-row-reverse' : ''">
                    <div class="col-md-5 text-center">
                      <img 
                        :src="formatImageUrl(aboutDesign.image_url)" 
                        :alt="portfolioUser.name" 
                        class="img-fluid shadow-sm border object-fit-cover"
                        :class="aboutDesign.image_shape === 'circle' ? 'rounded-circle' : 'rounded-4'"
                        style="max-height: 320px; width: 100%;"
                      />
                    </div>
                    <div class="col-md-7">
                      <div 
                        class="text-content leading-relaxed"
                        :class="[getAboutTextAlignClass(), getAboutFontSizeClass()]"
                        v-html="formatHtml(liveBioText)"
                      ></div>
                    </div>
                  </div>

                  <!-- Layout: Bento Bio + Ideas Side-by-Side -->
                  <div v-else-if="aboutDesign.layout_style === 'bento_ideas' && aboutDesign.ideas && aboutDesign.ideas.length" class="row g-4 align-items-start">
                    <div class="col-lg-6">
                      <div 
                        class="text-content leading-relaxed"
                        :class="[getAboutTextAlignClass(), getAboutFontSizeClass()]"
                        v-html="formatHtml(liveBioText)"
                      ></div>
                    </div>
                    <div class="col-lg-6">
                      <div class="row g-3">
                        <div v-for="(idea, idIdx) in aboutDesign.ideas" :key="idIdx" class="col-12">
                          <div class="p-3 rounded-4 bg-light border d-flex align-items-start gap-3 transition-hover">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 text-white shadow-2xs" style="width: 42px; height: 42px;" :style="{ backgroundColor: idea.color || accentColor }">
                              <i class="bi fs-5" :class="idea.icon || 'bi-lightbulb-fill'"></i>
                            </div>
                            <div>
                              <h3 class="h6 fw-bold text-dark mb-1">{{ idea.title }}</h3>
                              <p class="extra-small text-muted mb-0 leading-relaxed">{{ idea.description }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Layout: Classic or Magazine 2-Columns -->
                  <div v-else>
                    <div v-if="aboutDesign.image_url" class="mb-4 text-center">
                      <img :src="formatImageUrl(aboutDesign.image_url)" :alt="portfolioUser.name" class="img-fluid rounded-4 shadow-sm border object-fit-cover" style="max-height: 280px;" />
                    </div>
                    <div 
                      class="text-content leading-relaxed mb-0"
                      :class="[
                        getAboutTextAlignClass(),
                        getAboutFontSizeClass(),
                        aboutDesign.layout_style === 'two_columns' ? 'about-two-columns' : ''
                      ]"
                      v-html="formatHtml(liveBioText)"
                    ></div>
                  </div>

                  <!-- Key Ideas / Pillars Cards Grid (Shown below bio when not in bento_ideas mode) -->
                  <div v-if="aboutDesign.layout_style !== 'bento_ideas' && aboutDesign.ideas && aboutDesign.ideas.length" class="row g-3 mt-4 pt-3 border-top">
                    <div v-for="(idea, idIdx) in aboutDesign.ideas" :key="idIdx" :class="aboutDesign.ideas.length === 1 ? 'col-12' : (aboutDesign.ideas.length === 2 ? 'col-md-6' : 'col-md-4')">
                      <div class="p-3 rounded-4 bg-light border h-100 transition-hover" :class="aboutDesign.text_align === 'center' ? 'text-center' : ''">
                        <div 
                          class="rounded-3 d-inline-flex align-items-center justify-content-center text-white mb-2 shadow-2xs" 
                          style="width: 40px; height: 40px;" 
                          :style="{ backgroundColor: idea.color || accentColor }"
                        >
                          <i class="bi fs-5" :class="idea.icon || 'bi-stars'"></i>
                        </div>
                        <h3 class="h6 fw-bold text-dark mb-1">{{ idea.title }}</h3>
                        <p class="extra-small text-muted mb-0 leading-relaxed">{{ idea.description }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- YOUTUBE VIDEO SHOWCASE BLOCK (10 Models) -->
              <section v-else-if="block.id === 'video' && youtubeEmbedUrl" id="block-video" class="portfolio-block h-100" :class="'block-model-' + getBlockModel('video')">
                <div
                  class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100"
                  :class="[
                    getBlockModel('video') === 'studio_dark' ? 'bg-dark text-white' : '',
                    getBlockModel('video') === 'minimal_clean' ? 'bg-transparent shadow-none border-0 p-2' : '',
                    getBlockModel('video') === 'neon_glow' ? 'border border-2' : ''
                  ]"
                  :style="getBlockModel('video') === 'neon_glow' ? { borderColor: accentColor, boxShadow: `0 0 24px ${accentColor}30` } : {}"
                >
                  <!-- Split Text + Video Models -->
                  <div v-if="getBlockModel('video') === 'split_text_left' || getBlockModel('video') === 'split_text_right'" class="row g-4 align-items-center" :class="getBlockModel('video') === 'split_text_right' ? 'flex-lg-row-reverse' : ''">
                    <div class="col-lg-5">
                      <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 extra-small fw-bold mb-2">
                        <i class="bi bi-play-btn-fill me-1"></i> Vidéo Pitch
                      </span>
                      <h2 class="h4 fw-bold mb-2 section-heading">Vidéo de Présentation &amp; Pitch</h2>
                      <p class="small opacity-75 mb-0">Découvrez mon parcours, ma vision technique et mes réalisations en vidéo.</p>
                    </div>
                    <div class="col-lg-7">
                      <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm border">
                        <iframe :src="youtubeEmbedUrl" title="Vidéo pitch portfolio" allowfullscreen></iframe>
                      </div>
                    </div>
                  </div>

                  <!-- Browser Frame Model -->
                  <template v-else-if="getBlockModel('video') === 'browser_frame'">
                    <div class="d-flex align-items-center gap-2 mb-3">
                      <span class="section-icon-badge text-danger"><i class="bi bi-play-btn-fill fs-5"></i></span>
                      <h2 class="h5 fw-bold mb-0 section-heading">Vidéo de Présentation &amp; Pitch</h2>
                    </div>
                    <div class="rounded-4 overflow-hidden border shadow-sm">
                      <div class="bg-light border-bottom px-3 py-2 d-flex align-items-center gap-2">
                        <span class="rounded-circle bg-danger d-inline-block" style="width: 10px; height: 10px;"></span>
                        <span class="rounded-circle bg-warning d-inline-block" style="width: 10px; height: 10px;"></span>
                        <span class="rounded-circle bg-success d-inline-block" style="width: 10px; height: 10px;"></span>
                        <small class="extra-small text-muted ms-2 font-monospace">youtube.com/watch</small>
                      </div>
                      <div class="ratio ratio-16x9">
                        <iframe :src="youtubeEmbedUrl" title="Vidéo pitch portfolio" allowfullscreen></iframe>
                      </div>
                    </div>
                  </template>

                  <!-- Standard / Dark / Neon / Compact / Floating / Banner Models -->
                  <template v-else>
                    <div class="d-flex align-items-center gap-2 mb-3">
                      <span class="section-icon-badge text-danger">
                        <i class="bi bi-play-btn-fill fs-5"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">Vidéo de Présentation &amp; Pitch</h2>
                    </div>
                    <div
                      class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-xs border mx-auto"
                      :style="getBlockModel('video') === 'compact_player' ? { maxWidth: '640px' } : {}"
                    >
                      <iframe :src="youtubeEmbedUrl" title="Vidéo pitch portfolio" allowfullscreen></iframe>
                    </div>
                  </template>
                </div>
              </section>

              <!-- 3. SKILLS & STACK BLOCK (6 Presentation Modes, Custom Colors, Icons, Hover & Animations) -->
              <section v-else-if="block.id === 'skills' && (portfolioUser.skills && portfolioUser.skills.length)" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2" :class="skillsDesign.text_align === 'center' ? 'justify-content-center text-center' : ''">
                    <div>
                      <div class="d-flex align-items-center gap-2" :class="skillsDesign.text_align === 'center' ? 'justify-content-center' : ''">
                        <span class="section-icon-badge" :style="{ color: accentColor }">
                          <i class="bi bi-cpu-fill fs-5"></i>
                        </span>
                        <h2 class="h5 fw-bold mb-0 section-heading">{{ skillsDesign.title || 'Compétences & Stack Technique' }}</h2>
                      </div>
                      <p v-if="skillsDesign.subtitle" class="extra-small text-muted mb-0 mt-1">{{ skillsDesign.subtitle }}</p>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill extra-small">
                      {{ portfolioUser.skills.length }} compétence(s)
                    </span>
                  </div>

                  <!-- Variant 1: Chips / Pills -->
                  <div v-if="skillsStyle === 'chips' || !skillsStyle" class="d-flex flex-wrap gap-2" :class="skillsDesign.text_align === 'center' ? 'justify-content-center' : ''">
                    <span 
                      v-for="(skill, sIdx) in portfolioUser.skills" 
                      :key="sIdx"
                      class="skill-chip badge rounded-pill px-3 py-2 fw-semibold fs-6 shadow-2xs d-inline-flex align-items-center gap-2 transition"
                      :class="['skill-hover-' + (skillsDesign.hover_effect || 'lift'), 'skill-anim-' + (skillsDesign.animation || 'none')]"
                      :style="{ backgroundColor: getSkillBg(sIdx, skill), color: getSkillColor(sIdx, skill) }"
                    >
                      <i v-if="typeof skill === 'object' && skill.icon" class="bi" :class="skill.icon"></i>
                      <span>{{ typeof skill === 'object' ? skill.name : skill }}</span>
                      <small v-if="skillsDesign.show_category_badge && typeof skill === 'object' && skill.category" class="opacity-75 extra-small">({{ skill.category }})</small>
                      <span 
                        v-if="skillsDesign.show_level_badge !== false && typeof skill === 'object' && skill.level" 
                        class="badge rounded-pill px-2 py-0 extra-small fw-bold ms-1"
                        :class="getSkillLevelBadgeClass(skill.level)"
                      >
                        {{ skill.level }}
                      </span>
                    </span>
                  </div>

                  <!-- Variant 2: Neon Badges -->
                  <div v-else-if="skillsStyle === 'neon_badges'" class="d-flex flex-wrap gap-3" :class="skillsDesign.text_align === 'center' ? 'justify-content-center' : ''">
                    <div 
                      v-for="(skill, sIdx) in portfolioUser.skills" 
                      :key="sIdx"
                      class="px-3 py-2 rounded-pill border-2 d-inline-flex align-items-center gap-2 fw-bold small transition"
                      :class="['skill-hover-' + (skillsDesign.hover_effect || 'glow'), 'skill-anim-' + (skillsDesign.animation || 'none')]"
                      :style="{
                        borderColor: getSkillSolidColor(sIdx, skill),
                        color: getSkillSolidColor(sIdx, skill),
                        boxShadow: `0 0 12px ${getSkillSolidColor(sIdx, skill)}35`,
                        backgroundColor: `${getSkillSolidColor(sIdx, skill)}10`
                      }"
                    >
                      <i v-if="typeof skill === 'object' && skill.icon" class="bi" :class="skill.icon"></i>
                      <span>{{ typeof skill === 'object' ? skill.name : skill }}</span>
                      <span v-if="skillsDesign.show_level_badge !== false" class="badge rounded-pill text-white extra-small" :style="{ backgroundColor: getSkillSolidColor(sIdx, skill) }">
                        {{ getSkillPercent(skill) }}%
                      </span>
                    </div>
                  </div>

                  <!-- Variant 3: Animated Progress Bars -->
                  <div v-else-if="skillsStyle === 'bars'" class="row g-3">
                    <div v-for="(skill, sIdx) in portfolioUser.skills" :key="sIdx" :class="skillsColsClass">
                      <div class="p-3 rounded-4 bg-light border transition" :class="'skill-hover-' + (skillsDesign.hover_effect || 'lift')">
                        <div class="d-flex justify-content-between align-items-center small fw-bold mb-2">
                          <span class="d-flex align-items-center gap-2 text-dark">
                            <i v-if="typeof skill === 'object' && skill.icon" class="bi" :class="skill.icon" :style="{ color: getSkillSolidColor(sIdx, skill) }"></i>
                            <span>{{ typeof skill === 'object' ? skill.name : skill }}</span>
                            <small v-if="skillsDesign.show_category_badge && typeof skill === 'object' && skill.category" class="text-muted extra-small fw-normal">({{ skill.category }})</small>
                          </span>
                          <div class="d-flex align-items-center gap-2">
                            <span class="extra-small fw-bold" :style="{ color: getSkillSolidColor(sIdx, skill) }">{{ getSkillPercent(skill) }}%</span>
                            <span 
                              v-if="skillsDesign.show_level_badge !== false"
                              class="badge rounded-pill px-2 py-0 extra-small fw-bold"
                              :class="getSkillLevelBadgeClass(typeof skill === 'object' ? skill.level : 'Avancé')"
                            >
                              {{ typeof skill === 'object' && skill.level ? skill.level : 'Avancé' }}
                            </span>
                          </div>
                        </div>
                        <div class="progress rounded-pill bg-white border" style="height: 10px;">
                          <div 
                            class="progress-bar rounded-pill" 
                            :class="skillsDesign.animation === 'shimmer' ? 'progress-bar-striped progress-bar-animated' : ''"
                            :style="{ width: getSkillPercent(skill) + '%', backgroundColor: getSkillSolidColor(sIdx, skill) }"
                          ></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Variant 4: Circular SVG Ring Gauges -->
                  <div v-else-if="skillsStyle === 'circular'" class="row g-3">
                    <div v-for="(skill, sIdx) in portfolioUser.skills" :key="sIdx" :class="skillsColsClass">
                      <div class="p-3 rounded-4 bg-light border text-center h-100 d-flex flex-column align-items-center justify-content-center transition" :class="'skill-hover-' + (skillsDesign.hover_effect || 'lift')">
                        <div class="position-relative d-inline-flex align-items-center justify-content-center mb-2" style="width: 78px; height: 78px;">
                          <svg viewBox="0 0 36 36" class="w-100 h-100" style="transform: rotate(-90deg);">
                            <path
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none"
                              stroke="#E2E8F0"
                              stroke-width="3.2"
                            />
                            <path
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none"
                              :stroke="getSkillSolidColor(sIdx, skill)"
                              stroke-width="3.2"
                              stroke-linecap="round"
                              :stroke-dasharray="getSkillPercent(skill) + ', 100'"
                            />
                          </svg>
                          <div class="position-absolute fw-black small text-dark">
                            {{ getSkillPercent(skill) }}%
                          </div>
                        </div>
                        <strong class="d-block small text-dark mb-1">
                          <i v-if="typeof skill === 'object' && skill.icon" class="bi me-1" :class="skill.icon" :style="{ color: getSkillSolidColor(sIdx, skill) }"></i>
                          {{ typeof skill === 'object' ? skill.name : skill }}
                        </strong>
                        <span 
                          v-if="skillsDesign.show_level_badge !== false"
                          class="badge rounded-pill px-2 py-0 extra-small fw-bold"
                          :class="getSkillLevelBadgeClass(typeof skill === 'object' ? skill.level : 'Avancé')"
                        >
                          {{ typeof skill === 'object' && skill.level ? skill.level : 'Avancé' }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Variant 5: Grouped by Domain (Backend, Frontend, Mobile, DevOps...) -->
                  <div v-else-if="skillsStyle === 'grouped_domains'" class="row g-3">
                    <div v-for="(groupSkills, domainName) in groupedSkillsByDomain" :key="domainName" class="col-md-6">
                      <div class="p-3 p-md-4 rounded-4 bg-light border h-100">
                        <h3 class="h6 fw-bold text-dark mb-3 d-flex align-items-center justify-content-between pb-2 border-bottom">
                          <span><i class="bi bi-layers-fill me-2" :style="{ color: accentColor }"></i>{{ domainName }}</span>
                          <span class="badge bg-white text-muted border extra-small">{{ groupSkills.length }}</span>
                        </h3>
                        <div class="d-flex flex-wrap gap-2">
                          <span
                            v-for="(sk, gIdx) in groupSkills"
                            :key="gIdx"
                            class="badge bg-white text-dark border rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-2xs transition"
                            :class="'skill-hover-' + (skillsDesign.hover_effect || 'lift')"
                            :style="{ borderLeft: '3px solid ' + getSkillSolidColor(gIdx, sk) }"
                          >
                            <i v-if="typeof sk === 'object' && sk.icon" class="bi" :class="sk.icon" :style="{ color: getSkillSolidColor(gIdx, sk) }"></i>
                            <span class="fw-semibold">{{ typeof sk === 'object' ? sk.name : sk }}</span>
                            <small v-if="skillsDesign.show_level_badge !== false && typeof sk === 'object' && sk.level" class="text-muted extra-small">&bull; {{ sk.level }}</small>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Variant 6: Categorized 3D Cards Grid -->
                  <div v-else class="row g-3">
                    <div v-for="(skill, sIdx) in portfolioUser.skills" :key="sIdx" :class="skillsColsClass">
                      <div 
                        class="p-3 rounded-4 text-center border skill-grid-box h-100 d-flex flex-column justify-content-between bg-light transition"
                        :class="'skill-hover-' + (skillsDesign.hover_effect || 'lift')"
                        :style="{ borderTop: '3px solid ' + getSkillSolidColor(sIdx, skill) }"
                      >
                        <div>
                          <div v-if="typeof skill === 'object' && skill.icon" class="fs-4 mb-1" :style="{ color: getSkillSolidColor(sIdx, skill) }">
                            <i class="bi" :class="skill.icon"></i>
                          </div>
                          <strong class="d-block text-dark small mb-1">{{ typeof skill === 'object' ? skill.name : skill }}</strong>
                          <span v-if="skillsDesign.show_category_badge !== false" class="extra-small text-muted d-block mb-2">{{ typeof skill === 'object' && skill.category ? skill.category : 'Tech' }}</span>
                        </div>
                        <div>
                          <span 
                            v-if="skillsDesign.show_level_badge !== false"
                            class="badge rounded-pill px-2 py-1 extra-small fw-bold"
                            :class="getSkillLevelBadgeClass(typeof skill === 'object' ? skill.level : 'Avancé')"
                          >
                            {{ typeof skill === 'object' && skill.level ? skill.level : 'Avancé' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </section>

              <!-- 4. PROJECTS BLOCK (100% Customizable: Logo Toggle, Per-Project Logo Override, 6 Layouts & Hover Effects) -->
              <section v-else-if="block.id === 'projects' && projects.length" class="portfolio-block h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2" :class="projectsDesign.text_align === 'center' ? 'justify-content-center text-center' : ''">
                  <div>
                    <div class="d-flex align-items-center gap-2" :class="projectsDesign.text_align === 'center' ? 'justify-content-center' : ''">
                      <span class="section-icon-badge" :style="{ color: accentColor }">
                        <i class="bi bi-rocket-takeoff-fill fs-5"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">{{ projectsDesign.title || 'Projets & Réalisations' }}</h2>
                    </div>
                    <p v-if="projectsDesign.subtitle" class="extra-small text-muted mb-0 mt-1">{{ projectsDesign.subtitle }}</p>
                  </div>
                  <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 extra-small fw-bold">
                    {{ projects.length }} projet(s)
                  </span>
                </div>

                <!-- ZIG-ZAG ALTERNATING AGENCY SHOWCASE VARIANT -->
                <div v-if="projectsStyle === 'zigzag'" class="d-flex flex-column gap-4">
                  <div 
                    v-for="(proj, zIdx) in projects" 
                    :key="proj.id" 
                    class="card border-0 shadow-sm content-card overflow-hidden p-4 p-md-5 transition"
                    :class="'project-hover-' + (projectsDesign.hover_effect || 'lift_shadow')"
                    :style="{ borderRadius: projectsDesign.card_radius || '20px' }"
                  >
                    <div class="row align-items-center g-4" :class="zIdx % 2 === 1 ? 'flex-md-row-reverse' : ''">
                      <!-- Visual / Showcase Column -->
                      <div class="col-md-5">
                        <div 
                          class="p-4 rounded-4 d-flex flex-column align-items-center justify-content-center text-center position-relative overflow-hidden border project-visual-box"
                          style="min-height: 210px;"
                          :style="{ background: `linear-gradient(135deg, ${accentColor}15 0%, ${accentColor}05 100%)` }"
                        >
                          <template v-if="projectsDesign.show_logo !== false">
                            <img 
                              v-if="getEffectiveProjectLogo(proj)" 
                              :src="formatImageUrl(getEffectiveProjectLogo(proj))" 
                              :alt="proj.name || proj.title"
                              class="shadow-sm border bg-white p-2 mb-3 object-fit-contain project-logo-img"
                              :class="getProjectLogoShapeClass()"
                              :style="getProjectLogoSizeStyle(true)"
                            />
                            <div 
                              v-else 
                              class="text-white fw-bold d-flex align-items-center justify-content-center shadow-sm mb-3 project-logo-img"
                              :class="getProjectLogoShapeClass()"
                              :style="{ ...getProjectLogoSizeStyle(true), backgroundColor: accentColor, fontSize: '30px' }"
                            >
                              <i class="bi bi-rocket-takeoff"></i>
                            </div>
                          </template>
                          <span v-if="projectsDesign.show_category_badge !== false" class="badge rounded-pill bg-white text-dark border px-3 py-1 extra-small fw-bold shadow-2xs">
                            {{ getEffectiveProjectBadge(proj) }}
                          </span>
                        </div>
                      </div>

                      <!-- Project Case Study Details Column -->
                      <div class="col-md-7" :class="projectsDesign.text_align === 'center' ? 'text-center' : ''">
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap" :class="projectsDesign.text_align === 'center' ? 'justify-content-center' : ''">
                          <span v-if="projectsDesign.show_number_badge !== false" class="badge rounded-pill text-white extra-small px-3 py-1" :style="{ backgroundColor: accentColor }">
                            Projet #{{ zIdx + 1 }}
                          </span>
                          <span v-if="projectsDesign.show_stats !== false && proj.views_count" class="extra-small text-muted">
                            <i class="bi bi-eye me-1"></i>{{ proj.views_count }} vues
                          </span>
                        </div>
                        <h3 class="h4 fw-bold mb-2 text-dark">{{ proj.name || proj.title }}</h3>
                        <div class="text-muted mb-4 leading-relaxed" v-html="formatHtml(proj.pitch || proj.description || 'Projet innovant développé et propulsé sur Tech237.')"></div>

                        <div class="d-flex flex-wrap align-items-center gap-2 d-print-none" :class="projectsDesign.text_align === 'center' ? 'justify-content-center' : ''">
                          <button 
                            v-if="projectsDesign.show_case_study_btn !== false"
                            type="button" 
                            @click="openCaseStudyModal(proj)" 
                            class="btn btn-sm rounded-pill px-4 py-2 fw-bold text-white shadow-2xs"
                            :style="{ backgroundColor: accentColor }"
                          >
                            <i class="bi bi-journal-richtext me-1"></i> Étude de cas (Aperçu)
                          </button>
                          <a 
                            :href="'/projet/' + (proj.slug || proj.id)" 
                            target="_blank" 
                            class="btn btn-sm btn-outline-dark rounded-pill px-4 py-2 fw-semibold"
                          >
                            {{ projectsDesign.cta_label || 'Découvrir le projet' }} &rarr;
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- MINIMAL EDITORIAL ROWS VARIANT -->
                <div v-else-if="projectsStyle === 'minimal_rows'" class="card p-3 p-md-4 shadow-sm border-0 content-card" :style="{ borderRadius: projectsDesign.card_radius || '20px' }">
                  <div class="d-flex flex-column divide-y">
                    <div 
                      v-for="(proj, rIdx) in projects" 
                      :key="proj.id" 
                      class="py-3 px-2 d-flex align-items-center justify-content-between flex-wrap gap-3 border-bottom transition"
                      :class="'project-hover-' + (projectsDesign.hover_effect || 'slide_accent')"
                    >
                      <div class="d-flex align-items-center gap-3 min-w-0">
                        <span v-if="projectsDesign.show_number_badge !== false" class="fw-black text-muted font-monospace small">0{{ rIdx + 1 }}</span>
                        <template v-if="projectsDesign.show_logo !== false">
                          <img 
                            v-if="getEffectiveProjectLogo(proj)" 
                            :src="formatImageUrl(getEffectiveProjectLogo(proj))" 
                            :alt="proj.name || proj.title"
                            class="border object-fit-contain flex-shrink-0 bg-white"
                            :class="getProjectLogoShapeClass()"
                            :style="getProjectLogoSizeStyle(false)"
                          />
                          <div v-else class="text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" :class="getProjectLogoShapeClass()" :style="{ ...getProjectLogoSizeStyle(false), backgroundColor: accentColor }">
                            <i class="bi bi-rocket-takeoff"></i>
                          </div>
                        </template>
                        <div class="min-w-0">
                          <h3 class="h6 fw-bold mb-0 text-dark">{{ proj.name || proj.title }}</h3>
                          <p class="extra-small text-muted mb-0 text-truncate" style="max-width: 520px;">{{ stripHtmlTags(proj.pitch || proj.description) }}</p>
                        </div>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <span v-if="projectsDesign.show_category_badge !== false" class="badge bg-light text-dark border extra-small">{{ getEffectiveProjectBadge(proj) }}</span>
                        <button v-if="projectsDesign.show_case_study_btn !== false" type="button" @click="openCaseStudyModal(proj)" class="btn btn-xs btn-light border rounded-pill px-3 extra-small fw-semibold">Aperçu</button>
                        <a :href="'/projet/' + (proj.slug || proj.id)" target="_blank" class="btn btn-xs btn-dark rounded-pill px-3 extra-small fw-bold">Voir &rarr;</a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- CAROUSEL / SLIDER VARIANT -->
                <div v-else-if="projectsStyle === 'carousel'" class="carousel-project-container card p-4 p-md-5 shadow-sm border-0 content-card" :style="{ borderRadius: projectsDesign.card_radius || '20px' }">
                  <div v-if="projects[currentProjectSlide]" class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                      <div class="d-flex align-items-center gap-3">
                        <template v-if="projectsDesign.show_logo !== false">
                          <img 
                            v-if="getEffectiveProjectLogo(projects[currentProjectSlide])" 
                            :src="formatImageUrl(getEffectiveProjectLogo(projects[currentProjectSlide]))" 
                            class="border flex-shrink-0 object-fit-contain" 
                            :class="getProjectLogoShapeClass()"
                            :style="getProjectLogoSizeStyle(false)"
                            :alt="projects[currentProjectSlide].name || projects[currentProjectSlide].title"
                          />
                          <div v-else class="text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" :class="getProjectLogoShapeClass()" :style="{ ...getProjectLogoSizeStyle(false), backgroundColor: accentColor }">
                            <i class="bi bi-rocket-takeoff fs-4"></i>
                          </div>
                        </template>
                        <div class="min-w-0">
                          <h3 class="h5 fw-bold mb-0 text-dark">{{ projects[currentProjectSlide].name || projects[currentProjectSlide].title }}</h3>
                          <span v-if="projectsDesign.show_category_badge !== false" class="badge bg-secondary-subtle text-secondary rounded-pill extra-small">{{ getEffectiveProjectBadge(projects[currentProjectSlide]) }}</span>
                        </div>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-muted border extra-small">{{ currentProjectSlide + 1 }} / {{ projects.length }}</span>
                      </div>
                    </div>
                    
                    <div class="text-content leading-relaxed my-2" v-html="formatHtml(projects[currentProjectSlide].pitch || projects[currentProjectSlide].description || 'Projet innovant propulsé sur Tech237.')"></div>
                    
                    <div class="d-flex align-items-center justify-content-between pt-3 border-top flex-wrap gap-2">
                      <div v-if="projectsDesign.show_stats !== false" class="d-flex align-items-center gap-3 extra-small text-muted">
                        <span v-if="projects[currentProjectSlide].views_count"><i class="bi bi-eye me-1"></i>{{ projects[currentProjectSlide].views_count }} vues</span>
                        <span v-if="projects[currentProjectSlide].upvotes_count"><i class="bi bi-heart-fill text-danger me-1"></i>{{ projects[currentProjectSlide].upvotes_count }} upvotes</span>
                      </div>
                      <div class="d-flex align-items-center gap-2 ms-auto">
                        <button type="button" @click="prevProject" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                          <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" @click="nextProject" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                          <i class="bi bi-chevron-right"></i>
                        </button>
                        <a :href="'/projet/' + (projects[currentProjectSlide].slug || projects[currentProjectSlide].id)" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold extra-small">
                          {{ projectsDesign.cta_label || 'Consulter la fiche' }} &rarr;
                        </a>
                      </div>
                    </div>

                    <!-- Slide Indicator Dots -->
                    <div class="d-flex justify-content-center gap-1 mt-2">
                      <span 
                        v-for="(_, pIdx) in projects" 
                        :key="pIdx"
                        @click="currentProjectSlide = pIdx"
                        class="cursor-pointer rounded-pill transition"
                        :style="{
                          width: currentProjectSlide === pIdx ? '24px' : '8px',
                          height: '8px',
                          backgroundColor: currentProjectSlide === pIdx ? accentColor : '#CBD5E1'
                        }"
                      ></span>
                    </div>
                  </div>
                </div>

                <!-- TIMELINE VARIANT -->
                <div v-else-if="projectsStyle === 'timeline'" class="card p-4 p-md-5 shadow-sm border-0 content-card" :style="{ borderRadius: projectsDesign.card_radius || '20px' }">
                  <div class="timeline-stream d-flex flex-column gap-4">
                    <div v-for="proj in projects" :key="proj.id" class="d-flex gap-3 position-relative">
                      <div class="timeline-dot rounded-circle flex-shrink-0 mt-1" :style="{ backgroundColor: accentColor }"></div>
                      <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                          <h3 class="h6 fw-bold text-dark mb-0">{{ proj.name || proj.title }}</h3>
                          <span v-if="projectsDesign.show_category_badge !== false" class="badge bg-light text-muted border extra-small">{{ getEffectiveProjectBadge(proj) }}</span>
                        </div>
                        <div class="small text-muted mt-1 mb-2 leading-relaxed" v-html="formatHtml(proj.pitch || proj.description)"></div>
                        <a :href="'/projet/' + (proj.slug || proj.id)" target="_blank" class="btn btn-xs btn-link p-0 text-decoration-none fw-bold" :style="{ color: accentColor }">
                          {{ projectsDesign.cta_label || 'Découvrir ce projet sur Tech237' }} &rarr;
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- MULTI-COLUMN CARDS GRID OR SHOWCASE LARGE -->
                <div v-else class="row g-4">
                  <div v-for="(proj, cIdx) in projects" :key="proj.id" :class="projectsStyle === 'showcase_large' ? 'col-12' : projectColsClass">
                    <div 
                      class="card border-0 shadow-sm h-100 content-card p-4 d-flex flex-column justify-content-between transition"
                      :class="[
                        'project-hover-' + (projectsDesign.hover_effect || 'lift_shadow'),
                        projectsDesign.text_align === 'center' ? 'text-center' : ''
                      ]"
                      :style="{ borderRadius: projectsDesign.card_radius || '20px' }"
                    >
                      <div>
                        <div class="d-flex align-items-center gap-3 mb-3" :class="projectsDesign.text_align === 'center' ? 'flex-column justify-content-center' : ''">
                          <template v-if="projectsDesign.show_logo !== false">
                            <img 
                              v-if="getEffectiveProjectLogo(proj)" 
                              :src="formatImageUrl(getEffectiveProjectLogo(proj))" 
                              class="border flex-shrink-0 object-fit-contain bg-white project-logo-img" 
                              :class="getProjectLogoShapeClass()"
                              :style="getProjectLogoSizeStyle(false)"
                              :alt="proj.name || proj.title"
                            />
                            <div v-else class="text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0 project-logo-img" :class="getProjectLogoShapeClass()" :style="{ ...getProjectLogoSizeStyle(false), backgroundColor: accentColor }">
                              <i class="bi bi-rocket-takeoff"></i>
                            </div>
                          </template>
                          <div class="min-w-0">
                            <div v-if="projectsDesign.show_number_badge !== false" class="extra-small fw-bold mb-1" :style="{ color: accentColor }">PROJET #0{{ cIdx + 1 }}</div>
                            <h3 class="h6 fw-bold mb-0 text-truncate">{{ proj.name || proj.title }}</h3>
                            <small v-if="projectsDesign.show_category_badge !== false" class="text-muted extra-small">{{ getEffectiveProjectBadge(proj) }}</small>
                          </div>
                        </div>
                        <div class="small text-muted mb-3 leading-relaxed" v-html="formatHtml(proj.pitch || proj.description || 'Projet innovant propulsé sur Tech237.')"></div>
                      </div>

                      <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto flex-wrap gap-2">
                        <div v-if="projectsDesign.show_stats !== false" class="d-flex align-items-center gap-2 extra-small text-muted">
                          <span v-if="proj.views_count"><i class="bi bi-eye me-1"></i>{{ proj.views_count }}</span>
                          <span v-if="proj.upvotes_count"><i class="bi bi-heart-fill text-danger me-1"></i>{{ proj.upvotes_count }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1 d-print-none ms-auto">
                          <button v-if="projectsDesign.show_case_study_btn !== false" type="button" @click="openCaseStudyModal(proj)" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold extra-small">
                            <i class="bi bi-eye-fill me-1"></i> Aperçu
                          </button>
                          <a :href="'/projet/' + (proj.slug || proj.id)" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold extra-small">
                            {{ projectsDesign.cta_label || 'Fiche' }} &rarr;
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </section>

              <!-- 5. POCS / MINI-ACTUS BLOCK -->
              <section v-else-if="block.id === 'pocs' && pocs.length" class="portfolio-block h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div class="d-flex align-items-center gap-2">
                    <span class="section-icon-badge" :style="{ color: accentColor }">
                      <i class="bi bi-lightning-charge-fill fs-5 text-warning"></i>
                    </span>
                    <h2 class="h5 fw-bold mb-0 section-heading">Mini-Actus &amp; POCs</h2>
                  </div>
                  <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1 extra-small fw-bold">
                    {{ pocs.length }} POC(s)
                  </span>
                </div>

                <div class="row g-3">
                  <div v-for="poc in pocs" :key="poc.id" class="col-md-6">
                    <div class="card p-3 rounded-4 shadow-sm border-0 content-card h-100 d-flex flex-column justify-content-between">
                      <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                          <span class="badge bg-warning text-dark extra-small rounded-pill">POC</span>
                          <strong class="small text-dark text-truncate">{{ poc.title }}</strong>
                        </div>
                        <div class="extra-small text-muted mb-2 leading-relaxed" v-html="formatHtml(poc.content || poc.summary)"></div>
                      </div>
                      <div class="d-flex align-items-center justify-content-between pt-2 border-top extra-small text-muted mt-auto">
                        <span>{{ formatDateShort(poc.created_at) }}</span>
                        <a :href="'/communaute/pocs/' + (poc.slug || poc.id)" target="_blank" class="text-decoration-none fw-bold" :style="{ color: accentColor }">
                          Lire la suite &rarr;
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 6. EXPERIENCES BLOCK (100% Customizable: 5 Layouts, Images/Logos, Icons, Colors & Tags) -->
              <section v-else-if="block.id === 'experiences' && (portfolioUser.experiences && portfolioUser.experiences.length)" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" :class="experienceDesign.text_align === 'center' ? 'justify-content-center text-center' : ''">
                    <div>
                      <div class="d-flex align-items-center gap-2" :class="experienceDesign.text_align === 'center' ? 'justify-content-center' : ''">
                        <span class="section-icon-badge" :style="{ color: accentColor }">
                          <i class="bi fs-5" :class="experienceDesign.icon || 'bi-briefcase-fill'"></i>
                        </span>
                        <h2 class="h5 fw-bold mb-0 section-heading">{{ experienceDesign.title || 'Parcours & Expériences' }}</h2>
                      </div>
                      <p v-if="experienceDesign.subtitle" class="extra-small text-muted mb-0 mt-1">{{ experienceDesign.subtitle }}</p>
                    </div>
                  </div>

                  <!-- Layout: Cards Grid (2 columns) or Step Cards -->
                  <div v-if="experienceDesign.layout_style === 'cards_grid' || experienceDesign.layout_style === 'step_cards'" class="row g-3">
                    <div v-for="(exp, eIdx) in portfolioUser.experiences" :key="eIdx" class="col-md-6">
                      <div 
                        class="p-4 rounded-4 bg-light border h-100 position-relative transition"
                        :class="[
                          'exp-hover-' + (experienceDesign.hover_effect || 'lift'),
                          experienceDesign.text_align === 'center' ? 'text-center' : ''
                        ]"
                        :style="{ borderTop: '4px solid ' + (exp.color || accentColor) }"
                      >
                        <!-- Top Banner Image if configured -->
                        <div v-if="exp.image_url && experienceDesign.image_position === 'top_banner'" class="mb-3 rounded-3 overflow-hidden border">
                          <img :src="formatImageUrl(exp.image_url)" :alt="exp.company" class="w-100 object-fit-cover" style="height: 120px;" />
                        </div>

                        <div class="d-flex align-items-start gap-3" :class="experienceDesign.image_position === 'right' ? 'flex-row-reverse' : ''">
                          <img 
                            v-if="exp.image_url && experienceDesign.image_position !== 'hidden' && experienceDesign.image_position !== 'top_banner'" 
                            :src="formatImageUrl(exp.image_url)" 
                            :alt="exp.company" 
                            class="rounded-3 border bg-white p-1 object-fit-contain flex-shrink-0" 
                            style="width: 48px; height: 48px;" 
                          />
                          <div 
                            v-else 
                            class="rounded-3 text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0 shadow-2xs"
                            style="width: 44px; height: 44px;"
                            :style="{ backgroundColor: exp.color || accentColor }"
                          >
                            <span v-if="experienceDesign.layout_style === 'step_cards'">0{{ eIdx + 1 }}</span>
                            <i v-else class="bi fs-5" :class="exp.icon || 'bi-briefcase-fill'"></i>
                          </div>

                          <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                              <span class="badge bg-white text-dark border extra-small">{{ exp.period }}</span>
                              <span v-if="exp.contract_type" class="badge rounded-pill text-white extra-small" :style="{ backgroundColor: exp.color || accentColor }">{{ exp.contract_type }}</span>
                            </div>
                            <h3 class="h6 fw-bold text-dark mb-0">{{ exp.role }}</h3>
                            <div class="small fw-semibold text-muted mb-2">
                              {{ exp.company }}
                              <span v-if="exp.location" class="extra-small ms-1"><i class="bi bi-geo-alt-fill text-danger"></i> {{ exp.location }}</span>
                            </div>
                            <div v-if="exp.description" class="small text-muted mb-2 leading-relaxed" v-html="formatHtml(exp.description)"></div>
                            <div v-if="exp.tags" class="d-flex flex-wrap gap-1 mt-2">
                              <span v-for="(tg, tgIdx) in parseCommaTags(exp.tags)" :key="tgIdx" class="badge bg-white text-dark border rounded-pill extra-small px-2 py-1">
                                {{ tg }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Layout: Timeline (Left, Alternating, or Editorial Rows) -->
                  <div v-else class="timeline-stream d-flex flex-column gap-4">
                    <div 
                      v-for="(exp, eIdx) in portfolioUser.experiences" 
                      :key="eIdx" 
                      class="p-3 p-md-4 rounded-4 bg-light border position-relative transition"
                      :class="[
                        'exp-hover-' + (experienceDesign.hover_effect || 'lift'),
                        experienceDesign.text_align === 'center' ? 'text-center' : (experienceDesign.text_align === 'justify' ? 'text-justify' : 'text-start')
                      ]"
                      :style="{ borderLeft: '4px solid ' + (exp.color || accentColor) }"
                    >
                      <div v-if="exp.image_url && experienceDesign.image_position === 'top_banner'" class="mb-3 rounded-3 overflow-hidden border">
                        <img :src="formatImageUrl(exp.image_url)" :alt="exp.company" class="w-100 object-fit-cover" style="height: 140px;" />
                      </div>

                      <div class="d-flex align-items-start gap-3 flex-wrap" :class="experienceDesign.image_position === 'right' ? 'flex-row-reverse' : ''">
                        <img 
                          v-if="exp.image_url && experienceDesign.image_position !== 'hidden' && experienceDesign.image_position !== 'top_banner'" 
                          :src="formatImageUrl(exp.image_url)" 
                          :alt="exp.company" 
                          class="rounded-3 border bg-white p-1 object-fit-contain flex-shrink-0 shadow-2xs" 
                          style="width: 52px; height: 52px;" 
                        />
                        <div 
                          v-else 
                          class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-2xs" 
                          style="width: 42px; height: 42px;" 
                          :style="{ backgroundColor: exp.color || accentColor }"
                        >
                          <i class="bi" :class="exp.icon || 'bi-briefcase-fill'"></i>
                        </div>

                        <div class="flex-grow-1 min-w-0">
                          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                            <div>
                              <strong class="text-dark fs-6">{{ exp.role }}</strong>
                              <span class="fw-semibold text-muted ms-1">&bull; {{ exp.company }}</span>
                              <span v-if="exp.location" class="extra-small text-muted ms-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ exp.location }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                              <span v-if="exp.contract_type" class="badge rounded-pill text-white extra-small" :style="{ backgroundColor: exp.color || accentColor }">{{ exp.contract_type }}</span>
                              <span class="badge bg-white text-dark border extra-small">{{ exp.period }}</span>
                            </div>
                          </div>
                          <div v-if="exp.description" class="small text-muted mb-0 leading-relaxed mt-2" v-html="formatHtml(exp.description)"></div>
                          <div v-if="exp.tags" class="d-flex flex-wrap gap-1 mt-2">
                            <span v-for="(tg, tgIdx) in parseCommaTags(exp.tags)" :key="tgIdx" class="badge bg-white text-dark border rounded-pill extra-small px-2 py-1">
                              {{ tg }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 7. EDUCATIONS BLOCK -->
              <section v-else-if="block.id === 'educations' && (portfolioUser.educations && portfolioUser.educations.length)" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="section-icon-badge" :style="{ color: accentColor }">
                      <i class="bi bi-mortarboard-fill fs-5"></i>
                    </span>
                    <h2 class="h5 fw-bold mb-0 section-heading">Formations &amp; Certifications</h2>
                  </div>

                  <div class="row g-3">
                    <div v-for="(edu, dIdx) in portfolioUser.educations" :key="dIdx" class="col-md-6">
                      <div class="p-3 rounded-3 bg-light border h-100">
                        <span class="badge bg-white text-dark border extra-small mb-1">{{ edu.year }}</span>
                        <strong class="d-block small text-dark fw-bold">{{ edu.degree }}</strong>
                        <span class="d-block extra-small text-muted mb-1">{{ edu.institution }}</span>
                        <div v-if="edu.description" class="extra-small text-muted mb-0" v-html="formatHtml(edu.description)"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 8. BADGES BLOCK -->
              <section v-else-if="block.id === 'badges' && badges.length" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                      <span class="section-icon-badge" :style="{ color: accentColor }">
                        <i class="bi bi-award-fill fs-5 text-warning"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">Trophées &amp; Badges Tech237</h2>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill extra-small">
                      {{ badges.length }} badge(s)
                    </span>
                  </div>

                  <div class="row g-3">
                    <div v-for="b in badges" :key="b.id" class="col-6 col-md-4 col-lg-3">
                      <div class="p-3 rounded-4 text-center border h-100 bg-light">
                        <div class="fs-2 mb-1">{{ b.icon || '🏆' }}</div>
                        <strong class="d-block text-dark extra-small fw-bold">{{ b.name }}</strong>
                        <small class="extra-small text-muted" style="font-size: 10px;">{{ b.description }}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9. CUSTOM SECTIONS -->
              <section v-else-if="block.id === 'custom' && (portfolioUser.custom_sections && portfolioUser.custom_sections.length)" class="portfolio-block h-100">
                <div v-for="(sec, cIdx) in portfolioUser.custom_sections" :key="cIdx" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card mb-4">
                  <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi" :class="sec.icon || 'bi-stars'" :style="{ color: accentColor }"></i> {{ sec.title }}
                  </h2>
                  <div class="text-content leading-relaxed" v-html="formatHtml(sec.content)"></div>
                </div>
              </section>

              <!-- 9.B NOW SECTION (« EN CE MOMENT » — 100% Customizable) -->
              <section v-else-if="block.id === 'now' && hasNowSection" class="portfolio-block h-100">
                <div 
                  class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100"
                  :class="nowSection.text_align === 'center' ? 'text-center' : ''"
                >
                  <div class="d-flex align-items-center gap-2 mb-4" :class="nowSection.text_align === 'center' ? 'justify-content-center' : ''">
                    <span class="section-icon-badge" :style="{ color: accentColor }">
                      <i class="bi fs-5" :class="nowSection.icon || 'bi-compass-fill'"></i>
                    </span>
                    <h2 class="h5 fw-bold mb-0 section-heading">{{ nowSection.title || 'En ce moment (Now)' }}</h2>
                  </div>

                  <div class="row g-4 align-items-center">
                    <div v-if="nowSection.image_url" :class="nowSection.layout_style === 'banner_split' ? 'col-md-5' : 'col-12 mb-2'">
                      <img :src="formatImageUrl(nowSection.image_url)" alt="En ce moment" class="img-fluid rounded-4 shadow-sm border object-fit-cover w-100" style="max-height: 220px;" />
                    </div>

                    <div :class="nowSection.image_url && nowSection.layout_style === 'banner_split' ? 'col-md-7' : 'col-12'">
                      <div class="row g-3">
                        <div v-if="nowSection.project" :class="nowSection.layout_style === 'vertical_list' ? 'col-12' : 'col-md-4'">
                          <div class="p-3 rounded-4 bg-light border h-100 transition" :class="'elem-anim-' + (nowSection.animation || 'lift')">
                            <span class="badge bg-primary-subtle text-primary extra-small mb-2">
                              <i class="bi me-1" :class="nowSection.project_icon || 'bi-hammer'"></i> {{ nowSection.project_label || 'Sur quoi je travaille' }}
                            </span>
                            <p class="small fw-semibold text-dark mb-0">{{ nowSection.project }}</p>
                          </div>
                        </div>
                        <div v-if="nowSection.learning" :class="nowSection.layout_style === 'vertical_list' ? 'col-12' : 'col-md-4'">
                          <div class="p-3 rounded-4 bg-light border h-100 transition" :class="'elem-anim-' + (nowSection.animation || 'lift')">
                            <span class="badge bg-success-subtle text-success extra-small mb-2">
                              <i class="bi me-1" :class="nowSection.learning_icon || 'bi-book-half'"></i> {{ nowSection.learning_label || 'En apprentissage' }}
                            </span>
                            <p class="small fw-semibold text-dark mb-0">{{ nowSection.learning }}</p>
                          </div>
                        </div>
                        <div v-if="nowSection.goal" :class="nowSection.layout_style === 'vertical_list' ? 'col-12' : 'col-md-4'">
                          <div class="p-3 rounded-4 bg-light border h-100 transition" :class="'elem-anim-' + (nowSection.animation || 'lift')">
                            <span class="badge bg-warning-subtle text-warning-emphasis extra-small mb-2">
                              <i class="bi me-1" :class="nowSection.goal_icon || 'bi-bullseye'"></i> {{ nowSection.goal_label || 'Prochain objectif' }}
                            </span>
                            <p class="small fw-semibold text-dark mb-0">{{ nowSection.goal }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9.C GITHUB LIVE STATS BLOCK (100% Customizable) -->
              <section v-else-if="block.id === 'github' && hasGithubStats" class="portfolio-block h-100">
                <div 
                  class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100"
                  :class="githubStatsConfig.text_align === 'center' ? 'text-center' : ''"
                >
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" :class="githubStatsConfig.text_align === 'center' ? 'justify-content-center' : ''">
                    <div class="d-flex align-items-center gap-2">
                      <span class="section-icon-badge" :style="{ color: accentColor }">
                        <i class="bi fs-5" :class="githubStatsConfig.icon || 'bi-github'"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">{{ githubStatsConfig.title || 'Statistiques GitHub & Open-Source' }}</h2>
                    </div>
                    <a :href="'https://github.com/' + githubUsername" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3 extra-small fw-bold">
                      <i class="bi bi-github me-1"></i> @{{ githubUsername }}
                    </a>
                  </div>

                  <div v-if="githubStatsConfig.image_url" class="mb-4 text-center">
                    <img :src="formatImageUrl(githubStatsConfig.image_url)" alt="GitHub Banner" class="img-fluid rounded-4 shadow-sm border object-fit-cover" style="max-height: 200px; width: 100%;" />
                  </div>

                  <div v-if="githubProfile && githubStatsConfig.layout_style !== 'repos_focus'" class="row g-3 mb-4">
                    <div class="col-4">
                      <div class="p-3 rounded-4 bg-light border text-center transition" :class="'elem-anim-' + (githubStatsConfig.animation || 'lift')">
                        <strong class="fs-4 fw-black d-block" :style="{ color: accentColor }">{{ githubProfile.public_repos || 0 }}</strong>
                        <span class="extra-small text-muted">Dépôts Publics</span>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="p-3 rounded-4 bg-light border text-center transition" :class="'elem-anim-' + (githubStatsConfig.animation || 'lift')">
                        <strong class="fs-4 fw-black d-block" :style="{ color: accentColor }">{{ githubTotalStars }}</strong>
                        <span class="extra-small text-muted">Stars cumulées</span>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="p-3 rounded-4 bg-light border text-center transition" :class="'elem-anim-' + (githubStatsConfig.animation || 'lift')">
                        <strong class="fs-4 fw-black d-block" :style="{ color: accentColor }">{{ githubProfile.followers || 0 }}</strong>
                        <span class="extra-small text-muted">Followers</span>
                      </div>
                    </div>
                  </div>

                  <div v-if="githubTopLanguages.length && githubStatsConfig.show_languages !== false" class="mb-4">
                    <span class="extra-small fw-bold text-muted d-block mb-2">Langages les plus utilisés :</span>
                    <div class="d-flex flex-wrap gap-2" :class="githubStatsConfig.text_align === 'center' ? 'justify-content-center' : ''">
                      <span v-for="lang in githubTopLanguages" :key="lang.name" class="badge bg-light text-dark border rounded-pill px-3 py-2 extra-small fw-semibold">
                        <i class="bi bi-code-slash me-1" :style="{ color: accentColor }"></i>{{ lang.name }} ({{ lang.percent }}%)
                      </span>
                    </div>
                  </div>

                  <div v-if="githubRepos.length && githubStatsConfig.layout_style !== 'compact_banner'" class="row g-3">
                    <div v-for="repo in githubRepos.slice(0, Number(githubStatsConfig.repos_count) || 4)" :key="repo.id" class="col-md-6">
                      <a 
                        :href="repo.html_url" 
                        target="_blank" 
                        class="p-3 rounded-4 bg-light border d-block text-decoration-none h-100 transition"
                        :class="'elem-anim-' + (githubStatsConfig.animation || 'lift')"
                      >
                        <div class="d-flex align-items-center justify-content-between mb-1">
                          <strong class="small text-dark text-truncate"><i class="bi bi-journal-code me-1"></i>{{ repo.name }}</strong>
                          <span class="badge bg-white text-dark border extra-small">⭐ {{ repo.stargazers_count || 0 }}</span>
                        </div>
                        <p class="extra-small text-muted mb-2 text-truncate">{{ repo.description || 'Projet open-source hébergé sur GitHub' }}</p>
                        <div class="d-flex align-items-center justify-content-between extra-small text-muted">
                          <span v-if="repo.language" class="badge bg-secondary-subtle text-secondary extra-small">{{ repo.language }}</span>
                          <span><i class="bi bi-diagram-2 me-1"></i>{{ repo.forks_count || 0 }} forks</span>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9.D CERTIFICATIONS & AWARDS BLOCK -->
              <section v-else-if="block.id === 'certifications' && certificationsList.length" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                      <span class="section-icon-badge" :style="{ color: accentColor }">
                        <i class="bi bi-patch-check-fill fs-5"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">Certifications Vérifiées &amp; Distinctions</h2>
                    </div>
                    <span class="badge bg-info-subtle text-info-emphasis rounded-pill extra-small">
                      {{ certificationsList.length }} certificat(s)
                    </span>
                  </div>

                  <div class="row g-3">
                    <div v-for="(cert, cIdx) in certificationsList" :key="cIdx" class="col-md-6">
                      <div class="p-3 rounded-4 bg-light border h-100 d-flex flex-column justify-content-between">
                        <div>
                          <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-white text-dark border extra-small"><i class="bi bi-award-fill text-warning me-1"></i>{{ cert.issuer || 'Certification' }}</span>
                            <span v-if="cert.year" class="extra-small text-muted fw-bold">{{ cert.year }}</span>
                          </div>
                          <strong class="d-block small text-dark fw-bold">{{ cert.title }}</strong>
                        </div>
                        <div v-if="cert.credential_url" class="mt-3 pt-2 border-top">
                          <a :href="cert.credential_url" target="_blank" class="extra-small fw-bold text-decoration-none d-inline-flex align-items-center gap-1" :style="{ color: accentColor }">
                            <i class="bi bi-shield-check"></i> Vérifier le certificat officiel <i class="bi bi-box-arrow-up-right"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9.E.0 WORK PROCESS / METHODOLOGY IN 4 STEPS BLOCK (100% Customizable) -->
              <section v-else-if="block.id === 'process' && hasWorkProcess" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between mb-4" :class="workProcessConfig.text_align === 'center' ? 'justify-content-center text-center' : ''">
                    <div>
                      <div class="d-flex align-items-center gap-2" :class="workProcessConfig.text_align === 'center' ? 'justify-content-center' : ''">
                        <span class="section-icon-badge" :style="{ color: accentColor }">
                          <i class="bi fs-5" :class="workProcessConfig.icon || 'bi-diagram-3-fill'"></i>
                        </span>
                        <h2 class="h5 fw-bold mb-0 section-heading">{{ workProcessConfig.title || 'Comment je travaille (En 4 étapes)' }}</h2>
                      </div>
                      <p v-if="workProcessConfig.subtitle" class="extra-small text-muted mb-0 mt-1">{{ workProcessConfig.subtitle }}</p>
                    </div>
                  </div>

                  <div class="row g-4">
                    <div 
                      v-for="(step, stIdx) in workProcessSteps" 
                      :key="stIdx" 
                      :class="workProcessConfig.layout_style === 'zigzag_cards' || workProcessConfig.layout_style === 'minimal_numbered' ? 'col-md-6' : 'col-md-6 col-lg-3'"
                    >
                      <div 
                        class="p-4 rounded-4 bg-light border h-100 position-relative transition"
                        :class="[
                          'elem-anim-' + (workProcessConfig.animation || 'lift'),
                          workProcessConfig.text_align === 'center' ? 'text-center' : ''
                        ]"
                        :style="{ borderTop: '4px solid ' + (step.color || accentColor) }"
                      >
                        <div v-if="step.image_url" class="mb-3 rounded-3 overflow-hidden border">
                          <img :src="formatImageUrl(step.image_url)" :alt="step.title" class="w-100 object-fit-cover" style="height: 120px;" />
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-3" :class="workProcessConfig.text_align === 'center' ? 'justify-content-center gap-2' : ''">
                          <div 
                            class="rounded-circle text-white fw-black d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                            style="width: 42px; height: 42px; font-size: 16px;"
                            :style="{ backgroundColor: step.color || accentColor }"
                          >
                            <i v-if="step.icon" class="bi" :class="step.icon"></i>
                            <span v-else>0{{ stIdx + 1 }}</span>
                          </div>
                          <span v-if="step.badge" class="badge bg-white text-dark border rounded-pill extra-small px-2 py-1">{{ step.badge }}</span>
                        </div>

                        <h3 class="h6 fw-bold text-dark mb-2">{{ step.title }}</h3>
                        <p class="extra-small text-muted leading-relaxed mb-0">{{ step.description }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9.E PRICING PACKAGES & SERVICES BLOCK -->
              <section v-else-if="block.id === 'pricing' && pricingPackagesList.length" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                      <span class="section-icon-badge" :style="{ color: accentColor }">
                        <i class="bi bi-tags-fill fs-5"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">Mes Offres &amp; Grille Tarifaire</h2>
                    </div>
                  </div>

                  <div class="row g-4">
                    <div v-for="(pkg, pIdx) in pricingPackagesList" :key="pIdx" :class="pricingPackagesList.length === 1 ? 'col-12' : (pricingPackagesList.length === 2 ? 'col-md-6' : 'col-md-6 col-lg-4')">
                      <div 
                        class="p-4 rounded-4 border h-100 d-flex flex-column justify-content-between position-relative transition-hover"
                        :class="pkg.is_popular ? 'bg-white shadow-sm border-2' : 'bg-light'"
                        :style="pkg.is_popular ? { borderColor: accentColor } : {}"
                      >
                        <div>
                          <span v-if="pkg.is_popular" class="badge rounded-pill text-white extra-small position-absolute top-0 end-0 m-3" :style="{ backgroundColor: accentColor }">
                            ⭐ Recommandé
                          </span>
                          <h3 class="h6 fw-bold text-dark mb-2">{{ pkg.title }}</h3>
                          <div class="mb-3">
                            <span class="fs-3 fw-black" :style="{ color: accentColor }">{{ pkg.price }}</span>
                            <span v-if="pkg.unit" class="extra-small text-muted ms-1">{{ pkg.unit }}</span>
                          </div>
                          <ul class="list-unstyled small text-muted d-flex flex-column gap-2 mb-4">
                            <li v-for="(feat, fIdx) in parsePackageFeatures(pkg.features)" :key="fIdx" class="d-flex align-items-start gap-2">
                              <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                              <span>{{ feat }}</span>
                            </li>
                          </ul>
                        </div>
                        <button 
                          type="button" 
                          @click="selectPackageForContact(pkg)" 
                          class="btn rounded-pill w-100 py-2 fw-bold text-white shadow-2xs"
                          :style="{ backgroundColor: accentColor }"
                        >
                          Commander ce pack &rarr;
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9.F TESTIMONIALS & SOCIAL PROOF BLOCK (Slider Carousel, Marquee, Grid & Illustrated Avatars) -->
              <section v-else-if="block.id === 'testimonials' && testimonialsList.length" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" :class="testimonialsDesign.text_align === 'center' ? 'justify-content-center text-center' : ''">
                    <div>
                      <div class="d-flex align-items-center gap-2" :class="testimonialsDesign.text_align === 'center' ? 'justify-content-center' : ''">
                        <span class="section-icon-badge" :style="{ color: accentColor }">
                          <i class="bi bi-chat-quote-fill fs-5"></i>
                        </span>
                        <h2 class="h5 fw-bold mb-0 section-heading">{{ testimonialsDesign.title || 'Témoignages & Recommandations' }}</h2>
                      </div>
                      <p v-if="testimonialsDesign.subtitle" class="extra-small text-muted mb-0 mt-1">{{ testimonialsDesign.subtitle }}</p>
                    </div>

                    <!-- Slider Prev/Next Controls when layout_style === 'slider' -->
                    <div v-if="testimonialsDesign.layout_style === 'slider' && testimonialsList.length > 1" class="d-flex align-items-center gap-2 d-print-none">
                      <button type="button" @click="prevTestimonialSlide" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-2xs" style="width: 34px; height: 34px;">
                        <i class="bi bi-chevron-left"></i>
                      </button>
                      <span class="badge bg-light text-dark border extra-small">{{ currentTestimonialSlide + 1 }} / {{ testimonialsList.length }}</span>
                      <button type="button" @click="nextTestimonialSlide" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-2xs" style="width: 34px; height: 34px;">
                        <i class="bi bi-chevron-right"></i>
                      </button>
                    </div>
                  </div>

                  <!-- MODE 1: INTERACTIVE SLIDER / CAROUSEL -->
                  <div v-if="testimonialsDesign.layout_style === 'slider'">
                    <div class="row g-3">
                      <div 
                        v-for="(testi, vIdx) in visibleSliderTestimonials" 
                        :key="'sl-' + currentTestimonialSlide + '-' + vIdx" 
                        :class="Number(testimonialsDesign.slides_per_view) === 2 && visibleSliderTestimonials.length > 1 ? 'col-md-6' : 'col-12'"
                      >
                        <div 
                          class="p-4 rounded-4 bg-light border h-100 d-flex flex-column justify-content-between transition"
                          :class="[
                            'elem-anim-' + (testimonialsDesign.animation || 'lift'),
                            testimonialsDesign.text_align === 'center' ? 'text-center' : ''
                          ]"
                        >
                          <div>
                            <div v-if="testimonialsDesign.show_stars !== false" class="text-warning small mb-2">
                              <i v-for="star in (Number(testi.rating) || 5)" :key="star" class="bi bi-star-fill me-1"></i>
                            </div>
                            <p class="fs-6 text-dark fst-italic leading-relaxed mb-3">« {{ testi.comment }} »</p>
                          </div>
                          <div class="d-flex align-items-center gap-3 pt-3 border-top" :class="testimonialsDesign.text_align === 'center' ? 'justify-content-center' : ''">
                            <img 
                              v-if="getTestimonialAvatarSrc(testi)" 
                              :src="getTestimonialAvatarSrc(testi)" 
                              :alt="testi.author_name" 
                              class="rounded-circle border object-fit-cover flex-shrink-0 shadow-2xs" 
                              style="width: 46px; height: 46px;" 
                            />
                            <div v-else class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center small flex-shrink-0" style="width: 46px; height: 46px;" :style="{ backgroundColor: accentColor }">
                              {{ getInitials(testi.author_name) }}
                            </div>
                            <div class="min-w-0 text-start">
                              <strong class="d-block small text-dark text-truncate">{{ testi.author_name }}</strong>
                              <span class="extra-small text-muted d-block text-truncate">{{ testi.author_role }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Slider Dots -->
                    <div v-if="testimonialsList.length > 1" class="d-flex justify-content-center gap-2 mt-3">
                      <span
                        v-for="(_, dIdx) in testimonialsList"
                        :key="'tdot-' + dIdx"
                        @click="currentTestimonialSlide = dIdx"
                        class="cursor-pointer rounded-pill transition"
                        :style="{
                          width: currentTestimonialSlide === dIdx ? '24px' : '8px',
                          height: '8px',
                          backgroundColor: currentTestimonialSlide === dIdx ? accentColor : '#CBD5E1'
                        }"
                      ></span>
                    </div>
                  </div>

                  <!-- MODE 2: INFINITE MARQUEE RIBBON -->
                  <div v-else-if="testimonialsDesign.layout_style === 'marquee'" class="overflow-hidden py-2">
                    <div class="tech-marquee-track d-flex align-items-stretch gap-3">
                      <div 
                        v-for="(testi, mIdx) in [...testimonialsList, ...testimonialsList]" 
                        :key="'tmq-' + mIdx" 
                        class="p-4 rounded-4 bg-light border d-flex flex-column justify-content-between flex-shrink-0"
                        style="width: 340px; white-space: normal;"
                      >
                        <div>
                          <div v-if="testimonialsDesign.show_stars !== false" class="text-warning extra-small mb-2">
                            <i v-for="star in (Number(testi.rating) || 5)" :key="star" class="bi bi-star-fill me-1"></i>
                          </div>
                          <p class="small text-dark fst-italic leading-relaxed mb-3">« {{ testi.comment }} »</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 pt-2 border-top">
                          <img v-if="getTestimonialAvatarSrc(testi)" :src="getTestimonialAvatarSrc(testi)" :alt="testi.author_name" class="rounded-circle border object-fit-cover flex-shrink-0" style="width: 38px; height: 38px;" />
                          <div v-else class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center extra-small flex-shrink-0" style="width: 38px; height: 38px;" :style="{ backgroundColor: accentColor }">
                            {{ getInitials(testi.author_name) }}
                          </div>
                          <div class="min-w-0">
                            <strong class="d-block extra-small text-dark text-truncate">{{ testi.author_name }}</strong>
                            <span class="extra-small text-muted d-block text-truncate">{{ testi.author_role }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- MODE 3: GRID (2 or 3 Columns) OR FEATURED QUOTE -->
                  <div v-else class="row g-3">
                    <div 
                      v-for="(testi, tIdx) in testimonialsList" 
                      :key="tIdx" 
                      :class="testimonialsDesign.layout_style === 'grid_3' ? 'col-md-6 col-lg-4' : (testimonialsDesign.layout_style === 'featured_quote' ? 'col-12' : 'col-md-6')"
                    >
                      <div 
                        class="p-4 rounded-4 bg-light border h-100 d-flex flex-column justify-content-between transition"
                        :class="[
                          'elem-anim-' + (testimonialsDesign.animation || 'lift'),
                          testimonialsDesign.text_align === 'center' ? 'text-center' : ''
                        ]"
                      >
                        <div>
                          <div v-if="testimonialsDesign.show_stars !== false" class="text-warning small mb-2">
                            <i v-for="star in (Number(testi.rating) || 5)" :key="star" class="bi bi-star-fill me-1"></i>
                          </div>
                          <p class="small text-dark fst-italic leading-relaxed mb-3">« {{ testi.comment }} »</p>
                        </div>
                        <div class="d-flex align-items-center gap-3 pt-3 border-top" :class="testimonialsDesign.text_align === 'center' ? 'justify-content-center' : ''">
                          <img 
                            v-if="getTestimonialAvatarSrc(testi)" 
                            :src="getTestimonialAvatarSrc(testi)" 
                            :alt="testi.author_name" 
                            class="rounded-circle border object-fit-cover flex-shrink-0 shadow-2xs" 
                            style="width: 42px; height: 42px;" 
                          />
                          <div v-else class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center small flex-shrink-0" style="width: 42px; height: 42px;" :style="{ backgroundColor: accentColor }">
                            {{ getInitials(testi.author_name) }}
                          </div>
                          <div class="min-w-0 text-start">
                            <strong class="d-block small text-dark text-truncate">{{ testi.author_name }}</strong>
                            <span class="extra-small text-muted d-block text-truncate">{{ testi.author_role }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9.G INTERACTIVE FAQ ACCORDION BLOCK -->
              <section v-else-if="block.id === 'faq' && faqItemsList.length" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="section-icon-badge" :style="{ color: accentColor }">
                      <i class="bi bi-question-circle-fill fs-5"></i>
                    </span>
                    <h2 class="h5 fw-bold mb-0 section-heading">Questions Fréquentes (FAQ)</h2>
                  </div>

                  <div class="d-flex flex-column gap-3">
                    <div 
                      v-for="(faq, fIdx) in faqItemsList" 
                      :key="fIdx" 
                      class="rounded-4 border overflow-hidden transition"
                      :class="openFaqIdx === fIdx ? 'bg-white shadow-2xs' : 'bg-light'"
                    >
                      <button 
                        type="button" 
                        @click="openFaqIdx = openFaqIdx === fIdx ? -1 : fIdx"
                        class="btn w-100 p-3 p-md-4 text-start d-flex align-items-center justify-content-between fw-bold text-dark border-0"
                      >
                        <span>{{ faq.question }}</span>
                        <i class="bi fs-5 transition" :class="openFaqIdx === fIdx ? 'bi-dash-circle-fill' : 'bi-plus-circle'" :style="openFaqIdx === fIdx ? { color: accentColor } : {}"></i>
                      </button>
                      <div v-if="openFaqIdx === fIdx" class="px-3 px-md-4 pb-4 pt-0 small text-muted leading-relaxed border-top pt-3">
                        {{ faq.answer }}
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9b. INTERACTIVE QUOTE CALCULATOR BLOCK -->
              <section
                v-else-if="block.id === 'quote_calculator' && quoteCalculatorConfig.enabled"
                id="block-quote_calculator"
                class="portfolio-block h-100"
                :class="{ 'd-print-none': isHiddenInPrint('quote_calculator') }"
              >
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="text-center mb-4">
                    <div class="section-icon-badge d-inline-flex mb-2" :style="{ color: accentColor }">
                      <i class="bi bi-calculator-fill fs-2"></i>
                    </div>
                    <h2 class="h4 fw-bold mb-1">{{ quoteCalculatorConfig.title || 'Estimez votre Projet' }}</h2>
                    <p v-if="quoteCalculatorConfig.subtitle" class="text-muted small mb-0">{{ quoteCalculatorConfig.subtitle }}</p>
                  </div>

                  <div class="row g-4 align-items-start">
                    <div class="col-lg-7">
                      <label class="form-label small fw-bold text-uppercase text-muted mb-2">1. Choisissez votre prestation de base</label>
                      <div class="d-flex flex-column gap-2 mb-4">
                        <button
                          v-for="srv in quoteCalculatorConfig.base_services"
                          :key="srv.id"
                          type="button"
                          @click="selectedQuoteServiceId = srv.id"
                          class="btn text-start p-3 rounded-4 border d-flex align-items-center justify-content-between transition"
                          :class="selectedQuoteService?.id === srv.id ? 'shadow-sm fw-bold' : 'bg-light'"
                          :style="selectedQuoteService?.id === srv.id ? { borderColor: accentColor, backgroundColor: accentColor + '12' } : {}"
                        >
                          <div>
                            <div class="text-dark">{{ srv.label }}</div>
                            <small class="text-muted extra-small">Délai estimé : ~{{ srv.days || 7 }} jours</small>
                          </div>
                          <span class="badge rounded-pill px-3 py-2 text-white" :style="{ backgroundColor: accentColor }">
                            {{ Number(srv.price || 0).toLocaleString('fr-FR') }} {{ quoteCalculatorConfig.currency || 'FCFA' }}
                          </span>
                        </button>
                      </div>

                      <div v-if="quoteCalculatorConfig.addons?.length" class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-2">2. Options complémentaires</label>
                        <div class="row g-2">
                          <div v-for="addon in quoteCalculatorConfig.addons" :key="addon.id" class="col-sm-6">
                            <button
                              type="button"
                              @click="toggleQuoteAddon(addon.id)"
                              class="btn w-100 text-start p-2 px-3 rounded-3 border d-flex align-items-center justify-content-between small"
                              :class="selectedQuoteAddonIds.includes(addon.id) ? 'fw-bold shadow-2xs' : 'bg-light'"
                              :style="selectedQuoteAddonIds.includes(addon.id) ? { borderColor: accentColor, backgroundColor: accentColor + '12' } : {}"
                            >
                              <span class="text-truncate me-2">
                                <i class="bi me-1" :class="selectedQuoteAddonIds.includes(addon.id) ? 'bi-check-square-fill' : 'bi-square'" :style="{ color: accentColor }"></i>
                                {{ addon.label }}
                              </span>
                              <span class="extra-small fw-semibold text-muted text-nowrap">+{{ Number(addon.price || 0).toLocaleString('fr-FR') }}</span>
                            </button>
                          </div>
                        </div>
                      </div>

                      <div class="form-check form-switch mt-3 p-3 rounded-3 border bg-light ps-5">
                        <input class="form-check-input cursor-pointer" type="checkbox" v-model="quoteUrgency" id="publicQuoteUrgency" />
                        <label class="form-check-label small fw-bold cursor-pointer" for="publicQuoteUrgency">
                          Livraison prioritaire / Urgente (×{{ quoteCalculatorConfig.urgency_multiplier || 1.35 }})
                        </label>
                      </div>
                    </div>

                    <div class="col-lg-5">
                      <div class="p-4 rounded-4 border text-center bg-light">
                        <div class="extra-small text-uppercase fw-bold text-muted mb-1">Estimation indicative</div>
                        <div class="display-6 fw-black mb-1" :style="{ color: accentColor }">
                          {{ quoteTotalPrice.toLocaleString('fr-FR') }} <span class="fs-6">{{ quoteCalculatorConfig.currency || 'FCFA' }}</span>
                        </div>
                        <div class="small text-muted mb-4">
                          <i class="bi bi-clock me-1"></i> Délai estimé : <strong>{{ quoteTotalDays }} jours</strong>
                        </div>
                        <button
                          type="button"
                          @click="applyQuoteToContact"
                          class="btn w-100 rounded-pill py-2 fw-bold text-white shadow-sm"
                          :style="{ backgroundColor: accentColor }"
                        >
                          <i class="bi bi-send-check-fill me-2"></i>
                          {{ quoteCalculatorConfig.cta_text || 'Demander ce devis' }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9c. VISUAL GALLERY & BEFORE/AFTER COMPARATOR (10 Models) -->
              <section
                v-else-if="block.id === 'visual_gallery' && visualGalleryConfig.enabled && visualGalleryConfig.items?.length"
                id="block-visual_gallery"
                class="portfolio-block h-100"
                :class="[{ 'd-print-none': isHiddenInPrint('visual_gallery') }, 'gallery-model-' + getBlockModel('visual_gallery')]"
              >
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                      <h2 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-images" :style="{ color: accentColor }"></i>
                        <span>{{ visualGalleryConfig.title || 'Galerie & Showroom' }}</span>
                      </h2>
                      <p v-if="visualGalleryConfig.subtitle" class="text-muted small mb-0">{{ visualGalleryConfig.subtitle }}</p>
                    </div>
                    <div v-if="galleryCategories.length > 2" class="d-flex flex-wrap gap-1">
                      <button
                        v-for="cat in galleryCategories"
                        :key="cat"
                        type="button"
                        @click="galleryActiveCategory = cat"
                        class="btn btn-xs rounded-pill px-3 py-1 extra-small fw-bold"
                        :class="galleryActiveCategory === cat ? 'text-white shadow-2xs' : 'btn-light border text-dark'"
                        :style="galleryActiveCategory === cat ? { backgroundColor: accentColor } : {}"
                      >
                        {{ cat === 'all' ? 'Tout' : cat }}
                      </button>
                    </div>
                  </div>

                  <!-- Horizontal Scroll / Masonry / Grid Models -->
                  <div
                    :class="[
                      getBlockModel('visual_gallery') === 'horizontal_scroll' ? 'd-flex gap-3 overflow-x-auto pb-2' : (
                        getBlockModel('visual_gallery') === 'masonry' ? 'gallery-masonry-cols' : 'row g-3'
                      )
                    ]"
                  >
                    <div
                      v-for="(item, gIdx) in filteredGalleryItems"
                      :key="item.id || gIdx"
                      :class="[
                        getBlockModel('visual_gallery') === 'horizontal_scroll' ? 'flex-shrink-0' : (
                          getBlockModel('visual_gallery') === 'masonry' ? 'mb-3 break-inside-avoid' : (
                            getBlockModel('visual_gallery') === 'fullscreen_showcase' ? 'col-12' : (
                              getBlockModel('visual_gallery') === 'grid_2' ? 'col-md-6' : (
                                getBlockModel('visual_gallery') === 'grid_4' ? 'col-6 col-md-3' : (
                                  getBlockModel('visual_gallery') === 'bento_mosaic' && gIdx === 0 ? 'col-md-8' : (
                                    visualGalleryConfig.columns === 2 ? 'col-md-6' : 'col-md-6 col-lg-4'
                                  )
                                )
                              )
                            )
                          )
                        )
                      ]"
                      :style="getBlockModel('visual_gallery') === 'horizontal_scroll' ? { width: '310px' } : {}"
                    >
                      <div
                        class="rounded-4 border overflow-hidden bg-light h-100 d-flex flex-column shadow-2xs"
                        :class="getBlockModel('visual_gallery') === 'polaroid' ? 'p-2 pb-3 bg-white shadow' : ''"
                      >
                        <!-- Before/After Slider Mode -->
                        <div v-if="item.is_before_after && item.before_image_url" class="before-after-wrapper position-relative" :style="{ height: getBlockModel('visual_gallery') === 'fullscreen_showcase' ? '380px' : '220px' }">
                          <img :src="formatImageUrl(item.image_url)" :alt="item.title" class="w-100 h-100 object-fit-cover" />
                          <div
                            class="position-absolute top-0 start-0 h-100 overflow-hidden"
                            :style="{ width: getBeforeAfterPos(item.id || gIdx) + '%' }"
                          >
                            <img :src="formatImageUrl(item.before_image_url)" alt="Avant" class="h-100 object-fit-cover" style="width: 100%; max-width: none; min-width: 320px;" />
                          </div>
                          <span class="badge bg-dark bg-opacity-75 position-absolute top-0 start-0 m-2 extra-small">Avant</span>
                          <span class="badge bg-success bg-opacity-75 position-absolute top-0 end-0 m-2 extra-small">Après</span>
                          <input
                            type="range"
                            min="5"
                            max="95"
                            :value="getBeforeAfterPos(item.id || gIdx)"
                            @input="e => setBeforeAfterPos(item.id || gIdx, e.target.value)"
                            class="before-after-range position-absolute bottom-0 start-0 w-100 px-2 mb-1"
                          />
                        </div>

                        <!-- Standard Gallery Image with Lightbox -->
                        <div
                          v-else
                          class="position-relative overflow-hidden cursor-pointer"
                          :style="{ height: getBlockModel('visual_gallery') === 'fullscreen_showcase' ? '380px' : (getBlockModel('visual_gallery') === 'masonry' ? 'auto' : '220px') }"
                          @click="visualGalleryConfig.enable_lightbox !== false && (lightboxItem = item)"
                        >
                          <img :src="formatImageUrl(item.image_url)" :alt="item.title" class="w-100 h-100 object-fit-cover transition-hover" />
                          <span v-if="item.category" class="badge bg-dark bg-opacity-75 position-absolute top-0 start-0 m-2 extra-small">{{ item.category }}</span>
                        </div>

                        <div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
                          <div>
                            <h3 class="h6 fw-bold mb-1">{{ item.title || 'Réalisation' }}</h3>
                            <p v-if="item.caption" class="extra-small text-muted mb-0">{{ item.caption }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9d. LANGUAGES, SOFT SKILLS & DAILY TOOLS -->
              <section
                v-else-if="block.id === 'languages_and_tools' && languagesAndToolsConfig.enabled"
                id="block-languages_and_tools"
                class="portfolio-block h-100"
                :class="{ 'd-print-none': isHiddenInPrint('languages_and_tools') }"
              >
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <h2 class="h4 fw-bold mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-translate" :style="{ color: accentColor }"></i>
                    <span>{{ languagesAndToolsConfig.title || 'Langues, Soft Skills & Outils du Quotidien' }}</span>
                  </h2>

                  <div class="row g-4">
                    <div v-if="languagesAndToolsConfig.languages?.length" class="col-md-6">
                      <h3 class="h6 fw-bold text-muted text-uppercase extra-small mb-3">Langues parlées</h3>
                      <div class="d-flex flex-column gap-3">
                        <div v-for="(lang, lIdx) in languagesAndToolsConfig.languages" :key="lIdx">
                          <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span>{{ lang.flag }} {{ lang.name }}</span>
                            <span class="text-muted">{{ lang.level }}</span>
                          </div>
                          <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar rounded-pill" :style="{ width: (lang.percent || 80) + '%', backgroundColor: accentColor }"></div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div v-if="languagesAndToolsConfig.soft_skills?.length" class="col-md-6">
                      <h3 class="h6 fw-bold text-muted text-uppercase extra-small mb-3">Soft Skills (Savoir-être)</h3>
                      <div class="d-flex flex-wrap gap-2">
                        <span v-for="(sk, sIdx) in languagesAndToolsConfig.soft_skills" :key="sIdx" class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                          <i class="bi bi-check2-circle me-1" :style="{ color: accentColor }"></i>{{ sk }}
                        </span>
                      </div>
                    </div>

                    <div v-if="languagesAndToolsConfig.daily_tools?.length" class="col-12">
                      <h3 class="h6 fw-bold text-muted text-uppercase extra-small mb-3">Outils du quotidien</h3>
                      <div class="d-flex flex-wrap gap-2">
                        <div v-for="(tool, tIdx) in languagesAndToolsConfig.daily_tools" :key="tIdx" class="px-3 py-2 rounded-3 border bg-light d-inline-flex align-items-center gap-2 small fw-semibold">
                          <i class="bi" :class="tool.icon || 'bi-tools'" :style="{ color: tool.color || accentColor }"></i>
                          <span>{{ tool.name }}</span>
                          <span v-if="tool.category" class="extra-small text-muted">({{ tool.category }})</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9e. AWARDS, HACKATHONS & PRESS -->
              <section
                v-else-if="block.id === 'awards_press' && awardsPressConfig.enabled && awardsPressConfig.items?.length"
                id="block-awards_press"
                class="portfolio-block h-100"
                :class="{ 'd-print-none': isHiddenInPrint('awards_press') }"
              >
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <h2 class="h4 fw-bold mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-trophy-fill text-warning"></i>
                    <span>{{ awardsPressConfig.title || 'Distinctions, Hackathons & Presse' }}</span>
                  </h2>
                  <div class="row g-3">
                    <div v-for="(aw, awIdx) in awardsPressConfig.items" :key="awIdx" class="col-md-6">
                      <div class="p-3 rounded-4 border bg-light h-100 d-flex flex-column justify-content-between">
                        <div>
                          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <span class="badge rounded-pill text-white extra-small" :style="{ backgroundColor: accentColor }">{{ aw.type || 'Prix' }}</span>
                            <small class="text-muted extra-small">{{ aw.date }}</small>
                          </div>
                          <h3 class="h6 fw-bold mb-1">{{ aw.title }}</h3>
                          <div class="extra-small fw-semibold text-muted mb-2">{{ aw.organization }}</div>
                          <p v-if="aw.description" class="small text-muted mb-2">{{ aw.description }}</p>
                        </div>
                        <a v-if="aw.url" :href="aw.url" target="_blank" rel="noopener" class="extra-small fw-bold text-decoration-none" :style="{ color: accentColor }">
                          Consulter la source <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9f. TECH237 WIDGETS (FORMS & HEARTBEAT UPTIME) -->
              <section
                v-else-if="block.id === 'tech237_widgets' && tech237WidgetsConfig.enabled"
                id="block-tech237_widgets"
                class="portfolio-block h-100"
                :class="{ 'd-print-none': isHiddenInPrint('tech237_widgets') }"
              >
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="row g-4">
                    <div v-if="selectedPublicForm" :class="tech237WidgetsConfig.show_heartbeat && visiblePublicMonitors.length ? 'col-lg-6' : 'col-12'">
                      <div class="p-4 rounded-4 border bg-light h-100 d-flex flex-column justify-content-between">
                        <div>
                          <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 extra-small fw-bold mb-2">Formulaire Tech237</span>
                          <h3 class="h5 fw-bold mb-2">{{ tech237WidgetsConfig.form_title || selectedPublicForm.title }}</h3>
                          <p v-if="selectedPublicForm.description" class="small text-muted mb-3">{{ selectedPublicForm.description }}</p>
                        </div>
                        <div>
                          <a :href="`/f/${selectedPublicForm.slug}`" target="_blank" class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm" :style="{ backgroundColor: accentColor }">
                            <i class="bi bi-ui-checks me-2"></i> Remplir le formulaire
                          </a>
                        </div>
                      </div>
                    </div>

                    <div v-if="tech237WidgetsConfig.show_heartbeat && visiblePublicMonitors.length" :class="selectedPublicForm ? 'col-lg-6' : 'col-12'">
                      <div class="p-4 rounded-4 border bg-light h-100">
                        <h3 class="h6 fw-bold mb-3 d-flex align-items-center gap-2">
                          <i class="bi bi-activity text-danger"></i>
                          <span>{{ tech237WidgetsConfig.heartbeat_title || 'Statut des Services en Direct' }}</span>
                        </h3>
                        <div class="d-flex flex-column gap-2">
                          <div v-for="mon in visiblePublicMonitors" :key="mon.id" class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                              <span class="rounded-circle d-inline-block" :class="mon.is_currently_down ? 'bg-danger' : 'bg-success'" style="width: 10px; height: 10px;"></span>
                              <span class="small fw-bold text-dark">{{ mon.name }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 extra-small text-muted">
                              <span v-if="mon.uptime_percentage != null" class="fw-bold text-success">{{ mon.uptime_percentage }}%</span>
                              <span v-if="mon.last_response_time_ms != null">{{ mon.last_response_time_ms }} ms</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 10. CONTACT BLOCK WITH INTERACTIVE EMAIL FORM (100% Customizable Layout, Image, Align & Animation) -->
              <section v-else-if="block.id === 'contact'" id="portfolioContactSection" class="portfolio-block h-100">
                <div 
                  class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100 transition"
                  :class="[
                    'elem-anim-' + (contactFormConfig.animation || 'fade_up'),
                    contactFormConfig.text_align === 'center' ? 'text-center' : 'text-start'
                  ]"
                >
                  <div class="row g-4 align-items-center" :class="contactFormConfig.layout_style === 'split_right_info' ? 'flex-lg-row-reverse' : ''">
                    <!-- Info & Illustration Column (Full width if centered_card or banner_modern, 5 cols if split) -->
                    <div :class="(contactFormConfig.layout_style === 'split_left_info' || contactFormConfig.layout_style === 'split_right_info') ? 'col-lg-5' : 'col-12 text-center'">
                      <div class="mb-2 section-icon-badge d-inline-flex" :style="{ color: accentColor }">
                        <i class="bi fs-2" :class="contactFormConfig.icon || 'bi-send-check-fill'"></i>
                      </div>
                      <h2 class="h4 fw-bold mb-2">{{ contactFormConfig.title || 'Entrons en contact' }}</h2>
                      <p class="text-muted small mb-4" :class="(contactFormConfig.layout_style === 'centered_card' || contactFormConfig.text_align === 'center') ? 'max-w-md mx-auto' : ''">
                        {{ contactFormConfig.subtitle || 'Un projet innovant, une opportunité ou envie d\'échanger ? Envoyez-moi un message direct.' }}
                      </p>

                      <!-- Optional Contact Illustration Image -->
                      <div v-if="contactFormConfig.image_url" class="mb-4">
                        <img :src="formatImageUrl(contactFormConfig.image_url)" alt="Contact" class="img-fluid rounded-4 shadow-sm border object-fit-cover" style="max-height: 220px;" />
                      </div>

                      <!-- Direct Contact Shortcuts -->
                      <div v-if="contactFormConfig.show_contact_cards !== false" class="d-flex flex-wrap gap-2 mb-3" :class="(contactFormConfig.layout_style === 'centered_card' || contactFormConfig.text_align === 'center') ? 'justify-content-center' : ''">
                        <a 
                          v-if="portfolioUser.phone && portfolioUser.availability !== 'not_available'" 
                          :href="'https://wa.me/' + cleanPhone(portfolioUser.phone)" 
                          target="_blank"
                          class="btn btn-success rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2"
                        >
                          <i class="bi bi-whatsapp"></i>
                          <span>WhatsApp Direct</span>
                        </a>

                        <a 
                          v-if="googleMapsUrl" 
                          :href="googleMapsUrl" 
                          target="_blank" 
                          class="btn btn-outline-danger rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
                        >
                          <i class="bi bi-geo-alt-fill"></i>
                          <span>Voir sur Google Maps</span>
                        </a>
                      </div>

                      <!-- Social Links -->
                      <div v-if="hasSocialLinks && contactFormConfig.show_contact_cards !== false" class="d-flex align-items-center gap-2 mt-3" :class="(contactFormConfig.layout_style === 'centered_card' || contactFormConfig.text_align === 'center') ? 'justify-content-center' : ''">
                        <a v-if="socialLinks.github" :href="socialLinks.github" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                          <i class="bi bi-github"></i>
                        </a>
                        <a v-if="socialLinks.linkedin" :href="socialLinks.linkedin" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                          <i class="bi bi-linkedin text-primary"></i>
                        </a>
                        <a v-if="socialLinks.twitter" :href="socialLinks.twitter" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                          <i class="bi bi-twitter-x"></i>
                        </a>
                        <a v-if="socialLinks.website" :href="socialLinks.website" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                          <i class="bi bi-globe2 text-info"></i>
                        </a>
                      </div>
                    </div>

                    <!-- Form Column -->
                    <div :class="(contactFormConfig.layout_style === 'split_left_info' || contactFormConfig.layout_style === 'split_right_info') ? 'col-lg-7' : 'col-12'">
                      <div class="text-start mx-auto p-4 rounded-4 bg-light border" style="max-width: 680px; width: 100%;">
                        <h3 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                          <i class="bi bi-envelope-paper-fill" :style="{ color: accentColor }"></i> Formulaire de Contact Direct
                        </h3>

                        <div v-if="contactSuccessMsg" class="alert alert-success d-flex align-items-center gap-2 py-3 rounded-3 shadow-2xs">
                          <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                          <div class="small fw-semibold">{{ contactSuccessMsg }}</div>
                        </div>

                        <div v-if="contactErrorMsg" class="alert alert-danger d-flex align-items-center gap-2 py-2 rounded-3 shadow-2xs">
                          <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                          <div class="small">{{ contactErrorMsg }}</div>
                        </div>

                        <form v-if="!contactSuccessMsg" @submit.prevent="submitContactForm">
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label extra-small fw-bold text-dark mb-1">Prénom <span class="text-danger">*</span></label>
                              <input type="text" v-model="contactForm.first_name" class="form-control form-control-sm rounded-3" placeholder="Votre prénom" required />
                            </div>
                            <div class="col-md-6">
                              <label class="form-label extra-small fw-bold text-dark mb-1">Nom</label>
                              <input type="text" v-model="contactForm.last_name" class="form-control form-control-sm rounded-3" placeholder="Votre nom" />
                            </div>
                            <div class="col-md-6">
                              <label class="form-label extra-small fw-bold text-dark mb-1">Adresse Email <span class="text-danger">*</span></label>
                              <input type="email" v-model="contactForm.email" class="form-control form-control-sm rounded-3" placeholder="votre.email@domaine.com" required />
                            </div>
                            <div class="col-md-6">
                              <label class="form-label extra-small fw-bold text-dark mb-1">Sujet <span class="text-danger">*</span></label>
                              <input type="text" v-model="contactForm.subject" class="form-control form-control-sm rounded-3" placeholder="ex: Proposition de projet ou mission" required />
                            </div>
                            <div class="col-12">
                              <label class="form-label extra-small fw-bold text-dark mb-1">Description / Message <span class="text-danger">*</span></label>
                              <textarea v-model="contactForm.message" class="form-control form-control-sm rounded-3" rows="4" placeholder="Détaillez votre message..." required></textarea>
                            </div>
                            <div class="col-12 text-end">
                              <button 
                                type="submit" 
                                class="btn btn-sm rounded-pill px-4 py-2 fw-bold text-white shadow-2xs" 
                                :style="{ backgroundColor: accentColor }"
                                :disabled="isSubmittingContact"
                              >
                                <span v-if="isSubmittingContact" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                <i v-else class="bi bi-send-fill me-1"></i>
                                <span>{{ isSubmittingContact ? 'Envoi en cours...' : (contactFormConfig.button_text || 'Envoyer mon message') }}</span>
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- SECTION DIVIDER EFFECT (If enabled in Design Effects Studio) -->
              <div v-if="designEffects.section_divider && designEffects.section_divider !== 'none' && block.id !== 'contact'" class="portfolio-section-divider my-3 d-print-none">
                <div v-if="designEffects.section_divider === 'neon_line'" class="section-divider-neon" :style="{ '--accent-color': accentColor }"></div>
                <div v-else-if="designEffects.section_divider === 'tech_dots'" class="d-flex align-items-center justify-content-center gap-3 py-1">
                  <span class="rounded-circle opacity-50" :style="{ width: '6px', height: '6px', backgroundColor: accentColor }"></span>
                  <span class="rounded-1" :style="{ width: '10px', height: '10px', backgroundColor: accentColor, transform: 'rotate(45deg)' }"></span>
                  <span class="rounded-circle opacity-50" :style="{ width: '6px', height: '6px', backgroundColor: accentColor }"></span>
                </div>
                <div v-else-if="designEffects.section_divider === 'svg_wave'" class="overflow-hidden" style="height: 24px;">
                  <svg viewBox="0 0 1200 60" preserveAspectRatio="none" class="w-100 h-100">
                    <path d="M0,30 C350,65 850,-5 1200,30 L1200,60 L0,60 Z" :fill="accentColor" fill-opacity="0.14"></path>
                  </svg>
                </div>
                <div v-else-if="designEffects.section_divider === 'diagonal_slash'" class="section-divider-diagonal" :style="{ '--accent-color': accentColor }"></div>
              </div>

            </div>

          </template>

        </div>

      </main>

      <!-- CUSTOMIZABLE GLOBAL FOOTER (Multi-Column Real Website Footer or Minimal) -->
      <footer v-if="!isBlankCanvasMode || !blankCanvasConfig.hide_default_footer" class="portfolio-global-footer py-5 mt-5 border-top d-print-none position-relative z-10">
        <div class="container" :style="{ maxWidth: websiteNavbar.enabled ? '1320px' : undefined }">
          
          <!-- Multi-Column Website Footer -->
          <div v-if="footerConfig.layout_style !== 'minimal'" class="row g-4 text-start pb-4 mb-4 border-bottom">
            <!-- Col 1: Brand & Bio -->
            <div class="col-lg-4">
              <div class="d-flex align-items-center gap-2 mb-3">
                <img 
                  v-if="portfolioUser.avatar" 
                  :src="formatImageUrl(portfolioUser.avatar)" 
                  :alt="portfolioUser.name" 
                  class="rounded-circle border object-fit-cover" 
                  style="width: 42px; height: 42px;"
                />
                <div>
                  <strong class="d-block fs-6">{{ websiteNavbar.brand_name || portfolioUser.name }}</strong>
                  <span class="extra-small text-muted">{{ portfolioUser.headline || 'Expert & Bâtisseur Tech' }}</span>
                </div>
              </div>
              <p class="extra-small text-muted leading-relaxed mb-3">
                {{ stripHtmlTags(liveBioText || seoDescription).slice(0, 180) }}
              </p>
              <div v-if="footerConfig.show_socials !== false && hasSocialLinks" class="d-flex align-items-center gap-2">
                <a v-if="socialLinks.github" :href="socialLinks.github" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-2xs" style="width: 34px; height: 34px;">
                  <i class="bi bi-github"></i>
                </a>
                <a v-if="socialLinks.linkedin" :href="socialLinks.linkedin" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-2xs" style="width: 34px; height: 34px;">
                  <i class="bi bi-linkedin text-primary"></i>
                </a>
                <a v-if="socialLinks.twitter" :href="socialLinks.twitter" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-2xs" style="width: 34px; height: 34px;">
                  <i class="bi bi-twitter-x"></i>
                </a>
                <a v-if="socialLinks.website" :href="socialLinks.website" target="_blank" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-2xs" style="width: 34px; height: 34px;">
                  <i class="bi bi-globe2 text-info"></i>
                </a>
              </div>
            </div>

            <!-- Col 2: Quick Website Sitemap -->
            <div class="col-6 col-lg-2">
              <h4 class="h6 fw-bold mb-3">Navigation</h4>
              <ul class="list-unstyled d-flex flex-column gap-2 extra-small mb-0">
                <li v-for="item in websiteNavItems" :key="'ft-' + item.id">
                  <a href="#" @click.prevent="navigateWebsitePage(item.id, item.anchor)" class="text-muted text-decoration-none fw-semibold">
                    {{ item.label }}
                  </a>
                </li>
              </ul>
            </div>

            <!-- Col 3: Top Skills / Services (Only if skills block is enabled) -->
            <div v-if="isBlockEnabled('skills') && portfolioUser.skills && portfolioUser.skills.length" class="col-6 col-lg-3">
              <h4 class="h6 fw-bold mb-3">Expertises clés</h4>
              <div class="d-flex flex-wrap gap-1">
                <span 
                  v-for="(sk, sIdx) in (portfolioUser.skills || []).slice(0, 8)" 
                  :key="'ft-sk-' + sIdx" 
                  class="badge bg-light text-dark border extra-small"
                >
                  {{ typeof sk === 'object' ? sk.name : sk }}
                </span>
              </div>
            </div>

            <!-- Col 4: Direct Contact & vCard -->
            <div class="col-lg-3">
              <h4 class="h6 fw-bold mb-3">Contact Rapide</h4>
              <div class="d-flex flex-column gap-2 extra-small text-muted mb-3">
                <div v-if="portfolioUser.location">
                  <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ portfolioUser.location }}
                </div>
                <div>
                  <i class="bi bi-check-circle-fill text-success me-1"></i> {{ getAvailabilityLabel(portfolioUser.availability) }}
                </div>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button v-if="isBlockEnabled('contact')" type="button" @click="navigateWebsitePage('contact', 'portfolioContactSection')" class="btn btn-xs rounded-pill px-3 py-1 fw-bold text-white" :style="{ backgroundColor: accentColor }">
                  M'écrire
                </button>
                <button v-if="vcardEnabled" type="button" @click="openVcardModal" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold">
                  <i class="bi bi-qr-code me-1"></i> vCard
                </button>
              </div>
            </div>
          </div>

          <!-- Bottom Copyright & Tech237 Verified Bar -->
          <div class="text-center">
            <p v-if="footerConfig.custom_text" class="fw-semibold mb-2" v-html="formatHtml(footerConfig.custom_text)"></p>
            <div v-if="footerConfig.show_tech237_badge !== false" class="d-flex align-items-center justify-content-center gap-2 mb-1">
              <img src="/assets/images/logo.png" alt="Tech237" style="height: 18px; object-fit: contain;">
              <span class="fw-bold extra-small text-muted">Propulsé &amp; Vérifié par Tech237</span>
            </div>
          </div>
        </div>
      </footer>

      <!-- MODAL: PROJECT CASE STUDY (QUICK-VIEW) -->
      <div v-if="selectedCaseStudyProject" class="modal fade show d-block d-print-none" tabindex="-1" style="background-color: rgba(0,0,0,0.65); z-index: 1085;" @click.self="selectedCaseStudyProject = null">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header text-white p-4 border-0" :style="{ backgroundColor: accentColor }">
              <div class="d-flex align-items-center gap-3">
                <img 
                  v-if="selectedCaseStudyProject.logo" 
                  :src="formatImageUrl(selectedCaseStudyProject.logo)" 
                  class="rounded-3 bg-white p-1 object-fit-contain" 
                  style="width: 52px; height: 52px;" 
                  :alt="selectedCaseStudyProject.name"
                />
                <div>
                  <span class="badge bg-white text-dark extra-small mb-1">{{ selectedCaseStudyProject.category || 'Projet Tech' }}</span>
                  <h5 class="modal-title fw-bold mb-0">{{ selectedCaseStudyProject.name }}</h5>
                </div>
              </div>
              <button type="button" @click="selectedCaseStudyProject = null" class="btn-close btn-close-white"></button>
            </div>
            <div class="modal-body p-4 p-md-5 bg-white text-dark">
              <h6 class="fw-bold text-uppercase extra-small text-muted mb-2">Présentation &amp; Étude de cas</h6>
              <div class="text-content leading-relaxed mb-4" v-html="formatHtml(selectedCaseStudyProject.description || selectedCaseStudyProject.pitch || 'Projet développé et présenté sur Tech237.')"></div>

              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top">
                <div class="d-flex align-items-center gap-3 small text-muted">
                  <span v-if="selectedCaseStudyProject.views_count"><i class="bi bi-eye me-1"></i>{{ selectedCaseStudyProject.views_count }} consultations</span>
                  <span v-if="selectedCaseStudyProject.upvotes_count"><i class="bi bi-heart-fill text-danger me-1"></i>{{ selectedCaseStudyProject.upvotes_count }} soutiens</span>
                </div>
                <div class="d-flex gap-2">
                  <button type="button" @click="selectedCaseStudyProject = null" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold">
                    Fermer
                  </button>
                  <a 
                    :href="'/projet/' + (selectedCaseStudyProject.slug || selectedCaseStudyProject.id)" 
                    target="_blank" 
                    class="btn btn-sm rounded-pill px-4 fw-bold text-white"
                    :style="{ backgroundColor: accentColor }"
                  >
                    Ouvrir la page officielle du projet &rarr;
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FLOATING WHATSAPP DIRECT BUBBLE -->
      <a 
        v-if="whatsappFloatingUrl" 
        :href="whatsappFloatingUrl" 
        target="_blank" 
        class="btn-floating-whatsapp rounded-pill shadow-lg d-inline-flex align-items-center gap-2 text-white text-decoration-none fw-bold px-3 py-2 d-print-none"
        :class="whatsappFloating.position === 'right' ? 'pos-right' : 'pos-left'"
        title="Discuter directement sur WhatsApp"
      >
        <i class="bi bi-whatsapp fs-5"></i>
        <span class="small">WhatsApp Direct</span>
      </a>

      <!-- MODAL: DIGITAL BUSINESS CARD (vCard .vcf + QR Code) -->
      <div v-if="showVcardModal" class="modal fade show d-block d-print-none" tabindex="-1" style="background-color: rgba(0,0,0,0.65); z-index: 1080;" @click.self="showVcardModal = false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
          <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header text-white p-4 border-0" :style="{ backgroundColor: accentColor }">
              <div class="d-flex align-items-center gap-3">
                <img 
                  v-if="portfolioUser.avatar" 
                  :src="formatImageUrl(portfolioUser.avatar)" 
                  class="rounded-circle border border-2 border-white object-fit-cover" 
                  style="width: 48px; height: 48px;" 
                  :alt="portfolioUser.name"
                />
                <div>
                  <h5 class="modal-title fw-bold mb-0 fs-6">{{ portfolioUser.name }}</h5>
                  <small class="opacity-90 extra-small d-block">{{ portfolioUser.headline || 'Expert Tech237' }}</small>
                </div>
              </div>
              <button type="button" @click="showVcardModal = false" class="btn-close btn-close-white"></button>
            </div>
            <div class="modal-body p-4 text-center bg-white text-dark">
              <p class="extra-small text-muted mb-3">
                Scannez ce QR Code avec l'appareil photo de votre téléphone ou téléchargez la fiche contact <code>.vcf</code> pour m'ajouter à votre répertoire.
              </p>
              <div class="p-3 rounded-4 border bg-light d-inline-block mb-3 shadow-2xs">
                <img v-if="qrCodeDataUrl" :src="qrCodeDataUrl" alt="QR Code Portfolio" class="img-fluid rounded-3" style="width: 190px; height: 190px;" />
              </div>
              <div class="d-grid gap-2">
                <button 
                  type="button" 
                  @click="downloadVcardFile" 
                  class="btn rounded-pill py-2 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2"
                  :style="{ backgroundColor: accentColor }"
                >
                  <i class="bi bi-person-vcard-fill"></i>
                  <span>Ajouter aux contacts (.vcf)</span>
                </button>
                <a 
                  v-if="qrCodeDataUrl" 
                  :href="qrCodeDataUrl" 
                  :download="`qrcode-${portfolioUser.username}.png`" 
                  class="btn btn-sm btn-outline-secondary rounded-pill py-2 fw-semibold"
                >
                  <i class="bi bi-download me-1"></i> Télécharger l'image QR Code
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FLOATING ROUND BACK TO TOP BUTTON (Bottom-Right, Appears on scroll) -->
      <transition name="fade">
        <button 
          v-if="showBackToTopBtn && footerConfig.show_back_to_top !== false" 
          type="button" 
          @click="scrollToTop" 
          class="btn-floating-back-to-top rounded-circle shadow-lg border-0 d-flex align-items-center justify-content-center d-print-none cursor-pointer"
          :style="{ backgroundColor: accentColor, color: '#FFFFFF' }"
          title="Revenir en haut de la page"
        >
          <i class="bi bi-arrow-up fs-5"></i>
        </button>
      </transition>

      <!-- COMMAND PALETTE MODAL (Ctrl + K) -->
      <div
        v-if="commandPaletteOpen"
        class="command-palette-backdrop position-fixed top-0 start-0 w-100 h-100 d-flex align-items-start justify-content-center pt-5 px-3 d-print-none"
        @click.self="commandPaletteOpen = false"
      >
        <div class="card border-0 shadow-lg rounded-4 w-100 overflow-hidden" style="max-width: 580px; margin-top: 6vh;">
          <div class="p-3 border-bottom d-flex align-items-center gap-2 bg-white">
            <i class="bi bi-search text-muted"></i>
            <input
              type="text"
              v-model="commandQuery"
              autofocus
              class="form-control border-0 shadow-none"
              placeholder="Rechercher un projet, une compétence, une expérience ou une action..."
            />
            <button type="button" @click="commandPaletteOpen = false" class="btn btn-xs btn-light border rounded-pill px-2 extra-small">Échap</button>
          </div>
          <div class="list-group list-group-flush overflow-auto" style="max-height: 360px;">
            <button
              v-for="(res, rIdx) in commandResults"
              :key="rIdx"
              type="button"
              @click="executeCommandItem(res)"
              class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3"
            >
              <div class="d-flex align-items-center gap-3">
                <i class="bi fs-5" :class="res.icon" :style="{ color: accentColor }"></i>
                <div>
                  <div class="small fw-bold text-dark">{{ res.label }}</div>
                  <div v-if="res.sub" class="extra-small text-muted">{{ res.sub }}</div>
                </div>
              </div>
              <span class="badge bg-light text-muted border extra-small">{{ res.type }}</span>
            </button>
            <div v-if="!commandResults.length" class="p-4 text-center text-muted small">
              Aucun résultat pour « {{ commandQuery }} »
            </div>
          </div>
        </div>
      </div>

      <!-- GALLERY LIGHTBOX MODAL -->
      <div
        v-if="lightboxItem"
        class="command-palette-backdrop position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3 d-print-none"
        @click.self="lightboxItem = null"
      >
        <div class="position-relative bg-dark rounded-4 overflow-hidden shadow-lg" style="max-width: 900px; width: 100%;">
          <button
            type="button"
            @click="lightboxItem = null"
            class="btn btn-light btn-sm rounded-circle position-absolute top-0 end-0 m-3 z-3"
            style="width: 34px; height: 34px;"
          >
            <i class="bi bi-x-lg"></i>
          </button>
          <img :src="formatImageUrl(lightboxItem.image_url)" :alt="lightboxItem.title" class="w-100 object-fit-contain" style="max-height: 75vh;" />
          <div class="p-3 text-white bg-dark bg-opacity-75">
            <h4 class="h6 fw-bold mb-1">{{ lightboxItem.title }}</h4>
            <p v-if="lightboxItem.caption" class="extra-small text-white-50 mb-0">{{ lightboxItem.caption }}</p>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import QRCode from 'qrcode'
import { useAudioNarrator } from '@/Composables/useAudioNarrator'

const props = defineProps({
  portfolioUser: { type: Object, required: true },
  projects: { type: Array, default: () => [] },
  pocs: { type: Array, default: () => [] },
  badges: { type: Array, default: () => [] },
  publicForms: { type: Array, default: () => [] },
  publicMonitors: { type: Array, default: () => [] },
  currentTier: { type: Object, default: null },
  nextTier: { type: Object, default: null },
  rankPosition: { type: Number, default: 1 },
  isOwner: { type: Boolean, default: false },
})

const isCopied = ref(false)
const currentProjectSlide = ref(0)
const currentSlideIndex = ref(0)
const showPreloader = ref(false)
const showBackToTopBtn = ref(false)

// Contact form state
const contactForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  subject: '',
  message: '',
})
const isSubmittingContact = ref(false)
const contactSuccessMsg = ref('')
const contactErrorMsg = ref('')

async function submitContactForm() {
  isSubmittingContact.value = true
  contactSuccessMsg.value = ''
  contactErrorMsg.value = ''
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const response = await fetch(`/portfolio/${props.portfolioUser.username}/contact`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(contactForm.value)
    })
    const resData = await response.json()
    if (response.ok && resData?.success) {
      contactSuccessMsg.value = resData.message || 'Votre message a bien été envoyé !'
      contactForm.value = { first_name: '', last_name: '', email: '', subject: '', message: '' }
    } else {
      contactErrorMsg.value = resData?.message || resData?.error || 'Erreur lors de l\'envoi du message.'
    }
  } catch (err) {
    contactErrorMsg.value = err?.message || 'Erreur réseau lors de l\'envoi du message.'
  } finally {
    isSubmittingContact.value = false
  }
}

const layout = computed(() => {
  return props.portfolioUser.portfolio_layout || {}
})

// Layout Options
const currentTheme = computed(() => layout.value.theme || 'emerald')
const currentCardStyle = computed(() => layout.value.card_style || 'rounded')
const currentStructure = computed(() => layout.value.layout_structure || 'single')
const currentFont = computed(() => layout.value.font_family || 'poppins')
const currentPattern = computed(() => layout.value.bg_pattern || 'none')
const accentColor = computed(() => layout.value.accent_color || '#00A859')
const heroStyle = computed(() => layout.value.hero_style || 'agency_landing')
const projectsStyle = computed(() => layout.value.projects_style || 'zigzag')
const skillsStyle = computed(() => layout.value.skills_style || 'chips')
const ambientEffect = computed(() => layout.value.ambient_effect || 'none')
const pageAnimation = computed(() => layout.value.page_animation || 'fade')
const yearsOfExperience = computed(() => layout.value.years_of_experience || '')
const googleMapsUrl = computed(() => layout.value.google_maps_url || '')
const cvFileUrl = computed(() => {
  const file = layout.value.cv_file || layout.value.cv_url
  return file ? formatImageUrl(file) : null
})

// 1. Avatar Shape, Border & Online Pulse
const avatarShapeClass = computed(() => 'avatar-shape-' + (layout.value.avatar_shape || 'circle'))
const avatarBorderClass = computed(() => 'avatar-border-' + (layout.value.avatar_border || 'standard'))
const showOnlinePulse = computed(() => layout.value.show_online_pulse !== false)

// 2. Typewriter Headline Effect
const typewriterEnabled = computed(() => Boolean(layout.value.typewriter_enabled && layout.value.typewriter_roles))
const typewriterRolesList = computed(() => {
  const raw = layout.value.typewriter_roles || ''
  const arr = raw.split(',').map(s => s.trim()).filter(Boolean)
  if (arr.length) return arr
  return [props.portfolioUser.headline || 'Bâtisseur & Développeur Tech']
})
const typedText = ref('')
let typewriterTimer = null
let currentRoleIdx = 0
let currentCharIdx = 0
let isDeletingRole = false

function runTypewriterStep() {
  if (!typewriterEnabled.value) return
  const roles = typewriterRolesList.value
  const fullRole = roles[currentRoleIdx % roles.length] || ''

  if (!isDeletingRole) {
    typedText.value = fullRole.substring(0, currentCharIdx + 1)
    currentCharIdx++
    if (currentCharIdx >= fullRole.length) {
      isDeletingRole = true
      typewriterTimer = setTimeout(runTypewriterStep, 1800)
      return
    }
    typewriterTimer = setTimeout(runTypewriterStep, 75)
  } else {
    typedText.value = fullRole.substring(0, currentCharIdx - 1)
    currentCharIdx--
    if (currentCharIdx <= 0) {
      isDeletingRole = false
      currentRoleIdx = (currentRoleIdx + 1) % roles.length
      typewriterTimer = setTimeout(runTypewriterStep, 350)
      return
    }
    typewriterTimer = setTimeout(runTypewriterStep, 40)
  }
}

const displayedHeadline = computed(() => {
  if (typewriterEnabled.value) {
    return typedText.value || typewriterRolesList.value[0]
  }
  return props.portfolioUser.headline || 'Bâtisseur & Développeur Tech'
})

// 3. Mouse Spotlight & Custom Cursor
const spotlightEnabled = computed(() => Boolean(layout.value.spotlight_enabled))
const customCursorEnabled = computed(() => Boolean(layout.value.custom_cursor_enabled))
const mouseX = ref(-500)
const mouseY = ref(-500)

function handleMouseMove(e) {
  if (!spotlightEnabled.value && !customCursorEnabled.value) return
  mouseX.value = e.clientX
  mouseY.value = e.clientY
}

const spotlightStyle = computed(() => ({
  background: `radial-gradient(500px circle at ${mouseX.value}px ${mouseY.value}px, ${accentColor.value}22, transparent 70%)`,
  zIndex: 1
}))

const customCursorStyle = computed(() => ({
  transform: `translate3d(${mouseX.value - 16}px, ${mouseY.value - 16}px, 0)`,
  borderColor: accentColor.value
}))

// 4. Audio Pitch Vocal (using useAudioNarrator)
const { isPlaying, isPaused, toggleAudio } = useAudioNarrator()
const audioPitchEnabled = computed(() => Boolean(layout.value.audio_pitch_enabled))
const effectiveAudioPitchText = computed(() => {
  return (layout.value.audio_pitch_text || props.portfolioUser.bio || '').replace(/<[^>]+>/g, ' ').trim()
})

function handleAudioPitchClick() {
  if (!effectiveAudioPitchText.value) return
  toggleAudio(`${props.portfolioUser.name}. ${props.portfolioUser.headline || ''}`, effectiveAudioPitchText.value)
}

// 5. Digital Business Card (.vcf + QR Code)
const vcardEnabled = computed(() => layout.value.vcard_enabled !== false)
const showVcardModal = ref(false)
const qrCodeDataUrl = ref('')

async function openVcardModal() {
  showVcardModal.value = true
  if (!qrCodeDataUrl.value) {
    try {
      qrCodeDataUrl.value = await QRCode.toDataURL(shareUrl.value, {
        width: 240,
        margin: 2,
        color: {
          dark: accentColor.value || '#00A859',
          light: '#FFFFFF',
        },
      })
    } catch (e) {}
  }
}

function downloadVcardFile() {
  const name = props.portfolioUser.name || 'Membre Tech237'
  const title = props.portfolioUser.headline || 'Expert Tech'
  const phone = layout.value.whatsapp_floating?.number || props.portfolioUser.social_links?.whatsapp || props.portfolioUser.phone || ''
  const email = props.portfolioUser.email || ''
  const loc = props.portfolioUser.location || 'Cameroun'
  const url = shareUrl.value

  const vcardLines = [
    'BEGIN:VCARD',
    'VERSION:3.0',
    `FN:${name}`,
    `TITLE:${title}`,
    phone ? `TEL;TYPE=CELL:${phone}` : '',
    email ? `EMAIL;TYPE=INTERNET:${email}` : '',
    `ADR;TYPE=WORK:;;${loc};;;;`,
    `URL:${url}`,
    `NOTE:Portfolio officiel Tech237 - ${url}`,
    'END:VCARD'
  ].filter(Boolean).join('\r\n')

  const blob = new Blob([vcardLines], { type: 'text/vcard;charset=utf-8' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = `${props.portfolioUser.username || 'contact'}-tech237.vcf`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

// 6. Floating WhatsApp Direct Bubble
const whatsappFloating = computed(() => layout.value.whatsapp_floating || { enabled: false, position: 'left' })
const whatsappFloatingUrl = computed(() => {
  if (!whatsappFloating.value?.enabled) return ''
  const num = cleanPhone(whatsappFloating.value.number || props.portfolioUser.social_links?.whatsapp || props.portfolioUser.phone || '')
  if (!num) return ''
  const msg = encodeURIComponent(whatsappFloating.value.message || `Bonjour ${props.portfolioUser.name}, j'ai découvert votre portfolio sur Tech237.`)
  return `https://wa.me/${num}?text=${msg}`
})

// 7. Now Section (« En ce moment »)
const nowSection = computed(() => layout.value.now_section || { enabled: false })
const hasNowSection = computed(() => Boolean(nowSection.value?.enabled && (nowSection.value.project || nowSection.value.learning || nowSection.value.goal)))

// 8. GitHub Live Stats
const githubStatsConfig = computed(() => layout.value.github_stats || { enabled: false, username: '' })
const githubUsername = computed(() => {
  const raw = (githubStatsConfig.value?.username || '').trim()
  if (raw) return raw.replace(/^@/, '').replace(/^https?:\/\/(www\.)?github\.com\//i, '').split('/')[0]
  const socialGh = (props.portfolioUser.social_links?.github || '').trim()
  if (socialGh) return socialGh.replace(/^https?:\/\/(www\.)?github\.com\//i, '').split('/')[0]
  return ''
})
const hasGithubStats = computed(() => Boolean(githubStatsConfig.value?.enabled && githubUsername.value))
const githubProfile = ref(null)
const githubRepos = ref([])

const githubTotalStars = computed(() => {
  return githubRepos.value.reduce((acc, r) => acc + (Number(r.stargazers_count) || 0), 0)
})

const githubTopLanguages = computed(() => {
  const counts = {}
  let total = 0
  githubRepos.value.forEach(r => {
    if (r.language) {
      counts[r.language] = (counts[r.language] || 0) + 1
      total++
    }
  })
  if (!total) return []
  return Object.entries(counts)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 5)
    .map(([name, count]) => ({ name, percent: Math.round((count / total) * 100) }))
})

async function fetchGithubLiveStats() {
  if (!hasGithubStats.value) return
  try {
    const [pRes, rRes] = await Promise.all([
      fetch(`https://api.github.com/users/${encodeURIComponent(githubUsername.value)}`),
      fetch(`https://api.github.com/users/${encodeURIComponent(githubUsername.value)}/repos?sort=updated&per_page=12`)
    ])
    if (pRes.ok) githubProfile.value = await pRes.json()
    if (rRes.ok) {
      const repos = await rRes.json()
      if (Array.isArray(repos)) githubRepos.value = repos
    }
  } catch (e) {}
}

// 9. Certifications, Pricing Packages & Testimonials
const certificationsList = computed(() => {
  return (Array.isArray(layout.value.certifications) ? layout.value.certifications : []).filter(c => c && c.title)
})

const pricingPackagesList = computed(() => {
  return (Array.isArray(layout.value.pricing_packages) ? layout.value.pricing_packages : []).filter(p => p && p.title)
})

const testimonialsList = computed(() => {
  return (Array.isArray(layout.value.testimonials) ? layout.value.testimonials : []).filter(t => t && t.comment)
})

// 10. Real Website Navbar, Multi-Page Routing, KPIs, Marquee, Process, Case Study Modal & FAQ
const mobileMenuOpen = ref(false)
const currentWebsitePage = ref('home')
const selectedCaseStudyProject = ref(null)
const openFaqIdx = ref(0)

const websiteNavbar = computed(() => {
  const nav = layout.value.website_navbar
  if (!nav) {
    return {
      enabled: true,
      style: 'floating_pill',
      navigation_mode: 'multi_page',
      menu_type: 'desktop_links_mobile_drawer',
      drawer_side: 'right',
      hamburger_style: 'classic',
      position: 'sticky',
      font_size: 'sm',
      border_radius: '9999px',
      bg_type: 'auto',
      bg_color: '#FFFFFF',
      bg_color_2: '#F8FAFC',
      text_color: '#0F172A',
      logo_url: '',
      custom_menu_items: [],
      brand_name: '',
      brand_subtitle: '',
      cta_text: 'Me contacter'
    }
  }
  return {
    enabled: nav.enabled !== false,
    style: nav.style || 'floating_pill',
    navigation_mode: nav.navigation_mode || 'multi_page',
    menu_type: nav.menu_type || 'desktop_links_mobile_drawer',
    drawer_side: nav.drawer_side || 'right',
    hamburger_style: nav.hamburger_style || 'classic',
    position: nav.position || 'sticky',
    font_size: nav.font_size || 'sm',
    border_radius: nav.border_radius ?? '9999px',
    bg_type: nav.bg_type || 'auto',
    bg_color: nav.bg_color || '#FFFFFF',
    bg_color_2: nav.bg_color_2 || '#F8FAFC',
    text_color: nav.text_color || '#0F172A',
    logo_url: nav.logo_url || '',
    custom_menu_items: Array.isArray(nav.custom_menu_items) ? nav.custom_menu_items : [],
    brand_name: nav.brand_name || '',
    brand_subtitle: nav.brand_subtitle || '',
    cta_text: nav.cta_text || 'Me contacter'
  }
})

const navbarPositionClass = computed(() => {
  const pos = websiteNavbar.value.position || 'sticky'
  if (pos === 'fixed') return 'fixed-top'
  if (pos === 'static') return 'position-relative'
  return 'sticky-top'
})

const navbarFontSizePx = computed(() => {
  const f = websiteNavbar.value.font_size || 'sm'
  if (f === 'xs') return '11px'
  if (f === 'md') return '14px'
  if (f === 'lg') return '16px'
  return '13px'
})

const navbarInlineStyle = computed(() => {
  const nav = websiteNavbar.value
  const style = {
    borderRadius: nav.border_radius || (nav.style === 'floating_pill' ? '9999px' : '0px')
  }
  if (nav.bg_type === 'solid') {
    style.background = nav.bg_color || '#FFFFFF'
    style.color = nav.text_color || '#0F172A'
  } else if (nav.bg_type === 'gradient') {
    style.background = `linear-gradient(90deg, ${nav.bg_color || '#FFFFFF'}, ${nav.bg_color_2 || '#F8FAFC'})`
    style.color = nav.text_color || '#0F172A'
  } else if (nav.bg_type === 'glass') {
    style.background = 'rgba(255, 255, 255, 0.72)'
    style.backdropFilter = 'blur(14px)'
    style.color = nav.text_color || '#0F172A'
  }
  return style
})

const openAccordionMenuId = ref(null)
function toggleAccordionMenu(menuId) {
  openAccordionMenuId.value = openAccordionMenuId.value === menuId ? null : menuId
}

function getBlockAnchorId(blockId) {
  if (blockId === 'contact') return 'portfolioContactSection'
  return 'block-' + blockId
}

function navigateToTargetBlock(blockId, menuId) {
  openAccordionMenuId.value = null
  const anchor = getBlockAnchorId(blockId)
  if (websiteNavbar.value.enabled && websiteNavbar.value.navigation_mode === 'multi_page' && !(websiteNavbar.value.custom_menu_items?.length)) {
    navigateWebsitePage(menuId || blockId, anchor)
    return
  }
  if (websiteNavbar.value.enabled && websiteNavbar.value.navigation_mode === 'multi_page') {
    currentWebsitePage.value = 'home'
  }
  setTimeout(() => {
    const el = document.getElementById(anchor)
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    else window.scrollTo({ top: 0, behavior: 'smooth' })
  }, 40)
}

function getBlockModel(blockId) {
  const models = layout.value.block_models || {}
  if (models[blockId]) return models[blockId]
  return 'classic'
}

const websiteNavItems = computed(() => {
  const custom = websiteNavbar.value.custom_menu_items
  if (Array.isArray(custom) && custom.length > 0) {
    return custom.map(it => ({
      id: it.id || it.target_block,
      label: it.label || it.target_block,
      icon: it.icon || 'bi-grid-fill',
      target_block: it.target_block || 'hero',
      anchor: getBlockAnchorId(it.target_block || 'hero'),
      children: Array.isArray(it.children) ? it.children : []
    }))
  }
  const items = [
    { id: 'home', label: 'Accueil', icon: 'bi-house-door-fill', target_block: 'hero', anchor: 'block-hero', children: [] }
  ]
  const hasAboutPageBlocks = ['about', 'video', 'now', 'skills', 'experiences', 'educations', 'certifications', 'badges'].some(id => isBlockEnabled(id) && shouldRenderBlock(id))
  if (hasAboutPageBlocks) {
    items.push({ id: 'about', label: 'À Propos & Parcours', icon: 'bi-person-badge-fill', target_block: 'about', anchor: 'block-about', children: [] })
  }
  const hasProjectsPageBlocks = (isBlockEnabled('projects') && props.projects && props.projects.length)
    || (isBlockEnabled('pocs') && props.pocs && props.pocs.length)
    || (isBlockEnabled('github') && hasGithubStats.value)
  if (hasProjectsPageBlocks) {
    items.push({ id: 'projects', label: 'Réalisations', icon: 'bi-rocket-takeoff-fill', target_block: 'projects', anchor: 'block-projects', children: [] })
  }
  const hasServicesPageBlocks = (isBlockEnabled('pricing') && pricingPackagesList.value.length)
    || (isBlockEnabled('process') && hasWorkProcess.value)
    || (isBlockEnabled('testimonials') && testimonialsList.value.length)
    || (isBlockEnabled('faq') && faqItemsList.value.length)
  if (hasServicesPageBlocks) {
    items.push({ id: 'services', label: 'Services & Tarifs', icon: 'bi-tags-fill', target_block: 'pricing', anchor: 'block-pricing', children: [] })
  }
  if (isBlockEnabled('contact')) {
    items.push({ id: 'contact', label: 'Contact', icon: 'bi-envelope-paper-fill', target_block: 'contact', anchor: 'portfolioContactSection', children: [] })
  }
  return items
})

function isWebsiteNavItemActive(pageId) {
  if (websiteNavbar.value.navigation_mode === 'multi_page') {
    return currentWebsitePage.value === pageId
  }
  return pageId === 'home'
}

function navigateWebsitePage(pageId, anchorId) {
  if (websiteNavbar.value.enabled && websiteNavbar.value.navigation_mode === 'multi_page') {
    currentWebsitePage.value = pageId
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }
  if (anchorId) {
    const el = document.getElementById(anchorId)
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' })
      return
    }
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const kpiStatsConfig = computed(() => {
  const raw = layout.value.kpi_stats
  if (Array.isArray(raw)) {
    return { enabled: true, items: raw }
  }
  return raw || { enabled: true, items: [] }
})

const kpiStatsDesign = computed(() => {
  return layout.value.kpi_stats_design || { style: 'ribbon', animate_counters: true }
})

const kpiStatsItems = computed(() => {
  if (Array.isArray(kpiStatsConfig.value.items) && kpiStatsConfig.value.items.length) {
    return kpiStatsConfig.value.items.filter(k => k && (k.value || k.label))
  }
  return [
    { value: props.projects?.length ? `+${props.projects.length}` : '+10', label: 'Projets & Réalisations', icon: 'bi-rocket-takeoff-fill', color: accentColor.value },
    { value: yearsOfExperience.value || '100%', label: yearsOfExperience.value ? 'Expérience métier' : 'Engagement & Qualité', icon: 'bi-award-fill', color: accentColor.value },
    { value: `${props.portfolioUser.xp || 100} XP`, label: props.portfolioUser.rank_title || 'Membre Vérifié Tech237', icon: 'bi-lightning-charge-fill', color: accentColor.value },
    { value: '24h', label: 'Réactivité & Écoute', icon: 'bi-clock-history', color: accentColor.value }
  ]
})

const techMarqueeEnabled = computed(() => layout.value.tech_marquee_enabled !== false)
const marqueeSkillsLoop = computed(() => {
  const s = props.portfolioUser.skills || []
  if (!s.length) return []
  return [...s, ...s, ...s]
})

const workProcessConfig = computed(() => {
  return layout.value.work_process || { enabled: true, title: 'Comment je travaille (En 4 étapes)', steps: [] }
})
const workProcessSteps = computed(() => {
  if (Array.isArray(workProcessConfig.value.steps) && workProcessConfig.value.steps.length) {
    return workProcessConfig.value.steps.filter(st => st && st.title)
  }
  return [
    { title: '1. Audit & Cadrage', description: 'Analyse de vos besoins, définition des objectifs et rédaction du cahier des charges.' },
    { title: '2. Design & Architecture', description: 'Conception UI/UX moderne et choix d\'une architecture technique robuste et évolutive.' },
    { title: '3. Développement Agile', description: 'Développement itératif avec code propre, tests de sécurité et démonstrations régulières.' },
    { title: '4. Déploiement & Suivi', description: 'Mise en production, optimisation des performances et accompagnement technique.' }
  ]
})
const hasWorkProcess = computed(() => Boolean(workProcessConfig.value?.enabled !== false && workProcessSteps.value.length))

const faqSectionConfig = computed(() => layout.value.faq_section || { enabled: false, items: [] })
const faqItemsList = computed(() => {
  return (Array.isArray(faqSectionConfig.value.items) ? faqSectionConfig.value.items : []).filter(f => f && f.question)
})

function openCaseStudyModal(proj) {
  selectedCaseStudyProject.value = proj
}

function stripHtmlTags(str) {
  if (!str) return ''
  return String(str).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
}

function parsePackageFeatures(featuresStr) {
  if (!featuresStr) return []
  if (Array.isArray(featuresStr)) return featuresStr
  return String(featuresStr).split(/\r?\n|,/).map(s => s.trim()).filter(Boolean)
}

function selectPackageForContact(pkg) {
  contactForm.value.subject = `Commande / Devis : ${pkg.title} (${pkg.price})`
  if (!contactForm.value.message) {
    contactForm.value.message = `Bonjour ${props.portfolioUser.name},\nJe suis intéressé(e) par votre offre « ${pkg.title} » (${pkg.price}). Pouvons-nous en discuter ?`
  }
  if (isTabbedMode.value && activeTab.value !== 'all' && activeTab.value !== 'contact') {
    activeTab.value = 'contact'
  }
  setTimeout(() => {
    const el = document.getElementById('portfolioContactSection')
    if (el) el.scrollIntoView({ behavior: 'smooth' })
  }, 100)
}

function extractYouTubeEmbedUrl(url) {
  if (!url || typeof url !== 'string') return null
  const trimmed = url.trim()
  if (!trimmed) return null

  if (/^[a-zA-Z0-9_-]{11}$/.test(trimmed)) {
    return `https://www.youtube.com/embed/${trimmed}`
  }

  const regExp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i
  const match = trimmed.match(regExp)
  if (match && match[1]) {
    return `https://www.youtube.com/embed/${match[1]}`
  }

  return null
}

// YouTube presentation video
const youtubeEmbedUrl = computed(() => {
  return extractYouTubeEmbedUrl(layout.value.youtube_url)
})

// Slideshow banner
const slideshowConfig = computed(() => layout.value.slideshow || { enabled: false, slides: [], images: [], width: '100%', border_radius: '0px' })
const slideshowHeight = computed(() => {
  const h = slideshowConfig.value.height
  if (!h) return '340px'
  const str = String(h).trim()
  return str.endsWith('px') ? str : `${str}px`
})
const slideshowContainerStyle = computed(() => {
  const w = slideshowConfig.value.width || '100%'
  const r = slideshowConfig.value.border_radius || '0px'
  const isFramed = w !== '100%' || (r && r !== '0px')
  return {
    height: slideshowHeight.value,
    minHeight: '220px',
    width: w.endsWith('px') ? '96%' : w,
    maxWidth: w,
    borderRadius: r,
    margin: isFramed ? '16px auto' : '0 auto'
  }
})
const slideshowImages = computed(() => {
  const list = slideshowConfig.value.slides || slideshowConfig.value.images || []
  if (!Array.isArray(list)) return []
  return list.map(s => {
    if (typeof s === 'string') {
      return { image: s, url: s, caption: '', link: '' }
    }
    const img = s?.image || s?.url || s?.path || ''
    return {
      ...s,
      image: img,
      url: img,
      caption: s?.caption || '',
      link: s?.link || ''
    }
  }).filter(s => !!s.image)
})

const isSlideshowVisible = computed(() => {
  if (!slideshowImages.value.length) return false
  const s = slideshowConfig.value
  if (s.enabled === false || s.enabled === 'false' || s.enabled === 0 || s.enabled === '0') {
    return false
  }
  return true
})

function nextSlide() {
  if (slideshowImages.value.length) {
    currentSlideIndex.value = (currentSlideIndex.value + 1) % slideshowImages.value.length
  }
}

function prevSlide() {
  if (slideshowImages.value.length) {
    currentSlideIndex.value = (currentSlideIndex.value - 1 + slideshowImages.value.length) % slideshowImages.value.length
  }
}

// Preloader & Footer
const preloaderConfig = computed(() => layout.value.preloader || {
  enabled: false,
  style: 'spinner',
  text: 'Chargement du portfolio...',
  duration: 1.0
})

const footerConfig = computed(() => layout.value.footer || {
  custom_text: '',
  show_socials: true,
  show_tech237_badge: true,
  show_back_to_top: true
})

// Custom Background
const bgImageUrl = computed(() => {
  const img = layout.value.bg_image
  return img ? formatImageUrl(img) : ''
})
const bgImageOpacity = computed(() => (layout.value.bg_image_opacity ?? 30) / 100)
const bgImageBlur = computed(() => layout.value.bg_image_blur ?? 0)
const bgImageSize = computed(() => layout.value.bg_image_size || 'cover')

const customBgStyle = computed(() => ({
  backgroundImage: `url(${bgImageUrl.value})`,
  backgroundSize: bgImageSize.value,
  backgroundPosition: 'center',
  backgroundRepeat: bgImageSize.value === 'repeat' ? 'repeat' : 'no-repeat',
  opacity: bgImageOpacity.value,
  filter: `blur(${bgImageBlur.value}px)`,
  zIndex: 0
}))

// Tabbed multi-page website mode removed by user request
const isTabbedMode = computed(() => false)

const isTabbedBarVisible = computed(() => false)

const activeTab = ref('all')
const tabPosition = computed(() => layout.value.tabbed_nav?.position || 'top')
const tabStyle = computed(() => layout.value.tabbed_nav?.style || 'pills')
const tabSize = computed(() => layout.value.tabbed_nav?.size || 'md')
const tabRadius = computed(() => layout.value.tabbed_nav?.radius || 'full')
const tabAlignment = computed(() => layout.value.tabbed_nav?.alignment || 'center')

function getTabJustifyClass() {
  if (tabAlignment.value === 'start') return 'justify-content-start'
  if (tabAlignment.value === 'end') return 'justify-content-end'
  return 'justify-content-center'
}

function getTabBtnClass(tabId) {
  if (activeTab.value === tabId) {
    return 'btn-primary shadow-sm text-white'
  }
  return 'btn-light border text-dark'
}

function isBlockEnabled(blockId) {
  const blk = orderedBlocks.value.find(b => b.id === blockId)
  return blk ? blk.enabled !== false : true
}

const availableTabs = computed(() => {
  const hidden = Array.isArray(layout.value.tabbed_nav?.hidden_tabs) ? layout.value.tabbed_nav.hidden_tabs : []
  const tabs = [
    { id: 'all', label: 'Vue Globale', iconClass: 'bi-grid-fill' }
  ]
  if (isBlockEnabled('about') && (liveBioText.value || (aboutDesign.value.ideas && aboutDesign.value.ideas.length))) {
    tabs.push({ id: 'about', label: 'À Propos', iconClass: 'bi-person-lines-fill' })
  }
  if (isBlockEnabled('video') && youtubeEmbedUrl.value) {
    tabs.push({ id: 'video', label: 'Vidéo Pitch', iconClass: 'bi-play-btn-fill' })
  }
  if (isBlockEnabled('now') && hasNowSection.value) {
    tabs.push({ id: 'now', label: 'En ce moment', iconClass: 'bi-broadcast-pin' })
  }
  if (isBlockEnabled('github') && hasGithubStats.value) {
    tabs.push({ id: 'github', label: 'GitHub', iconClass: 'bi-github' })
  }
  if (isBlockEnabled('skills') && props.portfolioUser.skills && props.portfolioUser.skills.length) {
    tabs.push({ id: 'skills', label: 'Compétences', iconClass: 'bi-cpu-fill', count: props.portfolioUser.skills.length })
  }
  if (isBlockEnabled('projects') && props.projects && props.projects.length) {
    tabs.push({ id: 'projects', label: 'Projets', iconClass: 'bi-rocket-takeoff-fill', count: props.projects.length })
  }
  if (isBlockEnabled('experiences') && props.portfolioUser.experiences && props.portfolioUser.experiences.length) {
    tabs.push({ id: 'experiences', label: 'Parcours', iconClass: 'bi-briefcase-fill' })
  }
  if (isBlockEnabled('educations') && props.portfolioUser.educations && props.portfolioUser.educations.length) {
    tabs.push({ id: 'educations', label: 'Formations', iconClass: 'bi-mortarboard-fill' })
  }
  if (isBlockEnabled('pocs') && props.pocs && props.pocs.length) {
    tabs.push({ id: 'pocs', label: 'POCs', iconClass: 'bi-lightning-charge-fill', count: props.pocs.length })
  }
  if (isBlockEnabled('process') && hasWorkProcess.value) {
    tabs.push({ id: 'process', label: 'Méthodologie', iconClass: 'bi-diagram-3-fill' })
  }
  if (isBlockEnabled('pricing') && pricingPackagesList.value.length) {
    tabs.push({ id: 'pricing', label: 'Offres & Tarifs', iconClass: 'bi-tags-fill', count: pricingPackagesList.value.length })
  }
  if (isBlockEnabled('certifications') && certificationsList.value.length) {
    tabs.push({ id: 'certifications', label: 'Certifications', iconClass: 'bi-patch-check-fill', count: certificationsList.value.length })
  }
  if (isBlockEnabled('testimonials') && testimonialsList.value.length) {
    tabs.push({ id: 'testimonials', label: 'Témoignages', iconClass: 'bi-chat-quote-fill', count: testimonialsList.value.length })
  }
  if (isBlockEnabled('faq') && faqItemsList.value.length) {
    tabs.push({ id: 'faq', label: 'FAQ', iconClass: 'bi-question-circle-fill' })
  }
  if (isBlockEnabled('badges') && props.badges && props.badges.length) {
    tabs.push({ id: 'badges', label: 'Trophées', iconClass: 'bi-trophy-fill', count: props.badges.length })
  }
  if (isBlockEnabled('quote_calculator') && quoteCalculatorConfig.value.enabled) {
    tabs.push({ id: 'quote_calculator', label: 'Devis', iconClass: 'bi-calculator-fill' })
  }
  if (isBlockEnabled('visual_gallery') && visualGalleryConfig.value.enabled && visualGalleryConfig.value.items?.length) {
    tabs.push({ id: 'visual_gallery', label: 'Galerie', iconClass: 'bi-images', count: visualGalleryConfig.value.items.length })
  }
  if (isBlockEnabled('languages_and_tools') && languagesAndToolsConfig.value.enabled) {
    tabs.push({ id: 'languages_and_tools', label: 'Langues & Outils', iconClass: 'bi-translate' })
  }
  if (isBlockEnabled('awards_press') && awardsPressConfig.value.enabled && awardsPressConfig.value.items?.length) {
    tabs.push({ id: 'awards_press', label: 'Distinctions', iconClass: 'bi-trophy-fill', count: awardsPressConfig.value.items.length })
  }
  if (isBlockEnabled('tech237_widgets') && tech237WidgetsConfig.value.enabled) {
    tabs.push({ id: 'tech237_widgets', label: 'Widgets', iconClass: 'bi-activity' })
  }
  if (isBlockEnabled('contact')) {
    tabs.push({ id: 'contact', label: 'Contact', iconClass: 'bi-envelope-fill' })
  }
  return tabs.filter(t => !hidden.includes(t.id))
})

function isBlockActiveInTab(blockId) {
  if (!isBlockEnabled(blockId)) return false
  if (isTabbedMode.value) {
    if (activeTab.value === 'all') return true
    if (activeTab.value === blockId) return true
    if (activeTab.value === 'about' && blockId === 'hero') return true
    return false
  }
  if (websiteNavbar.value.enabled && websiteNavbar.value.navigation_mode === 'multi_page') {
    const page = currentWebsitePage.value
    if (page === 'home') {
      return ['hero', 'about', 'projects', 'process', 'pricing', 'testimonials', 'faq', 'contact'].includes(blockId)
    }
    if (page === 'about') {
      return ['about', 'video', 'now', 'skills', 'experiences', 'educations', 'certifications', 'badges'].includes(blockId)
    }
    if (page === 'projects') {
      return ['projects', 'pocs', 'github', 'skills'].includes(blockId)
    }
    if (page === 'services') {
      return ['process', 'pricing', 'custom', 'testimonials', 'faq', 'contact'].includes(blockId)
    }
    if (page === 'contact') {
      return ['contact', 'faq'].includes(blockId)
    }
  }
  return true
}

// Project multi-columns class
const projectColsClass = computed(() => {
  const cols = Number(layout.value.projects_columns) || 2
  if (cols === 1) return 'col-12'
  if (cols === 2) return 'col-md-6'
  if (cols === 3) return 'col-md-6 col-lg-4'
  if (cols === 4) return 'col-md-6 col-lg-3'
  return 'col-md-6'
})

// Carousel navigation
function nextProject() {
  if (props.projects && props.projects.length) {
    currentProjectSlide.value = (currentProjectSlide.value + 1) % props.projects.length
  }
}

function prevProject() {
  if (props.projects && props.projects.length) {
    currentProjectSlide.value = (currentProjectSlide.value - 1 + props.projects.length) % props.projects.length
  }
}

// Bento mode check
const isBentoGrid = computed(() => {
  return currentStructure.value.startsWith('bento')
})

// Design Effects Studio & Blank Canvas Page Builder
const designEffects = computed(() => {
  const d = layout.value.design_effects || {}
  return {
    cursor_effect: d.cursor_effect || (layout.value.spotlight_enabled ? 'spotlight' : (layout.value.custom_cursor_enabled ? 'magnetic_ring' : 'none')),
    section_divider: d.section_divider || 'none',
    heading_style: d.heading_style || 'classic',
    button_style: d.button_style || 'classic',
    card_border_effect: d.card_border_effect || 'subtle',
    scroll_reveal: d.scroll_reveal || 'fade_up',
    floating_orbs: Boolean(d.floating_orbs),
    glass_blur_intensity: Number(d.glass_blur_intensity ?? 12)
  }
})

const blankCanvasConfig = computed(() => {
  return layout.value.blank_canvas || {
    enabled: false,
    hide_default_hero: false,
    hide_default_navbar: false,
    hide_default_footer: false,
    hide_kpi_ribbon: true,
    hide_tech_marquee: true,
    canvas_max_width: '1240px',
    widgets: []
  }
})

const isBlankCanvasMode = computed(() => false)

const blankCanvasWidgets = computed(() => {
  const list = blankCanvasConfig.value.widgets
  if (!Array.isArray(list)) return []
  return list.filter(w => w && w.enabled !== false)
})

const customOnlyCanvasWidgets = computed(() => {
  return blankCanvasWidgets.value.filter(w => w.type !== 'system_block')
})

function getCanvasWidgetColClass(width) {
  if (width === '33%') return 'col-12 col-md-4'
  if (width === '50%') return 'col-12 col-md-6'
  if (width === '66%') return 'col-12 col-md-8'
  return 'col-12'
}

function getCanvasWidgetBoxClass(w) {
  if (w.bg_style === 'transparent') return ''
  if (w.bg_style === 'glass') return 'backdrop-blur border shadow-sm'
  return 'content-card shadow-sm border'
}

function getCanvasWidgetPaddingClass(w) {
  if (w.padding === 'none') return 'p-0'
  if (w.padding === 'sm') return 'p-3'
  if (w.padding === 'md') return 'p-3 p-md-4'
  if (w.padding === 'xl') return 'p-4 p-md-5 py-md-5'
  return 'p-4 p-md-5'
}

function getCanvasWidgetInlineStyle(w) {
  const style = {
    borderRadius: w.border_radius || '24px'
  }
  if (w.bg_style === 'custom_color') {
    style.backgroundColor = w.bg_color || '#FFFFFF'
    style.color = w.text_color || '#0F172A'
  } else if (w.bg_style === 'gradient_dark') {
    style.background = 'linear-gradient(135deg, #0F172A 0%, #1E293B 100%)'
    style.color = '#FFFFFF'
  } else if (w.bg_style === 'gradient_accent') {
    style.background = `linear-gradient(135deg, ${accentColor.value} 0%, #0F172A 100%)`
    style.color = '#FFFFFF'
  }
  return style
}

const containerMaxWidth = computed(() => {
  if (isBlankCanvasMode.value && blankCanvasConfig.value.canvas_max_width) {
    return blankCanvasConfig.value.canvas_max_width
  }
  if (currentStructure.value === 'bento_3col') return '1340px'
  if (isBentoGrid.value) return '1240px'
  if (currentStructure.value === 'compact') return '760px'
  if (currentStructure.value === 'zen') return '840px'
  if (websiteNavbar.value.enabled) return '1240px'
  return '980px'
})

function getBlockGridClass(block) {
  if (!isBentoGrid.value) return 'w-100'
  if (currentStructure.value === 'bento_3col') {
    if (block.width === 'third') return 'col-12 col-md-4'
    if (block.width === 'half') return 'col-12 col-md-6'
    return 'col-12'
  }
  return block.width === 'half' ? 'col-12 col-lg-6' : 'col-12'
}

const customColorPalette = computed(() => {
  return layout.value.custom_color_palette || { enabled: false }
})

const cssVars = computed(() => {
  const vars = {
    '--accent-color': accentColor.value,
  }
  const p = customColorPalette.value
  if (p && p.enabled) {
    if (p.bg_type === 'gradient') {
      vars.background = `linear-gradient(${Number(p.bg_angle) || 135}deg, ${p.bg_color_1 || '#F8FAFC'}, ${p.bg_color_2 || '#EEF2FF'})`
    } else {
      vars.background = p.bg_color_1 || '#F8FAFC'
    }
    vars.color = p.text_primary || '#0F172A'
    vars['--custom-card-bg'] = p.card_bg || '#FFFFFF'
    vars['--custom-card-opacity'] = (Number(p.card_opacity ?? 100) / 100).toString()
    vars['--custom-text-primary'] = p.text_primary || '#0F172A'
    vars['--custom-text-muted'] = p.text_muted || '#64748B'
    vars['--accent-secondary'] = p.accent_secondary || '#3B82F6'
  }
  return vars
})

// Dynamic Ordered Blocks
const defaultBlocks = [
  { id: 'hero', name: 'En-tête & Présentation', enabled: true, width: 'full' },
  { id: 'about', name: 'À propos & Parcours', enabled: true, width: 'full' },
  { id: 'video', name: 'Vidéo de Présentation', enabled: true, width: 'full' },
  { id: 'now', name: 'En ce moment (Now)', enabled: true, width: 'half' },
  { id: 'github', name: 'Statistiques GitHub Live', enabled: true, width: 'half' },
  { id: 'skills', name: 'Compétences & Stack technique', enabled: true, width: 'half' },
  { id: 'languages_and_tools', name: 'Langues, Soft Skills & Outils', enabled: true, width: 'half' },
  { id: 'projects', name: 'Projets Tech en vedette', enabled: true, width: 'full' },
  { id: 'visual_gallery', name: 'Galerie Visuelle & Avant/Après', enabled: true, width: 'full' },
  { id: 'pocs', name: 'Mini-Actus & POCs Tech237', enabled: true, width: 'half' },
  { id: 'experiences', name: 'Expériences professionnelles', enabled: true, width: 'full' },
  { id: 'educations', name: 'Formations & Diplômes', enabled: true, width: 'half' },
  { id: 'certifications', name: 'Certifications & Distinctions', enabled: true, width: 'half' },
  { id: 'awards_press', name: 'Distinctions, Hackathons & Presse', enabled: true, width: 'full' },
  { id: 'process', name: 'Mon Processus & Méthodologie', enabled: true, width: 'full' },
  { id: 'pricing', name: 'Grille Tarifaire & Packs Services', enabled: true, width: 'full' },
  { id: 'quote_calculator', name: 'Simulateur de Devis Interactif', enabled: true, width: 'full' },
  { id: 'testimonials', name: 'Témoignages & Recommandations', enabled: true, width: 'full' },
  { id: 'faq', name: 'Questions Fréquentes (FAQ)', enabled: true, width: 'full' },
  { id: 'tech237_widgets', name: 'Widgets Tech237 (Formulaires & Uptime)', enabled: true, width: 'full' },
  { id: 'badges', name: 'Trophées & Badges Tech237', enabled: true, width: 'half' },
  { id: 'custom', name: 'Sections personnalisées libres', enabled: true, width: 'full' },
  { id: 'contact', name: 'Coordonnées & Réseaux', enabled: true, width: 'full' },
]

const orderedBlocks = computed(() => {
  if (isBlankCanvasMode.value && blankCanvasConfig.value.hide_default_hero) {
    const sysWidgets = blankCanvasWidgets.value.filter(w => w.type === 'system_block' && w.system_block_id)
    const activeSysIds = sysWidgets.map(w => w.system_block_id)
    return defaultBlocks.map(db => ({
      ...db,
      enabled: activeSysIds.includes(db.id),
      width: 'full'
    }))
  }

  let list = Array.isArray(layout.value.blocks) && layout.value.blocks.length > 0 
    ? [...layout.value.blocks] 
    : [...defaultBlocks]

  if (youtubeEmbedUrl.value && !list.some(b => b.id === 'video')) {
    const aboutIdx = list.findIndex(b => b.id === 'about')
    const insertIdx = aboutIdx !== -1 ? aboutIdx + 1 : 1
    list.splice(insertIdx, 0, { id: 'video', name: 'Vidéo de Présentation', enabled: true, width: 'full' })
  }

  const extraBlocks = defaultBlocks.filter(db => ['now', 'github', 'certifications', 'process', 'pricing', 'testimonials', 'faq', 'quote_calculator', 'visual_gallery', 'languages_and_tools', 'awards_press', 'tech237_widgets'].includes(db.id))
  extraBlocks.forEach(eb => {
    if (!list.some(b => b.id === eb.id)) {
      const contactIdx = list.findIndex(b => b.id === 'contact')
      if (contactIdx !== -1) {
        list.splice(contactIdx, 0, { ...eb })
      } else {
        list.push({ ...eb })
      }
    }
  })

  // Fusion avec le constructeur de menu : si un menu cible un bloc, on s'assure qu'il est activé dans la page
  const customMenus = websiteNavbar.value.custom_menu_items || []
  if (customMenus.length > 0) {
    const linkedIds = new Set()
    customMenus.forEach(m => {
      if (m.target_block) linkedIds.add(m.target_block)
      if (Array.isArray(m.children)) m.children.forEach(c => c.target_block && linkedIds.add(c.target_block))
    })
    list = list.map(b => linkedIds.has(b.id) ? { ...b, enabled: true } : b)
  }

  return list
})

function shouldRenderBlock(id) {
  if (id === 'about') return Boolean(liveBioText.value || (aboutDesign.value.ideas && aboutDesign.value.ideas.length))
  if (id === 'video') return Boolean(youtubeEmbedUrl.value)
  if (id === 'now') return Boolean(hasNowSection.value)
  if (id === 'github') return Boolean(hasGithubStats.value)
  if (id === 'skills') return Boolean(props.portfolioUser.skills && props.portfolioUser.skills.length)
  if (id === 'projects') return Boolean(props.projects && props.projects.length)
  if (id === 'pocs') return Boolean(props.pocs && props.pocs.length)
  if (id === 'experiences') return Boolean(props.portfolioUser.experiences && props.portfolioUser.experiences.length)
  if (id === 'educations') return Boolean(props.portfolioUser.educations && props.portfolioUser.educations.length)
  if (id === 'certifications') return Boolean(certificationsList.value.length)
  if (id === 'process') return Boolean(hasWorkProcess.value)
  if (id === 'pricing') return Boolean(pricingPackagesList.value.length)
  if (id === 'testimonials') return Boolean(testimonialsList.value.length)
  if (id === 'faq') return Boolean(faqItemsList.value.length)
  if (id === 'badges') return Boolean(props.badges && props.badges.length)
  if (id === 'custom') return Boolean(props.portfolioUser.custom_sections && props.portfolioUser.custom_sections.length)
  if (id === 'quote_calculator') return Boolean(quoteCalculatorConfig.value.enabled)
  if (id === 'visual_gallery') return Boolean(visualGalleryConfig.value.enabled && visualGalleryConfig.value.items?.length)
  if (id === 'languages_and_tools') return Boolean(languagesAndToolsConfig.value.enabled)
  if (id === 'awards_press') return Boolean(awardsPressConfig.value.enabled && awardsPressConfig.value.items?.length)
  if (id === 'tech237_widgets') return Boolean(tech237WidgetsConfig.value.enabled)
  return true
}

// =========================================================================
// 10 New Portfolio Customization Options (State & Computeds)
// =========================================================================

// Option 2: Announcement Bar
const announcementBar = computed(() => {
  return layout.value.announcement_bar || { enabled: false }
})
const announcementDismissed = ref(false)
const announcementBarStyle = computed(() => {
  const bar = announcementBar.value
  let bg = accentColor.value
  if (bar.bg_style === 'gradient') bg = `linear-gradient(90deg, ${accentColor.value}, #0F172A)`
  else if (bar.bg_style === 'dark') bg = '#0F172A'
  else if (bar.bg_style === 'custom') bg = bar.bg_color || '#00A859'
  return {
    background: bg,
    color: bar.text_color || '#FFFFFF'
  }
})

// Option 3: Command Palette Ctrl + K
const commandPaletteConfig = computed(() => {
  return layout.value.command_palette || { enabled: true, show_in_navbar: true, shortcut_hint: true }
})
const commandPaletteOpen = ref(false)
const commandQuery = ref('')
const commandResults = computed(() => {
  const q = (commandQuery.value || '').trim().toLowerCase()
  const items = []

  // Quick actions
  items.push({ type: 'Action', icon: 'bi-envelope-fill', label: 'Contacter / Envoyer un message', action: () => navigateWebsitePage('contact', 'portfolioContactSection') })
  if (props.portfolioUser.cv_url) {
    items.push({ type: 'Action', icon: 'bi-file-earmark-pdf-fill', label: 'Télécharger le CV (PDF)', href: props.portfolioUser.cv_url })
  }
  items.push({ type: 'Action', icon: 'bi-share-fill', label: 'Copier le lien du portfolio', action: () => copyProfileUrl() })

  // Projects
  ;(props.projects || []).forEach(p => {
    items.push({ type: 'Projet', icon: 'bi-rocket-takeoff-fill', label: p.title, sub: p.category || p.description?.slice(0, 60), anchor: 'block-projects' })
  })
  // Skills
  ;(props.portfolioUser.skills || []).forEach(s => {
    const name = typeof s === 'object' ? s.name : s
    items.push({ type: 'Compétence', icon: 'bi-cpu-fill', label: name, sub: typeof s === 'object' ? s.level : '', anchor: 'block-skills' })
  })
  // Experiences
  ;(props.portfolioUser.experiences || []).forEach(exp => {
    items.push({ type: 'Expérience', icon: 'bi-briefcase-fill', label: `${exp.role} @ ${exp.company}`, sub: exp.period, anchor: 'block-experiences' })
  })

  if (!q) return items.slice(0, 12)
  return items.filter(it => (it.label || '').toLowerCase().includes(q) || (it.sub || '').toLowerCase().includes(q) || (it.type || '').toLowerCase().includes(q)).slice(0, 15)
})

function executeCommandItem(item) {
  commandPaletteOpen.value = false
  commandQuery.value = ''
  if (typeof item.action === 'function') {
    item.action()
  } else if (item.href) {
    window.open(item.href, '_blank')
  } else if (item.anchor) {
    const el = document.getElementById(item.anchor)
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

function handleCommandKeydown(e) {
  if (!commandPaletteConfig.value.enabled) return
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault()
    commandPaletteOpen.value = !commandPaletteOpen.value
  } else if (e.key === 'Escape' && commandPaletteOpen.value) {
    commandPaletteOpen.value = false
  }
}

// Option 4: Interactive Quote Calculator
const quoteCalculatorConfig = computed(() => {
  return layout.value.quote_calculator || { enabled: false, base_services: [], addons: [] }
})
const selectedQuoteServiceId = ref('')
const selectedQuoteAddonIds = ref([])
const quoteUrgency = ref(false)

const selectedQuoteService = computed(() => {
  const list = quoteCalculatorConfig.value.base_services || []
  return list.find(s => s.id === selectedQuoteServiceId.value) || list[0] || null
})

const quoteTotalPrice = computed(() => {
  let total = Number(selectedQuoteService.value?.price || 0)
  const addons = quoteCalculatorConfig.value.addons || []
  addons.forEach(a => {
    if (selectedQuoteAddonIds.value.includes(a.id)) {
      total += Number(a.price || 0)
    }
  })
  if (quoteUrgency.value) {
    total = Math.round(total * (Number(quoteCalculatorConfig.value.urgency_multiplier) || 1.35))
  }
  return total
})

const quoteTotalDays = computed(() => {
  let days = Number(selectedQuoteService.value?.days || 0)
  if (quoteUrgency.value && days > 2) days = Math.max(2, Math.ceil(days * 0.65))
  return days
})

function toggleQuoteAddon(id) {
  const idx = selectedQuoteAddonIds.value.indexOf(id)
  if (idx === -1) selectedQuoteAddonIds.value.push(id)
  else selectedQuoteAddonIds.value.splice(idx, 1)
}

function applyQuoteToContact() {
  const srv = selectedQuoteService.value?.label || 'Prestation'
  const addons = (quoteCalculatorConfig.value.addons || []).filter(a => selectedQuoteAddonIds.value.includes(a.id)).map(a => a.label)
  const currency = quoteCalculatorConfig.value.currency || 'FCFA'
  contactForm.value.subject = `Demande de devis : ${srv}`
  contactForm.value.message = `Bonjour ${props.portfolioUser.name},\n\nJe souhaite demander un devis estimatif basé sur votre simulateur :\n- Prestation : ${srv}\n- Options : ${addons.length ? addons.join(', ') : 'Aucune'}\n- Livraison urgente : ${quoteUrgency.value ? 'Oui' : 'Non'}\n- Estimation indicative : ${quoteTotalPrice.value.toLocaleString('fr-FR')} ${currency} (~${quoteTotalDays.value} jours)\n\nPouvez-vous me recontacter pour en discuter ?`
  navigateWebsitePage('contact', 'portfolioContactSection')
}

// Option 5: Visual Gallery & Before/After
const visualGalleryConfig = computed(() => {
  return layout.value.visual_gallery || { enabled: false, items: [], columns: 3, enable_lightbox: true }
})
const galleryActiveCategory = ref('all')
const galleryCategories = computed(() => {
  const cats = new Set()
  ;(visualGalleryConfig.value.items || []).forEach(it => { if (it.category) cats.add(it.category) })
  return ['all', ...Array.from(cats)]
})
const filteredGalleryItems = computed(() => {
  const list = (visualGalleryConfig.value.items || []).filter(it => it && it.image_url)
  if (galleryActiveCategory.value === 'all') return list
  return list.filter(it => it.category === galleryActiveCategory.value)
})
const lightboxItem = ref(null)
const beforeAfterPositions = ref({})
function getBeforeAfterPos(id) {
  return beforeAfterPositions.value[id] ?? 50
}
function setBeforeAfterPos(id, val) {
  beforeAfterPositions.value[id] = Number(val)
}

// Option 6: Languages, Soft Skills & Daily Tools
const languagesAndToolsConfig = computed(() => {
  return layout.value.languages_and_tools || { enabled: false, languages: [], soft_skills: [], daily_tools: [] }
})

// Option 7: Tech237 Widgets (Forms & Heartbeat)
const tech237WidgetsConfig = computed(() => {
  return layout.value.tech237_widgets || { enabled: false, form_slug: '', show_heartbeat: false }
})
const selectedPublicForm = computed(() => {
  const slug = tech237WidgetsConfig.value.form_slug
  if (!slug) return null
  return (props.publicForms || []).find(f => f.slug === slug) || null
})
const visiblePublicMonitors = computed(() => {
  return (props.publicMonitors || []).slice(0, 6)
})

// Option 8: Print Settings
const printSettingsConfig = computed(() => {
  return layout.value.print_settings || { include_qr_header: true, density: 'detailed', hidden_print_blocks: [] }
})
function isHiddenInPrint(blockId) {
  return (printSettingsConfig.value.hidden_print_blocks || []).includes(blockId)
}

// Option 10: Awards, Hackathons & Press
const awardsPressConfig = computed(() => {
  return layout.value.awards_press || { enabled: false, title: 'Distinctions, Hackathons & Presse', items: [] }
})

// 11. 100% Section Design Customizations (About, Skills, Projects, Experiences, Testimonials, Contact)
const aboutDesign = computed(() => {
  return layout.value.about_design || {
    title: 'À Propos',
    subtitle: '',
    icon: 'bi-person-lines-fill',
    layout_style: 'classic',
    text_align: 'left',
    font_size: 'md',
    box_style: 'card',
    animation: 'fade_up',
    highlight_quote: '',
    image_url: '',
    ideas: []
  }
})

const liveBioText = ref(props.portfolioUser.bio || '')
const showInlineBioEditor = ref(false)
const isSavingInlineBio = ref(false)
const inlineBioSavedMsg = ref(false)

async function saveInlineBioToUsersTable() {
  if (isSavingInlineBio.value) return
  isSavingInlineBio.value = true
  inlineBioSavedMsg.value = false
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const payload = {
      headline: props.portfolioUser.headline || '',
      bio: liveBioText.value || '',
      location: props.portfolioUser.location || '',
      availability: props.portfolioUser.availability || 'open',
      is_portfolio_public: props.portfolioUser.is_portfolio_public !== false,
      social_links: props.portfolioUser.social_links || {},
      skills: props.portfolioUser.skills || [],
      experiences: props.portfolioUser.experiences || [],
      educations: props.portfolioUser.educations || [],
      custom_sections: props.portfolioUser.custom_sections || [],
      portfolio_layout: props.portfolioUser.portfolio_layout || {}
    }
    const res = await fetch('/espace-utilisateur/portfolio/save', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify(payload)
    })
    if (res.ok) {
      inlineBioSavedMsg.value = true
      setTimeout(() => {
        inlineBioSavedMsg.value = false
        showInlineBioEditor.value = false
      }, 1500)
    }
  } catch (e) {} finally {
    isSavingInlineBio.value = false
  }
}

function getAboutTextAlignClass() {
  const align = aboutDesign.value.text_align
  if (align === 'center') return 'text-center'
  if (align === 'justify') return 'text-justify'
  return 'text-start'
}

function getAboutFontSizeClass() {
  const sz = aboutDesign.value.font_size
  if (sz === 'sm') return 'small'
  if (sz === 'lg') return 'fs-5'
  if (sz === 'xl') return 'fs-4'
  return ''
}

const skillsDesign = computed(() => {
  return layout.value.skills_design || {
    title: 'Compétences & Stack Technique',
    subtitle: '',
    icon: 'bi-cpu-fill',
    layout_style: layout.value.skills_style || 'chips',
    color_mode: 'custom_per_skill',
    hover_effect: 'lift',
    animation: 'shimmer',
    columns: 3,
    show_percentage: true,
    show_category: true
  }
})

const skillsColsClass = computed(() => {
  const c = Number(skillsDesign.value.columns) || 3
  if (c === 2) return 'col-12 col-sm-6'
  if (c === 4) return 'col-6 col-md-3'
  return 'col-12 col-sm-6 col-md-4'
})

const groupedSkillsByDomain = computed(() => {
  const groups = {}
  const list = props.portfolioUser.skills || []
  list.forEach(s => {
    const cat = (typeof s === 'object' && s.category) ? s.category : 'Général & Stack'
    if (!groups[cat]) groups[cat] = []
    groups[cat].push(s)
  })
  return groups
})

const projectsDesign = computed(() => {
  return layout.value.projects_design || {
    title: 'Projets & Réalisations',
    subtitle: '',
    icon: 'bi-rocket-takeoff-fill',
    layout_style: layout.value.projects_style || 'zigzag',
    columns: layout.value.projects_columns || 2,
    show_logo: true,
    logo_shape: 'rounded',
    logo_size: 'md',
    hover_effect: 'lift_shadow',
    card_radius: '20px',
    text_align: 'left',
    show_case_study_btn: true,
    show_stats: true,
    show_category: true,
    show_project_number: true,
    project_overrides: {}
  }
})

function getEffectiveProjectLogo(proj) {
  if (!proj) return ''
  const ov = projectsDesign.value.project_overrides?.[proj.id]
  if (ov && ov.custom_logo) return formatImageUrl(ov.custom_logo)
  if (proj.logo_url) return formatImageUrl(proj.logo_url)
  return ''
}

function getEffectiveProjectBadge(proj) {
  if (!proj) return ''
  const ov = projectsDesign.value.project_overrides?.[proj.id]
  return (ov && ov.custom_badge) ? ov.custom_badge : ''
}

function getProjectLogoShapeClass() {
  const s = projectsDesign.value.logo_shape
  if (s === 'circle') return 'rounded-circle'
  if (s === 'square') return 'rounded-1'
  return 'rounded-3'
}

function getProjectLogoSizeStyle(isLarge = false) {
  const sz = projectsDesign.value.logo_size || 'md'
  if (sz === 'sm') return isLarge ? { width: '48px', height: '48px' } : { width: '36px', height: '36px' }
  if (sz === 'lg') return isLarge ? { width: '80px', height: '80px' } : { width: '58px', height: '58px' }
  return isLarge ? { width: '64px', height: '64px' } : { width: '46px', height: '46px' }
}

const experienceDesign = computed(() => {
  return layout.value.experience_design || {
    title: 'Parcours & Expériences',
    subtitle: '',
    icon: 'bi-briefcase-fill',
    layout_style: 'timeline_left',
    text_align: 'left',
    show_company_image: true,
    image_position: 'left',
    hover_effect: 'lift',
    show_icons: true
  }
})

function parseCommaTags(str) {
  if (!str) return []
  if (Array.isArray(str)) return str
  return String(str).split(',').map(s => s.trim()).filter(Boolean)
}

const testimonialsDesign = computed(() => {
  const t = layout.value.testimonials_design || {}
  return {
    title: t.title || 'Témoignages & Recommandations',
    subtitle: t.subtitle || '',
    icon: t.icon || 'bi-chat-quote-fill',
    display_mode: t.display_mode || t.layout_style || 'slider',
    autoplay: t.autoplay !== false,
    speed: Number(t.speed || t.interval) || 5,
    cards_per_view: Number(t.cards_per_view || t.slides_per_view) || 2,
    hover_effect: t.hover_effect || t.animation || 'lift',
    text_align: t.text_align || 'left',
    show_stars: t.show_stars !== false
  }
})

const currentTestimonialSlide = ref(0)
const visibleSliderTestimonials = computed(() => {
  const list = testimonialsList.value
  if (!list.length) return []
  const perView = Number(testimonialsDesign.value.cards_per_view) || 2
  const start = currentTestimonialSlide.value % list.length
  const res = []
  for (let i = 0; i < Math.min(perView, list.length); i++) {
    res.push(list[(start + i) % list.length])
  }
  return res
})

function nextTestimonialSlide() {
  if (testimonialsList.value.length) {
    currentTestimonialSlide.value = (currentTestimonialSlide.value + 1) % testimonialsList.value.length
  }
}

function prevTestimonialSlide() {
  if (testimonialsList.value.length) {
    currentTestimonialSlide.value = (currentTestimonialSlide.value - 1 + testimonialsList.value.length) % testimonialsList.value.length
  }
}

const presetAvatarsMap = {
  ceo_male: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="50" fill="#1E3A8A"/><circle cx="50" cy="36" r="18" fill="#FBBF24"/><path d="M22 90 C22 65 78 65 78 90" fill="#0F172A"/><path d="M44 68 L50 84 L56 68 Z" fill="#EF4444"/><circle cx="44" cy="34" r="2.2" fill="#1E293B"/><circle cx="56" cy="34" r="2.2" fill="#1E293B"/><path d="M44 43 Q50 48 56 43" stroke="#1E293B" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg>')}`,
  ceo_female: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="50" fill="#831843"/><path d="M26 42 C26 16 74 16 74 42 L78 65 L22 65 Z" fill="#1F2937"/><circle cx="50" cy="38" r="17" fill="#F59E0B"/><path d="M24 90 C24 67 76 67 76 90" fill="#EC4899"/><circle cx="44" cy="36" r="2.2" fill="#1E293B"/><circle cx="56" cy="36" r="2.2" fill="#1E293B"/><path d="M44 44 Q50 49 56 44" stroke="#BE185D" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg>')}`,
  dev_male: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="50" fill="#065F46"/><circle cx="50" cy="37" r="18" fill="#D97706"/><path d="M22 90 C22 66 78 66 78 90" fill="#10B981"/><rect x="37" y="31" width="11" height="8" rx="2" stroke="#FFFFFF" stroke-width="2" fill="none"/><rect x="52" y="31" width="11" height="8" rx="2" stroke="#FFFFFF" stroke-width="2" fill="none"/><line x1="48" y1="35" x2="52" y2="35" stroke="#FFFFFF" stroke-width="2"/><path d="M45 45 Q50 49 55 45" stroke="#1E293B" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg>')}`,
  dev_female: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="50" fill="#4C1D95"/><circle cx="50" cy="24" r="12" fill="#111827"/><circle cx="50" cy="40" r="17" fill="#B45309"/><path d="M24 90 C24 68 76 68 76 90" fill="#8B5CF6"/><rect x="38" y="34" width="10" height="7" rx="2" stroke="#FDE68A" stroke-width="2" fill="none"/><rect x="52" y="34" width="10" height="7" rx="2" stroke="#FDE68A" stroke-width="2" fill="none"/><path d="M45 47 Q50 51 55 47" stroke="#FFF" stroke-width="2.2" fill="none" stroke-linecap="round"/></svg>')}`,
  designer: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="50" fill="#7C2D12"/><circle cx="50" cy="38" r="18" fill="#FBBF24"/><path d="M30 28 C35 16 65 16 70 28" fill="#EA580C"/><path d="M22 90 C22 66 78 66 78 90" fill="#F97316"/><circle cx="44" cy="36" r="2.3" fill="#1E293B"/><circle cx="56" cy="36" r="2.3" fill="#1E293B"/><path d="M44 45 Q50 50 56 45" stroke="#1E293B" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg>')}`,
  consultant: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="50" fill="#0F766E"/><circle cx="50" cy="37" r="18" fill="#92400E"/><path d="M22 90 C22 66 78 66 78 90" fill="#14B8A6"/><circle cx="44" cy="35" r="2.3" fill="#FFF"/><circle cx="56" cy="35" r="2.3" fill="#FFF"/><path d="M43 45 Q50 50 57 45" stroke="#FFF" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg>')}`,
  client_vip: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="50" fill="#1E293B"/><circle cx="50" cy="37" r="18" fill="#F59E0B"/><path d="M22 90 C22 66 78 66 78 90" fill="#EAB308"/><polygon points="50,10 54,18 62,18 56,23 58,30 50,26 42,30 44,23 38,18 46,18" fill="#FDE047"/><circle cx="44" cy="36" r="2.3" fill="#1E293B"/><circle cx="56" cy="36" r="2.3" fill="#1E293B"/><path d="M44 45 Q50 50 56 45" stroke="#1E293B" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg>')}`,
  preset_dev_male: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#1E293B"/><circle cx="40" cy="30" r="14" fill="#FBBF24"/><path d="M18 70c4-14 14-20 22-20s18 6 22 20" fill="#10B981"/><rect x="28" y="26" width="10" height="6" rx="2" fill="none" stroke="#fff" stroke-width="2"/><rect x="42" y="26" width="10" height="6" rx="2" fill="none" stroke="#fff" stroke-width="2"/></svg>')}`,
  preset_dev_female: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#4C1D95"/><path d="M22 32c0-12 8-18 18-18s18 6 18 18v12H22z" fill="#111827"/><circle cx="40" cy="32" r="13" fill="#FDE68A"/><path d="M18 70c4-13 13-19 22-19s18 6 22 19" fill="#EC4899"/></svg>')}`,
  preset_ceo: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#0F172A"/><circle cx="40" cy="29" r="14" fill="#FDBA74"/><path d="M16 70c5-15 15-21 24-21s19 6 24 21" fill="#3B82F6"/><polygon points="40,50 36,64 40,70 44,64" fill="#EF4444"/></svg>')}`,
  preset_designer: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#831843"/><circle cx="40" cy="30" r="14" fill="#FED7AA"/><path d="M18 70c4-14 14-20 22-20s18 6 22 20" fill="#F59E0B"/><path d="M24 22c6-6 26-6 32 0" stroke="#A855F7" stroke-width="6" stroke-linecap="round"/></svg>')}`,
  preset_startup: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#064E3B"/><circle cx="40" cy="30" r="14" fill="#FDE047"/><path d="M18 70c4-14 14-20 22-20s18 6 22 20" fill="#06B6D4"/></svg>')}`,
  preset_pm: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#1E3A8A"/><circle cx="40" cy="30" r="14" fill="#FCD34D"/><path d="M18 70c4-14 14-20 22-20s18 6 22 20" fill="#8B5CF6"/></svg>')}`,
  preset_bot: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#111827"/><rect x="22" y="22" width="36" height="30" rx="8" fill="#10B981"/><circle cx="33" cy="36" r="4" fill="#fff"/><circle cx="47" cy="36" r="4" fill="#fff"/><rect x="30" y="44" width="20" height="3" rx="1.5" fill="#fff"/></svg>')}`,
  preset_rocket: `data:image/svg+xml;utf8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#31102F"/><circle cx="40" cy="40" r="22" fill="#F43F5E"/><polygon points="40,22 50,48 40,43 30,48" fill="#fff"/></svg>')}`
}

function getTestimonialAvatarSrc(testi) {
  if (!testi) return ''
  if (testi.avatar_preset && presetAvatarsMap[testi.avatar_preset]) {
    return presetAvatarsMap[testi.avatar_preset]
  }
  if (testi.avatar_url) {
    return formatImageUrl(testi.avatar_url)
  }
  return ''
}

const contactFormConfig = computed(() => {
  return layout.value.contact_form || {
    enabled: true,
    title: 'Entrons en contact',
    subtitle: 'Un projet web, mobile ou une opportunité ? Envoyez-moi un message direct.',
    icon: 'bi-send-check-fill',
    image_url: '',
    layout_style: 'split_info_left',
    text_align: 'left',
    animation: 'lift',
    button_text: 'Envoyer le message'
  }
})

const socialLinks = computed(() => {
  return props.portfolioUser.social_links || {}
})

const hasSocialLinks = computed(() => {
  const s = socialLinks.value
  return Boolean(s.github || s.linkedin || s.twitter || s.website || s.whatsapp)
})

const shareUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.origin + '/@' + props.portfolioUser.username
  }
  return 'https://tech237.net/@' + props.portfolioUser.username
})

const seoTitle = computed(() => {
  const h = props.portfolioUser.headline ? ` — ${props.portfolioUser.headline}` : ' • Portfolio Tech'
  return `${props.portfolioUser.name}${h} | Tech237`
})

const seoDescription = computed(() => {
  return props.portfolioUser.bio || `Découvrez le portfolio professionnel, les projets et compétences de ${props.portfolioUser.name} sur Tech237.`
})

const whatsappShareUrl = computed(() => {
  const text = encodeURIComponent(`Découvrez mon portfolio tech et mes projets sur Tech237 : ${shareUrl.value}`)
  return `https://api.whatsapp.com/send?text=${text}`
})

const linkedinShareUrl = computed(() => {
  return `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl.value)}`
})

function copyPortfolioLink() {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(shareUrl.value)
    isCopied.value = true
    setTimeout(() => {
      isCopied.value = false
    }, 2500)
  }
}

function printCV() {
  window.print()
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function handleScroll() {
  showBackToTopBtn.value = window.scrollY > 280
}

function getInitials(name) {
  if (!name) return 'U'
  const p = name.trim().split(' ')
  return p.length >= 2 ? (p[0].charAt(0) + p[1].charAt(0)).toUpperCase() : name.substring(0, 2).toUpperCase()
}

function cleanPhone(phone) {
  if (!phone) return ''
  return phone.replace(/[^0-9]/g, '')
}

function formatDateShort(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) {
    return path
  }
  return '/' + path.replace(/^\/+/, '')
}

function getAvailabilityLabel(val) {
  const map = {
    freelance: 'Disponible freelance',
    full_time: 'À la recherche d\'un CDI',
    open: 'Ouvert aux opportunités',
    not_available: 'Actuellement en poste',
  }
  return map[val] || 'Ouvert aux opportunités'
}

function getAvailabilityClass(val) {
  if (val === 'open' || val === 'freelance' || val === 'full_time') {
    return 'availability-open'
  }
  return 'availability-busy'
}

// Format and safe HTML tag interpreter
function formatHtml(text) {
  if (!text) return ''
  const hasHtml = /<[a-z][\s\S]*>/i.test(text)
  if (hasHtml) {
    return text
      .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
      .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
      .replace(/on\w+\s*=\s*["'][^"']*["']/gi, '')
      .replace(/javascript:/gi, '')
  }
  const escaped = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
  return escaped.replace(/\n/g, '<br>')
}

// Skill levels styling
function getSkillLevelBadgeClass(level) {
  const l = (level || '').toLowerCase()
  if (l.includes('expert')) return 'bg-success text-white'
  if (l.includes('avancé') || l.includes('avance')) return 'bg-primary text-white'
  if (l.includes('intermédiaire') || l.includes('intermediaire')) return 'bg-warning text-dark'
  if (l.includes('débutant') || l.includes('debutant')) return 'bg-info text-dark'
  return 'bg-secondary text-white'
}

const skillBgPalette = [
  '#DCFCE7', '#DBEAFE', '#FEF3C7', '#F3E8FF', '#FCE7F3', '#E0F2FE', '#FFEDD5'
]
const skillColorPalette = [
  '#166534', '#1E40AF', '#92400E', '#6B21A8', '#9D174D', '#075985', '#9A3412'
]
const skillSolidPalette = [
  '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#F97316'
]

function getSkillSolidColor(idx, skill) {
  const mode = skillsDesign.value.color_mode
  if (mode === 'accent') return accentColor.value
  if (mode === 'custom_per_skill' && typeof skill === 'object' && skill?.color) {
    return skill.color
  }
  return skillSolidPalette[idx % skillSolidPalette.length]
}

function getSkillBg(idx, skill) {
  const mode = skillsDesign.value.color_mode
  if (mode === 'accent') return `${accentColor.value}18`
  if (mode === 'gradient') return `linear-gradient(135deg, ${accentColor.value}22, #6366f122)`
  if (mode === 'custom_per_skill' && typeof skill === 'object' && skill?.color) {
    return `${skill.color}18`
  }
  return skillBgPalette[idx % skillBgPalette.length]
}

function getSkillColor(idx, skill) {
  const mode = skillsDesign.value.color_mode
  if (mode === 'accent') return accentColor.value
  if (mode === 'custom_per_skill' && typeof skill === 'object' && skill?.color) {
    return skill.color
  }
  return skillColorPalette[idx % skillColorPalette.length]
}

function getSkillPercent(skill) {
  if (typeof skill === 'object' && skill !== null) {
    if (skill.percent !== undefined && skill.percent !== null && skill.percent !== '') {
      const p = Number(skill.percent)
      if (!isNaN(p) && p >= 0 && p <= 100) return p
    }
    if (skill.level) {
      const l = skill.level.toLowerCase()
      if (l.includes('expert')) return 95
      if (l.includes('avancé') || l.includes('avance')) return 80
      if (l.includes('intermédiaire') || l.includes('intermediaire')) return 60
      return 40
    }
  }
  return 85
}

// Ambient particle positioning
function getSnowflakeStyle(n) {
  const left = (n * 2.85) % 100
  const duration = 4 + (n % 6) * 1.5
  const delay = (n % 10) * 0.4
  const size = 12 + (n % 14)
  const opacity = 0.4 + (n % 5) * 0.12
  return {
    left: `${left}%`,
    animationDuration: `${duration}s`,
    animationDelay: `${delay}s`,
    fontSize: `${size}px`,
    opacity: opacity
  }
}

function getConfettiStyle(n) {
  const left = (n * 3.3) % 100
  const colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899']
  const duration = 3 + (n % 5) * 1.2
  const delay = (n % 8) * 0.3
  return {
    left: `${left}%`,
    backgroundColor: colors[n % colors.length],
    animationDuration: `${duration}s`,
    animationDelay: `${delay}s`
  }
}

function getStarStyle(n) {
  const left = (n * 4.1) % 96 + 2
  const top = (n * 7.3) % 90 + 5
  const duration = 1.5 + (n % 4) * 0.8
  const delay = (n % 6) * 0.3
  return {
    left: `${left}%`,
    top: `${top}%`,
    animationDuration: `${duration}s`,
    animationDelay: `${delay}s`
  }
}

function getMatrixStyle(n) {
  const left = (n * 5.5) % 98 + 1
  const duration = 2.5 + (n % 4) * 1.2
  const delay = (n % 5) * 0.5
  return {
    left: `${left}%`,
    animationDuration: `${duration}s`,
    animationDelay: `${delay}s`
  }
}

function getBubbleStyle(n) {
  const left = (n * 4.9) % 96 + 2
  const size = 14 + (n % 24)
  const duration = 5 + (n % 6) * 1.5
  const delay = (n % 7) * 0.6
  return {
    left: `${left}%`,
    width: `${size}px`,
    height: `${size}px`,
    animationDuration: `${duration}s`,
    animationDelay: `${delay}s`
  }
}

let slideshowInterval = null
let testimonialsInterval = null

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  window.addEventListener('keydown', handleCommandKeydown)
  handleScroll()

  if (preloaderConfig.value?.enabled) {
    showPreloader.value = true
    const duration = Math.max(0.5, Math.min(5, Number(preloaderConfig.value?.duration || 1.2))) * 1000
    setTimeout(() => {
      showPreloader.value = false
    }, duration)
  }

  // Slideshow autoplay
  if (isSlideshowVisible.value && slideshowConfig.value.autoplay !== false && slideshowImages.value.length > 1) {
    const speed = (Number(slideshowConfig.value.interval || slideshowConfig.value.speed) || 4) * 1000
    slideshowInterval = setInterval(() => {
      nextSlide()
    }, speed)
  }

  // Testimonials slider autoplay
  if (testimonialsDesign.value.display_mode === 'slider' && testimonialsDesign.value.autoplay !== false && testimonialsList.value.length > 1) {
    const tSpeed = (Number(testimonialsDesign.value.speed) || 5) * 1000
    testimonialsInterval = setInterval(() => {
      nextTestimonialSlide()
    }, tSpeed)
  }

  if (typewriterEnabled.value) {
    runTypewriterStep()
  }

  if (hasGithubStats.value) {
    fetchGithubLiveStats()
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('keydown', handleCommandKeydown)
  if (slideshowInterval) {
    clearInterval(slideshowInterval)
  }
  if (testimonialsInterval) {
    clearInterval(testimonialsInterval)
  }
  if (typewriterTimer) {
    clearTimeout(typewriterTimer)
  }
})
</script>

<style scoped>
/* ==========================================================================
   PORTFOLIO ROOT & THEMES
   ========================================================================== */
.portfolio-root {
  min-height: 100vh;
  transition: background 0.3s ease, color 0.3s ease;
}

/* 9 VISUAL PALETTES */
.theme-emerald {
  background: linear-gradient(180deg, #F0FDF4 0%, #FFFFFF 100%);
  color: #0F172A;
}
.theme-dark {
  background: #0B0F19;
  color: #E2E8F0;
}
.theme-dark .portfolio-nav {
  background: rgba(11, 15, 25, 0.85);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.theme-dark .nav-brand-text { color: #FFFFFF; }
.theme-dark .content-card,
.theme-dark .hero-box {
  background-color: #111827;
  color: #E2E8F0;
  border-color: rgba(255, 255, 255, 0.08) !important;
}
.theme-dark .user-name-heading { color: #FFFFFF; }
.theme-dark .text-muted { color: #94A3B8 !important; }

.theme-navy {
  background: linear-gradient(180deg, #0A192F 0%, #020C1B 100%);
  color: #CCD6F6;
}
.theme-navy .content-card,
.theme-navy .hero-box {
  background-color: #112240;
  color: #CCD6F6;
  border-color: rgba(255, 255, 255, 0.05) !important;
}
.theme-navy .user-name-heading { color: #E6F1FF; }

.theme-sunset {
  background: linear-gradient(180deg, #FFF7ED 0%, #FFF1F2 100%);
  color: #1C1917;
}
.theme-lavender {
  background: linear-gradient(180deg, #FAF5FF 0%, #FFFFFF 100%);
  color: #1E1B4B;
}
.theme-cyber {
  background: #050505;
  color: #00FF66;
}
.theme-cyber .content-card,
.theme-cyber .hero-box {
  background-color: #0d1117;
  border-color: #00FF66 !important;
  box-shadow: 0 0 15px rgba(0, 255, 102, 0.15);
}
.theme-minimal {
  background: #FFFFFF;
  color: #171717;
}
.theme-retro {
  background: #FEF3C7;
  color: #78350F;
}
.theme-synthwave {
  background: linear-gradient(180deg, #2D1B69 0%, #110B29 100%);
  color: #F8FAFC;
}
.theme-synthwave .content-card,
.theme-synthwave .hero-box {
  background-color: rgba(30, 20, 60, 0.75);
  border-color: rgba(236, 72, 153, 0.3) !important;
}

/* ==========================================================================
   EXPANDED TYPOGRAPHY FONTS (Montserrat, Roboto, Jakarta, etc.)
   ========================================================================== */
.font-poppins { font-family: 'Poppins', sans-serif; }
.font-montserrat { font-family: 'Montserrat', sans-serif; }
.font-inter { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
.font-roboto { font-family: 'Roboto', sans-serif; }
.font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
.font-outfit { font-family: 'Outfit', sans-serif; }
.font-raleway { font-family: 'Raleway', sans-serif; }
.font-fira, .font-firacode { font-family: 'Fira Code', monospace; }
.font-mono { font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace; }
.font-space { font-family: 'Space Grotesk', sans-serif; }
.font-playfair { font-family: 'Playfair Display', Georgia, serif; }
.font-cinzel { font-family: 'Cinzel', serif; }
.font-lexend { font-family: 'Lexend', sans-serif; }

/* BACKGROUND PATTERNS */
.bg-pattern-grid {
  background-image: radial-gradient(rgba(0, 0, 0, 0.08) 1px, transparent 1px);
  background-size: 24px 24px;
}
.bg-pattern-dots {
  background-image: radial-gradient(rgba(0, 0, 0, 0.12) 1.5px, transparent 1.5px);
  background-size: 16px 16px;
}
.bg-pattern-diagonal {
  background-image: repeating-linear-gradient(45deg, rgba(0, 0, 0, 0.03) 0, rgba(0, 0, 0, 0.03) 1px, transparent 0, transparent 20px);
}
.bg-pattern-circuit {
  background-image: linear-gradient(rgba(0, 168, 89, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(0, 168, 89, 0.05) 1px, transparent 1px);
  background-size: 32px 32px;
}

/* ==========================================================================
   30 GLOBAL STRUCTURE ARCHITECTURES
   ========================================================================== */
.structure-brutalist .content-card,
.structure-brutalist .hero-box {
  border: 3px solid #000000 !important;
  box-shadow: 6px 6px 0px #000000 !important;
  border-radius: 0px !important;
}

.structure-glassmorphism .content-card,
.structure-glassmorphism .hero-box {
  background: rgba(255, 255, 255, 0.55) !important;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.4) !important;
}

.structure-neumorphism .content-card,
.structure-neumorphism .hero-box {
  background: #f0f3f8;
  border-radius: 20px;
  box-shadow: 8px 8px 16px #d1d9e6, -8px -8px 16px #ffffff;
}

.structure-cyberpunk .content-card,
.structure-cyberpunk .hero-box {
  border: 1px solid #10b981 !important;
  box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
}

.structure-retro_90s .content-card,
.structure-retro_90s .hero-box {
  border-top: 3px solid #ffffff !important;
  border-left: 3px solid #ffffff !important;
  border-right: 3px solid #555555 !important;
  border-bottom: 3px solid #555555 !important;
  background-color: #c0c0c0;
  border-radius: 0 !important;
}

.structure-zen {
  padding-top: 2rem;
  padding-bottom: 4rem;
}
.structure-zen .content-card,
.structure-zen .hero-box {
  border: none !important;
  box-shadow: none !important;
  background: transparent !important;
}

.structure-smartphone {
  max-width: 440px;
  margin: 2rem auto;
  border: 12px solid #1e293b;
  border-radius: 40px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  background-color: #ffffff;
}

.structure-dashboard_saas .content-card {
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

/* CARD STYLES */
.content-card, .hero-box {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-style-rounded .content-card,
.card-style-rounded .hero-box {
  border-radius: 1.5rem !important;
}
.card-style-sharp .content-card,
.card-style-sharp .hero-box {
  border-radius: 4px !important;
}
.card-style-bordered .content-card,
.card-style-bordered .hero-box {
  border: 2px solid var(--accent-color) !important;
}
.card-style-glass .content-card,
.card-style-glass .hero-box {
  background: rgba(255, 255, 255, 0.6) !important;
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
}
.theme-dark .card-style-glass .content-card,
.theme-dark .card-style-glass .hero-box {
  background: rgba(17, 24, 39, 0.6) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
}
.card-style-terminal .content-card,
.card-style-terminal .hero-box {
  border-radius: 8px !important;
  border: 1px solid #10B981 !important;
  box-shadow: 0 0 15px rgba(16, 185, 129, 0.15);
}

/* HERO AVATARS & SHAPES */
.hero-avatar {
  width: 120px;
  height: 120px;
  object-fit: cover;
  font-size: 40px;
}
.hero-avatar-sq {
  width: 140px;
  height: 140px;
  object-fit: cover;
  font-size: 44px;
}
.avatar-shape-circle {
  border-radius: 50% !important;
}
.avatar-shape-squircle {
  border-radius: 28% !important;
}
.avatar-shape-hexagon {
  clip-path: polygon(25% 5%, 75% 5%, 100% 50%, 75% 95%, 25% 95%, 0% 50%);
  border-radius: 0 !important;
}
.avatar-shape-diamond {
  clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%);
  border-radius: 0 !important;
}
.avatar-border-glow {
  box-shadow: 0 0 0 4px #ffffff, 0 0 22px var(--accent-color) !important;
}
.avatar-border-gradient {
  border: 4px solid var(--accent-color) !important;
  box-shadow: 0 0 18px rgba(0, 168, 89, 0.35) !important;
}
.avatar-border-double {
  outline: 3px solid var(--accent-color);
  outline-offset: 3px;
}
.avatar-online-pulse-badge {
  position: absolute;
  bottom: 6px;
  right: 6px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background-color: #22C55E;
  border: 3px solid #FFFFFF;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.35);
  animation: pulseDot 2s infinite;
}

/* TYPEWRITER CURSOR */
.typewriter-cursor {
  display: inline-block;
  margin-left: 2px;
  font-weight: 700;
  animation: blinkCursor 0.8s infinite;
}

/* CUSTOM CURSOR & SPOTLIGHT */
.portfolio-custom-cursor {
  position: fixed;
  top: 0;
  left: 0;
  width: 32px;
  height: 32px;
  border: 2px solid var(--accent-color);
  border-radius: 50%;
  z-index: 9999;
  transition: transform 0.06s linear;
  box-shadow: 0 0 12px rgba(0, 168, 89, 0.25);
}

/* FLOATING WHATSAPP DIRECT BUBBLE */
.btn-floating-whatsapp {
  position: fixed;
  bottom: 25px;
  z-index: 1045;
  background-color: #25D366;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.btn-floating-whatsapp.pos-left {
  left: 25px;
}
.btn-floating-whatsapp.pos-right {
  right: 85px;
}
.btn-floating-whatsapp:hover {
  transform: translateY(-4px) scale(1.03);
  color: #FFFFFF;
}

/* ==========================================================================
   REAL WEBSITE NAVBAR, AGENCY HERO & INFINITE MARQUEE
   ========================================================================== */
.website-floating-pill-bar {
  background: rgba(255, 255, 255, 0.88);
  backdrop-filter: blur(16px);
}
.theme-dark .website-floating-pill-bar,
.theme-navy .website-floating-pill-bar,
.theme-cyber .website-floating-pill-bar,
.theme-synthwave .website-floating-pill-bar {
  background: rgba(17, 24, 39, 0.88);
  border-color: rgba(255, 255, 255, 0.12) !important;
}
.nav-style-full_sticky {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}
.website-nav-link {
  color: inherit;
  font-size: 13.5px;
}
.website-nav-link:hover:not(.active) {
  background-color: rgba(0, 0, 0, 0.05);
}
.theme-dark .website-nav-link:hover:not(.active) {
  background-color: rgba(255, 255, 255, 0.08);
}

/* Agency Hero Visual & Floating Badges */
.agency-hero-avatar {
  width: 220px;
  height: 220px;
  object-fit: cover;
  font-size: 64px;
}
.agency-visual-backdrop {
  position: absolute;
  inset: 12px;
  border: 2px dashed;
  transform: rotate(-4deg);
  z-index: 0;
}
.agency-floating-badge {
  position: absolute;
  z-index: 3;
  animation: floatBadge 3.5s ease-in-out infinite alternate;
}
.agency-floating-badge.badge-top-right {
  top: 8px;
  right: -10px;
}
.agency-floating-badge.badge-bottom-left {
  bottom: 8px;
  left: -10px;
  animation-delay: 1.2s;
}
@keyframes floatBadge {
  0% { transform: translateY(0px); }
  100% { transform: translateY(-8px); }
}

/* Infinite Tech Marquee */
.tech-marquee-container {
  position: relative;
  white-space: nowrap;
}
.tech-marquee-track {
  display: inline-flex;
  animation: marqueeScroll 28s linear infinite;
}
.tech-marquee-container:hover .tech-marquee-track {
  animation-play-state: paused;
}
@keyframes marqueeScroll {
  0% { transform: translateX(0); }
  100% { transform: translateX(-33.33%); }
}

/* HERO GLOW ORB */
.hero-glow-orb {
  position: absolute;
  top: -50px;
  left: 50%;
  transform: translateX(-50%);
  width: 300px;
  height: 200px;
  background: radial-gradient(circle, var(--accent-color) 0%, transparent 70%);
  opacity: 0.18;
  filter: blur(40px);
  pointer-events: none;
}

/* AVAILABILITY PILLS */
.availability-pill {
  font-size: 12px;
  font-weight: 600;
}
.availability-open {
  background-color: #DCFCE7;
  color: #166534;
  border: 1px solid #BBF7D0;
}
.availability-busy {
  background-color: #F1F5F9;
  color: #475569;
  border: 1px solid #E2E8F0;
}
.status-pulse-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #22C55E;
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.3);
  animation: pulseDot 2s infinite;
}
@keyframes pulseDot {
  0% { transform: scale(0.95); opacity: 0.8; }
  50% { transform: scale(1.2); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.8; }
}

.meta-pill {
  background-color: rgba(0, 0, 0, 0.04);
  color: #475569;
  border: 1px solid rgba(0, 0, 0, 0.06);
  font-size: 12px;
  font-weight: 500;
}
.theme-dark .meta-pill {
  background-color: rgba(255, 255, 255, 0.05);
  color: #94A3B8;
  border-color: rgba(255, 255, 255, 0.08);
}

/* TIMELINE */
.timeline-dot {
  width: 14px;
  height: 14px;
  border: 3px solid #FFFFFF;
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
}

/* TRANSITIONS */
.transition-hover {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
}

/* ==========================================================================
   TABS CUSTOMIZATION
   ========================================================================== */
.tab-size-sm { font-size: 12px; padding: 4px 10px; }
.tab-size-md { font-size: 14px; padding: 6px 16px; }
.tab-size-lg { font-size: 16px; padding: 8px 22px; font-weight: 700; }

.tab-radius-full { border-radius: 9999px !important; }
.tab-radius-rounded { border-radius: 8px !important; }
.tab-radius-sharp { border-radius: 0px !important; }

.tabs-style-underline .tab-nav-btn {
  background: transparent !important;
  border: none !important;
  border-bottom: 2px solid transparent !important;
  border-radius: 0 !important;
  color: #64748b;
}
.tabs-style-underline .tab-nav-btn.btn-primary {
  border-bottom-color: var(--accent-color) !important;
  color: var(--accent-color) !important;
}

/* ==========================================================================
   FLOATING BACK TO TOP BUTTON
   ========================================================================== */
.btn-floating-back-to-top {
  position: fixed;
  bottom: 25px;
  right: 25px;
  width: 48px;
  height: 48px;
  z-index: 1040;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.btn-floating-back-to-top:hover {
  transform: translateY(-4px) scale(1.05);
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35);
}

/* ==========================================================================
   AMBIENT PARTICLE EFFECTS (Snow, Confetti, Stars, Matrix, Bubbles)
   ========================================================================== */
.ambient-effects-layer {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 99;
  overflow: hidden;
}

/* SNOW */
.snowflake {
  position: fixed;
  top: -20px;
  color: #FFFFFF;
  text-shadow: 0 0 8px rgba(14, 165, 233, 0.8), 0 0 2px rgba(0, 0, 0, 0.2);
  user-select: none;
  animation: snowFall linear infinite;
}
@keyframes snowFall {
  0% { transform: translateY(-20px) rotate(0deg); }
  50% { transform: translateY(50vh) translateX(25px) rotate(180deg); }
  100% { transform: translateY(105vh) translateX(-20px) rotate(360deg); }
}

/* CONFETTI */
.confetti-piece {
  position: fixed;
  top: -15px;
  width: 10px;
  height: 14px;
  border-radius: 2px;
  opacity: 0.85;
  animation: confettiFall linear infinite;
}
@keyframes confettiFall {
  0% { transform: translateY(-15px) rotate(0deg); }
  100% { transform: translateY(105vh) rotate(720deg); }
}

/* STARS */
.ambient-star {
  position: fixed;
  color: #F59E0B;
  font-size: 16px;
  animation: starTwinkle ease-in-out infinite alternate;
}
@keyframes starTwinkle {
  0% { transform: scale(0.5); opacity: 0.2; }
  100% { transform: scale(1.3); opacity: 0.9; text-shadow: 0 0 10px #F59E0B; }
}

/* MATRIX RAIN */
.matrix-column {
  position: fixed;
  top: -50px;
  color: #10B981;
  font-family: monospace;
  font-size: 14px;
  writing-mode: vertical-rl;
  text-shadow: 0 0 8px #10B981;
  animation: matrixDrop linear infinite;
}
@keyframes matrixDrop {
  0% { transform: translateY(-50px); opacity: 0; }
  20% { opacity: 0.9; }
  80% { opacity: 0.9; }
  100% { transform: translateY(105vh); opacity: 0; }
}

/* BUBBLES */
.ambient-bubble {
  position: fixed;
  bottom: -40px;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.8), rgba(56, 189, 248, 0.2));
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
  animation: bubbleRise linear infinite;
}
@keyframes bubbleRise {
  0% { transform: translateY(0) scale(0.8); opacity: 0; }
  10% { opacity: 0.7; }
  90% { opacity: 0.7; }
  100% { transform: translateY(-105vh) scale(1.2); opacity: 0; }
}

/* ==========================================================================
   PRELOADER SPLASH SCREEN
   ========================================================================== */
.preloader-overlay {
  z-index: 99999;
  background: rgba(255, 255, 255, 0.96);
  backdrop-filter: blur(8px);
}
.theme-dark .preloader-overlay {
  background: rgba(11, 15, 25, 0.96);
}
.preloader-fade-leave-active {
  transition: opacity 0.4s ease;
}
.preloader-fade-leave-to {
  opacity: 0;
}
.preloader-pulse-logo {
  animation: pulseLogo 1.6s infinite ease-in-out;
}
.preloader-custom-img {
  animation: pulseLogo 1.6s infinite ease-in-out;
}
@keyframes pulseLogo {
  0%, 100% { transform: scale(1); filter: drop-shadow(0 0 0 rgba(0, 168, 89, 0)); }
  50% { transform: scale(1.08); filter: drop-shadow(0 0 12px rgba(0, 168, 89, 0.4)); }
}
.bounce-dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  display: inline-block;
  animation: bounceDot 1.4s infinite ease-in-out both;
}
.dot-1 { animation-delay: -0.32s; }
.dot-2 { animation-delay: -0.16s; }
@keyframes bounceDot {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1.0); }
}
.blink-cursor {
  animation: blinkCursor 0.8s infinite;
}
@keyframes blinkCursor {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
}

/* ==========================================================================
   PAGE ENTRY ANIMATIONS
   ========================================================================== */
.page-anim-fade {
  animation: pageFade 0.6s ease-out;
}
@keyframes pageFade {
  from { opacity: 0; }
  to { opacity: 1; }
}

.page-anim-slide_up {
  animation: pageSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes pageSlideUp {
  from { opacity: 0; transform: translateY(24px); }
  to { opacity: 1; transform: translateY(0); }
}

.page-anim-zoom {
  animation: pageZoom 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes pageZoom {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}

.page-anim-bounce {
  animation: pageBounce 0.7s cubic-bezier(0.28, 0.84, 0.42, 1);
}
@keyframes pageBounce {
  0% { opacity: 0; transform: translateY(-20px); }
  60% { opacity: 1; transform: translateY(8px); }
  100% { opacity: 1; transform: translateY(0); }
}

.page-anim-flip {
  animation: pageFlip 0.6s cubic-bezier(0.16, 1, 0.3, 1);
  perspective: 1000px;
}
@keyframes pageFlip {
  from { opacity: 0; transform: rotateX(10deg); }
  to { opacity: 1; transform: rotateX(0deg); }
}

/* ==========================================================================
   HAMBURGER LATERAL DRAWER, SECTION HOVER EFFECTS & CUSTOM LAYOUTS
   ========================================================================== */
.portfolio-offcanvas-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(4px);
  z-index: 2050;
  animation: pageFade 0.25s ease-out;
}

.portfolio-offcanvas-drawer {
  position: fixed;
  top: 0;
  bottom: 0;
  width: min(340px, 86vw);
  background: #ffffff;
  color: #0f172a;
  z-index: 2060;
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  overflow-y: auto;
}
.portfolio-offcanvas-drawer.drawer-side-left {
  left: 0;
  animation: slideDrawerLeft 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.portfolio-offcanvas-drawer.drawer-side-right {
  right: 0;
  animation: slideDrawerRight 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.portfolio-offcanvas-drawer.drawer-side-fullscreen {
  left: 0;
  right: 0;
  width: 100vw;
  max-width: 100vw;
  animation: pageZoom 0.28s ease-out;
}
.theme-dark .portfolio-offcanvas-drawer,
.theme-cyberpunk .portfolio-offcanvas-drawer,
.theme-emerald-night .portfolio-offcanvas-drawer {
  background: #111827;
  color: #f8fafc;
}
.theme-dark .portfolio-offcanvas-drawer .bg-light,
.theme-cyberpunk .portfolio-offcanvas-drawer .bg-light,
.theme-emerald-night .portfolio-offcanvas-drawer .bg-light {
  background-color: rgba(255, 255, 255, 0.06) !important;
  color: #f8fafc !important;
}

@keyframes slideDrawerLeft {
  from { transform: translateX(-100%); }
  to { transform: translateX(0); }
}
@keyframes slideDrawerRight {
  from { transform: translateX(100%); }
  to { transform: translateX(0); }
}

.about-two-columns {
  column-count: 1;
  column-gap: 2.2rem;
}
@media (min-width: 768px) {
  .about-two-columns {
    column-count: 2;
  }
}

.text-justify {
  text-align: justify;
}

/* Skill Hover & Animation Effects */
.skill-hover-lift,
.exp-hover-lift,
.elem-anim-lift {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.skill-hover-lift:hover,
.exp-hover-lift:hover,
.elem-anim-lift:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -8px rgba(15, 23, 42, 0.14) !important;
}

.skill-hover-glow,
.exp-hover-glow,
.elem-anim-glow {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.skill-hover-glow:hover,
.exp-hover-glow:hover,
.elem-anim-glow:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 18px rgba(16, 185, 129, 0.35) !important;
}

.skill-hover-scale,
.elem-anim-scale {
  transition: transform 0.25s ease;
}
.skill-hover-scale:hover,
.elem-anim-scale:hover {
  transform: scale(1.04);
}

.skill-hover-border_pulse,
.exp-hover-border_slide {
  transition: border-color 0.25s ease, transform 0.25s ease;
}
.skill-hover-border_pulse:hover,
.exp-hover-border_slide:hover {
  border-color: var(--accent-color, #00A859) !important;
  transform: translateX(4px);
}

.skill-anim-pulse,
.elem-anim-pulse:hover {
  animation: pulseLogo 2.2s infinite ease-in-out;
}

/* Project Hover Effects */
.project-hover-lift_shadow {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.project-hover-lift_shadow:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 34px -10px rgba(15, 23, 42, 0.18) !important;
}

.project-hover-glow_border {
  transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}
.project-hover-glow_border:hover {
  transform: translateY(-3px);
  border-color: var(--accent-color, #00A859) !important;
  box-shadow: 0 0 24px rgba(0, 168, 89, 0.25) !important;
}

.project-hover-zoom_visual img {
  transition: transform 0.45s ease;
}
.project-hover-zoom_visual:hover img {
  transform: scale(1.07);
}

.project-hover-tilt_3d {
  transition: transform 0.35s ease, box-shadow 0.35s ease;
}
.project-hover-tilt_3d:hover {
  transform: perspective(900px) rotateX(2deg) rotateY(-2deg) translateY(-4px);
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.16) !important;
}

.project-hover-slide_accent {
  transition: transform 0.25s ease, border-left 0.25s ease;
}
.project-hover-slide_accent:hover {
  transform: translateX(6px);
  border-left: 5px solid var(--accent-color, #00A859) !important;
}

/* ==========================================================================
   MACOS BOTTOM DOCK TAB BAR, FLOATING ORBS & DESIGN EFFECTS STUDIO
   ========================================================================== */
.tabbed-bar-bottom-dock {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  max-width: 92vw;
  width: max-content;
  background: rgba(255, 255, 255, 0.88);
  backdrop-filter: blur(16px);
  z-index: 1040;
  padding: 8px 14px !important;
}
.theme-dark .tabbed-bar-bottom-dock,
.theme-synthwave .tabbed-bar-bottom-dock {
  background: rgba(15, 23, 42, 0.88);
  border-color: rgba(255, 255, 255, 0.14) !important;
}

.tabs-style-glass_dock {
  background: rgba(255, 255, 255, 0.65);
  backdrop-filter: blur(18px);
}
.tabs-style-neon_glow .tab-nav-btn {
  box-shadow: 0 0 12px rgba(0, 168, 89, 0.18);
}

/* Floating Mesh Orbs */
.portfolio-floating-orbs {
  position: fixed;
  inset: 0;
  overflow: hidden;
  z-index: 0;
}
.mesh-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(90px);
  opacity: 0.16;
  animation: floatOrb 14s infinite alternate ease-in-out;
}
.mesh-orb.orb-1 {
  width: 380px;
  height: 380px;
  top: -80px;
  left: -80px;
}
.mesh-orb.orb-2 {
  width: 440px;
  height: 440px;
  top: 40%;
  right: -120px;
  background: #6366F1;
  animation-delay: -5s;
}
.mesh-orb.orb-3 {
  width: 320px;
  height: 320px;
  bottom: -60px;
  left: 30%;
  animation-delay: -9s;
}
@keyframes floatOrb {
  0% { transform: translate(0, 0) scale(1); }
  100% { transform: translate(40px, 35px) scale(1.12); }
}

/* Cursor Glow Trail */
.cursor-glow-trail {
  width: 36px !important;
  height: 36px !important;
  background: radial-gradient(circle, var(--accent-color, #00A859) 0%, transparent 70%) !important;
  border: none !important;
  filter: blur(4px);
}

/* Section Dividers */
.section-divider-neon {
  height: 2px;
  width: 100%;
  background: linear-gradient(90deg, transparent 0%, var(--accent-color, #00A859) 50%, transparent 100%);
  box-shadow: 0 0 12px var(--accent-color, #00A859);
  opacity: 0.65;
}
.section-divider-diagonal {
  height: 16px;
  width: 100%;
  background-image: repeating-linear-gradient(
    -45deg,
    transparent,
    transparent 8px,
    rgba(0, 168, 89, 0.15) 8px,
    rgba(0, 168, 89, 0.15) 10px
  );
}

/* Global Heading Styles */
.heading-style-gradient_text .portfolio-block h2 {
  background: linear-gradient(90deg, var(--accent-color, #00A859), #3B82F6, #8B5CF6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.heading-style-neon_underline .portfolio-block h2 {
  position: relative;
  padding-bottom: 8px;
}
.heading-style-neon_underline .portfolio-block h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 56px;
  height: 4px;
  border-radius: 99px;
  background: var(--accent-color, #00A859);
  box-shadow: 0 0 10px var(--accent-color, #00A859);
}
.heading-style-pill_badge .portfolio-block h2 {
  display: inline-flex;
  padding: 6px 18px;
  border-radius: 999px;
  background: rgba(0, 168, 89, 0.08);
  border: 1px solid rgba(0, 168, 89, 0.22);
}

/* Global Button Styles */
.btn-style-glow_pill .btn {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.btn-style-glow_pill .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 18px rgba(0, 168, 89, 0.45) !important;
}
.btn-style-neo_brutalist .btn {
  border: 2px solid #0F172A !important;
  box-shadow: 4px 4px 0px #0F172A !important;
  border-radius: 10px !important;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.btn-style-neo_brutalist .btn:hover {
  transform: translate(-2px, -2px);
  box-shadow: 6px 6px 0px #0F172A !important;
}
.btn-style-glass_frost .btn {
  backdrop-filter: blur(10px);
}

/* Global Card Border Effects */
.card-border-top_accent_bar .content-card {
  border-top: 4px solid var(--accent-color, #00A859) !important;
}
.card-border-glow_hover .content-card {
  transition: box-shadow 0.3s ease, border-color 0.3s ease;
}
.card-border-glow_hover .content-card:hover {
  border-color: var(--accent-color, #00A859) !important;
  box-shadow: 0 0 26px rgba(0, 168, 89, 0.18) !important;
}
.card-border-neo_brutal .content-card,
.card-border-neo_brutal .hero-box {
  border: 2px solid #0F172A !important;
  box-shadow: 6px 6px 0px #0F172A !important;
}
.card-border-gradient_frame .content-card {
  border: 1px solid rgba(0, 168, 89, 0.28) !important;
  box-shadow: 0 10px 30px -10px rgba(0, 168, 89, 0.12) !important;
}

/* ==========================================================================
   PRINT / PDF EXPORT OPTIMIZATIONS
   ========================================================================== */
@media print {
  .portfolio-nav, .btn, .d-print-none, .ambient-effects-layer, .preloader-overlay, .portfolio-tabbed-bar, .portfolio-top-slideshow, .btn-floating-back-to-top, .portfolio-offcanvas-backdrop, .portfolio-offcanvas-drawer {
    display: none !important;
  }
  .portfolio-root {
    background: #FFFFFF !important;
    color: #000000 !important;
  }
  .content-card, .hero-box {
    box-shadow: none !important;
    border: 1px solid #E2E8F0 !important;
    break-inside: avoid;
    page-break-inside: avoid;
  }
  .print-compact .p-md-5 {
    padding: 1rem !important;
  }
  .print-compact .mb-4, .print-compact .mb-5 {
    margin-bottom: 0.75rem !important;
  }
}

/* Custom Color Palette Override */
.custom-palette-active .content-card {
  background-color: var(--custom-card-bg, #FFFFFF) !important;
  color: var(--custom-text-primary, #0F172A) !important;
}
.custom-palette-active .text-muted {
  color: var(--custom-text-muted, #64748B) !important;
}

/* Announcement Bar Marquee */
.announcement-marquee {
  display: inline-block;
  white-space: nowrap;
  animation: announceScroll 18s linear infinite;
}
@keyframes announceScroll {
  0% { transform: translateX(40%); }
  100% { transform: translateX(-100%); }
}

/* Command Palette & Lightbox Backdrop */
.command-palette-backdrop {
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(6px);
  z-index: 2050;
}

/* Before / After Range Slider */
.before-after-range {
  accent-color: var(--accent-color, #00A859);
  cursor: ew-resize;
}

/* Accordion Menu Transition */
.accordion-slide-enter-active,
.accordion-slide-leave-active {
  transition: all 0.24s cubic-bezier(0.16, 1, 0.3, 1);
}
.accordion-slide-enter-from,
.accordion-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* Visual Gallery Masonry Model */
.gallery-masonry-cols {
  column-count: 3;
  column-gap: 1rem;
}
@media (max-width: 991px) {
  .gallery-masonry-cols { column-count: 2; }
}
@media (max-width: 575px) {
  .gallery-masonry-cols { column-count: 1; }
}
.break-inside-avoid {
  break-inside: avoid;
}

/* Hero Models */
.hero-model-centered_saas .hero-box {
  text-align: center !important;
}
.hero-model-glass_dark .hero-box,
.block-model-dark_studio .content-card,
.block-model-dark_contrast .content-card {
  background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%) !important;
  color: #F8FAFC !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
.hero-model-glass_dark .text-muted,
.block-model-dark_studio .text-muted,
.block-model-dark_contrast .text-muted {
  color: #94A3B8 !important;
}
.hero-model-terminal_dev .hero-box,
.block-model-terminal_bio .content-card {
  background: #0B1120 !important;
  color: #34D399 !important;
  font-family: 'JetBrains Mono', monospace !important;
  border: 1px solid rgba(52, 211, 153, 0.3) !important;
}
.hero-model-minimal_typo .hero-box,
.block-model-minimal_clean .content-card,
.block-model-minimal_centered .content-card {
  background: transparent !important;
  box-shadow: none !important;
  border: 1px solid rgba(148, 163, 184, 0.25) !important;
}
.block-model-minimal_centered .content-card {
  text-align: center !important;
}
.hero-model-floating_card .hero-box,
.block-model-modern_cards .content-card {
  box-shadow: 0 22px 45px -12px rgba(15, 23, 42, 0.14) !important;
}
.block-model-glass_panel .content-card,
.block-model-glass_card .content-card {
  background: rgba(255, 255, 255, 0.72) !important;
  backdrop-filter: blur(14px) !important;
  border: 1px solid rgba(255, 255, 255, 0.45) !important;
}
.block-model-neo_brutal .content-card {
  border: 2px solid #0F172A !important;
  box-shadow: 6px 6px 0px #0F172A !important;
}
.block-model-accent_border .content-card {
  border-left: 5px solid var(--accent-color, #00A859) !important;
}
.block-model-gradient_soft .content-card,
.hero-model-cover_banner .hero-box {
  background: linear-gradient(135deg, rgba(0, 168, 89, 0.08) 0%, rgba(255, 255, 255, 0.96) 100%) !important;
}
</style>