<template>
  <div class="bg-light min-vh-100 py-5">
    <Head :title="'Inscrits : ' + conference.title_fr + ' - Espace Membre Tech237'" />

    <div class="container" style="max-width: 1100px;">
      
      <!-- Top Navigation & Summary -->
      <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <Link href="/espace-utilisateur?section=conferences" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2">
          <i class="bi bi-arrow-left"></i>
          <span>Retour à mes conférences</span>
        </Link>

        <a :href="'/conference/' + conference.slug" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
          <i class="bi bi-box-arrow-up-right me-1"></i> Voir la page publique
        </a>
      </div>

      <!-- Conference Info Card -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
              <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-bold small mb-2">
                <i class="bi bi-calendar-event me-1"></i> {{ conference.city }} &bull; {{ conference.event_date ? conference.event_date.slice(0, 10) : '' }}
              </span>
              <h3 class="fw-bold text-dark mb-1">{{ conference.title_fr }}</h3>
              <p class="text-muted small mb-0">{{ conference.location || conference.city }}</p>
            </div>

            <div class="d-flex align-items-center gap-3">
              <div class="text-center p-3 bg-light rounded-3">
                <div class="h3 fw-bold text-dark mb-0">{{ registrations.length }}</div>
                <div class="text-muted small">Inscrits</div>
              </div>
              <div class="text-center p-3 bg-success-subtle rounded-3">
                <div class="h3 fw-bold text-success mb-0">{{ confirmedCount }}</div>
                <div class="text-muted small">Billets Confirmés</div>
              </div>
              <div class="text-center p-3 bg-warning-subtle rounded-3">
                <div class="h3 fw-bold text-warning-emphasis mb-0">{{ pendingCount }}</div>
                <div class="text-muted small">En attente</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bulk Email Action Card with Animated Progress Bar -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <h5 class="fw-bold text-dark mb-1">
              <i class="bi bi-envelope-check-fill text-primary me-2"></i>Envoi groupé des confirmations par email
            </h5>
            <p class="text-muted small mb-0">
              Transmettez le billet officiel au format HTML avec le logo du site aux participants sélectionnés.
            </p>
          </div>

          <button 
            type="button" 
            class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2"
            :disabled="isSendingBulk || unconfirmedSelected.length === 0"
            @click="sendBulkConfirmation"
          >
            <span v-if="isSendingBulk" class="spinner-border spinner-border-sm"></span>
            <i v-else class="bi bi-send-fill"></i>
            <span>Envoyer les billets ({{ unconfirmedSelected.length }})</span>
          </button>
        </div>

        <!-- Progress Bar when sending bulk -->
        <div v-if="isSendingBulk || bulkDone" class="mt-4 pt-3 border-top">
          <div class="d-flex justify-content-between align-items-center small fw-bold mb-2">
            <span class="text-primary">
              <i class="bi bi-arrow-repeat spin me-1" v-if="isSendingBulk"></i>
              {{ bulkStatusText }}
            </span>
            <span class="badge bg-primary rounded-pill px-3 py-1">
              {{ currentSentCount }} / {{ totalToSentCount }} email(s) transmis
            </span>
          </div>
          <div class="progress rounded-pill" style="height: 12px;">
            <div 
              class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
              role="progressbar" 
              :style="{ width: progressPercentage + '%' }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Filters & Participants Table -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 400px;">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
              <input 
                type="text" 
                class="form-control" 
                v-model="searchTerm" 
                placeholder="Rechercher par nom, email, ville..."
              >
            </div>
          </div>

          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" v-model="statusFilter" style="width: 170px;">
              <option value="all">Tous les statuts</option>
              <option value="pending">En attente de billet</option>
              <option value="confirmed">Billet envoyé</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th width="40" class="text-center">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    :checked="isAllSelected" 
                    @change="toggleSelectAll"
                  >
                </th>
                <th>Participant</th>
                <th>Email / Contact</th>
                <th>Sexe & Ville</th>
                <th>Date inscription</th>
                <th>Statut du Billet</th>
                <th class="text-end">Action Individuelle</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in filteredRegistrations" :key="r.id">
                <td class="text-center">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    :value="r.id" 
                    v-model="selectedIds"
                  >
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <img 
                      v-if="r.photo" 
                      :src="'/' + r.photo" 
                      alt="Photo" 
                      class="rounded-circle shadow-sm object-fit-cover" 
                      style="width: 38px; height: 38px;"
                    >
                    <div 
                      v-else 
                      class="rounded-circle bg-danger-subtle text-danger fw-bold d-flex align-items-center justify-content-center"
                      style="width: 38px; height: 38px;"
                    >
                      {{ (r.first_name || r.name || 'P').charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <div class="fw-bold text-dark cursor-pointer text-decoration-underline" @click="viewParticipantDetails(r)">
                        {{ r.first_name }} {{ r.name }}
                      </div>
                      <small class="text-muted">#Billet-{{ r.id }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="small fw-semibold text-dark">{{ r.email }}</div>
                  <div v-if="r.phone_whatsapp" class="small text-success">
                    <i class="bi bi-whatsapp me-1"></i>{{ r.phone_whatsapp }}
                  </div>
                </td>
                <td>
                  <span class="badge bg-light text-dark border me-1">{{ r.gender || 'N/A' }}</span>
                  <span class="small text-muted">{{ r.city || 'Non renseigné' }}</span>
                </td>
                <td>
                  <small class="text-muted">{{ formatDate(r.created_at) }}</small>
                </td>
                <td>
                  <span v-if="r.is_confirmed" class="badge bg-success-subtle text-success">
                    <i class="bi bi-check-circle-fill me-1"></i>Confirmé
                  </span>
                  <span v-else class="badge bg-warning-subtle text-warning-emphasis">
                    <i class="bi bi-clock me-1"></i>En attente d'envoi
                  </span>
                </td>
                <td class="text-end">
                  <button 
                    v-if="!r.is_confirmed"
                    type="button" 
                    class="btn btn-sm btn-outline-primary rounded-pill px-3"
                    :disabled="isSendingSingle === r.id"
                    @click="sendSingleConfirmation(r.id)"
                  >
                    <span v-if="isSendingSingle === r.id" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-send me-1"></i>
                    Envoyer billet
                  </button>
                  <span v-else class="text-success small fw-bold">
                    <i class="bi bi-check2-all me-1"></i>Billet transmis
                  </span>
                </td>
              </tr>

              <tr v-if="filteredRegistrations.length === 0">
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="bi bi-people fs-1 text-muted d-block mb-2"></i>
                  Aucun participant ne correspond aux critères de recherche.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Participant Details Modal -->
    <div v-if="activeParticipant" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">Fiche Participant</h5>
            <button type="button" class="btn-close" @click="activeParticipant = null"></button>
          </div>
          <div class="modal-body p-4 text-center">
            <img 
              v-if="activeParticipant.photo" 
              :src="'/' + activeParticipant.photo" 
              alt="Photo" 
              class="rounded-circle shadow mb-3 object-fit-cover" 
              style="width: 90px; height: 90px;"
            >
            <div 
              v-else 
              class="rounded-circle bg-danger-subtle text-danger fw-bold d-flex align-items-center justify-content-center shadow mx-auto mb-3 fs-2"
              style="width: 90px; height: 90px;"
            >
              {{ (activeParticipant.first_name || activeParticipant.name || 'P').charAt(0).toUpperCase() }}
            </div>

            <h4 class="fw-bold text-dark mb-1">{{ activeParticipant.first_name }} {{ activeParticipant.name }}</h4>
            <p class="text-muted small mb-3">{{ activeParticipant.email }} &bull; {{ activeParticipant.phone_whatsapp }}</p>

            <div class="text-start bg-light rounded-3 p-3 mb-3">
              <div class="small fw-bold text-dark mb-1">Présentation / Bio :</div>
              <p class="text-muted small mb-0">{{ activeParticipant.bio || 'Aucune description fournie.' }}</p>
            </div>

            <div class="d-flex justify-content-between text-muted small px-2">
              <span>Ville : <strong>{{ activeParticipant.city }}</strong></span>
              <span>Genre : <strong>{{ activeParticipant.gender }}</strong></span>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary rounded-pill px-4" @click="activeParticipant = null">Fermer</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
  conference: {
    type: Object,
    required: true,
  },
})

const registrations = computed(() => props.conference.registrations || [])

const confirmedCount = computed(() => registrations.value.filter(r => r.is_confirmed).length)
const pendingCount = computed(() => registrations.value.filter(r => !r.is_confirmed).length)

const searchTerm = ref('')
const statusFilter = ref('all')
const selectedIds = ref([])

const filteredRegistrations = computed(() => {
  return registrations.value.filter(r => {
    const matchesSearch = !searchTerm.value || 
      `${r.first_name} ${r.name}`.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      r.email.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      (r.city && r.city.toLowerCase().includes(searchTerm.value.toLowerCase()))

    const matchesStatus = statusFilter.value === 'all' || 
      (statusFilter.value === 'confirmed' && r.is_confirmed) ||
      (statusFilter.value === 'pending' && !r.is_confirmed)

    return matchesSearch && matchesStatus
  })
})

const unconfirmedSelected = computed(() => {
  return selectedIds.value.filter(id => {
    const item = registrations.value.find(r => r.id === id)
    return item && !item.is_confirmed
  })
})

const isAllSelected = computed(() => {
  return filteredRegistrations.value.length > 0 && selectedIds.value.length === filteredRegistrations.value.length
})

function toggleSelectAll(e) {
  if (e.target.checked) {
    selectedIds.value = filteredRegistrations.value.map(r => r.id)
  } else {
    selectedIds.value = []
  }
}

// Bulk sending state & animation
const isSendingBulk = ref(false)
const bulkDone = ref(false)
const currentSentCount = ref(0)
const totalToSentCount = ref(0)

const progressPercentage = computed(() => {
  if (totalToSentCount.value === 0) return 0
  return Math.min(100, Math.round((currentSentCount.value / totalToSentCount.value) * 100))
})

const bulkStatusText = computed(() => {
  if (isSendingBulk.value) {
    return `${currentSentCount.value} emails envoyés sur ${totalToSentCount.value} participants...`
  }
  return `Envoi complété avec succès (${currentSentCount.value}/${totalToSentCount.value}) !`
})

function sendBulkConfirmation() {
  const ids = unconfirmedSelected.value
  if (ids.length === 0) return

  totalToSentCount.value = ids.length
  currentSentCount.value = 0
  isSendingBulk.value = true
  bulkDone.value = false

  // Animated incremental counter for immediate responsive feedback
  const interval = setInterval(() => {
    if (currentSentCount.value < totalToSentCount.value) {
      currentSentCount.value++
    } else {
      clearInterval(interval)
    }
  }, 300)

  router.post(`/espace-utilisateur/conferences/${props.conference.id}/participants/confirmer-tous`, {
    selected_ids: ids,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      clearInterval(interval)
      currentSentCount.value = totalToSentCount.value
      isSendingBulk.value = false
      bulkDone.value = true
      selectedIds.value = []
    },
    onError: () => {
      clearInterval(interval)
      isSendingBulk.value = false
    }
  })
}

// Single sending
const isSendingSingle = ref(null)

function sendSingleConfirmation(regId) {
  isSendingSingle.value = regId
  router.post(`/espace-utilisateur/conferences/participants/${regId}/confirmer`, {}, {
    preserveScroll: true,
    onFinish: () => {
      isSendingSingle.value = null
    }
  })
}

// Details modal
const activeParticipant = ref(null)
function viewParticipantDetails(p) {
  activeParticipant.value = p
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.spin {
  animation: spin 1s linear infinite;
  display: inline-block;
}
@keyframes spin {
  100% { transform: rotate(360deg); }
}
</style>
