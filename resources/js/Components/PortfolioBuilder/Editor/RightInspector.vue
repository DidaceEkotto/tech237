<script setup>
import { ref, computed } from 'vue';
import BootstrapIconPicker from '../Controls/BootstrapIconPicker.vue';
import SpacingControl from '../Controls/SpacingControl.vue';
import ColorGradientControl from '../Controls/ColorGradientControl.vue';
import TypographyControl from '../Controls/TypographyControl.vue';
import EmojiPickerControl from '../Controls/EmojiPickerControl.vue';
import {
    HERO_TEMPLATES,
    NAVBAR_TEMPLATES,
    GALLERY_TEMPLATES,
    VIDEO_TEMPLATES,
    ABOUT_TEMPLATES,
    CONTACT_TEMPLATES,
    SLIDER_TEMPLATES,
    PRICING_TEMPLATES,
    VIMEO_TEMPLATES,
    CV_TEMPLATES,
    uid,
} from '@/Composables/PortfolioBuilder/moduleRegistry';
import { TEXT_ANIMATIONS, IMAGE_ANIMATIONS } from '@/Composables/PortfolioBuilder/fontsAndAnimations';

const props = defineProps({
    selectedNodeData: { type: Object, default: null },
    viewport: { type: String, default: 'desktop' },
    tree: { type: Object, default: () => ({ sections: [] }) },
    pages: { type: Array, default: () => [] },
    siteType: { type: String, default: 'onepage' },
    portfolioUser: { type: Object, default: () => ({}) },
    hydratedData: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['change', 'pick-media']);

const tab = ref('content'); // content | design | advanced
const idError = ref('');

const templateOptions = computed(() => {
    const t = props.selectedNodeData?.node?.type;
    if (t === 'hero') return HERO_TEMPLATES;
    if (t === 'navbar_accordion') return NAVBAR_TEMPLATES;
    if (t === 'gallery') return GALLERY_TEMPLATES;
    if (t === 'video') return VIDEO_TEMPLATES;
    if (t === 'about') return ABOUT_TEMPLATES;
    if (t === 'contact') return CONTACT_TEMPLATES;
    if (t === 'slider') return SLIDER_TEMPLATES;
    if (t === 'pricing') return PRICING_TEMPLATES;
    if (t === 'vimeo') return VIMEO_TEMPLATES;
    if (t === 'cv_builder') return CV_TEMPLATES;
    return [];
});

const sectionTargets = computed(() => {
    return (props.tree?.sections || []).map((s, i) => ({
        id: s.advanced?.customId || s.id,
        label: `${s.label || 'Section ' + (i + 1)} (#${s.advanced?.customId || s.id})`,
    }));
});

function notify() {
    emit('change');
}

function updateSectionCustomId(newVal) {
    const clean = newVal.replace(/[^a-zA-Z0-9_\-]/g, '');
    const currentSecId = props.selectedNodeData?.node?.id;
    const duplicate = clean && (props.tree?.sections || []).some(
        (s) => s.id !== currentSecId && (s.advanced?.customId === clean || s.id === clean)
    );
    if (duplicate) {
        idError.value = `L'identifiant "${clean}" est déjà utilisé par une autre section.`;
        return;
    }
    idError.value = '';
    props.selectedNodeData.node.advanced.customId = clean;
    notify();
}

function appendEmoji(obj, key, emoji) {
    obj[key] = (obj[key] || '') + emoji;
    notify();
}

function addNavItem() {
    props.selectedNodeData.node.content.items.push({ id: uid('nav'), label: 'Nouveau lien', icon: 'bi-star-fill', target: '#hero', children: [] });
    notify();
}

function addNavSubItem(item) {
    if (!item.children) item.children = [];
    item.children.push({ id: uid('sub'), label: 'Sous-lien', icon: 'bi-arrow-right-short', target: '#about' });
    notify();
}

function importTech237ProjectsToCv() {
    const projects = props.hydratedData?.projects || [];
    if (!projects.length) return;
    const cvProjects = props.selectedNodeData.node.content.projects;
    projects.forEach((p) => {
        if (!cvProjects.some((existing) => existing.name === p.name)) {
            cvProjects.push({
                id: uid('cp'),
                name: p.name,
                description: p.description || '',
                url: p.url || '',
                imageUrl: p.image || '',
            });
        }
    });
    notify();
}
</script>

<template>
    <aside class="bg-white border-start d-flex flex-column h-100" style="width: 340px; min-width: 300px;">
        <div v-if="!selectedNodeData" class="p-4 text-center text-muted my-auto">
            <i class="bi bi-cursor fs-1 d-block mb-2 text-primary opacity-50"></i>
            <div class="fw-semibold">Aucun élément sélectionné</div>
            <p class="small mb-0">Cliquez sur une section, une ligne ou un module dans le canvas pour modifier ses propriétés.</p>
        </div>

        <template v-else>
            <div class="p-2 border-bottom bg-light d-flex align-items-center justify-content-between">
                <span class="badge bg-primary text-uppercase">
                    {{ selectedNodeData.kind === 'module' ? selectedNodeData.node.type : selectedNodeData.kind }}
                </span>
                <span class="small text-muted">{{ viewport }}</span>
            </div>

            <div class="nav nav-tabs nav-fill px-2 pt-2 bg-light" style="font-size: 12px;">
                <button type="button" class="nav-link py-2" :class="{ active: tab === 'content' }" @click="tab = 'content'">Contenu</button>
                <button type="button" class="nav-link py-2" :class="{ active: tab === 'design' }" @click="tab = 'design'">Design</button>
                <button type="button" class="nav-link py-2" :class="{ active: tab === 'advanced' }" @click="tab = 'advanced'">Avancé</button>
            </div>

            <div class="p-3 overflow-y-auto flex-grow-1">
                <!-- TAB 1: CONTENU -->
                <div v-if="tab === 'content'">
                    <div v-if="selectedNodeData.kind === 'section'">
                        <label class="form-label small fw-semibold">Nom de la section</label>
                        <input v-model="selectedNodeData.node.label" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                        <label class="form-label small fw-semibold">Identifiant unique (ID Ancre)</label>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            :value="selectedNodeData.node.advanced?.customId || ''"
                            placeholder="ex: videos, apropos"
                            @input="updateSectionCustomId($event.target.value)"
                        />
                        <div v-if="idError" class="alert alert-danger py-1 px-2 small mt-2 mb-0">{{ idError }}</div>
                    </div>

                    <div v-else-if="selectedNodeData.kind === 'module'">
                        <div v-if="templateOptions.length" class="mb-3">
                            <label class="form-label small fw-bold text-primary">Modèle Visuel ({{ templateOptions.length }})</label>
                            <select v-model="selectedNodeData.node.content.template" class="form-select form-select-sm" @change="notify">
                                <option v-for="opt in templateOptions" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
                            </select>
                        </div>

                        <!-- NAVBAR ACCORDION -->
                        <template v-if="selectedNodeData.node.type === 'navbar_accordion'">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Position</label>
                                <select v-model="selectedNodeData.node.content.positionMode" class="form-select form-select-sm" @change="notify">
                                    <option value="static">Normal (Non fixe)</option>
                                    <option value="sticky">Sticky (Collant au scroll)</option>
                                    <option value="fixed">Fixe en haut</option>
                                </select>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Taille texte (px)</label>
                                    <input v-model.number="selectedNodeData.node.content.fontSize" type="number" min="11" max="22" class="form-control form-control-sm" @input="notify" />
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Arrondi (px)</label>
                                    <input v-model.number="selectedNodeData.node.content.borderRadius" type="number" min="0" max="999" class="form-control form-control-sm" @input="notify" />
                                </div>
                            </div>
                            <ColorGradientControl v-model="selectedNodeData.node.content.bgColor" label="Fond du menu" @update:modelValue="notify" />
                            <ColorGradientControl v-model="selectedNodeData.node.content.textColor" label="Couleur texte" :allow-gradient="false" @update:modelValue="notify" />
                            <ColorGradientControl v-model="selectedNodeData.node.content.activeBgColor" label="Fond élément actif" :allow-gradient="false" @update:modelValue="notify" />
                            <ColorGradientControl v-model="selectedNodeData.node.content.activeTextColor" label="Texte élément actif" :allow-gradient="false" @update:modelValue="notify" />

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Logo (sinon photo/initiales)</label>
                                <div class="input-group input-group-sm">
                                    <input v-model="selectedNodeData.node.content.logoUrl" type="text" class="form-control" placeholder="URL du logo" @input="notify" />
                                    <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { selectedNodeData.node.content.logoUrl = url; notify(); })">
                                        <i class="bi bi-images"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold">Éléments du menu</span>
                                <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-1" style="font-size:11px;" @click="addNavItem">+ Lien</button>
                            </div>
                            <div v-for="(item, idx) in selectedNodeData.node.content.items" :key="item.id" class="p-2 border rounded-3 mb-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold">Lien #{{ idx + 1 }}</span>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="selectedNodeData.node.content.items.splice(idx, 1); notify();"><i class="bi bi-trash"></i></button>
                                </div>
                                <input v-model="item.label" type="text" class="form-control form-control-sm mb-1" placeholder="Libellé" @input="notify" />

                                <!-- Onepage section selector OR Multipage page selector -->
                                <select v-if="siteType === 'onepage'" v-model="item.target" class="form-select form-select-sm mb-1" @change="notify">
                                    <option value="">-- Choisir une section --</option>
                                    <option v-for="sec in sectionTargets" :key="sec.id" :value="`#${sec.id}`">{{ sec.label }}</option>
                                </select>
                                <select v-else v-model="item.target" class="form-select form-select-sm mb-1" @change="notify">
                                    <option value="">-- Choisir une page --</option>
                                    <option v-for="pg in pages" :key="pg.id" :value="pg.is_homepage ? `/@${portfolioUser?.username}` : `/@${portfolioUser?.username}/${pg.slug}`">
                                        {{ pg.title }} (/{{ pg.slug }})
                                    </option>
                                </select>

                                <BootstrapIconPicker v-model="item.icon" label="" @update:modelValue="notify" />
                                <button type="button" class="btn btn-link btn-sm p-0 mt-1 text-decoration-none" style="font-size:11px;" @click="addNavSubItem(item)">
                                    + Sous-menu accordéon
                                </button>
                                <div v-for="(sub, sIdx) in (item.children || [])" :key="sub.id" class="ps-2 mt-1 border-start border-primary">
                                    <div class="d-flex gap-1 mb-1">
                                        <input v-model="sub.label" type="text" class="form-control form-control-sm" placeholder="Sous-menu" @input="notify" />
                                        <button type="button" class="btn btn-sm btn-outline-danger" @click="item.children.splice(sIdx, 1); notify();"><i class="bi bi-x"></i></button>
                                    </div>
                                    <input v-model="sub.target" type="text" class="form-control form-control-sm" placeholder="Cible (#...)" @input="notify" />
                                </div>
                            </div>
                        </template>

                        <!-- HERO (100% toggleable elements + Image or YouTube) -->
                        <template v-else-if="selectedNodeData.node.type === 'hero'">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <label class="form-label small fw-semibold mb-0">Badge</label>
                                <input v-model="selectedNodeData.node.content.showBadge" type="checkbox" class="form-check-input" @change="notify" />
                            </div>
                            <input v-if="selectedNodeData.node.content.showBadge !== false" v-model="selectedNodeData.node.content.badge" type="text" class="form-control form-control-sm mb-2" @input="notify" />

                            <div class="mb-1 d-flex justify-content-between align-items-center">
                                <label class="form-label small fw-semibold mb-0">Titre</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <EmojiPickerControl @pick="(e) => appendEmoji(selectedNodeData.node.content, 'title', e)" />
                                    <input v-model="selectedNodeData.node.content.showTitle" type="checkbox" class="form-check-input" @change="notify" />
                                </div>
                            </div>
                            <textarea v-if="selectedNodeData.node.content.showTitle !== false" v-model="selectedNodeData.node.content.title" rows="2" class="form-control form-control-sm mb-2" @input="notify"></textarea>

                            <div class="mb-1 d-flex justify-content-between align-items-center">
                                <label class="form-label small fw-semibold mb-0">Sous-titre</label>
                                <input v-model="selectedNodeData.node.content.showSubtitle" type="checkbox" class="form-check-input" @change="notify" />
                            </div>
                            <textarea v-if="selectedNodeData.node.content.showSubtitle !== false" v-model="selectedNodeData.node.content.subtitle" rows="2" class="form-control form-control-sm mb-2" @input="notify"></textarea>

                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Média Hero</label>
                                <select v-model="selectedNodeData.node.content.mediaType" class="form-select form-select-sm mb-2" @change="notify">
                                    <option value="image">Image</option>
                                    <option value="youtube">Vidéo YouTube</option>
                                </select>
                                <div v-if="selectedNodeData.node.content.mediaType === 'youtube'">
                                    <input v-model="selectedNodeData.node.content.youtubeUrl" type="text" class="form-control form-control-sm" placeholder="https://www.youtube.com/watch?v=..." @input="notify" />
                                </div>
                                <div v-else>
                                    <div class="input-group input-group-sm mb-1">
                                        <input v-model="selectedNodeData.node.content.imageUrl" type="text" class="form-control" @input="notify" />
                                        <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { selectedNodeData.node.content.imageUrl = url; notify(); })"><i class="bi bi-images"></i></button>
                                    </div>
                                    <select v-model="selectedNodeData.node.content.imagePosition" class="form-select form-select-sm" @change="notify">
                                        <option value="center center">Cadrage : Centre</option>
                                        <option value="top center">Cadrage : Haut</option>
                                        <option value="bottom center">Cadrage : Bas</option>
                                        <option value="left center">Cadrage : Gauche</option>
                                        <option value="right center">Cadrage : Droite</option>
                                    </select>
                                </div>
                            </div>

                            <div class="border-top pt-2 mt-2 small">
                                <div class="fw-bold mb-1">Éléments affichés</div>
                                <label class="d-block"><input v-model="selectedNodeData.node.content.showPrimaryCta" type="checkbox" class="form-check-input me-1" @change="notify" /> Bouton Principal</label>
                                <label class="d-block"><input v-model="selectedNodeData.node.content.showSecondaryCta" type="checkbox" class="form-check-input me-1" @change="notify" /> Bouton Secondaire</label>
                                <label class="d-block"><input v-model="selectedNodeData.node.content.showFloatingTop" type="checkbox" class="form-check-input me-1" @change="notify" /> Carte Flottante Haut</label>
                                <label class="d-block"><input v-model="selectedNodeData.node.content.showFloatingBottom" type="checkbox" class="form-check-input me-1" @change="notify" /> Carte Flottante Bas</label>
                                <label class="d-block"><input v-model="selectedNodeData.node.content.showTrust" type="checkbox" class="form-check-input me-1" @change="notify" /> Bandeau Confiance</label>
                            </div>
                        </template>

                        <!-- SLIDER -->
                        <template v-else-if="selectedNodeData.node.type === 'slider'">
                            <div class="form-check form-switch mb-2 small">
                                <input id="slAuto" v-model="selectedNodeData.node.content.autoplay" class="form-check-input" type="checkbox" @change="notify" />
                                <label for="slAuto" class="form-check-label">Lecture automatique</label>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Hauteur (px)</label>
                                <input v-model.number="selectedNodeData.node.content.heightPx" type="number" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold">Slides</span>
                                <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-1" style="font-size:11px;" @click="selectedNodeData.node.content.slides.push({ id: uid('sl'), title: 'Slide', subtitle: 'Description', imageUrl: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=900&q=80', ctaLabel: 'Voir', ctaUrl: '#' }); notify();">+ Slide</button>
                            </div>
                            <div v-for="(s, si) in selectedNodeData.node.content.slides" :key="s.id" class="p-2 border rounded-3 mb-2 bg-light">
                                <input v-model="s.title" type="text" class="form-control form-control-sm mb-1" placeholder="Titre" @input="notify" />
                                <input v-model="s.subtitle" type="text" class="form-control form-control-sm mb-1" placeholder="Sous-titre" @input="notify" />
                                <div class="input-group input-group-sm mb-1">
                                    <input v-model="s.imageUrl" type="text" class="form-control" placeholder="Image URL" @input="notify" />
                                    <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { s.imageUrl = url; notify(); })"><i class="bi bi-images"></i></button>
                                </div>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0" style="font-size:11px;" @click="selectedNodeData.node.content.slides.splice(si, 1); notify();">Supprimer</button>
                            </div>
                        </template>

                        <!-- PRICING -->
                        <template v-else-if="selectedNodeData.node.type === 'pricing'">
                            <label class="form-label small fw-semibold">Titre</label>
                            <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <div v-for="p in selectedNodeData.node.content.plans" :key="p.id" class="p-2 border rounded-3 mb-2 bg-light">
                                <input v-model="p.name" type="text" class="form-control form-control-sm mb-1" placeholder="Nom offre" @input="notify" />
                                <input v-model="p.price" type="text" class="form-control form-control-sm mb-1" placeholder="Prix" @input="notify" />
                                <input v-model="p.badgeText" type="text" class="form-control form-control-sm mb-1" placeholder="Badge (ex: Offre Principale)" @input="notify" />
                                <ColorGradientControl v-model="p.badgeColor" label="Couleur badge" :allow-gradient="false" @update:modelValue="notify" />
                            </div>
                        </template>

                        <!-- WHATSAPP -->
                        <template v-else-if="selectedNodeData.node.type === 'whatsapp'">
                            <label class="form-label small fw-semibold">Titre</label>
                            <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Description</label>
                            <input v-model="selectedNodeData.node.content.description" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Téléphone (avec indicatif)</label>
                            <input v-model="selectedNodeData.node.content.phone" type="text" class="form-control form-control-sm mb-2" placeholder="+237600000000" @input="notify" />
                            <label class="form-label small fw-semibold">Texte du bouton</label>
                            <input v-model="selectedNodeData.node.content.buttonLabel" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <ColorGradientControl v-model="selectedNodeData.node.content.bgColor" label="Couleur bouton" :allow-gradient="false" @update:modelValue="notify" />
                        </template>

                        <!-- PAYPAL -->
                        <template v-else-if="selectedNodeData.node.type === 'paypal'">
                            <label class="form-label small fw-semibold">Titre</label>
                            <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Lien PayPal.me</label>
                            <input v-model="selectedNodeData.node.content.paypalTarget" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <div class="row g-2 mb-2">
                                <div class="col-6"><input v-model="selectedNodeData.node.content.amount" type="text" class="form-control form-control-sm" placeholder="Montant" @input="notify" /></div>
                                <div class="col-6"><input v-model="selectedNodeData.node.content.currency" type="text" class="form-control form-control-sm" placeholder="EUR / USD" @input="notify" /></div>
                            </div>
                            <ColorGradientControl v-model="selectedNodeData.node.content.bgColor" label="Couleur bouton" :allow-gradient="false" @update:modelValue="notify" />
                        </template>

                        <!-- GITHUB PROJECTS -->
                        <template v-else-if="selectedNodeData.node.type === 'github_projects'">
                            <label class="form-label small fw-semibold">Titre</label>
                            <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Nom d'utilisateur GitHub</label>
                            <input v-model="selectedNodeData.node.content.username" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Nombre max de projets</label>
                            <input v-model.number="selectedNodeData.node.content.maxItems" type="number" min="1" max="20" class="form-control form-control-sm" @input="notify" />
                        </template>

                        <!-- MAP EMBED -->
                        <template v-else-if="selectedNodeData.node.type === 'map_embed'">
                            <label class="form-label small fw-semibold">Fournisseur</label>
                            <select v-model="selectedNodeData.node.content.provider" class="form-select form-select-sm mb-2" @change="notify">
                                <option value="openstreetmap">OpenStreetMap</option>
                                <option value="google">Google Maps</option>
                            </select>
                            <label class="form-label small fw-semibold">Adresse</label>
                            <input v-model="selectedNodeData.node.content.address" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <div class="row g-2 mb-2">
                                <div class="col-6"><input v-model="selectedNodeData.node.content.lat" type="text" class="form-control form-control-sm" placeholder="Lat" @input="notify" /></div>
                                <div class="col-6"><input v-model="selectedNodeData.node.content.lng" type="text" class="form-control form-control-sm" placeholder="Lng" @input="notify" /></div>
                            </div>
                        </template>

                        <!-- CV BUILDER -->
                        <template v-else-if="selectedNodeData.node.type === 'cv_builder'">
                            <label class="form-label small fw-semibold">Nom Complet</label>
                            <input v-model="selectedNodeData.node.content.fullName" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Poste / Titre</label>
                            <input v-model="selectedNodeData.node.content.jobTitle" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <div class="row g-2 mb-2">
                                <div class="col-6"><input v-model="selectedNodeData.node.content.experienceYears" type="text" class="form-control form-control-sm" placeholder="Années exp." @input="notify" /></div>
                                <div class="col-6"><input v-model="selectedNodeData.node.content.location" type="text" class="form-control form-control-sm" placeholder="Ville" @input="notify" /></div>
                            </div>
                            <label class="form-label small fw-semibold">Photo (optionnelle)</label>
                            <div class="input-group input-group-sm mb-2">
                                <input v-model="selectedNodeData.node.content.photoUrl" type="text" class="form-control" @input="notify" />
                                <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { selectedNodeData.node.content.photoUrl = url; notify(); })"><i class="bi bi-images"></i></button>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success w-100 mb-2" @click="importTech237ProjectsToCv">
                                <i class="bi bi-cloud-download me-1"></i>Importer mes projets Tech237
                            </button>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold">Compétences</span>
                                <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-0" style="font-size:11px;" @click="selectedNodeData.node.content.skills.push({ id: uid('sk'), name: 'Nouvelle', category: 'Backend', level: 80 }); notify();">+ Comp.</button>
                            </div>
                            <div v-for="(sk, ski) in selectedNodeData.node.content.skills" :key="sk.id" class="p-2 border rounded-3 mb-1 bg-light">
                                <div class="d-flex gap-1 mb-1">
                                    <input v-model="sk.name" type="text" class="form-control form-control-sm" placeholder="Nom" @input="notify" />
                                    <input v-model.number="sk.level" type="number" min="5" max="100" class="form-control form-control-sm" style="max-width:65px;" @input="notify" />
                                    <button type="button" class="btn btn-sm btn-outline-danger" @click="selectedNodeData.node.content.skills.splice(ski, 1); notify();"><i class="bi bi-x"></i></button>
                                </div>
                                <select v-model="sk.category" class="form-select form-select-sm" @change="notify">
                                    <option value="Backend">Backend</option>
                                    <option value="Frontend">Frontend</option>
                                    <option value="DevOps">DevOps</option>
                                    <option value="IT Solution">IT Solution</option>
                                    <option value="Design">Design</option>
                                </select>
                            </div>
                        </template>

                        <!-- PAGE LOADER -->
                        <template v-else-if="selectedNodeData.node.type === 'page_loader'">
                            <label class="form-label small fw-semibold">Animation</label>
                            <select v-model="selectedNodeData.node.content.animation" class="form-select form-select-sm mb-2" @change="notify">
                                <option value="spinner">Spinner Rotatif</option>
                                <option value="pulse">Logo Pulsant</option>
                                <option value="progress_bar">Barre de progression</option>
                                <option value="dots">Points rebondissants</option>
                            </select>
                            <label class="form-label small fw-semibold">Durée (ms)</label>
                            <input v-model.number="selectedNodeData.node.content.durationMs" type="number" min="300" max="5000" step="100" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Logo</label>
                            <div class="input-group input-group-sm">
                                <input v-model="selectedNodeData.node.content.logoUrl" type="text" class="form-control" @input="notify" />
                                <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { selectedNodeData.node.content.logoUrl = url; notify(); })"><i class="bi bi-images"></i></button>
                            </div>
                        </template>

                        <!-- AMBIENT PARTICLES -->
                        <template v-else-if="selectedNodeData.node.type === 'ambient_particles'">
                            <label class="form-label small fw-semibold">Effet d'Ambiance</label>
                            <select v-model="selectedNodeData.node.content.effect" class="form-select form-select-sm mb-2" @change="notify">
                                <option value="none">Aucun effet</option>
                                <option value="snow">Chute de Neige (Noël &amp; Fêtes)</option>
                                <option value="confetti">Pluie de Confettis</option>
                                <option value="gold_stars">Étoiles d'or Scintillantes</option>
                                <option value="matrix">Pluie de Code Matrix</option>
                                <option value="zen_bubbles">Bulles Flottantes Zen</option>
                            </select>
                            <label class="d-block small mb-1"><input v-model="selectedNodeData.node.content.mouseSpotlight" type="checkbox" class="form-check-input me-1" @change="notify" /> Spotlight lumineux suivant la souris</label>
                            <label class="d-block small"><input v-model="selectedNodeData.node.content.techRingCursor" type="checkbox" class="form-check-input me-1" @change="notify" /> Curseur personnalisé anneau Tech (Desktop)</label>
                        </template>

                        <!-- GALLERY (with Lightbox toggle) -->
                        <template v-else-if="selectedNodeData.node.type === 'gallery'">
                            <div class="form-check form-switch mb-2 small">
                                <input id="galLb" v-model="selectedNodeData.node.content.lightbox" class="form-check-input" type="checkbox" @change="notify" />
                                <label for="galLb" class="form-check-label">Activer le mode Lightbox (Compteur + Croix)</label>
                            </div>
                            <label class="form-label small fw-semibold">Titre Galerie</label>
                            <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-1 mb-2" style="font-size:11px;" @click="selectedNodeData.node.content.items.push({ id: uid('gal'), url: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=800&q=80', title: 'Nouvelle image', category: 'Projet' }); notify();">+ Image</button>
                            <div v-for="(g, gi) in selectedNodeData.node.content.items" :key="g.id" class="p-2 border rounded-3 mb-2 bg-light">
                                <input v-model="g.title" type="text" class="form-control form-control-sm mb-1" @input="notify" />
                                <div class="input-group input-group-sm mb-1">
                                    <input v-model="g.url" type="text" class="form-control" @input="notify" />
                                    <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { g.url = url; notify(); })"><i class="bi bi-images"></i></button>
                                </div>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0" style="font-size:11px;" @click="selectedNodeData.node.content.items.splice(gi, 1); notify();">Supprimer</button>
                            </div>
                        </template>

                        <!-- CONTACT (Customizable fields) -->
                        <template v-else-if="selectedNodeData.node.type === 'contact'">
                            <label class="form-label small fw-semibold">Titre</label>
                            <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Email affiché</label>
                            <input v-model="selectedNodeData.node.content.email" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold">Champs du formulaire</span>
                                <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-0" style="font-size:11px;" @click="if(!selectedNodeData.node.content.fields) selectedNodeData.node.content.fields = []; selectedNodeData.node.content.fields.push({ id: uid('cf'), name: 'phone', label: 'Téléphone', type: 'text', required: false }); notify();">+ Champ</button>
                            </div>
                            <div v-for="(f, fi) in (selectedNodeData.node.content.fields || [])" :key="f.id" class="d-flex gap-1 mb-1">
                                <input v-model="f.label" type="text" class="form-control form-control-sm" @input="notify" />
                                <button type="button" class="btn btn-sm btn-outline-danger" @click="selectedNodeData.node.content.fields.splice(fi, 1); notify();"><i class="bi bi-trash"></i></button>
                            </div>
                        </template>

                        <!-- HEADING / TEXT / IMAGE / BUTTON / OTHERS -->
                        <template v-else-if="selectedNodeData.node.type === 'heading'">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold mb-0">Texte</label>
                                <EmojiPickerControl @pick="(e) => appendEmoji(selectedNodeData.node.content, 'text', e)" />
                            </div>
                            <input v-model="selectedNodeData.node.content.text" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <select v-model="selectedNodeData.node.content.tag" class="form-select form-select-sm" @change="notify">
                                <option value="h1">H1</option>
                                <option value="h2">H2</option>
                                <option value="h3">H3</option>
                            </select>
                        </template>

                        <template v-else-if="selectedNodeData.node.type === 'text'">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold mb-0">Paragraphe</label>
                                <EmojiPickerControl @pick="(e) => appendEmoji(selectedNodeData.node.content, 'text', e)" />
                            </div>
                            <textarea v-model="selectedNodeData.node.content.text" rows="5" class="form-control form-control-sm" @input="notify"></textarea>
                        </template>

                        <template v-else-if="selectedNodeData.node.type === 'image'">
                            <label class="form-label small fw-semibold">Image</label>
                            <div class="input-group input-group-sm mb-2">
                                <input v-model="selectedNodeData.node.content.url" type="text" class="form-control" @input="notify" />
                                <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { selectedNodeData.node.content.url = url; notify(); })"><i class="bi bi-images"></i></button>
                            </div>
                            <label class="form-label small fw-semibold">Position / Cadrage</label>
                            <select v-model="selectedNodeData.node.content.imagePosition" class="form-select form-select-sm" @change="notify">
                                <option value="center center">Centre</option>
                                <option value="top center">Haut</option>
                                <option value="bottom center">Bas</option>
                                <option value="left center">Gauche</option>
                                <option value="right center">Droite</option>
                            </select>
                        </template>

                        <template v-else>
                            <div class="small text-muted">Personnalisez les styles, polices, animations et CSS dans les onglets <strong>Design</strong> et <strong>Avancé</strong>.</div>
                        </template>
                    </div>
                </div>

                <!-- TAB 2: DESIGN -->
                <div v-else-if="tab === 'design'">
                    <SpacingControl v-if="selectedNodeData.node.styles" :styles="selectedNodeData.node.styles" :viewport="viewport" @change="notify" />
                    <hr />
                    <TypographyControl v-if="selectedNodeData.node.styles" :styles="selectedNodeData.node.styles" :viewport="viewport" @change="notify" />
                </div>

                <!-- TAB 3: AVANCÉ (Animations + Custom CSS + Responsive Visibility) -->
                <div v-else-if="tab === 'advanced'">
                    <div v-if="selectedNodeData.node.advanced">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">ID HTML (Ancre)</label>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                :value="selectedNodeData.node.advanced.customId || ''"
                                @input="selectedNodeData.kind === 'section' ? updateSectionCustomId($event.target.value) : (selectedNodeData.node.advanced.customId = $event.target.value, notify())"
                            />
                            <div v-if="idError" class="alert alert-danger py-1 px-2 small mt-1 mb-0">{{ idError }}</div>
                        </div>

                        <div v-if="selectedNodeData.kind === 'module'" class="mb-2">
                            <label class="form-label small fw-semibold">Animation Texte / Titre</label>
                            <select v-model="selectedNodeData.node.advanced.textAnimation" class="form-select form-select-sm mb-2" @change="notify">
                                <option v-for="a in TEXT_ANIMATIONS" :key="a.id" :value="a.id">{{ a.label }}</option>
                            </select>
                            <label class="form-label small fw-semibold">Animation Image</label>
                            <select v-model="selectedNodeData.node.advanced.imageAnimation" class="form-select form-select-sm mb-2" @change="notify">
                                <option v-for="a in IMAGE_ANIMATIONS" :key="a.id" :value="a.id">{{ a.label }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">CSS Personnalisé (Temps réel)</label>
                            <textarea
                                v-model="selectedNodeData.node.advanced.customCss"
                                rows="4"
                                class="form-control form-control-sm font-monospace"
                                placeholder="box-shadow: 0 10px 30px rgba(0,0,0,0.1);"
                                @input="notify"
                            ></textarea>
                        </div>

                        <div v-if="selectedNodeData.kind === 'module'" class="border-top pt-2">
                            <div class="small fw-bold mb-1">Visibilité Responsive</div>
                            <label class="d-block small"><input v-model="selectedNodeData.node.advanced.hideOnDesktop" type="checkbox" class="form-check-input me-1" @change="notify" /> Masquer Ordinateur</label>
                            <label class="d-block small"><input v-model="selectedNodeData.node.advanced.hideOnTablet" type="checkbox" class="form-check-input me-1" @change="notify" /> Masquer Tablette</label>
                            <label class="d-block small"><input v-model="selectedNodeData.node.advanced.hideOnMobile" type="checkbox" class="form-check-input me-1" @change="notify" /> Masquer Mobile</label>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </aside>
</template>
