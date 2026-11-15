<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';

const props = defineProps({
    jam: { type: Object, required: true },
    mySubmission: { type: Object, default: null },
    votedSubmissionIds: { type: Array, default: () => [] },
    initialMessages: { type: Array, default: () => [] },
    isJamCreator: { type: Boolean, default: false },
    initialOnlineCount: { type: Number, default: 1 },
    initialOnlineUsers: { type: Array, default: () => [] },
    initialPolls: { type: Array, default: () => [] },
    initialCollaborators: { type: Array, default: () => [] },
    initialLiveCode: { type: Object, default: null },
    initialPinnedMessage: { type: Object, default: null },
    initialHeartsCount: { type: Number, default: 0 },
    initialRaisedHands: { type: Array, default: () => [] },
    initialMessageReactions: { type: Object, default: () => ({}) },
    initialSprint: { type: Object, default: null },
    initialCreatorActiveTab: { type: String, default: 'html' },
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
const chatInputEl = ref(null);
const sendingMessage = ref(false);
const activeTargetSubmissionId = ref(null);
const chatListEl = ref(null);
const chatWindowEl = ref(null);
const lastSentId = ref(null);
const unreadCount = ref(0);

// Per-Jam Presence, Typing & @Mentions
const onlineCount = ref(props.initialOnlineCount || 1);
const onlineUsers = ref([...(props.initialOnlineUsers || [])]);
const typingUsers = ref([]);
let typingStopTimer = null;
let lastTypingSentAt = 0;

// Polls State (1 vote per person, creator-only creation)
const polls = ref([...(props.initialPolls || [])]);
const pollQuestion = ref('');
const pollOptions = ref(['', '']);
const creatingPoll = ref(false);
const votingPollId = ref(null);
const openPollsCount = computed(() => polls.value.filter((p) => !p.is_closed).length);
const activeOpenPoll = computed(() => polls.value.find((p) => !p.is_closed) || null);
const hasUnvotedOpenPoll = computed(() => Boolean(activeOpenPoll.value && !activeOpenPoll.value.user_voted_option_id));

// Pinned Chat Announcement (Community Tool)
const pinnedMessage = ref(props.initialPinnedMessage || null);

// Live Co-Editing / Collaboration State
const collaborators = ref([...(props.initialCollaborators || [])]);
const pendingInvite = ref(null);
const bottomLeftPopup = ref(null); // { kind: 'invite' | 'message', title, text, invite?, senderName? }
let bottomPopupTimer = null;
let liveCodeSyncTimer = null;
let isApplyingRemoteCode = false;
let lastAppliedCodeAt = props.initialLiveCode?.updated_at || 0;
const isCollaborator = computed(() =>
    Boolean(authUser.value && collaborators.value.some((c) => Number(c.id) === Number(authUser.value.id)))
);
const canCoEditSharedCode = computed(() => canRearrangePreview.value || isCollaborator.value);

// Social Share Menu State
const showShareMenu = ref(false);
const showCollabModal = ref(false);

const chatPosStorageKey = `dj_chat_pos_${props.jam.slug}`;
const chatPos = ref({ x: null, y: null });
const isDraggingChat = ref(false);
let chatDragOffset = { x: 0, y: 0 };

// Sound Effects (iPhone-style send swoosh - always active)
let sharedAudioCtx = null;

// Network Connectivity State (Blocking Offline Popup)
const isOnline = ref(typeof navigator !== 'undefined' ? navigator.onLine : true);

// Floating Social Hearts State (Synchronized across all accounts)
const heartCount = ref(props.initialHeartsCount || 0);
const heartPulse = ref(false);
const floatingHearts = ref([]);
let heartSeq = 0;
let pendingHearts = 0;
let heartFlushTimer = null;
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

const previewIframeEl = ref(null);
let jsReloadTimer = null;

// Responsive Preview & Integrated JS Console
const previewDevice = ref('desktop'); // 'desktop' | 'tablet' | 'mobile'
const showConsole = ref(false);
const consoleLogs = ref([]);
const consoleErrorCount = computed(() => consoleLogs.value.filter((l) => l.level === 'error').length);

function clearConsoleLogs() {
    consoleLogs.value = [];
}

function handleSandboxMessage(e) {
    if (!e?.data || e.data.type !== 'dj-console') return;
    const level = e.data.level || 'log';
    const text = String(e.data.text || '');
    consoleLogs.value.push({
        id: `${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
        level,
        text,
        time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
    });
    if (consoleLogs.value.length > 80) {
        consoleLogs.value = consoleLogs.value.slice(-80);
    }
}

function buildSandboxDoc(html, css, js, withLivePatch = false) {
    const safeCss = String(css || '').replace(/<\/style>/gi, '<\\/style>');
    const safeJs = String(js || '').replace(/<\/script>/gi, '<\\/script>');
    const consoleBridge = withLivePatch
        ? `<script>(function(){var send=function(lvl,args){try{var txt=[].slice.call(args).map(function(a){if(typeof a==='object'){try{return JSON.stringify(a);}catch(e){return String(a);}}return String(a);}).join(' ');parent.postMessage({type:'dj-console',level:lvl,text:txt},'*');}catch(e){}};['log','warn','error'].forEach(function(m){var orig=console[m];console[m]=function(){send(m,arguments);if(orig)orig.apply(console,arguments);};});window.addEventListener('error',function(ev){send('error',[ev.message+' (ligne '+ev.lineno+')']);});window.addEventListener('unhandledrejection',function(ev){send('error',['Promise rejetée: '+(ev.reason&&ev.reason.message?ev.reason.message:ev.reason)]);});})();<\/script>`
        : '';
    const patchBridge = withLivePatch
        ? `<script>window.addEventListener('message',function(e){if(!e.data||e.data.type!=='dj-patch')return;var st=document.getElementById('dj-live-style');if(st)st.textContent=e.data.css||'';var root=document.getElementById('dj-live-root');if(root)root.innerHTML=e.data.html||'';});<\/script>`
        : '';
    return `<!doctype html><html><head><meta charset="utf-8"><style id="dj-live-style">${safeCss}</style></head><body><div id="dj-live-root">${html || ''}</div>${consoleBridge}${patchBridge}<script>${safeJs}<\/script></body></html>`;
}

// Starter Templates, ZIP Export & Portfolio Publish
const showTemplateMenu = ref(false);
const starterTemplates = {
    tailwind: {
        label: 'Starter Tailwind CDN',
        html: `<script src="https://cdn.tailwindcss.com"><\/script>\n<div class="min-h-screen bg-slate-950 text-white flex items-center justify-center p-6">\n  <div class="max-w-md w-full rounded-2xl border border-indigo-500/30 bg-slate-900/80 p-8 shadow-2xl">\n    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 mb-4">Tech237 Starter</span>\n    <h1 class="text-3xl font-extrabold mb-2">Interface Moderne</h1>\n    <p class="text-slate-400 text-sm mb-6">Prêt avec Tailwind CSS CDN pour prototyper rapidement.</p>\n    <button id="tw-btn" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-cyan-500 font-bold hover:opacity-90 transition">Tester l'action</button>\n  </div>\n</div>`,
        css: `body { margin: 0; }`,
        js: `document.getElementById('tw-btn')?.addEventListener('click', () => {\n  console.log('Bouton Tailwind cliqué !');\n  alert('Tailwind prêt sur Tech237 !');\n});`,
    },
    canvas: {
        label: 'Starter Canvas 2D / Jeu',
        html: `<div class="game-wrap">\n  <canvas id="stage" width="460" height="300"></canvas>\n  <p class="hint">Déplacez la souris sur le canvas</p>\n</div>`,
        css: `body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #07090f; color: #cbd5e1; font-family: system-ui; }\n.game-wrap { text-align: center; }\ncanvas { border-radius: 16px; border: 1px solid rgba(99,102,241,0.4); background: #0f172a; box-shadow: 0 20px 50px rgba(0,0,0,0.6); }\n.hint { font-size: 12px; margin-top: 10px; opacity: 0.75; }`,
        js: `const canvas = document.getElementById('stage');\nconst ctx = canvas.getContext('2d');\nlet mx = 230, my = 150;\ncanvas.addEventListener('mousemove', (e) => {\n  const r = canvas.getBoundingClientRect();\n  mx = e.clientX - r.left; my = e.clientY - r.top;\n});\nfunction loop() {\n  ctx.fillStyle = 'rgba(15, 23, 42, 0.25)';\n  ctx.fillRect(0, 0, canvas.width, canvas.height);\n  ctx.beginPath();\n  ctx.arc(mx, my, 18, 0, Math.PI * 2);\n  ctx.fillStyle = '#38bdf8';\n  ctx.fill();\n  requestAnimationFrame(loop);\n}\nloop();`,
    },
    dashboard: {
        label: 'Starter Dashboard Dark',
        html: `<main class="dash">\n  <header><h2>Analytics Tech237</h2><span class="pill">Live</span></header>\n  <section class="cards">\n    <article class="card"><small>Visiteurs</small><strong>12 480</strong></article>\n    <article class="card"><small>Conversion</small><strong>4.8%</strong></article>\n    <article class="card"><small>Revenus</small><strong>2.4M FCFA</strong></article>\n  </section>\n</main>`,
        css: `body { margin: 0; background: #090b10; color: #f1f5f9; font-family: 'Segoe UI', sans-serif; padding: 24px; }\n.dash header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }\n.pill { background: rgba(16,185,129,0.2); color: #34d399; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }\n.cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; }\n.card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 6px; }\n.card small { color: #94a3b8; }\n.card strong { font-size: 1.35rem; color: #818cf8; }`,
        js: `console.log('Dashboard initialisé');`,
    },
};

function applyStarterTemplate(key) {
    if (!canCoEditSharedCode.value) return;
    const tpl = starterTemplates[key];
    if (!tpl) return;
    showTemplateMenu.value = false;
    if (!window.confirm(`Remplacer le code actuel par "${tpl.label}" ?`)) return;
    htmlCode.value = tpl.html;
    cssCode.value = tpl.css;
    jsCode.value = tpl.js;
    refreshPreviewNow();
    showToast(`Template "${tpl.label}" chargé !`);
}

function buildZipBlob(files) {
    const encoder = new TextEncoder();
    const crcTable = new Uint32Array(256);
    for (let i = 0; i < 256; i++) {
        let c = i;
        for (let k = 0; k < 8; k++) c = (c & 1) ? (0xedb88320 ^ (c >>> 1)) : (c >>> 1);
        crcTable[i] = c >>> 0;
    }
    const crc32 = (bytes) => {
        let c = 0xffffffff;
        for (let i = 0; i < bytes.length; i++) c = crcTable[(c ^ bytes[i]) & 0xff] ^ (c >>> 8);
        return (c ^ 0xffffffff) >>> 0;
    };
    const localChunks = [];
    const centralChunks = [];
    let offset = 0;
    for (const file of files) {
        const nameBytes = encoder.encode(file.name);
        const dataBytes = encoder.encode(file.content);
        const crc = crc32(dataBytes);
        const lh = new Uint8Array(30 + nameBytes.length);
        const lv = new DataView(lh.buffer);
        lv.setUint32(0, 0x04034b50, true);
        lv.setUint16(4, 20, true);
        lv.setUint32(14, crc, true);
        lv.setUint32(18, dataBytes.length, true);
        lv.setUint32(22, dataBytes.length, true);
        lv.setUint16(26, nameBytes.length, true);
        lh.set(nameBytes, 30);
        localChunks.push(lh, dataBytes);

        const ch = new Uint8Array(46 + nameBytes.length);
        const cv = new DataView(ch.buffer);
        cv.setUint32(0, 0x02014b50, true);
        cv.setUint16(4, 20, true);
        cv.setUint16(6, 20, true);
        cv.setUint32(16, crc, true);
        cv.setUint32(20, dataBytes.length, true);
        cv.setUint32(24, dataBytes.length, true);
        cv.setUint16(28, nameBytes.length, true);
        cv.setUint32(42, offset, true);
        ch.set(nameBytes, 46);
        centralChunks.push(ch);
        offset += lh.length + dataBytes.length;
    }
    const centralSize = centralChunks.reduce((acc, b) => acc + b.length, 0);
    const eocd = new Uint8Array(22);
    const ev = new DataView(eocd.buffer);
    ev.setUint32(0, 0x06054b50, true);
    ev.setUint16(8, files.length, true);
    ev.setUint16(10, files.length, true);
    ev.setUint32(12, centralSize, true);
    ev.setUint32(16, offset, true);
    return new Blob([...localChunks, ...centralChunks, eocd], { type: 'application/zip' });
}

function exportProjectZip() {
    const fullIndexHtml = `<!doctype html>\n<html lang="fr">\n<head>\n  <meta charset="utf-8">\n  <meta name="viewport" content="width=device-width, initial-scale=1">\n  <title>${subTitle.value || props.jam.title}</title>\n  <link rel="stylesheet" href="style.css">\n</head>\n<body>\n${htmlCode.value || ''}\n  <script src="app.js"><\/script>\n</body>\n</html>`;
    const blob = buildZipBlob([
        { name: 'index.html', content: fullIndexHtml },
        { name: 'style.css', content: cssCode.value || '' },
        { name: 'app.js', content: jsCode.value || '' },
    ]);
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${props.jam.slug || 'dev-jam'}.zip`;
    a.click();
    setTimeout(() => URL.revokeObjectURL(url), 2000);
    showToast('Projet exporté en ZIP (index.html, style.css, app.js) !');
}

function publishToPortfolio() {
    const fullSnippet = `<!-- ${props.jam.title} -->\n<style>\n${cssCode.value || ''}\n</style>\n${htmlCode.value || ''}\n<script>\n${jsCode.value || ''}\n<\/script>`;
    try {
        localStorage.setItem('dj_portfolio_handoff', JSON.stringify({
            title: subTitle.value || props.jam.title,
            description: subDescription.value || props.jam.description || '',
            html_code: htmlCode.value || '',
            css_code: cssCode.value || '',
            js_code: jsCode.value || '',
            jam_slug: props.jam.slug,
        }));
    } catch {
        // ignore storage errors
    }
    navigator.clipboard?.writeText(fullSnippet).catch(() => {});
    showToast('Code copié ! Redirection vers votre Portfolio Builder Tech237...');
    window.open('/espace-utilisateur/portfolio-builder/sites', '_blank', 'noopener');
}

const liveSrcDoc = ref(buildSandboxDoc(htmlCode.value, cssCode.value, jsCode.value, true));

function refreshPreviewNow() {
    liveSrcDoc.value = buildSandboxDoc(htmlCode.value, cssCode.value, jsCode.value, true);
    previewKey.value++;
}

watch([htmlCode, cssCode], () => {
    const win = previewIframeEl.value?.contentWindow;
    if (win) {
        win.postMessage({ type: 'dj-patch', html: htmlCode.value || '', css: cssCode.value || '' }, '*');
    } else {
        liveSrcDoc.value = buildSandboxDoc(htmlCode.value, cssCode.value, jsCode.value, true);
    }
});

watch(jsCode, () => {
    clearTimeout(jsReloadTimer);
    jsReloadTimer = setTimeout(() => {
        liveSrcDoc.value = buildSandboxDoc(htmlCode.value, cssCode.value, jsCode.value, true);
    }, 450);
});

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

async function playIphoneSendSound() {
    const ctx = getAudioContext();
    if (!ctx) return;
    if (ctx.state === 'suspended') {
        try {
            await ctx.resume();
        } catch {
            return;
        }
    }

    const now = ctx.currentTime;

    // Layer 1: iMessage-style upward pitch swoosh
    const osc1 = ctx.createOscillator();
    const gain1 = ctx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(520, now);
    osc1.frequency.exponentialRampToValueAtTime(1240, now + 0.085);
    gain1.gain.setValueAtTime(0.001, now);
    gain1.gain.linearRampToValueAtTime(0.22, now + 0.015);
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.13);
    osc1.connect(gain1);
    gain1.connect(ctx.destination);
    osc1.start(now);
    osc1.stop(now + 0.14);

    // Layer 2: Crisp upper harmonic pop
    const osc2 = ctx.createOscillator();
    const gain2 = ctx.createGain();
    osc2.type = 'triangle';
    osc2.frequency.setValueAtTime(980, now + 0.025);
    osc2.frequency.exponentialRampToValueAtTime(1680, now + 0.095);
    gain2.gain.setValueAtTime(0.001, now + 0.025);
    gain2.gain.linearRampToValueAtTime(0.11, now + 0.04);
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.12);
    osc2.connect(gain2);
    gain2.connect(ctx.destination);
    osc2.start(now + 0.025);
    osc2.stop(now + 0.13);
}

function playHeartPopSound() {
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
    gain.gain.linearRampToValueAtTime(0.08, now + 0.01);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.08);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start(now);
    osc.stop(now + 0.09);
}

async function playMentionChimeSound() {
    const ctx = getAudioContext();
    if (!ctx) return;
    if (ctx.state === 'suspended') {
        try {
            await ctx.resume();
        } catch {
            return;
        }
    }
    const now = ctx.currentTime;
    const notes = [880, 1174.66];
    notes.forEach((freq, idx) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        const t = now + idx * 0.09;
        osc.frequency.setValueAtTime(freq, t);
        gain.gain.setValueAtTime(0.001, t);
        gain.gain.linearRampToValueAtTime(0.11, t + 0.015);
        gain.gain.exponentialRampToValueAtTime(0.001, t + 0.16);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(t);
        osc.stop(t + 0.17);
    });
}

// Raise Hand Queue
const raisedHands = ref([...(props.initialRaisedHands || [])]);
const isMyHandRaised = computed(() => {
    if (!authUser.value) return false;
    return raisedHands.value.some((h) => Number(h.id) === Number(authUser.value.id));
});

async function toggleHandRaise(targetUserId = null, forceLower = false) {
    if (!authUser.value) {
        showToast('Connectez-vous pour lever la main.');
        return;
    }
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/hand-raise`, {
            user_id: targetUserId || authUser.value.id,
            lower: forceLower,
        });
        raisedHands.value = res.data.raised_hands || [];
        if (!targetUserId) {
            showToast(res.data.raised ? '✋ Main levée ! Le créateur est notifié.' : 'Main baissée.');
        }
    } catch {
        showToast('Impossible de mettre à jour la main levée.');
    }
}

async function inviteFromHand(handUser) {
    await inviteUserToCode(handUser.id);
    await toggleHandRaise(handUser.id, true);
}

// Message Reactions
const reactionEmojis = ['👍', '🔥', '🚀', '💡'];
const messageReactions = ref({ ...(props.initialMessageReactions || {}) });

function getMsgReactionCount(msgId, emoji) {
    return (messageReactions.value?.[msgId]?.[emoji] || []).length;
}

function hasUserReacted(msgId, emoji) {
    if (!authUser.value) return false;
    const list = messageReactions.value?.[msgId]?.[emoji] || [];
    return list.some((id) => Number(id) === Number(authUser.value.id));
}

async function toggleMessageReaction(msgId, emoji) {
    if (!authUser.value) {
        showToast('Connectez-vous pour réagir aux messages.');
        return;
    }
    if (msgId <= 0) return;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/messages/${msgId}/react`, { emoji });
        messageReactions.value = res.data.message_reactions || {};
    } catch {
        showToast('Impossible de réagir.');
    }
}

// Quote Reply & @Mention Detection
const replyingTo = ref(null);
function startReply(msg) {
    replyingTo.value = {
        id: msg.id,
        author: msg.user?.name || 'Participant',
        excerpt: String(msg.message || '').slice(0, 90),
    };
    openChat();
    nextTick(() => chatInputEl.value?.focus());
}
function cancelReply() {
    replyingTo.value = null;
}

function isMessageMentioningMe(m) {
    if (!authUser.value || !m?.message) return false;
    if (Number(m.user_id) === Number(authUser.value.id)) return false;
    const tag = String(authUser.value.username || authUser.value.name || '').replace(/\s+/g, '').toLowerCase();
    const first = String(authUser.value.name || '').split(/\s+/)[0].toLowerCase();
    const lower = String(m.message).toLowerCase();
    return (tag && lower.includes(`@${tag}`)) || (first && lower.includes(`@${first}`));
}

// Synchronized Sprint / Pomodoro Timer
const sprint = ref(props.initialSprint || null);
const sprintTimeLeftText = ref('');
const sprintEnded = ref(false);
const showSprintPopover = ref(false);
const sprintLabel = ref('Sprint Code');

function updateSprintDisplay() {
    if (!sprint.value || !sprint.value.ends_at) {
        sprintTimeLeftText.value = '';
        sprintEnded.value = false;
        return;
    }
    const diff = Number(sprint.value.ends_at) - Date.now();
    if (diff <= 0) {
        sprintTimeLeftText.value = '00:00';
        sprintEnded.value = true;
        return;
    }
    sprintEnded.value = false;
    const totalSec = Math.ceil(diff / 1000);
    const min = String(Math.floor(totalSec / 60)).padStart(2, '0');
    const sec = String(totalSec % 60).padStart(2, '0');
    sprintTimeLeftText.value = `${min}:${sec}`;
}

async function startSprint(durationMin) {
    if (!canRearrangePreview.value) return;
    showSprintPopover.value = false;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/sprint`, {
            duration_min: durationMin,
            label: sprintLabel.value || `Sprint ${durationMin} min`,
        });
        sprint.value = res.data.sprint;
        updateSprintDisplay();
        showToast(`⏱️ Sprint "${sprint.value?.label}" lancé (${durationMin} min) !`);
    } catch {
        showToast('Impossible de démarrer le sprint.');
    }
}

async function stopSprint() {
    if (!canRearrangePreview.value) return;
    showSprintPopover.value = false;
    try {
        await apiClient.post(`/dev-jams/${props.jam.slug}/sprint`, { stop: true });
        sprint.value = null;
        updateSprintDisplay();
        showToast('Sprint arrêté.');
    } catch {
        showToast('Impossible d’arrêter le sprint.');
    }
}

// Watch Party (Suivre le créateur)
const creatorActiveTab = ref(props.initialCreatorActiveTab || 'html');
const followCreator = ref(false);

watch(codeTab, (newTab) => {
    if (canRearrangePreview.value && isOnline.value) {
        apiClient.post(`/dev-jams/${props.jam.slug}/creator-tab`, { tab: newTab }).catch(() => {});
    }
});

// Live Podium & Participant Badges
const podiumSubmissions = computed(() =>
    [...submissions.value]
        .sort((a, b) => (b.votes_count || 0) - (a.votes_count || 0))
        .slice(0, 3)
);

function getParticipantBadges(user) {
    if (!user?.id) return [];
    const badges = [];
    const uid = Number(user.id);
    if (Number(props.jam.creator_user_id) === uid) badges.push({ label: 'Créateur', icon: '👑', cls: 'dj-badge-gold' });
    if (collaborators.value.some((c) => Number(c.id) === uid)) badges.push({ label: 'Co-éditeur', icon: '🤝', cls: 'dj-badge-cyan' });
    const msgCount = messages.value.filter((m) => Number(m.user_id) === uid).length;
    if (msgCount >= 5) badges.push({ label: 'Actif', icon: '💬', cls: 'dj-badge-violet' });
    return badges;
}

// Floating Hearts (Multi-click Social Animation + Server Sync)
function flushHeartsToServer() {
    if (pendingHearts <= 0 || !isOnline.value) return;
    const countToSend = Math.min(20, pendingHearts);
    pendingHearts -= countToSend;
    apiClient
        .post(`/dev-jams/${props.jam.slug}/heart`, { count: countToSend })
        .then((res) => {
            if (typeof res.data?.hearts_count === 'number') {
                heartCount.value = Math.max(heartCount.value, res.data.hearts_count);
            }
            if (pendingHearts > 0) flushHeartsToServer();
        })
        .catch(() => {
            // keep local optimistic count if transient error
        });
}

function spawnHeart(e) {
    if (!isOnline.value) return;
    heartCount.value += 1;
    pendingHearts += 1;
    heartPulse.value = true;
    setTimeout(() => {
        heartPulse.value = false;
    }, 180);

    clearTimeout(heartFlushTimer);
    heartFlushTimer = setTimeout(flushHeartsToServer, 240);

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
    if (!canCoEditSharedCode.value) return;
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

const mentionCandidates = computed(() => {
    const map = new Map();
    const add = (u) => {
        if (!u || !u.id || !u.name) return;
        if (authUser.value && Number(u.id) === Number(authUser.value.id)) return;
        if (!map.has(Number(u.id))) {
            map.set(Number(u.id), {
                id: Number(u.id),
                name: u.name,
                tag: u.username || u.name.replace(/\s+/g, ''),
            });
        }
    };
    onlineUsers.value.forEach(add);
    messages.value.forEach((m) => add(m.user));
    submissions.value.forEach((s) => add(s.user));
    if (props.jam.creator_user) add(props.jam.creator_user);
    return Array.from(map.values());
});

const mentionQuery = computed(() => {
    const match = chatInput.value.match(/(?:^|\s)@([^\s@]{0,30})$/);
    return match ? match[1].toLowerCase() : null;
});

const filteredMentions = computed(() => {
    if (mentionQuery.value === null) return [];
    const q = mentionQuery.value;
    return mentionCandidates.value
        .filter((u) => !q || u.name.toLowerCase().includes(q) || u.tag.toLowerCase().includes(q))
        .slice(0, 6);
});

const typingBannerText = computed(() => {
    const list = typingUsers.value;
    if (!list.length) return '';
    if (list.length === 1) return `${list[0].name} est en train d'écrire`;
    if (list.length === 2) return `${list[0].name} et ${list[1].name} sont en train d'écrire`;
    return `${list[0].name}, ${list[1].name} et ${list.length - 2} autre(s) écrivent`;
});

function insertMention(candidate) {
    chatInput.value = chatInput.value.replace(/(^|\s)@[^\s@]*$/, `$1@${candidate.tag} `);
    nextTick(() => chatInputEl.value?.focus());
}

function replyToMessage(msg) {
    if (!authUser.value) {
        showToast('Connectez-vous pour répondre dans le chat.');
        return;
    }
    const targetName = msg.user?.username || (msg.user?.name ? msg.user.name.replace(/\s+/g, '') : 'Membre');
    const prefix = `@${targetName} `;
    if (!chatInput.value.startsWith(prefix)) {
        chatInput.value = prefix + chatInput.value;
    }
    openChat();
    nextTick(() => chatInputEl.value?.focus());
}

function parseChatTokens(text) {
    const str = String(text || '');
    const regex = /(https?:\/\/[^\s<]+|@[A-Za-z0-9_.\-À-ÿ]+)/g;
    const parts = [];
    let lastIndex = 0;
    let match;
    while ((match = regex.exec(str)) !== null) {
        if (match.index > lastIndex) {
            parts.push({ text: str.slice(lastIndex, match.index), kind: 'text' });
        }
        const token = match[0];
        parts.push({
            text: token,
            kind: token.startsWith('http://') || token.startsWith('https://') ? 'url' : 'mention',
        });
        lastIndex = regex.lastIndex;
    }
    if (lastIndex < str.length) {
        parts.push({ text: str.slice(lastIndex), kind: 'text' });
    }
    return parts.length ? parts : [{ text: str, kind: 'text' }];
}

function showBottomPopup(payload, autoHideMs = 7000) {
    clearTimeout(bottomPopupTimer);
    bottomLeftPopup.value = payload;
    if (autoHideMs > 0) {
        bottomPopupTimer = setTimeout(() => {
            if (bottomLeftPopup.value?.kind === payload.kind) {
                bottomLeftPopup.value = null;
            }
        }, autoHideMs);
    }
}

// Polls Actions
function addPollOption() {
    if (pollOptions.value.length < 6) {
        pollOptions.value.push('');
    }
}

function removePollOption(index) {
    if (pollOptions.value.length > 2) {
        pollOptions.value.splice(index, 1);
    }
}

async function createPoll() {
    if (!canRearrangePreview.value) return;
    const cleanOptions = pollOptions.value.map((o) => o.trim()).filter(Boolean);
    if (!pollQuestion.value.trim() || cleanOptions.length < 2) {
        showToast('Ajoutez une question et au moins 2 choix de réponse.');
        return;
    }
    creatingPoll.value = true;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/polls`, {
            question: pollQuestion.value.trim(),
            options: cleanOptions,
        });
        polls.value = res.data.polls || [];
        pollQuestion.value = '';
        pollOptions.value = ['', ''];
        showToast(res.data.message || 'Sondage créé !');
    } catch (e) {
        showToast(e.response?.data?.message || 'Impossible de créer le sondage.');
    } finally {
        creatingPoll.value = false;
    }
}

async function votePoll(poll, optionId) {
    if (!authUser.value) {
        showToast('Connectez-vous pour voter au sondage.');
        return;
    }
    if (poll.user_voted_option_id || poll.is_closed) return;
    votingPollId.value = poll.id;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/polls/${poll.id}/vote`, {
            option_id: optionId,
        });
        polls.value = res.data.polls || [];
        showToast(res.data.message || 'Vote enregistré !');
    } catch (e) {
        showToast(e.response?.data?.message || 'Vote refusé.');
    } finally {
        votingPollId.value = null;
    }
}

async function toggleClosePoll(poll) {
    if (!canRearrangePreview.value) return;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/polls/${poll.id}/toggle-close`);
        polls.value = res.data.polls || [];
    } catch {
        showToast('Action impossible.');
    }
}

// Collaboration / Help Invitations
async function inviteToCode(targetUserId) {
    if (!canRearrangePreview.value || !targetUserId) return;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/collaborations/invite`, {
            target_user_id: targetUserId,
        });
        showToast(res.data.message || 'Invitation envoyée !');
    } catch (e) {
        showToast(e.response?.data?.message || 'Invitation impossible.');
    }
}

async function respondInvite(accept) {
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/collaborations/respond`, { accept });
        pendingInvite.value = null;
        bottomLeftPopup.value = null;
        if (Array.isArray(res.data.collaborators)) collaborators.value = res.data.collaborators;
        if (accept && res.data.live_code) {
            applyRemoteLiveCode(res.data.live_code);
        }
        showToast(res.data.message);
    } catch (e) {
        pendingInvite.value = null;
        bottomLeftPopup.value = null;
        showToast(e.response?.data?.message || 'Invitation expirée.');
    }
}

async function revokeCollaborator(userId = null) {
    if (!canRearrangePreview.value) return;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/collaborations/revoke`, {
            user_id: userId,
        });
        collaborators.value = res.data.collaborators || [];
        showToast(res.data.message || 'Accès co-éditeur révoqué.');
    } catch {
        showToast('Impossible de révoquer.');
    }
}

function applyRemoteLiveCode(liveCode) {
    if (!liveCode || !liveCode.updated_at) return;
    if (authUser.value && Number(liveCode.updated_by) === Number(authUser.value.id)) return;
    if (liveCode.updated_at <= lastAppliedCodeAt) return;
    lastAppliedCodeAt = liveCode.updated_at;
    isApplyingRemoteCode = true;
    htmlCode.value = liveCode.html_code ?? htmlCode.value;
    cssCode.value = liveCode.css_code ?? cssCode.value;
    jsCode.value = liveCode.js_code ?? jsCode.value;
    nextTick(() => {
        isApplyingRemoteCode = false;
    });
}

watch([htmlCode, cssCode, jsCode], () => {
    if (isApplyingRemoteCode || !canCoEditSharedCode.value) return;
    if (!collaborators.value.length && !isCollaborator.value) return;
    clearTimeout(liveCodeSyncTimer);
    liveCodeSyncTimer = setTimeout(async () => {
        try {
            const res = await apiClient.post(`/dev-jams/${props.jam.slug}/live-code`, {
                html_code: htmlCode.value,
                css_code: cssCode.value,
                js_code: jsCode.value,
            });
            if (res.data?.live_code?.updated_at) {
                lastAppliedCodeAt = res.data.live_code.updated_at;
            }
        } catch {
            // silent sync failure
        }
    }, 550);
});

// Social Share Actions
function getJamShareUrl() {
    if (typeof window === 'undefined') return '';
    return window.location.href;
}

function shareSocial(platform) {
    const url = getJamShareUrl();
    const text = `Rejoins la Dev Jam "${props.jam.title}" sur Tech237 !`;
    const encUrl = encodeURIComponent(url);
    const encText = encodeURIComponent(text);
    showShareMenu.value = false;

    if (platform === 'facebook') {
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encUrl}`, '_blank', 'noopener');
    } else if (platform === 'twitter') {
        window.open(`https://twitter.com/intent/tweet?url=${encUrl}&text=${encText}`, '_blank', 'noopener');
    } else if (platform === 'whatsapp') {
        window.open(`https://wa.me/?text=${encodeURIComponent(`${text} ${url}`)}`, '_blank', 'noopener');
    } else if (platform === 'tiktok') {
        navigator.clipboard?.writeText(`${text} ${url}`).catch(() => {});
        showToast('Lien copié ! Collez-le sur TikTok.');
        window.open('https://www.tiktok.com/', '_blank', 'noopener');
    } else if (platform === 'copy') {
        navigator.clipboard?.writeText(url).catch(() => {});
        showToast('Lien de la Jam copié !');
    }
}

async function shareJamLinkInChat() {
    if (!canRearrangePreview.value) return;
    showShareMenu.value = false;
    const url = getJamShareUrl();
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/messages`, {
            message: `🔗 Rejoignez & partagez cette Dev Jam : ${url}`,
            submission_id: null,
        });
        const newMsg = res.data.message;
        if (!messages.value.some((m) => m.id === newMsg.id)) {
            lastSentId.value = newMsg.id;
            messages.value.push(newMsg);
        }
        playIphoneSendSound();
        openChat();
        showToast('Lien de la Jam partagé dans le chat !');
    } catch {
        showToast('Impossible de partager le lien dans le chat.');
    }
}

async function notifyTyping(isTyping) {
    if (!authUser.value) return;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/typing`, { is_typing: isTyping });
        if (typeof res.data.online_count === 'number') onlineCount.value = res.data.online_count;
        if (Array.isArray(res.data.online_users)) onlineUsers.value = res.data.online_users;
        if (Array.isArray(res.data.typing_users)) typingUsers.value = res.data.typing_users;
    } catch {
        // silent typing update
    }
}

function autoResizeChatInput() {
    const el = chatInputEl.value;
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = `${Math.min(el.scrollHeight, 110)}px`;
}

function handleChatKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

function handleChatInput() {
    getAudioContext();
    autoResizeChatInput();
    if (!authUser.value || !isOnline.value) return;
    if (!chatInput.value.trim()) {
        clearTimeout(typingStopTimer);
        notifyTyping(false);
        return;
    }
    const now = Date.now();
    if (now - lastTypingSentAt > 2200) {
        lastTypingSentAt = now;
        notifyTyping(true);
    }
    clearTimeout(typingStopTimer);
    typingStopTimer = setTimeout(() => {
        lastTypingSentAt = 0;
        notifyTyping(false);
    }, 3000);
}

let optimisticMsgSeq = 0;
function sendMessage() {
    if (!isOnline.value) return;
    const text = chatInput.value.trim();
    if (!authUser.value || !text) return;

    // Unlock Web Audio synchronously inside the user submit gesture
    const ctx = getAudioContext();
    if (ctx && ctx.state === 'suspended') {
        ctx.resume().catch(() => {});
    }

    clearTimeout(typingStopTimer);
    lastTypingSentAt = 0;
    showEmojiPicker.value = false;

    const replyContext = replyingTo.value ? { ...replyingTo.value } : null;
    const tempId = -++optimisticMsgSeq;
    const optimisticMsg = {
        id: tempId,
        user_id: authUser.value.id,
        submission_id: activeTargetSubmissionId.value,
        message: text,
        reply_preview: replyContext,
        created_at: new Date().toISOString(),
        user: {
            id: authUser.value.id,
            name: authUser.value.name,
            username: authUser.value.username,
        },
        _optimistic: true,
    };

    // Instant zero-latency UI update + iPhone send sound
    lastSentId.value = tempId;
    messages.value.push(optimisticMsg);
    chatInput.value = '';
    replyingTo.value = null;
    nextTick(() => {
        autoResizeChatInput();
        scrollChatBottom();
    });
    playIphoneSendSound();

    apiClient
        .post(`/dev-jams/${props.jam.slug}/messages`, {
            message: text,
            submission_id: activeTargetSubmissionId.value,
            reply_to_id: replyContext ? replyContext.id : null,
        })
        .then((res) => {
            const realMsg = res.data.message;
            const idx = messages.value.findIndex((m) => m.id === tempId);
            if (messages.value.some((m) => m.id === realMsg.id)) {
                if (idx !== -1) messages.value.splice(idx, 1);
            } else if (idx !== -1) {
                lastSentId.value = realMsg.id;
                messages.value[idx] = realMsg;
            } else {
                messages.value.push(realMsg);
            }
        })
        .catch((e) => {
            messages.value = messages.value.filter((m) => m.id !== tempId);
            chatInput.value = text;
            nextTick(autoResizeChatInput);
            if (!navigator.onLine) {
                isOnline.value = false;
            } else {
                showToast(e.response?.data?.message || 'Message non envoyé.');
            }
        });
}

async function deleteMsg(msg) {
    try {
        await apiClient.delete(`/dev-jams/${props.jam.slug}/messages/${msg.id}`);
        messages.value = messages.value.filter((m) => m.id !== msg.id);
    } catch {
        showToast('Suppression impossible.');
    }
}

async function pinMessage(messageId) {
    if (!canRearrangePreview.value) return;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/messages/pin`, {
            message_id: messageId,
        });
        pinnedMessage.value = res.data.pinned_message || null;
        showToast(pinnedMessage.value ? 'Message épinglé en haut du chat !' : 'Message désépinglé.');
    } catch {
        showToast('Impossible d’épingler ce message.');
    }
}

async function pollMessages() {
    if (!isOnline.value) return;
    try {
        const serverMessages = messages.value.filter((m) => m.id > 0);
        const lastId = serverMessages.length ? serverMessages[serverMessages.length - 1].id : 0;
        const res = await apiClient.get(`/dev-jams/${props.jam.slug}/messages?after_id=${lastId}`);
        isOnline.value = true;
        if (typeof res.data.online_count === 'number') onlineCount.value = res.data.online_count;
        if (Array.isArray(res.data.online_users)) onlineUsers.value = res.data.online_users;
        if (Array.isArray(res.data.typing_users)) typingUsers.value = res.data.typing_users;
        if (Array.isArray(res.data.polls)) polls.value = res.data.polls;
        if ('pinned_message' in res.data) pinnedMessage.value = res.data.pinned_message;
        if (typeof res.data.hearts_count === 'number' && pendingHearts === 0) {
            heartCount.value = res.data.hearts_count;
        }
        if (Array.isArray(res.data.raised_hands)) raisedHands.value = res.data.raised_hands;
        if (res.data.message_reactions && typeof res.data.message_reactions === 'object') {
            messageReactions.value = res.data.message_reactions;
        }
        if ('sprint' in res.data) {
            sprint.value = res.data.sprint;
            updateSprintDisplay();
        }
        if (res.data.creator_active_tab) {
            creatorActiveTab.value = res.data.creator_active_tab;
            if (followCreator.value && !canRearrangePreview.value && codeTab.value !== res.data.creator_active_tab) {
                codeTab.value = res.data.creator_active_tab;
            }
        }

        if (Array.isArray(res.data.collaborators)) {
            collaborators.value = res.data.collaborators;
        }

        const invites = res.data.pending_invites_for_me || [];
        if (invites.length) {
            pendingInvite.value = invites[0];
            showBottomPopup(
                {
                    kind: 'invite',
                    title: 'Invitation à co-éditer',
                    text: `${invites[0].from_user_name} vous invite à l'aider sur le code de cette Jam !`,
                    invite: invites[0],
                },
                0
            );
        }

        const notes = res.data.collab_notifications_for_me || [];
        if (notes.length) {
            notes.forEach((n) => showToast(n.text));
        }

        if (res.data.live_code && canCoEditSharedCode.value) {
            applyRemoteLiveCode(res.data.live_code);
        }

        const incoming = res.data.messages || [];
        if (incoming.length) {
            let added = 0;
            let lastIncomingOther = null;
            incoming.forEach((m) => {
                if (!messages.value.some((x) => x.id === m.id)) {
                    messages.value.push(m);
                    added += 1;
                    if (!authUser.value || Number(m.user_id) !== Number(authUser.value.id)) {
                        lastIncomingOther = m;
                        if (isMessageMentioningMe(m)) {
                            playMentionChimeSound();
                            showToast(`🔔 ${m.user?.name || 'Quelqu’un'} vous a mentionné !`);
                        }
                    }
                }
            });
            if (added > 0 && !chatOpen.value) {
                unreadCount.value += added;
            }
            if (lastIncomingOther && !pendingInvite.value) {
                showBottomPopup({
                    kind: 'message',
                    title: lastIncomingOther.user?.name || 'Nouveau message',
                    text: lastIncomingOther.message,
                });
            }
            nextTick(scrollChatBottom);
        }
    } catch (err) {
        if (!navigator.onLine || !err?.response) {
            isOnline.value = false;
        }
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

function handleOnline() {
    isOnline.value = true;
    pollMessages();
    if (pendingHearts > 0) flushHeartsToServer();
}

function handleOffline() {
    isOnline.value = false;
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
    } catch {
        // ignore storage errors
    }

    updateCountdown();
    updateSprintDisplay();
    countdownTimer = setInterval(() => {
        updateCountdown();
        updateSprintDisplay();
    }, 1000);
    pollTimer = setInterval(pollMessages, 2500);
    pollMessages();
    window.addEventListener('resize', handleWindowResize);
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
    window.addEventListener('message', handleSandboxMessage);
    nextTick(scrollChatBottom);
});

onUnmounted(() => {
    clearInterval(countdownTimer);
    clearInterval(pollTimer);
    clearTimeout(typingStopTimer);
    clearTimeout(heartFlushTimer);
    clearTimeout(jsReloadTimer);
    window.removeEventListener('resize', handleWindowResize);
    window.removeEventListener('online', handleOnline);
    window.removeEventListener('offline', handleOffline);
    window.removeEventListener('message', handleSandboxMessage);
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
                <!-- Active Co-Editors & Creator Revoke -->
                <div v-if="collaborators.length" class="d-none d-lg-flex align-items-center gap-1 px-2 py-1 rounded-pill bg-info bg-opacity-10 border border-info border-opacity-25">
                    <span class="small text-info fw-semibold" style="font-size: 11px;">
                        <i class="bi bi-people me-1"></i>{{ collaborators.length }} co-éditeur(s)
                    </span>
                    <button
                        v-if="canRearrangePreview"
                        type="button"
                        class="btn btn-xs btn-outline-danger rounded-pill py-0 px-2 ms-1"
                        style="font-size: 10px;"
                        title="Révoquer tous les co-éditeurs pour rester seul à manipuler le code"
                        @click="revokeCollaborator(null)"
                    >
                        Révoquer
                    </button>
                </div>

                <span class="badge bg-success bg-opacity-25 border border-success border-opacity-50 text-success rounded-pill px-2 py-1 d-none d-sm-inline" style="font-size: 11px;" title="Participants connectés à cette Jam uniquement">
                    <i class="bi bi-people-fill me-1"></i>{{ onlineCount }} en ligne
                </span>
                <span class="badge bg-black bg-opacity-50 border border-secondary border-opacity-50 text-warning rounded-pill px-2 py-1 d-none d-md-inline" style="font-size: 11px;">
                    <i class="bi bi-clock me-1"></i>{{ countdownText }}
                </span>

                <!-- Watch Party: Follow Creator Active Tab -->
                <button
                    v-if="!canRearrangePreview"
                    type="button"
                    class="btn btn-sm rounded-3 px-2 py-1 d-none d-lg-flex align-items-center gap-1"
                    :class="followCreator ? 'btn-info text-dark fw-semibold' : 'dj-btn-ghost'"
                    style="font-size: 11px;"
                    title="Suivre automatiquement l'onglet actif du créateur"
                    @click="followCreator = !followCreator; if (followCreator) codeTab = creatorActiveTab;"
                >
                    <i class="bi bi-eye-fill"></i>
                    <span>{{ followCreator ? 'Suit le créateur' : 'Suivre' }}</span>
                </button>

                <!-- Starter Templates -->
                <div v-if="canCoEditSharedCode" class="position-relative d-none d-lg-block">
                    <button
                        type="button"
                        class="btn btn-sm dj-btn-ghost rounded-3 px-2 py-1"
                        title="Charger un template de démarrage"
                        @click="showTemplateMenu = !showTemplateMenu"
                    >
                        <i class="bi bi-magic text-info me-1"></i>Templates
                    </button>
                    <div v-if="showTemplateMenu" class="dj-share-menu shadow-lg rounded-4 p-2 d-flex flex-column gap-1" style="min-width: 210px;">
                        <button
                            v-for="(tpl, key) in starterTemplates"
                            :key="key"
                            type="button"
                            class="btn btn-sm dj-share-item text-start"
                            @click="applyStarterTemplate(key)"
                        >
                            <i class="bi bi-stars text-info me-2"></i>{{ tpl.label }}
                        </button>
                    </div>
                </div>

                <!-- Publish to Portfolio -->
                <button
                    v-if="authUser"
                    type="button"
                    class="btn btn-sm dj-btn-ghost rounded-3 px-2 py-1 d-none d-xl-inline-flex align-items-center gap-1"
                    title="Copier le code et ouvrir le Portfolio Builder Tech237"
                    @click="publishToPortfolio"
                >
                    <i class="bi bi-briefcase-fill text-primary"></i>
                    <span>Portfolio</span>
                </button>

                <!-- Co-édition & Invitations Button -->
                <button
                    type="button"
                    class="btn btn-sm rounded-3 px-2 py-1 d-flex align-items-center gap-1"
                    :class="collaborators.length || isCollaborator || raisedHands.length ? 'btn-outline-warning' : 'dj-btn-ghost'"
                    title="Co-édition : inviter à coder ensemble, mains levées ou révoquer l'accès"
                    @click="showCollabModal = true"
                >
                    <i class="bi bi-people-fill text-warning"></i>
                    <span class="d-none d-md-inline">Co-édition</span>
                    <span v-if="collaborators.length" class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">
                        {{ collaborators.length }}
                    </span>
                    <span v-if="raisedHands.length" class="badge bg-info text-dark rounded-pill" style="font-size: 10px;" title="Mains levées">
                        ✋ {{ raisedHands.length }}
                    </span>
                </button>

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
                    v-if="canCoEditSharedCode"
                    type="button"
                    class="btn btn-sm rounded-3 px-3 py-1 fw-semibold dj-save-btn"
                    :disabled="savingSubmission"
                    @click="saveMySubmission"
                >
                    <span v-if="savingSubmission" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-cloud-arrow-up-fill me-1"></i>
                    <span class="d-none d-sm-inline">Enregistrer</span>
                </button>
                <span
                    v-else
                    class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-50 text-white-50 rounded-pill px-2 py-1"
                    style="font-size: 11px;"
                    title="Seul le créateur ou un co-éditeur invité peut manipuler le code"
                >
                    <i class="bi bi-lock-fill me-1"></i>Lecture seule
                </span>
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
                    :class="{ active: activeSection === 'polls' }"
                    title="Sondages en direct"
                    @click="activeSection = 'polls'"
                >
                    <i class="bi bi-bar-chart-fill fs-5"></i>
                    <span v-if="openPollsCount > 0" class="dj-mini-badge bg-info text-dark">{{ openPollsCount }}</span>
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
                    <input v-model="subTitle" :disabled="!canCoEditSharedCode" type="text" class="form-control form-control-sm dj-dark-input mb-2" placeholder="Titre du projet..." />
                    <input v-model="subDescription" :disabled="!canCoEditSharedCode" type="text" class="form-control form-control-sm dj-dark-input mb-2" placeholder="Courte description..." />
                    <select v-if="jam.allow_external_links" v-model="subType" :disabled="!canCoEditSharedCode" class="form-select form-select-sm dj-dark-input">
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
                                    <template v-if="canCoEditSharedCode">
                                        <i class="bi bi-shield-lock-fill text-success me-1"></i>Sandbox
                                    </template>
                                    <template v-else>
                                        <i class="bi bi-lock-fill text-warning me-1"></i>Lecture seule
                                    </template>
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
                                    :readonly="!canCoEditSharedCode"
                                    spellcheck="false"
                                    class="dj-textarea flex-grow-1 font-monospace p-3"
                                    @keydown="handleTabKey($event, 'html')"
                                ></textarea>
                                <textarea
                                    v-else-if="codeTab === 'css'"
                                    v-model="cssCode"
                                    :readonly="!canCoEditSharedCode"
                                    spellcheck="false"
                                    class="dj-textarea flex-grow-1 font-monospace p-3 text-info"
                                    @keydown="handleTabKey($event, 'css')"
                                ></textarea>
                                <textarea
                                    v-else
                                    v-model="jsCode"
                                    :readonly="!canCoEditSharedCode"
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
                                    <!-- Responsive Preview Modes -->
                                    <div class="btn-group btn-group-xs me-1 d-none d-sm-inline-flex" role="group" aria-label="Mode responsive">
                                        <button
                                            type="button"
                                            class="btn btn-xs py-0 px-1"
                                            :class="previewDevice === 'mobile' ? 'btn-info text-dark' : 'dj-btn-ghost'"
                                            title="Mobile (375px)"
                                            @click="previewDevice = 'mobile'"
                                        >
                                            <i class="bi bi-phone"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-xs py-0 px-1"
                                            :class="previewDevice === 'tablet' ? 'btn-info text-dark' : 'dj-btn-ghost'"
                                            title="Tablette (768px)"
                                            @click="previewDevice = 'tablet'"
                                        >
                                            <i class="bi bi-tablet"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-xs py-0 px-1"
                                            :class="previewDevice === 'desktop' ? 'btn-info text-dark' : 'dj-btn-ghost'"
                                            title="Desktop (100%)"
                                            @click="previewDevice = 'desktop'"
                                        >
                                            <i class="bi bi-laptop"></i>
                                        </button>
                                    </div>

                                    <!-- Console JS Toggle -->
                                    <button
                                        type="button"
                                        class="btn btn-xs py-0 px-2 d-flex align-items-center gap-1"
                                        :class="showConsole ? 'btn-warning text-dark' : 'dj-btn-ghost'"
                                        title="Afficher / masquer la Console JS"
                                        @click="showConsole = !showConsole"
                                    >
                                        <i class="bi bi-terminal"></i>
                                        <span v-if="consoleErrorCount" class="badge bg-danger rounded-pill" style="font-size: 9px;">{{ consoleErrorCount }}</span>
                                        <span v-else-if="consoleLogs.length" class="badge bg-secondary rounded-pill" style="font-size: 9px;">{{ consoleLogs.length }}</span>
                                    </button>

                                    <button
                                        v-if="canRearrangePreview && !previewFullscreen"
                                        type="button"
                                        class="btn btn-xs dj-btn-ghost py-0 px-2"
                                        :title="previewDock === 'bottom' ? 'Revenir à droite du Sandbox' : 'Placer en bas du Sandbox'"
                                        @click="setPreviewDock(previewDock === 'bottom' ? 'right' : 'bottom')"
                                    >
                                        <i class="bi" :class="previewDock === 'bottom' ? 'bi-layout-sidebar-reverse' : 'bi-layout-split'"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs dj-btn-ghost py-0 px-2" title="Rafraîchir l'aperçu (Ctrl+Entrée)" @click="refreshPreviewNow">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs dj-btn-ghost py-0 px-2" :title="previewFullscreen ? 'Réduire' : 'Plein écran'" @click="previewFullscreen = !previewFullscreen">
                                        <i class="bi" :class="previewFullscreen ? 'bi-fullscreen-exit' : 'bi-fullscreen'"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex-grow-1 d-flex justify-content-center overflow-auto dj-preview-stage">
                                <iframe
                                    ref="previewIframeEl"
                                    :key="previewKey"
                                    :srcdoc="liveSrcDoc"
                                    sandbox="allow-scripts allow-modals"
                                    class="border-0 h-100 dj-preview-frame"
                                    :class="`dj-device-${previewDevice}`"
                                    style="background: #0b0e15;"
                                    title="Aperçu en direct Dev Jam"
                                ></iframe>
                            </div>

                            <!-- Integrated JS Console Drawer -->
                            <div v-if="showConsole" class="dj-console-drawer border-top d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between px-3 py-1 border-bottom border-white border-opacity-10">
                                    <span class="small fw-semibold text-white-50 font-monospace" style="font-size: 11px;">
                                        <i class="bi bi-terminal-fill text-warning me-1"></i>Console JS ({{ consoleLogs.length }})
                                    </span>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-xs dj-btn-ghost py-0 px-2" style="font-size: 10px;" @click="clearConsoleLogs">
                                            Effacer
                                        </button>
                                        <button type="button" class="btn btn-xs dj-btn-ghost py-0 px-1" @click="showConsole = false">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="dj-console-logs overflow-auto px-3 py-2 font-monospace">
                                    <div v-if="!consoleLogs.length" class="text-white-50 small" style="font-size: 11px;">
                                        Aucun log pour le moment. Utilisez <code>console.log()</code> dans app.js.
                                    </div>
                                    <div
                                        v-for="entry in consoleLogs"
                                        :key="entry.id"
                                        class="dj-console-line d-flex align-items-start gap-2 py-1 border-bottom border-white border-opacity-5"
                                        :class="`dj-console-${entry.level}`"
                                    >
                                        <span class="text-white-50 flex-shrink-0" style="font-size: 10px;">{{ entry.time }}</span>
                                        <span class="text-uppercase fw-bold flex-shrink-0" style="font-size: 10px;">[{{ entry.level }}]</span>
                                        <span class="text-break" style="font-size: 11px;">{{ entry.text }}</span>
                                    </div>
                                </div>
                            </div>
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

                    <!-- Live Podium (Top 3) -->
                    <div v-if="podiumSubmissions.length" class="dj-glass-box rounded-4 p-3 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold small text-warning text-uppercase"><i class="bi bi-trophy-fill me-1"></i>Podium en direct</span>
                            <span class="small text-white-50" style="font-size: 11px;">Classement par votes</span>
                        </div>
                        <div class="row g-2">
                            <div v-for="(topSub, idx) in podiumSubmissions" :key="`podium-${topSub.id}`" class="col-md-4">
                                <div class="dj-dark-row rounded-3 p-2 d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="fs-5">{{ idx === 0 ? '🥇' : idx === 1 ? '🥈' : '🥉' }}</span>
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-white text-truncate small">{{ topSub.title }}</div>
                                            <div class="text-white-50 text-truncate" style="font-size: 11px;">par {{ topSub.user?.name || 'Membre' }}</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill flex-shrink-0">
                                        ❤️ {{ topSub.votes_count || 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="submissions.length" class="row g-3">
                        <div v-for="sub in submissions" :key="sub.id" class="col-md-6 col-xl-4">
                            <div class="dj-glass-box rounded-4 p-3 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-white">{{ sub.title }}</h6>
                                        <div class="small text-white-50 d-flex align-items-center flex-wrap gap-1 mt-1">
                                            <span>par {{ sub.user?.name || 'Membre' }}</span>
                                            <span
                                                v-for="badge in getParticipantBadges(sub.user)"
                                                :key="badge.label"
                                                class="badge rounded-pill"
                                                :class="badge.cls"
                                                style="font-size: 9px;"
                                            >
                                                {{ badge.icon }} {{ badge.label }}
                                            </span>
                                        </div>
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

                <!-- SECTION 2B: LIVE POLLS (Creator creates, 1 vote per person, live progress) -->
                <div v-else-if="activeSection === 'polls'" class="p-3 p-md-4 overflow-auto flex-grow-1">
                    <div class="mx-auto" style="max-width: 780px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill me-2 text-info"></i>Sondages en direct</h5>
                            <button type="button" class="btn btn-sm btn-outline-light rounded-pill" @click="activeSection = 'editor'">
                                <i class="bi bi-code-slash me-1"></i>Retour à l'éditeur
                            </button>
                        </div>

                        <!-- Creator-only Poll Builder -->
                        <div v-if="canRearrangePreview" class="dj-glass-box rounded-4 p-4 mb-4">
                            <h6 class="fw-bold text-white mb-3"><i class="bi bi-plus-circle me-2 text-info"></i>Créer un nouveau sondage</h6>
                            <div v-if="activeOpenPoll" class="p-3 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <span class="small text-warning">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    Un sondage est déjà en cours. Clôturez-le d'abord avant d'en créer un second.
                                </span>
                                <button type="button" class="btn btn-xs btn-warning text-dark rounded-pill px-3 fw-semibold" @click="toggleClosePoll(activeOpenPoll)">
                                    Clôturer le sondage en cours
                                </button>
                            </div>
                            <template v-else>
                                <div class="mb-3">
                                    <label class="form-label small text-white-50">Question du sondage</label>
                                    <input v-model="pollQuestion" type="text" class="form-control dj-dark-input" placeholder="Ex : Quel framework préférez-vous pour ce défi ?" maxlength="280" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-white-50">Choix de réponses (2 à 6)</label>
                                    <div v-for="(opt, idx) in pollOptions" :key="idx" class="d-flex gap-2 mb-2">
                                        <input v-model="pollOptions[idx]" type="text" class="form-control form-control-sm dj-dark-input" :placeholder="`Option ${idx + 1}`" maxlength="120" />
                                        <button v-if="pollOptions.length > 2" type="button" class="btn btn-sm btn-outline-danger" @click="removePollOption(idx)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <button v-if="pollOptions.length < 6" type="button" class="btn btn-xs dj-btn-ghost rounded-pill px-3 py-1" @click="addPollOption">
                                        <i class="bi bi-plus me-1"></i>Ajouter un choix
                                    </button>
                                </div>
                                <button type="button" class="btn btn-sm dj-save-btn rounded-pill px-4 fw-semibold" :disabled="creatingPoll" @click="createPoll">
                                    <span v-if="creatingPoll" class="spinner-border spinner-border-sm me-1"></span>
                                    Publier le sondage
                                </button>
                            </template>
                        </div>

                        <!-- Polls List -->
                        <div v-if="polls.length" class="d-flex flex-column gap-3">
                            <div
                                v-for="poll in polls"
                                :key="poll.id"
                                class="dj-glass-box rounded-4 p-4"
                                :class="{ 'dj-poll-card-glow': !poll.is_closed && !poll.user_voted_option_id }"
                            >
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <h6 class="fw-bold text-white mb-1">{{ poll.question }}</h6>
                                        <span class="small text-white-50">{{ poll.total_votes }} vote(s) • 1 vote par personne</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-pill" :class="poll.is_closed ? 'bg-secondary' : 'bg-success bg-opacity-25 text-success'">
                                            {{ poll.is_closed ? 'Clôturé' : 'En cours' }}
                                        </span>
                                        <button v-if="canRearrangePreview" type="button" class="btn btn-xs dj-btn-ghost rounded-pill px-2 py-1" @click="toggleClosePoll(poll)">
                                            {{ poll.is_closed ? 'Rouvrir' : 'Clôturer' }}
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <button
                                        v-for="opt in poll.options"
                                        :key="opt.id"
                                        type="button"
                                        class="dj-poll-option text-start position-relative overflow-hidden"
                                        :class="{
                                            'is-selected': poll.user_voted_option_id === opt.id,
                                            'is-locked': Boolean(poll.user_voted_option_id) || poll.is_closed || !authUser,
                                        }"
                                        :disabled="Boolean(poll.user_voted_option_id) || poll.is_closed || votingPollId === poll.id || !authUser"
                                        @click="votePoll(poll, opt.id)"
                                    >
                                        <div class="dj-poll-progress" :style="{ width: `${opt.percentage}%` }"></div>
                                        <div class="position-relative d-flex justify-content-between align-items-center px-3 py-2">
                                            <span class="fw-semibold small text-white">
                                                <i v-if="poll.user_voted_option_id === opt.id" class="bi bi-check-circle-fill text-info me-2"></i>
                                                {{ opt.label }}
                                            </span>
                                            <span class="small fw-bold text-info">{{ opt.percentage }}% ({{ opt.votes_count }})</span>
                                        </div>
                                    </button>
                                </div>
                                <div v-if="!authUser && !poll.is_closed" class="mt-3">
                                    <Link href="/login" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold">
                                        <i class="bi bi-lock-fill me-1"></i>Connectez-vous pour voter
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-5 text-white-50">
                            Aucun sondage n'a encore été lancé pour cette Jam.
                        </div>
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
                <button
                    v-if="activeOpenPoll"
                    type="button"
                    class="btn btn-xs rounded-pill px-3 py-0 fw-semibold dj-statusbar-poll-btn d-flex align-items-center gap-1"
                    :class="{ 'dj-poll-glow-pulse': hasUnvotedOpenPoll }"
                    @click="activeSection = 'polls'"
                >
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>{{ hasUnvotedOpenPoll ? 'Participer au sondage' : 'Sondage en cours' }}</span>
                </button>

                <!-- Synchronized Sprint / Pomodoro Timer -->
                <div class="position-relative">
                    <button
                        v-if="sprint || canRearrangePreview"
                        type="button"
                        class="btn btn-xs rounded-pill px-2 py-0 d-flex align-items-center gap-1"
                        :class="sprint ? (sprintEnded ? 'btn-danger' : 'btn-outline-info') : 'dj-btn-ghost'"
                        style="font-size: 11px;"
                        :title="canRearrangePreview ? 'Lancer ou gérer un Sprint chronométré' : 'Sprint en cours'"
                        @click="canRearrangePreview ? (showSprintPopover = !showSprintPopover) : null"
                    >
                        <span>⏱️</span>
                        <span v-if="sprint">{{ sprint.label }} : <strong>{{ sprintEnded ? 'Terminé !' : sprintTimeLeftText }}</strong></span>
                        <span v-else class="d-none d-lg-inline">Sprint</span>
                    </button>
                    <div v-if="showSprintPopover && canRearrangePreview" class="dj-share-menu dj-share-menu-up shadow-lg rounded-4 p-2 d-flex flex-column gap-1" style="min-width: 215px;">
                        <input
                            v-model="sprintLabel"
                            type="text"
                            class="form-control form-control-sm dj-dark-input mb-1"
                            placeholder="Nom du sprint (ex: Sprint UI)"
                            maxlength="60"
                        />
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-xs btn-outline-info flex-grow-1" @click="startSprint(10)">10 min</button>
                            <button type="button" class="btn btn-xs btn-outline-info flex-grow-1" @click="startSprint(20)">20 min</button>
                            <button type="button" class="btn btn-xs btn-outline-info flex-grow-1" @click="startSprint(25)">25 min</button>
                        </div>
                        <button v-if="sprint" type="button" class="btn btn-xs btn-outline-danger mt-1" @click="stopSprint">
                            Arrêter le Sprint
                        </button>
                    </div>
                </div>

                <!-- One-Click ZIP Export -->
                <button
                    type="button"
                    class="btn btn-xs dj-btn-ghost rounded-3 px-2 py-0 d-none d-md-inline-flex align-items-center gap-1"
                    title="Exporter le projet en ZIP (index.html, style.css, app.js)"
                    @click="exportProjectZip"
                >
                    <i class="bi bi-file-earmark-zip-fill text-success"></i>
                    <span class="d-none d-xl-inline">ZIP</span>
                </button>

                <!-- Social Share Dropdown -->
                <div class="position-relative">
                    <button
                        type="button"
                        class="btn btn-xs dj-btn-ghost rounded-3 px-2 py-0 d-flex align-items-center gap-1"
                        title="Partager la Jam sur les réseaux sociaux"
                        @click="showShareMenu = !showShareMenu"
                    >
                        <i class="bi bi-share-fill text-info"></i><span class="d-none d-md-inline">Partager</span>
                    </button>
                    <div v-if="showShareMenu" class="dj-share-menu dj-share-menu-up shadow-lg rounded-4 p-2 d-flex flex-column gap-1">
                        <button type="button" class="btn btn-sm dj-share-item text-start" @click="shareSocial('facebook')">
                            <i class="bi bi-facebook text-primary me-2"></i>Facebook
                        </button>
                        <button type="button" class="btn btn-sm dj-share-item text-start" @click="shareSocial('twitter')">
                            <i class="bi bi-twitter-x text-light me-2"></i>Twitter / X
                        </button>
                        <button type="button" class="btn btn-sm dj-share-item text-start" @click="shareSocial('whatsapp')">
                            <i class="bi bi-whatsapp text-success me-2"></i>WhatsApp
                        </button>
                        <button type="button" class="btn btn-sm dj-share-item text-start" @click="shareSocial('tiktok')">
                            <i class="bi bi-tiktok text-danger me-2"></i>TikTok
                        </button>
                        <button type="button" class="btn btn-sm dj-share-item text-start" @click="shareSocial('copy')">
                            <i class="bi bi-link-45deg text-info me-2"></i>Copier le lien
                        </button>
                        <button
                            v-if="canRearrangePreview"
                            type="button"
                            class="btn btn-sm btn-outline-info rounded-3 text-start mt-1"
                            @click="shareJamLinkInChat"
                        >
                            <i class="bi bi-chat-quote-fill me-2"></i>Poster le lien dans le chat
                        </button>
                    </div>
                </div>
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
                    <span class="fw-bold small text-white text-truncate">Live Chat</span>
                    <span class="badge bg-success bg-opacity-25 text-success rounded-pill flex-shrink-0" style="font-size: 10px;" title="Connectés sur cette Jam uniquement">
                        {{ onlineCount }} en ligne
                    </span>
                </div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                    <button
                        v-if="authUser && !canRearrangePreview"
                        type="button"
                        class="btn btn-xs py-0 px-2"
                        :class="isMyHandRaised ? 'btn-info text-dark fw-semibold' : 'dj-btn-ghost'"
                        :title="isMyHandRaised ? 'Baisser la main' : 'Lever la main pour demander la parole ou l’accès au code'"
                        @click="toggleHandRaise()"
                    >
                        ✋
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

            <!-- Pinned Announcement Banner -->
            <div v-if="pinnedMessage" class="dj-pinned-banner px-3 py-2 border-bottom d-flex align-items-start justify-content-between gap-2">
                <div class="min-w-0">
                    <div class="text-warning fw-bold" style="font-size: 10px;">
                        <i class="bi bi-pin-angle-fill me-1"></i>Épinglé • {{ pinnedMessage.author }}
                    </div>
                    <div class="small text-white text-truncate" style="font-size: 12px;">{{ pinnedMessage.message }}</div>
                </div>
                <button v-if="canRearrangePreview" type="button" class="btn-close btn-close-white flex-shrink-0" style="font-size: 8px;" title="Désépingler" @click="pinMessage(null)"></button>
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
                        :class="[
                            isMyMessage(m) ? 'dj-bubble-me' : 'dj-bubble-other',
                            isMessageMentioningMe(m) ? 'dj-bubble-mention' : '',
                        ]"
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
                                    v-if="authUser && m.id > 0"
                                    type="button"
                                    class="btn btn-link p-0 text-white-50 opacity-75"
                                    style="font-size: 11px;"
                                    title="Citer ce message"
                                    @click="startReply(m)"
                                >
                                    <i class="bi bi-quote"></i>
                                </button>
                                <button
                                    v-if="canRearrangePreview"
                                    type="button"
                                    class="btn btn-link p-0 text-warning opacity-75"
                                    style="font-size: 10px;"
                                    title="Épingler ce message en haut du chat"
                                    @click="pinMessage(m.id)"
                                >
                                    <i class="bi bi-pin-angle-fill"></i>
                                </button>
                                <button
                                    v-if="canRearrangePreview && !isMyMessage(m) && m.user_id"
                                    type="button"
                                    class="btn btn-link p-0 text-warning opacity-75"
                                    style="font-size: 11px;"
                                    title="Inviter à manipuler le même code (co-éditeur)"
                                    @click="inviteToCode(m.user_id)"
                                >
                                    <i class="bi bi-person-plus-fill"></i>
                                </button>
                                <button
                                    v-if="authUser && !isMyMessage(m)"
                                    type="button"
                                    class="btn btn-link p-0 text-info opacity-75"
                                    style="font-size: 11px;"
                                    title="Tagger / Répondre"
                                    @click="replyToMessage(m)"
                                >
                                    <i class="bi bi-reply-fill"></i>
                                </button>
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
                        <!-- Quote Reply Preview -->
                        <div v-if="m.reply_preview" class="dj-quote-box rounded-2 px-2 py-1 mb-1">
                            <div class="fw-bold text-info" style="font-size: 10px;">{{ m.reply_preview.author }}</div>
                            <div class="text-white-50 text-truncate" style="font-size: 11px;">{{ m.reply_preview.excerpt }}</div>
                        </div>

                        <div class="small" style="white-space: pre-wrap; word-break: break-word;">
                            <template v-for="(part, idx) in parseChatTokens(m.message)" :key="idx">
                                <a
                                    v-if="part.kind === 'url'"
                                    :href="part.text"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="dj-chat-link"
                                >{{ part.text }}</a>
                                <span v-else-if="part.kind === 'mention'" class="dj-mention-tag">{{ part.text }}</span>
                                <template v-else>{{ part.text }}</template>
                            </template>
                        </div>

                        <!-- Quick Message Reactions -->
                        <div v-if="m.id > 0" class="d-flex align-items-center flex-wrap gap-1 mt-1 pt-1">
                            <button
                                v-for="emoji in reactionEmojis"
                                :key="emoji"
                                type="button"
                                class="dj-react-pill"
                                :class="{
                                    active: hasUserReacted(m.id, emoji),
                                    empty: !getMsgReactionCount(m.id, emoji),
                                }"
                                @click="toggleMessageReaction(m.id, emoji)"
                            >
                                <span>{{ emoji }}</span>
                                <span v-if="getMsgReactionCount(m.id, emoji)" class="ms-1">{{ getMsgReactionCount(m.id, emoji) }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quote Reply Composer Banner -->
            <div v-if="replyingTo" class="dj-reply-banner px-3 py-1 border-top d-flex align-items-center justify-content-between gap-2">
                <div class="min-w-0">
                    <div class="text-info fw-semibold" style="font-size: 10px;">Réponse à {{ replyingTo.author }}</div>
                    <div class="text-white-50 text-truncate" style="font-size: 11px;">{{ replyingTo.excerpt }}</div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0" style="font-size: 8px;" @click="cancelReply"></button>
            </div>

            <!-- WhatsApp-style Typing Indicator -->
            <div v-if="typingBannerText" class="dj-typing-bar px-3 py-1 border-top d-flex align-items-center gap-2 small">
                <span class="dj-typing-dots"><span></span><span></span><span></span></span>
                <span class="text-info text-truncate" style="font-size: 11px;">{{ typingBannerText }}...</span>
            </div>

            <!-- @Mention Autocomplete Popover -->
            <div v-if="authUser && filteredMentions.length" class="dj-mention-menu border-top p-2 d-flex flex-column gap-1">
                <button
                    v-for="cand in filteredMentions"
                    :key="cand.id"
                    type="button"
                    class="btn btn-sm dj-mention-item d-flex align-items-center justify-content-between gap-2 text-start px-3 py-2 w-100"
                    @click="insertMention(cand)"
                >
                    <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                        <span class="dj-avatar dj-avatar-other flex-shrink-0" style="width: 24px; height: 24px; font-size: 9px;">
                            {{ userInitials(cand.name) }}
                        </span>
                        <span class="fw-semibold text-white text-truncate" style="font-size: 13px;">{{ cand.name }}</span>
                    </div>
                    <span class="badge bg-info bg-opacity-25 text-info font-monospace flex-shrink-0" style="font-size: 11px;">@{{ cand.tag }}</span>
                </button>
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
                <form v-if="authUser" class="d-flex align-items-end gap-1" @submit.prevent="sendMessage">
                    <button
                        type="button"
                        class="btn btn-sm dj-btn-ghost rounded-circle flex-shrink-0"
                        title="Emojis & Émoticônes"
                        @click="showEmojiPicker = !showEmojiPicker"
                    >
                        <i class="bi bi-emoji-smile text-warning"></i>
                    </button>
                    <textarea
                        ref="chatInputEl"
                        v-model="chatInput"
                        rows="1"
                        class="form-control form-control-sm dj-dark-input dj-chat-textarea rounded-4"
                        placeholder="Écrire un message (@ pour tagger)..."
                        maxlength="1000"
                        @input="handleChatInput"
                        @keydown="handleChatKeydown"
                    ></textarea>
                    <button
                        type="submit"
                        class="btn btn-sm btn-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 32px; height: 32px;"
                        :disabled="!isOnline || !chatInput.trim()"
                    >
                        <i class="bi bi-send-fill" style="font-size: 12px;"></i>
                    </button>
                </form>
                <div v-else class="text-center py-1">
                    <Link href="/login" class="btn btn-sm btn-outline-info rounded-pill w-100 fw-semibold">
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

        <!-- Bottom-Left Notification / Collaboration Invite Popup -->
        <div v-if="bottomLeftPopup" class="dj-bottom-left-popup shadow-lg rounded-4 p-3">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <span class="fw-bold small text-info">
                    <i class="bi me-1" :class="bottomLeftPopup.kind === 'invite' ? 'bi-people-fill' : 'bi-chat-dots-fill'"></i>
                    {{ bottomLeftPopup.title }}
                </span>
                <button type="button" class="btn-close btn-close-white" style="font-size: 9px;" @click="bottomLeftPopup = null"></button>
            </div>
            <p class="small text-light mb-2 text-truncate-2" style="word-break: break-word;">{{ bottomLeftPopup.text }}</p>
            <div v-if="bottomLeftPopup.kind === 'invite'" class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" @click="respondInvite(true)">
                    <i class="bi bi-check-lg me-1"></i>Accepter
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" @click="respondInvite(false)">
                    Refuser
                </button>
            </div>
            <div v-else class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" @click="openChat(); bottomLeftPopup = null;">
                    Voir le chat
                </button>
            </div>
        </div>

        <!-- Co-édition & Invitations Modal -->
        <div v-if="showCollabModal" class="position-fixed top-0 start-0 w-100 h-100 bg-black bg-opacity-75 d-flex align-items-center justify-content-center p-3" style="z-index: 2700;" @click="showCollabModal = false">
            <div class="dj-glass-box rounded-4 shadow-lg w-100 overflow-hidden d-flex flex-column" style="max-width: 540px; max-height: 82vh;" @click.stop>
                <div class="p-3 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-white">
                        <i class="bi bi-people-fill text-warning me-2"></i>Co-édition &amp; Invitations d'aide
                    </h6>
                    <button type="button" class="btn-close btn-close-white" @click="showCollabModal = false"></button>
                </div>

                <div class="p-3 overflow-auto d-flex flex-column gap-3">
                    <!-- Pending Invite for current user -->
                    <div v-if="pendingInvite" class="p-3 rounded-3 bg-info bg-opacity-10 border border-info border-opacity-25">
                        <div class="fw-bold small text-info mb-1">Invitation reçue</div>
                        <p class="small text-light mb-2">{{ pendingInvite.from_user_name }} vous invite à manipuler le même code en direct.</p>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3" @click="respondInvite(true)">Accepter</button>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" @click="respondInvite(false)">Refuser</button>
                        </div>
                    </div>

                    <!-- Raised Hands Queue (✋ Lever la main) -->
                    <div v-if="raisedHands.length">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-info text-uppercase" style="font-size: 11px;">
                                ✋ Mains levées ({{ raisedHands.length }})
                            </span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div v-for="h in raisedHands" :key="`hand-${h.id}`" class="d-flex align-items-center justify-content-between p-2 rounded-3 dj-dark-row">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="dj-avatar dj-avatar-other">{{ userInitials(h.name) }}</div>
                                    <div>
                                        <div class="small fw-semibold text-white">{{ h.name }}</div>
                                        <div class="text-info" style="font-size: 10px;">Demande la parole / co-édition</div>
                                    </div>
                                </div>
                                <div v-if="canRearrangePreview" class="d-flex gap-1">
                                    <button type="button" class="btn btn-xs btn-warning text-dark fw-semibold rounded-pill px-2 py-1" @click="inviteFromHand(h)">
                                        Inviter au code
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2 py-1" @click="toggleHandRaise(h.id, true)">
                                        Baisser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Co-Editors -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-white-50 text-uppercase" style="font-size: 11px;">
                                Co-éditeurs actifs ({{ collaborators.length }})
                            </span>
                            <button
                                v-if="canRearrangePreview && collaborators.length"
                                type="button"
                                class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0"
                                @click="revokeCollaborator(null)"
                            >
                                Tout révoquer
                            </button>
                        </div>
                        <div v-if="collaborators.length" class="d-flex flex-column gap-2">
                            <div v-for="c in collaborators" :key="c.id" class="d-flex align-items-center justify-content-between p-2 rounded-3 dj-dark-row">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="dj-avatar dj-avatar-me">{{ userInitials(c.name) }}</div>
                                    <div>
                                        <div class="small fw-semibold text-white">{{ c.name }}</div>
                                        <div class="text-success" style="font-size: 10px;">Peut modifier le code en direct</div>
                                    </div>
                                </div>
                                <button
                                    v-if="canRearrangePreview"
                                    type="button"
                                    class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1"
                                    @click="revokeCollaborator(c.id)"
                                >
                                    Révoquer
                                </button>
                            </div>
                        </div>
                        <div v-else class="small text-white-50 p-2 rounded-3 bg-black bg-opacity-25">
                            Aucun co-éditeur actif pour le moment.
                        </div>
                    </div>

                    <!-- Invite Participants (Creator only) -->
                    <div v-if="canRearrangePreview">
                        <div class="small fw-bold text-white-50 text-uppercase mb-2" style="font-size: 11px;">
                            Inviter un participant du chat à vous aider
                        </div>
                        <div v-if="mentionCandidates.length" class="d-flex flex-column gap-2">
                            <div v-for="cand in mentionCandidates" :key="cand.id" class="d-flex align-items-center justify-content-between p-2 rounded-3 dj-dark-row">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="dj-avatar dj-avatar-other">{{ userInitials(cand.name) }}</div>
                                    <span class="small fw-semibold text-white">{{ cand.name }}</span>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1"
                                    :disabled="collaborators.some((c) => Number(c.id) === Number(cand.id))"
                                    @click="inviteToCode(cand.id)"
                                >
                                    <i class="bi bi-person-plus-fill me-1"></i>
                                    {{ collaborators.some((c) => Number(c.id) === Number(cand.id)) ? 'Déjà actif' : 'Inviter' }}
                                </button>
                            </div>
                        </div>
                        <div v-else class="small text-white-50 p-2 rounded-3 bg-black bg-opacity-25">
                            Aucun autre participant connecté ou actif dans le chat pour le moment. Dès qu'un membre rejoint la Jam ou envoie un message, il apparaîtra ici.
                        </div>
                    </div>
                    <div v-else class="small text-white-50 p-2 rounded-3 bg-black bg-opacity-25">
                        <span v-if="isCollaborator" class="text-success fw-semibold">
                            Vous êtes actuellement co-éditeur sur cette Jam : vos modifications HTML/CSS/JS sont partagées avec le créateur.
                        </span>
                        <span v-else>
                            Seul le créateur de la Jam peut envoyer des invitations de co-édition. Si vous êtes invité, une popup apparaîtra en bas à gauche de votre écran.
                        </span>
                    </div>
                </div>
            </div>
        </div>

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

        <!-- Offline Blocking Overlay -->
        <div v-if="!isOnline" class="dj-offline-overlay d-flex align-items-center justify-content-center p-3">
            <div class="dj-glass-box rounded-4 shadow-lg p-4 text-center" style="max-width: 420px; border-color: rgba(244, 63, 94, 0.5) !important;">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-25 text-danger" style="width: 56px; height: 56px;">
                        <i class="bi bi-wifi-off fs-3"></i>
                    </span>
                </div>
                <h5 class="fw-bold text-white mb-2">Connexion internet perdue</h5>
                <p class="small text-white-50 mb-3">
                    Vous êtes hors ligne. Toutes les actions sont temporairement bloquées jusqu'au rétablissement de votre connexion.
                </p>
                <div class="d-inline-flex align-items-center gap-2 small text-warning">
                    <span class="spinner-border spinner-border-sm"></span>
                    <span>Tentative de reconnexion en cours...</span>
                </div>
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
.dj-mention-tag {
    display: inline-block;
    padding: 0 5px;
    border-radius: 6px;
    background: rgba(56, 189, 248, 0.22);
    color: #7dd3fc;
    font-weight: 700;
}
.dj-bubble-me .dj-mention-tag {
    background: rgba(255, 255, 255, 0.22);
    color: #ffffff;
}
.dj-typing-bar {
    background: rgba(10, 14, 22, 0.9);
    border-color: rgba(255, 255, 255, 0.08) !important;
}
.dj-typing-dots {
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.dj-typing-dots span {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #38bdf8;
    animation: typingBounce 1.2s infinite ease-in-out;
}
.dj-typing-dots span:nth-child(2) {
    animation-delay: 0.15s;
}
.dj-typing-dots span:nth-child(3) {
    animation-delay: 0.3s;
}
@keyframes typingBounce {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.45;
    }
    30% {
        transform: translateY(-4px);
        opacity: 1;
    }
}
.dj-mention-menu {
    background: rgba(12, 16, 26, 0.98);
    border-color: rgba(255, 255, 255, 0.08) !important;
    max-height: 150px;
    overflow-y: auto;
}
.dj-mention-item {
    border: none;
    border-radius: 8px;
    background: transparent;
}
.dj-mention-item:hover {
    background: rgba(99, 102, 241, 0.22);
}
.dj-share-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 6px);
    min-width: 215px;
    background: linear-gradient(160deg, rgba(16, 20, 32, 0.98), rgba(8, 10, 16, 0.99));
    border: 1px solid rgba(255, 255, 255, 0.12);
    z-index: 2500;
}
.dj-share-menu-up {
    top: auto;
    bottom: calc(100% + 8px);
    left: 0;
    right: auto;
}
.dj-share-item {
    color: #e2e8f0;
    background: transparent;
    border: none;
    border-radius: 8px;
}
.dj-share-item:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
}
.dj-poll-option {
    width: 100%;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.65);
    padding: 0;
    transition: border-color 0.15s ease, transform 0.15s ease;
}
.dj-poll-option:hover:not(:disabled) {
    border-color: #38bdf8;
    transform: translateY(-1px);
}
.dj-poll-option.is-selected {
    border-color: #22d3ee;
    box-shadow: 0 0 14px rgba(34, 211, 238, 0.2);
}
.dj-poll-progress {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.35), rgba(6, 182, 212, 0.3));
    transition: width 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}
.dj-chat-link {
    color: #38bdf8;
    text-decoration: underline;
    word-break: break-all;
}
.dj-bubble-me .dj-chat-link {
    color: #e0f2fe;
}
.dj-bottom-left-popup {
    position: fixed;
    left: 18px;
    bottom: 42px;
    width: 320px;
    max-width: calc(100vw - 36px);
    background: linear-gradient(155deg, rgba(16, 21, 34, 0.97), rgba(9, 12, 19, 0.99));
    border: 1px solid rgba(56, 189, 248, 0.38);
    backdrop-filter: blur(14px);
    z-index: 2560;
    animation: msgPopIn 0.25s cubic-bezier(0.22, 1, 0.36, 1);
}
.dj-dark-row {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
}
.dj-pinned-banner {
    background: linear-gradient(90deg, rgba(245, 158, 11, 0.16), rgba(15, 23, 42, 0.92));
    border-color: rgba(245, 158, 11, 0.28) !important;
}
.dj-statusbar-poll-btn {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(6, 182, 212, 0.3));
    border: 1px solid rgba(56, 189, 248, 0.55);
    color: #e0f2fe;
    font-size: 11px;
}
.dj-statusbar-poll-btn:hover {
    color: #ffffff;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.5), rgba(6, 182, 212, 0.5));
}
.dj-poll-glow-pulse {
    animation: pollGlowPulse 1.8s infinite ease-in-out;
}
.dj-poll-card-glow {
    border: 1px solid rgba(56, 189, 248, 0.65) !important;
    animation: pollCardGlow 2.2s infinite ease-in-out;
}
@keyframes pollGlowPulse {
    0%, 100% {
        box-shadow: 0 0 6px rgba(56, 189, 248, 0.45), 0 0 14px rgba(99, 102, 241, 0.3);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.9), 0 0 26px rgba(99, 102, 241, 0.6);
        transform: scale(1.03);
    }
}
@keyframes pollCardGlow {
    0%, 100% {
        box-shadow: 0 0 12px rgba(56, 189, 248, 0.22), 0 0 26px rgba(99, 102, 241, 0.16);
    }
    50% {
        box-shadow: 0 0 24px rgba(56, 189, 248, 0.5), 0 0 42px rgba(99, 102, 241, 0.35);
    }
}
@media (min-width: 992px) {
    .w-lg-50 {
        width: 50% !important;
    }
}
.dj-chat-textarea {
    resize: none;
    overflow-y: auto;
    max-height: 110px;
    line-height: 1.4;
    padding-top: 6px;
    padding-bottom: 6px;
}
.dj-offline-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(5, 7, 12, 0.82);
    backdrop-filter: blur(10px);
    pointer-events: all;
}
.dj-preview-stage {
    background: #06080d;
}
.dj-preview-frame {
    transition: width 0.25s ease;
}
.dj-device-desktop {
    width: 100%;
}
.dj-device-tablet {
    width: 768px;
    max-width: 100%;
    border-left: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-right: 1px solid rgba(255, 255, 255, 0.12) !important;
}
.dj-device-mobile {
    width: 375px;
    max-width: 100%;
    border-left: 1px solid rgba(56, 189, 248, 0.35) !important;
    border-right: 1px solid rgba(56, 189, 248, 0.35) !important;
}
.dj-console-drawer {
    height: 165px;
    background: #090b10;
    border-color: rgba(255, 255, 255, 0.1) !important;
}
.dj-console-logs {
    flex: 1;
    min-height: 0;
}
.dj-console-log {
    color: #e2e8f0;
}
.dj-console-warn {
    color: #fbbf24;
    background: rgba(251, 191, 36, 0.06);
}
.dj-console-error {
    color: #f87171;
    background: rgba(248, 113, 113, 0.08);
}
.dj-quote-box {
    background: rgba(0, 0, 0, 0.28);
    border-left: 2px solid #38bdf8;
}
.dj-reply-banner {
    background: rgba(15, 23, 42, 0.85);
    border-color: rgba(56, 189, 248, 0.3) !important;
}
.dj-bubble-mention {
    box-shadow: 0 0 0 1px rgba(250, 204, 21, 0.65), 0 0 14px rgba(250, 204, 21, 0.18);
}
.dj-react-pill {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #e2e8f0;
    border-radius: 999px;
    padding: 1px 6px;
    font-size: 10px;
    line-height: 1.4;
    cursor: pointer;
    transition: all 0.15s ease;
}
.dj-react-pill.empty {
    opacity: 0.45;
}
.dj-bubble:hover .dj-react-pill.empty {
    opacity: 0.9;
}
.dj-react-pill.active {
    background: rgba(56, 189, 248, 0.22);
    border-color: rgba(56, 189, 248, 0.65);
    color: #bae6fd;
    opacity: 1;
}
.dj-badge-gold {
    background: rgba(250, 204, 21, 0.18);
    color: #fde047;
    border: 1px solid rgba(250, 204, 21, 0.35);
}
.dj-badge-cyan {
    background: rgba(56, 189, 248, 0.18);
    color: #7dd3fc;
    border: 1px solid rgba(56, 189, 248, 0.35);
}
.dj-badge-violet {
    background: rgba(139, 92, 246, 0.2);
    color: #c4b5fd;
    border: 1px solid rgba(139, 92, 246, 0.35);
}
</style>
