<template>
  <FrontLayout>
    <Head>
      <title>{{ author.name }} - Auteur Tech237</title>
      <meta property="og:title" :content="author.name + ' - Rédaction Tech237'" />
      <meta property="og:description" :content="author.bio || 'Découvrez tous les articles et dossiers de ' + author.name" />
      <meta property="og:image" :content="author.avatar ? '/' + author.avatar : '/assets/images/og-default.jpg'" />
      <meta name="twitter:card" content="summary" />
    </Head>

    <div class="container my-5">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small text-muted">
          <li class="breadcrumb-item"><Link href="/" class="text-dark text-decoration-none">Accueil</Link></li>
          <li class="breadcrumb-item active">{{ author.name }}</li>
        </ol>
      </nav>

      <!-- Author Profile Header Banner -->
      <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-5 bg-white">
        <div class="row align-items-center g-4">
          <div class="col-auto">
            <img 
              v-if="author.avatar" 
              :src="'/' + author.avatar" 
              :alt="author.name" 
              class="rounded-circle object-fit-cover shadow border border-3 border-danger" 
              style="width: 110px; height: 110px;"
            >
            <div 
              v-else 
              class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center shadow" 
              style="width: 110px; height: 110px; font-size: 38px;"
            >
              {{ author.name.charAt(0) }}
            </div>
          </div>

          <div class="col">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
              <h2 class="fw-bold mb-0 text-dark">{{ author.name }}</h2>
              <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill small fw-bold">
                {{ author.role === 'super_admin' ? 'Rédacteur en Chef' : 'Journaliste / Auteur' }}
              </span>
              <span v-if="author.city" class="text-muted small">
                <i class="bi bi-geo-alt-fill text-secondary me-1"></i>{{ author.city }}
              </span>
            </div>

            <p class="text-muted lead mb-3 fs-6" style="max-width: 700px;">
              {{ author.bio || 'Passionné de pop culture, de technologie et de cinéma, analysant les dernières tendances du web et de l\'audiovisuel.' }}
            </p>

            <!-- Social Links & Contact -->
            <div class="d-flex align-items-center gap-3 fs-5">
              <a v-if="author.social_twitter" :href="author.social_twitter" target="_blank" class="text-dark text-decoration-none hover-pink" title="X (Twitter)">
                <i class="bi bi-twitter-x"></i>
              </a>
              <a v-if="author.social_linkedin" :href="author.social_linkedin" target="_blank" class="text-dark text-decoration-none hover-pink" title="LinkedIn">
                <i class="bi bi-linkedin"></i>
              </a>
              <a v-if="author.social_facebook" :href="author.social_facebook" target="_blank" class="text-dark text-decoration-none hover-pink" title="Facebook">
                <i class="bi bi-facebook"></i>
              </a>
              <a v-if="author.social_instagram" :href="author.social_instagram" target="_blank" class="text-dark text-decoration-none hover-pink" title="Instagram">
                <i class="bi bi-instagram"></i>
              </a>
              <a v-if="author.social_tiktok" :href="author.social_tiktok" target="_blank" class="text-dark text-decoration-none hover-pink" title="TikTok">
                <i class="bi bi-tiktok"></i>
              </a>
              <a v-if="author.email" :href="'mailto:' + author.email" class="text-dark text-decoration-none hover-pink" title="Email">
                <i class="bi bi-envelope-at-fill"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Author's Published Articles -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark">
          Articles publiés par {{ author.name }} ({{ articles.total || articles.data.length }})
        </h4>
      </div>

      <div class="row g-4 mb-5">
        <div v-for="art in articles.data" :key="art.id" class="col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden article-card-hover">
            <Link :href="'/article/' + art.slug" class="position-relative overflow-hidden">
              <img 
                :src="'/' + (art.featured_image || 'storage/images/chatgpt_hero.jpg')" 
                :alt="art.title_fr" 
                class="card-img-top object-fit-cover" 
                style="height: 200px;"
              >
              <span v-if="art.category" class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-1 rounded-pill small fw-bold">
                {{ art.category.name_fr }}
              </span>
            </Link>
            <div class="card-body p-4 d-flex flex-column">
              <div class="small text-muted mb-2">{{ art.date_formatted }}</div>
              <h5 class="card-title fw-bold mb-3 flex-grow-1">
                <Link :href="'/article/' + art.slug" class="text-dark text-decoration-none line-clamp-2">
                  {{ locale === 'en' ? (art.title_en || art.title_fr) : art.title_fr }}
                </Link>
              </h5>
              <Link :href="'/article/' + art.slug" class="text-danger fw-bold small text-decoration-none mt-auto">
                Lire l'article &rarr;
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination Links -->
      <div v-if="articles.links && articles.links.length > 3" class="d-flex justify-content-center mt-4">
        <ul class="pagination">
          <li 
            v-for="(link, i) in articles.links" 
            :key="i" 
            class="page-item" 
            :class="{ 'active': link.active, 'disabled': !link.url }"
          >
            <Link 
              v-if="link.url" 
              :href="link.url" 
              class="page-link" 
              v-html="link.label"
            />
            <span v-else class="page-link" v-html="link.label" />
          </li>
        </ul>
      </div>

    </div>
  </FrontLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  author: { type: Object, required: true },
  articles: { type: Object, required: true },
})

const page = usePage()
const locale = computed(() => page.props.currentLocale || 'fr')
</script>

<style scoped>
.hover-pink:hover {
  color: #FF004B !important;
}
.article-card-hover {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.article-card-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
}
</style>
