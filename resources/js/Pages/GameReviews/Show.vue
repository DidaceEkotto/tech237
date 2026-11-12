<template>
  <FrontLayout>
    <!-- Sticky Reading Progress Bar -->
    <div 
      class="reading-progress-indicator" 
      :style="{ width: readingProgress + '%' }"
      aria-hidden="true"
    ></div>

    <Head>
      <title>Test : {{ review.game_name }} - Note {{ review.final_score }}/20 Tech237 Gaming</title>
      <meta name="description" head-key="description" :content="review.summary_fr || 'Découvrez le test complet de ' + review.game_name" />
      <meta property="og:type" head-key="og:type" content="article" />
      <meta property="og:title" head-key="og:title" :content="'Test : ' + review.game_name + ' (' + review.final_score + '/20)'" />
      <meta property="og:description" head-key="og:description" :content="review.summary_fr || ('Test complet de ' + review.game_name)" />
      <meta property="og:url" head-key="og:url" :content="currentUrl" />
      <meta property="og:image" head-key="og:image" :content="reviewImage" />
      <meta name="twitter:card" head-key="twitter:card" content="summary_large_image" />
      <meta name="twitter:image" head-key="twitter:image" :content="reviewImage" />
    </Head>

    <div class="container my-4">
      
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3 small">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><Link href="/">Accueil</Link></li>
          <li class="breadcrumb-item"><Link href="/tests-jeux-video">Tests Jeux Vidéo</Link></li>
          <li class="breadcrumb-item active">{{ review.game_name }}</li>
        </ol>
      </nav>

      <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
          
          <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm border mb-4">
            
            <!-- Multi-platform badges & Estimated Reading Time -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span v-for="plat in (review.platforms || [review.platform_category])" :key="plat" class="badge bg-dark px-3 py-2">
                  <i class="bi bi-controller me-1 text-danger"></i> {{ plat }}
                </span>
                <span class="badge bg-danger-subtle text-danger px-3 py-2 fw-bold">Test Exclusif Tech237</span>
              </div>
              <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                <i class="bi bi-clock me-1 text-danger"></i>~{{ estimatedReadingTime }} min de lecture
              </span>
            </div>

            <h1 class="display-6 fw-bold text-dark mb-3">
              Test : {{ review.game_name }}
            </h1>

            <p class="lead text-muted border-start border-4 border-danger ps-3 mb-4">
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
                      <span class="badge bg-danger-subtle text-danger small">Voix IA {{ locale === 'en' ? 'EN' : 'FR' }}</span>
                    </div>
                    <div class="small text-muted" style="font-size: 11px;">
                      Durée estimée : ~{{ estimatedReadingTime }} min &bull; {{ speechStatusText }}
                    </div>
                  </div>
                </div>

                <div v-if="isSpeaking" class="d-flex align-items-center gap-2">
                  <button @click="toggleAudioSpeech" class="btn btn-sm rounded-pill px-3 fw-bold" :class="isPaused ? 'btn-success' : 'btn-warning text-dark'">
                    <i class="bi me-1" :class="isPaused ? 'bi-play-fill' : 'bi-pause-fill'"></i>
                    {{ isPaused ? 'Reprendre' : 'Pause' }}
                  </button>
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

            <!-- Cover Image placed before description -->
            <div v-if="review.cover_image" class="mb-4">
              <img :src="'/' + review.cover_image" :alt="review.game_name" class="img-fluid rounded-4 w-100 shadow-sm" style="max-height: 420px; object-fit: cover;">
            </div>

            <!-- Full Review Body (Description / WYSIWYG with In-Article Ad) -->
            <div class="article-body-content mb-5" v-html="contentWithInArticleAd"></div>

            <!-- SCORE SUMMARY CARD (PLACED AT BOTTOM AFTER DESCRIPTION AS REQUESTED) -->
            <div class="p-4 rounded-4 mb-4 text-white shadow" style="background: linear-gradient(135deg, #111116 0%, #1f0b20 100%);">
              <div class="row align-items-center g-3">
                <div class="col-md-4 text-center border-end border-secondary">
                  <span class="text-white-50 small fw-bold text-uppercase d-block mb-1">Note Finale</span>
                  <div class="display-3 fw-bold text-danger">{{ review.final_score }}<small class="fs-5 text-white-50">/20</small></div>
                </div>
                <div class="col-md-8">
                  <div class="row g-2">
                    <div class="col-6">
                      <div class="small text-white-50">Gameplay</div>
                      <div class="fw-bold fs-5">{{ review.score_gameplay }} / 20</div>
                    </div>
                    <div class="col-6">
                      <div class="small text-white-50">Graphismes</div>
                      <div class="fw-bold fs-5">{{ review.score_graphics }} / 20</div>
                    </div>
                    <div class="col-6">
                      <div class="small text-white-50">Accessibilité</div>
                      <div class="fw-bold fs-5">{{ review.score_accessibility }} / 20</div>
                    </div>
                    <div class="col-6">
                      <div class="small text-white-50">Bande Sonore</div>
                      <div class="fw-bold fs-5">{{ review.score_soundtrack }} / 20</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- PROS & CONS (PLACED AT BOTTOM AFTER DESCRIPTION AS REQUESTED) -->
            <div class="row g-3 mb-2">
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

            <!-- VOUS AVEZ AIMÉ CE TEST ? PARTAGEZ ! -->
            <div class="bg-dark text-white p-4 rounded-3 text-center my-4 shadow-sm">
              <h5 class="fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">VOUS AVEZ AIMÉ CE TEST ? PARTAGEZ !</h5>
              <div class="d-flex justify-content-center align-items-center gap-3 fs-5 flex-wrap">
                <a 
                  :href="whatsappShareUrl" 
                  target="_blank" 
                  class="btn btn-sm btn-success rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm"
                  title="Partager sur WhatsApp"
                >
                  <i class="bi bi-whatsapp fs-6"></i>
                  <span>WhatsApp</span>
                </a>
                <button 
                  type="button" 
                  @click="handleCopyLink" 
                  class="btn btn-sm btn-light text-dark rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm"
                  title="Copier le lien direct du test"
                >
                  <i class="bi bi-link-45deg fs-6"></i>
                  <span>Copier le lien</span>
                </button>
                <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(currentUrl)" target="_blank" class="text-white social-share-btn ms-1" title="Partager sur Facebook"><i class="bi bi-facebook"></i></a>
                <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent('Test : ' + review.game_name) + '&url=' + encodeURIComponent(currentUrl)" target="_blank" class="text-white social-share-btn" title="Partager sur X"><i class="bi bi-twitter-x"></i></a>
              </div>
            </div>

            <!-- À PROPOS DE L'AUTEUR (Author Card with Real Avatar & Link to Public Author Profile) -->
            <div class="author-box p-4 rounded-3 text-white mb-4 bg-dark shadow-sm">
              <div class="d-flex align-items-center gap-3 mb-3">
                <img 
                  v-if="review.author?.avatar" 
                  :src="'/' + review.author.avatar" 
                  :alt="review.author.name" 
                  class="rounded-circle object-fit-cover border border-2 border-white"
                  style="width: 56px; height: 56px;"
                >
                <div v-else class="rounded-circle bg-secondary text-white fw-bold d-flex align-items-center justify-content-center fs-4 border border-2 border-white" style="width: 56px; height: 56px;">
                  {{ (review.author?.name || 'M').charAt(0) }}
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-white fs-5">{{ review.author?.name || 'Rédaction Tech237' }}</h6>
                    <span class="badge bg-secondary small">{{ review.author?.role || 'Auteur' }}</span>
                  </div>
                  <p class="small text-white-50 mb-0 mt-1">
                    {{ review.author?.bio || 'Journaliste tech & pop culture passionné par les nouvelles technologies, le cinéma et les jeux vidéo.' }}
                  </p>
                  <Link v-if="review.author?.id" :href="'/auteur/' + review.author.id" class="small text-danger fw-bold text-decoration-none mt-1 d-inline-block">
                    Voir la page de l'auteur &rarr;
                  </Link>
                </div>
              </div>

              <!-- Author's Other Game Reviews -->
              <div v-if="authorReviews && authorReviews.length" class="border-top border-secondary pt-3 mt-2">
                <span class="small fw-bold text-white-50 text-uppercase d-block mb-2">Autres tests de cet auteur :</span>
                <ul class="list-unstyled small mb-0">
                  <li v-for="autRev in authorReviews" :key="autRev.id" class="mb-1 text-truncate">
                    <Link :href="'/test-jeu/' + autRev.slug" class="text-white text-decoration-none hover-underline">
                      &rarr; {{ autRev.game_name }} ({{ autRev.final_score }}/20)
                    </Link>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Previous and Next Game Review Navigation -->
            <div class="row g-3 my-4">
              <div class="col-md-6" v-if="prevReview">
                <Link :href="'/test-jeu/' + prevReview.slug" class="card h-100 p-3 text-decoration-none border shadow-sm rounded-3 article-nav-card">
                  <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-arrow-left-circle fs-3 text-danger"></i>
                    <div>
                      <span class="small text-muted text-uppercase fw-bold d-block" style="font-size: 10px;">Test précédent</span>
                      <h6 class="fw-bold text-dark mb-0 small text-truncate" style="max-width: 240px;">
                        {{ prevReview.game_name }}
                      </h6>
                    </div>
                  </div>
                </Link>
              </div>
              <div class="col-md-6" :class="{ 'offset-md-6': !prevReview }" v-if="nextReview">
                <Link :href="'/test-jeu/' + nextReview.slug" class="card h-100 p-3 text-decoration-none border shadow-sm rounded-3 article-nav-card text-end">
                  <div class="d-flex align-items-center justify-content-end gap-3">
                    <div>
                      <span class="small text-muted text-uppercase fw-bold d-block" style="font-size: 10px;">Test suivant</span>
                      <h6 class="fw-bold text-dark mb-0 small text-truncate" style="max-width: 240px;">
                        {{ nextReview.game_name }}
                      </h6>
                    </div>
                    <i class="bi bi-arrow-right-circle fs-3 text-danger"></i>
                  </div>
                </Link>
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
              <h6 class="fw-bold text-dark mb-2">Donnez votre avis sur ce test</h6>
              <div @click="checkAuthForComment">
                <textarea 
                  v-model="newCommentContent" 
                  rows="3" 
                  class="form-control bg-white border-0 shadow-none mb-2" 
                  placeholder="Partagez votre retour sur ce jeu vidéo..."
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
              Soyez le premier à commenter ce test !
            </div>
          </div>

        </div>

        <!-- Sidebar Column: Proposes 2 Related Tests as Requested -->
        <div class="col-lg-4">
          
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3">Verdict Tech237 Gaming</h5>
            <div class="text-center p-3 bg-light rounded-4 mb-3 border">
              <span class="badge bg-danger fs-3 px-3 py-2 rounded-pill">{{ review.final_score }} / 20</span>
              <div class="small text-muted mt-2">Test rédigé par {{ review.author?.name || 'Rédaction Tech237' }}</div>
            </div>
            <Link href="/tests-jeux-video" class="btn btn-outline-danger w-100 rounded-pill fw-bold">
              Tous les tests jeux vidéo
            </Link>
          </div>

          <!-- Sondage Tech du Jour -->
          <DailyTechPoll />

          <!-- Sidebar Ad Banner (Pavé 300x250) -->
          <div v-if="sidebarAd" class="card rounded-4 p-3 mb-4 border-0 shadow-sm text-center bg-light">
            <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 9px; letter-spacing: 1px;">Sponsor &bull; Partenaire</div>
            <a v-if="sidebarAd.image" :href="sidebarAd.link_url || '#'" target="_blank" rel="noopener noreferrer">
              <img :src="'/' + sidebarAd.image" :alt="sidebarAd.title || 'Publicité'" class="img-fluid rounded-3 shadow-sm w-100" style="max-height: 280px; object-fit: cover;">
            </a>
            <div v-else-if="sidebarAd.custom_code" v-html="sidebarAd.custom_code"></div>
          </div>

          <!-- 2 RELATED TESTS IN SIDEBAR (Requested by user) -->
          <div v-if="relatedReviews && relatedReviews.length > 0" class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
              <i class="bi bi-controller text-danger me-2"></i>Autres tests recommandés
            </h6>
            <div class="d-flex flex-column gap-3">
              <div v-for="rel in relatedReviews" :key="rel.id" class="border-bottom pb-3">
                <Link :href="'/test-jeu/' + rel.slug" class="text-decoration-none">
                  <img :src="'/' + (rel.cover_image || 'assets/images/placeholder.jpg')" class="img-fluid rounded-3 mb-2" style="height: 120px; width: 100%; object-fit: cover;">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 hover-danger">{{ rel.game_name }}</h6>
                    <span class="badge bg-danger rounded-pill">{{ rel.final_score }}/20</span>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, Head, usePage, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import { useAudioNarrator } from '@/Composables/useAudioNarrator'
import DailyTechPoll from '@/Components/DailyTechPoll.vue'
import { copyLinkWithToast, buildWhatsAppShareUrl } from '@/Utils/clipboard'

const props = defineProps({
  review: { type: Object, required: true },
  relatedReviews: { type: Array, default: () => [] },
  prevReview: { type: Object, default: null },
  nextReview: { type: Object, default: null },
  authorReviews: { type: Array, default: () => [] },
})

const page = usePage()
const locale = computed(() => page.props.currentLocale || 'fr')
const authUser = computed(() => page.props.auth?.user || null)

// Reading progress bar (0 to 100%)
const readingProgress = ref(0)
function updateReadingProgress() {
  if (typeof window === 'undefined') return
  const docEl = document.documentElement
  const scrollTop = window.scrollY || docEl.scrollTop || 0
  const scrollHeight = (docEl.scrollHeight || 0) - window.innerHeight
  if (scrollHeight <= 0) {
    readingProgress.value = 0
    return
  }
  readingProgress.value = Math.min(100, Math.max(0, Math.round((scrollTop / scrollHeight) * 100)))
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('scroll', updateReadingProgress, { passive: true })
    updateReadingProgress()
  }
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('scroll', updateReadingProgress)
  }
})

const currentUrl = computed(() => {
  return typeof window !== 'undefined' ? window.location.href : (page.props.seo?.url || '')
})

const reviewImage = computed(() => {
  const origin = typeof window !== 'undefined' ? window.location.origin : ''
  if (props.review.cover_image) {
    const img = props.review.cover_image
    if (img.startsWith('http://') || img.startsWith('https://')) return img
    return origin + '/' + img.replace(/^\/+/, '')
  }
  return origin + '/assets/images/logo.png'
})

const whatsappShareUrl = computed(() => {
  return buildWhatsAppShareUrl(`Test : ${props.review.game_name} (${props.review.final_score}/20) - Tech237`, currentUrl.value)
})

function handleCopyLink() {
  copyLinkWithToast(currentUrl.value, 'Lien du test copié !')
}

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
  const fullText = (props.review.game_name + '. ' + (currentSummary.value || '') + '. ' + (currentContent.value || ''))
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
    game_review_id: props.review.id,
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
    game_review_id: props.review.id,
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
.reading-progress-indicator {
  position: fixed;
  top: 0;
  left: 0;
  height: 4px;
  background: linear-gradient(90deg, #FF004B 0%, #9be106 100%);
  z-index: 1060;
  transition: width 0.08s ease-out;
  box-shadow: 0 1px 4px rgba(255, 0, 75, 0.4);
}
.hover-danger:hover {
  color: #FF004B !important;
}

.article-nav-card {
  transition: all 0.2s ease-in-out;
}
.article-nav-card:hover {
  border-color: #FF004B !important;
  background-color: #fffafb;
  transform: translateY(-2px);
}
.hover-underline:hover {
  text-decoration: underline !important;
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
