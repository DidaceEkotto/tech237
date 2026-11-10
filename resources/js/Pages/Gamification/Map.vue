<template>
  <FrontLayout>
    <Head title="La Map des Champions & Gagnants • Candy Map Tech237" />

    <div class="candy-world-wrapper py-4 position-relative overflow-hidden">
      
      <!-- Top Candy HUD Navigation Bar matching screenshots -->
      <div class="container mb-4" style="max-width: 900px;">
        <div class="candy-top-hud p-2 px-3 rounded-pill d-flex align-items-center justify-content-between shadow-lg mb-3">
          <Link href="/" class="btn btn-sm candy-hud-btn rounded-pill px-3 py-1 fw-bold text-white d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span class="d-none d-sm-inline">Accueil</span>
          </Link>

          <!-- Lives / Hearts & Gold Ingot Stats (Candy Style) -->
          <div class="d-flex align-items-center gap-2 gap-sm-3">
            <!-- Heart Badge -->
            <div class="candy-stat-pill bg-danger text-white rounded-pill px-2 py-1 d-flex align-items-center gap-1 shadow-xs" title="Énergie Communautaire">
              <span class="fs-6">❤️</span>
              <strong class="small">Plein</strong>
            </div>

            <!-- Gold Bar XP Badge -->
            <div class="candy-stat-pill rounded-pill px-2 py-1 d-flex align-items-center gap-1 shadow-xs" style="background: linear-gradient(180deg, #FDE68A 0%, #D97706 100%); color: #78350F;" :title="'Total XP du site : ' + totalXp">
              <span class="fs-6">🏆</span>
              <strong class="small">{{ totalXp }} XP</strong>
            </div>

            <!-- Players count -->
            <div class="candy-stat-pill bg-primary text-white rounded-pill px-2 py-1 d-flex align-items-center gap-1 shadow-xs">
              <span class="fs-6">👥</span>
              <strong class="small">{{ totalPlayers }}</strong>
            </div>
          </div>

          <!-- Trophy Button -->
          <button @click="showRankingModal = true" class="btn btn-sm candy-hud-trophy rounded-pill px-3 py-1 fw-bold text-dark d-flex align-items-center gap-1 shadow-xs">
            <span class="fs-6">🥇</span>
            <span class="d-none d-md-inline">Classement</span>
          </button>
        </div>

        <!-- Banner Title Ribbon -->
        <div class="text-center my-3 position-relative">
          <div class="candy-ribbon-title mx-auto px-4 py-2 text-white fw-bolder shadow-md">
            🍬 LA MAP DES GAGNANTS &bull; TECH237 🚀
          </div>
          <p class="text-dark fw-bold small mt-2 mb-0" style="text-shadow: 0 1px 2px rgba(255,255,255,0.8);">
            Franchissez les étapes en publiant des POCs, commentant et résolvant des défis !
          </p>
        </div>
      </div>

      <!-- Main Game Map Container -->
      <div class="container position-relative" style="max-width: 860px;">
        
        <!-- Floating User Status Pin (If Logged In) -->
        <div v-if="currentUserRank" class="card border-0 rounded-4 shadow-md p-3 mb-4 bg-white bg-opacity-95 text-dark" style="border: 2px solid #F472B6 !important; backdrop-filter: blur(8px);">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="position-relative">
                <img 
                  v-if="$page.props.auth?.user?.avatar" 
                  :src="$page.props.auth?.user?.avatar.startsWith('/') ? $page.props.auth?.user?.avatar : '/' + $page.props.auth?.user?.avatar" 
                  class="rounded-circle border border-2 border-warning shadow-sm" 
                  style="width: 48px; height: 48px; object-fit: cover;"
                />
                <div v-else class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center fs-5 shadow-sm" style="width: 48px; height: 48px;">
                  {{ ($page.props.auth?.user?.name || 'Moi').substring(0, 2).toUpperCase() }}
                </div>
                <span class="position-absolute -top-1 -end-1 badge rounded-pill bg-danger p-1" style="font-size: 9px;" title="Vous êtes ici !">
                  📍
                </span>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2">
                  <strong class="fs-6 text-dark mb-0">{{ $page.props.auth?.user?.name }}</strong>
                  <span class="badge bg-warning text-dark rounded-pill extra-small fw-bold">
                    Rang #{{ currentUserRank.position }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-2 mt-1">
                  <span class="badge rounded-pill extra-small px-2 py-0 fw-bold text-white shadow-xs" :style="{ backgroundColor: currentUserRank.tier?.color || '#00A859' }">
                    <i class="bi" :class="currentUserRank.tier?.badge_icon || 'bi-award'"></i> {{ currentUserRank.tier?.name || 'Novice Tech' }}
                  </span>
                  <span class="extra-small text-muted fw-semibold">{{ currentUserRank.xp }} XP</span>
                </div>
              </div>
            </div>

            <!-- Next Milestone preview -->
            <div v-if="currentUserRank.nextTier" class="text-end">
              <div class="extra-small text-muted mb-1">
                Prochain palier : <strong class="text-dark">{{ currentUserRank.nextTier.name }}</strong> (+{{ currentUserRank.xpNeeded }} XP)
              </div>
              <div class="progress" style="height: 8px; width: 140px; background-color: #E2E8F0; border-radius: 999px;">
                <div 
                  class="progress-bar progress-bar-striped progress-bar-animated bg-success rounded-pill" 
                  role="progressbar" 
                  :style="{ width: currentUserRank.progressPercent + '%' }"
                ></div>
              </div>
            </div>
            <div v-else class="text-end">
              <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold">
                👑 Niveau Maximal Atteint !
              </span>
            </div>
          </div>
        </div>

        <!-- Serpentine Candy Winding Trail -->
        <div class="candy-board-path position-relative py-5 px-2 px-md-5 rounded-5 shadow-lg overflow-hidden">
          
          <!-- Animated Background Clouds & Castle Elements -->
          <div class="candy-decor cloud-1">☁️</div>
          <div class="candy-decor cloud-2">☁️</div>
          <div class="candy-decor cloud-3">🍬</div>
          <div class="candy-decor cloud-4">🍭</div>
          <div class="candy-decor castle-decor">🏰</div>
          <div class="candy-decor tree-decor">🌳</div>

          <!-- Winding Levels List -->
          <div class="levels-trail-container d-flex flex-column gap-5 position-relative" style="z-index: 5;">
            
            <div 
              v-for="(tier, index) in tiersWithProgress" 
              :key="tier.id"
              class="candy-level-row d-flex align-items-center position-relative"
              :class="getRowAlignmentClass(index)"
            >
              <!-- Milestone Candy Node -->
              <div 
                class="candy-milestone-node text-center position-relative transition hover-scale-sm"
                :class="{ 'is-current-tier': isCurrentUserAtTier(tier), 'is-unlocked': isTierUnlocked(tier) }"
              >
                <!-- Crown on Top for highest tiers -->
                <div v-if="index === tiersWithProgress.length - 1" class="candy-crown-icon position-absolute" style="top: -24px; left: 50%; transform: translateX(-50%); font-size: 24px;">
                  👑
                </div>

                <!-- Three Stars Rating (Candy Style) -->
                <div class="candy-stars mb-1 d-flex justify-content-center gap-1" style="font-size: 11px;">
                  <span :class="isTierUnlocked(tier) ? 'star-gold' : 'star-dim'">⭐</span>
                  <span :class="isTierUnlocked(tier) ? 'star-gold' : 'star-dim'">⭐</span>
                  <span :class="isTierUnlocked(tier) ? 'star-gold' : 'star-dim'">⭐</span>
                </div>

                <!-- The Circular Level Button Disk -->
                <div 
                  class="candy-disk-button rounded-circle d-flex flex-column align-items-center justify-content-center shadow-lg mx-auto position-relative"
                  :style="getTierDiskStyle(tier, index)"
                >
                  <span class="candy-node-num fw-bolder">{{ tier.min_xp }}</span>
                  <span class="candy-node-unit extra-small">XP</span>

                  <!-- Active User Pin overlay if current user is at this level -->
                  <div v-if="isCurrentUserAtTier(tier)" class="current-user-pin-bubble position-absolute">
                    <div class="pin-pulse-halo"></div>
                    <div class="pin-badge-tag text-nowrap">Vous êtes ici ! 📍</div>
                  </div>
                </div>

                <!-- Level Name Ribbon Under Node -->
                <div class="candy-node-label mt-2 px-3 py-1 rounded-pill fw-bold text-nowrap shadow-xs" :style="getTierLabelStyle(tier)">
                  <i class="bi" :class="tier.badge_icon || 'bi-award'"></i>
                  <span class="ms-1">{{ tier.name }}</span>
                </div>

                <!-- Players Perched at this Level (Avatar stack) -->
                <div v-if="tier.players && tier.players.length" class="players-at-node-stack d-flex justify-content-center align-items-center mt-2">
                  <div class="d-flex align-items-center -space-x-2">
                    <Link 
                      v-for="player in tier.players.slice(0, 4)" 
                      :key="player.id"
                      :href="'/communaute/profil/' + player.id"
                      class="player-pin-avatar rounded-circle border border-2 border-white shadow-sm position-relative text-decoration-none"
                      :title="player.name + ' (' + (player.xp || 0) + ' XP) • Voir profil'"
                    >
                      <img 
                        v-if="player.avatar" 
                        :src="player.avatar.startsWith('/') ? player.avatar : '/' + player.avatar" 
                        :alt="player.name" 
                        class="w-100 h-100 rounded-circle object-fit-cover"
                      />
                      <div 
                        v-else 
                        class="w-100 h-100 rounded-circle text-white fw-bold d-flex align-items-center justify-content-center"
                        :style="{ backgroundColor: getPlayerColor(player.name), fontSize: '10px' }"
                      >
                        {{ getInitials(player.name) }}
                      </div>
                    </Link>

                    <!-- More indicator -->
                    <span 
                      v-if="tier.players.length > 4" 
                      class="player-pin-avatar bg-white text-dark rounded-circle border border-2 border-white shadow-sm fw-bold extra-small d-flex align-items-center justify-content-center"
                      style="font-size: 10px; width: 26px; height: 26px;"
                      :title="'+' + (tier.players.length - 4) + ' autres champions'"
                    >
                      +{{ tier.players.length - 4 }}
                    </span>
                  </div>
                </div>

              </div>
            </div>

          </div>

          <!-- Bottom Finish / Start Island -->
          <div class="text-center mt-5 position-relative z-1">
            <div class="candy-start-island p-3 px-4 rounded-pill d-inline-block text-white fw-bold shadow-md">
              🏁 DÉPART DU PARCOURS &bull; NIVEAU 0 XP
            </div>
          </div>

        </div>

      </div>

      <!-- Ranking Modal / Right Side Slide-out -->
      <div v-if="showRankingModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content rounded-4 border-0 shadow-2xl overflow-hidden">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #FDE68A 0%, #F59E0B 100%);">
              <div class="d-flex align-items-center gap-2">
                <span class="fs-4">🏆</span>
                <div>
                  <h5 class="modal-title fw-bolder text-dark mb-0">Classement des Champions</h5>
                  <small class="text-dark opacity-75">Hall of Fame Tech237</small>
                </div>
              </div>
              <button type="button" class="btn-close" @click="showRankingModal = false"></button>
            </div>
            
            <div class="modal-body p-3 bg-light">
              <div class="d-flex flex-column gap-2">
                <Link 
                  v-for="(user, uIdx) in allRankedUsers" 
                  :key="user.id"
                  :href="'/communaute/profil/' + user.id"
                  class="d-flex align-items-center justify-content-between p-2 px-3 rounded-3 bg-white border text-decoration-none text-dark transition hover-shadow-sm"
                  :style="uIdx === 0 ? 'background-color: #FEF9C3 !important; border-color: #FEF08A !important;' : ''"
                >
                  <div class="d-flex align-items-center gap-3 min-w-0">
                    <span class="fw-bold fs-6 text-center" style="width: 26px;">
                      <span v-if="uIdx === 0">🥇</span>
                      <span v-else-if="uIdx === 1">🥈</span>
                      <span v-else-if="uIdx === 2">🥉</span>
                      <span v-else class="text-muted small">#{{ uIdx + 1 }}</span>
                    </span>

                    <img 
                      v-if="user.avatar" 
                      :src="user.avatar.startsWith('/') ? user.avatar : '/' + user.avatar" 
                      class="rounded-circle border" 
                      style="width: 40px; height: 40px; object-fit: cover;"
                    />
                    <div 
                      v-else 
                      class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs"
                      :style="{ width: '40px', height: '40px', backgroundColor: getPlayerColor(user.name), fontSize: '13px' }"
                    >
                      {{ getInitials(user.name) }}
                    </div>

                    <div class="text-truncate">
                      <strong class="d-block text-truncate text-dark small">{{ user.name }}</strong>
                      <span class="badge rounded-pill extra-small px-2 py-0" style="background-color: #FEF3C7; color: #92400E; font-size: 10px;">
                        {{ user.rank_title || 'Novice Tech 🌱' }}
                      </span>
                    </div>
                  </div>

                  <div class="text-end ps-2 flex-shrink-0">
                    <span class="badge rounded-pill fw-bold extra-small px-2 py-1 shadow-2xs" style="background-color: #D1FAE5; color: #065F46;">
                      {{ user.xp || 0 }} XP
                    </span>
                  </div>
                </Link>
              </div>
            </div>

            <div class="modal-footer border-0 bg-white">
              <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold text-dark w-100" @click="showRankingModal = false">
                Fermer & Continuer le Jeu 🚀
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  levels: {
    type: Array,
    default: () => []
  },
  players: {
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
  }
})

const page = usePage()
const showRankingModal = ref(false)

const allRankedUsers = computed(() => {
  return [...(props.players || [])].sort((a, b) => (b.xp || 0) - (a.xp || 0))
})

const tiersWithProgress = computed(() => {
  const list = [...(props.levels || [])].sort((a, b) => a.min_xp - b.min_xp)
  return list.map(t => {
    const playersHere = (props.players || []).filter(p => {
      const uTierId = p.current_tier_id || null
      return uTierId === t.id
    })
    return {
      ...t,
      players: playersHere
    }
  })
})

function getRowAlignmentClass(index) {
  // Alternate serpentine pattern: center-left, center, center-right, center, repeat
  const mod = index % 4
  if (mod === 0) return 'justify-content-center'
  if (mod === 1) return 'justify-content-start ps-md-5'
  if (mod === 2) return 'justify-content-center'
  return 'justify-content-end pe-md-5'
}

function isCurrentUserAtTier(tier) {
  if (!props.currentUserRank || !props.currentUserRank.tier) return false
  return props.currentUserRank.tier.id === tier.id
}

function isTierUnlocked(tier) {
  if (!props.currentUserRank) return false
  return (props.currentUserRank.xp || 0) >= tier.min_xp
}

function getTierDiskStyle(tier, index) {
  const colors = [
    'linear-gradient(180deg, #60A5FA 0%, #2563EB 100%)', // Blue
    'linear-gradient(180deg, #34D399 0%, #059669 100%)', // Emerald
    'linear-gradient(180deg, #FBBF24 0%, #D97706 100%)', // Amber
    'linear-gradient(180deg, #F472B6 0%, #DB2777 100%)', // Pink
    'linear-gradient(180deg, #A78BFA 0%, #7C3AED 100%)', // Purple
  ]
  const bg = colors[index % colors.length]
  return {
    background: bg,
    border: '4px solid #FFFFFF',
    boxShadow: '0 8px 18px rgba(0,0,0,0.22), inset 0 2px 4px rgba(255,255,255,0.6)'
  }
}

function getTierLabelStyle(tier) {
  return {
    backgroundColor: '#FFFFFF',
    color: '#1F2937',
    border: '2px solid #E5E7EB',
    fontSize: '12px'
  }
}

function getInitials(name) {
  if (!name) return 'U'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase()
  return (parts[0][0] + parts[1][0]).toUpperCase()
}

function getPlayerColor(name) {
  const colors = ['#059669', '#2563EB', '#D97706', '#DB2777', '#7C3AED', '#DC2626']
  let hash = 0
  for (let i = 0; i < (name || '').length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash) % colors.length]
}
</script>

<style scoped>
.candy-world-wrapper {
  background: linear-gradient(180deg, #67E8F9 0%, #93C5FD 22%, #DDD6FE 50%, #FBCFE8 78%, #FDE68A 100%);
  min-height: 100vh;
}

/* Candy Top HUD */
.candy-top-hud {
  background: linear-gradient(180deg, #F472B6 0%, #EC4899 100%);
  border: 3px solid #FDF2F8;
}
.candy-hud-btn {
  background: rgba(255, 255, 255, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.4);
}
.candy-hud-trophy {
  background: linear-gradient(180deg, #FDE68A 0%, #F59E0B 100%);
  border: 1px solid #FEF3C7;
}

/* Ribbon Title */
.candy-ribbon-title {
  display: inline-block;
  background: linear-gradient(180deg, #EC4899 0%, #BE185D 100%);
  border: 3px solid #FFF;
  border-radius: 999px;
  font-size: 1.15rem;
  letter-spacing: 1px;
}

/* Candy Board Path */
.candy-board-path {
  background: radial-gradient(circle at 50% 30%, rgba(255, 255, 255, 0.85) 0%, rgba(240, 249, 255, 0.75) 100%);
  border: 4px solid #FFFFFF;
  backdrop-filter: blur(10px);
}

/* Floating decors */
.candy-decor {
  position: absolute;
  pointer-events: none;
  user-select: none;
  opacity: 0.65;
  font-size: 38px;
}
.cloud-1 { top: 40px; left: 30px; animation: floatCloud 8s infinite alternate ease-in-out; }
.cloud-2 { top: 280px; right: 40px; animation: floatCloud 10s infinite alternate ease-in-out; }
.cloud-3 { top: 520px; left: 40px; font-size: 32px; }
.cloud-4 { top: 760px; right: 50px; font-size: 32px; }
.castle-decor { bottom: 80px; right: 30px; font-size: 52px; opacity: 0.85; }
.tree-decor { bottom: 120px; left: 30px; font-size: 44px; opacity: 0.85; }

@keyframes floatCloud {
  0% { transform: translateY(0); }
  100% { transform: translateY(18px); }
}

/* Candy Disks */
.candy-disk-button {
  width: 72px;
  height: 72px;
  cursor: pointer;
}
.candy-node-num {
  font-size: 20px;
  color: #FFFFFF;
  line-height: 1;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
}
.candy-node-unit {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 700;
  font-size: 9px;
}

/* Player Pin Bubble */
.current-user-pin-bubble {
  top: -16px;
  right: -24px;
}
.pin-badge-tag {
  background: #EF4444;
  color: #FFF;
  font-size: 9px;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 999px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
  animation: bouncePin 1.2s infinite ease-in-out;
}
@keyframes bouncePin {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}

.player-pin-avatar {
  width: 28px;
  height: 28px;
  margin-left: -6px;
  transition: transform 0.2s ease;
}
.player-pin-avatar:hover {
  transform: scale(1.25);
  z-index: 10;
}

.star-gold {
  color: #FBBF24;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}
.star-dim {
  color: #CBD5E1;
  opacity: 0.5;
}

.candy-start-island {
  background: linear-gradient(180deg, #10B981 0%, #047857 100%);
  border: 3px solid #FFF;
  font-size: 13px;
  letter-spacing: 1px;
}

.hover-scale-sm:hover {
  transform: scale(1.06);
}
.transition {
  transition: all 0.25s ease;
}
</style>
