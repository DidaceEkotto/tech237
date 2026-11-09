<template>
  <FrontLayout>
    <Head>
      <title>{{ project.name }} - Projet Tech au Cameroun | Tech237</title>
      <meta name="description" head-key="description" :content="project.description?.replace(/<[^>]*>/g, '').substring(0, 160)" />
      <meta property="og:type" head-key="og:type" content="article" />
      <meta property="og:title" head-key="og:title" :content="project.name + ' - Projet Tech | Tech237'" />
      <meta property="og:description" head-key="og:description" :content="project.description?.replace(/<[^>]*>/g, '').substring(0, 160)" />
      <meta property="og:image" head-key="og:image" :content="project.banner ? '/' + project.banner : (project.logo ? '/' + project.logo : '/assets/images/logo.png')" />
      <meta name="twitter:card" head-key="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" head-key="twitter:title" :content="project.name + ' - Tech237'" />
      <meta name="twitter:description" head-key="twitter:description" :content="project.description?.replace(/<[^>]*>/g, '').substring(0, 160)" />
      <meta name="twitter:image" head-key="twitter:image" :content="project.banner ? '/' + project.banner : (project.logo ? '/' + project.logo : '/assets/images/logo.png')" />
    </Head>

    <div class="container py-4">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item"><Link href="/" class="text-danger text-decoration-none">Accueil</Link></li>
          <li class="breadcrumb-item"><Link href="/projets" class="text-danger text-decoration-none">Projets</Link></li>
          <li class="breadcrumb-item active">{{ project.name }}</li>
        </ol>
      </nav>

      <!-- Main Project Header -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div v-if="project.banner" class="w-100 bg-dark" style="max-height: 380px; overflow: hidden;">
          <img :src="'/' + project.banner" class="w-100" style="max-height: 380px; object-fit: cover;">
        </div>
        <div class="card-body p-4 p-md-5">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-start gap-3">
              <div v-if="project.logo" class="rounded-3 border overflow-hidden shadow-sm p-1 bg-white" style="width: 72px; height: 72px; min-width: 72px;">
                <img :src="'/' + project.logo" class="w-100 h-100 object-fit-contain rounded-2" :alt="project.name">
              </div>
              <div>
                <span class="badge bg-danger text-uppercase mb-2">{{ formatProjectType(project.project_type) }}</span>
                <h1 class="fw-bold display-6 mb-2">{{ project.name }}</h1>
                <div class="d-flex align-items-center gap-2 text-muted small flex-wrap">
                  <span v-if="project.startup">
                    <Link :href="'/startups/' + (project.startup.slug || project.startup.id)" class="text-secondary text-decoration-none hover-danger">
                      <i class="bi bi-building me-1"></i>{{ project.startup.name }} ({{ project.startup.legal_type }})
                    </Link>
                  </span>
                  <span v-if="project.user">&bull; Porté par {{ project.user.full_name || project.user.name }}</span>
                  <span v-if="project.development_duration">&bull; Durée : {{ project.development_duration }}</span>
                  <span>&bull; {{ project.views_count }} vues</span>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap align-items-center">
              <!-- Upvote Button (Product Hunt style) -->
              <button 
                @click="toggleUpvote" 
                class="btn rounded-pill px-3 fw-bold d-flex align-items-center gap-2 shadow-sm"
                :class="hasUpvotedState ? 'btn-success text-white' : 'btn-outline-success'"
                :disabled="isUpvoting"
                title="Voter pour ce projet (+2 XP)"
              >
                <i class="bi bi-caret-up-fill fs-5"></i>
                <span>{{ hasUpvotedState ? 'Upvoté' : 'Upvote' }}</span>
                <span class="badge bg-white text-dark rounded-pill shadow-xs">{{ upvotesCount }}</span>
              </button>

              <!-- Bookmark Button -->
              <button 
                @click="toggleBookmark" 
                class="btn rounded-pill px-3 fw-bold d-flex align-items-center gap-1 shadow-sm"
                :class="isBookmarkedState ? 'btn-warning text-dark' : 'btn-outline-secondary'"
                :disabled="isBookmarking"
                :title="isBookmarkedState ? 'Retirer des favoris' : 'Sauvegarder dans mes favoris'"
              >
                <i :class="isBookmarkedState ? 'bi bi-bookmark-fill' : 'bi bi-bookmark'"></i>
                <span class="d-none d-sm-inline">{{ isBookmarkedState ? 'Sauvegardé' : 'Favoris' }}</span>
              </button>

              <a v-if="project.website_url" :href="project.website_url" target="_blank" class="btn btn-danger rounded-pill px-4 fw-bold">
                <i class="bi bi-box-arrow-up-right me-1"></i> Voir la Démo
              </a>
              <a v-if="project.repo_url" :href="project.repo_url" target="_blank" class="btn btn-dark rounded-pill px-3">
                <i class="bi bi-github me-1"></i> GitHub
              </a>
              <button @click="openReportModal" class="btn btn-outline-secondary rounded-pill btn-sm" title="Signaler ce projet">
                <i class="bi bi-flag"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Left Column: Description, Features & Devlog Timeline -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h4 class="fw-bold mb-3">À propos du projet</h4>
            <div class="text-secondary lh-lg mb-4 project-wysiwyg-content" v-html="project.description"></div>

            <!-- Dynamic Features List if present -->
            <div v-if="project.features && project.features.length" class="mb-4">
              <h5 class="fw-bold text-dark mb-3"><i class="bi bi-check2-circle text-danger me-2"></i>Fonctionnalités Clés</h5>
              <div class="row g-2">
                <div v-for="(feat, idx) in project.features" :key="idx" class="col-md-6">
                  <div class="p-3 bg-light rounded-3 border h-100 d-flex align-items-center gap-2">
                    <i class="bi bi-check-lg text-success fs-5"></i>
                    <span class="fw-semibold text-dark small">{{ feat }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- GitHub-Style Devlog / Problem Resolution Timeline -->
            <div class="border-top pt-4">
              <h4 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-git text-danger"></i> Journal de bord & Résolution de défis (Devlog)
              </h4>
              <p class="text-muted small">
                Historique des étapes techniques, des obstacles rencontrés et des solutions concrètes mises en œuvre par l'équipe.
              </p>

              <div v-if="project.timelines && project.timelines.length" class="position-relative ps-4 mt-4 border-start border-2 border-danger-subtle ms-3">
                <div v-for="step in project.timelines" :key="step.id" class="mb-4 position-relative">
                  <!-- Timeline Dot -->
                  <div class="position-absolute rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; left: -37px; top: 0;">
                    <i class="bi bi-check2 small"></i>
                  </div>

                  <div class="card border-0 shadow-sm rounded-3 p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h6 class="fw-bold text-dark mb-0">{{ step.title }}</h6>
                      <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ formatDevlogDate(step.event_date || step.created_at) }}</small>
                    </div>

                    <div v-if="step.user && step.user.id !== project.user_id" class="mb-2">
                      <span class="badge bg-light text-dark border small">
                        <i class="bi bi-person-check text-success me-1"></i>Contributeur : {{ step.user.full_name || step.user.name }}
                      </span>
                    </div>

                    <p v-if="step.description" class="small text-secondary mb-2">{{ step.description }}</p>

                    <!-- Associated Features Tagged -->
                    <div v-if="step.features_impacted && step.features_impacted.length" class="mb-2">
                      <span v-for="fi in step.features_impacted" :key="fi" class="badge bg-secondary-subtle text-dark me-1 small">
                        <i class="bi bi-tag me-1"></i>{{ fi }}
                      </span>
                    </div>

                    <div v-if="step.problem_encountered" class="alert alert-danger py-2 px-3 small mb-2 border-0">
                      <strong><i class="bi bi-exclamation-triangle me-1"></i>Défi rencontré :</strong>
                      <div>{{ step.problem_encountered }}</div>
                    </div>

                    <div v-if="step.solution_applied" class="alert alert-success py-2 px-3 small mb-2 border-0">
                      <strong><i class="bi bi-lightbulb me-1"></i>Solution appliquée :</strong>
                      <div>{{ step.solution_applied }}</div>
                    </div>

                    <div v-if="step.images && step.images.length" class="mt-2">
                      <img :src="'/' + step.images[0]" class="img-fluid rounded border" style="max-height: 200px;">
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-muted small p-3 bg-light rounded-3">
                Aucun historique de devlog publié pour le moment.
              </div>
            </div>

          </div>
        </div>

        <!-- Right Column: Video Embed, Stores, Interactive Gallery & Tech Stack -->
        <div class="col-lg-4">
          
          <!-- YouTube Presentation Video Embed -->
          <div v-if="youtubeEmbedUrl" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3 d-flex align-items-center">
              <i class="bi bi-play-circle-fill text-danger me-2"></i>Vidéo de présentation
            </h5>
            <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm">
              <iframe :src="youtubeEmbedUrl" title="Vidéo de présentation" allowfullscreen></iframe>
            </div>
          </div>

          <!-- Store Download Buttons & Links (Huawei AppGallery, Google Play, App Store) -->
          <div v-if="project.appgallery_url || project.playstore_url || project.appstore_url || project.website_url" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3"><i class="bi bi-download text-danger me-2"></i>Téléchargements & Accès</h5>
            <div class="d-flex flex-column gap-2">
              <a v-if="project.appgallery_url" :href="project.appgallery_url" target="_blank" class="btn btn-danger rounded-pill fw-semibold text-start px-3 py-2">
                <i class="bi bi-bag-check-fill me-2 fs-5 align-middle"></i> Huawei AppGallery
              </a>
              <a v-if="project.playstore_url" :href="project.playstore_url" target="_blank" class="btn btn-dark rounded-pill fw-semibold text-start px-3 py-2">
                <i class="bi bi-google-play me-2 fs-5 align-middle text-warning"></i> Google Play Store
              </a>
              <a v-if="project.appstore_url" :href="project.appstore_url" target="_blank" class="btn btn-dark rounded-pill fw-semibold text-start px-3 py-2">
                <i class="bi bi-apple me-2 fs-5 align-middle"></i> Apple App Store
              </a>
              <a v-if="project.website_url" :href="project.website_url" target="_blank" class="btn btn-outline-dark rounded-pill fw-semibold text-start px-3 py-2">
                <i class="bi bi-globe me-2 fs-5 align-middle text-primary"></i> Site Web / Web App
              </a>
            </div>
          </div>

          <!-- Interactive Photo Gallery Lightbox -->
          <div v-if="allGalleryImages.length" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold mb-0"><i class="bi bi-images text-danger me-2"></i>Galerie Photos ({{ allGalleryImages.length }})</h5>
            </div>
            
            <div class="row g-2">
              <div v-for="(img, idx) in allGalleryImages.slice(0, 6)" :key="idx" class="col-4">
                <div class="position-relative rounded-3 overflow-hidden cursor-pointer border" style="height: 75px;" @click="openLightbox(idx)">
                  <img :src="'/' + img.image_path" class="w-100 h-100 object-fit-cover hover-scale" :alt="img.caption || project.name">
                  <div v-if="idx === 5 && allGalleryImages.length > 6" class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center text-white fw-bold">
                    +{{ allGalleryImages.length - 6 }}
                  </div>
                </div>
              </div>
            </div>

            <button v-if="allGalleryImages.length" @click="openLightbox(0)" class="btn btn-outline-danger btn-sm rounded-pill w-100 mt-3 fw-bold">
              <i class="bi bi-arrows-fullscreen me-1"></i> Ouvrir la Galerie Plein Écran
            </button>
          </div>

          <!-- Tech Stack -->
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3"><i class="bi bi-stack text-danger me-2"></i>Stack Technique</h5>
            
            <label class="small text-muted fw-bold d-block mb-1">Langages :</label>
            <div class="d-flex flex-wrap gap-1 mb-3">
              <span v-for="lang in displayedLanguages" :key="lang" class="badge bg-light text-dark border">
                {{ lang }}
              </span>
              <span v-if="!displayedLanguages.length" class="text-muted small">Non renseigné</span>
            </div>

            <label class="small text-muted fw-bold d-block mb-1">Frameworks & Moteurs :</label>
            <div class="d-flex flex-wrap gap-1 mb-3">
              <span v-for="fw in displayedFrameworks" :key="fw" class="badge bg-dark text-white">
                {{ fw }}
              </span>
              <span v-if="!displayedFrameworks.length" class="text-muted small">Non renseigné</span>
            </div>

            <label class="small text-muted fw-bold d-block mb-1">Catégorie :</label>
            <div class="d-flex flex-wrap gap-1">
              <span class="badge bg-danger-subtle text-danger">{{ formatProjectType(project.project_type) }}</span>
              <span v-for="sub in (project.app_types || project.game_types || [])" :key="sub" class="badge bg-secondary-subtle text-dark">
                {{ sub }}
              </span>
            </div>
          </div>

          <!-- Startup Card -->
          <div v-if="project.startup" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3"><i class="bi bi-building text-danger me-2"></i>Startup Porteuse</h5>
            <div class="d-flex align-items-center gap-2 mb-2">
              <Link :href="'/startups/' + (project.startup.slug || project.startup.id)" class="text-decoration-none">
                <img v-if="project.startup.logo" :src="'/' + project.startup.logo" class="rounded border p-1" style="width: 48px; height: 48px; object-fit: contain;">
              </Link>
              <div>
                <Link :href="'/startups/' + (project.startup.slug || project.startup.id)" class="fw-bold text-dark fs-6 mb-0 text-decoration-none hover-danger d-block">
                  {{ project.startup.name }}
                </Link>
                <div class="small text-muted">{{ project.startup.legal_type }} &bull; {{ project.startup.city }}</div>
              </div>
            </div>
            <div class="small text-secondary mb-3">{{ project.startup.description }}</div>
            
            <div v-if="project.startup.needs_funding" class="alert alert-info py-2 px-3 small mb-3">
              <i class="bi bi-cash-coin me-1"></i> Recherche investissement : 
              <strong>{{ Number(project.startup.funding_amount).toLocaleString() }} FCFA</strong>
            </div>

            <Link :href="'/startups/' + (project.startup.slug || project.startup.id)" class="btn btn-outline-danger btn-sm rounded-pill w-100 fw-bold">
              <i class="bi bi-box-arrow-up-right me-1"></i> Découvrir la startup
            </Link>
          </div>

          <!-- Collaborateurs du Projet Section -->
          <div v-if="project.active_collaborators && project.active_collaborators.length" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3 d-flex align-items-center">
              <i class="bi bi-people-fill text-danger me-2"></i>
              Collaborateurs du projet ({{ project.active_collaborators.length }})
            </h5>
            <div class="d-flex flex-column gap-3">
              <div 
                v-for="collab in project.active_collaborators" 
                :key="collab.id" 
                class="d-flex align-items-center gap-3 p-2 rounded-3 bg-light border"
              >
                <img 
                  v-if="collab.user?.avatar" 
                  :src="'/' + collab.user.avatar" 
                  class="rounded-circle border" 
                  style="width: 44px; height: 44px; object-fit: cover;"
                  :alt="collab.user?.name"
                >
                <div 
                  v-else 
                  class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" 
                  style="width: 44px; height: 44px; min-width: 44px; font-size: 1.1rem;"
                >
                  {{ (collab.user?.name || 'C').charAt(0).toUpperCase() }}
                </div>
                <div class="flex-grow-1 text-truncate">
                  <div class="fw-bold text-dark text-truncate">{{ collab.user?.full_name || collab.user?.name }}</div>
                  <small class="badge bg-secondary-subtle text-secondary small">{{ collab.role || 'Contributeur & Dev' }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- FULLSCREEN LIGHTBOX MODAL WITH THUMBNAILS STRIP & CONTROLS -->
    <div v-if="lightboxOpen" class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column justify-content-between p-3" style="background-color: rgba(0, 0, 0, 0.95); z-index: 9999;" @keyup.esc="closeLightbox" tabindex="0">
      <!-- Top Bar: Counter & Close -->
      <div class="d-flex justify-content-between align-items-center text-white px-2">
        <span class="fw-bold small bg-dark bg-opacity-75 px-3 py-1 rounded-pill">
          {{ activeImageIndex + 1 }} / {{ allGalleryImages.length }}
        </span>
        <button @click="closeLightbox" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px;">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <!-- Center: Main Preview with Next / Prev buttons -->
      <div class="d-flex align-items-center justify-content-between px-2 flex-grow-1 position-relative" style="max-height: 75vh;">
        <button @click="prevImage" class="btn btn-dark bg-opacity-50 text-white rounded-circle position-absolute start-0 ms-3 shadow" style="width: 48px; height: 48px; z-index: 10;">
          <i class="bi bi-chevron-left fs-4"></i>
        </button>

        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center">
          <img :src="'/' + currentLightboxImage.image_path" class="img-fluid rounded-3 shadow-lg" style="max-height: 70vh; max-width: 90vw; object-fit: contain;">
          <p v-if="currentLightboxImage.caption" class="text-white-50 small mt-2 mb-0">{{ currentLightboxImage.caption }}</p>
        </div>

        <button @click="nextImage" class="btn btn-dark bg-opacity-50 text-white rounded-circle position-absolute end-0 me-3 shadow" style="width: 48px; height: 48px; z-index: 10;">
          <i class="bi bi-chevron-right fs-4"></i>
        </button>
      </div>

      <!-- Bottom: Thumbnails Strip -->
      <div class="d-flex justify-content-center gap-2 overflow-x-auto py-2 px-3 bg-dark bg-opacity-50 rounded-4 mx-auto" style="max-width: 90vw;">
        <div 
          v-for="(img, idx) in allGalleryImages" 
          :key="idx" 
          @click="activeImageIndex = idx"
          class="rounded-2 overflow-hidden cursor-pointer border-2 transition-all flex-shrink-0"
          :class="idx === activeImageIndex ? 'border border-danger opacity-100 scale-105' : 'border border-secondary opacity-50'"
          style="width: 64px; height: 48px;"
        >
          <img :src="'/' + img.image_path" class="w-100 h-100 object-fit-cover">
        </div>
      </div>
    </div>

    <!-- Report Modal -->
    <div v-if="showReportModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title"><i class="bi bi-flag text-danger me-2"></i>Signaler ce projet</h5>
            <button type="button" class="btn-close btn-close-white" @click="showReportModal = false"></button>
          </div>
          <form @submit.prevent="submitReport">
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label small fw-bold">Motif du signalement <span class="text-danger">*</span></label>
                <select class="form-select" v-model="reportForm.reason" required>
                  <option value="Contenu inapproprié">Contenu inapproprié</option>
                  <option value="Violation de propriété intellectuelle">Violation de propriété intellectuelle</option>
                  <option value="Informations trompeuses / Fausses">Informations trompeuses / Fausses</option>
                  <option value="Autre motif">Autre motif</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold">Détails complémentaires</label>
                <textarea class="form-control" rows="3" v-model="reportForm.details" placeholder="Précisez votre remarque..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light" @click="showReportModal = false">Annuler</button>
              <button type="submit" class="btn btn-danger" :disabled="reportForm.processing">Envoyer le signalement</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  project: Object,
  relatedProjects: Array,
  hasUpvoted: Boolean,
  isBookmarked: Boolean,
})

const hasUpvotedState = ref(props.hasUpvoted || false)
const upvotesCount = ref(props.project.upvotes_count || 0)
const isUpvoting = ref(false)

const isBookmarkedState = ref(props.isBookmarked || false)
const isBookmarking = ref(false)

function toggleUpvote() {
  if (isUpvoting.value) return
  isUpvoting.value = true

  fetch(`/projets/${props.project.id}/upvote`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    }
  }).then(r => {
    if (r.status === 401) {
      window.location.href = '/login'
      return
    }
    return r.json()
  }).then(data => {
    if (data && data.success) {
      hasUpvotedState.value = data.upvoted
      upvotesCount.value = data.upvotes_count
    }
  }).finally(() => {
    isUpvoting.value = false
  })
}

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
      item_type: 'project',
      item_id: props.project.id,
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

const showReportModal = ref(false)
const lightboxOpen = ref(false)
const activeImageIndex = ref(0)

const reportForm = useForm({
  target_type: 'project',
  target_id: props.project.id,
  reason: 'Contenu inapproprié',
  details: '',
})

// Unified Gallery Images from ProjectImage models or JSON gallery
const allGalleryImages = computed(() => {
  if (props.project.images && props.project.images.length) {
    return props.project.images.map(img => ({
      image_path: img.image_path,
      caption: img.caption || ''
    }))
  }
  if (props.project.gallery && Array.isArray(props.project.gallery)) {
    return props.project.gallery.map(p => ({
      image_path: typeof p === 'string' ? p : p.path,
      caption: typeof p === 'object' ? p.caption : ''
    }))
  }
  return []
})

const currentLightboxImage = computed(() => {
  return allGalleryImages.value[activeImageIndex.value] || {}
})

function openLightbox(idx) {
  activeImageIndex.value = idx
  lightboxOpen.value = true
}

function closeLightbox() {
  lightboxOpen.value = false
}

function nextImage() {
  if (activeImageIndex.value < allGalleryImages.value.length - 1) {
    activeImageIndex.value++
  } else {
    activeImageIndex.value = 0
  }
}

function prevImage() {
  if (activeImageIndex.value > 0) {
    activeImageIndex.value--
  } else {
    activeImageIndex.value = allGalleryImages.value.length - 1
  }
}

function formatProjectType(type) {
  if (!type) return 'Non spécifié'
  const map = {
    web: 'Application Web',
    mobile: 'Application Mobile',
    desktop: 'Logiciel Desktop',
    game: 'Jeu Vidéo',
    ia: 'Intelligence Artificielle',
    iot: 'IoT / Embarqué',
    saas: 'Solution SaaS',
    api: 'API / Backend',
  }
  const key = String(type).toLowerCase().trim()
  return map[key] || type
}

function formatDevlogDate(dateStr) {
  if (!dateStr) return 'Étape validée'
  try {
    const d = new Date(dateStr)
    if (isNaN(d.getTime())) return dateStr
    return d.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    })
  } catch (e) {
    return dateStr
  }
}

const displayedLanguages = computed(() => {
  if (props.project.programming_languages && props.project.programming_languages.length) {
    return props.project.programming_languages.map(l => l.name || l)
  }
  if (Array.isArray(props.project.languages)) {
    return props.project.languages.map(l => (typeof l === 'object' && l ? l.name : l)).filter(Boolean)
  }
  if (typeof props.project.languages === 'string' && props.project.languages.trim()) {
    try {
      const parsed = JSON.parse(props.project.languages)
      if (Array.isArray(parsed)) return parsed.map(l => (typeof l === 'object' && l ? l.name : l)).filter(Boolean)
    } catch(e) {}
    return [props.project.languages]
  }
  return []
})

const displayedFrameworks = computed(() => {
  if (props.project.frameworks_list && props.project.frameworks_list.length) {
    return props.project.frameworks_list.map(f => f.name || f)
  }
  if (Array.isArray(props.project.frameworks_engines)) {
    return props.project.frameworks_engines.map(f => (typeof f === 'object' && f ? f.name : f)).filter(Boolean)
  }
  if (typeof props.project.frameworks_engines === 'string' && props.project.frameworks_engines.trim()) {
    try {
      const parsed = JSON.parse(props.project.frameworks_engines)
      if (Array.isArray(parsed)) return parsed.map(f => (typeof f === 'object' && f ? f.name : f)).filter(Boolean)
    } catch(e) {}
    return [props.project.frameworks_engines]
  }
  return []
})

const youtubeEmbedUrl = computed(() => {
  const url = props.project.youtube_presentation_url || props.project.youtube_videos?.[0] || props.project.timelines?.find(t => t.youtube_url)?.youtube_url
  if (!url) return null
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/
  const match = url.match(regExp)
  return (match && match[2].length === 11) ? `https://www.youtube.com/embed/${match[2]}` : null
})

function openReportModal() {
  showReportModal.value = true
}

function submitReport() {
  reportForm.post('/api/report', {
    onSuccess: () => {
      showReportModal.value = false
      reportForm.reset()
      alert('Signalement bien reçu.')
    },
  })
}
</script>

<style scoped>
.hover-scale {
  transition: transform 0.2s ease;
}
.hover-scale:hover {
  transform: scale(1.05);
}
.cursor-pointer {
  cursor: pointer;
}
.hover-danger:hover {
  color: #dc3545 !important;
}
:deep(.project-wysiwyg-content table) {
  width: 100%;
  margin: 1.5rem 0;
  border-collapse: collapse;
}
:deep(.project-wysiwyg-content th),
:deep(.project-wysiwyg-content td) {
  border: 1px solid #e2e8f0;
  padding: 10px 14px;
}
:deep(.project-wysiwyg-content img) {
  max-width: 100%;
  height: auto;
  border-radius: 12px;
}
:deep(.project-wysiwyg-content .ratio) {
  position: relative;
  width: 100%;
}
:deep(.project-wysiwyg-content blockquote) {
  border-left: 4px solid #FF004B;
  padding-left: 1rem;
  color: #64748b;
  font-style: italic;
}
</style>
