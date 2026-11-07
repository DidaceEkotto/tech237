<template>
  <FrontLayout>
    <div class="container my-4">
      
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3 small">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><Link href="/">Accueil</Link></li>
          <li class="breadcrumb-item"><Link href="/interviews">Interviews</Link></li>
          <li class="breadcrumb-item active">{{ interview.interviewee_name }}</li>
        </ol>
      </nav>

      <div class="row g-4">
        <div class="col-lg-8">
          
          <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm border mb-4">
            
            <div class="d-flex align-items-center gap-2 mb-3">
              <span class="badge bg-danger px-3 py-2 fw-bold text-uppercase">Entretien Exclusif</span>
              <span class="badge bg-secondary-subtle text-secondary px-3 py-2">{{ interview.interviewee_role || 'Personnalité' }}</span>
            </div>

            <h1 class="display-6 fw-bold text-dark mb-3">
              {{ locale === 'en' ? (interview.title_en || interview.title_fr) : interview.title_fr }}
            </h1>

            <div class="lead text-muted border-start border-4 border-danger ps-3 mb-4">
              {{ locale === 'en' ? (interview.summary_en || interview.summary_fr) : interview.summary_fr }}
            </div>

            <!-- YouTube Video Embed (if available) -->
            <div v-if="embedUrl" class="ratio ratio-16x9 rounded-4 overflow-hidden mb-4 shadow">
              <iframe :src="embedUrl" title="YouTube video player" allowfullscreen></iframe>
            </div>

            <!-- Cover Image (if no video or as header) -->
            <div v-else-if="interview.cover_image" class="mb-4">
              <img :src="'/' + interview.cover_image" :alt="interview.interviewee_name" class="img-fluid rounded-4 w-100 shadow-sm" style="max-height: 420px; object-fit: cover;">
            </div>

            <!-- Interview Transcript WYSIWYG Body -->
            <div class="article-body-content" v-html="locale === 'en' ? (interview.content_en || interview.content_fr) : interview.content_fr"></div>

          </div>

        </div>

        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-center">
            <div class="mb-3">
              <img v-if="interview.cover_image" :src="'/' + interview.cover_image" :alt="interview.interviewee_name" class="rounded-circle object-fit-cover shadow border border-3 border-danger" style="width: 100px; height: 100px;">
              <div v-else class="rounded-circle bg-danger-subtle text-danger d-inline-flex p-3 fs-1">
                <i class="bi bi-mic-fill"></i>
              </div>
            </div>
            <h5 class="fw-bold mb-1">{{ interview.interviewee_name }}</h5>
            <p class="small text-muted mb-3">{{ interview.interviewee_role }}</p>
            <div class="small text-secondary pt-3 border-top">
              Interview réalisée par l'équipe éditoriale Tech237.
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
  interview: { type: Object, required: true },
});

const page = usePage();
const locale = computed(() => page.props.currentLocale || 'fr');

const embedUrl = computed(() => {
  const url = props.interview.video_url;
  if (!url) return null;
  const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
  return match ? `https://www.youtube.com/embed/${match[1]}` : null;
});
</script>
