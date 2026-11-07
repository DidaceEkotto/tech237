<template>
  <FrontLayout>
    <Head>
      <title>{{ topic.title }} | Forum Tech237</title>
      <meta name="description" :content="topic.title">
      <meta property="og:title" :content="topic.title + ' | Forum Tech237'">
    </Head>

    <div class="forum-topic-page py-5">
      <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/forum" class="text-decoration-none text-muted">Forum</Link></li>
            <li class="breadcrumb-item">
              <span class="badge rounded-pill" :style="{ backgroundColor: topic.category?.color || '#FF004B', color: 'white' }">
                {{ topic.category?.name || topic.category?.name_fr }}
              </span>
            </li>
          </ol>
        </nav>

        <div class="row g-4">
          <!-- Main Content (8 cols) -->
          <div class="col-lg-8">
            
            <!-- Topic Main Post Box -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
              <div class="card-header bg-light border-0 p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <span v-if="topic.is_solved" class="badge bg-success px-3 py-2 rounded-pill fw-bold">
                      <i class="bi bi-check-circle-fill me-1"></i> Résolu
                    </span>
                    <span v-if="topic.is_pinned" class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                      <i class="bi bi-pin-angle-fill me-1"></i> Épinglé
                    </span>
                    <span v-if="topic.is_closed" class="badge bg-secondary px-3 py-2 rounded-pill fw-bold">
                      <i class="bi bi-lock-fill me-1"></i> Réponses closes
                    </span>
                  </div>

                  <!-- Creator Action: Toggle Solved (Blocks new replies when solved) -->
                  <div v-if="canSolve" class="d-flex gap-2">
                    <button 
                      @click="toggleSolveTopic" 
                      class="btn btn-sm rounded-pill fw-bold"
                      :class="topic.is_solved ? 'btn-outline-secondary' : 'btn-success'"
                    >
                      <i class="bi" :class="topic.is_solved ? 'bi-arrow-counterclockwise' : 'bi-check-circle-fill'"></i>
                      {{ topic.is_solved ? 'Rouvrir la discussion' : 'Marquer comme Résolu' }}
                    </button>
                  </div>
                </div>

                <h1 class="h3 fw-black text-dark mb-3">{{ topic.title }}</h1>

                <!-- Author info -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
                  <div class="d-flex align-items-center gap-3">
                    <Link :href="'/forum/auteur/' + topic.user?.id">
                      <img 
                        :src="'/' + (topic.user?.avatar || 'assets/images/default-avatar.png')" 
                        class="rounded-circle border" 
                        style="width: 44px; height: 44px; object-fit: cover;"
                      >
                    </Link>
                    <div>
                      <div class="fw-bold text-dark">
                        <Link :href="'/forum/auteur/' + topic.user?.id" class="text-dark text-decoration-none hover-danger">
                          {{ topic.user?.name || 'Membre' }}
                        </Link>
                      </div>
                      <small class="text-muted">
                        Inscrit {{ new Date(topic.user?.created_at || Date.now()).toLocaleDateString('fr-FR') }} &bull;
                        {{ topic.user?.forum_topics_count || 1 }} sujet(s) créé(s)
                      </small>
                    </div>
                  </div>

                  <div class="d-flex align-items-center gap-2">
                    <Link :href="'/forum/auteur/' + topic.user?.id" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                      <i class="bi bi-chat-left-text me-1"></i> Voir profil & Contact
                    </Link>
                    <button @click="openReportModal(topic.id, null)" class="btn btn-sm btn-light border text-muted" title="Signaler un abus">
                      <i class="bi bi-flag"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Topic Content Body -->
              <div class="card-body p-4">
                <div class="topic-content text-dark fs-6" style="line-height: 1.8; white-space: pre-line;">
                  {{ topic.content }}
                </div>
              </div>
            </div>

            <!-- Responses Section Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-chat-quote-fill text-danger me-2"></i>Réponses ({{ posts.length }})
              </h4>
              <small class="text-muted">Triées par réponses les plus appréciées</small>
            </div>

            <!-- Solved Notice Banner if topic is solved -->
            <div v-if="topic.is_solved" class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
              <i class="bi bi-check-circle-fill fs-3 text-success"></i>
              <div>
                <strong class="d-block">Ce sujet a été marqué comme résolu par son créateur.</strong>
                <small class="text-muted">Les nouvelles réponses sont closes pour conserver cette solution propre et efficace.</small>
              </div>
            </div>

            <!-- Responses List -->
            <div class="d-flex flex-column gap-3 mb-5">
              <div 
                v-for="(post, pIdx) in posts" 
                :key="post.id"
                class="card border-0 shadow-sm rounded-4 p-4 bg-white"
                :class="{ 'border-start border-4 border-success bg-success-subtle-light': pIdx === 0 && post.likes_count > 0 }"
              >
                <!-- Highlight badge for best answer -->
                <div v-if="pIdx === 0 && post.likes_count > 0" class="mb-2">
                  <span class="badge bg-success px-3 py-1 rounded-pill fw-bold">
                    <i class="bi bi-trophy-fill me-1"></i> Réponse la plus appréciée de la communauté
                  </span>
                </div>

                <!-- Post Author Header -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div class="d-flex align-items-center gap-3">
                    <Link :href="'/forum/auteur/' + post.user?.id">
                      <img 
                        :src="'/' + (post.user?.avatar || 'assets/images/default-avatar.png')" 
                        class="rounded-circle border" 
                        style="width: 38px; height: 38px; object-fit: cover;"
                      >
                    </Link>
                    <div>
                      <div class="fw-bold text-dark">
                        <Link :href="'/forum/auteur/' + post.user?.id" class="text-dark text-decoration-none hover-danger">
                          {{ post.user?.name || 'Membre' }}
                        </Link>
                      </div>
                      <small class="text-muted">{{ new Date(post.created_at).toLocaleString('fr-FR') }}</small>
                    </div>
                  </div>

                  <div class="d-flex align-items-center gap-2">
                    <!-- Like Button with Counter -->
                    <button 
                      type="button" 
                      @click="likePost(post.id)" 
                      class="btn btn-sm rounded-pill px-3 d-flex align-items-center gap-1"
                      :class="post.user_has_liked ? 'btn-danger text-white' : 'btn-outline-danger'"
                      :disabled="!authUser"
                    >
                      <i class="bi" :class="post.user_has_liked ? 'bi-heart-fill' : 'bi-heart'"></i>
                      <span>{{ post.likes_count }}</span>
                    </button>

                    <button @click="openReportModal(null, post.id)" class="btn btn-sm btn-link text-muted p-1" title="Signaler ce message">
                      <i class="bi bi-flag"></i>
                    </button>
                  </div>
                </div>

                <!-- Post Body with HTML / Embedded YouTube support -->
                <div class="post-content text-dark" v-html="post.content"></div>
              </div>

              <!-- Empty Responses -->
              <div v-if="posts.length === 0" class="text-center py-5 bg-white rounded-4 border">
                <i class="bi bi-chat-left-dots fs-1 text-muted d-block mb-2"></i>
                <h6 class="fw-bold text-dark">Aucune réponse pour le moment.</h6>
                <p class="text-muted small">Soyez le premier à donner votre avis ou apporter une solution !</p>
              </div>
            </div>

            <!-- Reply Form (WYSIWYG-style format buttons) if not solved/closed -->
            <div v-if="!topic.is_solved && !topic.is_closed" class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
              <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-reply-fill text-danger me-2"></i>Participer à la discussion
              </h5>

              <div v-if="!authUser" class="alert alert-warning rounded-4 border-0 p-3 mb-0">
                <i class="bi bi-info-circle me-1"></i> Vous devez être <Link href="/login" class="fw-bold text-dark">connecté</Link> pour poster une réponse sur le forum.
              </div>

              <form v-else @submit.prevent="submitReply">
                <!-- Quick Formatting Toolbar -->
                <div class="d-flex align-items-center gap-1 mb-2 bg-light p-2 rounded-3 border flex-wrap">
                  <button type="button" @click="insertFormat('**', '**')" class="btn btn-sm btn-white border" title="Gras"><strong>B</strong></button>
                  <button type="button" @click="insertFormat('*', '*')" class="btn btn-sm btn-white border" title="Italique"><em>I</em></button>
                  <button type="button" @click="insertFormat('`', '`')" class="btn btn-sm btn-white border" title="Code"><code>&lt;&gt;</code></button>
                  <button type="button" @click="insertFormat('> ', '')" class="btn btn-sm btn-white border" title="Citation"><i class="bi bi-quote"></i></button>
                  <button type="button" @click="promptYoutube" class="btn btn-sm btn-white border text-danger" title="Vidéo YouTube"><i class="bi bi-youtube"></i> YouTube</button>
                  <button type="button" @click="promptImage" class="btn btn-sm btn-white border text-primary" title="Image par URL"><i class="bi bi-link-45deg"></i> Image URL</button>
                  <button type="button" @click="$refs.localImageInput.click()" class="btn btn-sm btn-white border text-success fw-bold" title="Téléverser une image locale depuis mon PC"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Image PC</button>
                  <input type="file" ref="localImageInput" @change="onImageSelected" accept="image/*" class="d-none">
                </div>

                <!-- Selected Local Image Preview -->
                <div v-if="selectedImagePreview" class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded-3 border">
                  <img :src="selectedImagePreview" class="rounded-2 border" style="width: 48px; height: 48px; object-fit: cover;">
                  <div class="small flex-grow-1 text-truncate">
                    <strong class="text-dark">{{ selectedImageFile?.name }}</strong>
                    <div class="text-muted extra-small">{{ (selectedImageFile?.size / 1024).toFixed(1) }} Ko &bull; Sera sauvegardée dans storage/images/</div>
                  </div>
                  <button type="button" @click="clearSelectedImage" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <i class="bi bi-x"></i>
                  </button>
                </div>

                <div class="mb-3">
                  <textarea 
                    id="replyTextarea"
                    v-model="replyContent" 
                    rows="5" 
                    class="form-control" 
                    placeholder="Rédigez votre réponse ici. Vous pouvez formater le texte, intégrer des extraits de code ou insérer une vidéo YouTube..."
                    required
                  ></textarea>
                </div>

                <button type="submit" :disabled="isReplying" class="btn btn-melty rounded-pill px-4">
                  <i class="bi bi-send-fill me-1"></i> Publier ma réponse
                </button>
              </form>
            </div>

          </div>

          <!-- Sidebar (4 cols) -->
          <div class="col-lg-4">
            
            <!-- Author Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white text-center">
              <img 
                :src="'/' + (topic.user?.avatar || 'assets/images/default-avatar.png')" 
                class="rounded-circle mx-auto mb-3 border border-2 border-danger shadow-sm" 
                style="width: 72px; height: 72px; object-fit: cover;"
              >
              <h5 class="fw-bold text-dark mb-1">{{ topic.user?.name || 'Auteur' }}</h5>
              <p class="text-muted small mb-3">Membre de la communauté Tech237</p>
              
              <Link :href="'/forum/auteur/' + topic.user?.id" class="btn btn-outline-dark rounded-pill w-100 fw-bold btn-sm">
                <i class="bi bi-chat-left-dots me-1 text-success"></i> Voir le profil &amp; Contacter
              </Link>
            </div>

            <!-- Related Topics in same category -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
              <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                <i class="bi bi-chat-left-text text-danger me-2"></i>Autres sujets dans cette catégorie
              </h6>
              <div class="d-flex flex-column gap-3">
                <div v-for="rel in relatedTopics" :key="rel.id" class="border-bottom pb-2">
                  <Link :href="'/forum/' + topic.category?.slug + '/' + rel.slug" class="fw-semibold small text-dark text-decoration-none hover-danger d-block mb-1">
                    {{ rel.title }}
                  </Link>
                  <small class="text-muted">{{ new Date(rel.created_at).toLocaleDateString('fr-FR') }}</small>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- REPORT MODAL -->
    <div v-if="showReportModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold"><i class="bi bi-shield-exclamation text-danger me-2"></i>Signaler ce contenu</h5>
            <button type="button" class="btn-close" @click="showReportModal = false"></button>
          </div>
          <form @submit.prevent="submitReport">
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label small fw-bold">Motif du signalement <span class="text-danger">*</span></label>
                <select v-model="reportForm.reason" class="form-select" required>
                  <option value="Spam / Publicité abusive">Spam / Publicité abusive</option>
                  <option value="Propos injurieux / Harcèlement">Propos injurieux / Harcèlement</option>
                  <option value="Contenu haineux ou violent">Contenu haineux ou violent</option>
                  <option value="Désinformation">Désinformation</option>
                  <option value="Autre motif">Autre motif</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Détails complémentaires</label>
                <textarea v-model="reportForm.details" class="form-control" rows="3" placeholder="Expliquez brièvement le problème..."></textarea>
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="showReportModal = false">Annuler</button>
              <button type="submit" class="btn btn-danger rounded-pill px-4">Envoyer le signalement</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  topic: Object,
  posts: Array,
  relatedTopics: Array,
  canSolve: Boolean,
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user || null)

// Reply
const replyContent = ref('')
const isReplying = ref(false)
const localImageInput = ref(null)
const selectedImageFile = ref(null)
const selectedImagePreview = ref(null)

function onImageSelected(e) {
  const file = e.target.files?.[0]
  if (file) {
    selectedImageFile.value = file
    selectedImagePreview.value = URL.createObjectURL(file)
  }
}

function clearSelectedImage() {
  selectedImageFile.value = null
  selectedImagePreview.value = null
  if (localImageInput.value) {
    localImageInput.value.value = ''
  }
}

function submitReply() {
  if (!replyContent.value.trim() && !selectedImageFile.value) return
  isReplying.value = true

  const formData = new FormData()
  formData.append('content', replyContent.value)
  if (selectedImageFile.value) {
    formData.append('image', selectedImageFile.value)
  }

  router.post(`/forum/repondre/${props.topic.id}`, formData, {
    onSuccess: () => {
      replyContent.value = ''
      clearSelectedImage()
      isReplying.value = false
    },
    onError: () => {
      isReplying.value = false
    }
  })
}

function insertFormat(before, after) {
  const textarea = document.getElementById('replyTextarea')
  if (!textarea) return
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const selected = replyContent.value.substring(start, end)
  replyContent.value = replyContent.value.substring(0, start) + before + selected + after + replyContent.value.substring(end)
}

function promptYoutube() {
  const url = prompt('Collez l\'URL de la vidéo YouTube (ex: https://www.youtube.com/watch?v=...) :')
  if (url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/
    const match = url.match(regExp)
    if (match && match[2].length === 11) {
      const embed = `<div class="ratio ratio-16x9 my-3"><iframe src="https://www.youtube.com/embed/${match[2]}" frameborder="0" allowfullscreen></iframe></div>`
      replyContent.value += '\n' + embed + '\n'
    } else {
      alert('URL YouTube invalide.')
    }
  }
}

function promptImage() {
  const url = prompt('URL de l\'image (ex: https://.../image.png) :')
  if (url) {
    replyContent.value += `\n<img src="${url}" class="img-fluid rounded my-2" style="max-height: 400px;">\n`
  }
}

function toggleSolveTopic() {
  router.post(`/forum/topic/${props.topic.id}/resoudre`, {}, {
    preserveScroll: true,
  })
}

function likePost(postId) {
  router.post(`/forum/post/${postId}/like`, {}, {
    preserveScroll: true,
  })
}

// Report Modal
const showReportModal = ref(false)
const reportForm = ref({
  forum_topic_id: null,
  forum_post_id: null,
  reason: 'Spam / Publicité abusive',
  details: '',
})

function openReportModal(topicId, postId) {
  reportForm.value = {
    forum_topic_id: topicId,
    forum_post_id: postId,
    reason: 'Spam / Publicité abusive',
    details: '',
  }
  showReportModal.value = true
}

function submitReport() {
  router.post('/forum/signaler', reportForm.value, {
    onSuccess: () => {
      showReportModal.value = false
    }
  })
}
</script>

<style scoped>
.hover-danger:hover {
  color: #FF004B !important;
}
.bg-success-subtle-light {
  background-color: #f0fdf4 !important;
}
</style>
