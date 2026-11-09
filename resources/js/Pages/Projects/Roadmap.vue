<template>
  <FrontLayout>
    <Head>
      <title>Roadmap &amp; Avancement • {{ project.name }} | Tech237</title>
      <meta name="description" :content="'Consultez la roadmap publique et l\'avancement en direct du projet ' + project.name">
    </Head>

    <div class="py-5 bg-slate-50 min-vh-100" style="background-color: #f8fafc;">
      <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-muted text-decoration-none">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/projets" class="text-muted text-decoration-none">Projets</Link></li>
            <li class="breadcrumb-item"><Link :href="'/projet/' + (project.slug || project.id)" class="text-muted text-decoration-none">{{ project.name }}</Link></li>
            <li class="breadcrumb-item active text-dark fw-bold">Roadmap Publique</li>
          </ol>
        </nav>

        <!-- Project Hero Header -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #071738 0%, #0B2559 60%, #00A859 100%);">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
              <img 
                v-if="project.logo" 
                :src="'/' + project.logo" 
                class="rounded-4 bg-white p-2 shadow-sm" 
                style="width: 72px; height: 72px; object-fit: contain;" 
                :alt="project.name"
              />
              <div>
                <span class="badge bg-white text-dark rounded-pill px-3 py-1 mb-2 fw-bold text-uppercase" style="font-size: 10px;">
                  <i class="bi bi-kanban me-1 text-success"></i>Roadmap Publique
                </span>
                <h1 class="display-6 fw-bold text-white mb-1">{{ project.name }}</h1>
                <p class="text-white-50 mb-0 small" style="max-width: 580px;">
                  Suivez en direct l'avancement des fonctionnalités, les tâches en cours et les prochaines sorties de l'équipe.
                </p>
              </div>
            </div>

            <div>
              <Link :href="'/projet/' + (project.slug || project.id)" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                &larr; Fiche du Projet &amp; Devlogs
              </Link>
            </div>
          </div>
        </div>

        <!-- Roadmap Kanban View (Responsive Wrap: never exceeds screen width) -->
        <div v-if="board && board.columns && board.columns.length > 0">
          <div class="row g-4 align-items-stretch">
            
            <div 
              v-for="col in board.columns" 
              :key="col.id" 
              class="col-12 col-md-6 col-lg-4 col-xl-3"
            >
              <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                <!-- Column Header -->
                <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                  <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-block" :style="{ width: '10px', height: '10px', backgroundColor: col.color || '#3b82f6' }"></span>
                    <h3 class="h6 fw-bold text-dark mb-0">{{ col.title }}</h3>
                  </div>
                  <span class="badge bg-light text-muted rounded-pill px-2 py-1 extra-small">
                    {{ col.cards ? col.cards.length : 0 }}
                  </span>
                </div>

                <!-- Cards list -->
                <div class="d-flex flex-column gap-3">
                  <div 
                    v-for="card in col.cards" 
                    :key="card.id"
                    class="card rounded-3 border p-3 bg-light shadow-2xs"
                  >
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <span class="badge rounded-pill px-2 py-0 extra-small fw-bold" :class="getPriorityClass(card.priority)">
                        {{ getPriorityLabel(card.priority) }}
                      </span>
                      <span v-if="card.devlog_published" class="badge bg-success-subtle text-success extra-small rounded-pill">
                        <i class="bi bi-check-circle-fill me-1"></i>En Devlog
                      </span>
                    </div>

                    <h4 class="h6 fw-bold text-dark mb-2" style="font-size: 0.9rem;">
                      {{ card.title }}
                    </h4>

                    <p v-if="card.description" class="extra-small text-muted mb-2 line-clamp-2">
                      {{ card.description }}
                    </p>

                    <div v-if="card.checklist && card.checklist.length > 0" class="progress rounded-pill mb-2" style="height: 4px;">
                      <div class="progress-bar rounded-pill bg-success" :style="{ width: getChecklistPercentage(card) + '%' }"></div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between extra-small text-muted pt-2 border-top">
                      <span>{{ card.due_date ? formatDateShort(card.due_date) : 'En continu' }}</span>
                      <span v-if="card.assignee" class="fw-semibold text-dark">
                        {{ card.assignee.name }}
                      </span>
                    </div>
                  </div>

                  <div v-if="!col.cards || col.cards.length === 0" class="text-center py-4 text-muted extra-small">
                    Aucune tâche dans cette colonne.
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>

        <div v-else class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
          <div class="rounded-circle text-muted d-inline-flex p-4 fs-1 mb-3 mx-auto" style="background-color: #f1f5f9;">
            <i class="bi bi-calendar2-range"></i>
          </div>
          <h4 class="fw-bold text-dark mb-2">Aucune Roadmap publique disponible</h4>
          <p class="text-muted small mb-0">Le créateur du projet n'a pas encore publié son tableau de suivi.</p>
        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  project: { type: Object, required: true },
  board: { type: Object, default: null },
})

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
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
