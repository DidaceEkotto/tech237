<script setup>
import { ref, computed } from 'vue';
import { MODULE_CATALOG } from '@/Composables/PortfolioBuilder/moduleRegistry';

const props = defineProps({
    tree: { type: Object, required: true },
    pages: { type: Array, default: () => [] },
    activePage: { type: Object, default: null },
    templates: { type: Array, default: () => [] },
    editingMode: { type: String, default: 'page' },
});

const emit = defineEmits([
    'add-module-section',
    'drag-start-new-module',
    'select-node',
    'switch-page',
    'create-page',
    'delete-page',
    'switch-mode',
    'insert-template',
    'open-media',
]);

const activeTab = ref('modules'); // modules | layers | pages | theme | templates
const searchModule = ref('');
const newPageTitle = ref('');

const filteredModules = computed(() => {
    const q = searchModule.value.trim().toLowerCase();
    if (!q) return MODULE_CATALOG;
    return MODULE_CATALOG.filter(
        (m) => m.label.toLowerCase().includes(q) || m.description.toLowerCase().includes(q)
    );
});

function handleCreatePage() {
    if (!newPageTitle.value.trim()) return;
    emit('create-page', newPageTitle.value.trim());
    newPageTitle.value = '';
}
</script>

<template>
    <aside class="bg-white border-end d-flex flex-column h-100" style="width: 300px; min-width: 280px;">
        <!-- Sidebar Tabs -->
        <div class="nav nav-pills nav-fill p-2 border-bottom bg-light gap-1" style="font-size: 11px;">
            <button type="button" class="nav-link py-1 px-2" :class="{ active: activeTab === 'modules' }" @click="activeTab = 'modules'">
                <i class="bi bi-grid-fill d-block"></i>Modules
            </button>
            <button type="button" class="nav-link py-1 px-2" :class="{ active: activeTab === 'layers' }" @click="activeTab = 'layers'">
                <i class="bi bi-layers d-block"></i>Structure
            </button>
            <button type="button" class="nav-link py-1 px-2" :class="{ active: activeTab === 'pages' }" @click="activeTab = 'pages'">
                <i class="bi bi-file-earmark-text d-block"></i>Pages
            </button>
            <button type="button" class="nav-link py-1 px-2" :class="{ active: activeTab === 'theme' }" @click="activeTab = 'theme'">
                <i class="bi bi-window-stack d-block"></i>Thème
            </button>
            <button type="button" class="nav-link py-1 px-2" :class="{ active: activeTab === 'templates' }" @click="activeTab = 'templates'">
                <i class="bi bi-bookmarks d-block"></i>Modèles
            </button>
        </div>

        <div class="p-3 overflow-y-auto flex-grow-1">
            <!-- MODULES TAB -->
            <div v-if="activeTab === 'modules'">
                <input
                    v-model="searchModule"
                    type="text"
                    class="form-control form-control-sm mb-3 rounded-pill"
                    placeholder="Rechercher un module..."
                />
                <div class="row g-2">
                    <div v-for="mod in filteredModules" :key="mod.type" class="col-6">
                        <div
                            class="p-2 rounded-3 border bg-light-subtle text-center cursor-pointer h-100 pb-mod-card"
                            draggable="true"
                            @dragstart="emit('drag-start-new-module', mod.type)"
                            @click="emit('add-module-section', mod.type)"
                        >
                            <i class="bi fs-4 text-primary d-block mb-1" :class="mod.icon"></i>
                            <div class="fw-semibold" style="font-size: 12px;">{{ mod.label }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LAYERS / STRUCTURE TAB -->
            <div v-else-if="activeTab === 'layers'">
                <div v-for="(sec, sIdx) in (tree.sections || [])" :key="sec.id" class="mb-2 border rounded-3 p-2 bg-light">
                    <div class="fw-bold small cursor-pointer text-primary" @click="emit('select-node', 'section', sec.id)">
                        <i class="bi bi-square me-1"></i>Section #{{ sIdx + 1 }}
                    </div>
                    <div v-for="(row, rIdx) in (sec.rows || [])" :key="row.id" class="ps-2 mt-1">
                        <div class="small fw-semibold text-success cursor-pointer" @click="emit('select-node', 'row', row.id)">
                            <i class="bi bi-layout-three-columns me-1"></i>Ligne #{{ rIdx + 1 }}
                        </div>
                        <div v-for="col in (row.columns || [])" :key="col.id" class="ps-2">
                            <div
                                v-for="mod in (col.modules || [])"
                                :key="mod.id"
                                class="small py-1 px-2 mt-1 rounded bg-white border cursor-pointer d-flex align-items-center justify-content-between"
                                @click="emit('select-node', 'module', mod.id)"
                            >
                                <span><i class="bi bi-puzzle me-1 text-secondary"></i>{{ mod.type }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAGES TAB -->
            <div v-else-if="activeTab === 'pages'">
                <div class="input-group input-group-sm mb-3">
                    <input v-model="newPageTitle" type="text" class="form-control" placeholder="Nouvelle page..." @keyup.enter="handleCreatePage" />
                    <button type="button" class="btn btn-primary" @click="handleCreatePage">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                <div class="list-group">
                    <div
                        v-for="p in pages"
                        :key="p.id"
                        class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2"
                        :class="{ active: editingMode === 'page' && activePage?.id === p.id }"
                    >
                        <button type="button" class="btn btn-link p-0 text-start text-decoration-none flex-grow-1" :class="editingMode === 'page' && activePage?.id === p.id ? 'text-white' : 'text-dark'" @click="emit('switch-page', p.id)">
                            <i class="bi me-1" :class="p.is_homepage ? 'bi-house-fill' : 'bi-file-earmark'"></i>
                            <span class="small fw-semibold">{{ p.title }}</span>
                        </button>
                        <button
                            v-if="!p.is_homepage"
                            type="button"
                            class="btn btn-link btn-sm p-0 text-danger"
                            title="Supprimer"
                            @click="emit('delete-page', p.id)"
                        >
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- THEME BUILDER TAB -->
            <div v-else-if="activeTab === 'theme'" class="d-grid gap-2">
                <p class="small text-muted">
                    Modifiez l’en-tête et le pied de page globaux appliqués à tout le site.
                </p>
                <button
                    type="button"
                    class="btn btn-sm text-start p-3 rounded-3 border"
                    :class="editingMode === 'header' ? 'btn-primary text-white' : 'btn-light'"
                    @click="emit('switch-mode', 'header')"
                >
                    <i class="bi bi-window-dock me-2"></i><strong>Header Global</strong>
                    <div class="small opacity-75">Barre de navigation & logo</div>
                </button>
                <button
                    type="button"
                    class="btn btn-sm text-start p-3 rounded-3 border"
                    :class="editingMode === 'footer' ? 'btn-primary text-white' : 'btn-light'"
                    @click="emit('switch-mode', 'footer')"
                >
                    <i class="bi bi-window-desktop me-2"></i><strong>Footer Global</strong>
                    <div class="small opacity-75">Pied de page & liens</div>
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary mt-2"
                    @click="emit('open-media')"
                >
                    <i class="bi bi-images me-1"></i>Ouvrir la Médiathèque
                </button>
            </div>

            <!-- TEMPLATES TAB -->
            <div v-else-if="activeTab === 'templates'">
                <p class="small text-muted mb-2">Vos sections et modèles sauvegardés :</p>
                <div v-if="!templates.length" class="small text-muted text-center py-4 border rounded-3">
                    Cliquez sur <i class="bi bi-bookmark-plus"></i> sur une section pour l’enregistrer ici.
                </div>
                <div v-for="tpl in templates" :key="tpl.id" class="p-2 border rounded-3 mb-2 bg-light d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold small">{{ tpl.name }}</div>
                        <span class="badge bg-secondary" style="font-size: 10px;">{{ tpl.scope }}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill" @click="emit('insert-template', tpl)">
                        Insérer
                    </button>
                </div>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.pb-mod-card:hover {
    border-color: #4f46e5 !important;
    background-color: #eef2ff !important;
}
</style>
