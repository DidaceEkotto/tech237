<template>
  <FrontLayout>
    <Head>
      <title>{{ job.title }} | Offre d'Emploi IT Cameroun - Tech237</title>
      <meta name="description" head-key="description" :content="(job.short_description || job.description)?.replace(/<[^>]*>/g, '').substring(0, 160)">
      <meta property="og:type" head-key="og:type" content="article">
      <meta property="og:title" head-key="og:title" :content="job.title + ' | Tech237 Emplois IT'">
      <meta property="og:description" head-key="og:description" :content="(job.short_description || job.description)?.replace(/<[^>]*>/g, '').substring(0, 160)">
      <meta property="og:image" head-key="og:image" :content="job.creator_user?.avatar ? '/' + job.creator_user.avatar : (job.company_logo ? '/' + job.company_logo : '/assets/images/default-job.png')">
      <meta name="twitter:card" head-key="twitter:card" content="summary_large_image">
      <meta name="twitter:title" head-key="twitter:title" :content="job.title + ' | Offre Tech237'">
      <meta name="twitter:description" head-key="twitter:description" :content="(job.short_description || job.description)?.replace(/<[^>]*>/g, '').substring(0, 160)">
      <meta name="twitter:image" head-key="twitter:image" :content="job.creator_user?.avatar ? '/' + job.creator_user.avatar : (job.company_logo ? '/' + job.company_logo : '/assets/images/default-job.png')">
    </Head>

    <div class="job-show-page py-5 bg-light min-vh-100">
      <div class="container">
        
        <!-- Flash Alert if redirected after application -->
        <div v-if="$page.props.flash?.success" class="alert alert-success rounded-4 border-0 shadow-sm p-4 mb-4 d-flex align-items-center gap-3">
          <i class="bi bi-check-circle-fill fs-2 text-success"></i>
          <div>
            <h6 class="fw-bold mb-1">Candidature transmise avec succès !</h6>
            <p class="small mb-0">{{ $page.props.flash.success }}</p>
          </div>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/emplois" class="text-decoration-none text-muted">Offres d'Emploi</Link></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 320px;" aria-current="page">{{ job.title }}</li>
          </ol>
        </nav>

        <div class="row g-4">
          <!-- Main Content (8 cols) -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
              
              <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="badge bg-danger text-uppercase px-3 py-2 rounded-pill fw-bold">
                    {{ job.contract_type }}
                  </span>
                  <span class="badge bg-dark text-white px-3 py-2 rounded-pill">
                    <i class="bi bi-geo-alt-fill me-1"></i> {{ job.city }}
                  </span>
                  <span v-if="job.has_online_test" class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-patch-question-fill me-1"></i>Test d'évaluation technique
                  </span>
                </div>

                <!-- Bookmark Button -->
                <button 
                  @click="toggleBookmark" 
                  class="btn btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm"
                  :class="isBookmarkedState ? 'btn-warning text-dark' : 'btn-outline-secondary'"
                  :disabled="isBookmarking"
                  :title="isBookmarkedState ? 'Retirer des favoris' : 'Sauvegarder cette offre (+2 XP)'"
                >
                  <i :class="isBookmarkedState ? 'bi bi-bookmark-fill' : 'bi bi-bookmark'"></i>
                  <span>{{ isBookmarkedState ? 'Offre sauvegardée' : 'Sauvegarder l\'offre' }}</span>
                </button>
              </div>

              <!-- Domains Badges -->
              <div v-if="job.domain && (Array.isArray(job.domain) ? job.domain.length : true)" class="d-flex flex-wrap gap-1 mb-3">
                <span v-for="d in (Array.isArray(job.domain) ? job.domain : [job.domain])" :key="d" class="badge bg-light text-secondary border px-3 py-1 rounded-pill small">
                  <i class="bi bi-cpu text-danger me-1"></i>{{ d }}
                </span>
              </div>

              <h1 class="display-6 fw-bold text-dark mb-3">{{ job.title }}</h1>
              
              <p v-if="job.short_description" class="lead text-muted border-start border-4 border-danger ps-3 mb-4">
                {{ job.short_description }}
              </p>

              <!-- Quick overview strip -->
              <div class="row g-3 p-3 bg-light rounded-4 mb-4">
                <div class="col-sm-4">
                  <small class="text-muted d-block">Rémunération :</small>
                  <strong class="text-dark">
                    {{ job.salary_type === 'fixed' && job.salary_amount ? Number(job.salary_amount).toLocaleString() + ' FCFA / mois' : (job.salary_range || 'À négocier') }}
                  </strong>
                </div>
                <div class="col-sm-4">
                  <small class="text-muted d-block">Localisation :</small>
                  <strong class="text-dark">{{ job.city }}</strong>
                </div>
                <div class="col-sm-4">
                  <small class="text-muted d-block">Date limite de dépôt :</small>
                  <strong :class="isDeadlinePassed ? 'text-danger' : 'text-dark'">
                    <i class="bi bi-calendar-check text-danger me-1"></i>{{ formatDeadline(job.deadline_date) }}
                  </strong>
                </div>
              </div>

              <!-- Job Description -->
              <h4 class="fw-bold text-dark mb-3">Description du poste & Missions</h4>
              <div class="text-secondary mb-4 leading-relaxed whitespace-pre-line" style="font-size: 15px; line-height: 1.8;">
                {{ job.description }}
              </div>

              <!-- Technical test badge if present -->
              <div v-if="job.has_online_test" class="alert alert-primary border-0 rounded-4 p-3 d-flex align-items-center gap-3">
                <i class="bi bi-patch-question-fill fs-2 text-primary"></i>
                <div>
                  <strong class="d-block text-dark">Cette offre comporte une évaluation technique en ligne.</strong>
                  <small class="text-muted">Vous répondrez directement au questionnaire lors du formulaire de dépôt de candidature sur la page dédiée.</small>
                </div>
              </div>
            </div>
          </div>

          <!-- Apply Sidebar (4 cols) -->
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 85px; z-index: 10;">
              <h5 class="fw-bold text-dark mb-3"><i class="bi bi-send-check-fill text-danger me-2"></i>Postuler à cette offre</h5>

              <!-- Already Applied -->
              <div v-if="hasApplied" class="alert alert-success rounded-4 border-0 p-3 mb-3">
                <i class="bi bi-check-circle-fill me-1"></i> Vous avez déjà postulé à cette offre. Votre candidature est actuellement entre les mains du recruteur !
              </div>

              <!-- Deadline Passed Notice -->
              <div v-else-if="isDeadlinePassed" class="alert alert-secondary rounded-4 border-0 p-3 mb-3 text-center">
                <i class="bi bi-calendar-x fs-3 d-block text-muted mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Candidatures clôturées</h6>
                <p class="small text-muted mb-0">La date limite de dépôt ({{ formatDeadline(job.deadline_date) }}) est passée pour cette opportunité.</p>
              </div>

              <!-- Apply Button Active (Direct Page, NO MODAL) -->
              <div v-else class="mb-3">
                <p class="text-muted small mb-3">
                  Préparez votre CV au format Word (.doc, .docx) ou PDF (.pdf) ainsi qu'une brève présentation de votre parcours.
                </p>
                <Link 
                  :href="'/emploi/' + (job.slug || job.id) + '/postuler'" 
                  class="btn btn-danger w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                >
                  <i class="bi bi-file-earmark-arrow-up fs-5"></i>
                  <span>Déposer ma candidature</span>
                  <i class="bi bi-arrow-right"></i>
                </Link>
              </div>

              <!-- Similar Jobs -->
              <div v-if="similarJobs && similarJobs.length > 0" class="pt-4 border-top mt-4">
                <h6 class="fw-bold text-dark mb-3">Autres opportunités IT</h6>
                <div class="d-flex flex-column gap-2">
                  <div v-for="sim in similarJobs" :key="sim.id" class="border-bottom pb-2">
                    <Link :href="'/emploi/' + (sim.slug || sim.id)" class="fw-semibold small text-dark text-decoration-none hover-danger d-block mb-1">
                      {{ sim.title }}
                    </Link>
                    <small class="text-muted">{{ sim.city }} &bull; {{ sim.contract_type }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  job: Object,
  hasApplied: Boolean,
  similarJobs: Array,
  isBookmarked: Boolean,
})

const isBookmarkedState = ref(props.isBookmarked || false)
const isBookmarking = ref(false)

function toggleBookmark() {
  if (isBookmarking.value) return
  isBookmarking.value = true

  fetch('/espace-utilisateur/favoris/toggle', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify({
      item_type: 'job',
      item_id: props.job.id,
    })
  }).then(r => {
    if (r.status === 401) {
      window.location.href = '/login'
      return
    }
    return r.json()
  }).then(data => {
    if (data && data.success) {
      isBookmarkedState.value = data.bookmarked
    }
  }).finally(() => {
    isBookmarking.value = false
  })
}

const isDeadlinePassed = computed(() => {
  if (!props.job.deadline_date) return false
  const deadline = new Date(props.job.deadline_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return deadline < today
})

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
.whitespace-pre-line {
  white-space: pre-line;
}
.hover-danger:hover {
  color: #FF004B !important;
}
</style>
