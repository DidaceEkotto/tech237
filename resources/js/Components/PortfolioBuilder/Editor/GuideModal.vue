<script setup>
defineProps({
    show: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);
</script>

<template>
    <div
        v-if="show"
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center p-3"
        style="z-index: 2300;"
        @click="emit('close')"
    >
        <div class="bg-white rounded-4 shadow-lg p-4 w-100" style="max-width: 820px; max-height: 86vh; overflow-y: auto;" @click.stop>
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-book-half text-primary me-2"></i>Guide d'utilisation — Studio Website &amp; Portfolio Builder</h5>
                <button type="button" class="btn-close" @click="emit('close')"></button>
            </div>

            <div class="row g-3 small">
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <h6 class="fw-bold text-primary"><i class="bi bi-diagram-3 me-1"></i>1. One-Page vs Multi-Pages &amp; Migration</h6>
                        <p class="mb-0 text-secondary">
                            Lors de la création d'un site, choisissez <strong>One-Page</strong> (tout sur une seule page avec ancres <code>#section</code>) ou <strong>Multi-Pages</strong> (plusieurs pages indépendantes). Vous pouvez basculer entre les deux modes en <strong>1 clic</strong> depuis votre tableau de bord.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <h6 class="fw-bold text-primary"><i class="bi bi-menu-button-wide me-1"></i>2. Menu Accordéon &amp; État Actif</h6>
                        <p class="mb-0 text-secondary">
                            Créez des menus horizontaux ou verticaux (gauche/droite, dépliables), avec icônes Bootstrap, logo/avatar/initiales et sous-menus accordéon. Chaque lien se relie automatiquement à vos <strong>sections</strong> (One-Page) ou à vos <strong>pages</strong> (Multi-Pages), avec mise en surbrillance de la section active.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <h6 class="fw-bold text-primary"><i class="bi bi-palette me-1"></i>3. Design, 30 Polices, Dégradés &amp; CSS</h6>
                        <p class="mb-0 text-secondary">
                            Dans l'inspecteur de droite (onglets <strong>Contenu / Design / Avancé</strong>), choisissez parmi <strong>30 polices web avec aperçu</strong>, des couleurs unies ou dégradés, des animations texte (dont Machine à écrire) et image, des emojis, et ajoutez votre <strong>propre code CSS</strong> appliqué en temps réel.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <h6 class="fw-bold text-primary"><i class="bi bi-file-earmark-person me-1"></i>4. CV Pro, Export PDF &amp; Carte LinkedIn</h6>
                        <p class="mb-0 text-secondary">
                            Le module <strong>CV Pro</strong> propose 10 modèles, formations, projets, compétences catégorisées (Backend, Frontend, DevOps...) avec pourcentage, loisirs, un <strong>export PDF</strong> serveur et l'aperçu de la carte de partage WhatsApp/LinkedIn.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <h6 class="fw-bold text-primary"><i class="bi bi-images me-1"></i>5. Médiathèque &amp; Retouche d'Image</h6>
                        <p class="mb-0 text-secondary">
                            Cliquez sur l'icône <strong>sliders</strong> dans la médiathèque pour renommer, rogner (1:1, 16:9, 4:3), et appliquer des filtres (luminosité, contraste, noir &amp; blanc, sépia) directement sur vos images.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <h6 class="fw-bold text-primary"><i class="bi bi-file-zip me-1"></i>6. Export ZIP, Multi-Tenancy &amp; Stats</h6>
                        <p class="mb-0 text-secondary">
                            Exportez votre site en <strong>HTML/CSS/JS</strong>, <strong>PHP + SQLite</strong> ou <strong>Laravel + SQLite</strong>. Une fois publié en mode multi-tenant (<code>/@username</code> ou <code>/site/slug</code>), le nom est verrouillé tant que le site reste en ligne, et vous disposez de statistiques détaillées de visites.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
