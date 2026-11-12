<template>
  <FrontLayout>
    <Head title="Mes Formulaires & Sondages • Tech237" />

    <div class="forms-index-page py-5 bg-slate-50 min-vh-100" style="background-color: #f8fafc;">
      <div class="container">
        
        <!-- Hero Header -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-4 text-white position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #071738 0%, #0B2559 50%, #061e47 100%);">
          <div class="position-absolute end-0 top-50 translate-middle-y me-4 opacity-10 d-none d-lg-block pointer-events-none">
            <i class="bi bi-ui-checks-grid" style="font-size: 260px; color: #00A859;"></i>
          </div>

          <div class="row align-items-center g-4 position-relative z-1">
            <div class="col-lg-8">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" 
                   style="background: rgba(0, 168, 89, 0.18); border: 1px solid rgba(0, 168, 89, 0.4); color: #34d399; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                <span class="d-inline-block rounded-circle bg-success" style="width: 8px; height: 8px; box-shadow: 0 0 10px #00A859;"></span>
                <span>Outil Créateur • Tech237 Forms</span>
              </div>

              <h1 class="display-6 fw-black text-white mb-3" style="letter-spacing: -0.5px;">
                Formulaires &amp; Sondages Interactifs
              </h1>
              <p class="lead text-light mb-4" style="max-width: 650px; font-size: 1.1rem; line-height: 1.6; color: #e2e8f0;">
                Créez des formulaires puissants en mode classique ou conversationnel (Typeform), intégrez des dépôts de code, partagez vos liens et QR Codes, et recevez vos alertes instantanément par email.
              </p>

              <div class="d-flex align-items-center gap-3 flex-wrap">
                <Link href="/espace-utilisateur/formulaires/creer" 
                      class="btn btn-lg rounded-pill px-4 py-2 fw-bold text-white shadow d-flex align-items-center gap-2" 
                      style="background-color: #00A859; transition: 0.25s;">
                  <i class="bi bi-plus-circle-fill fs-5"></i>
                  <span>Créer un formulaire</span>
                </Link>

                <Link href="/espace-utilisateur/formulaires/guide" 
                      class="btn btn-lg btn-outline-light rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2">
                  <i class="bi bi-book-half"></i>
                  <span>Guide d'utilisation &amp; QR Code</span>
                </Link>

                <Link href="/espace-utilisateur" 
                      class="btn btn-sm btn-link text-white-50 text-decoration-none ms-md-2">
                  <i class="bi bi-arrow-left me-1"></i> Retour au tableau de bord
                </Link>
              </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="col-lg-4">
              <div class="card border-0 rounded-4 p-4 text-dark shadow bg-white">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2 text-dark">
                  <i class="bi bi-graph-up-arrow text-success"></i> Vue d'ensemble
                </h6>
                <div class="row g-2 text-center">
                  <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                      <div class="h3 fw-black text-dark mb-0">{{ forms.length }}</div>
                      <div class="extra-small text-muted fw-bold text-uppercase">Formulaires</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                      <div class="h3 fw-black text-success mb-0">{{ totalResponsesCount }}</div>
                      <div class="extra-small text-muted fw-bold text-uppercase">Réponses reçues</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                      <div class="h3 fw-black text-primary mb-0">{{ totalViewsCount }}</div>
                      <div class="extra-small text-muted fw-bold text-uppercase">Vues totales</div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                      <div class="h3 fw-black text-warning mb-0">{{ globalConversionRate }}%</div>
                      <div class="extra-small text-muted fw-bold text-uppercase">Conversion</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div class="d-flex align-items-center gap-2">
            <button 
              type="button" 
              class="btn btn-sm rounded-pill px-3 py-2 fw-bold"
              :class="filterStatus === 'all' ? 'btn-dark' : 'btn-white bg-white border text-muted'"
              @click="filterStatus = 'all'"
            >
              Tous ({{ forms.length }})
            </button>
            <button 
              type="button" 
              class="btn btn-sm rounded-pill px-3 py-2 fw-bold"
              :class="filterStatus === 'published' ? 'btn-success text-white' : 'btn-white bg-white border text-muted'"
              @click="filterStatus = 'published'"
            >
              En ligne ({{ publishedCount }})
            </button>
            <button 
              type="button" 
              class="btn btn-sm rounded-pill px-3 py-2 fw-bold"
              :class="filterStatus === 'draft' ? 'btn-secondary text-white' : 'btn-white bg-white border text-muted'"
              @click="filterStatus = 'draft'"
            >
              Brouillons ({{ draftCount }})
            </button>
          </div>

          <div class="input-group" style="max-width: 320px;">
            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
              <i class="bi bi-search"></i>
            </span>
            <input 
              type="text" 
              v-model="searchQuery" 
              class="form-control border-start-0 rounded-end-pill py-2" 
              placeholder="Rechercher un formulaire..." 
            />
          </div>
        </div>

        <!-- Form Cards Grid -->
        <div v-if="filteredForms.length > 0" class="row g-4">
          <div v-for="form in filteredForms" :key="form.id" class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-shadow transition">
              
              <!-- Color Accent Bar -->
              <div :style="{ backgroundColor: form.theme_color || '#00A859', height: '6px' }"></div>

              <div class="card-body p-4 d-flex flex-column">
                
                <!-- Status & Mode Badges -->
                <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                  <span class="badge rounded-pill px-3 py-1 fw-bold" 
                        :class="getStatusBadgeClass(form)">
                    {{ getStatusLabel(form) }}
                  </span>

                  <span class="badge bg-light text-dark border rounded-pill px-2 py-1 extra-small d-inline-flex align-items-center gap-1">
                    <i :class="form.display_mode === 'conversation' ? 'bi bi-chat-square-dots text-primary' : 'bi bi-file-text text-secondary'"></i>
                    {{ form.display_mode === 'conversation' ? 'Typeform (1 par 1)' : 'Classique (Page)' }}
                  </span>
                </div>

                <!-- Title & Description -->
                <h5 class="fw-bold text-dark mb-2 line-clamp-1" :title="form.title">
                  {{ form.title }}
                </h5>
                <p class="text-muted small mb-4 flex-grow-1 line-clamp-2">
                  {{ form.description || 'Aucune description rédigée.' }}
                </p>

                <!-- Metrics Row -->
                <div class="row g-2 p-3 bg-light rounded-3 mb-4 text-center">
                  <div class="col-6 border-end">
                    <div class="h5 fw-bold mb-0 text-success">{{ form.responses_count }}</div>
                    <div class="extra-small text-muted fw-semibold">Réponses</div>
                  </div>
                  <div class="col-6">
                    <div class="h5 fw-bold mb-0 text-dark">{{ form.views_count }}</div>
                    <div class="extra-small text-muted fw-semibold">Vues</div>
                  </div>
                </div>

                <!-- Footer Actions -->
                <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-2 flex-wrap">
                  <div class="d-flex align-items-center gap-1">
                    <!-- Responses Button -->
                    <Link :href="`/espace-utilisateur/formulaires/${form.id}/reponses`" 
                          class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1"
                          title="Consulter les réponses et graphiques">
                      <i class="bi bi-bar-chart-fill"></i>
                      <span>Réponses</span>
                    </Link>

                    <!-- Edit Button -->
                    <Link :href="`/espace-utilisateur/formulaires/${form.id}/editer`" 
                          class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold text-dark"
                          title="Modifier les questions et paramètres">
                      <i class="bi bi-pencil me-1"></i> Éditer
                    </Link>
                  </div>

                  <!-- Dropdown Menu -->
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light rounded-circle p-2 text-muted" 
                            type="button" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                      <li>
                        <a :href="`/f/${form.slug}`" target="_blank" class="dropdown-item small py-2 d-flex align-items-center gap-2">
                          <i class="bi bi-box-arrow-up-right text-primary"></i> Voir le formulaire public
                        </a>
                      </li>
                      <li>
                        <button type="button" @click="openQrModal(form)" class="dropdown-item small py-2 d-flex align-items-center gap-2">
                          <i class="bi bi-qr-code text-dark"></i> Afficher le QR Code
                        </button>
                      </li>
                      <li>
                        <button type="button" @click="copyShareLink(form)" class="dropdown-item small py-2 d-flex align-items-center gap-2">
                          <i class="bi bi-clipboard text-success"></i> Copier le lien public
                        </button>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <button type="button" @click="duplicateForm(form)" class="dropdown-item small py-2 d-flex align-items-center gap-2">
                          <i class="bi bi-files text-secondary"></i> Dupliquer le formulaire
                        </button>
                      </li>
                      <li>
                        <button type="button" @click="confirmDelete(form)" class="dropdown-item small py-2 text-danger d-flex align-items-center gap-2">
                          <i class="bi bi-trash"></i> Supprimer
                        </button>
                      </li>
                    </ul>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
          <div class="mb-4">
            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center p-4">
              <i class="bi bi-ui-checks-grid text-success" style="font-size: 54px;"></i>
            </div>
          </div>
          <h4 class="fw-black text-dark mb-2">Aucun formulaire trouvé</h4>
          <p class="text-muted small mx-auto mb-4" style="max-width: 500px;">
            Créez votre premier formulaire Tech237 en moins de 2 minutes pour vos sondages tech, hackathons, formulaires de recrutement ou retours d'utilisateurs.
          </p>
          <div class="d-flex justify-content-center gap-3">
            <Link href="/espace-utilisateur/formulaires/creer" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm">
              <i class="bi bi-plus-lg me-1"></i> Créer mon premier formulaire
            </Link>
            <Link href="/espace-utilisateur/formulaires/guide" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold">
              Consulter le guide
            </Link>
          </div>
        </div>

      </div>
    </div>

    <!-- QR Code Modal -->
    <div v-if="activeQrForm" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
          
          <div class="modal-header bg-dark text-white p-4">
            <div>
              <h5 class="modal-title fw-black mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-qr-code text-success"></i> QR Code Intelligent
              </h5>
              <div class="extra-small text-white-50 text-truncate" style="max-width: 340px;">
                {{ activeQrForm.title }}
              </div>
            </div>
            <button type="button" @click="activeQrForm = null" class="btn-close btn-close-white shadow-none"></button>
          </div>

          <div class="modal-body p-4 text-center">
            <p class="small text-muted mb-3">
              Scannez ce QR Code avec un smartphone pour ouvrir directement le formulaire en ligne ou projetez-le lors de vos événements &amp; présentations :
            </p>

            <!-- QR Code Canvas & Container -->
            <div class="d-inline-block p-3 bg-white rounded-4 border shadow-sm mb-3 position-relative">
              <canvas ref="qrCanvas" class="img-fluid" style="width: 240px; height: 240px;"></canvas>
            </div>

            <!-- Public Link input -->
            <div class="input-group mb-4">
              <input type="text" readonly :value="activeQrForm.public_url" class="form-control form-control-sm font-monospace extra-small bg-light" />
              <button class="btn btn-outline-success btn-sm px-3 fw-bold" type="button" @click="copyShareLink(activeQrForm)">
                <i class="bi bi-clipboard me-1"></i> {{ isCopied ? 'Copié !' : 'Copier' }}
              </button>
            </div>

            <!-- Download Buttons -->
            <div class="d-flex justify-content-center gap-2">
              <button type="button" @click="downloadQrPng" class="btn btn-dark btn-sm rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2">
                <i class="bi bi-download"></i> Télécharger PNG
              </button>
              <button type="button" @click="downloadQrSvgFile" class="btn btn-outline-dark btn-sm rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="bi bi-filetype-svg"></i> Télécharger SVG
              </button>
            </div>
          </div>

          <div class="modal-footer bg-light p-3 border-0 d-flex justify-content-center">
            <button type="button" @click="activeQrForm = null" class="btn btn-secondary rounded-pill px-4 fw-semibold">
              Fermer
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="toastMsg" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
      <div class="toast show align-items-center text-white bg-dark border-0 rounded-4 shadow-lg p-2" role="alert">
        <div class="d-flex">
          <div class="toast-body small fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success fs-6"></i>
            {{ toastMsg }}
          </div>
          <button type="button" @click="toastMsg = ''" class="btn-close btn-close-white me-2 m-auto shadow-none"></button>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import { renderQrToCanvas, downloadQrImage, createQrSvg } from '@/Utils/qrcode'

const props = defineProps({
  forms: {
    type: Array,
    default: () => []
  }
})

const searchQuery = ref('')
const filterStatus = ref('all')
const activeQrForm = ref(null)
const qrCanvas = ref(null)
const isCopied = ref(false)
const toastMsg = ref('')

const totalResponsesCount = computed(() => {
  return props.forms.reduce((sum, f) => sum + (f.responses_count || 0), 0)
})

const totalViewsCount = computed(() => {
  return props.forms.reduce((sum, f) => sum + (f.views_count || 0), 0)
})

const globalConversionRate = computed(() => {
  if (totalViewsCount.value === 0) return 0
  return Math.round((totalResponsesCount.value / totalViewsCount.value) * 100)
})

const publishedCount = computed(() => props.forms.filter(f => f.status === 'published').length)
const draftCount = computed(() => props.forms.filter(f => f.status === 'draft').length)

const filteredForms = computed(() => {
  return props.forms.filter(f => {
    const matchSearch = (f.title || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        (f.description || '').toLowerCase().includes(searchQuery.value.toLowerCase())
    if (!matchSearch) return false

    if (filterStatus.value === 'published') return f.status === 'published'
    if (filterStatus.value === 'draft') return f.status === 'draft'
    return true
  })
})

function getStatusBadgeClass(form) {
  if (form.is_expired) return 'bg-danger-subtle text-danger'
  if (form.is_quota_reached) return 'bg-warning-subtle text-warning-emphasis'
  if (form.status === 'published') return 'bg-success-subtle text-success'
  if (form.status === 'draft') return 'bg-secondary-subtle text-secondary'
  return 'bg-dark-subtle text-dark'
}

function getStatusLabel(form) {
  if (form.is_expired) return 'Expiré'
  if (form.is_quota_reached) return 'Quota atteint'
  if (form.status === 'published') return 'En ligne'
  if (form.status === 'draft') return 'Brouillon'
  return 'Fermé'
}

function openQrModal(form) {
  activeQrForm.value = form
  isCopied.value = false
  nextTick(() => {
    if (qrCanvas.value) {
      renderQrToCanvas(qrCanvas.value, form.public_url, 300, form.theme_color || '#0B2559')
    }
  })
}

function downloadQrPng() {
  if (!activeQrForm.value) return
  downloadQrImage(
    activeQrForm.value.public_url,
    `qrcode_${activeQrForm.value.slug}.png`,
    500,
    activeQrForm.value.theme_color || '#071738'
  )
  showToast('Image PNG du QR Code téléchargée !')
}

function downloadQrSvgFile() {
  if (!activeQrForm.value) return
  const svgContent = createQrSvg(activeQrForm.value.public_url, 400, activeQrForm.value.theme_color || '#071738')
  const blob = new Blob([svgContent], { type: 'image/svg+xml;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `qrcode_${activeQrForm.value.slug}.svg`
  a.click()
  URL.revokeObjectURL(url)
  showToast('Fichier vectoriel SVG du QR Code téléchargé !')
}

function copyShareLink(form) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(form.public_url)
    isCopied.value = true
    showToast('Lien public copié dans le presse-papier !')
    setTimeout(() => { isCopied.value = false }, 3000)
  }
}

function duplicateForm(form) {
  if (confirm(`Voulez-vous vraiment dupliquer le formulaire « ${form.title} » ?`)) {
    router.post(`/espace-utilisateur/formulaires/${form.id}/dupliquer`, {}, {
      onSuccess: () => showToast('Formulaire dupliqué avec succès !')
    })
  }
}

function confirmDelete(form) {
  if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement « ${form.title} » et toutes ses réponses ?`)) {
    router.delete(`/espace-utilisateur/formulaires/${form.id}`, {
      onSuccess: () => showToast('Formulaire supprimé.')
    })
  }
}

function showToast(msg) {
  toastMsg.value = msg
  setTimeout(() => { toastMsg.value = '' }, 3500)
}
</script>

<style scoped>
.hover-shadow {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-shadow:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
}
.extra-small {
  font-size: 0.72rem;
}
</style>
