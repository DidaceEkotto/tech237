<script setup>
import { ref, computed } from 'vue';
import { EMOJI_CATALOG } from '@/Composables/PortfolioBuilder/fontsAndAnimations';

const emit = defineEmits(['pick']);
const open = ref(false);
const search = ref('');

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return EMOJI_CATALOG;
    return EMOJI_CATALOG.filter((e) => e.name.includes(q) || e.char.includes(q));
});
</script>

<template>
    <div class="position-relative d-inline-block">
        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0" style="font-size: 11px;" @click="open = !open">
            😀 Emoji
        </button>
        <div
            v-if="open"
            class="position-absolute end-0 mt-1 p-2 bg-white border rounded-3 shadow-lg"
            style="width: 240px; z-index: 1200;"
        >
            <input v-model="search" type="text" class="form-control form-control-sm mb-2" placeholder="Chercher emoji..." />
            <div class="d-flex flex-wrap gap-1" style="max-height: 150px; overflow-y: auto;">
                <button
                    v-for="e in filtered"
                    :key="e.char"
                    type="button"
                    class="btn btn-sm btn-light p-1"
                    style="font-size: 16px; line-height: 1;"
                    @click="emit('pick', e.char); open = false;"
                >
                    {{ e.char }}
                </button>
            </div>
        </div>
    </div>
</template>
