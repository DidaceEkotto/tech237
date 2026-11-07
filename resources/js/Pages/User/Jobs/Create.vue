<template>
  <FrontLayout>
    <Head title="Créer une Offre d'Emploi IT - Tech237" />

    <div class="container my-5">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          
          <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
              <li class="breadcrumb-item"><Link href="/espace-utilisateur">Tableau de bord</Link></li>
              <li class="breadcrumb-item"><Link href="/espace-utilisateur?section=jobs">Offres d'Emploi</Link></li>
              <li class="breadcrumb-item active">Nouvelle Offre</li>
            </ol>
          </nav>

          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
              <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                <i class="bi bi-briefcase-fill fs-3"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-1">Créer une Offre d'Emploi IT</h3>
                <p class="text-muted small mb-0">Recrutez des ingénieurs, développeurs et spécialistes de la tech camerounaise & internationale.</p>
              </div>
            </div>

            <form @submit.prevent="submit">

              <!-- Titre du poste -->
              <div class="mb-3">
                <label class="form-label small fw-bold">Intitulé du poste <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control form-control-lg" 
                  v-model="form.title" 
                  placeholder="ex: Développeur Full-Stack Laravel & Vue.js, Ingénieur DevOps Cloud..." 
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
                        :id="'dom-' + dom" 
                        :value="dom" 
                        v-model="form.domains"
                      >
                      <label class="form-check-label small fw-semibold text-dark ms-2" :for="'dom-' + dom">
                        {{ dom }}
                      </label>
                    </div>
                  </div>
                </div>
                <div v-if="form.errors.domains" class="text-danger small mt-2">{{ form.errors.domains }}</div>
              </div>

              <div class="row g-3 mb-3">
                <!-- Type de contrat -->
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Type de contrat <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="form.contract_type" required>
                    <option v-for="(label, val) in contractTypes" :key="val" :value="val">
                      {{ label }}
                    </option>
                  </select>
                </div>

                <!-- Ville -->
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
                        id="salNegotiable" 
                        value="negotiable" 
                        v-model="form.salary_type"
                      >
                      <label class="form-check-label small fw-semibold text-dark" for="salNegotiable">
                        À négocier selon profil et expérience
                      </label>
                    </div>
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="radio" 
                        id="salFixed" 
                        value="fixed" 
                        v-model="form.salary_type"
                      >
                      <label class="form-check-label small fw-semibold text-dark" for="salFixed">
                        Montant fixe mensuel
                      </label>
                    </div>
                  </div>

                  <div class="col-md-6" v-if="form.salary_type === 'fixed'">
                    <label class="form-label small fw-bold">Montant en FCFA</label>
                    <div class="input-group">
                      <input type="number" class="form-control" v-model="form.salary_amount" placeholder="ex: 450000" min="0">
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
                  :min="todayDate"
                >
                <div class="form-text small">Après cette date, le bouton postuler sera automatiquement désactivé sur le site.</div>
                <div v-if="form.errors.deadline_date" class="text-danger small mt-1">{{ form.errors.deadline_date }}</div>
              </div>

              <!-- Options de téléversement (CV & Lettre) -->
              <div class="card p-4 bg-light border-0 rounded-4 mb-4">
                <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-file-earmark-arrow-up text-danger me-2"></i>Documents exigés des candidats</h6>
                
                <!-- Option CV: Obligatoire et cochée par défaut -->
                <div class="form-check form-switch mb-3">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="acceptsCv" 
                    v-model="form.accepts_cv" 
                    disabled 
                    checked
                  >
                  <label class="form-check-label fw-bold text-dark" for="acceptsCv">
                    Accepter l'upload du CV en format Word (.doc, .docx) ou PDF (.pdf) <span class="badge bg-danger ms-1">Obligatoire</span>
                  </label>
                </div>

                <!-- Option Lettre de motivation -->
                <div class="form-check form-switch">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="acceptsCoverLetter" 
                    v-model="form.accepts_cover_letter"
                  >
                  <label class="form-check-label fw-bold text-dark" for="acceptsCoverLetter">
                    Autoriser l'upload d'une Lettre de Motivation en format Word (.doc, .docx) ou PDF (.pdf) (Optionnel pour le candidat)
                  </label>
                </div>
              </div>

              <!-- Accroche / Description courte -->
              <div class="mb-3">
                <label class="form-label small fw-bold">Accroche courte du poste <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.short_description" 
                  placeholder="ex: Rejoignez notre équipe tech pour concevoir les solutions de paiement de demain..." 
                  maxlength="400"
                  required
                >
                <div class="form-text small">Résumé synthétique affiché dans les cartes de listing.</div>
                <div v-if="form.errors.short_description" class="text-danger small mt-1">{{ form.errors.short_description }}</div>
              </div>

              <!-- Description détaillée WYSIWYG -->
              <div class="mb-4">
                <label class="form-label small fw-bold">Description complète du poste & Responsabilités <span class="text-danger">*</span></label>
                
                <!-- Simple WYSIWYG Toolbar -->
                <div class="border rounded-top p-2 bg-light d-flex gap-1 flex-wrap">
                  <button type="button" class="btn btn-sm btn-white border px-2 py-1" @click="insertTag('**', '**')" title="Gras">
                    <i class="bi bi-type-bold"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-white border px-2 py-1" @click="insertTag('*', '*')" title="Italique">
                    <i class="bi bi-type-italic"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-white border px-2 py-1" @click="insertTag('### ', '\n')" title="Titre de section">
                    <i class="bi bi-type-h3"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-white border px-2 py-1" @click="insertTag('- ', '\n')" title="Liste à puces">
                    <i class="bi bi-list-ul"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-white border px-2 py-1" @click="insertTag('`', '`')" title="Code">
                    <i class="bi bi-code"></i>
                  </button>
                </div>
                <textarea 
                  ref="descRef"
                  class="form-control rounded-bottom rounded-top-0 font-monospace" 
                  rows="8" 
                  v-model="form.description" 
                  placeholder="Détaillez les missions, compétences techniques requises, environnement de travail et avantages..." 
                  required
                ></textarea>
                <div v-if="form.errors.description" class="text-danger small mt-1">{{ form.errors.description }}</div>
              </div>

              <!-- Case à cocher : Créer un test d'évaluation en ligne (USER REQUIREMENT) -->
              <div class="card p-4 rounded-4 mb-5 border-2" :class="form.has_online_test ? 'border-primary bg-primary-subtle' : 'border-secondary-subtle bg-light'">
                <div class="form-check form-switch mb-2">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="hasOnlineTest" 
                    v-model="form.has_online_test"
                  >
                  <label class="form-check-label fw-bold text-dark fs-6" for="hasOnlineTest">
                    <i class="bi bi-patch-question-fill text-primary me-1"></i>
                    Créer un test d'évaluation en ligne pour cette offre
                  </label>
                </div>
                <p class="small text-muted mb-0 ps-4">
                  <span v-if="form.has_online_test" class="text-primary fw-semibold">
                    <i class="bi bi-arrow-right-circle-fill me-1"></i>En validant, vous serez redirigé vers l'interface de création des questions (QCM et questions ouvertes) avant soumission à l'administrateur.
                  </span>
                  <span v-else>
                    Si cette case reste décochée, votre offre sera directement transmise à l'administrateur avec le statut <strong>"En cours de validation"</strong>.
                  </span>
                </p>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <Link href="/espace-utilisateur?section=jobs" class="btn btn-light border rounded-pill px-4">
                  Annuler
                </Link>
                <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" :disabled="form.processing">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <span v-if="form.has_online_test">Créer les questions du test &rarr;</span>
                  <span v-else>Soumettre l'offre d'emploi</span>
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
import { ref } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  domains: { type: Array, default: () => [] },
  contractTypes: { type: Object, default: () => ({}) },
  cities: { type: Array, default: () => [] },
})

const todayDate = new Date().toISOString().split('T')[0]
const descRef = ref(null)

const form = useForm({
  title: '',
  domains: ['Développement Web & Mobile'],
  contract_type: 'CDI',
  salary_type: 'negotiable',
  salary_amount: null,
  city: 'Douala',
  deadline_date: '',
  accepts_cv: true,
  accepts_cover_letter: false,
  short_description: '',
  description: '',
  has_online_test: false,
})

function insertTag(before, after) {
  const el = descRef.value
  if (!el) return
  const start = el.selectionStart
  const end = el.selectionEnd
  const text = form.description
  const selected = text.substring(start, end)
  form.description = text.substring(0, start) + before + selected + after + text.substring(end)
  el.focus()
}

function submit() {
  if (!form.domains.length) {
    alert('Veuillez cocher au moins un domaine informatique.')
    return
  }
  form.post('/espace-utilisateur/jobs')
}
</script>
