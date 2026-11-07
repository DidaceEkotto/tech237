<template>
  <FrontLayout>
    <div class="container my-5">
      <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm border max-w-900 mx-auto">
        <h1 class="display-6 fw-bold mb-4 border-bottom pb-3">
          {{ locale === 'en' ? 'Terms of Service' : 'Conditions Générales d\'Utilisation' }}
        </h1>
        <div class="article-body-content" v-html="locale === 'en' ? (settings.terms_of_service_en || settings.terms_of_service_fr) : settings.terms_of_service_fr"></div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

const page = usePage();
const settings = computed(() => page.props.siteSettings || {});
const locale = computed(() => page.props.currentLocale || 'fr');
</script>

<style scoped>
.max-w-900 { max-width: 900px; }
</style>
