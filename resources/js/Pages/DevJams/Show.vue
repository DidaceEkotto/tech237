<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';

const props = defineProps({
    jam: { type: Object, required: true },
    mySubmission: { type: Object, default: null },
    votedSubmissionIds: { type: Array, default: () => [] },
    initialMessages: { type: Array, default: () => [] },
    isJamCreator: { type: Boolean, default: false },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);
const canRearrangePreview = computed(() =>
    Boolean(props.isJamCreator || (authUser.value && Number(props.jam.user_id) === Number(authUser.value.id)))
);

// IDE Layout State
const activeSection = ref('editor'); // editor | gallery | info
const showExplorer = ref(true);
const showPreview = ref(true);
const previewFullscreen = ref(false);
const codeTab = ref('html'); // html | css | js
const toast = ref('');

// Creator-only Dockable Preview Layout ('right' | 'bottom')
const previewDockStorageKey = `dj_preview_dock_${props.jam.slug}`;
const previewDock = ref('right');
const isDraggingPreview = ref(false);
const previewDropZone = ref(null); // 'right' | 'bottom' | null
const previewDragCoords = ref({ x: 0, y: 0 });
const workspaceSplitEl = ref(null);

// Studio form state
const subTitle = ref(props.mySubmission?.title || 'Mon projet Dev Jam');
const subDescription = ref(props.mySubmission?.description || '');
const subType = ref(props.mySubmission?.submission_type || 'studio');
const htmlCode = ref(props.mySubmission?.html_code || `<div class="hero">\n  <span class="badge">Tech237 Dev Jam</span>\n  <h1>Construisons l'avenir</h1>\n  <p>Éditez index.html, style.css et app.js en direct.</p>\n  <button id="btn">Lancer l'animation ⚡</button>\n</div>`);
const cssCode = ref(props.mySubmission?.css_code || `body {\n  font-family: 'Segoe UI', system-ui, sans-serif;\n  display: grid;\n  place-items: center;\n  min-height: 100vh;\n  margin: 0;\n  background: radial-gradient(circle at top, #1e1b4b, #090b10 70%);\n  color: #f8fafc;\n}\n.hero {\n  background: rgba(255, 255, 255, 0.04);\n  border: 1px solid rgba(255, 255, 255, 0.12);\n  padding: 2.5rem;\n  border-radius: 20px;\n  text-align: center;\n  backdrop-filter: blur(12px);\n}\n.badge {\n  display: inline-block;\n  padding: 4px 12px;\n  border-radius: 999px;\n  background: rgba(99, 102, 241, 0.25);\n  color: #a5b4fc;\n  font-size: 12px;\n  font-weight: 700;\n}\nbutton {\n  margin-top: 14px;\n  padding: 10px 22px;\n  border-radius: 999px;\n  border: none;\n  background: linear-gradient(135deg, #6366f1, #06b6d4);\n  color: #ffffff;\n  font-weight: 700;\n  cursor: pointer;\n}`);
const jsCode = ref(props.mySubmission?.js_code || `document.getElementById('btn')?.addEventListener('click', () => {\n  const h1 = document.querySelector('h1');\n  if (h1) h1.textContent = 'Bravo ! 🚀 Code exécuté en direct';\n});`);
const externalRepoUrl = ref(props.mySubmission?.external_repo_url || '');
const externalDemoUrl = ref(props.mySubmission?.external_demo_url || '');
const savingSubmission = ref(false);
const previewKey = ref(0);

// Submissions & Votes
const submissions = ref([...(props.jam.submissions || [])]);
const votedIds = ref(new Set(props.votedSubmissionIds || []));
const previewSubmission = ref(null);

// Floating Live Chat State (Draggable & Persisted)
const chatOpen = ref(true);
const chatExpanded = ref(false);
const showEmojiPicker = ref(false);
const messages = ref([...(props.initialMessages || [])]);
const chatInput = ref('');
const sendingMessage = ref(false);
const activeTargetSubmissionId = ref(null);
const chatListEl = ref(null);
const chatWindowEl = ref(null);
const lastSentId = ref(null);
const unreadCount = ref(0);

const chatPosStorageKey = `dj_chat_pos_${props.jam.slug}`;
const chatPos = ref({ x: null, y: null });
const isDraggingChat = ref(false);
let chatDragOffset = { x: 0, y: 0 };

// Sound Effects (iPhone-style send swoosh)
const soundEnabled = ref(true);
let sharedAudioCtx = null;

// Floating Social Hearts State
const heartStorageKey = `dj_hearts_${props.jam.slug}`;
const heartCount = ref(0);
const heartPulse = ref(false);
const floatingHearts = ref([]);
let heartSeq = 0;
const HEART_COLORS = ['#f43f5e', '#ec4899', '#fb7185', '#e11d48', '#ff4d6d', '#f97316'];

const EMOJI_GROUPS = [
    '🚀', '🔥', '💻', '⚡', '🎉', '❤️', '👏', '😂',
    '😎', '🤔', '👀', '✨', '🐛', '💡', '🙌', '🏆',
    '💯', '🛠️', '🎨', '📦', '✅', '❌', '🤝', '💪',
];

let pollTimer = null;
let countdownTimer = null;
const countdownText = ref('');

const activeCodeText = computed(() => {
    if (codeTab.value === 'html') return htmlCode.value || '';
    if (codeTab.value === 'css') return cssCode.value || '';
    return jsCode.value || '';
});

const lineCount = computed(() => Math.max(20, activeCodeText.value.split('\n').length));

function buildSandboxDoc(html, css, js) {
    const safeCss = String(css || '').replace(/<\/style>/gi, '<\\/style>');
    const safeJs = String(js || '').replace(/<\/script>/gi, '<\\/script>');
    return `<!doctype html><html><head><meta charset="utf-8"><style>${safeCss}</style></head><body>${html || ''}<script>${safeJs}<\/script></body></html>`;
}

const liveSrcDoc = computed(() => buildSandboxDoc(htmlCode.value, cssCode.value, jsCode.value));

const filteredMessages = computed(() => {
    if (!activeTargetSubmissionId.value) return messages.value;
    return messages.value.filter((m) => m.submission_id === activeTargetSubmissionId.value);
});

function isMyMessage(m) {
    if (!authUser.value) return false;
    return Number(m.user_id) === Number(authUser.value.id);
}

function userInitials(name) {
    return String(name || 'M')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0].toUpperCase())
        .join('');
}

function formatMsgTime(ts) {
    if (!ts) return '';
    return new Date(ts).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function showToast(msg) {
    toast.value = msg;
    setTimeout(() => {
        if (toast.value === msg) toast.value = '';
    }, 3200);
}

function getAudioContext() {
    if (typeof window === 'undefined') return null;
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return null;
    if (!sharedAudioCtx) sharedAudioCtx = new AudioCtx();
    if (sharedAudioCtx.state === 'suspended') {
        sharedAudioCtx.resume().catch(() => {});
    }
    return sharedAudioCtx;
}

function playIphoneSendSound() {
    if (!soundEnabled.value) return;
    const ctx = getAudioContext();
    if (!ctx) return;

    const now = ctx.currentTime;

    // Layer 1: iMessage-style upward pitch swoosh
    const osc1 = ctx.createOscillator();
    const gain1 = ctx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(520, now);
    osc1.frequency.exponentialRampToValueAtTime(1240, now + 0.085);
    gain1.gain.setValueAtTime(0.001, now);
    gain1.gain.linearRampToValueAtTime(0.16, now + 0.015);
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.12);
    osc1.connect(gain1);
    gain1.connect(ctx.destination);
    osc1.start(now);
    osc1.stop(now + 0.13);

    // Layer 2: Crisp upper harmonic pop
    const osc2 = ctx.createOscillator();
    const gain2 = ctx.createGain();
    osc2.type = 'triangle';
    osc2.frequency.setValueAtTime(980, now + 0.025);
    osc2.frequency.exponentialRampToValueAtTime(1680, now + 0.095);
    gain2.gain.setValueAtTime(0.001, now + 0.025);
    gain2.gain.linearRampToValueAtTime(0.08, now + 0.04);
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.11);
    osc2.connect(gain2);
    gain2.connect(ctx.destination);
    osc2.start(now + 0.025);
    osc2.stop(now + 0.12);
}

function playHeartPopSound() {
    if (!soundEnabled.value) return;
    const ctx = getAudioContext();
    if (!ctx) return;

    const now = ctx.currentTime;
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    const baseFreq = 580 + Math.random() * 180;
    osc.frequency.setValueAtTime(baseFreq, now);
    osc.frequency.exponentialRampToValueAtTime(baseFreq * 1.45, now + 0.06);
    gain.gain.setValueAtTime(0.001, now);
    gain.gain.linearRampToValueAtTime(0.06, now + 0.01);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.08);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start(now);
    osc.stop(now + 0.09);
}

function toggleSound() {
    soundEnabled.value = !soundEnabled.value;
    try {
        localStorage.setItem('dj_sound_enabled', soundEnabled.value ? '1' : '0');
    } catch {
        // ignore storage errors
    }
}

// Floating Hearts (Multi-click Social Animation)
function spawnHeart(e) {
    heartCount.value += 1;
    heartPulse.value = true;
    setTimeout(() => {
        heartPulse.value = false;
    }, 180);

    try {
        localStorage.setItem(heartStorageKey, String(heartCount.value));
    } catch {
        // ignore storage errors
    }

    playHeartPopSound();

    const rect = e?.currentTarget?.getBoundingClientRect?.();
    const originX = rect ? rect.left + rect.width / 2 : window.innerWidth - 60;
    const originY = rect ? rect.top + rect.height / 2 : window.innerHeight - 80;

    const burstCount = Math.random() > 0.65 ? 2 : 1;
    for (let i = 0; i < burstCount; i++) {
        const id = ++heartSeq;
        const duration = 1400 + Math.round(Math.random() * 550);
        floatingHearts.value.push({
            id,
            x: originX + (Math.random() * 24 - 12),
            y: originY - 6,
            drift: Math.round(Math.random() * 76 - 38),
            rot: Math.round(Math.random() * 40 - 20),
            scale: (0.85 + Math.random() * 0.55).toFixed(2),
            color: HEART_COLORS[Math.floor(Math.random() * HEART_COLORS.length)],
            duration,
        });
        setTimeout(() => {
            floatingHearts.value = floatingHearts.value.filter((h) => h.id !== id);
        }, duration + 60);
    }
}

// Draggable Floating Chat Window
function clampChatPos(x, y) {
    const winW = window.innerWidth;
    const winH = window.innerHeight;
    const w = chatWindowEl.value?.offsetWidth || (chatExpanded.value ? 460 : 350);
    const h = chatWindowEl.value?.offsetHeight || (chatExpanded.value ? 600 : 470);
    const maxX = Math.max(8, winW - w - 8);
    const maxY = Math.max(8, winH - h - 8);
    return {
        x: Math.min(Math.max(8, x), maxX),
        y: Math.min(Math.max(8, y), maxY),
    };
}

function getDefaultChatPos() {
    const w = chatExpanded.value ? 460 : 350;
    const h = chatExpanded.value ? 600 : 470;
    return clampChatPos(window.innerWidth - w - 20, window.innerHeight - h - 38);
}

const chatStyle = computed(() => {
    if (chatPos.value.x === null || chatPos.value.y === null) {
        return { right: '20px', bottom: '34px' };
    }
    return {
        left: `${chatPos.value.x}px`,
        top: `${chatPos.value.y}px`,
        right: 'auto',
        bottom: 'auto',
    };
});

function startChatDrag(e) {
    if (e.target.closest('button') || e.target.closest('select')) return;
    const el = chatWindowEl.value;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    chatPos.value = { x: rect.left, y: rect.top };
    chatDragOffset = { x: e.clientX - rect.left, y: e.clientY - rect.top };
    isDraggingChat.value = true;
    window.addEventListener('pointermove', onChatDragMove);
    window.addEventListener('pointerup', onChatDragEnd);
}

function onChatDragMove(e) {
    if (!isDraggingChat.value) return;
    chatPos.value = clampChatPos(e.clientX - chatDragOffset.x, e.clientY - chatDragOffset.y);
}

function onChatDragEnd() {
    if (!isDraggingChat.value) return;
    isDraggingChat.value = false;
    window.removeEventListener('pointermove', onChatDragMove);
    window.removeEventListener('pointerup', onChatDragEnd);
    try {
        localStorage.setItem(chatPosStorageKey, JSON.stringify(chatPos.value));
    } catch {
        // ignore storage errors
    }
}

function resetChatPosition() {
    chatPos.value = getDefaultChatPos();
    try {
        localStorage.setItem(chatPosStorageKey, JSON.stringify(chatPos.value));
    } catch {
        // ignore storage errors
    }
}

// Creator-Only Draggable & Dockable Preview ("Aperçu en direct")
function setPreviewDock(zone) {
    if (!canRearrangePreview.value) return;
    previewDock.value = zone === 'bottom' ? 'bottom' : 'right';
    try {
        localStorage.setItem(previewDockStorageKey, previewDock.value);
    } catch {
        // ignore storage errors
    }
}

let previewDragStart = { x: 0, y: 0 };
function startPreviewDrag(e) {
    if (!canRearrangePreview.value) return;
    if (e.target.closest('button')) return;
    previewDragStart = { x: e.clientX, y: e.clientY };
    previewDragCoords.value = { x: e.clientX, y: e.clientY };
    isDraggingPreview.value = false;
    window.addEventListener('pointermove', onPreviewDragMove);
    window.addEventListener('pointerup', onPreviewDragEnd);
}

function onPreviewDragMove(e) {
    const dx = e.clientX - previewDragStart.x;
    const dy = e.clientY - previewDragStart.y;
    if (!isDraggingPreview.value && Math.hypot(dx, dy) < 10) return;

    isDraggingPreview.value = true;
    previewDragCoords.value = { x: e.clientX, y: e.clientY };

    const rect = workspaceSplitEl.value?.getBoundingClientRect();
    if (!rect) {
        previewDropZone.value = e.clientY > window.innerHeight * 0.55 ? 'bottom' : 'right';
        return;
    }
    const relY = (e.clientY - rect.top) / Math.max(1, rect.height);
    const relX = (e.clientX - rect.left) / Math.max(1, rect.width);
    if (relY > 0.55) {
        previewDropZone.value = 'bottom';
    } else if (relX > 0.45) {
        previewDropZone.value = 'right';
    } else {
        previewDropZone.value = 'bottom';
    }
}

function onPreviewDragEnd() {
    window.removeEventListener('pointermove', onPreviewDragMove);
    window.removeEventListener('pointerup', onPreviewDragEnd);
    if (isDraggingPreview.value && previewDropZone.value) {
        setPreviewDock(previewDropZone.value);
        showToast(
            previewDropZone.value === 'bottom'
                ? 'Aperçu placé en bas du Sandbox (pleine largeur).'
                : 'Aperçu replacé à droite du Sandbox.'
        );
    }
    isDraggingPreview.value = false;
    previewDropZone.value = null;
}

function handleTabKey(e, target) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        saveMySubmission();
        return;
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        previewKey.value++;
        return;
    }
    if (e.key !== 'Tab') return;
    e.preventDefault();
    const el = e.target;
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const current = target === 'html' ? htmlCode.value : (target === 'css' ? cssCode.value : jsCode.value);
    const updated = current.substring(0, start) + '  ' + current.substring(end);
    if (target === 'html') htmlCode.value = updated;
    else if (target === 'css') cssCode.value = updated;
    else jsCode.value = updated;
    nextTick(() => {
        el.selectionStart = el.selectionEnd = start + 2;
    });
}

function insertEmoji(emoji) {
    chatInput.value += emoji;
}

async function saveMySubmission() {
    if (!authUser.value) {
        showToast('Connectez-vous pour enregistrer votre participation.');
        return;
    }
    savingSubmission.value = true;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/soumettre`, {
            title: subTitle.value,
            description: subDescription.value,
            submission_type: subType.value,
            html_code: htmlCode.value,
            css_code: cssCode.value,
            js_code: jsCode.value,
            external_repo_url: externalRepoUrl.value || null,
            external_demo_url: externalDemoUrl.value || null,
        });
        const saved = res.data.submission;
        const idx = submissions.value.findIndex((s) => s.id === saved.id);
        if (idx !== -1) submissions.value[idx] = saved;
        else submissions.value.unshift(saved);
        showToast(res.data.message);
    } catch (e) {
        showToast(e.response?.data?.message || 'Erreur lors de la sauvegarde.');
    } finally {
        savingSubmission.value = false;
    }
}

async function toggleVote(sub) {
    if (!authUser.value) {
        showToast('Connectez-vous pour voter.');
        return;
    }
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/submissions/${sub.id}/vote`);
        sub.votes_count = res.data.votes_count;
        if (res.data.voted) votedIds.value.add(sub.id);
        else votedIds.value.delete(sub.id);
    } catch (e) {
        showToast(e.response?.data?.message || 'Vote impossible.');
    }
}

async function sendMessage() {
    if (!authUser.value || !chatInput.value.trim()) return;
    sendingMessage.value = true;
    showEmojiPicker.value = false;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/messages`, {
            message: chatInput.value.trim(),
            submission_id: activeTargetSubmissionId.value,
        });
        const newMsg = res.data.message;
        if (!messages.value.some((m) => m.id === newMsg.id)) {
            lastSentId.value = newMsg.id;
            messages.value.push(newMsg);
        }
        chatInput.value = '';
        playIphoneSendSound();
        nextTick(scrollChatBottom);
    } catch (e) {
        showToast(e.response?.data?.message || 'Message non envoyé.');
    } finally {
        sendingMessage.value = false;
    }
}

async function deleteMsg(msg) {
    try {
        await apiClient.delete(`/dev-jams/${props.jam.slug}/messages/${msg.id}`);
        messages.value = messages.value.filter((m) => m.id !== msg.id);
    } catch {
        showToast('Suppression impossible.');
    }
}

async function pollMessages() {
    try {
        const lastId = messages.value.length ? messages.value[messages.value.length - 1].id : 0;
        const res = await apiClient.get(`/dev-jams/${props.jam.slug}/messages?after_id=${lastId}`);
        const incoming = res.data.messages || [];
        if (incoming.length) {
            let added = 0;
            incoming.forEach((m) => {
                if (!messages.value.some((x) => x.id === m.id)) {
                    messages.value.push(m);
                    added += 1;
                }
            });
            if (added > 0 && !chatOpen.value) {
                unreadCount.value += added;
            }
            nextTick(scrollChatBottom);
        }
    } catch {
        // silent background poll
    }
}

function openChat() {
    chatOpen.value = true;
    unreadCount.value = 0;
    nextTick(scrollChatBottom);
}

function scrollChatBottom() {
    if (chatListEl.value) {
        chatListEl.value.scrollTop = chatListEl.value.scrollHeight;
    }
}

function updateCountdown() {
    const now = Date.now();
    const start = new Date(props.jam.starts_at).getTime();
    const end = new Date(props.jam.ends_at).getTime();
    const target = now < start ? start : end;
    const diff = target - now;
    if (diff <= 0) {
        countdownText.value = 'Temps écoulé';
        return;
    }
    const d = Math.floor(diff / 86400000);
    const h = Math.floor((diff % 86400000) / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    countdownText.value = `${now < start ? 'Début dans ' : 'Fin dans '}${d}j ${h}h ${m}m ${s}s`;
}

function focusSubmissionChat(sub) {
    activeTargetSubmissionId.value = sub.id;
    openChat();
}

function handleWindowResize() {
    if (chatPos.value.x !== null && chatPos.value.y !== null) {
        chatPos.value = clampChatPos(chatPos.value.x, chatPos.value.y);
    }
}

onMounted(() => {
    try {
        const savedPos = localStorage.getItem(chatPosStorageKey);
        if (savedPos) {
            const parsed = JSON.parse(savedPos);
            if (typeof parsed?.x === 'number' && typeof parsed?.y === 'number') {
                chatPos.value = clampChatPos(parsed.x, parsed.y);
            }
        }
        const savedDock = localStorage.getItem(previewDockStorageKey);
        if (savedDock === 'bottom' || savedDock === 'right') {
            previewDock.value = savedDock;
        }
        const savedHearts = Number(localStorage.getItem(heartStorageKey) || 0);
        if (!Number.isNaN(savedHearts)) heartCount.value = savedHearts;

        const savedSound = localStorage.getItem('dj_sound_enabled');
        if (savedSound === '0') soundEnabled.value = false;
    } catch {
        // ignore storage errors
    }

    updateCountdown();
    countdownTimer = setInterval(updateCountdown, 1000);
    pollTimer = setInterval(pollMessages, 3500);
    window.addEventListener('resize', handleWindowResize);
    nextTick(scrollChatBottom);
});

onUnmounted(() => {
    clearInterval(countdownTimer);
    clearInterval(pollTimer);
    window.removeEventListener('resize', handleWindowResize);
    window.removeEventListener('pointermove', onChatDragMove);
    window.removeEventListener('pointerup', onChatDragEnd);
    window.removeEventListener('pointermove', onPreviewDragMove);
    window.removeEventListener('pointerup', onPreviewDragEnd);
});
</script>

<template>
    <Head :title="`${jam.title} • Dev Jam Studio`" />

    <div class="dj-ide-root d-flex flex-column text-light vh-100 overflow-hidden">
        <!-- VS Code Style Top Title Bar -->
        <header class="dj-topbar d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <Link href="/dev-jams" class="btn btn-sm dj-btn-ghost rounded-3 px-2 py-1" title="Retour aux Dev Jams">
                    <i class="bi bi-arrow-left me-1"></i>Jams
                </Link>
                <div class="dj-traffic d-none d-md-flex gap-1 mx-1">
                    <span class="dot bg-danger"></span>
                    <span class="dot bg-warning"></span>
                    <span class="dot bg-success"></span>
                </div>
                <span class="fw-bold text-truncate small text-white">{{ jam.title }}</span>
                <span class="badge rounded-pill d-none sm-inline" :class="jam.mode === 'official' ? 'bg-danger' : 'bg-primary'" style="font-size: 10px;">
                    {{ jam.mode === 'official' ? 'Officielle Tech237' : 'Communautaire' }}
                </span>
                <span v-if="jam.theme" class="badge bg-white bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill d-none d-xl-inline" style="font-size: 10px;">
                    {{ jam.theme }}
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-black bg-opacity-50 border border-secondary border-opacity-50 text-warning rounded-pill px-2 py-1 d-none d-md-inline" style="font-size: 11px;">
                    <i class="bi bi-clock me-1"></i>{{ countdownText }}
                </span>

                <button
                    type="button"
                    class="btn btn-sm rounded-3 px-2 py-1"
                    :class="showPreview ? 'btn-outline-info' : 'dj-btn-ghost'"
                    title="Afficher / Masquer l'aperçu en direct"
                    @click="showPreview = !showPreview"
                >
                    <i class="bi bi-layout-split me-1"></i><span class="d-none d-sm-inline">Aperçu</span>
                </button>

                <button
                    type="button"
                    class="btn btn-sm rounded-3 px-3 py-1 fw-semibold dj-save-btn"
                    :disabled="savingSubmission"
                    @click="saveMySubmission"
                >
                    <span v-if="savingSubmission" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-cloud-arrow-up-fill me-1"></i>
                    <span class="d-none d-sm-inline">Enregistrer</span>
                </button>
            </div>
        </header>

        <div v-if="toast" class="position-fixed top-0 start-50 translate-middle-x mt-5 px-3 py-2 rounded-pill shadow-lg small fw-semibold dj-toast" style="z-index: 2600;">
            <i class="bi bi-check-circle-fill text-info me-2"></i>{{ toast }}
        </div>

        <!-- Main IDE Workspace -->
        <div class="d-flex flex-grow-1 overflow-hidden">
            <!-- Far-Left VS Code Activity Bar -->
            <aside class="dj-activity-bar d-flex flex-column align-items-center py-2 gap-2 border-end">
                <button
                    type="button"
                    class="dj-activity-btn"
                    :class="{ active: activeSection === 'editor' && showExplorer }"
                    title="Explorateur & Éditeur"
                    @click="if (activeSection === 'editor') showExplorer = !showExplorer; else { activeSection = 'editor'; showExplorer = true; }"
                >
                    <i class="bi bi-files fs-5"></i>
                </button>
                <button
                    type="button"
                    class="dj-activity-btn"
                    :class="{ active: activeSection === 'gallery' }"
                    title="Projets & Votes"
                    @click="activeSection = 'gallery'"
                >
                    <i class="bi bi-grid-1x2 fs-5"></i>
                    <span class="dj-mini-badge">{{ submissions.length }}</span>
                </button>
                <button
                    type="button"
                    class="dj-activity-btn"
                    :class="{ active: activeSection === 'info' }"
                    title="Infos & Règlement de la Jam"
                    @click="activeSection = 'info'"
                >
                    <i class="bi bi-info-circle fs-5"></i>
                </button>
                <button
                    type="button"
                    class="dj-activity-btn mt-auto"
                    :class="{ active: chatOpen }"
                    title="Live Chat Flottant"
                    @click="chatOpen ? (chatOpen = false) : openChat()"
                >
                    <i class="bi bi-chat-dots-fill fs-5"></i>
                    <span class="dj-mini-badge" :class="unreadCount > 0 ? 'bg-danger' : 'bg-primary'">
                        {{ unreadCount > 0 ? `+${unreadCount}` : filteredMessages.length }}
                    </span>
                </button>
            </aside>

            <!-- Collapsible Explorer Sidebar (in Editor mode) -->
            <aside v-if="activeSection === 'editor' && showExplorer" class="dj-sidebar d-none d-md-flex flex-column border-end p-3 overflow-auto">
                <div class="text-uppercase text-white-50 fw-bold mb-2" style="font-size: 10px; letter-spacing: 1px;">Explorateur Jam</div>

                <div class="mb-3">
                    <label class="form-label text-white-50 mb-1" style="font-size: 11px;">Nom de votre projet</label>
                    <input v-model="subTitle" type="text" class="form-control form-control-sm dj-dark-input mb-2" placeholder="Titre du projet..." />
                    <input v-model="subDescription" type="text" class="form-control form-control-sm dj-dark-input mb-2" placeholder="Courte description..." />
                    <select v-if="jam.allow_external_links" v-model="subType" class="form-select form-select-sm dj-dark-input">
                        <option value="studio">Studio HTML/CSS/JS</option>
                        <option value="external">Lien GitHub / Démo</option>
                    </select>
                </div>

                <div v-if="subType === 'studio'" class="mb-3">
                    <div class="text-uppercase text-white-50 fw-bold mb-1" style="font-size: 10px; letter-spacing: 1px;">Fichiers</div>
                    <button type="button" class="dj-file-item" :class="{ active: codeTab === 'html' }" @click="codeTab = 'html'">
                        <i class="bi bi-filetype-html text-danger me-2"></i>index.html
                    </button>
                    <button type="button" class="dj-file-item" :class="{ active: codeTab === 'css' }" @click="codeTab = 'css'">
                        <i class="bi bi-filetype-css text-info me-2"></i>style.css
                    </button>
                    <button type="button" class="dj-file-item" :class="{ active: codeTab === 'js' }" @click="codeTab = 'js'">
                        <i class="bi bi-filetype-js text-warning me-2"></i>app.js
                    </button>
                </div>

                <div v-if="jam.prize_text" class="mt-auto p-2 rounded-3 dj-glass-box small">
                    <div class="text-warning fw-bold mb-1" style="font-size: 11px;"><i class="bi bi-trophy-fill me-1"></i>Récompense</div>
                    <div class="text-white-50" style="font-size: 11px;">{{ jam.prize_text }}</div>
                </div>
            </aside>

            <!-- CENTER: EDITOR + PREVIEW / GALLERY / INFO -->
            <main class="flex-grow-1 d-flex flex-column overflow-hidden">
                <!-- SECTION 1: VS CODE EDITOR + LIVE PREVIEW -->
                <template v-if="activeSection === 'editor'">
                    <!-- External Links Mode -->
                    <div v-if="subType === 'external' && jam.allow_external_links" class="p-4 p-md-5 overflow-auto flex-grow-1">
                        <div class="mx-auto p-4 rounded-4 dj-glass-box" style="max-width: 680px;">
                            <h5 class="fw-bold mb-2"><i class="bi bi-github me-2"></i>Soumission Projet Externe</h5>
                            <p class="small text-white-50 mb-4">Pour les projets Backend, Mobile ou Fullstack hébergés ailleurs.</p>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Lien Dépôt GitHub / GitLab</label>
                                <input v-model="externalRepoUrl" type="url" class="form-control dj-dark-input" placeholder="https://github.com/..." />
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Lien Démo en ligne (ou Portfolio Tech237)</label>
                                <input v-model="externalDemoUrl" type="url" class="form-control dj-dark-input" placeholder="https://..." />
                            </div>
                            <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" :disabled="savingSubmission" @click="saveMySubmission">
                                Enregistrer la soumission
                            </button>
                        </div>
                    </div>

                    <!-- Studio Code + Live Preview Split -->
                    <div
                        v-else
                        ref="workspaceSplitEl"
                        class="d-flex flex-grow-1 overflow-hidden position-relative"
                        :class="previewDock === 'bottom' ? 'flex-column' : 'flex-column flex-lg-row'"
                    >
                        <!-- Code Pane -->
                        <div
                            v-show="!previewFullscreen"
                            class="d-flex flex-column overflow-hidden"
                            :class="[
                                previewDock === 'bottom'
                                    ? (showPreview ? 'w-100 dj-split-bottom-pane border-bottom border-secondary border-opacity-25' : 'w-100 h-100')
                                    : (showPreview ? 'w-100 w-lg-50 border-end border-secondary border-opacity-25' : 'w-100'),
                            ]"
                        >
                            <!-- VS Code Tabs -->
                            <div class="dj-tabs d-flex align-items-center justify-content-between px-2 border-bottom">
                                <div class="d-flex">
                                    <button type="button" class="dj-tab" :class="{ active: codeTab === 'html' }" @click="codeTab = 'html'">
                                        <i class="bi bi-filetype-html text-danger me-1"></i>index.html
                                    </button>
                                    <button type="button" class="dj-tab" :class="{ active: codeTab === 'css' }" @click="codeTab = 'css'">
                                        <i class="bi bi-filetype-css text-info me-1"></i>style.css
                                    </button>
                                    <button type="button" class="dj-tab" :class="{ active: codeTab === 'js' }" @click="codeTab = 'js'">
                                        <i class="bi bi-filetype-js text-warning me-1"></i>app.js
                                    </button>
                                </div>
                                <span class="small text-white-50 d-none d-sm-inline pe-2" style="font-size: 11px;">
                                    <i class="bi bi-shield-lock-fill text-success me-1"></i>Sandbox
                                </span>
                            </div>

                            <!-- Gutter + Textarea -->
                            <div class="d-flex flex-grow-1 overflow-auto dj-code-surface position-relative">
                                <div class="dj-gutter text-end user-select-none py-3 px-2 font-monospace">
                                    <div v-for="n in lineCount" :key="n">{{ n }}</div>
                                </div>
                                <textarea
                                    v-if="codeTab === 'html'"
                                    v-model="htmlCode"
                                    spellcheck="false"
                                    class="dj-textarea flex-grow-1 font-monospace p-3"
                                    @keydown="handleTabKey($event, 'html')"
                                ></textarea>
                                <textarea
                                    v-else-if="codeTab === 'css'"
                                    v-model="cssCode"
                                    spellcheck="false"
                                    class="dj-textarea flex-grow-1 font-monospace p-3 text-info"
                                    @keydown="handleTabKey($event, 'css')"
                                ></textarea>
                                <textarea
                                    v-else
                                    v-model="jsCode"
                                    spellcheck="false"
                                    class="dj-textarea flex-grow-1 font-monospace p-3 text-warning"
                                    @keydown="handleTabKey($event, 'js')"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Live Preview Pane ("Aperçu en direct" in sleek browser frame) -->
                        <div
                            v-if="showPreview"
                            class="d-flex flex-column overflow-hidden bg-black"
                            :class="[
                                previewFullscreen
                                    ? 'w-100 h-100'
                                    : (previewDock === 'bottom' ? 'w-100 dj-split-bottom-pane' : 'w-100 w-lg-50'),
                            ]"
                        >
                            <div
                                class="dj-preview-bar d-flex align-items-center justify-content-between px-3 py-1 border-bottom user-select-none"
                                :class="{ 'dj-preview-draggable': canRearrangePreview && !previewFullscreen }"
                                :title="canRearrangePreview ? 'Maintenir et glisser pour placer en bas du Sandbox ou à droite' : ''"
                                @pointerdown="startPreviewDrag"
                            >
                                <div class="d-flex align-items-center gap-2">
                                    <i v-if="canRearrangePreview && !previewFullscreen" class="bi bi-grip-vertical text-white-50"></i>
                                    <span class="badge bg-success bg-opacity-25 text-success rounded-pill" style="font-size: 10px;">● LIVE</span>
                                    <span class="small text-white-50 font-monospace" style="font-size: 11px;">Aperçu en direct</span>
                                    <span v-if="canRearrangePreview" class="badge bg-white bg-opacity-10 text-info d-none d-xl-inline" style="font-size: 9px;">
                                        {{ previewDock === 'bottom' ? 'Ancré en bas' : 'Ancré à droite' }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <button
                                        v-if="canRearrangePreview && !previewFullscreen"
                                        type="button"
                                        class="btn btn-xs dj-btn-ghost py-0 px-2"
                                        :title="previewDock === 'bottom' ? 'Revenir à droite du Sandbox' : 'Placer en bas du Sandbox'"
                                        @click="setPreviewDock(previewDock === 'bottom' ? 'right' : 'bottom')"
                                    >
                                        <i class="bi" :class="previewDock === 'bottom' ? 'bi-layout-sidebar-reverse' : 'bi-layout-split'"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs dj-btn-ghost py-0 px-2" title="Rafraîchir l'aperçu (Ctrl+Entrée)" @click="previewKey++">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs dj-btn-ghost py-0 px-2" :title="previewFullscreen ? 'Réduire' : 'Plein écran'" @click="previewFullscreen = !previewFullscreen">
                                        <i class="bi" :class="previewFullscreen ? 'bi-fullscreen-exit' : 'bi-fullscreen'"></i>
                                    </button>
                                </div>
                            </div>
                            <iframe
                                :key="previewKey"
                                :srcdoc="liveSrcDoc"
                                sandbox="allow-scripts allow-modals"
                                class="w-100 border-0 flex-grow-1 bg-white"
                                title="Aperçu en direct Dev Jam"
                            ></iframe>
                        </div>

                        <!-- Creator Drag-and-Drop Zone Overlays -->
                        <div v-if="isDraggingPreview" class="dj-drop-overlay">
                            <div class="dj-drop-zone dj-drop-right" :class="{ active: previewDropZone === 'right' }">
                                <i class="bi bi-layout-sidebar-reverse fs-3 mb-2"></i>
                                <span class="fw-semibold small">Aperçu à droite (côte à côte)</span>
                            </div>
                            <div class="dj-drop-zone dj-drop-bottom" :class="{ active: previewDropZone === 'bottom' }">
                                <i class="bi bi-layout-split fs-3 mb-2"></i>
                                <span class="fw-semibold small">Aperçu en bas du Sandbox (pleine largeur)</span>
                            </div>
                        </div>
                        <div
                            v-if="isDraggingPreview"
                            class="dj-drag-ghost badge rounded-pill px-3 py-2 shadow-lg"
                            :style="{ left: `${previewDragCoords.x + 14}px`, top: `${previewDragCoords.y + 14}px` }"
                        >
                            <i class="bi bi-window me-1"></i>Aperçu en direct
                        </div>
                    </div>
                </template>

                <!-- SECTION 2: GALLERY & VOTES -->
                <div v-else-if="activeSection === 'gallery'" class="p-3 p-md-4 overflow-auto flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-grid-1x2 me-2 text-info"></i>Projets soumis &amp; Votes ({{ submissions.length }})</h5>
                        <button type="button" class="btn btn-sm btn-outline-light rounded-pill" @click="activeSection = 'editor'">
                            <i class="bi bi-code-slash me-1"></i>Retour à l'éditeur
                        </button>
                    </div>
                    <div v-if="submissions.length" class="row g-3">
                        <div v-for="sub in submissions" :key="sub.id" class="col-md-6 col-xl-4">
                            <div class="dj-glass-box rounded-4 p-3 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-white">{{ sub.title }}</h6>
                                        <div class="small text-white-50">par {{ sub.user?.name || 'Membre' }}</div>
                                    </div>
                                    <span v-if="sub.is_winner" class="badge bg-warning text-dark rounded-pill">
                                        <i class="bi bi-trophy-fill me-1"></i>Gagnant
                                    </span>
                                </div>
                                <p v-if="sub.description" class="small text-white-50 mb-2">{{ sub.description }}</p>

                                <div v-if="sub.submission_type === 'studio'" class="mb-2 border border-secondary border-opacity-25 rounded-3 overflow-hidden bg-white">
                                    <iframe :srcdoc="buildSandboxDoc(sub.html_code, sub.css_code, sub.js_code)" sandbox="allow-scripts" class="w-100 border-0" style="height: 190px;" :title="sub.title"></iframe>
                                </div>
                                <div v-else class="mb-2 d-flex gap-2 flex-wrap">
                                    <a v-if="sub.external_demo_url" :href="sub.external_demo_url" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info rounded-pill">Démo</a>
                                    <a v-if="sub.external_repo_url" :href="sub.external_repo_url" target="_blank" rel="noopener" class="btn btn-sm btn-outline-light rounded-pill">GitHub</a>
                                </div>

                                <div class="mt-auto pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm rounded-pill px-3" :class="votedIds.has(sub.id) ? 'btn-danger' : 'btn-outline-danger'" @click="toggleVote(sub)">
                                        <i class="bi bi-heart-fill me-1"></i>{{ sub.votes_count || 0 }}
                                    </button>
                                    <div class="d-flex gap-1">
                                        <button v-if="sub.submission_type === 'studio'" type="button" class="btn btn-sm dj-btn-ghost rounded-pill" @click="previewSubmission = sub">
                                            <i class="bi bi-arrows-fullscreen"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill" @click="focusSubmissionChat(sub)">
                                            <i class="bi bi-chat-dots me-1"></i>Commenter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-5 text-white-50">
                        Aucun projet soumis pour le moment. Soyez le premier !
                    </div>
                </div>

                <!-- SECTION 3: JAM INFO & RULES -->
                <div v-else class="p-3 p-md-4 overflow-auto flex-grow-1">
                    <div class="mx-auto dj-glass-box rounded-4 p-4" style="max-width: 760px;">
                        <h4 class="fw-bold mb-2">{{ jam.title }}</h4>
                        <p class="text-white-50">{{ jam.description }}</p>
                        <div v-if="jam.rules" class="p-3 rounded-3 bg-black bg-opacity-40 border border-secondary border-opacity-25 mb-3">
                            <h6 class="fw-bold text-info mb-1">Règlement</h6>
                            <p class="small text-white-50 mb-0" style="white-space: pre-line;">{{ jam.rules }}</p>
                        </div>
                        <div v-if="jam.prize_text" class="p-3 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 text-warning small">
                            <i class="bi bi-trophy-fill me-2"></i><strong>Récompense :</strong> {{ jam.prize_text }}
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- VS Code Bottom Status Bar -->
        <footer class="dj-statusbar d-flex align-items-center justify-content-between px-3 py-1 border-top small">
            <div class="d-flex align-items-center gap-3">
                <span><i class="bi bi-git me-1"></i>tech237-jam</span>
                <span class="d-none d-sm-inline"><i class="bi bi-shield-check text-success me-1"></i>Sandbox Isolé</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-md-inline">UTF-8</span>
                <span>{{ codeTab.toUpperCase() }}</span>
                <span class="d-none d-sm-inline">{{ submissions.length }} projet(s)</span>
                <button
                    type="button"
                    class="btn btn-xs rounded-pill px-2 py-0 dj-heart-btn d-flex align-items-center gap-1"
                    :class="{ 'is-pulsing': heartPulse }"
                    title="Envoyer des cœurs (cliquez plusieurs fois !)"
                    @click="spawnHeart($event)"
                >
                    <i class="bi bi-heart-fill text-danger"></i>
                    <span class="fw-bold" style="font-size: 11px;">{{ heartCount }}</span>
                </button>
            </div>
        </footer>

        <!-- FLOATING LIVE CHAT WINDOW (Draggable by any participant) -->
        <div
            v-if="chatOpen"
            ref="chatWindowEl"
            class="dj-floating-chat shadow-lg d-flex flex-column"
            :class="{ expanded: chatExpanded, 'is-dragging': isDraggingChat }"
            :style="chatStyle"
        >
            <div
                class="dj-chat-header d-flex align-items-center justify-content-between px-3 py-2 border-bottom user-select-none"
                title="Maintenir pour déplacer le chat n'importe où sur l'écran"
                @pointerdown="startChatDrag"
            >
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <i class="bi bi-grip-horizontal text-white-50"></i>
                    <span class="dj-live-dot flex-shrink-0"></span>
                    <span class="fw-bold small text-white text-truncate">Live Chat &amp; Commentaires</span>
                </div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                    <button
                        type="button"
                        class="btn btn-xs dj-btn-ghost"
                        :title="soundEnabled ? 'Désactiver le son d\'envoi' : 'Activer le son d\'envoi'"
                        @click="toggleSound"
                    >
                        <i class="bi" :class="soundEnabled ? 'bi-volume-up-fill text-info' : 'bi-volume-mute-fill text-white-50'"></i>
                    </button>
                    <button
                        type="button"
                        class="btn btn-xs dj-btn-ghost"
                        title="Réinitialiser la position du chat"
                        @click="resetChatPosition"
                    >
                        <i class="bi bi-geo-alt"></i>
                    </button>
                    <button type="button" class="btn btn-xs dj-btn-ghost" :title="chatExpanded ? 'Réduire' : 'Agrandir'" @click="chatExpanded = !chatExpanded">
                        <i class="bi" :class="chatExpanded ? 'bi-arrows-angle-contract' : 'bi-arrows-angle-expand'"></i>
                    </button>
                    <button type="button" class="btn btn-xs dj-btn-ghost" title="Réduire en bulle" @click="chatOpen = false">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>

            <div class="px-2 py-2 border-bottom border-secondary border-opacity-25">
                <select v-model="activeTargetSubmissionId" class="form-select form-select-sm dj-dark-input">
                    <option :value="null">💬 Chat général de la Jam</option>
                    <option v-for="s in submissions" :key="s.id" :value="s.id">
                        📌 Commentaires : {{ s.title }}
                    </option>
                </select>
            </div>

            <div ref="chatListEl" class="dj-chat-body flex-grow-1 overflow-auto p-3 d-flex flex-column gap-2">
                <div v-if="!filteredMessages.length" class="text-center text-white-50 small my-auto py-4">
                    Aucun message pour le moment. Envoyez le premier ! 🚀
                </div>

                <div
                    v-for="m in filteredMessages"
                    :key="m.id"
                    class="d-flex gap-2 align-items-end dj-msg-anim"
                    :class="[
                        isMyMessage(m) ? 'flex-row-reverse' : 'flex-row',
                        lastSentId === m.id ? 'dj-msg-just-sent' : '',
                    ]"
                >
                    <div class="dj-avatar flex-shrink-0" :class="isMyMessage(m) ? 'dj-avatar-me' : 'dj-avatar-other'">
                        {{ userInitials(m.user?.name) }}
                    </div>
                    <div
                        class="dj-bubble px-3 py-2"
                        :class="isMyMessage(m) ? 'dj-bubble-me' : 'dj-bubble-other'"
                    >
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                            <div class="d-flex align-items-center gap-1 min-w-0">
                                <span class="fw-bold text-truncate" style="font-size: 11px;">
                                    {{ isMyMessage(m) ? 'Vous' : (m.user?.name || 'Membre') }}
                                </span>
                                <span v-if="Number(m.user_id) === Number(jam.user_id)" class="badge bg-warning bg-opacity-25 text-warning" style="font-size: 9px;">
                                    Créateur
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <span class="opacity-75" style="font-size: 10px;">{{ formatMsgTime(m.created_at) }}</span>
                                <button
                                    v-if="authUser && (isMyMessage(m) || canRearrangePreview)"
                                    type="button"
                                    class="btn btn-link p-0 text-danger opacity-75"
                                    style="font-size: 10px;"
                                    title="Supprimer"
                                    @click="deleteMsg(m)"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="small" style="white-space: pre-wrap; word-break: break-word;">{{ m.message }}</div>
                    </div>
                </div>
            </div>

            <!-- Emoji Picker Popover -->
            <div v-if="showEmojiPicker && authUser" class="dj-emoji-tray p-2 border-top d-flex flex-wrap gap-1">
                <button
                    v-for="em in EMOJI_GROUPS"
                    :key="em"
                    type="button"
                    class="btn btn-sm dj-emoji-btn"
                    @click="insertEmoji(em)"
                >
                    {{ em }}
                </button>
            </div>

            <!-- Chat Input (Auth Required) -->
            <div class="p-2 border-top border-secondary border-opacity-25">
                <form v-if="authUser" class="d-flex align-items-center gap-1" @submit.prevent="sendMessage">
                    <button
                        type="button"
                        class="btn btn-sm dj-btn-ghost rounded-circle"
                        title="Emojis & Émoticônes"
                        @click="showEmojiPicker = !showEmojiPicker"
                    >
                        <i class="bi bi-emoji-smile text-warning"></i>
                    </button>
                    <input
                        v-model="chatInput"
                        type="text"
                        class="form-control form-control-sm dj-dark-input rounded-pill"
                        placeholder="Écrire un message..."
                        maxlength="1000"
                    />
                    <button
                        type="submit"
                        class="btn btn-sm btn-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;"
                        :disabled="sendingMessage || !chatInput.trim()"
                    >
                        <i class="bi bi-send-fill" style="font-size: 12px;"></i>
                    </button>
                </form>
                <div v-else class="text-center py-1">
                    <Link href="/connexion" class="btn btn-sm btn-outline-info rounded-pill w-100 fw-semibold">
                        <i class="bi bi-lock-fill me-1"></i>Connectez-vous pour commenter
                    </Link>
                </div>
            </div>
        </div>

        <!-- Floating Chat Minimized Launcher -->
        <button
            v-else
            type="button"
            class="dj-chat-launcher btn btn-primary rounded-pill shadow-lg px-3 py-2 fw-semibold d-flex align-items-center gap-2"
            @click="openChat"
        >
            <i class="bi bi-chat-dots-fill"></i>
            <span>Live Chat ({{ filteredMessages.length }})</span>
            <span v-if="unreadCount > 0" class="badge bg-danger rounded-pill">+{{ unreadCount }}</span>
        </button>

        <!-- Floating Social Hearts Layer -->
        <div class="dj-hearts-overlay" aria-hidden="true">
            <div
                v-for="h in floatingHearts"
                :key="h.id"
                class="dj-floating-heart"
                :style="{
                    left: `${h.x}px`,
                    top: `${h.y}px`,
                    color: h.color,
                    '--drift-x': `${h.drift}px`,
                    '--rot': `${h.rot}deg`,
                    '--heart-scale': h.scale,
                    '--duration': `${h.duration}ms`,
                }"
            >
                <i class="bi bi-heart-fill"></i>
            </div>
        </div>

        <!-- Fullscreen Submission Preview Modal -->
        <div v-if="previewSubmission" class="position-fixed top-0 start-0 w-100 h-100 bg-black bg-opacity-75 d-flex align-items-center justify-content-center p-3" style="z-index: 2700;" @click="previewSubmission = null">
            <div class="dj-glass-box rounded-4 shadow-lg w-100 overflow-hidden d-flex flex-column" style="max-width: 980px; height: 82vh;" @click.stop>
                <div class="p-3 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-white">{{ previewSubmission.title }} — par {{ previewSubmission.user?.name }}</h6>
                    <button type="button" class="btn-close btn-close-white" @click="previewSubmission = null"></button>
                </div>
                <iframe :srcdoc="buildSandboxDoc(previewSubmission.html_code, previewSubmission.css_code, previewSubmission.js_code)" sandbox="allow-scripts" class="w-100 border-0 flex-grow-1 bg-white" :title="previewSubmission.title"></iframe>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dj-ide-root {
    background: linear-gradient(150deg, #07080c 0%, #0e111a 48%, #141824 100%);
    color: #e2e8f0;
}
.dj-topbar {
    background: linear-gradient(90deg, #0b0d14 0%, #131722 100%);
    border-color: rgba(255, 255, 255, 0.08) !important;
}
.dj-traffic .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    opacity: 0.85;
}
.dj-activity-bar {
    width: 52px;
    background: linear-gradient(180deg, #0a0c12 0%, #10141f 100%);
    border-color: rgba(255, 255, 255, 0.08) !important;
}
.dj-activity-btn {
    position: relative;
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    color: #64748b;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.18s ease;
}
.dj-activity-btn:hover,
.dj-activity-btn.active {
    color: #f8fafc;
    background: rgba(99, 102, 241, 0.18);
}
.dj-mini-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 9px;
    padding: 1px 5px;
    border-radius: 999px;
    background: #334155;
    color: #fff;
}
.dj-sidebar {
    width: 235px;
    background: linear-gradient(180deg, #0d1018 0%, #121622 100%);
    border-color: rgba(255, 255, 255, 0.08) !important;
}
.dj-file-item {
    width: 100%;
    text-align: left;
    border: none;
    background: transparent;
    color: #94a3b8;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 2px;
    transition: all 0.15s;
}
.dj-file-item:hover {
    background: rgba(255, 255, 255, 0.05);
    color: #f8fafc;
}
.dj-file-item.active {
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.24), rgba(56, 189, 248, 0.08));
    color: #ffffff;
    font-weight: 600;
}
.dj-tabs {
    background: #0b0e15;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
.dj-tab {
    border: none;
    background: transparent;
    color: #94a3b8;
    padding: 9px 16px;
    font-size: 12px;
    border-top: 2px solid transparent;
    transition: all 0.15s;
}
.dj-tab.active {
    background: #111521;
    color: #f8fafc;
    border-top-color: #6366f1;
    font-weight: 600;
}
.dj-code-surface {
    background: linear-gradient(180deg, #0f131d 0%, #0b0e16 100%);
}
.dj-gutter {
    width: 44px;
    font-size: 12px;
    line-height: 1.6;
    color: #475569;
    background: rgba(0, 0, 0, 0.2);
    flex-shrink: 0;
}
.dj-textarea {
    background: transparent;
    color: #e2e8f0;
    border: none;
    outline: none;
    resize: none;
    font-size: 13px;
    line-height: 1.6;
    min-height: 100%;
}
.dj-preview-bar {
    background: linear-gradient(90deg, #0d1018, #161b28);
    border-color: rgba(255, 255, 255, 0.08) !important;
}
.dj-preview-draggable {
    cursor: grab;
}
.dj-preview-draggable:active {
    cursor: grabbing;
}
.dj-split-bottom-pane {
    height: 50% !important;
    min-height: 0;
}
.dj-drop-overlay {
    position: absolute;
    inset: 0;
    z-index: 2100;
    background: rgba(5, 7, 12, 0.65);
    backdrop-filter: blur(4px);
    pointer-events: none;
}
.dj-drop-zone {
    position: absolute;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 2px dashed rgba(56, 189, 248, 0.4);
    background: rgba(15, 23, 42, 0.65);
    color: #cbd5e1;
    border-radius: 14px;
    transition: all 0.15s ease;
}
.dj-drop-right {
    top: 12px;
    right: 12px;
    width: calc(50% - 18px);
    height: calc(54% - 18px);
}
.dj-drop-bottom {
    left: 12px;
    right: 12px;
    bottom: 12px;
    height: calc(46% - 18px);
}
.dj-drop-zone.active {
    border-color: #38bdf8;
    background: rgba(14, 165, 233, 0.24);
    color: #ffffff;
    box-shadow: 0 0 24px rgba(56, 189, 248, 0.35);
}
.dj-drag-ghost {
    position: fixed;
    z-index: 2650;
    pointer-events: none;
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    color: #ffffff;
}
.dj-statusbar {
    background: linear-gradient(90deg, #090b10 0%, #111625 100%);
    border-color: rgba(255, 255, 255, 0.08) !important;
    color: #94a3b8;
    font-size: 11px;
}
.dj-dark-input {
    background-color: rgba(255, 255, 255, 0.06) !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
}
.dj-dark-input::placeholder {
    color: #64748b;
}
.dj-glass-box {
    background: linear-gradient(145deg, rgba(22, 27, 40, 0.85), rgba(11, 14, 22, 0.92));
    border: 1px solid rgba(255, 255, 255, 0.09);
}
.dj-btn-ghost {
    background: rgba(255, 255, 255, 0.06);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.dj-btn-ghost:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #ffffff;
}
.dj-save-btn {
    background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
    color: #ffffff;
    border: none;
}
.dj-toast {
    background: rgba(15, 23, 42, 0.95);
    border: 1px solid rgba(56, 189, 248, 0.35);
    color: #f8fafc;
}
.dj-floating-chat {
    position: fixed;
    width: 350px;
    height: 470px;
    max-width: calc(100vw - 16px);
    max-height: calc(100vh - 24px);
    border-radius: 18px;
    background: linear-gradient(160deg, rgba(16, 20, 30, 0.96) 0%, rgba(8, 10, 16, 0.98) 100%);
    border: 1px solid rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(18px);
    z-index: 2200;
    overflow: hidden;
    transition: width 0.2s ease, height 0.2s ease, box-shadow 0.2s ease;
}
.dj-floating-chat.is-dragging {
    box-shadow: 0 22px 48px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(99, 102, 241, 0.5) !important;
}
.dj-floating-chat.expanded {
    width: 460px;
    height: 600px;
}
.dj-chat-header {
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.2), rgba(6, 182, 212, 0.08));
    border-color: rgba(255, 255, 255, 0.08) !important;
    cursor: grab;
    touch-action: none;
}
.dj-floating-chat.is-dragging .dj-chat-header {
    cursor: grabbing;
}
.dj-live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 8px #22c55e;
}
.dj-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}
.dj-avatar-me {
    background: linear-gradient(135deg, #10b981, #0891b2);
    color: #ffffff;
}
.dj-avatar-other {
    background: linear-gradient(135deg, #334155, #1e293b);
    color: #e2e8f0;
}
.dj-bubble {
    max-width: 78%;
    border-radius: 16px;
}
.dj-bubble-me {
    background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
    color: #ffffff;
    border: 1px solid rgba(52, 211, 153, 0.45);
    border-bottom-right-radius: 4px;
    box-shadow: 0 6px 18px rgba(5, 150, 105, 0.22);
}
.dj-bubble-other {
    background: linear-gradient(145deg, #1b2131 0%, #121622 100%);
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-bottom-left-radius: 4px;
}
.dj-heart-btn {
    background: rgba(244, 63, 94, 0.14);
    border: 1px solid rgba(244, 63, 94, 0.35);
    color: #fda4af;
    transition: transform 0.16s cubic-bezier(0.22, 1, 0.36, 1), background 0.16s ease;
}
.dj-heart-btn:hover {
    background: rgba(244, 63, 94, 0.25);
    color: #ffffff;
}
.dj-heart-btn.is-pulsing {
    transform: scale(1.16);
}
.dj-hearts-overlay {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 2550;
    overflow: hidden;
}
.dj-floating-heart {
    position: fixed;
    font-size: 22px;
    transform: translate(-50%, -50%);
    filter: drop-shadow(0 4px 10px rgba(244, 63, 94, 0.5));
    animation: floatHeartUp var(--duration, 1600ms) cubic-bezier(0.18, 0.89, 0.32, 1) forwards;
    will-change: transform, opacity;
}
@keyframes floatHeartUp {
    0% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.4) rotate(0deg);
    }
    15% {
        opacity: 1;
        transform: translate(calc(-50% + var(--drift-x, 0px) * 0.25), -85px) scale(var(--heart-scale, 1.1)) rotate(calc(var(--rot, 0deg) * 0.4));
    }
    70% {
        opacity: 0.92;
    }
    100% {
        opacity: 0;
        transform: translate(calc(-50% + var(--drift-x, 0px)), -230px) scale(calc(var(--heart-scale, 1) * 0.75)) rotate(var(--rot, 0deg));
    }
}
.dj-emoji-tray {
    background: rgba(10, 12, 18, 0.96);
    border-color: rgba(255, 255, 255, 0.08) !important;
}
.dj-emoji-btn {
    padding: 2px 6px;
    font-size: 16px;
    background: transparent;
    border: none;
    border-radius: 8px;
}
.dj-emoji-btn:hover {
    background: rgba(255, 255, 255, 0.12);
    transform: scale(1.15);
}
.dj-chat-launcher {
    position: fixed;
    right: 22px;
    bottom: 36px;
    z-index: 2200;
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    border: none;
}
@keyframes msgPopIn {
    0% {
        opacity: 0;
        transform: translateY(10px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.dj-msg-anim {
    animation: msgPopIn 0.25s cubic-bezier(0.22, 1, 0.36, 1);
}
.dj-msg-just-sent .dj-bubble {
    box-shadow: 0 0 18px rgba(99, 102, 241, 0.45);
}
@media (min-width: 992px) {
    .w-lg-50 {
        width: 50% !important;
    }
}
</style>
