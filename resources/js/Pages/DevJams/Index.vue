<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';

const props = defineProps({
    jams: { type: Array, default: () => [] },
    popularJams: { type: Array, default: () => [] },
    modeFilter: { type: String, default: 'all' },
    counts: { type: Object, default: () => ({ all: 0, official: 0, community: 0 }) },
});

const liveJams = ref(props.jams.map((j) => ({ ...j })));
const livePopularJams = ref(props.popularJams.map((j) => ({ ...j })));
const openShareId = ref(null);
const copiedSlug = ref(null);
let statsTimer = null;

function statusBadge(status) {
    if (status === 'active') return { label: 'En cours', cls: 'bg-success' };
    if (status === 'upcoming') return { label: 'À venir', cls: 'bg-info text-dark' };
    if (status === 'voting') return { label: 'Votes ouverts', cls: 'bg-warning text-dark' };
    if (status === 'ended') return { label: 'Jam terminée', cls: 'bg-danger' };
    if (status === 'suspended') return { label: 'Jam suspendue', cls: 'bg-dark' };
    return { label: status, cls: 'bg-secondary' };
}

function formatDate(dt) {
    if (!dt) return '';
    return new Date(dt).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function creatorFullName(jam) {
    if (jam.creator_user) {
        const nom = (jam.creator_user.name || '').trim();
        const prenom = (jam.creator_user.first_name || '').trim();
        return `${nom} ${prenom}`.trim() || jam.creator_user.username || 'Membre Tech237';
    }
    if (jam.creator_admin?.name) {
        return jam.creator_admin.name;
    }
    return 'Équipe Tech237';
}

function creatorInitials(jam) {
    if (jam.creator_user) {
        const nom = (jam.creator_user.name || '').trim();
        const prenom = (jam.creator_user.first_name || '').trim();
        if (nom && prenom) {
            return (nom.charAt(0) + prenom.charAt(0)).toUpperCase();
        }
        const parts = nom.split(/\s+/).filter(Boolean);
        if (parts.length >= 2) {
            return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
        }
        return nom.slice(0, 2).toUpperCase() || 'TJ';
    }
    const adminName = (jam.creator_admin?.name || 'Tech237').trim();
    return adminName.slice(0, 2).toUpperCase();
}

function creatorAvatarUrl(jam) {
    const av = jam.creator_user?.avatar;
    if (!av) return null;
    if (av.startsWith('http://') || av.startsWith('https://') || av.startsWith('/')) return av;
    return `/storage/${av}`;
}

function shareUrlFor(jam) {
    if (typeof window === 'undefined') return `/dev-jams/${jam.slug}`;
    return `${window.location.origin}/dev-jams/${jam.slug}`;
}

function shareSocial(jam, platform) {
    const url = shareUrlFor(jam);
    const text = `Rejoins la Dev Jam "${jam.title}" sur Tech237 !`;
    openShareId.value = null;

    if (platform === 'copy') {
        navigator.clipboard?.writeText(url).then(() => {
            copiedSlug.value = jam.slug;
            setTimeout(() => {
                if (copiedSlug.value === jam.slug) copiedSlug.value = null;
            }, 2000);
        });
        return;
    }
    const targets = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
        twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`,
        whatsapp: `https://wa.me/?text=${encodeURIComponent(`${text} ${url}`)}`,
    };
    if (targets[platform]) {
        window.open(targets[platform], '_blank', 'noopener,noreferrer');
    }
}

async function pollLiveStats() {
    try {
        const res = await apiClient.get('/dev-jams/live-stats');
        const stats = res.data?.stats || {};
        const applyStats = (list) =>
            list.map((jam) => {
                const s = stats[jam.id];
                return s ? { ...jam, ...s } : jam;
            });
        liveJams.value = applyStats(liveJams.value);
        livePopularJams.value = applyStats(livePopularJams.value);
    } catch {
        // ignore transient network errors
    }
}

onMounted(() => {
    statsTimer = setInterval(pollLiveStats, 4000);
});

onUnmounted(() => {
    clearInterval(statsTimer);
});
</script>

<template>
    <Head title="Dev Jams Studio • Tech237" />
    <FrontLayout>
        <div class="container py-5">
            <!-- Hero Header -->
            <div class="p-4 p-md-5 rounded-4 mb-4 text-white shadow-sm" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #4f46e5 100%);">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1 mb-2 fw-bold">
                            <i class="bi bi-code-slash me-1"></i>Dev Jam Studio Tech237
                        </span>
                        <h1 class="fw-bold mb-2">Codez, partagez &amp; échangez en direct</h1>
                        <p class="mb-0 opacity-75" style="max-width: 680px;">
                            Participez aux <strong>Jams Officielles Tech237</strong> ou lancez votre propre <strong>Jam Communautaire</strong> avec éditeur HTML/CSS/JS sécurisé, aperçu live et chat en temps réel.
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <Link href="/dev-jams/creer" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm">
                            <i class="bi bi-plus-circle-fill text-primary me-1"></i>Créer une Jam Communautaire
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Most Popular Ongoing Jams Highlight -->
            <div v-if="livePopularJams.length > 1" class="mb-4 p-4 rounded-4 bg-white border shadow-sm">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-fire text-danger me-2"></i>Jams les plus populaires en cours
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">
                        <i class="bi bi-broadcast me-1"></i>Mis à jour en temps réel
                    </span>
                </div>
                <div class="row g-3">
                    <div v-for="(pjam, idx) in livePopularJams" :key="`pop-${pjam.id}`" class="col-md-4">
                        <div class="p-3 rounded-4 border h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(145deg, #f8fafc, #eef2ff);">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-warning text-dark rounded-pill fw-bold">#{{ idx + 1 }} Populaire</span>
                                    <span class="small fw-semibold text-success">
                                        <i class="bi bi-people-fill me-1"></i>{{ pjam.online_count || 0 }} en ligne
                                    </span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ pjam.title }}</h6>
                                <div class="small text-muted mb-2">Par {{ creatorFullName(pjam) }}</div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                <div class="small text-muted d-flex gap-2">
                                    <span><i class="bi bi-chat-dots me-1"></i>{{ pjam.messages_count || 0 }}</span>
                                    <span><i class="bi bi-heart-fill text-danger me-1"></i>{{ pjam.hearts_count || 0 }}</span>
                                </div>
                                <Link
                                    v-if="!pjam.is_blocked_for_me"
                                    :href="`/dev-jams/${pjam.slug}`"
                                    class="btn btn-xs btn-primary rounded-pill px-3 fw-semibold"
                                >
                                    Rejoindre
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mode Tabs -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <Link
                    href="/dev-jams?mode=all"
                    class="btn rounded-pill px-3 fw-semibold btn-sm"
                    :class="modeFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary'"
                >
                    Toutes les Jams ({{ counts.all }})
                </Link>
                <Link
                    href="/dev-jams?mode=official"
                    class="btn rounded-pill px-3 fw-semibold btn-sm"
                    :class="modeFilter === 'official' ? 'btn-danger' : 'btn-outline-danger'"
                >
                    <i class="bi bi-patch-check-fill me-1"></i>Jams Officielles Tech237 ({{ counts.official }})
                </Link>
                <Link
                    href="/dev-jams?mode=community"
                    class="btn rounded-pill px-3 fw-semibold btn-sm"
                    :class="modeFilter === 'community' ? 'btn-primary' : 'btn-outline-primary'"
                >
                    <i class="bi bi-people-fill me-1"></i>Jams Communautaires ({{ counts.community }})
                </Link>
            </div>

            <!-- Jams Grid -->
            <div v-if="liveJams.length" class="row g-4">
                <div v-for="jam in liveJams" :key="jam.id" class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <span
                                    class="badge rounded-pill"
                                    :class="jam.mode === 'official' ? 'bg-danger' : 'bg-primary'"
                                >
                                    <i class="bi me-1" :class="jam.mode === 'official' ? 'bi-patch-check-fill' : 'bi-people-fill'"></i>
                                    {{ jam.mode === 'official' ? 'Officielle Tech237' : 'Communautaire' }}
                                </span>
                                <span class="badge rounded-pill" :class="statusBadge(jam.effective_status || jam.status).cls">
                                    {{ statusBadge(jam.effective_status || jam.status).label }}
                                </span>
                            </div>

                            <!-- Creator Profile (Photo or Nom+Prénom Initials) -->
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <img
                                    v-if="creatorAvatarUrl(jam)"
                                    :src="creatorAvatarUrl(jam)"
                                    :alt="creatorFullName(jam)"
                                    class="rounded-circle object-fit-cover border"
                                    style="width: 38px; height: 38px;"
                                />
                                <div
                                    v-else
                                    class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                    style="width: 38px; height: 38px; background: linear-gradient(135deg, #4f46e5, #0ea5e9); font-size: 13px;"
                                >
                                    {{ creatorInitials(jam) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-dark small text-truncate">{{ creatorFullName(jam) }}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <i class="bi bi-broadcast text-success me-1"></i>{{ jam.online_count || 0 }} connecté(s)
                                    </div>
                                </div>
                            </div>

                            <h5 class="fw-bold text-dark mb-1">{{ jam.title }}</h5>
                            <div v-if="jam.theme" class="small text-primary fw-semibold mb-2">
                                <i class="bi bi-stars me-1"></i>Thème : {{ jam.theme }}
                            </div>
                            <p class="small text-muted mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ jam.description }}
                            </p>

                            <div v-if="jam.effective_status === 'ended'" class="alert alert-secondary py-1 px-2 small mb-3">
                                <i class="bi bi-flag-fill me-1"></i>Cette Jam est terminée.
                            </div>
                            <div v-else-if="jam.effective_status === 'suspended'" class="alert alert-danger py-1 px-2 small mb-3">
                                <i class="bi bi-shield-lock-fill me-1"></i>Jam suspendue par le créateur.
                            </div>

                            <div v-if="jam.prize_text" class="small bg-warning bg-opacity-10 border border-warning rounded-3 px-2 py-1 mb-3 text-dark">
                                <i class="bi bi-trophy-fill text-warning me-1"></i>{{ jam.prize_text }}
                            </div>

                            <div class="small text-muted mb-3">
                                <div><i class="bi bi-calendar3 me-1"></i>Du {{ formatDate(jam.starts_at) }}</div>
                                <div><i class="bi bi-flag-fill me-1"></i>Au {{ formatDate(jam.ends_at) }}</div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-3 border-top gap-2">
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <span title="Projets soumis"><i class="bi bi-file-code me-1"></i>{{ jam.submissions_count || 0 }}</span>
                                    <span title="Commentaires en direct"><i class="bi bi-chat-dots me-1"></i>{{ jam.messages_count || 0 }}</span>
                                    <span title="Réactions / Cœurs"><i class="bi bi-heart-fill text-danger me-1"></i>{{ jam.hearts_count || 0 }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1 position-relative">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-2"
                                        title="Partager sur les réseaux sociaux"
                                        @click="openShareId = openShareId === jam.id ? null : jam.id"
                                    >
                                        <i class="bi bi-share-fill"></i>
                                    </button>
                                    <div
                                        v-if="openShareId === jam.id"
                                        class="position-absolute bottom-100 end-0 mb-2 bg-white border rounded-3 shadow p-2 d-flex flex-column gap-1"
                                        style="z-index: 30; min-width: 165px;"
                                    >
                                        <button type="button" class="btn btn-sm btn-light text-start" @click="shareSocial(jam, 'facebook')">
                                            <i class="bi bi-facebook text-primary me-2"></i>Facebook
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light text-start" @click="shareSocial(jam, 'twitter')">
                                            <i class="bi bi-twitter-x me-2"></i>Twitter / X
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light text-start" @click="shareSocial(jam, 'whatsapp')">
                                            <i class="bi bi-whatsapp text-success me-2"></i>WhatsApp
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light text-start" @click="shareSocial(jam, 'copy')">
                                            <i class="bi bi-link-45deg text-info me-2"></i>
                                            {{ copiedSlug === jam.slug ? 'Lien copié !' : 'Copier le lien' }}
                                        </button>
                                    </div>
                                    <span v-if="jam.is_blocked_for_me" class="badge bg-danger rounded-pill px-2 py-2">
                                        Bloqué
                                    </span>
                                    <Link v-else :href="`/dev-jams/${jam.slug}`" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold">
                                        Entrer <i class="bi bi-arrow-right ms-1"></i>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-5 bg-white rounded-4 border shadow-sm">
                <i class="bi bi-code-square fs-1 text-primary d-block mb-2"></i>
                <h5 class="fw-bold">Aucune Dev Jam pour ce filtre</h5>
                <p class="text-muted small mb-3">Soyez le premier à lancer une Jam Communautaire sur Tech237 !</p>
                <Link href="/dev-jams/creer" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg me-1"></i>Créer une Jam Communautaire
                </Link>
            </div>
        </div>
    </FrontLayout>
</template>
