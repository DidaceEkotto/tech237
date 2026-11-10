<template>
  <div class="portfolio-root min-vh-100" :class="['theme-' + currentTheme, 'card-style-' + currentCardStyle]" :style="cssVars">
    <Head>
      <title>{{ seoTitle }}</title>
      <meta name="description" :content="seoDescription">
      <meta property="og:title" :content="seoTitle">
      <meta property="og:description" :content="seoDescription">
      <meta property="og:type" content="profile">
      <meta property="og:url" :content="shareUrl">
      <meta property="og:image" :content="portfolioUser.avatar ? (portfolioUser.avatar.startsWith('/') ? portfolioUser.avatar : '/' + portfolioUser.avatar) : '/assets/images/logo.png'">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:title" :content="seoTitle">
      <meta name="twitter:description" :content="seoDescription">
    </Head>

    <!-- Top Floating Navigation (Screen only, hidden on print) -->
    <header class="portfolio-nav sticky-top py-2 px-3 px-md-4 d-print-none shadow-2xs backdrop-blur">
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

          <!-- Download CV / Print Button -->
          <button 
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

    <!-- Main Container: Dynamic Blocks in Custom User Order -->
    <main class="container py-4 py-md-5" style="max-width: 980px;">
      
      <!-- Loop through user blocks according to custom order and enabled state -->
      <template v-for="block in orderedBlocks" :key="block.id">
        
        <!-- 1. HERO / HEADER BLOCK -->
        <section v-if="block.id === 'hero' && block.enabled" class="portfolio-block mb-4 mb-md-5">
          
          <!-- Variant: Centered (Default) -->
          <div v-if="heroStyle === 'centered'" class="hero-box hero-centered text-center p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden">
            <div class="hero-glow-orb"></div>
            
            <div class="position-relative z-1">
              <!-- Avatar with Rank Ring -->
              <div class="d-inline-block position-relative mb-3">
                <img 
                  v-if="portfolioUser.avatar" 
                  :src="portfolioUser.avatar.startsWith('/') ? portfolioUser.avatar : '/' + portfolioUser.avatar" 
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

              <!-- Location & Availability Pills -->
              <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 mb-3">
                <span v-if="portfolioUser.location" class="badge rounded-pill px-3 py-2 meta-pill">
                  <i class="bi bi-geo-alt-fill me-1 text-danger"></i> {{ portfolioUser.location }}
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
                  <span>Me contacter sur WhatsApp</span>
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

                <button 
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
          <div v-else-if="heroStyle === 'split'" class="hero-box hero-split p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden">
            <div class="row align-items-center g-4">
              <div class="col-md-4 text-center text-md-start">
                <img 
                  v-if="portfolioUser.avatar" 
                  :src="portfolioUser.avatar.startsWith('/') ? portfolioUser.avatar : '/' + portfolioUser.avatar" 
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
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ portfolioUser.location }}
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
                    class="btn btn-sm rounded-pill px-3 py-2 fw-bold text-white shadow-2xs"
                    style="background-color: #25D366;"
                  >
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                  </a>
                  <button type="button" @click="printCV" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i> CV PDF
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Variant: Terminal (Retro Geek Dark Console) -->
          <div v-else-if="heroStyle === 'terminal'" class="hero-box hero-terminal p-4 rounded-4 shadow-md bg-dark text-light font-monospace">
            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-secondary border-opacity-25">
              <div class="d-flex gap-2">
                <span class="rounded-circle bg-danger d-inline-block" style="width: 12px; height: 12px;"></span>
                <span class="rounded-circle bg-warning d-inline-block" style="width: 12px; height: 12px;"></span>
                <span class="rounded-circle bg-success d-inline-block" style="width: 12px; height: 12px;"></span>
              </div>
              <span class="extra-small text-secondary">bash - 80x24</span>
            </div>
            <div class="terminal-body small">
              <p class="mb-1 text-success">&gt; tech237 whoami --verbose</p>
              <p class="mb-1"><span class="text-info">NAME:</span> {{ portfolioUser.name }}</p>
              <p class="mb-1"><span class="text-info">ROLE:</span> {{ portfolioUser.headline || 'Fullstack Developer' }}</p>
              <p class="mb-1"><span class="text-info">LOCATION:</span> {{ portfolioUser.location || 'Douala / Remote' }}</p>
              <p class="mb-1"><span class="text-info">STATUS:</span> <span class="text-warning">{{ getAvailabilityLabel(portfolioUser.availability) }}</span></p>
              <p class="mb-2"><span class="text-info">TECH237_RANK:</span> Level {{ currentTier?.name }} [{{ portfolioUser.xp || 0 }} XP]</p>
              <p class="mb-0 text-success">&gt; ready for hire <span class="terminal-cursor">_</span></p>
            </div>
          </div>

          <!-- Variant: Minimal (Clean & Simple) -->
          <div v-else class="hero-box hero-minimal p-4 border-bottom mb-2 text-start">
            <div class="d-flex align-items-center gap-3">
              <img 
                v-if="portfolioUser.avatar" 
                :src="portfolioUser.avatar.startsWith('/') ? portfolioUser.avatar : '/' + portfolioUser.avatar" 
                :alt="portfolioUser.name"
                class="rounded-circle border"
                style="width: 72px; height: 72px; object-fit: cover;"
              />
              <div>
                <h1 class="h3 fw-bold mb-0 text-dark">{{ portfolioUser.name }}</h1>
                <p class="text-muted mb-1">{{ portfolioUser.headline }}</p>
                <small class="text-muted">{{ portfolioUser.location }} &bull; {{ getAvailabilityLabel(portfolioUser.availability) }}</small>
              </div>
            </div>
          </div>

        </section>

        <!-- 2. ABOUT / BIO BLOCK -->
        <section v-else-if="block.id === 'about' && block.enabled && portfolioUser.bio" class="portfolio-block mb-4 mb-md-5">
          <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
            <div class="d-flex align-items-center gap-2 mb-3">
              <span class="section-icon-badge" :style="{ color: accentColor }">
                <i class="bi bi-person-lines-fill fs-5"></i>
              </span>
              <h2 class="h5 fw-bold mb-0 section-heading">À propos de moi</h2>
            </div>
            <p class="text-content leading-relaxed mb-0" style="white-space: pre-line;">
              {{ portfolioUser.bio }}
            </p>
          </div>
        </section>

        <!-- 3. SKILLS & STACK BLOCK -->
        <section v-else-if="block.id === 'skills' && block.enabled && (portfolioUser.skills && portfolioUser.skills.length)" class="portfolio-block mb-4 mb-md-5">
          <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
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

            <!-- Chips Variant -->
            <div v-if="skillsStyle === 'chips'" class="d-flex flex-wrap gap-2">
              <span 
                v-for="(skill, sIdx) in portfolioUser.skills" 
                :key="sIdx"
                class="skill-chip badge rounded-pill px-3 py-2 fw-semibold fs-6 shadow-2xs"
                :style="{ backgroundColor: getSkillBg(sIdx), color: getSkillColor(sIdx) }"
              >
                {{ typeof skill === 'object' ? skill.name : skill }}
                <small v-if="typeof skill === 'object' && skill.level" class="opacity-75 ms-1">&bull; {{ skill.level }}</small>
              </span>
            </div>

            <!-- Bars Variant -->
            <div v-else-if="skillsStyle === 'bars'" class="row g-3">
              <div v-for="(skill, sIdx) in portfolioUser.skills" :key="sIdx" class="col-md-6">
                <div class="d-flex justify-content-between small fw-bold mb-1">
                  <span>{{ typeof skill === 'object' ? skill.name : skill }}</span>
                  <span class="text-muted">{{ typeof skill === 'object' && skill.level ? skill.level : 'Avancé' }}</span>
                </div>
                <div class="progress rounded-pill" style="height: 8px;">
                  <div class="progress-bar rounded-pill" :style="{ width: getSkillPercent(skill) + '%', backgroundColor: accentColor }"></div>
                </div>
              </div>
            </div>

            <!-- Categorized Variant -->
            <div v-else class="row g-3">
              <div v-for="(skill, sIdx) in portfolioUser.skills" :key="sIdx" class="col-6 col-md-4 col-lg-3">
                <div class="p-3 rounded-3 text-center border skill-grid-box h-100">
                  <strong class="d-block text-dark small mb-1">{{ typeof skill === 'object' ? skill.name : skill }}</strong>
                  <span class="extra-small text-muted">{{ typeof skill === 'object' && skill.category ? skill.category : 'Tech' }}</span>
                </div>
              </div>
            </div>

          </div>
        </section>

        <!-- 4. PROJECTS BLOCK -->
        <section v-else-if="block.id === 'projects' && block.enabled && projects.length" class="portfolio-block mb-4 mb-md-5">
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

          <!-- Cards Grid Variant -->
          <div v-if="projectsStyle === 'cards'" class="row g-4">
            <div v-for="proj in projects" :key="proj.id" class="col-md-6">
              <div class="card border-0 rounded-4 shadow-sm h-100 content-card p-4 d-flex flex-column justify-content-between transition-hover">
                <div>
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <img 
                      v-if="proj.logo" 
                      :src="proj.logo.startsWith('/') ? proj.logo : '/' + proj.logo" 
                      class="rounded-3 border" 
                      style="width: 48px; height: 48px; object-fit: contain;" 
                      :alt="proj.name"
                    />
                    <div v-else class="rounded-3 text-white fw-bold d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;" :style="{ backgroundColor: accentColor }">
                      <i class="bi bi-rocket-takeoff"></i>
                    </div>
                    <div class="min-w-0">
                      <h3 class="h6 fw-bold mb-0 text-truncate">{{ proj.name }}</h3>
                      <small class="text-muted extra-small">{{ proj.category || 'Tech & Software' }}</small>
                    </div>
                  </div>
                  <p class="small text-muted mb-3 leading-relaxed">
                    {{ proj.pitch || proj.description || 'Projet innovant propulsé sur Tech237.' }}
                  </p>
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

          <!-- Timeline Variant -->
          <div v-else class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
            <div class="timeline-stream d-flex flex-column gap-4">
              <div v-for="proj in projects" :key="proj.id" class="d-flex gap-3 position-relative">
                <div class="timeline-dot rounded-circle flex-shrink-0 mt-1" :style="{ backgroundColor: accentColor }"></div>
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h3 class="h6 fw-bold text-dark mb-0">{{ proj.name }}</h3>
                    <span class="badge bg-light text-muted border extra-small">{{ proj.category || 'Web / Mobile' }}</span>
                  </div>
                  <p class="small text-muted mt-1 mb-2">{{ proj.pitch || proj.description }}</p>
                  <a :href="'/projet/' + (proj.slug || proj.id)" target="_blank" class="btn btn-xs btn-link p-0 text-decoration-none fw-bold" :style="{ color: accentColor }">
                    Découvrir ce projet sur Tech237 &rarr;
                  </a>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 5. POCS / MINI-ACTUS BLOCK -->
        <section v-else-if="block.id === 'pocs' && block.enabled && pocs.length" class="portfolio-block mb-4 mb-md-5">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
              <span class="section-icon-badge" :style="{ color: accentColor }">
                <i class="bi bi-lightning-charge-fill fs-5 text-warning"></i>
              </span>
              <h2 class="h5 fw-bold mb-0 section-heading">Mini-Actus &amp; Veille Tech (POCs)</h2>
            </div>
            <span class="badge bg-warning-subtle text-dark rounded-pill px-3 py-1 extra-small fw-bold">
              {{ pocs.length }} POC(s)
            </span>
          </div>

          <div class="row g-3">
            <div v-for="poc in pocs.slice(0, 4)" :key="poc.id" class="col-md-6">
              <div class="card p-3 rounded-4 shadow-sm border-0 content-card h-100 d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-warning text-dark extra-small rounded-pill px-2 py-0">⚡ POC</span>
                    <small class="text-muted extra-small">{{ formatDateShort(poc.published_at || poc.created_at) }}</small>
                  </div>
                  <h3 class="h6 fw-bold mb-1">
                    <a :href="'/pocs/' + (poc.slug || poc.id)" target="_blank" class="text-dark text-decoration-none hover-underline">
                      {{ poc.title }}
                    </a>
                  </h3>
                  <p class="extra-small text-muted mb-2 text-truncate-2">
                    {{ poc.summary || 'Partage de veille et mini-actualité tech.' }}
                  </p>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top extra-small text-muted">
                  <span><i class="bi bi-eye me-1"></i>{{ poc.views_count || 0 }} vues</span>
                  <a :href="'/pocs/' + (poc.slug || poc.id)" target="_blank" class="text-success fw-bold text-decoration-none d-print-none">
                    Lire &rarr;
                  </a>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 6. EXPERIENCES BLOCK -->
        <section v-else-if="block.id === 'experiences' && block.enabled && (portfolioUser.experiences && portfolioUser.experiences.length)" class="portfolio-block mb-4 mb-md-5">
          <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
            <div class="d-flex align-items-center gap-2 mb-4">
              <span class="section-icon-badge" :style="{ color: accentColor }">
                <i class="bi bi-briefcase-fill fs-5"></i>
              </span>
              <h2 class="h5 fw-bold mb-0 section-heading">Parcours &amp; Expériences</h2>
            </div>

            <div class="d-flex flex-column gap-4">
              <div v-for="(exp, eIdx) in portfolioUser.experiences" :key="eIdx" class="experience-item d-flex gap-3 position-relative">
                <div class="timeline-dot rounded-circle flex-shrink-0 mt-1" :style="{ backgroundColor: accentColor }"></div>
                <div class="flex-grow-1">
                  <div class="d-flex align-items-baseline justify-content-between flex-wrap gap-2">
                    <h3 class="h6 fw-bold mb-0 text-dark">{{ exp.role }}</h3>
                    <span class="badge bg-light text-muted border extra-small">{{ exp.period }}</span>
                  </div>
                  <strong class="d-block small text-muted mb-2" :style="{ color: accentColor }">
                    {{ exp.company }} <span v-if="exp.location">&bull; {{ exp.location }}</span>
                  </strong>
                  <p v-if="exp.description" class="small text-muted mb-0 leading-relaxed" style="white-space: pre-line;">
                    {{ exp.description }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 7. EDUCATIONS BLOCK -->
        <section v-else-if="block.id === 'educations' && block.enabled && (portfolioUser.educations && portfolioUser.educations.length)" class="portfolio-block mb-4 mb-md-5">
          <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
            <div class="d-flex align-items-center gap-2 mb-4">
              <span class="section-icon-badge" :style="{ color: accentColor }">
                <i class="bi bi-mortarboard-fill fs-5"></i>
              </span>
              <h2 class="h5 fw-bold mb-0 section-heading">Formations &amp; Diplômes</h2>
            </div>

            <div class="row g-3">
              <div v-for="(edu, dIdx) in portfolioUser.educations" :key="dIdx" class="col-md-6">
                <div class="p-3 rounded-3 bg-light border h-100">
                  <span class="badge bg-white text-dark border extra-small mb-1">{{ edu.year }}</span>
                  <strong class="d-block small text-dark fw-bold">{{ edu.degree }}</strong>
                  <span class="d-block extra-small text-muted mb-1">{{ edu.institution }}</span>
                  <p v-if="edu.description" class="extra-small text-muted mb-0">{{ edu.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 8. BADGES & XP BLOCK -->
        <section v-else-if="block.id === 'badges' && block.enabled && badges.length" class="portfolio-block mb-4 mb-md-5">
          <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2">
                <span class="section-icon-badge" :style="{ color: accentColor }">
                  <i class="bi bi-trophy-fill fs-5 text-warning"></i>
                </span>
                <h2 class="h5 fw-bold mb-0 section-heading">Distinctions &amp; Badges Tech237</h2>
              </div>
              <span class="badge rounded-pill px-3 py-1 extra-small fw-bold text-white shadow-xs" :style="{ backgroundColor: currentTier?.color || '#00A859' }">
                Rang #{{ rankPosition }} national &bull; {{ portfolioUser.xp || 0 }} XP
              </span>
            </div>

            <div class="row g-3">
              <div v-for="b in badges" :key="b.id" class="col-6 col-md-4">
                <div class="p-3 rounded-4 bg-light border text-center h-100 d-flex flex-column align-items-center justify-content-center">
                  <div class="rounded-circle text-white shadow-xs d-flex align-items-center justify-content-center p-2 mb-2" :style="{ width: '44px', height: '44px', backgroundColor: b.color || '#00A859' }">
                    <i class="bi fs-5" :class="b.icon || 'bi-award-fill'"></i>
                  </div>
                  <strong class="small text-dark d-block mb-0">{{ b.name }}</strong>
                  <small class="extra-small text-muted" style="font-size: 10px;">{{ b.description }}</small>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 9. CUSTOM SECTIONS BLOCK -->
        <section v-else-if="block.id === 'custom' && block.enabled && (portfolioUser.custom_sections && portfolioUser.custom_sections.length)" class="portfolio-block mb-4 mb-md-5">
          <div v-for="(sec, cIdx) in portfolioUser.custom_sections" :key="cIdx" class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <span class="section-icon-badge" :style="{ color: accentColor }">
                <i class="bi" :class="sec.icon || 'bi-stars'"></i>
              </span>
              <h2 class="h5 fw-bold mb-0 section-heading">{{ sec.title || 'Section complémentaire' }}</h2>
            </div>
            <div class="text-content leading-relaxed" style="white-space: pre-line;">
              {{ sec.content }}
            </div>
          </div>
        </section>

        <!-- 10. CONTACT & SOCIALS BLOCK -->
        <section v-else-if="block.id === 'contact' && block.enabled" class="portfolio-block mb-4 mb-md-5">
          <div class="card p-4 p-md-5 rounded-4 shadow-sm border-0 content-card text-center">
            <h2 class="h4 fw-bold mb-2">Restons en contact !</h2>
            <p class="text-muted small mb-4" style="max-width: 500px; margin: 0 auto 1.5rem auto;">
              Vous avez un projet innovant, une opportunité ou souhaitez collaborer ? N'hésitez pas à me contacter directement.
            </p>

            <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap mb-4">
              <a v-if="socialLinks.github" :href="socialLinks.github" target="_blank" class="btn btn-outline-dark rounded-circle p-2 shadow-2xs" style="width: 44px; height: 44px;" title="GitHub">
                <i class="bi bi-github fs-5"></i>
              </a>
              <a v-if="socialLinks.linkedin" :href="socialLinks.linkedin" target="_blank" class="btn btn-outline-primary rounded-circle p-2 shadow-2xs" style="width: 44px; height: 44px;" title="LinkedIn">
                <i class="bi bi-linkedin fs-5"></i>
              </a>
              <a v-if="socialLinks.twitter" :href="socialLinks.twitter" target="_blank" class="btn btn-outline-dark rounded-circle p-2 shadow-2xs" style="width: 44px; height: 44px;" title="Twitter / X">
                <i class="bi bi-twitter-x fs-5"></i>
              </a>
              <a v-if="socialLinks.website" :href="socialLinks.website" target="_blank" class="btn btn-outline-success rounded-circle p-2 shadow-2xs" style="width: 44px; height: 44px;" title="Site Web Personnel">
                <i class="bi bi-globe2 fs-5"></i>
              </a>
              <a v-if="portfolioUser.phone" :href="'https://wa.me/' + cleanPhone(portfolioUser.phone)" target="_blank" class="btn btn-success rounded-circle p-2 shadow-2xs" style="width: 44px; height: 44px; background-color: #25D366; border-color: #25D366;" title="WhatsApp">
                <i class="bi bi-whatsapp fs-5 text-white"></i>
              </a>
              <a v-if="portfolioUser.email" :href="'mailto:' + portfolioUser.email" class="btn btn-outline-danger rounded-circle p-2 shadow-2xs" style="width: 44px; height: 44px;" title="Envoyer un email">
                <i class="bi bi-envelope-fill fs-5"></i>
              </a>
            </div>

            <div class="d-print-none">
              <Link 
                v-if="$page.props.auth?.user && $page.props.auth.user.id !== portfolioUser.id" 
                :href="'/espace-utilisateur?section=messages&recipient=' + portfolioUser.id"
                class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm"
                :style="{ backgroundColor: accentColor }"
              >
                <i class="bi bi-chat-left-dots-fill me-1"></i> Envoyer un message privé
              </Link>
            </div>
          </div>
        </section>

      </template>

      <!-- Footer Signature (Both screen and print) -->
      <footer class="text-center py-4 text-muted small mt-4 border-top">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
          <img src="/assets/images/logo.png" alt="Tech237" style="height: 20px; object-fit: contain;">
          <span class="fw-bold">Tech237 Community</span>
        </div>
        <p class="extra-small text-muted mb-0">
          Portfolio &amp; CV certifié généré sur Tech237 &bull; Là où les idées deviennent des projets au Cameroun.
        </p>
      </footer>

    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
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

const layout = computed(() => {
  return props.portfolioUser.portfolio_layout || {}
})

const currentTheme = computed(() => layout.value.theme || 'emerald')
const currentCardStyle = computed(() => layout.value.card_style || 'rounded')
const accentColor = computed(() => layout.value.accent_color || '#00A859')
const heroStyle = computed(() => layout.value.hero_style || 'centered')
const projectsStyle = computed(() => layout.value.projects_style || 'cards')
const skillsStyle = computed(() => layout.value.skills_style || 'chips')

const cssVars = computed(() => {
  return {
    '--accent-color': accentColor.value,
  }
})

// Dynamic Ordered Blocks
const defaultBlocks = [
  { id: 'hero', name: 'En-tête & Présentation', enabled: true },
  { id: 'about', name: 'À propos & Parcours', enabled: true },
  { id: 'skills', name: 'Compétences & Stack technique', enabled: true },
  { id: 'projects', name: 'Projets Tech en vedette', enabled: true },
  { id: 'pocs', name: 'Mini-Actus & POCs Tech237', enabled: true },
  { id: 'experiences', name: 'Expériences professionnelles', enabled: true },
  { id: 'educations', name: 'Formations & Diplômes', enabled: true },
  { id: 'badges', name: 'Trophées & Badges Tech237', enabled: true },
  { id: 'custom', name: 'Sections personnalisées libres', enabled: true },
  { id: 'contact', name: 'Coordonnées & Réseaux', enabled: true },
]

const orderedBlocks = computed(() => {
  if (Array.isArray(layout.value.blocks) && layout.value.blocks.length > 0) {
    return layout.value.blocks
  }
  return defaultBlocks
})

const socialLinks = computed(() => {
  return props.portfolioUser.social_links || {}
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
  const text = encodeURIComponent(`Découvrez le portfolio tech de ${props.portfolioUser.name} sur Tech237 : ${shareUrl.value}`)
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
  const map = {
    freelance: 'bg-success text-white',
    full_time: 'bg-primary text-white',
    open: 'bg-info-subtle text-info border border-info',
    not_available: 'bg-secondary-subtle text-secondary',
  }
  return map[val] || 'bg-success text-white'
}

function getSkillBg(idx) {
  const colors = ['#ecfdf5', '#eff6ff', '#fef3c7', '#f3e8ff', '#fce7f3', '#e0f2fe']
  return colors[idx % colors.length]
}

function getSkillColor(idx) {
  const colors = ['#065f46', '#1e40af', '#92400e', '#6b21a8', '#9d174d', '#075985']
  return colors[idx % colors.length]
}

function getSkillPercent(skill) {
  if (typeof skill === 'object' && skill.percent) return skill.percent
  return 85
}
</script>

<style scoped>
/* Base themes */
.portfolio-root {
  background-color: #f8fafc;
  color: #0f172a;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* Theme Emerald */
.theme-emerald {
  background-color: #f0fdf4;
}
.theme-emerald .hero-box {
  background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
  border: 1px solid #dcfce7;
}

/* Theme Dark / Cyberpunk */
.theme-dark {
  background-color: #0b0f19;
  color: #f1f5f9;
}
.theme-dark .portfolio-nav {
  background-color: rgba(11, 15, 25, 0.85);
  border-bottom: 1px solid #1e293b;
}
.theme-dark .nav-brand-text, .theme-dark .user-name-heading, .theme-dark .section-heading, .theme-dark .text-dark {
  color: #ffffff !important;
}
.theme-dark .content-card, .theme-dark .hero-box {
  background-color: #111827 !important;
  border: 1px solid #1f2937 !important;
  color: #f3f4f6;
}
.theme-dark .text-muted, .theme-dark .text-content {
  color: #94a3b8 !important;
}
.theme-dark .bg-light {
  background-color: #1e293b !important;
  color: #f1f5f9 !important;
}

/* Theme Navy */
.theme-navy {
  background-color: #0f172a;
  color: #f8fafc;
}
.theme-navy .content-card, .theme-navy .hero-box {
  background-color: #1e293b !important;
  border: 1px solid #334155 !important;
  color: #f8fafc;
}
.theme-navy .text-dark, .theme-navy .user-name-heading {
  color: #f8fafc !important;
}
.theme-navy .text-muted {
  color: #94a3b8 !important;
}

/* Theme Sunset */
.theme-sunset {
  background-color: #fffbeb;
}
.theme-sunset .hero-box {
  background: linear-gradient(135deg, #ffffff 0%, #fef3c7 100%);
  border: 1px solid #fde68a;
}

/* Theme Purple */
.theme-purple {
  background-color: #faf5ff;
}
.theme-purple .hero-box {
  background: linear-gradient(135deg, #ffffff 0%, #f3e8ff 100%);
  border: 1px solid #e9d5ff;
}

/* Theme Minimal */
.theme-minimal {
  background-color: #ffffff;
}

/* Card Styles */
.card-style-rounded .card, .card-style-rounded .hero-box {
  border-radius: 24px !important;
}
.card-style-sharp .card, .card-style-sharp .hero-box {
  border-radius: 6px !important;
}
.card-style-bordered .card, .card-style-bordered .hero-box {
  border: 2px solid #cbd5e1 !important;
  box-shadow: 4px 4px 0 rgba(0,0,0,0.06) !important;
}

/* Avatar sizes */
.hero-avatar {
  width: 120px;
  height: 120px;
  object-fit: cover;
}
.hero-avatar-sq {
  width: 140px;
  height: 140px;
  object-fit: cover;
}

.status-pulse-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
  background-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
}

.timeline-dot {
  width: 12px;
  height: 12px;
  box-shadow: 0 0 0 4px rgba(0, 168, 89, 0.2);
}

.btn-nav-action {
  background-color: rgba(255, 255, 255, 0.85);
  border: 1px solid #e2e8f0;
  color: #1e293b;
}
.btn-nav-action:hover {
  background-color: #ffffff;
  color: #0f172a;
}

.terminal-cursor {
  animation: blink 1s infinite;
}
@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}

/* Print CV Styles */
@media print {
  .d-print-none, .portfolio-nav {
    display: none !important;
  }
  .portfolio-root {
    background: #ffffff !important;
    color: #000000 !important;
  }
  .card, .hero-box {
    box-shadow: none !important;
    border: 1px solid #e2e8f0 !important;
    page-break-inside: avoid;
    margin-bottom: 1.5rem !important;
  }
  .container {
    max-width: 100% !important;
    padding: 0 !important;
  }
}
</style>
