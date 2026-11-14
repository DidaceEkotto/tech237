export const WEB_FONTS_30 = [
    'Inter',
    'Plus Jakarta Sans',
    'Poppins',
    'Roboto',
    'Open Sans',
    'Montserrat',
    'Lato',
    'Raleway',
    'Nunito',
    'Playfair Display',
    'Merriweather',
    'Lora',
    'Oswald',
    'Bebas Neue',
    'Space Grotesk',
    'DM Sans',
    'Outfit',
    'Sora',
    'Syne',
    'Manrope',
    'Work Sans',
    'Rubik',
    'Quicksand',
    'Fira Code',
    'JetBrains Mono',
    'Source Sans 3',
    'Ubuntu',
    'Josefin Sans',
    'Cinzel',
    'Archivo',
];

const loadedFonts = new Set();

export function ensureFontLoaded(fontName) {
    if (!fontName || typeof document === 'undefined' || loadedFonts.has(fontName)) return;
    loadedFonts.add(fontName);
    const id = 'pb-gfont-' + fontName.replace(/\s+/g, '-').toLowerCase();
    if (document.getElementById(id)) return;
    const link = document.createElement('link');
    link.id = id;
    link.rel = 'stylesheet';
    link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(fontName)}:wght@300;400;500;600;700;800&display=swap`;
    document.head.appendChild(link);
}

export const TEXT_ANIMATIONS = [
    { id: 'none', label: 'Aucune' },
    { id: 'typewriter', label: 'Machine à écrire (Typewriter)' },
    { id: 'fade_up', label: 'Apparition vers le haut' },
    { id: 'fade_in', label: 'Fondu doux' },
    { id: 'slide_left', label: 'Glissement depuis la gauche' },
    { id: 'slide_right', label: 'Glissement depuis la droite' },
    { id: 'zoom_in', label: 'Zoom progressif' },
    { id: 'pulse', label: 'Pulsation douce' },
];

export const IMAGE_ANIMATIONS = [
    { id: 'none', label: 'Aucune' },
    { id: 'zoom_hover', label: 'Zoom au survol' },
    { id: 'float_slow', label: 'Flottement doux' },
    { id: 'fade_in', label: 'Fondu d’entrée' },
    { id: 'tilt_3d', label: 'Inclinaison 3D' },
    { id: 'pulse', label: 'Pulsation' },
];

export const EMOJI_CATALOG = [
    { char: '🚀', name: 'fusee rocket launch' },
    { char: '✨', name: 'etoiles sparkles magic' },
    { char: '🔥', name: 'feu fire hot' },
    { char: '💡', name: 'idee lightbulb' },
    { char: '💻', name: 'ordinateur laptop code' },
    { char: '📱', name: 'mobile telephone phone' },
    { char: '🎨', name: 'design palette art' },
    { char: '⚡', name: 'eclair fast lightning' },
    { char: '🎯', name: 'cible target goal' },
    { char: '🏆', name: 'trophee trophy award' },
    { char: '✅', name: 'valide check ok' },
    { char: '❤️', name: 'coeur heart love' },
    { char: '🌍', name: 'monde globe earth' },
    { char: '📈', name: 'croissance chart stats' },
    { char: '💼', name: 'travail briefcase business' },
    { char: '🎓', name: 'diplome formation study' },
    { char: '🛠️', name: 'outils tools dev' },
    { char: '🔒', name: 'securite lock' },
    { char: '📸', name: 'photo camera' },
    { char: '🎥', name: 'video cinema' },
    { char: '💬', name: 'message chat contact' },
    { char: '📞', name: 'appel phone' },
    { char: '📧', name: 'email mail' },
    { char: '📍', name: 'position pin map' },
    { char: '🎉', name: 'fete party confetti' },
    { char: '⭐', name: 'etoile star' },
    { char: '💎', name: 'diamant premium' },
    { char: '🤝', name: 'partenariat handshake' },
    { char: '🇨🇲', name: 'cameroun cameroon 237' },
    { char: '💳', name: 'paiement card paypal' },
    { char: '🎵', name: 'musique music' },
    { char: '⚽', name: 'sport foot' },
    { char: '📚', name: 'livres lecture' },
    { char: '✈️', name: 'voyage travel' },
];
