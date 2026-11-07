<template>
  <FrontLayout>
    <div class="container my-4">
      
      <!-- Header -->
      <div class="p-4 p-md-5 rounded-4 mb-4 text-white shadow-sm" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);">
        <span class="badge bg-success mb-2 text-uppercase px-3 py-1 fw-bold">App Labs</span>
        <h1 class="display-5 fw-bold mb-2">
          <i class="bi bi-phone me-2"></i>{{ locale === 'en' ? 'Mobile & Desktop App Reviews' : 'Tests d\'Applications (Mobiles, Web & Desktop)' }}
        </h1>
        <p class="lead text-white-50 mb-0">
          {{ locale === 'en' ? 'Testing UI, UX, and handling of the hottest applications with final scores out of 20.' : 'Bancs d\'essai détaillés sur l\'UI, l\'UX et la prise en main avec notes finales sur 20.' }}
        </p>
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
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

const props = defineProps({
  reviews: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.currentLocale || 'fr');
</script>
