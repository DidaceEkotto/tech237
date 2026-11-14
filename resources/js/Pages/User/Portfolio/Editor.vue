<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';
import TopToolbar from '@/Components/PortfolioBuilder/Editor/TopToolbar.vue';
import LeftSidebar from '@/Components/PortfolioBuilder/Editor/LeftSidebar.vue';
import CanvasViewport from '@/Components/PortfolioBuilder/Editor/CanvasViewport.vue';
import RightInspector from '@/Components/PortfolioBuilder/Editor/RightInspector.vue';
import MediaLibraryModal from '@/Components/PortfolioBuilder/Editor/MediaLibraryModal.vue';
import GuideModal from '@/Components/PortfolioBuilder/Editor/GuideModal.vue';
import { useBuilderState } from '@/Composables/PortfolioBuilder/useBuilderState';
import { useDragAndDrop } from '@/Composables/PortfolioBuilder/useDragAndDrop';
import { MODULE_CATALOG } from '@/Composables/PortfolioBuilder/moduleRegistry';

const props = defineProps({
    site: { type: Object, required: true },
    pages: { type: Array, default: () => [] },
    activePage: { type: Object, default: null },
    revisions: { type: Array, default: () => [] },
    themeParts: { type: Object, default: () => ({}) },
    media: { type: Array, default: () => [] },
    templates: { type: Array, default: () => [] },
    hydratedData: { type: Object, default: () => ({}) },
    portfolioUser: { type: Object, default: () => ({}) },
});

const localSite = ref({ ...props.site });
const localPages = ref([...props.pages]);
const currentPage = ref(props.activePage);
const localRevisions = ref([...props.revisions]);
const localMedia = ref([...props.media]);
const localTemplates = ref([...props.templates]);
const localThemeParts = ref({ ...props.themeParts });

const editingMode = ref('page');
const viewport = ref('desktop');
const showLeftSidebar = ref(true);
const showRightInspector = ref(true);
const saving = ref(false);
const publishing = ref(false);
const toast = ref('');

const mediaModalOpen = ref(false);
const mediaCallback = ref(null);
const modulePromptColumnId = ref(null);
const seoModalOpen = ref(false);
const guideModalOpen = ref(false);

const seoForm = ref({
    title: currentPage.value?.seo_meta?.title || localSite.value.name,
    description: currentPage.value?.seo_meta?.description || localSite.value.description || '',
    keywords: currentPage.value?.seo_meta?.keywords || '',
    og_image: currentPage.value?.seo_meta?.og_image || '',
    favicon: currentPage.value?.seo_meta?.favicon || '',
});

const builder = useBuilderState(currentPage.value?.draft_tree || { schema_version: 1, sections: [] });
const dnd = useDragAndDrop();

function showToast(msg) {
    toast.value = msg;
    setTimeout(() => {
        if (toast.value === msg) toast.value = '';
    }, 3200);
}

function handleSwitchPage(pageId) {
    const target = localPages.value.find((p) => p.id === pageId);
    if (!target) return;
    editingMode.value = 'page';
    currentPage.value = target;
    builder.loadTree(target.draft_tree || { schema_version: 1, sections: [] });
}

function handleSwitchMode(mode) {
    editingMode.value = mode;
    if (mode === 'header') {
        builder.loadTree(localThemeParts.value.header?.tree || { schema_version: 1, sections: [] });
    } else if (mode === 'footer') {
        builder.loadTree(localThemeParts.value.footer?.tree || { schema_version: 1, sections: [] });
    } else if (currentPage.value) {
        builder.loadTree(currentPage.value.draft_tree || { schema_version: 1, sections: [] });
    }
}

async function handleSave() {
    saving.value = true;
    try {
        if (editingMode.value === 'header' || editingMode.value === 'footer') {
            const res = await apiClient.post(`/espace-utilisateur/portfolio-builder/sites/${localSite.value.id}/theme-parts`, {
                type: editingMode.value,
                tree: builder.tree.value,
                is_active: true,
            });
            localThemeParts.value[editingMode.value] = res.data.themePart;
            builder.isDirty.value = false;
            showToast(`${editingMode.value === 'header' ? 'Header' : 'Footer'} enregistré !`);
        } else if (currentPage.value) {
            const res = await apiClient.post(`/espace-utilisateur/portfolio-builder/pages/${currentPage.value.id}/save`, {
                tree: builder.tree.value,
                label: 'Sauvegarde Studio',
            });
            currentPage.value = res.data.page;
            const idx = localPages.value.findIndex((p) => p.id === res.data.page.id);
            if (idx !== -1) localPages.value[idx] = res.data.page;
            localRevisions.value = res.data.revisions || [];
            builder.isDirty.value = false;
            showToast('Brouillon enregistré !');
        }
    } catch (e) {
        showToast(e.response?.data?.message || 'Erreur lors de la sauvegarde.');
    } finally {
        saving.value = false;
    }
}

async function handlePublish() {
    await handleSave();
    publishing.value = true;
    try {
        const res = await apiClient.post(`/espace-utilisateur/portfolio-builder/sites/${localSite.value.id}/publish`);
        localSite.value.is_published = true;
        showToast(res.data.message || 'Site publié en ligne !');
    } catch (e) {
        showToast(e.response?.data?.message || 'Erreur lors de la publication.');
    } finally {
        publishing.value = false;
    }
}

async function handleUnpublish() {
    try {
        await apiClient.post(`/espace-utilisateur/portfolio-builder/sites/${localSite.value.id}/unpublish`);
        localSite.value.is_published = false;
        showToast('Site dépublié (mode brouillon).');
    } catch {
        showToast('Erreur lors de la dépublication.');
    }
}

async function saveSeoSettings() {
    if (!currentPage.value) return;
    try {
        await apiClient.post(`/espace-utilisateur/portfolio-builder/sites/${localSite.value.id}/settings`, {
            global_settings: { ...(localSite.value.global_settings || {}), seo: seoForm.value },
        });
        seoModalOpen.value = false;
        showToast('Paramètres SEO & Carte de partage enregistrés.');
    } catch {
        showToast('Erreur SEO.');
    }
}

async function handleRestoreRevision(revId) {
    if (!currentPage.value) return;
    try {
        const res = await apiClient.post(`/espace-utilisateur/portfolio-builder/pages/${currentPage.value.id}/revisions/${revId}/restore`);
        currentPage.value = res.data.page;
        localRevisions.value = res.data.revisions || [];
        builder.loadTree(res.data.page.draft_tree);
        showToast('Révision restaurée.');
    } catch {
        showToast('Impossible de restaurer cette révision.');
    }
}

async function handleCreatePage(title) {
    try {
        const res = await apiClient.post(`/espace-utilisateur/portfolio-builder/sites/${localSite.value.id}/pages`, { title });
        localPages.value.push(res.data.page);
        handleSwitchPage(res.data.page.id);
        showToast('Page créée.');
    } catch {
        showToast('Erreur lors de la création de page.');
    }
}

async function handleDeletePage(pageId) {
    try {
        await apiClient.delete(`/espace-utilisateur/portfolio-builder/pages/${pageId}`);
        localPages.value = localPages.value.filter((p) => p.id !== pageId);
        if (currentPage.value?.id === pageId && localPages.value.length) {
            handleSwitchPage(localPages.value[0].id);
        }
        showToast('Page supprimée.');
    } catch (e) {
        showToast(e.response?.data?.message || 'Erreur lors de la suppression.');
    }
}

async function handleSaveSectionTemplate(section) {
    const name = window.prompt('Nom de ce modèle de section :', section.label || 'Ma Section');
    if (!name) return;
    try {
        const res = await apiClient.post('/espace-utilisateur/portfolio-builder/templates', {
            name,
            scope: 'section',
            tree_payload: section,
        });
        localTemplates.value.unshift(res.data.template);
        showToast('Section ajoutée à vos modèles !');
    } catch {
        showToast('Erreur lors de l’enregistrement du modèle.');
    }
}

function handleInsertTemplate(tpl) {
    if (tpl.scope === 'section' && tpl.tree) {
        builder.insertTemplateSection(tpl.tree);
        showToast('Modèle inséré.');
    }
}

function handleDropModule({ columnId }) {
    const payload = dnd.dragPayload.value;
    if (!payload) return;
    if (payload.kind === 'new-module' && payload.moduleType) {
        builder.addModuleToColumn(columnId, payload.moduleType);
    } else if (payload.kind === 'move-module' && payload.moduleId) {
        builder.moveModule(payload.moduleId, columnId);
    }
    dnd.clearDrag();
}

function openMediaPicker(cb = null) {
    mediaCallback.value = cb;
    mediaModalOpen.value = true;
}

function handleMediaSelect(url) {
    if (typeof mediaCallback.value === 'function') {
        mediaCallback.value(url);
    }
    mediaModalOpen.value = false;
    mediaCallback.value = null;
}

function handleMediaUpdated(updated) {
    const idx = localMedia.value.findIndex((m) => m.id === updated.id);
    if (idx !== -1) localMedia.value[idx] = updated;
    showToast('Image modifiée dans la médiathèque.');
}

function addModuleFromPrompt(moduleType) {
    if (!modulePromptColumnId.value) return;
    builder.addModuleToColumn(modulePromptColumnId.value, moduleType);
    modulePromptColumnId.value = null;
}
</script>

<template>
    <Head :title="`Studio Builder — ${localSite.name}`" />

    <div class="d-flex flex-column vh-100 overflow-hidden bg-light">
        <TopToolbar
            :site="localSite"
            :active-page="currentPage"
            :editing-mode="editingMode"
            :viewport="viewport"
            :show-left-sidebar="showLeftSidebar"
            :show-right-inspector="showRightInspector"
            :can-undo="builder.canUndo.value"
            :can-redo="builder.canRedo.value"
            :is-dirty="builder.isDirty.value"
            :saving="saving"
            :publishing="publishing"
            :revisions="localRevisions"
            :portfolio-user="portfolioUser"
            @update:viewport="viewport = $event"
            @toggle-left="showLeftSidebar = !showLeftSidebar"
            @toggle-right="showRightInspector = !showRightInspector"
            @undo="builder.undo"
            @redo="builder.redo"
            @save="handleSave"
            @publish="handlePublish"
            @unpublish="handleUnpublish"
            @restore-revision="handleRestoreRevision"
            @open-seo="seoModalOpen = true"
            @open-guide="guideModalOpen = true"
        />

        <div
            v-if="toast"
            class="position-fixed bottom-0 start-50 translate-middle-x mb-3 px-4 py-2 bg-dark text-white rounded-pill shadow-lg small fw-semibold"
            style="z-index: 2500;"
        >
            <i class="bi bi-check-circle-fill text-success me-2"></i>{{ toast }}
        </div>

        <div class="d-flex flex-grow-1 overflow-hidden">
            <LeftSidebar
                v-show="showLeftSidebar"
                :tree="builder.tree.value"
                :pages="localPages"
                :active-page="currentPage"
                :templates="localTemplates"
                :editing-mode="editingMode"
                @add-module-section="(type) => builder.addSection(type)"
                @drag-start-new-module="(type) => dnd.startNewModuleDrag(type)"
                @select-node="(k, id) => builder.selectNode(k, id)"
                @switch-page="handleSwitchPage"
                @create-page="handleCreatePage"
                @delete-page="handleDeletePage"
                @switch-mode="handleSwitchMode"
                @insert-template="handleInsertTemplate"
                @open-media="openMediaPicker()"
            />

            <CanvasViewport
                :tree="builder.tree.value"
                :viewport="viewport"
                :selected-id="builder.selected.value?.id || null"
                :portfolio-user="portfolioUser"
                :pages="localPages"
                :active-page-slug="currentPage?.slug || ''"
                :site-type="localSite.site_type || 'onepage'"
                :hydrated-data="hydratedData"
                @select="(k, id) => builder.selectNode(k, id)"
                @clear-select="builder.clearSelection()"
                @add-section="(type) => builder.addSection(type)"
                @move-section="builder.moveSection"
                @duplicate-section="builder.duplicateSection"
                @delete-section="builder.deleteSection"
                @save-section-template="handleSaveSectionTemplate"
                @add-row="builder.addRow"
                @delete-row="builder.deleteRow"
                @add-module-prompt="(colId) => modulePromptColumnId = colId"
                @duplicate-module="builder.duplicateModule"
                @delete-module="builder.deleteModule"
                @drop-module="handleDropModule"
                @drag-start-module="(id) => dnd.startMoveModuleDrag(id)"
            />

            <RightInspector
                v-show="showRightInspector"
                :selected-node-data="builder.selectedNodeData.value"
                :viewport="viewport"
                :tree="builder.tree.value"
                :pages="localPages"
                :site-type="localSite.site_type || 'onepage'"
                :portfolio-user="portfolioUser"
                :hydrated-data="hydratedData"
                @change="builder.commitChange()"
                @pick-media="(cb) => openMediaPicker(cb)"
            />
        </div>

        <MediaLibraryModal
            :show="mediaModalOpen"
            :media-list="localMedia"
            :site-id="localSite.id"
            @close="mediaModalOpen = false"
            @select="handleMediaSelect"
            @uploaded="(m) => localMedia.unshift(m)"
            @updated="handleMediaUpdated"
        />

        <GuideModal :show="guideModalOpen" @close="guideModalOpen = false" />

        <!-- SEO & OpenGraph Modal -->
        <div v-if="seoModalOpen" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center p-3" style="z-index: 2200;" @click="seoModalOpen = false">
            <div class="bg-white rounded-4 shadow-lg p-4 w-100" style="max-width: 620px;" @click.stop>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-search text-primary me-2"></i>SEO, Favicon &amp; Carte de Partage</h5>
                    <button type="button" class="btn-close" @click="seoModalOpen = false"></button>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Titre SEO</label>
                    <input v-model="seoForm.title" type="text" class="form-control form-control-sm" />
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Méta Description</label>
                    <textarea v-model="seoForm.description" rows="2" class="form-control form-control-sm"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Mots-clés (séparés par virgule)</label>
                    <input v-model="seoForm.keywords" type="text" class="form-control form-control-sm" />
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Image OpenGraph</label>
                        <div class="input-group input-group-sm">
                            <input v-model="seoForm.og_image" type="text" class="form-control" />
                            <button type="button" class="btn btn-outline-primary" @click="openMediaPicker((u) => seoForm.og_image = u)"><i class="bi bi-images"></i></button>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Favicon</label>
                        <div class="input-group input-group-sm">
                            <input v-model="seoForm.favicon" type="text" class="form-control" />
                            <button type="button" class="btn btn-outline-primary" @click="openMediaPicker((u) => seoForm.favicon = u)"><i class="bi bi-images"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary rounded-pill px-4 w-100" @click="saveSeoSettings">Enregistrer le SEO</button>
            </div>
        </div>

        <!-- Quick Module Picker Modal -->
        <div
            v-if="modulePromptColumnId"
            class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center p-3"
            style="z-index: 2200;"
            @click="modulePromptColumnId = null"
        >
            <div class="bg-white rounded-4 shadow-lg p-4 w-100" style="max-width: 720px; max-height: 82vh; overflow-y: auto;" @click.stop>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Choisir un module à insérer</h6>
                    <button type="button" class="btn-close" @click="modulePromptColumnId = null"></button>
                </div>
                <div class="row g-2">
                    <div v-for="m in MODULE_CATALOG" :key="m.type" class="col-4 col-md-3">
                        <button type="button" class="btn btn-light border w-100 p-2 rounded-3 text-center h-100" @click="addModuleFromPrompt(m.type)">
                            <i class="bi fs-4 text-primary d-block mb-1" :class="m.icon"></i>
                            <div class="small fw-semibold">{{ m.label }}</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
