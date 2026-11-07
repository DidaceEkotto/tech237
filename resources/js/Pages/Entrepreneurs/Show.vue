<template>
  <FrontLayout>
    <Head>
      <title>Entrepreneur du mois : {{ entrepreneur.name }} | Tech237</title>
      <meta name="description" head-key="description" :content="stripHtml(entrepreneur.bio_fr || '').substring(0, 160)">
      <meta property="og:type" head-key="og:type" content="article">
      <meta property="og:title" head-key="og:title" :content="'Entrepreneur du mois : ' + entrepreneur.name + ' | Tech237'">
      <meta property="og:description" head-key="og:description" :content="stripHtml(entrepreneur.bio_fr || '').substring(0, 160)">
      <meta property="og:image" head-key="og:image" :content="entrepreneur.photo ? '/' + entrepreneur.photo : '/assets/images/author-default.png'">
      <meta name="twitter:card" head-key="twitter:card" content="summary_large_image">
      <meta name="twitter:title" head-key="twitter:title" :content="'Entrepreneur du mois : ' + entrepreneur.name + ' | Tech237'">
      <meta name="twitter:description" head-key="twitter:description" :content="stripHtml(entrepreneur.bio_fr || '').substring(0, 160)">
      <meta name="twitter:image" head-key="twitter:image" :content="entrepreneur.photo ? '/' + entrepreneur.photo : '/assets/images/author-default.png'">
    </Head>

    <div class="entrepreneur-article-page py-5 bg-light min-vh-100">
      <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/entrepreneur-du-mois" class="text-decoration-none text-muted">Entrepreneurs</Link></li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">{{ entrepreneur.name }}</li>
          </ol>
        </nav>

        <!-- Article Top Hero Banner -->
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-5 text-white position-relative" style="background: linear-gradient(135deg, #0B2559 0%, #061533 100%);">
          <div class="p-4 p-md-5 position-relative z-1">
            <div class="row align-items-center g-4">
              <div class="col-md-auto text-center">
                <img 
                  :src="'/' + (entrepreneur.photo || 'assets/images/author-default.png')" 
                  :alt="entrepreneur.name" 
                  class="rounded-circle shadow-lg border border-4" 
                  style="width: 130px; height: 130px; object-fit: cover; border-color: var(--tech-green) !important;"
                >
              </div>
              <div class="col">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                  <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase text-white shadow-sm" style="background-color: var(--tech-green);">
                    <i class="bi bi-award-fill me-1 text-warning"></i> Entrepreneur du mois &bull; {{ entrepreneur.month_year }}
                  </span>
                  <span v-if="entrepreneur.startup" class="badge bg-white text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
                    <i class="bi bi-rocket-takeoff-fill text-success me-1"></i> {{ entrepreneur.startup.name }}
                  </span>
                </div>
                <h1 class="display-5 fw-bold text-white mb-2">{{ entrepreneur.name }}</h1>
                <p class="lead fw-semibold mb-2" style="color: var(--tech-green-light);">{{ entrepreneur.role_title || entrepreneur.role_or_title || 'Fondateur & Visionnaire Tech' }}</p>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <a v-if="entrepreneur.social_links?.linkedin" :href="entrepreneur.social_links.linkedin" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="bi bi-linkedin me-1 text-info"></i> LinkedIn
                  </a>
                  <a v-if="entrepreneur.social_links?.twitter" :href="entrepreneur.social_links.twitter" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="bi bi-twitter-x me-1"></i> X (Twitter)
                  </a>
                  <a v-if="entrepreneur.social_links?.website" :href="entrepreneur.social_links.website" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="bi bi-globe me-1 text-success"></i> Site Web
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2 Columns Layout: Left = Full Article Content | Right = Interactive Lightbox Gallery -->
        <div class="row g-4 mb-5">
          
          <!-- LEFT COLUMN (8 cols): In-depth Story, Bio, Parcours, Difficultés, Vision, Video Player -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
              
              <!-- Video Player Embed (if YouTube URL provided) -->
              <div v-if="youtubeEmbedUrl" class="mb-5">
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm border">
                  <iframe 
                    :src="youtubeEmbedUrl" 
                    title="Interview vidéo de l'entrepreneur" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen
                  ></iframe>
                </div>
                <div class="extra-small text-muted mt-2 d-flex align-items-center">
                  <i class="bi bi-play-circle-fill text-danger me-1"></i>
                  <span>Interview vidéo &amp; vision entrepreneuriale de {{ entrepreneur.name }}</span>
                </div>
              </div>

              <!-- 1. Bio & Présentation -->
              <div class="mb-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <span class="rounded-circle d-inline-flex p-2 text-white" style="background-color: var(--tech-navy); width: 36px; height: 36px; align-items: center; justify-content: center;">
                    <i class="bi bi-person-badge"></i>
                  </span>
                  <h3 class="fw-bold text-dark mb-0">Biographie &amp; Origines</h3>
                </div>
                <div class="wysiwyg-content text-secondary leading-relaxed" v-html="currentBio"></div>
              </div>

              <!-- 2. Le Parcours & Les Débuts -->
              <div class="mb-5 pt-4 border-top">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <span class="rounded-circle d-inline-flex p-2 text-white" style="background-color: var(--tech-green); width: 36px; height: 36px; align-items: center; justify-content: center;">
                    <i class="bi bi-compass"></i>
                  </span>
                  <h3 class="fw-bold text-dark mb-0">Le Parcours &amp; Le Déclic Tech</h3>
                </div>
                <div class="wysiwyg-content text-secondary leading-relaxed" v-html="currentJourney"></div>
              </div>

              <!-- 3. Les Difficultés & Obstacles Surmontés -->
              <div class="mb-5 pt-4 border-top">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <span class="rounded-circle d-inline-flex p-2 text-white bg-danger" style="width: 36px; height: 36px; align-items: center; justify-content: center;">
                    <i class="bi bi-lightning-charge"></i>
                  </span>
                  <h3 class="fw-bold text-dark mb-0">Les Épreuves &amp; Obstacles Surmontés</h3>
                </div>
                <div class="wysiwyg-content text-secondary leading-relaxed" v-html="currentChallenges"></div>
              </div>

              <!-- 4. Vision & Conseils aux Jeunes Bâtisseurs -->
              <div class="pt-4 border-top">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <span class="rounded-circle d-inline-flex p-2 text-white bg-warning text-dark" style="width: 36px; height: 36px; align-items: center; justify-content: center;">
                    <i class="bi bi-lightbulb"></i>
                  </span>
                  <h3 class="fw-bold text-dark mb-0">Vision d'Avenir &amp; Conseils Clés</h3>
                </div>
                <div class="wysiwyg-content text-secondary leading-relaxed" v-html="currentVision"></div>
              </div>

            </div>

            <!-- Startup Spotlight Card (if linked) -->
            <div v-if="entrepreneur.startup" class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-4" style="border-left-color: var(--tech-green) !important;">
              <div class="row align-items-center g-3">
                <div class="col-auto">
                  <img 
                    :src="'/' + (entrepreneur.startup.logo || 'assets/images/default-startup.png')" 
                    class="rounded-3 border p-1 shadow-sm" 
                    style="width: 72px; height: 72px; object-fit: contain;"
                  >
                </div>
                <div class="col">
                  <span class="badge px-2 py-1 rounded-pill mb-1" style="background-color: var(--tech-green-light); color: var(--tech-green);">Entreprise Enregistrée</span>
                  <h4 class="fw-bold text-dark mb-1">{{ entrepreneur.startup.name }}</h4>
                  <p class="text-muted small mb-0">{{ entrepreneur.startup.description }}</p>
                </div>
                <div class="col-md-auto">
                  <Link :href="'/startups/' + (entrepreneur.startup.slug || entrepreneur.startup.id)" class="btn btn-outline-dark rounded-pill px-4 fw-bold btn-sm">
                    Consulter la startup &rarr;
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN (4 cols): Sticky Photo Gallery & Lightbox -->
          <div class="col-lg-4">
            <div class="sticky-top" style="top: 90px;">
              <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="bi bi-images text-success me-2"></i>Galerie Photos
                  </h5>
                  <span class="badge rounded-pill bg-light text-muted border">
                    {{ activeGalleryIndex + 1 }} / {{ allImages.length }}
                  </span>
                </div>

                <!-- Main Lightbox Display -->
                <div class="position-relative rounded-4 overflow-hidden border shadow-sm mb-3 bg-dark" style="height: 280px; cursor: pointer;" @click="openLightbox">
                  <img 
                    :src="'/' + allImages[activeGalleryIndex]" 
                    class="w-100 h-100 object-fit-cover transition-all"
                    alt="Photo entrepreneur"
                  >
                  <!-- Zoom overlay indicator -->
                  <div class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-75 text-white px-2 py-1 rounded-pill extra-small">
                    <i class="bi bi-arrows-fullscreen me-1"></i> Agrandir
                  </div>

                  <!-- Prev / Next arrows over main display -->
                  <button 
                    v-if="allImages.length > 1" 
                    type="button" 
                    class="btn btn-dark btn-sm rounded-circle position-absolute top-50 start-0 translate-middle-y ms-2 opacity-75 hover-opacity-100 d-flex align-items-center justify-content-center" 
                    style="width: 34px; height: 34px;"
                    @click.stop="prevImage"
                    title="Photo précédente"
                  >
                    <i class="bi bi-chevron-left"></i>
                  </button>
                  <button 
                    v-if="allImages.length > 1" 
                    type="button" 
                    class="btn btn-dark btn-sm rounded-circle position-absolute top-50 end-0 translate-middle-y me-2 opacity-75 hover-opacity-100 d-flex align-items-center justify-content-center" 
                    style="width: 34px; height: 34px;"
                    @click.stop="nextImage"
                    title="Photo suivante"
                  >
                    <i class="bi bi-chevron-right"></i>
                  </button>
                </div>

                <!-- Thumbnails bar at bottom (with active highlight) -->
                <div v-if="allImages.length > 1" class="d-flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                  <div 
                    v-for="(img, idx) in allImages" 
                    :key="idx" 
                    class="rounded-3 overflow-hidden border flex-shrink-0 cursor-pointer transition-all"
                    :class="activeGalleryIndex === idx ? 'border-success border-3 shadow-sm scale-105' : 'opacity-70 hover-opacity-100'"
                    style="width: 64px; height: 64px;"
                    @click="activeGalleryIndex = idx"
                  >
                    <img :src="'/' + img" class="w-100 h-100 object-fit-cover" :alt="'Miniature ' + idx">
                  </div>
                </div>

                <div class="text-center mt-3 pt-3 border-top">
                  <small class="text-muted extra-small d-block mb-2">Partagez ce portrait inspirant :</small>
                  <div class="d-flex justify-content-center gap-2">
                    <a :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent('Découvrez le portrait de ' + entrepreneur.name + ' sur Tech237 : ' + currentUrl)" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                      <i class="bi bi-whatsapp me-1"></i> WhatsApp
                    </a>
                    <a :href="'https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(currentUrl)" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                      <i class="bi bi-linkedin me-1"></i> Partager
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- BOTTOM SECTION: Past Entrepreneurs with Clickable Cards -->
        <div v-if="previousEntrepreneurs && previousEntrepreneurs.length > 0" class="pt-5 border-top">
          <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
              <h3 class="fw-bold text-dark mb-1"><i class="bi bi-clock-history text-success me-2"></i>Autres Entrepreneurs à l'honneur</h3>
              <p class="text-muted small mb-0">Revivez les parcours inspirants des leaders qui façonnent la tech camerounaise.</p>
            </div>
          </div>

          <div class="row g-4">
            <div v-for="prev in previousEntrepreneurs" :key="prev.id" class="col-md-6 col-lg-4">
              <Link :href="'/entrepreneur-du-mois/' + prev.id" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 hover-shadow transition d-flex flex-column justify-content-between">
                  <div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                      <img 
                        :src="'/' + (prev.photo || 'assets/images/author-default.png')" 
                        class="rounded-circle border border-2 border-success shadow-sm" 
                        style="width: 68px; height: 68px; object-fit: cover;"
                      >
                      <div>
                        <span class="badge bg-light text-muted border mb-1">{{ prev.month_year }}</span>
                        <h5 class="fw-bold text-dark mb-0">{{ prev.name }}</h5>
                        <small class="text-muted d-block">{{ prev.role_title || prev.role_or_title }}</small>
                      </div>
                    </div>
                    <p class="text-muted small mb-3 text-truncate-2">
                      {{ stripHtml(prev.bio_fr || '') }}
                    </p>
                  </div>
                  <div class="text-success fw-bold small d-flex align-items-center justify-content-between pt-2 border-top">
                    <span>Lire le portrait complet</span>
                    <i class="bi bi-arrow-right"></i>
                  </div>
                </div>
              </Link>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- FULLSCREEN LIGHTBOX MODAL -->
    <div v-if="isLightboxOpen" class="lightbox-backdrop position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.92); z-index: 2000;" @click.self="isLightboxOpen = false">
      <button type="button" class="btn btn-outline-light rounded-circle position-absolute top-0 end-0 m-4" style="width: 44px; height: 44px;" @click="isLightboxOpen = false">
        <i class="bi bi-x-lg fs-5"></i>
      </button>

      <div class="text-center position-relative max-w-90 max-h-85">
        <img :src="'/' + allImages[activeGalleryIndex]" class="img-fluid rounded-4 shadow-lg" style="max-height: 80vh; max-width: 90vw; object-fit: contain;">
        <div class="text-white-50 mt-3 small">
          Photo {{ activeGalleryIndex + 1 }} sur {{ allImages.length }} &bull; {{ entrepreneur.name }}
        </div>
      </div>

      <!-- Left / Right Navigation in Lightbox -->
      <button 
        v-if="allImages.length > 1"
        type="button" 
        class="btn btn-outline-light rounded-circle position-absolute top-50 start-0 translate-middle-y ms-4" 
        style="width: 50px; height: 50px;" 
        @click="prevImage"
      >
        <i class="bi bi-chevron-left fs-4"></i>
      </button>
      <button 
        v-if="allImages.length > 1"
        type="button" 
        class="btn btn-outline-light rounded-circle position-absolute top-50 end-0 translate-middle-y me-4" 
        style="width: 50px; height: 50px;" 
        @click="nextImage"
      >
        <i class="bi bi-chevron-right fs-4"></i>
      </button>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  entrepreneur: { type: Object, required: true },
  previousEntrepreneurs: { type: Array, default: () => [] },
})

const page = usePage()
const locale = computed(() => page.props.currentLocale || 'fr')
const currentUrl = computed(() => typeof window !== 'undefined' ? window.location.href : '')

// Aggregate main photo + gallery photos into unified list
const allImages = computed(() => {
  const list = []
  if (props.entrepreneur.photo) {
    list.push(props.entrepreneur.photo)
  }
  if (Array.isArray(props.entrepreneur.gallery)) {
    props.entrepreneur.gallery.forEach(img => {
      if (img && !list.includes(img)) list.push(img)
    })
  }
  return list.length > 0 ? list : ['assets/images/author-default.png']
})

const activeGalleryIndex = ref(0)
const isLightboxOpen = ref(false)

function openLightbox() {
  isLightboxOpen.value = true
}

function nextImage() {
  activeGalleryIndex.value = (activeGalleryIndex.value + 1) % allImages.value.length
}

function prevImage() {
  activeGalleryIndex.value = (activeGalleryIndex.value - 1 + allImages.value.length) % allImages.value.length
}

// YouTube embed helper
const youtubeEmbedUrl = computed(() => {
  const url = props.entrepreneur.video_url
  if (!url) return null
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/
  const match = url.match(regExp)
  if (match && match[2].length === 11) {
    return 'https://www.youtube-nocookie.com/embed/' + match[2]
  }
  return null
})

// Content Helpers (prefer HTML or format linebreaks)
function formatContent(text) {
  if (!text) return ''
  if (text.includes('<p>') || text.includes('<div>')) return text
  return text.split('\n').filter(p => p.trim()).map(p => `<p>${p}</p>`).join('')
}

const currentBio = computed(() => {
  const isEn = locale.value === 'en'
  return formatContent(isEn ? (props.entrepreneur.bio_en || props.entrepreneur.bio_fr) : props.entrepreneur.bio_fr)
})

const currentJourney = computed(() => {
  const isEn = locale.value === 'en'
  const text = isEn ? (props.entrepreneur.journey_en || props.entrepreneur.story_en || props.entrepreneur.journey_fr) : (props.entrepreneur.journey_fr || props.entrepreneur.story_fr)
  return formatContent(text || 'Parcours inspirant au cœur de la tech camerounaise.')
})

const currentChallenges = computed(() => {
  const isEn = locale.value === 'en'
  const text = isEn ? (props.entrepreneur.challenges_en || props.entrepreneur.difficulties_en || props.entrepreneur.challenges_fr) : (props.entrepreneur.challenges_fr || props.entrepreneur.difficulties_fr)
  return formatContent(text || 'Défis relevés avec persévérance et vision stratégique.')
})

const currentVision = computed(() => {
  const isEn = locale.value === 'en'
  const text = isEn ? (props.entrepreneur.vision_en || props.entrepreneur.vision_fr) : props.entrepreneur.vision_fr
  return formatContent(text || 'Bâtir des solutions locales durables à fort impact.')
})

function stripHtml(html) {
  return html ? html.replace(/<[^>]*>/g, '') : ''
}
</script>

<style scoped>
.hover-shadow:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 25px rgba(0,0,0,0.08) !important;
}
.transition {
  transition: all 0.25s ease;
}
.transition-all {
  transition: all 0.2s ease;
}
.cursor-pointer {
  cursor: pointer;
}
.scale-105 {
  transform: scale(1.05);
}
.extra-small {
  font-size: 11px;
}
.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.custom-scrollbar::-webkit-scrollbar {
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 4px;
}
.wysiwyg-content :deep(p) {
  margin-bottom: 1.25rem;
  font-size: 1.05rem;
  line-height: 1.75;
}
.wysiwyg-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.75rem;
  margin: 1.5rem 0;
}
</style>
