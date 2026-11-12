<template>
  <FrontLayout>
    <Head :title="'Réponses • ' + form.title + ' • Tech237 Forms'" />

    <div class="forms-responses-page py-5 bg-slate-50 min-vh-100" style="background-color: #f8fafc;">
      <div class="container-fluid px-lg-5">
        
        <!-- Header Toolbar -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 bg-white p-4 rounded-4 shadow-sm border">
          <div class="d-flex align-items-center gap-3">
            <Link href="/espace-utilisateur/formulaires" class="btn btn-sm btn-light rounded-circle p-2 text-muted" title="Retour aux formulaires">
              <i class="bi bi-arrow-left fs-5"></i>
            </Link>

            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="fw-black text-dark mb-0">{{ form.title }}</h4>
                <span class="badge rounded-pill px-2 py-1 extra-small" :class="form.status === 'published' ? 'bg-success' : 'bg-secondary'">
                  {{ form.status === 'published' ? 'En ligne' : 'Fermé' }}
                </span>
              </div>
              <div class="extra-small text-muted mt-1">
                Lien public : <a :href="form.public_url" target="_blank" class="text-success text-decoration-none fw-bold">{{ form.public_url }}</a>
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center gap-2 flex-wrap">
            <Link :href="`/espace-utilisateur/formulaires/${form.id}/editer`" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1">
              <i class="bi bi-pencil"></i>
              <span>Modifier le formulaire</span>
            </Link>

            <a :href="`/espace-utilisateur/formulaires/${form.id}/export-csv`" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
              <i class="bi bi-file-earmark-excel-fill"></i>
              <span>Exporter en CSV</span>
            </a>
          </div>
        </div>

        <!-- KPI Metrics Row -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="extra-small text-muted fw-bold text-uppercase">Réponses totales</span>
                <span class="rounded-circle bg-success bg-opacity-10 p-2 text-success">
                  <i class="bi bi-chat-left-text-fill"></i>
                </span>
              </div>
              <div class="h3 fw-black text-dark mb-0">{{ summary.total_responses }}</div>
              <div class="extra-small text-muted mt-1">Soumissions enregistrées</div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="extra-small text-muted fw-bold text-uppercase">Vues du formulaire</span>
                <span class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary">
                  <i class="bi bi-eye-fill"></i>
                </span>
              </div>
              <div class="h3 fw-black text-dark mb-0">{{ summary.views_count }}</div>
              <div class="extra-small text-muted mt-1">Visites sur le lien public</div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="extra-small text-muted fw-bold text-uppercase">Taux de conversion</span>
                <span class="rounded-circle bg-warning bg-opacity-10 p-2 text-warning">
                  <i class="bi bi-graph-up"></i>
                </span>
              </div>
              <div class="h3 fw-black text-dark mb-0">{{ summary.conversion_rate }}%</div>
              <div class="extra-small text-muted mt-1">Visiteurs ayant répondu</div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="extra-small text-muted fw-bold text-uppercase">Temps moyen</span>
                <span class="rounded-circle bg-info bg-opacity-10 p-2 text-info">
                  <i class="bi bi-clock-history"></i>
                </span>
              </div>
              <div class="h3 fw-black text-dark mb-0">{{ formatDuration(summary.avg_duration_seconds) }}</div>
              <div class="extra-small text-muted mt-1">Pour compléter les questions</div>
            </div>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3">
          <li class="nav-item">
            <button 
              class="nav-link rounded-pill fw-bold small px-4 d-inline-flex align-items-center gap-2"
              :class="viewMode === 'table' ? 'active bg-dark text-white' : 'text-muted hover-bg-light'"
              @click="viewMode = 'table'"
            >
              <i class="bi bi-table"></i>
              <span>Tableau des réponses ({{ summary.total_responses }})</span>
            </button>
          </li>
          <li class="nav-item">
            <button 
              class="nav-link rounded-pill fw-bold small px-4 d-inline-flex align-items-center gap-2"
              :class="viewMode === 'charts' ? 'active bg-dark text-white' : 'text-muted hover-bg-light'"
              @click="viewMode = 'charts'"
            >
              <i class="bi bi-pie-chart-fill"></i>
              <span>Synthèse &amp; Statistiques visuelles</span>
            </button>
          </li>
        </ul>

        <!-- VIEW 1: RESPONSES TABLE -->
        <div v-show="viewMode === 'table'">
          <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
            
            <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5 class="fw-bold mb-0 text-dark">Détail des soumissions</h5>
              
              <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 260px;">
                  <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                    <i class="bi bi-search"></i>
                  </span>
                  <input type="text" v-model="filterSearch" class="form-control border-start-0 rounded-end-pill" placeholder="Filtrer par email..." />
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light extra-small text-uppercase text-muted">
                  <tr>
                    <th class="ps-4"># ID</th>
                    <th>Date &amp; Heure</th>
                    <th>Répondant</th>
                    <th>Durée</th>
                    <th v-for="f in form.fields.slice(0, 3)" :key="f.id">
                      {{ f.label }}
                    </th>
                    <th class="text-end pe-4">Action</th>
                  </tr>
                </thead>
                <tbody class="small">
                  <tr v-for="(resp, rIdx) in filteredResponses" :key="resp.id">
                    <td class="ps-4 fw-bold text-muted">#{{ (responses.current_page - 1) * responses.per_page + rIdx + 1 }}</td>
                    <td class="text-nowrap">
                      {{ formatDate(resp.created_at) }}
                    </td>
                    <td>
                      <div v-if="resp.user" class="fw-bold text-dark d-flex align-items-center gap-1">
                        <i class="bi bi-person-check-fill text-success"></i>
                        <span>{{ resp.user.name }}</span>
                      </div>
                      <div class="text-muted extra-small">
                        {{ resp.respondent_email || 'Anonyme' }}
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border">
                        {{ resp.completed_in_seconds ? resp.completed_in_seconds + 's' : '--' }}
                      </span>
                    </td>
                    <td v-for="f in form.fields.slice(0, 3)" :key="f.id" class="text-truncate" style="max-width: 180px;">
                      {{ formatAnswerPreview(resp.answers, f) }}
                    </td>
                    <td class="text-end pe-4">
                      <button type="button" @click="openResponseModal(resp)" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-eye"></i> Voir
                      </button>
                    </td>
                  </tr>

                  <tr v-if="filteredResponses.length === 0">
                    <td :colspan="5 + Math.min(form.fields.length, 3)" class="text-center py-5 text-muted">
                      Aucune réponse enregistrée pour le moment.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="responses.links && responses.links.length > 3" class="p-3 bg-light border-top d-flex justify-content-center">
              <nav>
                <ul class="pagination pagination-sm mb-0">
                  <li v-for="(link, lIdx) in responses.links" :key="lIdx" class="page-item" :class="{ active: link.active, disabled: !link.url }">
                    <Link :href="link.url || '#'" class="page-link" v-html="link.label"></Link>
                  </li>
                </ul>
              </nav>
            </div>

          </div>
        </div>

        <!-- VIEW 2: VISUAL CHARTS & BREAKDOWN -->
        <div v-show="viewMode === 'charts'">
          <div v-if="fieldsStats.length > 0" class="row g-4">
            <div v-for="stat in fieldsStats" :key="stat.field_id" class="col-md-6 col-lg-4">
              <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
                <h6 class="fw-bold text-dark mb-3 line-clamp-1" :title="stat.label">
                  {{ stat.label }}
                </h6>

                <div class="d-flex flex-column gap-3">
                  <div v-for="(count, label) in stat.counts" :key="label">
                    <div class="d-flex justify-content-between align-items-center extra-small fw-bold mb-1">
                      <span class="text-dark text-truncate" style="max-width: 200px;">{{ label }}</span>
                      <span class="text-muted">{{ count }} ({{ calculatePercent(count) }}%)</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 8px;">
                      <div 
                        class="progress-bar bg-success rounded-pill" 
                        role="progressbar" 
                        :style="{ width: calculatePercent(count) + '%' }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
            <i class="bi bi-pie-chart text-muted mb-3" style="font-size: 42px;"></i>
            <h5 class="fw-bold text-dark mb-1">Aucune question à choix multiples</h5>
            <p class="text-muted small mb-0">
              Les statistiques visuelles s'affichent automatiquement pour les questions de type Menu déroulant, Choix unique, Cases à cocher ou Notes.
            </p>
          </div>
        </div>

      </div>
    </div>

    <!-- Individual Response Modal -->
    <div v-if="selectedResponse" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
          
          <div class="modal-header bg-dark text-white p-4">
            <div>
              <h5 class="modal-title fw-black mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-person text-success"></i> Fiche Réponse Détaillée
              </h5>
              <div class="extra-small text-white-50">
                Soumis le {{ formatDate(selectedResponse.created_at) }} • {{ selectedResponse.respondent_email || 'Anonyme' }}
              </div>
            </div>
            <button type="button" @click="selectedResponse = null" class="btn-close btn-close-white shadow-none"></button>
          </div>

          <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
            <div class="d-flex flex-column gap-3">
              <div v-for="(field, idx) in form.fields" :key="field.id" class="p-3 bg-light rounded-3 border">
                
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="extra-small fw-bold text-muted text-uppercase">
                    #{{ idx + 1 }} • {{ field.label }}
                  </span>
                  <span class="badge bg-white text-dark border extra-small">{{ field.type }}</span>
                </div>

                <!-- Answer Content Rendering -->
                <div class="mt-2">
                  <!-- File / PDF -->
                  <div v-if="field.type === 'file' && getAnswerValue(selectedResponse.answers, field)">
                    <a :href="getAnswerValue(selectedResponse.answers, field)" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2">
                      <i class="bi bi-file-earmark-arrow-down-fill"></i>
                      <span>Télécharger le fichier joint</span>
                    </a>
                  </div>

                  <!-- Code Snippet -->
                  <div v-else-if="field.type === 'code_snippet' && getAnswerValue(selectedResponse.answers, field)">
                    <pre class="bg-dark text-light p-3 rounded-3 extra-small mb-0 font-monospace" style="max-height: 200px; overflow-y: auto;"><code>{{ getAnswerValue(selectedResponse.answers, field) }}</code></pre>
                  </div>

                  <!-- GitHub Link -->
                  <div v-else-if="field.type === 'github_link' && getAnswerValue(selectedResponse.answers, field)">
                    <a :href="getAnswerValue(selectedResponse.answers, field)" target="_blank" class="text-dark fw-bold text-decoration-none">
                      <i class="bi bi-github me-1"></i> {{ getAnswerValue(selectedResponse.answers, field) }}
                    </a>
                  </div>

                  <!-- Rating -->
                  <div v-else-if="field.type === 'rating' && getAnswerValue(selectedResponse.answers, field)">
                    <div class="d-flex align-items-center gap-1 text-warning fs-5">
                      <i v-for="star in 5" :key="star" :class="star <= Number(getAnswerValue(selectedResponse.answers, field)) ? 'bi bi-star-fill' : 'bi bi-star text-muted'"></i>
                      <span class="small fw-bold text-dark ms-2">{{ getAnswerValue(selectedResponse.answers, field) }} / 5</span>
                    </div>
                  </div>

                  <!-- Array Answers (Checkbox) -->
                  <div v-else-if="Array.isArray(getAnswerValue(selectedResponse.answers, field))">
                    <div class="d-flex flex-wrap gap-1">
                      <span v-for="val in getAnswerValue(selectedResponse.answers, field)" :key="val" class="badge bg-success-subtle text-success border">
                        {{ val }}
                      </span>
                    </div>
                  </div>

                  <!-- Default Text / Value -->
                  <div v-else class="text-dark fw-semibold">
                    {{ getAnswerValue(selectedResponse.answers, field) || '--' }}
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="modal-footer bg-light p-3 border-0">
            <button type="button" @click="selectedResponse = null" class="btn btn-secondary rounded-pill px-4 fw-semibold">
              Fermer
            </button>
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
  form: { type: Object, required: true },
  responses: { type: Object, required: true },
  summary: { type: Object, required: true },
  fieldsStats: { type: Array, default: () => [] }
})

const viewMode = ref('table')
const filterSearch = ref('')
const selectedResponse = ref(null)

const filteredResponses = computed(() => {
  const list = props.responses.data || []
  if (!filterSearch.value) return list
  return list.filter(r => (r.respondent_email || '').toLowerCase().includes(filterSearch.value.toLowerCase()))
})

function getAnswerValue(answers, field) {
  if (!answers) return null
  return answers[field.id] ?? answers[field.name] ?? null
}

function formatAnswerPreview(answers, field) {
  const val = getAnswerValue(answers, field)
  if (val === null || val === '') return '--'
  if (Array.isArray(val)) return val.join(', ')
  if (typeof val === 'string' && (val.startsWith('http') || val.startsWith('/storage/'))) return 'Lien / Fichier'
  return String(val)
}

function formatDate(dateStr) {
  if (!dateStr) return '--'
  return new Date(dateStr).toLocaleString('fr-FR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatDuration(sec) {
  if (!sec) return '--'
  if (sec < 60) return sec + 's'
  const min = Math.floor(sec / 60)
  const remainingSec = sec % 60
  return `${min}m ${remainingSec}s`
}

function calculatePercent(count) {
  if (!props.summary.total_responses) return 0
  return Math.round((count / props.summary.total_responses) * 100)
}

function openResponseModal(resp) {
  selectedResponse.value = resp
}
</script>

<style scoped>
.extra-small {
  font-size: 0.75rem;
}
</style>
