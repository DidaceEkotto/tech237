<template>
  <FrontLayout>
    <Head :title="'Modifier ' + startup.name + ' - Tech237'" />

    <div class="container my-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          
          <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
              <li class="breadcrumb-item"><Link href="/espace-utilisateur">Tableau de bord</Link></li>
              <li class="breadcrumb-item"><Link href="/espace-utilisateur?section=startups">Mes Startups</Link></li>
              <li class="breadcrumb-item active">{{ startup.name }}</li>
            </ol>
          </nav>

          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
              <img 
                v-if="startup.logo" 
                :src="startup.logo.startsWith('/') ? startup.logo : '/' + startup.logo" 
                :alt="startup.name" 
                class="rounded-3 border object-fit-contain p-1 bg-white shadow-sm"
                style="width: 56px; height: 56px;"
              >
              <div v-else class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center fw-bold" style="width: 56px; height: 56px;">
                {{ startup.name.charAt(0) }}
              </div>
              <div>
                <h3 class="fw-bold mb-1">Modifier : {{ startup.name }}</h3>
                <span class="badge" :class="startup.status === 'approved' ? 'bg-success' : 'bg-warning text-dark'">
                  Statut : {{ startup.status === 'approved' ? 'Approuvée' : 'En examen' }}
                </span>
              </div>
            </div>

            <form @submit.prevent="submit">
              
              <div class="row g-3 mb-3">
                <div class="col-md-8">
                  <label class="form-label small fw-bold">Nom de la Startup <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.name" required>
                  <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Forme Juridique (OHADA) <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="form.legal_type" required>
                    <option value="SARL">SARL</option>
                    <option value="SA">SA</option>
                    <option value="SAS">SAS</option>
                    <option value="EURL">EURL</option>
                    <option value="ETS">ETS</option>
                    <option value="GIE">GIE</option>
                  </select>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Ville du Siège <span class="text-danger">*</span></label>
                  <CitySelect2 
                    v-model="form.city" 
                    placeholder="Sélectionnez une ville..."
                    :required="true"
                  />
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Quartier / Localisation</label>
                  <input type="text" class="form-control" v-model="form.location">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Nombre d'employés <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" v-model="form.employee_count" min="1" required>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Numéro RCCM</label>
                  <input type="text" class="form-control font-monospace" v-model="form.rccm_number">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Numéro d'Identifiant Unique (NIU)</label>
                  <input type="text" class="form-control font-monospace" v-model="form.niu_number">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Description / Activité <span class="text-danger">*</span></label>
                <WysiwygEditor 
                  v-model="form.description" 
                  minHeight="220px" 
                  placeholder="Présentez l'activité, les réalisations et la vision de votre startup..." 
                />
              </div>

              <div class="card p-3 bg-light border-0 rounded-3 mb-4">
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="needsFunding" v-model="form.needs_funding">
                  <label class="form-check-label fw-bold text-dark" for="needsFunding">
                    Recherche active de financements / levée de fonds
                  </label>
                </div>
                <div v-if="form.needs_funding" class="mt-2">
                  <label class="form-label small fw-bold">Montant recherché (FCFA)</label>
                  <input type="number" class="form-control" v-model="form.funding_amount">
                </div>
              </div>

              <!-- Logo & Banner with preview -->
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Logo de l'entreprise</label>
                  <div class="p-3 border-2 border-dashed rounded-4 text-center bg-light" style="border: 2px dashed #cbd5e1; cursor: pointer;" @click="$refs.logoInput.click()">
                    <input type="file" ref="logoInput" class="d-none" @change="onLogoChange" accept="image/*">
                    <div class="d-flex flex-column align-items-center">
                      <img :src="logoPreview || (startup.logo ? (startup.logo.startsWith('/') ? startup.logo : '/' + startup.logo) : '/assets/images/placeholder.jpg')" class="rounded-3 shadow-sm border mb-2 object-fit-contain p-1 bg-white" style="width: 70px; height: 70px;">
                      <span class="small text-muted">Cliquer pour changer le logo</span>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label small fw-bold">Bannière / Couverture</label>
                  <div class="p-3 border-2 border-dashed rounded-4 text-center bg-light" style="border: 2px dashed #cbd5e1; cursor: pointer;" @click="$refs.bannerInput.click()">
                    <input type="file" ref="bannerInput" class="d-none" @change="onBannerChange" accept="image/*">
                    <div class="d-flex flex-column align-items-center">
                      <img :src="bannerPreview || (startup.banner ? (startup.banner.startsWith('/') ? startup.banner : '/' + startup.banner) : '/assets/images/placeholder.jpg')" class="rounded-3 shadow-sm border mb-2 object-fit-cover w-100" style="height: 70px;">
                      <span class="small text-muted">Cliquer pour changer la bannière</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="d-flex gap-3 justify-content-end">
                <Link href="/espace-utilisateur?section=startups" class="btn btn-light border rounded-pill px-4">
                  Annuler
                </Link>
                <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" :disabled="form.processing">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  Enregistrer les modifications
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
import CitySelect2 from '@/Components/CitySelect2.vue'

const props = defineProps({
  startup: { type: Object, required: true },
})

const logoPreview = ref(null)
const bannerPreview = ref(null)

const form = useForm({
  name: props.startup.name || '',
  legal_type: props.startup.legal_type || 'SARL',
  city: props.startup.city || 'Douala',
  location: props.startup.location || '',
  employee_count: props.startup.employee_count || 1,
  needs_funding: Boolean(props.startup.needs_funding),
  funding_amount: props.startup.funding_amount || null,
  rccm_number: props.startup.rccm_number || '',
  niu_number: props.startup.niu_number || '',
  description: props.startup.description || '',
  logo: null,
  banner: null,
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
  form.post(`/espace-utilisateur/startups/${props.startup.id}`)
}
</script>
