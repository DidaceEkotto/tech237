<template>
  <FrontLayout>
    <Head title="Nouveau Formulaire • Tech237 Forms" />

    <div class="forms-create-page py-5 bg-slate-50 min-vh-100" style="background-color: #f8fafc;">
      <div class="container" style="max-width: 860px;">
        
        <!-- Header Nav -->
        <div class="d-flex align-items-center justify-content-between mb-4">
          <Link href="/espace-utilisateur/formulaires" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-2 fw-semibold text-dark shadow-xs d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour aux formulaires</span>
          </Link>

          <Link href="/espace-utilisateur/formulaires/guide" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
            <i class="bi bi-question-circle me-1"></i> Guide d'utilisation
          </Link>
        </div>

        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white mb-4">
          <div class="text-center mb-5">
            <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-success bg-opacity-10 text-success mb-3">
              <i class="bi bi-ui-checks-grid fs-2"></i>
            </div>
            <h2 class="h3 fw-black text-dark mb-2">Créer un Nouveau Formulaire</h2>
            <p class="text-muted small mx-auto" style="max-width: 520px;">
              Choisissez un modèle prédéfini adapté aux besoins tech ou démarrez de zéro. Vous pourrez personnaliser tous les champs, la logique et les couleurs à l'étape suivante.
            </p>
          </div>

          <form @submit.prevent="submitCreate">

            <!-- Step 1: Choose Template -->
            <label class="form-label fw-bold text-dark mb-3">
              1. Choisissez un modèle de départ :
            </label>
            <div class="row g-3 mb-4">
              
              <!-- Blank -->
              <div class="col-md-6">
                <div 
                  class="card h-100 border-2 rounded-4 p-3 cursor-pointer transition template-card"
                  :class="selectedTemplate === 'blank' ? 'border-success bg-success bg-opacity-10' : 'border-light-subtle hover-border-secondary'"
                  @click="selectedTemplate = 'blank'"
                >
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-white border text-dark fs-4 shadow-2xs">
                      🚀
                    </div>
                    <div>
                      <h6 class="fw-bold mb-1 text-dark">Formulaire Vierge</h6>
                      <p class="extra-small text-muted mb-0">Démarrez avec une structure minimale et ajoutez vos propres questions.</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Event & Hackathon -->
              <div class="col-md-6">
                <div 
                  class="card h-100 border-2 rounded-4 p-3 cursor-pointer transition template-card"
                  :class="selectedTemplate === 'event_registration' ? 'border-success bg-success bg-opacity-10' : 'border-light-subtle hover-border-secondary'"
                  @click="selectedTemplate = 'event_registration'"
                >
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-white border text-primary fs-4 shadow-2xs">
                      🎟️
                    </div>
                    <div>
                      <h6 class="fw-bold mb-1 text-dark">Inscription Événement / Hackathon</h6>
                      <p class="extra-small text-muted mb-0">Nom, Email, WhatsApp, Ville du Cameroun et Rôle tech.</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tech Recruitment -->
              <div class="col-md-6">
                <div 
                  class="card h-100 border-2 rounded-4 p-3 cursor-pointer transition template-card"
                  :class="selectedTemplate === 'tech_recruitment' ? 'border-success bg-success bg-opacity-10' : 'border-light-subtle hover-border-secondary'"
                  @click="selectedTemplate = 'tech_recruitment'"
                >
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-white border text-danger fs-4 shadow-2xs">
                      💻
                    </div>
                    <div>
                      <h6 class="fw-bold mb-1 text-dark">Recrutement Tech &amp; Développeurs</h6>
                      <p class="extra-small text-muted mb-0">Stack, Profil GitHub, Extrait de Code (Snippet) et Upload CV.</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Feedback Survey -->
              <div class="col-md-6">
                <div 
                  class="card h-100 border-2 rounded-4 p-3 cursor-pointer transition template-card"
                  :class="selectedTemplate === 'feedback_survey' ? 'border-success bg-success bg-opacity-10' : 'border-light-subtle hover-border-secondary'"
                  @click="selectedTemplate = 'feedback_survey'"
                >
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-white border text-warning fs-4 shadow-2xs">
                      💬
                    </div>
                    <div>
                      <h6 class="fw-bold mb-1 text-dark">Sondage &amp; Avis Communauté</h6>
                      <p class="extra-small text-muted mb-0">Note par étoiles, Recommandation, Retours et remarques.</p>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- Step 2: Form Title & Details -->
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">2. Titre de votre formulaire <span class="text-danger">*</span></label>
              <input 
                type="text" 
                v-model="form.title" 
                class="form-control form-control-lg rounded-3" 
                placeholder="ex: Inscription au Hackathon Yaoundé 2026" 
                required 
              />
            </div>

            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">Description ou consignes (Optionnel)</label>
              <textarea 
                v-model="form.description" 
                rows="3" 
                class="form-control rounded-3" 
                placeholder="Expliquez en quelques lignes l'objectif de votre questionnaire..."
              ></textarea>
            </div>

            <!-- Step 3: Display Mode -->
            <label class="form-label fw-bold text-dark mb-2">
              3. Choisissez le mode d'affichage pour les répondants :
            </label>
            <div class="row g-3 mb-4">
              
              <!-- Classic Mode -->
              <div class="col-md-6">
                <div 
                  class="card h-100 border-2 rounded-4 p-3 cursor-pointer transition"
                  :class="form.display_mode === 'classic' ? 'border-success bg-light' : 'border-light-subtle'"
                  @click="form.display_mode = 'classic'"
                >
                  <div class="form-check">
                    <input class="form-check-input" type="radio" v-model="form.display_mode" value="classic" id="modeClassic">
                    <label class="form-check-label fw-bold text-dark cursor-pointer" for="modeClassic">
                      Mode Classique (Google Forms)
                    </label>
                  </div>
                  <p class="extra-small text-muted mt-2 mb-0 ps-4">
                    Tous les champs s'affichent sur une page continue en défilement vertical. Idéal pour les formulaires exhaustifs ou administratifs.
                  </p>
                </div>
              </div>

              <!-- Conversation Typeform Mode -->
              <div class="col-md-6">
                <div 
                  class="card h-100 border-2 rounded-4 p-3 cursor-pointer transition"
                  :class="form.display_mode === 'conversation' ? 'border-success bg-light' : 'border-light-subtle'"
                  @click="form.display_mode = 'conversation'"
                >
                  <div class="form-check">
                    <input class="form-check-input" type="radio" v-model="form.display_mode" value="conversation" id="modeConversation">
                    <label class="form-check-label fw-bold text-dark cursor-pointer d-flex align-items-center gap-1" for="modeConversation">
                      <span>Mode Conversationnel (Typeform)</span>
                      <span class="badge bg-primary-subtle text-primary extra-small rounded-pill">Recommandé</span>
                    </label>
                  </div>
                  <p class="extra-small text-muted mt-2 mb-0 ps-4">
                    Une seule question affichée à la fois, transitions fluides et validation rapide à la touche <kbd>Entrée ↵</kbd>. Taux de réponse optimal sur mobile.
                  </p>
                </div>
              </div>

            </div>

            <!-- Step 4: Theme Color -->
            <div class="mb-5">
              <label class="form-label small fw-bold text-dark d-block mb-2">4. Couleur d'accentuation principale :</label>
              <div class="d-flex align-items-center gap-3">
                <button 
                  v-for="color in colorPalette" 
                  :key="color"
                  type="button" 
                  class="rounded-circle border-2 p-0 shadow-xs color-btn"
                  :style="{ backgroundColor: color, width: '38px', height: '38px', borderColor: form.theme_color === color ? '#000' : 'transparent' }"
                  @click="form.theme_color = color"
                >
                  <i v-if="form.theme_color === color" class="bi bi-check text-white fw-bold fs-5"></i>
                </button>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-end gap-3 pt-3 border-top">
              <Link href="/espace-utilisateur/formulaires" class="btn btn-light rounded-pill px-4 fw-semibold">
                Annuler
              </Link>
              <button 
                type="submit" 
                :disabled="isSubmitting" 
                class="btn btn-success rounded-pill px-5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm"
              >
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status"></span>
                <span>Créer et Personnaliser</span>
                <i class="bi bi-arrow-right"></i>
              </button>
            </div>

          </form>

        </div>

      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const selectedTemplate = ref('blank')
const isSubmitting = ref(false)

const form = ref({
  title: '',
  description: '',
  display_mode: 'conversation',
  theme_color: '#00A859',
  template: 'blank',
})

const colorPalette = ['#00A859', '#0B2559', '#2563EB', '#7C3AED', '#DB2777', '#EA580C', '#0F172A']

watch(selectedTemplate, (newVal) => {
  form.value.template = newVal
  if (newVal === 'event_registration' && !form.value.title) {
    form.value.title = 'Inscription à notre prochain Hackathon / Meetup Tech'
    form.value.description = 'Rejoignez les développeurs et fondateurs de la communauté Tech237 pour un atelier intensif.'
  } else if (newVal === 'tech_recruitment' && !form.value.title) {
    form.value.title = 'Candidature : Développeur Web & Mobile'
    form.value.description = 'Remplissez ce questionnaire technique et partagez votre extrait de code pour rejoindre l\'équipe.'
  } else if (newVal === 'feedback_survey' && !form.value.title) {
    form.value.title = 'Avis et Retours sur notre Solution'
    form.value.description = 'Vos retours nous aident à améliorer continuellement l\'expérience de la communauté.'
  }
})

function submitCreate() {
  isSubmitting.value = true
  router.post('/espace-utilisateur/formulaires', form.value, {
    onFinish: () => { isSubmitting.value = false }
  })
}
</script>

<style scoped>
.template-card {
  transition: all 0.2s ease;
}
.template-card:hover {
  transform: translateY(-2px);
}
.cursor-pointer {
  cursor: pointer;
}
.extra-small {
  font-size: 0.78rem;
}
.color-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  outline: none;
  transition: transform 0.15s ease;
}
.color-btn:hover {
  transform: scale(1.1);
}
</style>
