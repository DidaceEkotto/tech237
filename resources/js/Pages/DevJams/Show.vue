<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';
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

const mainTab = ref('studio'); // studio | gallery
const mobilePane = ref('workspace'); // workspace | chat
const codeTab = ref('html'); // html | css | js
const showRules = ref(false);
const toast = ref('');

// Studio form state
const subTitle = ref(props.mySubmission?.title || 'Mon projet Dev Jam');
const subDescription = ref(props.mySubmission?.description || '');
const subType = ref(props.mySubmission?.submission_type || 'studio');
const htmlCode = ref(props.mySubmission?.html_code || `<div class="card">\n  <h1>Hello Tech237 Jam!</h1>\n  <p>Modifiez ce code HTML, CSS et JS en direct.</p>\n  <button id="btn">Tester l'interaction</button>\n</div>`);
const cssCode = ref(props.mySubmission?.css_code || `body {\n  font-family: system-ui, sans-serif;\n  display: grid;\n  place-items: center;\n  min-height: 100vh;\n  margin: 0;\n  background: linear-gradient(135deg, #0f172a, #312e81);\n  color: #f8fafc;\n}\n.card {\n  background: rgba(255,255,255,0.08);\n  padding: 2rem;\n  border-radius: 16px;\n  text-align: center;\n  border: 1px solid rgba(255,255,255,0.18);\n}\nbutton {\n  margin-top: 12px;\n  padding: 10px 18px;\n  border-radius: 999px;\n  border: none;\n  background: #38bdf8;\n  color: #0f172a;\n  font-weight: 700;\n  cursor: pointer;\n}`);
const jsCode = ref(props.mySubmission?.js_code || `document.getElementById('btn')?.addEventListener('click', () => {\n  alert('Bravo ! Votre code JS fonctionne dans le bac à sable sécurisé.');\n});`);
const externalRepoUrl = ref(props.mySubmission?.external_repo_url || '');
const externalDemoUrl = ref(props.mySubmission?.external_demo_url || '');
const savingSubmission = ref(false);

// Submissions & Votes
const submissions = ref([...(props.jam.submissions || [])]);
const votedIds = ref(new Set(props.votedSubmissionIds || []));
const previewSubmission = ref(null);

// Live Chat
const messages = ref([...(props.initialMessages || [])]);
const chatInput = ref('');
const sendingMessage = ref(false);
const activeTargetSubmissionId = ref(null);
const chatListEl = ref(null);
let pollTimer = null;
let countdownTimer = null;
const countdownText = ref('');

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

function showToast(msg) {
    toast.value = msg;
    setTimeout(() => {
        if (toast.value === msg) toast.value = '';
    }, 3200);
}

function handleTabKey(e, modelRef) {
    if (e.key !== 'Tab') return;
    e.preventDefault();
    const el = e.target;
    const start = el.selectionStart;
    const end = el.selectionEnd;
    modelRef.value = modelRef.value.substring(0, start) + '  ' + modelRef.value.substring(end);
    nextTick(() => {
        el.selectionStart = el.selectionEnd = start + 2;
    });
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
        if (idx !== -1) {
            submissions.value[idx] = saved;
        } else {
            submissions.value.unshift(saved);
        }
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
        if (res.data.voted) {
            votedIds.value.add(sub.id);
        } else {
            votedIds.value.delete(sub.id);
        }
    } catch (e) {
        showToast(e.response?.data?.message || 'Vote impossible.');
    }
}

async function sendMessage() {
    if (!authUser.value || !chatInput.value.trim()) return;
    sendingMessage.value = true;
    try {
        const res = await apiClient.post(`/dev-jams/${props.jam.slug}/messages`, {
            message: chatInput.value.trim(),
            submission_id: activeTargetSubmissionId.value,
        });
        const newMsg = res.data.message;
        if (!messages.value.some((m) => m.id === newMsg.id)) {
            messages.value.push(newMsg);
        }
        chatInput.value = '';
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
            incoming.forEach((m) => {
                if (!messages.value.some((x) => x.id === m.id)) {
                    messages.value.push(m);
                }
            });
            nextTick(scrollChatBottom);
        }
    } catch {
        // silent background poll
    }
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
    mobilePane.value = 'chat';
}

onMounted(() => {
    updateCountdown();
    countdownTimer = setInterval(updateCountdown, 1000);
    pollTimer = setInterval(pollMessages, 3500);
    nextTick(scrollChatBottom);
});

onUnmounted(() => {
    clearInterval(countdownTimer);
    clearInterval(pollTimer);
});
</script>

<template>
    <Head :title="`${jam.title} • Dev Jam Studio`" />
    <FrontLayout>
        <div class="container-fluid px-3 px-lg-4 py-3">
            <!-- Top Jam Header -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <Link href="/dev-jams" class="text-decoration-none small text-muted">
                                <i class="bi bi-arrow-left me-1"></i>Dev Jams
                            </Link>
                            <span class="badge rounded-pill" :class="jam.mode === 'official' ? 'bg-danger' : 'bg-primary'">
                                {{ jam.mode === 'official' ? 'Officielle Tech237' : 'Jam Communautaire' }}
                            </span>
                            <span v-if="jam.theme" class="badge bg-info text-dark rounded-pill">
                                Thème : {{ jam.theme }}
                            </span>
                            <span class="badge bg-dark rounded-pill">
                                <i class="bi bi-clock me-1"></i>{{ countdownText }}
                            </span>
                        </div>
                        <h4 class="fw-bold mb-1">{{ jam.title }}</h4>
                        <p class="small text-muted mb-0">{{ jam.description }}</p>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span v-if="jam.prize_text" class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                            <i class="bi bi-trophy-fill me-1"></i>{{ jam.prize_text }}
                        </span>
                        <button v-if="jam.rules" type="button" class="btn btn-sm btn-outline-secondary rounded-pill" @click="showRules = !showRules">
                            <i class="bi bi-journal-text me-1"></i>Règlement
                        </button>
                    </div>
                </div>
                <div v-if="showRules && jam.rules" class="card-footer bg-light small text-secondary px-4 py-2">
                    <strong>Règlement :</strong> {{ jam.rules }}
                </div>
            </div>

            <div v-if="toast" class="alert alert-info rounded-4 py-2 px-3 small fw-semibold mb-3">
                <i class="bi bi-info-circle-fill me-2"></i>{{ toast }}
            </div>

            <!-- Mobile Switcher -->
            <div class="d-flex d-lg-none gap-2 mb-3">
                <button type="button" class="btn btn-sm flex-fill rounded-pill" :class="mobilePane === 'workspace' ? 'btn-dark' : 'btn-outline-secondary'" @click="mobilePane = 'workspace'">
                    <i class="bi bi-code-slash me-1"></i>Atelier &amp; Projets
                </button>
                <button type="button" class="btn btn-sm flex-fill rounded-pill" :class="mobilePane === 'chat' ? 'btn-primary' : 'btn-outline-primary'" @click="mobilePane = 'chat'">
                    <i class="bi bi-chat-dots-fill me-1"></i>Live Chat ({{ filteredMessages.length }})
                </button>
            </div>

            <!-- Main Split Layout: Studio/Gallery (Left) + Live Chat (Right) -->
            <div class="row g-3">
                <div class="col-lg-8 col-xl-9" :class="{ 'd-none d-lg-block': mobilePane !== 'workspace' }">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 border-bottom">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn fw-semibold" :class="mainTab === 'studio' ? 'btn-dark' : 'btn-outline-secondary'" @click="mainTab = 'studio'">
                                    <i class="bi bi-code-square me-1"></i>Mon Éditeur Live (HTML/CSS/JS)
                                </button>
                                <button type="button" class="btn fw-semibold" :class="mainTab === 'gallery' ? 'btn-dark' : 'btn-outline-secondary'" @click="mainTab = 'gallery'">
                                    <i class="bi bi-grid-fill me-1"></i>Projets &amp; Votes ({{ submissions.length }})
                                </button>
                            </div>

                            <button
                                v-if="mainTab === 'studio'"
                                type="button"
                                class="btn btn-sm btn-success rounded-pill px-3 fw-bold"
                                :disabled="savingSubmission"
                                @click="saveMySubmission"
                            >
                                <span v-if="savingSubmission" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi bi-cloud-upload-fill me-1"></i>Enregistrer ma participation
                            </button>
                        </div>

                        <!-- TAB 1: SECURE CODE STUDIO -->
                        <div v-if="mainTab === 'studio'" class="card-body p-3">
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-md-5">
                                    <input v-model="subTitle" type="text" class="form-control form-control-sm fw-semibold" placeholder="Titre de votre projet..." />
                                </div>
                                <div class="col-md-4">
                                    <input v-model="subDescription" type="text" class="form-control form-control-sm" placeholder="Courte description..." />
                                </div>
                                <div v-if="jam.allow_external_links" class="col-md-3">
                                    <select v-model="subType" class="form-select form-select-sm">
                                        <option value="studio">Éditeur Tech237 (HTML/CSS/JS)</option>
                                        <option value="external">Lien GitHub / Démo externe</option>
                                    </select>
                                </div>
                            </div>

                            <!-- External Links Mode -->
                            <div v-if="subType === 'external' && jam.allow_external_links" class="p-4 rounded-3 bg-light border mb-3">
                                <h6 class="fw-bold mb-3"><i class="bi bi-github me-1"></i>Soumission de projet externe (Backend, Mobile, Fullstack)</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Lien Dépôt GitHub / GitLab</label>
                                        <input v-model="externalRepoUrl" type="url" class="form-control form-control-sm" placeholder="https://github.com/..." />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Lien Démo en ligne (ou Portfolio Tech237)</label>
                                        <input v-model="externalDemoUrl" type="url" class="form-control form-control-sm" placeholder="https://..." />
                                    </div>
                                </div>
                            </div>

                            <!-- Integrated HTML / CSS / JS Editor + Live Sandbox Preview -->
                            <div v-else class="row g-3">
                                <div class="col-xl-6">
                                    <div class="border rounded-3 overflow-hidden bg-dark text-white">
                                        <div class="d-flex align-items-center justify-content-between px-2 py-1 bg-black bg-opacity-50 border-bottom border-secondary">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-sm" :class="codeTab === 'html' ? 'btn-danger' : 'btn-outline-secondary text-white'" @click="codeTab = 'html'">HTML</button>
                                                <button type="button" class="btn btn-sm" :class="codeTab === 'css' ? 'btn-primary' : 'btn-outline-secondary text-white'" @click="codeTab = 'css'">CSS</button>
                                                <button type="button" class="btn btn-sm" :class="codeTab === 'js' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-white'" @click="codeTab = 'js'">JS</button>
                                            </div>
                                            <span class="small text-white-50"><i class="bi bi-shield-lock-fill text-success me-1"></i>Sandbox isolé</span>
                                        </div>
                                        <textarea
                                            v-if="codeTab === 'html'"
                                            v-model="htmlCode"
                                            spellcheck="false"
                                            class="form-control bg-dark text-light border-0 font-monospace small p-3"
                                            style="height: 430px; resize: none; outline: none; box-shadow: none;"
                                            @keydown="handleTabKey($event, htmlCode)"
                                        ></textarea>
                                        <textarea
                                            v-else-if="codeTab === 'css'"
                                            v-model="cssCode"
                                            spellcheck="false"
                                            class="form-control bg-dark text-info border-0 font-monospace small p-3"
                                            style="height: 430px; resize: none; outline: none; box-shadow: none;"
                                            @keydown="handleTabKey($event, cssCode)"
                                        ></textarea>
                                        <textarea
                                            v-else
                                            v-model="jsCode"
                                            spellcheck="false"
                                            class="form-control bg-dark text-warning border-0 font-monospace small p-3"
                                            style="height: 430px; resize: none; outline: none; box-shadow: none;"
                                            @keydown="handleTabKey($event, jsCode)"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="border rounded-3 overflow-hidden h-100 d-flex flex-column bg-white">
                                        <div class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center small fw-semibold">
                                            <span><i class="bi bi-play-circle-fill text-success me-1"></i>Aperçu en direct</span>
                                            <span class="text-muted" style="font-size: 11px;">Exécution sécurisée côté navigateur</span>
                                        </div>
                                        <iframe
                                            :srcdoc="liveSrcDoc"
                                            sandbox="allow-scripts allow-modals"
                                            class="w-100 border-0 flex-grow-1"
                                            style="min-height: 430px; background: #fff;"
                                            title="Aperçu sécurisé Dev Jam"
                                        ></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: SUBMISSIONS GALLERY & VOTING -->
                        <div v-else class="card-body p-3">
                            <div v-if="submissions.length" class="row g-3">
                                <div v-for="sub in submissions" :key="sub.id" class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 d-flex flex-column bg-light bg-opacity-50">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-0">{{ sub.title }}</h6>
                                                <div class="small text-muted">par {{ sub.user?.name || 'Membre' }}</div>
                                            </div>
                                            <span v-if="sub.is_winner" class="badge bg-warning text-dark rounded-pill">
                                                <i class="bi bi-trophy-fill me-1"></i>Gagnant
                                            </span>
                                        </div>

                                        <p v-if="sub.description" class="small text-secondary mb-2">{{ sub.description }}</p>

                                        <div v-if="sub.submission_type === 'studio'" class="mb-2 border rounded-3 overflow-hidden bg-white">
                                            <iframe
                                                :srcdoc="buildSandboxDoc(sub.html_code, sub.css_code, sub.js_code)"
                                                sandbox="allow-scripts"
                                                class="w-100 border-0"
                                                style="height: 200px;"
                                                :title="sub.title"
                                            ></iframe>
                                        </div>
                                        <div v-else class="mb-2 d-flex gap-2 flex-wrap">
                                            <a v-if="sub.external_demo_url" :href="sub.external_demo_url" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Voir la démo
                                            </a>
                                            <a v-if="sub.external_repo_url" :href="sub.external_repo_url" target="_blank" rel="noopener" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-1">
                                                <i class="bi bi-github me-1"></i>Code source
                                            </a>
                                        </div>

                                        <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm rounded-pill px-3"
                                                :class="votedIds.has(sub.id) ? 'btn-danger' : 'btn-outline-danger'"
                                                @click="toggleVote(sub)"
                                            >
                                                <i class="bi bi-heart-fill me-1"></i>{{ sub.votes_count || 0 }}
                                            </button>
                                            <div class="d-flex gap-1">
                                                <button v-if="sub.submission_type === 'studio'" type="button" class="btn btn-sm btn-outline-dark rounded-pill" @click="previewSubmission = sub">
                                                    <i class="bi bi-arrows-fullscreen me-1"></i>Agrandir
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" @click="focusSubmissionChat(sub)">
                                                    <i class="bi bi-chat-dots me-1"></i>Commenter
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-5 text-muted">
                                Aucun projet soumis pour le moment. Soyez le premier dans l'onglet Éditeur Live !
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT PANEL: LIVE CHAT & COMMENTS -->
                <div class="col-lg-4 col-xl-3" :class="{ 'd-none d-lg-block': mobilePane !== 'chat' }">
                    <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column" style="max-height: 620px;">
                        <div class="card-header bg-white p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">
                                    <span class="badge bg-success rounded-circle p-1 me-1">&nbsp;</span>
                                    Live Chat &amp; Commentaires
                                </h6>
                                <button v-if="activeTargetSubmissionId" type="button" class="btn btn-link btn-sm p-0 text-decoration-none" @click="activeTargetSubmissionId = null">
                                    Voir tout
                                </button>
                            </div>
                            <select v-model="activeTargetSubmissionId" class="form-select form-select-sm">
                                <option :value="null">Chat général de la Jam</option>
                                <option v-for="s in submissions" :key="s.id" :value="s.id">
                                    Commentaires : {{ s.title }}
                                </option>
                            </select>
                        </div>

                        <div ref="chatListEl" class="card-body p-3 overflow-auto flex-grow-1" style="min-height: 320px;">
                            <div v-if="!filteredMessages.length" class="text-center text-muted small py-4">
                                Aucun message pour l'instant. Lancez la discussion !
                            </div>
                            <div v-for="m in filteredMessages" :key="m.id" class="mb-2 p-2 rounded-3 bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold small text-dark">{{ m.user?.name || 'Membre' }}</span>
                                    <button
                                        v-if="authUser && (m.user_id === authUser.id || isJamCreator)"
                                        type="button"
                                        class="btn btn-link btn-sm text-danger p-0"
                                        title="Supprimer"
                                        @click="deleteMsg(m)"
                                    >
                                        <i class="bi bi-trash" style="font-size: 11px;"></i>
                                    </button>
                                </div>
                                <div class="small text-secondary" style="white-space: pre-wrap; word-break: break-word;">{{ m.message }}</div>
                            </div>
                        </div>

                        <div class="card-footer bg-white p-2 border-top">
                            <form v-if="authUser" class="d-flex gap-2" @submit.prevent="sendMessage">
                                <input
                                    v-model="chatInput"
                                    type="text"
                                    class="form-control form-control-sm rounded-pill"
                                    placeholder="Écrire un message ou commentaire..."
                                    maxlength="1000"
                                />
                                <button type="submit" class="btn btn-sm btn-primary rounded-circle" :disabled="sendingMessage || !chatInput.trim()">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </form>
                            <div v-else class="text-center small text-muted py-1">
                                <Link href="/connexion" class="fw-semibold">Connectez-vous</Link> pour participer au chat live.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fullscreen Submission Preview Modal -->
            <div v-if="previewSubmission" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center p-3" style="z-index: 2300;" @click="previewSubmission = null">
                <div class="bg-white rounded-4 shadow-lg w-100 overflow-hidden d-flex flex-column" style="max-width: 980px; height: 82vh;" @click.stop>
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0">{{ previewSubmission.title }} — par {{ previewSubmission.user?.name }}</h6>
                        </div>
                        <button type="button" class="btn-close" @click="previewSubmission = null"></button>
                    </div>
                    <iframe
                        :srcdoc="buildSandboxDoc(previewSubmission.html_code, previewSubmission.css_code, previewSubmission.js_code)"
                        sandbox="allow-scripts"
                        class="w-100 border-0 flex-grow-1"
                        :title="previewSubmission.title"
                    ></iframe>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
