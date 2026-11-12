<template>
  <FrontLayout>
    <div class="container my-4">
      
      <!-- Header with Background Ad Image & Gradient Overlay -->
      <div class="p-4 p-md-5 rounded-4 mb-4 text-white shadow-sm position-relative overflow-hidden">
        <!-- Background ad image layer -->
        <div
          v-if="heroBgImage"
          class="position-absolute top-0 start-0 w-100 h-100"
          :style="{
            backgroundImage: `url('${heroBgImage}')`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }"
        ></div>

        <!-- Semi-transparent gradient overlay for readability & ad visibility -->
        <div
          class="position-absolute top-0 start-0 w-100 h-100"
          :style="{
            background: heroBgImage
              ? 'linear-gradient(135deg, rgba(31, 11, 32, 0.82) 0%, rgba(74, 14, 78, 0.86) 100%)'
              : 'linear-gradient(135deg, rgb(31, 11, 32) 0%, rgb(74, 14, 78) 100%)',
            pointerEvents: 'none',
          }"
        ></div>

        <!-- Sponsor link badge if ad has link_url -->
        <a
          v-if="heroAd?.link_url"
          :href="heroAd.link_url"
          target="_blank"
          rel="noopener noreferrer"
          class="badge bg-dark bg-opacity-75 text-white-50 border border-white border-opacity-25 position-absolute top-0 end-0 m-3 extra-small text-decoration-none z-2"
        >
          <i class="bi bi-badge-ad text-warning me-1"></i>Sponsorisé &bull; {{ heroAd.title }} <i class="bi bi-box-arrow-up-right ms-1"></i>
        </a>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 position-relative z-1">
          <div>
            <span class="badge bg-danger mb-2 text-uppercase px-3 py-1 fw-bold">Gaming Labs</span>
            <h1 class="display-5 fw-bold mb-2 text-white">
              <i class="bi bi-controller me-2"></i>{{ locale === 'en' ? 'Video Game Reviews & Verdicts' : 'Tests Jeux Vidéo & Verdicts (/20)' }}
            </h1>
            <p class="lead text-white-50 mb-0">
              {{ locale === 'en' ? 'In-depth analysis of gameplay, graphics, accessibility and soundtrack across all major gaming platforms.' : 'Analyses complètes du gameplay, des graphismes, de l\'accessibilité et de la bande son sur toutes les plateformes.' }}
            </p>
          </div>
          <div :class="heroAd?.link_url ? 'mt-md-4' : ''">
            <button 
              @click="toggleBookmark"
              :disabled="isBookmarking"
              class="btn rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-nowrap transition"
              :class="isBookmarkedState ? 'btn-warning text-dark' : 'btn-outline-light'"
              :title="isBookmarkedState ? 'Rubrique suivie : alertes email actives' : 'Recevoir un email avec le résumé et l\'image à chaque nouveau test de jeu vidéo'"
            >
              <i :class="isBookmarkedState ? 'bi bi-bell-fill text-dark' : 'bi bi-bell'"></i>
              <span>{{ isBookmarkedState ? 'Rubrique suivie (Email)' : 'Suivre cette rubrique' }}</span>
            </button>
          </div>
        </div>
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
  isBookmarked: { type: Boolean, default: false },
});

const isBookmarkedState = ref(props.isBookmarked || false);
const isBookmarking = ref(false);

function toggleBookmark() {
  if (isBookmarking.value) return;
  isBookmarking.value = true;

  fetch('/espace-utilisateur/favoris/toggle', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify({
      item_type: 'games_rubrique',
      item_id: 0,
    })
  }).then(r => {
    if (r.status === 401) {
      window.location.href = '/login';
      return;
    }
    return r.json();
  }).then(data => {
    if (data && data.success) {
      isBookmarkedState.value = data.bookmarked;
    }
  }).catch(err => {
    console.error('Bookmark error:', err);
  }).finally(() => {
    isBookmarking.value = false;
  });
}

const page = usePage();
const locale = computed(() => page.props.currentLocale || 'fr');

const heroAd = computed(() => page.props.adBanners?.hero_games?.[0] || null);
const heroBgImage = computed(() => {
  if (heroAd.value?.image) {
    const img = heroAd.value.image;
    return img.startsWith('http') ? img : (img.startsWith('/') ? img : '/' + img);
  }
  return null;
});

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
