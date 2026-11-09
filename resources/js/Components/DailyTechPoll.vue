<template>
  <div v-if="livePoll" class="card border-0 rounded-4 shadow-sm p-4 text-white overflow-hidden position-relative mb-4" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <span class="badge px-3 py-1 rounded-pill fw-bold text-uppercase text-white shadow-sm" style="background-color: var(--tech-green, #00A859); font-size: 11px;">
        <i class="bi bi-pie-chart-fill me-1"></i> Sondage Tech du Jour
      </span>
      <span class="badge bg-white bg-opacity-10 text-white-50 extra-small rounded-pill">
        <i class="bi bi-check-all me-1"></i> {{ totalVotes }} {{ totalVotes > 1 ? 'votes' : 'vote' }}
      </span>
    </div>

    <!-- Question -->
    <h5 class="fw-bold text-white mb-2" style="font-size: 1.05rem; line-height: 1.4;">
      {{ livePoll.question }}
    </h5>
    <p v-if="livePoll.description" class="text-white-50 small mb-3">
      {{ livePoll.description }}
    </p>

    <!-- Success Message if just voted -->
    <div v-if="successMsg" class="alert alert-success py-2 px-3 rounded-3 small mb-3 border-0 bg-success text-white d-flex align-items-center gap-2">
      <i class="bi bi-trophy-fill text-warning"></i>
      <span>{{ successMsg }}</span>
    </div>

    <!-- Error Message -->
    <div v-if="errorMsg" class="alert alert-danger py-2 px-3 rounded-3 small mb-3 border-0 bg-danger text-white">
      {{ errorMsg }}
    </div>

    <!-- Form: Options to vote -->
    <div v-if="!hasVoted" class="d-flex flex-column gap-2 mb-3">
      <label 
        v-for="opt in livePoll.options" 
        :key="opt.id"
        class="poll-option-label p-3 rounded-3 d-flex align-items-center justify-content-between cursor-pointer"
        :class="selectedOption === opt.id ? 'poll-option-selected' : 'poll-option-default'"
        style="cursor: pointer;"
        @click="selectedOption = opt.id"
      >
        <div class="d-flex align-items-center gap-3 flex-grow-1">
          <input 
            type="radio" 
            :name="'poll_' + livePoll.id" 
            :value="opt.id" 
            :checked="selectedOption === opt.id"
            class="form-check-input mt-0 flex-shrink-0"
            style="cursor: pointer; width: 18px; height: 18px;"
          >
          <span class="fw-bold small" style="color: #0B2559 !important; font-size: 0.92rem; line-height: 1.35;">
            {{ getOptionText(opt) }}
          </span>
        </div>
        <i v-if="selectedOption === opt.id" class="bi bi-check-circle-fill text-success fs-5 ms-2 flex-shrink-0"></i>
      </label>

      <button 
        @click="submitVote" 
        :disabled="!selectedOption || isSubmitting" 
        class="btn rounded-pill w-100 py-2 mt-2 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2"
        style="background-color: var(--tech-green, #00A859);"
      >
        <span v-if="isSubmitting" class="spinner-border spinner-border-sm"></span>
        <span v-else><i class="bi bi-send-fill me-1"></i> Voter &amp; Découvrir les résultats</span>
      </button>
      <div class="text-center text-white-50 extra-small mt-1" style="font-size: 11px;">
        <i class="bi bi-shield-check me-1"></i> 1 seul vote par personne &bull; Ouvert à tous
      </div>
    </div>

    <!-- Results view if already voted -->
    <div v-else class="d-flex flex-column gap-2 mb-2">
      <div 
        v-for="opt in calculatedOptions" 
        :key="opt.id" 
        class="p-3 rounded-3 bg-white shadow-sm border border-light"
      >
        <div class="d-flex justify-content-between align-items-center small fw-bold mb-1">
          <span style="color: #0B2559 !important;">{{ getOptionText(opt) }}</span>
          <span class="text-success fw-bold">{{ opt.percentage }}%</span>
        </div>
        <div class="progress" style="height: 10px; background-color: #e2e8f0; border-radius: 999px;">
          <div 
            class="progress-bar rounded-pill" 
            role="progressbar" 
            :style="{ width: opt.percentage + '%', backgroundColor: '#00A859' }"
          ></div>
        </div>
        <div class="text-end text-muted extra-small mt-1" style="font-size: 11px;">
          {{ opt.votes_count }} {{ opt.votes_count > 1 ? 'votes' : 'vote' }}
        </div>
      </div>

      <div class="text-center text-white-50 small mt-2">
        <i class="bi bi-check-circle-fill text-success me-1"></i> Votre vote est comptabilisé. Merci pour votre participation !
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  poll: {
    type: Object,
    default: null,
  }
})

const selectedOption = ref(null)
const isSubmitting = ref(false)
const hasVoted = ref(props.poll?.user_has_voted || props.poll?.has_voted || false)
const successMsg = ref('')
const errorMsg = ref('')
const livePoll = ref(props.poll ? JSON.parse(JSON.stringify(props.poll)) : null)

function getGuestToken() {
  if (typeof window === 'undefined') return ''
  let token = localStorage.getItem('tech237_poll_guest_token')
  if (!token) {
    token = 'g_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + Date.now().toString(36)
    localStorage.setItem('tech237_poll_guest_token', token)
  }
  return token
}

function checkIfVotedLocally(pollId) {
  if (typeof window === 'undefined' || !pollId) return false
  return localStorage.getItem(`tech237_poll_voted_${pollId}`) === '1'
}

watch(() => props.poll, (newPoll) => {
  if (newPoll) {
    livePoll.value = JSON.parse(JSON.stringify(newPoll))
    hasVoted.value = newPoll.user_has_voted || newPoll.has_voted || checkIfVotedLocally(newPoll.id) || false
  }
}, { deep: true })

onMounted(async () => {
  const token = getGuestToken()
  if (livePoll.value?.id && checkIfVotedLocally(livePoll.value.id)) {
    hasVoted.value = true
  }

  if (!livePoll.value) {
    try {
      const res = await fetch(`/api/sondage-actif?guest_token=${encodeURIComponent(token)}`, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      })
      if (res.ok) {
        const data = await res.json()
        if (data && data.poll) {
          livePoll.value = data.poll
          if (data.poll.has_voted || checkIfVotedLocally(data.poll.id)) {
            hasVoted.value = true
          }
        }
      }
    } catch (e) {
      // Quiet fail if network error
    }
  }
})

function getOptionText(opt) {
  if (!opt) return ''
  return opt.label || opt.title || opt.option_text || opt.name || opt.text || ''
}

const totalVotes = computed(() => {
  if (!livePoll.value || !livePoll.value.options) return 0
  return livePoll.value.options.reduce((sum, opt) => sum + (opt.votes_count || 0), 0)
})

const calculatedOptions = computed(() => {
  if (!livePoll.value || !livePoll.value.options) return []
  const total = totalVotes.value
  return livePoll.value.options.map(opt => {
    const count = opt.votes_count || 0
    const pct = total > 0 ? Math.round((count / total) * 100) : 0
    return {
      ...opt,
      percentage: pct,
    }
  })
})

async function submitVote() {
  if (!selectedOption.value || isSubmitting.value || !livePoll.value) return
  isSubmitting.value = true
  errorMsg.value = ''
  successMsg.value = ''

  const guestToken = getGuestToken()

  try {
    const res = await fetch(`/sondages/${livePoll.value.id}/vote`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        option_id: selectedOption.value,
        guest_token: guestToken,
      }),
    })

    let data = null
    const contentType = res.headers.get('content-type') || ''
    if (contentType.includes('application/json')) {
      data = await res.json()
    } else {
      const text = await res.text()
      try {
        data = JSON.parse(text)
      } catch (err) {
        if (res.ok) {
          data = { success: true }
        }
      }
    }

    if (!res.ok) {
      errorMsg.value = data?.error || 'Erreur lors de la prise en compte de votre vote.'
      if (res.status === 422 && data?.error?.includes('déjà voté')) {
        hasVoted.value = true
        localStorage.setItem(`tech237_poll_voted_${livePoll.value.id}`, '1')
      }
    } else {
      hasVoted.value = true
      localStorage.setItem(`tech237_poll_voted_${livePoll.value.id}`, '1')
      successMsg.value = data?.message || 'Votre vote a bien été enregistré !'
      
      if (data?.poll) {
        livePoll.value = data.poll
      } else {
        // Fetch fresh results
        try {
          const freshRes = await fetch(`/api/sondage-actif?guest_token=${encodeURIComponent(guestToken)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
          })
          if (freshRes.ok) {
            const freshData = await freshRes.json()
            if (freshData?.poll) {
              livePoll.value = freshData.poll
            }
          }
        } catch (fetchErr) {
          const opt = livePoll.value.options.find(o => o.id === selectedOption.value)
          if (opt) opt.votes_count = (opt.votes_count || 0) + 1
        }
      }
    }
  } catch (e) {
    console.error('Poll submit error:', e)
    // Probe /api/sondage-actif to see if vote actually succeeded
    try {
      const probeRes = await fetch(`/api/sondage-actif?guest_token=${encodeURIComponent(guestToken)}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      })
      if (probeRes.ok) {
        const probeData = await probeRes.json()
        if (probeData?.poll?.has_voted) {
          hasVoted.value = true
          localStorage.setItem(`tech237_poll_voted_${livePoll.value.id}`, '1')
          livePoll.value = probeData.poll
          successMsg.value = 'Votre vote a bien été enregistré !'
          return
        }
      }
    } catch (_) {}
    errorMsg.value = 'Erreur lors de l\'enregistrement de votre vote. Veuillez réessayer.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.poll-option-label {
  transition: all 0.2s ease;
  user-select: none;
}
.poll-option-default {
  background-color: #ffffff !important;
  border: 1.5px solid rgba(0, 0, 0, 0.08) !important;
}
.poll-option-default:hover {
  background-color: #f8fafc !important;
  border-color: #00A859 !important;
  transform: translateY(-1px);
}
.poll-option-selected {
  background-color: #f0fdf4 !important;
  border: 2px solid #00A859 !important;
  box-shadow: 0 4px 12px rgba(0, 168, 89, 0.15) !important;
}
</style>
