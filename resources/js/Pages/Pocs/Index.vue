<template>
  <FrontLayout>
    <Head>
      <title>POCs & Mini-Actualités Tech | Tech237</title>
      <meta name="description" content="Partagez et découvrez de courtes actualités dans le domaine de la tech avec les autres membres de la communauté Tech237.">
      <meta property="og:title" content="POCs & Mini-Actualités Tech | Tech237">
      <meta property="og:description" content="Partagez et découvrez de courtes actualités dans le domaine de la tech avec les autres membres de la communauté Tech237.">
      <meta property="og:type" content="website">
    </Head>

    <div class="pocs-index-page py-5 bg-slate-50 min-vh-100" style="background-color: #f8fafc;">
      <div class="container">
        
        <!-- Modern Hero Header Banner -->
        <div class="poc-hero card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #071738 0%, #0B2559 50%, #061e47 100%);">
          <!-- Background decorative glow and icons -->
          <div class="position-absolute end-0 top-50 translate-middle-y me-4 opacity-10 d-none d-lg-block pointer-events-none" style="pointer-events: none;">
            <i class="bi bi-cpu-fill" style="font-size: 280px; color: #00A859;"></i>
          </div>
          <div class="position-absolute start-50 top-0 translate-middle-x w-100 h-100 pointer-events-none" style="background: radial-gradient(circle at 80% 20%, rgba(0, 168, 89, 0.18) 0%, transparent 60%); pointer-events: none;"></div>

          <div class="row align-items-center g-4 position-relative z-1">
            <div class="col-lg-8">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 shadow-xs" style="background: rgba(0, 168, 89, 0.15); border: 1px solid rgba(0, 168, 89, 0.35); color: #34d399; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                <span class="d-inline-block rounded-circle bg-success" style="width: 8px; height: 8px; box-shadow: 0 0 10px #00A859;"></span>
                <span>Communauté &bull; Micro-News Tech</span>
              </div>
              
              <h1 class="display-6 fw-black text-white mb-3" style="letter-spacing: -0.5px;">
                Les POCs de la Communauté
              </h1>
              
              <p class="lead text-light mb-4" style="max-width: 650px; font-size: 1.15rem; line-height: 1.6; color: #e2e8f0;">
                Partagez de courtes actualités dans le domaine de la tech avec les autres membres de la communauté.
              </p>

              <div class="d-flex align-items-center gap-3 flex-wrap">
                <Link 
                  href="/pocs/proposer" 
                  class="btn btn-lg rounded-pill px-4 py-2 fw-bold text-white shadow d-flex align-items-center gap-2 hero-cta-btn"
                  style="background-color: var(--tech-green, #00A859); transition: all 0.25s ease;"
                >
                  <i class="bi bi-plus-circle-fill fs-5"></i>
                  <span>Proposer un POC</span>
                </Link>
                <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 backdrop-blur text-white px-3 py-2 rounded-pill border border-white border-opacity-10 small">
                  <i class="bi bi-check-circle-fill text-success"></i>
                  <span class="fw-semibold">{{ totalPocs }} actualité(s) partagée(s)</span>
                </div>
              </div>
            </div>

            <!-- Sleek Integrated Search Card inside Hero -->
            <div class="col-lg-4">
              <div class="card border-0 rounded-4 p-4 text-dark shadow-lg bg-white" style="border: 1px solid rgba(255,255,255,0.2) !important;">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; background: #0B2559;">
                    <i class="bi bi-search" style="font-size: 0.85rem;"></i>
                  </div>
                  <h6 class="fw-bold mb-0 text-dark">Explorer les POCs</h6>
                </div>
                
                <div class="input-group mb-2">
                  <input 
                    type="text" 
                    v-model="searchQueryInput" 
                    @keyup.enter="applyFilters" 
                    class="form-control rounded-start-pill border-end-0 py-2 ps-3" 
                    placeholder="ex: IA, Mobile Money, Flutter..."
                    style="border-color: #cbd5e1; font-size: 0.9rem;"
                  />
                  <button @click="applyFilters" class="btn btn-dark rounded-end-pill px-3 py-2 fw-bold">
                    <i class="bi bi-arrow-right"></i>
                  </button>
                </div>
                
                <div class="d-flex justify-content-between align-items-center extra-small text-muted mt-2">
                  <span>Recherche par mots-clés ou tags</span>
                  <button v-if="searchQueryInput" @click="clearSearch" class="btn btn-link btn-sm p-0 text-danger text-decoration-none fw-bold" style="font-size: 0.78rem;">
                    <i class="bi bi-x-circle me-1"></i>Effacer
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
              class="btn btn-sm rounded-pill px-3 py-2 fw-bold transition-all shadow-xs d-inline-flex align-items-center gap-1"
              :class="currentTag === t.key ? 'btn-dark text-white' : 'btn-white bg-white text-dark border'"
              @click="filterByTag(t.key)"
              style="transition: all 0.2s ease;"
            >
              <i :class="t.icon" :style="{ color: currentTag === t.key ? '#ffffff' : t.color }"></i>
              <span>{{ t.label }}</span>
            </button>
          </div>

          <!-- Sort dropdown -->
          <div class="d-flex align-items-center gap-2">
            <span class="text-muted small fw-semibold text-nowrap"><i class="bi bi-sort-down me-1"></i>Trier :</span>
            <select v-model="selectedSort" @change="applyFilters" class="form-select form-select-sm bg-white rounded-pill border shadow-xs fw-semibold" style="width: 170px;">
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
              
              <article 
                v-for="poc in pocs.data" 
                :key="poc.id"
                class="card border-0 rounded-4 bg-white poc-card overflow-hidden transition"
                style="border: 1px solid #e2e8f0 !important; box-shadow: 0 4px 14px rgba(0,0,0,0.03);"
              >
                <!-- Card Header with Author, Avatar & Tag -->
                <div class="card-header bg-white border-0 p-4 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
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
                        class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs"
                        style="width: 44px; height: 44px; font-size: 1.1rem; background: linear-gradient(135deg, #0B2559 0%, #00A859 100%);"
                      >
                        {{ (poc.user.first_name || poc.user.name || 'U').charAt(0).toUpperCase() }}
                      </div>
                    </Link>
                    <div 
                      v-else 
                      class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs"
                      style="width: 44px; height: 44px; font-size: 1.1rem; background: linear-gradient(135deg, #64748b 0%, #475569 100%);"
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
                      <small class="text-muted extra-small d-flex align-items-center gap-1">
                        <i class="bi bi-clock"></i>
                        <span>{{ formatTimeAgo(poc.published_at || poc.created_at) }}</span>
                      </small>
                    </div>
                  </div>

                  <!-- Tag Badge -->
                  <span class="badge rounded-pill px-3 py-1 text-uppercase fw-bold shadow-xs" :class="getTagBadgeClass(poc.tag)">
                    #{{ poc.tag }}
                  </span>
                </div>

                <!-- Card Body -->
                <div class="card-body p-4 pt-2">
                  <!-- Title -->
                  <h3 class="h5 fw-bold mb-2">
                    <Link :href="'/pocs/' + (poc.slug || poc.id)" class="text-dark text-decoration-none poc-title-link">
                      {{ poc.title }}
                    </Link>
                  </h3>

                  <!-- Cover image preview if exists -->
                  <div v-if="poc.cover_image" class="my-3 rounded-4 overflow-hidden border poc-image-container" style="max-height: 320px; background: #f8fafc;">
                    <Link :href="'/pocs/' + (poc.slug || poc.id)">
                      <img :src="'/' + poc.cover_image" class="w-100 h-100 object-fit-cover poc-image-zoom" :alt="poc.title" style="max-height: 320px; object-fit: cover;">
                    </Link>
                  </div>

                  <!-- Plain text excerpt -->
                  <p class="text-secondary small mb-3" style="line-height: 1.65; color: #475569;">
                    {{ poc.summary || stripHtml(poc.content).substring(0, 190) + '...' }}
                  </p>

                  <!-- Link source if available -->
                  <div v-if="poc.link_url" class="mb-2">
                    <a :href="poc.link_url" target="_blank" rel="noopener noreferrer" class="badge bg-slate-100 text-primary border text-decoration-none px-3 py-2 rounded-pill small d-inline-flex align-items-center gap-1 hover-primary" style="background-color: #f1f5f9; font-size: 0.8rem;">
                      <i class="bi bi-box-arrow-up-right"></i>
                      <span class="text-truncate" style="max-width: 320px;">{{ poc.link_url }}</span>
                    </a>
                  </div>
                </div>

                <!-- Card Footer Action Bar -->
                <div class="card-footer bg-white border-0 px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-top: 1px solid #f1f5f9 !important;">
                  <div class="d-flex align-items-center gap-3">
                    <!-- Upvote Button -->
                    <button 
                      type="button" 
                      class="btn btn-sm rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-2 transition"
                      :class="poc.has_upvoted ? 'btn-danger text-white shadow-xs' : 'btn-outline-danger'"
                      @click="toggleUpvote(poc)"
                    >
                      <i class="bi" :class="poc.has_upvoted ? 'bi-fire' : 'bi-heart'"></i>
                      <span>{{ poc.upvotes_count || 0 }}</span>
                    </button>

                    <!-- Comments count link -->
                    <Link :href="'/pocs/' + (poc.slug || poc.id) + '#commentaires'" class="text-muted small text-decoration-none d-flex align-items-center gap-1 hover-dark px-2 py-1 rounded">
                      <i class="bi bi-chat-text"></i>
                      <span>{{ poc.comments_count || 0 }}</span>
                    </Link>

                    <!-- Views -->
                    <span class="text-muted extra-small d-flex align-items-center gap-1">
                      <i class="bi bi-eye"></i>
                      <span>{{ poc.views_count || 0 }}</span>
                    </span>
                  </div>

                  <div>
                    <Link :href="'/pocs/' + (poc.slug || poc.id)" class="btn btn-sm btn-dark rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                      <span>Lire la suite</span>
                      <i class="bi bi-arrow-right"></i>
                    </Link>
                  </div>
                </div>

              </article>

            </div>

            <!-- Empty State -->
            <div v-else class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white" style="border: 1px dashed #cbd5e1 !important;">
              <div class="rounded-circle text-warning d-inline-flex p-4 fs-1 mb-3 mx-auto" style="background-color: #fef3c7;">
                <i class="bi bi-lightning-charge-fill"></i>
              </div>
              <h4 class="fw-bold text-dark mb-2">Aucun POC pour cette recherche</h4>
              <p class="text-muted small mb-4" style="max-width: 480px; margin: 0 auto 1.5rem auto;">
                Soyez le premier à partager une mini-actualité ou nouveauté tech dans cette catégorie !
              </p>
              <div>
                <Link href="/pocs/proposer" class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm" style="background-color: var(--tech-green, #00A859);">
                  <i class="bi bi-plus-circle me-1"></i> Proposer le premier POC
                </Link>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="pocs.links && pocs.links.length > 3" class="d-flex justify-content-center mt-5">
              <nav>
                <ul class="pagination pagination-sm">
                  <li v-for="(link, lIdx) in pocs.links" :key="lIdx" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                    <Link class="page-link rounded-pill mx-1 px-3 py-2 fw-semibold" :href="link.url || '#'" v-html="link.label" />
                  </li>
                </ul>
              </nav>
            </div>
          </div>

          <!-- Right Sidebar (4 cols) -->
          <div class="col-lg-4">
            
            <!-- CTA Proposer Card with Modern Gradient -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #00A859 0%, #0B2559 100%);">
              <div class="position-absolute end-0 bottom-0 opacity-10 pointer-events-none me-2 mb-2">
                <i class="bi bi-megaphone-fill" style="font-size: 110px;"></i>
              </div>
              <div class="position-relative z-1">
                <span class="badge bg-white text-dark fw-bold rounded-pill mb-2 px-2 py-1 text-uppercase" style="font-size: 10px;">
                  Expression Libre
                </span>
                <h5 class="fw-bold mb-2">Vous avez une actu tech ?</h5>
                <p class="small text-white-50 mb-3" style="line-height: 1.55;">
                  Partagez de courtes actualités dans le domaine de la tech avec les autres membres de la communauté : un projet open-source, une levée, un bug résolu ou une découverte.
                </p>
                <Link href="/pocs/proposer" class="btn btn-light rounded-pill w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" style="color: #0B2559;">
                  <i class="bi bi-pencil-square"></i>
                  <span>Rédiger une mini-actualité</span>
                </Link>
              </div>
            </div>

            <!-- Top POCs Populaires -->
            <div v-if="popularPocs && popularPocs.length > 0" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
              <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                <i class="bi bi-fire text-danger me-2"></i>POCs Populaires
              </h6>
              <div class="d-flex flex-column gap-3">
                <div v-for="(pop, pIdx) in popularPocs" :key="pop.id" class="border-bottom pb-2">
                  <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-light text-dark border fw-bold" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
                      {{ pIdx + 1 }}
                    </span>
                    <div class="flex-grow-1">
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
    ia: 'bg-primary-subtle text-primary border border-primary-subtle',
    fintech: 'bg-success-subtle text-success border border-success-subtle',
    dev: 'bg-warning-subtle text-dark border border-warning-subtle',
    startup: 'bg-danger-subtle text-danger border border-danger-subtle',
    cybersec: 'bg-dark text-white',
    mobile: 'bg-info-subtle text-info border border-info-subtle',
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

<style scoped>
.poc-card {
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}
.poc-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.08) !important;
}
.poc-title-link {
  color: #0f172a;
  transition: color 0.2s ease;
}
.poc-title-link:hover {
  color: #00A859;
}
.poc-image-container {
  position: relative;
}
.poc-image-zoom {
  transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.poc-card:hover .poc-image-zoom {
  transform: scale(1.02);
}
.hero-cta-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 20px rgba(0, 168, 89, 0.4) !important;
}
</style>
