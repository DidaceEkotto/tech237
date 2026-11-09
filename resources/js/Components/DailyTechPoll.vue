<template>
  <div v-if="poll" class="card border-0 rounded-4 shadow-sm p-4 text-white overflow-hidden position-relative mb-4" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
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
      {{ poll.question }}
    </h5>
    <p v-if="poll.description" class="text-white-50 small mb-3">
      {{ poll.description }}
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
        v-for="opt in poll.options" 
        :key="opt.id"
        class="poll-option-label p-3 rounded-3 border d-flex align-items-center justify-content-between cursor-pointer transition-all"
        :class="selectedOption === opt.id ? 'border-success bg-white bg-opacity-15 shadow-sm' : 'border-white border-opacity-10 bg-white bg-opacity-5'"
        style="cursor: pointer;"
        @click="selectedOption = opt.id"
      >
        <div class="d-flex align-items-center gap-2">
          <input 
            type="radio" 
            :name="'poll_' + poll.id" 
            :value="opt.id" 
            v-model="selectedOption" 
            class="form-check-input mt-0"
            style="cursor: pointer;"
          >
          <span class="fw-semibold small text-white">{{ opt.label || opt.title }}</span>
        </div>
        <i v-if="selectedOption === opt.id" class="bi bi-check-circle-fill text-success"></i>
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
    </div>

    <!-- Results view if already voted -->
    <div v-else class="d-flex flex-column gap-3 mb-2">
      <div 
        v-for="opt in calculatedOptions" 
        :key="opt.id" 
        class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10"
      >
        <div class="d-flex justify-content-between align-items-center small fw-bold mb-1">
          <span class="text-white">{{ opt.label || opt.title }}</span>
          <span class="text-success">{{ opt.percentage }}%</span>
        </div>
        <div class="progress" style="height: 8px; background-color: rgba(255,255,255,0.15);">
          <div 
            class="progress-bar rounded-pill" 
            role="progressbar" 
            :style="{ width: opt.percentage + '%', backgroundColor: 'var(--tech-green, #00A859)' }"
          ></div>
        </div>
        <div class="text-end text-white-50 extra-small mt-1" style="font-size: 10px;">
          {{ opt.votes_count }} {{ opt.votes_count > 1 ? 'votes' : 'vote' }}
        </div>
      </div>

      <div class="text-center text-white-50 small mt-1">
        <i class="bi bi-check-circle-fill text-success me-1"></i> Merci pour votre participation !
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  poll: {
    type: Object,
    default: null,
  }
})

const selectedOption = ref(null)
const isSubmitting = ref(false)
const hasVoted = ref(props.poll?.user_has_voted || false)
const successMsg = ref('')
const errorMsg = ref('')
const livePoll = ref(props.poll ? JSON.parse(JSON.stringify(props.poll)) : null)

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

  try {
    const res = await fetch(`/sondages/${livePoll.value.id}/vote`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        option_id: selectedOption.value,
      }),
    })

    const data = await res.json()

    if (!res.ok) {
      errorMsg.value = data.error || 'Erreur lors de la prise en compte de votre vote.'
    } else {
      hasVoted.value = true
      successMsg.value = data.message || 'Votre vote a bien été enregistré !'
      if (data.poll) {
        livePoll.value = data.poll
      } else {
        // Fallback: increment locally
        const opt = livePoll.value.options.find(o => o.id === selectedOption.value)
        if (opt) opt.votes_count = (opt.votes_count || 0) + 1
      }
    }
  } catch (e) {
    errorMsg.value = 'Erreur réseau lors de l\'enregistrement de votre vote.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.poll-option-label {
  transition: all 0.2s ease;
}
.poll-option-label:hover {
  background-color: rgba(255, 255, 255, 0.12) !important;
}
</style>
