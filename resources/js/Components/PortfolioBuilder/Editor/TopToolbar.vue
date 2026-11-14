<script setup>
defineProps({
    site: { type: Object, required: true },
    activePage: { type: Object, default: null },
    editingMode: { type: String, default: 'page' }, // page | header | footer
    viewport: { type: String, default: 'desktop' },
    canUndo: { type: Boolean, default: false },
    canRedo: { type: Boolean, default: false },
    isDirty: { type: Boolean, default: false },
    saving: { type: Boolean, default: false },
    publishing: { type: Boolean, default: false },
    revisions: { type: Array, default: () => [] },
    portfolioUser: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
    'update:viewport',
    'undo',
    'redo',
    'save',
    'publish',
    'restore-revision',
    'switch-mode',
]);
</script>

<template>
    <header class="bg-dark text-white px-3 py-2 d-flex align-items-center justify-content-between gap-2 border-bottom border-secondary flex-wrap">
        <!-- Left: Exit + Brand + Site/Page -->
        <div class="d-flex align-items-center gap-2">
            <a
                href="/espace-utilisateur?section=portfolio"
                class="btn btn-sm btn-outline-light rounded-pill px-3"
                title="Quitter l'éditeur"
            >
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
            <div class="vr bg-secondary mx-1"></div>
            <div>
                <div class="fw-bold small lh-1">{{ site.name }}</div>
                <div class="text-info" style="font-size: 11px;">
                    <span v-if="editingMode === 'header'">Theme Builder : Header Global</span>
                    <span v-else-if="editingMode === 'footer'">Theme Builder : Footer Global</span>
                    <span v-else>Page : {{ activePage?.title || 'Accueil' }}</span>
                    <span v-if="isDirty" class="text-warning ms-2">● Modifications non enregistrées</span>
                </div>
            </div>
        </div>

        <!-- Center: Undo/Redo + Responsive Switcher -->
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary text-white" :disabled="!canUndo" title="Annuler" @click="emit('undo')">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary text-white" :disabled="!canRedo" title="Rétablir" @click="emit('redo')">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>

            <div class="btn-group btn-group-sm">
                <button
                    type="button"
                    class="btn"
                    :class="viewport === 'desktop' ? 'btn-primary' : 'btn-outline-secondary text-white'"
                    title="Ordinateur"
                    @click="emit('update:viewport', 'desktop')"
                >
                    <i class="bi bi-laptop"></i>
                </button>
                <button
                    type="button"
                    class="btn"
                    :class="viewport === 'tablet' ? 'btn-primary' : 'btn-outline-secondary text-white'"
                    title="Tablette"
                    @click="emit('update:viewport', 'tablet')"
                >
                    <i class="bi bi-tablet"></i>
                </button>
                <button
                    type="button"
                    class="btn"
                    :class="viewport === 'mobile' ? 'btn-primary' : 'btn-outline-secondary text-white'"
                    title="Mobile"
                    @click="emit('update:viewport', 'mobile')"
                >
                    <i class="bi bi-phone"></i>
                </button>
            </div>
        </div>

        <!-- Right: Revisions + Preview + Save + Publish -->
        <div class="d-flex align-items-center gap-2">
            <div v-if="editingMode === 'page' && revisions.length" class="dropdown">
                <button type="button" class="btn btn-sm btn-outline-secondary text-white dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-clock-history me-1"></i>Historique
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="max-height: 260px; overflow-y: auto;">
                    <li v-for="rev in revisions" :key="rev.id">
                        <button type="button" class="dropdown-item small" @click="emit('restore-revision', rev.id)">
                            <div class="fw-semibold">{{ rev.label || 'Sauvegarde' }}</div>
                            <small class="text-muted">{{ new Date(rev.created_at).toLocaleString() }}</small>
                        </button>
                    </li>
                </ul>
            </div>

            <a
                v-if="portfolioUser?.username"
                :href="`/@${portfolioUser.username}?preview_site=${site.id}`"
                target="_blank"
                class="btn btn-sm btn-outline-info rounded-pill px-3"
            >
                <i class="bi bi-eye me-1"></i>Prévisualiser
            </a>

            <button
                type="button"
                class="btn btn-sm btn-light rounded-pill px-3 fw-semibold"
                :disabled="saving"
                @click="emit('save')"
            >
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-save me-1"></i>Enregistrer
            </button>

            <button
                type="button"
                class="btn btn-sm btn-success rounded-pill px-3 fw-bold"
                :disabled="publishing"
                @click="emit('publish')"
            >
                <span v-if="publishing" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-rocket-takeoff me-1"></i>Publier
            </button>
        </div>
    </header>
</template>
