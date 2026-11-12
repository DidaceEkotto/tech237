<template>
  <FrontLayout>
    <Head :title="'Mur public de ' + profileUser.name + ' • Contributeur Tech237'" />

    <div class="public-profile-page py-5" style="background-color: #f8fafc; min-height: 85vh;">
      <div class="container" style="max-width: 1040px;">

        <!-- Breadcrumbs & Navigation -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
          <Link href="/carte-des-champions" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-2 fw-semibold text-dark shadow-xs">
            <i class="bi bi-map me-1 text-warning"></i> Revenir à la Map des Gagnants
          </Link>

          <span class="badge bg-light text-muted border px-3 py-2 rounded-pill extra-small">
            Profil vérifié &bull; Tech237 Community
          </span>
        </div>

        <!-- 1. Hero Profile Banner Card -->
        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4">
          <!-- Gradient Cover Banner -->
          <div class="profile-cover p-4 p-md-5 text-white position-relative" style="background: linear-gradient(135deg, #071738 0%, #0B2559 50%, #00A859 100%); min-height: 160px;">
            <div class="position-absolute end-0 top-0 p-4 opacity-10 pointer-events-none d-none d-md-block">
              <i class="bi bi-person-badge" style="font-size: 140px;"></i>
            </div>
          </div>

          <!-- Profile Body & Avatar Overlay -->
          <div class="card-body p-4 p-md-5 pt-0 position-relative">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3" style="margin-top: -54px;">
              
              <!-- Avatar & Identity -->
              <div class="d-flex align-items-end gap-3 flex-wrap">
                <div class="position-relative">
                  <img 
                    v-if="profileUser.avatar" 
                    :src="profileUser.avatar.startsWith('/') ? profileUser.avatar : '/' + profileUser.avatar" 
                    :alt="profileUser.name"
                    class="rounded-circle border border-4 border-white shadow-md bg-white"
                    style="width: 100px; height: 100px; object-fit: cover;"
                  />
                  <div 
                    v-else 
                    class="rounded-circle border border-4 border-white shadow-md text-white fw-bold d-flex align-items-center justify-content-center"
                    style="width: 100px; height: 100px; background-color: #0B2559; font-size: 32px;"
                  >
                    {{ getInitials(profileUser.name) }}
                  </div>
                </div>

                <div class="mb-1">
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h1 class="h3 fw-bold text-dark mb-0">{{ profileUser.name }}</h1>
                    <span class="badge rounded-pill fw-bold extra-small text-white shadow-xs" :style="{ backgroundColor: currentTier?.color || '#00A859' }">
                      <i class="bi" :class="currentTier?.badge_icon || 'bi-award'"></i> {{ profileUser.rank_title || currentTier?.name || 'Novice Tech 🌱' }}
                    </span>
                  </div>
                  <small class="text-muted d-block mt-1">
                    Membre actif depuis {{ formatDate(profileUser.created_at) }} &bull; 
                    <span class="text-warning fw-bold">#{{ stats.rank_position }} au classement national</span>
                  </small>

                  <!-- XP Progress to next level -->
                  <div v-if="nextTier" class="mt-2" style="max-width: 320px;">
                    <div class="d-flex justify-content-between align-items-center extra-small mb-1">
                      <span class="text-muted">Prochain palier : <strong class="text-dark">{{ nextTier.name }}</strong></span>
                      <span class="fw-bold text-success">{{ stats.total_xp }} / {{ nextTier.min_xp }} XP</span>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 999px; background-color: #e2e8f0;">
                      <div class="progress-bar bg-success rounded-pill" :style="{ width: Math.min(100, Math.round((stats.total_xp / nextTier.min_xp) * 100)) + '%' }"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action: Contact / Send Message -->
              <div v-if="!$page.props.auth?.user || $page.props.auth.user.id !== profileUser.id" class="d-flex gap-2 align-items-center mb-1">
                <button 
                  type="button"
                  @click="openContactModal"
                  class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
                  style="background-color: var(--tech-green, #00A859);"
                >
                  <i class="bi bi-chat-left-text-fill"></i>
                  <span>Lui envoyer un message</span>
                </button>
              </div>

            </div>

            <!-- Bio -->
            <p v-if="profileUser.bio" class="text-muted small mt-4 mb-0 leading-relaxed" style="max-width: 720px;">
              {{ profileUser.bio }}
            </p>
          </div>
        </div>

        <!-- 2. Metrics Stats Strip -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white text-center h-100">
              <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Points d'XP</span>
              <div class="h3 fw-bold text-success mb-0">{{ stats.total_xp }} XP</div>
              <small class="text-muted extra-small">Rang #{{ stats.rank_position }}</small>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white text-center h-100">
              <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Projets Tech</span>
              <div class="h3 fw-bold text-primary mb-0">{{ stats.projects_count }}</div>
              <small class="text-muted extra-small">Livrés &amp; publics</small>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white text-center h-100">
              <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Mini-Actus (POCs)</span>
              <div class="h3 fw-bold text-warning mb-0">{{ stats.pocs_count }}</div>
              <small class="text-muted extra-small">Partages communautaires</small>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white text-center h-100">
              <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Badges Débloqués</span>
              <div class="h3 fw-bold text-purple mb-0" style="color: #9333EA;">{{ stats.badges_count }}</div>
              <small class="text-muted extra-small">Distinctions d'honneur</small>
            </div>
          </div>
        </div>

        <!-- 3. Navigation Tabs for Contributions -->
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 mb-4">
          <ul class="nav nav-pills gap-2 flex-wrap">
            <li class="nav-item">
              <button 
                type="button" 
                @click="activeTab = 'projects'" 
                class="nav-link rounded-pill px-4 fw-bold"
                :class="activeTab === 'projects' ? 'active bg-success text-white' : 'text-muted'"
              >
                <i class="bi bi-rocket-takeoff-fill me-1"></i> Projets ({{ projects.length }})
              </button>
            </li>
            <li class="nav-item">
              <button 
                type="button" 
                @click="activeTab = 'pocs'" 
                class="nav-link rounded-pill px-4 fw-bold"
                :class="activeTab === 'pocs' ? 'active bg-success text-white' : 'text-muted'"
              >
                <i class="bi bi-lightning-charge-fill me-1"></i> Mini-Actus &amp; POCs ({{ pocs.length }})
              </button>
            </li>
            <li class="nav-item">
              <button 
                type="button" 
                @click="activeTab = 'forum'" 
                class="nav-link rounded-pill px-4 fw-bold"
                :class="activeTab === 'forum' ? 'active bg-success text-white' : 'text-muted'"
              >
                <i class="bi bi-chat-left-dots-fill me-1"></i> Forum ({{ forumTopics.length }})
              </button>
            </li>
            <li class="nav-item">
              <button 
                type="button" 
                @click="activeTab = 'badges'" 
                class="nav-link rounded-pill px-4 fw-bold"
                :class="activeTab === 'badges' ? 'active bg-success text-white' : 'text-muted'"
              >
                <i class="bi bi-award-fill me-1"></i> Badges ({{ badges.length }})
              </button>
            </li>
          </ul>
        </div>

        <!-- 4. Tab Contents -->

        <!-- TAB 1: PROJECTS -->
        <div v-if="activeTab === 'projects'">
          <div v-if="projects && projects.length > 0" class="row g-4">
            <div v-for="proj in projects" :key="proj.id" class="col-md-6">
              <div class="card border-0 rounded-4 shadow-sm h-100 bg-white p-4 d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <img 
                      v-if="proj.logo" 
                      :src="'/' + proj.logo" 
                      class="rounded-3 border" 
                      style="width: 48px; height: 48px; object-fit: contain;" 
                      :alt="proj.name"
                    />
                    <div v-else class="rounded-3 bg-light border p-2 text-primary fs-3">
                      <i class="bi bi-rocket-takeoff"></i>
                    </div>
                    <div>
                      <h4 class="h6 fw-bold text-dark mb-0">{{ proj.name }}</h4>
                      <small class="text-muted extra-small">Publié le {{ formatDate(proj.created_at) }}</small>
                    </div>
                  </div>
                  <p class="small text-muted mb-3 line-clamp-2">
                    {{ proj.pitch || proj.description || 'Projet innovant sur Tech237.' }}
                  </p>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                  <span class="badge bg-light text-dark border rounded-pill px-3 py-1 extra-small">
                    {{ proj.category || 'Tech & Web' }}
                  </span>
                  <Link :href="'/projet/' + (proj.slug || proj.id)" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold">
                    Découvrir &rarr;
                  </Link>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="card border-0 rounded-4 shadow-sm p-5 text-center text-muted bg-white">
            <i class="bi bi-folder2-open fs-1 d-block mb-2 text-secondary"></i>
            Ce membre n'a pas encore publié de projet public.
          </div>
        </div>

        <!-- TAB 2: POCS (MINI-ACTUS) -->
        <div v-else-if="activeTab === 'pocs'">
          <div v-if="pocs && pocs.length > 0" class="row g-4">
            <div v-for="poc in pocs" :key="poc.id" class="col-md-6">
              <div class="card border-0 rounded-4 shadow-sm h-100 bg-white overflow-hidden d-flex flex-column justify-content-between">
                <div v-if="poc.cover_image" class="poc-cover border-bottom" style="height: 140px; overflow: hidden;">
                  <img :src="'/' + poc.cover_image" class="w-100 h-100 object-fit-cover" :alt="poc.title" />
                </div>
                <div class="p-4 flex-grow-1">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-warning-subtle text-dark rounded-pill px-2 py-0 extra-small fw-bold">
                      ⚡ POC Tech
                    </span>
                    <small class="text-muted extra-small">{{ formatDate(poc.published_at || poc.created_at) }}</small>
                  </div>
                  <h4 class="h6 fw-bold text-dark mb-2">
                    <Link :href="'/pocs/' + (poc.slug || poc.id)" class="text-dark text-decoration-none hover-underline">
                      {{ poc.title }}
                    </Link>
                  </h4>
                  <p class="small text-muted line-clamp-2 mb-0">
                    {{ poc.summary }}
                  </p>
                </div>
                <div class="p-3 bg-light border-top d-flex align-items-center justify-content-between extra-small text-muted">
                  <div class="d-flex gap-3">
                    <span><i class="bi bi-fire text-danger me-1"></i>{{ poc.upvotes_count || 0 }} vote(s)</span>
                    <span><i class="bi bi-eye me-1"></i>{{ poc.views_count || 0 }} vue(s)</span>
                  </div>
                  <Link :href="'/pocs/' + (poc.slug || poc.id)" class="text-success fw-bold text-decoration-none">
                    Lire le POC &rarr;
                  </Link>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="card border-0 rounded-4 shadow-sm p-5 text-center text-muted bg-white">
            <i class="bi bi-lightning-charge fs-1 d-block mb-2 text-warning"></i>
            Ce membre n'a pas encore partagé de mini-actualité (POC).
          </div>
        </div>

        <!-- TAB 3: FORUM -->
        <div v-else-if="activeTab === 'forum'">
          <div v-if="forumTopics && forumTopics.length > 0" class="d-flex flex-column gap-3">
            <div v-for="top in forumTopics" :key="top.id" class="card border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0 extra-small">Discussion Forum</span>
                <small class="text-muted extra-small">{{ formatDate(top.created_at) }}</small>
              </div>
              <h5 class="h6 fw-bold text-dark mb-2">
                <Link :href="'/forum/' + (top.slug || top.id)" class="text-dark text-decoration-none">
                  {{ top.title }}
                </Link>
              </h5>
              <div class="d-flex align-items-center gap-3 extra-small text-muted">
                <span><i class="bi bi-chat-dots me-1"></i>{{ top.posts_count || 0 }} réponse(s)</span>
                <span><i class="bi bi-eye me-1"></i>{{ top.views_count || 0 }} vue(s)</span>
              </div>
            </div>
          </div>
          <div v-else class="card border-0 rounded-4 shadow-sm p-5 text-center text-muted bg-white">
            <i class="bi bi-chat-square-dots fs-1 d-block mb-2 text-primary"></i>
            Ce membre n'a pas encore ouvert de sujet sur le forum.
          </div>
        </div>

        <!-- TAB 4: BADGES -->
        <div v-else-if="activeTab === 'badges'">
          <div v-if="badges && badges.length > 0" class="row g-3">
            <div v-for="b in badges" :key="b.id" class="col-md-6 col-lg-4">
              <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100 d-flex flex-row align-items-center gap-3">
                <div 
                  class="rounded-circle text-white shadow-xs d-flex align-items-center justify-content-center p-3 fs-3"
                  :style="{ backgroundColor: b.color || '#00A859', width: '56px', height: '56px', minWidth: '56px' }"
                >
                  <i class="bi" :class="b.icon || 'bi-award-fill'"></i>
                </div>
                <div>
                  <strong class="d-block text-dark small mb-0">{{ b.name }}</strong>
                  <small class="text-muted extra-small leading-tight d-block">{{ b.description }}</small>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="card border-0 rounded-4 shadow-sm p-5 text-center text-muted bg-white">
            <i class="bi bi-award fs-1 d-block mb-2 text-warning"></i>
            Ce membre n'a pas encore reçu de badge honorifique.
          </div>
        </div>

      </div>
    </div>

    <!-- Modal Static : Envoyer un message direct / Email -->
    <div 
      v-if="showContactModal" 
      class="modal fade show d-block" 
      tabindex="-1" 
      role="dialog"
      style="background-color: rgba(11, 37, 89, 0.65); backdrop-filter: blur(4px); z-index: 1055;"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%); border-bottom: 3px solid var(--tech-green, #00A859);">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-white bg-opacity-10 p-2 text-success fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-envelope-at-fill" style="color: var(--tech-green, #00A859);"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold mb-0">Contacter {{ profileUser.name }}</h5>
                <p class="mb-0 extra-small text-white-50">Un email direct sera transmis sur sa boîte de messagerie personnelle</p>
              </div>
            </div>
            <button 
              type="button" 
              class="btn-close btn-close-white" 
              @click="closeContactModal" 
              aria-label="Fermer"
              :disabled="sendingEmail"
            ></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4 bg-light">
            <!-- Success Alert -->
            <div v-if="contactSuccess" class="alert alert-success border-0 rounded-3 shadow-xs d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-check-circle-fill fs-5 text-success"></i>
              <div>
                <strong>Message transmis avec succès !</strong>
                <p class="mb-0 extra-small">{{ contactSuccess }}</p>
              </div>
            </div>

            <!-- Error Alert -->
            <div v-if="contactError" class="alert alert-danger border-0 rounded-3 shadow-xs d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
              <div>
                <strong>Erreur</strong>
                <p class="mb-0 extra-small">{{ contactError }}</p>
              </div>
            </div>

            <form v-if="!contactSuccess" @submit.prevent="submitContactForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-dark">Votre Nom &amp; Prénom <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    v-model="contactForm.sender_name" 
                    required 
                    class="form-control rounded-3" 
                    placeholder="ex: Jean Dupont" 
                    :disabled="sendingEmail"
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-dark">Votre adresse Email <span class="text-danger">*</span></label>
                  <input 
                    type="email" 
                    v-model="contactForm.sender_email" 
                    required 
                    class="form-control rounded-3" 
                    placeholder="nom@exemple.cm" 
                    :disabled="sendingEmail"
                  />
                  <div class="form-text extra-small">Le destinataire pourra vous répondre directement à cette adresse.</div>
                </div>
                <div class="col-12">
                  <label class="form-label small fw-bold text-dark">Sujet de votre message <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    v-model="contactForm.subject" 
                    required 
                    class="form-control rounded-3" 
                    placeholder="ex: Opportunité de collaboration sur un projet Web" 
                    :disabled="sendingEmail"
                  />
                </div>
                <div class="col-12">
                  <label class="form-label small fw-bold text-dark">Votre Message <span class="text-danger">*</span></label>
                  <textarea 
                    v-model="contactForm.message" 
                    required 
                    rows="5" 
                    class="form-control rounded-3" 
                    placeholder="Présentez-vous brièvement et décrivez l'objet de votre prise de contact..." 
                    :disabled="sendingEmail"
                  ></textarea>
                </div>
              </div>

              <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-3 border-top">
                <button 
                  type="button" 
                  class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" 
                  @click="closeContactModal"
                  :disabled="sendingEmail"
                >
                  Annuler
                </button>
                <button 
                  type="submit" 
                  class="btn btn-success rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
                  style="background-color: var(--tech-green, #00A859);"
                  :disabled="sendingEmail"
                >
                  <span v-if="sendingEmail" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  <i v-else class="bi bi-send-fill"></i>
                  <span>{{ sendingEmail ? 'Envoi en cours...' : 'Envoyer le message' }}</span>
                </button>
              </div>
            </form>

            <div v-else class="text-center py-4">
              <button 
                type="button" 
                class="btn btn-success rounded-pill px-4 fw-bold shadow-sm"
                style="background-color: var(--tech-green, #00A859);"
                @click="closeContactModal"
              >
                Fermer
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const page = usePage()

const props = defineProps({
  profileUser: { type: Object, required: true },
  currentTier: { type: Object, default: null },
  nextTier: { type: Object, default: null },
  stats: { type: Object, default: () => ({}) },
  projects: { type: Array, default: () => [] },
  pocs: { type: Array, default: () => [] },
  forumTopics: { type: Array, default: () => [] },
  badges: { type: Array, default: () => [] },
})

const activeTab = ref('projects')
const showContactModal = ref(false)
const sendingEmail = ref(false)
const contactSuccess = ref('')
const contactError = ref('')

const contactForm = ref({
  sender_name: page.props.auth?.user?.name || '',
  sender_email: page.props.auth?.user?.email || '',
  subject: '',
  message: '',
})

function openContactModal() {
  contactForm.value = {
    sender_name: page.props.auth?.user?.name || '',
    sender_email: page.props.auth?.user?.email || '',
    subject: '',
    message: '',
  }
  contactSuccess.value = ''
  contactError.value = ''
  showContactModal.value = true
}

function closeContactModal() {
  if (sendingEmail.value) return
  showContactModal.value = false
}

async function submitContactForm() {
  sendingEmail.value = true
  contactError.value = ''
  contactSuccess.value = ''

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const response = await fetch(`/communaute/profil/${props.profileUser.id}/contact`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(contactForm.value),
    })

    const data = await response.json().catch(() => ({}))

    if (response.ok && data.success) {
      contactSuccess.value = data.message || 'Votre message a bien été envoyé !'
    } else {
      contactError.value = data.error || data.message || 'Une erreur est survenue lors de l\'envoi du message.'
    }
  } catch (err) {
    contactError.value = err.message || 'Erreur réseau lors de l\'envoi du message.'
  } finally {
    sendingEmail.value = false
  }
}

function getInitials(name) {
  if (!name) return 'U'
  const parts = name.trim().split(' ')
  return parts.length >= 2 
    ? (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase()
    : name.substring(0, 2).toUpperCase()
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<style scoped>
.shadow-xs {
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.hover-underline:hover {
  text-decoration: underline !important;
}
</style>
