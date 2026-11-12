<template>
  <div 
    class="portfolio-root min-vh-100 position-relative overflow-x-hidden" 
    :class="[
      'theme-' + currentTheme, 
      'card-style-' + currentCardStyle,
      'font-' + currentFont,
      'bg-pattern-' + currentPattern,
      'structure-' + currentStructure,
      customCursorEnabled ? 'custom-cursor-active' : ''
    ]" 
    :style="cssVars"
    @mousemove="handleMouseMove"
  >
    <!-- MOUSE SPOTLIGHT HALO -->
    <div 
      v-if="spotlightEnabled" 
      class="portfolio-spotlight-layer position-fixed top-0 start-0 w-100 h-100 pointer-events-none d-print-none"
      :style="spotlightStyle"
    ></div>

    <!-- CUSTOM TECH CURSOR RING (Desktop) -->
    <div 
      v-if="customCursorEnabled" 
      class="portfolio-custom-cursor pointer-events-none d-none d-lg-block d-print-none"
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

    <!-- TOP WEBSITE NAVBAR (Real Personal Website Header + Mobile Menu) -->
    <header 
      class="portfolio-nav sticky-top d-print-none position-relative z-20"
      :class="[
        websiteNavbar.enabled ? 'website-nav-mode nav-style-' + websiteNavbar.style : 'py-2 px-3 px-md-4 shadow-2xs backdrop-blur'
      ]"
    >
      <div class="container" :style="{ maxWidth: websiteNavbar.enabled ? '1320px' : undefined }">
        <div 
          class="d-flex align-items-center justify-content-between gap-2"
          :class="websiteNavbar.enabled && websiteNavbar.style === 'floating_pill' ? 'website-floating-pill-bar px-3 px-md-4 py-2 rounded-pill shadow-sm border my-2' : 'py-2'"
        >
          <!-- Left: Owner Brand Identity (or Tech237 fallback) -->
          <div class="d-flex align-items-center gap-2 min-w-0">
            <a 
              href="#top" 
              @click.prevent="navigateWebsitePage('home')" 
              class="d-flex align-items-center gap-2 text-decoration-none min-w-0"
            >
              <img 
                v-if="portfolioUser.avatar" 
                :src="formatImageUrl(portfolioUser.avatar)" 
                :alt="portfolioUser.name" 
                class="rounded-circle border object-fit-cover flex-shrink-0 shadow-2xs"
                style="width: 36px; height: 36px;"
              />
              <div 
                v-else 
                class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0 extra-small"
                style="width: 36px; height: 36px;"
                :style="{ backgroundColor: accentColor }"
              >
                {{ getInitials(portfolioUser.name) }}
              </div>
              <div class="min-w-0">
                <span class="fw-bold fs-6 nav-brand-text d-block text-truncate lh-sm">
                  {{ websiteNavbar.brand_name || portfolioUser.name }}
                </span>
                <small v-if="websiteNavbar.brand_subtitle" class="text-muted extra-small d-none d-sm-block text-truncate lh-1">
                  {{ websiteNavbar.brand_subtitle }}
                </small>
              </div>
            </a>
            <span class="badge rounded-pill px-2 py-1 extra-small fw-semibold border nav-verified-pill d-none d-xl-inline-flex align-items-center gap-1 ms-1" title="Portfolio certifié sur Tech237">
              <i class="bi bi-patch-check-fill text-success"></i>
              <span>Vérifié</span>
            </span>
          </div>

          <!-- Center: Real Website Navigation Links (Desktop) -->
          <nav v-if="websiteNavbar.enabled" class="d-none d-lg-flex align-items-center gap-1">
            <button
              v-for="item in websiteNavItems"
              :key="item.id"
              type="button"
              @click="navigateWebsitePage(item.id, item.anchor)"
              class="btn btn-sm rounded-pill px-3 py-1 fw-semibold transition d-inline-flex align-items-center gap-1 website-nav-link"
              :class="isWebsiteNavItemActive(item.id) ? 'active text-white shadow-2xs' : ''"
              :style="isWebsiteNavItemActive(item.id) ? { backgroundColor: accentColor } : {}"
            >
              <i class="bi" :class="item.icon"></i>
              <span>{{ item.label }}</span>
            </button>
          </nav>

          <!-- Right: Utility & Primary Website CTA -->
          <div class="d-flex align-items-center gap-2">
            <!-- Share Link Copy Button -->
            <button 
              type="button" 
              @click="copyPortfolioLink" 
              class="btn btn-sm btn-nav-action rounded-pill px-2 px-md-3 fw-semibold d-inline-flex align-items-center gap-1"
              title="Copier le lien du site"
            >
              <i class="bi" :class="isCopied ? 'bi-check-lg text-success' : 'bi-share'"></i>
              <span class="d-none d-xl-inline">{{ isCopied ? 'Copié !' : 'Partager' }}</span>
            </button>

            <!-- Digital Business Card (vCard + QR Code) Button -->
            <button 
              v-if="vcardEnabled" 
              type="button" 
              @click="openVcardModal" 
              class="btn btn-sm btn-nav-action rounded-pill px-2 px-md-3 fw-semibold d-inline-flex align-items-center gap-1"
              title="Carte de Visite Digitale (.vcf & QR Code)"
            >
              <i class="bi bi-qr-code-scan text-primary"></i>
              <span class="d-none d-xl-inline">vCard</span>
            </button>

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

            <!-- Primary Website CTA Button -->
            <button 
              v-if="websiteNavbar.enabled"
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

            <!-- Mobile Burger Menu Button -->
            <button
              v-if="websiteNavbar.enabled"
              type="button"
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="btn btn-sm btn-nav-action rounded-circle d-lg-none p-0 d-flex align-items-center justify-content-center"
              style="width: 36px; height: 36px;"
              aria-label="Menu"
            >
              <i class="bi fs-5" :class="mobileMenuOpen ? 'bi-x-lg' : 'bi-list'"></i>
            </button>
          </div>
        </div>

        <!-- Mobile Dropdown Navigation Drawer -->
        <div v-if="websiteNavbar.enabled && mobileMenuOpen" class="d-lg-none py-3 px-3 mb-2 rounded-4 border shadow-lg content-card">
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

    <!-- TOP HERO BANNER SLIDESHOW / CAROUSEL -->
    <div 
      v-if="isSlideshowVisible" 
      class="portfolio-top-slideshow position-relative overflow-hidden w-100 d-print-none shadow-sm"
      :style="{ height: slideshowHeight, minHeight: '220px' }"
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

    <!-- TABBED MULTI-PAGE NAVIGATION BAR (Bootstrap Icons, fully customizable) -->
    <div 
      v-if="isTabbedMode" 
      class="portfolio-tabbed-bar sticky-top py-2 px-3 d-print-none backdrop-blur border-bottom shadow-xs position-relative z-10" 
      style="top: 50px;"
      :class="['tabs-align-' + tabAlignment, 'tabs-style-' + tabStyle]"
    >
      <div class="container d-flex align-items-center flex-wrap gap-2" :class="getTabJustifyClass()">
        <button 
          v-for="t in availableTabs" 
          :key="t.id"
          type="button" 
          @click="activeTab = t.id" 
          class="btn transition d-inline-flex align-items-center gap-2 tab-nav-btn"
          :class="[
            getTabBtnClass(t.id),
            'tab-size-' + tabSize,
            'tab-radius-' + tabRadius
          ]"
          :style="activeTab === t.id ? { backgroundColor: accentColor, borderColor: accentColor, color: '#FFFFFF' } : {}"
        >
          <i class="bi" :class="t.iconClass"></i>
          <span>{{ t.label }}</span>
          <span v-if="t.count !== undefined" class="badge rounded-pill extra-small px-2" :class="activeTab === t.id ? 'bg-white text-dark' : 'bg-secondary-subtle text-secondary'">
            {{ t.count }}
          </span>
        </button>
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
              <div v-if="portfolioUser.skills && portfolioUser.skills.length" class="pt-3 border-top mb-3">
                <h3 class="h6 fw-bold mb-2">Compétences clés</h3>
                <div class="d-flex flex-wrap gap-1">
                  <span 
                    v-for="(sk, skIdx) in portfolioUser.skills" 
                    :key="skIdx"
                    class="badge rounded-pill px-2 py-1 extra-small fw-semibold shadow-2xs"
                    :style="{ backgroundColor: getSkillBg(skIdx), color: getSkillColor(skIdx) }"
                  >
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

        <!-- Bento Grid or Single Stream -->
        <div :class="isBentoGrid ? 'row g-4' : 'd-flex flex-column gap-4'">
          
          <template v-for="block in orderedBlocks" :key="block.id">
            
            <div 
              v-if="block.enabled && shouldRenderBlock(block.id) && isBlockActiveInTab(block.id)" 
              :id="'block-' + block.id"
              :class="getBlockGridClass(block)"
            >
              <!-- 1. HERO / HEADER BLOCK -->
              <section v-if="block.id === 'hero'" class="portfolio-block h-100">
                
                <!-- Variant: Agency Landing 2-Columns (Real Website Hero) -->
                <div v-if="heroStyle === 'agency_landing'" class="hero-box hero-agency-landing p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden h-100">
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
                    <div class="card border-0 shadow-sm rounded-4 p-3 text-center content-card h-100 transition-hover">
                      <div class="fs-3 fw-black mb-1" :style="{ color: accentColor }">{{ kpi.value }}</div>
                      <div class="extra-small fw-semibold text-muted">{{ kpi.label }}</div>
                    </div>
                  </div>
                </div>

                <!-- INFINITE TECH MARQUEE RIBBON -->
                <div v-if="techMarqueeEnabled && portfolioUser.skills && portfolioUser.skills.length" class="tech-marquee-container rounded-4 border shadow-2xs mt-3 py-2 px-3 overflow-hidden content-card d-print-none">
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

              <!-- 2. ABOUT / BIO BLOCK -->
              <section v-else-if="block.id === 'about' && portfolioUser.bio" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="section-icon-badge" :style="{ color: accentColor }">
                      <i class="bi bi-person-lines-fill fs-5"></i>
                    </span>
                    <h2 class="h5 fw-bold mb-0 section-heading">À propos de moi</h2>
                  </div>
                  <div class="text-content leading-relaxed mb-0" v-html="formatHtml(portfolioUser.bio)"></div>
                </div>
              </section>

              <!-- YOUTUBE VIDEO SHOWCASE BLOCK -->
              <section v-else-if="block.id === 'video' && youtubeEmbedUrl" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="section-icon-badge text-danger">
                      <i class="bi bi-play-btn-fill fs-5"></i>
                    </span>
                    <h2 class="h5 fw-bold mb-0 section-heading">Vidéo de Présentation &amp; Pitch</h2>
                  </div>
                  <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-xs border">
                    <iframe :src="youtubeEmbedUrl" title="Vidéo pitch portfolio" allowfullscreen></iframe>
                  </div>
                </div>
              </section>

              <!-- 3. SKILLS & STACK BLOCK -->
              <section v-else-if="block.id === 'skills' && (portfolioUser.skills && portfolioUser.skills.length)" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                      <span class="section-icon-badge" :style="{ color: accentColor }">
                        <i class="bi bi-cpu-fill fs-5"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">Compétences &amp; Stack Technique</h2>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill extra-small">
                      {{ portfolioUser.skills.length }} compétence(s)
                    </span>
                  </div>

                  <!-- Chips Variant with Levels -->
                  <div v-if="skillsStyle === 'chips' || !skillsStyle" class="d-flex flex-wrap gap-2">
                    <span 
                      v-for="(skill, sIdx) in portfolioUser.skills" 
                      :key="sIdx"
                      class="skill-chip badge rounded-pill px-3 py-2 fw-semibold fs-6 shadow-2xs d-inline-flex align-items-center gap-1"
                      :style="{ backgroundColor: getSkillBg(sIdx), color: getSkillColor(sIdx) }"
                    >
                      <span>{{ typeof skill === 'object' ? skill.name : skill }}</span>
                      <span 
                        v-if="typeof skill === 'object' && skill.level" 
                        class="badge rounded-pill px-2 py-0 extra-small fw-bold ms-1"
                        :class="getSkillLevelBadgeClass(skill.level)"
                      >
                        {{ skill.level }}
                      </span>
                    </span>
                  </div>

                  <!-- Bars Variant with Levels -->
                  <div v-else-if="skillsStyle === 'bars'" class="row g-3">
                    <div v-for="(skill, sIdx) in portfolioUser.skills" :key="sIdx" class="col-md-6">
                      <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>{{ typeof skill === 'object' ? skill.name : skill }}</span>
                        <span 
                          class="badge rounded-pill px-2 py-0 extra-small fw-bold"
                          :class="getSkillLevelBadgeClass(typeof skill === 'object' ? skill.level : 'Avancé')"
                        >
                          {{ typeof skill === 'object' && skill.level ? skill.level : 'Avancé' }}
                        </span>
                      </div>
                      <div class="progress rounded-pill" style="height: 8px;">
                        <div class="progress-bar rounded-pill" :style="{ width: getSkillPercent(skill) + '%', backgroundColor: accentColor }"></div>
                      </div>
                    </div>
                  </div>

                  <!-- Categorized / 3D Cards Variant with Levels -->
                  <div v-else class="row g-3">
                    <div v-for="(skill, sIdx) in portfolioUser.skills" :key="sIdx" class="col-6 col-md-4">
                      <div class="p-3 rounded-4 text-center border skill-grid-box h-100 d-flex flex-column justify-content-between">
                        <div>
                          <strong class="d-block text-dark small mb-1">{{ typeof skill === 'object' ? skill.name : skill }}</strong>
                          <span class="extra-small text-muted d-block mb-2">{{ typeof skill === 'object' && skill.category ? skill.category : 'Tech' }}</span>
                        </div>
                        <div>
                          <span 
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

              <!-- 4. PROJECTS BLOCK (Direct logo, multi-columns or interactive carousel) -->
              <section v-else-if="block.id === 'projects' && projects.length" class="portfolio-block h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div class="d-flex align-items-center gap-2">
                    <span class="section-icon-badge" :style="{ color: accentColor }">
                      <i class="bi bi-rocket-takeoff-fill fs-5"></i>
                    </span>
                    <h2 class="h5 fw-bold mb-0 section-heading">Projets &amp; Réalisations</h2>
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
                    class="card border-0 rounded-4 shadow-sm content-card overflow-hidden p-4 p-md-5 transition-hover"
                  >
                    <div class="row align-items-center g-4" :class="zIdx % 2 === 1 ? 'flex-md-row-reverse' : ''">
                      <!-- Visual / Showcase Column -->
                      <div class="col-md-5">
                        <div 
                          class="p-4 rounded-4 d-flex flex-column align-items-center justify-content-center text-center position-relative overflow-hidden border"
                          style="min-height: 210px;"
                          :style="{ background: `linear-gradient(135deg, ${accentColor}15 0%, ${accentColor}05 100%)` }"
                        >
                          <img 
                            v-if="proj.logo" 
                            :src="formatImageUrl(proj.logo)" 
                            :alt="proj.name"
                            class="rounded-4 shadow-sm border bg-white p-2 mb-3"
                            style="width: 88px; height: 88px; object-fit: contain;"
                          />
                          <div 
                            v-else 
                            class="rounded-4 text-white fw-bold d-flex align-items-center justify-content-center shadow-sm mb-3"
                            style="width: 88px; height: 88px; font-size: 32px;"
                            :style="{ backgroundColor: accentColor }"
                          >
                            <i class="bi bi-rocket-takeoff"></i>
                          </div>
                          <span class="badge rounded-pill bg-white text-dark border px-3 py-1 extra-small fw-bold shadow-2xs">
                            {{ proj.category || 'Solution Digitale' }}
                          </span>
                        </div>
                      </div>

                      <!-- Project Case Study Details Column -->
                      <div class="col-md-7">
                        <div class="d-flex align-items-center gap-2 mb-2">
                          <span class="badge rounded-pill text-white extra-small px-3 py-1" :style="{ backgroundColor: accentColor }">
                            Projet #{{ zIdx + 1 }}
                          </span>
                          <span v-if="proj.views_count" class="extra-small text-muted">
                            <i class="bi bi-eye me-1"></i>{{ proj.views_count }} vues
                          </span>
                        </div>
                        <h3 class="h4 fw-bold mb-2 text-dark">{{ proj.name }}</h3>
                        <div class="text-muted mb-4 leading-relaxed" v-html="formatHtml(proj.pitch || proj.description || 'Projet innovant développé et propulsé sur Tech237.')"></div>

                        <div class="d-flex flex-wrap align-items-center gap-2 d-print-none">
                          <button 
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
                            Fiche complète &rarr;
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- CAROUSEL / SLIDER VARIANT -->
                <div v-else-if="projectsStyle === 'carousel'" class="carousel-project-container card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
                  <div v-if="projects[currentProjectSlide]" class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                      <div class="d-flex align-items-center gap-3">
                        <img 
                          v-if="projects[currentProjectSlide].logo" 
                          :src="formatImageUrl(projects[currentProjectSlide].logo)" 
                          class="rounded-3 border flex-shrink-0" 
                          style="width: 56px; height: 56px; object-fit: contain;" 
                          :alt="projects[currentProjectSlide].name"
                        />
                        <div v-else class="rounded-3 text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;" :style="{ backgroundColor: accentColor }">
                          <i class="bi bi-rocket-takeoff fs-4"></i>
                        </div>
                        <div class="min-w-0">
                          <h3 class="h5 fw-bold mb-0 text-dark">{{ projects[currentProjectSlide].name }}</h3>
                          <span class="badge bg-secondary-subtle text-secondary rounded-pill extra-small">{{ projects[currentProjectSlide].category || 'Tech & Web' }}</span>
                        </div>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-muted border extra-small">{{ currentProjectSlide + 1 }} / {{ projects.length }}</span>
                      </div>
                    </div>
                    
                    <div class="text-content leading-relaxed my-2" v-html="formatHtml(projects[currentProjectSlide].pitch || projects[currentProjectSlide].description || 'Projet innovant propulsé sur Tech237.')"></div>
                    
                    <div class="d-flex align-items-center justify-content-between pt-3 border-top flex-wrap gap-2">
                      <div class="d-flex align-items-center gap-3 extra-small text-muted">
                        <span v-if="projects[currentProjectSlide].views_count"><i class="bi bi-eye me-1"></i>{{ projects[currentProjectSlide].views_count }} vues</span>
                        <span v-if="projects[currentProjectSlide].upvotes_count"><i class="bi bi-heart-fill text-danger me-1"></i>{{ projects[currentProjectSlide].upvotes_count }} upvotes</span>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <button type="button" @click="prevProject" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                          <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" @click="nextProject" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                          <i class="bi bi-chevron-right"></i>
                        </button>
                        <a :href="'/projet/' + (projects[currentProjectSlide].slug || projects[currentProjectSlide].id)" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold extra-small">
                          Consulter la fiche &rarr;
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
                <div v-else-if="projectsStyle === 'timeline'" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
                  <div class="timeline-stream d-flex flex-column gap-4">
                    <div v-for="proj in projects" :key="proj.id" class="d-flex gap-3 position-relative">
                      <div class="timeline-dot rounded-circle flex-shrink-0 mt-1" :style="{ backgroundColor: accentColor }"></div>
                      <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                          <h3 class="h6 fw-bold text-dark mb-0">{{ proj.name }}</h3>
                          <span class="badge bg-light text-muted border extra-small">{{ proj.category || 'Web / Mobile' }}</span>
                        </div>
                        <div class="small text-muted mt-1 mb-2 leading-relaxed" v-html="formatHtml(proj.pitch || proj.description)"></div>
                        <a :href="'/projet/' + (proj.slug || proj.id)" target="_blank" class="btn btn-xs btn-link p-0 text-decoration-none fw-bold" :style="{ color: accentColor }">
                          Découvrir ce projet sur Tech237 &rarr;
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- MULTI-COLUMN CARDS GRID (1, 2, 3 or 4 columns) -->
                <div v-else class="row g-4">
                  <div v-for="proj in projects" :key="proj.id" :class="projectColsClass">
                    <div class="card border-0 rounded-4 shadow-sm h-100 content-card p-4 d-flex flex-column justify-content-between transition-hover">
                      <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                          <img 
                            v-if="proj.logo" 
                            :src="formatImageUrl(proj.logo)" 
                            class="rounded-3 border flex-shrink-0" 
                            style="width: 48px; height: 48px; object-fit: contain;" 
                            :alt="proj.name"
                          />
                          <div v-else class="rounded-3 text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;" :style="{ backgroundColor: accentColor }">
                            <i class="bi bi-rocket-takeoff"></i>
                          </div>
                          <div class="min-w-0">
                            <h3 class="h6 fw-bold mb-0 text-truncate">{{ proj.name }}</h3>
                            <small class="text-muted extra-small">{{ proj.category || 'Tech & Software' }}</small>
                          </div>
                        </div>
                        <div class="small text-muted mb-3 leading-relaxed" v-html="formatHtml(proj.pitch || proj.description || 'Projet innovant propulsé sur Tech237.')"></div>
                      </div>

                      <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                        <div class="d-flex align-items-center gap-2 extra-small text-muted">
                          <span v-if="proj.views_count"><i class="bi bi-eye me-1"></i>{{ proj.views_count }}</span>
                          <span v-if="proj.upvotes_count"><i class="bi bi-heart-fill text-danger me-1"></i>{{ proj.upvotes_count }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1 d-print-none">
                          <button type="button" @click="openCaseStudyModal(proj)" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold extra-small">
                            <i class="bi bi-eye-fill me-1"></i> Aperçu
                          </button>
                          <a :href="'/projet/' + (proj.slug || proj.id)" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold extra-small">
                            Fiche &rarr;
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

              <!-- 6. EXPERIENCES BLOCK -->
              <section v-else-if="block.id === 'experiences' && (portfolioUser.experiences && portfolioUser.experiences.length)" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="section-icon-badge" :style="{ color: accentColor }">
                      <i class="bi bi-briefcase-fill fs-5"></i>
                    </span>
                    <h2 class="h5 fw-bold mb-0 section-heading">Parcours &amp; Expériences</h2>
                  </div>

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

              <!-- 9.B NOW SECTION (« EN CE MOMENT ») -->
              <section v-else-if="block.id === 'now' && hasNowSection" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="section-icon-badge" :style="{ color: accentColor }">
                      <i class="bi bi-compass-fill fs-5"></i>
                    </span>
                    <h2 class="h5 fw-bold mb-0 section-heading">En ce moment (Now)</h2>
                  </div>

                  <div class="row g-3">
                    <div v-if="nowSection.project" class="col-md-4">
                      <div class="p-3 rounded-4 bg-light border h-100">
                        <span class="badge bg-primary-subtle text-primary extra-small mb-2">🔨 Sur quoi je travaille</span>
                        <p class="small fw-semibold text-dark mb-0">{{ nowSection.project }}</p>
                      </div>
                    </div>
                    <div v-if="nowSection.learning" class="col-md-4">
                      <div class="p-3 rounded-4 bg-light border h-100">
                        <span class="badge bg-success-subtle text-success extra-small mb-2">📚 En apprentissage</span>
                        <p class="small fw-semibold text-dark mb-0">{{ nowSection.learning }}</p>
                      </div>
                    </div>
                    <div v-if="nowSection.goal" class="col-md-4">
                      <div class="p-3 rounded-4 bg-light border h-100">
                        <span class="badge bg-warning-subtle text-warning-emphasis extra-small mb-2">🎯 Prochain objectif</span>
                        <p class="small fw-semibold text-dark mb-0">{{ nowSection.goal }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- 9.C GITHUB LIVE STATS BLOCK -->
              <section v-else-if="block.id === 'github' && hasGithubStats" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                    <div class="d-flex align-items-center gap-2">
                      <span class="section-icon-badge" :style="{ color: accentColor }">
                        <i class="bi bi-github fs-5"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">Statistiques GitHub &amp; Open-Source</h2>
                    </div>
                    <a :href="'https://github.com/' + githubUsername" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3 extra-small fw-bold">
                      <i class="bi bi-github me-1"></i> @{{ githubUsername }}
                    </a>
                  </div>

                  <div v-if="githubProfile" class="row g-3 mb-4">
                    <div class="col-4">
                      <div class="p-3 rounded-4 bg-light border text-center">
                        <strong class="fs-4 fw-black d-block" :style="{ color: accentColor }">{{ githubProfile.public_repos || 0 }}</strong>
                        <span class="extra-small text-muted">Dépôts Publics</span>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="p-3 rounded-4 bg-light border text-center">
                        <strong class="fs-4 fw-black d-block" :style="{ color: accentColor }">{{ githubTotalStars }}</strong>
                        <span class="extra-small text-muted">Stars cumulées</span>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="p-3 rounded-4 bg-light border text-center">
                        <strong class="fs-4 fw-black d-block" :style="{ color: accentColor }">{{ githubProfile.followers || 0 }}</strong>
                        <span class="extra-small text-muted">Followers</span>
                      </div>
                    </div>
                  </div>

                  <div v-if="githubTopLanguages.length" class="mb-4">
                    <span class="extra-small fw-bold text-muted d-block mb-2">Langages les plus utilisés :</span>
                    <div class="d-flex flex-wrap gap-2">
                      <span v-for="lang in githubTopLanguages" :key="lang.name" class="badge bg-light text-dark border rounded-pill px-3 py-2 extra-small fw-semibold">
                        <i class="bi bi-code-slash me-1" :style="{ color: accentColor }"></i>{{ lang.name }} ({{ lang.percent }}%)
                      </span>
                    </div>
                  </div>

                  <div v-if="githubRepos.length" class="row g-3">
                    <div v-for="repo in githubRepos.slice(0, 4)" :key="repo.id" class="col-md-6">
                      <a :href="repo.html_url" target="_blank" class="p-3 rounded-4 bg-light border d-block text-decoration-none h-100 transition-hover">
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

              <!-- 9.E.0 WORK PROCESS / METHODOLOGY IN 4 STEPS BLOCK -->
              <section v-else-if="block.id === 'process' && hasWorkProcess" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                      <span class="section-icon-badge" :style="{ color: accentColor }">
                        <i class="bi bi-diagram-3-fill fs-5"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">{{ workProcessConfig.title || 'Comment je travaille (En 4 étapes)' }}</h2>
                    </div>
                  </div>

                  <div class="row g-4">
                    <div v-for="(step, stIdx) in workProcessSteps" :key="stIdx" class="col-md-6 col-lg-3">
                      <div class="p-4 rounded-4 bg-light border h-100 position-relative transition-hover">
                        <div 
                          class="rounded-circle text-white fw-black d-flex align-items-center justify-content-center mb-3 shadow-sm"
                          style="width: 42px; height: 42px; font-size: 16px;"
                          :style="{ backgroundColor: accentColor }"
                        >
                          0{{ stIdx + 1 }}
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

              <!-- 9.F TESTIMONIALS & SOCIAL PROOF BLOCK -->
              <section v-else-if="block.id === 'testimonials' && testimonialsList.length" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card h-100">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                      <span class="section-icon-badge" :style="{ color: accentColor }">
                        <i class="bi bi-chat-quote-fill fs-5"></i>
                      </span>
                      <h2 class="h5 fw-bold mb-0 section-heading">Témoignages &amp; Recommandations</h2>
                    </div>
                  </div>

                  <div class="row g-3">
                    <div v-for="(testi, tIdx) in testimonialsList" :key="tIdx" class="col-md-6">
                      <div class="p-4 rounded-4 bg-light border h-100 d-flex flex-column justify-content-between">
                        <div>
                          <div class="text-warning small mb-2">
                            <i v-for="star in (Number(testi.rating) || 5)" :key="star" class="bi bi-star-fill me-1"></i>
                          </div>
                          <p class="small text-dark fst-italic leading-relaxed mb-3">« {{ testi.comment }} »</p>
                        </div>
                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                          <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center small flex-shrink-0" style="width: 38px; height: 38px;" :style="{ backgroundColor: accentColor }">
                            {{ getInitials(testi.author_name) }}
                          </div>
                          <div class="min-w-0">
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

              <!-- 10. CONTACT BLOCK WITH INTERACTIVE EMAIL FORM -->
              <section v-else-if="block.id === 'contact'" id="portfolioContactSection" class="portfolio-block h-100">
                <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card text-center h-100">
                  <div class="mx-auto mb-2 section-icon-badge" :style="{ color: accentColor }">
                    <i class="bi bi-send-check-fill fs-2"></i>
                  </div>
                  <h2 class="h4 fw-bold mb-2">Entrons en contact</h2>
                  <p class="text-muted small max-w-md mx-auto mb-4">
                    Un projet innovant, une opportunité ou envie d'échanger sur la tech au Cameroun ? Envoyez-moi un message direct ci-dessous.
                  </p>

                  <!-- Direct Contact Shortcuts -->
                  <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 mb-4">
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

                  <!-- INTERACTIVE CONTACT FORM -->
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
                            <span>{{ isSubmittingContact ? 'Envoi en cours...' : 'Envoyer mon message' }}</span>
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>

                  <!-- Social Links in Contact Block -->
                  <div v-if="hasSocialLinks" class="d-flex align-items-center justify-content-center gap-2 pt-4 border-top mt-4">
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
              </section>

            </div>

          </template>

        </div>

      </main>

      <!-- CUSTOMIZABLE GLOBAL FOOTER (Multi-Column Real Website Footer or Minimal) -->
      <footer class="portfolio-global-footer py-5 mt-5 border-top d-print-none position-relative z-10">
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
                {{ stripHtmlTags(portfolioUser.bio || seoDescription).slice(0, 180) }}
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

            <!-- Col 3: Top Skills / Services -->
            <div class="col-6 col-lg-3">
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
                <button type="button" @click="navigateWebsitePage('contact', 'portfolioContactSection')" class="btn btn-xs rounded-pill px-3 py-1 fw-bold text-white" :style="{ backgroundColor: accentColor }">
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
      brand_name: '',
      brand_subtitle: '',
      cta_text: 'Me contacter'
    }
  }
  return {
    enabled: nav.enabled !== false,
    style: nav.style || 'floating_pill',
    navigation_mode: nav.navigation_mode || 'multi_page',
    brand_name: nav.brand_name || '',
    brand_subtitle: nav.brand_subtitle || '',
    cta_text: nav.cta_text || 'Me contacter'
  }
})

const websiteNavItems = computed(() => {
  const items = [
    { id: 'home', label: 'Accueil', icon: 'bi-house-door-fill', anchor: 'block-hero' },
    { id: 'about', label: 'À Propos & Parcours', icon: 'bi-person-badge-fill', anchor: 'block-about' },
  ]
  if ((props.projects && props.projects.length) || (props.pocs && props.pocs.length) || hasGithubStats.value) {
    items.push({ id: 'projects', label: 'Réalisations', icon: 'bi-rocket-takeoff-fill', anchor: 'block-projects' })
  }
  if (pricingPackagesList.value.length || hasWorkProcess.value || testimonialsList.value.length || faqItemsList.value.length) {
    items.push({ id: 'services', label: 'Services & Tarifs', icon: 'bi-tags-fill', anchor: 'block-pricing' })
  }
  items.push({ id: 'contact', label: 'Contact', icon: 'bi-envelope-paper-fill', anchor: 'portfolioContactSection' })
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
  return layout.value.kpi_stats || { enabled: true, items: [] }
})

const kpiStatsItems = computed(() => {
  if (Array.isArray(kpiStatsConfig.value.items) && kpiStatsConfig.value.items.length) {
    return kpiStatsConfig.value.items.filter(k => k && (k.value || k.label))
  }
  return [
    { value: props.projects?.length ? `+${props.projects.length}` : '+10', label: 'Projets & Réalisations' },
    { value: yearsOfExperience.value || '100%', label: yearsOfExperience.value ? 'Expérience métier' : 'Engagement & Qualité' },
    { value: `${props.portfolioUser.xp || 100} XP`, label: props.portfolioUser.rank_title || 'Membre Vérifié Tech237' },
    { value: '24h', label: 'Réactivité & Écoute' }
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
const slideshowConfig = computed(() => layout.value.slideshow || { enabled: false, slides: [], images: [] })
const slideshowHeight = computed(() => {
  const h = slideshowConfig.value.height
  if (!h) return '340px'
  const str = String(h).trim()
  return str.endsWith('px') ? str : `${str}px`
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

// Tabbed multi-page website mode (NO EMOJIS, Bootstrap Icons)
const isTabbedMode = computed(() => {
  return currentStructure.value.startsWith('tabbed') || Boolean(layout.value.tabbed_nav?.enabled)
})

const activeTab = ref(layout.value.tabbed_nav?.default_tab || 'all')
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

const availableTabs = computed(() => {
  const tabs = [
    { id: 'all', label: 'Vue Globale', iconClass: 'bi-grid-fill' }
  ]
  if (props.portfolioUser.bio) {
    tabs.push({ id: 'about', label: 'À Propos', iconClass: 'bi-person-lines-fill' })
  }
  if (youtubeEmbedUrl.value) {
    tabs.push({ id: 'video', label: 'Vidéo Pitch', iconClass: 'bi-play-btn-fill' })
  }
  if (props.portfolioUser.skills && props.portfolioUser.skills.length) {
    tabs.push({ id: 'skills', label: 'Compétences', iconClass: 'bi-cpu-fill', count: props.portfolioUser.skills.length })
  }
  if (props.projects && props.projects.length) {
    tabs.push({ id: 'projects', label: 'Projets', iconClass: 'bi-rocket-takeoff-fill', count: props.projects.length })
  }
  if (props.portfolioUser.experiences && props.portfolioUser.experiences.length) {
    tabs.push({ id: 'experiences', label: 'Parcours', iconClass: 'bi-briefcase-fill' })
  }
  if (props.portfolioUser.educations && props.portfolioUser.educations.length) {
    tabs.push({ id: 'educations', label: 'Formations', iconClass: 'bi-mortarboard-fill' })
  }
  if (props.pocs && props.pocs.length) {
    tabs.push({ id: 'pocs', label: 'POCs', iconClass: 'bi-lightning-charge-fill', count: props.pocs.length })
  }
  if (pricingPackagesList.value.length) {
    tabs.push({ id: 'pricing', label: 'Offres & Tarifs', iconClass: 'bi-tags-fill', count: pricingPackagesList.value.length })
  }
  if (certificationsList.value.length) {
    tabs.push({ id: 'certifications', label: 'Certifications', iconClass: 'bi-patch-check-fill', count: certificationsList.value.length })
  }
  if (testimonialsList.value.length) {
    tabs.push({ id: 'testimonials', label: 'Témoignages', iconClass: 'bi-chat-quote-fill', count: testimonialsList.value.length })
  }
  tabs.push({ id: 'contact', label: 'Contact', iconClass: 'bi-envelope-fill' })
  return tabs
})

function isBlockActiveInTab(blockId) {
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

const containerMaxWidth = computed(() => {
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

const cssVars = computed(() => {
  return {
    '--accent-color': accentColor.value,
  }
})

// Dynamic Ordered Blocks
const defaultBlocks = [
  { id: 'hero', name: 'En-tête & Présentation', enabled: true, width: 'full' },
  { id: 'about', name: 'À propos & Parcours', enabled: true, width: 'full' },
  { id: 'video', name: 'Vidéo de Présentation', enabled: true, width: 'full' },
  { id: 'now', name: 'En ce moment (Now)', enabled: true, width: 'half' },
  { id: 'github', name: 'Statistiques GitHub Live', enabled: true, width: 'half' },
  { id: 'skills', name: 'Compétences & Stack technique', enabled: true, width: 'half' },
  { id: 'projects', name: 'Projets Tech en vedette', enabled: true, width: 'full' },
  { id: 'pocs', name: 'Mini-Actus & POCs Tech237', enabled: true, width: 'half' },
  { id: 'experiences', name: 'Expériences professionnelles', enabled: true, width: 'full' },
  { id: 'educations', name: 'Formations & Diplômes', enabled: true, width: 'half' },
  { id: 'certifications', name: 'Certifications & Distinctions', enabled: true, width: 'half' },
  { id: 'process', name: 'Mon Processus & Méthodologie', enabled: true, width: 'full' },
  { id: 'pricing', name: 'Grille Tarifaire & Packs Services', enabled: true, width: 'full' },
  { id: 'testimonials', name: 'Témoignages & Recommandations', enabled: true, width: 'full' },
  { id: 'faq', name: 'Questions Fréquentes (FAQ)', enabled: true, width: 'full' },
  { id: 'badges', name: 'Trophées & Badges Tech237', enabled: true, width: 'half' },
  { id: 'custom', name: 'Sections personnalisées libres', enabled: true, width: 'full' },
  { id: 'contact', name: 'Coordonnées & Réseaux', enabled: true, width: 'full' },
]

const orderedBlocks = computed(() => {
  let list = Array.isArray(layout.value.blocks) && layout.value.blocks.length > 0 
    ? [...layout.value.blocks] 
    : [...defaultBlocks]

  if (youtubeEmbedUrl.value && !list.some(b => b.id === 'video')) {
    const aboutIdx = list.findIndex(b => b.id === 'about')
    const insertIdx = aboutIdx !== -1 ? aboutIdx + 1 : 1
    list.splice(insertIdx, 0, { id: 'video', name: 'Vidéo de Présentation', enabled: true, width: 'full' })
  }

  const extraBlocks = defaultBlocks.filter(db => ['now', 'github', 'certifications', 'process', 'pricing', 'testimonials', 'faq'].includes(db.id))
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

  return list
})

function shouldRenderBlock(id) {
  if (id === 'about') return Boolean(props.portfolioUser.bio)
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
  return true
}

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

function getSkillBg(idx) {
  return skillBgPalette[idx % skillBgPalette.length]
}

function getSkillColor(idx) {
  return skillColorPalette[idx % skillColorPalette.length]
}

function getSkillPercent(skill) {
  if (typeof skill === 'object' && skill.level) {
    const l = skill.level.toLowerCase()
    if (l.includes('expert')) return 95
    if (l.includes('avancé') || l.includes('avance')) return 80
    if (l.includes('intermédiaire') || l.includes('intermediaire')) return 60
    return 40
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

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
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

  if (typewriterEnabled.value) {
    runTypewriterStep()
  }

  if (hasGithubStats.value) {
    fetchGithubLiveStats()
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  if (slideshowInterval) {
    clearInterval(slideshowInterval)
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
   PRINT / PDF EXPORT OPTIMIZATIONS
   ========================================================================== */
@media print {
  .portfolio-nav, .btn, .d-print-none, .ambient-effects-layer, .preloader-overlay, .portfolio-tabbed-bar, .portfolio-top-slideshow, .btn-floating-back-to-top {
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
}
</style>