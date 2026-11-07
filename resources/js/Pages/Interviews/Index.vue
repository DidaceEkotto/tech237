<template>
  <FrontLayout>
    <div class="container my-4">
      
      <!-- Header -->
      <div class="p-4 p-md-5 rounded-4 mb-4 text-white shadow-sm" style="background: linear-gradient(135deg, #31103f 0%, #581c87 100%);">
        <span class="badge bg-danger mb-2 text-uppercase px-3 py-1 fw-bold">Interviews</span>
        <h1 class="display-5 fw-bold mb-2">
          <i class="bi bi-mic me-2"></i>{{ locale === 'en' ? 'Exclusive Interviews & Conversations' : 'Interviews Exclusives & Grands Entretiens' }}
        </h1>
        <p class="lead text-white-50 mb-0">
          {{ locale === 'en' ? 'Direct discussions with cinema stars, showrunners, developers and creators.' : 'Rencontres au sommet avec les stars du cinéma, créateurs de séries, développeurs et personnalités pop-culture.' }}
        </p>
      </div>

      <!-- Interviews Grid -->
      <div v-if="interviews.length" class="row g-4 mb-5">
        <div v-for="item in interviews" :key="item.id" class="col-md-6">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="position-relative">
              <Link :href="'/interview/' + item.slug">
                <img :src="'/' + (item.cover_image || 'storage/images/interview_deltoro.jpg')" :alt="item.interviewee_name" class="img-fluid w-100" style="height: 240px; object-fit: cover;">
              </Link>
              <span v-if="item.video_url" class="badge bg-danger position-absolute top-0 end-0 m-3 px-3 py-2 fw-bold">
                <i class="bi bi-youtube me-1"></i> Vidéo Intégrée
              </span>
            </div>
            <div class="card-body p-4 d-flex flex-column">
              <span class="badge bg-danger-subtle text-danger align-self-start mb-2">
                {{ item.interviewee_role || 'Invité Spécial' }}
              </span>
              <h4 class="fw-bold mb-2">
                <Link :href="'/interview/' + item.slug" class="text-dark text-decoration-none">
                  {{ locale === 'en' ? (item.title_en || item.title_fr) : item.title_fr }}
                </Link>
              </h4>
              <p class="small text-muted flex-grow-1 text-truncate-2">
                {{ locale === 'en' ? (item.summary_en || item.summary_fr) : item.summary_fr }}
              </p>
              <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                <span class="small fw-bold text-dark">{{ item.interviewee_name }}</span>
                <Link :href="'/interview/' + item.slug" class="btn btn-outline-danger btn-sm rounded-pill fw-bold">
                  Lire l'entretien <i class="bi bi-arrow-right ms-1"></i>
                </Link>
              </div>
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
  interviews: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.currentLocale || 'fr');
</script>
