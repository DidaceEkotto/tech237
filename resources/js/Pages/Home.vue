<template>
  <FrontLayout>
    <Head>
      <title>Tech237 - Là où les idées deviennent des projets | Média Tech, Startups & Innovation</title>
      <meta name="description" content="Tech237 - Là où les idées deviennent des projets. Toute l'actualité tech, intelligence artificielle, startups, code, gaming et recrutement IT au Cameroun." />
      <meta property="og:type" content="website" />
      <meta property="og:title" content="Tech237 - Là où les idées deviennent des projets" />
      <meta property="og:description" content="Le média de référence de la tech, des startups et de l'innovation au Cameroun et en Afrique." />
      <meta property="og:image" content="/assets/images/logo.png" />
    </Head>

    <div class="container my-4">

      <!-- 1. TOP HERO: SLIDER (LEFT - 8 cols) + ENTREPRENEUR DU MOIS (RIGHT - 4 cols) -->
      <section class="mb-5">
        <div class="row g-4 align-items-stretch">
          
          <!-- Column 1: Slider (Larger - 8 cols) -->
          <div :class="(entrepreneurOfMonth || mostReadArticle) ? 'col-lg-8' : 'col-12'">
            <div v-if="sliders && sliders.length" class="carousel-wrapper h-100 rounded-4 overflow-hidden position-relative shadow-sm border">
              <div 
                class="carousel-inner-slider d-flex h-100" 
                :style="{ transform: `translateX(-${currentSlide * 100}%)`, transition: 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)' }"
              >
                <div 
                  v-for="slide in sliders" 
                  :key="slide.id" 
                  class="carousel-slide-item flex-shrink-0 w-100 position-relative"
                  style="min-height: 440px; height: 100%;"
                >
                  <img :src="'/' + slide.image" :alt="slide.title_fr" class="w-100 h-100" style="object-fit: cover;">
                  <div class="carousel-caption-overlay d-flex flex-column justify-content-end p-4 p-md-5" style="background: linear-gradient(180deg, transparent 0%, rgba(6, 21, 45, 0.75) 45%, rgba(6, 21, 45, 0.98) 100%);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <span class="badge px-3 py-1 fw-bold text-white text-uppercase" style="background-color: var(--tech-green);">
                        <i class="bi bi-lightning-charge-fill me-1"></i> À LA UNE
                      </span>
                      <span class="badge bg-white text-dark px-2 py-1 rounded-pill small fw-semibold">
                        Tech237 Exclusif
                      </span>
                    </div>
                    <h2 class="display-6 fw-bold text-white mb-2" style="font-size: 1.65rem;">
                      {{ locale === 'en' ? (slide.title_en || slide.title_fr) : slide.title_fr }}
                    </h2>
                    <p v-if="slide.subtitle_fr" class="text-white-50 small mb-3 d-none d-md-block">
                      {{ locale === 'en' ? (slide.subtitle_en || slide.subtitle_fr) : slide.subtitle_fr }}
                    </p>
                    <div v-if="slide.link_url">
                      <a :href="slide.link_url" class="btn btn-sm px-4 py-2 rounded-pill fw-bold text-white shadow-sm" style="background-color: var(--tech-green);">
                        {{ locale === 'en' ? (slide.button_text_en || 'Discover') : (slide.button_text_fr || 'Découvrir') }} &rarr;
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Carousel Controls -->
              <button @click="prevSlide" class="carousel-control-btn prev-btn">
                <i class="bi bi-chevron-left"></i>
              </button>
              <button @click="nextSlide" class="carousel-control-btn next-btn">
                <i class="bi bi-chevron-right"></i>
              </button>

              <!-- Carousel Dots -->
              <div class="carousel-dots-wrapper d-flex gap-2 justify-content-center">
                <span 
                  v-for="(slide, idx) in sliders" 
                  :key="idx" 
                  @click="currentSlide = idx"
                  class="carousel-dot"
                  :class="{ 'active': currentSlide === idx }"
                ></span>
              </div>
            </div>
          </div>

          <!-- Column 2: Entrepreneur du mois (Right - 4 cols), fallback to most read article -->
          <div v-if="entrepreneurOfMonth || mostReadArticle" class="col-lg-4 d-flex">
            <!-- Case A: Entrepreneur du mois -->
            <div v-if="entrepreneurOfMonth" class="card border-0 rounded-4 shadow-sm w-100 overflow-hidden d-flex flex-column justify-content-between p-4 text-white position-relative" style="background: linear-gradient(145deg, #0B2559 0%, #061533 100%);">
              <div class="position-relative z-1">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase text-white shadow-sm" style="background-color: var(--tech-green); font-size: 11px;">
                    <i class="bi bi-star-fill text-warning me-1"></i> ENTREPRENEUR DU MOIS
                  </span>
                  <span class="text-white-50 extra-small fw-semibold">{{ entrepreneurOfMonth.month_year }}</span>
                </div>

                <div class="text-center my-3">
                  <div class="position-relative d-inline-block">
                    <img 
                      :src="'/' + (entrepreneurOfMonth.photo || 'assets/images/author-default.png')" 
                      :alt="entrepreneurOfMonth.name" 
                      class="rounded-circle shadow-lg border border-3 border-success"
                      style="width: 105px; height: 105px; object-fit: cover;"
                    >
                    <span class="badge position-absolute bottom-0 end-0 px-2 py-1 rounded-pill fw-bold text-white shadow" style="background-color: var(--tech-green); font-size: 10px;">
                      <i class="bi bi-award-fill"></i>
                    </span>
                  </div>
                  <h4 class="fw-bold text-white mb-1 mt-2 fs-5">{{ entrepreneurOfMonth.name }}</h4>
                  <div class="small fw-semibold mb-2" style="color: var(--tech-green-light);">{{ entrepreneurOfMonth.role_title || entrepreneurOfMonth.role_or_title }}</div>
                  <span v-if="entrepreneurOfMonth.startup" class="badge bg-white text-dark rounded-pill px-2 py-1 extra-small">
                    <i class="bi bi-building me-1 text-success"></i> {{ entrepreneurOfMonth.startup.name }}
                  </span>
                </div>

                <p class="text-white-50 small mb-3 text-center" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ entrepreneurOfMonth.bio_fr }}
                </p>
              </div>

              <div class="pt-3 border-top border-white border-opacity-10 text-center">
                <Link href="/entrepreneur-du-mois" class="btn rounded-pill w-100 py-2 fw-bold text-white shadow-sm" style="background-color: var(--tech-green);">
                  Découvrir son parcours &rarr;
                </Link>
              </div>
            </div>

            <!-- Case B: Fallback - Most Read Article -->
            <div v-else-if="mostReadArticle" class="card border-0 rounded-4 shadow-sm w-100 overflow-hidden d-flex flex-column justify-content-between p-3 bg-white">
              <div>
                <span class="badge px-3 py-1 rounded-pill fw-bold text-uppercase text-white mb-2" style="background-color: var(--tech-green); font-size: 11px;">
                  <i class="bi bi-fire text-warning me-1"></i> ARTICLE LE PLUS LU
                </span>
                <div class="rounded-3 overflow-hidden position-relative my-2" style="height: 180px;">
                  <img :src="'/' + (mostReadArticle.featured_image || 'storage/images/gta6_hero.jpg')" class="w-100 h-100 object-fit-cover">
                  <span class="badge bg-dark text-white position-absolute bottom-0 end-0 m-2 extra-small">
                    <i class="bi bi-eye-fill me-1"></i> {{ mostReadArticle.views_count }} vues
                  </span>
                </div>
                <h5 class="fw-bold text-dark mt-2 mb-2 fs-6">
                  <Link :href="'/article/' + mostReadArticle.slug" class="text-dark text-decoration-none">
                    {{ mostReadArticle.title_fr }}
                  </Link>
                </h5>
                <p class="text-muted small mb-0" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ mostReadArticle.summary_fr }}
                </p>
              </div>

              <div class="pt-3 border-top mt-3">
                <Link :href="'/article/' + mostReadArticle.slug" class="btn btn-outline-dark rounded-pill w-100 fw-bold btn-sm">
                  Lire l'article &rarr;
                </Link>
              </div>
            </div>
          </div>

        </div>
      </section>

      <!-- 2. MAIN LAYOUT: HERO STORY + SECONDARY GRID + RIGHT SIDEBAR -->
      <div class="row g-4 mb-5">
        
        <!-- Left 8 Columns -->
        <div class="col-lg-8">
          
          <!-- Top Hero Story Card with Text Overlay -->
          <div v-if="heroArticles[0]" class="mb-4">
            <Link :href="'/article/' + heroArticles[0].slug" class="text-decoration-none">
              <div class="hero-top-story position-relative rounded-4 overflow-hidden shadow-sm border" style="height: 400px;">
                <img 
                  :src="'/' + (heroArticles[0].featured_image || 'storage/images/chatgpt_hero.jpg')" 
                  :alt="heroArticles[0].title_fr" 
                  class="w-100 h-100" 
                  style="object-fit: cover;"
                >
                <div class="hero-top-overlay p-4 d-flex flex-column justify-content-end" style="background: linear-gradient(180deg, transparent 0%, rgba(6, 21, 45, 0.7) 40%, rgba(6, 21, 45, 0.98) 95%);">
                  <span class="badge px-3 py-1 mb-2 fw-bold text-white text-uppercase align-self-start" style="background-color: var(--tech-green);">
                    <i class="bi bi-stars me-1"></i> {{ heroArticles[0].category?.name_fr || 'HIGH-TECH' }}
                  </span>
                  <h3 class="fw-bold text-white mb-2">
                    {{ locale === 'en' ? (heroArticles[0].title_en || heroArticles[0].title_fr) : heroArticles[0].title_fr }}
                  </h3>
                  <div class="small text-white-50 d-flex align-items-center gap-2">
                    <span><i class="bi bi-pen me-1"></i>{{ heroArticles[0].author?.name || 'Rédaction Tech237' }}</span>
                    &bull;
                    <span><i class="bi bi-clock me-1"></i>{{ heroArticles[0].date_formatted }}</span>
                  </div>
                </div>
              </div>
            </Link>
          </div>

          <!-- 4 Secondary Grid Cards (2x2) with Tech Emerald Green Badges -->
          <div class="row g-3 mb-5">
            <div v-for="art in heroArticles.slice(1, 5)" :key="art.id" class="col-md-6">
              <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden tech237-card">
                <Link :href="'/article/' + art.slug" class="overflow-hidden">
                  <img 
                    :src="'/' + (art.featured_image || 'storage/images/gta6_hero.jpg')" 
                    :alt="art.title_fr" 
                    class="card-img-top" 
                    style="height: 190px; object-fit: cover; transition: transform 0.3s ease;"
                  >
                </Link>
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <div>
                    <span class="badge fw-bold mb-2 text-uppercase text-white" style="background-color: var(--tech-green); font-size: 11px;">
                      {{ art.category?.name_fr || 'ACTUALITÉ' }}
                    </span>
                    <h6 class="card-title fw-bold" style="color: var(--tech-navy);">
                      <Link :href="'/article/' + art.slug" class="text-decoration-none" style="color: inherit;">
                        {{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}
                      </Link>
                    </h6>
                  </div>
                  <div class="extra-small text-muted mt-2">
                    <i class="bi bi-calendar3 me-1"></i>{{ art.date_formatted }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: HIGH-TECH & IA -->
          <div v-if="techArticles && techArticles.length" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-pill p-1 px-2" style="background: var(--tech-green); color: #fff;">
                  <i class="bi bi-cpu-fill"></i>
                </div>
                <h4 class="fw-bold text-uppercase mb-0" style="color: var(--tech-navy);">HIGH-TECH & INNOVATIONS</h4>
              </div>
              <Link href="/category/high-tech" class="small text-muted text-decoration-none fw-semibold">
                {{ locale === 'en' ? 'View all' : 'Voir tout' }} &rarr;
              </Link>
            </div>
            <div class="row g-3">
              <div v-for="art in techArticles" :key="art.id" class="col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden tech237-card">
                  <Link :href="'/article/' + art.slug">
                    <img :src="'/' + art.featured_image" class="card-img-top" style="height: 170px; object-fit: cover;">
                  </Link>
                  <div class="card-body p-3">
                    <span class="badge fw-bold mb-2 text-white" style="background-color: var(--tech-green); font-size: 10px;">HIGH-TECH</span>
                    <h6 class="card-title fw-bold mb-0">
                      <Link :href="'/article/' + art.slug" class="text-decoration-none" style="color: var(--tech-navy);">
                        {{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}
                      </Link>
                    </h6>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: SÉRIES & CULTURE DIGITALE -->
          <div v-if="seriesArticles && seriesArticles.length" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-pill p-1 px-2" style="background: var(--tech-navy); color: #fff;">
                  <i class="bi bi-tv-fill"></i>
                </div>
                <h4 class="fw-bold text-uppercase mb-0" style="color: var(--tech-navy);">SÉRIES & STREAMING</h4>
              </div>
              <Link href="/category/series" class="small text-muted text-decoration-none fw-semibold">
                {{ locale === 'en' ? 'View all' : 'Voir tout' }} &rarr;
              </Link>
            </div>
            <div class="row g-3">
              <div v-for="art in seriesArticles" :key="art.id" class="col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden tech237-card">
                  <Link :href="'/article/' + art.slug">
                    <img :src="'/' + art.featured_image" class="card-img-top" style="height: 170px; object-fit: cover;">
                  </Link>
                  <div class="card-body p-3">
                    <span class="badge fw-bold mb-2 text-white" style="background-color: var(--tech-navy); font-size: 10px;">SÉRIES</span>
                    <h6 class="card-title fw-bold mb-0">
                      <Link :href="'/article/' + art.slug" class="text-decoration-none" style="color: var(--tech-navy);">
                        {{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}
                      </Link>
                    </h6>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: GAMING & TESTS JEUX VIDÉO -->
          <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-pill p-1 px-2" style="background: var(--tech-green); color: #fff;">
                  <i class="bi bi-controller"></i>
                </div>
                <h4 class="fw-bold text-uppercase mb-0" style="color: var(--tech-navy);">GAMING & LAB TESTS</h4>
              </div>
              <Link href="/tests-jeux-video" class="small text-muted text-decoration-none fw-semibold">
                Tous les tests &rarr;
              </Link>
            </div>
            <div class="row g-3">
              <div v-for="gr in gameReviews" :key="gr.id" class="col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden tech237-card">
                  <div class="position-relative">
                    <Link :href="'/test-jeu/' + gr.slug">
                      <img :src="'/' + (gr.cover_image || 'storage/images/game_default.jpg')" class="card-img-top" style="height: 170px; object-fit: cover;">
                    </Link>
                    <span class="badge position-absolute top-0 end-0 m-2 fw-bold fs-6 shadow-sm text-white" style="background-color: var(--tech-green);">
                      {{ gr.final_score }}/20
                    </span>
                  </div>
                  <div class="card-body p-3">
                    <span class="badge text-dark fw-bold mb-2 bg-light border" style="font-size: 10px;">
                      {{ gr.platform_category }}
                    </span>
                    <h6 class="card-title fw-bold mb-0">
                      <Link :href="'/test-jeu/' + gr.slug" class="text-decoration-none" style="color: var(--tech-navy);">
                        {{ gr.game_name }}
                      </Link>
                    </h6>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: L'INTERVIEW DU JOUR TECH237 -->
          <div v-if="dailyInterview" class="mb-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-white position-relative" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
              <div class="row g-0 align-items-center">
                <!-- Interviewee Photo Column -->
                <div class="col-md-5 position-relative">
                  <img 
                    :src="'/' + (dailyInterview.cover_image || 'storage/images/interview_default.jpg')" 
                    :alt="dailyInterview.interviewee_name" 
                    class="w-100 h-100 object-fit-cover"
                    style="min-height: 290px; max-height: 350px;"
                  >
                  <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(180deg, transparent 0%, rgba(6, 21, 45, 0.95) 100%);">
                    <span class="badge px-3 py-1 fw-bold text-white text-uppercase shadow-sm" style="background-color: var(--tech-green);">
                      <i class="bi bi-mic-fill me-1"></i> INTERVIEW DU JOUR
                    </span>
                  </div>
                </div>

                <!-- Interview Details Column -->
                <div class="col-md-7 p-4 p-lg-5">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-danger text-uppercase">EXCLUSIF TECH237</span>
                    <span class="text-white-50 small">&bull; {{ dailyInterview.interviewee_role || 'Fondateur & Innovateur' }}</span>
                  </div>
                  <h3 class="fw-bold mb-2 text-white">
                    {{ dailyInterview.interviewee_name }}
                  </h3>
                  <h5 class="fw-semibold text-warning mb-3">
                    "{{ locale === 'en' ? (dailyInterview.title_en || dailyInterview.title_fr) : dailyInterview.title_fr }}"
                  </h5>
                  <p class="small text-white-50 mb-4 line-clamp-2">
                    {{ locale === 'en' ? (dailyInterview.summary_en || dailyInterview.summary_fr) : (dailyInterview.summary_fr || 'Découvrez la vision, les obstacles surmontés et les défis tech relevés par nos invités.') }}
                  </p>
                  <Link :href="'/interview/' + dailyInterview.slug" class="btn rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2 text-white shadow-sm" style="background-color: var(--tech-green);">
                    <i class="bi bi-play-circle-fill fs-5"></i> Lire et visionner l'interview
                  </Link>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Right 4 Columns: Tech237 Sidebar -->
        <div class="col-lg-4">
          
          <!-- 1. Dark Newsletter Card (Tech Navy + Tech Green) -->
          <div class="card text-white rounded-4 p-4 mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="rounded-circle d-flex align-items-center justify-content-center p-2 text-white" style="background: var(--tech-green); width: 34px; height: 34px;">
                <i class="bi bi-envelope-fill"></i>
              </div>
              <h5 class="fw-bold mb-0">NEWSLETTER TECH237</h5>
            </div>
            <p class="small text-white-50 mb-3">Le condensé hebdomadaire des levées de fonds, recrutements et projets tech au Cameroun.</p>
            <form @submit.prevent="submitSidebarNewsletter">
              <div class="input-group mb-2">
                <span class="input-group-text bg-white bg-opacity-10 border-0 text-white"><i class="bi bi-at"></i></span>
                <input type="email" v-model="sidebarEmail" class="form-control bg-white bg-opacity-10 text-white border-0" placeholder="Votre adresse email" required>
              </div>
              <button 
                type="submit" 
                class="btn w-100 fw-bold rounded-pill text-white mt-2 shadow-sm" 
                style="background-color: var(--tech-green);"
              >
                <i class="bi bi-send-fill me-1"></i> Je m'abonne
              </button>
              <div v-if="sidebarSubscribed" class="small text-success mt-2">
                <i class="bi bi-check-circle-fill me-1"></i> Merci pour votre inscription !
              </div>
            </form>
          </div>

          <!-- 2. "En ce moment" Trending Box -->
          <div class="card rounded-4 p-4 mb-4 border shadow-sm bg-white">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-fire text-danger me-1"></i>En ce moment
              </h5>
              <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill extra-small fw-bold">Live</span>
            </div>
            <div class="list-group list-group-flush small">
              <Link 
                v-for="(trend, idx) in trendingArticles" 
                :key="trend.id" 
                :href="'/article/' + trend.slug" 
                class="list-group-item px-0 py-2 text-decoration-none fw-semibold d-flex align-items-start gap-2 border-bottom"
                style="color: var(--tech-navy);"
              >
                <span class="badge rounded-circle p-2 d-flex align-items-center justify-content-center text-white" style="background: var(--tech-navy); width: 22px; height: 22px; font-size: 11px;">
                  {{ idx + 1 }}
                </span>
                <span>{{ locale === 'en' ? (trend.title_en || trend.title_fr) : trend.title_fr }}</span>
              </Link>
            </div>
          </div>

          <!-- 3. Community Showcase Callout (Tech237: Là où les idées deviennent des projets) -->
          <div class="card rounded-4 p-4 mb-4 border-0 shadow-sm text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0B2559 0%, #00A859 100%);">
            <span class="badge bg-white text-dark align-self-start fw-bold mb-2 px-3 py-1 rounded-pill">
              🚀 INCUBATEUR & SHOWCASE
            </span>
            <h5 class="fw-bold mb-2">Vous développez un projet tech au Cameroun ?</h5>
            <p class="small text-white-50 mb-3">
              <em>"Là où les idées deviennent des projets"</em>. Enregistrez votre startup SARL/SA et vos applications pour attirer des partenaires et investisseurs.
            </p>
            <Link href="/register" class="btn btn-light rounded-pill px-4 btn-sm fw-bold shadow-sm" style="color: var(--tech-navy);">
              Rejoindre la communauté &rarr;
            </Link>
          </div>

          <!-- Sidebar Ad Banner (Pavé 300x250) -->
          <div v-if="sidebarAd" class="card rounded-4 p-3 mb-4 border-0 shadow-sm text-center bg-light">
            <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 9px; letter-spacing: 1px;">Sponsor &bull; Partenaire</div>
            <a v-if="sidebarAd.image" :href="sidebarAd.link_url || '#'" target="_blank" rel="noopener noreferrer">
              <img :src="'/' + sidebarAd.image" :alt="sidebarAd.title || 'Publicité'" class="img-fluid rounded-3 shadow-sm w-100" style="max-height: 280px; object-fit: cover;">
            </a>
            <div v-else-if="sidebarAd.custom_code" v-html="sidebarAd.custom_code"></div>
          </div>

          <!-- 4. "NOUS SUIVRE" Social Box -->
          <div class="card text-white rounded-4 p-4 mb-4 border-0 shadow-sm text-center" style="background-color: var(--tech-navy-dark);">
            <h6 class="fw-bold text-uppercase mb-2" style="letter-spacing: 1px;">SUIVRE TECH237</h6>
            <p class="small text-white-50 mb-3">Rejoignez la plus grande communauté numérique du 237.</p>
            <div class="d-flex justify-content-center gap-3 fs-5">
              <a :href="siteSettings.social_facebook || '#'" target="_blank" class="text-white"><i class="bi bi-facebook"></i></a>
              <a :href="siteSettings.social_twitter || '#'" target="_blank" class="text-white"><i class="bi bi-twitter-x"></i></a>
              <a :href="siteSettings.social_instagram || '#'" target="_blank" class="text-white"><i class="bi bi-instagram"></i></a>
              <a :href="siteSettings.social_tiktok || '#'" target="_blank" class="text-white"><i class="bi bi-tiktok"></i></a>
              <a :href="siteSettings.social_youtube || '#'" target="_blank" class="text-white"><i class="bi bi-youtube"></i></a>
            </div>
          </div>

        </div>

      </div>

      <!-- Home Middle Ad Banner (Milieu de la page d'accueil) -->
      <div v-if="homeMiddleAd" class="home-middle-banner my-5 text-center">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-light overflow-hidden">
          <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 9px; letter-spacing: 1px;">Publicité &bull; Partenaire Tech237</div>
          <a v-if="homeMiddleAd.image" :href="homeMiddleAd.link_url || '#'" target="_blank" rel="noopener noreferrer">
            <img :src="'/' + homeMiddleAd.image" :alt="homeMiddleAd.title || 'Publicité'" class="img-fluid rounded-3 shadow-sm w-100" style="max-height: 180px; object-fit: cover;">
          </a>
          <div v-else-if="homeMiddleAd.custom_code" v-html="homeMiddleAd.custom_code"></div>
        </div>
      </div>

      <!-- CONDITIONAL SECTION A: ÉVÉNEMENTS & CONFÉRENCES TECH -->
      <section v-if="upcomingEvents && upcomingEvents.length > 0" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <div>
            <span class="badge px-3 py-1 rounded-pill fw-bold text-uppercase text-white mb-1" style="background-color: var(--tech-green); font-size: 11px;">
              <i class="bi bi-calendar-event me-1"></i> AGENDA TECH
            </span>
            <h3 class="fw-bold text-dark mb-0">Conférences &amp; Événements Tech</h3>
          </div>
          <Link href="/conferences" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold">
            Tous les événements &rarr;
          </Link>
        </div>

        <div class="row g-4">
          <div v-for="conf in upcomingEvents" :key="conf.id" class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white tech237-hover-lift">
              <div class="position-relative" style="height: 180px;">
                <img 
                  :src="'/' + (conf.cover_image || 'assets/images/default-conf.jpg')" 
                  class="w-100 h-100 object-fit-cover"
                >
                <span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fw-bold text-white shadow-sm" style="background-color: var(--tech-navy);">
                  <i class="bi bi-calendar3 me-1 text-warning"></i> {{ new Date(conf.event_date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) }}
                </span>
                <span v-if="conf.city" class="badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 start-0 m-3 rounded-pill extra-small">
                  <i class="bi bi-geo-alt-fill me-1 text-danger"></i> {{ conf.city }}
                </span>
              </div>
              <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                  <h5 class="fw-bold text-dark mb-2 fs-6">
                    <Link :href="'/conference/' + (conf.slug || conf.id)" class="text-dark text-decoration-none">
                      {{ locale === 'en' ? (conf.title_en || conf.title_fr) : conf.title_fr }}
                    </Link>
                  </h5>
                  <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ locale === 'en' ? (conf.description_en || conf.description_fr) : conf.description_fr }}
                  </p>
                </div>
                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                  <span class="text-muted extra-small">
                    <i class="bi bi-people me-1"></i> {{ conf.registrations_count || 0 }} inscrits
                  </span>
                  <Link :href="'/conference/' + (conf.slug || conf.id)" class="btn btn-sm rounded-pill px-3 fw-bold text-white" style="background-color: var(--tech-green);">
                    Participer &rarr;
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- CONDITIONAL SECTION B: STARTUPS DU 237 & PROJET EN COURS -->
      <section v-if="featuredStartups && featuredStartups.length > 0" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <div>
            <span class="badge px-3 py-1 rounded-pill fw-bold text-uppercase text-white mb-1" style="background-color: var(--tech-navy); font-size: 11px;">
              <i class="bi bi-rocket-takeoff me-1 text-warning"></i> ÉCOSYSTÈME STARTUPS
            </span>
            <h3 class="fw-bold text-dark mb-0">Startups Camerounaises &amp; Projets Développés</h3>
          </div>
          <Link href="/startups" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold">
            Annuaire des startups &rarr;
          </Link>
        </div>

        <div class="row g-4">
          <div v-for="st in featuredStartups" :key="st.id" class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-column justify-content-between tech237-hover-lift">
              <div>
                <div class="d-flex align-items-center gap-3 mb-3">
                  <img 
                    :src="'/' + (st.logo || 'assets/images/default-startup.png')" 
                    class="rounded-3 border shadow-sm" 
                    style="width: 52px; height: 52px; object-fit: cover;"
                  >
                  <div class="overflow-hidden">
                    <h6 class="fw-bold text-dark mb-0 text-truncate">{{ st.name }}</h6>
                    <small class="text-muted">{{ st.city || 'Cameroun' }} &bull; <span class="badge bg-light text-dark border">{{ st.legal_type || 'SARL' }}</span></small>
                  </div>
                </div>

                <p class="text-secondary small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ stripHtml(st.description) }}
                </p>

                <!-- Project currently worked on -->
                <div class="p-2 bg-light rounded-3 border mb-3">
                  <small class="text-muted d-block fw-bold extra-small text-uppercase mb-1">
                    <i class="bi bi-gear-wide-connected me-1 text-success"></i> Projet en cours :
                  </small>
                  <div v-if="st.projects && st.projects.length > 0">
                    <Link :href="'/projet/' + (st.projects[0].slug || st.projects[0].id)" class="text-decoration-none fw-bold small text-dark d-flex align-items-center justify-content-between">
                      <span class="text-truncate">{{ st.projects[0].name }}</span>
                      <i class="bi bi-arrow-right-short text-success fs-5"></i>
                    </Link>
                  </div>
                  <div v-else class="text-muted extra-small fst-italic">
                    Projet en phase d'idéation / R&amp;D
                  </div>
                </div>
              </div>

              <div class="border-top pt-2">
                <Link :href="'/startups/' + (st.slug || st.id)" class="btn btn-sm btn-outline-dark rounded-pill w-100 fw-bold">
                  Voir la startup
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- CONDITIONAL SECTION C: PROJETS TECH & INNOVATIONS -->
      <section v-if="featuredProjects && featuredProjects.length > 0" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <div>
            <span class="badge px-3 py-1 rounded-pill fw-bold text-uppercase text-white mb-1" style="background-color: var(--tech-green); font-size: 11px;">
              <i class="bi bi-code-slash me-1"></i> VITRINE TECH
            </span>
            <h3 class="fw-bold text-dark mb-0">Projets &amp; Applications Conçus au 237</h3>
          </div>
          <Link href="/projets" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold">
            Explorer tous les projets &rarr;
          </Link>
        </div>

        <div class="row g-4">
          <div v-for="proj in featuredProjects" :key="proj.id" class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white tech237-hover-lift">
              <div class="position-relative" style="height: 140px;">
                <img 
                  :src="'/' + (proj.banner || 'assets/images/default-event.jpg')" 
                  class="w-100 h-100 object-fit-cover"
                >
                <span class="badge position-absolute top-0 end-0 m-2 rounded-pill text-white shadow-sm" style="background-color: var(--tech-green, #00A859); font-size: 10px;">
                  {{ formatProjectType(proj) }}
                </span>
                <!-- Logo superposé sur la bannière -->
                <div class="position-absolute" style="bottom: -18px; left: 16px; z-index: 3;">
                  <div class="rounded-3 border border-2 border-white shadow-sm overflow-hidden bg-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <img 
                      v-if="proj.logo" 
                      :src="proj.logo.startsWith('/') ? proj.logo : '/' + proj.logo" 
                      :alt="proj.name" 
                      class="w-100 h-100 object-fit-contain p-1"
                    >
                    <div v-else class="fw-bold text-success fs-6">
                      {{ (proj.name || 'P').charAt(0).toUpperCase() }}
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body p-3 pt-4 d-flex flex-column justify-content-between">
                <div>
                  <h6 class="fw-bold text-dark mb-1 text-truncate mt-1">
                    <Link :href="'/projet/' + (proj.slug || proj.id)" class="text-dark text-decoration-none">
                      {{ proj.name }}
                    </Link>
                  </h6>
                  <p class="text-muted extra-small mb-2" v-if="proj.startup">
                    Par <strong class="text-dark">{{ proj.startup.name }}</strong>
                  </p>
                  <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 38px;">
                    {{ formatCardDescription(proj.description) }}
                  </p>
                </div>
                <div class="border-top pt-2">
                  <Link :href="'/projet/' + (proj.slug || proj.id)" class="btn btn-sm rounded-pill w-100 fw-bold text-white" style="background-color: var(--tech-green);">
                    Découvrir le projet
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 3. DARK BLOCK: "DOSSIERS THÉMATIQUES & INNOVATIONS" -->
      <section v-if="dossiers && dossiers.length" class="p-4 p-md-5 rounded-4 text-white mb-5 shadow" style="background: linear-gradient(135deg, #06152D 0%, #0B2559 100%);">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <div>
            <h3 class="fw-bold mb-1 text-uppercase d-flex align-items-center gap-2">
              <i class="bi bi-folder-check text-success"></i> DOSSIERS TECH237 À SUIVRE
            </h3>
            <p class="text-white-50 small mb-0">Les analyses thématiques de fond : IA, fintech, cybersécurité, mobile money et cloud.</p>
          </div>
          <Link href="/index-dossiers" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-bold">
            Tous les dossiers &rarr;
          </Link>
        </div>

        <div class="row g-3">
          <div v-for="dos in dossiers" :key="dos.id" class="col-6 col-md-4 col-lg-2">
            <Link :href="'/dossier/' + dos.slug" class="text-decoration-none">
              <div class="card bg-white bg-opacity-10 text-white border-0 h-100 rounded-3 overflow-hidden position-relative tech237-hover-lift">
                <img v-if="dos.cover_image" :src="'/' + dos.cover_image" class="w-100" style="height: 120px; object-fit: cover; opacity: 0.85;">
                <div v-else class="w-100 bg-black bg-opacity-40 d-flex align-items-center justify-content-center" style="height: 120px;">
                  <i class="bi bi-folder-fill fs-2" style="color: var(--tech-green);"></i>
                </div>
                <div class="p-2 text-center">
                  <div class="small fw-bold text-white text-truncate"># {{ locale === 'en' ? (dos.title_en || dos.title_fr) : dos.title_fr }}</div>
                </div>
              </div>
            </Link>
          </div>
        </div>
      </section>

    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  sliders: Array,
  entrepreneurOfMonth: Object,
  mostReadArticle: Object,
  upcomingEvents: Array,
  featuredStartups: Array,
  featuredProjects: Array,
  heroArticles: Array,
  latestArticles: Array,
  trendingArticles: Array,
  seriesArticles: Array,
  cinemaArticles: Array,
  techArticles: Array,
  gamingArticles: Array,
  gameReviews: Array,
  appReviews: Array,
  interviews: Array,
  dailyInterview: Object,
  dossiers: Array,
})

const page = usePage()
const locale = computed(() => page.props.currentLocale || 'fr')
const siteSettings = computed(() => page.props.siteSettings || {})

const stripHtml = (text) => {
  if (!text) return ''
  return text.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim()
}

const formatCardDescription = (text) => {
  if (!text) return ''
  const clean = text
    .replace(/<br\s*[\/]?>/gi, ' ')
    .replace(/<[^>]*>/g, '')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
  return clean.length > 95 ? clean.substring(0, 95) + '...' : clean
}

const formatProjectType = (proj) => {
  if (!proj) return 'Application Web'
  if (Array.isArray(proj.app_types) && proj.app_types.length) {
    return proj.app_types[0]
  }
  if (Array.isArray(proj.game_types) && proj.game_types.length) {
    return proj.game_types[0]
  }
  const map = {
    web: 'Application Web',
    mobile: 'Application Mobile',
    desktop: 'Logiciel Desktop',
    game: 'Jeu Vidéo',
  }
  return map[proj.project_type] || proj.project_type || 'Application Web'
}

// Ad Banners: Home Middle and Sidebar
const homeMiddleAd = computed(() => page.props.adBanners?.home_middle?.[0] || null)
const sidebarAd = computed(() => page.props.adBanners?.sidebar?.[0] || null)

// Carousel logic
const currentSlide = ref(0)
let slideInterval = null

function nextSlide() {
  if (!props.sliders || !props.sliders.length) return
  currentSlide.value = (currentSlide.value + 1) % props.sliders.length
}

function prevSlide() {
  if (!props.sliders || !props.sliders.length) return
  currentSlide.value = (currentSlide.value - 1 + props.sliders.length) % props.sliders.length
}

onMounted(() => {
  if (props.sliders && props.sliders.length > 1) {
    slideInterval = setInterval(nextSlide, 5000)
  }
})

onUnmounted(() => {
  if (slideInterval) clearInterval(slideInterval)
})

// Sidebar Newsletter
const sidebarEmail = ref('')
const sidebarSubscribed = ref(false)

function submitSidebarNewsletter() {
  sidebarSubscribed.value = true
  sidebarEmail.value = ''
}
</script>

<style scoped>
.carousel-wrapper {
  background-color: var(--tech-navy-dark);
}
.carousel-caption-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
}
.carousel-control-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(6, 21, 45, 0.6);
  border: none;
  color: #fff;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  z-index: 10;
}
.carousel-control-btn:hover {
  background: var(--tech-green);
}
.prev-btn { left: 16px; }
.next-btn { right: 16px; }
.carousel-dots-wrapper {
  position: absolute;
  bottom: 16px;
  left: 0;
  right: 0;
  z-index: 10;
}
.carousel-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  transition: all 0.3s ease;
}
.carousel-dot.active {
  width: 26px;
  border-radius: 10px;
  background: var(--tech-green);
}
.hero-top-story {
  cursor: pointer;
}
.hero-top-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
}
.tech237-hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.tech237-hover-lift:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px rgba(0, 168, 89, 0.2) !important;
}
.extra-small {
  font-size: 11px;
}
</style>
