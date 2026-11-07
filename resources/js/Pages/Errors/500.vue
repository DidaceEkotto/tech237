<template>
  <FrontLayout>
    <Head title="Erreur serveur (500) - Tech237" />

    <div class="container my-5 py-5 text-center">
      <div class="mx-auto" style="max-width: 680px;">
        <div class="display-1 fw-bold mb-2 font-monospace" style="letter-spacing: -2px; color: #dc3545;">500</div>
        <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex p-4 fs-1 mb-3">
          <i class="bi bi-cpu-fill"></i>
        </div>
        <h2 class="fw-bold text-dark mb-3">Une erreur inattendue est survenue</h2>
        <p class="text-muted lead mb-4 fs-6">
          Nos serveurs ont rencontré un problème temporaire. L'équipe technique a été notifiée afin de rétablir le service au plus vite.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
          <Link href="/" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: var(--tech-green, #00D18F);">
            <i class="bi bi-arrow-clockwise me-1"></i> Recharger l'accueil
          </Link>
          <a href="javascript:history.back()" class="btn btn-outline-dark rounded-pill px-4 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Page précédente
          </a>
        </div>

        <!-- Mode Débogage (actif uniquement si APP_DEBUG=true) -->
        <div v-if="debug" class="text-start mt-5 p-4 rounded-4 bg-light border shadow-sm">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> Débogage Développeur (APP_DEBUG=true)
            </span>
            <button 
              @click="showTrace = !showTrace" 
              class="btn btn-sm btn-outline-secondary rounded-pill px-3"
            >
              {{ showTrace ? 'Masquer la trace' : 'Afficher la trace complète' }}
            </button>
          </div>

          <div v-if="message" class="alert alert-danger mb-3 font-monospace small">
            <strong>Exception Message :</strong> {{ message }}
          </div>

          <div v-if="file" class="small text-muted mb-2 font-monospace">
            <strong>Emplacement :</strong> {{ file }} <span v-if="line"><strong>:{{ line }}</strong></span>
          </div>

          <div v-if="showTrace && trace" class="mt-3">
            <h6 class="fw-bold small text-muted text-uppercase">Stack Trace :</h6>
            <pre class="bg-dark text-light p-3 rounded-3 small font-monospace overflow-auto" style="max-height: 350px; font-size: 11px;">{{ trace }}</pre>
          </div>
        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  status: { type: Number, default: 500 },
  message: { type: String, default: '' },
  debug: { type: Boolean, default: false },
  file: { type: String, default: null },
  line: { type: [Number, String], default: null },
  trace: { type: String, default: null },
})

const showTrace = ref(true) // default open on 500 debug
</script>
