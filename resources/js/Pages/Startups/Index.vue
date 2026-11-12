<template>
  <FrontLayout>
    <Head>
      <title>Annuaire des Startups Tech & Projets Développés | Tech237</title>
      <meta name="description" content="Découvrez les startups innovantes camerounaises, leurs fondateurs et les projets technologiques qu'elles développent en ce moment." />
      <meta property="og:title" content="Startups Tech du Cameroun | Tech237" />
      <meta property="og:description" content="Explorez l'écosystème entrepreneurial numérique camerounais : startups, fintechs, applications et solutions locales." />
      <meta property="og:image" content="/assets/images/logo.png" />
    </Head>

    <div class="startups-index-page py-5 bg-light min-vh-100">
      <div class="container">

        <!-- Top Header Hero with Background Ad Image & Gradient Overlay -->
        <div class="rounded-4 p-4 p-md-5 text-white mb-5 shadow-sm position-relative overflow-hidden">
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
            style="background: linear-gradient(135deg, rgba(11, 37, 89, 0.82) 0%, rgba(0, 168, 89, 0.76) 100%); pointer-events: none;"
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

          <div class="position-relative z-1 max-w-750">
            <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 bg-white text-dark shadow-sm">
              <i class="bi bi-rocket-takeoff-fill text-success me-1"></i> Écosystème Startup 237
            </span>
            <h1 class="display-5 fw-bold text-white mb-2">Les Startups Tech du Cameroun</h1>
            <p class="lead text-white-50 mb-4">
              <em>"Là où les idées deviennent des projets"</em>. Découvrez les jeunes entreprises innovantes, leurs technologies et les solutions qu'elles construisent pour l'Afrique et le monde.
            </p>
            <div class="d-flex align-items-center gap-3 flex-wrap">
              <Link href="/espace-utilisateur/startups/create" class="btn btn-light rounded-pill px-4 py-2 fw-bold shadow-sm" style="color: var(--tech-navy);">
                <i class="bi bi-plus-circle-fill me-1 text-success"></i> Enregistrer ma Startup
              </Link>
              <Link href="/projets" class="btn btn-outline-light rounded-pill px-4 py-2 fw-bold">
                <i class="bi bi-code-slash me-1"></i> Voir les Projets &rarr;
              </Link>
            </div>
          </div>
        </div>

        <!-- Filters Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-5 bg-white">
          <div class="row g-3 align-items-center">
            <!-- Search -->
            <div class="col-md-7">
              <div class="input-group">
                <span class="input-group-text bg-light border-0 ps-3 text-muted"><i class="bi bi-search"></i></span>
                <input 
                  type="text" 
                  class="form-control bg-light border-0 py-2" 
                  placeholder="Rechercher une startup par nom ou activité..." 
                  v-model="searchQuery" 
                  @keyup.enter="applyFilters"
                >
                <button class="btn btn-dark px-4 fw-bold" @click="applyFilters">Filtrer</button>
              </div>
            </div>

            <!-- City Filter -->
            <div class="col-md-5">
              <select class="form-select bg-light border-0 py-2" v-model="selectedCity" @change="applyFilters">
                <option value="">Toutes les villes du Cameroun</option>
                <option value="Douala">Douala (Littoral)</option>
                <option value="Yaoundé">Yaoundé (Centre)</option>
                <option value="Bafoussam">Bafoussam (Ouest)</option>
                <option value="Buea">Buea (Silicon Mountain)</option>
                <option value="Garoua">Garoua (Nord)</option>
                <option value="Kribi">Kribi (Sud)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Startups Grid -->
        <div v-if="startups.data && startups.data.length > 0" class="row g-4 mb-5">
          <div v-for="st in startups.data" :key="st.id" class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden d-flex flex-column justify-content-between hover-shadow transition">
              
              <!-- Header / Banner preview with superimposed logo -->
              <div class="position-relative" style="height: 140px; background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
                <img 
                  v-if="st.banner" 
                  :src="st.banner.startsWith('/') ? st.banner : '/' + st.banner" 
                  class="w-100 h-100 object-fit-cover"
                  :alt="st.name"
                >
                <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center text-white-50">
                  <i class="bi bi-building fs-1 opacity-25"></i>
                </div>
                <span class="badge position-absolute top-0 end-0 m-3 px-3 py-1 rounded-pill text-white shadow-sm" style="background-color: var(--tech-green, #00A859); font-size: 11px;">
                  {{ st.legal_type || 'SARL' }}
                </span>

                <!-- Logo superposé sur la bannière -->
                <div class="position-absolute" style="bottom: -22px; left: 20px; z-index: 3;">
                  <div class="rounded-3 border border-2 border-white shadow-sm overflow-hidden bg-white d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <img 
                      v-if="st.logo" 
                      :src="st.logo.startsWith('/') ? st.logo : '/' + st.logo" 
                      :alt="st.name" 
                      class="w-100 h-100 object-fit-contain p-1"
                    >
                    <div v-else class="fw-bold text-success fs-5">
                      {{ (st.name || 'S').charAt(0).toUpperCase() }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Body with spacing for superimposed logo -->
              <div class="p-4 pt-4 d-flex flex-column flex-grow-1 justify-content-between" style="margin-top: 10px;">
                <div>
                  <div class="d-flex justify-content-end mb-2">
                    <span class="badge bg-light text-muted border extra-small rounded-pill px-2 py-1">
                      <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ st.city || 'Cameroun' }}
                    </span>
                  </div>

                  <h4 class="fw-bold text-dark mb-1">
                    <Link :href="'/startups/' + (st.slug || st.id)" class="text-dark text-decoration-none hover-success">
                      {{ st.name }}
                    </Link>
                  </h4>

                <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; min-height: 60px;">
                  {{ st.description?.replace(/<[^>]*>/g, '') }}
                </p>

                <!-- Current Project Block -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                  <div class="extra-small fw-bold text-uppercase text-muted mb-1 d-flex align-items-center">
                    <i class="bi bi-gear-wide-connected text-success me-1"></i> Projet actuellement développé :
                  </div>
                  <div v-if="st.projects && st.projects.length > 0">
                    <Link :href="'/projet/' + (st.projects[0].slug || st.projects[0].id)" class="text-decoration-none fw-bold small text-dark d-flex align-items-center justify-content-between">
                      <span class="text-truncate">{{ st.projects[0].name }}</span>
                      <i class="bi bi-arrow-right-short text-success fs-5"></i>
                    </Link>
                    <small class="text-muted extra-small d-block">{{ st.projects[0].project_type?.toUpperCase() }} &bull; {{ st.projects[0].development_duration || 'En développement actif' }}</small>
                  </div>
                  <div v-else class="text-muted extra-small fst-italic">
                    Cette startup prépare son prochain projet tech.
                  </div>
                </div>

              </div>
            </div>

            <!-- Footer CTA -->
              <div class="p-4 pt-0 d-flex gap-2">
                <Link :href="'/startups/' + (st.slug || st.id)" class="btn btn-outline-dark rounded-pill flex-grow-1 fw-bold btn-sm py-2">
                  Consulter la fiche &rarr;
                </Link>
                <button 
                  type="button" 
                  class="btn btn-light border rounded-pill px-3 btn-sm text-success fw-bold"
                  @click="openContactModal(st)"
                  title="Contacter la startup"
                >
                  <i class="bi bi-chat-dots-fill"></i>
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-5 card border-0 shadow-sm rounded-4 p-5 bg-white">
          <div class="rounded-circle bg-light d-inline-flex p-4 text-muted mb-3 mx-auto fs-1">
            <i class="bi bi-rocket-takeoff"></i>
          </div>
          <h4 class="fw-bold text-dark" v-if="searchQuery">Aucun résultats trouvé pour votre recherche</h4>
          <h4 class="fw-bold text-dark" v-else>Aucune startup trouvée pour ces critères</h4>
          <p class="text-muted small mb-4">Soyez le premier à inscrire votre entreprise dans l'annuaire national Tech237 !</p>
          <div>
            <Link href="/espace-utilisateur/startups/create" class="btn btn-success rounded-pill px-4 py-2 fw-bold text-white shadow-sm" style="background-color: var(--tech-green);">
              Ajouter ma Startup &rarr;
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="startups.links && startups.links.length > 3" class="mt-4 d-flex justify-content-center">
          <nav>
            <ul class="pagination pagination-sm">
              <li v-for="(link, lIdx) in startups.links" :key="lIdx" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                <Link class="page-link rounded-pill mx-1" :href="link.url || '#'" v-html="link.label" />
              </li>
            </ul>
          </nav>
        </div>

      </div>
    </div>

    <!-- STATIC CONTACT MODAL FOR INDEX -->
    <div v-if="selectedStartupForContact" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;" @click.self="selectedStartupForContact = null">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header border-0 pb-0">
            <div>
              <h5 class="modal-title fw-bold text-dark mb-0">
                <i class="bi bi-envelope-paper-heart-fill me-2" style="color: var(--tech-green);"></i>Contacter {{ selectedStartupForContact.name }}
              </h5>
              <small class="text-muted">Un email sera directement transmis au fondateur.</small>
            </div>
            <button type="button" class="btn-close" @click="selectedStartupForContact = null"></button>
          </div>

          <form @submit.prevent="submitContact">
            <div class="modal-body">
              <div v-if="contactSuccess" class="alert alert-success rounded-3 small mb-3">
                <i class="bi bi-check-circle-fill me-1"></i> {{ contactSuccess }}
              </div>

              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small fw-bold">Nom <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="contactForm.nom" placeholder="Votre nom" required>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-bold">Prénom <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="contactForm.prenom" placeholder="Votre prénom" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Votre Adresse Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" v-model="contactForm.email" placeholder="Pour recevoir la réponse" required>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Sujet <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="contactForm.sujet" placeholder="ex: Partenariat, investissement..." required>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Description / Message <span class="text-danger">*</span></label>
                <textarea class="form-control" rows="4" v-model="contactForm.description" placeholder="Écrivez votre message..." required></textarea>
              </div>
            </div>

            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="selectedStartupForContact = null">Fermer</button>
              <button 
                type="submit" 
                class="btn rounded-pill px-4 fw-bold text-white shadow-sm" 
                style="background-color: var(--tech-green);"
                :disabled="contactForm.processing"
              >
                <span v-if="contactForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-send-fill me-1"></i> Envoyer
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  startups: Object,
  filters: Object,
})

const page = usePage()
const authUser = page.props.auth?.user
const heroAd = computed(() => page.props.adBanners?.hero_startups?.[0] || null)
const heroBgImage = computed(() => {
  if (heroAd.value?.image) {
    const img = heroAd.value.image
    return img.startsWith('http') ? img : (img.startsWith('/') ? img : '/' + img)
  }
  return 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=1600&q=80'
})

const searchQuery = ref(props.filters?.search || '')
const selectedCity = ref(props.filters?.city || '')

const selectedStartupForContact = ref(null)
const contactSuccess = ref('')

const contactForm = useForm({
  nom: authUser?.name || '',
  prenom: authUser?.first_name || '',
  email: authUser?.email || '',
  sujet: '',
  description: '',
})

function openContactModal(st) {
  selectedStartupForContact.value = st
  contactSuccess.value = ''
}

function submitContact() {
  if (!selectedStartupForContact.value) return
  contactForm.post(`/startups/${selectedStartupForContact.value.id}/contact`, {
    preserveScroll: true,
    onSuccess: () => {
      contactSuccess.value = 'Votre message a été transmis avec succès !'
      contactForm.reset('sujet', 'description')
      setTimeout(() => {
        selectedStartupForContact.value = null
        contactSuccess.value = ''
      }, 2000)
    }
  })
}

function applyFilters() {
  router.get('/startups', {
    search: searchQuery.value || undefined,
    city: selectedCity.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  })
}
</script>

<style scoped>
.max-w-750 { max-width: 750px; }
.hover-shadow:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}
.transition {
  transition: all 0.25s ease;
}
.hover-success:hover {
  color: var(--tech-green) !important;
}
.extra-small {
  font-size: 11px;
}
</style>
