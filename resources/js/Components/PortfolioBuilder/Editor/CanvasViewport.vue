<script setup>
import { computed } from 'vue';
import SectionNode from '../Nodes/SectionNode.vue';

const props = defineProps({
    tree: { type: Object, required: true },
    viewport: { type: String, default: 'desktop' },
    selectedId: { type: String, default: null },
    portfolioUser: { type: Object, default: () => ({}) },
    pages: { type: Array, default: () => [] },
    hydratedData: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
    'select',
    'clear-select',
    'add-section',
    'move-section',
    'duplicate-section',
    'delete-section',
    'save-section-template',
    'add-row',
    'delete-row',
    'add-module-prompt',
    'duplicate-module',
    'delete-module',
    'drop-module',
    'drag-start-module',
]);

const frameWidth = computed(() => {
    if (props.viewport === 'mobile') return '390px';
    if (props.viewport === 'tablet') return '768px';
    return '100%';
});
</script>

<template>
    <main class="flex-grow-1 bg-secondary bg-opacity-10 overflow-y-auto p-3 p-md-4" @click="emit('clear-select')">
        <div
            class="mx-auto bg-white shadow-sm rounded-4 min-vh-100 p-3 transition-all"
            :style="{ width: frameWidth, maxWidth: '100%' }"
        >
            <SectionNode
                v-for="(sec, idx) in (tree.sections || [])"
                :key="sec.id"
                :section="sec"
                :index="idx"
                :total="tree.sections.length"
                :viewport="viewport"
                :is-editor="true"
                :selected-id="selectedId"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :hydrated-data="hydratedData"
                @select="(k, id) => emit('select', k, id)"
                @move-section="(id, dir) => emit('move-section', id, dir)"
                @duplicate-section="(id) => emit('duplicate-section', id)"
                @delete-section="(id) => emit('delete-section', id)"
                @save-section-template="(s) => emit('save-section-template', s)"
                @add-row="(sid, cols) => emit('add-row', sid, cols)"
                @delete-row="(sid, rid) => emit('delete-row', sid, rid)"
                @add-module-prompt="(colId) => emit('add-module-prompt', colId)"
                @duplicate-module="(id) => emit('duplicate-module', id)"
                @delete-module="(id) => emit('delete-module', id)"
                @drop-module="(payload) => emit('drop-module', payload)"
                @drag-start-module="(id) => emit('drag-start-module', id)"
            />

            <div class="text-center py-4">
                <button
                    type="button"
                    class="btn btn-outline-primary rounded-pill px-4 fw-semibold"
                    @click.stop="emit('add-section', null)"
                >
                    <i class="bi bi-plus-circle me-2"></i>Ajouter une Section
                </button>
            </div>
        </div>
    </main>
</template>
