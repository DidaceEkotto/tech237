<template>
  <FrontLayout>
    <Head>
      <title>Projets Tech & Startups - Tech237 Showcase</title>
      <meta name="description" content="Découvrez les startups innovantes, applications web, mobiles et jeux vidéo développés par la communauté Tech237. Là où les idées deviennent des projets." />
    </Head>

    <div class="container py-4">
      <div class="p-4 p-md-5 text-white rounded-4 mb-4 shadow" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
        <span class="badge mb-2 px-3 py-1 fw-bold text-uppercase text-white" style="background-color: var(--tech-green, #00A859);">
          <i class="bi bi-rocket-takeoff me-1"></i> Showcase Investisseurs & Développeurs
        </span>
        <h1 class="fw-bold display-6 mb-2">Projets Tech & Innovations Startups</h1>
        <p class="text-white-50 lead mb-3"><em>"Là où les idées deviennent des projets"</em>. Explorez les applications, jeux vidéo et solutions logicielles créées par nos talents et startups certifiées au Cameroun.</p>
        
        <div class="d-flex flex-wrap gap-2">
          <Link href="/projets" class="btn btn-sm rounded-pill px-3" :class="!selectedType ? 'btn-danger' : 'btn-outline-light'">Tous</Link>
          <Link href="/projets?type=web" class="btn btn-sm rounded-pill px-3" :class="selectedType === 'web' ? 'btn-danger' : 'btn-outline-light'">Web & SaaS</Link>
          <Link href="/projets?type=mobile" class="btn btn-sm rounded-pill px-3" :class="selectedType === 'mobile' ? 'btn-danger' : 'btn-outline-light'">Applications Mobiles</Link>
          <Link href="/projets?type=game" class="btn btn-sm rounded-pill px-3" :class="selectedType === 'game' ? 'btn-danger' : 'btn-outline-light'">Jeux Vidéo</Link>
          <Link href="/projets?type=desktop" class="btn btn-sm rounded-pill px-3" :class="selectedType === 'desktop' ? 'btn-danger' : 'btn-outline-light'">Desktop</Link>
        </div>
      </div>

      <div class="row g-4">
        <div v-for="proj in projects.data" :key="proj.id" class="col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white tech237-hover-lift">
            <div class="position-relative">
              <img 
                v-if="proj.banner" 
                :src="proj.banner.startsWith('/') ? proj.banner : '/' + proj.banner" 
                :alt="proj.name"
                class="card-img-top w-100" 
                style="height: 160px; object-fit: cover;"
              >
              <div v-else class="d-flex align-items-center justify-content-center" style="height: 160px; background: linear-gradient(135deg, #0d5c48 0%, #063428 100%);">
                <i class="bi bi-code-slash fs-1 text-white opacity-50"></i>
              </div>

              <!-- Logo superposé sur la bannière -->
              <div class="position-absolute" style="bottom: -22px; left: 20px; z-index: 3;">
                <div class="rounded-3 border border-2 border-white shadow-sm overflow-hidden bg-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                  <img 
                    v-if="proj.logo" 
                    :src="proj.logo.startsWith('/') ? proj.logo : '/' + proj.logo" 
                    :alt="proj.name" 
                    class="w-100 h-100 object-fit-contain p-1"
                  >
                  <div v-else class="fw-bold text-success fs-5">
                    {{ (proj.name || 'P').charAt(0).toUpperCase() }}
                  </div>
                </div>
              </div>
            </div>

            <div class="card-body p-4 pt-4 d-flex flex-column" style="margin-top: 12px;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge text-uppercase px-2 py-1 rounded-pill fw-bold text-white" style="background-color: var(--tech-green, #00A859); font-size: 11px;">
                  {{ formatProjectType(proj) }}
                </span>
                <span v-if="proj.startup" class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                  {{ proj.startup.name }}
                </span>
              </div>

              <h5 class="fw-bold text-dark mb-2">{{ proj.name }}</h5>
              <p class="text-secondary small flex-grow-1 mb-3" style="min-height: 42px;">
                {{ formatCardDescription(proj.description) }}
              </p>

              <div class="d-flex flex-wrap gap-1 mb-3" v-if="(proj.languages && proj.languages.length) || (proj.frameworks_engines && proj.frameworks_engines.length)">
                <span v-for="lang in (proj.languages || []).slice(0, 3)" :key="lang" class="badge bg-light text-dark border small">
                  {{ lang }}
                </span>
                <span v-for="fw in (proj.frameworks_engines || []).slice(0, 2)" :key="fw" class="badge bg-dark-subtle text-dark small">
                  {{ fw }}
                </span>
              </div>

              <Link :href="'/projet/' + proj.slug" class="btn btn-outline-success w-100 rounded-pill fw-bold" style="border-color: #00A859; color: #00A859;">
                Découvrir le projet <i class="bi bi-arrow-right ms-1"></i>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

defineProps({
  projects: Object,
  selectedType: String,
})

function formatCardDescription(text) {
  if (!text) return ''
  const clean = text
    .replace(/<br\s*[\/]?>/gi, ' ')
    .replace(/<[^>]*>/g, '')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
  return clean.length > 95 ? clean.substring(0, 95) + '...' : clean
}

function formatProjectType(proj) {
  if (!proj) return 'Application Web'
  if (Array.isArray(proj.app_types) && proj.app_types.length) {
    return proj.app_types[0]
  }
  if (Array.isArray(proj.game_types) && proj.game_types.length) {
    return proj.game_types[0]
  }
  const map = {
    web: 'Application Web',
    mobile: 'Application Mobile',
    desktop: 'Logiciel Desktop',
    game: 'Jeu Vidéo',
  }
  return map[proj.project_type] || proj.project_type || 'Application Web'
}
</script>
