<template>
  <FrontLayout>
    <Head title="Nouveau Projet Tech - Tech237" />

    <div class="container my-5">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          
          <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
              <li class="breadcrumb-item"><Link href="/espace-utilisateur">Tableau de bord</Link></li>
              <li class="breadcrumb-item"><Link href="/espace-utilisateur?section=projects">Mes Projets</Link></li>
              <li class="breadcrumb-item active">Nouveau Projet</li>
            </ol>
          </nav>

          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
              <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                <i class="bi bi-rocket-takeoff-fill fs-3"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-1">Publier un Nouveau Projet Tech</h3>
                <p class="text-muted small mb-0">Partagez votre application, site web ou jeu vidéo avec la communauté et les investisseurs.</p>
              </div>
            </div>

            <!-- STEP WIZARD PROGRESS BAR -->
            <div class="d-flex justify-content-between align-items-center mb-5 position-relative">
              <div class="position-absolute top-50 start-0 w-100 translate-middle-y bg-light" style="height: 4px; z-index: 1;"></div>
              <div class="position-absolute top-50 start-0 translate-middle-y bg-danger transition-all" :style="{ width: step === 1 ? '0%' : (step === 2 ? '50%' : '100%'), height: '4px', zIndex: 2 }"></div>
              
              <button type="button" @click="goToStep(1)" class="btn rounded-circle fw-bold position-relative shadow-sm" :class="step >= 1 ? 'btn-danger text-white' : 'btn-light border'" style="width: 44px; height: 44px; z-index: 3;">
                1
              </button>
              <button type="button" @click="goToStep(2)" class="btn rounded-circle fw-bold position-relative shadow-sm" :class="step >= 2 ? 'btn-danger text-white' : 'btn-light border'" style="width: 44px; height: 44px; z-index: 3;">
                2
              </button>
              <button type="button" @click="goToStep(3)" class="btn rounded-circle fw-bold position-relative shadow-sm" :class="step >= 3 ? 'btn-danger text-white' : 'btn-light border'" style="width: 44px; height: 44px; z-index: 3;">
                3
              </button>
            </div>

            <form @submit.prevent="submit">

              <!-- ÉTAPE 1 : INFOS GÉNÉRALES -->
              <div v-show="step === 1">
                <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-info-circle text-danger me-2"></i>Étape 1 : Informations Générales</h5>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-7">
                    <label class="form-label small fw-bold">Nom du Projet / Produit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" v-model="form.name" placeholder="ex: Tech237 Mobile, PayQuick..." required>
                    <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
                  </div>
                  <div class="col-md-5">
                    <label class="form-label small fw-bold">Rattacher à une Startup</label>
                    <select class="form-select" v-model="form.startup_id">
                      <option :value="null">-- Indépendant (Aucune) --</option>
                      <option v-for="st in startups" :key="st.id" :value="st.id">
                        {{ st.name }} ({{ st.legal_type }})
                      </option>
                    </select>
                  </div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Type de Projet <span class="text-danger">*</span></label>
                    <select class="form-select" v-model="form.project_type" required>
                      <option value="web">Application Web / Plateforme SaaS</option>
                      <option value="mobile">Application Mobile (iOS / Android)</option>
                      <option value="desktop">Logiciel Desktop (Windows / Mac / Linux)</option>
                      <option value="game">Jeu Vidéo (PC / Mobile / Console)</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Durée approximative de développement</label>
                    <input type="text" class="form-control" v-model="form.development_duration" placeholder="ex: 3 mois, 6 mois, 1 an...">
                  </div>
                </div>

                <div class="mb-4">
                  <label class="form-label small fw-bold">Description complète du projet & Vision <span class="text-danger">*</span></label>
                  <p class="text-muted extra-small mb-2">Utilisez les outils de mise en forme Word, tableaux, listes, images ou intégrez directement une vidéo YouTube explicative.</p>
                  <WysiwygEditor v-model="form.description" minHeight="320px" placeholder="Expliquez la proposition de valeur, les fonctionnalités, le problème résolu et la vision..." />
                  <div v-if="form.errors.description" class="text-danger small mt-1">{{ form.errors.description }}</div>
                </div>

                <div class="d-flex justify-content-end">
                  <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" @click="nextStep">
                    Étape suivante &rarr;
                  </button>
                </div>
              </div>

              <!-- ÉTAPE 2 : STACK TECHNIQUE & FONCTIONNALITÉS CLÉS -->
              <div v-show="step === 2">
                <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-stack text-danger me-2"></i>Étape 2 : Stack Technique & Fonctionnalités</h5>

                <!-- Languages Database Multi-Select & Custom Entry -->
                <div class="mb-4">
                  <label class="form-label small fw-bold">Langages de Programmation</label>
                  <div v-if="programmingLanguages.length" class="row g-2 mb-2">
                    <div v-for="lang in programmingLanguages" :key="lang.id" class="col-sm-6 col-md-4">
                      <div class="form-check p-2 bg-light rounded-3 border">
                        <input class="form-check-input ms-1" type="checkbox" :id="'lang-' + lang.id" :value="lang.id" v-model="form.language_ids">
                        <label class="form-check-label small fw-semibold text-dark ms-2" :for="'lang-' + lang.id">
                          {{ lang.name }}
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="mt-2">
                    <label class="form-label extra-small text-muted mb-1"><i class="bi bi-pencil-square me-1"></i>Saisir librement d'autres langages (séparés par des virgules) :</label>
                    <input type="text" class="form-control" v-model="form.custom_languages" placeholder="ex: Python, TypeScript, Rust, Go, Dart, PHP, Java..." />
                  </div>
                </div>

                <!-- Frameworks Database Multi-Select & Custom Entry -->
                <div class="mb-4">
                  <label class="form-label small fw-bold">Frameworks, Bibliothèques & Moteurs</label>
                  <div v-if="frameworks.length" class="row g-2 mb-2">
                    <div v-for="fw in frameworks" :key="fw.id" class="col-sm-6 col-md-4">
                      <div class="form-check p-2 bg-light rounded-3 border">
                        <input class="form-check-input ms-1" type="checkbox" :id="'fw-' + fw.id" :value="fw.id" v-model="form.framework_ids">
                        <label class="form-check-label small fw-semibold text-dark ms-2" :for="'fw-' + fw.id">
                          {{ fw.name }}
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="mt-2">
                    <label class="form-label extra-small text-muted mb-1"><i class="bi bi-pencil-square me-1"></i>Saisir librement d'autres frameworks ou outils (séparés par des virgules) :</label>
                    <input type="text" class="form-control" v-model="form.custom_frameworks" placeholder="ex: Laravel, Vue.js 3, Flutter, React Native, Spring Boot, Docker..." />
                  </div>
                </div>

                <!-- Dynamic Features List (User Requirement: (+) Ajouter / (-) Supprimer) -->
                <div class="mb-4 p-3 bg-light rounded-4 border">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label small fw-bold mb-0 text-dark">
                      <i class="bi bi-list-check text-danger me-1"></i>Fonctionnalités Clés du Projet <span class="text-danger">*</span>
                    </label>
                    <button type="button" @click="addFeature" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
                      <i class="bi bi-plus-lg me-1"></i> Ajouter une fonctionnalité
                    </button>
                  </div>

                  <div v-for="(feat, idx) in form.features" :key="idx" class="d-flex gap-2 mb-2 align-items-center">
                    <span class="badge bg-danger rounded-circle text-white" style="width: 24px; height: 24px; line-height: 18px;">{{ idx + 1 }}</span>
                    <input type="text" class="form-control" v-model="form.features[idx]" placeholder="ex: Authentification biométrique, Paiement Mobile Money, Mode hors ligne..." required>
                    <button v-if="form.features.length > 1" type="button" @click="removeFeature(idx)" class="btn btn-outline-secondary btn-sm rounded-circle" title="Supprimer">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>

                <div class="d-flex justify-content-between">
                  <button type="button" class="btn btn-light border rounded-pill px-4" @click="step = 1">
                    &larr; Précédent
                  </button>
                  <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" @click="nextStep">
                    Étape suivante &rarr;
                  </button>
                </div>
              </div>

              <!-- ÉTAPE 3 : MÉDIAS, VIDÉO DE PRÉSENTATION & LIENS STORES -->
              <div v-show="step === 3">
                <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-globe2 text-danger me-2"></i>Étape 3 : Médias & Liens de Déploiement</h5>

                <!-- Dropzone Style Logo Upload with Preview -->
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Icône / Logo du projet <span class="text-danger">*</span></label>
                    <div class="p-3 border-2 border-dashed rounded-4 text-center bg-light" style="border: 2px dashed #cbd5e1; cursor: pointer;" @click="$refs.logoInput.click()">
                      <input type="file" ref="logoInput" class="d-none" @change="onLogoChange" accept="image/*">
                      <div v-if="logoPreview" class="d-flex flex-column align-items-center">
                        <img :src="logoPreview" class="rounded-3 shadow-sm border mb-2 object-fit-contain p-1 bg-white" style="width: 80px; height: 80px;">
                        <span class="small text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Logo choisi</span>
                      </div>
                      <div v-else class="py-2 text-muted">
                        <i class="bi bi-image text-danger fs-3 d-block mb-1"></i>
                        <span class="small fw-semibold text-dark">Sélectionner le logo carré</span>
                      </div>
                    </div>
                  </div>

                  <!-- Dropzone Banner Upload with Preview -->
                  <div class="col-md-6">
                    <label class="form-label small fw-bold">Bannière / Couverture (Optionnel)</label>
                    <div class="p-3 border-2 border-dashed rounded-4 text-center bg-light" style="border: 2px dashed #cbd5e1; cursor: pointer;" @click="$refs.bannerInput.click()">
                      <input type="file" ref="bannerInput" class="d-none" @change="onBannerChange" accept="image/*">
                      <div v-if="bannerPreview" class="d-flex flex-column align-items-center">
                        <img :src="bannerPreview" class="rounded-3 shadow-sm border mb-2 object-fit-cover w-100" style="height: 80px;">
                        <span class="small text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Bannière choisie</span>
                      </div>
                      <div v-else class="py-2 text-muted">
                        <i class="bi bi-card-image text-secondary fs-3 d-block mb-1"></i>
                        <span class="small fw-semibold text-dark">Sélectionner la couverture</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Video and Store Links -->
                <div class="card p-4 bg-light border-0 rounded-4 mb-4">
                  <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-youtube text-danger me-2"></i>Vidéo & Téléchargements</h6>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-12">
                      <label class="form-label small fw-bold">Vidéo de présentation YouTube (URL complète)</label>
                      <input type="url" class="form-control" v-model="form.youtube_presentation_url" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Lien Huawei AppGallery</label>
                      <input type="url" class="form-control" v-model="form.appgallery_url" placeholder="https://appgallery.huawei.com/app/...">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Lien Google Play Store</label>
                      <input type="url" class="form-control" v-model="form.playstore_url" placeholder="https://play.google.com/store/apps/...">
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Lien Apple App Store</label>
                      <input type="url" class="form-control" v-model="form.appstore_url" placeholder="https://apps.apple.com/app/...">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Site Web / Démo en ligne</label>
                      <input type="url" class="form-control" v-model="form.website_url" placeholder="https://monprojet.cm">
                    </div>
                    <div class="col-md-12">
                      <label class="form-label small fw-bold">Dépôt GitHub / GitLab (Optionnel)</label>
                      <input type="url" class="form-control" v-model="form.repo_url" placeholder="https://github.com/mon-compte/mon-projet">
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-between">
                  <button type="button" class="btn btn-light border rounded-pill px-4" @click="step = 2">
                    &larr; Précédent
                  </button>
                  <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" :disabled="form.processing">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-check2-circle me-1"></i> Publier le projet
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
import { useForm, Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import WysiwygEditor from '@/Components/WysiwygEditor.vue'

const props = defineProps({
  startups: { type: Array, default: () => [] },
  programmingLanguages: { type: Array, default: () => [] },
  frameworks: { type: Array, default: () => [] },
})

const step = ref(1)
const logoPreview = ref(null)
const bannerPreview = ref(null)

const form = useForm({
  name: '',
  startup_id: null,
  project_type: 'web',
  development_duration: '',
  description: '',
  language_ids: [],
  custom_languages: '',
  framework_ids: [],
  custom_frameworks: '',
  features: ['Fonctionnalité principale'],
  youtube_presentation_url: '',
  appgallery_url: '',
  website_url: '',
  playstore_url: '',
  appstore_url: '',
  repo_url: '',
  logo: null,
  banner: null,
})

function goToStep(s) {
  if (s > step.value && !validateCurrentStep()) return
  step.value = s
}

function nextStep() {
  if (validateCurrentStep()) {
    step.value++
  }
}

function validateCurrentStep() {
  if (step.value === 1) {
    if (!form.name || !form.description) {
      alert('Veuillez renseigner le nom et la description du projet.')
      return false
    }
  }
  return true
}

function addFeature() {
  form.features.push('')
}

function removeFeature(index) {
  if (form.features.length > 1) {
    form.features.splice(index, 1)
  }
}

function onLogoChange(e) {
  const file = e.target.files[0]
  if (file) {
    form.logo = file
    logoPreview.value = URL.createObjectURL(file)
  }
}

function onBannerChange(e) {
  const file = e.target.files[0]
  if (file) {
    form.banner = file
    bannerPreview.value = URL.createObjectURL(file)
  }
}

function submit() {
  form.features = form.features.filter(f => f && f.trim().length > 0)
  if (form.features.length === 0) {
    form.features = ['Fonctionnalité principale']
  }
  form.post('/espace-utilisateur/projets')
}
</script>
