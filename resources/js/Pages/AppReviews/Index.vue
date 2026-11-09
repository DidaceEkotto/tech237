<template>
  <FrontLayout>
    <div class="container my-4">
      
      <!-- Header -->
      <div class="p-4 p-md-5 rounded-4 mb-4 text-white shadow-sm" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
          <div>
            <span class="badge bg-success mb-2 text-uppercase px-3 py-1 fw-bold">App Labs</span>
            <h1 class="display-5 fw-bold mb-2">
              <i class="bi bi-phone me-2"></i>{{ locale === 'en' ? 'Mobile & Desktop App Reviews' : 'Tests d\'Applications (Mobiles, Web & Desktop)' }}
            </h1>
            <p class="lead text-white-50 mb-0">
              {{ locale === 'en' ? 'Testing UI, UX, and handling of the hottest applications with final scores out of 20.' : 'Bancs d\'essai détaillés sur l\'UI, l\'UX et la prise en main avec notes finales sur 20.' }}
            </p>
          </div>
          <div>
            <button 
              @click="toggleBookmark"
              :disabled="isBookmarking"
              class="btn rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-nowrap transition"
              :class="isBookmarkedState ? 'btn-warning text-dark' : 'btn-outline-light'"
              :title="isBookmarkedState ? 'Rubrique suivie : alertes email actives' : 'Recevoir un email avec le résumé et l\'image à chaque nouveau test d\'application'"
            >
              <i :class="isBookmarkedState ? 'bi bi-bell-fill text-dark' : 'bi bi-bell'"></i>
              <span>{{ isBookmarkedState ? 'Rubrique suivie (Email)' : 'Suivre cette rubrique' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Reviews Grid -->
      <div v-if="reviews.length" class="row g-4 mb-5">
        <div v-for="app in reviews" :key="app.id" class="col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="position-relative">
              <Link :href="'/test-app/' + app.slug">
                <img :src="'/' + (app.cover_image || 'storage/images/app_chatgpt.jpg')" :alt="app.app_name" class="img-fluid w-100" style="height: 200px; object-fit: cover;">
              </Link>
              <div class="position-absolute bottom-0 start-0 m-3">
                <span class="score-badge-20">{{ app.final_score }} / 20</span>
              </div>
              <span class="badge bg-dark position-absolute top-0 end-0 m-3">
                <i class="bi" :class="app.category_logo || 'bi-shop'"></i> {{ app.category_name }}
              </span>
            </div>
            <div class="card-body p-4 d-flex flex-column">
              <h4 class="fw-bold mb-2">
                <Link :href="'/test-app/' + app.slug" class="text-dark text-decoration-none">
                  {{ app.app_name }}
                </Link>
              </h4>
              <p class="small text-muted flex-grow-1 text-truncate-2">
                {{ locale === 'en' ? (app.summary_en || app.summary_fr) : app.summary_fr }}
              </p>
              
              <div class="p-2 bg-light rounded-3 small mb-3 border">
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Design & UI</span>
                  <strong>{{ app.score_ui }}/20</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Expérience UX</span>
                  <strong>{{ app.score_ux }}/20</strong>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Prise en main</span>
                  <strong>{{ app.score_handling }}/20</strong>
                </div>
              </div>

              <Link :href="'/test-app/' + app.slug" class="btn btn-outline-success w-100 rounded-pill fw-bold btn-sm">
                Voir l'évaluation complète <i class="bi bi-arrow-right ms-1"></i>
              </Link>
            </div>
          </div>
        </div>
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

const page = usePage();
const locale = computed(() => page.props.currentLocale || 'fr');

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
      item_type: 'apps_rubrique',
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
</script>
