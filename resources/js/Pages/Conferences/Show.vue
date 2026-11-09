<template>
  <FrontLayout>
    <Head>
      <title>{{ conference.title_fr || conference.title }} | Conférence & Événement Tech - Tech237</title>
      <meta name="description" head-key="description" :content="(conference.description_fr || conference.description)?.replace(/<[^>]*>/g, '').substring(0, 160)">
      <meta property="og:type" head-key="og:type" content="article">
      <meta property="og:title" head-key="og:title" :content="(conference.title_fr || conference.title) + ' | Tech237'">
      <meta property="og:description" head-key="og:description" :content="(conference.description_fr || conference.description)?.replace(/<[^>]*>/g, '').substring(0, 160)">
      <meta property="og:image" head-key="og:image" :content="getConferenceImage(conference)">
      <meta name="twitter:card" head-key="twitter:card" content="summary_large_image">
      <meta name="twitter:title" head-key="twitter:title" :content="(conference.title_fr || conference.title) + ' | Tech237'">
      <meta name="twitter:description" head-key="twitter:description" :content="(conference.description_fr || conference.description)?.replace(/<[^>]*>/g, '').substring(0, 160)">
      <meta name="twitter:image" head-key="twitter:image" :content="getConferenceImage(conference)">
    </Head>

    <div class="conference-show-page py-5">
      <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/conferences" class="text-decoration-none text-muted">Conférences</Link></li>
            <li class="breadcrumb-item active" aria-current="page">{{ conference.title_fr || conference.title }}</li>
          </ol>
        </nav>

        <div class="row g-4">
          <!-- Main Event Column (8 cols) -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
              <div class="position-relative">
                <img 
                  :src="getConferenceImage(conference)" 
                  :alt="conference.title_fr || conference.title"
                  class="img-fluid w-100" 
                  style="max-height: 420px; object-fit: cover;"
                  @error="handleImgError"
                >
                <span v-if="conference.is_official || conference.is_official_admin" class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fw-bold shadow">
                  <i class="bi bi-patch-check-fill me-1"></i> Événement Officiel Tech237
                </span>
                <span class="badge bg-dark position-absolute bottom-0 end-0 m-3 px-3 py-2 rounded-pill text-white fw-bold shadow">
                  <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ conference.city }}
                </span>
              </div>

              <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                  <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-calendar-event me-1"></i>
                    {{ formatFullDate(conference.event_date || conference.start_date) }}
                  </span>

                  <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold">
                    {{ (conference.is_in_person === false || conference.location_type === 'online') ? '🌐 En ligne (Webinaire)' : '📍 Présentiel physique' }}
                  </span>

                  <span class="badge px-3 py-2 rounded-pill fw-bold" :class="conference.is_free !== false ? 'bg-success text-white' : 'bg-warning text-dark'">
                    {{ conference.is_free !== false ? '🎟️ Entrée Gratuite' : ('💵 Tarif : ' + (conference.price || 'Payant')) }}
                  </span>

                  <a v-if="conference.website_url" :href="conference.website_url" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-globe me-1"></i> Site officiel
                  </a>
                </div>

                <h1 class="display-6 fw-black text-dark mb-4">{{ conference.title_fr || conference.title }}</h1>

                <!-- Event Details Grid -->
                <div class="row g-3 p-3 bg-light rounded-4 mb-4">
                  <div class="col-sm-6">
                    <small class="text-muted d-block">Date de début :</small>
                    <strong class="text-dark">{{ formatFullDate(conference.event_date || conference.start_date) }}</strong>
                  </div>
                  <div class="col-sm-6" v-if="conference.end_date && conference.end_date !== (conference.event_date || conference.start_date)">
                    <small class="text-muted d-block">Date de clôture :</small>
                    <strong class="text-dark">{{ formatFullDate(conference.end_date) }}</strong>
                  </div>
                  <div class="col-sm-6">
                    <small class="text-muted d-block">Lieu exact :</small>
                    <strong class="text-dark">{{ conference.location || conference.venue_address || (conference.city + ', Cameroun') }}</strong>
                  </div>
                  <div class="col-sm-6">
                    <small class="text-muted d-block">Participants enregistrés :</small>
                    <strong class="text-dark">{{ conference.registrations_count || 0 }} {{ conference.max_attendees ? '/ ' + conference.max_attendees + ' places' : 'participants inscrits' }}</strong>
                  </div>
                  <div class="col-sm-6">
                    <small class="text-muted d-block">Organisateur :</small>
                    <strong class="text-dark">{{ conference.creator_user ? conference.creator_user.name : (conference.user ? conference.user.name : 'Tech237 Cameroun') }}</strong>
                  </div>
                </div>

                <!-- Description -->
                <h4 class="fw-bold text-dark mb-3"><i class="bi bi-file-text text-danger me-2"></i>À propos de l'événement</h4>
                <div v-if="hasHtml(conferenceDescription)" class="text-secondary mb-4 leading-relaxed article-content" style="font-size: 15px; line-height: 1.8;" v-html="conferenceDescription"></div>
                <div v-else class="text-secondary mb-4 leading-relaxed whitespace-pre-line" style="font-size: 15px; line-height: 1.8;">
                  {{ conferenceDescription || 'Aucune description détaillée n\'a été renseignée pour cet événement.' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar Booking / Actions (4 cols) -->
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px;">
              <h5 class="fw-bold text-dark mb-3"><i class="bi bi-ticket-perforated-fill text-danger me-2"></i>Participer à l'événement</h5>
              
              <div v-if="isUserRegistered" class="alert alert-success rounded-4 border-0 p-3 mb-3">
                <i class="bi bi-check-circle-fill me-1"></i> Vous êtes déjà inscrit(e) à cette conférence ! Votre billet électronique vous a été transmis par email.
              </div>

              <div v-else-if="!conference.accepts_registrations" class="alert alert-secondary rounded-4 border-0 p-3 mb-3">
                <i class="bi bi-info-circle me-1"></i> Les inscriptions en ligne sont fermées pour cette session.
              </div>

              <div v-else-if="conference.max_attendees && conference.registrations_count >= conference.max_attendees" class="alert alert-danger rounded-4 border-0 p-3 mb-3">
                <i class="bi bi-x-circle me-1"></i> Événement complet (toutes les places sont réservées).
              </div>

              <div v-else class="mb-3">
                <p class="text-muted small mb-3">
                  Réservez gratuitement votre place dès maintenant. Vous recevrez une confirmation immédiate et votre billet électronique par email.
                </p>
                <button @click="showRegisterModal = true" class="btn btn-melty w-100 rounded-pill py-2 fw-bold shadow-sm">
                  <i class="bi bi-person-plus-fill me-1"></i> S'inscrire à la conférence
                </button>
              </div>

              <!-- Share -->
              <div class="pt-3 border-top text-center">
                <small class="text-muted d-block mb-2">Partager avec vos contacts :</small>
                <div class="d-flex justify-content-center gap-2">
                  <a :href="'https://wa.me/?text=' + encodeURIComponent('Découvrez cet événement sur Tech237 : ' + (conference.title_fr || conference.title))" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                  </a>
                  <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent(conference.title_fr || conference.title)" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                    <i class="bi bi-twitter-x"></i> X
                  </a>
                </div>
              </div>
            </div>

            <!-- Sondage Tech du Jour -->
            <DailyTechPoll class="mt-4" />
          </div>
        </div>

      </div>
    </div>

    <!-- REGISTRATION MODAL (Photo, Nom, Prénom, Sexe, Ville, Email, WhatsApp, Bio) -->
    <div v-if="showRegisterModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">
              <i class="bi bi-ticket-perforated text-danger me-2"></i>Inscription : {{ conference.title_fr || conference.title }}
            </h5>
            <button type="button" class="btn-close" @click="showRegisterModal = false"></button>
          </div>
          <form @submit.prevent="submitRegistration">
            <div class="modal-body p-4">
              <p class="text-muted small mb-3">Complétez vos coordonnées pour recevoir votre billet électronique et confirmation par email.</p>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Prénom <span class="text-danger">*</span></label>
                  <input type="text" v-model="regForm.first_name" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Nom <span class="text-danger">*</span></label>
                  <input type="text" v-model="regForm.last_name" class="form-control form-control-sm" required>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Email <span class="text-danger">*</span></label>
                  <input type="email" v-model="regForm.email" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Téléphone / WhatsApp</label>
                  <input type="tel" v-model="regForm.phone_whatsapp" class="form-control form-control-sm" placeholder="+237 6...">
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Sexe <span class="text-danger">*</span></label>
                  <select v-model="regForm.gender" class="form-select form-select-sm" required>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                    <option value="Autre">Autre</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Ville de résidence <span class="text-danger">*</span></label>
                  <input type="text" v-model="regForm.city" class="form-control form-control-sm" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Photo de profil / Avatar (recommandé pour votre badge)</label>
                <input type="file" @change="handlePhotoUpload" class="form-control form-control-sm" accept="image/*">
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Courte biographie ou motivation (facultatif)</label>
                <textarea v-model="regForm.bio" rows="2" class="form-control form-control-sm" placeholder="Parlez-nous de vos compétences ou attentes pour ce meetup..."></textarea>
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="showRegisterModal = false">Annuler</button>
              <button type="submit" :disabled="isRegistering" class="btn btn-melty rounded-pill px-4">
                <i class="bi bi-check2-circle me-1"></i> Valider mon inscription
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import DailyTechPoll from '@/Components/DailyTechPoll.vue'

const props = defineProps({
  conference: Object,
  isUserRegistered: Boolean,
  upcomingConferences: Array,
})

const showRegisterModal = ref(false)
const isRegistering = ref(false)

const conferenceDescription = computed(() => {
  return props.conference?.description_fr || props.conference?.description || props.conference?.description_en || ''
})

function formatFullDate(val) {
  if (!val) return 'Date à confirmer'
  const d = new Date(val)
  if (isNaN(d.getTime())) return val
  return d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

function getConferenceImage(conf) {
  const img = conf?.cover_image || conf?.banner_image
  if (!img) return '/assets/images/default-event.jpg'
  if (img.startsWith('http://') || img.startsWith('https://')) return img
  return img.startsWith('/') ? img : '/' + img
}

function handleImgError(e) {
  e.target.src = '/assets/images/default-event.jpg'
}

function hasHtml(str) {
  if (!str) return false
  return /<[a-z][\s\S]*>/i.test(str)
}

const regForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone_whatsapp: '',
  gender: 'Homme',
  city: 'Douala',
  bio: '',
  photo: null,
})

function handlePhotoUpload(e) {
  regForm.value.photo = e.target.files[0]
}

function submitRegistration() {
  isRegistering.value = true
  const data = new FormData()
  for (const key in regForm.value) {
    if (regForm.value[key] !== null) {
      data.append(key, regForm.value[key])
    }
  }

  router.post(`/conference/${props.conference.id}/inscription`, data, {
    onSuccess: () => {
      showRegisterModal.value = false
      isRegistering.value = false
    },
    onError: () => {
      isRegistering.value = false
    }
  })
}
</script>

<style scoped>
.whitespace-pre-line {
  white-space: pre-line;
}
</style>
