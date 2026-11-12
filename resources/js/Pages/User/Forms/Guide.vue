<template>
  <FrontLayout>
    <Head title="Guide d'utilisation & QR Code • Tech237 Forms" />

    <div class="forms-guide-page py-5 bg-slate-50 min-vh-100" style="background-color: #f8fafc;">
      <div class="container" style="max-width: 980px;">
        
        <!-- Header Nav -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
          <Link href="/espace-utilisateur/formulaires" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-2 fw-semibold text-dark shadow-xs d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour aux formulaires</span>
          </Link>

          <Link href="/espace-utilisateur/formulaires/creer" class="btn btn-sm btn-success rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Créer un formulaire</span>
          </Link>
        </div>

        <!-- Hero Card -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-5 text-white position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #071738 0%, #0B2559 50%, #061e47 100%);">
          <div class="row align-items-center g-4 position-relative z-1">
            <div class="col-lg-8">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" 
                   style="background: rgba(0, 168, 89, 0.18); border: 1px solid rgba(0, 168, 89, 0.4); color: #34d399; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="bi bi-book-half"></i>
                <span>Documentation Complète</span>
              </div>
              <h1 class="display-6 fw-black text-white mb-3" style="letter-spacing: -0.5px;">
                Guide d'Utilisation &amp; QR Code
              </h1>
              <p class="lead text-light mb-0" style="font-size: 1.1rem; line-height: 1.6; color: #e2e8f0;">
                Maîtrisez la création de formulaires nouvelle génération sur Tech237 : génération de QR Codes pour vos événements, modes Typeform et Classique, questions spécialisées tech et alertes email instantanées.
              </p>
            </div>

            <div class="col-lg-4 text-center">
              <div class="p-3 bg-white rounded-4 shadow d-inline-block text-dark">
                <i class="bi bi-qr-code text-success" style="font-size: 80px;"></i>
                <div class="extra-small fw-bold text-muted mt-1">Partage Rapide par QR Code</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Guide Sections Grid -->
        <div class="row g-4 mb-5">
          
          <!-- SECTION 1: QR CODE INTEL (FOCUS USER REQUEST) -->
          <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white border-start border-4 border-success">
              <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                  <i class="bi bi-qr-code fs-3"></i>
                </div>
                <div>
                  <h3 class="h5 fw-black text-dark mb-1">1. Le Guide Complet du QR Code Intelligent</h3>
                  <div class="extra-small text-muted fw-bold text-uppercase">Partage événementiel, présentations et affiches</div>
                </div>
              </div>

              <p class="text-muted small mb-4" style="line-height: 1.7;">
                Chaque formulaire créé sur Tech237 dispose automatiquement d'un <strong>QR Code vectoriel haute résolution</strong> lié directement à son URL courte <code>/f/votre-slug</code>. Ce QR Code est généré localement en JavaScript, sans dépendance externe lente, et peut être téléchargé en deux formats :
              </p>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                      <i class="bi bi-file-earmark-image text-primary"></i> Format PNG (Haute Définition 500×500)
                    </h6>
                    <p class="extra-small text-muted mb-0">
                      Parfait pour insérer directement sur vos <strong>slides de conférences (PowerPoint, Google Slides, Keynote)</strong>, partager dans des groupes WhatsApp, ou publier sur LinkedIn, Twitter et Facebook.
                    </p>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                      <i class="bi bi-filetype-svg text-success"></i> Format SVG (Vectoriel Infini)
                    </h6>
                    <p class="extra-small text-muted mb-0">
                      Indispensable pour vos <strong>kakemonos, bâches, affiches imprimées ou t-shirts</strong>. Le format SVG ne perd aucune qualité même imprimé sur un panneau géant de 4 mètres de haut.
                    </p>
                  </div>
                </div>
              </div>

              <div class="p-4 rounded-4 bg-dark text-white">
                <h6 class="fw-bold mb-2 text-warning d-flex align-items-center gap-2">
                  <i class="bi bi-lightbulb-fill"></i> Bonnes pratiques pour vos événements &amp; hackathons :
                </h6>
                <ul class="mb-0 small text-light ps-3 d-flex flex-column gap-2" style="line-height: 1.6;">
                  <li><strong>Affichez le QR Code au début et à la fin de votre présentation :</strong> Laissez-le affiché pendant au moins 60 secondes pour permettre au public de scanner confortablement depuis le fond de la salle.</li>
                  <li><strong>Activez le mode Conversationnel (Typeform) :</strong> Sur smartphone, les participants n'aiment pas remplir de longs formulaires au format texte microscopique. Le mode 1 question par 1 garantit un taux de réponse 2× plus élevé.</li>
                  <li><strong>Testez le contraste :</strong> Si votre diapositive a un fond sombre, téléchargez le QR Code avec un arrière-plan blanc ou intégrez une bordure claire autour du code.</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- SECTION 2: MODES D'AFFICHAGE -->
          <div class="col-md-6">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                  <i class="bi bi-aspect-ratio fs-4"></i>
                </div>
                <div>
                  <h4 class="h6 fw-bold text-dark mb-0">2. Les Deux Modes d'Affichage</h4>
                  <div class="extra-small text-muted">Classique vs Conversationnel</div>
                </div>
              </div>

              <div class="d-flex flex-column gap-3 small text-muted">
                <div class="p-3 bg-light rounded-3 border">
                  <strong class="text-dark d-block mb-1">📄 Mode Classique (Style Google Forms)</strong>
                  Tous les champs défilent sur une page verticale. C'est le format idéal pour les formulaires administratifs, les questionnaires avec peu de questions ou les utilisateurs sur grand écran.
                </div>
                <div class="p-3 bg-light rounded-3 border">
                  <strong class="text-dark d-block mb-1">💬 Mode Conversationnel (Style Typeform)</strong>
                  Une seule question s'affiche à la fois, avec jauge de progression, transitions fluides et raccourci clavier <kbd>Entrée ↵</kbd>. Parfait pour les sondages mobiles et les hackathons.
                </div>
              </div>
            </div>
          </div>

          <!-- SECTION 3: CHAMPS SPÉCIALISÉS TECH -->
          <div class="col-md-6">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                  <i class="bi bi-code-slash fs-4"></i>
                </div>
                <div>
                  <h4 class="h6 fw-bold text-dark mb-0">3. Champs Spécialisés Développeurs</h4>
                  <div class="extra-small text-muted">Taillés pour l'écosystème Tech237</div>
                </div>
              </div>

              <ul class="list-unstyled small text-muted d-flex flex-column gap-2 mb-0">
                <li class="p-2 rounded-2 bg-light border d-flex align-items-center gap-2">
                  <i class="bi bi-code-square text-dark fs-5"></i>
                  <div><strong>Dépôt de Code (Snippet) :</strong> Zone de saisie avec coloration de syntaxe pour tester des solutions algorithmiques.</div>
                </li>
                <li class="p-2 rounded-2 bg-light border d-flex align-items-center gap-2">
                  <i class="bi bi-geo-alt-fill text-success fs-5"></i>
                  <div><strong>Ville du Cameroun (CitySelect2) :</strong> Sélecteur natif avec les 98 villes camerounaises (Douala, Yaoundé, Buea, etc.).</div>
                </li>
                <li class="p-2 rounded-2 bg-light border d-flex align-items-center gap-2">
                  <i class="bi bi-github text-dark fs-5"></i>
                  <div><strong>Lien GitHub / GitLab :</strong> Validation immédiate des URLs de dépôts ou profils.</div>
                </li>
                <li class="p-2 rounded-2 bg-light border d-flex align-items-center gap-2">
                  <i class="bi bi-paperclip text-primary fs-5"></i>
                  <div><strong>Upload CV &amp; Documents :</strong> Téléversement sécurisé de PDF, images ou fichiers techniques jusqu'à 10 Mo.</div>
                </li>
              </ul>
            </div>
          </div>

          <!-- SECTION 4: LOGIQUE CONDITIONNELLE -->
          <div class="col-md-6">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-info bg-opacity-10 text-info p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                  <i class="bi bi-diagram-3-fill fs-4"></i>
                </div>
                <div>
                  <h4 class="h6 fw-bold text-dark mb-0">4. Logique Conditionnelle Intelligente</h4>
                  <div class="extra-small text-muted">Affichez les questions au bon moment</div>
                </div>
              </div>

              <p class="text-muted small mb-3">
                Vous pouvez créer des formulaires adaptatifs sans écrire une seule ligne de code grâce aux règles conditionnelles :
              </p>

              <div class="p-3 bg-light rounded-3 border small">
                <div class="fw-bold text-dark mb-2">Exemple concret :</div>
                <div class="extra-small text-muted">
                  • <strong>Question 1 :</strong> « Êtes-vous Designer ou Développeur ? »<br>
                  • <strong>Question 2 (Conditionnée) :</strong> Si réponse = « Développeur », afficher la question « Quel est votre framework backend préféré ? »<br>
                  • Si la condition n'est pas remplie, la question est ignorée automatiquement.
                </div>
              </div>
            </div>
          </div>

          <!-- SECTION 5: NOTIFICATIONS EMAIL & SÉCURITÉ -->
          <div class="col-md-6">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                  <i class="bi bi-envelope-check-fill fs-4"></i>
                </div>
                <div>
                  <h4 class="h6 fw-bold text-dark mb-0">5. Alertes Email &amp; Sécurité</h4>
                  <div class="extra-small text-muted">Suivi temps réel &amp; Anti-triche</div>
                </div>
              </div>

              <ul class="list-unstyled small text-muted d-flex flex-column gap-2 mb-0">
                <li class="p-2 rounded-2 bg-light border">
                  <strong class="text-dark d-block">📧 Notification instantanée au créateur</strong>
                  Recevez immédiatement par email le récapitulatif complet de chaque réponse dès qu'un participant clique sur envoyer.
                </li>
                <li class="p-2 rounded-2 bg-light border">
                  <strong class="text-dark d-block">✅ Accusé de réception au participant</strong>
                  Si le formulaire demande l'email du répondant, une confirmation lui est automatiquement expédiée.
                </li>
                <li class="p-2 rounded-2 bg-light border">
                  <strong class="text-dark d-block">🛡️ Limitation à 1 réponse par compte / IP</strong>
                  Empêche les votes multiples et les participations en doublon pour vos concours et hackathons.
                </li>
              </ul>
            </div>
          </div>

          <!-- SECTION 6: ANALYTICS & EXPORT CSV -->
          <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                  <i class="bi bi-file-earmark-excel-fill fs-4"></i>
                </div>
                <div>
                  <h4 class="h6 fw-bold text-dark mb-0">6. Exportation CSV &amp; Statistiques Visuelles</h4>
                  <div class="extra-small text-muted">Exploitez vos données dans Excel, Google Sheets ou Notion</div>
                </div>
              </div>

              <p class="text-muted small mb-3">
                Depuis l'onglet <strong>Réponses</strong> de votre formulaire, visualisez en un clin d'œil les taux de conversion et les graphiques de répartition par option. Cliquez sur <strong>« Exporter en CSV »</strong> pour générer un fichier encodé en UTF-8 (BOM inclus pour éviter les problèmes d'accents sur Microsoft Excel) directement exploitable dans vos tableurs.
              </p>

              <div class="text-center pt-3 border-top mt-4">
                <Link href="/espace-utilisateur/formulaires/creer" class="btn btn-success rounded-pill px-5 py-2 fw-bold shadow-sm">
                  <i class="bi bi-plus-circle-fill me-1"></i> Créer mon formulaire maintenant
                </Link>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
</script>

<style scoped>
.extra-small {
  font-size: 0.75rem;
}
</style>
