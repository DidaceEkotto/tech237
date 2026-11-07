<template>
  <FrontLayout>
    <Head>
      <title>Galerie Photos du Projet : {{ project.name }} | Espace Utilisateur</title>
    </Head>

    <div class="project-gallery-page py-5">
      <div class="container">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><Link href="/espace-utilisateur" class="text-decoration-none text-muted">Espace Utilisateur</Link></li>
                <li class="breadcrumb-item"><Link href="/espace-utilisateur?section=projects" class="text-decoration-none text-muted">Mes Projets</Link></li>
                <li class="breadcrumb-item active">{{ project.name }}</li>
              </ol>
            </nav>
            <h2 class="fw-black text-dark mb-0">Galerie Photos & Captures d'écran</h2>
            <p class="text-muted small mb-0">{{ project.name }} &bull; Gérez les photos affichées dans le carrousel immersif public.</p>
          </div>

          <Link href="/espace-utilisateur?section=projects" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Retour aux Projets
          </Link>
        </div>

        <!-- Success notification -->
        <div v-if="$page.props.flash?.success" class="alert alert-success rounded-4 small p-3 mb-4">
          <i class="bi bi-check-circle-fill me-1"></i> {{ $page.props.flash.success }}
        </div>

        <!-- Dropzone Upload Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-5 bg-white text-center">
          <div 
            class="dropzone-area p-4 border border-2 border-dashed rounded-4 cursor-pointer"
            :class="{ 'border-danger bg-danger-subtle': isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
          >
            <i class="bi bi-cloud-arrow-up-fill fs-1 text-danger d-block mb-2"></i>
            <h5 class="fw-bold text-dark mb-1">Glissez-déposez une photo ici ou cliquez pour parcourir</h5>
            <p class="text-muted small mb-3">Formats supportés : JPEG, PNG, WEBP (Max 10 Mo par photo).</p>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold">
              Sélectionner une photo
            </button>
            <input type="file" ref="fileInput" @change="handleFileSelect" class="d-none" accept="image/*">
          </div>

          <!-- Preview & Upload Button -->
          <div v-if="selectedFile" class="mt-4 p-3 bg-light rounded-4 text-start d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <img :src="filePreview" class="rounded-3 shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
              <div>
                <strong class="d-block text-dark">{{ selectedFile.name }}</strong>
                <small class="text-muted">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} Mo</small>
              </div>
            </div>

            <div class="d-flex align-items-center gap-2">
              <input type="text" v-model="captionText" class="form-control form-control-sm" placeholder="Légende (ex: Tableau de bord)" style="min-width: 200px;">
              <button @click="uploadImage" :disabled="isUploading" class="btn btn-melty btn-sm rounded-pill px-4">
                <i class="bi bi-upload me-1"></i> Téléverser
              </button>
              <button @click="cancelSelected" class="btn btn-light btn-sm rounded-pill px-3 border">
                Annuler
              </button>
            </div>
          </div>
        </div>

        <!-- Photos Gallery Grid -->
        <h4 class="fw-bold text-dark mb-3">
          <i class="bi bi-images text-danger me-2"></i>Photos de la galerie ({{ project.images?.length || 0 }})
        </h4>

        <div class="row g-4 mb-5">
          <div v-for="img in project.images" :key="img.id" class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
              <img :src="'/' + img.image_path" class="card-img-top" style="height: 220px; object-fit: cover;">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <small class="text-dark fw-bold d-block">{{ img.caption || 'Sans légende' }}</small>
                  <small class="text-muted">Ordre : {{ img.order }}</small>
                </div>
                <button @click="confirmDelete(img)" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Supprimer de la galerie">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>

          <div v-if="!project.images || project.images.length === 0" class="col-12 text-center py-5 bg-white rounded-4 border">
            <i class="bi bi-image fs-1 text-muted d-block mb-2"></i>
            <h5 class="fw-bold text-dark">Aucune photo dans la galerie de ce projet.</h5>
            <p class="text-muted small">Utilisez la zone de dépôt ci-dessus pour ajouter vos premières captures d'écran.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL (Without Page Reload via Inertia) -->
    <div v-if="imageToDelete" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Confirmer la suppression</h5>
            <button type="button" class="btn-close" @click="imageToDelete = null"></button>
          </div>
          <div class="modal-body p-4 text-center">
            <img :src="'/' + imageToDelete.image_path" class="rounded-3 mb-3 shadow-sm" style="max-height: 140px; max-width: 100%; object-fit: cover;">
            <p class="mb-0 text-muted">Voulez-vous vraiment retirer cette image de la galerie du projet ? Cette action est irréversible.</p>
          </div>
          <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
            <button type="button" class="btn btn-light rounded-pill px-4" @click="imageToDelete = null">Annuler</button>
            <button type="button" @click="deleteConfirmed" class="btn btn-danger rounded-pill px-4">
              <i class="bi bi-trash me-1"></i> Supprimer définitivement
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
  project: Object,
})

const fileInput = ref(null)
const isDragging = ref(false)
const selectedFile = ref(null)
const filePreview = ref(null)
const captionText = ref('')
const isUploading = ref(false)
const imageToDelete = ref(null)

function triggerFileInput() {
  fileInput.value.click()
}

function handleFileSelect(e) {
  if (e.target.files.length > 0) {
    setFile(e.target.files[0])
  }
}

function handleDrop(e) {
  isDragging.value = false
  if (e.dataTransfer.files.length > 0) {
    setFile(e.dataTransfer.files[0])
  }
}

function setFile(file) {
  selectedFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    filePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

function cancelSelected() {
  selectedFile.value = null
  filePreview.value = null
  captionText.value = ''
  if (fileInput.value) fileInput.value.value = ''
}

function uploadImage() {
  if (!selectedFile.value) return
  isUploading.value = true

  const data = new FormData()
  data.append('image', selectedFile.value)
  if (captionText.value) {
    data.append('caption', captionText.value)
  }

  router.post(`/espace-utilisateur/projets/${props.project.id}/galerie`, data, {
    onSuccess: () => {
      cancelSelected()
      isUploading.value = false
    },
    onError: () => {
      isUploading.value = false
    }
  })
}

function confirmDelete(img) {
  imageToDelete.value = img
}

function deleteConfirmed() {
  if (!imageToDelete.value) return
  router.delete(`/espace-utilisateur/projets/images/${imageToDelete.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      imageToDelete.value = null
    }
  })
}
</script>

<style scoped>
.dropzone-area {
  background-color: #fafafa;
  transition: all 0.2s ease;
}
.dropzone-area:hover {
  background-color: #f5f5f5;
  border-color: #FF004B !important;
}
</style>
