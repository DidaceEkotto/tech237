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
              <span class="badge bg-dark position-absolute top-0 end-0 m-3">
                <i class="bi" :class="game.platform_logo || 'bi-controller'"></i> {{ game.platform_category }}
              </span>
            </div>
            <div class="card-body p-4 d-flex flex-column">
              <h5 class="fw-bold mb-2">
                <Link :href="'/test-jeu/' + game.slug" class="text-dark text-decoration-none hover-danger">
                  {{ truncateText(game.game_name, 59) }}
                </Link>
              </h5>
              <p class="small text-muted flex-grow-1 mb-3">
                {{ truncateText(locale === 'en' ? (game.summary_en || game.summary_fr) : game.summary_fr, 99) }}
              </p>

              <Link :href="'/test-jeu/' + game.slug" class="btn btn-outline-danger w-100 rounded-pill fw-bold btn-sm mt-auto">
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

const truncateText = (text, maxLength) => {
  if (!text) return '';
  const clean = text.replace(/<[^>]*>?/gm, '').trim();
  if (clean.length <= maxLength) return clean;
  return clean.substring(0, maxLength - 3) + '...';
};
</script>
