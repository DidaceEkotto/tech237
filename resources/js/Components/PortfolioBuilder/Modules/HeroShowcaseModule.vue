<script setup>
import { computed } from 'vue';

const props = defineProps({
    content: { type: Object, required: true },
    isEditor: { type: Boolean, default: false },
});

const tpl = computed(() => props.content.template || 'impact_split');
const isDark = computed(() => ['dark_cyber', 'full_bleed_overlay'].includes(tpl.value));

const youtubeEmbedUrl = computed(() => {
    const raw = props.content.youtubeUrl || '';
    if (!raw) return '';
    try {
        const u = new URL(raw);
        let id = '';
        if (u.hostname.includes('youtu.be')) id = u.pathname.replace('/', '');
        else if (u.searchParams.get('v')) id = u.searchParams.get('v');
        else if (u.pathname.includes('/embed/')) id = u.pathname.split('/embed/')[1]?.split('/')[0] || '';
        return id ? `https://www.youtube.com/embed/${id}` : '';
    } catch {
        return '';
    }
});
</script>

<template>
    <div
        class="pb-hero position-relative overflow-hidden rounded-4 p-4 p-md-5"
        :class="{
            'bg-dark text-white': isDark,
            'text-center': ['centered_gradient', 'minimal_editorial', 'typographic_bold'].includes(tpl),
        }"
        :style="{
            background: tpl === 'centered_gradient'
                ? 'linear-gradient(135deg, #eef2ff 0%, #fff7ed 100%)'
                : tpl === 'dark_cyber'
                    ? 'radial-gradient(circle at top right, #312e81 0%, #0f172a 65%)'
                    : tpl === 'impact_split'
                        ? 'linear-gradient(120deg, #f0fdf4 0%, #ffffff 55%, #fefce8 100%)'
                        : undefined,
        }"
    >
        <!-- Split Layouts -->
        <div
            v-if="['impact_split', 'bento_intro', 'card_floating', 'diagonal_accent', 'startup_saas', 'creative_studio'].includes(tpl)"
            class="row align-items-center g-4"
        >
            <div class="col-lg-6">
                <span
                    v-if="content.showBadge !== false && content.badge"
                    class="badge rounded-pill px-3 py-2 mb-3 fw-semibold shadow-sm"
                    :class="isDark ? 'bg-primary text-white' : 'bg-white text-success border'"
                >
                    <i class="bi bi-stars me-1 text-warning"></i>{{ content.badge }}
                </span>

                <h1 v-if="content.showTitle !== false && content.title" class="display-5 fw-bolder lh-sm mb-3" :class="isDark ? 'text-white' : 'text-dark'">
                    {{ content.title }}
                </h1>

                <p v-if="content.showSubtitle !== false && content.subtitle" class="lead mb-4" :class="isDark ? 'text-light opacity-75' : 'text-secondary'">
                    {{ content.subtitle }}
                </p>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a
                        v-if="content.showPrimaryCta !== false && content.primaryCtaLabel"
                        :href="isEditor ? 'javascript:void(0)' : (content.primaryCtaUrl || '#contact')"
                        class="btn btn-lg rounded-pill px-4 fw-bold text-white shadow-sm border-0"
                        style="background: linear-gradient(135deg, #f97316, #ea580c);"
                    >
                        <i class="bi bi-heart-fill me-2"></i>{{ content.primaryCtaLabel }}
                    </a>
                    <a
                        v-if="content.showSecondaryCta !== false && content.secondaryCtaLabel"
                        :href="isEditor ? 'javascript:void(0)' : (content.secondaryCtaUrl || '#projects')"
                        class="btn btn-lg rounded-pill px-4 fw-semibold"
                        :class="isDark ? 'btn-outline-light' : 'btn-outline-success bg-white'"
                    >
                        {{ content.secondaryCtaLabel }} <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div v-if="content.showTrust !== false && content.trustText" class="d-flex align-items-center gap-2 small" :class="isDark ? 'text-light opacity-75' : 'text-muted'">
                    <div class="d-flex">
                        <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center fw-bold border border-white" style="width:26px;height:26px;font-size:11px;">A</span>
                        <span class="rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center fw-bold border border-white ms-n1" style="width:26px;height:26px;font-size:11px;">K</span>
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold border border-white ms-n1" style="width:26px;height:26px;font-size:11px;">M</span>
                    </div>
                    <span>{{ content.trustText }}</span>
                </div>
            </div>

            <div class="col-lg-6 position-relative">
                <div class="position-relative rounded-4 overflow-hidden shadow-lg border bg-white">
                    <div v-if="content.mediaType === 'youtube' && youtubeEmbedUrl" class="ratio ratio-16x9">
                        <iframe :src="youtubeEmbedUrl" allowfullscreen class="border-0"></iframe>
                    </div>
                    <img
                        v-else-if="content.imageUrl"
                        :src="content.imageUrl"
                        alt="Hero visual"
                        class="w-100 object-fit-cover"
                        :style="{ maxHeight: '380px', objectPosition: content.imagePosition || 'center center' }"
                    />
                    <div v-else class="bg-light d-flex align-items-center justify-content-center" style="height: 320px;">
                        <i class="bi bi-image fs-1 text-muted"></i>
                    </div>
                </div>

                <div
                    v-if="content.showFloatingTop !== false && content.floatingTopTitle"
                    class="position-absolute top-0 end-0 m-2 bg-white text-dark rounded-4 shadow p-2 px-3 d-flex align-items-center gap-2 border"
                    style="max-width: 240px;"
                >
                    <span class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-patch-check-fill"></i>
                    </span>
                    <div class="lh-sm">
                        <div class="fw-bold small">{{ content.floatingTopTitle }}</div>
                        <div class="text-muted" style="font-size: 11px;">{{ content.floatingTopSub }}</div>
                    </div>
                </div>

                <div
                    v-if="content.showFloatingBottom !== false && content.floatingBottomTitle"
                    class="position-absolute bottom-0 start-0 m-2 bg-white text-dark rounded-4 shadow p-2 px-3 d-flex align-items-center gap-2 border"
                    style="max-width: 240px;"
                >
                    <span class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-award-fill"></i>
                    </span>
                    <div class="lh-sm">
                        <div class="fw-bold small">{{ content.floatingBottomTitle }}</div>
                        <div class="text-muted" style="font-size: 11px;">{{ content.floatingBottomSub }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Centered Variants -->
        <div v-else class="mx-auto" style="max-width: 780px;">
            <span
                v-if="content.showBadge !== false && content.badge"
                class="badge rounded-pill px-3 py-2 mb-3 fw-semibold"
                :class="isDark ? 'bg-primary bg-opacity-25 text-info border border-info' : 'bg-white text-primary border shadow-sm'"
            >
                {{ content.badge }}
            </span>
            <h1 v-if="content.showTitle !== false && content.title" class="display-4 fw-bolder mb-3">{{ content.title }}</h1>
            <p v-if="content.showSubtitle !== false && content.subtitle" class="lead mb-4 opacity-75">{{ content.subtitle }}</p>
            <div v-if="content.mediaType === 'youtube' && youtubeEmbedUrl" class="ratio ratio-16x9 rounded-4 overflow-hidden shadow mb-4">
                <iframe :src="youtubeEmbedUrl" allowfullscreen class="border-0"></iframe>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a
                    v-if="content.showPrimaryCta !== false && content.primaryCtaLabel"
                    :href="isEditor ? 'javascript:void(0)' : (content.primaryCtaUrl || '#contact')"
                    class="btn btn-primary btn-lg rounded-pill px-4 fw-bold"
                >
                    {{ content.primaryCtaLabel }}
                </a>
                <a
                    v-if="content.showSecondaryCta !== false && content.secondaryCtaLabel"
                    :href="isEditor ? 'javascript:void(0)' : (content.secondaryCtaUrl || '#projects')"
                    class="btn btn-lg rounded-pill px-4 fw-semibold"
                    :class="isDark ? 'btn-outline-light' : 'btn-outline-dark'"
                >
                    {{ content.secondaryCtaLabel }}
                </a>
            </div>
        </div>
    </div>
</template>
