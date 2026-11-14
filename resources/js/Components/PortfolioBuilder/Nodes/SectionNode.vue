<script setup>
import { computed } from 'vue';
import RowNode from './RowNode.vue';
import { resolveResponsiveStyles } from '@/Composables/PortfolioBuilder/useResponsiveStyles';

const props = defineProps({
    section: { type: Object, required: true },
    index: { type: Number, default: 0 },
    total: { type: Number, default: 1 },
    viewport: { type: String, default: 'desktop' },
    isEditor: { type: Boolean, default: false },
    selectedId: { type: String, default: null },
    portfolioUser: { type: Object, default: () => ({}) },
    pages: { type: Array, default: () => [] },
    hydratedData: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
    'select',
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

const sectionStyle = computed(() => resolveResponsiveStyles(props.section.styles, props.viewport));
</script>

<template>
    <section
        :id="section.advanced?.customId || undefined"
        class="position-relative"
        :class="[
            section.advanced?.cssClass || '',
            {
                'pb-editor-section my-2 rounded-4': isEditor,
                'pb-selected-section': isEditor && selectedId === section.id,
            }
        ]"
        :style="{
            backgroundColor: sectionStyle.backgroundColor,
            paddingTop: sectionStyle.paddingTop,
            paddingBottom: sectionStyle.paddingBottom,
            paddingLeft: sectionStyle.paddingLeft,
            paddingRight: sectionStyle.paddingRight,
        }"
        @click.stop="isEditor && emit('select', 'section', section.id)"
    >
        <!-- Section Toolbar -->
        <div
            v-if="isEditor"
            class="position-absolute top-0 end-0 m-2 d-flex align-items-center gap-1 bg-dark text-white rounded-pill px-2 py-1 shadow-sm"
            style="z-index: 30; font-size: 11px; opacity: 0.9;"
            @click.stop
        >
            <span class="fw-semibold px-1">{{ section.label || 'Section' }}</span>
            <button type="button" class="btn btn-link btn-sm text-white p-0 px-1" :disabled="index === 0" title="Monter" @click="emit('move-section', section.id, -1)">
                <i class="bi bi-arrow-up"></i>
            </button>
            <button type="button" class="btn btn-link btn-sm text-white p-0 px-1" :disabled="index === total - 1" title="Descendre" @click="emit('move-section', section.id, 1)">
                <i class="bi bi-arrow-down"></i>
            </button>
            <button type="button" class="btn btn-link btn-sm text-white p-0 px-1" title="Enregistrer comme modèle" @click="emit('save-section-template', section)">
                <i class="bi bi-bookmark-plus"></i>
            </button>
            <button type="button" class="btn btn-link btn-sm text-white p-0 px-1" title="Dupliquer" @click="emit('duplicate-section', section.id)">
                <i class="bi bi-copy"></i>
            </button>
            <button type="button" class="btn btn-link btn-sm text-danger p-0 px-1" title="Supprimer" @click="emit('delete-section', section.id)">
                <i class="bi bi-trash"></i>
            </button>
        </div>

        <div
            class="mx-auto"
            :style="{ maxWidth: section.advanced?.fullWidth ? '100%' : (sectionStyle.maxWidth || '1180px') }"
        >
            <RowNode
                v-for="row in (section.rows || [])"
                :key="row.id"
                :row="row"
                :section-id="section.id"
                :viewport="viewport"
                :is-editor="isEditor"
                :selected-id="selectedId"
                :portfolio-user="portfolioUser"
                :pages="pages"
                :hydrated-data="hydratedData"
                @select="(k, id) => emit('select', k, id)"
                @delete-row="(sid, rid) => emit('delete-row', sid, rid)"
                @add-module-prompt="(colId) => emit('add-module-prompt', colId)"
                @duplicate-module="(id) => emit('duplicate-module', id)"
                @delete-module="(id) => emit('delete-module', id)"
                @drop-module="(payload) => emit('drop-module', payload)"
                @drag-start-module="(id) => emit('drag-start-module', id)"
            />

            <!-- Quick Row Adder in Editor -->
            <div v-if="isEditor" class="d-flex justify-content-center gap-2 mt-2">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 11px;" @click.stop="emit('add-row', section.id, 1)">
                    + 1 Col
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 11px;" @click.stop="emit('add-row', section.id, 2)">
                    + 2 Cols
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 11px;" @click.stop="emit('add-row', section.id, 3)">
                    + 3 Cols
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
.pb-editor-section {
    border: 1px dashed rgba(59, 130, 246, 0.35);
}
.pb-selected-section {
    border: 2px solid #2563eb !important;
}
</style>
