<template>
  <FrontLayout>
    <Head>
      <title>{{ currentCategoryName }} - Actualités & Dossiers Tech237</title>
      <meta name="description" :content="currentDescription || 'Retrouvez toute l\'actualité de ' + currentCategoryName" />
      <meta property="og:type" content="website" />
      <meta property="og:title" :content="currentCategoryName + ' - Tech237'" />
      <meta property="og:description" :content="currentDescription" />
    </Head>

    <div class="container my-4">
      
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3 small">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><Link href="/" class="text-dark text-decoration-none">Accueil</Link></li>
          <li class="breadcrumb-item active fw-bold text-dark">{{ currentCategoryName }}</li>
        </ol>
      </nav>

      <!-- Category Big Header -->
      <div class="mb-4">
        <h1 class="display-5 fw-bold text-dark mb-1">{{ currentCategoryName }}</h1>
        <p class="text-muted small mb-0" v-if="currentDescription">
          {{ currentDescription }}
        </p>
      </div>

      <div class="row g-4">
        
        <!-- Left 8 Columns -->
        <div class="col-lg-8">
          
          <!-- Top Featured Big Card (Matching screenshot) -->
          <div v-if="articles.data && articles.data.length" class="mb-4">
            <Link :href="'/article/' + articles.data[0].slug" class="text-decoration-none">
              <div class="position-relative rounded-4 overflow-hidden shadow-sm" style="height: 380px;">
                <img 
                  :src="'/' + (articles.data[0].featured_image || 'storage/images/chatgpt_hero.jpg')" 
                  :alt="articles.data[0].title_fr" 
                  class="w-100 h-100" 
                  style="object-fit: cover;"
                >
                <!-- Overlay with gradient to overlay title clearly -->
                <div class="position-absolute top-0 start-0 w-100 h-100 p-4 d-flex flex-column justify-content-end" style="background: linear-gradient(180deg, rgba(0,0,0,0) 25%, rgba(0,0,0,0.5) 60%, rgba(0,0,0,0.9) 100%); z-index: 2;">
                  <span class="badge text-white fw-bold mb-2 align-self-start text-uppercase px-2 py-1" style="background-color: var(--tech-green, #00A859); font-size: 11px;">
                    {{ currentCategoryName }}
                  </span>
                  <h2 class="fw-bold text-white mb-2 fs-3" style="text-shadow: 0 2px 4px rgba(0,0,0,0.6);">
                    {{ locale === 'en' ? (articles.data[0].title_en || articles.data[0].title_fr) : articles.data[0].title_fr }}
                  </h2>
                  <p v-if="articles.data[0].summary_fr" class="text-white-50 small mb-2 d-none d-md-block text-truncate" style="max-width: 90%; text-shadow: 0 1px 2px rgba(0,0,0,0.6);">
                    {{ locale === 'en' ? (articles.data[0].summary_en || articles.data[0].summary_fr) : articles.data[0].summary_fr }}
                  </p>
                  <div class="small text-white-50 d-flex align-items-center gap-2">
                    <span class="fw-semibold text-white">{{ articles.data[0].author?.name || 'Tech237' }}</span>
                    <span>&bull;</span>
                    <span>{{ articles.data[0].date_formatted }}</span>
                  </div>
                </div>
              </div>
            </Link>
          </div>

          <!-- Secondary Grid of Articles (2x2) -->
          <div v-if="articles.data && articles.data.length > 1" class="row g-3 mb-5">
            <div v-for="art in articles.data.slice(1, 5)" :key="art.id" class="col-md-6">
              <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                <Link :href="'/article/' + art.slug">
                  <img :src="'/' + (art.featured_image || 'storage/images/chatgpt_hero.jpg')" class="card-img-top" style="height: 180px; object-fit: cover;">
                </Link>
                <div class="card-body p-3">
                  <span class="badge text-dark fw-bold mb-2 text-uppercase" style="background-color: #9be106; font-size: 10px;">
                    {{ currentCategoryName }}
                  </span>
                  <h6 class="card-title fw-bold">
                    <Link :href="'/article/' + art.slug" class="text-dark text-decoration-none">
                      {{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}
                    </Link>
                  </h6>
                </div>
              </div>
            </div>
          </div>

          <!-- Dark Block: DOSSIER À SUIVRE (Matching screenshot) -->
          <div class="p-4 rounded-4 bg-dark text-white mb-5 shadow">
            <h5 class="fw-bold mb-1 text-uppercase">DOSSIER À SUIVRE</h5>
            <p class="text-white-50 small mb-3">Sélection de dossiers exclusifs dans cette thématique.</p>
            <div class="row g-2">
              <div v-for="d in (featuredDossiers || []).slice(0, 4)" :key="d.id" class="col-6 col-md-3">
                <Link :href="'/dossier/' + d.slug" class="text-decoration-none">
                  <div class="p-3 bg-secondary rounded-3 text-center h-100 text-white">
                    <i class="bi bi-folder-fill fs-3 text-warning mb-1 d-block"></i>
                    <div class="small fw-bold text-truncate"># {{ d.title_fr }}</div>
                  </div>
                </Link>
              </div>
            </div>
          </div>

          <!-- Remaining Articles Stream -->
          <div v-if="articles.data && articles.data.length > 5" class="row g-3 mb-4">
            <div v-for="art in articles.data.slice(5)" :key="art.id" class="col-md-6">
              <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                <Link :href="'/article/' + art.slug">
                  <img :src="'/' + art.featured_image" class="card-img-top" style="height: 160px; object-fit: cover;">
                </Link>
                <div class="card-body p-3">
                  <span class="badge text-dark fw-bold mb-2 text-uppercase" style="background-color: #9be106; font-size: 10px;">
                    {{ currentCategoryName }}
                  </span>
                  <h6 class="card-title fw-bold mb-0">
                    <Link :href="'/article/' + art.slug" class="text-dark text-decoration-none">
                      {{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}
                    </Link>
                  </h6>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="articles.links && articles.links.length > 3" class="d-flex justify-content-center gap-1 my-4">
            <Link 
              v-for="(link, i) in articles.links" 
              :key="i" 
              :href="link.url || '#'" 
              class="btn btn-sm rounded-circle px-3 py-2 fw-bold"
              :class="link.active ? 'btn-dark text-white' : 'btn-light text-dark border'"
              v-html="link.label"
            />
          </div>

        </div>

        <!-- Right 4 Columns: Sidebar -->
        <div class="col-lg-4">
          <!-- Sondage Tech du Jour -->
          <DailyTechPoll />

          <!-- Sidebar Ad Banner (Pavé 300x250) -->
          <div v-if="sidebarAd" class="card rounded-4 p-3 mb-4 border-0 shadow-sm text-center bg-light">
            <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 9px; letter-spacing: 1px;">Sponsor &bull; Partenaire</div>
            <a v-if="sidebarAd.image" :href="sidebarAd.link_url || '#'" target="_blank" rel="noopener noreferrer">
              <img :src="'/' + sidebarAd.image" :alt="sidebarAd.title || 'Publicité'" class="img-fluid rounded-3 shadow-sm w-100" style="max-height: 280px; object-fit: cover;">
            </a>
            <div v-else-if="sidebarAd.custom_code" v-html="sidebarAd.custom_code"></div>
          </div>

          <!-- Dark Newsletter Card with Lime Green Button -->
          <div class="card bg-dark text-white rounded-3 p-4 mb-4 border-0 shadow-sm">
            <h5 class="fw-bold mb-1">NEWSLETTER</h5>
            <p class="small text-white-50 mb-3">Tout voir pour ne rien rater de l'actualité.</p>
            <form @submit.prevent="submitSidebarNewsletter">
              <div class="input-group mb-2">
                <span class="input-group-text bg-secondary border-0 text-white"><i class="bi bi-envelope"></i></span>
                <input type="email" v-model="sidebarEmail" class="form-control bg-secondary text-white border-0" placeholder="Votre adresse mail" required>
              </div>
              <button type="submit" class="btn w-100 fw-bold rounded-pill text-dark mt-2" style="background-color: #9be106;">
                Je m'abonne
              </button>
            </form>
          </div>

          <!-- "En ce moment" -->
          <div class="card rounded-3 p-4 mb-4 border shadow-sm bg-white">
            <h5 class="fw-bold mb-3 text-dark">En ce moment</h5>
            <div class="list-group list-group-flush small">
              <Link 
                v-for="(art, idx) in articles.data.slice(0, 5)" 
                :key="art.id" 
                :href="'/article/' + art.slug" 
                class="list-group-item px-0 py-2 text-dark text-decoration-none fw-semibold"
              >
                {{ idx + 1 }}. {{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}
              </Link>
            </div>
          </div>

          <!-- Dark "NOUS SUIVRE" -->
          <div class="card bg-dark text-white rounded-3 p-4 mb-4 border-0 shadow-sm text-center">
            <h6 class="fw-bold text-uppercase mb-2">SUIVRE TECH237</h6>
            <p class="small text-white-50 mb-3">Suivez Tech237 sur tous nos réseaux.</p>
            <div class="d-flex justify-content-center gap-3 fs-5">
              <a :href="siteSettings.social_facebook || '#'" target="_blank" class="text-white"><i class="bi bi-facebook"></i></a>
              <a :href="siteSettings.social_twitter || '#'" target="_blank" class="text-white"><i class="bi bi-twitter-x"></i></a>
              <a :href="siteSettings.social_instagram || '#'" target="_blank" class="text-white"><i class="bi bi-instagram"></i></a>
              <a :href="siteSettings.social_youtube || '#'" target="_blank" class="text-white"><i class="bi bi-youtube"></i></a>
            </div>
          </div>

        </div>

      </div>

    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, Head, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import DailyTechPoll from '@/Components/DailyTechPoll.vue'

const props = defineProps({
  category: { type: Object, required: true },
  articles: { type: Object, required: true },
})

const page = usePage()
const locale = computed(() => page.props.currentLocale || 'fr')
const siteSettings = computed(() => page.props.siteSettings || {})
const featuredDossiers = computed(() => page.props.featuredDossiers || [])
const sidebarAd = computed(() => page.props.adBanners?.sidebar?.[0] || null)

const currentCategoryName = computed(() => {
  return (locale.value === 'en' && props.category.name_en) ? props.category.name_en : props.category.name_fr
})

const currentDescription = computed(() => {
  return (locale.value === 'en' && props.category.description_en) ? props.category.description_en : props.category.description_fr
})

const sidebarEmail = ref('')
function submitSidebarNewsletter() {
  if (!sidebarEmail.value) return
  sidebarEmail.value = ''
  alert('Merci pour votre inscription à la newsletter !')
}
</script>
