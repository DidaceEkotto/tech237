import { ref, computed, onUnmounted } from 'vue'

export function useAudioNarrator(options = {}) {
  const isSpeaking = ref(false)
  const isPaused = ref(false)
  const speechProgress = ref(0)
  const currentChunkIndex = ref(0)
  const totalChunksCount = ref(0)
  const speechRate = ref(options.rate || 1.0)

  let availableVoices = []
  let textChunks = []
  let chunkOriginalLengths = []
  let totalCharacters = 0
  let currentLang = 'fr-FR'
  let activeUtterance = null
  let activeAudio = null
  let isUsingHtml5Fallback = false
  let isPausingIntentionally = false
  let sessionId = 0

  // Intra-chunk position tracking for exact word-level pause/resume
  let currentChunkCharIndex = 0
  let chunkStartedAt = 0
  let boundaryReceived = false
  let progressTimer = null

  function hasWebSpeechSupport() {
    if (typeof window === 'undefined') return false
    // Opera Mini in extreme mode or older mobile browsers lack speechSynthesis
    const isOperaMini =
      typeof navigator !== 'undefined' &&
      (navigator.userAgent.indexOf('Opera Mini') > -1 || Object.prototype.toString.call(window.operamini) === '[object OperaMini]')
    if (isOperaMini) return false
    return 'speechSynthesis' in window && typeof window.SpeechSynthesisUtterance !== 'undefined'
  }

  // Preload and cache available voices across Chrome, Safari, Firefox, Edge, Brave, Opera
  function loadVoices() {
    if (!hasWebSpeechSupport()) return
    try {
      availableVoices = window.speechSynthesis.getVoices() || []
      if (availableVoices.length === 0 && 'onvoiceschanged' in window.speechSynthesis) {
        window.speechSynthesis.onvoiceschanged = () => {
          try {
            availableVoices = window.speechSynthesis.getVoices() || []
          } catch (e) {}
        }
      }
    } catch (e) {}
  }

  loadVoices()

  function getBestVoice(targetLang = 'fr-FR') {
    if (!hasWebSpeechSupport()) return null
    try {
      if (!availableVoices || availableVoices.length === 0) {
        availableVoices = window.speechSynthesis.getVoices() || []
      }
    } catch (e) {
      return null
    }
    if (!availableVoices || availableVoices.length === 0) return null

    const normalizedTarget = targetLang.replace('_', '-').toLowerCase()
    const langPrefix = normalizedTarget.substring(0, 2)

    const matchingVoices = availableVoices.filter(v => {
      if (!v.lang) return false
      const vLang = v.lang.replace('_', '-').toLowerCase()
      return vLang === normalizedTarget || vLang.startsWith(langPrefix + '-') || vLang === langPrefix
    })

    if (matchingVoices.length === 0) return null

    // Prefer localService voices on Chromium/Brave/Edge/Opera because local voices
    // never suffer from network timeouts and reliably emit onboundary events
    const exactLocal = matchingVoices.find(
      v => v.localService && v.lang.replace('_', '-').toLowerCase() === normalizedTarget
    )
    if (exactLocal) return exactLocal

    const anyLocal = matchingVoices.find(v => v.localService)
    if (anyLocal) return anyLocal

    const exactMatch = matchingVoices.find(
      v => v.lang.replace('_', '-').toLowerCase() === normalizedTarget
    )
    return exactMatch || matchingVoices[0]
  }

  // Split raw HTML/text into natural clauses (~90-140 chars)
  // Short clauses prevent Chromium's 15s cutoff bug, make pause/resume ultra-precise,
  // and stay under the 200-char limit for HTML5 audio fallback.
  function splitTextIntoChunks(text) {
    if (!text) return []
    const clean = String(text)
      .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, ' ')
      .replace(/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi, ' ')
      .replace(/<[^>]*>?/gm, ' ')
      .replace(/&nbsp;/gi, ' ')
      .replace(/&amp;/gi, '&')
      .replace(/&quot;/gi, '"')
      .replace(/&#39;|&apos;/gi, "'")
      .replace(/&lt;/gi, '<')
      .replace(/&gt;/gi, '>')
      .replace(/\s+/g, ' ')
      .trim()

    if (!clean) return []

    // Split on sentence boundaries first (. ! ? ; : \n)
    const rawSentences = clean.match(/[^.!?;:\n]+[.!?;:\n]*/g) || [clean]
    const chunks = []
    let buffer = ''

    const pushLongSegment = (segment) => {
      // Split long sentences on commas or conjunctions, then words if still > 140 chars
      const subParts = segment.split(/(?<=[,–—])\s+/)
      let subBuf = ''
      for (const part of subParts) {
        if ((subBuf + ' ' + part).trim().length <= 140) {
          subBuf = subBuf ? subBuf + ' ' + part : part
        } else {
          if (subBuf) chunks.push(subBuf.trim())
          if (part.length <= 140) {
            subBuf = part
          } else {
            const words = part.split(/\s+/)
            let wordBuf = ''
            for (const w of words) {
              if ((wordBuf + ' ' + w).trim().length > 140) {
                if (wordBuf) chunks.push(wordBuf.trim())
                wordBuf = w
              } else {
                wordBuf = wordBuf ? wordBuf + ' ' + w : w
              }
            }
            subBuf = wordBuf
          }
        }
      }
      if (subBuf.trim()) chunks.push(subBuf.trim())
    }

    for (const raw of rawSentences) {
      const s = raw.trim()
      if (!s) continue

      if ((buffer + ' ' + s).trim().length <= 130) {
        buffer = buffer ? buffer + ' ' + s : s
      } else {
        if (buffer) {
          chunks.push(buffer.trim())
          buffer = ''
        }
        if (s.length <= 140) {
          buffer = s
        } else {
          pushLongSegment(s)
        }
      }
    }

    if (buffer.trim()) {
      chunks.push(buffer.trim())
    }

    return chunks.length > 0 ? chunks : [clean]
  }

  function calculateEstimatedMinutes(rawText) {
    if (!rawText) return 1
    const clean = String(rawText).replace(/<[^>]*>?/gm, ' ').trim()
    const words = clean.split(/\s+/).filter(Boolean).length
    return Math.max(1, Math.ceil(words / 160))
  }

  function clearProgressTimer() {
    if (progressTimer) {
      clearInterval(progressTimer)
      progressTimer = null
    }
  }

  function updateProgressValue(intraChunkChars = 0) {
    if (totalCharacters <= 0 || textChunks.length === 0) return
    let completedChars = 0
    for (let i = 0; i < currentChunkIndex.value; i++) {
      completedChars += chunkOriginalLengths[i] || 0
    }
    const currentOrigLen = chunkOriginalLengths[currentChunkIndex.value] || 0
    const currentRemainingLen = (textChunks[currentChunkIndex.value] || '').length
    const alreadyTrimmedFromCurrent = Math.max(0, currentOrigLen - currentRemainingLen)
    const totalSpokenInCurrent = Math.min(currentOrigLen, alreadyTrimmedFromCurrent + intraChunkChars)

    const pct = Math.round(((completedChars + totalSpokenInCurrent) / totalCharacters) * 100)
    speechProgress.value = Math.max(speechProgress.value, Math.min(99, pct))
  }

  function startSmoothProgressTicker(chunkLen) {
    clearProgressTimer()
    progressTimer = setInterval(() => {
      if (!isSpeaking.value || isPaused.value) return
      if (!boundaryReceived && chunkStartedAt > 0) {
        const elapsedSec = (Date.now() - chunkStartedAt) / 1000
        const estChars = Math.min(chunkLen, Math.floor(elapsedSec * 14 * speechRate.value))
        updateProgressValue(estChars)
      }
    }, 250)
  }

  // Estimate or retrieve the exact character offset spoken in the current chunk
  function getSpokenCharOffsetInCurrentChunk(chunkText) {
    if (!chunkText) return 0
    let rawOffset = currentChunkCharIndex
    if (!boundaryReceived && chunkStartedAt > 0) {
      const elapsedSec = Math.max(0, (Date.now() - chunkStartedAt) / 1000)
      rawOffset = Math.floor(elapsedSec * 14 * speechRate.value)
    }
    if (rawOffset <= 0) return 0
    if (rawOffset >= chunkText.length) return chunkText.length

    // Snap back to the start of the word at rawOffset so we never slice mid-word
    let wordStart = rawOffset
    while (wordStart > 0 && !/\s/.test(chunkText[wordStart - 1])) {
      wordStart--
    }
    return wordStart
  }

  function stop() {
    sessionId++
    isPausingIntentionally = true
    clearProgressTimer()

    if (hasWebSpeechSupport()) {
      try {
        window.speechSynthesis.cancel()
      } catch (e) {}
    }

    if (activeAudio) {
      try {
        activeAudio.pause()
        activeAudio.src = ''
      } catch (e) {}
      activeAudio = null
    }

    activeUtterance = null
    if (typeof window !== 'undefined') {
      window._activeNarratorUtterance = null
    }

    isPausingIntentionally = false
    isSpeaking.value = false
    isPaused.value = false
    speechProgress.value = 0
    currentChunkIndex.value = 0
    totalChunksCount.value = 0
    currentChunkCharIndex = 0
    chunkStartedAt = 0
    boundaryReceived = false
    textChunks = []
    chunkOriginalLengths = []
    totalCharacters = 0
  }

  // HTML5 <audio> fallback player (for Opera Mini, browsers without speechSynthesis, or failed synthesis)
  function playNextChunkHtml5(lang = 'fr-FR', activeSession = sessionId) {
    if (activeSession !== sessionId || isPaused.value) return

    if (currentChunkIndex.value >= textChunks.length) {
      speechProgress.value = 100
      setTimeout(() => {
        if (activeSession === sessionId) stop()
      }, 500)
      return
    }

    const chunkText = textChunks[currentChunkIndex.value]
    if (!chunkText || !chunkText.trim()) {
      currentChunkIndex.value++
      playNextChunkHtml5(lang, activeSession)
      return
    }

    const url = `/api/tts?lang=${encodeURIComponent(lang)}&text=${encodeURIComponent(chunkText)}`
    const audio = new Audio(url)
    audio.playbackRate = speechRate.value
    activeAudio = audio

    currentChunkCharIndex = 0
    chunkStartedAt = Date.now()
    boundaryReceived = false

    audio.onplay = () => {
      if (activeSession !== sessionId) return
      isSpeaking.value = true
      isPaused.value = false
      chunkStartedAt = Date.now()
      updateProgressValue(0)
      startSmoothProgressTicker(chunkText.length)
    }

    audio.ontimeupdate = () => {
      if (activeSession !== sessionId || isPaused.value) return
      if (audio.duration && isFinite(audio.duration) && audio.duration > 0) {
        boundaryReceived = true
        const ratio = Math.min(1, audio.currentTime / audio.duration)
        currentChunkCharIndex = Math.floor(ratio * chunkText.length)
        updateProgressValue(currentChunkCharIndex)
      }
    }

    audio.onended = () => {
      if (activeSession !== sessionId || isPausingIntentionally || isPaused.value) return
      clearProgressTimer()
      currentChunkIndex.value++
      playNextChunkHtml5(lang, activeSession)
    }

    audio.onerror = () => {
      if (activeSession !== sessionId || isPausingIntentionally || isPaused.value) return
      clearProgressTimer()
      currentChunkIndex.value++
      playNextChunkHtml5(lang, activeSession)
    }

    audio.play().catch(() => {
      if (activeSession !== sessionId || isPausingIntentionally || isPaused.value) return
      stop()
    })
  }

  // Web Speech API player with exact word-boundary tracking & automatic fallback
  function playNextChunk(lang = 'fr-FR', activeSession = sessionId) {
    if (activeSession !== sessionId || isPaused.value) return

    if (isUsingHtml5Fallback || !hasWebSpeechSupport()) {
      isUsingHtml5Fallback = true
      playNextChunkHtml5(lang, activeSession)
      return
    }

    if (currentChunkIndex.value >= textChunks.length) {
      speechProgress.value = 100
      setTimeout(() => {
        if (activeSession === sessionId) stop()
      }, 500)
      return
    }

    const chunkText = textChunks[currentChunkIndex.value]
    if (!chunkText || !chunkText.trim()) {
      currentChunkIndex.value++
      playNextChunk(lang, activeSession)
      return
    }

    try {
      // Ensure any prior dead utterance is cleared without triggering advance
      isPausingIntentionally = true
      window.speechSynthesis.cancel()
      isPausingIntentionally = false
    } catch (e) {
      isPausingIntentionally = false
    }

    const utterance = new SpeechSynthesisUtterance(chunkText)
    utterance.lang = lang
    utterance.rate = speechRate.value
    utterance.pitch = 1.0

    const voice = getBestVoice(lang)
    if (voice) {
      utterance.voice = voice
      if (voice.lang) utterance.lang = voice.lang
    }

    currentChunkCharIndex = 0
    chunkStartedAt = Date.now()
    boundaryReceived = false

    utterance.onstart = () => {
      if (activeSession !== sessionId) return
      isSpeaking.value = true
      isPaused.value = false
      chunkStartedAt = Date.now()
      updateProgressValue(0)
      startSmoothProgressTicker(chunkText.length)
    }

    utterance.onboundary = (event) => {
      if (activeSession !== sessionId || isPaused.value) return
      if (typeof event.charIndex === 'number' && event.charIndex >= 0) {
        boundaryReceived = true
        currentChunkCharIndex = event.charIndex
        updateProgressValue(currentChunkCharIndex)
      }
    }

    utterance.onend = () => {
      if (activeSession !== sessionId || isPausingIntentionally || isPaused.value) return
      clearProgressTimer()
      currentChunkIndex.value++
      playNextChunk(lang, activeSession)
    }

    utterance.onerror = (e) => {
      if (activeSession !== sessionId || isPausingIntentionally || isPaused.value) return
      clearProgressTimer()
      // Ignore cancel/interrupt events caused by pause() or stop()
      if (e && (e.error === 'interrupted' || e.error === 'canceled')) {
        return
      }
      // If browser Web Speech engine fails (e.g., synthesis-failed, language-unavailable, audio-busy),
      // seamlessly switch to HTML5 <audio> fallback at the exact same chunk!
      if (
        e &&
        (e.error === 'synthesis-failed' ||
          e.error === 'synthesis-unavailable' ||
          e.error === 'language-unavailable' ||
          e.error === 'voice-unavailable' ||
          e.error === 'not-allowed')
      ) {
        isUsingHtml5Fallback = true
        playNextChunkHtml5(lang, activeSession)
        return
      }
      currentChunkIndex.value++
      playNextChunk(lang, activeSession)
    }

    // Keep global reference to prevent garbage-collection bug in Chrome/Safari/Firefox
    activeUtterance = utterance
    if (typeof window !== 'undefined') {
      window._activeNarratorUtterance = utterance
    }

    try {
      // Safari / Chrome quirk: if speechSynthesis was left in paused state by OS, resume queue
      if (window.speechSynthesis.paused) {
        window.speechSynthesis.resume()
      }
      isSpeaking.value = true
      isPaused.value = false
      window.speechSynthesis.speak(utterance)
    } catch (err) {
      isUsingHtml5Fallback = true
      playNextChunkHtml5(lang, activeSession)
    }
  }

  function pause() {
    if (!isSpeaking.value || isPaused.value) return

    isPausingIntentionally = true
    isPaused.value = true
    clearProgressTimer()

    // If using HTML5 <audio> fallback, native pause preserves exact currentTime millisecond
    if (isUsingHtml5Fallback && activeAudio) {
      try {
        activeAudio.pause()
      } catch (e) {}
      isPausingIntentionally = false
      return
    }

    // For Web Speech API: slice the current chunk at the exact spoken word boundary
    // and cancel the active utterance so that resume() speaks from the exact remaining words
    // across Chrome, Firefox, Safari, Brave, Edge, and Opera without EVER restarting from zero!
    const chunkText = textChunks[currentChunkIndex.value] || ''
    if (chunkText.length > 0) {
      const offset = getSpokenCharOffsetInCurrentChunk(chunkText)
      if (offset > 0 && offset < chunkText.length) {
        const remaining = chunkText.slice(offset).trim()
        if (remaining.length > 0) {
          textChunks[currentChunkIndex.value] = remaining
        }
      } else if (offset >= chunkText.length && currentChunkIndex.value + 1 < textChunks.length) {
        currentChunkIndex.value++
      }
    }

    if (hasWebSpeechSupport()) {
      try {
        window.speechSynthesis.cancel()
      } catch (e) {}
    }

    // Keep isPausingIntentionally true briefly so async onend/onerror from cancel() are ignored
    setTimeout(() => {
      if (isPaused.value) {
        isPausingIntentionally = false
      }
    }, 80)
  }

  function resume(lang = null) {
    if (!isSpeaking.value || !isPaused.value) return
    if (lang) currentLang = lang

    isPausingIntentionally = false
    isPaused.value = false

    // If using HTML5 <audio> fallback and activeAudio is paused mid-chunk, resume directly
    if (isUsingHtml5Fallback && activeAudio && activeAudio.src) {
      activeAudio.play().catch(() => {
        playNextChunkHtml5(currentLang, sessionId)
      })
      return
    }

    playNextChunk(currentLang, sessionId)
  }

  function start(fullText, lang = 'fr-FR') {
    stop()

    currentLang = lang || 'fr-FR'
    textChunks = splitTextIntoChunks(fullText)
    if (textChunks.length === 0) return

    chunkOriginalLengths = textChunks.map(c => c.length)
    totalCharacters = chunkOriginalLengths.reduce((sum, len) => sum + len, 0)
    totalChunksCount.value = textChunks.length
    currentChunkIndex.value = 0
    speechProgress.value = 0
    isSpeaking.value = true
    isPaused.value = false
    isPausingIntentionally = false
    isUsingHtml5Fallback = !hasWebSpeechSupport()

    const currentSession = ++sessionId
    playNextChunk(currentLang, currentSession)
  }

  function toggle(fullText, lang = 'fr-FR') {
    if (lang) currentLang = lang

    // 1. Currently playing -> Pause at exact position
    if (isSpeaking.value && !isPaused.value) {
      pause()
      return
    }

    // 2. Currently paused -> Resume from exact position (never restart from 0!)
    if (isSpeaking.value && isPaused.value && textChunks.length > 0) {
      resume(currentLang)
      return
    }

    // 3. Not started yet -> Start from beginning
    start(fullText, currentLang)
  }

  const statusText = computed(() => {
    if (!isSpeaking.value) return "Écouter l'article en audio"
    if (isPaused.value) return 'En pause — Cliquez pour reprendre où vous vous êtes arrêté'
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
    pause,
    resume,
    stop,
    toggle,
    calculateEstimatedMinutes,
  }
}
