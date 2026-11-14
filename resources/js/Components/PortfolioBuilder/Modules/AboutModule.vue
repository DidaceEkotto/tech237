<script setup>
import { computed } from 'vue';

const props = defineProps({
    content: { type: Object, required: true },
    portfolioUser: { type: Object, default: () => ({}) },
});

const tpl = computed(() => props.content.template || 'split_photo');
const isDark = computed(() => tpl.value === 'dark_spotlight');

const initials = computed(() => {
    const raw = props.content.displayName || props.portfolioUser?.name || props.portfolioUser?.username || 'A';
    return raw
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0].toUpperCase())
        .join('');
});
</script>

<template>
    <div
        class="pb-about rounded-4 p-4 p-md-5 border"
        :class="isDark ? 'bg-dark text-white border-secondary' : 'bg-white text-dark'"
    >
        <div class="row align-items-center g-4">
            <div class="col-md-5 text-center" v-if="content.showImage !== false">
                <img
                    v-if="content.imageUrl"
                    :src="content.imageUrl"
                    :alt="content.displayName || 'Portrait'"
                    class="rounded-4 shadow object-fit-cover w-100"
                    style="max-height: 320px;"
                />
                <div
                    v-else
                    class="rounded-4 mx-auto d-flex flex-column align-items-center justify-content-center text-white fw-bold shadow"
                    style="height: 240px; background: linear-gradient(135deg, #4f46e5, #06b6d4); font-size: 48px;"
                >
                    <span>{{ initials }}</span>
                    <span class="fs-6 fw-normal mt-2 opacity-75">{{ content.displayName || portfolioUser?.name }}</span>
                </div>
            </div>

            <div :class="content.showImage === false ? 'col-12' : 'col-md-7'">
                <div v-if="content.showEyebrow !== false && content.eyebrow" class="text-uppercase fw-bold small text-primary mb-2">
                    {{ content.eyebrow }}
                </div>
                <h2 v-if="content.showTitle !== false && content.title" class="fw-bold mb-3">{{ content.title }}</h2>
                <p v-if="content.showBio !== false && content.bio" class="mb-4" :class="isDark ? 'text-light opacity-75' : 'text-secondary'">
                    {{ content.bio }}
                </p>

                <div v-if="tpl === 'stats_bio' && content.stats?.length" class="row g-2 mb-4">
                    <div v-for="st in content.stats" :key="st.id" class="col-4">
                        <div class="p-2 rounded-3 text-center" :class="isDark ? 'bg-secondary bg-opacity-25' : 'bg-light'">
                            <div class="fw-bold fs-5 text-primary">{{ st.value }}</div>
                            <div class="small" :class="isDark ? 'text-light opacity-75' : 'text-muted'">{{ st.label }}</div>
                        </div>
                    </div>
                </div>

                <div v-if="content.showHighlights !== false && content.highlights && content.highlights.length" class="d-flex flex-wrap gap-2 mb-3">
                    <span
                        v-for="(item, idx) in content.highlights"
                        :key="idx"
                        class="badge rounded-pill px-3 py-2 fw-medium"
                        :class="isDark ? 'bg-secondary bg-opacity-50 text-white' : 'bg-light text-dark border'"
                    >
                        <i class="bi bi-check-circle-fill text-success me-1"></i>{{ item }}
                    </span>
                </div>

                <div v-if="content.showCta && content.ctaLabel" class="mt-3">
                    <a
                        :href="content.ctaUrl || '#contact'"
                        :target="/^https?:\/\//i.test(content.ctaUrl || '') ? '_blank' : undefined"
                        :rel="/^https?:\/\//i.test(content.ctaUrl || '') ? 'noopener noreferrer' : undefined"
                        class="btn btn-primary rounded-pill px-4 fw-semibold"
                    >
                        {{ content.ctaLabel }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
