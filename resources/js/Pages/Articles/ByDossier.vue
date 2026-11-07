<template>
  <FrontLayout>
    <div class="container my-4">
      
      <!-- Dossier Header -->
      <div class="card border-0 rounded-4 text-white p-4 p-md-5 mb-5 shadow overflow-hidden position-relative" style="background: linear-gradient(135deg, #090a10 0%, #1e1b4b 100%);">
        <div class="row align-items-center position-relative z-1">
          <div class="col-lg-8">
            <span class="badge bg-danger mb-3 px-3 py-2 fw-bold text-uppercase">
              <i class="bi bi-folder2-open me-1"></i> Dossier Thématique
            </span>
            <h1 class="display-5 fw-bold mb-3">
              {{ locale === 'en' ? (dossier.title_en || dossier.title_fr) : dossier.title_fr }}
            </h1>
            <p class="lead text-white-50 mb-0">
              {{ locale === 'en' ? (dossier.description_en || dossier.description_fr) : dossier.description_fr }}
            </p>
          </div>
          <div class="col-lg-4 text-center mt-3 mt-lg-0">
            <img 
              :src="'/' + (dossier.cover_image || 'storage/images/chatgpt_dossier.jpg')" 
              :alt="dossier.title_fr" 
              class="img-fluid rounded-4 shadow"
              style="max-height: 200px; width: 100%; object-fit: cover;"
            >
          </div>
        </div>
      </div>

      <!-- Articles in this Dossier -->
      <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
        <h4 class="fw-bold text-uppercase mb-0 border-start border-4 border-danger ps-3">
          Tous les articles de ce dossier ({{ articles.length }})
        </h4>
      </div>

      <div v-if="articles.length" class="row g-4 mb-5">
        <div v-for="article in articles" :key="article.id" class="col-md-6 col-lg-4">
          <div class="article-card">
            <div class="article-card-img-wrap">
              <Link :href="'/article/' + article.slug">
                <img :src="'/' + (article.featured_image || 'storage/images/chatgpt_hero.jpg')" :alt="article.title_fr" class="article-card-img">
              </Link>
            </div>
            <div class="article-card-body">
              <Link :href="'/article/' + article.slug" class="article-card-title">
                {{ locale === 'en' ? (article.title_en || article.title_fr) : article.title_fr }}
              </Link>
              <p class="small text-muted flex-grow-1 text-truncate-2">
                {{ locale === 'en' ? (article.summary_en || article.summary_fr) : article.summary_fr }}
              </p>
              <div class="d-flex justify-content-between align-items-center pt-2 border-top small text-muted">
                <span>{{ article.date_formatted }}</span>
                <span><i class="bi bi-eye me-1"></i>{{ article.views_count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-5 bg-white rounded-4 border">
        <i class="bi bi-folder-x fs-1 text-muted d-block mb-2"></i>
        <h5 class="fw-bold">Aucun article dans ce dossier pour le moment.</h5>
      </div>

    </div>
  </FrontLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

const props = defineProps({
  dossier: { type: Object, required: true },
  articles: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.currentLocale || 'fr');
</script>
