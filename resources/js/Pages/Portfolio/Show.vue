<template>
  <div 
    class="portfolio-root min-vh-100 position-relative overflow-x-hidden" 
    :class="[
      'theme-' + currentTheme, 
      'card-style-' + currentCardStyle,
      'font-' + currentFont,
      'bg-pattern-' + currentPattern,
      'structure-' + currentStructure
    ]" 
    :style="cssVars"
  >
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

    <!-- TOP FLOATING BAR (Screen only, hidden on print) -->
    <header class="portfolio-nav sticky-top py-2 px-3 px-md-4 d-print-none shadow-2xs backdrop-blur position-relative z-10">
      <div class="container d-flex align-items-center justify-content-between">
        
        <!-- Brand & Verified Badge -->
        <div class="d-flex align-items-center gap-2">
          <Link href="/" class="d-flex align-items-center gap-2 text-decoration-none">
            <img src="/assets/images/logo.png" alt="Tech237" style="height: 28px; object-fit: contain;">
            <span class="fw-bold fs-6 nav-brand-text">Tech237</span>
          </Link>
          <span class="vr d-none d-sm-block opacity-25 mx-1" style="height: 18px;"></span>
          <span class="badge rounded-pill px-2 py-1 extra-small fw-semibold border nav-verified-pill d-none d-sm-inline-flex align-items-center gap-1">
            <i class="bi bi-patch-check-fill text-success"></i>
            <span>Portfolio vérifié</span>
          </span>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex align-items-center gap-2">
          <!-- Share Link Copy Button -->
          <button 
            type="button" 
            @click="copyPortfolioLink" 
            class="btn btn-sm btn-nav-action rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1"
            title="Copier mon lien de partage public"
          >
            <i class="bi" :class="isCopied ? 'bi-check-lg text-success' : 'bi-link-45deg'"></i>
            <span>{{ isCopied ? 'Lien copié !' : 'Partager' }}</span>
          </button>

          <!-- WhatsApp Direct Share -->
          <a 
            :href="whatsappShareUrl" 
            target="_blank" 
            class="btn btn-sm btn-success rounded-circle shadow-2xs p-0 d-flex align-items-center justify-content-center"
            style="width: 32px; height: 32px; background-color: #25D366; border-color: #25D366;"
            title="Partager sur WhatsApp"
          >
            <i class="bi bi-whatsapp text-white" style="font-size: 14px;"></i>
          </a>

          <!-- LinkedIn Direct Share -->
          <a 
            :href="linkedinShareUrl" 
            target="_blank" 
            class="btn btn-sm btn-primary rounded-circle shadow-2xs p-0 d-flex align-items-center justify-content-center"
            style="width: 32px; height: 32px; background-color: #0A66C2; border-color: #0A66C2;"
            title="Partager sur LinkedIn"
          >
            <i class="bi bi-linkedin text-white" style="font-size: 13px;"></i>
          </a>

          <!-- Download CV Button (Custom File or PDF Print) -->
          <a 
            v-if="cvFileUrl" 
            :href="cvFileUrl" 
            target="_blank" 
            download
            class="btn btn-sm btn-nav-action rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1 text-decoration-none"
            title="Télécharger mon CV au format officiel"
          >
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
            <span class="d-none d-md-inline">Télécharger CV</span>
          </a>
          <button 
            v-else 
            type="button" 
            @click="printCV" 
            class="btn btn-sm btn-nav-action rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1"
            title="Exporter ou Imprimer ce CV en PDF"
          >
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
            <span class="d-none d-md-inline">Exporter PDF</span>
          </button>

          <!-- Owner Quick Edit Shortcut -->
          <Link 
            v-if="isOwner" 
            href="/espace-utilisateur?section=portfolio" 
            class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold shadow-2xs d-inline-flex align-items-center gap-1"
            title="Modifier ce portfolio"
          >
            <i class="bi bi-sliders"></i>
            <span class="d-none d-sm-inline">Personnaliser</span>
          </Link>
        </div>

      </div>
    </header>

    <!-- TOP HERO BANNER SLIDESHOW / CAROUSEL -->
    <div 
      v-if="slideshowConfig.enabled && slideshowImages.length" 
      class="portfolio-top-slideshow position-relative overflow-hidden w-100 d-print-none shadow-sm"
      :style="{ height: slideshowConfig.height || '340px' }"
    >
      <div 
        v-for="(slide, sIdx) in slideshowImages" 
        :key="sIdx"
        class="slideshow-item position-absolute top-0 start-0 w-100 h-100 transition-opacity"
        :class="{ 'opacity-100 z-2': currentSlideIndex === sIdx, 'opacity-0 z-1 pointer-events-none': currentSlideIndex !== sIdx }"
      >
        <img 
          :src="formatImageUrl(slide.image || slide.url || slide)" 
          :alt="slide.caption || 'Slide Tech237'" 
          class="w-100 h-100 object-fit-cover"
        />
        <div v-if="slide.caption" class="slideshow-caption position-absolute bottom-0 start-0 w-100 p-3 p-md-4 text-white text-center" style="background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.7) 100%);">
          <p class="mb-0 fs-6 fw-bold text-shadow">{{ slide.caption }}</p>
        </div>
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
                <img 
                  v-if="portfolioUser.avatar" 
                  :src="formatImageUrl(portfolioUser.avatar)" 
                  :alt="portfolioUser.name"
                  class="rounded-circle border border-4 border-white shadow-md hero-avatar mx-auto mb-2"
                />
                <div 
                  v-else 
                  class="rounded-circle border border-4 border-white shadow-md text-white fw-bold d-flex align-items-center justify-content-center hero-avatar mx-auto mb-2"
                  :style="{ backgroundColor: accentColor }"
                >
                  {{ getInitials(portfolioUser.name) }}
                </div>
                
                <h1 class="h4 fw-bold mb-1 user-name-heading">{{ portfolioUser.name }}</h1>
                <p class="small fw-semibold mb-2" :style="{ color: accentColor }">{{ portfolioUser.headline || 'Bâtisseur & Développeur Tech' }}</p>
                
                <span class="badge rounded-pill px-3 py-1 availability-pill" :class="getAvailabilityClass(portfolioUser.availability)">
                  <span class="status-pulse-dot me-1"></span>
                  {{ getAvailabilityLabel(portfolioUser.availability) }}
                </span>
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

            <!-- Contact Block in CV Split -->
            <div v-if="isBlockActiveInTab('contact')" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
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
              :class="getBlockGridClass(block)"
            >
              <!-- 1. HERO / HEADER BLOCK -->
              <section v-if="block.id === 'hero'" class="portfolio-block h-100">
                
                <!-- Variant: Centered (Default) -->
                <div v-if="heroStyle === 'centered'" class="hero-box hero-centered text-center p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden h-100">
                  <div class="hero-glow-orb"></div>
                  <div class="position-relative z-1">
                    <!-- Avatar with Rank Ring -->
                    <div class="d-inline-block position-relative mb-3">
                      <img 
                        v-if="portfolioUser.avatar" 
                        :src="formatImageUrl(portfolioUser.avatar)" 
                        :alt="portfolioUser.name"
                        class="rounded-circle border border-4 border-white shadow-md hero-avatar"
                      />
                      <div 
                        v-else 
                        class="rounded-circle border border-4 border-white shadow-md text-white fw-bold d-flex align-items-center justify-content-center hero-avatar mx-auto"
                        :style="{ backgroundColor: accentColor }"
                      >
                        {{ getInitials(portfolioUser.name) }}
                      </div>

                      <!-- Verified Check -->
                      <span class="position-absolute bottom-0 end-0 badge rounded-circle bg-success border border-2 border-white p-1" title="Membre vérifié Tech237">
                        <i class="bi bi-check-lg text-white" style="font-size: 11px;"></i>
                      </span>
                    </div>

                    <!-- Name & Rank -->
                    <h1 class="display-6 fw-bold mb-1 user-name-heading">{{ portfolioUser.name }}</h1>
                    
                    <!-- Headline / Subtitle -->
                    <p class="fs-5 fw-semibold mb-2 user-headline-text" :style="{ color: accentColor }">
                      {{ portfolioUser.headline || 'Bâtisseur & Développeur Tech' }}
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
                      <img 
                        v-if="portfolioUser.avatar" 
                        :src="formatImageUrl(portfolioUser.avatar)" 
                        :alt="portfolioUser.name"
                        class="rounded-4 border border-4 border-white shadow-md hero-avatar-sq"
                      />
                      <div 
                        v-else 
                        class="rounded-4 border border-4 border-white shadow-md text-white fw-bold d-flex align-items-center justify-content-center hero-avatar-sq mx-auto mx-md-0"
                        :style="{ backgroundColor: accentColor }"
                      >
                        {{ getInitials(portfolioUser.name) }}
                      </div>
                    </div>
                    <div class="col-md-8 text-center text-md-start">
                      <span class="badge rounded-pill px-3 py-1 availability-pill mb-2" :class="getAvailabilityClass(portfolioUser.availability)">
                        <span class="status-pulse-dot me-1"></span>
                        {{ getAvailabilityLabel(portfolioUser.availability) }}
                      </span>
                      <h1 class="display-6 fw-bold mb-1 user-name-heading">{{ portfolioUser.name }}</h1>
                      <p class="fs-5 fw-semibold mb-2 user-headline-text" :style="{ color: accentColor }">
                        {{ portfolioUser.headline || 'Bâtisseur & Développeur Tech' }}
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
                        <a 
                          v-if="portfolioUser.phone" 
                          :href="'https://wa.me/' + cleanPhone(portfolioUser.phone)" 
                          target="_blank" 
                          class="btn btn-success rounded-pill px-4 py-2 fw-bold text-white shadow-sm"
                        >
                          <i class="bi bi-whatsapp me-1"></i> WhatsApp
                        </a>
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
                    <div class="text-info mb-1">// {{ portfolioUser.headline || 'Architecte Logiciel' }}</div>
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
                    <p class="fs-6 opacity-90 mb-0">{{ portfolioUser.headline || 'Bâtisseur & Développeur Tech' }}</p>
                  </div>
                  <div class="p-4 text-center position-relative content-card">
                    <img 
                      v-if="portfolioUser.avatar" 
                      :src="formatImageUrl(portfolioUser.avatar)" 
                      :alt="portfolioUser.name"
                      class="rounded-circle border border-4 border-white shadow-md hero-avatar mx-auto mb-2"
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
                      class="rounded-circle border border-2 border-emerald-400 shadow hero-avatar mb-3"
                    />
                    <h1 class="display-6 fw-black text-white text-uppercase tracking-wider mb-1">{{ portfolioUser.name }}</h1>
                    <p class="text-emerald-400 font-monospace mb-2">{{ portfolioUser.headline }}</p>
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
                        class="rounded-circle border hero-avatar"
                        style="width: 72px; height: 72px;"
                      />
                      <div>
                        <h1 class="h3 fw-bold mb-0 text-dark">{{ portfolioUser.name }}</h1>
                        <p class="small text-muted mb-0 fw-bold">{{ portfolioUser.headline }}</p>
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
                      class="rounded-circle border hero-avatar"
                    />
                    <div>
                      <h1 class="h3 fw-bold mb-1 user-name-heading">{{ portfolioUser.name }}</h1>
                      <p class="text-muted fw-semibold mb-2">{{ portfolioUser.headline }}</p>
                      <div class="d-flex gap-2 flex-wrap">
                        <span v-if="yearsOfExperience" class="badge bg-light text-dark border extra-small">{{ yearsOfExperience }}</span>
                        <span class="badge bg-light text-dark border extra-small">{{ getAvailabilityLabel(portfolioUser.availability) }}</span>
                        <span v-if="portfolioUser.location" class="badge bg-light text-muted border extra-small">{{ portfolioUser.location }}</span>
                      </div>
                    </div>
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

                <!-- CAROUSEL / SLIDER VARIANT -->
                <div v-if="projectsStyle === 'carousel'" class="carousel-project-container card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
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
                        <a :href="'/projet/' + (proj.slug || proj.id)" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold extra-small d-print-none">
                          Voir la fiche &rarr;
                        </a>
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

              <!-- 10. CONTACT BLOCK WITH INTERACTIVE EMAIL FORM -->
              <section v-else-if="block.id === 'contact'" class="portfolio-block h-100">
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

      <!-- CUSTOMIZABLE GLOBAL FOOTER -->
      <footer class="portfolio-global-footer py-5 mt-5 border-top d-print-none text-center position-relative z-10">
        <div class="container">
          <!-- Custom Footer Text -->
          <p v-if="footerConfig.custom_text" class="fw-semibold text-dark mb-2" v-html="formatHtml(footerConfig.custom_text)"></p>
          
          <!-- Social Icons if enabled -->
          <div v-if="footerConfig.show_socials !== false && hasSocialLinks" class="d-flex align-items-center justify-content-center gap-2 mb-3">
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

          <!-- Tech237 Signature Badge if enabled -->
          <div v-if="footerConfig.show_tech237_badge !== false" class="d-flex align-items-center justify-content-center gap-2 mb-1">
            <img src="/assets/images/logo.png" alt="Tech237" style="height: 20px; object-fit: contain;">
            <span class="fw-bold small text-muted">Tech237 Community</span>
          </div>
          <p v-if="footerConfig.show_tech237_badge !== false" class="extra-small text-muted mb-0">
            Portfolio vérifié hébergé sur Tech237 &bull; Le média et réseau d'innovation tech au Cameroun.
          </p>
        </div>
      </footer>

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
const heroStyle = computed(() => layout.value.hero_style || 'centered')
const projectsStyle = computed(() => layout.value.projects_style || 'cards')
const skillsStyle = computed(() => layout.value.skills_style || 'chips')
const ambientEffect = computed(() => layout.value.ambient_effect || 'none')
const pageAnimation = computed(() => layout.value.page_animation || 'fade')
const yearsOfExperience = computed(() => layout.value.years_of_experience || '')
const googleMapsUrl = computed(() => layout.value.google_maps_url || '')
const cvFileUrl = computed(() => {
  const file = layout.value.cv_file
  return file ? formatImageUrl(file) : null
})

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
const slideshowImages = computed(() => {
  const list = slideshowConfig.value.slides || slideshowConfig.value.images || []
  return Array.isArray(list) ? list.filter(s => (typeof s === 'string' ? !!s : !!(s?.image || s?.url))) : []
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
  tabs.push({ id: 'contact', label: 'Contact', iconClass: 'bi-envelope-fill' })
  return tabs
})

function isBlockActiveInTab(blockId) {
  if (!isTabbedMode.value) return true
  if (activeTab.value === 'all') return true
  if (activeTab.value === blockId) return true
  if (activeTab.value === 'about' && blockId === 'hero') return true
  return false
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
  if (isBentoGrid.value) return '1180px'
  if (currentStructure.value === 'compact') return '760px'
  if (currentStructure.value === 'zen') return '840px'
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
  { id: 'skills', name: 'Compétences & Stack technique', enabled: true, width: 'half' },
  { id: 'projects', name: 'Projets Tech en vedette', enabled: true, width: 'full' },
  { id: 'pocs', name: 'Mini-Actus & POCs Tech237', enabled: true, width: 'half' },
  { id: 'experiences', name: 'Expériences professionnelles', enabled: true, width: 'full' },
  { id: 'educations', name: 'Formations & Diplômes', enabled: true, width: 'half' },
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

  return list
})

function shouldRenderBlock(id) {
  if (id === 'about') return Boolean(props.portfolioUser.bio)
  if (id === 'video') return Boolean(youtubeEmbedUrl.value)
  if (id === 'skills') return Boolean(props.portfolioUser.skills && props.portfolioUser.skills.length)
  if (id === 'projects') return Boolean(props.projects && props.projects.length)
  if (id === 'pocs') return Boolean(props.pocs && props.pocs.length)
  if (id === 'experiences') return Boolean(props.portfolioUser.experiences && props.portfolioUser.experiences.length)
  if (id === 'educations') return Boolean(props.portfolioUser.educations && props.portfolioUser.educations.length)
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
  if (slideshowConfig.value.enabled && slideshowConfig.value.autoplay !== false && slideshowImages.value.length > 1) {
    const speed = (Number(slideshowConfig.value.interval || slideshowConfig.value.speed) || 4) * 1000
    slideshowInterval = setInterval(() => {
      nextSlide()
    }, speed)
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  if (slideshowInterval) {
    clearInterval(slideshowInterval)
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

/* HERO AVATARS */
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