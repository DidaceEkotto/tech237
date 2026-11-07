<template>
  <div class="bg-light min-vh-100 py-5">
    <Head :title="'Modifier : ' + conference.title_fr + ' - Espace Membre Tech237'" />

    <div class="container" style="max-width: 900px;">
      <!-- Breadcrumb & Back -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <Link href="/espace-utilisateur?section=conferences" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour aux conférences</span>
          </Link>
        </div>
        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-bold">
          <i class="bi bi-pencil me-1"></i> Modification de l'événement
        </span>
      </div>

      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white border-0 p-4 pb-0">
          <h3 class="fw-bold text-dark mb-1">Modifier la Conférence</h3>
          <p class="text-muted small mb-0">Mettez à jour les informations et programmes de votre événement.</p>
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
                >
              </div>
            </div>

            <!-- Cover Image -->
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">
                Affiche / Image de couverture de l'événement
              </label>
              <div v-if="conference.cover_image" class="mb-2">
                <img :src="'/' + conference.cover_image" alt="Affiche" class="rounded shadow-sm" style="height: 100px; object-fit: cover;">
              </div>
              <input 
                type="file" 
                class="form-control" 
                @change="handleImageUpload" 
                accept="image/*"
              >
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
                required
              ></textarea>
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
                Enregistrer les modifications
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
  conference: {
    type: Object,
    required: true,
  },
  cities: {
    type: Array,
    default: () => ['Douala', 'Yaoundé', 'Bafoussam', 'Garoua', 'Maroua', 'Buea', 'Limbe', 'Kribi', 'Paris', 'En ligne (Webinaire)'],
  },
})

const form = useForm({
  title_fr: props.conference.title_fr || '',
  title_en: props.conference.title_en || '',
  event_date: props.conference.event_date ? props.conference.event_date.slice(0, 10) : '',
  end_date: props.conference.end_date ? props.conference.end_date.slice(0, 10) : '',
  deadline_registration: props.conference.deadline_registration ? props.conference.deadline_registration.slice(0, 10) : '',
  city: props.conference.city || 'Douala',
  location: props.conference.location || '',
  cover_image: null,
  accepts_registrations: Boolean(props.conference.accepts_registrations),
  description_fr: props.conference.description_fr || '',
  description_en: props.conference.description_en || '',
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
  form.post(`/espace-utilisateur/conferences/${props.conference.id}`, {
    forceFormData: true,
  })
}
</script>
