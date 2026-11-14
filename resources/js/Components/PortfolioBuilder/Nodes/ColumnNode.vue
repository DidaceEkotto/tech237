<script setup>
import { computed } from 'vue';
import RichModuleRenderer from '../Modules/RichModuleRenderer.vue';
import { resolveResponsiveStyles } from '@/Composables/PortfolioBuilder/useResponsiveStyles';

const props = defineProps({
    column: { type: Object, required: true },
    viewport: { type: String, default: 'desktop' },
    isEditor: { type: Boolean, default: false },
    selectedId: { type: String, default: null },
    portfolioUser: { type: Object, default: () => ({}) },
    pages: { type: Array, default: () => [] },
    hydratedData: { type: Object, default: () => ({}) },
    dragState: { type: Object, default: null },
});

const emit = defineEmits([
    'select',
    'add-module-prompt',
    'duplicate-module',
    'delete-module',
    'drop-module',
    'drag-start-module',
]);

const colClass = computed(() => {
    const w = props.column.width || 12;
    if (props.viewport === 'mobile') return 'col-12';
    return `col-md-${w}`;
});

const colStyle = computed(() => resolveResponsiveStyles(props.column.styles, props.viewport));

function onDrop(e) {
    if (!props.isEditor) return;
    e.preventDefault();
    e.stopPropagation();
    emit('drop-module', { columnId: props.column.id });
}
</script>

<template>
    <div
        :class="[colClass, { 'pb-editor-col position-relative': isEditor }]"
        :style="colStyle"
        @dragover.prevent
        @drop="onDrop"
    >
        <div
            v-for="mod in (column.modules || [])"
            :key="mod.id"
            class="position-relative mb-2"
            :class="{
                'pb-module-box': isEditor,
                'pb-selected-module': isEditor && selectedId === mod.id,
            }"
            :draggable="isEditor"
            @dragstart.stop="isEditor && emit('drag-start-module', mod.id)"
            @click.stop="isEditor && emit('select', 'module', mod.id)"
        >
            <!-- Editor Floating Actions -->
            <div
                v-if="isEditor && selectedId === mod.id"
                class="position-absolute top-0 end-0 translate-middle-y d-flex gap-1 bg-primary text-white rounded-pill px-2 py-1 shadow-sm"
                style="z-index: 30; font-size: 11px;"
                @click.stop
            >
                <span class="fw-semibold px-1">{{ mod.type }}</span>
                <button type="button" class="btn btn-link btn-sm text-white p-0 px-1" title="Dupliquer" @click="emit('duplicate-module', mod.id)">
                    <i class="bi bi-copy"></i>
                </button>
                <button type="button" class="btn btn-link btn-sm text-white p-0 px-1" title="Supprimer" @click="emit('delete-module', mod.id)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <RichModuleRenderer
                :module="mod"
                :viewport="viewport"
                :is-editor="isEditor"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :hydrated-data="hydratedData"
            />
        </div>

        <!-- Empty Column Dropzone in Editor -->
        <button
            v-if="isEditor"
            type="button"
            class="btn btn-sm w-100 border border-dashed text-muted py-2 mt-1 rounded-3 pb-add-mod-btn"
            @click.stop="emit('add-module-prompt', column.id)"
        >
            <i class="bi bi-plus-circle me-1"></i>Ajouter / Déposer un module
        </button>
    </div>
</template>

<style scoped>
.pb-editor-col {
    min-height: 54px;
    border: 1px dashed rgba(148, 163, 184, 0.35);
    border-radius: 12px;
    padding: 8px;
}
.pb-module-box {
    cursor: pointer;
    border: 1px solid transparent;
    border-radius: 12px;
    transition: border-color 0.15s ease;
}
.pb-module-box:hover {
    border-color: rgba(79, 70, 229, 0.45);
}
.pb-selected-module {
    border: 2px solid #4f46e5 !important;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}
.pb-add-mod-btn {
    background: rgba(248, 250, 252, 0.7);
    font-size: 12px;
}
.pb-add-mod-btn:hover {
    background: #eef2ff;
    color: #4f46e5 !important;
    border-color: #4f46e5 !important;
}
</style>
