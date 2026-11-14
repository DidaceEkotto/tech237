<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    content: { type: Object, required: true },
    isEditor: { type: Boolean, default: false },
});

const idx = ref(0);
let timer = null;

const slides = computed(() => props.content.slides || []);
const tpl = computed(() => props.content.template || 'classic_fade');

function next() {
    if (!slides.value.length) return;
    idx.value = (idx.value + 1) % slides.value.length;
}
function prev() {
    if (!slides.value.length) return;
    idx.value = (idx.value - 1 + slides.value.length) % slides.value.length;
}

onMounted(() => {
    if (props.content.autoplay && !props.isEditor) {
        timer = setInterval(next, Number(props.content.intervalMs || 4500));
    }
});

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div v-if="slides.length" class="pb-slider position-relative rounded-4 overflow-hidden shadow">
        <div v-if="tpl === 'split_caption'" class="row g-0 bg-white border rounded-4 overflow-hidden align-items-center">
            <div class="col-md-7">
                <img :src="slides[idx].imageUrl" :alt="slides[idx].title" class="w-100 object-fit-cover" :style="{ height: (content.heightPx || 380) + 'px' }" />
            </div>
            <div class="col-md-5 p-4">
                <h3 class="fw-bold mb-2">{{ slides[idx].title }}</h3>
                <p class="text-secondary mb-3">{{ slides[idx].subtitle }}</p>
                <a v-if="slides[idx].ctaLabel" :href="isEditor ? 'javascript:void(0)' : slides[idx].ctaUrl" class="btn btn-primary rounded-pill px-4">
                    {{ slides[idx].ctaLabel }}
                </a>
            </div>
        </div>

        <div v-else class="position-relative">
            <img :src="slides[idx].imageUrl" :alt="slides[idx].title" class="w-100 object-fit-cover" :style="{ height: (content.heightPx || 380) + 'px' }" />
            <div class="position-absolute inset-0 bg-dark bg-opacity-40 d-flex flex-column justify-content-end p-4 text-white" style="top:0;right:0;bottom:0;left:0;">
                <h3 class="fw-bold mb-1">{{ slides[idx].title }}</h3>
                <p class="mb-2 opacity-75">{{ slides[idx].subtitle }}</p>
                <div>
                    <a v-if="slides[idx].ctaLabel" :href="isEditor ? 'javascript:void(0)' : slides[idx].ctaUrl" class="btn btn-light btn-sm rounded-pill px-3 fw-semibold">
                        {{ slides[idx].ctaLabel }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Arrows -->
        <template v-if="content.showArrows !== false && slides.length > 1">
            <button type="button" class="position-absolute top-50 start-0 translate-middle-y ms-2 btn btn-light btn-sm rounded-circle shadow" @click.stop="prev">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="position-absolute top-50 end-0 translate-middle-y me-2 btn btn-light btn-sm rounded-circle shadow" @click.stop="next">
                <i class="bi bi-chevron-right"></i>
            </button>
        </template>

        <!-- Dots or Thumbnails -->
        <div v-if="content.showDots !== false && slides.length > 1" class="d-flex justify-content-center gap-2 py-2 bg-white border-top">
            <button
                v-for="(s, sIdx) in slides"
                :key="s.id"
                type="button"
                class="btn btn-sm p-0 border-0"
                @click.stop="idx = sIdx"
            >
                <img v-if="tpl === 'thumbnail_nav'" :src="s.imageUrl" class="rounded-2 object-fit-cover border" :class="idx === sIdx ? 'border-primary border-2' : 'opacity-50'" style="width: 48px; height: 32px;" />
                <span v-else class="d-inline-block rounded-circle" :class="idx === sIdx ? 'bg-primary' : 'bg-secondary bg-opacity-25'" style="width: 10px; height: 10px;"></span>
            </button>
        </div>
    </div>
</template>
