<template>
  <div>
    <Head title="Connexion - Espace Membre Tech237" />

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
            ESPACE MEMBRES & INNOVATEURS
          </span>
          <h1 class="display-5 fw-bold mb-3 text-white">
            Là où les idées deviennent des projets.
          </h1>
          <p class="lead text-white-50 mb-4" style="max-width: 520px; line-height: 1.6;">
            Accédez à votre tableau de bord, gérez vos startups OHADA, publiez vos applications et partagez vos projets avec la communauté Tech237.
          </p>

          <div class="d-flex align-items-center gap-4 text-white-50 small">
            <div><i class="bi bi-shield-check text-success me-1"></i> Données chiffrées</div>
            <div><i class="bi bi-rocket-takeoff text-info me-1"></i> Réseau Startups 237</div>
            <div><i class="bi bi-briefcase-fill text-warning me-1"></i> Recrutement IT</div>
          </div>
        </div>

        <div class="position-relative z-2 text-white-50 small">
          © 2026 Tech237. Tous droits réservés.
        </div>
      </div>

      <!-- Right Column: Login Form with clean "Retour à l'accueil" button -->
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
            <h2 class="fw-bold text-dark mb-1">Bon retour parmi nous !</h2>
            <p class="text-muted small">Renseignez vos identifiants pour accéder à votre espace personnel.</p>
          </div>

          <form @submit.prevent="submit">
            
            <div class="mb-3">
              <label class="form-label small fw-bold text-dark">Adresse Email <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input 
                  type="email" 
                  class="form-control border-start-0" 
                  v-model="form.email" 
                  placeholder="nom@exemple.com" 
                  required 
                  autocomplete="email"
                >
              </div>
              <div v-if="form.errors.email" class="text-danger small mt-1">{{ form.errors.email }}</div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold text-dark">Mot de passe <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  class="form-control border-start-0 border-end-0" 
                  v-model="form.password" 
                  placeholder="••••••••" 
                  required
                  autocomplete="current-password"
                >
                <button type="button" class="btn btn-light border border-start-0" @click="showPassword = !showPassword">
                  <i class="bi" :class="showPassword ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                </button>
              </div>
              <div v-if="form.errors.password" class="text-danger small mt-1">{{ form.errors.password }}</div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe" v-model="form.remember">
                <label class="form-check-label small text-muted" for="rememberMe">Se souvenir de moi</label>
              </div>
              <div>
                <Link href="/mot-de-passe-oublie" class="small text-danger fw-semibold text-decoration-none">
                  Mot de passe oublié ?
                </Link>
              </div>
            </div>

            <button 
              type="submit" 
              class="btn btn-danger w-100 py-3 fw-bold rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2"
              :disabled="form.processing"
            >
              <span v-if="form.processing" class="spinner-border spinner-border-sm"></span>
              <span v-else><i class="bi bi-box-arrow-in-right"></i> Se connecter</span>
            </button>

            <div class="text-center mt-4 text-muted small">
              Vous n'avez pas encore de compte ? 
              <Link href="/register" class="text-danger fw-bold text-decoration-none ms-1">Créer un compte</Link>
            </div>

          </form>
        </div>

        <!-- Bottom Help/Copyright for right column -->
        <div class="text-center text-muted small pt-3 border-top">
          Besoin d'aide ? <Link href="/nous-soutenir" class="text-dark text-decoration-none fw-semibold">Contactez notre support</Link>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Head, Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const siteSettings = computed(() => page.props.siteSettings || {})

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
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
