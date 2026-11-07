<template>
  <div class="bg-light min-vh-100 py-5">
    <Head title="Créer une Conférence / Événement - Espace Membre Tech237" />

    <div class="container" style="max-width: 900px;">
      <!-- Breadcrumb & Back -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <Link href="/espace-utilisateur?section=conferences" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour aux conférences</span>
          </Link>
        </div>
        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">
          <i class="bi bi-calendar-event me-1"></i> Nouvel Événement Tech
        </span>
      </div>

      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white border-0 p-4 pb-0">
          <h3 class="fw-bold text-dark mb-1">Organiser une Conférence ou un Événement</h3>
          <p class="text-muted small mb-0">
            Remplissez les détails ci-dessous. Dès validation par l'administration Tech237, votre conférence sera publiée et visible sur le site.
          </p>
        </div>

        <div class="card-body p-4">
          <form @submit.prevent="submit">

            <!-- Title FR & EN -->
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">
                  Titre de l'événement (Français) <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.title_fr" 
                  placeholder="ex: Sommet Intelligence Artificielle & Cloud 2026"
                  required
                >
                <div v-if="form.errors.title_fr" class="text-danger small mt-1">{{ form.errors.title_fr }}</div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">
                  Titre de l'événement (Anglais - Optionnel)
                </label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.title_en" 
                  placeholder="ex: AI & Cloud Tech Summit 2026"
                >
              </div>
            </div>

            <!-- Dates & Registration Deadline -->
            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark">
                  Date de début <span class="text-danger">*</span>
                </label>
                <input 
                  type="date" 
                  class="form-control" 
                  v-model="form.event_date" 
                  :min="todayDate"
                  required
                >
                <div v-if="form.errors.event_date" class="text-danger small mt-1">{{ form.errors.event_date }}</div>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark">
                  Date de fin (Optionnelle)
                </label>
                <input 
                  type="date" 
                  class="form-control" 
                  v-model="form.end_date" 
                  :min="form.event_date || todayDate"
                >
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark">
                  Date limite de candidature
                </label>
                <input 
                  type="date" 
                  class="form-control" 
                  v-model="form.deadline_registration"
                >
              </div>
            </div>

            <!-- City & Specific Location -->
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">
                  Ville / Format <span class="text-danger">*</span>
                </label>
                <select class="form-select" v-model="form.city" required>
                  <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">
                  Lieu exact / Salle ou Lien
                </label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.location" 
                  placeholder="ex: Hôtel Krystal Palace / Salle Bilingue Akwa"
                >
              </div>
            </div>

            <!-- Cover Image -->
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">
                Affiche / Image de couverture de l'événement
              </label>
              <input 
                type="file" 
                class="form-control" 
                @change="handleImageUpload" 
                accept="image/*"
              >
              <div class="form-text small">Formats acceptés : JPG, PNG, WEBP (Max: 10 Mo).</div>
            </div>

            <!-- Checkbox: Accepts Registrations -->
            <div class="card p-3 border-0 bg-light rounded-3 mb-4">
              <div class="form-check form-switch mb-0">
                <input 
                  class="form-check-input" 
                  type="checkbox" 
                  id="acceptsRegs" 
                  v-model="form.accepts_registrations"
                >
                <label class="form-check-label fw-bold text-dark" for="acceptsRegs">
                  <i class="bi bi-people-fill text-danger me-1"></i>
                  Accepter des participations / inscriptions en ligne
                </label>
                <div class="text-muted small mt-1">
                  Si activé, les visiteurs pourront s'inscrire en direct sur la page de votre conférence. Vous pourrez gérer les inscrits et leur transmettre des billets électroniques avec barre de progression.
                </div>
              </div>
            </div>

            <!-- WYSIWYG Description FR -->
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">
                Description détaillée (Français) <span class="text-danger">*</span>
              </label>
              <div class="wysiwyg-toolbar p-2 border rounded-top bg-light d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('bold', 'fr')"><b>G</b></button>
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('italic', 'fr')"><i>I</i></button>
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('underline', 'fr')"><u>S</u></button>
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('h3', 'fr')">H3</button>
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('ul', 'fr')"><i class="bi bi-list-ul"></i></button>
              </div>
              <textarea 
                ref="descFrRef"
                rows="6" 
                class="form-control rounded-bottom rounded-top-0 border-top-0" 
                v-model="form.description_fr" 
                placeholder="Décrivez les intervenants, le programme, les objectifs et les temps forts..."
                required
              ></textarea>
              <div v-if="form.errors.description_fr" class="text-danger small mt-1">{{ form.errors.description_fr }}</div>
            </div>

            <!-- WYSIWYG Description EN -->
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">
                Description détaillée (Anglais - Optionnelle)
              </label>
              <div class="wysiwyg-toolbar p-2 border rounded-top bg-light d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('bold', 'en')"><b>B</b></button>
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('italic', 'en')"><i>I</i></button>
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('underline', 'en')"><u>U</u></button>
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('h3', 'en')">H3</button>
                <button type="button" class="btn btn-sm btn-white border" @click="formatText('ul', 'en')"><i class="bi bi-list-ul"></i></button>
              </div>
              <textarea 
                ref="descEnRef"
                rows="4" 
                class="form-control rounded-bottom rounded-top-0 border-top-0" 
                v-model="form.description_en" 
                placeholder="English description of speakers, agenda and key highlights..."
              ></textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex align-items-center justify-content-end gap-3 pt-3 border-top">
              <Link href="/espace-utilisateur?section=conferences" class="btn btn-light rounded-pill px-4">
                Annuler
              </Link>
              <button 
                type="submit" 
                class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm"
                :disabled="form.processing"
              >
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-check-circle me-1"></i>
                Soumettre pour validation
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  cities: {
    type: Array,
    default: () => ['Douala', 'Yaoundé', 'Bafoussam', 'Garoua', 'Maroua', 'Buea', 'Limbe', 'Kribi', 'Paris', 'En ligne (Webinaire)'],
  },
})

const todayDate = new Date().toISOString().slice(0, 10)

const form = useForm({
  title_fr: '',
  title_en: '',
  event_date: todayDate,
  end_date: '',
  deadline_registration: '',
  city: 'Douala',
  location: '',
  cover_image: null,
  accepts_registrations: true,
  description_fr: '',
  description_en: '',
})

const descFrRef = ref(null)
const descEnRef = ref(null)

function handleImageUpload(e) {
  if (e.target.files && e.target.files[0]) {
    form.cover_image = e.target.files[0]
  }
}

function formatText(cmd, lang) {
  const target = lang === 'fr' ? descFrRef.value : descEnRef.value
  if (!target) return

  const start = target.selectionStart
  const end = target.selectionEnd
  const text = lang === 'fr' ? form.description_fr : form.description_en
  const selected = text.substring(start, end)

  let formatted = selected
  if (cmd === 'bold') formatted = `<b>${selected}</b>`
  else if (cmd === 'italic') formatted = `<i>${selected}</i>`
  else if (cmd === 'underline') formatted = `<u>${selected}</u>`
  else if (cmd === 'h3') formatted = `\n<h3>${selected}</h3>\n`
  else if (cmd === 'ul') formatted = `\n<ul>\n  <li>${selected}</li>\n</ul>\n`

  const updated = text.substring(0, start) + formatted + text.substring(end)
  if (lang === 'fr') form.description_fr = updated
  else form.description_en = updated
}

function submit() {
  form.post('/espace-utilisateur/conferences', {
    forceFormData: true,
  })
}
</script>
