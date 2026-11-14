<script setup>
import { ref, computed } from 'vue';
import NavbarAccordionModule from './NavbarAccordionModule.vue';
import HeroShowcaseModule from './HeroShowcaseModule.vue';
import AboutModule from './AboutModule.vue';
import GalleryModule from './GalleryModule.vue';
import VideoModule from './VideoModule.vue';
import ContactFormModule from './ContactFormModule.vue';
import { resolveResponsiveStyles, isNodeHidden } from '@/Composables/PortfolioBuilder/useResponsiveStyles';

const props = defineProps({
    module: { type: Object, required: true },
    viewport: { type: String, default: 'desktop' },
    isEditor: { type: Boolean, default: false },
    portfolioUser: { type: Object, default: () => ({}) },
    pages: { type: Array, default: () => [] },
    hydratedData: { type: Object, default: () => ({}) },
});

const openFaqIdx = ref(0);

const hidden = computed(() => isNodeHidden(props.module.advanced, props.viewport));
const computedStyle = computed(() => resolveResponsiveStyles(props.module.styles, props.viewport));

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
</script>

<template>
    <div
        v-show="!hidden || isEditor"
        :id="module.advanced?.customId || undefined"
        class="pb-module-inner"
        :class="[
            module.advanced?.cssClass || '',
            { 'opacity-50': hidden && isEditor }
        ]"
        :style="computedStyle"
    >
        <NavbarAccordionModule
            v-if="module.type === 'navbar_accordion'"
            :content="module.content"
            :portfolio-user="portfolioUser"
            :pages="pages"
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

        <ContactFormModule
            v-else-if="module.type === 'contact'"
            :content="module.content"
            :portfolio-user="portfolioUser"
            :is-editor="isEditor"
        />

        <!-- Heading -->
        <component
            v-else-if="module.type === 'heading'"
            :is="module.content?.tag || 'h2'"
            class="mb-0"
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

        <!-- Image -->
        <figure v-else-if="module.type === 'image'" class="mb-0">
            <img
                :src="module.content?.url"
                :alt="module.content?.alt || 'Image'"
                class="img-fluid rounded-4 shadow-sm w-100 object-fit-cover"
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

        <!-- Pricing -->
        <div v-else-if="module.type === 'pricing'">
            <h3 v-if="module.content?.title" class="fw-bold mb-3 text-center">{{ module.content.title }}</h3>
            <div class="row g-3 justify-content-center">
                <div v-for="p in (module.content?.plans || [])" :key="p.id" class="col-md-5">
                    <div
                        class="p-4 rounded-4 border h-100 d-flex flex-column"
                        :class="p.highlighted ? 'border-primary shadow bg-primary bg-opacity-10' : 'bg-white shadow-sm'"
                    >
                        <div class="fw-bold fs-5 mb-1">{{ p.name }}</div>
                        <div class="fs-3 fw-bolder text-primary mb-3">
                            {{ p.price }} <span class="fs-6 fw-normal text-muted">{{ p.period }}</span>
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
