<template>
  <FrontLayout>
    <Head>
      <title>Collaborateurs du Projet : {{ project.name }} | Espace Utilisateur</title>
    </Head>

    <div class="project-collaborators-page py-5 bg-light-subtle min-vh-100">
      <div class="container">
        
        <!-- Header & Breadcrumb -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item">
                  <Link href="/espace-utilisateur" class="text-decoration-none text-muted">Espace Utilisateur</Link>
                </li>
                <li class="breadcrumb-item">
                  <Link href="/espace-utilisateur?section=projects" class="text-decoration-none text-muted">Mes Projets</Link>
                </li>
                <li class="breadcrumb-item active text-dark fw-bold">{{ project.name }}</li>
              </ol>
            </nav>
            <h2 class="fw-black text-dark mb-1">
              <i class="bi bi-people-fill text-success me-2"></i>Collaborateurs &amp; Invitations
            </h2>
            <p class="text-muted small mb-0">
              Gérez les membres qui collaborent sur votre projet <strong>{{ project.name }}</strong> et invitez de nouveaux talents.
            </p>
          </div>

          <div class="d-flex gap-2">
            <Link :href="'/projet/' + project.slug" target="_blank" class="btn btn-outline-dark rounded-pill px-3 btn-sm">
              <i class="bi bi-box-arrow-up-right me-1"></i> Voir la fiche publique
            </Link>
            <Link href="/espace-utilisateur?section=projects" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Retour aux Projets
            </Link>
          </div>
        </div>

        <!-- Project Hero Card -->
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-3 border overflow-hidden p-1 bg-light shadow-sm d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; min-width: 58px;">
                <img 
                  v-if="project.logo" 
                  :src="project.logo.startsWith('/') ? project.logo : '/' + project.logo" 
                  :alt="project.name" 
                  class="w-100 h-100 object-fit-contain rounded-2"
                >
                <div v-else class="fw-bold text-success fs-4">
                  {{ (project.name || 'P').charAt(0).toUpperCase() }}
                </div>
              </div>
              <div>
                <h4 class="fw-bold text-dark mb-1">{{ project.name }}</h4>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="badge bg-secondary-subtle text-secondary text-uppercase small">{{ project.project_type || 'Projet' }}</span>
                  <span v-if="project.startup" class="badge bg-primary-subtle text-primary small">
                    <i class="bi bi-building me-1"></i>{{ project.startup.name }}
                  </span>
                  <span class="badge bg-success-subtle text-success small">
                    <i class="bi bi-people-fill me-1"></i>{{ (collaborators || []).length }} collaborateur(s) actif(s)
                  </span>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2">
              <Link :href="'/espace-utilisateur/projets/' + project.id + '/etapes'" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                <i class="bi bi-journal-check me-1"></i> Devlogs
              </Link>
              <Link :href="'/espace-utilisateur/projets/' + project.id + '/galerie'" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-images me-1"></i> Galerie
              </Link>
              <Link :href="'/espace-utilisateur/projets/' + project.id + '/edit'" class="btn btn-sm btn-dark rounded-pill px-3">
                <i class="bi bi-pencil me-1"></i> Modifier le projet
              </Link>
            </div>
          </div>
        </div>

        <!-- Success notification -->
        <div v-if="$page.props.flash?.success" class="alert alert-success rounded-4 small p-3 mb-4 shadow-sm border-0 d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill fs-5 text-success"></i>
          <div>{{ $page.props.flash.success }}</div>
        </div>

        <!-- Error notification -->
        <div v-if="$page.props.flash?.error || Object.keys(inviteForm.errors).length > 0" class="alert alert-danger rounded-4 small p-3 mb-4 shadow-sm border-0 d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
          <div>
            {{ $page.props.flash?.error || inviteForm.errors.email || inviteForm.errors.role || inviteForm.errors.message || "Une erreur est survenue lors de l'opération." }}
          </div>
        </div>

        <div class="row g-4">
          
          <!-- LEFT COLUMN: INVITATION FORM WITH AUTOCOMPLETE -->
          <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
              <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                  <i class="bi bi-person-plus-fill fs-5"></i>
                </div>
                <div>
                  <h5 class="fw-bold text-dark mb-0">Inviter un collaborateur</h5>
                  <small class="text-muted">Partagez votre projet avec un membre</small>
                </div>
              </div>

              <div class="alert alert-info border-0 rounded-3 p-3 small mb-4">
                <i class="bi bi-info-circle-fill text-primary me-1"></i>
                L'invitation est valable pendant <strong>48 heures</strong>. Le collaborateur recevra un email officiel avec les détails et le logo du projet, ainsi qu'une notification sur son espace.
              </div>

              <form @submit.prevent="submitInvite">
                
                <!-- Email field with live autocomplete -->
                <div class="mb-3 position-relative" ref="autocompleteContainer">
                  <label class="form-label small fw-bold text-dark">
                    Adresse email de la personne à inviter <span class="text-danger">*</span>
                  </label>
                  
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-envelope"></i>
                    </span>
                    <input 
                      type="email" 
                      class="form-control border-start-0" 
                      :class="{ 'is-invalid': inviteForm.errors.email }"
                      v-model="inviteForm.email" 
                      @input="handleEmailInput"
                      @focus="handleEmailFocus"
                      placeholder="ex: developpeur@domaine.cm"
                      autocomplete="off"
                      required
                    >
                    <span v-if="isSearching" class="input-group-text bg-light text-muted">
                      <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                    </span>
                  </div>

                  <!-- Autocomplete Dropdown Menu -->
                  <div 
                    v-if="showDropdown && searchResults.length > 0" 
                    class="autocomplete-dropdown shadow-lg rounded-3 border bg-white mt-1 py-1 position-absolute w-100"
                    style="z-index: 1050; max-height: 280px; overflow-y: auto;"
                  >
                    <div class="px-3 py-1 small text-muted text-uppercase fw-bold border-bottom" style="font-size: 10px;">
                      Membres trouvés (cliquez pour sélectionner)
                    </div>
                    <button
                      type="button"
                      v-for="user in searchResults"
                      :key="user.id"
                      class="dropdown-item px-3 py-2 d-flex align-items-center gap-3 text-start border-0 bg-transparent w-100 user-select-item"
                      @click="selectUser(user)"
                    >
                      <!-- Avatar -->
                      <div class="flex-shrink-0">
                        <img 
                          v-if="user.avatar" 
                          :src="user.avatar" 
                          :alt="user.name" 
                          class="rounded-circle border" 
                          style="width: 36px; height: 36px; object-fit: cover;"
                        >
                        <div 
                          v-else 
                          class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center"
                          style="width: 36px; height: 36px;"
                        >
                          {{ (user.name || 'U').charAt(0).toUpperCase() }}
                        </div>
                      </div>

                      <!-- Details -->
                      <div class="flex-grow-1 overflow-hidden">
                        <strong class="d-block text-dark text-truncate small mb-0">{{ user.name }}</strong>
                        <span class="text-muted small text-truncate d-block">{{ user.email }}</span>
                      </div>

                      <!-- Selection icon -->
                      <div class="text-success small">
                        <i class="bi bi-plus-circle"></i>
                      </div>
                    </button>
                  </div>

                  <!-- No results notice -->
                  <div 
                    v-else-if="showDropdown && searchQueried && searchResults.length === 0 && inviteForm.email.length >= 2" 
                    class="autocomplete-dropdown shadow-sm rounded-3 border bg-white mt-1 p-3 position-absolute w-100 text-center text-muted small"
                    style="z-index: 1050;"
                  >
                    <i class="bi bi-info-circle me-1"></i> Aucun compte trouvé avec cette adresse. Une invitation sera envoyée par email à cette adresse dès soumission.
                  </div>

                  <div v-if="inviteForm.errors.email" class="text-danger small mt-1">
                    {{ inviteForm.errors.email }}
                  </div>
                </div>

                <!-- Role suggestion badges & Input -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">Rôle / Mission sur le projet</label>
                    <small class="text-muted" style="font-size: 11px;">Exemples rapides :</small>
                  </div>

                  <!-- Quick badge pills -->
                  <div class="d-flex flex-wrap gap-1 mb-2">
                    <button 
                      type="button" 
                      v-for="presetRole in roleSuggestions" 
                      :key="presetRole"
                      @click="inviteForm.role = presetRole"
                      class="btn btn-xs rounded-pill border py-0 px-2 small"
                      :class="inviteForm.role === presetRole ? 'btn-success text-white' : 'btn-light text-secondary'"
                      style="font-size: 11px;"
                    >
                      {{ presetRole }}
                    </button>
                  </div>

                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="inviteForm.role" 
                    placeholder="ex: Lead Dev Frontend, Designer UI, Game Dev..."
                  >
                  <div v-if="inviteForm.errors.role" class="text-danger small mt-1">{{ inviteForm.errors.role }}</div>
                </div>

                <!-- Personal Message -->
                <div class="mb-4">
                  <label class="form-label small fw-bold text-dark">Message personnalisé d'accompagnement (facultatif)</label>
                  <textarea 
                    class="form-control" 
                    rows="3" 
                    v-model="inviteForm.message" 
                    placeholder="Bonjour, je t'invite à collaborer sur les fonctionnalités de notre projet..."
                  ></textarea>
                  <div v-if="inviteForm.errors.message" class="text-danger small mt-1">{{ inviteForm.errors.message }}</div>
                </div>

                <!-- Submit Button -->
                <button 
                  type="submit" 
                  class="btn btn-success rounded-pill w-100 py-2 fw-bold shadow-sm" 
                  :disabled="inviteForm.processing"
                >
                  <span v-if="inviteForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-send-fill me-2"></i>
                  Envoyer l'invitation (48h)
                </button>
              </form>
            </div>
          </div>

          <!-- RIGHT COLUMN: ACTIVE COLLABORATORS & PENDING INVITATIONS -->
          <div class="col-lg-7 d-flex flex-column gap-4">
            
            <!-- ACTIVE COLLABORATORS CARD -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
              <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                  <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-people-fill"></i>
                  </div>
                  <h5 class="fw-bold text-dark mb-0">Collaborateurs actuels</h5>
                </div>
                <span class="badge bg-success rounded-pill px-3 py-1">
                  {{ (collaborators || []).length }} actif(s)
                </span>
              </div>

              <div v-if="(collaborators || []).length" class="d-flex flex-column gap-3">
                <div 
                  v-for="c in collaborators" 
                  :key="c.id" 
                  class="p-3 rounded-4 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-3 transition-card"
                >
                  <div class="d-flex align-items-center gap-3">
                    <img 
                      v-if="c.user?.avatar" 
                      :src="c.user.avatar.startsWith('/') ? c.user.avatar : '/' + c.user.avatar" 
                      :alt="c.user.name"
                      class="rounded-circle border shadow-sm" 
                      style="width: 48px; height: 48px; object-fit: cover;"
                    >
                    <div 
                      v-else 
                      class="rounded-circle bg-dark text-white fw-bold d-flex align-items-center justify-content-center shadow-sm"
                      style="width: 48px; height: 48px; min-width: 48px;"
                    >
                      {{ (c.user?.name || 'C').charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <strong class="d-block text-dark fs-6">{{ c.user?.full_name || c.user?.name }}</strong>
                      <div class="small text-muted d-flex align-items-center gap-2 flex-wrap">
                        <span>{{ c.user?.email }}</span>
                        <span class="badge bg-secondary-subtle text-dark border">{{ c.role || 'Collaborateur' }}</span>
                      </div>
                      <small class="text-muted d-block" style="font-size: 11px;">
                        <i class="bi bi-calendar-check me-1"></i>Collaborateur depuis le {{ formatDate(c.joined_at) }}
                      </small>
                    </div>
                  </div>

                  <!-- Revoke Button triggers static confirmation modal -->
                  <button 
                    type="button"
                    @click="openRevokeModal(c)" 
                    class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold" 
                    title="Annuler la collaboration"
                  >
                    <i class="bi bi-person-x-fill me-1"></i> Révoquer
                  </button>
                </div>
              </div>

              <!-- Empty state -->
              <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                <h6 class="fw-bold text-dark mb-1">Aucun collaborateur actif</h6>
                <p class="small mb-0">Utilisez le formulaire ci-contre pour inviter des membres à contribuer à votre projet.</p>
              </div>
            </div>

            <!-- PENDING & EXPIRED INVITATIONS CARD -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
              <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                  <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-envelope-paper-fill"></i>
                  </div>
                  <h5 class="fw-bold text-dark mb-0">Invitations envoyées</h5>
                </div>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">
                  {{ (invitations || []).length }} au total
                </span>
              </div>

              <div v-if="(invitations || []).length" class="d-flex flex-column gap-3">
                <div 
                  v-for="inv in invitations" 
                  :key="inv.id" 
                  class="p-3 rounded-4 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-3"
                >
                  <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                      <strong class="text-dark">{{ inv.email }}</strong>
                      
                      <!-- Status badge -->
                      <span v-if="inv.status === 'pending' && !isExpired(inv.expires_at)" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small">
                        <i class="bi bi-hourglass-split me-1"></i>En attente (48h)
                      </span>
                      <span v-else-if="inv.status === 'accepted'" class="badge bg-success-subtle text-success border border-success-subtle small">
                        <i class="bi bi-check-circle-fill me-1"></i>Acceptée
                      </span>
                      <span v-else-if="inv.status === 'declined'" class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                        <i class="bi bi-x-circle-fill me-1"></i>Déclinée
                      </span>
                      <span v-else class="badge bg-secondary-subtle text-secondary border small">
                        <i class="bi bi-clock-history me-1"></i>Expirée
                      </span>
                    </div>

                    <div class="small text-muted mb-1">
                      Rôle proposé : <span class="fw-semibold text-dark">{{ inv.role }}</span>
                      &bull; Envoyée le {{ formatDate(inv.created_at) }}
                    </div>

                    <div v-if="inv.status === 'pending' && !isExpired(inv.expires_at)" class="small text-danger fw-semibold" style="font-size: 11px;">
                      <i class="bi bi-alarm me-1"></i>Expire le {{ formatDateTime(inv.expires_at) }}
                    </div>
                  </div>

                  <div class="d-flex align-items-center gap-2">
                    <!-- Copy link button -->
                    <button 
                      type="button"
                      @click="copyInviteLink(inv.token)" 
                      class="btn btn-sm btn-outline-primary rounded-pill px-3"
                      :title="'Copier le lien d\'invitation'"
                    >
                      <i class="bi" :class="copiedToken === inv.token ? 'bi-check2 text-success' : 'bi-clipboard me-1'"></i>
                      <span>{{ copiedToken === inv.token ? 'Copié !' : 'Copier le lien' }}</span>
                    </button>

                    <!-- Re-invite shortcut -->
                    <button 
                      type="button"
                      v-if="inv.status !== 'accepted'"
                      @click="reinvite(inv)" 
                      class="btn btn-sm btn-outline-secondary rounded-pill px-2"
                      title="Renvoyer l'invitation à cette personne"
                    >
                      <i class="bi bi-arrow-repeat"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Empty state -->
              <div v-else class="text-center py-5 text-muted bg-light rounded-4">
                <i class="bi bi-envelope-check fs-1 d-block mb-2 text-muted"></i>
                <h6 class="fw-bold text-dark mb-1">Aucune invitation envoyée</h6>
                <p class="small mb-0">Toutes les invitations envoyées pour ce projet apparaîtront ici.</p>
              </div>
            </div>

          </div>

        </div>

      </div>
    </div>

    <!-- STATIC CONFIRMATION MODAL: REVOKE COLLABORATOR -->
    <div 
      v-if="collabToRevoke" 
      class="modal fade show d-block" 
      tabindex="-1" 
      style="background-color: rgba(0,0,0,0.6); z-index: 1060;" 
      @click.self="collabToRevoke = null"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 p-4 text-center bg-white">
          <div class="rounded-circle bg-danger-subtle text-danger mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="bi bi-person-x-fill fs-2"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">Annuler la collaboration ?</h5>
          <p class="small text-muted mb-3">
            Êtes-vous sûr de vouloir retirer <strong>{{ collabToRevoke?.user?.name }}</strong> des collaborateurs de ce projet ?
          </p>
          <div class="alert alert-secondary p-2 small mb-4 text-start">
            <i class="bi bi-info-circle me-1"></i>
            Le projet disparaîtra immédiatement de son espace membre. Si vous changez d'avis plus tard, vous pourrez lui renvoyer une invitation et il récupérera son historique.
          </div>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-light rounded-pill px-4" @click="collabToRevoke = null">Annuler</button>
            <button 
              type="button" 
              class="btn btn-danger rounded-pill px-4 fw-bold" 
              :disabled="isRevoking" 
              @click="submitRevoke"
            >
              <span v-if="isRevoking" class="spinner-border spinner-border-sm me-1"></span>
              Confirmer la révocation
            </button>
          </div>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  project: {
    type: Object,
    required: true,
  },
  collaborators: {
    type: Array,
    default: () => [],
  },
  invitations: {
    type: Array,
    default: () => [],
  },
})

// Role suggestions
const roleSuggestions = [
  'Contributeur & Dev',
  'Lead Développeur',
  'Designer UI/UX',
  'Dev Mobile',
  'Devlog & Comm',
]

// Invitation Form
const inviteForm = useForm({
  email: '',
  role: 'Contributeur & Dev',
  message: '',
})

// Autocompletion State
const autocompleteContainer = ref(null)
const searchResults = ref([])
const isSearching = ref(false)
const showDropdown = ref(false)
const searchQueried = ref(false)
let debounceTimer = null

function handleEmailInput() {
  const query = inviteForm.email.trim()
  
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }

  if (query.length < 2) {
    searchResults.value = []
    showDropdown.value = false
    searchQueried.value = false
    return
  }

  debounceTimer = setTimeout(() => {
    fetchUsers(query)
  }, 250)
}

function handleEmailFocus() {
  if (searchResults.value.length > 0) {
    showDropdown.value = true
  }
}

async function fetchUsers(query) {
  isSearching.value = true
  searchQueried.value = true
  try {
    const res = await fetch(`/api/users/search?q=${encodeURIComponent(query)}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
    if (res.ok) {
      const data = await res.json()
      searchResults.value = Array.isArray(data) ? data : []
      showDropdown.value = true
    }
  } catch (err) {
    console.error('Erreur recherche utilisateurs autocomplétion:', err)
  } finally {
    isSearching.value = false
  }
}

function selectUser(user) {
  // Automatically fill the email input with the selected user's email
  inviteForm.email = user.email
  showDropdown.value = false
  searchResults.value = []
}

// Close dropdown on click outside
function handleClickOutside(event) {
  if (autocompleteContainer.value && !autocompleteContainer.value.contains(event.target)) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  if (debounceTimer) clearTimeout(debounceTimer)
})

// Submit Invitation
function submitInvite() {
  showDropdown.value = false
  inviteForm.post(`/espace-utilisateur/projets/${props.project.id}/inviter`, {
    preserveScroll: true,
    onSuccess: () => {
      inviteForm.reset('email', 'message')
      searchResults.value = []
    },
  })
}

// Re-invite shortcut
function reinvite(inv) {
  inviteForm.email = inv.email
  inviteForm.role = inv.role || 'Contributeur & Dev'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Revoke Collaborator
const collabToRevoke = ref(null)
const isRevoking = ref(false)

function openRevokeModal(collab) {
  collabToRevoke.value = collab
}

function submitRevoke() {
  if (!collabToRevoke.value) return
  isRevoking.value = true
  router.delete(`/espace-utilisateur/projets/${props.project.id}/collaborateurs/${collabToRevoke.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      isRevoking.value = false
      collabToRevoke.value = null
    },
  })
}

// Copy invitation link
const copiedToken = ref(null)

function copyInviteLink(token) {
  const url = `${window.location.origin}/invitations/${token}`
  if (navigator.clipboard) {
    navigator.clipboard.writeText(url).then(() => {
      copiedToken.value = token
      setTimeout(() => {
        if (copiedToken.value === token) {
          copiedToken.value = null
        }
      }, 2500)
    })
  } else {
    // Fallback
    const input = document.createElement('input')
    input.value = url
    document.body.appendChild(input)
    input.select()
    document.execCommand('copy')
    document.body.removeChild(input)
    copiedToken.value = token
    setTimeout(() => {
      if (copiedToken.value === token) {
        copiedToken.value = null
      }
    }, 2500)
  }
}

// Helpers
function isExpired(dateStr) {
  if (!dateStr) return false
  return new Date(dateStr) < new Date()
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function formatDateTime(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<style scoped>
.fw-black {
  font-weight: 900;
}
.cursor-pointer {
  cursor: pointer;
}
.user-select-item {
  transition: background-color 0.15s ease;
}
.user-select-item:hover, .user-select-item:focus {
  background-color: #f0fdf4 !important;
}
.transition-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.transition-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.autocomplete-dropdown {
  border-color: #dee2e6;
}
</style>
