<script setup>
import { ref } from 'vue';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';

const props = defineProps({
    user: { type: Object, required: true },
    initialSites: { type: Array, default: () => [] },
    initialMedia: { type: Array, default: () => [] },
});

const sites = ref([...props.initialSites]);
const newSiteName = ref('');
const creating = ref(false);
const toast = ref('');

function showToast(msg) {
    toast.value = msg;
    setTimeout(() => {
        if (toast.value === msg) toast.value = '';
    }, 3200);
}

async function createSite() {
    if (!newSiteName.value.trim()) return;
    creating.value = true;
    try {
        const res = await apiClient.post('/espace-utilisateur/portfolio-builder/sites', {
            name: newSiteName.value.trim(),
        });
        sites.value.push(res.data.site);
        newSiteName.value = '';
        showToast('Nouveau site créé !');
    } catch (e) {
        showToast(e.response?.data?.message || 'Erreur lors de la création.');
    } finally {
        creating.value = false;
    }
}

async function duplicateSite(site) {
    try {
        const res = await apiClient.post(`/espace-utilisateur/portfolio-builder/sites/${site.id}/duplicate`);
        sites.value.push(res.data.site);
        showToast('Site dupliqué.');
    } catch {
        showToast('Erreur lors de la duplication.');
    }
}

async function setPrimary(site) {
    try {
        const res = await apiClient.patch(`/espace-utilisateur/portfolio-builder/sites/${site.id}`, { is_primary: true });
        sites.value = sites.value.map((s) => ({
            ...s,
            is_primary: s.id === site.id,
        }));
        showToast(`"${res.data.site.name}" est maintenant votre site principal.`);
    } catch {
        showToast('Erreur lors de la mise à jour.');
    }
}

async function togglePublish(site) {
    try {
        const endpoint = site.is_published
            ? `/espace-utilisateur/portfolio-builder/sites/${site.id}/unpublish`
            : `/espace-utilisateur/portfolio-builder/sites/${site.id}/publish`;
        const res = await apiClient.post(endpoint);
        const idx = sites.value.findIndex((s) => s.id === site.id);
        if (idx !== -1) sites.value[idx] = { ...sites.value[idx], ...res.data.site };
        showToast(res.data.message);
    } catch {
        showToast('Erreur lors du changement de statut.');
    }
}

async function deleteSite(site) {
    if (!window.confirm(`Supprimer définitivement le site "${site.name}" ?`)) return;
    try {
        await apiClient.delete(`/espace-utilisateur/portfolio-builder/sites/${site.id}`);
        sites.value = sites.value.filter((s) => s.id !== site.id);
        showToast('Site supprimé.');
    } catch (e) {
        showToast(e.response?.data?.message || 'Impossible de supprimer ce site.');
    }
}
</script>

<template>
    <div class="pb-dashboard-manager">
        <!-- Header Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-body p-4 bg-white d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 mb-2 fw-semibold">
                        <i class="bi bi-magic me-1"></i>Studio Website &amp; Portfolio Builder
                    </span>
                    <h3 class="fw-bold mb-1 text-dark">Constructeur Visuel de Sites &amp; Portfolios</h3>
                    <p class="text-muted small mb-0">
                        Créez, personnalisez et publiez vos sites multi-pages sans code (Sections, Lignes, Colonnes, Drag &amp; Drop, Modèles &amp; Theme Builder).
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a
                        v-if="user?.username"
                        :href="`/@${user.username}`"
                        target="_blank"
                        class="btn btn-outline-dark rounded-pill px-3 fw-semibold btn-sm"
                    >
                        <i class="bi bi-box-arrow-up-right me-1"></i>Voir /@{{ user.username }}
                    </a>
                </div>
            </div>
        </div>

        <div v-if="toast" class="alert alert-success rounded-4 py-2 px-3 small fw-semibold mb-3">
            <i class="bi bi-check-circle-fill me-2"></i>{{ toast }}
        </div>

        <!-- Create New Site -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center gap-2">
                <input
                    v-model="newSiteName"
                    type="text"
                    class="form-control rounded-pill"
                    style="max-width: 340px;"
                    placeholder="Nom d'un nouveau site (ex: Studio Freelance)..."
                    @keyup.enter="createSite"
                />
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" :disabled="creating" @click="createSite">
                    <span v-if="creating" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-plus-lg me-1"></i>Créer un site
                </button>
            </div>
        </div>

        <!-- Sites Grid -->
        <div class="row g-4">
            <div v-for="site in sites" :key="site.id" class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <h5 class="fw-bold mb-1">{{ site.name }}</h5>
                                <div class="d-flex flex-wrap gap-1">
                                    <span v-if="site.is_primary" class="badge bg-primary rounded-pill">Principal</span>
                                    <span class="badge rounded-pill" :class="site.is_published ? 'bg-success' : 'bg-secondary'">
                                        {{ site.is_published ? 'Publié' : 'Brouillon' }}
                                    </span>
                                    <span class="badge bg-light text-dark border rounded-pill">
                                        {{ site.pages?.length || 1 }} page(s)
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="small text-muted mb-3 flex-grow-1">
                            {{ site.description || 'Site modulable construit avec le Studio Visuel Tech237.' }}
                        </p>

                        <div class="d-grid gap-2">
                            <a
                                :href="`/espace-utilisateur/portfolio-builder/sites/${site.id}/editor`"
                                class="btn btn-dark rounded-pill fw-semibold"
                            >
                                <i class="bi bi-brush me-2"></i>Ouvrir l'Éditeur Visuel
                            </a>

                            <div class="d-flex gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm flex-grow-1 rounded-pill"
                                    :class="site.is_published ? 'btn-outline-warning' : 'btn-outline-success'"
                                    @click="togglePublish(site)"
                                >
                                    {{ site.is_published ? 'Dépublier' : 'Publier' }}
                                </button>
                                <button
                                    v-if="!site.is_primary"
                                    type="button"
                                    class="btn btn-sm btn-outline-primary rounded-pill"
                                    title="Définir comme site principal"
                                    @click="setPrimary(site)"
                                >
                                    Principal
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary rounded-pill"
                                    title="Dupliquer"
                                    @click="duplicateSite(site)"
                                >
                                    <i class="bi bi-copy"></i>
                                </button>
                                <button
                                    v-if="sites.length > 1"
                                    type="button"
                                    class="btn btn-sm btn-outline-danger rounded-pill"
                                    title="Supprimer"
                                    @click="deleteSite(site)"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
