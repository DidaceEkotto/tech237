<template>
  <FrontLayout>
    <Head title="La Map des Champions & Gagnants • Gamification Tech237" />

    <div class="gamification-map-page py-5" style="background: radial-gradient(circle at 50% 20%, #0F172A 0%, #070D1E 100%); min-height: 90vh; color: #fff;">
      <div class="container" style="max-width: 960px;">
        
        <!-- Header & Breadcrumbs -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
          <Link href="/" class="btn btn-sm btn-outline-light rounded-pill px-3 py-2 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Retour à l'accueil
          </Link>
          <div class="d-flex gap-2">
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
              <i class="bi bi-controller me-1"></i> Parcours d'Évolution Gamifié
            </span>
          </div>
        </div>

        <!-- Hero Header -->
        <div class="card border-0 rounded-4 shadow-lg p-4 p-md-5 mb-5 text-center text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(59, 130, 246, 0.15) 100%); border: 1px solid rgba(255, 255, 255, 0.1) !important; backdrop-filter: blur(10px);">
          
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 text-warning fw-bold extra-small bg-black bg-opacity-40 border border-warning border-opacity-25 mx-auto">
            <i class="bi bi-trophy-fill"></i>
            <span>Saison Tech237 • Écosystème Actif</span>
          </div>

          <h1 class="display-6 fw-bold mb-2">
            La Map des <span style="color: #FBBF24;">Champions</span> &amp; Gagnants
          </h1>
          <p class="text-white-50 small mb-4" style="max-width: 580px; margin: 0 auto;">
            Grimpez les échelons en contribuant au site : proposez des mini-actualités (POCs), résolvez des problèmes, commentez et livrez des projets pour débloquer de nouveaux rangs légendaires.
          </p>

          <!-- Metrics Bar -->
          <div class="row g-3 justify-content-center pt-3 border-top border-white border-opacity-10">
            <div class="col-6 col-md-3">
              <div class="p-2">
                <div class="fs-4 fw-bold text-warning">{{ totalXp }} XP</div>
                <div class="extra-small text-white-50">Total XP Distribués</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="p-2">
                <div class="fs-4 fw-bold text-success">{{ totalPlayers }}</div>
                <div class="extra-small text-white-50">Contributeurs Classés</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="p-2">
                <div class="fs-4 fw-bold text-info">{{ levels.length }}</div>
                <div class="extra-small text-white-50">Paliers d'Évolution</div>
              </div>
            </div>
            <div v-if="currentUserRank" class="col-6 col-md-3">
              <div class="p-2">
                <div class="fs-4 fw-bold text-pink" style="color: #EC4899;">#{{ currentUserRank.position }}</div>
                <div class="extra-small text-white-50">Votre Rang Actuel</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Current User Status Card (If Logged In) -->
        <div v-if="currentUserRank" class="card border-0 rounded-4 shadow-sm p-3 p-md-4 mb-5 text-white bg-black bg-opacity-40 border border-success border-opacity-25">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="position-relative">
                <img 
                  v-if="$page.props.auth?.user?.avatar" 
                  :src="$page.props.auth?.user?.avatar.startsWith('/') ? $page.props.auth?.user?.avatar : '/' + $page.props.auth?.user?.avatar" 
                  class="rounded-circle border border-2 border-success" 
                  style="width: 52px; height: 52px; object-fit: cover;"
                />
                <div v-else class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center fs-5 shadow-xs" style="width: 52px; height: 52px;">
                  {{ ($page.props.auth?.user?.name || 'Moi').substring(0, 2).toUpperCase() }}
                </div>
                <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-warning p-1" style="font-size: 8px;">
                  📍
                </span>
              </div>
              <div>
                <strong class="d-block text-white fs-6 mb-0">{{ $page.props.auth?.user?.name }}</strong>
                <span class="badge rounded-pill extra-small px-3 py-1 fw-bold text-white shadow-xs" :style="{ backgroundColor: currentUserRank.tier?.color || '#00A859' }">
                  <i class="bi" :class="currentUserRank.tier?.badge_icon || 'bi-award'"></i> {{ currentUserRank.tier?.name || 'Novice Tech' }}
                </span>
              </div>
            </div>

            <!-- Progress to next tier -->
            <div class="text-md-end">
              <div class="small fw-bold text-white mb-1">
                {{ currentUserRank.xp }} XP cumulés
              </div>
              <div v-if="currentUserRank.next_tier" class="extra-small text-white-50">
                Prochain jalon : <strong class="text-warning">{{ currentUserRank.next_tier.name }}</strong> ({{ currentUserRank.next_tier.min_xp - currentUserRank.xp }} XP restants)
              </div>
              <div v-else class="extra-small text-warning fw-bold">
                👑 Vous avez atteint le rang suprême !
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- SERPENTINE CANDY CRUSH ROADMAP JOURNEY     -->
        <!-- ========================================== -->
        <div class="candy-crush-roadmap position-relative py-4">
          
          <!-- Central Winding Pathway Background Line -->
          <div class="roadmap-spine-path"></div>

          <!-- Milestones Nodes List -->
          <div class="d-flex flex-column gap-5 position-relative z-1">
            <div 
              v-for="(level, idx) in levels" 
              :key="level.id"
              class="roadmap-node-row d-flex align-items-center"
              :class="getNodeAlignmentClass(idx)"
            >
              
              <div class="roadmap-stage-card card border-0 rounded-4 shadow-lg p-4 position-relative" :style="{ borderColor: level.color || '#00A859' }">
                
                <!-- "VOUS ÊTES ICI" PIN (If current user is at this level) -->
                <div 
                  v-if="isCurrentUserAtLevel(level)" 
                  class="current-user-pin animate-bounce"
                >
                  <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold shadow-lg d-inline-flex align-items-center gap-1">
                    <span>📍 Vous êtes ici !</span>
                  </span>
                </div>

                <!-- Stage Header: Icon & Min XP -->
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                  <div class="d-flex align-items-center gap-2">
                    <div 
                      class="stage-milestone-circle rounded-circle shadow-md d-flex align-items-center justify-content-center text-white"
                      :style="{ backgroundColor: level.color || '#00A859' }"
                    >
                      <i class="bi fs-4" :class="level.badge_icon || 'bi-trophy-fill'"></i>
                    </div>
                    <div>
                      <span class="extra-small text-white-50 text-uppercase fw-bold d-block">Étape #{{ idx + 1 }}</span>
                      <h4 class="h6 fw-bold text-white mb-0">{{ level.name }}</h4>
                    </div>
                  </div>

                  <span class="badge rounded-pill px-3 py-1 fw-bold text-white shadow-xs" :style="{ backgroundColor: level.color || '#00A859' }">
                    {{ level.min_xp }} XP
                  </span>
                </div>

                <!-- Description / Objectives -->
                <p class="extra-small text-white-50 mb-3 leading-relaxed">
                  {{ level.description || 'Progressez dans la communauté pour franchir ce cap décisif !' }}
                </p>

                <!-- Players On This Stage Section -->
                <div class="pt-3 border-top border-white border-opacity-10">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <small class="extra-small text-warning fw-bold">
                      <i class="bi bi-people-fill me-1"></i> Joueurs sur cette case ({{ level.players_count || 0 }})
                    </small>
                    <small class="extra-small text-white-50">Cliquer pour voir le mur</small>
                  </div>

                  <!-- Avatars Cluster -->
                  <div v-if="level.players && level.players.length > 0" class="d-flex flex-wrap gap-2 align-items-center">
                    <Link 
                      v-for="p in level.players" 
                      :key="p.id"
                      :href="'/communaute/profil/' + p.id"
                      class="player-bubble text-decoration-none transition hover-scale position-relative"
                      :title="p.name + ' (' + (p.xp || 0) + ' XP) • Cliquer pour ouvrir son mur public'"
                    >
                      <img 
                        v-if="p.avatar" 
                        :src="p.avatar.startsWith('/') ? p.avatar : '/' + p.avatar" 
                        :alt="p.name"
                        class="rounded-circle border border-2 border-white shadow-sm"
                        style="width: 38px; height: 38px; object-fit: cover;"
                      />
                      <div 
                        v-else 
                        class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center border border-2 border-white shadow-sm"
                        :style="{ width: '38px', height: '38px', backgroundColor: getAvatarColor(p.id), fontSize: '13px' }"
                      >
                        {{ getInitials(p.name) }}
                      </div>
                    </Link>
                  </div>

                  <div v-else class="text-white-50 extra-small fst-italic py-1">
                    Soyez le premier à atteindre ce jalon ! ⚡
                  </div>
                </div>

              </div>

            </div>
          </div>

        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  levels: { type: Array, default: () => [] },
  currentUserRank: { type: Object, default: null },
  totalXp: { type: Number, default: 0 },
  totalPlayers: { type: Number, default: 0 },
  topThree: { type: Array, default: () => [] },
})

const page = usePage()

function getNodeAlignmentClass(idx) {
  // Serpentine zigzag: left, center, right, center...
  const pos = idx % 4
  if (pos === 0) return 'justify-content-start'
  if (pos === 1) return 'justify-content-center'
  if (pos === 2) return 'justify-content-end'
  return 'justify-content-center'
}

function isCurrentUserAtLevel(level) {
  if (!props.currentUserRank?.tier) return false
  return (parseInt(props.currentUserRank.tier.min_xp) === parseInt(level.min_xp))
}

function getInitials(name) {
  if (!name) return 'U'
  const parts = name.trim().split(' ')
  return parts.length >= 2 
    ? (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase()
    : name.substring(0, 2).toUpperCase()
}

function getAvatarColor(id) {
  const colors = ['#00A859', '#3B82F6', '#8B5CF6', '#EC4899', '#F59E0B', '#06B6D4']
  return colors[(id || 0) % colors.length]
}
</script>

<style scoped>
.candy-crush-roadmap {
  min-height: 800px;
}
.roadmap-spine-path {
  position: absolute;
  top: 40px;
  bottom: 40px;
  left: 50%;
  width: 6px;
  background: repeating-linear-gradient(to bottom, #FBBF24, #FBBF24 12px, transparent 12px, transparent 24px);
  transform: translateX(-50%);
  opacity: 0.25;
  z-index: 0;
}
.roadmap-stage-card {
  width: 100%;
  max-width: 440px;
  background: rgba(15, 23, 42, 0.85) !important;
  border: 1px solid rgba(255, 255, 255, 0.12) !important;
  backdrop-filter: blur(12px);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.roadmap-stage-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.4) !important;
}
.stage-milestone-circle {
  width: 48px;
  height: 48px;
  min-width: 48px;
}
.current-user-pin {
  position: absolute;
  top: -14px;
  left: 20px;
  z-index: 10;
}
.player-bubble:hover {
  transform: scale(1.15);
  z-index: 5;
}
.hover-scale {
  transition: transform 0.15s ease;
}
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}
.animate-bounce {
  animation: bounce 1.4s infinite ease-in-out;
}
</style>
