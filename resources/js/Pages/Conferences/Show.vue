<template>
  <FrontLayout>
    <Head>
      <title>{{ conference.title_fr || conference.title }} | Conférence & Événement Tech - Tech237</title>
      <meta name="description" head-key="description" :content="(conference.description_fr || conference.description)?.replace(/<[^>]*>/g, '').substring(0, 160)">
      <meta property="og:type" head-key="og:type" content="article">
      <meta property="og:title" head-key="og:title" :content="(conference.title_fr || conference.title) + ' | Tech237'">
      <meta property="og:description" head-key="og:description" :content="(conference.description_fr || conference.description)?.replace(/<[^>]*>/g, '').substring(0, 160)">
      <meta property="og:image" head-key="og:image" :content="conference.banner_image ? '/' + conference.banner_image : (conference.cover_image ? '/' + conference.cover_image : '/assets/images/default-event.jpg')">
      <meta name="twitter:card" head-key="twitter:card" content="summary_large_image">
      <meta name="twitter:title" head-key="twitter:title" :content="(conference.title_fr || conference.title) + ' | Tech237'">
      <meta name="twitter:description" head-key="twitter:description" :content="(conference.description_fr || conference.description)?.replace(/<[^>]*>/g, '').substring(0, 160)">
      <meta name="twitter:image" head-key="twitter:image" :content="conference.banner_image ? '/' + conference.banner_image : (conference.cover_image ? '/' + conference.cover_image : '/assets/images/default-event.jpg')">
    </Head>

    <div class="conference-show-page py-5">
      <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/conferences" class="text-decoration-none text-muted">Conférences</Link></li>
            <li class="breadcrumb-item active" aria-current="page">{{ conference.title }}</li>
          </ol>
        </nav>

        <div class="row g-4">
          <!-- Main Event Column (8 cols) -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
              <div class="position-relative">
                <img 
                  :src="'/' + (conference.banner_image || 'assets/images/default-event.jpg')" 
                  class="img-fluid w-100" 
                  style="max-height: 400px; object-fit: cover;"
                >
                <span v-if="conference.is_official" class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fw-bold shadow">
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
                    {{ new Date(conference.start_date).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                  </span>

                  <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold">
                    {{ conference.location_type === 'online' ? '🌐 Webinaire en ligne' : (conference.location_type === 'hybrid' ? '⚡ Hybride' : '📍 Présentiel physique') }}
                  </span>
                </div>

                <h1 class="display-6 fw-black text-dark mb-4">{{ conference.title }}</h1>

                <!-- Event Details Grid -->
                <div class="row g-3 p-3 bg-light rounded-4 mb-4">
                  <div class="col-sm-6">
                    <small class="text-muted d-block">Horaire de début :</small>
                    <strong class="text-dark">{{ new Date(conference.start_date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) }}</strong>
                  </div>
                  <div class="col-sm-6">
                    <small class="text-muted d-block">Lieu exact :</small>
                    <strong class="text-dark">{{ conference.venue_address || (conference.city + ', Cameroun') }}</strong>
                  </div>
                  <div class="col-sm-6">
                    <small class="text-muted d-block">Participants inscrits :</small>
                    <strong class="text-dark">{{ conference.registrations_count }} / {{ conference.max_attendees }} places</strong>
                  </div>
                  <div class="col-sm-6">
                    <small class="text-muted d-block">Organisateur :</small>
                    <strong class="text-dark">{{ conference.creator_user ? conference.creator_user.name : 'Melty Cameroun Officiel' }}</strong>
                  </div>
                </div>

                <!-- Description -->
                <h4 class="fw-bold text-dark mb-3">À propos de l'événement</h4>
                <div class="text-secondary mb-4 leading-relaxed whitespace-pre-line" style="font-size: 15px; line-height: 1.8;">
                  {{ conference.description }}
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar Booking / Actions (4 cols) -->
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px;">
              <h5 class="fw-bold text-dark mb-3"><i class="bi bi-ticket-perforated-fill text-danger me-2"></i>Participer à l'événement</h5>
              
              <div v-if="isUserRegistered" class="alert alert-success rounded-4 border-0 p-3 mb-3">
                <i class="bi bi-check-circle-fill me-1"></i> Vous êtes déjà inscrit(e) à cette conférence ! Votre billet électronique vous a été transmis.
              </div>

              <div v-else-if="!conference.accepts_registrations" class="alert alert-secondary rounded-4 border-0 p-3 mb-3">
                <i class="bi bi-info-circle me-1"></i> Les inscriptions en ligne sont fermées pour cette session.
              </div>

              <div v-else-if="conference.registrations_count >= conference.max_attendees" class="alert alert-danger rounded-4 border-0 p-3 mb-3">
                <i class="bi bi-x-circle me-1"></i> Événement complet (toutes les places sont réservées).
              </div>

              <div v-else class="mb-3">
                <p class="text-muted small mb-3">
                  Réservez gratuitement votre place dès maintenant. Vous recevrez une confirmation immédiate et votre invitation par email.
                </p>
                <button @click="showRegisterModal = true" class="btn btn-melty w-100 rounded-pill py-2 fw-bold shadow-sm">
                  <i class="bi bi-person-plus-fill me-1"></i> S'inscrire à la conférence
                </button>
              </div>

              <!-- Share -->
              <div class="pt-3 border-top text-center">
                <small class="text-muted d-block mb-2">Partager avec vos contacts :</small>
                <div class="d-flex justify-content-center gap-2">
                  <a :href="'https://wa.me/?text=' + encodeURIComponent('Découvrez cet événement sur Melty : ' + conference.title)" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                  </a>
                  <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent(conference.title)" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                    <i class="bi bi-twitter-x"></i> X
                  </a>
                </div>
              </div>
            </div>
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
              <i class="bi bi-ticket-perforated text-danger me-2"></i>Inscription : {{ conference.title }}
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
                  <label class="form-label small fw-bold">Email de contact <span class="text-danger">*</span></label>
                  <input type="email" v-model="regForm.email" class="form-control form-control-sm" placeholder="ex: monadresse@gmail.com" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Téléphone / WhatsApp <span class="text-danger">*</span></label>
                  <input type="text" v-model="regForm.phone_whatsapp" class="form-control form-control-sm" placeholder="+237 6..." required>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Genre (Sexe) <span class="text-danger">*</span></label>
                  <select v-model="regForm.gender" class="form-select form-select-sm" required>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                    <option value="Autre">Autre</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Ville de résidence <span class="text-danger">*</span></label>
                  <input type="text" v-model="regForm.city" class="form-control form-control-sm" placeholder="Douala, Yaoundé..." required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Courte bio / Pourquoi souhaitez-vous participer ?</label>
                <textarea v-model="regForm.bio" rows="2" class="form-control form-control-sm" placeholder="Votre métier, attente principale..."></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Photo pour votre badge de participant (Optionnel)</label>
                <input type="file" @change="handlePhotoUpload" class="form-control form-control-sm" accept="image/*">
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
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  conference: Object,
  isUserRegistered: Boolean,
  upcomingConferences: Array,
})

const showRegisterModal = ref(false)
const isRegistering = ref(false)

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
