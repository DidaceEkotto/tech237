<template>
  <FrontLayout>
    <Head>
      <title>Projets Tech & Startups - Tech237 Showcase</title>
      <meta name="description" content="Découvrez les startups innovantes, applications web, mobiles et jeux vidéo développés par la communauté Tech237. Là où les idées deviennent des projets." />
    </Head>

    <div class="container py-4">
      <div class="p-4 p-md-5 text-white rounded-4 mb-4 shadow" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
        <span class="badge mb-2 px-3 py-1 fw-bold text-uppercase text-white" style="background-color: var(--tech-green);">
          <i class="bi bi-rocket-takeoff me-1"></i> Showcase Investisseurs & Développeurs
        </span>
        <h1 class="fw-bold display-6 mb-2">Projets Tech & Innovations Startups</h1>
        <p class="text-white-50 lead mb-3"><em>"Là où les idées deviennent des projets"</em>. Explorez les applications, jeux vidéo et solutions logicielles créées par nos talents et startups certifiées OHADA au Cameroun.</p>
        
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
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <img v-if="proj.banner" :src="'/' + proj.banner" class="card-img-top" style="height: 160px; object-fit: cover;">
            <div v-else class="bg-secondary-subtle d-flex align-items-center justify-content-center" style="height: 160px;">
              <i class="bi bi-code-slash fs-1 text-muted"></i>
            </div>

            <div class="card-body p-4 d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-danger text-uppercase">{{ proj.project_type }}</span>
                <span v-if="proj.startup" class="badge bg-light text-dark border">{{ proj.startup.name }}</span>
              </div>

              <h5 class="fw-bold text-dark mb-2">{{ proj.name }}</h5>
              <p class="text-secondary small flex-grow-1 text-truncate-3">{{ proj.description }}</p>

              <div class="d-flex flex-wrap gap-1 mb-3">
                <span v-for="lang in (proj.languages || []).slice(0, 3)" :key="lang" class="badge bg-light text-dark border small">
                  {{ lang }}
                </span>
                <span v-for="fw in (proj.frameworks_engines || []).slice(0, 2)" :key="fw" class="badge bg-dark-subtle text-dark small">
                  {{ fw }}
                </span>
              </div>

              <Link :href="'/projet/' + proj.slug" class="btn btn-outline-danger w-100 rounded-pill fw-bold">
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
</script>
