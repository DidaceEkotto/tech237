<script setup>
import { ref, computed, watch, onMounted, defineAsyncComponent } from 'vue';
import NavbarAccordionModule from './NavbarAccordionModule.vue';
import HeroShowcaseModule from './HeroShowcaseModule.vue';
import AboutModule from './AboutModule.vue';
import GalleryModule from './GalleryModule.vue';
import VideoModule from './VideoModule.vue';
import ContactFormModule from './ContactFormModule.vue';
import SliderModule from './SliderModule.vue';
import CvBuilderModule from './CvBuilderModule.vue';
import ExtraModulesRenderer from './ExtraModulesRenderer.vue';
import CreativeModulesRenderer from './CreativeModulesRenderer.vue';
import { resolveResponsiveStyles, isNodeHidden } from '@/Composables/PortfolioBuilder/useResponsiveStyles';
import { ensureFontLoaded } from '@/Composables/PortfolioBuilder/fontsAndAnimations';

const RowNode = defineAsyncComponent(() => import('../Nodes/RowNode.vue'));

const props = defineProps({
    module: { type: Object, required: true },
    viewport: { type: String, default: 'desktop' },
    isEditor: { type: Boolean, default: false },
    selectedId: { type: String, default: null },
    portfolioUser: { type: Object, default: () => ({}) },
    pages: { type: Array, default: () => [] },
    activePageSlug: { type: String, default: '' },
    siteType: { type: String, default: 'onepage' },
    siteStats: { type: Object, default: () => ({ views_count: 0, unique_visitors_count: 0 }) },
    hydratedData: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
    'select',
    'add-module-prompt',
    'duplicate-module',
    'delete-module',
    'drop-module',
    'drag-start-module',
]);

const openFaqIndices = ref(new Set([0]));
const gravatarCache = ref({});

async function resolveGravatarUrl(email) {
    const clean = String(email || '').trim().toLowerCase();
    if (!clean) return '';
    if (gravatarCache.value[clean]) return gravatarCache.value[clean];
    try {
        const data = new TextEncoder().encode(clean);
        const hashBuf = await crypto.subtle.digest('SHA-256', data);
        const hex = Array.from(new Uint8Array(hashBuf)).map((b) => b.toString(16).padStart(2, '0')).join('');
        const url = `https://www.gravatar.com/avatar/${hex}?s=160&d=identicon`;
        gravatarCache.value[clean] = url;
        return url;
    } catch {
        return `https://www.gravatar.com/avatar/?s=160&d=mp`;
    }
}

watch(
    () => props.module.content?.items,
    (items) => {
        if (props.module.type === 'testimonials' && Array.isArray(items)) {
            items.forEach((t) => {
                if (t.avatarType === 'gravatar' && t.gravatarEmail) {
                    resolveGravatarUrl(t.gravatarEmail);
                }
            });
        }
        if (props.module.type === 'faq_accordion' && Array.isArray(items)) {
            const next = new Set();
            items.forEach((f, idx) => {
                if (f.openByDefault) next.add(idx);
            });
            if (next.size) openFaqIndices.value = next;
        }
    },
    { immediate: true, deep: true }
);

function toggleFaq(idx) {
    const allowMultiple = !!props.module.content?.allowMultiple;
    const next = new Set(allowMultiple ? openFaqIndices.value : []);
    if (openFaqIndices.value.has(idx)) {
        next.delete(idx);
    } else {
        next.add(idx);
    }
    openFaqIndices.value = next;
}

function testimonialAvatar(t) {
    if (t.avatarType === 'upload' && t.avatarUrl) return t.avatarUrl;
    if (t.avatarType === 'gravatar' && t.gravatarEmail) {
        const clean = String(t.gravatarEmail).trim().toLowerCase();
        return gravatarCache.value[clean] || 'https://www.gravatar.com/avatar/?s=160&d=mp';
    }
    return '';
}

function isExternalLink(url) {
    return /^https?:\/\//i.test(String(url || '').trim());
}

const hidden = computed(() => isNodeHidden(props.module.advanced, props.viewport));
const computedStyle = computed(() => {
    const base = { ...resolveResponsiveStyles(props.module.styles, props.viewport) };
    const align = base.blockAlign;
    if (align === 'center') {
        base.marginLeft = 'auto';
        base.marginRight = 'auto';
    } else if (align === 'right') {
        base.marginLeft = 'auto';
        base.marginRight = '0';
    } else if (align === 'left') {
        base.marginLeft = '0';
        base.marginRight = 'auto';
    }
    delete base.blockAlign;
    return base;
});

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
const CREATIVE_TYPES = [
    'bento_grid', 'before_after', 'countdown', 'progress_bars', 'marquee_ticker', 'tabs_content',
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

        <CreativeModulesRenderer
            v-else-if="CREATIVE_TYPES.includes(module.type)"
            :module="module"
            :is-editor="isEditor"
        />

        <!-- Nested Section -->
        <div
            v-else-if="module.type === 'nested_section'"
            class="position-relative"
            :style="{
                backgroundColor: module.content?.bgColor || '#f8fafc',
                backgroundImage: module.content?.bgImage ? `url(${module.content.bgImage})` : undefined,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                borderRadius: `${module.content?.borderRadius ?? 16}px`,
                padding: `${module.content?.padding ?? 20}px`,
            }"
        >
            <RowNode
                v-for="nrow in (module.content?.rows || [])"
                :key="nrow.id"
                :row="nrow"
                :section-id="module.id"
                :viewport="viewport"
                :is-editor="isEditor"
                :selected-id="selectedId"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :active-page-slug="activePageSlug"
                :site-type="siteType"
                :site-stats="siteStats"
                :hydrated-data="hydratedData"
                @select="(k, id) => emit('select', k, id)"
                @add-module-prompt="(colId) => emit('add-module-prompt', colId)"
                @duplicate-module="(id) => emit('duplicate-module', id)"
                @delete-module="(id) => emit('delete-module', id)"
                @drop-module="(payload) => emit('drop-module', payload)"
                @drag-start-module="(id) => emit('drag-start-module', id)"
            />
        </div>

        <!-- Page Loader / Ambient Particles indicator in Editor -->
        <div v-else-if="module.type === 'page_loader' && isEditor" class="p-3 rounded-4 border border-dashed bg-light text-center small">
            <i class="bi bi-hourglass-split text-primary me-1"></i>
            <strong>Écran de chargement :</strong> « {{ module.content?.text || 'Chargement...' }} » ({{ module.content?.animation }}, {{ module.content?.durationMs }}ms)
        </div>
        <div v-else-if="module.type === 'ambient_particles' && isEditor" class="p-3 rounded-4 border border-dashed bg-light text-center small">
            <i class="bi bi-snow text-info me-1"></i>
            <strong>Ambiance &amp; Curseur</strong> : {{ module.content?.effect }}
            <span v-if="module.content?.mouseSpotlight">• Spotlight</span>
            <span v-if="module.content?.techRingCursor">• Curseur Tech</span>
        </div>

        <!-- Heading (H1-H6 + directional X/Y offset + Typewriter) -->
        <component
            v-else-if="module.type === 'heading'"
            :is="module.content?.tag || 'h2'"
            class="mb-0"
            :class="{ 'pb-typewriter': module.advanced?.textAnimation === 'typewriter' }"
            :style="{
                textAlign: module.content?.align || 'left',
                transform: (module.content?.offsetX || module.content?.offsetY)
                    ? `translate(${module.content?.offsetX || 0}px, ${module.content?.offsetY || 0}px)`
                    : undefined,
            }"
        >
            {{ module.content?.text }}
        </component>

        <!-- Text (with X/Y positioning & maxWidth) -->
        <p
            v-else-if="module.type === 'text'"
            class="mb-0"
            :style="{
                textAlign: module.content?.align || 'left',
                whiteSpace: 'pre-line',
                maxWidth: module.content?.maxWidth || undefined,
                transform: (module.content?.offsetX || module.content?.offsetY)
                    ? `translate(${module.content?.offsetX || 0}px, ${module.content?.offsetY || 0}px)`
                    : undefined,
            }"
        >
            {{ module.content?.text }}
        </p>

        <!-- Image (Gradient overlay, filters, link & millimeter-positioned overlays) -->
        <figure v-else-if="module.type === 'image'" class="mb-0">
            <component
                :is="(!isEditor && module.content?.linkUrl) ? 'a' : 'div'"
                :href="(!isEditor && module.content?.linkUrl) ? module.content.linkUrl : undefined"
                :target="(!isEditor && module.content?.linkUrl && (module.content?.openInNewTab || isExternalLink(module.content.linkUrl))) ? '_blank' : undefined"
                :rel="(!isEditor && module.content?.linkUrl && (module.content?.openInNewTab || isExternalLink(module.content.linkUrl))) ? 'noopener noreferrer' : undefined"
                class="position-relative d-block overflow-hidden rounded-4"
                :class="{
                    'shadow-sm': module.content?.shadow === 'sm',
                    'shadow': !module.content?.shadow || module.content?.shadow === 'md',
                    'shadow-lg': module.content?.shadow === 'lg',
                }"
            >
                <img
                    :src="module.content?.url"
                    :alt="module.content?.alt || 'Image'"
                    class="img-fluid w-100 d-block"
                    :class="imgAnimClass"
                    :style="{
                        objectFit: module.content?.objectFit || 'cover',
                        objectPosition: module.content?.imagePosition || 'center center',
                        filter: `brightness(${module.content?.brightness ?? 100}%) contrast(${module.content?.contrast ?? 100}%) grayscale(${module.content?.grayscale ?? 0}%) blur(${module.content?.blur ?? 0}px)`,
                    }"
                />
                <div
                    v-if="module.content?.gradientEnabled"
                    class="position-absolute top-0 start-0 w-100 h-100"
                    :style="{
                        background: `linear-gradient(${module.content?.gradientDirection || '135deg'}, ${module.content?.gradientColor1 || '#4f46e5'}, ${module.content?.gradientColor2 || '#06b6d4'})`,
                        opacity: module.content?.gradientOpacity ?? 0.45,
                        pointerEvents: 'none',
                    }"
                ></div>
                <!-- Millimeter-positioned overlay elements inside the image -->
                <div
                    v-for="ov in (module.content?.overlays || [])"
                    :key="ov.id"
                    class="position-absolute"
                    :style="{
                        left: `${ov.x ?? 50}%`,
                        top: `${ov.y ?? 50}%`,
                        transform: 'translate(-50%, -50%)',
                        color: ov.color || '#ffffff',
                        backgroundColor: ov.bgColor || 'transparent',
                        fontSize: `${ov.fontSize || 18}px`,
                        fontWeight: ov.fontWeight || '700',
                        padding: ov.bgColor && ov.bgColor !== 'transparent' ? '6px 14px' : '0',
                        borderRadius: `${ov.borderRadius ?? 8}px`,
                        zIndex: 5,
                        whiteSpace: 'pre-line',
                        textAlign: 'center',
                    }"
                >
                    {{ ov.text }}
                </div>
            </component>
            <figcaption v-if="module.content?.caption" class="small text-muted mt-2 text-center">
                {{ module.content.caption }}
            </figcaption>
        </figure>

        <!-- Button (Anchor or External URL opening in new tab) -->
        <div v-else-if="module.type === 'button'" :style="{ textAlign: module.content?.align || 'left' }">
            <a
                :href="isEditor ? 'javascript:void(0)' : (module.content?.href || '#')"
                :target="(!isEditor && (module.content?.openInNewTab || isExternalLink(module.content?.href))) ? '_blank' : undefined"
                :rel="(!isEditor && (module.content?.openInNewTab || isExternalLink(module.content?.href))) ? 'noopener noreferrer' : undefined"
                class="btn fw-semibold d-inline-flex align-items-center gap-2 border-0"
                :style="computedStyle"
            >
                <span>{{ module.content?.label || 'Bouton' }}</span>
                <i v-if="module.content?.icon" class="bi" :class="module.content.icon"></i>
            </a>
        </div>

        <!-- Testimonials (6 templates, animations, upload or Gravatar avatar) -->
        <div v-else-if="module.type === 'testimonials'">
            <h3 v-if="module.content?.title" class="fw-bold mb-3">{{ module.content.title }}</h3>
            <div class="row g-3">
                <div
                    v-for="t in (module.content?.items || [])"
                    :key="t.id"
                    :class="module.content?.template === 'grid_compact' ? 'col-md-4' : 'col-md-6'"
                >
                    <div
                        class="p-4 rounded-4 border h-100"
                        :class="[
                            module.content?.template === 'dark_spotlight' ? 'bg-dark text-white border-secondary shadow' :
                            (module.content?.template === 'glassmorphism' ? 'bg-white bg-opacity-75 shadow-sm' : 'bg-white shadow-sm'),
                            module.content?.animation && module.content?.animation !== 'none' ? `pb-anim-${module.content.animation}` : '',
                        ]"
                    >
                        <div class="text-warning mb-2">
                            <i v-for="s in (t.rating || 5)" :key="s" class="bi bi-star-fill me-1"></i>
                        </div>
                        <p class="fst-italic mb-3" :class="module.content?.template === 'dark_spotlight' ? 'text-light opacity-75' : 'text-secondary'">
                            "{{ t.quote }}"
                        </p>
                        <div class="d-flex align-items-center gap-2">
                            <img
                                v-if="testimonialAvatar(t)"
                                :src="testimonialAvatar(t)"
                                :alt="t.author"
                                class="rounded-circle object-fit-cover border"
                                style="width: 40px; height: 40px;"
                            />
                            <div>
                                <div class="fw-bold">{{ t.author }}</div>
                                <div class="small" :class="module.content?.template === 'dark_spotlight' ? 'text-light opacity-50' : 'text-muted'">{{ t.role }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Counter KPI (6 templates, prefix/suffix/icon) -->
        <div
            v-else-if="module.type === 'counter_kpi'"
            class="row g-3 text-center"
            :class="{ 'p-4 rounded-4 bg-dark text-white': module.content?.template === 'dark_neon' }"
        >
            <div v-for="k in (module.content?.items || [])" :key="k.id" class="col-md-4">
                <div
                    class="p-4 rounded-4 h-100"
                    :class="{
                        'border bg-white shadow-sm': !module.content?.template || module.content?.template === 'cards_classic',
                        'border-0 bg-transparent': module.content?.template === 'minimal_clean',
                        'text-white shadow': module.content?.template === 'gradient_pills',
                        'border border-secondary bg-secondary bg-opacity-10 text-white': module.content?.template === 'dark_neon',
                        'border bg-white bg-opacity-50 shadow-sm': module.content?.template === 'glass_cards',
                        'border-start border-4 bg-light': module.content?.template === 'horizontal_bar',
                    }"
                    :style="module.content?.template === 'gradient_pills' ? { background: `linear-gradient(135deg, ${module.content?.accentColor || '#4f46e5'}, #06b6d4)` } : undefined"
                >
                    <i v-if="k.icon" class="bi fs-4 mb-2 d-block" :class="k.icon" :style="{ color: module.content?.template === 'gradient_pills' ? '#ffffff' : (module.content?.accentColor || '#4f46e5') }"></i>
                    <div class="display-6 fw-bolder" :style="{ color: module.content?.template === 'gradient_pills' ? '#ffffff' : (module.content?.accentColor || '#4f46e5') }">
                        {{ k.prefix || '' }}{{ k.value }}{{ k.suffix || '' }}
                    </div>
                    <div class="small fw-semibold mt-1 opacity-75">{{ k.label }}</div>
                </div>
            </div>
        </div>

        <!-- Pricing (unlimited plans + customizable options + CTA) -->
        <div v-else-if="module.type === 'pricing'" :class="{ 'bg-dark text-white p-4 rounded-4': module.content?.template === 'dark_pro' }">
            <h3 v-if="module.content?.title" class="fw-bold mb-4 text-center">{{ module.content.title }}</h3>
            <div class="row g-3 justify-content-center">
                <div
                    v-for="p in (module.content?.plans || [])"
                    :key="p.id"
                    :class="(module.content?.plans?.length || 0) >= 3 ? 'col-md-6 col-lg-4' : 'col-md-5'"
                >
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
                        <a
                            v-if="p.ctaLabel"
                            :href="isEditor ? 'javascript:void(0)' : (p.ctaUrl || '#contact')"
                            :target="(!isEditor && isExternalLink(p.ctaUrl)) ? '_blank' : undefined"
                            :rel="(!isEditor && isExternalLink(p.ctaUrl)) ? 'noopener noreferrer' : undefined"
                            class="btn rounded-pill fw-semibold w-100 mt-auto"
                            :class="p.highlighted ? 'btn-primary' : 'btn-outline-primary'"
                        >
                            {{ p.ctaLabel }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Accordion (4 templates, unlimited items) -->
        <div
            v-else-if="module.type === 'faq_accordion'"
            :class="{ 'bg-dark text-white p-4 rounded-4': module.content?.template === 'dark_modern' }"
        >
            <h3 v-if="module.content?.title" class="fw-bold mb-3">{{ module.content.title }}</h3>
            <div class="accordion">
                <div
                    v-for="(f, fi) in (module.content?.items || [])"
                    :key="f.id"
                    class="accordion-item mb-2 overflow-hidden"
                    :class="[
                        module.content?.template === 'separated_cards' ? 'rounded-4 border shadow-sm' :
                        (module.content?.template === 'minimal_lines' ? 'border-0 border-bottom rounded-0 bg-transparent' : 'rounded-3 border'),
                        module.content?.template === 'dark_modern' ? 'bg-secondary bg-opacity-10 border-secondary text-white' : '',
                    ]"
                >
                    <h2 class="accordion-header">
                        <button
                            type="button"
                            class="accordion-button fw-semibold"
                            :class="[
                                { collapsed: !openFaqIndices.has(fi) },
                                module.content?.template === 'dark_modern' ? 'bg-transparent text-white' : '',
                            ]"
                            @click.stop="toggleFaq(fi)"
                        >
                            {{ f.question }}
                        </button>
                    </h2>
                    <div
                        v-show="openFaqIndices.has(fi)"
                        class="accordion-body small"
                        :class="module.content?.template === 'dark_modern' ? 'text-light opacity-75' : 'text-secondary'"
                    >
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
