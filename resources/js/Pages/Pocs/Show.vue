<template>
  <FrontLayout>
    <Head>
      <title>{{ poc.title }} | Mini-Actualité POC Tech237</title>
      <meta name="description" :content="poc.summary || poc.title">
      <meta property="og:title" :content="poc.title + ' | POC Tech237'">
      <meta property="og:description" :content="poc.summary || poc.title">
      <meta v-if="poc.cover_image" property="og:image" :content="'/' + poc.cover_image">
    </Head>

    <div class="poc-show-page py-5 bg-light min-vh-100">
      <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/pocs" class="text-decoration-none text-muted">POCs</Link></li>
            <li class="breadcrumb-item active text-dark fw-bold text-truncate" style="max-width: 300px;">{{ poc.title }}</li>
          </ol>
        </nav>

        <div class="row g-4">
          
          <!-- Main Content Column (8 cols) -->
          <div class="col-lg-8">
            
            <article class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
              
              <!-- Tag & Meta Header -->
              <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <span class="badge rounded-pill px-3 py-1 text-uppercase fw-bold" :class="getTagBadgeClass(poc.tag)">
                  #{{ poc.tag }}
                </span>
                <span class="text-muted extra-small">
                  <i class="bi bi-clock me-1"></i> Publié {{ formatFullDate(poc.published_at || poc.created_at) }} &bull;
                  <i class="bi bi-eye ms-2 me-1"></i> {{ poc.views_count }} lecture(s)
                </span>
              </div>

              <!-- Main Title -->
              <h1 class="h2 fw-black text-dark mb-4 leading-tight">
                {{ poc.title }}
              </h1>

              <!-- Author Info Bar -->
              <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-4 mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                  <Link v-if="poc.user" :href="'/auteur/' + poc.user.id" class="text-decoration-none">
                    <img 
                      v-if="poc.user.avatar" 
                      :src="'/' + poc.user.avatar" 
                      class="rounded-circle border" 
                      style="width: 48px; height: 48px; object-fit: cover;"
                      :alt="poc.user.name"
                    />
                    <div 
                      v-else 
                      class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center shadow-sm"
                      style="width: 48px; height: 48px; font-size: 1.2rem;"
                    >
                      {{ (poc.user.first_name || poc.user.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                  </Link>
                  <div 
                    v-else 
                    class="rounded-circle bg-secondary text-white fw-bold d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 48px; height: 48px; font-size: 1.2rem;"
                  >
                    {{ (poc.author_name || 'C').charAt(0).toUpperCase() }}
                  </div>

                  <div>
                    <div class="fw-bold text-dark">
                      {{ poc.user ? (poc.user.first_name ? poc.user.first_name + ' ' + poc.user.name : poc.user.name) : (poc.author_name || 'Contributeur Tech') }}
                    </div>
                    <small class="text-muted extra-small">
                      {{ poc.user ? 'Membre de la communauté' : 'Auteur invité' }}
                    </small>
                  </div>
                </div>

                <!-- Upvote Action in Header -->
                <div>
                  <button 
                    type="button" 
                    class="btn btn-sm rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-xs transition"
                    :class="hasUpvotedState ? 'btn-danger text-white' : 'btn-outline-danger'"
                    @click="toggleUpvote"
                  >
                    <i class="bi" :class="hasUpvotedState ? 'bi-fire' : 'bi-heart'"></i>
                    <span>{{ hasUpvotedState ? 'Vous aimez' : 'Booster ce POC' }}</span>
                    <span class="badge bg-white text-danger rounded-pill ms-1">{{ upvotesCount }}</span>
                  </button>
                </div>
              </div>

              <!-- Cover Image if present -->
              <div v-if="poc.cover_image" class="mb-4 rounded-4 overflow-hidden shadow-sm border">
                <img :src="'/' + poc.cover_image" class="w-100 h-auto object-fit-cover" :alt="poc.title" style="max-height: 440px;">
              </div>

              <!-- WYSIWYG Content Body -->
              <div class="poc-content article-content mb-4" v-html="poc.content"></div>

              <!-- External Source Link Button -->
              <div v-if="poc.link_url" class="p-3 bg-light rounded-4 border d-flex align-items-center justify-content-between flex-wrap gap-2 my-4">
                <div class="d-flex align-items-center gap-2">
                  <i class="bi bi-box-arrow-up-right fs-4 text-primary"></i>
                  <div>
                    <strong class="d-block text-dark small">Lien et ressource associée :</strong>
                    <span class="text-muted extra-small text-break">{{ poc.link_url }}</span>
                  </div>
                </div>
                <a :href="poc.link_url" target="_blank" rel="noopener noreferrer" class="btn btn-primary rounded-pill btn-sm px-4 fw-bold">
                  Accéder à la source &rarr;
                </a>
              </div>

              <!-- Social Sharing & Interactive Footer -->
              <div class="pt-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                  <span class="small text-muted fw-bold">Partager :</span>
                  <a :href="'https://wa.me/?text=' + encodeURIComponent(poc.title + ' sur Tech237 : ' + shareUrl)" target="_blank" class="btn btn-sm btn-outline-success rounded-circle" style="width: 36px; height: 36px;" title="WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                  </a>
                  <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent(poc.title) + '&url=' + encodeURIComponent(shareUrl)" target="_blank" class="btn btn-sm btn-outline-dark rounded-circle" style="width: 36px; height: 36px;" title="X (Twitter)">
                    <i class="bi bi-twitter-x"></i>
                  </a>
                  <a :href="'https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(shareUrl)" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 36px; height: 36px;" title="LinkedIn">
                    <i class="bi bi-linkedin"></i>
                  </a>
                  <button @click="copyShareLink" class="btn btn-sm btn-light border rounded-circle" style="width: 36px; height: 36px;" title="Copier le lien">
                    <i class="bi bi-link-45deg"></i>
                  </button>
                  <span v-if="copySuccess" class="text-success extra-small fw-bold animate__animated animate__fadeIn">Lien copié !</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                  <button 
                    type="button" 
                    class="btn btn-sm rounded-pill px-4 fw-bold d-flex align-items-center gap-2"
                    :class="hasUpvotedState ? 'btn-danger text-white' : 'btn-outline-danger'"
                    @click="toggleUpvote"
                  >
                    <i class="bi" :class="hasUpvotedState ? 'bi-fire' : 'bi-heart'"></i>
                    <span>{{ upvotesCount }} Upvotes</span>
                  </button>
                </div>
              </div>

            </article>

            <!-- COMMENTS SECTION -->
            <div id="commentaires" class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
              <h4 class="fw-bold text-dark mb-4 d-flex align-items-center">
                <i class="bi bi-chat-left-dots-fill text-danger me-2"></i>
                Commentaires &amp; Retours ({{ poc.comments?.length || 0 }})
              </h4>

              <!-- Post Comment Form (Only for logged-in users with active accounts) -->
              <form v-if="$page.props.auth?.user && !$page.props.auth?.user?.is_banned" @submit.prevent="submitComment" class="mb-5">
                <div class="mb-3">
                  <textarea 
                    v-model="commentForm.content" 
                    class="form-control rounded-3 p-3" 
                    rows="3" 
                    placeholder="Posez une question, partagez votre avis ou félicitez l'auteur pour ce POC..."
                    required
                  ></textarea>
                  <div v-if="commentForm.errors.content" class="text-danger small mt-1">{{ commentForm.errors.content }}</div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <small class="text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i> Échanges bienveillants et constructifs &bull; +3 XP
                  </small>
                  <button 
                    type="submit" 
                    class="btn rounded-pill px-4 fw-bold text-white shadow-sm"
                    style="background-color: var(--tech-green, #00A859);"
                    :disabled="commentForm.processing || !commentForm.content"
                  >
                    <span v-if="commentForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-send-fill me-1"></i> Publier mon commentaire
                  </button>
                </div>
              </form>

              <!-- Authentication required callout -->
              <div v-else class="card border-0 bg-light rounded-4 p-4 mb-5 text-center shadow-xs">
                <div class="mb-2 text-primary fs-3">
                  <i class="bi bi-lock-fill"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Connectez-vous pour participer à la discussion</h5>
                <p class="text-muted small mb-3" style="max-width: 500px; margin: 0 auto;">
                  Pour commenter cette mini-actualité (POC) et gagner des points d'expérience (XP), vous devez avoir un compte actif sur Tech237.
                </p>
                <div class="d-flex justify-content-center gap-2">
                  <Link href="/login" class="btn btn-sm btn-dark rounded-pill px-4 fw-bold shadow-xs">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
                  </Link>
                  <Link href="/register" class="btn btn-sm btn-outline-success rounded-pill px-4 fw-bold shadow-xs">
                    Créer un compte
                  </Link>
                </div>
              </div>

              <!-- Comments List -->
              <div v-if="poc.comments && poc.comments.length > 0" class="d-flex flex-column gap-3">
                <div 
                  v-for="comm in poc.comments" 
                  :key="comm.id" 
                  class="p-3 rounded-4 bg-light border"
                >
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <img 
                        v-if="comm.user?.avatar" 
                        :src="'/' + comm.user.avatar" 
                        class="rounded-circle border" 
                        style="width: 32px; height: 32px; object-fit: cover;"
                      />
                      <div 
                        v-else 
                        class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center" 
                        style="width: 32px; height: 32px; font-size: 11px;"
                      >
                        {{ (comm.user?.name || 'U').charAt(0).toUpperCase() }}
                      </div>
                      <span class="fw-bold small text-dark">{{ comm.user?.name || 'Membre Tech' }}</span>
                      <span v-if="comm.user_id === poc.user_id" class="badge bg-warning-subtle text-dark extra-small rounded-pill">Auteur</span>
                    </div>
                    <small class="text-muted extra-small">
                      {{ formatTimeAgo(comm.created_at) }}
                    </small>
                  </div>
                  <p class="small text-secondary mb-0 whitespace-pre-line ps-4">
                    {{ comm.content }}
                  </p>
                </div>
              </div>

              <!-- No comments yet -->
              <div v-else class="text-center py-4 text-muted small bg-light rounded-4">
                <i class="bi bi-chat-square text-secondary fs-3 d-block mb-1"></i>
                Soyez le premier à commenter ce POC !
              </div>

            </div>

          </div>

          <!-- Right Sidebar (4 cols) -->
          <div class="col-lg-4">
            
            <!-- Propose POC CTA Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #00A859 0%, #0B2559 100%);">
              <h5 class="fw-bold mb-2"><i class="bi bi-plus-circle me-1"></i>Vous aussi, partagez !</h5>
              <p class="small text-white-50 mb-3">
                Vous développez un outil, une lib, une app ou vous avez une actualité ? Proposez votre POC en direct.
              </p>
              <Link href="/pocs/proposer" class="btn btn-light rounded-pill w-100 fw-bold shadow-sm" style="color: #0B2559;">
                <i class="bi bi-pencil-square me-1"></i> Proposer un POC
              </Link>
            </div>

            <!-- Related POCs -->
            <div v-if="relatedPocs && relatedPocs.length > 0" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Autres POCs #{{ poc.tag }}
              </h6>
              <div class="d-flex flex-column gap-3">
                <div v-for="rel in relatedPocs" :key="rel.id" class="border-bottom pb-2">
                  <Link :href="'/pocs/' + (rel.slug || rel.id)" class="fw-semibold small text-dark text-decoration-none hover-danger d-block mb-1">
                    {{ rel.title }}
                  </Link>
                  <small class="text-muted extra-small">
                    {{ formatTimeAgo(rel.published_at || rel.created_at) }} &bull; {{ rel.upvotes_count }} upvotes
                  </small>
                </div>
              </div>
            </div>

            <!-- Daily Tech Poll -->
            <DailyTechPoll />

          </div>
        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import DailyTechPoll from '@/Components/DailyTechPoll.vue'

const props = defineProps({
  poc: Object,
  relatedPocs: Array,
})

const hasUpvotedState = ref(props.poc?.has_upvoted || false)
const upvotesCount = ref(props.poc?.upvotes_count || 0)
const isUpvoting = ref(false)
const copySuccess = ref(false)

const shareUrl = computed(() => {
  return typeof window !== 'undefined' ? window.location.href : ''
})

const commentForm = useForm({
  content: '',
})

function getTagBadgeClass(tag) {
  const map = {
    ia: 'bg-primary-subtle text-primary',
    fintech: 'bg-success-subtle text-success',
    dev: 'bg-warning-subtle text-dark',
    startup: 'bg-danger-subtle text-danger',
    cybersec: 'bg-dark text-white',
    mobile: 'bg-info-subtle text-info',
  }
  return map[tag] || 'bg-secondary-subtle text-secondary'
}

function formatFullDate(val) {
  if (!val) return ''
  const date = new Date(val)
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function formatTimeAgo(val) {
  if (!val) return ''
  const date = new Date(val)
  const now = new Date()
  const diffSec = Math.floor((now - date) / 1000)

  if (diffSec < 60) return 'À l\'instant'
  if (diffSec < 3600) return `Il y a ${Math.floor(diffSec / 60)} min`
  if (diffSec < 86400) return `Il y a ${Math.floor(diffSec / 3600)} h`
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}

function copyShareLink() {
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(shareUrl.value)
    copySuccess.value = true
    setTimeout(() => {
      copySuccess.value = false
    }, 3000)
  }
}

function toggleUpvote() {
  if (isUpvoting.value) return
  isUpvoting.value = true

  const guestToken = typeof window !== 'undefined' ? localStorage.getItem('tech237_poll_guest_token') : null

  fetch(`/pocs/${props.poc.id}/upvote`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify({
      guest_token: guestToken,
    })
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      hasUpvotedState.value = data.upvoted
      upvotesCount.value = data.upvotes_count
    }
  }).catch(e => {
    console.error('Upvote error:', e)
  }).finally(() => {
    isUpvoting.value = false
  })
}

function submitComment() {
  if (!commentForm.content) return

  commentForm.post(`/pocs/${props.poc.id}/commentaires`, {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset()
    }
  })
}
</script>
