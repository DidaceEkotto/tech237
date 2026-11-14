<script setup>
import { ref, watch, nextTick } from 'vue';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';

const props = defineProps({
    show: { type: Boolean, default: false },
    mediaList: { type: Array, default: () => [] },
    siteId: { type: Number, default: null },
});

const emit = defineEmits(['close', 'select', 'uploaded', 'updated']);

const uploading = ref(false);
const error = ref('');

// Image Editor State
const editingItem = ref(null);
const editFilename = ref('');
const brightness = ref(100);
const contrast = ref(100);
const grayscale = ref(0);
const sepia = ref(0);
const cropAspect = ref('original'); // original | 16:9 | 1:1 | 4:3
const savingEdit = ref(false);
const canvasRef = ref(null);
let sourceImg = null;

async function handleFileUpload(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    uploading.value = true;
    error.value = '';
    try {
        const fd = new FormData();
        fd.append('file', file);
        if (props.siteId) fd.append('site_id', props.siteId);
        const res = await apiClient.post('/espace-utilisateur/portfolio-builder/media', fd);
        emit('uploaded', res.data.media);
        emit('select', res.data.media.url);
    } catch (err) {
        error.value = err.response?.data?.message || 'Erreur lors de l’import.';
    } finally {
        uploading.value = false;
    }
}

function openImageEditor(item) {
    editingItem.value = item;
    editFilename.value = item.filename;
    brightness.value = 100;
    contrast.value = 100;
    grayscale.value = 0;
    sepia.value = 0;
    cropAspect.value = 'original';

    nextTick(() => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            sourceImg = img;
            renderCanvas();
        };
        img.src = item.url;
    });
}

function renderCanvas() {
    const canvas = canvasRef.value;
    if (!canvas || !sourceImg) return;
    let sw = sourceImg.naturalWidth;
    let sh = sourceImg.naturalHeight;
    let sx = 0;
    let sy = 0;

    if (cropAspect.value === '1:1') {
        const size = Math.min(sw, sh);
        sx = (sw - size) / 2;
        sy = (sh - size) / 2;
        sw = size;
        sh = size;
    } else if (cropAspect.value === '16:9') {
        const targetH = sw * (9 / 16);
        if (targetH <= sh) {
            sy = (sh - targetH) / 2;
            sh = targetH;
        }
    } else if (cropAspect.value === '4:3') {
        const targetH = sw * (3 / 4);
        if (targetH <= sh) {
            sy = (sh - targetH) / 2;
            sh = targetH;
        }
    }

    const maxW = 900;
    const scale = sw > maxW ? maxW / sw : 1;
    canvas.width = Math.round(sw * scale);
    canvas.height = Math.round(sh * scale);

    const ctx = canvas.getContext('2d');
    ctx.filter = `brightness(${brightness.value}%) contrast(${contrast.value}%) grayscale(${grayscale.value}%) sepia(${sepia.value}%)`;
    ctx.drawImage(sourceImg, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height);
}

watch([brightness, contrast, grayscale, sepia, cropAspect], renderCanvas);

async function saveEditedImage() {
    if (!editingItem.value || !canvasRef.value) return;
    savingEdit.value = true;
    try {
        const blob = await new Promise((resolve) => canvasRef.value.toBlob(resolve, 'image/jpeg', 0.9));
        const fd = new FormData();
        fd.append('filename', editFilename.value);
        if (blob) {
            fd.append('edited_file', blob, editFilename.value || 'edited.jpg');
        }
        const res = await apiClient.post(`/espace-utilisateur/portfolio-builder/media/${editingItem.value.id}/update`, fd);
        emit('updated', res.data.media);
        editingItem.value = null;
    } catch (e) {
        error.value = e.response?.data?.message || 'Erreur lors de la sauvegarde.';
    } finally {
        savingEdit.value = false;
    }
}
</script>

<template>
    <div
        v-if="show"
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center p-3"
        style="z-index: 2100;"
        @click="emit('close')"
    >
        <div class="bg-white rounded-4 shadow-lg p-4 w-100" style="max-width: 860px; max-height: 88vh; overflow-y: auto;" @click.stop>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-images me-2 text-primary"></i>
                    {{ editingItem ? 'Éditeur d’Image (Rogner, Filtres & Nom)' : 'Médiathèque' }}
                </h5>
                <button type="button" class="btn-close" @click="emit('close')"></button>
            </div>

            <!-- IMAGE EDITOR VIEW -->
            <div v-if="editingItem" class="row g-3">
                <div class="col-md-7 text-center bg-light rounded-3 p-3 border">
                    <canvas ref="canvasRef" class="img-fluid rounded-3 shadow-sm" style="max-height: 360px;"></canvas>
                </div>
                <div class="col-md-5">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Nom du fichier</label>
                        <input v-model="editFilename" type="text" class="form-control form-control-sm" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Rognage (Ratio)</label>
                        <select v-model="cropAspect" class="form-select form-select-sm">
                            <option value="original">Original</option>
                            <option value="1:1">Carré (1:1)</option>
                            <option value="16:9">Paysage (16:9)</option>
                            <option value="4:3">Standard (4:3)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label xsmall mb-0">Luminosité ({{ brightness }}%)</label>
                        <input v-model="brightness" type="range" min="40" max="180" class="form-range" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label xsmall mb-0">Contraste ({{ contrast }}%)</label>
                        <input v-model="contrast" type="range" min="40" max="180" class="form-range" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label xsmall mb-0">Noir &amp; Blanc ({{ grayscale }}%)</label>
                        <input v-model="grayscale" type="range" min="0" max="100" class="form-range" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label xsmall mb-0">Sépia ({{ sepia }}%)</label>
                        <input v-model="sepia" type="range" min="0" max="100" class="form-range" />
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" @click="editingItem = null">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill flex-grow-1" :disabled="savingEdit" @click="saveEditedImage">
                            <span v-if="savingEdit" class="spinner-border spinner-border-sm me-1"></span>Enregistrer l’image
                        </button>
                    </div>
                </div>
            </div>

            <!-- GALLERY VIEW -->
            <template v-else>
                <div class="p-3 rounded-3 bg-light border mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="small text-secondary">Importer une nouvelle image (JPG, PNG, WebP — max 5 Mo)</div>
                    <label class="btn btn-sm btn-primary rounded-pill px-3 mb-0 cursor-pointer">
                        <span v-if="uploading" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="bi bi-cloud-upload me-1"></i>Téléverser
                        <input type="file" accept="image/*" class="d-none" @change="handleFileUpload" />
                    </label>
                </div>
                <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>

                <div class="row g-3">
                    <div v-for="m in mediaList" :key="m.id" class="col-6 col-md-3">
                        <div class="border rounded-3 overflow-hidden h-100 bg-white shadow-sm d-flex flex-column">
                            <img :src="m.url" :alt="m.filename" class="w-100 object-fit-cover cursor-pointer" style="height: 110px;" @click="emit('select', m.url)" />
                            <div class="p-2 small text-truncate">{{ m.filename }}</div>
                            <div class="px-2 pb-2 d-flex gap-1 mt-auto">
                                <button type="button" class="btn btn-xs btn-primary flex-grow-1 py-0" style="font-size:11px;" @click="emit('select', m.url)">Choisir</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0" style="font-size:11px;" title="Rogner / Filtres" @click="openImageEditor(m)">
                                    <i class="bi bi-sliders"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="!mediaList.length" class="col-12 text-center text-muted py-4 small">
                        Aucun média importé pour le moment.
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
