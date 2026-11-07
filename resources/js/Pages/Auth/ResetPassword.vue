<template>
  <div>
    <Head title="Nouveau mot de passe - Tech237" />

    <div class="min-vh-100 d-flex flex-row overflow-hidden bg-white">
      
      <!-- Left Column: Visual Brand Banner -->
      <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 text-white position-relative auth-left-banner" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%) !important;">
        <div class="position-relative z-2">
          <Link href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
            <img :src="'/' + (siteSettings.logo || 'assets/images/logo.png')" alt="Tech237 Logo" height="48">
          </Link>
        </div>

        <div class="position-relative z-2 my-auto py-5">
          <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 shadow-sm text-white" style="background-color: var(--tech-green);">
            RÉINITIALISATION DE SÉCURITÉ
          </span>
          <h1 class="display-5 fw-bold mb-3 text-white">
            Protégez vos Projets &amp; Données
          </h1>
          <p class="lead text-white-50 mb-4" style="max-width: 520px; line-height: 1.6;">
            Définissez un nouveau mot de passe fort pour reprendre le contrôle de votre espace créateur, de vos startups et projets technologiques.
          </p>

          <div class="d-flex align-items-center gap-4 text-white-50 small">
            <div><i class="bi bi-shield-check text-success me-1"></i> Chiffrement BCrypt</div>
            <div><i class="bi bi-lock-fill text-info me-1"></i> Session sécurisée</div>
            <div><i class="bi bi-check2-all text-warning me-1"></i> Contrôle en temps réel</div>
          </div>
        </div>

        <div class="position-relative z-2 text-white-50 small">
          © 2026 Tech237. Tous droits réservés.
        </div>
      </div>

      <!-- Right Column: Reset Password Form -->
      <div class="col-12 col-lg-6 d-flex flex-column justify-content-between p-4 p-md-5 bg-white min-vh-100 overflow-y-auto">
        
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
        <div class="mx-auto w-100 my-auto py-4" style="max-width: 440px;">
          <div class="mb-4">
            <div class="rounded-circle d-inline-flex p-3 mb-3 text-white" style="background-color: var(--tech-green); width: 56px; height: 56px; align-items: center; justify-content: center;">
              <i class="bi bi-key-fill fs-3"></i>
            </div>
            <h2 class="fw-bold text-dark mb-1">Nouveau mot de passe</h2>
            <p class="text-muted small">Choisissez un mot de passe robuste pour votre compte <strong class="text-dark">{{ email }}</strong>.</p>
          </div>

          <div v-if="$page.props.flash?.error" class="alert alert-danger rounded-4 small p-3 mb-3">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $page.props.flash.error }}
          </div>

          <form @submit.prevent="submit">
            <input type="hidden" v-model="form.token">
            <input type="hidden" v-model="form.email">

            <!-- New Password -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-dark">Nouveau mot de passe <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  v-model="form.password" 
                  class="form-control border-start-0 border-end-0" 
                  placeholder="Minimum 8 caractères..." 
                  required
                >
                <button type="button" @click="showPassword = !showPassword" class="btn btn-light border border-start-0">
                  <i :class="showPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'"></i>
                </button>
              </div>
              <div v-if="form.errors.password" class="text-danger small mt-1">{{ form.errors.password }}</div>
            </div>

            <!-- Strength Meter -->
            <div class="mb-3">
              <div class="d-flex justify-content-between small fw-bold mb-1">
                <span class="text-muted extra-small">Force du mot de passe :</span>
                <span :style="{ color: strengthColor, fontSize: '11px' }">{{ strengthLabel }}</span>
              </div>
              <div class="progress" style="height: 6px;">
                <div class="progress-bar transition" role="progressbar" :style="{ width: strengthPercent + '%', backgroundColor: strengthColor }"></div>
              </div>
            </div>

            <!-- Real-Time Password Checklist -->
            <div class="card bg-light border-0 p-3 rounded-4 mb-3">
              <small class="fw-bold text-dark d-block mb-2 extra-small">Critères de sécurité recommandés :</small>
              <ul class="list-unstyled mb-0 extra-small">
                <li :class="ruleLength ? 'text-success fw-bold' : 'text-muted'">
                  <i :class="ruleLength ? 'bi bi-check-circle-fill text-success' : 'bi bi-circle text-muted'" class="me-1"></i>
                  Au moins 8 caractères
                </li>
                <li :class="ruleUpper ? 'text-success fw-bold' : 'text-muted'">
                  <i :class="ruleUpper ? 'bi bi-check-circle-fill text-success' : 'bi bi-circle text-muted'" class="me-1"></i>
                  Au moins 1 majuscule (A-Z)
                </li>
                <li :class="ruleNumber ? 'text-success fw-bold' : 'text-muted'">
                  <i :class="ruleNumber ? 'bi bi-check-circle-fill text-success' : 'bi bi-circle text-muted'" class="me-1"></i>
                  Au moins 1 chiffre (0-9)
                </li>
                <li :class="ruleSpecial ? 'text-success fw-bold' : 'text-muted'">
                  <i :class="ruleSpecial ? 'bi bi-check-circle-fill text-success' : 'bi bi-circle text-muted'" class="me-1"></i>
                  Caractère spécial (_ @ # - { })
                </li>
                <li :class="ruleNoSpaces ? 'text-success fw-bold' : 'text-danger'">
                  <i :class="ruleNoSpaces ? 'bi bi-check-circle-fill text-success' : 'bi bi-x-circle text-danger'" class="me-1"></i>
                  Aucun espace vide autorisé
                </li>
              </ul>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">Confirmez le nouveau mot de passe <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  v-model="form.password_confirmation" 
                  class="form-control border-start-0" 
                  placeholder="Retapez le mot de passe..." 
                  required
                >
              </div>
              <div v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation" class="text-danger small mt-1">
                Les deux mots de passe ne correspondent pas.
              </div>
            </div>

            <button 
              type="submit" 
              :disabled="form.processing || !isFormValid" 
              class="btn rounded-pill w-100 py-3 fw-bold text-white shadow-sm mb-4"
              style="background-color: var(--tech-green);"
            >
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-check-circle-fill me-1"></i> Mettre à jour mon mot de passe
            </button>

            <div class="text-center pt-3 border-top">
              <Link href="/login" class="text-muted small text-decoration-none fw-semibold hover-success">
                <i class="bi bi-arrow-left me-1"></i> Revenir à la page de connexion
              </Link>
            </div>
          </form>
        </div>

        <div class="text-center text-muted extra-small pt-3">
          Tech237 &bull; Plateforme d'innovation technologique camerounaise
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
  token: String,
  email: String,
})

const page = usePage()
const siteSettings = computed(() => page.props.siteSettings || {})

const showPassword = ref(false)

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

// Checklist Rules
const ruleLength = computed(() => form.password.length >= 8)
const ruleUpper = computed(() => /[A-Z]/.test(form.password))
const ruleNumber = computed(() => /[0-9]/.test(form.password))
const ruleSpecial = computed(() => /[_@#\-{}]/.test(form.password))
const ruleNoSpaces = computed(() => form.password.length === 0 || !/\s/.test(form.password))

// Strength Meter
const strengthScore = computed(() => {
  let score = 0
  if (ruleLength.value) score++
  if (ruleUpper.value) score++
  if (ruleNumber.value) score++
  if (ruleSpecial.value) score++
  if (ruleNoSpaces.value && form.password.length > 0) score++
  return score
})

const strengthLabel = computed(() => {
  if (form.password.length === 0) return 'Non renseigné'
  if (strengthScore.value <= 2) return 'Faible'
  if (strengthScore.value <= 4) return 'Moyen'
  return 'Fort (Recommandé)'
})

const strengthColor = computed(() => {
  if (form.password.length === 0) return '#cbd5e1'
  if (strengthScore.value <= 2) return '#ef4444'
  if (strengthScore.value <= 4) return '#b45309'
  return '#00A859'
})

const strengthPercent = computed(() => {
  if (form.password.length === 0) return 0
  if (strengthScore.value <= 2) return 33
  if (strengthScore.value <= 4) return 66
  return 100
})

const isFormValid = computed(() => {
  return ruleLength.value &&
    ruleUpper.value &&
    ruleNumber.value &&
    ruleSpecial.value &&
    ruleNoSpaces.value &&
    form.password === form.password_confirmation
})

function submit() {
  form.post('/reinitialiser-mot-de-passe')
}
</script>

<style scoped>
.transition {
  transition: all 0.3s ease;
}
.hover-success:hover {
  color: var(--tech-green) !important;
}
.extra-small {
  font-size: 11px;
}
</style>
