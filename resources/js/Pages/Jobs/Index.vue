<template>
  <FrontLayout>
    <Head>
      <title>Offres d'Emploi & Stages IT au Cameroun | Tech237</title>
      <meta name="description" content="Trouvez votre prochain emploi tech : Développeur web, mobile, data scientist, DevOps, designer au Cameroun sur Tech237.">
      <meta property="og:title" content="Offres d'Emploi IT | Tech237">
      <meta property="og:description" content="Recrutement tech et opportunités dans les startups avec Tech237.">
    </Head>

    <div class="jobs-index-page py-5 bg-light min-vh-100">
      <div class="container">
        
        <!-- Header Hero with Background Ad Image & Gradient Overlay -->
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
            style="background: linear-gradient(135deg, rgba(11, 37, 89, 0.82) 0%, rgba(6, 21, 45, 0.86) 100%); pointer-events: none;"
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

          <div class="position-absolute end-0 top-0 translate-middle-y me-5 opacity-10 d-none d-lg-block pointer-events-none" style="z-index: 1;">
            <i class="bi bi-briefcase-fill" style="font-size: 260px;"></i>
          </div>

          <div class="row align-items-center g-4 position-relative z-1">
            <div class="col-lg-8">
              <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 shadow-sm text-white" style="background-color: var(--tech-green);">
                <i class="bi bi-laptop me-1"></i> Carrières & Hub Recrutement Tech237
              </span>
              <h1 class="display-6 fw-black text-white mb-2">Opportunités & Offres d'Emploi IT</h1>
              <p class="lead text-light opacity-90 mb-4">Rejoignez les meilleures équipes d'ingénierie logicielle, startups innovantes et entreprises numériques. Postulez en quelques clics.</p>

              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-white text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
                  <i class="bi bi-check-circle-fill text-success me-1"></i> {{ jobs.total || jobs.data?.length || 0 }} poste(s) ouvert(s)
                </span>
                <span class="badge bg-dark-subtle text-light border border-secondary px-3 py-2 rounded-pill">
                  CDI &bull; CDD &bull; Stages pré-emploi &bull; Freelance
                </span>
              </div>
            </div>

            <!-- Filters in Hero -->
            <div class="col-lg-4">
              <div class="card border-0 rounded-4 p-3 bg-white text-dark shadow-lg">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-funnel-fill small"></i>
                  </div>
                  <h6 class="fw-bold mb-0">Filtrer les opportunités</h6>
                </div>
                
                <div class="mb-2">
                  <label class="form-label extra-small fw-bold text-muted mb-1 text-uppercase">Domaine IT</label>
                  <select v-model="filterDomain" class="form-select form-select-sm">
                    <option value="">Tous les domaines de l'IT</option>
                    <option v-for="d in domains" :key="d" :value="d">{{ d }}</option>
                  </select>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <label class="form-label extra-small fw-bold text-muted mb-1 text-uppercase">Contrat</label>
                    <select v-model="filterContract" class="form-select form-select-sm">
                      <option value="">Tous</option>
                      <option v-for="c in contracts" :key="c" :value="c">{{ c }}</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label class="form-label extra-small fw-bold text-muted mb-1 text-uppercase">Ville</label>
                    <input type="text" v-model="filterCity" class="form-control form-control-sm" placeholder="ex: Douala, Yaoundé...">
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label extra-small fw-bold text-muted mb-1 text-uppercase">Mots-clés</label>
                  <input type="text" v-model="filterSearch" class="form-control form-control-sm" placeholder="ex: Laravel, Vue, React, Python...">
                </div>

                <button @click="applyFilters" class="btn btn-danger w-100 rounded-pill btn-sm fw-bold shadow-sm">
                  <i class="bi bi-search me-1"></i> Filtrer les offres
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Jobs Listing -->
        <div class="row g-4 mb-5">
          <div class="col-12">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-grid-fill text-danger me-2"></i>Dernières Opportunités Détectées
              </h4>
              <span class="text-muted small">
                Triées par date de publication
              </span>
            </div>

            <div class="d-flex flex-column gap-3">
              <div 
                v-for="job in jobs.data" 
                :key="job.id" 
                class="card border-0 shadow-sm rounded-4 p-4 bg-white job-card-modern position-relative transition"
              >
                <div class="row align-items-center g-3">
                  
                  <!-- Left: Company Icon / Logo Avatar -->
                  <div class="col-auto d-none d-sm-block">
                    <div class="company-logo-badge rounded-4 shadow-sm d-flex align-items-center justify-content-center text-white fw-bold">
                      <i class="bi bi-briefcase fs-3"></i>
                    </div>
                  </div>

                  <!-- Middle: Job Details & Badges -->
                  <div class="col">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                      <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-bold">
                        <i class="bi bi-code-slash me-1"></i>{{ job.it_domain }}
                      </span>
                      <span class="badge bg-dark text-white px-3 py-1 rounded-pill fw-semibold">
                        {{ job.contract_type }}
                      </span>
                      <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ job.city }}
                      </span>
                      <span v-if="job.has_online_test" class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-semibold">
                        <i class="bi bi-patch-question-fill me-1"></i>Test technique QCM
                      </span>
                    </div>

                    <h4 class="fw-bold mb-1">
                      <Link :href="'/emploi/' + (job.slug || job.id)" class="text-dark text-decoration-none job-title-link">
                        {{ job.title }}
                      </Link>
                    </h4>

                    <div class="d-flex align-items-center gap-2 text-muted small mb-2 flex-wrap">
                      <span class="fw-semibold text-dark">
                        <i class="bi bi-building me-1 text-secondary"></i>{{ job.company_name }}
                      </span>
                      &bull;
                      <span class="text-secondary">
                        Publiée le {{ formatDate(job.created_at) }}
                      </span>
                      &bull;
                      <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 rounded-pill">
                        <i class="bi bi-cash me-1"></i>{{ job.salary_range || 'Selon profil / À négocier' }}
                      </span>
                    </div>

                    <p class="text-muted small mb-0 job-desc-clamp">
                      {{ job.short_description || job.description }}
                    </p>
                  </div>

                  <!-- Right: Deadline & CTA -->
                  <div class="col-lg-3 col-md-4 text-md-end border-start-md ps-md-4">
                    <div class="mb-3">
                      <span class="text-muted extra-small d-block text-uppercase fw-bold">Date limite de dépôt</span>
                      <span class="fw-bold text-dark small d-inline-flex align-items-center gap-1">
                        <i class="bi bi-calendar-event text-danger"></i>
                        {{ formatDeadline(job.deadline_date) }}
                      </span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                      <Link :href="'/emploi/' + (job.slug || job.id)" class="btn btn-outline-dark rounded-pill btn-sm fw-bold px-3">
                        Voir le poste
                      </Link>
                      <Link :href="'/emploi/' + (job.slug || job.id) + '/postuler'" class="btn btn-danger rounded-pill btn-sm fw-bold px-3 shadow-sm">
                        <i class="bi bi-send me-1"></i> Postuler &rarr;
                      </Link>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Empty state -->
              <div v-if="!jobs.data || jobs.data.length === 0" class="text-center py-5 bg-white rounded-4 border shadow-sm p-4">
                <div class="rounded-circle bg-light d-inline-flex p-4 text-muted mb-3">
                  <i class="bi bi-briefcase fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark" v-if="filterSearch">Aucun résultats trouvé pour votre recherche</h5>
                <h5 class="fw-bold text-dark" v-else>Aucune offre ne correspond à vos filtres.</h5>
                <p class="text-muted small mb-3">Essayez d'élargir votre recherche pour voir toutes les opportunités tech disponibles.</p>
                <button @click="resetFilters" class="btn btn-light border rounded-pill px-4 btn-sm fw-bold">
                  Réinitialiser les filtres
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="jobs.links && jobs.links.length > 3" class="d-flex justify-content-center">
          <nav>
            <ul class="pagination pagination-sm">
              <li v-for="(link, lIdx) in jobs.links" :key="lIdx" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                <Link class="page-link rounded-pill mx-1" :href="link.url || '#'" v-html="link.label" />
              </li>
            </ul>
          </nav>
        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const page = usePage()
const heroAd = computed(() => page.props.adBanners?.hero_jobs?.[0] || null)
const heroBgImage = computed(() => {
  if (heroAd.value?.image) {
    const img = heroAd.value.image
    return img.startsWith('http') ? img : (img.startsWith('/') ? img : '/' + img)
  }
  return 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1600&q=80'
})

const props = defineProps({
  jobs: Object,
  domains: Array,
  contracts: Array,
  filters: Object,
})

const filterDomain = ref(props.filters?.domain || '')
const filterContract = ref(props.filters?.contract || '')
const filterCity = ref(props.filters?.city || '')
const filterSearch = ref(props.filters?.search || '')

function applyFilters() {
  router.get('/emplois', {
    domain: filterDomain.value || undefined,
    contract: filterContract.value || undefined,
    city: filterCity.value || undefined,
    search: filterSearch.value || undefined,
  }, {
    preserveState: true,
  })
}

function resetFilters() {
  filterDomain.value = ''
  filterContract.value = ''
  filterCity.value = ''
  filterSearch.value = ''
  applyFilters()
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch (e) {
    return dateStr
  }
}

function formatDeadline(dateStr) {
  if (!dateStr) return 'Ouvert jusqu\'à clôture'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
  } catch (e) {
    return dateStr
  }
}
</script>

<style scoped>
.job-card-modern {
  border: 1px solid #e2e8f0 !important;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.job-card-modern:hover {
  transform: translateY(-4px);
  box-shadow: 0 14px 28px rgba(0, 0, 0, 0.08) !important;
  border-color: #cbd5e1 !important;
}
.company-logo-badge {
  width: 58px;
  height: 58px;
  background: linear-gradient(135deg, #FF004B 0%, #be123c 100%);
}
.job-title-link {
  transition: color 0.2s ease;
}
.job-title-link:hover {
  color: #FF004B !important;
}
.job-desc-clamp {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.5;
}
@media (min-width: 768px) {
  .border-start-md {
    border-left: 1px solid #f1f5f9;
  }
}
.extra-small {
  font-size: 11px;
}
</style>
