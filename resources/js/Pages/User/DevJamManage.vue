<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

const props = defineProps({
    jam: { type: Object, required: true },
    messages: { type: Array, default: () => [] },
    polls: { type: Array, default: () => [] },
    participants: { type: Array, default: () => [] },
    blockedUsers: { type: Array, default: () => [] },
    blockedUserIds: { type: Array, default: () => [] },
    messageReactions: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

function isBlocked(userId) {
    return props.blockedUserIds.includes(userId);
}

function userDisplayName(u) {
    if (!u) return 'Utilisateur';
    return `${(u.name || '').trim()} ${(u.first_name || '').trim()}`.trim() || u.username || 'Membre';
}

function blockUser(userId) {
    router.post(`/espace-utilisateur/dev-jams/${props.jam.slug}/block`, { user_id: userId }, { preserveScroll: true });
}

function unblockUser(userId) {
    router.post(`/espace-utilisateur/dev-jams/${props.jam.slug}/unblock`, { user_id: userId }, { preserveScroll: true });
}

function reactionSummary(messageId) {
    const map = props.messageReactions?.[messageId] || {};
    return Object.entries(map)
        .filter(([, arr]) => Array.isArray(arr) && arr.length > 0)
        .map(([emoji, arr]) => `${emoji} ${arr.length}`);
}

function statusLabel(s) {
    if (s === 'active') return { text: 'En cours', cls: 'bg-success' };
    if (s === 'ended') return { text: 'Terminée', cls: 'bg-danger' };
    if (s === 'suspended') return { text: 'Suspendue', cls: 'bg-dark' };
    if (s === 'upcoming') return { text: 'À venir', cls: 'bg-info text-dark' };
    return { text: s, cls: 'bg-secondary' };
}
</script>

<template>
    <Head :title="`Gestion Jam : ${jam.title} • Espace Utilisateur`" />
    <FrontLayout>
        <div class="container py-5">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <Link href="/espace-utilisateur" class="btn btn-sm btn-outline-secondary rounded-pill mb-2">
                        <i class="bi bi-arrow-left me-1"></i>Retour à mon espace
                    </Link>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 class="fw-bold mb-0">{{ jam.title }}</h2>
                        <span class="badge rounded-pill" :class="statusLabel(jam.effective_status || jam.status).cls">
                            {{ statusLabel(jam.effective_status || jam.status).text }}
                        </span>
                    </div>
                    <p class="text-muted small mb-0 mt-1">{{ jam.description }}</p>
                </div>
                <Link :href="`/dev-jams/${jam.slug}`" class="btn btn-primary rounded-pill px-4 fw-semibold">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Ouvrir le Studio Jam
                </Link>
            </div>

            <!-- Statistics Row -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="small text-muted">En ligne / Participants</div>
                        <div class="fs-4 fw-bold text-primary">{{ stats.online_count || 0 }} / {{ stats.participants_count || 0 }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="small text-muted">Commentaires</div>
                        <div class="fs-4 fw-bold text-dark">{{ stats.messages_count || 0 }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="small text-muted">Réactions (Cœurs + Emojis)</div>
                        <div class="fs-4 fw-bold text-danger">
                            {{ (stats.hearts_count || 0) + (stats.message_reactions_count || 0) }}
                        </div>
                        <div class="text-muted" style="font-size: 11px;">
                            ❤️ {{ stats.hearts_count || 0 }} • Emojis {{ stats.message_reactions_count || 0 }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="small text-muted">Votes finaux (Oui / Non)</div>
                        <div class="fs-5 fw-bold">
                            <span class="text-success">Oui {{ stats.outcome_yes || 0 }}</span>
                            <span class="text-muted mx-1">/</span>
                            <span class="text-danger">Non {{ stats.outcome_no || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Comments & Reactions -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-chat-dots-fill text-primary me-2"></i>Commentaires ({{ messages.length }})
                        </h5>
                        <div v-if="messages.length" class="d-flex flex-column gap-2" style="max-height: 420px; overflow-y: auto;">
                            <div v-for="m in messages" :key="m.id" class="p-3 rounded-3 border bg-light">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                    <strong class="small">{{ userDisplayName(m.user) }}</strong>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted" style="font-size: 11px;">
                                            {{ new Date(m.created_at).toLocaleString('fr-FR') }}
                                        </span>
                                        <button
                                            v-if="m.user && m.user.id !== jam.creator_user_id && !isBlocked(m.user.id)"
                                            type="button"
                                            class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0"
                                            style="font-size: 11px;"
                                            @click="blockUser(m.user.id)"
                                        >
                                            <i class="bi bi-slash-circle me-1"></i>Bloquer
                                        </button>
                                        <button
                                            v-else-if="m.user && isBlocked(m.user.id)"
                                            type="button"
                                            class="btn btn-xs btn-outline-success rounded-pill px-2 py-0"
                                            style="font-size: 11px;"
                                            @click="unblockUser(m.user.id)"
                                        >
                                            Débloquer
                                        </button>
                                    </div>
                                </div>
                                <div class="small text-dark">{{ m.message }}</div>
                                <div v-if="reactionSummary(m.id).length" class="d-flex flex-wrap gap-1 mt-2">
                                    <span
                                        v-for="(badge, idx) in reactionSummary(m.id)"
                                        :key="idx"
                                        class="badge bg-white text-dark border rounded-pill"
                                    >
                                        {{ badge }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-muted small">Aucun commentaire pour cette Jam.</div>
                    </div>

                    <!-- Polls -->
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-bar-chart-fill text-info me-2"></i>Sondages ({{ polls.length }})
                        </h5>
                        <div v-if="polls.length" class="d-flex flex-column gap-3">
                            <div v-for="poll in polls" :key="poll.id" class="p-3 rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="small">{{ poll.question }}</strong>
                                    <span class="badge rounded-pill" :class="poll.is_closed ? 'bg-secondary' : 'bg-success'">
                                        {{ poll.is_closed ? 'Fermé' : 'Ouvert' }} ({{ poll.total_votes }} votes)
                                    </span>
                                </div>
                                <div v-for="opt in poll.options" :key="opt.id" class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>{{ opt.label }}</span>
                                        <span class="fw-semibold">{{ opt.votes_count }} ({{ opt.percent }}%)</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" :style="{ width: `${opt.percent}%` }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-muted small">Aucun sondage créé dans cette Jam.</div>
                    </div>
                </div>

                <!-- Participants & Block/Unblock Management -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h5 class="fw-bold mb-2">
                            <i class="bi bi-people-fill text-primary me-2"></i>Participants ({{ participants.length }})
                        </h5>
                        <p class="small text-muted mb-3">
                            Si vous bloquez une personne, elle n'aura plus accès à <strong>aucune</strong> de vos Dev Jams tant que vous ne la débloquez pas.
                        </p>
                        <div v-if="participants.length" class="d-flex flex-column gap-2">
                            <div
                                v-for="p in participants"
                                :key="p.id"
                                class="d-flex align-items-center justify-content-between p-2 rounded-3 border"
                            >
                                <div class="small fw-semibold">{{ userDisplayName(p) }}</div>
                                <button
                                    v-if="!isBlocked(p.id)"
                                    type="button"
                                    class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                    @click="blockUser(p.id)"
                                >
                                    <i class="bi bi-person-x-fill me-1"></i>Bloquer
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    class="btn btn-sm btn-success rounded-pill px-3"
                                    @click="unblockUser(p.id)"
                                >
                                    <i class="bi bi-person-check-fill me-1"></i>Débloquer
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-muted small">Aucun participant enregistré pour le moment.</div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-shield-x text-danger me-2"></i>Utilisateurs bloqués ({{ blockedUsers.length }})
                        </h5>
                        <div v-if="blockedUsers.length" class="d-flex flex-column gap-2">
                            <div
                                v-for="b in blockedUsers"
                                :key="b.id"
                                class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25"
                            >
                                <div class="small fw-semibold text-dark">
                                    {{ userDisplayName(b.blocked_user) }}
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-success rounded-pill px-3"
                                    @click="unblockUser(b.blocked_user_id)"
                                >
                                    Débloquer
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-muted small">Aucun utilisateur bloqué sur vos Jams.</div>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
