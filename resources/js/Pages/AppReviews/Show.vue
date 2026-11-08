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

            <!-- Audio Narrator (TTS Player) -->
            <div class="audio-narrator-card p-3 rounded-4 mb-4 border shadow-sm bg-light">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                  <button 
                    @click="toggleAudioSpeech" 
                    class="btn rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm"
                    :class="isSpeaking ? 'btn-danger' : 'btn-dark'"
                    style="width: 48px; height: 48px;"
                    title="Écouter le test"
                  >
                    <i class="bi fs-4" :class="isSpeaking && !isPaused ? 'bi-pause-fill' : 'bi-play-fill'"></i>
                  </button>
                  <div>
                    <div class="fw-bold small text-dark d-flex align-items-center gap-2">
                      <span>Écouter le test audio</span>
                      <span class="badge bg-success-subtle text-success small">Voix IA {{ locale === 'en' ? 'EN' : 'FR' }}</span>
                    </div>
                    <div class="small text-muted" style="font-size: 11px;">
                      Durée estimée : ~{{ estimatedReadingTime }} min &bull; {{ speechStatusText }}
                    </div>
                  </div>
                </div>

                <div v-if="isSpeaking" class="d-flex align-items-center gap-2">
                  <button @click="stopAudioSpeech" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-stop-fill me-1"></i> Arrêter
                  </button>
                </div>
              </div>

              <!-- Animated Progress Bar during playback -->
              <div v-if="isSpeaking" class="progress mt-3" style="height: 4px;">
                <div 
                  class="progress-bar bg-danger progress-bar-striped progress-bar-animated" 
                  role="progressbar" 
                  :style="{ width: speechProgress + '%' }"
                ></div>
              </div>
            </div>

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

          <!-- COMMENTS SECTION (With 1-Level Replies & Login Guard Modal) -->
          <div class="comments-section mt-5 pt-4 border-top">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-chat-left-text-fill text-danger me-2"></i>
                Commentaires ({{ totalCommentsCount }})
              </h4>
            </div>

            <!-- Flash success message -->
            <div v-if="commentSuccessMessage" class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i> {{ commentSuccessMessage }}
              <button type="button" class="btn-close" @click="commentSuccessMessage = ''"></button>
            </div>

            <!-- Add Comment Input Box -->
            <div class="card border-0 shadow-sm bg-light p-3 rounded-4 mb-4">
              <h6 class="fw-bold text-dark mb-2">Donnez votre avis sur cette application</h6>
              <div @click="checkAuthForComment">
                <textarea 
                  v-model="newCommentContent" 
                  rows="3" 
                  class="form-control bg-white border-0 shadow-none mb-2" 
                  placeholder="Partagez votre retour d'expérience avec la communauté..."
                  :disabled="!authUser"
                ></textarea>
              </div>
              <div class="d-flex justify-content-end">
                <button 
                  @click="submitComment" 
                  class="btn rounded-pill px-4 fw-bold text-white shadow-sm btn-sm"
                  style="background-color: var(--tech-green);"
                  :disabled="isSubmittingComment"
                >
                  <i class="bi bi-send me-1"></i> Publier
                </button>
              </div>
            </div>

            <!-- Comments List -->
            <div v-if="review.comments && review.comments.length" class="comments-list">
              <div v-for="comment in review.comments" :key="comment.id" class="card border shadow-sm p-3 rounded-4 mb-3 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <img 
                      v-if="comment.user?.avatar" 
                      :src="'/' + comment.user.avatar" 
                      :alt="comment.user.name" 
                      class="rounded-circle object-fit-cover" 
                      style="width: 38px; height: 38px;"
                    >
                    <div v-else class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 13px;">
                      {{ (comment.user?.name || 'U').charAt(0) }}
                    </div>
                    <div>
                      <div class="fw-bold small text-dark">{{ comment.user?.name || 'Membre de la communauté' }}</div>
                      <div class="text-muted small" style="font-size: 11px;">{{ new Date(comment.created_at).toLocaleDateString('fr-FR') }}</div>
                    </div>
                  </div>
                  <button 
                    @click="openReplyForm(comment.id)" 
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-0"
                    style="font-size: 12px;"
                  >
                    <i class="bi bi-reply me-1"></i> Répondre
                  </button>
                </div>

                <p class="text-dark small mb-2 ps-1">{{ comment.content }}</p>

                <!-- 1-Level Reply Box if toggled -->
                <div v-if="replyingToId === comment.id" class="mt-3 ps-3 border-start border-3 border-danger">
                  <div class="card p-2 bg-light border-0 rounded-3">
                    <textarea 
                      v-model="replyContent" 
                      rows="2" 
                      class="form-control form-control-sm bg-white mb-2" 
                      placeholder="Rédigez votre réponse..."
                    ></textarea>
                    <div class="d-flex gap-2 justify-content-end">
                      <button @click="replyingToId = null" class="btn btn-sm btn-light border py-1">Annuler</button>
                      <button @click="submitReply(comment.id)" class="btn btn-sm btn-danger py-1 px-3">Envoyer la réponse</button>
                    </div>
                  </div>
                </div>

                <!-- Nested Replies (1-Level) -->
                <div v-if="comment.replies && comment.replies.length" class="mt-3 ps-3 border-start border-2 border-danger-subtle">
                  <div v-for="reply in comment.replies" :key="reply.id" class="bg-light p-2 rounded-3 mb-2">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 10px;">
                        {{ (reply.user?.name || 'U').charAt(0) }}
                      </div>
                      <span class="fw-semibold small text-dark">{{ reply.user?.name || 'Membre' }}</span>
                      <span class="text-muted small ms-auto" style="font-size: 10px;">{{ new Date(reply.created_at).toLocaleDateString('fr-FR') }}</span>
                    </div>
                    <p class="small text-muted mb-0 ps-4">{{ reply.content }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-4 text-muted small bg-light rounded-4">
              Soyez le premier à commenter ce test applicatif !
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

    <!-- Static Login Modal when Guest attempts to Comment -->
    <div v-if="showLoginModal" class="login-modal-backdrop" @click.self="showLoginModal = false">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 p-4 text-center bg-white">
          <div class="rounded-circle bg-danger-subtle text-danger mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="bi bi-chat-heart-fill fs-3"></i>
          </div>
          <h5 class="fw-bold mb-2">Rejoignez la discussion !</h5>
          <p class="text-muted small mb-4">
            Pour publier un commentaire et échanger avec la communauté Tech237, vous devez posséder un compte vérifié.
          </p>
          <div class="d-grid gap-2">
            <Link :href="'/login?redirect=' + encodeURIComponent(currentUrl)" class="btn rounded-pill fw-bold py-2 text-white shadow-sm" style="background-color: var(--tech-green);">
              <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
            </Link>
            <Link :href="'/register?redirect=' + encodeURIComponent(currentUrl)" class="btn btn-outline-dark rounded-pill fw-bold py-2">
              Créer un compte
            </Link>
          </div>
          <button @click="showLoginModal = false" class="btn btn-link text-muted small mt-3 text-decoration-none">
            Continuer la lecture sans commenter
          </button>
        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, Head, usePage, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import { useAudioNarrator } from '@/Composables/useAudioNarrator'

const props = defineProps({
  review: { type: Object, required: true },
  relatedReviews: { type: Array, default: () => [] },
})

const page = usePage()
const locale = computed(() => page.props.currentLocale || 'fr')
const authUser = computed(() => page.props.auth?.user || null)

const currentUrl = computed(() => {
  return typeof window !== 'undefined' ? window.location.href : ''
})

const currentContent = computed(() => {
  return locale.value === 'en' ? (props.review.content_en || props.review.content_fr) : props.review.content_fr
})

const currentSummary = computed(() => {
  return locale.value === 'en' ? (props.review.summary_en || props.review.summary_fr) : props.review.summary_fr
})

// Audio Narrator using robust Composable (Cross-browser, Mobile & Chunked)
const {
  isSpeaking,
  isPaused,
  speechProgress,
  statusText: speechStatusText,
  toggle: toggleNarrator,
  stop: stopAudioSpeech,
  calculateEstimatedMinutes
} = useAudioNarrator()

const estimatedReadingTime = computed(() => {
  return calculateEstimatedMinutes(currentContent.value)
})

function toggleAudioSpeech() {
  const fullText = (props.review.app_name + '. ' + (currentSummary.value || '') + '. ' + (currentContent.value || ''))
  const lang = locale.value === 'en' ? 'en-US' : 'fr-FR'
  toggleNarrator(fullText, lang)
}

// In-Article Ad Banner
const inArticleAd = computed(() => page.props.adBanners?.in_article?.[0] || null)
const sidebarAd = computed(() => page.props.adBanners?.sidebar?.[0] || null)

const contentWithInArticleAd = computed(() => {
  const content = currentContent.value
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

// Comments System
const showLoginModal = ref(false)
const newCommentContent = ref('')
const isSubmittingComment = ref(false)
const commentSuccessMessage = ref('')
const replyingToId = ref(null)
const replyContent = ref('')

const totalCommentsCount = computed(() => {
  if (!props.review.comments) return 0
  let count = props.review.comments.length
  props.review.comments.forEach(c => {
    if (c.replies) count += c.replies.length
  })
  return count
})

function checkAuthForComment() {
  if (!authUser.value) {
    showLoginModal.value = true
  }
}

function openReplyForm(commentId) {
  if (!authUser.value) {
    showLoginModal.value = true
    return
  }
  replyingToId.value = commentId
  replyContent.value = ''
}

async function submitComment() {
  if (!authUser.value) {
    showLoginModal.value = true
    return
  }
  if (!newCommentContent.value || newCommentContent.value.trim().length < 2) return

  isSubmittingComment.value = true
  router.post('/api/comments', {
    app_review_id: props.review.id,
    content: newCommentContent.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      newCommentContent.value = ''
      commentSuccessMessage.value = 'Votre commentaire a été envoyé pour modération par l\'équipe Tech237.'
    },
    onFinish: () => {
      isSubmittingComment.value = false
    }
  })
}

async function submitReply(parentId) {
  if (!authUser.value) {
    showLoginModal.value = true
    return
  }
  if (!replyContent.value || replyContent.value.trim().length < 2) return

  router.post('/api/comments', {
    app_review_id: props.review.id,
    parent_id: parentId,
    content: replyContent.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      replyContent.value = ''
      replyingToId.value = null
      commentSuccessMessage.value = 'Votre réponse a été soumise avec succès pour modération.'
    }
  })
}
</script>

<style scoped>
.hover-success:hover {
  color: #00A859 !important;
}

.login-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1055;
  padding: 1rem;
}
</style>
