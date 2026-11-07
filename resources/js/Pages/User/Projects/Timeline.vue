<template>
  <FrontLayout>
    <Head :title="'Journal de Bord & Résolutions - ' + project.name" />

    <div class="container my-5">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          
          <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
              <li class="breadcrumb-item"><Link href="/espace-utilisateur">Tableau de bord</Link></li>
              <li class="breadcrumb-item"><Link href="/espace-utilisateur?section=projects">Mes Projets</Link></li>
              <li class="breadcrumb-item active">Journal GitHub-Style : {{ project.name }}</li>
            </ol>
          </nav>

          <!-- Top Project Info Card -->
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 border overflow-hidden p-1 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px;">
                  <img 
                    v-if="project.logo" 
                    :src="project.logo.startsWith('/') ? project.logo : '/' + project.logo" 
                    :alt="project.name" 
                    class="w-100 h-100 object-fit-contain rounded-2"
                  >
                  <div v-else class="fw-bold text-danger fs-5">
                    {{ (project.name || 'P').charAt(0).toUpperCase() }}
                  </div>
                </div>
                <div>
                  <h4 class="fw-bold mb-0 text-dark">{{ project.name }}</h4>
                  <div class="small text-muted">
                    Journal des étapes, défis techniques surmontés et résolutions ({{ (project.timelines || []).length }} entrées)
                  </div>
                </div>
              </div>

              <div class="d-flex gap-2">
                <Link :href="'/espace-utilisateur/projets/' + project.id + '/galerie'" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                  <i class="bi bi-images me-1"></i> Galerie photos
                </Link>
                <Link :href="'/projet/' + project.slug" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                  <i class="bi bi-box-arrow-up-right me-1"></i> Fiche publique
                </Link>
                <button @click="showAddStep = !showAddStep" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                  <i class="bi bi-plus-lg me-1"></i> {{ showAddStep ? 'Fermer le formulaire' : 'Nouvelle étape / Résolution' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Add Timeline Step Form -->
          <div v-if="showAddStep" class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-5 bg-white border-top border-4 border-danger">
            <h5 class="fw-bold text-dark mb-3">
              <i class="bi bi-patch-check-fill text-danger me-2"></i>
              Consigner une Nouvelle Étape ou Résolution de Bug
            </h5>
            
            <form @submit.prevent="submitTimeline">
              <div class="row g-3 mb-3">
                <div class="col-md-8">
                  <label class="form-label small fw-bold">Titre de l'étape / jalon <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.title" placeholder="ex: Optimisation temps de chargement & Cache Redis" required>
                  <div v-if="form.errors.title" class="text-danger small mt-1">{{ form.errors.title }}</div>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Date de l'événement</label>
                  <input type="date" class="form-control" v-model="form.event_date">
                </div>
              </div>

              <!-- LINK TO PROJECT FEATURES (User Requirement) -->
              <div v-if="project.features && project.features.length" class="mb-3 p-3 bg-light rounded-3 border">
                <label class="form-label small fw-bold text-dark mb-2">
                  <i class="bi bi-tag-fill text-danger me-1"></i>Associer à une ou plusieurs fonctionnalités du projet :
                </label>
                <div class="row g-2">
                  <div v-for="(feat, idx) in project.features" :key="idx" class="col-md-6">
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="checkbox" 
                        :id="'feat-' + idx" 
                        :value="feat" 
                        v-model="form.feature_ids"
                      >
                      <label class="form-check-label small fw-semibold text-dark" :for="'feat-' + idx">
                        {{ feat }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded-3 border">
                    <label class="form-label small fw-bold text-danger">
                      <i class="bi bi-bug-fill me-1"></i> Problème ou blocage rencontré
                    </label>
                    <textarea class="form-control bg-white" rows="3" v-model="form.problem_encountered" placeholder="Décrivez le bug, la contrainte technique ou l'impasse initiale..."></textarea>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded-3 border">
                    <label class="form-label small fw-bold text-success">
                      <i class="bi bi-lightbulb-fill me-1"></i> Solution & Résolution appliquée
                    </label>
                    <textarea class="form-control bg-white" rows="3" v-model="form.solution_applied" placeholder="Comment avez-vous résolu le problème (code, refactor, architecture)..."></textarea>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Détails complémentaires / Changelog</label>
                <textarea class="form-control" rows="3" v-model="form.description" placeholder="Notes techniques, tests effectués, retours utilisateurs..."></textarea>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Lien Vidéo Démo (YouTube)</label>
                  <input type="url" class="form-control form-control-sm" v-model="form.youtube_url" placeholder="https://youtube.com/watch?v=...">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Capture d'écran / Preuve technique (image)</label>
                  <input type="file" class="form-control form-control-sm" @change="e => form.screenshot = e.target.files[0]" accept="image/*">
                </div>
              </div>

              <div class="d-flex gap-2 justify-content-end">
                <button type="button" @click="showAddStep = false" class="btn btn-light border rounded-pill px-4">Annuler</button>
                <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" :disabled="form.processing">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  Publier l'étape
                </button>
              </div>
            </form>
          </div>

          <!-- Timeline Steps List (GitHub Commits / Devlog Style) -->
          <div v-if="project.timelines && project.timelines.length" class="position-relative ps-4 border-start border-3 border-danger ms-3">
            <div v-for="(step, idx) in project.timelines" :key="step.id" class="position-relative mb-5">
              <!-- Pin bullet point -->
              <div 
                class="position-absolute rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow" 
                style="width: 26px; height: 26px; left: -38px; top: 0;"
              >
                <i class="bi bi-check-lg" style="font-size: 13px;"></i>
              </div>

              <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                  <h5 class="fw-bold mb-0 text-dark">{{ step.title }}</h5>
                  <span class="badge bg-light text-muted border font-monospace">
                    {{ new Date(step.event_date || step.created_at).toLocaleDateString('fr-FR') }}
                  </span>
                </div>

                <p v-if="step.description" class="small text-muted mb-3">{{ step.description }}</p>

                <!-- Associated Features Tagged -->
                <div v-if="step.feature_ids && step.feature_ids.length" class="mb-3 d-flex flex-wrap gap-1">
                  <span v-for="(f, fi) in step.feature_ids" :key="fi" class="badge bg-secondary-subtle text-dark border small">
                    <i class="bi bi-check2 me-1 text-success"></i>{{ f }}
                  </span>
                </div>

                <!-- Problem vs Solution cards -->
                <div v-if="step.problem_encountered || step.solution_applied" class="row g-3 mb-3">
                  <div v-if="step.problem_encountered" class="col-md-6">
                    <div class="p-3 bg-danger-subtle rounded-3 h-100">
                      <span class="fw-bold text-danger small d-block mb-1">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Problème :
                      </span>
                      <p class="small text-danger-emphasis mb-0">{{ step.problem_encountered }}</p>
                    </div>
                  </div>
                  <div v-if="step.solution_applied" class="col-md-6">
                    <div class="p-3 bg-success-subtle rounded-3 h-100">
                      <span class="fw-bold text-success small d-block mb-1">
                        <i class="bi bi-check-circle-fill me-1"></i> Solution adoptée :
                      </span>
                      <p class="small text-success-emphasis mb-0">{{ step.solution_applied }}</p>
                    </div>
                  </div>
                </div>

                <!-- Screenshots / Media -->
                <div v-if="step.images && step.images.length" class="d-flex gap-2 flex-wrap mb-3">
                  <a v-for="(img, i) in step.images" :key="i" :href="'/' + img" target="_blank">
                    <img :src="'/' + img" alt="Screenshot" class="rounded border shadow-sm" style="height: 100px; width: 150px; object-fit: cover;">
                  </a>
                </div>

                <!-- YouTube Video Link -->
                <div v-if="step.youtube_url">
                  <a :href="step.youtube_url" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-youtube me-1"></i> Voir la démo vidéo YouTube
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-5 text-muted bg-white rounded-4 border">
            <i class="bi bi-journal-code fs-1 d-block mb-2 text-muted"></i>
            <h5 class="fw-bold text-dark">Aucune étape enregistrée pour ce projet</h5>
            <p class="small mb-3">Commencez dès maintenant votre journal de bord de développement.</p>
            <button @click="showAddStep = true" class="btn btn-danger btn-sm rounded-pill px-4">
              Ajouter la première étape
            </button>
          </div>

        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  project: { type: Object, required: true },
})

const showAddStep = ref(false)

const form = useForm({
  title: '',
  description: '',
  problem_encountered: '',
  solution_applied: '',
  feature_ids: [],
  youtube_url: '',
  event_date: new Date().toISOString().slice(0, 10),
  screenshot: null,
})

function submitTimeline() {
  form.post(`/espace-utilisateur/projet/${props.project.id}/timeline`, {
    onSuccess: () => {
      form.reset()
      showAddStep.value = false
    }
  })
}
</script>
