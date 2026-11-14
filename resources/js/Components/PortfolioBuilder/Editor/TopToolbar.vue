<script setup>
defineProps({
    site: { type: Object, required: true },
    activePage: { type: Object, default: null },
    editingMode: { type: String, default: 'page' },
    viewport: { type: String, default: 'desktop' },
    showLeftSidebar: { type: Boolean, default: true },
    showRightInspector: { type: Boolean, default: true },
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
    'toggle-left',
    'toggle-right',
    'undo',
    'redo',
    'save',
    'publish',
    'unpublish',
    'restore-revision',
    'open-seo',
    'open-guide',
]);
</script>

<template>
    <header class="bg-dark text-white px-3 py-2 d-flex align-items-center justify-content-between gap-2 border-bottom border-secondary flex-wrap">
        <!-- Left: Exit + Toggle Left Panel + Site/Page -->
        <div class="d-flex align-items-center gap-2">
            <a
                href="/espace-utilisateur?section=portfolio"
                class="btn btn-sm btn-outline-light rounded-pill px-2 px-md-3"
                title="Quitter l'éditeur"
            >
                <i class="bi bi-arrow-left me-1"></i><span class="d-none d-sm-inline">Dashboard</span>
            </a>

            <button
                type="button"
                class="btn btn-sm"
                :class="showLeftSidebar ? 'btn-primary' : 'btn-outline-secondary text-white'"
                title="Afficher / Masquer le panneau de gauche"
                @click="emit('toggle-left')"
            >
                <i class="bi bi-layout-sidebar"></i>
            </button>

            <div>
                <div class="fw-bold small lh-1">
                    {{ site.name }}
                    <span class="badge bg-info text-dark ms-1" style="font-size: 10px;">{{ site.site_type === 'multipage' ? 'Multi-Pages' : 'One-Page' }}</span>
                </div>
                <div class="text-info" style="font-size: 11px;">
                    <span v-if="editingMode === 'header'">Theme Builder : Header Global</span>
                    <span v-else-if="editingMode === 'footer'">Theme Builder : Footer Global</span>
                    <span v-else>Page : {{ activePage?.title || 'Accueil' }}</span>
                    <span v-if="isDirty" class="text-warning ms-2">● Non enregistré</span>
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
                <button type="button" class="btn" :class="viewport === 'desktop' ? 'btn-primary' : 'btn-outline-secondary text-white'" title="Ordinateur" @click="emit('update:viewport', 'desktop')">
                    <i class="bi bi-laptop"></i>
                </button>
                <button type="button" class="btn" :class="viewport === 'tablet' ? 'btn-primary' : 'btn-outline-secondary text-white'" title="Tablette" @click="emit('update:viewport', 'tablet')">
                    <i class="bi bi-tablet"></i>
                </button>
                <button type="button" class="btn" :class="viewport === 'mobile' ? 'btn-primary' : 'btn-outline-secondary text-white'" title="Mobile" @click="emit('update:viewport', 'mobile')">
                    <i class="bi bi-phone"></i>
                </button>
            </div>
        </div>

        <!-- Right: SEO + Guide + Revisions + Preview + Save + Publish + Toggle Right Panel -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill" title="SEO & Carte de partage" @click="emit('open-seo')">
                <i class="bi bi-search me-1"></i><span class="d-none d-lg-inline">SEO</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill" title="Guide d'utilisation" @click="emit('open-guide')">
                <i class="bi bi-question-circle"></i>
            </button>

            <div v-if="editingMode === 'page' && revisions.length" class="dropdown">
                <button type="button" class="btn btn-sm btn-outline-secondary text-white dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-clock-history"></i>
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
                class="btn btn-sm btn-outline-info rounded-pill px-2 px-md-3"
            >
                <i class="bi bi-eye me-1"></i><span class="d-none d-md-inline">Aperçu</span>
            </a>

            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold" :disabled="saving" @click="emit('save')">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-save me-1"></i><span class="d-none d-sm-inline">Enregistrer</span>
            </button>

            <button v-if="site.is_published" type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2" title="Dépublier le site" @click="emit('unpublish')">
                Dépublier
            </button>
            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" :disabled="publishing" @click="emit('publish')">
                <span v-if="publishing" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-rocket-takeoff me-1"></i>Publier
            </button>

            <button
                type="button"
                class="btn btn-sm"
                :class="showRightInspector ? 'btn-primary' : 'btn-outline-secondary text-white'"
                title="Afficher / Masquer l'inspecteur de droite"
                @click="emit('toggle-right')"
            >
                <i class="bi bi-layout-sidebar-reverse"></i>
            </button>
        </div>
    </header>
</template>
