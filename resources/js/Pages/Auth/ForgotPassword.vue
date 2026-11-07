<template>
  <div>
    <Head title="Mot de passe oublié - Tech237" />

    <div class="min-vh-100 d-flex flex-row overflow-hidden bg-white">
      
      <!-- Left Column: Visual Brand Banner (50% on desktop, hidden on mobile) -->
      <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 text-white position-relative auth-left-banner" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%) !important;">
        <div class="position-relative z-2">
          <Link href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
            <img :src="'/' + (siteSettings.logo || 'assets/images/logo.png')" alt="Tech237 Logo" height="48">
          </Link>
        </div>

        <div class="position-relative z-2 my-auto py-5">
          <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 shadow-sm text-white" style="background-color: var(--tech-green);">
            RÉCUPÉRATION SÉCURISÉE
          </span>
          <h1 class="display-5 fw-bold mb-3 text-white">
            Sécurité &amp; Confidentialité
          </h1>
          <p class="lead text-white-50 mb-4" style="max-width: 520px; line-height: 1.6;">
            Récupérez l'accès à votre compte Tech237 en toute sécurité. Nous vous enverrons un lien à usage unique valable 10 minutes.
          </p>

          <div class="d-flex align-items-center gap-4 text-white-50 small">
            <div><i class="bi bi-shield-check text-success me-1"></i> Chiffrement TLS</div>
            <div><i class="bi bi-clock-history text-info me-1"></i> Validité 10 min</div>
            <div><i class="bi bi-key-fill text-warning me-1"></i> Accès sécurisé</div>
          </div>
        </div>

        <div class="position-relative z-2 text-white-50 small">
          © 2026 Tech237. Tous droits réservés.
        </div>
      </div>

      <!-- Right Column: Forgot Password Form with clean "Retour à l'accueil" button -->
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
              <i class="bi bi-shield-lock-fill fs-3"></i>
            </div>
            <h2 class="fw-bold text-dark mb-1">Mot de passe oublié ?</h2>
            <p class="text-muted small">Renseignez votre email ci-dessous. Nous vous transmettrons un lien de réinitialisation sécurisé.</p>
          </div>

          <!-- Flash Success Alert -->
          <div v-if="$page.props.flash?.success" class="alert alert-success rounded-4 small p-3 mb-4">
            <i class="bi bi-check-circle-fill me-1"></i> {{ $page.props.flash.success }}
            <div v-if="$page.props.flash?.demo_reset_url" class="mt-2 pt-2 border-top">
              <a :href="$page.props.flash.demo_reset_url" class="btn btn-sm btn-dark rounded-pill w-100 fw-bold">
                Ouvrir le lien de réinitialisation &rarr;
              </a>
            </div>
          </div>

          <!-- Flash Error Alert -->
          <div v-if="$page.props.flash?.error" class="alert alert-danger rounded-4 small p-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $page.props.flash.error }}
          </div>

          <form @submit.prevent="submit">
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">Adresse Email de votre compte <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input 
                  type="email" 
                  v-model="form.email" 
                  class="form-control border-start-0" 
                  placeholder="nom@exemple.com" 
                  required
                  autocomplete="email"
                >
              </div>
              <div v-if="form.errors.email" class="text-danger small mt-1">{{ form.errors.email }}</div>
            </div>

            <!-- Cooldown Notice -->
            <div v-if="cooldownSeconds > 0" class="alert alert-warning small rounded-3 p-2 mb-3 text-center">
              <i class="bi bi-hourglass-split me-1"></i> Patientez <strong>{{ cooldownSeconds }}s</strong> avant de renvoyer une nouvelle demande.
            </div>

            <button 
              type="submit" 
              :disabled="form.processing || cooldownSeconds > 0" 
              class="btn rounded-pill w-100 py-3 fw-bold text-white shadow-sm mb-4"
              style="background-color: var(--tech-green);"
            >
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-send-check me-1"></i> Envoyer le lien de réinitialisation
            </button>

            <div class="text-center pt-3 border-top">
              <Link href="/login" class="text-muted small text-decoration-none fw-semibold hover-success">
                <i class="bi bi-arrow-left me-1"></i> Retour à la connexion
              </Link>
            </div>
          </form>
        </div>

        <div class="text-center text-muted extra-small pt-3">
          Besoin d'aide ? Contactez l'assistance à <a href="mailto:support@tech237.cm" class="text-dark">support@tech237.cm</a>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const siteSettings = computed(() => page.props.siteSettings || {})

const form = useForm({
  email: '',
})

const cooldownSeconds = ref(0)
let timer = null

function submit() {
  form.post('/mot-de-passe-oublie', {
    onSuccess: () => {
      cooldownSeconds.value = 120
      clearInterval(timer)
      timer = setInterval(() => {
        if (cooldownSeconds.value > 0) {
          cooldownSeconds.value--
        } else {
          clearInterval(timer)
        }
      }, 1000)
    }
  })
}
</script>

<style scoped>
.hover-success:hover {
  color: var(--tech-green) !important;
}
.extra-small {
  font-size: 11px;
}
</style>
