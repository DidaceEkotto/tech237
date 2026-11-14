<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: 'bi-star-fill' },
    label: { type: String, default: 'Icône Bootstrap' },
});

const emit = defineEmits(['update:modelValue']);

const search = ref('');
const open = ref(false);

const POPULAR_ICONS = [
    'bi-house-door-fill', 'bi-person-badge', 'bi-person-circle', 'bi-briefcase-fill',
    'bi-code-slash', 'bi-laptop', 'bi-terminal-fill', 'bi-cpu', 'bi-layers',
    'bi-images', 'bi-camera-fill', 'bi-play-circle-fill', 'bi-youtube', 'bi-film',
    'bi-envelope-fill', 'bi-chat-dots-fill', 'bi-telephone-fill', 'bi-geo-alt-fill',
    'bi-rocket-takeoff-fill', 'bi-stars', 'bi-lightning-charge-fill', 'bi-award-fill',
    'bi-trophy-fill', 'bi-patch-check-fill', 'bi-shield-check', 'bi-heart-fill',
    'bi-bookmark-star-fill', 'bi-graph-up-arrow', 'bi-bar-chart-fill', 'bi-kanban',
    'bi-grid-fill', 'bi-layout-split', 'bi-palette-fill', 'bi-brush-fill',
    'bi-pen-fill', 'bi-file-earmark-text-fill', 'bi-folder-fill', 'bi-cloud-arrow-up-fill',
    'bi-globe', 'bi-link-45deg', 'bi-share-fill', 'bi-qr-code', 'bi-cart-fill',
    'bi-bag-check-fill', 'bi-credit-card-fill', 'bi-cash-stack', 'bi-calendar-event-fill',
    'bi-clock-fill', 'bi-compass-fill', 'bi-map-fill', 'bi-music-note-beamed',
    'bi-mic-fill', 'bi-headphones', 'bi-book-fill', 'bi-mortarboard-fill',
    'bi-lightbulb-fill', 'bi-gear-fill', 'bi-sliders', 'bi-wrench-adjustable',
    'bi-tools', 'bi-bug-fill', 'bi-database-fill', 'bi-server', 'bi-hdd-network-fill',
    'bi-wifi', 'bi-phone-fill', 'bi-tablet-fill', 'bi-display', 'bi-tv-fill',
    'bi-arrow-right-circle-fill', 'bi-check-circle-fill', 'bi-plus-circle-fill',
    'bi-info-circle-fill', 'bi-question-circle-fill', 'bi-exclamation-triangle-fill',
    'bi-github', 'bi-linkedin', 'bi-twitter-x', 'bi-instagram', 'bi-facebook', 'bi-whatsapp',
];

const filteredIcons = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return POPULAR_ICONS;
    return POPULAR_ICONS.filter((ic) => ic.toLowerCase().includes(q));
});

function selectIcon(icon) {
    emit('update:modelValue', icon);
    open.value = false;
}
</script>

<template>
    <div class="pb-icon-picker">
        <label v-if="label" class="form-label small fw-semibold text-secondary mb-1">{{ label }}</label>
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white">
                <i class="bi" :class="modelValue || 'bi-star'"></i>
            </span>
            <input
                type="text"
                class="form-control"
                :value="modelValue"
                placeholder="ex: bi-house-door-fill"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <button type="button" class="btn btn-outline-secondary" @click="open = !open">
                <i class="bi bi-grid-3x3-gap"></i>
            </button>
        </div>

        <div v-if="open" class="mt-2 p-2 border rounded-3 bg-white shadow-sm">
            <input
                v-model="search"
                type="text"
                class="form-control form-control-sm mb-2"
                placeholder="Rechercher une icône (ex: house, video, chart)..."
            />
            <div class="d-grid gap-1" style="grid-template-columns: repeat(6, minmax(0, 1fr)); max-height: 180px; overflow-y: auto;">
                <button
                    v-for="ic in filteredIcons"
                    :key="ic"
                    type="button"
                    class="btn btn-sm d-flex align-items-center justify-content-center p-2"
                    :class="modelValue === ic ? 'btn-primary' : 'btn-light border'"
                    :title="ic"
                    @click="selectIcon(ic)"
                >
                    <i class="bi" :class="ic"></i>
                </button>
            </div>
        </div>
    </div>
</template>
