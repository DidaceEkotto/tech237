<script setup>
import { ref } from 'vue';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';

const props = defineProps({
    show: { type: Boolean, default: false },
    mediaList: { type: Array, default: () => [] },
    siteId: { type: Number, default: null },
});

const emit = defineEmits(['close', 'select', 'uploaded']);

const uploading = ref(false);
const error = ref('');

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
</script>

<template>
    <div
        v-if="show"
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center p-3"
        style="z-index: 2100;"
        @click="emit('close')"
    >
        <div class="bg-white rounded-4 shadow-lg p-4 w-100" style="max-width: 780px; max-height: 85vh; overflow-y: auto;" @click.stop>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-images me-2 text-primary"></i>Médiathèque</h5>
                <button type="button" class="btn-close" @click="emit('close')"></button>
            </div>

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
                    <div
                        class="border rounded-3 overflow-hidden cursor-pointer h-100 bg-white shadow-sm"
                        @click="emit('select', m.url)"
                    >
                        <img :src="m.url" :alt="m.filename" class="w-100 object-fit-cover" style="height: 110px;" />
                        <div class="p-2 small text-truncate">{{ m.filename }}</div>
                    </div>
                </div>
                <div v-if="!mediaList.length" class="col-12 text-center text-muted py-4 small">
                    Aucun média importé pour le moment.
                </div>
            </div>
        </div>
    </div>
</template>
