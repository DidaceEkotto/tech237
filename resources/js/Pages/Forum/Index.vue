<template>
  <FrontLayout>
    <Head>
      <title>Forum Communautaire Tech & Innovation | Tech237</title>
      <meta name="description" content="Échangez avec les passionnés de tech, codeurs, ingénieurs et porteurs de projets sur le Forum Tech237.">
      <meta property="og:title" content="Forum Communautaire | Tech237">
      <meta property="og:description" content="Discussions, entraide, code, projets tech et innovations au Cameroun.">
    </Head>

    <div class="forum-index-page py-5">
      <div class="container">
        
        <!-- Header Banner -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
          <div class="row align-items-center g-4 position-relative z-1">
            <div class="col-lg-8">
              <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase mb-2 text-white" style="background-color: var(--tech-green);">
                <i class="bi bi-chat-square-text-fill me-1"></i> Forum Tech237
              </span>
              <h1 class="display-6 fw-black text-white mb-2">Communauté, Débats & Entraide</h1>
              <p class="lead text-light mb-4">Posez vos questions, partagez vos astuces gaming, décryptez les séries et échangez sur les technologies de pointe.</p>
              
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <button 
                  v-if="authUser" 
                  @click="openCreateTopicModal" 
                  class="btn btn-melty rounded-pill px-4 py-2 fw-bold"
                >
                  <i class="bi bi-plus-circle me-1"></i> Lancer un nouveau sujet
                </button>
                <Link 
                  v-else 
                  href="/login" 
                  class="btn btn-melty rounded-pill px-4 py-2 fw-bold"
                >
                  <i class="bi bi-person-fill me-1"></i> Connectez-vous pour participer
                </Link>

                <div class="d-flex align-items-center gap-3 text-light small border-start ps-3">
                  <div><strong>{{ stats.total_topics }}</strong> sujets</div>
                  <div><strong>{{ stats.total_posts }}</strong> réponses</div>
                  <div><strong class="text-success">{{ stats.total_solved }}</strong> résolus</div>
                </div>
              </div>
            </div>

            <!-- Search box in Hero -->
            <div class="col-lg-4">
              <div class="card border-0 rounded-4 bg-white p-3 text-dark shadow">
                <label class="form-label small fw-bold mb-2"><i class="bi bi-search text-danger me-1"></i>Rechercher une discussion</label>
                <div class="input-group">
                  <input 
                    type="text" 
                    v-model="searchQuery" 
                    @keyup.enter="applyFilters" 
                    class="form-control" 
                    placeholder="Mots-clés, jeu, bug, série..."
                  >
                  <button @click="applyFilters" class="btn btn-dark">
                    <i class="bi bi-arrow-right"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Category Grid -->
        <div class="mb-5">
          <h4 class="fw-bold text-dark mb-3"><i class="bi bi-grid-fill text-danger me-2"></i>Catégories du Forum</h4>
          <div class="row g-3">
            <div v-for="cat in categories" :key="cat.id" class="col-md-3 col-sm-6">
              <div 
                @click="filterByCategory(cat.id)" 
                class="card border-0 shadow-sm rounded-4 p-3 h-100 cursor-pointer transition cat-card-hover"
                :class="{ 'border-2 border-danger': selectedCatId == cat.id }"
                :style="{ borderTop: '4px solid ' + cat.color }"
              >
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center text-white" :style="{ backgroundColor: cat.color, width: '42px', height: '42px' }">
                    <i :class="'bi ' + cat.icon"></i>
                  </div>
                  <div>
                    <h6 class="fw-bold text-dark mb-0">{{ cat.name || cat.name_fr }}</h6>
                    <small class="text-muted">{{ cat.topics_count || 0 }} sujet(s)</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Bar and Active Category Badge -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold text-dark mb-0">Discussions Récentes</h4>
            <span v-if="selectedCatId" class="badge bg-danger rounded-pill px-3 py-2">
              Filtre actif &bull;
              <button @click="filterByCategory(null)" class="btn-close btn-close-white ms-1" style="font-size: 8px;"></button>
            </span>
          </div>

          <button v-if="authUser" @click="openCreateTopicModal" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">
            <i class="bi bi-pencil-square me-1"></i> Créer un sujet
          </button>
        </div>

        <!-- Topics Table / List -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 bg-white">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="min-width: 320px;">Sujet & Auteur</th>
                  <th>Catégorie</th>
                  <th class="text-center">Réponses</th>
                  <th class="text-center">Vues</th>
                  <th class="text-end">Dernière activité</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="topic in topics.data" :key="topic.id">
                  <td>
                    <div class="d-flex align-items-start gap-3">
                      <!-- Author avatar -->
                      <Link :href="'/forum/auteur/' + topic.user?.id" class="text-decoration-none flex-shrink-0">
                        <img 
                          v-if="topic.user?.avatar"
                          :src="'/' + topic.user.avatar" 
                          class="rounded-circle" 
                          style="width: 40px; height: 40px; object-fit: cover;"
                          :alt="topic.user?.name || 'Avatar'"
                        >
                        <div 
                          v-else 
                          class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold shadow-sm"
                          style="width: 40px; height: 40px; font-size: 1.1rem; min-width: 40px;"
                        >
                          {{ (topic.user?.name || topic.user?.full_name || 'U').charAt(0).toUpperCase() }}
                        </div>
                      </Link>
                      <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                          <span v-if="topic.is_pinned" class="badge bg-warning text-dark"><i class="bi bi-pin-angle-fill me-1"></i>Épinglé</span>
                          <span v-if="topic.is_solved" class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Résolu</span>
                          <span v-if="topic.is_closed" class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>Fermé</span>
                          
                          <Link :href="'/forum/' + topic.category?.slug + '/' + topic.slug" class="fw-bold text-dark text-decoration-none hover-danger fs-6">
                            {{ topic.title }}
                          </Link>
                        </div>
                        <small class="text-muted">
                          Par <Link :href="'/forum/auteur/' + topic.user?.id" class="text-dark fw-semibold text-decoration-none">{{ topic.user?.name || 'Membre' }}</Link>
                          ({{ topic.user?.forum_topics_count || 1 }} sujets)
                        </small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge rounded-pill px-3 py-1" :style="{ backgroundColor: topic.category?.color || '#FF004B', color: 'white' }">
                      {{ topic.category?.name_fr }}
                    </span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-light text-dark border px-3 py-1 fw-bold">{{ topic.posts_count }}</span>
                  </td>
                  <td class="text-center text-muted small">
                    {{ topic.views_count }}
                  </td>
                  <td class="text-end text-muted small">
                    {{ new Date(topic.updated_at).toLocaleDateString('fr-FR') }}
                  </td>
                </tr>
                <tr v-if="!topics.data || topics.data.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-chat-square-text fs-1 d-block mb-2 text-danger"></i>
                    <div class="fw-bold fs-6 mb-1" v-if="searchQuery">Aucun résultats trouvé pour votre recherche</div>
                    <div v-else>Aucun sujet trouvé. Soyez le premier à lancer une discussion !</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="topics.links && topics.links.length > 3" class="d-flex justify-content-center">
          <nav>
            <ul class="pagination pagination-sm">
              <li v-for="(link, lIdx) in topics.links" :key="lIdx" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                <Link class="page-link rounded-pill mx-1" :href="link.url || '#'" v-html="link.label" />
              </li>
            </ul>
          </nav>
        </div>

      </div>
    </div>

    <!-- CREATE TOPIC MODAL -->
    <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1050;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-danger me-2"></i>Lancer une nouvelle discussion</h5>
            <button type="button" class="btn-close" @click="showCreateModal = false"></button>
          </div>
          <form @submit.prevent="submitTopic">
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label small fw-bold">Catégorie du sujet <span class="text-danger">*</span></label>
                <select v-model="topicForm.forum_category_id" class="form-select" required>
                  <option value="" disabled>-- Choisissez une catégorie --</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name || c.name_fr }}</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Titre du sujet <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  v-model="topicForm.title" 
                  class="form-control form-control-lg" 
                  placeholder="ex: Quel est votre avis sur les derniers écrans pliables ?" 
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Message initial / Description détaillée <span class="text-danger">*</span></label>
                <textarea 
                  v-model="topicForm.content" 
                  rows="6" 
                  class="form-control" 
                  placeholder="Développez votre question, votre analyse ou votre problème pour que la communauté puisse vous répondre précisément..." 
                  required
                ></textarea>
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="showCreateModal = false">Annuler</button>
              <button type="submit" :disabled="isSubmitting" class="btn btn-melty rounded-pill px-4">
                <i class="bi bi-send me-1"></i> Publier le sujet
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
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  categories: Array,
  topics: Object,
  stats: Object,
  filters: Object,
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user || null)

const searchQuery = ref(props.filters?.search || '')
const selectedCatId = ref(props.filters?.category_id || null)

function filterByCategory(catId) {
  selectedCatId.value = catId
  applyFilters()
}

function applyFilters() {
  router.get('/forum', {
    search: searchQuery.value || undefined,
    category_id: selectedCatId.value || undefined,
  }, {
    preserveState: true,
  })
}

// Create Topic Modal
const showCreateModal = ref(false)
const isSubmitting = ref(false)
const topicForm = ref({
  forum_category_id: '',
  title: '',
  content: '',
})

function openCreateTopicModal() {
  topicForm.value = {
    forum_category_id: props.categories?.[0]?.id || '',
    title: '',
    content: '',
  }
  showCreateModal.value = true
}

function submitTopic() {
  isSubmitting.value = true
  router.post('/forum/creer-sujet', topicForm.value, {
    onSuccess: () => {
      showCreateModal.value = false
      isSubmitting.value = false
    },
    onError: () => {
      isSubmitting.value = false
    }
  })
}
</script>

<style scoped>
.cat-card-hover {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cat-card-hover:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}
.hover-danger:hover {
  color: #FF004B !important;
}
</style>
