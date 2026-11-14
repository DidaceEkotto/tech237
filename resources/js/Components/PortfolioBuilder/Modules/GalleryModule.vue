<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    content: { type: Object, required: true },
});

const activeSlide = ref(0);
const beforeRatio = ref(50);
const lightboxItem = ref(null);

const items = computed(() => props.content.items || []);
const tpl = computed(() => props.content.template || 'grid_3');

function prevSlide() {
    if (!items.value.length) return;
    activeSlide.value = (activeSlide.value - 1 + items.value.length) % items.value.length;
}
function nextSlide() {
    if (!items.value.length) return;
    activeSlide.value = (activeSlide.value + 1) % items.value.length;
}
</script>

<template>
    <div class="pb-gallery">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 v-if="content.title" class="fw-bold mb-0">{{ content.title }}</h3>
            <span class="badge bg-light text-secondary border">{{ tpl }}</span>
        </div>

        <!-- Carousel / Fullscreen Showcase -->
        <div v-if="['carousel_slider', 'fullscreen_showcase'].includes(tpl) && items.length" class="position-relative rounded-4 overflow-hidden shadow">
            <img
                :src="items[activeSlide]?.url"
                :alt="items[activeSlide]?.title"
                class="w-100 object-fit-cover"
                :style="{ height: tpl === 'fullscreen_showcase' ? '440px' : '320px' }"
            />
            <div class="position-absolute bottom-0 start-0 end-0 p-3 bg-dark bg-opacity-50 text-white d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">{{ items[activeSlide]?.title }}</div>
                    <small class="opacity-75">{{ items[activeSlide]?.category }}</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle" @click.stop="prevSlide"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle" @click.stop="nextSlide"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
        </div>

        <!-- Before / After Comparator -->
        <div v-else-if="tpl === 'before_after' && items.length >= 2" class="position-relative rounded-4 overflow-hidden border" style="height: 320px;">
            <img :src="items[1].url" class="w-100 h-100 object-fit-cover" alt="Après" />
            <div class="position-absolute top-0 start-0 bottom-0 overflow-hidden" :style="{ width: beforeRatio + '%' }">
                <img :src="items[0].url" class="h-100 object-fit-cover" style="width: 100vw; max-width: none;" alt="Avant" />
            </div>
            <input
                v-model="beforeRatio"
                type="range"
                min="5"
                max="95"
                class="form-range position-absolute bottom-0 start-50 translate-middle-x mb-3 px-4"
                style="max-width: 320px; z-index: 5;"
            />
        </div>

        <!-- Horizontal Scroll -->
        <div v-else-if="tpl === 'horizontal_scroll'" class="d-flex gap-3 overflow-auto pb-2">
            <div
                v-for="item in items"
                :key="item.id"
                class="flex-shrink-0 rounded-4 overflow-hidden border bg-white shadow-sm"
                style="width: 280px;"
            >
                <img :src="item.url" :alt="item.title" class="w-100 object-fit-cover" style="height: 190px;" />
                <div class="p-3">
                    <div class="fw-bold small">{{ item.title }}</div>
                    <div class="text-muted xsmall">{{ item.category }}</div>
                </div>
            </div>
        </div>

        <!-- Grid / Masonry / Bento / Polaroid / Minimal -->
        <div v-else class="row g-3">
            <div
                v-for="(item, idx) in items"
                :key="item.id"
                :class="tpl === 'grid_4' ? 'col-6 col-md-3' : (tpl === 'bento_mosaic' && idx === 0 ? 'col-md-8' : 'col-md-4')"
            >
                <div
                    class="rounded-4 overflow-hidden border bg-white shadow-sm h-100 cursor-pointer"
                    :class="{ 'p-2 pb-3': tpl === 'polaroid_stack' }"
                    @click.stop="lightboxItem = item"
                >
                    <img :src="item.url" :alt="item.title" class="w-100 object-fit-cover rounded-3" style="height: 200px;" />
                    <div class="p-2 px-3">
                        <div class="fw-bold small">{{ item.title }}</div>
                        <div class="text-muted" style="font-size: 12px;">{{ item.category }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal -->
        <div
            v-if="lightboxItem"
            class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center p-3"
            style="z-index: 2000;"
            @click="lightboxItem = null"
        >
            <div class="bg-white rounded-4 overflow-hidden shadow-lg" style="max-width: 720px; width: 100%;" @click.stop>
                <img :src="lightboxItem.url" :alt="lightboxItem.title" class="w-100 object-fit-cover" style="max-height: 70vh;" />
                <div class="p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">{{ lightboxItem.title }}</div>
                        <small class="text-muted">{{ lightboxItem.category }}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-dark rounded-pill" @click="lightboxItem = null">Fermer</button>
                </div>
            </div>
        </div>
    </div>
</template>
