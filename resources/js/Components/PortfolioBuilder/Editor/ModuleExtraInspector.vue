<script setup>
import { ref } from 'vue';
import BootstrapIconPicker from '../Controls/BootstrapIconPicker.vue';
import ColorGradientControl from '../Controls/ColorGradientControl.vue';
import EmojiPickerControl from '../Controls/EmojiPickerControl.vue';
import { uid, createRow } from '@/Composables/PortfolioBuilder/moduleRegistry';

const props = defineProps({
    node: { type: Object, required: true },
    sectionTargets: { type: Array, default: () => [] },
});

const emit = defineEmits(['change', 'pick-media']);

const mapQuery = ref('');
const mapResults = ref([]);
const searchingMap = ref(false);

function notify() {
    emit('change');
}

function appendEmoji(obj, key, emoji) {
    obj[key] = (obj[key] || '') + emoji;
    notify();
}

function nudgeOffset(content, dx, dy) {
    content.offsetX = (Number(content.offsetX) || 0) + dx;
    content.offsetY = (Number(content.offsetY) || 0) + dy;
    notify();
}

async function searchLocation() {
    const q = mapQuery.value.trim();
    if (!q) return;
    searchingMap.value = true;
    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(q)}`);
        mapResults.value = await res.json();
    } catch {
        mapResults.value = [];
    } finally {
        searchingMap.value = false;
    }
}

function applyMapResult(r) {
    props.node.content.address = r.display_name;
    props.node.content.lat = String(Number(r.lat).toFixed(5));
    props.node.content.lng = String(Number(r.lon).toFixed(5));
    mapResults.value = [];
    notify();
}
</script>

<template>
    <!-- ABOUT -->
    <template v-if="node.type === 'about'">
        <div class="border rounded-3 p-2 mb-2 bg-light small">
            <div class="fw-bold mb-1">Éléments affichés</div>
            <div class="row g-1">
                <div class="col-6"><label><input v-model="node.content.showEyebrow" type="checkbox" class="form-check-input me-1" @change="notify" /> Surtitre</label></div>
                <div class="col-6"><label><input v-model="node.content.showTitle" type="checkbox" class="form-check-input me-1" @change="notify" /> Titre</label></div>
                <div class="col-6"><label><input v-model="node.content.showBio" type="checkbox" class="form-check-input me-1" @change="notify" /> Bio</label></div>
                <div class="col-6"><label><input v-model="node.content.showImage" type="checkbox" class="form-check-input me-1" @change="notify" /> Image/Avatar</label></div>
                <div class="col-6"><label><input v-model="node.content.showHighlights" type="checkbox" class="form-check-input me-1" @change="notify" /> Points forts</label></div>
                <div class="col-6"><label><input v-model="node.content.showCta" type="checkbox" class="form-check-input me-1" @change="notify" /> Bouton CTA</label></div>
            </div>
        </div>
        <label class="form-label small fw-semibold">Nom affiché (si initiales)</label>
        <input v-model="node.content.displayName" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <label class="form-label small fw-semibold">Image (upload ou URL)</label>
        <div class="input-group input-group-sm mb-2">
            <input v-model="node.content.imageUrl" type="text" class="form-control" placeholder="URL image..." @input="notify" />
            <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { node.content.imageUrl = url; notify(); })"><i class="bi bi-images"></i></button>
        </div>
        <input v-if="node.content.showEyebrow !== false" v-model="node.content.eyebrow" type="text" class="form-control form-control-sm mb-2" placeholder="Surtitre" @input="notify" />
        <input v-if="node.content.showTitle !== false" v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" placeholder="Titre" @input="notify" />
        <textarea v-if="node.content.showBio !== false" v-model="node.content.bio" rows="3" class="form-control form-control-sm mb-2" placeholder="Biographie..." @input="notify"></textarea>

        <div v-if="node.content.showHighlights !== false" class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-bold">Points forts</span>
                <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-0" style="font-size:11px;" @click="if(!node.content.highlights) node.content.highlights = []; node.content.highlights.push('Nouveau point'); notify();">+ Point</button>
            </div>
            <div v-for="(h, hi) in (node.content.highlights || [])" :key="hi" class="d-flex gap-1 mb-1">
                <input v-model="node.content.highlights[hi]" type="text" class="form-control form-control-sm" @input="notify" />
                <button type="button" class="btn btn-sm btn-outline-danger" @click="node.content.highlights.splice(hi, 1); notify();"><i class="bi bi-trash"></i></button>
            </div>
        </div>
        <div v-if="node.content.showCta" class="row g-1 mb-2">
            <div class="col-6"><input v-model="node.content.ctaLabel" type="text" class="form-control form-control-sm" placeholder="Texte CTA" @input="notify" /></div>
            <div class="col-6"><input v-model="node.content.ctaUrl" type="text" class="form-control form-control-sm" placeholder="Lien / #ancre" @input="notify" /></div>
        </div>
    </template>

    <!-- BUTTON CTA -->
    <template v-else-if="node.type === 'button'">
        <label class="form-label small fw-semibold">Texte du bouton</label>
        <input v-model="node.content.label" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <label class="form-label small fw-semibold">Type de lien</label>
        <select v-model="node.content.linkType" class="form-select form-select-sm mb-2" @change="notify">
            <option value="anchor">Ancre de section (#...)</option>
            <option value="url">Lien URL (interne ou externe)</option>
        </select>
        <select v-if="node.content.linkType === 'anchor'" v-model="node.content.href" class="form-select form-select-sm mb-2" @change="notify">
            <option value="#contact">#contact</option>
            <option v-for="sec in sectionTargets" :key="sec.id" :value="`#${sec.id}`">{{ sec.label }}</option>
        </select>
        <input v-else v-model="node.content.href" type="text" class="form-control form-control-sm mb-2" placeholder="https://..." @input="notify" />
        <label class="d-block small mb-2">
            <input v-model="node.content.openInNewTab" type="checkbox" class="form-check-input me-1" @change="notify" />
            Ouvrir dans un nouvel onglet (auto pour liens externes)
        </label>
        <BootstrapIconPicker v-model="node.content.icon" label="Icône" @update:modelValue="notify" />
        <label class="form-label small fw-semibold mt-2">Alignement</label>
        <select v-model="node.content.align" class="form-select form-select-sm" @change="notify">
            <option value="left">Gauche</option>
            <option value="center">Centré</option>
            <option value="right">Droite</option>
        </select>
    </template>

    <!-- YOUTUBE VIDEO -->
    <template v-else-if="node.type === 'video'">
        <label class="form-label small fw-semibold">Titre de la section vidéo</label>
        <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-bold">Vidéos YouTube</span>
            <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-0" style="font-size:11px;" @click="node.content.videos.push({ id: uid('vid'), title: 'Nouvelle vidéo', youtubeUrl: '', duration: '03:00' }); notify();">+ Vidéo</button>
        </div>
        <div v-for="(v, vi) in (node.content.videos || [])" :key="v.id" class="p-2 border rounded-3 mb-2 bg-light">
            <input v-model="v.title" type="text" class="form-control form-control-sm mb-1" placeholder="Titre" @input="notify" />
            <input v-model="v.youtubeUrl" type="text" class="form-control form-control-sm mb-1" placeholder="https://www.youtube.com/watch?v=..." @input="notify" />
            <div class="d-flex gap-1">
                <input v-model="v.duration" type="text" class="form-control form-control-sm" placeholder="Durée" @input="notify" />
                <button type="button" class="btn btn-sm btn-outline-danger" @click="node.content.videos.splice(vi, 1); notify();"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    </template>

    <!-- VIMEO VIDEO -->
    <template v-else-if="node.type === 'vimeo'">
        <label class="form-label small fw-semibold">Titre</label>
        <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-bold">Vidéos Vimeo (ID ou URL)</span>
            <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-0" style="font-size:11px;" @click="node.content.videos.push({ id: uid('vm'), title: 'Vidéo Vimeo', vimeoUrl: '76979871', description: '' }); notify();">+ Vimeo</button>
        </div>
        <div v-for="(v, vi) in (node.content.videos || [])" :key="v.id" class="p-2 border rounded-3 mb-2 bg-light">
            <input v-model="v.title" type="text" class="form-control form-control-sm mb-1" placeholder="Titre" @input="notify" />
            <div class="d-flex gap-1">
                <input v-model="v.vimeoUrl" type="text" class="form-control form-control-sm" placeholder="ID (ex: 76979871) ou URL vimeo.com/..." @input="notify" />
                <button type="button" class="btn btn-sm btn-outline-danger" @click="node.content.videos.splice(vi, 1); notify();"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    </template>

    <!-- COUNTER KPI -->
    <template v-else-if="node.type === 'counter_kpi'">
        <ColorGradientControl v-model="node.content.accentColor" label="Couleur d'accent" :allow-gradient="false" @update:modelValue="notify" />
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-bold">Compteurs</span>
            <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-0" style="font-size:11px;" @click="node.content.items.push({ id: uid('kpi'), prefix: '', value: '100', suffix: '+', label: 'Indicateur', icon: 'bi-star-fill' }); notify();">+ KPI</button>
        </div>
        <div v-for="(k, ki) in (node.content.items || [])" :key="k.id" class="p-2 border rounded-3 mb-2 bg-light">
            <div class="row g-1 mb-1">
                <div class="col-3"><input v-model="k.prefix" type="text" class="form-control form-control-sm" placeholder="Préf." @input="notify" /></div>
                <div class="col-6"><input v-model="k.value" type="text" class="form-control form-control-sm" placeholder="Nombre" @input="notify" /></div>
                <div class="col-3"><input v-model="k.suffix" type="text" class="form-control form-control-sm" placeholder="Suff." @input="notify" /></div>
            </div>
            <input v-model="k.label" type="text" class="form-control form-control-sm mb-1" placeholder="Libellé" @input="notify" />
            <div class="d-flex justify-content-between align-items-center">
                <BootstrapIconPicker v-model="k.icon" label="" @update:modelValue="notify" />
                <button type="button" class="btn btn-sm btn-outline-danger ms-1" @click="node.content.items.splice(ki, 1); notify();"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    </template>

    <!-- FAQ ACCORDION -->
    <template v-else-if="node.type === 'faq_accordion'">
        <label class="form-label small fw-semibold">Titre FAQ</label>
        <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <label class="d-block small mb-2">
            <input v-model="node.content.allowMultiple" type="checkbox" class="form-check-input me-1" @change="notify" />
            Autoriser plusieurs réponses ouvertes simultanément
        </label>
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-bold">Questions / Réponses</span>
            <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-0" style="font-size:11px;" @click="node.content.items.push({ id: uid('faq'), question: 'Nouvelle question ?', answer: 'Votre réponse détaillée ici.', openByDefault: false }); notify();">+ Question</button>
        </div>
        <div v-for="(f, fi) in (node.content.items || [])" :key="f.id" class="p-2 border rounded-3 mb-2 bg-light">
            <input v-model="f.question" type="text" class="form-control form-control-sm mb-1" placeholder="Question" @input="notify" />
            <textarea v-model="f.answer" rows="2" class="form-control form-control-sm mb-1" placeholder="Réponse" @input="notify"></textarea>
            <div class="d-flex justify-content-between align-items-center small">
                <label><input v-model="f.openByDefault" type="checkbox" class="form-check-input me-1" @change="notify" /> Ouvert par défaut</label>
                <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="node.content.items.splice(fi, 1); notify();">Supprimer</button>
            </div>
        </div>
    </template>

    <!-- TESTIMONIALS -->
    <template v-else-if="node.type === 'testimonials'">
        <label class="form-label small fw-semibold">Titre</label>
        <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <label class="form-label small fw-semibold">Animation d'entrée</label>
        <select v-model="node.content.animation" class="form-select form-select-sm mb-2" @change="notify">
            <option value="none">Aucune</option>
            <option value="fade_up">Apparition Haut</option>
            <option value="fade_in">Fondu</option>
            <option value="zoom_in">Zoom</option>
            <option value="pulse">Pulsation douce</option>
        </select>
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-bold">Témoignages</span>
            <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-0" style="font-size:11px;" @click="node.content.items.push({ id: uid('test'), author: 'Client', role: 'Poste / Entreprise', quote: 'Excellent accompagnement !', rating: 5, avatarType: 'none', avatarUrl: '', gravatarEmail: '' }); notify();">+ Témoignage</button>
        </div>
        <div v-for="(t, ti) in (node.content.items || [])" :key="t.id" class="p-2 border rounded-3 mb-2 bg-light">
            <div class="row g-1 mb-1">
                <div class="col-6"><input v-model="t.author" type="text" class="form-control form-control-sm" placeholder="Auteur" @input="notify" /></div>
                <div class="col-6"><input v-model="t.role" type="text" class="form-control form-control-sm" placeholder="Rôle" @input="notify" /></div>
            </div>
            <textarea v-model="t.quote" rows="2" class="form-control form-control-sm mb-1" placeholder="Citation" @input="notify"></textarea>
            <div class="row g-1 mb-1">
                <div class="col-6">
                    <select v-model.number="t.rating" class="form-select form-select-sm" @change="notify">
                        <option :value="5">5 étoiles</option>
                        <option :value="4">4 étoiles</option>
                        <option :value="3">3 étoiles</option>
                    </select>
                </div>
                <div class="col-6">
                    <select v-model="t.avatarType" class="form-select form-select-sm" @change="notify">
                        <option value="none">Sans photo</option>
                        <option value="upload">Image / Upload</option>
                        <option value="gravatar">Gravatar (Email)</option>
                    </select>
                </div>
            </div>
            <div v-if="t.avatarType === 'upload'" class="input-group input-group-sm mb-1">
                <input v-model="t.avatarUrl" type="text" class="form-control" placeholder="URL photo" @input="notify" />
                <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { t.avatarUrl = url; notify(); })"><i class="bi bi-images"></i></button>
            </div>
            <input v-else-if="t.avatarType === 'gravatar'" v-model="t.gravatarEmail" type="email" class="form-control form-control-sm mb-1" placeholder="email@gravatar.com" @input="notify" />
            <button type="button" class="btn btn-link btn-sm text-danger p-0" style="font-size:11px;" @click="node.content.items.splice(ti, 1); notify();">Supprimer</button>
        </div>
    </template>

    <!-- BACK TO TOP -->
    <template v-else-if="node.type === 'back_to_top'">
        <label class="form-label small fw-semibold">Libellé</label>
        <input v-model="node.content.label" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <label class="d-block small mb-2"><input v-model="node.content.showLabel" type="checkbox" class="form-check-input me-1" @change="notify" /> Afficher le texte</label>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label small fw-semibold">Forme</label>
                <select v-model="node.content.shape" class="form-select form-select-sm" @change="notify">
                    <option value="pill">Pilule</option>
                    <option value="circle">Cercle</option>
                    <option value="square">Carré arrondi</option>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label small fw-semibold">Taille</label>
                <select v-model="node.content.size" class="form-select form-select-sm" @change="notify">
                    <option value="sm">Petit</option>
                    <option value="md">Moyen</option>
                    <option value="lg">Grand</option>
                </select>
            </div>
        </div>
        <label class="form-label small fw-semibold">Position</label>
        <select v-model="node.content.position" class="form-select form-select-sm mb-2" @change="notify">
            <option value="bottom-right">Bas Droite</option>
            <option value="bottom-left">Bas Gauche</option>
            <option value="bottom-center">Bas Centre</option>
        </select>
        <BootstrapIconPicker v-model="node.content.icon" label="Icône" @update:modelValue="notify" />
        <ColorGradientControl v-model="node.content.bgColor" label="Couleur fond" :allow-gradient="false" @update:modelValue="notify" />
        <ColorGradientControl v-model="node.content.textColor" label="Couleur texte" :allow-gradient="false" @update:modelValue="notify" />
        <label class="d-block small mt-1"><input v-model="node.content.floating" type="checkbox" class="form-check-input me-1" @change="notify" /> Bouton flottant fixe</label>
        <label class="d-block small"><input v-model="node.content.pulseAnimation" type="checkbox" class="form-check-input me-1" @change="notify" /> Animation pulsation</label>
    </template>

    <!-- PARALLAX -->
    <template v-else-if="node.type === 'parallax'">
        <label class="form-label small fw-semibold">Image de fond</label>
        <div class="input-group input-group-sm mb-2">
            <input v-model="node.content.imageUrl" type="text" class="form-control" @input="notify" />
            <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { node.content.imageUrl = url; notify(); })"><i class="bi bi-images"></i></button>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label small fw-semibold">Hauteur min (px)</label>
                <input v-model.number="node.content.minHeightPx" type="number" min="150" max="900" class="form-control form-control-sm" @input="notify" />
            </div>
            <div class="col-6">
                <label class="form-label small fw-semibold">Opacité voile</label>
                <input v-model.number="node.content.overlayOpacity" type="number" min="0" max="1" step="0.05" class="form-control form-control-sm" @input="notify" />
            </div>
        </div>
        <ColorGradientControl v-model="node.content.overlayColor" label="Couleur du voile" :allow-gradient="false" @update:modelValue="notify" />
        <label class="d-block small mb-1"><input v-model="node.content.showText" type="checkbox" class="form-check-input me-1" @change="notify" /> Afficher titre &amp; description</label>
        <template v-if="node.content.showText !== false">
            <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-1" placeholder="Titre" @input="notify" />
            <textarea v-model="node.content.subtitle" rows="2" class="form-control form-control-sm mb-2" placeholder="Description" @input="notify"></textarea>
        </template>
        <label class="d-block small mb-1"><input v-model="node.content.showCta" type="checkbox" class="form-check-input me-1" @change="notify" /> Afficher bouton CTA</label>
        <div v-if="node.content.showCta !== false" class="row g-1 mb-2">
            <div class="col-6"><input v-model="node.content.ctaLabel" type="text" class="form-control form-control-sm" placeholder="Texte CTA" @input="notify" /></div>
            <div class="col-6"><input v-model="node.content.ctaUrl" type="text" class="form-control form-control-sm" placeholder="Lien CTA" @input="notify" /></div>
        </div>
        <label class="form-label small fw-semibold">Lien de redirection global (optionnel)</label>
        <input v-model="node.content.redirectUrl" type="text" class="form-control form-control-sm mb-1" placeholder="https://..." @input="notify" />
        <label class="d-block small"><input v-model="node.content.openInNewTab" type="checkbox" class="form-check-input me-1" @change="notify" /> Ouvrir dans un nouvel onglet</label>
    </template>

    <!-- MAP EMBED WITH LOCATION SEARCH -->
    <template v-else-if="node.type === 'map_embed'">
        <label class="form-label small fw-semibold">Titre</label>
        <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <label class="form-label small fw-semibold">Rechercher un lieu / adresse</label>
        <div class="input-group input-group-sm mb-2">
            <input v-model="mapQuery" type="text" class="form-control" placeholder="Ex: Akwa Douala, Yaoundé..." @keyup.enter="searchLocation" />
            <button type="button" class="btn btn-primary" :disabled="searchingMap" @click="searchLocation">
                <i class="bi" :class="searchingMap ? 'bi-hourglass-split' : 'bi-search'"></i>
            </button>
        </div>
        <div v-if="mapResults.length" class="list-group mb-2 shadow-sm" style="max-height: 150px; overflow-y: auto;">
            <button
                v-for="r in mapResults"
                :key="r.place_id"
                type="button"
                class="list-group-item list-group-item-action small py-1"
                @click="applyMapResult(r)"
            >
                {{ r.display_name }}
            </button>
        </div>
        <label class="form-label small fw-semibold">Fournisseur</label>
        <select v-model="node.content.provider" class="form-select form-select-sm mb-2" @change="notify">
            <option value="openstreetmap">OpenStreetMap</option>
            <option value="google">Google Maps</option>
        </select>
        <label class="form-label small fw-semibold">Adresse</label>
        <input v-model="node.content.address" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <div class="row g-2">
            <div class="col-6"><input v-model="node.content.lat" type="text" class="form-control form-control-sm" placeholder="Lat" @input="notify" /></div>
            <div class="col-6"><input v-model="node.content.lng" type="text" class="form-control form-control-sm" placeholder="Lng" @input="notify" /></div>
        </div>
    </template>

    <!-- NESTED SECTION -->
    <template v-else-if="node.type === 'nested_section'">
        <ColorGradientControl v-model="node.content.bgColor" label="Fond du conteneur" :allow-gradient="false" @update:modelValue="notify" />
        <label class="form-label small fw-semibold">Image de fond</label>
        <div class="input-group input-group-sm mb-2">
            <input v-model="node.content.bgImage" type="text" class="form-control" @input="notify" />
            <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { node.content.bgImage = url; notify(); })"><i class="bi bi-images"></i></button>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label small fw-semibold">Arrondi (px)</label>
                <input v-model.number="node.content.borderRadius" type="number" class="form-control form-control-sm" @input="notify" />
            </div>
            <div class="col-6">
                <label class="form-label small fw-semibold">Padding (px)</label>
                <input v-model.number="node.content.padding" type="number" class="form-control form-control-sm" @input="notify" />
            </div>
        </div>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-xs btn-outline-primary flex-fill" @click="node.content.rows.push(createRow(1)); notify();">+1 Col</button>
            <button type="button" class="btn btn-xs btn-outline-primary flex-fill" @click="node.content.rows.push(createRow(2)); notify();">+2 Cols</button>
        </div>
    </template>

    <!-- NEW CREATIVE MODULES -->
    <template v-else-if="node.type === 'bento_grid'">
        <label class="form-label small fw-semibold">Titre</label>
        <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <button type="button" class="btn btn-xs btn-primary rounded-pill mb-2" @click="node.content.cards.push({ id: uid('bg'), title: 'Nouvelle carte', description: 'Description', icon: 'bi-star-fill', span: 1, bgColor: '#f8fafc', textColor: '#0f172a', badge: '' }); notify();">+ Carte Bento</button>
        <div v-for="(card, ci) in (node.content.cards || [])" :key="card.id" class="p-2 border rounded-3 mb-2 bg-light">
            <input v-model="card.title" type="text" class="form-control form-control-sm mb-1" placeholder="Titre" @input="notify" />
            <input v-model="card.description" type="text" class="form-control form-control-sm mb-1" placeholder="Description" @input="notify" />
            <div class="row g-1 mb-1">
                <div class="col-6">
                    <select v-model.number="card.span" class="form-select form-select-sm" @change="notify">
                        <option :value="1">Largeur 1/3</option>
                        <option :value="2">Largeur 2/3</option>
                    </select>
                </div>
                <div class="col-6"><input v-model="card.badge" type="text" class="form-control form-control-sm" placeholder="Badge" @input="notify" /></div>
            </div>
            <button type="button" class="btn btn-link btn-sm text-danger p-0" style="font-size:11px;" @click="node.content.cards.splice(ci, 1); notify();">Supprimer</button>
        </div>
    </template>

    <template v-else-if="node.type === 'before_after'">
        <label class="form-label small fw-semibold">Titre</label>
        <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <label class="form-label small fw-semibold">Image Avant</label>
        <div class="input-group input-group-sm mb-2">
            <input v-model="node.content.beforeUrl" type="text" class="form-control" @input="notify" />
            <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { node.content.beforeUrl = url; notify(); })"><i class="bi bi-images"></i></button>
        </div>
        <label class="form-label small fw-semibold">Image Après</label>
        <div class="input-group input-group-sm mb-2">
            <input v-model="node.content.afterUrl" type="text" class="form-control" @input="notify" />
            <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { node.content.afterUrl = url; notify(); })"><i class="bi bi-images"></i></button>
        </div>
        <label class="form-label small fw-semibold">Hauteur (px)</label>
        <input v-model.number="node.content.heightPx" type="number" min="200" max="700" class="form-control form-control-sm" @input="notify" />
    </template>

    <template v-else-if="node.type === 'countdown'">
        <label class="form-label small fw-semibold">Titre</label>
        <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <label class="form-label small fw-semibold">Sous-titre</label>
        <input v-model="node.content.subtitle" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <label class="form-label small fw-semibold">Date cible</label>
        <input v-model="node.content.targetDate" type="datetime-local" class="form-control form-control-sm mb-2" @input="notify" />
        <ColorGradientControl v-model="node.content.bgColor" label="Fond" :allow-gradient="false" @update:modelValue="notify" />
        <ColorGradientControl v-model="node.content.accentColor" label="Couleur chiffres" :allow-gradient="false" @update:modelValue="notify" />
    </template>

    <template v-else-if="node.type === 'progress_bars'">
        <label class="form-label small fw-semibold">Titre</label>
        <input v-model="node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
        <button type="button" class="btn btn-xs btn-primary rounded-pill mb-2" @click="node.content.items.push({ id: uid('pb'), label: 'Compétence', percent: 80, color: '#4f46e5' }); notify();">+ Barre</button>
        <div v-for="(bar, bi) in (node.content.items || [])" :key="bar.id" class="p-2 border rounded-3 mb-1 bg-light">
            <input v-model="bar.label" type="text" class="form-control form-control-sm mb-1" @input="notify" />
            <div class="d-flex gap-1 align-items-center">
                <input v-model.number="bar.percent" type="number" min="0" max="100" class="form-control form-control-sm" @input="notify" />
                <input v-model="bar.color" type="color" class="form-control form-control-color form-control-sm" @input="notify" />
                <button type="button" class="btn btn-sm btn-outline-danger" @click="node.content.items.splice(bi, 1); notify();"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    </template>

    <template v-else-if="node.type === 'marquee_ticker'">
        <ColorGradientControl v-model="node.content.bgColor" label="Fond bandeau" :allow-gradient="false" @update:modelValue="notify" />
        <ColorGradientControl v-model="node.content.textColor" label="Couleur texte" :allow-gradient="false" @update:modelValue="notify" />
        <label class="form-label small fw-semibold">Vitesse (secondes)</label>
        <input v-model.number="node.content.speedSec" type="number" min="5" max="60" class="form-control form-control-sm mb-2" @input="notify" />
        <button type="button" class="btn btn-xs btn-primary rounded-pill mb-2" @click="node.content.items.push('Nouveau texte'); notify();">+ Élément</button>
        <div v-for="(item, ii) in (node.content.items || [])" :key="ii" class="d-flex gap-1 mb-1">
            <input v-model="node.content.items[ii]" type="text" class="form-control form-control-sm" @input="notify" />
            <button type="button" class="btn btn-sm btn-outline-danger" @click="node.content.items.splice(ii, 1); notify();"><i class="bi bi-trash"></i></button>
        </div>
    </template>

    <template v-else-if="node.type === 'tabs_content'">
        <ColorGradientControl v-model="node.content.accentColor" label="Couleur onglet actif" :allow-gradient="false" @update:modelValue="notify" />
        <button type="button" class="btn btn-xs btn-primary rounded-pill mb-2" @click="node.content.tabs.push({ id: uid('tb'), label: 'Onglet', icon: 'bi-star', title: 'Titre onglet', body: 'Contenu...' }); notify();">+ Onglet</button>
        <div v-for="(tb, tbi) in (node.content.tabs || [])" :key="tb.id" class="p-2 border rounded-3 mb-2 bg-light">
            <input v-model="tb.label" type="text" class="form-control form-control-sm mb-1" placeholder="Nom onglet" @input="notify" />
            <input v-model="tb.title" type="text" class="form-control form-control-sm mb-1" placeholder="Titre contenu" @input="notify" />
            <textarea v-model="tb.body" rows="2" class="form-control form-control-sm mb-1" placeholder="Contenu" @input="notify"></textarea>
            <button type="button" class="btn btn-link btn-sm text-danger p-0" style="font-size:11px;" @click="node.content.tabs.splice(tbi, 1); notify();">Supprimer</button>
        </div>
    </template>
</template>
