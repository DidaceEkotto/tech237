<template>
  <FrontLayout>
    <Head>
      <title>Invitation à collaborer sur {{ project.name }} | Tech237</title>
    </Head>

    <div class="container py-5 my-md-4">
      <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

          <!-- Main Invitation Card -->
          <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
            
            <!-- Top Banner -->
            <div class="p-4 text-white text-center position-relative" style="background: linear-gradient(135deg, #0B2559 0%, #1e3a8a 100%);">
              <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill small fw-bold mb-2">
                🤝 Invitation à collaborer
              </span>
              <h2 class="fw-bold mb-1">Rejoignez l'équipe du projet</h2>
              <p class="text-white-50 small mb-0">Participez au développement et à l'évolution de ce produit tech.</p>
            </div>

            <!-- Card Body -->
            <div class="p-4 p-md-5">

              <!-- Expired Notice -->
              <div v-if="isExpired || invitation.status === 'expired'" class="alert alert-warning rounded-4 border-0 p-4 text-center mb-4">
                <i class="bi bi-clock-history fs-1 text-warning d-block mb-2"></i>
                <h5 class="fw-bold text-dark">Lien d'invitation expiré</h5>
                <p class="small text-muted mb-0">
                  Cette invitation a dépassé le délai de validité de <strong>48 heures</strong>. 
                  Veuillez contacter le propriétaire du projet pour qu'il vous envoie un nouveau lien.
                </p>
              </div>

              <!-- Already Accepted Notice -->
              <div v-else-if="invitation.status === 'accepted'" class="alert alert-success rounded-4 border-0 p-4 text-center mb-4">
                <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-2"></i>
                <h5 class="fw-bold text-dark">Invitation déjà acceptée</h5>
                <p class="small text-muted mb-3">
                  Vous collaborez déjà sur ce projet. Vous pouvez accéder à votre espace pour soumettre des devlogs et des photos.
                </p>
                <Link href="/espace-utilisateur?section=projects" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">
                  Accéder à mon espace projet
                </Link>
              </div>

              <!-- Already Declined Notice -->
              <div v-else-if="invitation.status === 'declined'" class="alert alert-secondary rounded-4 border-0 p-4 text-center mb-4">
                <i class="bi bi-x-circle-fill fs-1 text-secondary d-block mb-2"></i>
                <h5 class="fw-bold text-dark">Invitation déclinée</h5>
                <p class="small text-muted mb-0">
                  Cette invitation a été refusée et ce lien est devenu inutilisable.
                </p>
              </div>

              <!-- Blocked Notice -->
              <div v-else-if="isBlocked" class="alert alert-danger rounded-4 border-0 p-4 text-center mb-4">
                <i class="bi bi-shield-slash-fill fs-1 text-danger d-block mb-2"></i>
                <h5 class="fw-bold text-dark">Collaboration bloquée</h5>
                <p class="small text-muted mb-0">
                  Un refus définitif de collaboration a été enregistré entre votre compte et ce créateur.
                </p>
              </div>

              <!-- ACTIVE INVITATION PRESENTATION -->
              <div v-else>

                <!-- Project Preview Box -->
                <div class="card border rounded-4 p-4 mb-4 bg-light">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 border overflow-hidden p-1 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; min-width: 64px;">
                      <img 
                        v-if="project.logo" 
                        :src="project.logo.startsWith('/') ? project.logo : '/' + project.logo" 
                        :alt="project.name" 
                        class="w-100 h-100 object-fit-contain rounded-2"
                      >
                      <div v-else class="fw-bold text-success fs-4">
                        {{ (project.name || 'P').charAt(0).toUpperCase() }}
                      </div>
                    </div>
                    <div>
                      <span class="badge bg-danger-subtle text-danger small text-uppercase mb-1">
                        {{ project.project_type }}
                      </span>
                      <h4 class="fw-bold mb-0 text-dark">{{ project.name }}</h4>
                      <small v-if="project.startup" class="text-muted">
                        <i class="bi bi-building me-1"></i>Porté par {{ project.startup.name }}
                      </small>
                    </div>
                  </div>

                  <p class="small text-secondary mb-3 line-clamp-3">
                    {{ project.description?.replace(/<[^>]*>/g, '') }}
                  </p>

                  <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted">
                    <span><i class="bi bi-person-fill text-primary me-1"></i>Créateur : <strong>{{ inviter.full_name || inviter.name }}</strong></span>
                    <span><i class="bi bi-award-fill text-warning me-1"></i>Rôle : <strong>{{ invitation.role || 'Collaborateur' }}</strong></span>
                  </div>
                </div>

                <!-- Custom inviter message -->
                <div v-if="invitation.message" class="p-3 rounded-3 bg-white border border-start-4 border-primary mb-4">
                  <small class="text-muted fw-bold d-block mb-1">Message du créateur :</small>
                  <p class="small mb-0 fst-italic text-dark">« {{ invitation.message }} »</p>
                </div>

                <!-- 48h Expiration Notice -->
                <div class="alert alert-warning border-0 rounded-3 p-3 small d-flex align-items-center gap-2 mb-4">
                  <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                  <div>
                    <strong>Validité limitée à 48 heures :</strong>
                    <div class="text-muted">Cette invitation expirera le {{ formatDateTime(invitation.expires_at) }}. Une fois acceptée ou refusée, le lien devient immédiatement inutilisable.</div>
                  </div>
                </div>

                <!-- What this gives you -->
                <div class="p-3 rounded-3 bg-light border mb-4">
                  <h6 class="fw-bold text-dark mb-2 small"><i class="bi bi-check2-all text-success me-1"></i>En tant que collaborateur :</h6>
                  <ul class="small text-muted mb-0 ps-3">
                    <li>Vous pourrez consigner les étapes Devlog et résolutions de défis sur ce projet.</li>
                    <li>Vous pourrez téléverser des captures d'écran et visuels dans la galerie du projet.</li>
                    <li>Vos propositions seront soumises à la validation du propriétaire pour garantir l'intégrité du projet.</li>
                    <li>Votre nom et profil apparaîtront dans la section <em>Collaborateurs du projet</em>.</li>
                  </ul>
                </div>

                <!-- Auth Check -->
                <div v-if="!authUser" class="text-center p-4 bg-light rounded-4 border mb-3">
                  <h6 class="fw-bold text-dark mb-2">Connectez-vous pour répondre à cette invitation</h6>
                  <p class="small text-muted mb-3">Vous devez être connecté avec votre compte Tech237 pour collaborer.</p>
                  <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <Link :href="'/login?redirect=' + encodeURIComponent($page.url)" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">
                      Se connecter
                    </Link>
                    <Link :href="'/register?redirect=' + encodeURIComponent($page.url)" class="btn btn-success rounded-pill px-4 btn-sm fw-bold">
                      Créer un compte
                    </Link>
                  </div>
                </div>

                <!-- Action Buttons with Confirmation Modals (Requirement: static confirmation modal) -->
                <div v-else class="d-flex flex-column gap-2 mt-4">
                  <button @click="openConfirmModal('accept')" class="btn btn-success rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-check-lg fs-5"></i>
                    <span>Accepter la collaboration</span>
                  </button>

                  <div class="d-flex gap-2">
                    <button @click="openConfirmModal('decline')" class="btn btn-outline-secondary rounded-pill py-2 flex-grow-1 btn-sm fw-semibold">
                      <i class="bi bi-x-lg me-1"></i> Refuser l'invitation
                    </button>
                    <button @click="openConfirmModal('block')" class="btn btn-outline-danger rounded-pill py-2 flex-grow-1 btn-sm fw-semibold" title="Bloquer toute future collaboration">
                      <i class="bi bi-shield-x me-1"></i> Ne plus jamais collaborer
                    </button>
                  </div>
                </div>

              </div>

            </div>

          </div>

        </div>
      </div>
    </div>

    <!-- STATIC CONFIRMATION MODALS (Requirement: toujours ajouter un modal static de confirmation pour toute action susceptible d'avoir des conséquences) -->
    
    <!-- Modal: Accepter -->
    <div v-if="activeModal === 'accept'" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.55);" @click.self="activeModal = null">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 p-4 text-center bg-white">
          <div class="rounded-circle bg-success-subtle text-success mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="bi bi-people-fill fs-2"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">Confirmer la collaboration</h5>
          <p class="small text-muted mb-4">
            Vous êtes sur le point d'accepter de collaborer sur le projet <strong>{{ project.name }}</strong>. 
            Le créateur en sera immédiatement notifié et ce projet s'affichera dans votre tableau de bord.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-light rounded-pill px-4" @click="activeModal = null">Annuler</button>
            <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" :disabled="isProcessing" @click="submitAccept">
              Confirmer et Rejoindre
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Refuser -->
    <div v-if="activeModal === 'decline'" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.55);" @click.self="activeModal = null">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 p-4 text-center bg-white">
          <div class="rounded-circle bg-secondary-subtle text-secondary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="bi bi-x-circle-fill fs-2"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">Décliner l'invitation ?</h5>
          <p class="small text-muted mb-4">
            Ce lien deviendra inutilisable. Le créateur pourra toujours vous renvoyer une nouvelle invitation à l'avenir si vous changez d'avis.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-light rounded-pill px-4" @click="activeModal = null">Annuler</button>
            <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" :disabled="isProcessing" @click="submitDecline">
              Refuser l'invitation
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Bloquer ("je ne souhaite jamais collaborer avec vous") -->
    <div v-if="activeModal === 'block'" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6);" @click.self="activeModal = null">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 p-4 text-center bg-white">
          <div class="rounded-circle bg-danger-subtle text-danger mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="bi bi-slash-circle-fill fs-2"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">Bloquer toute collaboration future ?</h5>
          <div class="alert alert-danger p-3 small text-start mb-3 border-0 rounded-3">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>Conséquence définitive :</strong> Si vous confirmez, <strong>{{ inviter.full_name || inviter.name }}</strong> ne pourra plus jamais vous envoyer d'invitations sur aucun projet, et vice-versa.
          </div>
          <p class="small text-muted mb-4">
            Souhaitez-vous vraiment activer cette restriction permanente ?
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-light rounded-pill px-4" @click="activeModal = null">Annuler</button>
            <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" :disabled="isProcessing" @click="submitBlock">
              Je ne souhaite jamais collaborer
            </button>
          </div>
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
  invitation: Object,
  project: Object,
  inviter: Object,
  isExpired: Boolean,
  isBlocked: Boolean,
  authUser: Object,
})

const activeModal = ref(null)
const isProcessing = ref(false)

function openConfirmModal(type) {
  activeModal.value = type
}

function submitAccept() {
  isProcessing.value = true
  router.post(`/invitations/${props.invitation.token}/accepter`, {}, {
    onFinish: () => {
      isProcessing.value = false
      activeModal.value = null
    }
  })
}

function submitDecline() {
  isProcessing.value = true
  router.post(`/invitations/${props.invitation.token}/refuser`, {}, {
    onFinish: () => {
      isProcessing.value = false
      activeModal.value = null
    }
  })
}

function submitBlock() {
  isProcessing.value = true
  router.post(`/invitations/${props.invitation.token}/bloquer`, {}, {
    onFinish: () => {
      isProcessing.value = false
      activeModal.value = null
    }
  })
}

function formatDateTime(dateStr) {
  if (!dateStr) return ''
  try {
    return new Date(dateStr).toLocaleString('fr-FR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch(e) {
    return dateStr
  }
}
</script>
