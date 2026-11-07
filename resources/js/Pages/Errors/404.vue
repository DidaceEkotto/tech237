<template>
  <FrontLayout>
    <Head title="Page introuvable (404) - Tech237" />

    <div class="container my-5 py-5 text-center">
      <div class="mx-auto" style="max-width: 680px;">
        <div class="display-1 fw-bold mb-2 font-monospace" style="letter-spacing: -2px; color: var(--tech-navy, #0B2545);">404</div>
        <div class="rounded-circle d-inline-flex p-4 fs-1 mb-3" style="background-color: rgba(0, 209, 143, 0.15); color: var(--tech-green, #00D18F);">
          <i class="bi bi-compass"></i>
        </div>
        <h2 class="fw-bold text-dark mb-3">Oups ! Cette page est introuvable</h2>
        <p class="text-muted lead mb-4 fs-6">
          L'article, l'offre d'emploi, la startup ou la page que vous recherchez a peut-être été déplacée, renommée ou n'existe plus.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
          <Link href="/" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: var(--tech-green, #00D18F);">
            <i class="bi bi-house-door-fill me-1"></i> Retour à l'accueil
          </Link>
          <Link href="/startups" class="btn btn-outline-dark rounded-pill px-4 fw-semibold">
            <i class="bi bi-building me-1"></i> Découvrir les Startups
          </Link>
          <Link href="/emplois" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
            <i class="bi bi-briefcase me-1"></i> Offres d'Emploi IT
          </Link>
        </div>

        <!-- Mode Débogage (actif uniquement si APP_DEBUG=true) -->
        <div v-if="debug" class="text-start mt-5 p-4 rounded-4 bg-light border shadow-sm">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill">
              <i class="bi bi-bug-fill me-1"></i> Mode Débogage (APP_DEBUG=true)
            </span>
            <button 
              @click="showTrace = !showTrace" 
              class="btn btn-sm btn-outline-secondary rounded-pill px-3"
            >
              {{ showTrace ? 'Masquer les détails' : 'Afficher les détails de la trace' }}
            </button>
          </div>

          <div v-if="message" class="alert alert-danger mb-3 font-monospace small">
            <strong>Exception :</strong> {{ message }}
          </div>

          <div v-if="file" class="small text-muted mb-2 font-monospace">
            <strong>Fichier :</strong> {{ file }} <span v-if="line"><strong>:{{ line }}</strong></span>
          </div>

          <div v-if="showTrace && trace" class="mt-3">
            <h6 class="fw-bold small text-muted text-uppercase">Stack Trace :</h6>
            <pre class="bg-dark text-light p-3 rounded-3 small font-monospace overflow-auto" style="max-height: 320px; font-size: 11px;">{{ trace }}</pre>
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
  status: { type: Number, default: 404 },
  message: { type: String, default: '' },
  debug: { type: Boolean, default: false },
  file: { type: String, default: null },
  line: { type: [Number, String], default: null },
  trace: { type: String, default: null },
})

const showTrace = ref(false)
</script>
