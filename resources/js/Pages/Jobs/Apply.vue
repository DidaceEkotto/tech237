<template>
  <FrontLayout>
    <Head>
      <title>Postuler : {{ job.title }} | Tech237 Emplois IT</title>
      <meta name="description" :content="'Postuler à l\'offre ' + job.title + ' sur Tech237'">
    </Head>

    <div class="job-apply-page py-5 bg-light min-vh-100">
      <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/emplois" class="text-decoration-none text-muted">Offres d'Emploi</Link></li>
            <li class="breadcrumb-item">
              <Link :href="'/emploi/' + (job.slug || job.id)" class="text-decoration-none text-muted text-truncate d-inline-block" style="max-width: 250px;">
                {{ job.title }}
              </Link>
            </li>
            <li class="breadcrumb-item active text-danger fw-bold" aria-current="page">Déposer ma candidature</li>
          </ol>
        </nav>

        <!-- Deadline expired warning -->
        <div v-if="isDeadlinePassed" class="alert alert-danger rounded-4 border-0 p-4 mb-4 text-center">
          <i class="bi bi-calendar-x fs-1 d-block mb-2 text-danger"></i>
          <h4 class="fw-bold">Les candidatures pour cette offre sont désormais clôturées.</h4>
          <p class="mb-3 text-muted">La date limite était fixée au {{ formatDeadline(job.deadline_date) }}.</p>
          <Link href="/emplois" class="btn btn-outline-dark rounded-pill px-4">
            &larr; Découvrir d'autres offres d'emploi
          </Link>
        </div>

        <!-- Already Applied warning -->
        <div v-else-if="hasApplied" class="alert alert-success rounded-4 border-0 p-4 mb-4 text-center">
          <i class="bi bi-check-circle-fill fs-1 d-block mb-2 text-success"></i>
          <h4 class="fw-bold">Vous avez déjà postulé à ce poste !</h4>
          <p class="mb-3 text-muted">Votre dossier a bien été transmis aux recruteurs. Vous recevrez une notification par email dès l'examen de votre candidature.</p>
          <Link :href="'/emploi/' + (job.slug || job.id)" class="btn btn-outline-success rounded-pill px-4">
            &larr; Revenir à la fiche du poste
          </Link>
        </div>

        <!-- APPLICATION FORM -->
        <div v-else class="row g-4">
          
          <!-- FORM COLUMN (8 COLS) -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
              
              <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px;">
                  <i class="bi bi-send-check fs-4"></i>
                </div>
                <div>
                  <h3 class="fw-bold mb-1">Formulaire de Candidature</h3>
                  <p class="text-muted small mb-0">Remplissez les informations ci-dessous et déposez votre CV au format Word (.doc, .docx) ou PDF.</p>
                </div>
              </div>

              <!-- General Error Alert -->
              <div v-if="formError" class="alert alert-danger rounded-4 border-0 mb-4 small">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ formError }}
              </div>

              <form @submit.prevent="submitApplication">
                
                <!-- Section 1: Informations Personnelles -->
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-lines-fill text-danger me-2"></i>1. Vos Coordonnées</h5>
                
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Nom complet <span class="text-danger">*</span></label>
                    <input 
                      type="text" 
                      v-model="applyForm.name" 
                      class="form-control rounded-3" 
                      placeholder="ex: Jean Dupont" 
                      required
                    >
                    <div v-if="errors.name" class="text-danger extra-small mt-1">{{ errors.name }}</div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Adresse Email <span class="text-danger">*</span></label>
                    <input 
                      type="email" 
                      v-model="applyForm.email" 
                      class="form-control rounded-3" 
                      placeholder="ex: jean.dupont@email.com" 
                      required
                    >
                    <div v-if="errors.email" class="text-danger extra-small mt-1">{{ errors.email }}</div>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label small fw-bold">Numéro de téléphone / WhatsApp</label>
                    <input 
                      type="tel" 
                      v-model="applyForm.phone" 
                      class="form-control rounded-3" 
                      placeholder="ex: +237 690 00 00 00"
                    >
                    <div v-if="errors.phone" class="text-danger extra-small mt-1">{{ errors.phone }}</div>
                  </div>
                </div>

                <!-- Section 2: Documents à Téléverser -->
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-file-earmark-arrow-up-fill text-danger me-2"></i>2. Documents & Curriculum Vitae</h5>
                
                <div class="row g-3 mb-4">
                  <!-- CV Upload (Required) -->
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">
                      Curriculum Vitae (CV) <span class="text-danger">*</span>
                    </label>
                    <div class="border rounded-4 p-3 bg-light text-center cursor-pointer file-upload-box" :class="{ 'border-danger': !cvFileName }">
                      <input 
                        type="file" 
                        accept=".pdf,.doc,.docx" 
                        class="d-none" 
                        id="cvInput" 
                        @change="handleCvUpload" 
                        required
                      >
                      <label for="cvInput" class="cursor-pointer mb-0 w-100">
                        <i class="bi bi-file-earmark-pdf fs-2 text-danger d-block mb-1"></i>
                        <span v-if="cvFileName" class="fw-bold text-success small d-block text-truncate">
                          <i class="bi bi-check-lg me-1"></i>{{ cvFileName }}
                        </span>
                        <span v-else class="d-block small text-dark fw-semibold">
                          Cliquez pour choisir votre CV
                        </span>
                        <span class="text-muted extra-small d-block">Format PDF ou Word (.doc, .docx) - Max 10 Mo</span>
                      </label>
                    </div>
                    <div v-if="errors.cv" class="text-danger extra-small mt-1">{{ errors.cv }}</div>
                  </div>

                  <!-- Cover Letter Upload (Optional) -->
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">
                      Lettre de motivation (Optionnel)
                    </label>
                    <div class="border rounded-4 p-3 bg-light text-center cursor-pointer file-upload-box">
                      <input 
                        type="file" 
                        accept=".pdf,.doc,.docx" 
                        class="d-none" 
                        id="clInput" 
                        @change="handleCoverLetterUpload"
                      >
                      <label for="clInput" class="cursor-pointer mb-0 w-100">
                        <i class="bi bi-file-earmark-text fs-2 text-primary d-block mb-1"></i>
                        <span v-if="clFileName" class="fw-bold text-success small d-block text-truncate">
                          <i class="bi bi-check-lg me-1"></i>{{ clFileName }}
                        </span>
                        <span v-else class="d-block small text-dark fw-semibold">
                          Ajouter une lettre de motivation
                        </span>
                        <span class="text-muted extra-small d-block">Format PDF ou Word (.doc, .docx)</span>
                      </label>
                    </div>
                    <div v-if="errors.cover_letter_file" class="text-danger extra-small mt-1">{{ errors.cover_letter_file }}</div>
                  </div>
                </div>

                <!-- Section 3: Parlez-nous un peu de vous -->
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-chat-text-fill text-danger me-2"></i>3. Présentation & Motivations</h5>
                <div class="mb-4">
                  <label class="form-label small fw-bold">Parlez-nous un peu de vous <span class="text-danger">*</span></label>
                  <textarea 
                    v-model="applyForm.presentation_text" 
                    rows="5" 
                    class="form-control rounded-3" 
                    placeholder="Résumez en quelques paragraphes vos réalisations techniques clés, vos compétences, vos langages de prédilection et ce qui vous motive pour ce poste..."
                    required
                  ></textarea>
                  <div v-if="errors.presentation_text" class="text-danger extra-small mt-1">{{ errors.presentation_text }}</div>
                </div>

                <!-- Section 4: Questionnaire d'évaluation technique en ligne si configuré -->
                <div v-if="job.has_online_test && job.questions && job.questions.length > 0" class="mb-4 pt-3 border-top">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2">
                      <i class="bi bi-patch-question-fill fs-5"></i>
                    </div>
                    <div>
                      <h5 class="fw-bold text-dark mb-0">Évaluation Technique Requise</h5>
                      <small class="text-muted">Répondez aux {{ job.questions.length }} question(s) préparée(s) par l'équipe de recrutement.</small>
                    </div>
                  </div>

                  <div class="card border-0 bg-light rounded-4 p-4">
                    <div v-for="(q, index) in job.questions" :key="q.id" class="mb-4 pb-3" :class="{ 'border-bottom': index < job.questions.length - 1 }">
                      <label class="form-label fw-bold text-dark mb-2 fs-6">
                        <span class="badge rounded-pill me-2 text-white" style="background-color: var(--tech-green);">Q{{ index + 1 }}</span>
                        {{ q.question_text || q.question }}
                      </label>

                      <!-- QCM Options -->
                      <div v-if="q.question_type === 'qcm' && q.options" class="d-flex flex-column gap-2 mt-2">
                        <div 
                          v-for="(opt, optIdx) in (Array.isArray(q.options) ? q.options : JSON.parse(q.options || '[]'))" 
                          :key="optIdx" 
                          class="form-check p-2 ps-4 bg-white rounded-3 border"
                        >
                          <input 
                            class="form-check-input" 
                            type="radio" 
                            :name="'q_' + q.id" 
                            :id="'q_' + q.id + '_' + optIdx" 
                            :value="opt" 
                            v-model="answers[q.id]" 
                            required
                          >
                          <label class="form-check-label small fw-semibold text-dark cursor-pointer w-100" :for="'q_' + q.id + '_' + optIdx">
                            {{ opt }}
                          </label>
                        </div>
                      </div>

                      <!-- Open Text Answer -->
                      <div v-else class="mt-2">
                        <textarea 
                          v-model="answers[q.id]" 
                          rows="3" 
                          class="form-control form-control-sm bg-white" 
                          placeholder="Votre réponse argumentée..." 
                          required
                        ></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2">
                  <Link :href="'/emploi/' + (job.slug || job.id)" class="btn btn-light rounded-pill px-4 text-muted fw-bold">
                    &larr; Annuler
                  </Link>

                  <button 
                    type="submit" 
                    :disabled="isSubmitting" 
                    class="btn btn-danger rounded-pill px-5 py-3 fw-bold shadow-sm d-flex align-items-center gap-2"
                  >
                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="bi bi-send-check-fill fs-5"></i>
                    <span>Confirmer & Envoyer ma candidature</span>
                  </button>
                </div>

              </form>

            </div>
          </div>

          <!-- SIDEBAR SUMMARY COLUMN (4 COLS) -->
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 85px;">
              <h5 class="fw-bold text-dark mb-3"><i class="bi bi-briefcase text-danger me-2"></i>Détails du Poste</h5>
              
              <div class="mb-3">
                <span class="badge bg-danger text-uppercase px-3 py-1 rounded-pill fw-bold mb-2">
                  {{ job.contract_type }}
                </span>
                <h4 class="fw-bold text-dark mb-1">{{ job.title }}</h4>
                <p class="text-muted small mb-0">
                  <i class="bi bi-building me-1"></i>{{ job.company_name }} &bull; <i class="bi bi-geo-alt me-1"></i>{{ job.city }}
                </p>
              </div>

              <hr>

              <div class="d-flex flex-column gap-3 mb-4">
                <div>
                  <small class="text-muted d-block text-uppercase extra-small fw-bold">Rémunération</small>
                  <strong class="text-dark">
                    {{ job.salary_type === 'fixed' && job.salary_amount ? Number(job.salary_amount).toLocaleString() + ' FCFA / mois' : (job.salary_range || 'À négocier') }}
                  </strong>
                </div>

                <div>
                  <small class="text-muted d-block text-uppercase extra-small fw-bold">Domaine Technique</small>
                  <strong class="text-dark">{{ job.it_domain }}</strong>
                </div>

                <div>
                  <small class="text-muted d-block text-uppercase extra-small fw-bold">Date limite de dépôt</small>
                  <strong class="text-danger d-inline-flex align-items-center gap-1">
                    <i class="bi bi-calendar-event"></i>
                    {{ formatDeadline(job.deadline_date) }}
                  </strong>
                </div>
              </div>

              <div class="bg-light p-3 rounded-4 mb-3">
                <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-info-circle text-primary me-1"></i>Conseil pour postuler</h6>
                <p class="text-muted extra-small mb-0">
                  Veillez à ce que votre CV mentionne clairement vos projets récents, vos technologies maîtresses ainsi que des liens vers vos réalisations ou votre GitHub si possible.
                </p>
              </div>

              <Link :href="'/emploi/' + (job.slug || job.id)" class="btn btn-outline-secondary rounded-pill w-100 btn-sm fw-bold">
                &larr; Relire la description du poste
              </Link>
            </div>
          </div>

        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  job: Object,
  hasApplied: Boolean,
  errors: {
    type: Object,
    default: () => ({}),
  }
})

const isSubmitting = ref(false)
const formError = ref('')
const cvFileName = ref('')
const clFileName = ref('')

const isDeadlinePassed = computed(() => {
  if (!props.job.deadline_date) return false
  const deadline = new Date(props.job.deadline_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return deadline < today
})

const applyForm = ref({
  name: '',
  email: '',
  phone: '',
  cv: null,
  cover_letter_file: null,
  presentation_text: '',
})

const answers = ref({})

function handleCvUpload(e) {
  const file = e.target.files[0]
  if (file) {
    applyForm.value.cv = file
    cvFileName.value = file.name
  }
}

function handleCoverLetterUpload(e) {
  const file = e.target.files[0]
  if (file) {
    applyForm.value.cover_letter_file = file
    clFileName.value = file.name
  }
}

function submitApplication() {
  if (!applyForm.value.cv) {
    formError.value = 'Le téléversement de votre CV au format Word (.doc, .docx) ou PDF (.pdf) est obligatoire pour postuler.'
    return
  }

  formError.value = ''
  isSubmitting.value = true

  const data = new FormData()
  for (const key in applyForm.value) {
    if (applyForm.value[key] !== null) {
      data.append(key, applyForm.value[key])
    }
  }

  for (const qId in answers.value) {
    data.append(`answers[${qId}]`, answers.value[qId])
  }

  router.post(`/emploi/${props.job.id}/postuler`, data, {
    preserveScroll: true,
    onSuccess: () => {
      isSubmitting.value = false
    },
    onError: (errors) => {
      isSubmitting.value = false
      if (errors && Object.keys(errors).length > 0) {
        formError.value = Object.values(errors)[0]
      }
    }
  })
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
.file-upload-box {
  transition: all 0.2s ease;
}
.file-upload-box:hover {
  background-color: #f8fafc !important;
  border-color: #FF004B !important;
}
.cursor-pointer {
  cursor: pointer;
}
.extra-small {
  font-size: 11px;
}
</style>
