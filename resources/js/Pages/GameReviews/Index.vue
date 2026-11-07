<template>
  <FrontLayout>
    <div class="container my-4">
      
      <!-- Header -->
      <div class="p-4 p-md-5 rounded-4 mb-4 text-white shadow-sm" style="background: linear-gradient(135deg, #1f0b20 0%, #4a0e4e 100%);">
        <span class="badge bg-danger mb-2 text-uppercase px-3 py-1 fw-bold">Gaming Labs</span>
        <h1 class="display-5 fw-bold mb-2">
          <i class="bi bi-controller me-2"></i>{{ locale === 'en' ? 'Video Game Reviews & Verdicts' : 'Tests Jeux Vidéo & Verdicts (/20)' }}
        </h1>
        <p class="lead text-white-50 mb-0">
          {{ locale === 'en' ? 'In-depth analysis of gameplay, graphics, accessibility and soundtrack across all major gaming platforms.' : 'Analyses complètes du gameplay, des graphismes, de l\'accessibilité et de la bande son sur toutes les plateformes.' }}
        </p>
      </div>

      <!-- Platform Filter Pills -->
      <div class="d-flex align-items-center gap-2 mb-4 overflow-auto py-2">
        <span class="small fw-bold text-muted text-nowrap"><i class="bi bi-filter me-1"></i>Plateforme :</span>
        <button 
          v-for="plat in platforms" 
          :key="plat" 
          @click="selectedPlatform = plat"
          class="btn btn-sm rounded-pill text-nowrap"
          :class="selectedPlatform === plat ? 'btn-danger fw-bold' : 'btn-white bg-white border text-dark'"
        >
          {{ plat }}
        </button>
      </div>

      <!-- Reviews Grid -->
      <div v-if="filteredReviews.length" class="row g-4 mb-5">
        <div v-for="game in filteredReviews" :key="game.id" class="col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="position-relative">
              <Link :href="'/test-jeu/' + game.slug">
                <img :src="'/' + (game.cover_image || 'storage/images/game_zelda.jpg')" :alt="game.game_name" class="img-fluid w-100" style="height: 220px; object-fit: cover;">
              </Link>
              <div class="position-absolute bottom-0 start-0 m-3">
                <span class="score-badge-20">{{ game.final_score }} / 20</span>
              </div>
              <span class="badge bg-dark position-absolute top-0 end-0 m-3">
                <i class="bi" :class="game.platform_logo || 'bi-controller'"></i> {{ game.platform_category }}
              </span>
            </div>
            <div class="card-body p-4 d-flex flex-column">
              <h4 class="fw-bold mb-2">
                <Link :href="'/test-jeu/' + game.slug" class="text-dark text-decoration-none">
                  {{ game.game_name }}
                </Link>
              </h4>
              <p class="small text-muted flex-grow-1 text-truncate-2">
                {{ locale === 'en' ? (game.summary_en || game.summary_fr) : game.summary_fr }}
              </p>
              
              <div class="p-2 bg-light rounded-3 small mb-3 border">
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Gameplay</span>
                  <strong>{{ game.score_gameplay }}/20</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Graphismes</span>
                  <strong>{{ game.score_graphics }}/20</strong>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Audio</span>
                  <strong>{{ game.score_soundtrack }}/20</strong>
                </div>
              </div>

              <Link :href="'/test-jeu/' + game.slug" class="btn btn-outline-danger w-100 rounded-pill fw-bold btn-sm">
                Lire le test complet <i class="bi bi-arrow-right ms-1"></i>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-5 bg-white rounded-4 border">
        <i class="bi bi-controller fs-1 text-muted d-block mb-2"></i>
        <h5 class="fw-bold">Aucun test de jeu pour cette plateforme.</h5>
      </div>

    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

const props = defineProps({
  reviews: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.currentLocale || 'fr');

const selectedPlatform = ref('Toutes');
const platforms = [
  'Toutes',
  'PlayStation 5',
  'PlayStation 4',
  'Xbox Series S & X',
  'Xbox One',
  'Nintendo Switch',
  'Nintendo Switch 2',
  'PC',
  'Linux ou Mac',
  'Mobile'
];

const filteredReviews = computed(() => {
  if (selectedPlatform.value === 'Toutes') return props.reviews;
  return props.reviews.filter(r => r.platform_category === selectedPlatform.value);
});
</script>
