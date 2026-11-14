<script setup>
import { Head } from '@inertiajs/vue3';
import SectionNode from '@/Components/PortfolioBuilder/Nodes/SectionNode.vue';

const props = defineProps({
    portfolioUser: { type: Object, required: true },
    site: { type: Object, required: true },
    pages: { type: Array, default: () => [] },
    activePage: { type: Object, required: true },
    tree: { type: Object, required: true },
    headerPart: { type: Object, default: null },
    footerPart: { type: Object, default: null },
    hydratedData: { type: Object, default: () => ({}) },
    isOwner: { type: Boolean, default: false },
    isPreview: { type: Boolean, default: false },
});
</script>

<template>
    <Head :title="`${activePage.meta_title || activePage.title} — ${site.name}`" />

    <div class="min-vh-100 bg-white">
        <!-- Preview Banner for Owner -->
        <div
            v-if="isPreview"
            class="bg-warning-subtle text-dark border-bottom px-3 py-2 d-flex align-items-center justify-content-between small"
        >
            <div>
                <i class="bi bi-eye-fill me-2"></i>
                <strong>Mode Aperçu Brouillon</strong> — Vous visualisez les modifications non encore publiées.
            </div>
            <a :href="`/espace-utilisateur/portfolio-builder/sites/${site.id}/editor`" class="btn btn-sm btn-dark rounded-pill px-3">
                Retour à l'Éditeur
            </a>
        </div>

        <!-- Global Header ThemePart -->
        <div v-if="headerPart?.tree?.sections?.length">
            <SectionNode
                v-for="sec in headerPart.tree.sections"
                :key="sec.id"
                :section="sec"
                :is-editor="false"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :hydrated-data="hydratedData"
            />
        </div>

        <!-- Active Page Sections -->
        <main>
            <SectionNode
                v-for="sec in (tree.sections || [])"
                :key="sec.id"
                :section="sec"
                :is-editor="false"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :hydrated-data="hydratedData"
            />
        </main>

        <!-- Global Footer ThemePart -->
        <div v-if="footerPart?.tree?.sections?.length">
            <SectionNode
                v-for="sec in footerPart.tree.sections"
                :key="sec.id"
                :section="sec"
                :is-editor="false"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :hydrated-data="hydratedData"
            />
        </div>
    </div>
</template>
