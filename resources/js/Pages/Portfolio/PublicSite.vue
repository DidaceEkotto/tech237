<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import SectionNode from '@/Components/PortfolioBuilder/Nodes/SectionNode.vue';
import AmbientEffectsLayer from '@/Components/PortfolioBuilder/Modules/AmbientEffectsLayer.vue';

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

const loaderVisible = ref(false);

const seo = computed(() => props.site.global_settings?.seo || {});

const siteStats = computed(() => ({
    views_count: props.site.views_count || 0,
    unique_visitors_count: props.site.unique_visitors_count || 0,
}));

function findModuleByType(type) {
    for (const sec of props.tree?.sections || []) {
        for (const row of sec.rows || []) {
            for (const col of row.columns || []) {
                for (const mod of col.modules || []) {
                    if (mod.type === type) return mod;
                }
            }
        }
    }
    return null;
}

const pageLoaderModule = computed(() => findModuleByType('page_loader'));
const ambientModule = computed(() => findModuleByType('ambient_particles'));

onMounted(() => {
    if (pageLoaderModule.value?.content?.enabled) {
        loaderVisible.value = true;
        setTimeout(() => {
            loaderVisible.value = false;
        }, Number(pageLoaderModule.value.content.durationMs || 1200));
    }
});
</script>

<template>
    <Head :title="`${seo.title || activePage.title} — ${site.name}`">
        <meta v-if="seo.description" name="description" :content="seo.description" />
        <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />
        <meta v-if="seo.og_image" property="og:image" :content="seo.og_image" />
        <link v-if="seo.favicon" rel="icon" :href="seo.favicon" />
    </Head>

    <!-- Page Loader Overlay -->
    <div
        v-if="loaderVisible && pageLoaderModule"
        class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center"
        :style="{
            backgroundColor: pageLoaderModule.content?.bgColor || '#0f172a',
            color: pageLoaderModule.content?.textColor || '#ffffff',
            zIndex: 3000,
        }"
    >
        <img v-if="pageLoaderModule.content?.logoUrl" :src="pageLoaderModule.content.logoUrl" alt="Logo" class="rounded-circle mb-3" style="width: 72px; height: 72px; object-fit: cover;" />
        <div class="spinner-border mb-2"></div>
        <div class="small fw-semibold">{{ pageLoaderModule.content?.text || 'Chargement...' }}</div>
    </div>

    <!-- Ambient Particles + Mouse Spotlight + Tech Ring Cursor -->
    <AmbientEffectsLayer
        v-if="ambientModule"
        :effect="ambientModule.content?.effect || 'none'"
        :mouse-spotlight="!!ambientModule.content?.mouseSpotlight"
        :tech-ring-cursor="!!ambientModule.content?.techRingCursor"
    />

    <!-- overflow-x: clip preserves position: sticky on navbars -->
    <div class="min-vh-100 bg-white d-flex flex-column" style="overflow-x: clip;">
        <div
            v-if="isPreview"
            class="bg-warning-subtle text-dark border-bottom px-3 py-2 d-flex align-items-center justify-content-between small"
        >
            <div>
                <i class="bi bi-eye-fill me-2"></i>
                <strong>Mode Aperçu Brouillon</strong> — Modifications non encore publiées.
            </div>
            <a :href="`/espace-utilisateur/portfolio-builder/sites/${site.id}/editor`" class="btn btn-sm btn-dark rounded-pill px-3">
                Retour à l'Éditeur
            </a>
        </div>

        <div v-if="headerPart?.tree?.sections?.length">
            <SectionNode
                v-for="sec in headerPart.tree.sections"
                :key="sec.id"
                :section="sec"
                :is-editor="false"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :active-page-slug="activePage.slug"
                :site-type="site.site_type || 'onepage'"
                :site-stats="siteStats"
                :hydrated-data="hydratedData"
            />
        </div>

        <main class="flex-grow-1">
            <SectionNode
                v-for="sec in (tree.sections || [])"
                :key="sec.id"
                :section="sec"
                :is-editor="false"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :active-page-slug="activePage.slug"
                :site-type="site.site_type || 'onepage'"
                :site-stats="siteStats"
                :hydrated-data="hydratedData"
            />
        </main>

        <div v-if="footerPart?.tree?.sections?.length">
            <SectionNode
                v-for="sec in footerPart.tree.sections"
                :key="sec.id"
                :section="sec"
                :is-editor="false"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :active-page-slug="activePage.slug"
                :site-type="site.site_type || 'onepage'"
                :site-stats="siteStats"
                :hydrated-data="hydratedData"
            />
        </div>

        <!-- Mandatory Tech237 Powered By Footer -->
        <footer class="border-top bg-light py-3 px-4 text-center small text-secondary">
            <a href="https://tech237.com" target="_blank" rel="noopener" class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark fw-semibold">
                <img src="/assets/images/logo.png" alt="Tech237" style="height: 22px; width: auto;" onerror="this.style.display='none'" />
                <span>Propulsé par <strong class="text-primary">tech237.com</strong></span>
            </a>
        </footer>
    </div>
</template>
