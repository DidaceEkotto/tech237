<template>
  <FrontLayout>
    <Head>
      <title>Conférences & Événements Tech au Cameroun | Tech237</title>
      <meta name="description" content="Découvrez les sommets, meetups, hackathons et conférences tech au Cameroun et en Afrique.">
      <meta property="og:title" content="Conférences & Événements Tech | Tech237">
      <meta property="og:description" content="Agenda des grands événements technologiques et numériques.">
    </Head>

    <div class="conferences-index-page py-5">
      <div class="container">
        
        <!-- Hero Header with Background Ad Image & Gradient Overlay -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-5 text-white position-relative overflow-hidden">
          <!-- Background ad image layer -->
          <div 
            class="position-absolute top-0 start-0 w-100 h-100" 
            :style="{
              backgroundImage: `url('${heroBgImage}')`,
              backgroundSize: 'cover',
              backgroundPosition: 'center',
            }"
          ></div>

          <!-- Semi-transparent gradient overlay for readability & ad visibility -->
          <div 
            class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" 
            style="background: linear-gradient(135deg, rgba(9, 13, 22, 0.82) 0%, rgba(30, 41, 59, 0.78) 100%); pointer-events: none;"
          ></div>

          <!-- Sponsor link badge if ad has link_url -->
          <a 
            v-if="heroAd?.link_url" 
            :href="heroAd.link_url" 
            target="_blank" 
            rel="noopener noreferrer" 
            class="badge bg-dark bg-opacity-75 text-white-50 border border-white border-opacity-25 position-absolute top-0 end-0 m-3 extra-small text-decoration-none z-2"
          >
            <i class="bi bi-badge-ad text-warning me-1"></i>Sponsorisé &bull; {{ heroAd.title }} <i class="bi bi-box-arrow-up-right ms-1"></i>
          </a>

          <div class="row align-items-center g-4 position-relative z-1">
            <div class="col-lg-8">
              <span class="badge bg-danger px-3 py-2 rounded-pill fw-bold text-uppercase mb-2">
                <i class="bi bi-calendar-check-fill me-1"></i> Agenda Tech & Innovation
              </span>
              <h1 class="display-6 fw-black text-white mb-2">Conférences, Salons & Meetups Tech</h1>
              <p class="lead text-light mb-4">Ne manquez aucun événement majeur au Cameroun et en Afrique centrale. Participez, réseautez et développez votre écosystème.</p>
              
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <button 
                  v-if="authUser" 
                  @click="showProposeModal = true" 
                  class="btn btn-melty rounded-pill px-4 py-2 fw-bold"
                >
                  <i class="bi bi-calendar-plus me-1"></i> Proposer un Événement
                </button>
                <Link 
                  v-else 
                  href="/login" 
                  class="btn btn-melty rounded-pill px-4 py-2 fw-bold"
                >
                  <i class="bi bi-person-fill me-1"></i> Connectez-vous pour proposer
                </Link>
              </div>
            </div>

            <!-- Filter Card (Date, City, Search) -->
            <div class="col-lg-4">
              <div class="card border-0 rounded-4 p-3 bg-white text-dark shadow">
                <h6 class="fw-bold mb-3"><i class="bi bi-funnel text-danger me-1"></i>Filtrer l'agenda</h6>
                
                <div class="mb-2">
                  <label class="form-label extra-small fw-bold text-muted mb-1">À partir du (Date)</label>
                  <input type="date" v-model="filterDate" class="form-control form-control-sm">
                </div>

                <div class="mb-2">
                  <label class="form-label extra-small fw-bold text-muted mb-1">Ville</label>
                  <select v-model="filterCity" class="form-select form-select-sm">
                    <option value="">Toutes les villes</option>
                    <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label extra-small fw-bold text-muted mb-1">Recherche mot-clé</label>
                  <input type="text" v-model="filterSearch" class="form-control form-control-sm" placeholder="ex: IA, Hackathon, Sommet...">
                </div>

                <button @click="applyFilters" class="btn btn-dark w-100 rounded-pill btn-sm fw-bold">
                  <i class="bi bi-search me-1"></i> Appliquer les filtres
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Conferences Grid -->
        <div class="row g-4 mb-5">
          <div v-for="conf in conferences.data" :key="conf.id" class="col-md-4 col-sm-6">
            <Link :href="'/conference/' + conf.slug" class="text-decoration-none">
              <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-shadow transition bg-white">
                <div class="position-relative">
                  <img 
                    :src="getConferenceImage(conf)" 
                    :alt="conf.title_fr || conf.title"
                    class="card-img-top" 
                    style="height: 180px; object-fit: cover;"
                    @error="handleImgError"
                  >
                  <span v-if="conf.is_official || conf.is_official_admin" class="badge bg-danger position-absolute top-0 start-0 m-2 px-3 py-1 rounded-pill">
                    <i class="bi bi-patch-check-fill me-1"></i> Officiel Tech237
                  </span>
                  <span class="badge bg-dark position-absolute bottom-0 end-0 m-2 px-2 py-1 rounded-pill text-white">
                    <i class="bi bi-geo-alt me-1"></i>{{ conf.city }}
                  </span>
                </div>

                <div class="card-body p-3 d-flex flex-column">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <small class="text-danger fw-bold">
                      <i class="bi bi-calendar-event me-1"></i>{{ formatConfDate(conf.event_date || conf.start_date) }}
                    </small>
                    <div class="d-flex gap-1 flex-wrap">
                      <span class="badge bg-light text-muted border small">{{ (conf.is_in_person === false || conf.location_type === 'online') ? 'En ligne' : 'Présentiel' }}</span>
                      <span class="badge small" :class="conf.is_free !== false ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-dark'">{{ conf.is_free !== false ? 'Gratuit' : (conf.price || 'Payant') }}</span>
                    </div>
                  </div>

                  <h5 class="fw-bold text-dark mb-2 hover-danger">{{ conf.title_fr || conf.title }}</h5>
                  <p class="text-muted small mb-3 flex-grow-1" style="max-height: 44px; overflow: hidden;">
                    {{ stripHtml(conf.description_fr || conf.description) }}
                  </p>

                  <div class="d-flex align-items-center justify-content-between pt-2 border-top text-muted small">
                    <span>
                      <i class="bi bi-people me-1"></i> {{ conf.registrations_count || 0 }} {{ conf.max_attendees ? '/ ' + conf.max_attendees + ' inscrits' : 'inscrits' }}
                    </span>
                    <span class="fw-bold text-danger">Voir détails &rarr;</span>
                  </div>
                </div>
              </div>
            </Link>
          </div>

          <div v-if="!conferences.data || conferences.data.length === 0" class="col-12 text-center py-5 bg-white rounded-4 border">
            <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
            <h5 class="fw-bold text-dark" v-if="filterSearch">Aucun résultat trouvé pour votre recherche</h5>
            <h5 class="fw-bold text-dark" v-else>Aucune conférence ne correspond à vos critères.</h5>
            <p class="text-muted small">Essayez de modifier vos filtres de recherche ou proposez votre événement !</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="conferences.links && conferences.links.length > 3" class="d-flex justify-content-center">
          <nav>
            <ul class="pagination pagination-sm">
              <li v-for="(link, lIdx) in conferences.links" :key="lIdx" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                <Link class="page-link rounded-pill mx-1" :href="link.url || '#'" v-html="link.label" />
              </li>
            </ul>
          </nav>
        </div>

      </div>
    </div>

    <!-- PROPOSE EVENT MODAL -->
    <div v-if="showProposeModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1050;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus text-danger me-2"></i>Proposer un événement tech</h5>
            <button type="button" class="btn-close" @click="showProposeModal = false"></button>
          </div>
          <form @submit.prevent="submitProposal">
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label small fw-bold">Titre de l'événement <span class="text-danger">*</span></label>
                <input type="text" v-model="eventForm.title" class="form-control" placeholder="ex: Meetup Flutter & Dart Douala" required>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small fw-bold">Date de début <span class="text-danger">*</span></label>
                  <input type="date" v-model="eventForm.start_date" class="form-control form-control-sm" required>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-bold">Date de fin</label>
                  <input type="date" v-model="eventForm.end_date" class="form-control form-control-sm">
                </div>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-4">
                  <label class="form-label small fw-bold">Format</label>
                  <select v-model="eventForm.location_type" class="form-select form-select-sm">
                    <option value="in_person">Présentiel</option>
                    <option value="online">En ligne</option>
                    <option value="hybrid">Hybride</option>
                  </select>
                </div>
                <div class="col-4">
                  <label class="form-label small fw-bold">Ville <span class="text-danger">*</span></label>
                  <input type="text" v-model="eventForm.city" class="form-control form-control-sm" placeholder="Douala" required>
                </div>
                <div class="col-4">
                  <label class="form-label small fw-bold">Places Max</label>
                  <input type="number" min="10" v-model="eventForm.max_attendees" class="form-control form-control-sm" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Lieu exact ou lien de connexion</label>
                <input type="text" v-model="eventForm.venue_address" class="form-control form-control-sm" placeholder="Adresse précise ou lien Meet/Zoom">
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Description complète <span class="text-danger">*</span></label>
                <textarea v-model="eventForm.description" rows="4" class="form-control form-control-sm" placeholder="Présentation du programme, des intervenants, public visé..." required></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Affiche / Image de couverture</label>
                <input type="file" @change="handleBannerUpload" class="form-control form-control-sm" accept="image/*">
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="showProposeModal = false">Annuler</button>
              <button type="submit" :disabled="isSubmitting" class="btn btn-melty rounded-pill px-4">Soumettre l'événement</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  conferences: Object,
  cities: Array,
  filters: Object,
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user || null)
const heroAd = computed(() => page.props.adBanners?.hero_conferences?.[0] || null)
const heroBgImage = computed(() => {
  if (heroAd.value?.image) {
    const img = heroAd.value.image
    return img.startsWith('http') ? img : (img.startsWith('/') ? img : '/' + img)
  }
  return 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1600&q=80'
})

const filterCity = ref(props.filters?.city || '')
const filterDate = ref(props.filters?.date || '')
const filterSearch = ref(props.filters?.search || '')

function formatConfDate(val) {
  if (!val) return 'Date à confirmer'
  const d = new Date(val)
  if (isNaN(d.getTime())) return val
  return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

function getConferenceImage(conf) {
  const img = conf?.cover_image || conf?.banner_image
  if (!img) return '/assets/images/default-event.jpg'
  if (img.startsWith('http://') || img.startsWith('https://')) return img
  return img.startsWith('/') ? img : '/' + img
}

function handleImgError(e) {
  e.target.src = '/assets/images/default-event.jpg'
}

function stripHtml(text) {
  if (!text) return ''
  return text.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim()
}

function applyFilters() {
  router.get('/conferences', {
    city: filterCity.value || undefined,
    date: filterDate.value || undefined,
    search: filterSearch.value || undefined,
  }, {
    preserveState: true,
  })
}

// Propose Modal
const showProposeModal = ref(false)
const isSubmitting = ref(false)
const eventForm = ref({
  title: '',
  start_date: '',
  end_date: '',
  location_type: 'in_person',
  city: 'Douala',
  venue_address: '',
  max_attendees: 100,
  description: '',
  banner_image: null,
})

function handleBannerUpload(e) {
  eventForm.value.banner_image = e.target.files[0]
}

function submitProposal() {
  isSubmitting.value = true
  const data = new FormData()
  for (const key in eventForm.value) {
    if (eventForm.value[key] !== null) {
      data.append(key, eventForm.value[key])
    }
  }

  router.post('/conference/proposer', data, {
    onSuccess: () => {
      showProposeModal.value = false
      isSubmitting.value = false
    },
    onError: () => {
      isSubmitting.value = false
    }
  })
}
</script>

<style scoped>
.hover-shadow:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}
.hover-danger:hover {
  color: #FF004B !important;
}
</style>
