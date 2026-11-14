<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';
import { onMediaPlayOther } from '@/Composables/PortfolioBuilder/useMediaPlaybackBus';

const props = defineProps({
    module: { type: Object, required: true },
    isEditor: { type: Boolean, default: false },
    siteStats: { type: Object, default: () => ({ views_count: 0, unique_visitors_count: 0 }) },
});

const rootEl = ref(null);
const instanceId = `vm_${Math.random().toString(36).slice(2, 9)}`;
const c = computed(() => props.module.content || {});
const githubRepos = ref([]);
const loadingGithub = ref(false);

let stopMediaBus = null;
onMounted(() => {
    loadGithub();
    stopMediaBus = onMediaPlayOther((playingMediaId) => {
        if (!rootEl.value) return;
        const frames = rootEl.value.querySelectorAll('iframe[data-pb-media-id]');
        frames.forEach((frame) => {
            if (frame.getAttribute('data-pb-media-id') !== playingMediaId) {
                try {
                    frame.contentWindow?.postMessage(JSON.stringify({ method: 'pause' }), '*');
                } catch {
                    // ignore
                }
            }
        });
    });
});
onUnmounted(() => {
    if (stopMediaBus) stopMediaBus();
});

async function loadGithub() {
    if (props.module.type !== 'github_projects' || !c.value.username) return;
    loadingGithub.value = true;
    try {
        const res = await apiClient.get(`/espace-utilisateur/portfolio-builder/github-repos?username=${encodeURIComponent(c.value.username)}&limit=${c.value.maxItems || 6}`);
        githubRepos.value = res.data.repos || [];
    } catch {
        githubRepos.value = [];
    } finally {
        loadingGithub.value = false;
    }
}

watch(() => [c.value.username, c.value.maxItems], loadGithub);

function toVimeoEmbed(urlOrId) {
    if (!urlOrId) return '';
    const trimmed = String(urlOrId).trim();
    if (/^\d+$/.test(trimmed)) {
        return `https://player.vimeo.com/video/${trimmed}?api=1`;
    }
    const m = trimmed.match(/vimeo\.com\/(?:video\/)?(\d+)/);
    return m ? `https://player.vimeo.com/video/${m[1]}?api=1` : '';
}

function onVimeoLoad(e) {
    try {
        e.target.contentWindow?.postMessage(JSON.stringify({ method: 'addEventListener', value: 'play' }), '*');
    } catch {
        // ignore
    }
}

function whatsappHref() {
    const cleanPhone = String(c.value.phone || '').replace(/[^0-9]/g, '');
    const msg = encodeURIComponent(c.value.prefillMessage || '');
    return `https://wa.me/${cleanPhone}?text=${msg}`;
}

function mapEmbedSrc() {
    const lat = parseFloat(c.value.lat || 4.0511);
    const lng = parseFloat(c.value.lng || 9.7679);
    if (c.value.provider === 'google') {
        const q = encodeURIComponent(c.value.address || `${lat},${lng}`);
        return `https://maps.google.com/maps?q=${q}&z=${c.value.zoom || 13}&output=embed`;
    }
    return `https://www.openstreetmap.org/export/embed.html?bbox=${lng - 0.03},${lat - 0.03},${lng + 0.03},${lat + 0.03}&layer=mapnik&marker=${lat},${lng}`;
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function shareUrl(platform) {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(document.title);
    if (platform === 'whatsapp') window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
    if (platform === 'linkedin') window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank');
    if (platform === 'twitter') window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
    if (platform === 'facebook') window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
    if (platform === 'copy') navigator.clipboard?.writeText(window.location.href);
}
</script>

<template>
    <!-- WhatsApp -->
    <div v-if="module.type === 'whatsapp'" class="p-4 rounded-4 border bg-white shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h5 class="fw-bold mb-1">{{ c.title }}</h5>
            <p class="small text-muted mb-0">{{ c.description }}</p>
        </div>
        <a
            :href="isEditor ? 'javascript:void(0)' : whatsappHref()"
            target="_blank"
            rel="noopener"
            class="btn rounded-pill px-4 fw-bold border-0"
            :style="{ backgroundColor: c.bgColor || '#25D366', color: c.textColor || '#ffffff' }"
        >
            <i class="bi bi-whatsapp me-2"></i>{{ c.buttonLabel || 'WhatsApp' }}
        </a>
    </div>

    <!-- PayPal -->
    <div v-else-if="module.type === 'paypal'" class="p-4 rounded-4 border bg-white shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h5 class="fw-bold mb-1">{{ c.title }}</h5>
            <p class="small text-muted mb-0">{{ c.description }} <strong v-if="c.amount">({{ c.amount }} {{ c.currency }})</strong></p>
        </div>
        <a
            :href="isEditor ? 'javascript:void(0)' : (c.paypalTarget || 'https://paypal.me')"
            target="_blank"
            rel="noopener"
            class="btn rounded-pill px-4 fw-bold border-0"
            :style="{ backgroundColor: c.bgColor || '#0070BA', color: c.textColor || '#ffffff' }"
        >
            <i class="bi bi-paypal me-2"></i>{{ c.buttonLabel || 'Payer avec PayPal' }}
        </a>
    </div>

    <!-- Vimeo -->
    <div v-else-if="module.type === 'vimeo'" ref="rootEl" class="rounded-4 p-3" :class="{ 'bg-dark text-white p-4': c.template === 'dark_cinema' }">
        <h3 v-if="c.title" class="fw-bold mb-3">{{ c.title }}</h3>
        <div class="row g-3">
            <div v-for="v in (c.videos || [])" :key="v.id" :class="c.template === 'grid_2_col' ? 'col-md-6' : 'col-12'">
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow">
                    <iframe
                        v-if="toVimeoEmbed(v.vimeoUrl)"
                        :src="toVimeoEmbed(v.vimeoUrl)"
                        :data-pb-media-id="`${instanceId}_${v.id}`"
                        allowfullscreen
                        class="border-0"
                        @load="onVimeoLoad"
                    ></iframe>
                </div>
                <div class="fw-semibold small mt-2">{{ v.title }}</div>
            </div>
        </div>
    </div>

    <!-- GitHub Projects -->
    <div v-else-if="module.type === 'github_projects'">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0"><i class="bi bi-github me-2"></i>{{ c.title }}</h3>
            <span class="badge bg-dark rounded-pill">@{{ c.username }}</span>
        </div>
        <div v-if="loadingGithub" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Chargement GitHub...</div>
        <div v-else class="row g-3">
            <div v-for="repo in githubRepos" :key="repo.id" class="col-md-4">
                <a :href="isEditor ? 'javascript:void(0)' : repo.html_url" target="_blank" rel="noopener" class="text-decoration-none text-dark d-block p-3 rounded-4 border bg-white shadow-sm h-100">
                    <div class="fw-bold text-primary mb-1">{{ repo.name }}</div>
                    <p class="small text-muted mb-2">{{ repo.description || 'Dépôt GitHub' }}</p>
                    <div class="d-flex gap-3 small text-secondary">
                        <span v-if="repo.language"><i class="bi bi-code-slash me-1"></i>{{ repo.language }}</span>
                        <span><i class="bi bi-star-fill text-warning me-1"></i>{{ repo.stargazers_count }}</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Map (OpenStreetMap / Google Maps) -->
    <div v-else-if="module.type === 'map_embed'">
        <h4 v-if="c.title" class="fw-bold mb-3">{{ c.title }}</h4>
        <div class="rounded-4 overflow-hidden border shadow-sm">
            <iframe :src="mapEmbedSrc()" class="w-100 border-0" :style="{ height: (c.heightPx || 340) + 'px' }" loading="lazy"></iframe>
        </div>
    </div>

    <!-- Visitor Counter -->
    <div v-else-if="module.type === 'visitor_counter'" class="p-3 rounded-4 border d-inline-flex align-items-center gap-3 shadow-sm" :style="{ backgroundColor: c.bgColor || '#f8fafc' }">
        <i class="bi bi-eye-fill fs-4" :style="{ color: c.accentColor || '#4f46e5' }"></i>
        <div>
            <div class="fw-bolder fs-5 lh-1">{{ siteStats.views_count || 0 }}</div>
            <div class="small text-muted">{{ c.label || 'Visites' }} <span v-if="c.showUniques">({{ siteStats.unique_visitors_count || 0 }} uniques)</span></div>
        </div>
    </div>

    <!-- Back To Top -->
    <div v-else-if="module.type === 'back_to_top'">
        <button
            type="button"
            class="btn fw-semibold border-0 d-inline-flex align-items-center justify-content-center gap-1"
            :class="[
                c.shape === 'circle' ? 'rounded-circle' : (c.shape === 'square' ? 'rounded-3' : 'rounded-pill'),
                c.size === 'sm' ? 'btn-sm px-2 py-1' : (c.size === 'lg' ? 'btn-lg px-4 py-3' : 'px-3 py-2'),
                c.shadow !== false ? 'shadow' : '',
                c.pulseAnimation ? 'pb-btt-pulse' : '',
                c.floating && !isEditor ? (
                    c.position === 'bottom-left' ? 'position-fixed bottom-0 start-0 m-4' :
                    (c.position === 'bottom-center' ? 'position-fixed bottom-0 start-50 translate-middle-x mb-4' : 'position-fixed bottom-0 end-0 m-4')
                ) : '',
            ]"
            :style="{
                backgroundColor: c.bgColor || '#4f46e5',
                color: c.textColor || '#ffffff',
                width: c.shape === 'circle' && !c.showLabel ? (c.size === 'lg' ? '52px' : (c.size === 'sm' ? '36px' : '44px')) : undefined,
                height: c.shape === 'circle' && !c.showLabel ? (c.size === 'lg' ? '52px' : (c.size === 'sm' ? '36px' : '44px')) : undefined,
                zIndex: 1040,
            }"
            @click="scrollToTop"
        >
            <i class="bi" :class="c.icon || 'bi-arrow-up'"></i>
            <span v-if="c.showLabel !== false && c.label">{{ c.label }}</span>
        </button>
    </div>

    <!-- Social Share -->
    <div
        v-else-if="module.type === 'social_share'"
        class="p-2 rounded-4 border bg-white shadow-sm d-inline-flex align-items-center gap-2 flex-wrap"
        :class="{
            'position-fixed top-50 start-0 translate-middle-y ms-2 flex-column': c.position === 'left' && !isEditor,
            'position-fixed top-50 end-0 translate-middle-y me-2 flex-column': c.position === 'right' && !isEditor,
        }"
        style="z-index: 1035;"
    >
        <span v-if="c.position === 'inline' && c.title" class="small fw-semibold me-1">{{ c.title }}</span>
        <button type="button" class="btn btn-sm btn-success rounded-circle" title="WhatsApp" @click="shareUrl('whatsapp')"><i class="bi bi-whatsapp"></i></button>
        <button type="button" class="btn btn-sm btn-primary rounded-circle" title="LinkedIn" @click="shareUrl('linkedin')"><i class="bi bi-linkedin"></i></button>
        <button type="button" class="btn btn-sm btn-dark rounded-circle" title="X" @click="shareUrl('twitter')"><i class="bi bi-twitter-x"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" title="Copier le lien" @click="shareUrl('copy')"><i class="bi bi-link-45deg"></i></button>
    </div>

    <!-- Footer Pro -->
    <footer v-else-if="module.type === 'footer_pro'" class="rounded-4 p-4 p-md-5" :style="{ backgroundColor: c.bgColor || '#0f172a', color: c.textColor || '#f8fafc' }">
        <div class="row g-4 align-items-center">
            <div class="col-md-5">
                <p class="mb-2 opacity-75">{{ c.brandDescription }}</p>
                <div class="small opacity-50">{{ c.copyright }}</div>
            </div>
            <div class="col-md-4 d-flex flex-wrap gap-3">
                <a v-for="l in (c.links || [])" :key="l.id" :href="isEditor ? 'javascript:void(0)' : l.href" class="text-decoration-none small" :style="{ color: 'inherit' }">
                    {{ l.label }}
                </a>
            </div>
            <div class="col-md-3 d-flex justify-content-md-end gap-2">
                <a v-for="s in (c.socials || [])" :key="s.id" :href="isEditor ? 'javascript:void(0)' : s.url" target="_blank" class="btn btn-sm btn-outline-light rounded-circle">
                    <i class="bi" :class="s.icon"></i>
                </a>
            </div>
        </div>
    </footer>

    <!-- Parallax -->
    <component
        :is="(!isEditor && c.redirectUrl) ? 'a' : 'div'"
        v-else-if="module.type === 'parallax'"
        :href="(!isEditor && c.redirectUrl) ? c.redirectUrl : undefined"
        :target="(!isEditor && c.redirectUrl && c.openInNewTab) ? '_blank' : undefined"
        :rel="(!isEditor && c.redirectUrl && c.openInNewTab) ? 'noopener noreferrer' : undefined"
        class="position-relative rounded-4 overflow-hidden d-flex align-items-center text-white p-4 text-decoration-none"
        :class="c.template === 'split_card' ? 'justify-content-start' : 'justify-content-center text-center'"
        :style="{
            minHeight: (c.minHeightPx || 360) + 'px',
            backgroundImage: `url(${c.imageUrl})`,
            backgroundAttachment: (isEditor || c.fixedBg === false) ? 'scroll' : 'fixed',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
        }"
    >
        <div
            class="position-absolute top-0 start-0 w-100 h-100"
            :style="{ backgroundColor: c.overlayColor || '#0f172a', opacity: c.overlayOpacity ?? 0.55 }"
        ></div>
        <div
            v-if="c.showText !== false"
            class="position-relative"
            :class="{
                'p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-25': c.template === 'glass_center',
                'p-4 rounded-4 bg-dark bg-opacity-75': c.template === 'split_card',
            }"
            style="z-index: 2; max-width: 680px; backdrop-filter: blur(6px);"
        >
            <h2 v-if="c.title" class="display-6 fw-bold mb-2">{{ c.title }}</h2>
            <p v-if="c.subtitle" class="lead opacity-75 mb-3">{{ c.subtitle }}</p>
            <a
                v-if="c.showCta !== false && c.ctaLabel && !c.redirectUrl"
                :href="isEditor ? 'javascript:void(0)' : (c.ctaUrl || '#contact')"
                :target="(!isEditor && /^https?:\/\//i.test(c.ctaUrl || '')) ? '_blank' : undefined"
                :rel="(!isEditor && /^https?:\/\//i.test(c.ctaUrl || '')) ? 'noopener noreferrer' : undefined"
                class="btn btn-light rounded-pill px-4 fw-semibold"
            >
                {{ c.ctaLabel }}
            </a>
        </div>
    </component>
</template>

<style scoped>
.pb-btt-pulse {
    animation: pbBttPulse 2s infinite;
}
@keyframes pbBttPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.06); }
}
</style>
