<template>
  <div v-if="levelUpData" class="modal-backdrop fade show" style="background: rgba(15, 23, 42, 0.75); z-index: 1080;"></div>
  <div v-if="levelUpData" class="modal fade show d-block" tabindex="-1" style="z-index: 1085;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden position-relative text-center p-4 p-md-5 bg-white">
        
        <!-- Shimmering background glow -->
        <div 
          class="position-absolute start-50 top-0 translate-middle rounded-circle opacity-25 pointer-events-none" 
          :style="{
            width: '320px',
            height: '320px',
            background: 'radial-gradient(circle, ' + (levelUpData.color || '#00A859') + ' 0%, transparent 70%)',
            filter: 'blur(30px)'
          }"
        ></div>

        <!-- Confetti emojis floating animation -->
        <div class="celebration-emojis mb-3">
          <span class="fs-1 animate-bounce d-inline-block">🎉</span>
          <span class="fs-2 animate-pulse d-inline-block mx-2">✨</span>
          <span class="fs-1 animate-bounce d-inline-block">🏆</span>
        </div>

        <!-- Big Badge Icon Pin -->
        <div 
          class="rounded-circle text-white shadow-lg mx-auto d-flex align-items-center justify-content-center mb-3 animate-zoom"
          :style="{
            width: '88px',
            height: '88px',
            backgroundColor: levelUpData.color || '#00A859',
            fontSize: '38px'
          }"
        >
          <i class="bi" :class="levelUpData.badge_icon || 'bi-trophy-fill'"></i>
        </div>

        <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold extra-small text-uppercase mb-2 mx-auto">
          Nouveau Palier Débloqué !
        </span>

        <h3 class="h3 fw-bold text-dark mb-1">
          Félicitations !
        </h3>
        <p class="text-muted small mb-3">
          Votre engagement et vos contributions sur Tech237 portent leurs fruits !
        </p>

        <!-- New Rank Pill -->
        <div class="p-3 rounded-4 bg-light border mb-4">
          <span class="small text-muted d-block mb-1">Vous atteignez désormais le rang officiel :</span>
          <h4 class="h5 fw-bold mb-0" :style="{ color: levelUpData.color || '#00A859' }">
            {{ levelUpData.name }}
          </h4>
          <div class="extra-small text-muted mt-1">
            <strong>{{ levelUpData.new_xp || levelUpData.min_xp }} XP</strong> cumulés sur la plateforme
          </div>
        </div>

        <p v-if="levelUpData.description" class="small text-muted mb-4 fst-italic">
          « {{ levelUpData.description }} »
        </p>

        <div class="d-grid gap-2">
          <button 
            type="button" 
            @click="dismissModal" 
            class="btn btn-success rounded-pill py-2 fw-bold text-white shadow-sm"
            style="background-color: var(--tech-green, #00A859);"
          >
            Continuer l'aventure 🚀
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

const page = usePage()
const levelUpData = computed(() => page.props.auth?.user?.pending_level_up || null)
const hasPlayedSound = ref(false)

function playCelebrationChime() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext
    if (!AudioContext) return
    const ctx = new AudioContext()
    
    // 4-note ascending major celebration chime: C5, E5, G5, C6
    const notes = [523.25, 659.25, 783.99, 1046.50]
    notes.forEach((freq, i) => {
      const osc = ctx.createOscillator()
      const gain = ctx.createGain()
      osc.type = 'triangle'
      osc.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.11)
      
      gain.gain.setValueAtTime(0, ctx.currentTime + i * 0.11)
      gain.gain.linearRampToValueAtTime(0.25, ctx.currentTime + i * 0.11 + 0.03)
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.11 + 0.6)
      
      osc.connect(gain)
      gain.connect(ctx.destination)
      
      osc.start(ctx.currentTime + i * 0.11)
      osc.stop(ctx.currentTime + i * 0.11 + 0.7)
    })
  } catch (e) {
    // Audio context may be restricted by browser autoplay policy until interaction
  }
}

watch(levelUpData, (newVal) => {
  if (newVal && !hasPlayedSound.value) {
    hasPlayedSound.value = true
    playCelebrationChime()
  }
}, { immediate: true })

onMounted(() => {
  if (levelUpData.value && !hasPlayedSound.value) {
    hasPlayedSound.value = true
    playCelebrationChime()
  }
})

function dismissModal() {
  router.post('/espace-utilisateur/clear-level-up', {}, {
    preserveScroll: true,
    preserveState: true,
  })
}
</script>

<style scoped>
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}
@keyframes zoom {
  0% { transform: scale(0.8); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}
.animate-bounce {
  animation: bounce 1.2s infinite ease-in-out;
}
.animate-zoom {
  animation: zoom 0.4s ease-out;
}
</style>
