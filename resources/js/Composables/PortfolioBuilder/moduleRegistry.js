export function uid(prefix = 'node') {
    return `${prefix}_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 7)}`;
}

export const HERO_TEMPLATES = [
    { id: 'impact_split', label: 'Impact Split (Badge + Image flottante)', icon: 'bi-layout-split' },
    { id: 'centered_gradient', label: 'Centré Dégradé Moderne', icon: 'bi-align-center' },
    { id: 'dark_cyber', label: 'Sombre Tech / Néon', icon: 'bi-cpu' },
    { id: 'minimal_editorial', label: 'Éditorial Minimaliste', icon: 'bi-type-h1' },
    { id: 'glassmorphism', label: 'Glassmorphism Flottant', icon: 'bi-layers' },
    { id: 'diagonal_accent', label: 'Accent Diagonal', icon: 'bi-slash-square' },
    { id: 'bento_intro', label: 'Bento Intro Grid', icon: 'bi-grid-1x2' },
    { id: 'card_floating', label: 'Carte Flottante', icon: 'bi-card-heading' },
    { id: 'full_bleed_overlay', label: 'Plein Écran Overlay', icon: 'bi-fullscreen' },
    { id: 'typographic_bold', label: 'Typographie Géante', icon: 'bi-fonts' },
    { id: 'startup_saas', label: 'Startup SaaS', icon: 'bi-rocket-takeoff' },
    { id: 'creative_studio', label: 'Studio Créatif', icon: 'bi-palette' },
];

export const NAVBAR_TEMPLATES = [
    { id: 'pill_accordion', label: 'Pilule Accordéon Moderne', icon: 'bi-menu-button-wide' },
    { id: 'glass_floating', label: 'Glass Flottant', icon: 'bi-window' },
    { id: 'minimal_underline', label: 'Minimaliste Souligné', icon: 'bi-dash-lg' },
    { id: 'solid_dark', label: 'Barre Sombre Pro', icon: 'bi-moon-stars' },
    { id: 'gradient_modern', label: 'Dégradé Vibrant', icon: 'bi-rainbow' },
    { id: 'vertical_left', label: 'Barre Latérale Gauche', icon: 'bi-layout-sidebar' },
    { id: 'vertical_right', label: 'Barre Latérale Droite', icon: 'bi-layout-sidebar-reverse' },
    { id: 'collapsible_sidebar', label: 'Menu Dépliable / Repliable', icon: 'bi-arrows-collapse-vertical' },
];

export const SLIDER_TEMPLATES = [
    { id: 'classic_fade', label: 'Carrousel Classique', icon: 'bi-images' },
    { id: 'split_caption', label: 'Image + Texte Split', icon: 'bi-layout-split' },
    { id: 'fullwidth_hero', label: 'Plein Écran Cinéma', icon: 'bi-arrows-fullscreen' },
    { id: 'cards_3d', label: 'Cartes Modernes', icon: 'bi-collection' },
    { id: 'thumbnail_nav', label: 'Avec Miniatures', icon: 'bi-grid-3x2' },
];

export const PRICING_TEMPLATES = [
    { id: 'cards_classic', label: 'Cartes Classiques', icon: 'bi-card-list' },
    { id: 'dark_pro', label: 'Sombre Premium', icon: 'bi-moon' },
    { id: 'gradient_highlight', label: 'Dégradé Highlight', icon: 'bi-stars' },
    { id: 'minimal_pills', label: 'Minimaliste Épuré', icon: 'bi-dash-square' },
];

export const VIMEO_TEMPLATES = [
    { id: 'theater_wide', label: 'Cinéma Pleine Largeur', icon: 'bi-film' },
    { id: 'split_caption', label: 'Vidéo + Description Split', icon: 'bi-layout-split' },
    { id: 'grid_2_col', label: 'Grille 2 Vidéos', icon: 'bi-grid-fill' },
    { id: 'dark_cinema', label: 'Mode Sombre Studio', icon: 'bi-camera-reels' },
    { id: 'minimal_embed', label: 'Intégration Épurée', icon: 'bi-vimeo' },
];

export const CV_TEMPLATES = [
    { id: 'modern_sidebar', label: 'Moderne Sidebar Colorée', icon: 'bi-layout-sidebar' },
    { id: 'executive_clean', label: 'Exécutif Épuré', icon: 'bi-briefcase' },
    { id: 'tech_dark', label: 'Tech Developer Dark', icon: 'bi-terminal' },
    { id: 'creative_gradient', label: 'Créatif Dégradé', icon: 'bi-palette' },
    { id: 'academic_classic', label: 'Académique Classique', icon: 'bi-mortarboard' },
    { id: 'minimal_swiss', label: 'Minimaliste Suisse', icon: 'bi-grid' },
    { id: 'timeline_pro', label: 'Chronologie Pro', icon: 'bi-clock-history' },
    { id: 'bento_cv', label: 'Bento Grid CV', icon: 'bi-grid-1x2' },
    { id: 'startup_bold', label: 'Startup Audacieux', icon: 'bi-rocket' },
    { id: 'compact_onepage', label: 'Compact 1 Page', icon: 'bi-file-earmark-person' },
];

export const GALLERY_TEMPLATES = [
    { id: 'grid_3', label: 'Grille 3 Colonnes', icon: 'bi-grid-3x3' },
    { id: 'grid_4', label: 'Grille 4 Colonnes', icon: 'bi-grid' },
    { id: 'masonry', label: 'Maçonnerie Dynamique', icon: 'bi-columns-gap' },
    { id: 'carousel_slider', label: 'Diaporama Carrousel', icon: 'bi-images' },
    { id: 'horizontal_scroll', label: 'Défilement Horizontal', icon: 'bi-arrows-expand' },
    { id: 'fullscreen_showcase', label: 'Plein Écran Showcase', icon: 'bi-arrows-fullscreen' },
    { id: 'bento_mosaic', label: 'Mosaïque Bento', icon: 'bi-grid-1x2' },
    { id: 'before_after', label: 'Comparateur Avant / Après', icon: 'bi-symmetry-vertical' },
    { id: 'polaroid_stack', label: 'Cartes Polaroid', icon: 'bi-card-image' },
    { id: 'minimal_lightbox', label: 'Galerie Épurée', icon: 'bi-aspect-ratio' },
];

export const VIDEO_TEMPLATES = [
    { id: 'theater_wide', label: 'Cinéma Pleine Largeur', icon: 'bi-film' },
    { id: 'split_caption', label: 'Vidéo + Description Split', icon: 'bi-layout-split' },
    { id: 'grid_2_col', label: 'Grille 2 Vidéos', icon: 'bi-grid-fill' },
    { id: 'grid_3_col', label: 'Grille 3 Vidéos', icon: 'bi-grid-3x2' },
    { id: 'playlist_sidebar', label: 'Lecteur + Playlist', icon: 'bi-collection-play' },
    { id: 'floating_card', label: 'Carte Vidéo Flottante', icon: 'bi-play-btn' },
    { id: 'dark_cinema', label: 'Mode Sombre Studio', icon: 'bi-camera-reels' },
    { id: 'reel_vertical', label: 'Format Shorts / Reels', icon: 'bi-phone' },
    { id: 'chapter_list', label: 'Vidéo avec Chapitres', icon: 'bi-list-ol' },
    { id: 'minimal_embed', label: 'Intégration Épurée', icon: 'bi-youtube' },
];

export const ABOUT_TEMPLATES = [
    { id: 'split_photo', label: 'Photo + Bio Split', icon: 'bi-person-badge' },
    { id: 'timeline_story', label: 'Parcours Chronologique', icon: 'bi-clock-history' },
    { id: 'stats_bio', label: 'Bio + Chiffres Clés', icon: 'bi-bar-chart' },
    { id: 'quote_editorial', label: 'Citation Éditoriale', icon: 'bi-chat-quote' },
    { id: 'bento_profile', label: 'Profil Bento', icon: 'bi-grid-1x2' },
    { id: 'card_minimal', label: 'Carte Profil Minimaliste', icon: 'bi-person-vcard' },
    { id: 'dark_spotlight', label: 'Spotlight Sombre', icon: 'bi-stars' },
    { id: 'two_col_values', label: 'Mission & Valeurs (2 Col)', icon: 'bi-columns' },
    { id: 'founder_note', label: 'Mot du Fondateur / Dev', icon: 'bi-pen' },
    { id: 'cv_highlight', label: 'Résumé CV Express', icon: 'bi-file-earmark-person' },
];

export const CONTACT_TEMPLATES = [
    { id: 'split_info_form', label: 'Infos + Formulaire Split', icon: 'bi-envelope-paper' },
    { id: 'centered_card', label: 'Carte Centrée', icon: 'bi-card-text' },
    { id: 'minimal_underline', label: 'Minimaliste Ligne', icon: 'bi-input-cursor-text' },
    { id: 'dark_neon', label: 'Contact Sombre Pro', icon: 'bi-moon' },
    { id: 'two_col_map_style', label: 'Localisation + Formulaire', icon: 'bi-geo-alt' },
    { id: 'floating_glass', label: 'Glassmorphism Contact', icon: 'bi-layers' },
    { id: 'cta_banner_form', label: 'Bannière CTA + Email', icon: 'bi-megaphone' },
    { id: 'step_quick', label: 'Demande de Devis Rapide', icon: 'bi-lightning' },
    { id: 'availability_badge', label: 'Statut Disponibilité + Form', icon: 'bi-check-circle' },
    { id: 'social_first', label: 'Réseaux & Contact Direct', icon: 'bi-share' },
];

export const COUNTER_TEMPLATES = [
    { id: 'cards_classic', label: 'Cartes Classiques', icon: 'bi-card-list' },
    { id: 'minimal_clean', label: 'Minimaliste Épuré', icon: 'bi-dash-lg' },
    { id: 'gradient_pills', label: 'Pilules Dégradées', icon: 'bi-stars' },
    { id: 'dark_neon', label: 'Sombre Tech Néon', icon: 'bi-cpu' },
    { id: 'glass_cards', label: 'Glassmorphism', icon: 'bi-layers' },
    { id: 'horizontal_bar', label: 'Bandeau Horizontal', icon: 'bi-distribute-horizontal' },
];

export const TESTIMONIAL_TEMPLATES = [
    { id: 'cards_classic', label: 'Cartes Classiques', icon: 'bi-chat-square-quote' },
    { id: 'dark_spotlight', label: 'Spotlight Sombre', icon: 'bi-moon-stars' },
    { id: 'quote_editorial', label: 'Citation Éditoriale', icon: 'bi-quote' },
    { id: 'glassmorphism', label: 'Glassmorphism Moderne', icon: 'bi-layers' },
    { id: 'chat_bubbles', label: 'Bulles de Conversation', icon: 'bi-chat-dots' },
    { id: 'grid_compact', label: 'Grille Compacte 3 Col', icon: 'bi-grid-3x2' },
];

export const PARALLAX_TEMPLATES = [
    { id: 'classic_overlay', label: 'Plein Écran Overlay', icon: 'bi-layers-half' },
    { id: 'split_card', label: 'Carte Flottante Latérale', icon: 'bi-layout-split' },
    { id: 'glass_center', label: 'Glassmorphism Centré', icon: 'bi-window' },
    { id: 'diagonal_band', label: 'Bandeau Diagonal', icon: 'bi-slash-square' },
    { id: 'minimal_caption', label: 'Image Pure + Légende', icon: 'bi-image' },
];

export const FAQ_TEMPLATES = [
    { id: 'bordered_classic', label: 'Accordéon Classique', icon: 'bi-list-ul' },
    { id: 'separated_cards', label: 'Cartes Séparées', icon: 'bi-card-text' },
    { id: 'minimal_lines', label: 'Lignes Minimalistes', icon: 'bi-dash-lg' },
    { id: 'dark_modern', label: 'Sombre Moderne', icon: 'bi-moon' },
];

export const MODULE_CATALOG = [
    {
        type: 'navbar_accordion',
        label: 'Menu Accordéon',
        category: 'mise_en_page',
        icon: 'bi-menu-button-wide-fill',
        description: 'Navbar personnalisable avec liens accordéon, icônes Bootstrap et logo/avatar.',
        defaultContent: {
            template: 'pill_accordion',
            positionMode: 'sticky', // static | sticky | fixed
            fontSize: 14,
            borderRadius: 999,
            bgColor: '#ffffff',
            textColor: '#0f172a',
            accentColor: '#4f46e5',
            activeBgColor: '#4f46e5',
            activeTextColor: '#ffffff',
            logoUrl: '',
            collapsible: false,
            ctaLabel: 'Me contacter',
            ctaHref: '#contact',
            items: [
                { id: 'nav_1', label: 'Accueil', icon: 'bi-house-door-fill', target: '#hero', children: [] },
                {
                    id: 'nav_2',
                    label: 'À propos',
                    icon: 'bi-person-badge',
                    target: '#about',
                    children: [
                        { id: 'sub_1', label: 'Mon Parcours', target: '#about', icon: 'bi-award' },
                        { id: 'sub_2', label: 'Mes Projets', target: '#projects', icon: 'bi-kanban' },
                    ],
                },
                { id: 'nav_3', label: 'Galerie', icon: 'bi-images', target: '#gallery', children: [] },
                { id: 'nav_4', label: 'Vidéos', icon: 'bi-play-circle', target: '#videos', children: [] },
                { id: 'nav_5', label: 'Contact', icon: 'bi-envelope', target: '#contact', children: [] },
            ],
        },
        defaultStyles: {
            desktop: { paddingTop: '10px', paddingBottom: '10px', paddingLeft: '16px', paddingRight: '16px', marginBottom: '12px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'hero',
        label: 'Hero / Header',
        category: 'mise_en_page',
        icon: 'bi-stars',
        description: 'Section d’en-tête avec 12 modèles visuels professionnels.',
        defaultContent: {
            template: 'impact_split',
            showBadge: true,
            showTitle: true,
            showSubtitle: true,
            showPrimaryCta: true,
            showSecondaryCta: true,
            showTrust: true,
            showFloatingTop: true,
            showFloatingBottom: true,
            mediaType: 'image', // image | youtube
            youtubeUrl: '',
            imagePosition: 'center center',
            badge: 'Disponible pour de nouveaux défis',
            title: 'Construisons ensemble des solutions numériques à fort impact',
            subtitle: 'Développeur Fullstack & Architecte Produit — Conception d’applications web modernes, performantes et orientées résultats.',
            primaryCtaLabel: 'Démarrer un projet',
            primaryCtaUrl: '#contact',
            secondaryCtaLabel: 'Découvrir mes réalisations',
            secondaryCtaUrl: '#projects',
            imageUrl: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1000&q=80',
            floatingTopTitle: 'Impact 100% Terrain',
            floatingTopSub: 'Web, Mobile & Cloud',
            floatingBottomTitle: 'Expertise Certifiée',
            floatingBottomSub: 'Qualité & Performance',
            trustText: 'Rejoignez +50 clients et partenaires satisfaits.',
        },
        defaultStyles: {
            desktop: { paddingTop: '48px', paddingBottom: '48px', paddingLeft: '32px', paddingRight: '32px', borderRadius: '24px', backgroundColor: '#f8fafc' },
            tablet: { paddingTop: '32px', paddingBottom: '32px', paddingLeft: '20px', paddingRight: '20px' },
            mobile: { paddingTop: '24px', paddingBottom: '24px', paddingLeft: '16px', paddingRight: '16px' },
        },
    },
    {
        type: 'about',
        label: 'À Propos',
        category: 'essentiels',
        icon: 'bi-person-lines-fill',
        description: 'Présentation 100% personnalisable avec 10 modèles au choix, image ou initiales.',
        defaultContent: {
            template: 'split_photo',
            showEyebrow: true,
            showTitle: true,
            showBio: true,
            showImage: true,
            showHighlights: true,
            showCta: false,
            eyebrow: 'À PROPOS DE MOI',
            title: 'Passionné par la tech et l’innovation utile',
            bio: 'J’accompagne les entreprises, startups et organisations dans la création de plateformes web robustes, rapides et élégantes.',
            imageUrl: '',
            displayName: 'Mon Nom',
            highlights: ['+5 ans d’expérience', 'Architecture Laravel & Vue.js', 'UI/UX Orienté Conversion'],
            ctaLabel: 'Me contacter',
            ctaUrl: '#contact',
            stats: [
                { id: 'as1', value: '5+', label: 'Années exp.' },
                { id: 'as2', value: '40+', label: 'Projets' },
                { id: 'as3', value: '100%', label: 'Engagement' },
            ],
        },
        defaultStyles: {
            desktop: { paddingTop: '36px', paddingBottom: '36px', paddingLeft: '24px', paddingRight: '24px', borderRadius: '20px', backgroundColor: '#ffffff' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'heading',
        label: 'Titre (Heading)',
        category: 'essentiels',
        icon: 'bi-type-h1',
        description: 'Titre hiérarchique H1 à H6 déplaçable avec flèches directionnelles.',
        defaultContent: {
            tag: 'h2',
            text: 'Votre titre accrocheur ici',
            align: 'left',
            offsetX: 0,
            offsetY: 0,
        },
        defaultStyles: {
            desktop: { color: '#0f172a', fontSize: '32px', fontWeight: '800', marginBottom: '12px' },
            tablet: { fontSize: '26px' },
            mobile: { fontSize: '22px' },
        },
    },
    {
        type: 'text',
        label: 'Texte / Paragraphe',
        category: 'essentiels',
        icon: 'bi-text-paragraph',
        description: 'Bloc de texte éditorial avec positionnement libre (X/Y) et alignement.',
        defaultContent: {
            text: 'Ajoutez ici votre description, votre proposition de valeur ou les détails de votre service.',
            align: 'left',
            offsetX: 0,
            offsetY: 0,
            maxWidth: '',
        },
        defaultStyles: {
            desktop: { color: '#475569', fontSize: '16px', lineHeight: '1.65', marginBottom: '12px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'image',
        label: 'Image & Superposition',
        category: 'multimedia',
        icon: 'bi-image',
        description: 'Image avec dégradé personnalisable, filtres et textes superposés au millimètre.',
        defaultContent: {
            url: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80',
            alt: 'Aperçu visuel',
            caption: '',
            gradientEnabled: false,
            gradientColor1: '#4f46e5',
            gradientColor2: '#06b6d4',
            gradientDirection: '135deg',
            gradientOpacity: 0.45,
            objectFit: 'cover',
            brightness: 100,
            contrast: 100,
            grayscale: 0,
            blur: 0,
            shadow: 'md',
            linkUrl: '',
            openInNewTab: true,
            overlays: [],
        },
        defaultStyles: {
            desktop: { borderRadius: '16px', width: '100%' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'button',
        label: 'Bouton CTA',
        category: 'essentiels',
        icon: 'bi-cursor-fill',
        description: 'Bouton d’action avec lien externe (nouvel onglet) ou ancre de section.',
        defaultContent: {
            label: 'Découvrir maintenant',
            linkType: 'anchor',
            href: '#contact',
            openInNewTab: false,
            icon: 'bi-arrow-right',
            variant: 'primary',
            align: 'left',
        },
        defaultStyles: {
            desktop: { backgroundColor: '#4f46e5', color: '#ffffff', borderRadius: '999px', paddingTop: '12px', paddingBottom: '12px', paddingLeft: '24px', paddingRight: '24px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'gallery',
        label: 'Galerie (10 Modèles)',
        category: 'multimedia',
        icon: 'bi-images',
        description: 'Grille, maçonnerie, diaporama, défilement horizontal et avant/après.',
        defaultContent: {
            template: 'grid_3',
            lightbox: true,
            title: 'Galerie & Réalisations Visuelles',
            items: [
                { id: 'g1', url: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80', title: 'Dashboard Analytics', category: 'SaaS' },
                { id: 'g2', url: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80', title: 'Plateforme Data', category: 'Web App' },
                { id: 'g3', url: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=800&q=80', title: 'Collaboration Équipe', category: 'Produit' },
            ],
        },
        defaultStyles: {
            desktop: { paddingTop: '24px', paddingBottom: '24px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'video',
        label: 'Vidéo YouTube (10 Modèles)',
        category: 'multimedia',
        icon: 'bi-youtube',
        description: 'Intégration YouTube avec 10 mises en page modernes.',
        defaultContent: {
            template: 'theater_wide',
            title: 'Vidéos & Démos Produit',
            videos: [
                { id: 'v1', title: 'Présentation de la solution', youtubeUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', duration: '03:20' },
                { id: 'v2', title: 'Architecture & Déploiement', youtubeUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', duration: '05:45' },
            ],
        },
        defaultStyles: {
            desktop: { paddingTop: '24px', paddingBottom: '24px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'testimonials',
        label: 'Témoignages',
        category: 'interactif',
        icon: 'bi-chat-square-quote-fill',
        description: 'Avis clients avec 6 modèles, animations et avatar upload ou Gravatar.',
        defaultContent: {
            template: 'cards_classic',
            animation: 'none',
            accentColor: '#4f46e5',
            title: 'Ce que disent nos partenaires',
            items: [
                { id: 't1', author: 'Amina K.', role: 'Fondatrice, FinTech 237', quote: 'Un travail rigoureux, livré dans les délais avec une attention exceptionnelle aux détails.', rating: 5, avatarType: 'none', avatarUrl: '', gravatarEmail: '' },
                { id: 't2', author: 'Marc D.', role: 'CTO, Studio Cloud', quote: 'Architecture propre et interface fluide. Je recommande vivement.', rating: 5, avatarType: 'none', avatarUrl: '', gravatarEmail: '' },
            ],
        },
        defaultStyles: {
            desktop: { paddingTop: '24px', paddingBottom: '24px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'counter_kpi',
        label: 'Compteurs / KPIs',
        category: 'interactif',
        icon: 'bi-graph-up-arrow',
        description: 'Chiffres clés personnalisables avec 6 modèles visuels.',
        defaultContent: {
            template: 'cards_classic',
            accentColor: '#4f46e5',
            bgColor: '#ffffff',
            textColor: '#0f172a',
            items: [
                { id: 'k1', prefix: '', value: '50', suffix: '+', label: 'Projets livrés', icon: 'bi-rocket-takeoff' },
                { id: 'k2', prefix: '', value: '99.9', suffix: '%', label: 'Satisfaction client', icon: 'bi-heart-fill' },
                { id: 'k3', prefix: '', value: '24/7', suffix: '', label: 'Support réactif', icon: 'bi-headset' },
            ],
        },
        defaultStyles: {
            desktop: { paddingTop: '20px', paddingBottom: '20px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'pricing',
        label: 'Tarifs / Offres',
        category: 'interactif',
        icon: 'bi-tags-fill',
        description: 'Grille tarifaire illimitée avec options personnalisables par offre.',
        defaultContent: {
            template: 'cards_classic',
            title: 'Nos Formules',
            plans: [
                { id: 'p1', name: 'Starter', price: '150 000 FCFA', period: '/ projet', features: ['Site Vitrine Responsive', 'Formulaire de Contact', 'SEO de base'], highlighted: false, badgeText: '', badgeColor: '#f97316', ctaLabel: 'Choisir cette offre', ctaUrl: '#contact' },
                { id: 'p2', name: 'Pro Studio', price: '350 000 FCFA', period: '/ projet', features: ['Application Laravel + Vue', 'Tableau de bord admin', 'Support 3 mois'], highlighted: true, badgeText: 'Offre Principale', badgeColor: '#4f46e5', ctaLabel: 'Démarrer maintenant', ctaUrl: '#contact' },
            ],
        },
        defaultStyles: {
            desktop: { paddingTop: '24px', paddingBottom: '24px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'faq_accordion',
        label: 'FAQ Accordéon',
        category: 'interactif',
        icon: 'bi-question-circle-fill',
        description: 'Questions/réponses illimitées avec modèles visuels.',
        defaultContent: {
            template: 'bordered_classic',
            accentColor: '#4f46e5',
            allowMultiple: false,
            title: 'Questions Fréquentes',
            items: [
                { id: 'f1', question: 'Quel est le délai moyen de livraison ?', answer: 'Entre 2 et 4 semaines selon la complexité fonctionnelle du projet.', openByDefault: true },
                { id: 'f2', question: 'Proposez-vous un accompagnement après mise en ligne ?', answer: 'Oui, chaque projet inclut une garantie technique et des options de maintenance.', openByDefault: false },
            ],
        },
        defaultStyles: {
            desktop: { paddingTop: '20px', paddingBottom: '20px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'contact',
        label: 'Contact (10 Modèles)',
        category: 'interactif',
        icon: 'bi-envelope-at-fill',
        description: 'Formulaire de contact avec 10 designs professionnels.',
        defaultContent: {
            template: 'split_info_form',
            title: 'Parlons de votre prochain projet',
            subtitle: 'Réponse garantie sous 24h ouvrées.',
            email: 'contact@exemple.com',
            phone: '+237 600 00 00 00',
            location: 'Douala / Yaoundé, Cameroun',
            buttonLabel: 'Envoyer le message',
            fields: [
                { id: 'cf_name', name: 'name', label: 'Votre nom', type: 'text', required: true },
                { id: 'cf_email', name: 'email', label: 'Votre email', type: 'email', required: true },
                { id: 'cf_subject', name: 'subject', label: 'Sujet du message', type: 'text', required: false },
                { id: 'cf_message', name: 'message', label: 'Décrivez votre besoin...', type: 'textarea', required: true },
            ],
        },
        defaultStyles: {
            desktop: { paddingTop: '32px', paddingBottom: '32px', borderRadius: '20px', backgroundColor: '#f8fafc' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'tech237_dynamic',
        label: 'Données Tech237 (Live)',
        category: 'tech237',
        icon: 'bi-lightning-charge-fill',
        description: 'Projets, POCs, Badges, Formulaires ou Uptime Heartbeat synchronisés.',
        defaultContent: {
            source: 'projects', // projects | pocs | badges | forms | monitors
            title: 'Mes Projets Publiés',
            maxItems: 6,
        },
        defaultStyles: {
            desktop: { paddingTop: '24px', paddingBottom: '24px' },
            tablet: {},
            mobile: {},
        },
    },
    {
        type: 'slider',
        label: 'Slider / Carrousel',
        category: 'multimedia',
        icon: 'bi-collection-play-fill',
        description: 'Diaporama personnalisable avec plusieurs designs, autoplay et contrôles.',
        defaultContent: {
            template: 'classic_fade',
            autoplay: true,
            intervalMs: 4500,
            showArrows: true,
            showDots: true,
            heightPx: 380,
            slides: [
                { id: 'sl1', title: 'Slide Principal', subtitle: 'Présentez votre meilleure réalisation', imageUrl: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1000&q=80', ctaLabel: 'Découvrir', ctaUrl: '#contact' },
                { id: 'sl2', title: 'Innovation & Design', subtitle: 'Des interfaces pensées pour vos utilisateurs', imageUrl: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1000&q=80', ctaLabel: 'Voir plus', ctaUrl: '#projects' },
            ],
        },
        defaultStyles: { desktop: { paddingTop: '16px', paddingBottom: '16px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'whatsapp',
        label: 'Bouton WhatsApp',
        category: 'interactif',
        icon: 'bi-whatsapp',
        description: 'Lien direct WhatsApp personnalisable avec téléphone et message.',
        defaultContent: {
            title: 'Discutons sur WhatsApp',
            description: 'Réponse rapide directe sur votre téléphone.',
            phone: '+237600000000',
            prefillMessage: 'Bonjour, je souhaite discuter d’un projet.',
            buttonLabel: 'Écrire sur WhatsApp',
            bgColor: '#25D366',
            textColor: '#ffffff',
        },
        defaultStyles: { desktop: { paddingTop: '16px', paddingBottom: '16px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'paypal',
        label: 'Paiement PayPal',
        category: 'interactif',
        icon: 'bi-paypal',
        description: 'Bouton / carte de paiement ou don PayPal personnalisable.',
        defaultContent: {
            title: 'Régler ou Soutenir via PayPal',
            description: 'Paiement sécurisé en un clic.',
            paypalTarget: 'https://paypal.me/votrecompte',
            amount: '25',
            currency: 'EUR',
            buttonLabel: 'Payer avec PayPal',
            bgColor: '#0070BA',
            textColor: '#ffffff',
        },
        defaultStyles: { desktop: { paddingTop: '16px', paddingBottom: '16px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'vimeo',
        label: 'Vidéo Vimeo',
        category: 'multimedia',
        icon: 'bi-vimeo',
        description: 'Lecteur Vimeo avec plusieurs modèles de présentation.',
        defaultContent: {
            template: 'theater_wide',
            title: 'Vidéo Vimeo',
            videos: [
                { id: 'vm1', title: 'Showreel Studio', vimeoUrl: 'https://vimeo.com/76979871', description: 'Présentation vidéo haute définition.' },
            ],
        },
        defaultStyles: { desktop: { paddingTop: '20px', paddingBottom: '20px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'github_projects',
        label: 'Projets GitHub',
        category: 'tech237',
        icon: 'bi-github',
        description: 'Affiche automatiquement les dépôts publics d’un compte GitHub.',
        defaultContent: {
            title: 'Mes Projets Open-Source GitHub',
            username: 'laravel',
            maxItems: 6,
            accentColor: '#0f172a',
        },
        defaultStyles: { desktop: { paddingTop: '24px', paddingBottom: '24px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'map_embed',
        label: 'Carte Maps / OSM',
        category: 'interactif',
        icon: 'bi-geo-alt-fill',
        description: 'Intégrez OpenStreetMap ou Google Maps.',
        defaultContent: {
            provider: 'openstreetmap', // openstreetmap | google
            title: 'Nous Trouver',
            address: 'Douala, Cameroun',
            lat: '4.0511',
            lng: '9.7679',
            zoom: 13,
            heightPx: 340,
        },
        defaultStyles: { desktop: { paddingTop: '20px', paddingBottom: '20px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'visitor_counter',
        label: 'Compteur de Visites',
        category: 'interactif',
        icon: 'bi-eye-fill',
        description: 'Affiche le nombre de visites en direct sur votre site.',
        defaultContent: {
            label: 'Visiteurs sur ce site',
            showUniques: true,
            bgColor: '#f8fafc',
            accentColor: '#4f46e5',
        },
        defaultStyles: { desktop: { paddingTop: '12px', paddingBottom: '12px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'back_to_top',
        label: 'Bouton Haut de Page',
        category: 'interactif',
        icon: 'bi-arrow-up-circle-fill',
        description: 'Bouton 100% personnalisable (forme, taille, position, icône, couleurs).',
        defaultContent: {
            label: 'Haut',
            icon: 'bi-arrow-up',
            showLabel: true,
            floating: true,
            position: 'bottom-right',
            shape: 'pill',
            size: 'md',
            shadow: true,
            pulseAnimation: false,
            bgColor: '#4f46e5',
            textColor: '#ffffff',
        },
        defaultStyles: { desktop: {}, tablet: {}, mobile: {} },
    },
    {
        type: 'social_share',
        label: 'Partage Réseaux',
        category: 'interactif',
        icon: 'bi-share-fill',
        description: 'Boutons de partage placés en ligne ou en barre flottante (haut/gauche/droite).',
        defaultContent: {
            position: 'inline', // inline | top | left | right
            title: 'Partager cette page',
            platforms: ['whatsapp', 'linkedin', 'twitter', 'facebook', 'copy'],
        },
        defaultStyles: { desktop: { paddingTop: '12px', paddingBottom: '12px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'footer_pro',
        label: 'Pied de Page Pro',
        category: 'mise_en_page',
        icon: 'bi-window-desktop',
        description: 'Footer 100% personnalisable avec liens sociaux et copyright.',
        defaultContent: {
            brandDescription: 'Solutions web modernes, rapides et sur-mesure.',
            copyright: '© 2026 Tous droits réservés.',
            bgColor: '#0f172a',
            textColor: '#f8fafc',
            links: [
                { id: 'fl1', label: 'Accueil', href: '#hero' },
                { id: 'fl2', label: 'À propos', href: '#about' },
                { id: 'fl3', label: 'Contact', href: '#contact' },
            ],
            socials: [
                { id: 'fs1', icon: 'bi-linkedin', url: 'https://linkedin.com' },
                { id: 'fs2', icon: 'bi-github', url: 'https://github.com' },
            ],
        },
        defaultStyles: { desktop: {}, tablet: {}, mobile: {} },
    },
    {
        type: 'page_loader',
        label: 'Chargement de Page',
        category: 'interactif',
        icon: 'bi-hourglass-split',
        description: 'Écran de préchargement avec logo, animation et durée.',
        defaultContent: {
            enabled: true,
            logoUrl: '',
            text: 'Chargement en cours...',
            animation: 'spinner', // spinner | pulse | progress_bar | dots | curtain
            durationMs: 1200,
            bgColor: '#0f172a',
            textColor: '#ffffff',
        },
        defaultStyles: { desktop: {}, tablet: {}, mobile: {} },
    },
    {
        type: 'parallax',
        label: 'Effet Parallaxe',
        category: 'multimedia',
        icon: 'bi-layers-half',
        description: 'Bannière parallaxe 100% personnalisable (modèles, lien, texte optionnel).',
        defaultContent: {
            template: 'classic_overlay',
            showText: true,
            showCta: true,
            title: 'Une expérience visuelle immersive',
            subtitle: 'Effet parallaxe fluide et personnalisable',
            imageUrl: 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
            overlayColor: '#0f172a',
            overlayOpacity: 0.55,
            minHeightPx: 360,
            fixedBg: true,
            ctaLabel: 'En savoir plus',
            ctaUrl: '#contact',
            redirectUrl: '',
            openInNewTab: false,
        },
        defaultStyles: { desktop: {}, tablet: {}, mobile: {} },
    },
    {
        type: 'ambient_particles',
        label: 'Ambiance & Curseur Tech',
        category: 'interactif',
        icon: 'bi-snow',
        description: 'Particules saisonnières (Neige, Matrix, Confettis...) + Spotlight & Curseur Anneau.',
        defaultContent: {
            effect: 'snow', // none | snow | confetti | gold_stars | matrix | zen_bubbles
            mouseSpotlight: true,
            techRingCursor: true,
        },
        defaultStyles: { desktop: {}, tablet: {}, mobile: {} },
    },
    {
        type: 'cv_builder',
        label: 'CV Pro & Export PDF',
        category: 'tech237',
        icon: 'bi-file-earmark-person-fill',
        description: 'CV complet (10 modèles, formations, projets, compétences %, loisirs, PDF & carte LinkedIn).',
        defaultContent: {
            template: 'modern_sidebar',
            fullName: 'Jean Dupont',
            jobTitle: 'Développeur Fullstack Senior',
            experienceYears: '5',
            email: 'contact@exemple.com',
            phone: '+237 600 00 00 00',
            location: 'Douala, Cameroun',
            photoUrl: '',
            accentColor: '#4f46e5',
            summary: 'Ingénieur logiciel spécialisé en Laravel, Vue.js et architectures cloud évolutives.',
            formations: [
                { id: 'fm1', title: 'Master Génie Logiciel', school: 'Université / École Polytechnique', period: '2018 - 2020' },
            ],
            projects: [
                { id: 'cp1', name: 'Plateforme SaaS Tech237', description: 'Constructeur visuel de sites web.', url: 'https://tech237.com', imageUrl: '' },
            ],
            skills: [
                { id: 'sk1', name: 'Laravel / PHP', category: 'Backend', level: 92 },
                { id: 'sk2', name: 'Vue.js / Inertia', category: 'Frontend', level: 88 },
                { id: 'sk3', name: 'Docker / CI-CD', category: 'DevOps', level: 80 },
            ],
            hobbies: [
                { id: 'hb1', label: 'Open Source', icon: 'bi-code-slash' },
                { id: 'hb2', label: 'Photographie', icon: 'bi-camera' },
            ],
            socials: [
                { id: 'sc1', label: 'LinkedIn', icon: 'bi-linkedin', url: 'https://linkedin.com' },
                { id: 'sc2', label: 'GitHub', icon: 'bi-github', url: 'https://github.com' },
            ],
        },
        defaultStyles: { desktop: { paddingTop: '24px', paddingBottom: '24px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'nested_section',
        label: 'Section Imbriquée',
        category: 'mise_en_page',
        icon: 'bi-bounding-box',
        description: 'Conteneur imbriqué permettant d’insérer des lignes, colonnes et modules dans un bloc.',
        defaultContent: {
            title: 'Bloc Imbriqué',
            bgColor: '#f8fafc',
            bgImage: '',
            borderRadius: 16,
            padding: 20,
            rows: [
                {
                    id: 'nrow_1',
                    styles: { desktop: { gap: '16px', alignItems: 'stretch' }, tablet: {}, mobile: {} },
                    columns: [
                        { id: 'ncol_1', width: 12, styles: { desktop: {}, tablet: {}, mobile: {} }, modules: [] },
                    ],
                },
            ],
        },
        defaultStyles: { desktop: { paddingTop: '8px', paddingBottom: '8px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'bento_grid',
        label: 'Grille Bento Moderne',
        category: 'mise_en_page',
        icon: 'bi-grid-1x2-fill',
        description: 'Grille asymétrique style Apple/SaaS pour mettre en valeur vos atouts.',
        defaultContent: {
            title: 'Nos Atouts en un Coup d’Œil',
            cards: [
                { id: 'bg1', title: 'Performance Extrême', description: 'Architecture optimisée pour un chargement instantané.', icon: 'bi-lightning-charge-fill', span: 2, bgColor: '#eef2ff', textColor: '#1e1b4b', badge: 'Rapide' },
                { id: 'bg2', title: 'Design Sur-Mesure', description: 'Identité visuelle unique.', icon: 'bi-palette-fill', span: 1, bgColor: '#fdf2f8', textColor: '#831843', badge: 'UI/UX' },
                { id: 'bg3', title: 'Sécurité Avancée', description: 'Protection des données et conformité.', icon: 'bi-shield-lock-fill', span: 1, bgColor: '#ecfdf5', textColor: '#064e3b', badge: 'SSL' },
                { id: 'bg4', title: 'Support Dédié 24/7', description: 'Une équipe réactive à vos côtés à chaque étape.', icon: 'bi-headset', span: 2, bgColor: '#0f172a', textColor: '#f8fafc', badge: 'Pro' },
            ],
        },
        defaultStyles: { desktop: { paddingTop: '24px', paddingBottom: '24px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'before_after',
        label: 'Comparateur Avant / Après',
        category: 'multimedia',
        icon: 'bi-symmetry-vertical',
        description: 'Curseur interactif pour comparer deux images (refonte, retouche, avant/après).',
        defaultContent: {
            title: 'Transformation Avant / Après',
            beforeLabel: 'Avant',
            afterLabel: 'Après',
            beforeUrl: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80&sat=-100',
            afterUrl: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80',
            initialPercent: 50,
            heightPx: 360,
        },
        defaultStyles: { desktop: { paddingTop: '20px', paddingBottom: '20px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'countdown',
        label: 'Compte à Rebours',
        category: 'interactif',
        icon: 'bi-alarm-fill',
        description: 'Minuteur dynamique pour lancement de produit, événement ou offre limitée.',
        defaultContent: {
            title: 'Lancement imminent',
            subtitle: 'Ne manquez pas notre prochaine annonce !',
            targetDate: '2026-12-31T23:59',
            accentColor: '#4f46e5',
            bgColor: '#0f172a',
            textColor: '#ffffff',
            bgImage: '',
            gradientEnabled: false,
            gradientColor1: '#4f46e5',
            gradientColor2: '#06b6d4',
            gradientDirection: '135deg',
            gradientOpacity: 0.55,
            brightness: 100,
            contrast: 100,
            grayscale: 0,
            blur: 0,
        },
        defaultStyles: { desktop: { paddingTop: '20px', paddingBottom: '20px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'progress_bars',
        label: 'Barres de Progression',
        category: 'interactif',
        icon: 'bi-bar-chart-steps',
        description: 'Barres de compétences ou d’avancement animées avec pourcentages.',
        defaultContent: {
            title: 'Mes Domaines d’Expertise',
            items: [
                { id: 'pb1', label: 'Développement Fullstack (Laravel / Vue)', percent: 95, color: '#4f46e5' },
                { id: 'pb2', label: 'Architecture Cloud & DevOps', percent: 85, color: '#06b6d4' },
                { id: 'pb3', label: 'Design UI/UX & Prototypage', percent: 90, color: '#ec4899' },
            ],
        },
        defaultStyles: { desktop: { paddingTop: '20px', paddingBottom: '20px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'marquee_ticker',
        label: 'Bandeau Défilant (Marquee)',
        category: 'interactif',
        icon: 'bi-megaphone-fill',
        description: 'Ruban animé en continu pour partenaires, technologies ou annonces.',
        defaultContent: {
            bgColor: '#0f172a',
            textColor: '#ffffff',
            speedSec: 20,
            items: ['Laravel 12', 'Vue.js 3', 'Inertia.js', 'Tailwind & Bootstrap', 'Design Sur-Mesure', 'Tech237.com'],
        },
        defaultStyles: { desktop: { paddingTop: '12px', paddingBottom: '12px' }, tablet: {}, mobile: {} },
    },
    {
        type: 'tabs_content',
        label: 'Onglets Interactifs (Tabs)',
        category: 'interactif',
        icon: 'bi-folder2-open',
        description: 'Organisez vos services ou détails en onglets cliquables élégants.',
        defaultContent: {
            accentColor: '#4f46e5',
            tabs: [
                { id: 'tb1', label: 'Conception', icon: 'bi-pencil-square', title: 'Design & Architecture', body: 'Nous concevons des interfaces intuitives et des architectures solides adaptées à vos objectifs.' },
                { id: 'tb2', label: 'Développement', icon: 'bi-code-slash', title: 'Code Propre & Rapide', body: 'Développement fullstack moderne avec tests automatisés et optimisation SEO.' },
                { id: 'tb3', label: 'Déploiement', icon: 'bi-rocket-takeoff', title: 'Mise en Production', body: 'Hébergement sécurisé, monitoring continu et accompagnement long terme.' },
            ],
        },
        defaultStyles: { desktop: { paddingTop: '20px', paddingBottom: '20px' }, tablet: {}, mobile: {} },
    },
];

export function getModuleMeta(type) {
    return MODULE_CATALOG.find((m) => m.type === type) || MODULE_CATALOG[0];
}

export function createModule(type) {
    const meta = getModuleMeta(type);
    return {
        id: uid('mod'),
        type: meta.type,
        content: JSON.parse(JSON.stringify(meta.defaultContent)),
        styles: JSON.parse(JSON.stringify(meta.defaultStyles)),
        advanced: {
            cssClass: '',
            customId: '',
            hideOnDesktop: false,
            hideOnTablet: false,
            hideOnMobile: false,
            animation: 'none',
        },
    };
}

export function createRow(columnsCount = 1, initialModules = []) {
    const widthsMap = {
        1: [12],
        2: [6, 6],
        3: [4, 4, 4],
        4: [3, 3, 3, 3],
    };
    const widths = widthsMap[columnsCount] || [12];
    const columns = widths.map((w, idx) => ({
        id: uid('col'),
        width: w,
        styles: { desktop: {}, tablet: {}, mobile: {} },
        modules: idx === 0 && initialModules.length ? initialModules : [],
    }));

    return {
        id: uid('row'),
        styles: {
            desktop: { gap: '20px', alignItems: 'stretch' },
            tablet: {},
            mobile: {},
        },
        columns,
    };
}

export function createSection(initialModuleType = null) {
    const initialModules = initialModuleType ? [createModule(initialModuleType)] : [];
    return {
        id: uid('sec'),
        label: 'Section',
        styles: {
            desktop: {
                paddingTop: '32px',
                paddingBottom: '32px',
                paddingLeft: '16px',
                paddingRight: '16px',
                backgroundColor: 'transparent',
                maxWidth: '1180px',
            },
            tablet: {},
            mobile: {},
        },
        advanced: {
            customId: '',
            cssClass: '',
            fullWidth: false,
        },
        rows: [createRow(1, initialModules)],
    };
}
