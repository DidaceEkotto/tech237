<template>
  <FrontLayout>
    <Head title="Guide d'utilisation Kanban & Gestion de Projets • Tech237" />

    <div class="kanban-guide-page py-5" style="background-color: #f8fafc; min-height: 85vh;">
      <div class="container" style="max-width: 980px;">
        
        <!-- Breadcrumbs & Back -->
        <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
          <Link href="/espace-utilisateur/kanban" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-2 fw-semibold text-dark shadow-xs d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour aux Tableaux Kanban</span>
          </Link>

          <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold">
            <i class="bi bi-lightbulb-fill me-1"></i> Guide Méthodologique & Pratique
          </span>
        </div>

        <!-- Hero Header -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-5 bg-white overflow-hidden position-relative">
          <div class="position-absolute end-0 top-0 p-4 opacity-10 d-none d-md-block pointer-events-none">
            <i class="bi bi-kanban" style="font-size: 160px; color: var(--tech-green);"></i>
          </div>

          <div style="max-width: 640px; position: relative; z-index: 1;">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 text-white fw-bold extra-small" style="background: linear-gradient(135deg, #0B2559 0%, #1e3a8a 100%);">
              <i class="bi bi-stars"></i>
              <span>Gestion Agile Style Trello</span>
            </div>
            <h1 class="h2 fw-bold text-dark mb-3">
              Comment fonctionne le module <span style="color: var(--tech-green);">Kanban</span> sur Tech237 ?
            </h1>
            <p class="text-muted leading-relaxed mb-4">
              Le tableau Kanban vous permet d'organiser visuellement le cycle de développement de vos innovations, de coordonner votre équipe et de tenir informée votre communauté en temps réel.
            </p>
            <div class="d-flex gap-3 flex-wrap">
              <Link href="/espace-utilisateur/kanban" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-kanban me-1"></i> Ouvrir mes Tableaux
              </Link>
              <a href="#etapes-cles" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                Découvrir les fonctionnalités &darr;
              </a>
            </div>
          </div>
        </div>

        <!-- Section: 6 Feature Highlights Cards -->
        <div id="etapes-cles" class="row g-4 mb-5">
          
          <!-- 1. Colonnes & Flux Visuel -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="rounded-circle text-primary bg-primary-subtle d-inline-flex align-items-center justify-content-center p-3 fs-3 mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-view-column-fill"></i>
              </div>
              <h4 class="h6 fw-bold text-dark mb-2">1. Colonnes &amp; Flux Visuel</h4>
              <p class="small text-muted mb-0">
                Créez des étapes sur-mesure (ex: <em>Idées, À faire, En cours, Revue, Terminé</em>). Faites glisser vos cartes d'une colonne à l'autre par simple <strong>Drag &amp; Drop</strong>.
              </p>
            </div>
          </div>

          <!-- 2. Cartes & Checklists -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="rounded-circle text-warning bg-warning-subtle d-inline-flex align-items-center justify-content-center p-3 fs-3 mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-check2-square"></i>
              </div>
              <h4 class="h6 fw-bold text-dark mb-2">2. Checklists &amp; Spécifications</h4>
              <p class="small text-muted mb-0">
                Découpez chaque tâche en sous-objectifs cochables. Une barre de progression calcule en temps réel l'avancement de votre sprint technique.
              </p>
            </div>
          </div>

          <!-- 3. Alertes Automatiques de Retard -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white border-start border-4 border-danger">
              <div class="rounded-circle text-danger bg-danger-subtle d-inline-flex align-items-center justify-content-center p-3 fs-3 mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-envelope-exclamation-fill"></i>
              </div>
              <h4 class="h6 fw-bold text-dark mb-2">3. Alertes Email d'Échéance</h4>
              <p class="small text-muted mb-0">
                Définissez une date limite sur vos tâches. <strong>Si l'objectif n'est pas atteint à la date fixée</strong>, un email d'alerte automatique est immédiatement envoyé à tous les contributeurs du projet.
              </p>
            </div>
          </div>

          <!-- 4. Collaboration d'Équipe -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="rounded-circle text-info bg-info-subtle d-inline-flex align-items-center justify-content-center p-3 fs-3 mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-people-fill"></i>
              </div>
              <h4 class="h6 fw-bold text-dark mb-2">4. Assignation &amp; Collaboration</h4>
              <p class="small text-muted mb-0">
                Liez le tableau à votre projet tech. Tous vos collaborateurs actifs peuvent consulter les tâches et s'en voir attribuer avec leur avatar visible sur la carte.
              </p>
            </div>
          </div>

          <!-- 5. Publication Directe en Devlog -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="rounded-circle text-success bg-success-subtle d-inline-flex align-items-center justify-content-center p-3 fs-3 mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-rocket-takeoff-fill"></i>
              </div>
              <h4 class="h6 fw-bold text-dark mb-2">5. Publication en Devlog (1 clic)</h4>
              <p class="small text-muted mb-0">
                Dès qu'une fonctionnalité majeure est terminée, cliquez sur <em>« Publier dans le Devlog »</em> pour informer vos followers et générer des emails de notification automatiques.
              </p>
            </div>
          </div>

          <!-- 6. Synchronisation Roadmap Publique -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white">
              <div class="rounded-circle text-purple bg-opacity-10 d-inline-flex align-items-center justify-content-center p-3 fs-3 mb-3" style="width: 56px; height: 56px; background-color: #f3e8ff; color: #9333ea;">
                <i class="bi bi-map-fill"></i>
              </div>
              <h4 class="h6 fw-bold text-dark mb-2">6. Roadmap Publique &amp; Transparence</h4>
              <p class="small text-muted mb-0">
                Activez l'option <em>« Rendre la Roadmap publique »</em>. Vos utilisateurs et futurs investisseurs pourront visualiser en lecture seule l'état de vos sorties sans pouvoir altérer vos données.
              </p>
            </div>
          </div>

          <!-- 7. Pièces Jointes & Dropzone Images -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white border-start border-4 border-primary">
              <div class="rounded-circle text-primary bg-primary-subtle d-inline-flex align-items-center justify-content-center p-3 fs-3 mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-images"></i>
              </div>
              <h4 class="h6 fw-bold text-dark mb-2">7. Dropzone Pièces Jointes (Images)</h4>
              <p class="small text-muted mb-0">
                Téléversez en drag & drop vos maquettes UI, schémas d'architecture et captures d'écran. La première image sert automatiquement d'illustration de couverture sur votre carte.
              </p>
            </div>
          </div>

          <!-- 8. Échanges en Équipe & Mentions @ -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white border-start border-4 border-success">
              <div class="rounded-circle text-success bg-success-subtle d-inline-flex align-items-center justify-content-center p-3 fs-3 mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-chat-quote-fill"></i>
              </div>
              <h4 class="h6 fw-bold text-dark mb-2">8. Commentaires d'Équipe &amp; Mentions @</h4>
              <p class="small text-muted mb-0">
                Discutez des spécifications directement au cœur de chaque tâche. Taguez vos collègues avec <code>@Prénom</code> pour attirer leur attention et gardez une trace complète de l'historique d'activité.
              </p>
            </div>
          </div>

          <!-- 9. Gestion des Contributeurs -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 rounded-4 shadow-sm p-4 bg-white border-start border-4 border-warning">
              <div class="rounded-circle text-warning bg-warning-subtle d-inline-flex align-items-center justify-content-center p-3 fs-3 mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-person-plus-fill"></i>
              </div>
              <h4 class="h6 fw-bold text-dark mb-2">9. Invitation &amp; Retrait de Membres</h4>
              <p class="small text-muted mb-0">
                Associez des contributeurs dès la création de votre tableau ou via le bouton <em>« Équipe »</em>. Vous pouvez ajouter tout membre inscrit sur Tech237 ou le retirer à tout moment en 1 clic.
              </p>
            </div>
          </div>

        </div>

        <!-- FAQ / Best Practices -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white mb-5">
          <h3 class="h4 fw-bold text-dark mb-4 d-flex align-items-center">
            <i class="bi bi-question-circle text-primary me-2"></i>
            Bonnes pratiques recommandées
          </h3>

          <div class="accordion accordion-flush" id="kanbanFaqAccordion">
            <div class="accordion-item border-0 mb-3">
              <h2 class="accordion-header">
                <button class="accordion-button bg-light rounded-3 fw-bold text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                  Combien de tâches dois-je mettre par colonne ?
                </button>
              </h2>
              <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#kanbanFaqAccordion">
                <div class="accordion-body text-muted small">
                  En méthodologie agile, il est recommandé de limiter les tâches « En cours » (WIP - Work In Progress) à 2 ou 3 par personne pour éviter l'éparpillement et maximiser la vélocité.
                </div>
              </div>
            </div>

            <div class="accordion-item border-0 mb-3">
              <h2 class="accordion-header">
                <button class="accordion-button bg-light rounded-3 fw-bold text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                  Qui reçoit l'alerte email lorsqu'une date limite est dépassée ?
                </button>
              </h2>
              <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#kanbanFaqAccordion">
                <div class="accordion-body text-muted small">
                  L'email d'alerte automatique est transmis au membre assigné à la tâche, au créateur du projet et à tous les collaborateurs officiels du projet.
                </div>
              </div>
            </div>

            <div class="accordion-item border-0">
              <h2 class="accordion-header">
                <button class="accordion-button bg-light rounded-3 fw-bold text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                  Puis-je utiliser le Kanban pour mes tâches personnelles hors projet ?
                </button>
              </h2>
              <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#kanbanFaqAccordion">
                <div class="accordion-body text-muted small">
                  Oui ! Lors de la création d'un tableau, choisissez simplement <em>« Tableau personnel »</em> dans la liste déroulante pour organiser vos objectifs privés.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom CTA -->
        <div class="card border-0 rounded-4 shadow-sm p-5 text-center text-white" style="background: linear-gradient(135deg, #0B2559 0%, #00A859 100%);">
          <h3 class="h3 fw-bold mb-2">Prêt à propulser vos projets ?</h3>
          <p class="text-white-50 small mb-4" style="max-width: 500px; margin: 0 auto 1.5rem auto;">
            Créez votre premier tableau Kanban et commencez à coordonner vos fonctionnalités dès aujourd'hui.
          </p>
          <div>
            <Link href="/espace-utilisateur/kanban" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
              <i class="bi bi-plus-circle me-1 text-success"></i> Accéder au Gestionnaire Kanban
            </Link>
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
.shadow-xs {
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
</style>
