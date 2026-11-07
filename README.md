# 🚀 Melty.fr Clone - Application Web Complète

Application web moderne, réactive et sécurisée clonant fidèlement l'expérience du média **Melty.fr**, développée avec :
- **Front-End** : Inertia.js + Vue.js 3 + Bootstrap 5 + Bootstrap Icons (Navigation fluide Single-Page App sans rechargement de page).
- **Back-Office / Administration** : Laravel + Livewire + Bootstrap 5 + Éditeurs WYSIWYG Summernote bilingues (FR / EN).
- **Polices & Ressources 100% Locales (Offline Ready)** : Police Google Poppins embarquée en local (`public/assets/fonts/`), Bootstrap 5 (`public/assets/css/` & `public/assets/js/`), Bootstrap Icons (`public/assets/css/fonts/`), Summernote WYSIWYG local.
- **Stockage Médias & Fichiers** :
  - Images uploadées stockées dans : `storage/images/` (accessible directement via `public/storage/images/`).
  - Feuilles de style et scripts locaux : `public/assets/css/`, `public/assets/images/`, `public/assets/js/`.

---

## 🔑 Identifiants d'Administration par Défaut

- **URL de Connexion** : `http://127.0.0.1:8000/admin/login`
- **Email** : `admin@admin.test`
- **Mot de passe initial** : `000000` (six zéros)
- **Rôle** : `super_admin`
- **Guard d'authentification** : `admin`

---

## 🛡️ Sécurité & Système de Connexion

1. **Protection Anti-Bruteforce & Bannissement Automatique** :
   - Si un administrateur échoue plus de 5 fois consécutives, son compte est automatiquement banni (`is_banned = 1`, horodatage `banned_at`).
   - Tout administrateur banni est bloqué de l'administration.
2. **Espace Exclusif Super Admin (`/admin/banned-admins`)** :
   - Seul le `super_admin` a accès à cette section exclusive répertoriant tous les comptes bannis.
   - C'est l'unique endroit permettant de restaurer un compte banni et réinitialiser son compteur de tentatives.
3. **Formulaire de Connexion Livewire & Captcha Mathématique** :
   - Bouton pour afficher / masquer le mot de passe (œil).
   - Question / Réponse mathématique antispam dynamique (ex: `2 + 1 = 3`), régénérée à chaque actualisation ou tentative.
   - `autocomplete="off"` pour désactiver le pré-remplissage automatique des navigateurs.
4. **Bannière d'Alerte Mot de Passe par Défaut (`default_password`)** :
   - À la création d'un administrateur ou lors d'une réinitialisation, le mot de passe est `000000` et `default_password = 0`.
   - Une bannière rouge prévenante apparaît sur tout le tableau de bord intimant de changer le mot de passe.
   - Dès que le mot de passe est personnalisé, `default_password = 1` et la bannière disparaît.
   - Les admins peuvent réinitialiser le mot de passe d'un membre à `000000`, remettant `default_password = 0`.
5. **Modales Statiques de Confirmation** :
   - Modale statique sur la déconnexion.
   - Modale statique sur les suppressions, bannissements et réinitialisations.
6. **Journal d'Audit Complet (`admin_logs`)** :
   - Enregistrement de tous les événements : connexions, pages visitées, créations, modifications, suppressions, adresses IP et user-agents.

---

## 📰 Fonctionnalités Éditoriales & Back-Office

- **Articles Bilingues (FR / EN)** :
  - Titre, chapeau et contenu en Français et en Anglais.
  - Deux éditeurs WYSIWYG Summernote complets et 100% hors-ligne.
  - Mise en forme Microsoft Word (gras, italique, polices, couleurs, listes ordonnées et à puces).
  - Création de jolis tableaux.
  - Ajout de liens et intégration automatique de vidéos YouTube en lecteur interactif.
  - Insertion d'images stockées directement dans `storage/images/`.
  - Prévisualisation de l'article en modal statique avant publication.
  - **Gestion des Rôles & Workflow de Validation** : Si un rédacteur (`editor`) soumet un article, celui-ci passe obligatoirement en statut `pending_review` (en attente de review) pour validation par un `admin` ou `super_admin`.
- **Dossiers Thématiques / Hubs (ex: Dossier ChatGPT & IA)** :
  - Regroupe tous les articles liés à une thématique forte (ex: ChatGPT, Marvel MCU, etc.).
- **Catégories & Sous-Catégories** :
  - Arborescence à 2 niveaux (ex: Séries -> Netflix), couleurs de badges et icônes Bootstrap personnalisables.
- **Tags** : Mots-clés pour relier les contenus.
- **Tests d'Applications Mobiles, Web & Desktop** :
  - Notation de l'UI (sur 20), de l'UX (sur 20), de la prise en main (sur 20) et note finale sur 20.
  - Liens vers le Google Play Store et l'Apple App Store.
  - Logos et catégories de stores (Play Store, App Store, Web App, Desktop...).
- **Tests Jeux Vidéo** :
  - Notation du gameplay (sur 20), graphismes (sur 20), accessibilité (sur 20), bande sonore (sur 20) et note finale sur 20.
  - Plateformes supportées : PS5, PS4, Xbox Series S & X, Xbox One, Nintendo Switch, Nintendo Switch 2, PC, Linux/Mac, Mobile.
  - Points forts et points faibles.
- **Interviews Exclusives** :
  - Nom et rôle de la personnalité, intégration vidéo YouTube, transcription questions/réponses en FR et EN.
- **Bannières Publicitaires (Régie interne)** :
  - Emplacements : Header Top, Sidebar, Milieu de page (Home Middle), In-Article, Footer.
- **Sliders d'Accueil** : Carrousel dynamique avec visuels et boutons de redirection.
- **Newsletter & Abonnés** :
  - Liste des inscrits avec horodatage et adresse IP tracée.
  - Rédacteur et expéditeur de newsletter bilingue (FR / EN) en WYSIWYG.
  - Historique complet des newsletters envoyées et nombre de bénéficiaires.
- **Paramètres Généraux du Site (Settings)** :
  - Nom du site, logo SVG, image de partage OpenGraph SEO.
  - Boîte postale, emails, téléphones WhatsApp et secondaires, ville, localisation.
  - Réseaux sociaux : Facebook, Twitter/X, Instagram, TikTok, YouTube, LinkedIn, Telegram.
  - Mots-clés et méta descriptions (FR et EN).
  - **Coordonnées de soutien / dons** : Orange Money (Nom + Téléphone), MTN Mobile Money (Nom + Téléphone), RIB / Coordonnées bancaires.
  - **Pages Légales** : Conditions d'utilisation et Politique de confidentialité éditables en WYSIWYG (FR et EN).

---

## 🎨 Front-End Melty (Inertia.js + Vue.js 3)

- **Identité Visuelle Melty.fr** :
  - Bandeau Breaking News / Tendance en haut de page.
  - Barre de navigation sticky noire et rouge avec logo vectoriel et rubriques.
  - **Recherche plein écran avec autocomplétion en direct** : En cliquant sur la recherche, un modal fullscreen interactif en Vue.js apparaît avec suggestions en temps réel (Articles, Jeux vidéo, Apps, Dossiers).
  - Barre secondaire des dossiers chauds (pills dynamiques).
  - Grille Hero à la Une (grand article principal + articles tendances empilés).
  - Sections thématiques dédiées : Actualités, Tests Jeux Vidéo (avec badges note /20), Tests Applications (avec badges note /20), Interviews.
  - Emplacements publicitaires intégrés avec label `PUBLICITÉ`.
  - Page **"Nous soutenir"** dédiée avec les informations Orange Money, MTN MoMo et RIB bancaire.
  - Sélecteur de langue interactif 🇫🇷 FR / 🇬🇧 EN en Single-Page App sans rechargement de page.

---

## 🚀 Démarrage du Projet

### 1. Activer le serveur web Laravel :
```bash
php artisan serve
```

### 2. Compiler ou regarder les assets Vue 3 (optionnel si modification) :
```bash
npm run build
# ou en développement continu :
npm run dev
```

### 3. Exécuter les tests unitaires et fonctionnels :
```bash
php artisan test
```
*(11 tests et 54 assertions exécutés avec succès !)*
