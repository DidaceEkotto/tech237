<script setup>
import { computed } from 'vue';
import ColumnNode from './ColumnNode.vue';
import { resolveResponsiveStyles } from '@/Composables/PortfolioBuilder/useResponsiveStyles';

const props = defineProps({
    row: { type: Object, required: true },
    sectionId: { type: String, required: true },
    viewport: { type: String, default: 'desktop' },
    isEditor: { type: Boolean, default: false },
    selectedId: { type: String, default: null },
    portfolioUser: { type: Object, default: () => ({}) },
    pages: { type: Array, default: () => [] },
    hydratedData: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
    'select',
    'delete-row',
    'add-module-prompt',
    'duplicate-module',
    'delete-module',
    'drop-module',
    'drag-start-module',
]);

const rowStyle = computed(() => resolveResponsiveStyles(props.row.styles, props.viewport));
</script>

<template>
    <div
        class="position-relative mb-3"
        :class="{
            'pb-editor-row p-2 rounded-3': isEditor,
            'pb-selected-row': isEditor && selectedId === row.id,
        }"
        @click.stop="isEditor && emit('select', 'row', row.id)"
    >
        <div
            v-if="isEditor && selectedId === row.id"
            class="position-absolute top-0 start-0 translate-middle-y d-flex gap-1 bg-success text-white rounded-pill px-2 py-1 shadow-sm"
            style="z-index: 25; font-size: 11px;"
            @click.stop
        >
            <span class="fw-semibold px-1">Ligne ({{ row.columns?.length || 1 }} col)</span>
            <button type="button" class="btn btn-link btn-sm text-white p-0 px-1" title="Supprimer la ligne" @click="emit('delete-row', sectionId, row.id)">
                <i class="bi bi-trash"></i>
            </button>
        </div>

        <div class="row g-3" :style="rowStyle">
            <ColumnNode
                v-for="col in (row.columns || [])"
                :key="col.id"
                :column="col"
                :viewport="viewport"
                :is-editor="isEditor"
                :selected-id="selectedId"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :hydrated-data="hydratedData"
                @select="(k, id) => emit('select', k, id)"
                @add-module-prompt="(colId) => emit('add-module-prompt', colId)"
                @duplicate-module="(id) => emit('duplicate-module', id)"
                @delete-module="(id) => emit('delete-module', id)"
                @drop-module="(payload) => emit('drop-module', payload)"
                @drag-start-module="(id) => emit('drag-start-module', id)"
            />
        </div>
    </div>
</template>

<style scoped>
.pb-editor-row {
    border: 1px dashed rgba(16, 185, 129, 0.35);
}
.pb-selected-row {
    border: 2px solid #10b981 !important;
}
</style>
