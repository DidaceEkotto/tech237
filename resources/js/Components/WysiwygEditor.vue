<template>
  <div class="wysiwyg-wrapper border rounded-4 overflow-hidden bg-white shadow-sm">
    <!-- TOOLBAR -->
    <div class="wysiwyg-toolbar p-2 bg-light border-bottom d-flex flex-wrap align-items-center gap-1">
      
      <!-- Format Dropdown -->
      <select 
        class="form-select form-select-sm border-secondary-subtle" 
        style="width: 140px; font-weight: 500;" 
        @change="applyFormatBlock($event.target.value); $event.target.value = ''"
      >
        <option value="" disabled selected>Format texte</option>
        <option value="p">Texte normal (P)</option>
        <option value="h2">Grand Titre (H2)</option>
        <option value="h3">Sous-titre (H3)</option>
        <option value="h4">Titre section (H4)</option>
        <option value="blockquote">Citation (Quote)</option>
        <option value="pre">Bloc de code</option>
      </select>

      <div class="vr mx-1"></div>

      <!-- Bold, Italic, Underline, Strike -->
      <div class="btn-group btn-group-sm">
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Gras (Ctrl+B)" 
          @click="execCmd('bold')"
        >
          <i class="bi bi-type-bold fw-bold"></i>
        </button>
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Italique (Ctrl+I)" 
          @click="execCmd('italic')"
        >
          <i class="bi bi-type-italic"></i>
        </button>
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Souligné (Ctrl+U)" 
          @click="execCmd('underline')"
        >
          <i class="bi bi-type-underline"></i>
        </button>
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Barré" 
          @click="execCmd('strikeThrough')"
        >
          <i class="bi bi-type-strikethrough"></i>
        </button>
      </div>

      <div class="vr mx-1"></div>

      <!-- Alignment -->
      <div class="btn-group btn-group-sm">
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Aligner à gauche" 
          @click="execCmd('justifyLeft')"
        >
          <i class="bi bi-text-left"></i>
        </button>
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Centrer" 
          @click="execCmd('justifyCenter')"
        >
          <i class="bi bi-text-center"></i>
        </button>
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Aligner à droite" 
          @click="execCmd('justifyRight')"
        >
          <i class="bi bi-text-right"></i>
        </button>
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Justifier" 
          @click="execCmd('justifyFull')"
        >
          <i class="bi bi-justify"></i>
        </button>
      </div>

      <div class="vr mx-1"></div>

      <!-- Lists -->
      <div class="btn-group btn-group-sm">
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Liste à puces" 
          @click="execCmd('insertUnorderedList')"
        >
          <i class="bi bi-list-ul"></i>
        </button>
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          title="Liste numérotée" 
          @click="execCmd('insertOrderedList')"
        >
          <i class="bi bi-list-ol"></i>
        </button>
      </div>

      <div class="vr mx-1"></div>

      <!-- Table Insert -->
      <button 
        type="button" 
        class="btn btn-sm btn-outline-secondary" 
        title="Insérer un tableau" 
        @click="showTableModal = true"
      >
        <i class="bi bi-table text-primary"></i> <span class="d-none d-md-inline ms-1">Tableau</span>
      </button>

      <!-- YouTube Embed -->
      <button 
        type="button" 
        class="btn btn-sm btn-outline-secondary" 
        title="Insérer une vidéo YouTube" 
        @click="showYoutubeModal = true"
      >
        <i class="bi bi-youtube text-danger"></i> <span class="d-none d-md-inline ms-1">YouTube</span>
      </button>

      <!-- Image Insert (From PC / Word style or URL) -->
      <div class="dropdown">
        <button 
          type="button" 
          class="btn btn-sm btn-outline-secondary dropdown-toggle" 
          data-bs-toggle="dropdown"
          title="Insérer une image"
        >
          <i class="bi bi-image text-success"></i> <span class="d-none d-md-inline ms-1">Image</span>
        </button>
        <ul class="dropdown-menu shadow border-0 mt-1">
          <li>
            <label class="dropdown-item cursor-pointer d-flex align-items-center gap-2 mb-0">
              <i class="bi bi-laptop text-primary"></i>
              <span>Choisir depuis l'ordinateur (comme sur Word)</span>
              <input type="file" accept="image/*" class="d-none" @change="handleLocalImageUpload" />
            </label>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-2" href="#" @click.prevent="showImageUrlModal = true">
              <i class="bi bi-link-45deg text-secondary"></i>
              <span>Insérer via un lien URL</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Link & HR -->
      <button 
        type="button" 
        class="btn btn-sm btn-outline-secondary" 
        title="Insérer un lien" 
        @click="insertLink"
      >
        <i class="bi bi-link"></i>
      </button>
      <button 
        type="button" 
        class="btn btn-sm btn-outline-secondary" 
        title="Ligne horizontale" 
        @click="execCmd('insertHorizontalRule')"
      >
        <i class="bi bi-hr"></i>
      </button>

      <div class="ms-auto d-flex align-items-center gap-1">
        <!-- Undo / Redo -->
        <button type="button" class="btn btn-sm btn-outline-secondary" title="Annuler" @click="execCmd('undo')">
          <i class="bi bi-arrow-counterclockwise"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" title="Rétablir" @click="execCmd('redo')">
          <i class="bi bi-arrow-clockwise"></i>
        </button>

        <!-- HTML Code Toggle -->
        <button 
          type="button" 
          class="btn btn-sm" 
          :class="isHtmlMode ? 'btn-danger' : 'btn-outline-dark'" 
          title="Basculer en mode code HTML" 
          @click="toggleHtmlMode"
        >
          <i class="bi bi-code-slash"></i>
        </button>
      </div>

    </div>

    <!-- VISUAL EDITING AREA -->
    <div 
      v-show="!isHtmlMode"
      ref="editorRef"
      class="wysiwyg-content p-4" 
      contenteditable="true"
      :style="{ minHeight: minHeight || '320px', outline: 'none' }"
      @input="handleInput"
      @blur="handleInput"
      @paste="handlePaste"
    ></div>

    <!-- RAW HTML SOURCE CODE MODE -->
    <textarea 
      v-show="isHtmlMode"
      v-model="rawHtml"
      class="form-control font-monospace border-0 p-4 rounded-0"
      :style="{ minHeight: minHeight || '320px', fontSize: '13px', background: '#1e1e2f', color: '#f8fafc' }"
      @input="handleRawHtmlInput"
    ></textarea>

    <!-- FOOTER HELPER -->
    <div class="p-2 px-3 bg-light border-top d-flex justify-content-between align-items-center text-muted small">
      <span>
        <i class="bi bi-check-circle-fill text-success me-1"></i>
        {{ isHtmlMode ? 'Mode Code Source HTML' : 'Éditeur visuel interactif Word/WYSIWYG' }}
      </span>
      <span>
        {{ wordCount }} mots &bull; {{ charCount }} caractères
      </span>
    </div>

    <!-- MODAL INSERT TABLE -->
    <div v-if="showTableModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold"><i class="bi bi-table text-primary me-2"></i>Insérer un tableau</h6>
            <button type="button" class="btn-close" @click="showTableModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label small fw-bold">Nombre de colonnes</label>
              <input type="number" v-model.number="tableCols" min="1" max="10" class="form-control form-control-sm" />
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Nombre de lignes</label>
              <input type="number" v-model.number="tableRows" min="1" max="30" class="form-control form-control-sm" />
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="tableHeader" v-model="tableWithHeader">
              <label class="form-check-label small" for="tableHeader">Inclure une ligne d'en-tête</label>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm rounded-pill px-3" @click="showTableModal = false">Annuler</button>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" @click="insertTable">Insérer</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL INSERT YOUTUBE -->
    <div v-if="showYoutubeModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold"><i class="bi bi-youtube text-danger me-2"></i>Insérer une vidéo YouTube</h6>
            <button type="button" class="btn-close" @click="showYoutubeModal = false"></button>
          </div>
          <div class="modal-body">
            <label class="form-label small fw-bold">Lien ou URL de la vidéo YouTube <span class="text-danger">*</span></label>
            <input 
              type="text" 
              v-model="youtubeUrlInput" 
              class="form-control" 
              placeholder="ex: https://www.youtube.com/watch?v=dQw4w9WgXcQ ou https://youtu.be/..." 
              @keyup.enter="insertYoutube"
            />
            <small class="text-muted d-block mt-2">
              La vidéo sera intégrée directement avec le lecteur interactif YouTube et pourra être visualisée par les lecteurs du projet.
            </small>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm rounded-pill px-3" @click="showYoutubeModal = false">Annuler</button>
            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" @click="insertYoutube">Intégrer la vidéo</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL INSERT IMAGE VIA URL -->
    <div v-if="showImageUrlModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold"><i class="bi bi-image text-success me-2"></i>Insérer une image par lien URL</h6>
            <button type="button" class="btn-close" @click="showImageUrlModal = false"></button>
          </div>
          <div class="modal-body">
            <label class="form-label small fw-bold">Adresse URL de l'image <span class="text-danger">*</span></label>
            <input 
              type="text" 
              v-model="imageUrlInput" 
              class="form-control" 
              placeholder="https://domaine.com/images/screenshot.png"
              @keyup.enter="insertImageUrl"
            />
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm rounded-pill px-3" @click="showImageUrlModal = false">Annuler</button>
            <button type="button" class="btn btn-success btn-sm rounded-pill px-3" @click="insertImageUrl">Insérer</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Rédigez votre texte ici...',
  },
  minHeight: {
    type: String,
    default: '320px',
  }
})

const emit = defineEmits(['update:modelValue'])

const editorRef = ref(null)
const isHtmlMode = ref(false)
const rawHtml = ref('')

// Table insertion state
const showTableModal = ref(false)
const tableRows = ref(3)
const tableCols = ref(3)
const tableWithHeader = ref(true)

// YouTube modal state
const showYoutubeModal = ref(false)
const youtubeUrlInput = ref('')

// Image URL modal state
const showImageUrlModal = ref(false)
const imageUrlInput = ref('')

onMounted(() => {
  if (editorRef.value) {
    editorRef.value.innerHTML = props.modelValue || ''
    rawHtml.value = props.modelValue || ''
  }
})

watch(() => props.modelValue, (newVal) => {
  if (editorRef.value && editorRef.value.innerHTML !== newVal) {
    // Only update if not actively focused to preserve cursor
    if (document.activeElement !== editorRef.value) {
      editorRef.value.innerHTML = newVal || ''
    }
  }
  rawHtml.value = newVal || ''
})

function handleInput() {
  if (!editorRef.value) return
  const html = editorRef.value.innerHTML
  rawHtml.value = html
  emit('update:modelValue', html)
}

function handleRawHtmlInput() {
  if (editorRef.value) {
    editorRef.value.innerHTML = rawHtml.value
  }
  emit('update:modelValue', rawHtml.value)
}

function toggleHtmlMode() {
  isHtmlMode.value = !isHtmlMode.value
  if (!isHtmlMode.value && editorRef.value) {
    editorRef.value.innerHTML = rawHtml.value
  } else {
    if (editorRef.value) {
      rawHtml.value = editorRef.value.innerHTML
    }
  }
}

function execCmd(command, value = null) {
  if (isHtmlMode.value) return
  editorRef.value?.focus()
  document.execCommand(command, false, value)
  handleInput()
}

function applyFormatBlock(tag) {
  if (!tag) return
  execCmd('formatBlock', tag)
}

function insertLink() {
  const url = prompt('Entrez l\'adresse URL du lien (ex: https://example.com) :')
  if (url) {
    execCmd('createLink', url)
  }
}

// TABLE INSERTION
function insertTable() {
  const rows = Math.max(1, tableRows.value || 3)
  const cols = Math.max(1, tableCols.value || 3)
  
  let tableHtml = '<div class="table-responsive my-3"><table class="table table-bordered table-striped align-middle">'
  
  if (tableWithHeader.value) {
    tableHtml += '<thead class="table-dark"><tr>'
    for (let c = 1; c <= cols; c++) {
      tableHtml += `<th>Colonne ${c}</th>`
    }
    tableHtml += '</tr></thead>'
  }

  tableHtml += '<tbody>'
  for (let r = 1; r <= rows; r++) {
    tableHtml += '<tr>'
    for (let c = 1; c <= cols; c++) {
      tableHtml += `<td>Cellule ${r}-${c}</td>`
    }
    tableHtml += '</tr>'
  }
  tableHtml += '</tbody></table></div><p><br></p>'

  insertHtmlAtCursor(tableHtml)
  showTableModal.value = false
}

// YOUTUBE VIDEO EMBED
function extractYoutubeId(url) {
  if (!url) return null
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/
  const match = url.match(regExp)
  return (match && match[2].length === 11) ? match[2] : null
}

function insertYoutube() {
  const videoId = extractYoutubeId(youtubeUrlInput.value.trim())
  if (!videoId) {
    alert('Veuillez entrer une URL YouTube valide (ex: https://www.youtube.com/watch?v=...)')
    return
  }

  const embedHtml = `
    <div class="ratio ratio-16x9 my-3 shadow-sm rounded-4 overflow-hidden border">
      <iframe 
        src="https://www.youtube.com/embed/${videoId}" 
        title="YouTube video player" 
        frameborder="0" 
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
        allowfullscreen>
      </iframe>
    </div>
    <p><br></p>
  `
  insertHtmlAtCursor(embedHtml)
  youtubeUrlInput.value = ''
  showYoutubeModal.value = false
}

// LOCAL IMAGE UPLOAD (WORD STYLE) -> saves to storage/images/
async function handleLocalImageUpload(event) {
  const file = event.target.files?.[0]
  if (!file) return

  const formData = new FormData()
  formData.append('image', file)

  // Grab CSRF token if available
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

  try {
    const res = await fetch('/api/wysiwyg-upload', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
      }
    })

    if (res.ok) {
      const data = await res.json()
      if (data.url) {
        const imgHtml = `
          <figure class="my-3 text-center">
            <img 
              src="${data.url}" 
              class="img-fluid rounded-4 shadow-sm d-block mx-auto" 
              alt="${file.name}" 
              style="max-height: 480px; object-fit: contain;" 
            />
            <figcaption class="text-muted small mt-1">${file.name}</figcaption>
          </figure>
          <p><br></p>
        `
        insertHtmlAtCursor(imgHtml)
        event.target.value = ''
        return
      }
    }
  } catch (err) {
    console.warn('Upload server-side failed, fallback to local data URL', err)
  }

  // Fallback to local DataURL for full offline capability
  const reader = new FileReader()
  reader.onload = (e) => {
    const base64Data = e.target.result
    const imgHtml = `
      <figure class="my-3 text-center">
        <img 
          src="${base64Data}" 
          class="img-fluid rounded-4 shadow-sm d-block mx-auto" 
          alt="${file.name}" 
          style="max-height: 480px; object-fit: contain;" 
        />
        <figcaption class="text-muted small mt-1">${file.name}</figcaption>
      </figure>
      <p><br></p>
    `
    insertHtmlAtCursor(imgHtml)
    event.target.value = ''
  }
  reader.readAsDataURL(file)
}

// IMAGE VIA URL
function insertImageUrl() {
  const url = imageUrlInput.value.trim()
  if (!url) return

  const imgHtml = `
    <figure class="my-3 text-center">
      <img 
        src="${url}" 
        class="img-fluid rounded-4 shadow-sm d-block mx-auto" 
        alt="Image" 
        style="max-height: 480px; object-fit: contain;" 
      />
    </figure>
    <p><br></p>
  `
  insertHtmlAtCursor(imgHtml)
  imageUrlInput.value = ''
  showImageUrlModal.value = false
}

// HELPER: Insert HTML into contenteditable at current selection
function insertHtmlAtCursor(html) {
  if (isHtmlMode.value) {
    rawHtml.value += html
    handleRawHtmlInput()
    return
  }

  editorRef.value?.focus()
  const sel = window.getSelection()
  if (sel && sel.getRangeAt && sel.rangeCount) {
    const range = sel.getRangeAt(0)
    range.deleteContents()

    const el = document.createElement('div')
    el.innerHTML = html
    const frag = document.createDocumentFragment()
    let node
    let lastNode
    while ((node = el.firstChild)) {
      lastNode = frag.appendChild(node)
    }
    range.insertNode(frag)

    if (lastNode) {
      const newRange = range.cloneRange()
      newRange.setStartAfter(lastNode)
      newRange.collapse(true)
      sel.removeAllRanges()
      sel.addRange(newRange)
    }
  } else {
    // Fallback: append to end
    editorRef.value.innerHTML += html
  }
  handleInput()
}

// Clean paste
function handlePaste(e) {
  // Allow default rich paste or images from clipboard if user copies from Word or browser
}

const wordCount = computed(() => {
  const text = (editorRef.value?.innerText || '').trim()
  if (!text) return 0
  return text.split(/\s+/).filter(Boolean).length
})

const charCount = computed(() => {
  return (editorRef.value?.innerText || '').length
})
</script>

<style>
.wysiwyg-content {
  font-family: inherit;
  font-size: 15px;
  line-height: 1.7;
  color: #334155;
}
.wysiwyg-content:focus {
  outline: none;
}
.wysiwyg-content p {
  margin-bottom: 0.8rem;
}
.wysiwyg-content h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-top: 1.5rem;
  margin-bottom: 0.8rem;
  color: #0f172a;
}
.wysiwyg-content h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 1.2rem;
  margin-bottom: 0.6rem;
  color: #1e293b;
}
.wysiwyg-content h4 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}
.wysiwyg-content blockquote {
  border-left: 4px solid #FF004B;
  padding-left: 1rem;
  margin-left: 0;
  color: #64748b;
  font-style: italic;
}
.wysiwyg-content pre {
  background: #f1f5f9;
  border-radius: 8px;
  padding: 1rem;
  font-family: monospace;
  overflow-x: auto;
}
.wysiwyg-content table {
  width: 100%;
  margin: 1rem 0;
  border-collapse: collapse;
}
.wysiwyg-content table th, 
.wysiwyg-content table td {
  border: 1px solid #cbd5e1;
  padding: 8px 12px;
}
.wysiwyg-content img {
  max-width: 100%;
  height: auto;
  border-radius: 12px;
}
.wysiwyg-content .ratio {
  position: relative;
  width: 100%;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
