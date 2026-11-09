<template>
  <FrontLayout>
    <Head>
      <title>{{ rubriqueTitle }} | Tech237</title>
      <meta name="description" :content="'Toute l\'actualité ' + rubriqueTitle + ' en direct sur Tech237.'">
      <meta property="og:title" :content="rubriqueTitle + ' | Tech237'">
      <meta property="og:description" :content="'Toute l\'actualité ' + rubriqueTitle + ' en direct sur Tech237.'">
      <meta property="og:type" content="website">
    </Head>

    <div class="rubrique-page py-4" :style="customAccentVars">
      <div class="container">
        
        <!-- 1. BANNER STYLE 1: Hero Gradient Immersif with Optional Custom Banner Image -->
        <div 
          v-if="bannerStyle === 'hero_gradient'" 
          class="rubrique-hero-gradient p-4 p-md-5 rounded-4 text-white mb-4 position-relative overflow-hidden shadow-sm"
          :style="rubriqueHeroStyle"
        >
          <div class="position-relative z-1">
            <span class="badge px-3 py-2 rounded-pill mb-2 fw-bold text-uppercase" :style="{ backgroundColor: accentColor }">
              <i v-if="rubrique.icon" :class="'bi ' + rubrique.icon + ' me-1'"></i>
              Rubrique Éditoriale
            </span>
            <h1 class="display-5 fw-black text-white mb-2">{{ rubriqueTitle }}</h1>
            <p class="lead text-light mb-0" style="max-width: 650px;">
              {{ locale === 'en' ? 'Explore all news, articles, and updates about ' + rubriqueTitle + '.' : 'Retrouvez toutes les actualités, décryptages et dossiers exclusifs sur ' + rubriqueTitle + '.' }}
            </p>
          </div>
        </div>

        <!-- BANNER STYLE 2: Boxed with Giant Badge -->
        <div v-else-if="bannerStyle === 'boxed_badge'" class="p-4 rounded-4 bg-white border mb-4 shadow-sm d-flex align-items-center gap-3">
          <div class="rounded-4 p-3 d-flex align-items-center justify-content-center text-white" :style="{ backgroundColor: accentColor, width: '64px', height: '64px' }">
            <i :class="'bi ' + (rubrique.icon || 'bi-collection-play') + ' fs-2'"></i>
          </div>
          <div>
            <h1 class="h2 fw-black text-dark mb-1">{{ rubriqueTitle }}</h1>
            <small class="text-muted">{{ articles.total }} article(s) publié(s) dans cette rubrique</small>
          </div>
        </div>

        <!-- BANNER STYLE 3: Minimalist Bold Typo -->
        <div v-else class="border-bottom pb-3 mb-4">
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge rounded-pill px-3 py-1 text-white" :style="{ backgroundColor: accentColor }">Rubrique</span>
            <span class="text-muted small">&bull; Actualités & Tendances</span>
          </div>
          <h1 class="fw-black display-6 text-dark mb-0">{{ rubriqueTitle }}</h1>
        </div>

        <!-- 2. Audio Narrator (TTS) on Rubrique -->
        <div v-if="options.show_audio_player !== false" class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-light">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <button 
                type="button" 
                @click="toggleAudioNarrator" 
                class="btn rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm"
                :style="{ backgroundColor: accentColor, width: '44px', height: '44px' }"
              >
                <i :class="isAudioPlaying ? 'bi bi-pause-fill fs-4' : 'bi bi-play-fill fs-4'"></i>
              </button>
              <div>
                <div class="fw-bold small text-dark d-flex align-items-center gap-2">
                  <span>{{ isAudioPlaying ? 'Lecture audio en cours...' : 'Écouter le flash audio de cette rubrique' }}</span>
                  <span v-if="isAudioPlaying" class="spinner-grow spinner-grow-sm text-danger"></span>
                </div>
                <small class="text-muted">Synthèse vocale 100% locale des titres à la une</small>
              </div>
            </div>
            <span class="badge bg-white text-muted border px-3 py-2 rounded-pill small">
              <i class="bi bi-mic me-1" style="color: var(--tech-green);"></i> Narrateur Tech237
            </span>
          </div>
        </div>

        <!-- 3. Category Filter Pills -->
        <div v-if="options.show_filter_pills !== false && rubrique.categories && rubrique.categories.length > 0" class="d-flex align-items-center gap-2 flex-wrap mb-4">
          <span class="small fw-bold text-muted text-uppercase me-1" style="font-size: 11px;">Sous-thèmes :</span>
          <Link 
            v-for="cat in rubrique.categories" 
            :key="cat.id"
            :href="'/category/' + cat.slug"
            class="badge rounded-pill px-3 py-2 text-decoration-none border bg-white text-dark hover-shadow"
          >
            <i :class="'bi ' + (cat.icon || 'bi-tag') + ' me-1'" :style="{ color: cat.color || accentColor }"></i>
            {{ locale === 'en' ? (cat.name_en || cat.name_fr) : cat.name_fr }}
          </Link>
        </div>

        <!-- 4. Related Dossiers Carousel -->
        <div v-if="options.show_dossiers_carousel !== false && rubrique.dossiers && rubrique.dossiers.length > 0" class="mb-4">
          <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
            <i class="bi bi-folder-fill text-danger me-2"></i>Dossiers Thématiques Associés
          </h6>
          <div class="row g-3">
            <div v-for="dos in rubrique.dossiers" :key="dos.id" class="col-md-4 col-sm-6">
              <Link :href="'/dossier/' + dos.slug" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 dossier-card-hover">
                  <img :src="'/' + (dos.cover_image || 'assets/images/placeholder.jpg')" class="card-img-top" style="height: 120px; object-fit: cover;">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-1">{{ dos.title_fr }}</h6>
                    <small class="text-muted">Découvrir le dossier &rarr;</small>
                  </div>
                </div>
              </Link>
            </div>
          </div>
        </div>

        <!-- 5. Main Content: Articles Layout & Sidebar -->
        <div class="row g-4">
          <!-- Articles Column -->
          <div :class="options.show_trending_sidebar !== false ? 'col-lg-8' : 'col-12'">
            
            <!-- LAYOUT 1: STANDARD 3 COLS -->
            <div v-if="layoutStyle === 'grid_standard'" class="row g-4">
              <div v-for="art in articles.data" :key="art.id" class="col-md-6">
                <Link :href="'/article/' + art.slug" class="text-decoration-none">
                  <div class="card border-0 h-100" :class="cardClass">
                    <div class="position-relative overflow-hidden rounded-top-4">
                      <img :src="formatImageUrl(art.featured_image || art.image)" class="card-img-top article-thumb" style="height: 200px; object-fit: cover;">
                      <span v-if="art.category" class="badge position-absolute top-0 start-0 m-2 px-2 py-1 rounded-pill" :style="{ backgroundColor: art.category.color || accentColor, color: 'white' }">
                        {{ art.category.name_fr }}
                      </span>
                    </div>
                    <div class="card-body p-3">
                      <small class="text-muted d-block mb-1">{{ art.date_formatted }}</small>
                      <h5 class="fw-bold text-dark card-title mb-2">{{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}</h5>
                      <p class="text-muted small mb-0">{{ locale === 'en' ? (art.summary_en || art.summary_fr) : art.summary_fr }}</p>
                    </div>
                  </div>
                </Link>
              </div>
            </div>

            <!-- LAYOUT 2: LARGE CARDS IMMERSIVE -->
            <div v-else-if="layoutStyle === 'grid_large'" class="d-flex flex-column gap-4">
              <div v-for="art in articles.data" :key="art.id">
                <Link :href="'/article/' + art.slug" class="text-decoration-none">
                  <div class="card border-0 overflow-hidden" :class="cardClass">
                    <img :src="formatImageUrl(art.featured_image || art.image)" class="card-img-top" style="height: 320px; object-fit: cover;">
                    <div class="card-body p-4">
                      <span v-if="art.category" class="badge px-3 py-1 rounded-pill mb-2" :style="{ backgroundColor: art.category.color || accentColor, color: 'white' }">
                        {{ art.category.name_fr }}
                      </span>
                      <h3 class="fw-bold text-dark mb-2">{{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}</h3>
                      <p class="text-muted mb-3">{{ locale === 'en' ? (art.summary_en || art.summary_fr) : art.summary_fr }}</p>
                      <small class="text-muted">{{ art.date_formatted }} &bull; Par {{ art.author ? art.author.name : 'Tech237' }}</small>
                    </div>
                  </div>
                </Link>
              </div>
            </div>

            <!-- LAYOUT 3: HORIZONTAL MEDIA LIST -->
            <div v-else-if="layoutStyle === 'grid_list'" class="d-flex flex-column gap-3">
              <div v-for="art in articles.data" :key="art.id">
                <Link :href="'/article/' + art.slug" class="text-decoration-none">
                  <div class="card border-0 p-3" :class="cardClass">
                    <div class="row g-3 align-items-center">
                      <div class="col-sm-4">
                        <img :src="formatImageUrl(art.featured_image || art.image)" class="img-fluid rounded-3" style="width: 100%; height: 140px; object-fit: cover;">
                      </div>
                      <div class="col-sm-8">
                        <span v-if="art.category" class="badge px-2 py-1 rounded-pill mb-1" :style="{ backgroundColor: art.category.color || accentColor, color: 'white' }">
                          {{ art.category.name_fr }}
                        </span>
                        <h5 class="fw-bold text-dark mb-1">{{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}</h5>
                        <p class="text-muted small mb-2">{{ locale === 'en' ? (art.summary_en || art.summary_fr) : art.summary_fr }}</p>
                        <small class="text-muted">{{ art.date_formatted }}</small>
                      </div>
                    </div>
                  </div>
                </Link>
              </div>
            </div>

            <!-- LAYOUT 4: MASONRY / DYNAMIC ASYMMETRIC -->
            <div v-else class="row g-4">
              <div v-for="(art, idx) in articles.data" :key="art.id" :class="idx % 3 === 0 ? 'col-12' : 'col-md-6'">
                <Link :href="'/article/' + art.slug" class="text-decoration-none">
                  <div class="card border-0 h-100" :class="cardClass">
                    <img :src="formatImageUrl(art.featured_image || art.image)" class="card-img-top" :style="{ height: idx % 3 === 0 ? '280px' : '180px', objectFit: 'cover' }">
                    <div class="card-body p-3">
                      <span v-if="art.category" class="badge mb-2" :style="{ backgroundColor: art.category.color || accentColor, color: 'white' }">{{ art.category.name_fr }}</span>
                      <h4 class="fw-bold text-dark mb-1" :class="{ 'h3': idx % 3 === 0 }">{{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}</h4>
                      <p class="text-muted small mb-0">{{ locale === 'en' ? (art.summary_en || art.summary_fr) : art.summary_fr }}</p>
                    </div>
                  </div>
                </Link>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="!articles.data || articles.data.length === 0" class="text-center py-5 bg-white rounded-4 border">
              <i class="bi bi-newspaper fs-1 text-muted d-block mb-2"></i>
              <h5 class="fw-bold text-dark">Aucun article publié pour le moment dans cette rubrique.</h5>
              <p class="text-muted small">Revenez très vite pour découvrir nos prochains articles !</p>
            </div>

            <!-- Pagination -->
            <div v-if="articles.links && articles.links.length > 3" class="mt-4 d-flex justify-content-center">
              <nav>
                <ul class="pagination pagination-sm">
                  <li v-for="(link, lIdx) in articles.links" :key="lIdx" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                    <Link class="page-link rounded-pill mx-1" :href="link.url || '#'" v-html="link.label" />
                  </li>
                </ul>
              </nav>
            </div>

          </div>

          <!-- Sidebar Column (Trending / Top read) -->
          <div v-if="options.show_trending_sidebar !== false" class="col-lg-4">
            <!-- Sondage Tech du Jour -->
            <DailyTechPoll />

            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white sticky-top" style="top: 80px;">
              <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                <i class="bi bi-fire text-danger me-2"></i>Les Plus Lus du Moment
              </h6>
              <div class="d-flex flex-column gap-3">
                <div v-for="(tr, idx) in trendingArticles" :key="tr.id" class="d-flex gap-2 align-items-start border-bottom pb-2">
                  <span class="fw-black text-danger fs-5" style="width: 24px;">{{ idx + 1 }}</span>
                  <div>
                    <Link :href="'/article/' + tr.slug" class="text-decoration-none fw-bold small text-dark d-block mb-1 hover-danger">
                      {{ tr.title_fr }}
                    </Link>
                    <small class="text-muted">{{ tr.views_count }} vues</small>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sidebar Ad Banner (Pavé 300x250) -->
            <div v-if="sidebarAd" class="card rounded-4 p-3 mt-4 border-0 shadow-sm text-center bg-light">
              <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 9px; letter-spacing: 1px;">Sponsor &bull; Partenaire</div>
              <a v-if="sidebarAd.image" :href="sidebarAd.link_url || '#'" target="_blank" rel="noopener noreferrer">
                <img :src="'/' + sidebarAd.image" :alt="sidebarAd.title || 'Publicité'" class="img-fluid rounded-3 shadow-sm w-100" style="max-height: 280px; object-fit: cover;">
              </a>
              <div v-else-if="sidebarAd.custom_code" v-html="sidebarAd.custom_code"></div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { computed, ref, onUnmounted } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import DailyTechPoll from '@/Components/DailyTechPoll.vue'

const props = defineProps({
  rubrique: Object,
  articles: Object,
  trendingArticles: Array,
})

const page = usePage()
const locale = computed(() => page.props.currentLocale || 'fr')
const sidebarAd = computed(() => page.props.adBanners?.sidebar?.[0] || null)

const formatImageUrl = (img) => {
  if (!img) return '/assets/images/placeholder.jpg'
  if (img.startsWith('http://') || img.startsWith('https://')) return img
  return img.startsWith('/') ? img : '/' + img
}

const options = computed(() => props.rubrique.options || {})
const layoutStyle = computed(() => options.value.layout || 'grid_standard')
const bannerStyle = computed(() => options.value.banner_style || 'hero_gradient')
const cardStyle = computed(() => options.value.card_style || 'rounded_shadow')
const accentColor = computed(() => options.value.accent_color || '#FF004B')

const rubriqueHeroStyle = computed(() => {
  if (props.rubrique?.banner_image) {
    return {
      backgroundImage: `linear-gradient(135deg, rgba(11, 37, 69, 0.82) 0%, rgba(6, 21, 51, 0.92) 100%), url('/${props.rubrique.banner_image}')`,
      backgroundSize: 'cover',
      backgroundPosition: 'center center',
      backgroundRepeat: 'no-repeat',
    }
  }
  return {}
})

const rubriqueTitle = computed(() => {
  return locale.value === 'en' ? (props.rubrique.name_en || props.rubrique.name_fr) : props.rubrique.name_fr
})

const customAccentVars = computed(() => {
  return {
    '--rubrique-accent': accentColor.value,
  }
})

const cardClass = computed(() => {
  switch (cardStyle.value) {
    case 'flat_bordered':
      return 'border rounded-3 shadow-none bg-white'
    case 'dark_mode':
      return 'bg-dark text-white rounded-4 shadow-sm border border-secondary'
    case 'zoom_hover':
      return 'shadow-sm rounded-4 bg-white zoom-card-effect'
    case 'rounded_shadow':
    default:
      return 'shadow-sm rounded-4 bg-white hover-shadow'
  }
})

// Audio Narrator (TTS)
const isAudioPlaying = ref(false)
let speechSynth = typeof window !== 'undefined' ? window.speechSynthesis : null
let currentUtterance = null

function toggleAudioNarrator() {
  if (!speechSynth) {
    alert('La synthèse vocale n\'est pas supportée sur ce navigateur.')
    return
  }

  if (isAudioPlaying.value) {
    speechSynth.cancel()
    isAudioPlaying.value = false
  } else {
    speechSynth.cancel()
    const headlines = (props.articles.data || []).map(a => a.title_fr).slice(0, 5).join('. ')
    const textToRead = `Voici le flash actualités pour la rubrique ${rubriqueTitle.value}. ` + headlines
    
    currentUtterance = new SpeechSynthesisUtterance(textToRead)
    currentUtterance.lang = locale.value === 'en' ? 'en-US' : 'fr-FR'
    currentUtterance.rate = 1.0

    currentUtterance.onend = () => {
      isAudioPlaying.value = false
    }
    currentUtterance.onerror = () => {
      isAudioPlaying.value = false
    }

    speechSynth.speak(currentUtterance)
    isAudioPlaying.value = true
  }
}

onUnmounted(() => {
  if (speechSynth) {
    speechSynth.cancel()
  }
})
</script>

<style scoped>
.rubrique-hero-gradient {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border-left: 6px solid var(--rubrique-accent);
}
.hover-shadow {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-shadow:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}
.zoom-card-effect img {
  transition: transform 0.3s ease;
}
.zoom-card-effect:hover img {
  transform: scale(1.05);
}
.hover-danger:hover {
  color: #FF004B !important;
}
</style>
