<template>
  <FrontLayout>
    <Head>
      <title>Profil & Discussions de {{ author.name }} | Forum Tech237</title>
      <meta name="description" :content="'Consultez toutes les discussions et réponses de ' + author.name + ' sur Tech237.'">
    </Head>

    <div class="user-topics-page py-5 bg-light min-vh-100">
      <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/forum" class="text-decoration-none text-muted">Forum</Link></li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">{{ author.name }}</li>
          </ol>
        </nav>

        <div class="row g-4">
          <!-- Left Column: Author Card & Direct Message (4 cols) -->
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center sticky-top" style="top: 90px;">
              <img 
                v-if="author.avatar"
                :src="'/' + author.avatar" 
                class="rounded-circle mx-auto mb-3 border border-3 border-success shadow-sm"
                style="width: 100px; height: 100px; object-fit: cover;"
                :alt="author.name"
              >
              <div 
                v-else 
                class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold shadow-sm mx-auto mb-3 border border-3 border-white"
                style="width: 100px; height: 100px; font-size: 2.5rem;"
              >
                {{ (author.name || 'U').charAt(0).toUpperCase() }}
              </div>
              <h4 class="fw-bold text-dark mb-1">{{ author.name }}</h4>
              <p class="text-muted small mb-3">Membre de la communauté Tech237</p>

              <div class="d-flex justify-content-center gap-3 py-2 border-top border-bottom mb-4">
                <div class="text-center">
                  <div class="fw-bold fs-5 text-dark">{{ author.forum_topics_count || 0 }}</div>
                  <small class="text-muted" style="font-size: 11px;">Sujets Forum</small>
                </div>
                <div class="text-center border-start ps-3">
                  <div class="fw-bold fs-5 text-dark">{{ author.projects_count || 0 }}</div>
                  <small class="text-muted" style="font-size: 11px;">Projets Tech</small>
                </div>
                <div class="text-center border-start ps-3">
                  <div class="fw-bold fs-5 text-dark">{{ author.startups_count || 0 }}</div>
                  <small class="text-muted" style="font-size: 11px;">Startups</small>
                </div>
              </div>

              <!-- DIRECT MESSAGE BUTTON -->
              <button 
                type="button"
                @click="openDirectMessage" 
                class="btn rounded-pill w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 text-white shadow-sm"
                style="background-color: var(--tech-green);"
              >
                <i class="bi bi-chat-left-dots-fill"></i> Contacter en message privé
              </button>
            </div>

            <!-- Sondage Tech du Jour -->
            <DailyTechPoll class="mt-4" />
          </div>

          <!-- Right Column: Topics & Replies Tabs (8 cols) -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
              
              <!-- Tabs Navigation -->
              <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3">
                <li class="nav-item">
                  <button 
                    class="nav-link rounded-pill fw-bold" 
                    :class="activeTab === 'topics' ? 'btn-tech active' : 'text-dark bg-light'"
                    @click="activeTab = 'topics'"
                  >
                    <i class="bi bi-chat-quote me-1"></i> Sujets lancés ({{ topics?.total || 0 }})
                  </button>
                </li>
                <li class="nav-item">
                  <button 
                    class="nav-link rounded-pill fw-bold" 
                    :class="activeTab === 'posts' ? 'btn-tech active' : 'text-dark bg-light'"
                    @click="activeTab = 'posts'"
                  >
                    <i class="bi bi-reply-all me-1"></i> Réponses &amp; Interventions ({{ posts?.total || 0 }})
                  </button>
                </li>
              </ul>

              <!-- TAB 1: TOPICS -->
              <div v-if="activeTab === 'topics'">
                <div class="d-flex flex-column gap-3">
                  <div 
                    v-for="t in topics.data" 
                    :key="t.id"
                    class="border rounded-4 p-3 hover-shadow transition"
                  >
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                      <span class="badge rounded-pill" :style="{ backgroundColor: t.category?.color || '#00A859', color: 'white' }">
                        {{ t.category?.name || t.category?.name_fr }}
                      </span>
                      <div class="d-flex align-items-center gap-2">
                        <span v-if="t.is_solved" class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Résolu</span>
                        <small class="text-muted">{{ new Date(t.created_at).toLocaleDateString('fr-FR') }}</small>
                      </div>
                    </div>

                    <h5 class="fw-bold mb-2">
                      <Link :href="'/forum/' + t.category?.slug + '/' + t.slug" class="text-dark text-decoration-none hover-success">
                        {{ t.title }}
                      </Link>
                    </h5>

                    <p class="text-muted small mb-2" style="max-height: 48px; overflow: hidden;">
                      {{ t.content }}
                    </p>

                    <div class="d-flex align-items-center justify-content-between text-muted small pt-2 border-top">
                      <span><i class="bi bi-chat-left me-1"></i> {{ t.posts_count }} réponse(s)</span>
                      <Link :href="'/forum/' + t.category?.slug + '/' + t.slug" class="fw-bold text-decoration-none" style="color: var(--tech-green);">
                        Accéder au sujet &rarr;
                      </Link>
                    </div>
                  </div>

                  <div v-if="!topics.data || topics.data.length === 0" class="text-center py-5 text-muted">
                    <i class="bi bi-chat-dots fs-1 d-block mb-2 text-muted"></i>
                    Cet utilisateur n'a pas encore lancé de discussion sur le forum.
                  </div>
                </div>

                <!-- Pagination Topics -->
                <div v-if="topics.links && topics.links.length > 3" class="mt-4 d-flex justify-content-center">
                  <nav>
                    <ul class="pagination pagination-sm">
                      <li v-for="(link, lIdx) in topics.links" :key="lIdx" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                        <Link class="page-link rounded-pill mx-1" :href="link.url || '#'" v-html="link.label" />
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>

              <!-- TAB 2: REPLIES / POSTS -->
              <div v-else-if="activeTab === 'posts'">
                <div class="d-flex flex-column gap-3">
                  <div 
                    v-for="p in (posts?.data || [])" 
                    :key="p.id"
                    class="border rounded-4 p-3 hover-shadow transition bg-light"
                  >
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                      <span class="badge rounded-pill bg-secondary text-white">
                        <i class="bi bi-chat-text me-1"></i> Réponse
                      </span>
                      <small class="text-muted">{{ new Date(p.created_at).toLocaleDateString('fr-FR') }}</small>
                    </div>

                    <div v-if="p.topic" class="mb-2">
                      <small class="text-muted d-block">En réponse à la discussion :</small>
                      <Link :href="'/forum/' + (p.topic.category?.slug || 'general') + '/' + p.topic.slug" class="fw-bold text-dark text-decoration-none hover-success">
                        {{ p.topic.title }}
                      </Link>
                    </div>

                    <div class="text-secondary small bg-white p-3 rounded-3 border mb-2" v-html="p.content"></div>

                    <div v-if="p.topic" class="text-end">
                      <Link :href="'/forum/' + (p.topic.category?.slug || 'general') + '/' + p.topic.slug" class="fw-bold text-decoration-none small" style="color: var(--tech-green);">
                        Voir dans le fil de discussion &rarr;
                      </Link>
                    </div>
                  </div>

                  <div v-if="!posts || !posts.data || posts.data.length === 0" class="text-center py-5 text-muted">
                    <i class="bi bi-chat-left-dots fs-1 d-block mb-2 text-muted"></i>
                    Cet utilisateur n'a pas encore posté de réponses sur le forum.
                  </div>
                </div>

                <!-- Pagination Posts -->
                <div v-if="posts && posts.links && posts.links.length > 3" class="mt-4 d-flex justify-content-center">
                  <nav>
                    <ul class="pagination pagination-sm">
                      <li v-for="(link, lIdx) in posts.links" :key="lIdx" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                        <Link class="page-link rounded-pill mx-1" :href="link.url || '#'" v-html="link.label" />
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- DIRECT MESSAGE STATIC MODAL -->
    <div v-if="showMessageModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;" @click.self="showMessageModal = false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold text-dark">
              <i class="bi bi-chat-left-dots-fill me-2" style="color: var(--tech-green);"></i>Message privé à {{ author.name }}
            </h5>
            <button type="button" class="btn-close" @click="showMessageModal = false"></button>
          </div>
          <form @submit.prevent="submitMessage">
            <div class="modal-body">
              <div v-if="!user" class="alert alert-warning rounded-3 small mb-0">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                Vous devez être connecté pour envoyer un message privé.
                <div class="mt-2">
                  <Link :href="'/connexion?redirect=' + encodeURIComponent($page.url)" class="btn btn-sm btn-dark rounded-pill px-3">
                    Se connecter maintenant
                  </Link>
                </div>
              </div>
              <div v-else>
                <div v-if="messageSentSuccess" class="alert alert-success rounded-3 small mb-3">
                  <i class="bi bi-check-circle-fill me-1"></i> Votre message privé a bien été envoyé !
                </div>
                <div class="mb-3">
                  <label class="form-label small fw-bold">Sujet du message <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="messageForm.subject" placeholder="ex: Collaboration technique, échange..." required>
                  <div v-if="messageForm.errors.subject" class="text-danger extra-small mt-1">{{ messageForm.errors.subject }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label small fw-bold">Votre message <span class="text-danger">*</span></label>
                  <textarea class="form-control" rows="5" v-model="messageForm.message" placeholder="Écrivez votre message privé ici..." required></textarea>
                  <div v-if="messageForm.errors.message" class="text-danger extra-small mt-1">{{ messageForm.errors.message }}</div>
                </div>
              </div>
            </div>
            <div class="modal-footer border-0 pt-0" v-if="user">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="showMessageModal = false">Annuler</button>
              <button type="submit" class="btn rounded-pill px-4 fw-bold text-white shadow-sm" style="background-color: var(--tech-green);" :disabled="messageForm.processing">
                <span v-if="messageForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-send-fill me-1"></i> Envoyer le message
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, usePage, useForm } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import DailyTechPoll from '@/Components/DailyTechPoll.vue'

const props = defineProps({
  author: Object,
  topics: Object,
  posts: Object,
})

const activeTab = ref('topics')
const page = usePage()
const user = computed(() => page.props.auth?.user)

const showMessageModal = ref(false)
const messageSentSuccess = ref(false)

const messageForm = useForm({
  recipient_id: props.author.id,
  subject: '',
  message: '',
})

function openDirectMessage() {
  messageSentSuccess.value = false
  messageForm.recipient_id = props.author.id
  showMessageModal.value = true
}

function submitMessage() {
  messageForm.post('/espace-utilisateur/messages/envoyer', {
    preserveScroll: true,
    onSuccess: () => {
      messageSentSuccess.value = true
      messageForm.reset('subject', 'message')
      setTimeout(() => {
        showMessageModal.value = false
        messageSentSuccess.value = false
      }, 2000)
    }
  })
}
</script>

<style scoped>
.hover-shadow:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.06) !important;
}
.hover-success:hover {
  color: var(--tech-green) !important;
}
.btn-tech {
  background-color: var(--tech-green) !important;
  color: white !important;
}
.transition {
  transition: all 0.25s ease;
}
</style>
