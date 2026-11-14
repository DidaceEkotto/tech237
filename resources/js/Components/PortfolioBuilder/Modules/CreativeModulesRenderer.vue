<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    module: { type: Object, required: true },
    isEditor: { type: Boolean, default: false },
});

const c = computed(() => props.module.content || {});

// Before / After slider state
const baPercent = ref(Number(props.module.content?.initialPercent ?? 50));

// Tabs state
const activeTabIdx = ref(0);

// Countdown state
const nowTick = ref(Date.now());
let countdownTimer = null;
onMounted(() => {
    if (props.module.type === 'countdown') {
        countdownTimer = setInterval(() => {
            nowTick.value = Date.now();
        }, 1000);
    }
});
onUnmounted(() => {
    if (countdownTimer) clearInterval(countdownTimer);
});

const countdownParts = computed(() => {
    const target = new Date(c.value.targetDate || '2026-12-31T23:59').getTime();
    const diff = Math.max(0, target - nowTick.value);
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((diff / (1000 * 60)) % 60);
    const seconds = Math.floor((diff / 1000) % 60);
    return [
        { label: 'Jours', val: String(days).padStart(2, '0') },
        { label: 'Heures', val: String(hours).padStart(2, '0') },
        { label: 'Minutes', val: String(minutes).padStart(2, '0') },
        { label: 'Secondes', val: String(seconds).padStart(2, '0') },
    ];
});
</script>

<template>
    <!-- Bento Grid -->
    <div v-if="module.type === 'bento_grid'">
        <h3 v-if="c.title" class="fw-bold mb-3">{{ c.title }}</h3>
        <div class="row g-3">
            <div
                v-for="card in (c.cards || [])"
                :key="card.id"
                :class="card.span === 2 ? 'col-md-8' : 'col-md-4'"
            >
                <div
                    class="p-4 rounded-4 h-100 shadow-sm d-flex flex-column justify-content-between"
                    :style="{ backgroundColor: card.bgColor || '#f8fafc', color: card.textColor || '#0f172a' }"
                >
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <i v-if="card.icon" class="bi fs-3" :class="card.icon"></i>
                        <span v-if="card.badge" class="badge bg-white bg-opacity-25 text-reset border rounded-pill px-2 py-1">{{ card.badge }}</span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">{{ card.title }}</h5>
                        <p class="small mb-0 opacity-75">{{ card.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Before / After Image Comparator -->
    <div v-else-if="module.type === 'before_after'">
        <h3 v-if="c.title" class="fw-bold mb-3">{{ c.title }}</h3>
        <div
            class="position-relative rounded-4 overflow-hidden shadow select-none"
            :style="{ height: `${c.heightPx || 360}px` }"
        >
            <img :src="c.afterUrl" alt="Après" class="w-100 h-100 object-fit-cover d-block" />
            <div
                class="position-absolute top-0 start-0 h-100 overflow-hidden"
                :style="{ width: `${baPercent}%` }"
            >
                <img
                    :src="c.beforeUrl"
                    alt="Avant"
                    class="h-100 object-fit-cover d-block"
                    :style="{ width: `${10000 / Math.max(1, baPercent)}%`, maxWidth: 'none' }"
                />
            </div>
            <span v-if="c.beforeLabel" class="position-absolute top-0 start-0 m-3 badge bg-dark bg-opacity-75">{{ c.beforeLabel }}</span>
            <span v-if="c.afterLabel" class="position-absolute top-0 end-0 m-3 badge bg-primary bg-opacity-75">{{ c.afterLabel }}</span>
            <div
                class="position-absolute top-0 bottom-0 bg-white"
                :style="{ left: `${baPercent}%`, width: '3px', transform: 'translateX(-50%)', pointerEvents: 'none' }"
            >
                <div
                    class="position-absolute top-50 start-50 translate-middle rounded-circle bg-white text-dark shadow d-flex align-items-center justify-content-center"
                    style="width: 34px; height: 34px;"
                >
                    <i class="bi bi-arrows-expand"></i>
                </div>
            </div>
            <input
                v-model.number="baPercent"
                type="range"
                min="0"
                max="100"
                class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                style="cursor: ew-resize; z-index: 5;"
                @click.stop
            />
        </div>
    </div>

    <!-- Countdown Timer (with Background Image, Filters & Gradient) -->
    <div
        v-else-if="module.type === 'countdown'"
        class="position-relative p-4 p-md-5 rounded-4 text-center shadow-sm overflow-hidden"
        :style="{ backgroundColor: c.bgColor || '#0f172a', color: c.textColor || '#ffffff' }"
    >
        <img
            v-if="c.bgImage"
            :src="c.bgImage"
            alt="Fond"
            class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover"
            :style="{
                filter: `brightness(${c.brightness ?? 100}%) contrast(${c.contrast ?? 100}%) grayscale(${c.grayscale ?? 0}%) blur(${c.blur ?? 0}px)`,
                zIndex: 0,
            }"
        />
        <div
            v-if="c.gradientEnabled"
            class="position-absolute top-0 start-0 w-100 h-100"
            :style="{
                background: `linear-gradient(${c.gradientDirection || '135deg'}, ${c.gradientColor1 || '#4f46e5'}, ${c.gradientColor2 || '#06b6d4'})`,
                opacity: c.gradientOpacity ?? 0.55,
                zIndex: 1,
            }"
        ></div>
        <div class="position-relative" style="z-index: 2;">
            <h3 v-if="c.title" class="fw-bold mb-1">{{ c.title }}</h3>
            <p v-if="c.subtitle" class="small opacity-75 mb-4">{{ c.subtitle }}</p>
            <div class="d-flex justify-content-center gap-2 gap-md-3 flex-wrap">
                <div
                    v-for="part in countdownParts"
                    :key="part.label"
                    class="px-3 py-2 rounded-4 border border-white border-opacity-25"
                    style="min-width: 84px; background: rgba(255,255,255,0.08); backdrop-filter: blur(4px);"
                >
                    <div class="fs-2 fw-bolder" :style="{ color: c.accentColor || '#818cf8' }">{{ part.val }}</div>
                    <div class="small opacity-75">{{ part.label }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bars -->
    <div v-else-if="module.type === 'progress_bars'" class="p-4 rounded-4 border bg-white shadow-sm">
        <h3 v-if="c.title" class="fw-bold mb-3">{{ c.title }}</h3>
        <div v-for="bar in (c.items || [])" :key="bar.id" class="mb-3">
            <div class="d-flex justify-content-between small fw-semibold mb-1">
                <span>{{ bar.label }}</span>
                <span>{{ bar.percent }}%</span>
            </div>
            <div class="progress rounded-pill" style="height: 10px;">
                <div
                    class="progress-bar rounded-pill"
                    role="progressbar"
                    :style="{ width: `${Math.min(100, Math.max(0, bar.percent || 0))}%`, backgroundColor: bar.color || '#4f46e5', transition: 'width 0.5s ease' }"
                ></div>
            </div>
        </div>
    </div>

    <!-- Marquee Ticker -->
    <div
        v-else-if="module.type === 'marquee_ticker'"
        class="rounded-4 overflow-hidden py-3 px-2"
        :style="{ backgroundColor: c.bgColor || '#0f172a', color: c.textColor || '#ffffff' }"
    >
        <div class="pb-marquee-track d-flex gap-5 align-items-center fw-bold" :style="{ animationDuration: `${c.speedSec || 20}s` }">
            <span v-for="(item, idx) in [...(c.items || []), ...(c.items || [])]" :key="idx" class="text-nowrap d-inline-flex align-items-center gap-2">
                <i class="bi bi-stars text-warning"></i>{{ item }}
            </span>
        </div>
    </div>

    <!-- Interactive Tabs -->
    <div v-else-if="module.type === 'tabs_content'" class="p-4 rounded-4 border bg-white shadow-sm">
        <div class="d-flex flex-wrap gap-2 border-bottom pb-3 mb-3">
            <button
                v-for="(t, ti) in (c.tabs || [])"
                :key="t.id"
                type="button"
                class="btn btn-sm rounded-pill px-3 fw-semibold"
                :class="activeTabIdx === ti ? 'text-white' : 'btn-light'"
                :style="activeTabIdx === ti ? { backgroundColor: c.accentColor || '#4f46e5' } : undefined"
                @click.stop="activeTabIdx = ti"
            >
                <i v-if="t.icon" class="bi me-1" :class="t.icon"></i>{{ t.label }}
            </button>
        </div>
        <div v-if="(c.tabs || [])[activeTabIdx]">
            <h4 class="fw-bold mb-2">{{ c.tabs[activeTabIdx].title }}</h4>
            <p class="text-secondary mb-0" style="white-space: pre-line;">{{ c.tabs[activeTabIdx].body }}</p>
        </div>
    </div>
</template>

<style scoped>
.pb-marquee-track {
    width: max-content;
    animation: pbMarquee linear infinite;
}
@keyframes pbMarquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>
