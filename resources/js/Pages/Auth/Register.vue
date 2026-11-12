<template>
  <div>
    <Head title="Inscription - Espace Membre Tech237" />

    <div class="min-vh-100 d-flex flex-row overflow-hidden bg-white">
      
      <!-- Left Column: Visual Brand Banner (50% on desktop, hidden on mobile) -->
      <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-between p-5 text-white position-relative auth-left-banner" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%) !important;">
        <div class="position-relative z-2">
          <Link href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
            <img :src="'/' + (siteSettings.logo || 'assets/images/logo.png')" alt="Tech237 Logo" height="48">
          </Link>
        </div>

        <div class="position-relative z-2 my-auto py-5">
          <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 shadow-sm text-white" style="background-color: var(--tech-green);">
            REJOIGNEZ LA COMMUNAUTÉ TECH237
          </span>
          <h1 class="display-6 fw-bold mb-3 text-white">
            Là où les idées deviennent des projets.
          </h1>
          <p class="lead text-white-50 mb-4 fs-6" style="line-height: 1.6;">
            Validez votre structure juridique (SARL, SA, SAS, ETS), publiez vos projets et participez aux échanges avec les développeurs et fondateurs du Cameroun.
          </p>

          <div class="p-3 rounded-3 bg-white bg-opacity-10 mb-4 border border-white border-opacity-10">
            <div class="fw-bold text-warning mb-1"><i class="bi bi-shield-lock-fill me-1"></i> Sécurité Tech237 :</div>
            <div class="small text-white-50">
              Votre mot de passe doit comporter au moins 8 caractères, une majuscule, un chiffre et un caractère spécial autorisé (_ - @ # { }).
            </div>
          </div>
        </div>

        <div class="position-relative z-2 text-white-50 small">
          © 2026 Tech237. Tous droits réservés.
        </div>
      </div>

      <!-- Right Column: Registration Form with "Retour à l'accueil" -->
      <div class="col-12 col-lg-7 d-flex flex-column justify-content-between p-4 p-md-5 bg-white min-vh-100 overflow-y-auto">
        
        <!-- Top: Return to Home button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <Link href="/" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour à l'accueil</span>
          </Link>

          <!-- Mobile Logo -->
          <div class="d-lg-none">
            <img :src="'/' + (siteSettings.logo || 'assets/images/logo.png')" alt="Tech237" height="38">
          </div>
        </div>

        <!-- Form Center Container -->
        <div class="mx-auto w-100 my-auto py-2" style="max-width: 620px;">
          <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Créer mon Compte Membre</h2>
            <p class="text-muted small">Remplissez le formulaire ci-dessous pour créer votre profil communautaire et porteur de projets.</p>
          </div>

          <form @submit.prevent="submit">
            
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Nom de famille <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="form.name" placeholder="ex: Kamga, Dupont..." required>
                <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Prénom <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="form.first_name" placeholder="ex: Paul, Jean..." required>
                <div v-if="form.errors.first_name" class="text-danger small mt-1">{{ form.errors.first_name }}</div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark">Sexe (Genre) <span class="text-danger">*</span></label>
                <select class="form-select" v-model="form.gender" required>
                  <option value="Homme">Homme</option>
                  <option value="Femme">Femme</option>
                  <option value="Autre">Autre</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark">Indicatif Pays <span class="text-danger">*</span></label>
                <select class="form-select" v-model="form.country_code" required>
                  <option value="+237">+237 (Cameroun)</option>
                  <option value="+33">+33 (France)</option>
                  <option value="+32">+32 (Belgique)</option>
                  <option value="+225">+225 (Côte d'Ivoire)</option>
                  <option value="+221">+221 (Sénégal)</option>
                  <option value="+1">+1 (Canada / USA)</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark">Téléphone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" v-model="form.phone" placeholder="690000000" required>
                <div v-if="form.errors.phone" class="text-danger small mt-1">{{ form.errors.phone }}</div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Adresse Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" v-model="form.email" placeholder="nom@exemple.com" required autocomplete="email">
                <div v-if="form.errors.email" class="text-danger small mt-1">{{ form.errors.email }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Date de naissance</label>
                <input type="date" class="form-control" v-model="form.birth_date">
                <small class="text-muted" style="font-size: 11px;">Permet d'actualiser votre âge automatiquement chaque année.</small>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Statut Matrimonial</label>
                <select class="form-select" v-model="form.marital_status">
                  <option value="Célibataire">Célibataire</option>
                  <option value="Marié(e)">Marié(e)</option>
                  <option value="Autre">Autre</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Ville de résidence <span class="text-danger">*</span></label>
                <CitySelect2 
                  v-model="form.city" 
                  placeholder="Sélectionnez votre ville..."
                  :required="true"
                />
                <div v-if="form.errors.city" class="text-danger small mt-1">{{ form.errors.city }}</div>
              </div>
            </div>

            <!-- Password with Show/Hide Toggle & Strict Validation Meter -->
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Mot de passe <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input 
                    :type="showPassword ? 'text' : 'password'" 
                    class="form-control" 
                    v-model="form.password" 
                    @input="checkPasswordStrength"
                    placeholder="••••••••" 
                    required
                    autocomplete="new-password"
                  >
                  <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword">
                    <i class="bi" :class="showPassword ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                  </button>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Confirmer mot de passe <span class="text-danger">*</span></label>
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  class="form-control" 
                  v-model="form.password_confirmation" 
                  placeholder="••••••••" 
                  required
                  autocomplete="new-password"
                >
              </div>
            </div>

            <!-- 3-Level Strength Meter Bar (Faible / Moyen / Fort) -->
            <div v-if="form.password" class="mb-3">
              <div class="progress" style="height: 6px;">
                <div 
                  class="progress-bar" 
                  role="progressbar" 
                  :style="{ width: strengthPercent + '%', backgroundColor: strengthColor }"
                ></div>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-1">
                <span class="small fw-bold" :style="{ color: strengthColor }">
                  Niveau : {{ strengthLabel }}
                </span>
                <span class="small text-muted" style="font-size: 11px;">
                  Min 8 car., 1 Majuscule, 1 Chiffre, 1 Spécial (_ - @ # { })
                </span>
              </div>
            </div>

            <!-- Real-time Checklist -->
            <div class="card bg-light border-0 p-3 mb-4 rounded-3 small">
              <div class="row g-2">
                <div class="col-sm-6" :class="checks.length ? 'text-success' : 'text-muted'">
                  <i class="bi" :class="checks.length ? 'bi-check-circle-fill' : 'bi-circle'"></i> Au moins 8 caractères
                </div>
                <div class="col-sm-6" :class="checks.upper ? 'text-success' : 'text-muted'">
                  <i class="bi" :class="checks.upper ? 'bi-check-circle-fill' : 'bi-circle'"></i> Une majuscule (A-Z)
                </div>
                <div class="col-sm-6" :class="checks.number ? 'text-success' : 'text-muted'">
                  <i class="bi" :class="checks.number ? 'bi-check-circle-fill' : 'bi-circle'"></i> Un chiffre (0-9)
                </div>
                <div class="col-sm-6" :class="checks.special ? 'text-success' : 'text-muted'">
                  <i class="bi" :class="checks.special ? 'bi-check-circle-fill' : 'bi-circle'"></i> Un symbole (_ - @ # { })
                </div>
                <div class="col-sm-12" :class="checks.noSpace ? 'text-success' : 'text-danger'">
                  <i class="bi" :class="checks.noSpace ? 'bi-check-circle-fill' : 'bi-x-circle'"></i> Aucun espace autorisé
                </div>
              </div>
            </div>

            <div v-if="form.errors.password" class="alert alert-danger py-2 small mb-3">
              {{ form.errors.password }}
            </div>

            <button 
              type="submit" 
              class="btn btn-danger w-100 py-3 fw-bold rounded-pill shadow-sm"
              :disabled="form.processing || !isPasswordValid"
            >
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
              <i class="bi bi-person-check-fill me-1"></i> Créer mon compte
            </button>

            <div class="text-center mt-3 text-muted small">
              Déjà un compte ? 
              <Link href="/login" class="text-danger fw-bold text-decoration-none ms-1">Se connecter</Link>
            </div>

          </form>
        </div>

        <div class="text-center text-muted small pt-3 border-top">
          En vous inscrivant, vous acceptez les <Link href="/conditions-generales" class="text-dark text-decoration-none fw-semibold">Conditions Générales</Link> et la <Link href="/politique-de-confidentialite" class="text-dark text-decoration-none fw-semibold">Politique de Confidentialité</Link>.
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Head, Link, usePage } from '@inertiajs/vue3'
import CitySelect2 from '@/Components/CitySelect2.vue'

const page = usePage()
const siteSettings = computed(() => page.props.siteSettings || {})

const showPassword = ref(false)

const form = useForm({
  name: '',
  first_name: '',
  gender: 'Homme',
  country_code: '+237',
  phone: '',
  email: '',
  birth_date: '',
  marital_status: 'Célibataire',
  city: 'Douala',
  password: '',
  password_confirmation: '',
})

// Password Strength Checks
const checks = ref({
  length: false,
  upper: false,
  number: false,
  special: false,
  noSpace: true,
})

function checkPasswordStrength() {
  const p = form.password || ''
  checks.value.length = p.length >= 8
  checks.value.upper = /[A-Z]/.test(p)
  checks.value.number = /[0-9]/.test(p)
  checks.value.special = /[_@#{\}\-]/.test(p)
  checks.value.noSpace = !/\s/.test(p)
}

const isPasswordValid = computed(() => {
  return checks.value.length && checks.value.upper && checks.value.number && checks.value.special && checks.value.noSpace
})

const strengthPercent = computed(() => {
  let score = 0
  if (checks.value.length) score += 25
  if (checks.value.upper) score += 25
  if (checks.value.number) score += 25
  if (checks.value.special) score += 25
  return score
})

const strengthLabel = computed(() => {
  if (strengthPercent.value <= 30) return 'Faible (Interdit)'
  if (strengthPercent.value < 100) return 'Moyen (Compléter critères)'
  return 'Sécurisé & Conforme'
})

const strengthColor = computed(() => {
  if (strengthPercent.value <= 30) return '#dc3545' // Red
  if (strengthPercent.value < 100) return '#fd7e14' // Orange
  return '#198754' // Green
})

function submit() {
  if (!isPasswordValid.value) {
    alert('Veuillez respecter tous les critères de sécurité du mot de passe.')
    return
  }
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<style scoped>
.auth-left-banner {
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(225, 29, 72, 0.85) 100%),
              url('/assets/images/auth-bg.jpg') center/cover no-repeat;
  background-color: #0f172a;
}
</style>
