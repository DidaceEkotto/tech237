<template>
  <FrontLayout>
    <Head>
      <title>POCs & Mini-Actualités Tech | Tech237</title>
      <meta name="description" content="Découvrez les mini-actualités, sorties de projets, brèves et discussions partagées en direct par la communauté tech camerounaise.">
    </Head>

    <div class="pocs-index-page py-5 bg-light min-vh-100">
      <div class="container">
        
        <!-- Hero Header Banner -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
          <div class="position-absolute end-0 top-0 translate-middle-y me-5 opacity-10 d-none d-lg-block pointer-events-none">
            <i class="bi bi-lightning-charge-fill" style="font-size: 260px;"></i>
          </div>

          <div class="row align-items-center g-4 position-relative z-1">
            <div class="col-lg-8">
              <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 shadow-sm text-white" style="background-color: var(--tech-green, #00A859);">
                <i class="bi bi-lightning-charge-fill me-1"></i> Communauté &amp; Micro-News Tech
              </span>
              <h1 class="display-6 fw-black text-white mb-2">Les POCs de la Communauté</h1>
              <p class="lead text-light opacity-90 mb-4" style="max-width: 650px;">
                Partagez vos sorties de projets, trouvailles open-source, brèves technologiques et annonces en direct avec l'ensemble des développeurs et créateurs.
              </p>

              <div class="d-flex align-items-center gap-3 flex-wrap">
                <Link 
                  href="/pocs/proposer" 
                  class="btn btn-lg rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-flex align-items-center gap-2"
                  style="background-color: var(--tech-green, #00A859);"
                >
                  <i class="bi bi-plus-circle-fill"></i>
                  <span>Proposer un POC</span>
                </Link>
                <span class="badge bg-white bg-opacity-10 text-white border border-secondary px-3 py-2 rounded-pill">
                  <i class="bi bi-check-circle-fill text-success me-1"></i> {{ totalPocs }} actualité(s) partagée(s)
                </span>
              </div>
            </div>

            <!-- Search Box inside Hero -->
            <div class="col-lg-4">
              <div class="card border-0 rounded-4 p-3 bg-white text-dark shadow-lg">
                <h6 class="fw-bold mb-3"><i class="bi bi-search text-danger me-2"></i>Rechercher dans les POCs</h6>
                <div class="input-group mb-2">
                  <input 
                    type="text" 
                    v-model="searchQueryInput" 
                    @keyup.enter="applyFilters" 
                    class="form-control form-control-sm" 
                    placeholder="ex: IA, Mobile Money, Flutter..."
                  />
                  <button @click="applyFilters" class="btn btn-dark btn-sm px-3">
                    <i class="bi bi-search"></i>
                  </button>
                </div>
                <div class="d-flex justify-content-between align-items-center extra-small text-muted">
                  <span>Filtrer par pertinence</span>
                  <button v-if="searchQueryInput" @click="clearSearch" class="btn btn-link btn-sm p-0 text-danger extra-small">
                    Effacer
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tag Filters Pills & Sort Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <!-- Tag pills -->
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <button 
              v-for="t in tagsList" 
              :key="t.key"
              type="button"
              class="btn btn-sm rounded-pill px-3 fw-bold transition-all shadow-xs"
              :class="currentTag === t.key ? 'btn-dark text-white' : 'btn-white bg-white text-dark border'"
              @click="filterByTag(t.key)"
            >
              <i :class="t.icon" class="me-1" :style="{ color: currentTag === t.key ? '#ffffff' : t.color }"></i>
              {{ t.label }}
            </button>
          </div>

          <!-- Sort dropdown -->
          <div class="d-flex align-items-center gap-2">
            <small class="text-muted fw-semibold">Trier :</small>
            <select v-model="selectedSort" @change="applyFilters" class="form-select form-select-sm bg-white rounded-pill border shadow-xs" style="width: 160px;">
              <option value="recent">Plus récents</option>
              <option value="popular">Plus populaires (Upvotes)</option>
            </select>
          </div>
        </div>

        <!-- Main Content (8 cols) + Sidebar (4 cols) -->
        <div class="row g-4">
          
          <!-- POCs Feed Column -->
          <div class="col-lg-8">
            <div v-if="pocs.data && pocs.data.length > 0" class="d-flex flex-column gap-4">
              
              <div 
                v-for="poc in pocs.data" 
                :key="poc.id"
                class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden hover-shadow transition"
              >
                <!-- Card Header with Author & Time -->
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div class="d-flex align-items-center gap-3">
                    <Link v-if="poc.user" :href="'/auteur/' + poc.user.id" class="text-decoration-none">
                      <img 
                        v-if="poc.user.avatar" 
                        :src="'/' + poc.user.avatar" 
                        class="rounded-circle border" 
                        style="width: 44px; height: 44px; object-fit: cover;"
                        :alt="poc.user.name"
                      />
                      <div 
                        v-else 
                        class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 44px; height: 44px; font-size: 1.1rem;"
                      >
                        {{ (poc.user.first_name || poc.user.name || 'U').charAt(0).toUpperCase() }}
                      </div>
                    </Link>
                    <div 
                      v-else 
                      class="rounded-circle bg-secondary text-white fw-bold d-flex align-items-center justify-content-center shadow-sm"
                      style="width: 44px; height: 44px; font-size: 1.1rem;"
                    >
                      {{ (poc.author_name || 'C').charAt(0).toUpperCase() }}
                    </div>

                    <div>
                      <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-dark fs-6">
                          {{ poc.user ? (poc.user.first_name ? poc.user.first_name + ' ' + poc.user.name : poc.user.name) : (poc.author_name || 'Contributeur') }}
                        </span>
                        <span v-if="poc.user?.role === 'super_admin'" class="badge bg-danger-subtle text-danger extra-small rounded-pill">Admin</span>
                      </div>
                      <small class="text-muted extra-small">
                        {{ formatTimeAgo(poc.published_at || poc.created_at) }}
                      </small>
                    </div>
                  </div>

                  <!-- Tag Badge -->
                  <span class="badge rounded-pill px-3 py-1 text-uppercase fw-bold" :class="getTagBadgeClass(poc.tag)">
                    #{{ poc.tag }}
                  </span>
                </div>

                <!-- Card Body -->
                <div class="card-body p-4 pt-3">
                  <!-- Title -->
                  <h4 class="fw-bold mb-2">
                    <Link :href="'/pocs/' + (poc.slug || poc.id)" class="text-dark text-decoration-none hover-danger">
                      {{ poc.title }}
                    </Link>
                  </h4>

                  <!-- Cover image preview if exists -->
                  <div v-if="poc.cover_image" class="my-3 rounded-4 overflow-hidden border shadow-xs" style="max-height: 280px;">
                    <Link :href="'/pocs/' + (poc.slug || poc.id)">
                      <img :src="'/' + poc.cover_image" class="w-100 h-100 object-fit-cover hover-scale" :alt="poc.title" style="max-height: 280px;">
                    </Link>
                  </div>

                  <!-- Plain text excerpt -->
                  <p class="text-secondary small mb-3 leading-relaxed">
                    {{ poc.summary || stripHtml(poc.content).substring(0, 180) + '...' }}
                  </p>

                  <!-- Link source if available -->
                  <div v-if="poc.link_url" class="mb-3">
                    <a :href="poc.link_url" target="_blank" rel="noopener noreferrer" class="badge bg-light text-primary border text-decoration-none px-3 py-2 rounded-pill small d-inline-flex align-items-center gap-1">
                      <i class="bi bi-box-arrow-up-right"></i>
                      <span class="text-truncate" style="max-width: 280px;">{{ poc.link_url }}</span>
                    </a>
                  </div>
                </div>

                <!-- Card Footer Action Bar -->
                <div class="card-footer bg-light border-0 px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div class="d-flex align-items-center gap-3">
                    <!-- Upvote Button -->
                    <button 
                      type="button" 
                      class="btn btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-1 transition"
                      :class="poc.has_upvoted ? 'btn-danger text-white' : 'btn-outline-danger'"
                      @click="toggleUpvote(poc)"
                    >
                      <i class="bi" :class="poc.has_upvoted ? 'bi-fire' : 'bi-heart'"></i>
                      <span>{{ poc.upvotes_count || 0 }}</span>
                    </button>

                    <!-- Comments count link -->
                    <Link :href="'/pocs/' + (poc.slug || poc.id) + '#commentaires'" class="text-muted small text-decoration-none d-flex align-items-center gap-1 hover-dark">
                      <i class="bi bi-chat-text"></i>
                      <span>{{ poc.comments_count || 0 }}</span>
                    </Link>

                    <!-- Views -->
                    <span class="text-muted extra-small">
                      <i class="bi bi-eye me-1"></i>{{ poc.views_count || 0 }}
                    </span>
                  </div>

                  <div>
                    <Link :href="'/pocs/' + (poc.slug || poc.id)" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold">
                      Lire la suite &rarr;
                    </Link>
                  </div>
                </div>

              </div>

            </div>

            <!-- Empty State -->
            <div v-else class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
              <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex p-4 fs-1 mb-3 mx-auto">
                <i class="bi bi-lightning-charge"></i>
              </div>
              <h4 class="fw-bold text-dark mb-2">Aucun POC pour cette recherche</h4>
              <p class="text-muted small mb-4">Soyez le premier à partager une mini-actualité ou nouveauté tech dans cette catégorie !</p>
              <div>
                <Link href="/pocs/proposer" class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm" style="background-color: var(--tech-green, #00A859);">
                  <i class="bi bi-plus-circle me-1"></i> Proposer le premier POC
                </Link>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="pocs.links && pocs.links.length > 3" class="d-flex justify-content-center mt-4">
              <nav>
                <ul class="pagination pagination-sm">
                  <li v-for="(link, lIdx) in pocs.links" :key="lIdx" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                    <Link class="page-link rounded-pill mx-1" :href="link.url || '#'" v-html="link.label" />
                  </li>
                </ul>
              </nav>
            </div>
          </div>

          <!-- Right Sidebar (4 cols) -->
          <div class="col-lg-4">
            
            <!-- CTA Proposer Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #00A859 0%, #0B2559 100%);">
              <h5 class="fw-bold mb-2"><i class="bi bi-megaphone-fill me-2"></i>Vous avez une actu tech ?</h5>
              <p class="small text-white-50 mb-3">
                Un projet open-source, une levée de fonds, un bug résolu ou un tutoriel ? Partagez-le avec l'écosystème Tech237 en 2 minutes.
              </p>
              <Link href="/pocs/proposer" class="btn btn-light rounded-pill w-100 fw-bold shadow-sm" style="color: #0B2559;">
                <i class="bi bi-pencil-square me-1"></i> Rédiger une mini-actualité
              </Link>
            </div>

            <!-- Top POCs Populaires -->
            <div v-if="popularPocs && popularPocs.length > 0" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                <i class="bi bi-fire text-danger me-2"></i>POCs Populaires
              </h6>
              <div class="d-flex flex-column gap-3">
                <div v-for="pop in popularPocs" :key="pop.id" class="border-bottom pb-2">
                  <Link :href="'/pocs/' + (pop.slug || pop.id)" class="fw-semibold small text-dark text-decoration-none hover-danger d-block mb-1">
                    {{ pop.title }}
                  </Link>
                  <div class="d-flex align-items-center justify-content-between extra-small text-muted">
                    <span>#{{ pop.tag }}</span>
                    <span class="text-danger fw-bold"><i class="bi bi-heart-fill me-1"></i>{{ pop.upvotes_count }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Daily Tech Poll in Sidebar -->
            <DailyTechPoll />

          </div>
        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import DailyTechPoll from '@/Components/DailyTechPoll.vue'

const props = defineProps({
  pocs: Object,
  popularPocs: Array,
  currentTag: String,
  currentSort: String,
  searchQuery: String,
  totalPocs: Number,
})

const searchQueryInput = ref(props.searchQuery || '')
const selectedSort = ref(props.currentSort || 'recent')

const tagsList = [
  { key: 'all', label: 'Tous les POCs', icon: 'bi-grid', color: '#0B2559' },
  { key: 'ia', label: 'Intelligence Artificielle', icon: 'bi-cpu', color: '#6366f1' },
  { key: 'fintech', label: 'Fintech & Mobile Money', icon: 'bi-credit-card', color: '#00A859' },
  { key: 'dev', label: 'Dev & Open Source', icon: 'bi-code-slash', color: '#f59e0b' },
  { key: 'startup', label: 'Startups & Levées', icon: 'bi-rocket-takeoff', color: '#ec4899' },
  { key: 'cybersec', label: 'Cybersécurité', icon: 'bi-shield-lock', color: '#ef4444' },
  { key: 'mobile', label: 'Mobile & Télécoms', icon: 'bi-phone', color: '#06b6d4' },
]

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

function filterByTag(tag) {
  router.get('/pocs', {
    tag: tag === 'all' ? null : tag,
    q: searchQueryInput.value || null,
    sort: selectedSort.value,
  }, { preserveState: true })
}

function applyFilters() {
  router.get('/pocs', {
    tag: props.currentTag === 'all' ? null : props.currentTag,
    q: searchQueryInput.value || null,
    sort: selectedSort.value,
  }, { preserveState: true })
}

function clearSearch() {
  searchQueryInput.value = ''
  applyFilters()
}

function stripHtml(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>?/gm, '')
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

function toggleUpvote(poc) {
  const guestToken = typeof window !== 'undefined' ? localStorage.getItem('tech237_poll_guest_token') : null

  fetch(`/pocs/${poc.id}/upvote`, {
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
      poc.has_upvoted = data.upvoted
      poc.upvotes_count = data.upvotes_count
    }
  }).catch(e => {
    console.error('Upvote error:', e)
  })
}
</script>
