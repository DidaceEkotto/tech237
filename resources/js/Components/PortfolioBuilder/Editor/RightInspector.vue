<script setup>
import { ref, computed } from 'vue';
import BootstrapIconPicker from '../Controls/BootstrapIconPicker.vue';
import SpacingControl from '../Controls/SpacingControl.vue';
import ColorGradientControl from '../Controls/ColorGradientControl.vue';
import TypographyControl from '../Controls/TypographyControl.vue';
import {
    HERO_TEMPLATES,
    NAVBAR_TEMPLATES,
    GALLERY_TEMPLATES,
    VIDEO_TEMPLATES,
    ABOUT_TEMPLATES,
    CONTACT_TEMPLATES,
    uid,
} from '@/Composables/PortfolioBuilder/moduleRegistry';

const props = defineProps({
    selectedNodeData: { type: Object, default: null },
    viewport: { type: String, default: 'desktop' },
});

const emit = defineEmits(['change', 'pick-media']);

const tab = ref('content'); // content | design | advanced

const templateOptions = computed(() => {
    const t = props.selectedNodeData?.node?.type;
    if (t === 'hero') return HERO_TEMPLATES;
    if (t === 'navbar_accordion') return NAVBAR_TEMPLATES;
    if (t === 'gallery') return GALLERY_TEMPLATES;
    if (t === 'video') return VIDEO_TEMPLATES;
    if (t === 'about') return ABOUT_TEMPLATES;
    if (t === 'contact') return CONTACT_TEMPLATES;
    return [];
});

function notify() {
    emit('change');
}

function addNavItem() {
    const items = props.selectedNodeData.node.content.items;
    items.push({ id: uid('nav'), label: 'Nouveau lien', icon: 'bi-star-fill', target: '#hero', children: [] });
    notify();
}

function addNavSubItem(item) {
    if (!item.children) item.children = [];
    item.children.push({ id: uid('sub'), label: 'Sous-lien', icon: 'bi-arrow-right-short', target: '#about' });
    notify();
}

function removeNavItem(idx) {
    props.selectedNodeData.node.content.items.splice(idx, 1);
    notify();
}

function addGalleryItem() {
    props.selectedNodeData.node.content.items.push({
        id: uid('gal'),
        url: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=800&q=80',
        title: 'Nouvelle image',
        category: 'Projet',
    });
    notify();
}

function addVideoItem() {
    props.selectedNodeData.node.content.videos.push({
        id: uid('vid'),
        title: 'Nouvelle vidéo',
        youtubeUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        duration: '02:30',
    });
    notify();
}
</script>

<template>
    <aside class="bg-white border-start d-flex flex-column h-100" style="width: 330px; min-width: 300px;">
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
                <span class="small text-muted">Viewport : {{ viewport }}</span>
            </div>

            <!-- 3 Tabs: Contenu / Design / Avancé -->
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
                    </div>

                    <div v-else-if="selectedNodeData.kind === 'module'">
                        <!-- Model / Template Picker when applicable -->
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
                            <ColorGradientControl v-model="selectedNodeData.node.content.textColor" label="Couleur texte" @update:modelValue="notify" />
                            <ColorGradientControl v-model="selectedNodeData.node.content.accentColor" label="Couleur accent" @update:modelValue="notify" />

                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Titre / Marque</label>
                                <input v-model="selectedNodeData.node.content.brandTitle" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
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
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="removeNavItem(idx)"><i class="bi bi-trash"></i></button>
                                </div>
                                <input v-model="item.label" type="text" class="form-control form-control-sm mb-1" placeholder="Libellé (ex: Accueil)" @input="notify" />
                                <input v-model="item.target" type="text" class="form-control form-control-sm mb-1" placeholder="Cible (#hero, #about, /page)" @input="notify" />
                                <BootstrapIconPicker v-model="item.icon" label="" @update:modelValue="notify" />
                                <button type="button" class="btn btn-link btn-sm p-0 mt-1 text-decoration-none" style="font-size:11px;" @click="addNavSubItem(item)">
                                    + Ajouter un sous-menu accordéon
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

                        <!-- HERO -->
                        <template v-else-if="selectedNodeData.node.type === 'hero'">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Badge</label>
                                <input v-model="selectedNodeData.node.content.badge" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Titre Principal</label>
                                <textarea v-model="selectedNodeData.node.content.title" rows="2" class="form-control form-control-sm" @input="notify"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Sous-titre</label>
                                <textarea v-model="selectedNodeData.node.content.subtitle" rows="3" class="form-control form-control-sm" @input="notify"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Bouton Principal</label>
                                <input v-model="selectedNodeData.node.content.primaryCtaLabel" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Image Hero</label>
                                <div class="input-group input-group-sm">
                                    <input v-model="selectedNodeData.node.content.imageUrl" type="text" class="form-control" @input="notify" />
                                    <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { selectedNodeData.node.content.imageUrl = url; notify(); })">
                                        <i class="bi bi-images"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- ABOUT -->
                        <template v-else-if="selectedNodeData.node.type === 'about'">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Nom affiché</label>
                                <input v-model="selectedNodeData.node.content.displayName" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Titre</label>
                                <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Biographie</label>
                                <textarea v-model="selectedNodeData.node.content.bio" rows="4" class="form-control form-control-sm" @input="notify"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Photo (ou initiales si vide)</label>
                                <div class="input-group input-group-sm">
                                    <input v-model="selectedNodeData.node.content.imageUrl" type="text" class="form-control" @input="notify" />
                                    <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { selectedNodeData.node.content.imageUrl = url; notify(); })">
                                        <i class="bi bi-images"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- GALLERY -->
                        <template v-else-if="selectedNodeData.node.type === 'gallery'">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Titre Galerie</label>
                                <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold">Images</span>
                                <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-1" style="font-size:11px;" @click="addGalleryItem">+ Image</button>
                            </div>
                            <div v-for="(g, gi) in selectedNodeData.node.content.items" :key="g.id" class="p-2 border rounded-3 mb-2 bg-light">
                                <input v-model="g.title" type="text" class="form-control form-control-sm mb-1" placeholder="Titre" @input="notify" />
                                <div class="input-group input-group-sm mb-1">
                                    <input v-model="g.url" type="text" class="form-control" placeholder="URL image" @input="notify" />
                                    <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { g.url = url; notify(); })"><i class="bi bi-images"></i></button>
                                </div>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0" style="font-size:11px;" @click="selectedNodeData.node.content.items.splice(gi, 1); notify();">Supprimer</button>
                            </div>
                        </template>

                        <!-- VIDEO -->
                        <template v-else-if="selectedNodeData.node.type === 'video'">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Titre Section Vidéo</label>
                                <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold">Vidéos YouTube</span>
                                <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-1" style="font-size:11px;" @click="addVideoItem">+ Vidéo</button>
                            </div>
                            <div v-for="(v, vi) in selectedNodeData.node.content.videos" :key="v.id" class="p-2 border rounded-3 mb-2 bg-light">
                                <input v-model="v.title" type="text" class="form-control form-control-sm mb-1" placeholder="Titre" @input="notify" />
                                <input v-model="v.youtubeUrl" type="text" class="form-control form-control-sm mb-1" placeholder="https://www.youtube.com/watch?v=..." @input="notify" />
                                <button type="button" class="btn btn-link btn-sm text-danger p-0" style="font-size:11px;" @click="selectedNodeData.node.content.videos.splice(vi, 1); notify();">Supprimer</button>
                            </div>
                        </template>

                        <!-- CONTACT -->
                        <template v-else-if="selectedNodeData.node.type === 'contact'">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Titre</label>
                                <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Sous-titre</label>
                                <input v-model="selectedNodeData.node.content.subtitle" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Email</label>
                                <input v-model="selectedNodeData.node.content.email" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Téléphone</label>
                                <input v-model="selectedNodeData.node.content.phone" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Localisation</label>
                                <input v-model="selectedNodeData.node.content.location" type="text" class="form-control form-control-sm" @input="notify" />
                            </div>
                        </template>

                        <!-- HEADING / TEXT / BUTTON / IMAGE / TECH237 -->
                        <template v-else-if="selectedNodeData.node.type === 'heading'">
                            <label class="form-label small fw-semibold">Niveau</label>
                            <select v-model="selectedNodeData.node.content.tag" class="form-select form-select-sm mb-2" @change="notify">
                                <option value="h1">H1</option>
                                <option value="h2">H2</option>
                                <option value="h3">H3</option>
                                <option value="h4">H4</option>
                            </select>
                            <label class="form-label small fw-semibold">Texte</label>
                            <input v-model="selectedNodeData.node.content.text" type="text" class="form-control form-control-sm" @input="notify" />
                        </template>

                        <template v-else-if="selectedNodeData.node.type === 'text'">
                            <label class="form-label small fw-semibold">Paragraphe</label>
                            <textarea v-model="selectedNodeData.node.content.text" rows="5" class="form-control form-control-sm" @input="notify"></textarea>
                        </template>

                        <template v-else-if="selectedNodeData.node.type === 'image'">
                            <label class="form-label small fw-semibold">Image</label>
                            <div class="input-group input-group-sm mb-2">
                                <input v-model="selectedNodeData.node.content.url" type="text" class="form-control" @input="notify" />
                                <button type="button" class="btn btn-outline-primary" @click="emit('pick-media', (url) => { selectedNodeData.node.content.url = url; notify(); })"><i class="bi bi-images"></i></button>
                            </div>
                            <label class="form-label small fw-semibold">Légende</label>
                            <input v-model="selectedNodeData.node.content.caption" type="text" class="form-control form-control-sm" @input="notify" />
                        </template>

                        <template v-else-if="selectedNodeData.node.type === 'button'">
                            <label class="form-label small fw-semibold">Texte du bouton</label>
                            <input v-model="selectedNodeData.node.content.label" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Lien</label>
                            <input v-model="selectedNodeData.node.content.href" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <BootstrapIconPicker v-model="selectedNodeData.node.content.icon" @update:modelValue="notify" />
                        </template>

                        <template v-else-if="selectedNodeData.node.type === 'tech237_dynamic'">
                            <label class="form-label small fw-semibold">Titre</label>
                            <input v-model="selectedNodeData.node.content.title" type="text" class="form-control form-control-sm mb-2" @input="notify" />
                            <label class="form-label small fw-semibold">Source de données Tech237</label>
                            <select v-model="selectedNodeData.node.content.source" class="form-select form-select-sm mb-2" @change="notify">
                                <option value="projects">Projets publiés</option>
                                <option value="pocs">POCs publiés</option>
                                <option value="badges">Badges obtenus</option>
                                <option value="forms">Tech237 Forms</option>
                                <option value="monitors">Heartbeat Monitors</option>
                            </select>
                        </template>

                        <div v-else class="small text-muted">
                            Éditez le JSON ou utilisez l’onglet Design pour personnaliser ce bloc.
                        </div>
                    </div>
                </div>

                <!-- TAB 2: DESIGN -->
                <div v-else-if="tab === 'design'">
                    <SpacingControl
                        v-if="selectedNodeData.node.styles"
                        :styles="selectedNodeData.node.styles"
                        :viewport="viewport"
                        @change="notify"
                    />
                    <hr />
                    <TypographyControl
                        v-if="selectedNodeData.node.styles"
                        :styles="selectedNodeData.node.styles"
                        :viewport="viewport"
                        @change="notify"
                    />
                </div>

                <!-- TAB 3: AVANCÉ -->
                <div v-else-if="tab === 'advanced'">
                    <div v-if="selectedNodeData.node.advanced">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">ID HTML (Ancre menu, ex: about)</label>
                            <input v-model="selectedNodeData.node.advanced.customId" type="text" class="form-control form-control-sm" placeholder="ex: about" @input="notify" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Classes CSS personnalisées</label>
                            <input v-model="selectedNodeData.node.advanced.cssClass" type="text" class="form-control form-control-sm" placeholder="shadow-lg" @input="notify" />
                        </div>

                        <div v-if="selectedNodeData.kind === 'section'" class="form-check form-switch mb-2">
                            <input id="fullWidthCheck" v-model="selectedNodeData.node.advanced.fullWidth" class="form-check-input" type="checkbox" @change="notify" />
                            <label for="fullWidthCheck" class="form-check-label small">Section Pleine Largeur (100%)</label>
                        </div>

                        <div v-if="selectedNodeData.kind === 'module'" class="border-top pt-2 mt-2">
                            <div class="small fw-bold mb-2">Visibilité Responsive</div>
                            <div class="form-check form-switch small mb-1">
                                <input id="hideD" v-model="selectedNodeData.node.advanced.hideOnDesktop" class="form-check-input" type="checkbox" @change="notify" />
                                <label for="hideD" class="form-check-label">Masquer sur Ordinateur</label>
                            </div>
                            <div class="form-check form-switch small mb-1">
                                <input id="hideT" v-model="selectedNodeData.node.advanced.hideOnTablet" class="form-check-input" type="checkbox" @change="notify" />
                                <label for="hideT" class="form-check-label">Masquer sur Tablette</label>
                            </div>
                            <div class="form-check form-switch small">
                                <input id="hideM" v-model="selectedNodeData.node.advanced.hideOnMobile" class="form-check-input" type="checkbox" @change="notify" />
                                <label for="hideM" class="form-check-label">Masquer sur Mobile</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </aside>
</template>
