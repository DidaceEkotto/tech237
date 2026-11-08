import { ref, computed, onUnmounted } from 'vue'

export function useAudioNarrator(options = {}) {
  const isSpeaking = ref(false)
  const isPaused = ref(false)
  const speechProgress = ref(0)
  const currentChunkIndex = ref(0)
  const totalChunksCount = ref(0)
  
  let availableVoices = []
  let textChunks = []
  let activeUtterance = null
  let progressInterval = null

  // Preload and cache available voices
  function loadVoices() {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) return
    availableVoices = window.speechSynthesis.getVoices() || []
    if (availableVoices.length === 0 && window.speechSynthesis.onvoiceschanged !== undefined) {
      window.speechSynthesis.onvoiceschanged = () => {
        availableVoices = window.speechSynthesis.getVoices() || []
      }
    }
  }

  loadVoices()

  // Find the most appropriate voice for the given language code (default 'fr-FR')
  function getBestVoice(targetLang = 'fr-FR') {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) return null
    if (!availableVoices || availableVoices.length === 0) {
      availableVoices = window.speechSynthesis.getVoices() || []
    }

    const langPrefix = targetLang.substring(0, 2).toLowerCase() // 'fr' or 'en'
    
    // 1. Exact match e.g. 'fr-FR' or 'fr_FR'
    const exactVoice = availableVoices.find(v => 
      v.lang && (v.lang.toLowerCase() === targetLang.toLowerCase() || v.lang.replace('_', '-').toLowerCase() === targetLang.toLowerCase())
    )
    if (exactVoice) return exactVoice

    // 2. Starts with language code (e.g. 'fr-CA', 'fr')
    const langVoice = availableVoices.find(v => v.lang && v.lang.toLowerCase().startsWith(langPrefix))
    if (langVoice) return langVoice

    return null
  }

  // Split raw text into natural chunks (sentences of ~150-200 chars) to prevent Chrome/Mobile cutoff bug
  function splitTextIntoChunks(text) {
    if (!text) return []
    // Clean HTML tags and decode entities
    const clean = text
      .replace(/<[^>]*>?/gm, ' ')
      .replace(/&nbsp;/g, ' ')
      .replace(/&amp;/g, '&')
      .replace(/&quot;/g, '"')
      .replace(/&#39;/g, "'")
      .replace(/\s+/g, ' ')
      .trim()

    if (!clean) return []

    // Split by punctuation: . ! ? ; or newline
    const sentences = clean.match(/[^.!?;\n]+[.!?;\n]+/g) || [clean]
    const chunks = []
    let currentChunk = ''

    for (const sentence of sentences) {
      const s = sentence.trim()
      if (!s) continue

      if ((currentChunk + ' ' + s).length > 180) {
        if (currentChunk) chunks.push(currentChunk.trim())
        currentChunk = s
      } else {
        currentChunk = currentChunk ? currentChunk + ' ' + s : s
      }
    }

    if (currentChunk.trim()) {
      chunks.push(currentChunk.trim())
    }

    return chunks.length > 0 ? chunks : [clean]
  }

  function calculateEstimatedMinutes(rawText) {
    if (!rawText) return 1
    const clean = rawText.replace(/<[^>]*>?/gm, ' ').trim()
    const words = clean.split(/\s+/).filter(Boolean).length
    return Math.max(1, Math.ceil(words / 150)) // ~150 words/min speech rate
  }

  function stop() {
    if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
      window.speechSynthesis.cancel()
    }
    if (progressInterval) {
      clearInterval(progressInterval)
      progressInterval = null
    }
    activeUtterance = null
    window._activeNarratorUtterance = null
    isSpeaking.value = false
    isPaused.value = false
    speechProgress.value = 0
    currentChunkIndex.value = 0
    totalChunksCount.value = 0
    textChunks = []
  }

  function playNextChunk(lang = 'fr-FR') {
    if (currentChunkIndex.value >= textChunks.length) {
      // Completed all speech chunks
      speechProgress.value = 100
      setTimeout(() => {
        stop()
      }, 600)
      return
    }

    const chunkText = textChunks[currentChunkIndex.value]
    const utterance = new SpeechSynthesisUtterance(chunkText)
    utterance.lang = lang
    utterance.rate = 1.0

    const voice = getBestVoice(lang)
    if (voice) {
      utterance.voice = voice
    }

    utterance.onstart = () => {
      isSpeaking.value = true
      isPaused.value = false
      // Calculate progress based on chunk position
      if (textChunks.length > 0) {
        speechProgress.value = Math.round((currentChunkIndex.value / textChunks.length) * 100)
      }
    }

    utterance.onend = () => {
      currentChunkIndex.value++
      playNextChunk(lang)
    }

    utterance.onerror = (e) => {
      // If cancelled intentionally, ignore
      if (e.error === 'interrupted' || e.error === 'canceled') return
      currentChunkIndex.value++
      playNextChunk(lang)
    }

    // Keep global reference to avoid GC bug
    activeUtterance = utterance
    window._activeNarratorUtterance = utterance

    window.speechSynthesis.speak(utterance)
  }

  function start(fullText, lang = 'fr-FR') {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) {
      alert('La synthèse vocale n\'est pas supportée par votre navigateur.')
      return
    }

    stop()

    textChunks = splitTextIntoChunks(fullText)
    if (textChunks.length === 0) return

    totalChunksCount.value = textChunks.length
    currentChunkIndex.value = 0
    speechProgress.value = 0

    // Chrome bugfix: resume if stuck
    if (window.speechSynthesis.paused) {
      window.speechSynthesis.resume()
    }

    playNextChunk(lang)
  }

  function toggle(fullText, lang = 'fr-FR') {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) {
      alert('La synthèse vocale n\'est pas supportée par votre navigateur.')
      return
    }

    if (isSpeaking.value && !isPaused.value) {
      window.speechSynthesis.pause()
      isPaused.value = true
      return
    }

    if (isSpeaking.value && isPaused.value) {
      window.speechSynthesis.resume()
      isPaused.value = false
      return
    }

    start(fullText, lang)
  }

  const statusText = computed(() => {
    if (!isSpeaking.value) return 'Écouter l\'article en audio'
    if (isPaused.value) return 'Lecture audio en pause'
    return 'Lecture audio en cours...'
  })

  onUnmounted(() => {
    stop()
  })

  return {
    isSpeaking,
    isPaused,
    speechProgress,
    statusText,
    start,
    stop,
    toggle,
    calculateEstimatedMinutes
  }
}
