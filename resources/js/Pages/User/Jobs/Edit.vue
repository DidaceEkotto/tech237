<template>
  <FrontLayout>
    <Head :title="'Modifier l\'Offre - ' + job.title" />

    <div class="container my-5">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          
          <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
              <li class="breadcrumb-item"><Link href="/espace-utilisateur">Tableau de bord</Link></li>
              <li class="breadcrumb-item"><Link href="/espace-utilisateur?section=jobs">Offres d'Emploi</Link></li>
              <li class="breadcrumb-item active">{{ job.title }}</li>
            </ol>
          </nav>

          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
              <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                <i class="bi bi-pencil-square fs-3"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-1">Modifier l'Offre d'Emploi</h3>
                <p class="text-muted small mb-0">{{ job.title }} &bull; Statut : {{ job.status }}</p>
              </div>
            </div>

            <form @submit.prevent="submit">

              <div class="mb-3">
                <label class="form-label small fw-bold">Intitulé du poste <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control form-control-lg" 
                  v-model="form.title" 
                  required
                >
                <div v-if="form.errors.title" class="text-danger small mt-1">{{ form.errors.title }}</div>
              </div>

              <!-- Domaines Informatiques (Multi-Sélection Exclusive IT) -->
              <div class="mb-4 p-3 bg-light rounded-4 border">
                <label class="form-label small fw-bold text-dark mb-2">
                  <i class="bi bi-cpu-fill text-danger me-1"></i>Domaines Informatiques ciblés <span class="text-danger">* (Sélection multiple)</span>
                </label>
                <div class="row g-2">
                  <div v-for="dom in domains" :key="dom" class="col-md-6 col-lg-4">
                    <div class="form-check p-2 bg-white rounded-3 border h-100">
                      <input 
                        class="form-check-input ms-1" 
                        type="checkbox" 
                        :id="'edit-dom-' + dom" 
                        :value="dom" 
                        v-model="form.domains"
                      >
                      <label class="form-check-label small fw-semibold text-dark ms-2" :for="'edit-dom-' + dom">
                        {{ dom }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Type de contrat <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="form.contract_type" required>
                    <option v-for="(label, val) in contractTypes" :key="val" :value="val">
                      {{ label }}
                    </option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label small fw-bold">Ville / Localisation <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="form.city" required>
                    <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
                  </select>
                </div>
              </div>

              <!-- Salaire & Rémunération -->
              <div class="card p-3 bg-light border-0 rounded-4 mb-4">
                <div class="row g-3 align-items-center">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Rémunération proposée</label>
                    <div class="form-check mb-2">
                      <input 
                        class="form-check-input" 
                        type="radio" 
                        id="editSalNegotiable" 
                        value="negotiable" 
                        v-model="form.salary_type"
                      >
                      <label class="form-check-label small fw-semibold text-dark" for="editSalNegotiable">
                        À négocier selon profil et expérience
                      </label>
                    </div>
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="radio" 
                        id="editSalFixed" 
                        value="fixed" 
                        v-model="form.salary_type"
                      >
                      <label class="form-check-label small fw-semibold text-dark" for="editSalFixed">
                        Montant fixe mensuel
                      </label>
                    </div>
                  </div>

                  <div class="col-md-6" v-if="form.salary_type === 'fixed'">
                    <label class="form-label small fw-bold">Montant en FCFA</label>
                    <div class="input-group">
                      <input type="number" class="form-control" v-model="form.salary_amount" min="0">
                      <span class="input-group-text bg-white">FCFA / mois</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Date limite de candidature -->
              <div class="mb-4">
                <label class="form-label small fw-bold">Date limite de dépôt de candidature</label>
                <input 
                  type="date" 
                  class="form-control" 
                  v-model="form.deadline_date"
                >
              </div>

              <!-- Options de téléversement (Lettre) -->
              <div class="card p-4 bg-light border-0 rounded-4 mb-4">
                <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-file-earmark-arrow-up text-danger me-2"></i>Documents exigés des candidats</h6>
                
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input" type="checkbox" checked disabled>
                  <label class="form-check-label fw-bold text-dark">
                    Dépôt du CV au format Word (.doc, .docx) ou PDF (.pdf) <span class="badge bg-danger ms-1">Obligatoire</span>
                  </label>
                </div>

                <div class="form-check form-switch">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="editCoverLetter" 
                    v-model="form.accepts_cover_letter"
                  >
                  <label class="form-check-label fw-bold text-dark" for="editCoverLetter">
                    Autoriser l'envoi d'une Lettre de Motivation (Word ou PDF)
                  </label>
                </div>
              </div>

              <!-- Accroche -->
              <div class="mb-3">
                <label class="form-label small fw-bold">Accroche courte du poste <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.short_description" 
                  required
                >
              </div>

              <!-- Description détaillée -->
              <div class="mb-4">
                <label class="form-label small fw-bold">Description complète du poste <span class="text-danger">*</span></label>
                <textarea 
                  class="form-control font-monospace" 
                  rows="8" 
                  v-model="form.description" 
                  required
                ></textarea>
              </div>

              <!-- Case à cocher test en ligne -->
              <div class="card p-4 rounded-4 mb-5 border-2" :class="form.has_online_test ? 'border-primary bg-primary-subtle' : 'border-secondary-subtle bg-light'">
                <div class="form-check form-switch mb-2">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="editHasOnlineTest" 
                    v-model="form.has_online_test"
                  >
                  <label class="form-check-label fw-bold text-dark fs-6" for="editHasOnlineTest">
                    <i class="bi bi-patch-question-fill text-primary me-1"></i>
                    Activer un test d'évaluation en ligne pour cette offre
                  </label>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <Link href="/espace-utilisateur?section=jobs" class="btn btn-light border rounded-pill px-4">
                  Annuler
                </Link>
                <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" :disabled="form.processing">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <span v-if="form.has_online_test">Modifier les questions du test &rarr;</span>
                  <span v-else>Mettre à jour l'offre</span>
                </button>
              </div>

            </form>
          </div>

        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  job: { type: Object, required: true },
  domains: { type: Array, default: () => [] },
  contractTypes: { type: Object, default: () => ({}) },
  cities: { type: Array, default: () => [] },
})

const form = useForm({
  title: props.job.title || '',
  domains: Array.isArray(props.job.domain) ? props.job.domain : ['Développement Web & Mobile'],
  contract_type: props.job.contract_type || 'CDI',
  salary_type: props.job.salary_type || 'negotiable',
  salary_amount: props.job.salary_amount || null,
  city: props.job.city || 'Douala',
  deadline_date: props.job.deadline_date ? props.job.deadline_date.substring(0, 10) : '',
  accepts_cv: true,
  accepts_cover_letter: Boolean(props.job.accepts_cover_letter),
  short_description: props.job.short_description || '',
  description: props.job.description || '',
  has_online_test: Boolean(props.job.has_online_test),
})

function submit() {
  form.post(`/espace-utilisateur/jobs/${props.job.id}`)
}
</script>
