<template>
  <FrontLayout>
    <Head :title="currentLetter ? 'Dossiers avec la lettre ' + currentLetter + ' - Tech237' : 'Index des Dossiers Thématiques - Tech237'" />

    <!-- Hero Header -->
    <div class="py-5 bg-white border-bottom">
      <div class="container">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <div class="d-flex align-items-center gap-2 fw-bold text-uppercase small" style="color: var(--tech-green);">
            <i class="bi bi-folder2-open"></i> Index Alphabétique des Dossiers
          </div>
          <Link href="/" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold">
            <i class="bi bi-house-door me-1"></i> Accueil Tech237
          </Link>
        </div>

        <h1 class="display-6 fw-bold text-dark mb-2">Index des Grands Dossiers Tech237</h1>
        <p class="text-muted mb-4" style="max-width: 680px;">
          Retrouvez tous les grands dossiers thématiques, analyses de startups, tutoriels tech, gaming et innovations numériques classés par lettre.
        </p>

        <!-- Alphabet Bar -->
        <div class="d-flex flex-wrap gap-1 p-2 bg-light rounded-pill border">
          <Link 
            href="/index-dossiers" 
            class="letter-pill"
            :class="{ 'active': !currentLetter }"
          >
            Tous
          </Link>
          <Link 
            href="/index-dossiers?letter=0-9" 
            class="letter-pill"
            :class="{ 'active': currentLetter === '0-9' }"
          >
            #0_9
          </Link>
          <Link 
            v-for="letter in letters" 
            :key="letter" 
            :href="'/index-dossiers?letter=' + letter" 
            class="letter-pill"
            :class="{ 'active': currentLetter === letter }"
          >
            {{ letter }}
          </Link>
        </div>
      </div>
    </div>

    <!-- Dossiers List Container -->
    <div class="py-5 bg-light min-vh-50">
      <div class="container">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <h4 class="fw-bold mb-0 text-dark">
            <span v-if="currentLetter">Dossiers contenant la lettre « {{ currentLetter === '0-9' ? '#0_9' : currentLetter }} »</span>
            <span v-else>Tous les dossiers thématiques</span>
            <span class="badge bg-secondary-subtle text-secondary ms-2 fs-6 rounded-pill">{{ dossiers.length }}</span>
          </h4>

          <Link href="/" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Retour à l'accueil
          </Link>
        </div>

        <div v-if="dossiers.length > 0" class="row g-4">
          <div v-for="d in dossiers" :key="d.id" class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden dossier-card transition-all bg-white">
              <div class="position-relative" style="height: 180px; background-color: var(--tech-navy);">
                <img 
                  v-if="d.cover_image" 
                  :src="'/' + d.cover_image" 
                  :alt="d.title_fr" 
                  class="w-100 h-100 object-fit-cover opacity-90"
                >
                <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center text-white-50">
                  <i class="bi bi-folder-fill fs-1"></i>
                </div>
                <div class="position-absolute top-0 end-0 m-3">
                  <span class="badge rounded-pill px-3 py-2 fw-bold shadow-sm text-white" style="background-color: var(--tech-green);">
                    {{ d.articles_count }} article(s)
                  </span>
                </div>
              </div>

              <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                  <h5 class="fw-bold text-dark mb-2">#{{ d.title_fr }}</h5>
                  <p class="text-muted small mb-3 line-clamp-2">
                    {{ d.description_fr || 'Découvrez l\'ensemble des articles, enquêtes et décryptages liés à ce dossier sur Tech237.' }}
                  </p>
                </div>

                <Link :href="'/dossier/' + d.slug" class="btn btn-outline-dark rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-2">
                  <span>Consulter le dossier &amp; ses articles</span>
                  <i class="bi bi-arrow-right"></i>
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else class="text-center py-5 bg-white rounded-4 shadow-sm border p-5">
          <i class="bi bi-folder-x fs-1 text-muted d-block mb-3"></i>
          <h4 class="fw-bold text-dark mb-2">Aucun dossier trouvé pour « {{ currentLetter }} »</h4>
          <p class="text-muted small mb-4">
            Il n'y a actuellement aucun dossier thématique enregistré contenant cette lettre.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <Link href="/index-dossiers" class="btn btn-dark rounded-pill px-4 fw-bold">
              Voir tous les dossiers
            </Link>
            <Link href="/" class="btn btn-outline-secondary rounded-pill px-4">
              Retour à l'accueil
            </Link>
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
  dossiers: {
    type: Array,
    default: () => [],
  },
  currentLetter: {
    type: String,
    default: '',
  },
})

const letters = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z']
</script>

<style scoped>
.letter-pill {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: 700;
  font-size: 12px;
  color: #495057;
  text-decoration: none;
  transition: all 0.2s ease;
}
.letter-pill:hover {
  background-color: #dee2e6;
  color: var(--tech-green);
}
.letter-pill.active {
  background-color: var(--tech-green);
  color: white;
  box-shadow: 0 2px 6px rgba(0, 168, 89, 0.3);
}
.dossier-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.min-vh-50 {
  min-height: 50vh;
}
</style>
