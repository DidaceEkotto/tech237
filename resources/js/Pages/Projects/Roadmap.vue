<template>
  <FrontLayout>
    <Head>
      <title>Roadmap &amp; Avancement • {{ project.name }} | Tech237</title>
      <meta name="description" :content="'Consultez la roadmap publique, les fonctionnalités en cours et les prochaines sorties du projet ' + project.name">
    </Head>

    <div class="roadmap-page py-5 bg-slate-50 min-vh-100" style="background-color: #f8fafc;">
      <div class="container">
        
        <!-- Breadcrumb & Nav -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-muted text-decoration-none">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/projets" class="text-muted text-decoration-none">Projets</Link></li>
            <li class="breadcrumb-item"><Link :href="'/projet/' + (project.slug || project.id)" class="text-muted text-decoration-none">{{ project.name }}</Link></li>
            <li class="breadcrumb-item active text-dark fw-bold">Roadmap Publique</li>
          </ol>
        </nav>

        <!-- Project Hero Header with Template Badge & Progress -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #071738 0%, #0B2559 55%, #00A859 100%);">
          <div class="position-absolute end-0 top-50 translate-middle-y me-4 opacity-10 d-none d-lg-block pointer-events-none">
            <i class="bi bi-kanban" style="font-size: 260px; color: #00A859;"></i>
          </div>

          <div class="position-relative z-1">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-4">
              
              <!-- Project Brand & Title -->
              <div class="d-flex align-items-center gap-3">
                <img 
                  v-if="project.logo" 
                  :src="'/' + project.logo" 
                  class="rounded-4 bg-white p-2 shadow-sm flex-shrink-0" 
                  style="width: 72px; height: 72px; object-fit: contain;" 
                  :alt="project.name"
                />
                <div v-else class="rounded-4 bg-white text-dark d-flex align-items-center justify-content-center fw-black shadow-sm flex-shrink-0" style="width: 72px; height: 72px; font-size: 28px;">
                  {{ project.name.charAt(0).toUpperCase() }}
                </div>

                <div>
                  <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold text-uppercase" style="font-size: 10px;">
                      <i class="bi bi-kanban me-1 text-success"></i>Roadmap Publique
                    </span>
                    <span v-if="board?.template" class="badge rounded-pill px-3 py-1 fw-bold text-white border border-white border-opacity-25" :style="{ backgroundColor: getTemplateHeroBg(board.template) }">
                      <i :class="['bi', getTemplateIcon(board.template), 'me-1']"></i>{{ getTemplateTitle(board.template) }}
                    </span>
                  </div>

                  <h1 class="display-6 fw-black text-white mb-1" style="letter-spacing: -0.5px;">{{ project.name }}</h1>
                  <p class="text-white-50 mb-0 small" style="max-width: 620px; line-height: 1.55;">
                    {{ board?.description || 'Suivez en direct l\'état d\'avancement, les fonctionnalités en cours de développement et les prochaines sorties de l\'équipe.' }}
                  </p>
                </div>
              </div>

              <!-- CTA Links -->
              <div class="d-flex align-items-center gap-2 flex-wrap flex-shrink-0">
                <Link :href="'/projet/' + (project.slug || project.id)" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                  &larr; Fiche du Projet
                </Link>
                <Link v-if="project.website_url" :href="project.website_url" target="_blank" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold">
                  <i class="bi bi-box-arrow-up-right me-1"></i>Visiter le site
                </Link>
              </div>

            </div>

            <!-- Global Roadmap Progress Stats Strip -->
            <div v-if="board && totalCards > 0" class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-15 backdrop-blur">
              <div class="row g-3 align-items-center">
                <div class="col-md-7">
                  <div class="d-flex justify-content-between align-items-center text-white small mb-1">
                    <span class="fw-semibold">
                      <i class="bi bi-check2-circle text-success me-1"></i>Taux de complétion global
                    </span>
                    <span class="fw-bold">{{ progressPercentage }}% ({{ doneCards }} / {{ totalCards }} tâches)</span>
                  </div>
                  <div class="progress rounded-pill" style="height: 8px; background-color: rgba(255,255,255,0.2);">
                    <div 
                      class="progress-bar rounded-pill bg-success" 
                      role="progressbar" 
                      :style="{ width: progressPercentage + '%' }" 
                      :aria-valuenow="progressPercentage" 
                      aria-valuemin="0" 
                      aria-valuemax="100"
                    ></div>
                  </div>
                </div>

                <div class="col-md-5">
                  <div class="d-flex justify-content-md-end align-items-center gap-3 text-white small">
                    <div class="text-center">
                      <strong class="d-block h5 mb-0 fw-bold">{{ (board.columns || []).length }}</strong>
                      <span class="extra-small opacity-75">Étapes</span>
                    </div>
                    <div class="text-center border-start border-white border-opacity-25 ps-3">
                      <strong class="d-block h5 mb-0 fw-bold">{{ inProgressCards }}</strong>
                      <span class="extra-small opacity-75">En cours</span>
                    </div>
                    <div class="text-center border-start border-white border-opacity-25 ps-3">
                      <strong class="d-block h5 mb-0 fw-bold text-success">{{ doneCards }}</strong>
                      <span class="extra-small opacity-75">Livrées</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Filter Bar: Search, Priority & Template info -->
        <div v-if="board && board.columns && board.columns.length > 0" class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            
            <!-- Left: Search Box -->
            <div class="input-group" style="max-width: 320px;">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
              <input 
                type="text" 
                v-model="searchFilter" 
                class="form-control border-start-0 bg-light" 
                placeholder="Rechercher une fonctionnalité..." 
              />
              <button v-if="searchFilter" @click="searchFilter = ''" class="btn btn-light border" type="button">✕</button>
            </div>

            <!-- Center: Priority Filter Pills -->
            <div class="d-flex align-items-center gap-1 flex-wrap">
              <span class="extra-small fw-bold text-muted me-1 d-none d-lg-inline">Priorité :</span>
              <button 
                type="button" 
                class="btn btn-sm rounded-pill px-3 py-1 fw-bold extra-small transition"
                :class="priorityFilter === 'all' ? 'btn-dark text-white' : 'btn-light text-muted border'"
                @click="priorityFilter = 'all'"
              >
                Toutes
              </button>
              <button 
                type="button" 
                class="btn btn-sm rounded-pill px-3 py-1 fw-bold extra-small transition"
                :class="priorityFilter === 'urgent' ? 'btn-danger text-white' : 'btn-light text-danger border'"
                @click="priorityFilter = 'urgent'"
              >
                🔴 Urgent
              </button>
              <button 
                type="button" 
                class="btn btn-sm rounded-pill px-3 py-1 fw-bold extra-small transition"
                :class="priorityFilter === 'high' ? 'btn-warning text-dark' : 'btn-light text-warning-emphasis border'"
                @click="priorityFilter = 'high'"
              >
                🟠 Haute
              </button>
              <button 
                type="button" 
                class="btn btn-sm rounded-pill px-3 py-1 fw-bold extra-small transition"
                :class="priorityFilter === 'medium' ? 'btn-primary text-white' : 'btn-light text-primary border'"
                @click="priorityFilter = 'medium'"
              >
                🔵 Moyenne
              </button>
            </div>

            <!-- Right: Template Explanation helper -->
            <div class="d-flex align-items-center gap-2 extra-small text-muted">
              <i class="bi bi-info-circle text-primary"></i>
              <span>Cliquez sur une carte pour consulter ses détails publics</span>
            </div>

          </div>
        </div>

        <!-- Roadmap Kanban View -->
        <div v-if="board && board.columns && board.columns.length > 0">
          <div class="row g-4 align-items-start">
            
            <div 
              v-for="col in board.columns" 
              :key="col.id" 
              class="col-12 col-md-6 col-lg-4 col-xl-3"
            >
              <div 
                class="card border rounded-4 shadow-sm bg-white p-3 h-100 column-board-card transition"
                :class="{ 'border-success border-2': col.is_done_column }"
                style="min-height: 280px;"
              >
                <!-- Column Header -->
                <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                  <div class="d-flex align-items-center gap-2">
                    <span class="rounded-pill d-inline-block" :style="{ width: '12px', height: '12px', backgroundColor: col.color || '#3b82f6' }"></span>
                    <h3 class="h6 fw-bold text-dark mb-0">{{ col.title }}</h3>
                  </div>
                  <span class="badge bg-slate-100 text-muted rounded-pill px-2 py-1 extra-small" style="background-color: #f1f5f9;">
                    {{ getFilteredCards(col).length }}
                  </span>
                </div>

                <!-- Cards list -->
                <div class="d-flex flex-column gap-3">
                  <div 
                    v-for="card in getFilteredCards(col)" 
                    :key="card.id"
                    class="card rounded-3 border p-3 bg-light shadow-2xs hover-card-item cursor-pointer transition position-relative"
                    @click="openPublicCardModal(card, col)"
                  >
                    <!-- Cover image preview if set -->
                    <div v-if="getCoverImageUrl(card)" class="mb-2 rounded-2 overflow-hidden border" style="max-height: 100px;">
                      <img :src="getCoverImageUrl(card)" class="w-100 object-fit-cover" style="max-height: 100px;" :alt="card.title" />
                    </div>

                    <!-- Labels & Priority -->
                    <div class="d-flex align-items-center justify-content-between gap-1 mb-2 flex-wrap">
                      <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span 
                          v-for="(label, lIdx) in (card.labels || []).slice(0, 3)" 
                          :key="lIdx"
                          class="badge bg-white text-dark border extra-small rounded-pill px-2 py-0"
                          style="font-size: 10px;"
                        >
                          {{ label }}
                        </span>
                      </div>

                      <span class="badge rounded-pill px-2 py-0 extra-small fw-bold" :class="getPriorityClass(card.priority)">
                        {{ getPriorityLabel(card.priority) }}
                      </span>
                    </div>

                    <!-- Title -->
                    <h4 class="h6 fw-bold text-dark mb-1" style="font-size: 0.92rem; line-height: 1.4;">
                      {{ card.title }}
                    </h4>

                    <!-- Snippet -->
                    <p v-if="card.description" class="extra-small text-muted mb-2 line-clamp-2" style="font-size: 11.5px; line-height: 1.45;">
                      {{ card.description }}
                    </p>

                    <!-- Checklist Bar -->
                    <div v-if="card.checklist && card.checklist.length > 0" class="mb-2">
                      <div class="d-flex justify-content-between align-items-center extra-small text-muted mb-1" style="font-size: 10.5px;">
                        <span><i class="bi bi-check2-square me-1"></i>Jalons</span>
                        <span>{{ getChecklistCount(card) }}</span>
                      </div>
                      <div class="progress rounded-pill" style="height: 4px;">
                        <div class="progress-bar rounded-pill bg-success" :style="{ width: getChecklistPercentage(card) + '%' }"></div>
                      </div>
                    </div>

                    <!-- Footer: Meta, Time & Assignee -->
                    <div class="d-flex align-items-center justify-content-between extra-small text-muted pt-2 border-top mt-1" style="font-size: 11px;">
                      <div class="d-flex align-items-center gap-2">
                        <!-- Due Date -->
                        <span v-if="card.due_date" class="d-flex align-items-center gap-1">
                          <i class="bi bi-calendar3"></i>
                          <span>{{ formatDateShort(card.due_date) }}</span>
                        </span>

                        <!-- Time Estimation -->
                        <span v-if="card.estimated_hours || card.spent_hours" class="badge bg-white text-dark border extra-small px-1 py-0" :title="'Temps passé : ' + (card.spent_hours || 0) + 'h / Estimé : ' + (card.estimated_hours || 0) + 'h'">
                          ⏱️ {{ card.spent_hours || 0 }}h<span v-if="card.estimated_hours">/{{ card.estimated_hours }}h</span>
                        </span>

                        <!-- Devlog badge -->
                        <span v-if="card.devlog_published" class="badge bg-success-subtle text-success extra-small rounded-pill" title="Cette fonctionnalité a fait l'objet d'un article Devlog">
                          <i class="bi bi-rocket-takeoff-fill"></i> Devlog
                        </span>
                      </div>

                      <!-- Assignee Avatar -->
                      <div v-if="card.assignee" class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-2xs" style="width: 22px; height: 22px; font-size: 8.5px; background: #0B2559;" :title="card.assignee.name">
                        <img v-if="card.assignee.avatar" :src="'/' + card.assignee.avatar" class="w-100 h-100 rounded-circle object-fit-cover" :alt="card.assignee.name" />
                        <span v-else>{{ card.assignee.name.charAt(0).toUpperCase() }}</span>
                      </div>
                    </div>

                  </div>

                  <div v-if="!getFilteredCards(col).length" class="text-center py-4 text-muted extra-small">
                    Aucune tâche dans cette colonne.
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
          <div class="rounded-circle text-muted d-inline-flex p-4 fs-1 mb-3 mx-auto" style="background-color: #f1f5f9;">
            <i class="bi bi-calendar2-range"></i>
          </div>
          <h4 class="fw-bold text-dark mb-2">Aucune Roadmap publique disponible</h4>
          <p class="text-muted small mb-4" style="max-width: 460px; margin: 0 auto 1.5rem auto;">
            Le créateur du projet n'a pas encore configuré ou publié le tableau de suivi de ce projet.
          </p>
          <div>
            <Link :href="'/projet/' + (project.slug || project.id)" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold">
              &larr; Retour à la fiche du projet
            </Link>
          </div>
        </div>

      </div>
    </div>

    <!-- Public Task Detail Modal (Non-Critical, Safe Information View) -->
    <div v-if="selectedCard" class="modal-backdrop fade show" style="background: rgba(15, 23, 42, 0.65); z-index: 1050;" @click="closePublicCardModal"></div>
    <div v-if="selectedCard" class="modal fade show d-block" style="z-index: 1055;" tabindex="-1" @keydown.esc="closePublicCardModal">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
          
          <!-- Modal Header -->
          <div class="modal-header border-bottom py-3 px-4 bg-light d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 min-w-0">
              <span class="rounded-circle d-inline-block" :style="{ width: '12px', height: '12px', backgroundColor: selectedColumn?.color || '#3b82f6' }"></span>
              <span class="badge rounded-pill px-2 py-1 extra-small text-white" :style="{ backgroundColor: selectedColumn?.color || '#3b82f6' }">
                {{ selectedColumn?.title }}
              </span>
              <span class="badge rounded-pill px-2 py-1 extra-small fw-bold" :class="getPriorityClass(selectedCard.priority)">
                {{ getPriorityLabel(selectedCard.priority) }}
              </span>
            </div>
            <button type="button" class="btn-close" @click="closePublicCardModal"></button>
          </div>

          <!-- Cover Image Banner if present -->
          <div v-if="getCoverImageUrl(selectedCard)" class="w-100 position-relative overflow-hidden border-bottom" style="max-height: 200px;">
            <img :src="getCoverImageUrl(selectedCard)" class="w-100 object-fit-cover" style="max-height: 200px;" :alt="selectedCard.title" />
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4">
            
            <!-- Card Title -->
            <h3 class="h4 fw-bold text-dark mb-3">
              {{ selectedCard.title }}
            </h3>

            <!-- Labels -->
            <div v-if="selectedCard.labels && selectedCard.labels.length" class="d-flex align-items-center gap-1 flex-wrap mb-3">
              <span 
                v-for="(label, lIdx) in selectedCard.labels" 
                :key="lIdx"
                class="badge bg-light text-dark border rounded-pill px-3 py-1 small"
              >
                {{ label }}
              </span>
            </div>

            <!-- Description -->
            <div class="mb-4">
              <h6 class="small fw-bold text-uppercase text-muted mb-2">
                <i class="bi bi-text-paragraph me-1"></i>Description &amp; Objectif
              </h6>
              <div class="p-3 bg-light rounded-3 text-dark small" style="line-height: 1.6; white-space: pre-wrap;">
                {{ selectedCard.description || 'Aucune description détaillée fournie pour cette tâche.' }}
              </div>
            </div>

            <!-- Checklist / Milestones (Public View) -->
            <div v-if="selectedCard.checklist && selectedCard.checklist.length" class="mb-4">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="small fw-bold text-uppercase text-muted mb-0">
                  <i class="bi bi-check2-square me-1"></i>Jalons &amp; Sous-objectifs ({{ getChecklistCount(selectedCard) }})
                </h6>
                <span class="badge bg-success-subtle text-success extra-small fw-bold">
                  {{ getChecklistPercentage(selectedCard) }}% complété
                </span>
              </div>

              <div class="progress rounded-pill mb-3" style="height: 6px;">
                <div class="progress-bar rounded-pill bg-success" :style="{ width: getChecklistPercentage(selectedCard) + '%' }"></div>
              </div>

              <div class="list-group list-group-flush rounded-3 border overflow-hidden">
                <div 
                  v-for="(item, iIdx) in selectedCard.checklist" 
                  :key="iIdx"
                  class="list-group-item d-flex align-items-center gap-2 py-2 px-3 small"
                  :class="{ 'bg-success-subtle bg-opacity-25 text-muted': item.done }"
                >
                  <i :class="['bi', item.done ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted']"></i>
                  <span :class="{ 'text-decoration-line-through': item.done }">{{ item.text }}</span>
                </div>
              </div>
            </div>

            <!-- Meta details: Due date & Estimation -->
            <div class="row g-3 mb-3">
              <div v-if="selectedCard.due_date" class="col-sm-6">
                <div class="p-3 bg-light rounded-3 border">
                  <span class="extra-small text-muted d-block mb-1">Date d'échéance visée</span>
                  <strong class="small text-dark d-flex align-items-center gap-1">
                    <i class="bi bi-calendar-event text-primary"></i>
                    <span>{{ formatDateLong(selectedCard.due_date) }}</span>
                  </strong>
                </div>
              </div>

              <div v-if="selectedCard.estimated_hours || selectedCard.spent_hours" class="col-sm-6">
                <div class="p-3 bg-light rounded-3 border">
                  <span class="extra-small text-muted d-block mb-1">Temps estimé / passé</span>
                  <strong class="small text-dark d-flex align-items-center gap-1">
                    <i class="bi bi-stopwatch text-primary"></i>
                    <span>{{ selectedCard.spent_hours || 0 }}h consommées / {{ selectedCard.estimated_hours || 0 }}h prévues</span>
                  </strong>
                </div>
              </div>

              <div v-if="selectedCard.assignee" class="col-sm-6">
                <div class="p-3 bg-light rounded-3 border">
                  <span class="extra-small text-muted d-block mb-1">Responsable</span>
                  <strong class="small text-dark d-flex align-items-center gap-2">
                    <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 9px; background: #0B2559;">
                      <img v-if="selectedCard.assignee.avatar" :src="'/' + selectedCard.assignee.avatar" class="w-100 h-100 rounded-circle object-fit-cover" :alt="selectedCard.assignee.name" />
                      <span v-else>{{ selectedCard.assignee.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <span>{{ selectedCard.assignee.name }}</span>
                  </strong>
                </div>
              </div>
            </div>

            <!-- Devlog Callout if published -->
            <div v-if="selectedCard.devlog_published" class="alert alert-success d-flex align-items-center justify-content-between p-3 rounded-3 mb-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-rocket-takeoff-fill fs-5 text-success"></i>
                <div>
                  <strong class="small d-block text-success">Publié dans le Devlog public</strong>
                  <span class="extra-small text-muted">Cette avancée majeure a été partagée publiquement avec la communauté.</span>
                </div>
              </div>
              <Link :href="'/projet/' + (project.slug || project.id)" class="btn btn-sm btn-success rounded-pill px-3 fw-bold extra-small text-white">
                Voir les Devlogs &rarr;
              </Link>
            </div>

          </div>

          <!-- Modal Footer -->
          <div class="modal-footer border-top bg-light py-2 px-4 d-flex justify-content-between">
            <span class="extra-small text-muted">
              Informations publiques fournies par {{ project.name }} sur Tech237
            </span>
            <button type="button" class="btn btn-sm btn-dark rounded-pill px-4 fw-bold" @click="closePublicCardModal">
              Fermer
            </button>
          </div>

        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  project: { type: Object, required: true },
  board: { type: Object, default: null },
})

const searchFilter = ref('')
const priorityFilter = ref('all')
const selectedCard = ref(null)
const selectedColumn = ref(null)

function openPublicCardModal(card, col) {
  selectedCard.value = card
  selectedColumn.value = col
}

function closePublicCardModal() {
  selectedCard.value = null
  selectedColumn.value = null
}

const totalCards = computed(() => {
  if (!props.board?.columns) return 0
  return props.board.columns.reduce((sum, col) => sum + (col.cards ? col.cards.length : 0), 0)
})

const doneCards = computed(() => {
  if (!props.board?.columns) return 0
  const doneCol = props.board.columns.find(c => c.is_done_column)
  if (!doneCol) return 0
  return doneCol.cards ? doneCol.cards.length : 0
})

const inProgressCards = computed(() => {
  return Math.max(0, totalCards.value - doneCards.value)
})

const progressPercentage = computed(() => {
  if (totalCards.value === 0) return 0
  return Math.round((doneCards.value / totalCards.value) * 100)
})

function getFilteredCards(col) {
  let list = col.cards || []
  if (searchFilter.value.trim()) {
    const q = searchFilter.value.toLowerCase()
    list = list.filter(c => 
      c.title.toLowerCase().includes(q) || 
      (c.description && c.description.toLowerCase().includes(q)) ||
      (Array.isArray(c.labels) && c.labels.some(l => l.toLowerCase().includes(q)))
    )
  }
  if (priorityFilter.value !== 'all') {
    list = list.filter(c => c.priority === priorityFilter.value)
  }
  return list
}

function getCoverImageUrl(card) {
  if (!card) return null
  if (card.cover_image) {
    return card.cover_image.startsWith('http') || card.cover_image.startsWith('/') 
      ? card.cover_image 
      : '/' + card.cover_image
  }
  if (card.attachments && card.attachments.length > 0) {
    const first = card.attachments[0]
    return first.url.startsWith('http') || first.url.startsWith('/') ? first.url : '/' + first.url
  }
  return null
}

function getPriorityLabel(p) {
  const map = { urgent: 'Urgent', high: 'Haute', medium: 'Moyenne', low: 'Faible' }
  return map[p] || 'Moyenne'
}

function getPriorityClass(p) {
  const map = {
    urgent: 'bg-danger text-white',
    high: 'bg-warning text-dark',
    medium: 'bg-primary-subtle text-primary',
    low: 'bg-light text-secondary border',
  }
  return map[p] || 'bg-light text-secondary'
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

function getTemplateHeroBg(template) {
  const map = {
    standard: '#2563eb',
    scrum: '#7c3aed',
    bug_tracker: '#dc2626',
    startup_launch: '#db2777',
    freelance: '#d97706',
  }
  return map[template] || '#2563eb'
}

function getChecklistCount(card) {
  const items = card.checklist || []
  const done = items.filter(i => i.done).length
  return `${done}/${items.length}`
}

function getChecklistPercentage(card) {
  const items = card.checklist || []
  if (items.length === 0) return 0
  const done = items.filter(i => i.done).length
  return Math.round((done / items.length) * 100)
}

function formatDateShort(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}

function formatDateLong(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>

<style scoped>
.roadmap-page {
  font-family: inherit;
}
.backdrop-blur {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.column-board-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card-item {
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}
.hover-card-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
  border-color: #cbd5e1 !important;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.cursor-pointer {
  cursor: pointer;
}
.shadow-2xs {
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
</style>
