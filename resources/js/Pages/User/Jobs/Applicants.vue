<template>
  <FrontLayout>
    <Head :title="'Candidatures - ' + job.title" />

    <div class="container my-5">
      
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item"><Link href="/espace-utilisateur">Tableau de bord</Link></li>
          <li class="breadcrumb-item"><Link href="/espace-utilisateur?section=jobs">Offres d'Emploi</Link></li>
          <li class="breadcrumb-item active">Candidats : {{ job.title }}</li>
        </ol>
      </nav>

      <!-- Flash Success -->
      <div v-if="$page.props.flash?.success" class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ $page.props.flash.success }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>

      <!-- Top Header Card -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
              <span class="badge bg-danger text-uppercase">{{ job.contract_type }}</span>
              <span class="badge bg-light text-dark border">{{ job.city }}</span>
              <span v-if="job.has_online_test" class="badge bg-primary-subtle text-primary border border-primary">
                <i class="bi bi-patch-question-fill me-1"></i>Évaluation en ligne active
              </span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">{{ job.title }}</h3>
            <p class="text-muted small mb-0">Gestion et sélection des candidats ayant postulé à votre offre.</p>
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger text-white fs-6 px-3 py-2 rounded-pill shadow-sm">
              <i class="bi bi-people-fill me-1"></i> {{ (job.applications || []).length }} Candidat(s)
            </span>
            <Link :href="'/espace-utilisateur/jobs/' + job.id + '/edit'" class="btn btn-outline-dark btn-sm rounded-pill px-3">
              <i class="bi bi-pencil me-1"></i> Modifier l'offre
            </Link>
          </div>
        </div>
      </div>

      <!-- Candidates Table / Cards -->
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        
        <!-- Filter Tabs -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 border-bottom pb-3">
          <div class="btn-group btn-group-sm">
            <button 
              type="button" 
              class="btn rounded-pill px-3 me-1" 
              :class="filterStatus === 'all' ? 'btn-danger' : 'btn-outline-secondary'"
              @click="filterStatus = 'all'"
            >
              Tous ({{ (job.applications || []).length }})
            </button>
            <button 
              type="button" 
              class="btn rounded-pill px-3 me-1" 
              :class="filterStatus === 'submitted' ? 'btn-danger' : 'btn-outline-secondary'"
              @click="filterStatus = 'submitted'"
            >
              En attente ({{ countStatus('submitted') }})
            </button>
            <button 
              type="button" 
              class="btn rounded-pill px-3 me-1" 
              :class="filterStatus === 'interview_scheduled' ? 'btn-danger' : 'btn-outline-secondary'"
              @click="filterStatus = 'interview_scheduled'"
            >
              Entretiens convoqués ({{ countStatus('interview_scheduled') }})
            </button>
            <button 
              type="button" 
              class="btn rounded-pill px-3" 
              :class="filterStatus === 'rejected' ? 'btn-danger' : 'btn-outline-secondary'"
              @click="filterStatus = 'rejected'"
            >
              Rejetées ({{ countStatus('rejected') }})
            </button>
          </div>
        </div>

        <!-- List of applicants -->
        <div v-if="filteredApplications.length" class="d-flex flex-column gap-3">
          <div 
            v-for="app in filteredApplications" 
            :key="app.id" 
            class="card border rounded-4 p-4 transition-all"
            :class="app.status === 'interview_scheduled' ? 'border-success-subtle bg-success-subtle bg-opacity-10' : (app.status === 'rejected' ? 'border-danger-subtle bg-danger-subtle bg-opacity-10' : 'bg-light')"
          >
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
              
              <!-- Candidate Profile -->
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; font-size: 20px;">
                  {{ (app.name || 'C').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <h5 class="fw-bold mb-0 text-dark">{{ app.name }}</h5>
                  <div class="small text-muted d-flex align-items-center gap-2 flex-wrap">
                    <span><i class="bi bi-envelope me-1"></i>{{ app.email }}</span>
                    <span v-if="app.phone">&bull; <i class="bi bi-telephone me-1"></i>{{ app.phone }}</span>
                    <span>&bull; Postulé le {{ new Date(app.created_at).toLocaleDateString('fr-FR') }}</span>
                  </div>
                </div>
              </div>

              <!-- Status Badge -->
              <div>
                <span 
                  class="badge fs-6 px-3 py-2 rounded-pill"
                  :class="statusBadgeClass(app.status)"
                >
                  {{ statusLabel(app.status) }}
                </span>
              </div>
            </div>

            <!-- Online Test Score & Examination Notice if Job has Test -->
            <div v-if="job.has_online_test" class="p-3 bg-white rounded-3 border mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-patch-question-fill text-primary fs-4"></i>
                <div>
                  <div class="fw-bold text-dark small">Résultats du Test d'Évaluation en Ligne</div>
                  <div class="small text-muted">
                    Score QCM : 
                    <span class="badge" :class="app.test_score >= 70 ? 'bg-success' : (app.test_score >= 50 ? 'bg-warning text-dark' : 'bg-danger')">
                      {{ app.test_score !== null ? app.test_score + '%' : 'Non noté automatiquement' }}
                    </span>
                    &bull; 
                    <span v-if="app.examined_at" class="text-success fw-bold">
                      <i class="bi bi-check-circle-fill me-1"></i>Test examiné le {{ new Date(app.examined_at).toLocaleDateString('fr-FR') }}
                    </span>
                    <span v-else class="text-danger fw-bold">
                      <i class="bi bi-exclamation-triangle-fill me-1"></i>Test non encore examiné (Requis avant entretien)
                    </span>
                  </div>
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="button" @click="openTestModal(app)" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                  <i class="bi bi-card-checklist me-1"></i> Voir les réponses du test
                </button>
                <button 
                  v-if="!app.examined_at" 
                  type="button" 
                  @click="markExamined(app.id)" 
                  class="btn btn-sm btn-success rounded-pill px-3 fw-bold"
                >
                  <i class="bi bi-check-lg me-1"></i> Marquer examiné
                </button>
              </div>
            </div>

            <!-- "Parlez-nous un peu de vous" Excerpt -->
            <div v-if="app.presentation_text" class="mb-3 p-3 bg-white rounded-3 border">
              <div class="small fw-bold text-dark mb-1"><i class="bi bi-chat-quote-fill text-danger me-1"></i>Parlez-nous un peu de vous :</div>
              <p class="small text-secondary mb-0" style="white-space: pre-line;">{{ app.presentation_text }}</p>
            </div>

            <!-- Interview Details if already scheduled -->
            <div v-if="app.interview" class="alert alert-success border-0 rounded-3 p-3 mb-3 d-flex align-items-center gap-3">
              <i class="bi bi-calendar-check-fill fs-2 text-success"></i>
              <div>
                <h6 class="fw-bold text-success mb-1">Entretien Physique Confirmé</h6>
                <div class="small text-dark">
                  <strong>Date :</strong> {{ app.interview.interview_date }} à {{ app.interview.interview_time }} &bull;
                  <strong>Lieu :</strong> {{ app.interview.location }}
                </div>
                <div v-if="app.interview.notes" class="small text-muted mt-1">
                  <strong>Notes :</strong> {{ app.interview.notes }}
                </div>
              </div>
            </div>

            <!-- Rejection details if rejected -->
            <div v-if="app.status === 'rejected' && app.rejection_reason" class="alert alert-danger border-0 rounded-3 p-3 mb-3">
              <h6 class="fw-bold text-danger mb-1"><i class="bi bi-x-circle-fill me-1"></i>Candidature non retenue</h6>
              <div class="small text-danger-emphasis">
                <strong>Motif :</strong> {{ app.rejection_reason }}
              </div>
            </div>

            <!-- Documents & Actions Toolbar -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top flex-wrap gap-2">
              
              <!-- Download Documents -->
              <div class="d-flex gap-2 flex-wrap">
                <a 
                  v-if="app.cv_file" 
                  :href="'/' + app.cv_file" 
                  target="_blank" 
                  class="btn btn-sm btn-danger rounded-pill px-3 fw-bold"
                  title="Télécharger le CV au format Word ou PDF"
                >
                  <i class="bi bi-file-earmark-pdf me-1"></i> Télécharger CV ({{ app.cv_file.endsWith('.pdf') ? 'PDF' : 'Word' }})
                </a>

                <a 
                  v-if="app.cover_letter_file" 
                  :href="'/' + app.cover_letter_file" 
                  target="_blank" 
                  class="btn btn-sm btn-outline-dark rounded-pill px-3"
                  title="Télécharger la lettre de motivation"
                >
                  <i class="bi bi-file-earmark-text me-1"></i> Lettre de motivation
                </a>
              </div>

              <!-- Recruiter Decision Buttons -->
              <div class="d-flex gap-2">
                <!-- Schedule In-Person Interview Button -->
                <button 
                  v-if="app.status !== 'interview_scheduled' && app.status !== 'rejected'"
                  type="button" 
                  @click="openInterviewModal(app)" 
                  class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm"
                  :disabled="job.has_online_test && !app.examined_at"
                  :title="job.has_online_test && !app.examined_at ? 'Vous devez examiner le test en ligne avant de programmer l\'entretien' : 'Convoquer à un entretien physique'"
                >
                  <i class="bi bi-calendar-plus me-1"></i> Programmer Entretien Physique
                </button>

                <span v-else-if="app.status === 'interview_scheduled'" class="badge bg-success-subtle text-success border border-success p-2 rounded-pill small">
                  <i class="bi bi-check-all me-1"></i> Entretien déjà convoqué
                </span>

                <!-- Reject Applicant Button -->
                <button 
                  v-if="app.status !== 'rejected' && app.status !== 'interview_scheduled'"
                  type="button" 
                  @click="openRejectModal(app)" 
                  class="btn btn-sm btn-outline-danger rounded-pill px-3"
                >
                  <i class="bi bi-x-circle me-1"></i> Rejeter
                </button>
              </div>

            </div>

          </div>
        </div>

        <div v-else class="text-center py-5 text-muted bg-light rounded-4">
          <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
          <h5 class="fw-bold text-dark">Aucune candidature dans cette catégorie</h5>
          <p class="small mb-0">Les nouveaux candidats postulant en ligne s'afficheront ici en temps réel.</p>
        </div>

      </div>

    </div>

    <!-- MODAL 1: TEST EXAMINATION & ANSWERS -->
    <div v-if="selectedAppForTest" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); z-index: 1055;">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title"><i class="bi bi-patch-question text-primary me-2"></i>Réponses au Test : {{ selectedAppForTest.name }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="selectedAppForTest = null"></button>
          </div>
          <div class="modal-body p-4">
            <div class="p-3 bg-light rounded-3 mb-3 d-flex justify-content-between align-items-center">
              <div>
                <strong>Candidat :</strong> {{ selectedAppForTest.name }} ({{ selectedAppForTest.email }})
              </div>
              <span class="badge bg-primary fs-6">
                Score automatique : {{ selectedAppForTest.test_score !== null ? selectedAppForTest.test_score + '%' : 'N/A' }}
              </span>
            </div>

            <div v-if="job.questions && job.questions.length" class="d-flex flex-column gap-3">
              <div v-for="(q, idx) in job.questions" :key="q.id" class="p-3 rounded-3 border bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h6 class="fw-bold mb-0 text-dark">Question {{ idx + 1 }} : {{ q.question_text }}</h6>
                  <span class="badge bg-light text-dark border">{{ q.question_type === 'qcm' ? 'QCM' : 'Texte' }}</span>
                </div>

                <div class="mt-2">
                  <div class="small fw-bold text-muted mb-1">Réponse fournie par le candidat :</div>
                  <div class="p-2 rounded bg-light border small text-dark font-monospace">
                    {{ selectedAppForTest.test_answers?.[q.id] || '(Aucune réponse)' }}
                  </div>
                </div>

                <div v-if="q.question_type === 'qcm' && q.correct_answer" class="mt-2 small">
                  <span class="text-success fw-bold"><i class="bi bi-check2"></i> Bonne réponse attendue :</span>
                  <span class="text-dark ms-1">{{ q.correct_answer }}</span>
                </div>
              </div>
            </div>
            <div v-else class="text-muted small">Aucune question enregistrée.</div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-light border rounded-pill px-4" @click="selectedAppForTest = null">Fermer</button>
            <button 
              v-if="!selectedAppForTest.examined_at" 
              type="button" 
              class="btn btn-success rounded-pill px-4 fw-bold"
              @click="markExaminedAndClose(selectedAppForTest.id)"
            >
              <i class="bi bi-check-lg me-1"></i> Valider l'examen du test
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: SCHEDULE IN-PERSON INTERVIEW WITH PAST DATES BLOCKED & TEMPLATES -->
    <div v-if="selectedAppForInterview" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); z-index: 1055;">
      <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Convocation Entretien Physique : {{ selectedAppForInterview.name }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="selectedAppForInterview = null"></button>
          </div>
          <form @submit.prevent="submitInterview">
            <div class="modal-body p-4">
              
              <!-- Recruiter notice -->
              <div class="alert alert-success-subtle text-success-emphasis small rounded-3 mb-3">
                <i class="bi bi-envelope-check-fill me-1"></i>
                La validation enverra automatiquement un email officiel au candidat avec les coordonnées, la date et le lieu du rendez-vous.
              </div>

              <!-- Date & Time (Strictly blocks past dates and times) -->
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Date de l'entretien <span class="text-danger">*</span></label>
                  <input 
                    type="date" 
                    class="form-control" 
                    v-model="interviewForm.interview_date" 
                    :min="todayDate" 
                    required
                  >
                  <div class="form-text small text-muted">Les dates passées sont strictement bloquées.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label small fw-bold">Heure du rendez-vous <span class="text-danger">*</span></label>
                  <input 
                    type="time" 
                    class="form-control" 
                    v-model="interviewForm.interview_time" 
                    required
                  >
                </div>
              </div>

              <!-- Location / Address -->
              <div class="mb-3">
                <label class="form-label small fw-bold">Lieu exact du rendez-vous physique <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="interviewForm.location" 
                  placeholder="ex: Immeuble Kemit Labs, 3e étage, Rue Drouot, Akwa, Douala" 
                  required
                >
              </div>

              <!-- Template selector -->
              <div class="mb-3">
                <label class="form-label small fw-bold">Modèle d'email officiel à envoyer</label>
                <select class="form-select form-select-sm" @change="applyInterviewTemplate($event.target.value)">
                  <option value="standard">Modèle Standard - Entretien Technique & RH</option>
                  <option value="direc">Modèle Entretien Final avec Direction</option>
                  <option value="stage">Modèle Entretien Stage / Alternance</option>
                </select>
              </div>

              <!-- Message Email -->
              <div class="mb-3">
                <label class="form-label small fw-bold">Contenu du message d'invitation</label>
                <textarea 
                  class="form-control small font-monospace" 
                  rows="5" 
                  v-model="interviewForm.email_message"
                  required
                ></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Consignes / Notes privées (Optionnel)</label>
                <input 
                  type="text" 
                  class="form-control form-control-sm" 
                  v-model="interviewForm.notes" 
                  placeholder="ex: Prévoir pièce d'identité à l'accueil, apporter son ordinateur portable..."
                >
              </div>

            </div>
            <div class="modal-footer bg-light">
              <button type="button" class="btn btn-light border rounded-pill px-4" @click="selectedAppForInterview = null">Annuler</button>
              <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm" :disabled="interviewForm.processing">
                <span v-if="interviewForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i class="bi bi-send-fill me-1"></i> Confirmer & Envoyer l'Email
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL 3: REJECT CANDIDATE WITH REJECTION TEMPLATES -->
    <div v-if="selectedAppForReject" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); z-index: 1055;">
      <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Rejeter la candidature : {{ selectedAppForReject.name }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="selectedAppForReject = null"></button>
          </div>
          <form @submit.prevent="submitReject">
            <div class="modal-body p-4">
              
              <!-- Template selector -->
              <div class="mb-3">
                <label class="form-label small fw-bold">Modèle de notification de rejet</label>
                <select class="form-select form-select-sm" @change="applyRejectTemplate($event.target.value)">
                  <option value="standard">Modèle Courtois - Profil non retenu</option>
                  <option value="experience">Modèle Expérience requise insuffisante</option>
                  <option value="closed">Modèle Clôture du recrutement</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Motif du rejet <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="rejectForm.rejection_reason" 
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Message explicatif transmis au candidat</label>
                <textarea 
                  class="form-control small" 
                  rows="4" 
                  v-model="rejectForm.email_message"
                  required
                ></textarea>
              </div>

            </div>
            <div class="modal-footer bg-light">
              <button type="button" class="btn btn-light border rounded-pill px-4" @click="selectedAppForReject = null">Annuler</button>
              <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold" :disabled="rejectForm.processing">
                <span v-if="rejectForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                Confirmer le Rejet
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
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  job: { type: Object, required: true },
})

const filterStatus = ref('all')
const todayDate = new Date().toISOString().split('T')[0]

const selectedAppForTest = ref(null)
const selectedAppForInterview = ref(null)
const selectedAppForReject = ref(null)

const interviewForm = useForm({
  interview_date: todayDate,
  interview_time: '10:00',
  location: '',
  notes: '',
  email_message: '',
})

const rejectForm = useForm({
  rejection_reason: 'Profil ne correspondant pas aux critères techniques exigés.',
  email_message: '',
})

const filteredApplications = computed(() => {
  const apps = props.job.applications || []
  if (filterStatus.value === 'all') return apps
  return apps.filter(a => a.status === filterStatus.value)
})

function countStatus(st) {
  return (props.job.applications || []).filter(a => a.status === st).length
}

function statusBadgeClass(st) {
  switch (st) {
    case 'interview_scheduled': return 'bg-success text-white'
    case 'under_review': return 'bg-info text-dark'
    case 'rejected': return 'bg-danger text-white'
    default: return 'bg-warning text-dark'
  }
}

function statusLabel(st) {
  switch (st) {
    case 'interview_scheduled': return 'Entretien Convoqué'
    case 'under_review': return 'Test Examiné'
    case 'rejected': return 'Candidature Rejetée'
    default: return 'En attente d\'examen'
  }
}

function openTestModal(app) {
  selectedAppForTest.value = app
}

function markExamined(appId) {
  router.post(`/espace-utilisateur/jobs/candidats/${appId}/examiner`, {}, {
    preserveScroll: true,
  })
}

function markExaminedAndClose(appId) {
  markExamined(appId)
  selectedAppForTest.value = null
}

function openInterviewModal(app) {
  selectedAppForInterview.value = app
  interviewForm.interview_date = todayDate
  interviewForm.interview_time = '10:00'
  interviewForm.location = props.job.city || 'Douala'
  applyInterviewTemplate('standard')
}

function applyInterviewTemplate(type) {
  if (!selectedAppForInterview.value) return
  const candName = selectedAppForInterview.value.name
  const jobTitle = props.job.title

  if (type === 'standard') {
    interviewForm.email_message = `Bonjour ${candName},\n\nNous avons examiné votre candidature avec grand intérêt pour le poste de "${jobTitle}".\n\nNous avons le plaisir de vous inviter à un entretien d'évaluation physique dans nos locaux.\n\nMerci de bien vouloir vous munir de votre pièce d'identité et de confirmer votre présence par retour de mail.\n\nCordialement,\nL'équipe Recrutement.`
  } else if (type === 'direc') {
    interviewForm.email_message = `Bonjour ${candName},\n\nSuite au premier filtrage de votre dossier pour le poste de "${jobTitle}", nous souhaitons vous rencontrer pour un entretien final avec la direction.\n\nNous vous attendons à la date et heure convenues.\n\nBien cordialement.`
  } else {
    interviewForm.email_message = `Bonjour ${candName},\n\nVotre candidature pour le stage "${jobTitle}" a retenu notre attention.\n\nNous vous invitons à venir échanger sur vos motivations et objectifs pédagogiques.\n\nÀ très bientôt.`
  }
}

function submitInterview() {
  interviewForm.post(`/espace-utilisateur/jobs/candidats/${selectedAppForInterview.value.id}/convoquer`, {
    preserveScroll: true,
    onSuccess: () => {
      selectedAppForInterview.value = null
      interviewForm.reset()
    }
  })
}

function openRejectModal(app) {
  selectedAppForReject.value = app
  applyRejectTemplate('standard')
}

function applyRejectTemplate(type) {
  if (!selectedAppForReject.value) return
  const candName = selectedAppForReject.value.name
  const jobTitle = props.job.title

  if (type === 'standard') {
    rejectForm.rejection_reason = 'Profil ne correspondant pas aux critères recherchés.'
    rejectForm.email_message = `Bonjour ${candName},\n\nNous vous remercions pour l'intérêt porté à notre offre de "${jobTitle}".\n\nMalgré la qualité de votre parcours, nous avons décidé de poursuivre avec d'autres profils plus proches des impératifs actuels du poste.\n\nNous vous souhaitons pleine réussite dans vos démarches professionnelles.`
  } else if (type === 'experience') {
    rejectForm.rejection_reason = 'Niveau d\'expérience technique insuffisant.'
    rejectForm.email_message = `Bonjour ${candName},\n\nVotre candidature pour le poste de "${jobTitle}" a bien été examinée. Le niveau d'expertise technique requis sur ce poste ne nous permet pas de donner une suite favorable.\n\nNous conservons votre profil pour de futures opportunités plus juniors.`
  } else {
    rejectForm.rejection_reason = 'Clôture du processus de recrutement.'
    rejectForm.email_message = `Bonjour ${candName},\n\nNous vous informons que le poste de "${jobTitle}" a été pourvu. Votre candidature n'a donc pas pu être retenue.\n\nMerci pour votre engagement.`
  }
}

function submitReject() {
  rejectForm.post(`/espace-utilisateur/jobs/candidats/${selectedAppForReject.value.id}/rejeter`, {
    preserveScroll: true,
    onSuccess: () => {
      selectedAppForReject.value = null
      rejectForm.reset()
    }
  })
}
</script>
