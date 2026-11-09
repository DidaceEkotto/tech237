<template>
  <FrontLayout>
    <Head>
      <title>{{ startup.name }} - Startup Cameroun | Tech237</title>
      <meta name="description" head-key="description" :content="startup.description?.replace(/<[^>]*>/g, '').substring(0, 160)" />
      <meta property="og:type" head-key="og:type" content="article" />
      <meta property="og:title" head-key="og:title" :content="startup.name + ' - Startup Cameroun | Tech237'" />
      <meta property="og:description" head-key="og:description" :content="startup.description?.replace(/<[^>]*>/g, '').substring(0, 160)" />
      <meta property="og:image" head-key="og:image" :content="startup.logo ? '/' + startup.logo : (startup.banner ? '/' + startup.banner : '/assets/images/default-startup.png')" />
      <meta name="twitter:card" head-key="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" head-key="twitter:title" :content="startup.name + ' | Tech237'" />
      <meta name="twitter:description" head-key="twitter:description" :content="startup.description?.replace(/<[^>]*>/g, '').substring(0, 160)" />
      <meta name="twitter:image" head-key="twitter:image" :content="startup.logo ? '/' + startup.logo : (startup.banner ? '/' + startup.banner : '/assets/images/default-startup.png')" />
    </Head>

    <div class="startup-show-page py-5 bg-light min-vh-100">
      <div class="container">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/startups" class="text-decoration-none text-muted">Startups</Link></li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">{{ startup.name }}</li>
          </ol>
        </nav>

        <!-- Top Header Card -->
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-5 bg-white">
          <!-- Banner -->
          <div class="position-relative" style="height: 220px; background: linear-gradient(135deg, #0B2559 0%, #061533 100%);">
            <img 
              v-if="startup.banner" 
              :src="startup.banner.startsWith('/') ? startup.banner : '/' + startup.banner" 
              class="w-100 h-100 object-fit-cover opacity-75"
              :alt="startup.name"
            >
            <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center text-white-50">
              <i class="bi bi-building fs-1 opacity-25"></i>
            </div>

            <!-- Top Right Badges -->
            <div class="position-absolute top-0 end-0 m-3 d-flex gap-2">
              <span class="badge px-3 py-2 rounded-pill fw-bold text-white shadow" style="background-color: var(--tech-green, #00A859);">
                <i class="bi bi-patch-check-fill me-1"></i> Entreprise Enregistrée
              </span>
              <span v-if="startup.needs_funding" class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow">
                <i class="bi bi-cash-coin me-1"></i> Recherche de Financement
              </span>
            </div>

            <!-- Logo superposé sur la bannière -->
            <div class="position-absolute" style="bottom: -45px; left: 30px; z-index: 5;">
              <div class="rounded-4 border border-3 border-white shadow bg-white p-2 d-flex align-items-center justify-content-center overflow-hidden" style="width: 95px; height: 95px;">
                <img 
                  v-if="startup.logo" 
                  :src="startup.logo.startsWith('/') ? startup.logo : '/' + startup.logo" 
                  class="w-100 h-100 object-fit-contain" 
                  :alt="startup.name"
                >
                <div v-else class="fw-bold text-success display-6">
                  {{ (startup.name || 'S').charAt(0).toUpperCase() }}
                </div>
              </div>
            </div>
          </div>

          <!-- Main Info Bar -->
          <div class="card-body p-4 pt-5 pt-sm-4 bg-white">
            <div class="row align-items-center g-3">
              <!-- Spacer for logo on desktop -->
              <div class="col-auto d-none d-sm-block" style="width: 105px;"></div>
              <div class="col">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                  <h2 class="fw-bold text-dark mb-0">{{ startup.name }}</h2>
                  <span class="badge bg-light text-dark border">{{ startup.legal_type || 'SARL' }}</span>
                </div>
                <div class="text-muted small d-flex align-items-center gap-3 flex-wrap">
                  <span><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ startup.city || 'Douala' }}<span v-if="startup.location">, {{ startup.location }}</span></span>
                  <span><i class="bi bi-people me-1"></i>{{ startup.employee_count || 1 }} collaborateur(s)</span>
                  <span v-if="startup.rccm_number"><i class="bi bi-file-earmark-text me-1"></i>RCCM: <strong class="text-dark">{{ startup.rccm_number }}</strong></span>
                </div>
              </div>
              <div class="col-md-auto text-md-end">
                <Link href="/espace-utilisateur/startups/create" class="btn btn-outline-dark rounded-pill px-3 fw-bold btn-sm">
                  <i class="bi bi-plus-lg me-1"></i> Enregistrer une startup
                </Link>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <!-- Left Column (8 cols): Description & Projects Currently Developed -->
          <div class="col-lg-8">
            
            <!-- Description / Activité (WYSIWYG formatted) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
              <h4 class="fw-bold text-dark mb-4 d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2" style="color: var(--tech-green);"></i> À propos de la Startup &amp; Mission
              </h4>
              <div class="wysiwyg-rendered-content text-secondary leading-relaxed" v-html="startup.description || 'Description en cours de rédaction.'"></div>
            </div>

            <!-- Active Projects by this Startup (User requested: sur quel projet elle travaille actuellement) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
              <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="fw-bold text-dark mb-0 d-flex align-items-center">
                  <i class="bi bi-kanban-fill me-2" style="color: var(--tech-navy);"></i> Projets Développés par {{ startup.name }} ({{ startup.projects?.length || 0 }})
                </h4>
                <Link href="/projets" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                  Voir tous les projets &rarr;
                </Link>
              </div>

              <div v-if="startup.projects && startup.projects.length > 0" class="row g-3">
                <div v-for="proj in startup.projects" :key="proj.id" class="col-md-6">
                  <div class="card border rounded-4 p-3 h-100 hover-shadow transition bg-light">
                    <div class="d-flex align-items-center gap-3 mb-2">
                      <img 
                        :src="'/' + (proj.logo || proj.banner || 'assets/images/default-project.jpg')" 
                        class="rounded-3 border" 
                        style="width: 48px; height: 48px; object-fit: cover;"
                      >
                      <div class="overflow-hidden">
                        <h6 class="fw-bold text-dark mb-0 text-truncate">
                          <Link :href="'/projet/' + (proj.slug || proj.id)" class="text-dark text-decoration-none hover-success">
                            {{ proj.name }}
                          </Link>
                        </h6>
                        <small class="badge bg-white text-dark border">{{ proj.project_type?.toUpperCase() }}</small>
                      </div>
                    </div>
                    <p class="text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                      {{ proj.description?.replace(/<[^>]*>/g, '') }}
                    </p>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                      <small class="text-muted extra-small">{{ proj.development_duration || 'Projet en cours' }}</small>
                      <Link :href="'/projet/' + (proj.slug || proj.id)" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold">
                        Détails &rarr;
                      </Link>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-4 text-muted small bg-light rounded-4 p-4 border">
                <i class="bi bi-clock-history fs-3 d-block mb-2 text-secondary"></i>
                Cette startup n'a pas encore rendu public ses projets. Ils apparaîtront ici dès leur publication officielle.
              </div>
            </div>

          </div>

          <!-- Right Column (4 cols): Legal Data, Funding Need, Founder -->
          <div class="col-lg-4">
            
            <!-- Funding Card if applicable -->
            <div v-if="startup.needs_funding" class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white" style="background: linear-gradient(135deg, #0B2559 0%, #00A859 100%);">
              <span class="badge bg-warning text-dark align-self-start fw-bold mb-2 px-3 py-1 rounded-pill">
                <i class="bi bi-lightning-charge-fill me-1"></i> LEVÉE DE FONDS
              </span>
              <h5 class="fw-bold mb-2">Recherche Active de Capitaux</h5>
              <div v-if="startup.funding_amount" class="h3 fw-bold mb-2 text-warning">
                {{ Number(startup.funding_amount).toLocaleString('fr-FR') }} FCFA
              </div>
              <p class="small text-white-50 mb-3">
                Vous êtes un fonds de capital-risque, un business angel ou un partenaire stratégique ? Entrez en contact avec les fondateurs.
              </p>
              <button 
                type="button" 
                class="btn btn-light rounded-pill w-100 fw-bold shadow-sm" 
                style="color: var(--tech-navy);"
                @click="openContactModal"
              >
                <i class="bi bi-chat-left-dots-fill me-1 text-success"></i> Contacter la startup
              </button>
            </div>

            <!-- Legal & Registration Specs (OHADA) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
              <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-shield-check text-success me-2"></i>Informations Légales &amp; Fiscales
              </h6>
              <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                <li class="d-flex justify-content-between py-1 border-bottom">
                  <span class="text-muted">Forme Juridique :</span>
                  <strong class="text-dark">{{ startup.legal_type || 'SARL' }}</strong>
                </li>
                <li class="d-flex justify-content-between py-1 border-bottom">
                  <span class="text-muted">Ville du Siège :</span>
                  <strong class="text-dark">{{ startup.city || 'Cameroun' }}</strong>
                </li>
                <li class="d-flex justify-content-between py-1 border-bottom" v-if="startup.rccm_number">
                  <span class="text-muted">N° RCCM :</span>
                  <span class="font-monospace text-dark">{{ startup.rccm_number }}</span>
                </li>
                <li class="d-flex justify-content-between py-1 border-bottom" v-if="startup.niu_number">
                  <span class="text-muted">Identifiant NIU :</span>
                  <span class="font-monospace text-dark">{{ startup.niu_number }}</span>
                </li>
                <li class="d-flex justify-content-between py-1">
                  <span class="text-muted">Taille Équipe :</span>
                  <strong class="text-dark">{{ startup.employee_count || 1 }} personnes</strong>
                </li>
              </ul>
            </div>

            <!-- Founder Card -->
            <div v-if="startup.user" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white text-center">
              <img 
                v-if="hasCustomAvatar(startup.user.avatar)"
                :src="startup.user.avatar.startsWith('/') ? startup.user.avatar : '/' + startup.user.avatar" 
                class="rounded-circle mx-auto mb-2 border border-2 border-success shadow-sm" 
                style="width: 72px; height: 72px; object-fit: cover;"
                :alt="startup.user.first_name || startup.user.name"
              >
              <div 
                v-else 
                class="rounded-circle mx-auto mb-2 border border-2 border-success shadow-sm bg-danger text-white fw-bold d-flex align-items-center justify-content-center"
                style="width: 72px; height: 72px; font-size: 26px;"
              >
                {{ getUserInitial(startup.user) }}
              </div>
              <h5 class="fw-bold text-dark mb-1">{{ startup.user.first_name ? startup.user.first_name + ' ' + (startup.user.name || '') : startup.user.name }}</h5>
              <p class="text-muted small mb-3">Fondateur / Représentant Légal</p>
              
              <button 
                type="button" 
                class="btn btn-outline-dark rounded-pill w-100 fw-bold btn-sm"
                @click="openContactModal"
              >
                <i class="bi bi-chat-dots me-1 text-success"></i> Contacter le fondateur
              </button>
            </div>

            <!-- Related Startups -->
            <div v-if="relatedStartups && relatedStartups.length > 0" class="card border-0 shadow-sm rounded-4 p-4 bg-white">
              <h6 class="fw-bold text-dark mb-3">Autres Startups à {{ startup.city }}</h6>
              <div class="d-flex flex-column gap-3">
                <div v-for="rel in relatedStartups" :key="rel.id" class="d-flex align-items-center gap-3">
                  <img :src="'/' + (rel.logo || 'assets/images/default-startup.png')" class="rounded-3 border" style="width: 40px; height: 40px; object-fit: contain;">
                  <div>
                    <h6 class="fw-bold text-dark mb-0 small">
                      <Link :href="'/startups/' + (rel.slug || rel.id)" class="text-dark text-decoration-none hover-success">
                        {{ rel.name }}
                      </Link>
                    </h6>
                    <small class="text-muted">{{ rel.legal_type }}</small>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- STATIC CONTACT STARTUP / FOUNDER MODAL -->
    <div v-if="showContactModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;" @click.self="showContactModal = false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header border-0 pb-0">
            <div>
              <h5 class="modal-title fw-bold text-dark mb-0">
                <i class="bi bi-envelope-paper-heart-fill me-2" style="color: var(--tech-green);"></i>Contacter {{ startup.name }}
              </h5>
              <small class="text-muted">Un email sera directement transmis au fondateur de la startup.</small>
            </div>
            <button type="button" class="btn-close" @click="showContactModal = false"></button>
          </div>

          <form @submit.prevent="submitContact">
            <div class="modal-body">
              <div v-if="contactSuccess" class="alert alert-success rounded-3 small mb-3">
                <i class="bi bi-check-circle-fill me-1"></i> {{ contactSuccess }}
              </div>

              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small fw-bold">Nom <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="contactForm.nom" placeholder="Votre nom" required>
                  <div v-if="contactForm.errors.nom" class="text-danger extra-small mt-1">{{ contactForm.errors.nom }}</div>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-bold">Prénom <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="contactForm.prenom" placeholder="Votre prénom" required>
                  <div v-if="contactForm.errors.prenom" class="text-danger extra-small mt-1">{{ contactForm.errors.prenom }}</div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Votre Adresse Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" v-model="contactForm.email" placeholder="Pour que la startup puisse vous répondre" required>
                <div v-if="contactForm.errors.email" class="text-danger extra-small mt-1">{{ contactForm.errors.email }}</div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Sujet <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="contactForm.sujet" placeholder="ex: Proposition d'investissement, partenariat..." required>
                <div v-if="contactForm.errors.sujet" class="text-danger extra-small mt-1">{{ contactForm.errors.sujet }}</div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Description / Message <span class="text-danger">*</span></label>
                <textarea class="form-control" rows="4" v-model="contactForm.description" placeholder="Expliquez en détail pourquoi vous contactez cette startup..." required></textarea>
                <div v-if="contactForm.errors.description" class="text-danger extra-small mt-1">{{ contactForm.errors.description }}</div>
              </div>
            </div>

            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-light rounded-pill px-4" @click="showContactModal = false">Fermer</button>
              <button 
                type="submit" 
                class="btn rounded-pill px-4 fw-bold text-white shadow-sm" 
                style="background-color: var(--tech-green);"
                :disabled="contactForm.processing"
              >
                <span v-if="contactForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-send-fill me-1"></i> Envoyer le message
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
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const props = defineProps({
  startup: { type: Object, required: true },
  relatedStartups: { type: Array, default: () => [] },
})

const page = usePage()
const authUser = page.props.auth?.user

function hasCustomAvatar(avatar) {
  if (!avatar) return false
  return !avatar.includes('author-default.png') && !avatar.includes('default-avatar.png')
}

function getUserInitial(user) {
  if (!user) return 'U'
  const name = user.first_name || user.name || 'U'
  return name.trim().charAt(0).toUpperCase()
}

const showContactModal = ref(false)
const contactSuccess = ref('')

const contactForm = useForm({
  nom: authUser?.name || '',
  prenom: authUser?.first_name || '',
  email: authUser?.email || '',
  sujet: '',
  description: '',
})

function openContactModal() {
  contactSuccess.value = ''
  showContactModal.value = true
}

function submitContact() {
  contactForm.post(`/startups/${props.startup.id}/contact`, {
    preserveScroll: true,
    onSuccess: () => {
      contactSuccess.value = 'Votre message a bien été envoyé au fondateur !'
      contactForm.reset('sujet', 'description')
      setTimeout(() => {
        showContactModal.value = false
        contactSuccess.value = ''
      }, 2000)
    }
  })
}
</script>

<style scoped>
.hover-shadow:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
}
.transition {
  transition: all 0.25s ease;
}
.hover-success:hover {
  color: var(--tech-green) !important;
}
.extra-small {
  font-size: 11px;
}
</style>
