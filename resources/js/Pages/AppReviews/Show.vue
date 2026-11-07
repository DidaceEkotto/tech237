<template>
  <FrontLayout>
    <Head>
      <title>Test : {{ review.app_name }} - Note {{ review.final_score }}/20 Tech237 Apps</title>
      <meta name="description" :content="review.summary_fr || 'Découvrez le test complet et la note de l\'application ' + review.app_name" />
      <meta property="og:type" content="article" />
      <meta property="og:title" :content="'Test : ' + review.app_name + ' (' + review.final_score + '/20)'" />
      <meta property="og:description" :content="review.summary_fr || ('Test de ' + review.app_name)" />
      <meta property="og:image" :content="review.cover_image ? '/' + review.cover_image : '/assets/images/og-default.jpg'" />
      <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div class="container my-4">
      
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3 small">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><Link href="/">Accueil</Link></li>
          <li class="breadcrumb-item"><Link href="/tests-applications">Tests Applications</Link></li>
          <li class="breadcrumb-item active">{{ review.app_name }}</li>
        </ol>
      </nav>

      <div class="row g-4">
        <div class="col-lg-8">
          
          <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm border mb-4">
            
            <!-- Multi-platform badges -->
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
              <span v-for="plat in (review.platforms || [review.category_name])" :key="plat" class="badge bg-dark px-3 py-2">
                <i class="bi bi-phone me-1 text-success"></i> {{ plat }}
              </span>
              <span class="badge bg-success-subtle text-success px-3 py-2 fw-bold">Banc d'Essai Applicatif</span>
            </div>

            <h1 class="display-6 fw-bold text-dark mb-3">
              Banc d'essai : {{ review.app_name }}
            </h1>

            <p class="lead text-muted border-start border-4 border-success ps-3 mb-4">
              {{ locale === 'en' ? (review.summary_en || review.summary_fr) : review.summary_fr }}
            </p>

            <!-- App Logo / Cover placed before description -->
            <div v-if="review.cover_image" class="mb-4">
              <img :src="'/' + review.cover_image" :alt="review.app_name" class="img-fluid rounded-4 w-100 shadow-sm" style="max-height: 420px; object-fit: cover;">
            </div>

            <!-- Stores Download Buttons & Links (Google Play, App Store, Website, Huawei AppGallery) -->
            <div class="d-flex gap-2 mb-4 flex-wrap">
              <a v-if="review.playstore_url" :href="review.playstore_url" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3">
                <i class="bi bi-google-play me-1 text-warning"></i> Google Play
              </a>
              <a v-if="review.appstore_url" :href="review.appstore_url" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3">
                <i class="bi bi-apple me-1 text-white"></i> App Store
              </a>
              <a v-if="review.website_url" :href="review.website_url" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                <i class="bi bi-globe me-1 text-primary"></i> Site Web Officiel
              </a>
              <a v-if="review.appgallery_url" :href="review.appgallery_url" target="_blank" class="btn btn-danger btn-sm rounded-pill px-3">
                <i class="bi bi-bag-check me-1"></i> Huawei AppGallery
              </a>
            </div>

            <!-- Full Review Body (WYSIWYG with In-Article Ad) -->
            <div class="article-body-content mb-5" v-html="contentWithInArticleAd"></div>

            <!-- SCORE SUMMARY CARD (PLACED AT BOTTOM AFTER DESCRIPTION AS REQUESTED) -->
            <div class="p-4 rounded-4 mb-4 text-white shadow" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
              <div class="row align-items-center g-3">
                <div class="col-md-4 text-center border-end border-success border-opacity-50">
                  <span class="text-white-50 small fw-bold text-uppercase d-block mb-1">Note Finale</span>
                  <div class="display-3 fw-bold text-warning">{{ review.final_score }}<small class="fs-5 text-white-50">/20</small></div>
                </div>
                <div class="col-md-8">
                  <div class="row g-3">
                    <div class="col-4 text-center">
                      <div class="small text-white-50">Interface (UI)</div>
                      <div class="fw-bold fs-5">{{ review.score_ui }} / 20</div>
                    </div>
                    <div class="col-4 text-center">
                      <div class="small text-white-50">Expérience (UX)</div>
                      <div class="fw-bold fs-5">{{ review.score_ux }} / 20</div>
                    </div>
                    <div class="col-4 text-center">
                      <div class="small text-white-50">Prise en main</div>
                      <div class="fw-bold fs-5">{{ review.score_handling }} / 20</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- PROS & CONS (PLACED AT BOTTOM AFTER DESCRIPTION) -->
            <div v-if="review.pros_fr || review.cons_fr" class="row g-3 mb-2">
              <div class="col-md-6">
                <div class="p-3 rounded-4 bg-success-subtle h-100 border border-success border-opacity-25">
                  <h6 class="fw-bold text-success mb-2">
                    <i class="bi bi-plus-circle-fill me-1"></i>Points Forts
                  </h6>
                  <div class="small text-dark" style="white-space: pre-line;">
                    {{ locale === 'en' ? (review.pros_en || review.pros_fr) : review.pros_fr }}
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-4 h-100" style="background-color: rgba(220, 53, 69, 0.12); border: 1px solid rgba(220, 53, 69, 0.35);">
                  <h6 class="fw-bold mb-2 d-flex align-items-center" style="color: #dc3545 !important;">
                    <span class="badge rounded-pill me-2 px-2 py-1 text-white" style="background-color: #dc3545 !important;"><i class="bi bi-dash-circle-fill"></i></span>
                    <span class="fw-bold fs-6" style="color: #dc3545 !important;">Points Faibles</span>
                  </h6>
                  <div class="small fw-semibold" style="white-space: pre-line; color: #b02a37 !important;">
                    {{ locale === 'en' ? (review.cons_en || review.cons_fr) : review.cons_fr }}
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3">Verdict Tech237 Apps</h5>
            <div class="text-center p-3 bg-light rounded-4 mb-3 border">
              <span class="badge bg-success fs-3 px-3 py-2 rounded-pill">{{ review.final_score }} / 20</span>
              <div class="small text-muted mt-2">Test réalisé par {{ review.author?.name || 'Rédaction Tech237' }}</div>
            </div>
            <Link href="/tests-applications" class="btn btn-outline-success w-100 rounded-pill fw-bold">
              Tous les tests applications
            </Link>
          </div>

          <!-- Sidebar Ad Banner (Pavé 300x250) -->
          <div v-if="sidebarAd" class="card rounded-4 p-3 mb-4 border-0 shadow-sm text-center bg-light">
            <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 9px; letter-spacing: 1px;">Sponsor &bull; Partenaire</div>
            <a v-if="sidebarAd.image" :href="sidebarAd.link_url || '#'" target="_blank" rel="noopener noreferrer">
              <img :src="'/' + sidebarAd.image" :alt="sidebarAd.title || 'Publicité'" class="img-fluid rounded-3 shadow-sm w-100" style="max-height: 280px; object-fit: cover;">
            </a>
            <div v-else-if="sidebarAd.custom_code" v-html="sidebarAd.custom_code"></div>
          </div>

          <!-- 2 RELATED APP TESTS IN SIDEBAR -->
          <div v-if="relatedReviews && relatedReviews.length > 0" class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
              <i class="bi bi-phone text-success me-2"></i>Autres applications recommandées
            </h6>
            <div class="d-flex flex-column gap-3">
              <div v-for="rel in relatedReviews" :key="rel.id" class="border-bottom pb-3">
                <Link :href="'/test-application/' + rel.slug" class="text-decoration-none">
                  <img :src="'/' + (rel.cover_image || 'assets/images/placeholder.jpg')" class="img-fluid rounded-3 mb-2" style="height: 120px; width: 100%; object-fit: cover;">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 hover-success">{{ rel.app_name }}</h6>
                    <span class="badge bg-success rounded-pill">{{ rel.final_score }}/20</span>
                  </div>
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
import { computed } from 'vue'
import { Link, Head, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  review: { type: Object, required: true },
  relatedReviews: { type: Array, default: () => [] },
})

const page = usePage()
const locale = computed(() => page.props.currentLocale || 'fr')

const inArticleAd = computed(() => page.props.adBanners?.in_article?.[0] || null)
const sidebarAd = computed(() => page.props.adBanners?.sidebar?.[0] || null)

const contentWithInArticleAd = computed(() => {
  const content = locale.value === 'en' ? (props.review.content_en || props.review.content_fr) : props.review.content_fr
  if (!content) return ''
  if (!inArticleAd.value) return content

  let adHtml = ''
  if (inArticleAd.value.image) {
    const link = inArticleAd.value.link_url || '#'
    adHtml = `
      <div class="in-article-ad my-4 text-center p-3 bg-light rounded-3 border">
        <span class="text-muted small text-uppercase d-block mb-1" style="font-size: 10px; letter-spacing: 1px;">Publicité Sponsorisée</span>
        <a href="${link}" target="_blank" rel="noopener noreferrer">
          <img src="/${inArticleAd.value.image}" alt="${inArticleAd.value.title || 'Publicité'}" class="img-fluid rounded-3 shadow-sm" style="max-height: 260px; width: auto; object-fit: contain;">
        </a>
      </div>
    `
  } else if (inArticleAd.value.custom_code) {
    adHtml = `
      <div class="in-article-ad my-4 text-center">
        <span class="text-muted small text-uppercase d-block mb-1" style="font-size: 10px; letter-spacing: 1px;">Publicité</span>
        ${inArticleAd.value.custom_code}
      </div>
    `
  }

  if (!adHtml) return content

  const paragraphs = content.split('</p>')
  if (paragraphs.length >= 3) {
    paragraphs.splice(2, 0, adHtml)
    return paragraphs.join('</p>')
  } else {
    return content + adHtml
  }
})
</script>
