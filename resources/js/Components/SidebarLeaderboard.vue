<template>
  <div class="card border-0 rounded-4 shadow-sm p-3 p-md-4 bg-white mb-4 position-relative overflow-hidden" style="border: 1px solid #f1f5f9;">
    <!-- Large 3D Gold Trophy in Top Right Corner matching user screenshot -->
    <div class="position-absolute end-0 top-0 mt-2 me-2 pointer-events-none d-flex flex-column align-items-end" style="z-index: 2;">
      <div class="position-relative">
        <!-- Floating Live XP pill -->
        <span class="badge bg-white text-dark shadow-xs border rounded-pill px-2 py-1 extra-small fw-bold position-absolute" style="top: -6px; right: 2px; font-size: 10px; z-index: 3;">
          Live XP
        </span>
        <!-- Trophy Graphic -->
        <svg width="78" height="74" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.95; filter: drop-shadow(0 4px 6px rgba(245, 158, 11, 0.25));">
          <path d="M35 25H85V60C85 73.8071 73.8071 85 60 85C46.1929 85 35 73.8071 35 60V25Z" fill="#F59E0B"/>
          <path d="M40 25H80V58C80 69.0457 71.0457 78 60 78C48.9543 78 40 69.0457 40 58V25Z" fill="#FBBF24"/>
          <path d="M35 32H18C15.7909 32 14 33.7909 14 36V48C14 56.8366 21.1634 64 30 64H35V54H28C25.7909 54 24 52.2091 24 50V40C24 38.8954 24.8954 38 26 38H35V32Z" fill="#F59E0B"/>
          <path d="M85 32H102C104.209 32 106 33.7909 106 36V48C106 56.8366 98.8366 64 90 64H85V54H92C94.2091 54 96 52.2091 96 50V40C96 38.8954 95.1046 38 94 38H85V32Z" fill="#F59E0B"/>
          <path d="M52 85H68V98H52V85Z" fill="#D97706"/>
          <path d="M40 98H80V106C80 108.209 78.2091 110 76 110H44C41.7909 110 40 108.209 40 106V98Z" fill="#F59E0B"/>
          <path d="M45 102H75" stroke="#FDE68A" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <!-- Top player XP badge overlapping trophy base as seen in reference screenshot -->
        <span v-if="topMembers && topMembers.length" class="badge rounded-pill fw-bold extra-small px-2 py-1 position-absolute shadow-xs" style="bottom: 0px; right: -2px; background-color: #D1FAE5; color: #065F46; font-size: 11px; z-index: 3;">
          {{ topMembers[0].xp || 0 }} XP
        </span>
      </div>
    </div>

    <!-- Header Left -->
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom position-relative" style="z-index: 1;">
      <span class="rounded-circle bg-warning bg-opacity-25 text-warning d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
        <i class="bi bi-trophy-fill fs-5" style="color: #f59e0b;"></i>
      </span>
      <div>
        <h6 class="fw-bold text-dark mb-0 fs-6">Top Contributeurs</h6>
        <small class="text-muted extra-small" style="font-size: 11px;">Champions Tech237</small>
      </div>
    </div>

    <!-- Contributors List -->
    <div class="d-flex flex-column gap-2 mb-3 position-relative" style="z-index: 1;">
      <div 
        v-for="(member, idx) in topMembers" 
        :key="member.id"
        class="d-flex align-items-center justify-content-between p-2 rounded-3 transition"
        :style="idx === 0 ? 'background-color: #FEF9C3; border: 1px solid #FEF08A;' : 'background-color: transparent; border: 1px solid transparent;'"
      >
        <Link :href="'/communaute/profil/' + member.id" class="d-flex align-items-center gap-2 text-decoration-none text-dark flex-grow-1 min-w-0">
          <!-- Medal Icon matching screenshot -->
          <div class="text-center flex-shrink-0" style="width: 26px;">
            <span v-if="idx === 0" class="fs-5" title="1ère place">🥇</span>
            <span v-else-if="idx === 1" class="fs-5" title="2ème place">🥈</span>
            <span v-else-if="idx === 2" class="fs-5" title="3ème place">🥉</span>
            <span v-else class="text-muted small fw-bold">#{{ idx + 1 }}</span>
          </div>

          <!-- Avatar Circle -->
          <div class="position-relative flex-shrink-0">
            <img 
              v-if="member.avatar" 
              :src="member.avatar.startsWith('/') ? member.avatar : '/' + member.avatar" 
              :alt="member.name"
              class="rounded-circle border"
              style="width: 38px; height: 38px; object-fit: cover;"
            />
            <div 
              v-else 
              class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs"
              :style="{ width: '38px', height: '38px', backgroundColor: idx === 0 ? '#059669' : (idx === 1 ? '#2563eb' : '#d97706'), fontSize: '13px' }"
            >
              {{ getInitials(member.name) }}
            </div>
          </div>

          <!-- Name & Rank Pill -->
          <div class="overflow-hidden min-w-0">
            <strong class="d-block text-truncate text-dark small mb-0">{{ member.name }}</strong>
            <span class="badge rounded-pill extra-small px-2 py-0 text-truncate" style="background-color: #FEF3C7; color: #92400E; font-size: 10px;">
              {{ member.rank_title || 'Novice Tech 🌱' }}
            </span>
          </div>
        </Link>

        <!-- Points badge matching screenshot (mint green pill) -->
        <div class="text-end ps-2 flex-shrink-0">
          <span class="badge rounded-pill fw-bold extra-small px-2 py-1 shadow-2xs" style="background-color: #D1FAE5; color: #065F46; font-size: 11px;">
            {{ member.xp || 0 }} XP
          </span>
        </div>
      </div>

      <div v-if="!topMembers.length" class="text-center py-3 text-muted extra-small">
        Aucun contributeur classé pour le moment.
      </div>
    </div>

    <!-- Bottom CTA Button matching user screenshot -->
    <div class="position-relative" style="z-index: 1;">
      <Link 
        href="/carte-des-champions" 
        class="btn w-100 rounded-pill py-2 fw-bold text-dark shadow-sm d-flex align-items-center justify-content-center gap-2 border-0 hover-scale-sm transition"
        style="background-color: #F59E0B; color: #18181b; font-size: 14px;"
      >
        <i class="bi bi-map-fill"></i>
        <span>Ouvrir la Map des Gagnants 🚀</span>
      </Link>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const topMembers = computed(() => {
  return page.props.topContributors || []
})

function getInitials(name) {
  if (!name) return 'U'
  const parts = name.trim().split(' ')
  return parts.length >= 2 
    ? (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase()
    : name.substring(0, 2).toUpperCase()
}

function getAvatarColor(idx) {
  const colors = ['#00A859', '#3B82F6', '#8B5CF6']
  return colors[idx % colors.length]
}
</script>

<style scoped>
.hover-bg-light:hover {
  background-color: #f8fafc;
}
.shadow-xs {
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
</style>
