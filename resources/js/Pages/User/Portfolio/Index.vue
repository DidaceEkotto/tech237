<script setup>
import { ref } from 'vue';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';
import GuideModal from '@/Components/PortfolioBuilder/Editor/GuideModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    initialSites: { type: Array, default: () => [] },
    initialMedia: { type: Array, default: () => [] },
});

const sites = ref([...props.initialSites]);
const newSiteName = ref('');
const newSiteType = ref('onepage');
const creating = ref(false);
const toast = ref('');
const guideOpen = ref(false);

const statsModalSite = ref(null);
const statsData = ref(null);
const loadingStats = ref(false);

function showToast(msg) {
    toast.value = msg;
    setTimeout(() => {
        if (toast.value === msg) toast.value = '';
    }, 3400);
}

async function createSite() {
    if (!newSiteName.value.trim()) return;
    creating.value = true;
    try {
        const res = await apiClient.post('/espace-utilisateur/portfolio-builder/sites', {
            name: newSiteName.value.trim(),
            site_type: newSiteType.value,
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

async function renameSite(site, newName) {
    if (site.is_published || !newName.trim() || newName.trim() === site.name) return;
    try {
        const res = await apiClient.patch(`/espace-utilisateur/portfolio-builder/sites/${site.id}`, { name: newName.trim() });
        site.name = res.data.site.name;
        showToast('Nom du site mis à jour.');
    } catch (e) {
        showToast(e.response?.data?.message || 'Modification impossible.');
    }
}

async function migrateType(site) {
    const targetType = site.site_type === 'onepage' ? 'multipage' : 'onepage';
    try {
        const res = await apiClient.post(`/espace-utilisateur/portfolio-builder/sites/${site.id}/migrate-type`, { target_type: targetType });
        const idx = sites.value.findIndex((s) => s.id === site.id);
        if (idx !== -1) sites.value[idx] = res.data.site;
        showToast(res.data.message);
    } catch {
        showToast('Erreur lors de la migration.');
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
        sites.value = sites.value.map((s) => ({ ...s, is_primary: s.id === site.id }));
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

async function openStats(site) {
    statsModalSite.value = site;
    loadingStats.value = true;
    try {
        const res = await apiClient.get(`/espace-utilisateur/portfolio-builder/sites/${site.id}/stats`);
        statsData.value = res.data;
    } catch {
        statsData.value = { views_count: site.views_count || 0, unique_visitors_count: site.unique_visitors_count || 0, daily: [], top_pages: [] };
    } finally {
        loadingStats.value = false;
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
                        Créez des sites <strong>One-Page</strong> ou <strong>Multi-Pages</strong>, migrez en 1 clic, exportez en ZIP (HTML, PHP/SQLite, Laravel) et suivez vos statistiques.
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-3 fw-semibold btn-sm" @click="guideOpen = true">
                        <i class="bi bi-book-half me-1"></i>Guide d'utilisation
                    </button>
                    <a v-if="user?.username" :href="`/@${user.username}`" target="_blank" class="btn btn-outline-dark rounded-pill px-3 fw-semibold btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Voir /@{{ user.username }}
                    </a>
                </div>
            </div>
        </div>

        <div v-if="toast" class="alert alert-success rounded-4 py-2 px-3 small fw-semibold mb-3">
            <i class="bi bi-check-circle-fill me-2"></i>{{ toast }}
        </div>

        <!-- Create New Site (choose One-Page or Multi-Page) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center gap-2">
                <input
                    v-model="newSiteName"
                    type="text"
                    class="form-control rounded-pill"
                    style="max-width: 300px;"
                    placeholder="Nom d'un nouveau site..."
                    @keyup.enter="createSite"
                />
                <select v-model="newSiteType" class="form-select rounded-pill" style="max-width: 190px;">
                    <option value="onepage">Site One-Page</option>
                    <option value="multipage">Site Multi-Pages</option>
                </select>
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
                        <div class="mb-2">
                            <div class="input-group input-group-sm mb-2">
                                <input
                                    type="text"
                                    class="form-control fw-bold"
                                    :value="site.name"
                                    :disabled="site.is_published"
                                    :title="site.is_published ? 'Nom verrouillé tant que le site est publié' : 'Modifier le nom'"
                                    @change="renameSite(site, $event.target.value)"
                                />
                                <span v-if="site.is_published" class="input-group-text bg-light text-muted" title="Verrouillé en ligne">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                            </div>

                            <div class="d-flex flex-wrap gap-1">
                                <span v-if="site.is_primary" class="badge bg-primary rounded-pill">Principal</span>
                                <span class="badge rounded-pill" :class="site.is_published ? 'bg-success' : 'bg-secondary'">
                                    {{ site.is_published ? 'Publié' : 'Brouillon' }}
                                </span>
                                <span class="badge bg-info text-dark rounded-pill">
                                    {{ site.site_type === 'multipage' ? 'Multi-Pages' : 'One-Page' }}
                                </span>
                                <span class="badge bg-light text-dark border rounded-pill">
                                    {{ site.views_count || 0 }} vues
                                </span>
                            </div>
                        </div>

                        <p class="small text-muted mb-3 flex-grow-1">
                            Lien tenant : <code>/site/{{ site.slug }}</code>
                        </p>

                        <div class="d-grid gap-2">
                            <a :href="`/espace-utilisateur/portfolio-builder/sites/${site.id}/editor`" class="btn btn-dark rounded-pill fw-semibold">
                                <i class="bi bi-brush me-2"></i>Ouvrir l'Éditeur Visuel
                            </a>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm flex-grow-1 rounded-pill" :class="site.is_published ? 'btn-outline-warning' : 'btn-outline-success'" @click="togglePublish(site)">
                                    {{ site.is_published ? 'Dépublier' : 'Publier' }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill" title="Basculer One-Page / Multi-Pages en 1 clic" @click="migrateType(site)">
                                    <i class="bi bi-arrow-left-right me-1"></i>{{ site.site_type === 'onepage' ? '-> Multi' : '-> 1 Page' }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill" title="Statistiques" @click="openStats(site)">
                                    <i class="bi bi-bar-chart-fill"></i>
                                </button>
                            </div>

                            <div class="d-flex gap-1 justify-content-between pt-2 border-top">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill dropdown-toggle px-2 py-1" data-bs-toggle="dropdown" style="font-size:11px;">
                                        <i class="bi bi-file-zip me-1"></i>Exporter ZIP
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item small" :href="`/espace-utilisateur/portfolio-builder/sites/${site.id}/export?format=html`">HTML / CSS / JS + Images</a></li>
                                        <li><a class="dropdown-item small" :href="`/espace-utilisateur/portfolio-builder/sites/${site.id}/export?format=php`">PHP + SQLite + Mini Admin</a></li>
                                        <li><a class="dropdown-item small" :href="`/espace-utilisateur/portfolio-builder/sites/${site.id}/export?format=laravel`">Laravel + SQLite Starter</a></li>
                                    </ul>
                                </div>
                                <div class="d-flex gap-1">
                                    <button v-if="!site.is_primary" type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1" style="font-size:11px;" @click="setPrimary(site)">Principal</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" style="font-size:11px;" @click="duplicateSite(site)"><i class="bi bi-copy"></i></button>
                                    <button v-if="sites.length > 1" type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1" style="font-size:11px;" @click="deleteSite(site)"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Site Stats Modal -->
        <div v-if="statsModalSite" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center p-3" style="z-index: 2200;" @click="statsModalSite = null">
            <div class="bg-white rounded-4 shadow-lg p-4 w-100" style="max-width: 600px;" @click.stop>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Statistiques — {{ statsModalSite.name }}</h5>
                    <button type="button" class="btn-close" @click="statsModalSite = null"></button>
                </div>
                <div v-if="loadingStats" class="text-center py-4"><span class="spinner-border"></span></div>
                <div v-else-if="statsData">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-light border text-center">
                                <div class="fs-3 fw-bolder text-primary">{{ statsData.views_count }}</div>
                                <div class="small text-muted">Visites Totales</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-light border text-center">
                                <div class="fs-3 fw-bolder text-success">{{ statsData.unique_visitors_count }}</div>
                                <div class="small text-muted">Visiteurs Uniques</div>
                            </div>
                        </div>
                    </div>
                    <h6 class="fw-bold small mb-2">Activité des 14 derniers jours</h6>
                    <div class="table-responsive" style="max-height: 220px;">
                        <table class="table table-sm small">
                            <thead><tr><th>Date</th><th>Vues</th><th>Uniques</th></tr></thead>
                            <tbody>
                                <tr v-for="d in statsData.daily" :key="d.visited_date">
                                    <td>{{ d.visited_date }}</td>
                                    <td>{{ d.views }}</td>
                                    <td>{{ d.uniques }}</td>
                                </tr>
                                <tr v-if="!statsData.daily?.length"><td colspan="3" class="text-center text-muted">Aucune visite enregistrée pour le moment.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <GuideModal :show="guideOpen" @close="guideOpen = false" />
    </div>
</template>
