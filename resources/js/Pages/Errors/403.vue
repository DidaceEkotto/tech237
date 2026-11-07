<template>
  <FrontLayout>
    <Head title="Accès refusé (403) - Tech237" />

    <div class="container my-5 py-5 text-center">
      <div class="mx-auto" style="max-width: 680px;">
        <div class="display-1 fw-bold mb-2 font-monospace" style="letter-spacing: -2px; color: #ffc107;">403</div>
        <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex p-4 fs-1 mb-3">
          <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h2 class="fw-bold text-dark mb-3">Accès restreint</h2>
        <p class="text-muted lead mb-4 fs-6">
          Vous n'avez pas les autorisations nécessaires pour accéder à cette ressource. Veuillez vous connecter avec le compte adéquat ou contacter l'administration.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
          <Link href="/" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: var(--tech-green, #00D18F);">
            <i class="bi bi-house-door-fill me-1"></i> Retour à l'accueil
          </Link>
          <Link href="/login" class="btn btn-outline-dark rounded-pill px-4 fw-semibold">
            <i class="bi bi-person-fill me-1"></i> Se connecter
          </Link>
        </div>

        <!-- Mode Débogage (actif uniquement si APP_DEBUG=true) -->
        <div v-if="debug" class="text-start mt-5 p-4 rounded-4 bg-light border shadow-sm">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill">
              <i class="bi bi-shield-exclamation me-1"></i> Débogage Autorisation (APP_DEBUG=true)
            </span>
          </div>

          <div v-if="message" class="alert alert-warning mb-2 font-monospace small">
            <strong>Raison :</strong> {{ message }}
          </div>

          <div v-if="file" class="small text-muted font-monospace">
            <strong>Fichier :</strong> {{ file }} <span v-if="line"><strong>:{{ line }}</strong></span>
          </div>
        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  status: { type: Number, default: 403 },
  message: { type: String, default: '' },
  debug: { type: Boolean, default: false },
  file: { type: String, default: null },
  line: { type: [Number, String], default: null },
  trace: { type: String, default: null },
})
</script>
