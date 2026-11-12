<template>
  <FrontLayout>
    <Head>
      <title>Gestion de Projet &amp; Tableaux Kanban | Tech237</title>
      <meta name="description" content="Gérez vos projets tech, organisez vos sprints en tableau Kanban et publiez vos accomplissements dans vos Devlogs.">
    </Head>

    <div class="py-5 bg-slate-50 min-vh-100" style="background-color: #f8fafc;">
      <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-muted text-decoration-none">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/espace-utilisateur" class="text-muted text-decoration-none">Espace Utilisateur</Link></li>
            <li class="breadcrumb-item active text-dark fw-bold">Gestion de Projets (Kanban)</li>
          </ol>
        </nav>

        <!-- Header -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #071738 0%, #0B2559 50%, #00A859 100%);">
          <div class="position-absolute end-0 top-50 translate-middle-y me-4 opacity-10 d-none d-lg-block pointer-events-none">
            <i class="bi bi-kanban-fill" style="font-size: 260px;"></i>
          </div>

          <div class="position-relative z-1 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
            <div>
              <span class="badge px-3 py-1 rounded-pill mb-2 fw-bold text-uppercase" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
                <i class="bi bi-trello me-1"></i> Méthode Agile &amp; Kanban
              </span>
              <h1 class="display-6 fw-bold text-white mb-2">Vos Tableaux Kanban</h1>
              <p class="lead text-white-50 mb-0" style="max-width: 620px; font-size: 1.05rem;">
                Organisez vos développements en colonnes, collaborez avec votre équipe et transformez vos tâches terminées en Devlogs publics en un clic.
              </p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
              <Link 
                href="/espace-utilisateur/kanban/guide" 
                class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2"
              >
                <i class="bi bi-question-circle"></i>
                <span>Guide d'utilisation</span>
              </Link>
              <button 
                @click="openCreateModal" 
                class="btn btn-light rounded-pill px-4 py-2 fw-bold text-dark shadow-sm d-flex align-items-center gap-2"
              >
                <i class="bi bi-plus-circle-fill text-success fs-5"></i>
                <span>Nouveau Tableau</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Boards Grid -->
        <div v-if="boards && boards.length > 0" class="row g-4">
          <div v-for="board in boards" :key="board.id" class="col-md-6 col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white hover-shadow transition overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
              <div class="card-body p-4 d-flex flex-column">
                
                <!-- Project & Template badges -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                  <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span v-if="board.project" class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 small fw-bold d-inline-flex align-items-center gap-1">
                      <i class="bi bi-folder-fill"></i>
                      <span class="text-truncate" style="max-width: 140px;">{{ board.project.name }}</span>
                    </span>
                    <span v-else class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 small fw-bold">
                      <i class="bi bi-person me-1"></i>Personnel
                    </span>

                    <!-- Preset Template Badge -->
                    <span class="badge rounded-pill px-2 py-1 extra-small fw-bold border" :class="getTemplateBadgeClass(board.template)">
                      <i :class="['bi', getTemplateIcon(board.template), 'me-1']"></i>{{ getTemplateTitle(board.template) }}
                    </span>
                  </div>

                  <span v-if="board.is_public" class="badge bg-success-subtle text-success rounded-pill px-2 py-1 extra-small fw-bold">
                    <i class="bi bi-globe me-1"></i>Roadmap Publique
                  </span>
                </div>

                <!-- Board Title -->
                <h4 class="h5 fw-bold text-dark mb-2">
                  <Link :href="'/espace-utilisateur/kanban/' + board.id" class="text-dark text-decoration-none hover-primary">
                    {{ board.title }}
                  </Link>
                </h4>

                <p v-if="board.description" class="text-muted small mb-4 flex-grow-1" style="line-height: 1.55;">
                  {{ board.description }}
                </p>
                <div v-else class="flex-grow-1"></div>

                <!-- Progress Bar -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center small mb-1">
                    <span class="text-muted fw-semibold">Progression</span>
                    <span class="fw-bold text-dark">{{ board.progress }}%</span>
                  </div>
                  <div class="progress rounded-pill" style="height: 8px;">
                    <div 
                      class="progress-bar rounded-pill" 
                      role="progressbar" 
                      :style="{ width: board.progress + '%', backgroundColor: '#00A859' }"
                      :aria-valuenow="board.progress" 
                      aria-valuemin="0" 
                      aria-valuemax="100"
                    ></div>
                  </div>
                </div>

                <!-- Card Meta & CTA -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                  <div class="small text-muted d-flex align-items-center gap-3">
                    <span><i class="bi bi-card-checklist me-1"></i>{{ board.total_cards }} tâche(s)</span>
                    <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>{{ board.done_cards }} faite(s)</span>
                  </div>

                  <Link :href="'/espace-utilisateur/kanban/' + board.id" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold">
                    Ouvrir &rarr;
                  </Link>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white" style="border: 1px dashed #cbd5e1 !important;">
          <div class="rounded-circle text-primary d-inline-flex p-4 fs-1 mb-3 mx-auto" style="background-color: #eff6ff;">
            <i class="bi bi-kanban"></i>
          </div>
          <h3 class="h4 fw-bold text-dark mb-2">Aucun tableau Kanban pour le moment</h3>
          <p class="text-muted small mb-4" style="max-width: 480px; margin: 0 auto 1.5rem auto;">
            Créez votre premier tableau pour organiser le développement de votre projet tech ou suivre vos tâches quotidiennes avec la méthode Trello.
          </p>
          <div class="d-flex justify-content-center gap-2 flex-wrap">
            <Link href="/espace-utilisateur/kanban/guide" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
              <i class="bi bi-book me-1"></i> Comprendre le fonctionnement
            </Link>
            <button @click="openCreateModal" class="btn btn-success rounded-pill px-4 py-2 fw-bold text-white shadow-sm">
              <i class="bi bi-plus-circle me-1"></i> Créer mon premier tableau
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Create Board Modal -->
    <div v-if="showModal" class="modal-backdrop fade show" style="background: rgba(15, 23, 42, 0.65);"></div>
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
          <form @submit.prevent="submitCreateBoard">
            <div class="modal-header border-bottom py-3 px-4 bg-light d-flex align-items-center justify-content-between">
              <h5 class="modal-title fw-bold text-dark mb-0">
                <i class="bi bi-kanban-fill text-primary me-2"></i>Nouveau Tableau Kanban
              </h5>
              <div class="d-flex align-items-center gap-2">
                <button 
                  type="submit" 
                  class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold text-white shadow-sm text-nowrap" 
                  :disabled="isSubmitting"
                >
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                  Créer
                </button>
                <button type="button" class="btn-close" @click="showModal = false"></button>
              </div>
            </div>
            <div class="modal-body p-4">
              <!-- Title -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Titre du tableau <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  v-model="form.title" 
                  class="form-control rounded-3" 
                  placeholder="ex: Sprint v1.0 • Passerelle Mobile Money"
                  required
                  maxlength="120"
                />
              </div>

              <!-- Linked Project -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Projet associé (optionnel)</label>
                <select v-model="form.project_id" class="form-select rounded-3">
                  <option :value="null">-- Tableau personnel (non lié à un projet) --</option>
                  <option v-for="proj in myProjects" :key="proj.id" :value="proj.id">
                    {{ proj.name }}
                  </option>
                </select>
                <div class="extra-small text-muted mt-1">
                  Lier le tableau à un projet permet à vos collaborateurs d'y accéder et de publier des Devlogs directs.
                </div>
              </div>

              <!-- Template Selection -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between mb-1">
                  <span><i class="bi bi-grid-1x2-fill text-primary me-1"></i>Modèle de tableau préréglé</span>
                  <span class="badge bg-light text-muted border extra-small">{{ templates.find(t => t.id === form.template)?.title || 'Standard' }}</span>
                </label>
                <div class="row g-2">
                  <div v-for="tmpl in templates" :key="tmpl.id" class="col-sm-6">
                    <div 
                      class="p-2 rounded-3 border cursor-pointer h-100 transition template-card"
                      :class="form.template === tmpl.id ? 'border-primary bg-primary-subtle shadow-xs' : 'bg-light hover-bg-white'"
                      @click="form.template = tmpl.id"
                      style="cursor: pointer;"
                    >
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <i :class="['bi', tmpl.icon]" :style="{ color: tmpl.color }"></i>
                        <span class="extra-small fw-bold text-dark text-truncate">{{ tmpl.title }}</span>
                      </div>
                      <p class="extra-small text-muted mb-1 line-clamp-1" style="font-size: 11px;">{{ tmpl.description }}</p>
                      <div class="d-flex flex-wrap gap-1">
                        <span v-for="(c, ci) in tmpl.cols" :key="ci" class="badge bg-white text-dark border extra-small px-1 py-0" style="font-size: 9.5px;">
                          {{ c }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Description -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Description (optionnelle)</label>
                <textarea 
                  v-model="form.description" 
                  class="form-control rounded-3" 
                  rows="3" 
                  placeholder="Objectifs du sprint, fonctionnalités prioritaires..."
                  maxlength="500"
                ></textarea>
              </div>

              <!-- Public Roadmap checkbox -->
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="publicRoadmapCheck" v-model="form.is_public">
                <label class="form-check-label small fw-semibold text-dark" for="publicRoadmapCheck">
                  Rendre la Roadmap publique
                </label>
                <div class="extra-small text-muted">
                  Si activé, les visiteurs pourront consulter l'avancement des fonctionnalités sur la page du projet.
                </div>
              </div>

              <!-- Invite Contributors right at creation -->
              <div v-if="registeredUsers && registeredUsers.length" class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">
                  <i class="bi bi-people-fill text-primary me-1"></i>Inviter des contributeurs dès la création
                </label>
                <div class="d-flex flex-wrap gap-1 p-2 bg-light rounded-3 border" style="max-height: 120px; overflow-y: auto;">
                  <button 
                    v-for="u in registeredUsers.slice(0, 30)" 
                    :key="'reg-' + u.id" 
                    type="button"
                    class="badge rounded-pill border px-2 py-1 user-select-none d-flex align-items-center gap-1 transition"
                    :class="form.contributor_ids.includes(u.id) ? 'bg-primary text-white border-primary' : 'bg-white text-dark border-secondary-subtle'"
                    @click="toggleContributorSelection(u.id)"
                  >
                    <i class="bi" :class="form.contributor_ids.includes(u.id) ? 'bi-check-circle-fill' : 'bi-plus'"></i>
                    <span>{{ u.name }}</span>
                  </button>
                </div>
                <small class="text-muted extra-small">Cliquez pour ajouter des collaborateurs au projet dès son lancement.</small>
              </div>

            </div>
            <div class="modal-footer border-top bg-light py-2 px-4 d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-sm btn-light rounded-pill px-4 fw-semibold" @click="showModal = false">
                Annuler
              </button>
              <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold text-white shadow-sm" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                Créer le Tableau
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  boards: { type: Array, default: () => [] },
  myProjects: { type: Array, default: () => [] },
  registeredUsers: { type: Array, default: () => [] },
})

const showModal = ref(false)
const isSubmitting = ref(false)

const templates = [
  {
    id: 'standard',
    title: 'Standard (Trello)',
    icon: 'bi-kanban',
    description: 'Backlog, À faire, En cours, Terminé.',
    cols: ['Backlog & Idées', 'À faire', 'En cours', 'Terminé'],
    color: '#3b82f6'
  },
  {
    id: 'scrum',
    title: 'Scrum / Agile Sprint',
    icon: 'bi-arrow-repeat',
    description: 'Backlog, Sprint en cours, En Dév, Code Review, Déployé.',
    cols: ['Backlog', 'Sprint', 'En Dév', 'Review', 'Déployé'],
    color: '#8b5cf6'
  },
  {
    id: 'bug_tracker',
    title: 'Bug & Issue Tracker',
    icon: 'bi-bug',
    description: 'Signalés, Analyse & Repro, En Correction, Validation, Résolu.',
    cols: ['Signalés', 'Analyse', 'Correction', 'Validation', 'Résolu'],
    color: '#ef4444'
  },
  {
    id: 'startup_launch',
    title: 'Lancement Produit / MVP',
    icon: 'bi-rocket-takeoff',
    description: 'Idéation, Spécifications, MVP & Dév, Marketing, Production.',
    cols: ['Idéation', 'Design/Spéc', 'MVP', 'Marketing', 'Production'],
    color: '#ec4899'
  },
  {
    id: 'freelance',
    title: 'Mission Freelance & Client',
    icon: 'bi-briefcase',
    description: 'Cahier des charges, Réalisation, Retour client, Facturé.',
    cols: ['Cahier charges', 'Réalisation', 'Retour client', 'Livré & Facturé'],
    color: '#f59e0b'
  },
]

const form = ref({
  title: '',
  description: '',
  project_id: null,
  template: 'standard',
  is_public: false,
  contributor_ids: [],
})

function openCreateModal() {
  form.value = {
    title: '',
    description: '',
    project_id: props.myProjects.length > 0 ? props.myProjects[0].id : null,
    template: 'standard',
    is_public: false,
    contributor_ids: [],
  }
  showModal.value = true
}

function toggleContributorSelection(userId) {
  const idx = form.value.contributor_ids.indexOf(userId)
  if (idx !== -1) {
    form.value.contributor_ids.splice(idx, 1)
  } else {
    form.value.contributor_ids.push(userId)
  }
}

function submitCreateBoard() {
  if (isSubmitting.value) return
  isSubmitting.value = true

  router.post('/espace-utilisateur/kanban/boards', form.value, {
    onSuccess: () => {
      showModal.value = false
      isSubmitting.value = false
    },
    onError: () => {
      isSubmitting.value = false
    }
  })
}

function getTemplateTitle(template) {
  const map = {
    standard: 'Standard (Trello)',
    scrum: 'Scrum / Agile Sprint',
    bug_tracker: 'Bug & Issue Tracker',
    startup_launch: 'Lancement Produit / MVP',
    freelance: 'Mission Freelance & Client',
  }
  return map[template] || 'Standard (Trello)'
}

function getTemplateIcon(template) {
  const map = {
    standard: 'bi-kanban',
    scrum: 'bi-arrow-repeat',
    bug_tracker: 'bi-bug',
    startup_launch: 'bi-rocket-takeoff',
    freelance: 'bi-briefcase',
  }
  return map[template] || 'bi-kanban'
}

function getTemplateBadgeClass(template) {
  const map = {
    standard: 'bg-primary-subtle text-primary border-primary-subtle',
    scrum: 'bg-info-subtle text-primary border-info-subtle',
    bug_tracker: 'bg-danger-subtle text-danger border-danger-subtle',
    startup_launch: 'bg-warning-subtle text-dark border-warning-subtle',
    freelance: 'bg-success-subtle text-success border-success-subtle',
  }
  return map[template] || 'bg-light text-dark border-secondary-subtle'
}
</script>

<style scoped>
.hover-shadow {
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}
.hover-shadow:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.08) !important;
}
.hover-primary {
  transition: color 0.2s ease;
}
.hover-primary:hover {
  color: #00A859 !important;
}
</style>
