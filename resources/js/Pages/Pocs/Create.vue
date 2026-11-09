<template>
  <FrontLayout>
    <Head>
      <title>Proposer un POC & Mini-Actualité | Tech237</title>
      <meta name="description" content="Partagez votre actualité, un lancement tech, un projet open-source ou une nouveauté avec la communauté Tech237.">
    </Head>

    <div class="poc-create-page py-5 bg-light min-vh-100">
      <div class="container" style="max-width: 900px;">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><Link href="/" class="text-decoration-none text-muted">Accueil</Link></li>
            <li class="breadcrumb-item"><Link href="/pocs" class="text-decoration-none text-muted">POCs</Link></li>
            <li class="breadcrumb-item active text-dark fw-bold">Proposer une mini-actualité</li>
          </ol>
        </nav>

        <!-- Main Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
          <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-4 flex-wrap gap-2">
            <div>
              <span class="badge px-3 py-1 rounded-pill fw-bold text-uppercase mb-2 text-white" style="background-color: var(--tech-green, #00A859);">
                <i class="bi bi-lightning-charge-fill me-1"></i> Espace Communautaire
              </span>
              <h1 class="h3 fw-bold text-dark mb-1">Partager un POC ou une Mini-Actualité</h1>
              <p class="text-muted small mb-0">Partagez une sortie de projet, une annonce tech, un tutoriel ou une découverte utile pour l'écosystème.</p>
            </div>
            <Link href="/pocs" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
              <i class="bi bi-arrow-left me-1"></i> Retour aux POCs
            </Link>
          </div>

          <form @submit.prevent="submitPoc">
            
            <!-- Title -->
            <div class="mb-4">
              <label class="form-label fw-bold text-dark">
                Titre de la mini-actualité <span class="text-danger">*</span>
              </label>
              <input 
                type="text" 
                v-model="form.title" 
                class="form-control form-control-lg rounded-3" 
                placeholder="ex: Lancement de notre passerelle Mobile Money open-source en PHP"
                required
                maxlength="200"
              />
              <div v-if="form.errors.title" class="text-danger small mt-1">{{ form.errors.title }}</div>
              <div class="d-flex justify-content-between extra-small text-muted mt-1">
                <span>Soyez clair, accrocheur et direct.</span>
                <span>{{ form.title.length }} / 200</span>
              </div>
            </div>

            <!-- Tag & External Link -->
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label fw-bold text-dark">
                  Thématique / Catégorie <span class="text-danger">*</span>
                </label>
                <select v-model="form.tag" class="form-select rounded-3" required>
                  <option value="general">💡 Général Tech</option>
                  <option value="ia">🤖 Intelligence Artificielle</option>
                  <option value="fintech">💳 Fintech & Paiement</option>
                  <option value="dev">💻 Dev & Open Source</option>
                  <option value="startup">🚀 Startups & Business</option>
                  <option value="cybersec">🔒 Cybersécurité</option>
                  <option value="mobile">📱 Mobile & Télécoms</option>
                </select>
                <div v-if="form.errors.tag" class="text-danger small mt-1">{{ form.errors.tag }}</div>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold text-dark">
                  Lien source ou ressource (facultatif)
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-link-45deg"></i></span>
                  <input 
                    type="url" 
                    v-model="form.link_url" 
                    class="form-control rounded-end-3" 
                    placeholder="https://github.com/... ou https://monsite.cm"
                  />
                </div>
                <div v-if="form.errors.link_url" class="text-danger small mt-1">{{ form.errors.link_url }}</div>
              </div>
            </div>

            <!-- Cover Image Upload -->
            <div class="mb-4">
              <label class="form-label fw-bold text-dark">
                Image de couverture principale (facultatif)
              </label>
              <div class="d-flex align-items-center gap-3">
                <input 
                  type="file" 
                  accept="image/*" 
                  class="form-control rounded-3" 
                  @change="handleCoverChange"
                />
              </div>
              <div v-if="coverPreview" class="mt-2 position-relative d-inline-block">
                <img :src="coverPreview" class="rounded-3 shadow-sm border object-fit-cover" style="max-height: 140px; max-width: 240px;" alt="Aperçu">
                <button type="button" @click="removeCover" class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                  <i class="bi bi-x"></i>
                </button>
              </div>
              <small class="text-muted d-block mt-1">Formats acceptés : JPG, PNG, WebP (Max 4 Mo). Une belle image augmente la visibilité.</small>
            </div>

            <!-- WYSIWYG EDITOR -->
            <div class="mb-4">
              <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center">
                <span>Contenu détaillé &amp; Mise en forme (WYSIWYG) <span class="text-danger">*</span></span>
                <span class="badge bg-light text-muted border fw-normal">
                  <i class="bi bi-youtube text-danger me-1"></i> Vidéos &bull;
                  <i class="bi bi-image text-success me-1"></i> Images PC &bull;
                  <i class="bi bi-list-ul me-1"></i> Listes à puces
                </span>
              </label>
              
              <!-- Component WysiwygEditor -->
              <WysiwygEditor 
                v-model="form.content" 
                placeholder="Rédigez votre mini-actualité ici. Vous pouvez formater le texte en gras ou italique, faire des listes à puces, insérer des vidéos YouTube ou téléverser des images depuis votre ordinateur..." 
                min-height="340px"
              />
              <div v-if="form.errors.content" class="text-danger small mt-1">{{ form.errors.content }}</div>
            </div>

            <!-- Author Name for Guests -->
            <div v-if="!user" class="mb-4 p-3 bg-light rounded-4 border">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-person-fill text-danger"></i>
                <label class="form-label fw-bold text-dark mb-0">Votre Nom ou Pseudo</label>
              </div>
              <input 
                type="text" 
                v-model="form.author_name" 
                class="form-control rounded-3" 
                placeholder="ex: Alex Dev"
              />
              <small class="text-muted d-block mt-1">
                Astuce : <Link href="/login" class="text-decoration-underline text-dark fw-bold">Connectez-vous</Link> pour lier ce POC à votre profil et gagner +5 XP.
              </small>
            </div>

            <!-- Submit Button & Info -->
            <div class="pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="text-muted small">
                <i class="bi bi-shield-check text-success me-1"></i> Publication immédiate &bull; Modération après coup par les administrateurs
              </div>
              <button 
                type="submit" 
                class="btn rounded-pill px-5 py-2 fw-bold text-white shadow-sm d-flex align-items-center gap-2"
                style="background-color: var(--tech-green, #00A859);"
                :disabled="form.processing || !form.title || !form.content"
              >
                <span v-if="form.processing" class="spinner-border spinner-border-sm"></span>
                <i v-else class="bi bi-send-fill"></i>
                <span>Publier mon POC maintenant</span>
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
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import WysiwygEditor from '@/Components/WysiwygEditor.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)

const form = useForm({
  title: '',
  tag: 'general',
  link_url: '',
  content: '',
  cover_image: null,
  author_name: '',
})

const coverPreview = ref(null)

function handleCoverChange(e) {
  const file = e.target.files?.[0]
  if (file) {
    form.cover_image = file
    coverPreview.value = URL.createObjectURL(file)
  }
}

function removeCover() {
  form.cover_image = null
  coverPreview.value = null
}

function submitPoc() {
  if (!form.title || !form.content) return

  form.post('/pocs', {
    preserveScroll: true,
  })
}
</script>
