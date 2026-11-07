<template>
  <FrontLayout>
    <Head title="Enregistrer une Startup (OHADA) - Tech237" />

    <div class="container my-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          
          <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
              <li class="breadcrumb-item"><Link href="/espace-utilisateur">Tableau de bord</Link></li>
              <li class="breadcrumb-item"><Link href="/espace-utilisateur?section=startups">Mes Startups</Link></li>
              <li class="breadcrumb-item active">Nouvelle Startup</li>
            </ol>
          </nav>

          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
              <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                <i class="bi bi-building-add fs-3"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-1">Enregistrer une Nouvelle Startup</h3>
                <p class="text-muted small mb-0">Conforme au droit camerounais et au traité OHADA. Validation sous 2 jours ouvrés.</p>
              </div>
            </div>

            <form @submit.prevent="submit">
              
              <div class="row g-3 mb-3">
                <div class="col-md-8">
                  <label class="form-label small fw-bold">Nom de la Startup / Entreprise <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.name" placeholder="ex: Kemit Digital Labs" required>
                  <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Forme Juridique (OHADA) <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="form.legal_type" required>
                    <option value="SARL">SARL (Société à Resp. Limitée)</option>
                    <option value="SA">SA (Société Anonyme)</option>
                    <option value="SAS">SAS (Société par Actions Simplifiée)</option>
                    <option value="EURL">EURL (Entreprise Unipersonnelle)</option>
                    <option value="ETS">ETS (Établissement Indépendant)</option>
                    <option value="GIE">GIE (Groupement d'Intérêt Éco.)</option>
                  </select>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Ville du Siège <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.city" placeholder="Douala, Yaoundé..." required>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Quartier / Adresse précise</label>
                  <input type="text" class="form-control" v-model="form.location" placeholder="Akwa, Bonanjo, Bastos...">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Nombre d'employés <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" v-model="form.employee_count" min="1" required>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Numéro RCCM (Registre du Commerce)</label>
                  <input type="text" class="form-control font-monospace" v-model="form.rccm_number" placeholder="RC/DLA/2026/B/...">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Numéro d'Identifiant Unique (NIU)</label>
                  <input type="text" class="form-control font-monospace" v-model="form.niu_number" placeholder="M0123456789X">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Description de l'activité & Proposition de valeur <span class="text-danger">*</span></label>
                <WysiwygEditor 
                  v-model="form.description" 
                  minHeight="220px" 
                  placeholder="Présentez le problème résolu, votre modèle économique, vos technologies et votre vision..." 
                />
                <div v-if="form.errors.description" class="text-danger small mt-1">{{ form.errors.description }}</div>
              </div>

              <!-- Funding Request Switch -->
              <div class="card p-3 bg-light border-0 rounded-3 mb-4">
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="needsFunding" v-model="form.needs_funding">
                  <label class="form-check-label fw-bold text-dark" for="needsFunding">
                    Cette startup est actuellement en recherche de financements / levée de fonds
                  </label>
                </div>
                <div v-if="form.needs_funding" class="mt-2">
                  <label class="form-label small fw-bold">Montant recherché (FCFA)</label>
                  <input type="number" class="form-control" v-model="form.funding_amount" placeholder="ex: 15000000">
                </div>
              </div>

              <!-- Media & Legal Documents Upload -->
              <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Identité Visuelle & Pièces Légales</h5>

              <!-- EXPLICIT REQUIRED LOGO NOTICE (USER REQUIREMENT) -->
              <div class="alert alert-warning py-2 px-3 small border-0 rounded-3 mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill text-warning fs-5"></i>
                <span><strong>Attention :</strong> Le logo officiel de la startup est <u>obligatoire</u> pour l'authentification et l'affichage sur la plateforme.</span>
              </div>
              
              <!-- DROPZONE LOGO WITH IMAGE PREVIEW -->
              <div class="mb-4">
                <label class="form-label small fw-bold">Logo de l'entreprise <span class="text-danger">*</span></label>
                <div class="p-3 border-2 border-dashed rounded-4 text-center bg-light position-relative" style="border: 2px dashed #cbd5e1; cursor: pointer;" @click="$refs.logoInput.click()">
                  <input type="file" ref="logoInput" class="d-none" @change="onLogoChange" accept="image/*" required>
                  <div v-if="logoPreview" class="d-flex flex-column align-items-center">
                    <img :src="logoPreview" class="rounded-3 shadow-sm border mb-2 object-fit-contain p-1 bg-white" style="width: 90px; height: 90px;">
                    <span class="small text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Logo sélectionné (cliquer pour modifier)</span>
                  </div>
                  <div v-else class="py-3 text-muted">
                    <i class="bi bi-cloud-arrow-up text-danger fs-2 d-block mb-1"></i>
                    <span class="fw-semibold text-dark d-block">Cliquez ou déposez votre logo ici</span>
                    <span class="small text-muted">Format PNG, JPG ou WEBP (Max: 2Mo)</span>
                  </div>
                </div>
                <div v-if="form.errors.logo" class="text-danger small mt-1">{{ form.errors.logo }}</div>
              </div>

              <!-- DROPZONE BANNER WITH PREVIEW -->
              <div class="mb-4">
                <label class="form-label small fw-bold">Bannière / Couverture (Optionnel)</label>
                <div class="p-3 border-2 border-dashed rounded-4 text-center bg-light position-relative" style="border: 2px dashed #cbd5e1; cursor: pointer;" @click="$refs.bannerInput.click()">
                  <input type="file" ref="bannerInput" class="d-none" @change="onBannerChange" accept="image/*">
                  <div v-if="bannerPreview" class="d-flex flex-column align-items-center">
                    <img :src="bannerPreview" class="rounded-3 shadow-sm border mb-2 object-fit-cover w-100" style="max-height: 140px;">
                    <span class="small text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Bannière prête</span>
                  </div>
                  <div v-else class="py-2 text-muted">
                    <i class="bi bi-card-image text-secondary fs-3 d-block mb-1"></i>
                    <span class="small fw-semibold text-dark">Sélectionner une image de couverture (1200x400 recommandé)</span>
                  </div>
                </div>
              </div>

              <!-- Legal Documents -->
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">CNI du Fondateur (Recto)</label>
                  <input type="file" class="form-control form-control-sm" @change="e => form.cni_recto = e.target.files[0]" accept="image/*,application/pdf">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">CNI du Fondateur (Verso)</label>
                  <input type="file" class="form-control form-control-sm" @change="e => form.cni_verso = e.target.files[0]" accept="image/*,application/pdf">
                </div>
                <div class="col-md-12">
                  <label class="form-label small fw-bold">Certificat RCCM ou Statuts Notariés (PDF ou image)</label>
                  <input type="file" class="form-control form-control-sm" @change="e => form.rccm_doc = e.target.files[0]" accept="image/*,application/pdf">
                </div>
              </div>

              <div class="d-flex gap-3 justify-content-end">
                <Link href="/espace-utilisateur?section=startups" class="btn btn-light border rounded-pill px-4">
                  Annuler
                </Link>
                <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" :disabled="form.processing">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  Soumettre pour validation
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
import WysiwygEditor from '@/Components/WysiwygEditor.vue'

const logoPreview = ref(null)
const bannerPreview = ref(null)

const form = useForm({
  name: '',
  legal_type: 'SARL',
  city: 'Douala',
  location: '',
  employee_count: 1,
  needs_funding: false,
  funding_amount: null,
  rccm_number: '',
  niu_number: '',
  description: '',
  logo: null,
  banner: null,
  cni_recto: null,
  cni_verso: null,
  rccm_doc: null,
})

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
  if (!form.logo) {
    alert('Le logo de la startup est obligatoire.')
    return
  }
  form.post('/espace-utilisateur/startup')
}
</script>
