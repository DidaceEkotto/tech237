<template>
  <div class="champions-arena-root min-vh-100 d-flex flex-column">
    <Head title="La Map des Champions & Gagnants • Tech237" />

    <!-- Top Navigation Bar (Logo + Site Stats + Back to Home) -->
    <header class="arena-header sticky-top">
      <div class="container-fluid px-3 px-md-4 py-2 d-flex align-items-center justify-content-between">
        
        <!-- Site Logo & Arena Brand -->
        <div class="d-flex align-items-center gap-3">
          <Link href="/" class="d-inline-flex align-items-center text-decoration-none" title="Retour au site Tech237">
            <img 
              :src="logoUrl" 
              alt="Tech237 Logo" 
              class="arena-logo" 
            />
          </Link>
          <div class="vr d-none d-sm-block opacity-25" style="height: 28px;"></div>
          <div class="d-none d-md-flex flex-column">
            <span class="arena-badge-tag">
              <span class="pulse-dot"></span>
              CAMPUS &bull; GAMIFICATION
            </span>
            <span class="arena-tagline">La Carte des Champions Tech237</span>
          </div>
        </div>

        <!-- Center Global Stats Pill -->
        <div class="d-none d-lg-flex align-items-center gap-3 bg-white bg-opacity-75 rounded-pill px-4 py-2 shadow-2xs border border-amber-200">
          <div class="d-flex align-items-center gap-2" title="Total XP distribué sur le site">
            <span class="stat-icon-coin">⚡</span>
            <div class="lh-1">
              <strong class="d-block text-dark fw-bold small">{{ Number(totalXp).toLocaleString() }} XP</strong>
              <span class="text-muted extra-small" style="font-size: 10px;">Énergie Totale</span>
            </div>
          </div>
          <div class="vr opacity-25" style="height: 22px;"></div>
          <div class="d-flex align-items-center gap-2" title="Membres enregistrés">
            <span class="stat-icon-coin">👥</span>
            <div class="lh-1">
              <strong class="d-block text-dark fw-bold small">{{ totalPlayers }} Joueurs</strong>
              <span class="text-muted extra-small" style="font-size: 10px;">Communauté</span>
            </div>
          </div>
          <div v-if="currentUserRank" class="vr opacity-25" style="height: 22px;"></div>
          <div v-if="currentUserRank" class="d-flex align-items-center gap-2" title="Votre rang actuel">
            <span class="stat-icon-coin">🥇</span>
            <div class="lh-1">
              <strong class="d-block text-emerald-700 fw-bold small">Rang #{{ currentUserRank.position }}</strong>
              <span class="text-muted extra-small" style="font-size: 10px;">{{ currentUserRank.xp }} XP</span>
            </div>
          </div>
        </div>

        <!-- Right Action: Return to Home Button -->
        <div class="d-flex align-items-center gap-2">
          <!-- User Profile shortcut if logged in -->
          <Link 
            v-if="$page.props.auth?.user" 
            :href="'/communaute/profil/' + $page.props.auth.user.id" 
            class="d-none d-sm-inline-flex align-items-center gap-2 btn btn-sm btn-light rounded-pill px-3 py-2 border shadow-2xs text-decoration-none"
            title="Mon Profil Public"
          >
            <img 
              v-if="$page.props.auth.user.avatar" 
              :src="$page.props.auth.user.avatar.startsWith('/') ? $page.props.auth.user.avatar : '/' + $page.props.auth.user.avatar" 
              class="rounded-circle" 
              style="width: 22px; height: 22px; object-fit: cover;"
            />
            <span v-else class="rounded-circle bg-emerald-600 text-white fw-bold d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 10px;">
              {{ getInitials($page.props.auth.user.name) }}
            </span>
            <span class="fw-semibold text-dark small text-truncate" style="max-width: 120px;">
              {{ $page.props.auth.user.name }}
            </span>
          </Link>

          <!-- Retour à l'accueil button -->
          <Link 
            href="/" 
            class="btn-return-home d-inline-flex align-items-center gap-2 text-decoration-none"
          >
            <i class="bi bi-arrow-left"></i>
            <span>Retour à l'accueil</span>
          </Link>
        </div>

      </div>
    </header>

    <!-- Main Split-Screen Workspace -->
    <main class="arena-main flex-grow-1 container-fluid px-3 px-md-4 py-4">
      <div class="row g-4 h-100">

        <!-- PART 1: The Winding Champions Journey Map (Largest Section: ~62-65% width) -->
        <section class="col-12 col-lg-7 col-xl-8 d-flex flex-column">
          <div class="map-card-wrapper position-relative rounded-5 shadow-sm p-4 p-md-5 flex-grow-1 overflow-hidden">
            
            <!-- Atmospheric Ambient Floating Elements -->
            <div class="bg-island island-top-right"></div>
            <div class="bg-island island-bottom-left"></div>
            <div class="cloud-float cloud-pos-1">☁️</div>
            <div class="cloud-float cloud-pos-2">☁️</div>
            <div class="cloud-float cloud-pos-3">✨</div>
            <div class="cloud-float cloud-pos-4">🚀</div>

            <!-- Top Summit Banner: Highest Level -->
            <div class="summit-crest-box text-center mb-5 position-relative z-2">
              <div class="summit-crown-badge mx-auto mb-2">
                👑
              </div>
              <h1 class="summit-heading mb-1">
                Le Sommet des Champions
              </h1>
              <p class="summit-lead mb-0 text-muted">
                Grimpez les échelons du savoir : chaque POC publié, problème résolu et projet partagé vous rapproche de la Légende.
              </p>
              <div class="mt-2 d-inline-flex align-items-center gap-2 px-3 py-1 bg-amber-50 border border-amber-200 rounded-pill text-amber-900 extra-small fw-semibold">
                <span>💡 Cliquez sur un palier XP pour afficher tous ses champions dans le panneau de droite</span>
              </div>
            </div>

            <!-- Visual Vertical Serpent Roadmap from Top (Highest XP) to Bottom (0 XP) -->
            <div class="roadmap-serpentine-track position-relative mx-auto my-3" style="max-width: 680px;">
              
              <!-- Curved SVG Connecting Line winding between nodes -->
              <svg 
                class="roadmap-connecting-svg position-absolute w-100 h-100 pointer-events-none" 
                viewBox="0 0 680 1400" 
                preserveAspectRatio="none"
              >
                <!-- Outer glow curve -->
                <path 
                  :d="computedCurvedSvgPath" 
                  fill="none" 
                  stroke="rgba(251, 191, 36, 0.4)" 
                  stroke-width="16" 
                  stroke-linecap="round"
                />
                <!-- Striped Candy-style dashed core curve -->
                <path 
                  :d="computedCurvedSvgPath" 
                  fill="none" 
                  stroke="#F59E0B" 
                  stroke-width="6" 
                  stroke-dasharray="14 12" 
                  stroke-linecap="round"
                  class="svg-dash-animated"
                />
              </svg>

              <!-- List of XP Tiers ordered from Top (Highest XP) to Bottom (0 XP) -->
              <div class="tiers-nodes-list d-flex flex-column position-relative z-2">
                <div 
                  v-for="(tier, index) in sortedLevels" 
                  :key="tier.id"
                  class="tier-node-row d-flex align-items-center my-4 position-relative"
                  :class="getSerpentineAlignment(index, sortedLevels.length)"
                >
                  <!-- The Interactive XP Disc & Pod -->
                  <div 
                    class="tier-milestone-pod text-center position-relative"
                    :class="{ 
                      'is-selected': selectedTier && selectedTier.id === tier.id,
                      'is-current-user': isCurrentUserTier(tier)
                    }"
                    @click="selectTier(tier)"
                    :title="'Cliquer pour explorer les champions du palier ' + tier.name"
                  >
                    <!-- Halo Ripple when selected -->
                    <div v-if="selectedTier && selectedTier.id === tier.id" class="pod-selected-halo"></div>

                    <!-- Milestone Crown or Flag for the Summit node -->
                    <div v-if="index === 0" class="summit-star-trophy position-absolute">
                      <span class="trophy-bounce">🏆</span>
                    </div>

                    <!-- Interactive 3D Candy XP Disc -->
                    <div 
                      class="candy-xp-disc d-flex flex-column align-items-center justify-content-center mx-auto"
                      :style="getTierDiscStyle(tier, index)"
                    >
                      <div class="disc-gloss-shine"></div>
                      
                      <!-- Disc Content -->
                      <span class="disc-xp-val fw-bolder">{{ tier.min_xp }}</span>
                      <span class="disc-xp-label">XP</span>

                      <!-- Selected Checkmark / Arrow indicator -->
                      <div v-if="selectedTier && selectedTier.id === tier.id" class="active-check-badge">
                        <i class="bi bi-eye-fill"></i>
                      </div>

                      <!-- Current User "Vous êtes ici !" Floating Pin -->
                      <div v-if="isCurrentUserTier(tier)" class="current-user-pin-chip position-absolute">
                        <div class="pin-ring-pulse"></div>
                        <span class="pin-label text-nowrap">Vous êtes ici ! 📍</span>
                      </div>
                    </div>

                    <!-- Tier Name Ribbon / Pill Under Disc -->
                    <div 
                      class="tier-label-capsule mt-2 px-3 py-1 rounded-pill fw-bold d-inline-flex align-items-center gap-1 shadow-sm"
                      :style="getTierCapsuleStyle(tier)"
                    >
                      <i class="bi" :class="tier.badge_icon || 'bi-award-fill'"></i>
                      <span>{{ tier.name }}</span>
                    </div>

                    <!-- Quick Champions Counter Badge -->
                    <div class="mt-1">
                      <span 
                        class="badge rounded-pill extra-small px-2 py-1 shadow-2xs fw-semibold"
                        :class="(tier.players && tier.players.length) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'"
                      >
                        {{ (tier.players && tier.players.length) ? tier.players.length + ' champion(s)' : 'En attente' }}
                      </span>
                    </div>

                    <!-- Mini Stack of Avatars Perched at this Milestone -->
                    <div v-if="tier.players && tier.players.length" class="perched-avatars-cluster d-flex justify-content-center align-items-center mt-2">
                      <div class="d-flex align-items-center -space-x-2">
                        <div 
                          v-for="p in tier.players.slice(0, 3)" 
                          :key="p.id" 
                          class="avatar-tiny-ring rounded-circle shadow-xs"
                          :title="p.name + ' (' + (p.xp || 0) + ' XP)'"
                        >
                          <img 
                            v-if="p.avatar" 
                            :src="p.avatar.startsWith('/') ? p.avatar : '/' + p.avatar" 
                            class="w-100 h-100 rounded-circle object-fit-cover" 
                          />
                          <div 
                            v-else 
                            class="w-100 h-100 rounded-circle text-white fw-bold d-flex align-items-center justify-content-center"
                            :style="{ backgroundColor: getPlayerColor(p.name), fontSize: '9px' }"
                          >
                            {{ getInitials(p.name) }}
                          </div>
                        </div>
                        <span 
                          v-if="tier.players.length > 3" 
                          class="avatar-tiny-ring bg-white text-dark rounded-circle shadow-xs fw-bold extra-small d-flex align-items-center justify-content-center"
                          style="font-size: 9px; width: 22px; height: 22px;"
                        >
                          +{{ tier.players.length - 3 }}
                        </span>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              <!-- Bottom Start Gate: 0 XP (Novice Tech) -->
              <div class="start-journey-gate text-center mt-5 pt-3 position-relative z-2">
                <div class="start-gate-pill mx-auto px-4 py-2 rounded-pill shadow-md d-inline-flex align-items-center gap-2">
                  <span class="fs-5">🏁</span>
                  <div class="text-start">
                    <strong class="d-block text-white text-uppercase tracking-wider fw-bolder" style="font-size: 13px;">
                      DÉPART DU PARCOURS &bull; NIVEAU 0 XP
                    </strong>
                    <span class="text-white opacity-85 extra-small">
                      Novice Tech &bull; Point d'entrée de tous les bâtisseurs
                    </span>
                  </div>
                </div>
                <p class="text-muted extra-small mt-2 mb-0">
                  Chaque nouvelle inscription démarre ici. Contribuez pour gravir la montagne !
                </p>
              </div>

            </div>

          </div>
        </section>

        <!-- PART 2: Champions Roster Cadre (The Right Panel for Selected XP Tier) -->
        <aside class="col-12 col-lg-5 col-xl-4 d-flex flex-column">
          <div class="champions-cadre-card rounded-5 shadow-sm p-4 d-flex flex-column h-100 position-sticky" style="top: 80px;">
            
            <!-- Cadre Header: Selected Tier Overview -->
            <transition name="tier-header-fade" mode="out-in">
              <div 
                v-if="selectedTier" 
                :key="selectedTier.id"
                class="tier-roster-header p-3 rounded-4 mb-3 position-relative overflow-hidden shadow-xs"
                :style="getRosterHeaderStyle(selectedTier)"
              >
                <div class="d-flex align-items-start justify-content-between gap-2 position-relative z-1">
                  <div class="d-flex align-items-center gap-3">
                    <div 
                      class="tier-header-icon rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                      :style="{ backgroundColor: selectedTier.color || '#00A859' }"
                    >
                      <i class="bi fs-3 text-white" :class="selectedTier.badge_icon || 'bi-award-fill'"></i>
                    </div>
                    <div>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-dark rounded-pill extra-small fw-bold shadow-2xs">
                          Palier {{ selectedTier.min_xp }} XP
                        </span>
                        <span v-if="isCurrentUserTier(selectedTier)" class="badge bg-danger text-white rounded-pill extra-small fw-bold">
                          Votre rang
                        </span>
                      </div>
                      <h2 class="h5 fw-bolder text-dark mb-0 mt-1">
                        {{ selectedTier.name }}
                      </h2>
                    </div>
                  </div>

                  <!-- Champions Count Pill -->
                  <div class="text-end">
                    <span class="badge rounded-pill px-3 py-2 fw-bolder fs-6 shadow-2xs" style="background: rgba(255,255,255,0.9); color: #0F172A;">
                      {{ selectedTierPlayers.length }}
                    </span>
                    <span class="d-block text-muted extra-small mt-1" style="font-size: 10px;">
                      {{ selectedTierPlayers.length > 1 ? 'Membres' : 'Membre' }}
                    </span>
                  </div>
                </div>

                <p class="text-dark opacity-80 small mt-3 mb-0 position-relative z-1">
                  {{ selectedTier.description || 'Franchissez ce palier pour débloquer les privilèges de la communauté Tech237.' }}
                </p>
              </div>
            </transition>

            <!-- Search Filter Bar within this tier's champions -->
            <div class="mb-3 position-relative">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input 
                  v-model="searchQuery" 
                  type="text" 
                  class="form-control bg-white border-start-0 rounded-end-pill pe-3 py-2 shadow-none" 
                  placeholder="Rechercher un champion par son nom..." 
                />
              </div>
            </div>

            <!-- Champions List Stream with Smooth Transitions -->
            <div class="champions-roster-scroll flex-grow-1 overflow-y-auto pe-1">
              <transition name="roster-switch" mode="out-in">
                
                <!-- If there are players in this tier -->
                <div v-if="filteredTierPlayers.length > 0" :key="'list-' + (selectedTier ? selectedTier.id : 'none')" class="d-flex flex-column gap-2">
                  <div 
                    v-for="(player, pIdx) in filteredTierPlayers" 
                    :key="player.id"
                    class="player-champion-item p-2 px-3 rounded-4 bg-white border d-flex align-items-center justify-content-between transition shadow-2xs"
                    :class="{ 'highlight-user': isCurrentLoggedInUser(player) }"
                  >
                    <!-- Player Identity -->
                    <div class="d-flex align-items-center gap-3 min-w-0">
                      <!-- Rank or Medal -->
                      <div class="player-rank-number text-center flex-shrink-0">
                        <span v-if="pIdx === 0 && selectedTier.min_xp >= 100" class="fs-5">🥇</span>
                        <span v-else-if="pIdx === 1 && selectedTier.min_xp >= 100" class="fs-5">🥈</span>
                        <span v-else-if="pIdx === 2 && selectedTier.min_xp >= 100" class="fs-5">🥉</span>
                        <span v-else class="text-muted small fw-bold">#{{ pIdx + 1 }}</span>
                      </div>

                      <!-- Avatar -->
                      <div class="position-relative flex-shrink-0">
                        <img 
                          v-if="player.avatar" 
                          :src="player.avatar.startsWith('/') ? player.avatar : '/' + player.avatar" 
                          :alt="player.name" 
                          class="rounded-circle border border-2 border-white shadow-2xs" 
                          style="width: 44px; height: 44px; object-fit: cover;"
                        />
                        <div 
                          v-else 
                          class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-2xs"
                          :style="{ width: '44px', height: '44px', backgroundColor: getPlayerColor(player.name), fontSize: '14px' }"
                        >
                          {{ getInitials(player.name) }}
                        </div>
                        <span v-if="isCurrentLoggedInUser(player)" class="user-self-dot" title="C'est vous !"></span>
                      </div>

                      <!-- Name & Rank Details -->
                      <div class="min-w-0">
                        <div class="d-flex align-items-center gap-1">
                          <Link 
                            :href="'/communaute/profil/' + player.id" 
                            class="player-name-link fw-bold text-dark text-truncate text-decoration-none small"
                            :title="player.name + ' - Ouvrir le profil public'"
                          >
                            {{ player.name }}
                          </Link>
                          <span v-if="isCurrentLoggedInUser(player)" class="badge bg-emerald-100 text-emerald-800 extra-small px-1 py-0 rounded">
                            Moi
                          </span>
                        </div>
                        <div class="d-flex align-items-center gap-1 mt-0">
                          <span class="text-muted extra-small" style="font-size: 11px;">
                            {{ player.rank_title || selectedTier?.name || 'Novice Tech' }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Right XP Pill & Profile Button -->
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                      <span class="player-xp-chip fw-bolder extra-small px-2 py-1 rounded-pill">
                        {{ player.xp || 0 }} XP
                      </span>
                      <Link 
                        :href="'/communaute/profil/' + player.id" 
                        class="btn-visit-profile btn btn-sm btn-light rounded-circle shadow-2xs p-0 d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;"
                        title="Voir le profil public"
                      >
                        <i class="bi bi-chevron-right text-dark small"></i>
                      </Link>
                    </div>

                  </div>
                </div>

                <!-- Empty State: No players in this tier yet -->
                <div 
                  v-else-if="selectedTier && selectedTierPlayers.length === 0" 
                  :key="'empty-' + selectedTier.id"
                  class="empty-tier-box text-center p-4 rounded-4 bg-white border border-dashed my-auto"
                >
                  <div class="empty-tier-badge mb-3">
                    <span class="fs-1">🚩</span>
                  </div>
                  <h3 class="h6 fw-bolder text-dark mb-1">
                    Ce sommet est encore inexploré !
                  </h3>
                  <p class="text-muted extra-small mb-3">
                    Aucun membre n'a encore atteint le palier <strong>{{ selectedTier.name }}</strong> ({{ selectedTier.min_xp }} XP requis). Soyez le premier pionnier à y hisser votre drapeau !
                  </p>
                  
                  <div class="how-to-earn-card text-start p-3 rounded-3 bg-light border mb-3">
                    <strong class="d-block extra-small text-dark fw-bold mb-2 text-uppercase tracking-wider">
                      🎯 Comment remporter des XP :
                    </strong>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-1 extra-small text-muted">
                      <li class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark rounded-pill px-2">+15 XP</span>
                        <span>Publier une mini-actualité (POC)</span>
                      </li>
                      <li class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-pill px-2">+30 XP</span>
                        <span>Partager un projet dans le showcase</span>
                      </li>
                      <li class="d-flex align-items-center gap-2">
                        <span class="badge bg-success text-white rounded-pill px-2">+10 XP</span>
                        <span>Apporter une solution validée sur le forum</span>
                      </li>
                      <li class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary text-white rounded-pill px-2">+5 XP</span>
                        <span>Commenter et participer aux sondages tech</span>
                      </li>
                    </ul>
                  </div>

                  <Link 
                    v-if="$page.props.auth?.user" 
                    href="/pocs" 
                    class="btn btn-sm btn-dark rounded-pill px-3 py-2 fw-bold w-100"
                  >
                    ⚡ Partager un POC pour gagner des XP
                  </Link>
                  <Link 
                    v-else 
                    href="/login" 
                    class="btn btn-sm btn-warning rounded-pill px-3 py-2 fw-bold w-100"
                  >
                    Se connecter pour jouer
                  </Link>
                </div>

                <!-- If search yields no result -->
                <div v-else :key="'no-search'" class="text-center py-4 text-muted extra-small">
                  <i class="bi bi-search fs-3 d-block mb-1 text-secondary opacity-50"></i>
                  Aucun membre ne correspond à « {{ searchQuery }} » dans ce palier.
                </div>

              </transition>
            </div>

            <!-- Cadre Footer: Quick Level Progress / Action -->
            <div class="tier-roster-footer pt-3 mt-3 border-top">
              <div v-if="currentUserRank" class="d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted extra-small d-block">Votre progression</span>
                  <strong class="text-dark small fw-bold">{{ currentUserRank.xp }} XP cumulés</strong>
                </div>
                <Link 
                  href="/espace-utilisateur" 
                  class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold extra-small"
                >
                  Mon Espace 🚀
                </Link>
              </div>
              <div v-else class="text-center">
                <span class="text-muted extra-small d-block mb-2">Rejoignez l'arène des bâtisseurs tech</span>
                <Link 
                  href="/register" 
                  class="btn btn-sm btn-warning rounded-pill px-4 fw-bold text-dark w-100"
                >
                  Créer un compte & Commencer à 0 XP
                </Link>
              </div>
            </div>

          </div>
        </aside>

      </div>
    </main>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  levels: {
    type: Array,
    default: () => []
  },
  players: {
    type: Array,
    default: () => []
  },
  allUsers: {
    type: Array,
    default: () => []
  },
  totalPlayers: {
    type: Number,
    default: 0
  },
  totalXp: {
    type: Number,
    default: 0
  },
  currentUserRank: {
    type: Object,
    default: null
  },
  topThree: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const searchQuery = ref('')
const selectedTier = ref(null)

// Logo from SiteSetting
const logoUrl = computed(() => {
  const logo = page.props.siteSettings?.logo || 'assets/images/logo.png'
  return logo.startsWith('/') ? logo : '/' + logo
})

// Tiers ordered: Highest XP at the TOP (index 0) down to 0 XP at the BOTTOM
const sortedLevels = computed(() => {
  const list = [...(props.levels || [])].sort((a, b) => b.min_xp - a.min_xp)
  return list
})

// Initialize selectedTier on mount
onMounted(() => {
  if (props.currentUserRank?.tier) {
    const match = sortedLevels.value.find(
      t => t.id === props.currentUserRank.tier.id || t.min_xp === props.currentUserRank.tier.min_xp
    )
    if (match) {
      selectedTier.value = match
      return
    }
  }

  // Fallback to highest tier with players, or top tier
  const withPlayers = sortedLevels.value.find(t => t.players && t.players.length > 0)
  selectedTier.value = withPlayers || sortedLevels.value[0] || null
})

// Players for currently selected tier
const selectedTierPlayers = computed(() => {
  if (!selectedTier.value) return []
  return selectedTier.value.players || []
})

// Filtered players by search query
const filteredTierPlayers = computed(() => {
  const players = selectedTierPlayers.value
  if (!searchQuery.value.trim()) return players
  const q = searchQuery.value.toLowerCase().trim()
  return players.filter(p => (p.name || '').toLowerCase().includes(q))
})

// Action: Click a tier node to reveal its champions in the second frame
function selectTier(tier) {
  selectedTier.value = tier
  searchQuery.value = ''
}

// Helpers
function isCurrentUserTier(tier) {
  if (!props.currentUserRank || !props.currentUserRank.tier) return false
  return (
    props.currentUserRank.tier.id === tier.id ||
    props.currentUserRank.tier.min_xp === tier.min_xp
  )
}

function isCurrentLoggedInUser(player) {
  return page.props.auth?.user && page.props.auth.user.id === player.id
}

function getInitials(name) {
  if (!name) return 'U'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const colorPalette = [
  '#059669', '#2563EB', '#D97706', '#DC2626', '#7C3AED', '#DB2777', '#0891B2'
]

function getPlayerColor(name) {
  if (!name) return '#059669'
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

// Serpentine alignment: zigzag pattern between center-left, center, center-right
function getSerpentineAlignment(index, total) {
  const mod = index % 4
  if (mod === 0) return 'justify-content-center'
  if (mod === 1) return 'justify-content-start ps-4 ps-md-5'
  if (mod === 2) return 'justify-content-center'
  return 'justify-content-end pe-4 pe-md-5'
}

// Dynamic curved SVG path between the points
const computedCurvedSvgPath = computed(() => {
  const n = sortedLevels.value.length || 7
  let path = ''
  const startY = 120
  const endY = 1320
  const stepY = (endY - startY) / Math.max(n - 1, 1)

  for (let i = 0; i < n; i++) {
    const y = startY + i * stepY
    const mod = i % 4
    let x = 340 // center
    if (mod === 1) x = 160 // left
    if (mod === 3) x = 520 // right

    if (i === 0) {
      path += `M ${x} ${y}`
    } else {
      const prevY = startY + (i - 1) * stepY
      const prevMod = (i - 1) % 4
      let prevX = 340
      if (prevMod === 1) prevX = 160
      if (prevMod === 3) prevX = 520

      const cY1 = prevY + stepY * 0.5
      const cY2 = y - stepY * 0.5
      path += ` C ${prevX} ${cY1}, ${x} ${cY2}, ${x} ${y}`
    }
  }

  return path
})

// Disc Styling with physical 3D button depth & candy gradient
function getTierDiscStyle(tier, index) {
  const baseColor = tier.color || '#00A859'
  const isSelected = selectedTier.value && selectedTier.value.id === tier.id

  return {
    background: `linear-gradient(145deg, ${lightenColor(baseColor, 20)} 0%, ${baseColor} 70%, ${darkenColor(baseColor, 20)} 100%)`,
    borderColor: lightenColor(baseColor, 40),
    boxShadow: isSelected 
      ? `0 12px 28px ${hexToRgba(baseColor, 0.45)}, 0 4px 0 ${darkenColor(baseColor, 35)}`
      : `0 8px 18px ${hexToRgba(baseColor, 0.3)}, 0 4px 0 ${darkenColor(baseColor, 35)}`
  }
}

function getTierCapsuleStyle(tier) {
  const baseColor = tier.color || '#00A859'
  return {
    backgroundColor: '#FFFFFF',
    color: '#0F172A',
    border: `2px solid ${baseColor}`
  }
}

function getRosterHeaderStyle(tier) {
  const baseColor = tier.color || '#00A859'
  return {
    background: `linear-gradient(135deg, ${hexToRgba(baseColor, 0.12)} 0%, #FFFFFF 100%)`,
    border: `1.5px solid ${hexToRgba(baseColor, 0.25)}`
  }
}

// Color arithmetic helpers for custom lighting
function hexToRgba(hex, alpha = 1) {
  if (!hex || !hex.startsWith('#')) return `rgba(0, 168, 89, ${alpha})`
  const clean = hex.replace('#', '')
  const r = parseInt(clean.substring(0, 2), 16) || 0
  const g = parseInt(clean.substring(2, 4), 16) || 0
  const b = parseInt(clean.substring(4, 6), 16) || 0
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

function lightenColor(col, percent) {
  if (!col || !col.startsWith('#')) return '#10B981'
  const num = parseInt(col.replace('#', ''), 16)
  const amt = Math.round(2.55 * percent)
  const R = (num >> 16) + amt
  const G = (num >> 8 & 0x00FF) + amt
  const B = (num & 0x0000FF) + amt
  return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 + (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 + (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1)
}

function darkenColor(col, percent) {
  return lightenColor(col, -percent)
}
</script>

<style scoped>
/* Page Root & Ambient Canvas */
.champions-arena-root {
  background: radial-gradient(circle at 20% 15%, #FFFBEB 0%, #F8FAFC 50%, #F1F5F9 100%);
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #1E293B;
  min-height: 100vh;
}

/* Header */
.arena-header {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(14px);
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  z-index: 1020;
}

.arena-logo {
  height: 40px;
  max-width: 170px;
  object-fit: contain;
}

.arena-badge-tag {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #D97706;
  display: flex;
  align-items: center;
  gap: 5px;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  background-color: #10B981;
  border-radius: 999px;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
  animation: pulseBeacon 1.8s infinite;
}

@keyframes pulseBeacon {
  0% { transform: scale(0.9); opacity: 0.7; }
  50% { transform: scale(1.3); opacity: 1; }
  100% { transform: scale(0.9); opacity: 0.7; }
}

.arena-tagline {
  font-size: 12px;
  font-weight: 600;
  color: #475569;
}

.stat-icon-coin {
  font-size: 16px;
}

/* Return Home Button */
.btn-return-home {
  background: linear-gradient(180deg, #F59E0B 0%, #D97706 100%);
  color: #FFFFFF !important;
  font-weight: 700;
  font-size: 13px;
  padding: 8px 18px;
  border-radius: 999px;
  box-shadow: 0 3px 0 #B45309, 0 6px 14px rgba(217, 119, 6, 0.25);
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.btn-return-home:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 0 #B45309, 0 8px 18px rgba(217, 119, 6, 0.35);
}

.btn-return-home:active {
  transform: translateY(2px);
  box-shadow: 0 1px 0 #B45309;
}

/* Left Map Section Container */
.map-card-wrapper {
  background: linear-gradient(180deg, #FFFFFF 0%, #FEFCE8 60%, #FEF3C7 100%);
  border: 1px solid #FDE68A;
  min-height: calc(100vh - 120px);
}

/* Floating background islands and decor */
.bg-island {
  position: absolute;
  border-radius: 999px;
  filter: blur(60px);
  pointer-events: none;
  opacity: 0.45;
}

.island-top-right {
  top: -60px;
  right: -60px;
  width: 320px;
  height: 320px;
  background: #FDE047;
}

.island-bottom-left {
  bottom: -40px;
  left: -40px;
  width: 280px;
  height: 280px;
  background: #BBF7D0;
}

.cloud-float {
  position: absolute;
  font-size: 28px;
  pointer-events: none;
  opacity: 0.7;
  animation: floatCloud 8s infinite alternate ease-in-out;
}

.cloud-pos-1 { top: 60px; left: 30px; animation-duration: 9s; }
.cloud-pos-2 { top: 220px; right: 40px; animation-duration: 11s; }
.cloud-pos-3 { bottom: 300px; left: 50px; animation-duration: 7s; }
.cloud-pos-4 { bottom: 120px; right: 60px; animation-duration: 10s; }

@keyframes floatCloud {
  0% { transform: translateY(0px) translateX(0px); }
  50% { transform: translateY(-12px) translateX(8px); }
  100% { transform: translateY(6px) translateX(-6px); }
}

/* Summit Header */
.summit-crest-box {
  max-width: 540px;
}

.summit-crown-badge {
  width: 58px;
  height: 58px;
  border-radius: 999px;
  background: linear-gradient(135deg, #FEF08A 0%, #F59E0B 100%);
  border: 3px solid #FDE68A;
  box-shadow: 0 8px 20px rgba(245, 158, 11, 0.35);
  font-size: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: crownBob 3s infinite ease-in-out;
}

@keyframes crownBob {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}

.summit-heading {
  font-size: clamp(22px, 3vw, 32px);
  font-weight: 900;
  letter-spacing: -0.02em;
  color: #0F172A;
}

.summit-lead {
  font-size: 14px;
}

/* Serpentine Track & SVG Line */
.roadmap-connecting-svg {
  top: 0;
  left: 0;
  z-index: 1;
}

.svg-dash-animated {
  animation: dashAnimation 25s linear infinite;
}

@keyframes dashAnimation {
  from { stroke-dashoffset: 0; }
  to { stroke-dashoffset: 400; }
}

/* Pod & Interactive Disc */
.tier-milestone-pod {
  cursor: pointer;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  user-select: none;
}

/* Hover effect on XP tier */
.tier-milestone-pod:hover {
  transform: translateY(-8px) scale(1.08);
}

.tier-milestone-pod:active {
  transform: translateY(-2px) scale(1.02);
}

.pod-selected-halo {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  width: 104px;
  height: 104px;
  border-radius: 999px;
  border: 3px dashed #F59E0B;
  animation: haloSpin 12s linear infinite;
  pointer-events: none;
}

@keyframes haloSpin {
  from { transform: translateX(-50%) rotate(0deg); }
  to { transform: translateX(-50%) rotate(360deg); }
}

.summit-star-trophy {
  top: -26px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 24px;
}

.trophy-bounce {
  display: inline-block;
  animation: trophyBob 2s infinite ease-in-out;
}

@keyframes trophyBob {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

/* Candy 3D Disc */
.candy-xp-disc {
  width: 80px;
  height: 80px;
  border-radius: 999px;
  border: 4px solid #FFFFFF;
  position: relative;
  overflow: visible;
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.disc-gloss-shine {
  position: absolute;
  top: 4px;
  left: 10px;
  right: 10px;
  height: 28px;
  border-radius: 999px;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.7) 0%, rgba(255, 255, 255, 0) 100%);
  pointer-events: none;
}

.disc-xp-val {
  font-size: 22px;
  color: #FFFFFF;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.35);
  line-height: 1;
}

.disc-xp-label {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.05em;
  color: rgba(255, 255, 255, 0.95);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.active-check-badge {
  position: absolute;
  bottom: -4px;
  right: -4px;
  width: 24px;
  height: 24px;
  background: #0F172A;
  color: #FBBF24;
  border-radius: 999px;
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 5px rgba(0,0,0,0.3);
  border: 2px solid #FFFFFF;
}

/* Current user pin on map */
.current-user-pin-chip {
  top: -36px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
}

.pin-label {
  background: #DC2626;
  color: #FFFFFF;
  font-size: 11px;
  font-weight: 800;
  padding: 3px 9px;
  border-radius: 999px;
  box-shadow: 0 3px 8px rgba(220, 38, 38, 0.4);
  border: 2px solid #FFFFFF;
}

/* Label under disc */
.tier-label-capsule {
  font-size: 13px;
  transition: all 0.25s ease;
}

.tier-milestone-pod:hover .tier-label-capsule {
  transform: translateY(2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
}

/* Avatars clustered at milestone */
.avatar-tiny-ring {
  width: 22px;
  height: 22px;
  border: 2px solid #FFFFFF;
  overflow: hidden;
}

/* Start gate pill at bottom */
.start-gate-pill {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  border: 3px solid #6EE7B7;
}

/* RIGHT PANEL: Champions Cadre */
.champions-cadre-card {
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  max-height: calc(100vh - 120px);
}

.tier-header-icon {
  width: 52px;
  height: 52px;
  flex-shrink: 0;
}

/* Scroll area for champions list */
.champions-roster-scroll {
  scrollbar-width: thin;
  scrollbar-color: #CBD5E1 transparent;
}

.player-champion-item {
  border-color: #F1F5F9 !important;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.player-champion-item:hover {
  transform: translateX(4px);
  background-color: #F8FAFC !important;
  border-color: #E2E8F0 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
}

.player-champion-item.highlight-user {
  background-color: #ECFDF5 !important;
  border-color: #A7F3D0 !important;
}

.player-name-link {
  transition: color 0.15s ease;
}

.player-name-link:hover {
  color: #059669 !important;
}

.player-xp-chip {
  background-color: #FEF3C7;
  color: #92400E;
  font-size: 11px;
}

.user-self-dot {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 12px;
  height: 12px;
  background-color: #10B981;
  border: 2px solid #FFFFFF;
  border-radius: 999px;
}

.btn-visit-profile:hover {
  background-color: #0F172A !important;
  color: #FFFFFF !important;
}

.btn-visit-profile:hover i {
  color: #FFFFFF !important;
}

/* Transitions */
.tier-header-fade-enter-active,
.tier-header-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.tier-header-fade-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}

.tier-header-fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

.roster-switch-enter-active,
.roster-switch-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.roster-switch-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.roster-switch-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Utility classes */
.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.extra-small {
  font-size: 12px;
}

.-space-x-2 > * + * {
  margin-left: -6px;
}

@media (max-width: 991.98px) {
  .champions-cadre-card {
    position: static !important;
    max-height: none;
  }
}
</style>
