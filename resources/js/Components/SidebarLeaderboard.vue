<template>
  <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4 overflow-hidden position-relative">
    <!-- Subtle background trophy watermarked -->
    <div class="position-absolute end-0 top-0 p-3 opacity-10 pointer-events-none">
      <i class="bi bi-trophy-fill" style="font-size: 88px; color: #f59e0b;"></i>
    </div>

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 position-relative z-1">
      <div class="d-flex align-items-center gap-2">
        <span class="rounded-circle bg-warning bg-opacity-25 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
          <i class="bi bi-trophy-fill fs-6"></i>
        </span>
        <div>
          <h5 class="h6 fw-bold text-dark mb-0">Top Contributeurs</h5>
          <small class="text-muted extra-small">Champions Tech237</small>
        </div>
      </div>
      <span class="badge bg-light text-muted border rounded-pill px-2 py-1 extra-small">
        Live XP
      </span>
    </div>

    <!-- Top 3 List -->
    <div class="d-flex flex-column gap-2 mb-3 position-relative z-1">
      <div 
        v-for="(member, idx) in topMembers" 
        :key="member.id"
        class="d-flex align-items-center justify-content-between p-2 rounded-3 transition hover-bg-light"
        :class="{ 'bg-warning bg-opacity-10 border border-warning border-opacity-25': idx === 0 }"
      >
        <Link :href="'/communaute/profil/' + member.id" class="d-flex align-items-center gap-2 text-decoration-none text-dark flex-grow-1 min-w-0">
          <!-- Rank Medal -->
          <div class="fw-bold fs-6 text-center" style="width: 24px;">
            <span v-if="idx === 0" title="1ère place">🥇</span>
            <span v-else-if="idx === 1" title="2ème place">🥈</span>
            <span v-else-if="idx === 2" title="3ème place">🥉</span>
            <span v-else class="text-muted extra-small">#{{ idx + 1 }}</span>
          </div>

          <!-- Avatar -->
          <div class="position-relative">
            <img 
              v-if="member.avatar" 
              :src="member.avatar.startsWith('/') ? member.avatar : '/' + member.avatar" 
              :alt="member.name"
              class="rounded-circle border"
              style="width: 36px; height: 36px; min-width: 36px; object-fit: cover;"
            />
            <div 
              v-else 
              class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs"
              :style="{ width: '36px', height: '36px', minWidth: '36px', backgroundColor: getAvatarColor(idx), fontSize: '13px' }"
            >
              {{ getInitials(member.name) }}
            </div>
          </div>

          <div class="overflow-hidden min-w-0">
            <strong class="d-block text-truncate text-dark small">{{ member.name }}</strong>
            <span class="badge rounded-pill extra-small px-2 py-0 text-truncate" :style="{ backgroundColor: idx === 0 ? '#FEF3C7' : '#F1F5F9', color: idx === 0 ? '#92400E' : '#475569' }">
              {{ member.rank_title || 'Novice Tech 🌱' }}
            </span>
          </div>
        </Link>

        <!-- Points badge -->
        <div class="text-end ps-2 flex-shrink-0">
          <span class="badge bg-success-subtle text-success rounded-pill fw-bold extra-small px-2 py-1">
            {{ member.xp || 0 }} XP
          </span>
        </div>
      </div>

      <div v-if="!topMembers.length" class="text-center py-3 text-muted extra-small">
        Aucun contributeur classé pour le moment.
      </div>
    </div>

    <!-- CTA Button to Candy Crush-Style Map -->
    <div class="position-relative z-1">
      <Link 
        href="/carte-des-champions" 
        class="btn btn-warning w-100 rounded-pill py-2 fw-bold text-dark shadow-sm d-flex align-items-center justify-content-center gap-2"
        style="background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%); border: none;"
      >
        <i class="bi bi-map-fill fs-6"></i>
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
