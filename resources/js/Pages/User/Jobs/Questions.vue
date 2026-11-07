<template>
  <FrontLayout>
    <Head :title="'Test d\'Évaluation en Ligne - ' + job.title" />

    <div class="container my-5">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          
          <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
              <li class="breadcrumb-item"><Link href="/espace-utilisateur">Tableau de bord</Link></li>
              <li class="breadcrumb-item"><Link href="/espace-utilisateur?section=jobs">Offres d'Emploi</Link></li>
              <li class="breadcrumb-item active">Test d'Évaluation : {{ job.title }}</li>
            </ol>
          </nav>

          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
              <div>
                <div class="badge bg-primary text-uppercase mb-1">Évaluation Technique / QCM</div>
                <h3 class="fw-bold mb-1">Concevoir le Test d'Évaluation en Ligne</h3>
                <p class="text-muted small mb-0">Poste : <strong>{{ job.title }}</strong> &bull; {{ questions.length }} question(s) configurée(s)</p>
              </div>

              <div class="d-flex gap-2">
                <button type="button" @click="addQuestion('qcm')" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                  <i class="bi bi-ui-radios-grid me-1"></i> + Question QCM
                </button>
                <button type="button" @click="addQuestion('text')" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold">
                  <i class="bi bi-fonts me-1"></i> + Réponse Directe (Texte)
                </button>
              </div>
            </div>

            <div class="alert alert-info border-0 rounded-4 p-3 small mb-4 d-flex align-items-center gap-3">
              <i class="bi bi-info-circle-fill fs-3 text-primary"></i>
              <div>
                <strong>Fonctionnement de l'évaluation :</strong> Les questions configurées ici seront soumises au candidat lors de sa postulation en ligne. Les questions QCM avec bonne réponse définie permettent un calcul automatique du score (sur 100), facilitant votre tri des profils.
              </div>
            </div>

            <form @submit.prevent="submit">
              
              <!-- Questions List -->
              <div v-if="questions.length" class="d-flex flex-column gap-4 mb-4">
                <div 
                  v-for="(q, idx) in questions" 
                  :key="idx" 
                  class="card border rounded-4 p-4 position-relative shadow-sm transition-all"
                  :class="q.question_type === 'qcm' ? 'border-primary-subtle bg-white' : 'border-secondary-subtle bg-light'"
                >
                  <!-- Header: Index and Question Type -->
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-dark rounded-circle text-white p-2" style="width: 28px; height: 28px; line-height: 14px;">
                        {{ idx + 1 }}
                      </span>
                      <span 
                        class="badge rounded-pill fw-semibold"
                        :class="q.question_type === 'qcm' ? 'bg-primary text-white' : 'bg-secondary text-white'"
                      >
                        {{ q.question_type === 'qcm' ? 'Choix Multiple (QCM)' : 'Réponse Directe (Texte libre)' }}
                      </span>
                    </div>

                    <div class="d-flex gap-2">
                      <!-- Switch type button -->
                      <button 
                        type="button" 
                        class="btn btn-sm btn-light border rounded-pill px-2"
                        @click="q.question_type = q.question_type === 'qcm' ? 'text' : 'qcm'"
                        title="Changer le type de question"
                      >
                        <i class="bi bi-arrow-repeat me-1"></i>Changer en {{ q.question_type === 'qcm' ? 'Texte' : 'QCM' }}
                      </button>
                      <!-- Delete question button -->
                      <button 
                        type="button" 
                        @click="removeQuestion(idx)" 
                        class="btn btn-sm btn-outline-danger rounded-circle"
                        title="Supprimer la question"
                        style="width: 32px; height: 32px;"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Intitulé de la question -->
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Intitulé de la question <span class="text-danger">*</span></label>
                    <input 
                      type="text" 
                      class="form-control fw-semibold" 
                      v-model="q.question_text" 
                      placeholder="ex: Quelle est la différence entre une interface et une classe abstraite en PHP ?" 
                      required
                    >
                  </div>

                  <!-- Type A: TEXT DIRECT ANSWER PREVIEW -->
                  <div v-if="q.question_type === 'text'" class="p-3 bg-white rounded-3 border">
                    <label class="form-label small text-muted mb-1">
                      <i class="bi bi-chat-left-text me-1"></i>Aperçu candidat (Champ texte de saisie directe) :
                    </label>
                    <input type="text" class="form-control form-control-sm bg-light" placeholder="Le candidat rédigera sa réponse ici..." disabled>
                  </div>

                  <!-- Type B: QCM MULTIPLE CHOICE OPTIONS -->
                  <div v-else-if="q.question_type === 'qcm'" class="p-3 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <label class="form-label small fw-bold text-dark mb-0">
                        <i class="bi bi-list-check text-primary me-1"></i>Options de réponse (Cochez le bouton radio pour définir la bonne réponse) :
                      </label>
                      <button type="button" @click="addOption(idx)" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter une option
                      </button>
                    </div>

                    <div class="d-flex flex-column gap-2 mt-2">
                      <div 
                        v-for="(opt, optIdx) in q.options" 
                        :key="optIdx" 
                        class="d-flex align-items-center gap-2 bg-white p-2 rounded-3 border"
                      >
                        <!-- Radio to select correct answer -->
                        <div class="form-check ms-1" title="Marquer comme bonne réponse">
                          <input 
                            class="form-check-input" 
                            type="radio" 
                            :name="'correct-answer-' + idx" 
                            :value="opt" 
                            :checked="q.correct_answer === opt && opt !== ''"
                            @change="q.correct_answer = opt"
                            required
                          >
                        </div>

                        <!-- Option Text Input -->
                        <input 
                          type="text" 
                          class="form-control form-control-sm border-0" 
                          v-model="q.options[optIdx]" 
                          placeholder="Option de réponse..."
                          @input="onOptionInput(idx, optIdx)"
                          required
                        >

                        <span v-if="q.correct_answer === opt && opt !== ''" class="badge bg-success-subtle text-success small">
                          <i class="bi bi-check2"></i> Bonne réponse
                        </span>

                        <!-- Remove option button -->
                        <button 
                          v-if="q.options.length > 2" 
                          type="button" 
                          @click="removeOption(idx, optIdx)" 
                          class="btn btn-sm btn-light text-muted rounded-circle"
                          title="Supprimer cette option"
                        >
                          <i class="bi bi-x"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Empty State -->
              <div v-else class="text-center py-5 bg-light rounded-4 border mb-4">
                <i class="bi bi-patch-question fs-1 d-block mb-2 text-primary"></i>
                <h5 class="fw-bold text-dark">Aucune question ajoutée pour le moment</h5>
                <p class="small text-muted mb-3">Créez au moins une question pour valider le test d'évaluation.</p>
                <div class="d-flex justify-content-center gap-2">
                  <button type="button" @click="addQuestion('qcm')" class="btn btn-primary rounded-pill px-4 fw-bold">
                    <i class="bi bi-ui-radios-grid me-1"></i> Ajouter une question QCM
                  </button>
                  <button type="button" @click="addQuestion('text')" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                    <i class="bi bi-fonts me-1"></i> Question réponse libre
                  </button>
                </div>
              </div>

              <!-- Buttons Bar -->
              <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <Link href="/espace-utilisateur?section=jobs" class="btn btn-light border rounded-pill px-4">
                  Passer pour le moment
                </Link>
                
                <div class="d-flex gap-2">
                  <button type="button" @click="addQuestion('qcm')" class="btn btn-outline-dark rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter une question
                  </button>
                  <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" :disabled="isSubmitting || questions.length === 0">
                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-check2-circle me-1"></i> Valider & Soumettre l'offre
                  </button>
                </div>
              </div>

            </form>
          </div>

        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  job: { type: Object, required: true },
})

const isSubmitting = ref(false)

// Initialize questions from existing or start with 2 default questions
const initialQuestions = (props.job.questions && props.job.questions.length) 
  ? props.job.questions.map(q => ({
      question_text: q.question_text,
      question_type: q.question_type || 'qcm',
      options: q.options && q.options.length ? [...q.options] : ['Option 1', 'Option 2', 'Option 3', 'Option 4'],
      correct_answer: q.correct_answer || (q.options ? q.options[0] : ''),
    }))
  : [
      {
        question_text: 'Quelle technologie utilisez-vous en priorité pour l\'architecture backend ?',
        question_type: 'qcm',
        options: ['Laravel (PHP)', 'Node.js / Express', 'Django (Python)', 'Spring Boot (Java)'],
        correct_answer: 'Laravel (PHP)',
      },
      {
        question_text: 'Décrivez brièvement un projet complexe que vous avez mené à terme.',
        question_type: 'text',
        options: [],
        correct_answer: '',
      }
    ]

const questions = ref(initialQuestions)

function addQuestion(type = 'qcm') {
  if (type === 'qcm') {
    questions.value.push({
      question_text: '',
      question_type: 'qcm',
      options: ['Option A', 'Option B', 'Option C'],
      correct_answer: 'Option A',
    })
  } else {
    questions.value.push({
      question_text: '',
      question_type: 'text',
      options: [],
      correct_answer: '',
    })
  }
}

function removeQuestion(idx) {
  questions.value.splice(idx, 1)
}

function addOption(qIdx) {
  questions.value[qIdx].options.push('Nouvelle option')
}

function removeOption(qIdx, optIdx) {
  const removed = questions.value[qIdx].options[optIdx]
  questions.value[qIdx].options.splice(optIdx, 1)
  if (questions.value[qIdx].correct_answer === removed) {
    questions.value[qIdx].correct_answer = questions.value[qIdx].options[0] || ''
  }
}

function onOptionInput(qIdx, optIdx) {
  // If the edited option was the correct answer, update the reference
  const currentOptions = questions.value[qIdx].options
  if (!currentOptions.includes(questions.value[qIdx].correct_answer)) {
    questions.value[qIdx].correct_answer = currentOptions[optIdx]
  }
}

function submit() {
  if (questions.value.length === 0) {
    alert('Veuillez ajouter au moins une question.')
    return
  }

  isSubmitting.value = true
  router.post(`/espace-utilisateur/jobs/${props.job.id}/questions`, {
    questions: questions.value
  }, {
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}
</script>
