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
            <div class="col-md-5 text-center" v-if="tpl !== 'quote_editorial'">
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

            <div :class="tpl === 'quote_editorial' ? 'col-12 text-center' : 'col-md-7'">
                <div v-if="content.eyebrow" class="text-uppercase fw-bold small text-primary mb-2">
                    {{ content.eyebrow }}
                </div>
                <h2 class="fw-bold mb-3">{{ content.title }}</h2>
                <p class="mb-4" :class="isDark ? 'text-light opacity-75' : 'text-secondary'">
                    {{ content.bio }}
                </p>

                <div v-if="content.highlights && content.highlights.length" class="d-flex flex-wrap gap-2" :class="{ 'justify-content-center': tpl === 'quote_editorial' }">
                    <span
                        v-for="(item, idx) in content.highlights"
                        :key="idx"
                        class="badge rounded-pill px-3 py-2 fw-medium"
                        :class="isDark ? 'bg-secondary bg-opacity-50 text-white' : 'bg-light text-dark border'"
                    >
                        <i class="bi bi-check-circle-fill text-success me-1"></i>{{ item }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
