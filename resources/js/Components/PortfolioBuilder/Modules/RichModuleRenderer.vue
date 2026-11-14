<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import NavbarAccordionModule from './NavbarAccordionModule.vue';
import HeroShowcaseModule from './HeroShowcaseModule.vue';
import AboutModule from './AboutModule.vue';
import GalleryModule from './GalleryModule.vue';
import VideoModule from './VideoModule.vue';
import ContactFormModule from './ContactFormModule.vue';
import SliderModule from './SliderModule.vue';
import CvBuilderModule from './CvBuilderModule.vue';
import ExtraModulesRenderer from './ExtraModulesRenderer.vue';
import { resolveResponsiveStyles, isNodeHidden } from '@/Composables/PortfolioBuilder/useResponsiveStyles';
import { ensureFontLoaded } from '@/Composables/PortfolioBuilder/fontsAndAnimations';

const props = defineProps({
    module: { type: Object, required: true },
    viewport: { type: String, default: 'desktop' },
    isEditor: { type: Boolean, default: false },
    portfolioUser: { type: Object, default: () => ({}) },
    pages: { type: Array, default: () => [] },
    activePageSlug: { type: String, default: '' },
    siteType: { type: String, default: 'onepage' },
    siteStats: { type: Object, default: () => ({ views_count: 0, unique_visitors_count: 0 }) },
    hydratedData: { type: Object, default: () => ({}) },
});

const openFaqIdx = ref(0);

const hidden = computed(() => isNodeHidden(props.module.advanced, props.viewport));
const computedStyle = computed(() => resolveResponsiveStyles(props.module.styles, props.viewport));

onMounted(() => {
    if (computedStyle.value?.fontFamily) ensureFontLoaded(computedStyle.value.fontFamily);
});
watch(
    () => computedStyle.value?.fontFamily,
    (f) => {
        if (f) ensureFontLoaded(f);
    }
);

const textAnimClass = computed(() => {
    const a = props.module.advanced?.textAnimation || 'none';
    return a !== 'none' ? `pb-anim-${a}` : '';
});

const imgAnimClass = computed(() => {
    const a = props.module.advanced?.imageAnimation || 'none';
    return a !== 'none' ? `pb-img-anim-${a}` : '';
});

const scopedCustomCss = computed(() => {
    const raw = (props.module.advanced?.customCss || '').trim();
    if (!raw) return '';
    return `#pb-mod-${props.module.id} { ${raw} }`;
});

const dynamicItems = computed(() => {
    const src = props.module.content?.source || 'projects';
    const max = Number(props.module.content?.maxItems || 6);
    if (src === 'projects') return (props.hydratedData?.projects || []).slice(0, max);
    if (src === 'pocs') return (props.hydratedData?.pocs || []).slice(0, max);
    if (src === 'badges') return (props.hydratedData?.badges || []).slice(0, max);
    if (src === 'forms') return (props.hydratedData?.publicForms || []).slice(0, max);
    if (src === 'monitors') return (props.hydratedData?.publicMonitors || []).slice(0, max);
    return [];
});

const EXTRA_TYPES = [
    'whatsapp', 'paypal', 'vimeo', 'github_projects', 'map_embed',
    'visitor_counter', 'back_to_top', 'social_share', 'footer_pro', 'parallax',
];
</script>

<template>
    <div
        v-show="!hidden || isEditor"
        :id="module.advanced?.customId || `pb-mod-${module.id}`"
        class="pb-module-inner"
        :class="[
            module.advanced?.cssClass || '',
            textAnimClass,
            { 'opacity-50': hidden && isEditor }
        ]"
        :style="computedStyle"
    >
        <!-- Real-time Scoped Custom CSS -->
        <component v-if="scopedCustomCss" :is="'style'">{{ scopedCustomCss }}</component>

        <NavbarAccordionModule
            v-if="module.type === 'navbar_accordion'"
            :content="module.content"
            :portfolio-user="portfolioUser"
            :pages="pages"
            :active-page-slug="activePageSlug"
            :site-type="siteType"
            :is-editor="isEditor"
        />

        <HeroShowcaseModule
            v-else-if="module.type === 'hero'"
            :content="module.content"
            :is-editor="isEditor"
        />

        <AboutModule
            v-else-if="module.type === 'about'"
            :content="module.content"
            :portfolio-user="portfolioUser"
        />

        <GalleryModule
            v-else-if="module.type === 'gallery'"
            :content="module.content"
        />

        <VideoModule
            v-else-if="module.type === 'video'"
            :content="module.content"
        />

        <SliderModule
            v-else-if="module.type === 'slider'"
            :content="module.content"
            :is-editor="isEditor"
        />

        <CvBuilderModule
            v-else-if="module.type === 'cv_builder'"
            :content="module.content"
            :portfolio-user="portfolioUser"
            :hydrated-data="hydratedData"
            :is-editor="isEditor"
        />

        <ContactFormModule
            v-else-if="module.type === 'contact'"
            :content="module.content"
            :portfolio-user="portfolioUser"
            :is-editor="isEditor"
        />

        <ExtraModulesRenderer
            v-else-if="EXTRA_TYPES.includes(module.type)"
            :module="module"
            :is-editor="isEditor"
            :site-stats="siteStats"
        />

        <!-- Page Loader / Ambient Particles indicator in Editor -->
        <div v-else-if="module.type === 'page_loader' && isEditor" class="p-3 rounded-4 border border-dashed bg-light text-center small">
            <i class="bi bi-hourglass-split text-primary me-1"></i>
            <strong>Écran de chargement configuré</strong> ({{ module.content?.animation }}, {{ module.content?.durationMs }}ms)
        </div>
        <div v-else-if="module.type === 'ambient_particles' && isEditor" class="p-3 rounded-4 border border-dashed bg-light text-center small">
            <i class="bi bi-snow text-info me-1"></i>
            <strong>Ambiance &amp; Curseur</strong> : {{ module.content?.effect }}
            <span v-if="module.content?.mouseSpotlight">• Spotlight</span>
            <span v-if="module.content?.techRingCursor">• Curseur Tech</span>
        </div>

        <!-- Heading (supports Typewriter animation) -->
        <component
            v-else-if="module.type === 'heading'"
            :is="module.content?.tag || 'h2'"
            class="mb-0"
            :class="{ 'pb-typewriter': module.advanced?.textAnimation === 'typewriter' }"
            :style="{ textAlign: module.content?.align || 'left' }"
        >
            {{ module.content?.text }}
        </component>

        <!-- Text -->
        <p
            v-else-if="module.type === 'text'"
            class="mb-0"
            :style="{ textAlign: module.content?.align || 'left', whiteSpace: 'pre-line' }"
        >
            {{ module.content?.text }}
        </p>

        <!-- Image (with position/crop + animation) -->
        <figure v-else-if="module.type === 'image'" class="mb-0 overflow-hidden rounded-4">
            <img
                :src="module.content?.url"
                :alt="module.content?.alt || 'Image'"
                class="img-fluid rounded-4 shadow-sm w-100 object-fit-cover"
                :class="imgAnimClass"
                :style="{ objectPosition: module.content?.imagePosition || 'center center' }"
            />
            <figcaption v-if="module.content?.caption" class="small text-muted mt-2 text-center">
                {{ module.content.caption }}
            </figcaption>
        </figure>

        <!-- Button -->
        <div v-else-if="module.type === 'button'" :style="{ textAlign: module.content?.align || 'left' }">
            <a
                :href="isEditor ? 'javascript:void(0)' : (module.content?.href || '#')"
                class="btn fw-semibold d-inline-flex align-items-center gap-2 border-0"
                :style="computedStyle"
            >
                <span>{{ module.content?.label || 'Bouton' }}</span>
                <i v-if="module.content?.icon" class="bi" :class="module.content.icon"></i>
            </a>
        </div>

        <!-- Testimonials -->
        <div v-else-if="module.type === 'testimonials'">
            <h3 v-if="module.content?.title" class="fw-bold mb-3">{{ module.content.title }}</h3>
            <div class="row g-3">
                <div v-for="t in (module.content?.items || [])" :key="t.id" class="col-md-6">
                    <div class="p-4 rounded-4 border bg-white shadow-sm h-100">
                        <div class="text-warning mb-2">
                            <i v-for="s in (t.rating || 5)" :key="s" class="bi bi-star-fill me-1"></i>
                        </div>
                        <p class="fst-italic text-secondary mb-3">"{{ t.quote }}"</p>
                        <div class="fw-bold">{{ t.author }}</div>
                        <div class="small text-muted">{{ t.role }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Counter KPI -->
        <div v-else-if="module.type === 'counter_kpi'" class="row g-3 text-center">
            <div v-for="k in (module.content?.items || [])" :key="k.id" class="col-md-4">
                <div class="p-4 rounded-4 border bg-white shadow-sm">
                    <div class="display-6 fw-bolder text-primary">{{ k.value }}</div>
                    <div class="small fw-semibold text-secondary mt-1">{{ k.label }}</div>
                </div>
            </div>
        </div>

        <!-- Pricing (multiple templates + customizable Primary Badge) -->
        <div v-else-if="module.type === 'pricing'" :class="{ 'bg-dark text-white p-4 rounded-4': module.content?.template === 'dark_pro' }">
            <h3 v-if="module.content?.title" class="fw-bold mb-4 text-center">{{ module.content.title }}</h3>
            <div class="row g-3 justify-content-center">
                <div v-for="p in (module.content?.plans || [])" :key="p.id" class="col-md-5">
                    <div
                        class="position-relative p-4 rounded-4 border h-100 d-flex flex-column"
                        :class="[
                            module.content?.template === 'dark_pro' ? 'bg-secondary bg-opacity-10 border-secondary text-white' : 'bg-white text-dark',
                            p.highlighted ? 'border-primary shadow-lg' : 'shadow-sm',
                        ]"
                        :style="p.highlighted && module.content?.template === 'gradient_highlight' ? { background: 'linear-gradient(135deg, #4f46e5, #06b6d4)', color: '#ffffff' } : undefined"
                    >
                        <span
                            v-if="p.badgeText"
                            class="position-absolute top-0 end-0 m-3 badge rounded-pill px-3 py-1 text-white"
                            :style="{ backgroundColor: p.badgeColor || '#f97316' }"
                        >
                            {{ p.badgeText }}
                        </span>
                        <div class="fw-bold fs-5 mb-1">{{ p.name }}</div>
                        <div class="fs-3 fw-bolder mb-3">
                            {{ p.price }} <span class="fs-6 fw-normal opacity-75">{{ p.period }}</span>
                        </div>
                        <ul class="list-unstyled small mb-4 flex-grow-1">
                            <li v-for="(feat, fi) in (p.features || [])" :key="fi" class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>{{ feat }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Accordion -->
        <div v-else-if="module.type === 'faq_accordion'">
            <h3 v-if="module.content?.title" class="fw-bold mb-3">{{ module.content.title }}</h3>
            <div class="accordion">
                <div v-for="(f, fi) in (module.content?.items || [])" :key="f.id" class="accordion-item rounded-3 mb-2 border overflow-hidden">
                    <h2 class="accordion-header">
                        <button
                            type="button"
                            class="accordion-button fw-semibold"
                            :class="{ collapsed: openFaqIdx !== fi }"
                            @click.stop="openFaqIdx = openFaqIdx === fi ? -1 : fi"
                        >
                            {{ f.question }}
                        </button>
                    </h2>
                    <div v-show="openFaqIdx === fi" class="accordion-body text-secondary small">
                        {{ f.answer }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Tech237 Live Data -->
        <div v-else-if="module.type === 'tech237_dynamic'">
            <h3 v-if="module.content?.title" class="fw-bold mb-3">{{ module.content.title }}</h3>
            <div v-if="dynamicItems.length" class="row g-3">
                <div v-for="item in dynamicItems" :key="item.id" class="col-md-4">
                    <div class="p-3 rounded-4 border bg-white shadow-sm h-100">
                        <div class="fw-bold mb-1">{{ item.name || item.title || item.slug }}</div>
                        <p class="small text-muted mb-2 text-truncate">{{ item.description || item.url || item.status }}</p>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Tech237 Live</span>
                    </div>
                </div>
            </div>
            <div v-else class="p-4 rounded-4 border border-dashed text-center text-muted small">
                Aucun élément publié pour la source <strong>{{ module.content?.source }}</strong>.
            </div>
        </div>
    </div>
</template>

<style scoped>
.pb-typewriter {
    overflow: hidden;
    border-right: 2px solid currentColor;
    white-space: nowrap;
    animation: pbTyping 2.8s steps(36, end), pbBlink 0.75s step-end infinite;
}
@keyframes pbTyping { from { width: 0 } to { width: 100% } }
@keyframes pbBlink { from, to { border-color: transparent } 50% { border-color: currentColor } }

.pb-anim-fade_up { animation: pbFadeUp 0.6s ease both; }
.pb-anim-fade_in { animation: pbFadeIn 0.7s ease both; }
.pb-anim-zoom_in { animation: pbZoomIn 0.6s ease both; }
.pb-anim-pulse { animation: pbPulse 2s infinite; }
@keyframes pbFadeUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
@keyframes pbFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes pbZoomIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: none; } }
@keyframes pbPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }

.pb-img-anim-zoom_hover { transition: transform 0.35s ease; }
.pb-img-anim-zoom_hover:hover { transform: scale(1.06); }
.pb-img-anim-float_slow { animation: pbFloat 3.5s ease-in-out infinite; }
@keyframes pbFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
</style>
