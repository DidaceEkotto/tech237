<script setup>
import { Head, Link } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

const props = defineProps({
    jams: { type: Array, default: () => [] },
    modeFilter: { type: String, default: 'all' },
    counts: { type: Object, default: () => ({ all: 0, official: 0, community: 0 }) },
});

function statusBadge(status) {
    if (status === 'active') return { label: 'En cours', cls: 'bg-success' };
    if (status === 'upcoming') return { label: 'À venir', cls: 'bg-info text-dark' };
    if (status === 'voting') return { label: 'Votes ouverts', cls: 'bg-warning text-dark' };
    if (status === 'ended') return { label: 'Terminée', cls: 'bg-secondary' };
    return { label: status, cls: 'bg-secondary' };
}

function formatDate(dt) {
    if (!dt) return '';
    return new Date(dt).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
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
            <div v-if="jams.length" class="row g-4">
                <div v-for="jam in jams" :key="jam.id" class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
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

                            <h5 class="fw-bold text-dark mb-1">{{ jam.title }}</h5>
                            <div v-if="jam.theme" class="small text-primary fw-semibold mb-2">
                                <i class="bi bi-stars me-1"></i>Thème : {{ jam.theme }}
                            </div>
                            <p class="small text-muted mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ jam.description }}
                            </p>

                            <div v-if="jam.prize_text" class="small bg-warning bg-opacity-10 border border-warning rounded-3 px-2 py-1 mb-3 text-dark">
                                <i class="bi bi-trophy-fill text-warning me-1"></i>{{ jam.prize_text }}
                            </div>

                            <div class="small text-muted mb-3">
                                <div><i class="bi bi-calendar3 me-1"></i>Du {{ formatDate(jam.starts_at) }}</div>
                                <div><i class="bi bi-flag-fill me-1"></i>Au {{ formatDate(jam.ends_at) }}</div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                <div class="small text-muted">
                                    <span class="me-2"><i class="bi bi-file-code me-1"></i>{{ jam.submissions_count || 0 }}</span>
                                    <span><i class="bi bi-chat-dots me-1"></i>{{ jam.messages_count || 0 }}</span>
                                </div>
                                <Link :href="`/dev-jams/${jam.slug}`" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold">
                                    Entrer dans la Jam <i class="bi bi-arrow-right ms-1"></i>
                                </Link>
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
