<template>
  <FrontLayout>
    <Head :title="'Éditeur • ' + form.title + ' • Tech237 Forms'" />

    <div class="forms-edit-page py-4 bg-slate-50 min-vh-100" style="background-color: #f8fafc;">
      <div class="container-fluid px-lg-5">
        
        <!-- Top Toolbar -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 bg-white p-3 rounded-4 shadow-sm border">
          <div class="d-flex align-items-center gap-3">
            <Link href="/espace-utilisateur/formulaires" class="btn btn-sm btn-light rounded-circle p-2 text-muted" title="Retour aux formulaires">
              <i class="bi bi-arrow-left fs-5"></i>
            </Link>

            <div>
              <div class="d-flex align-items-center gap-2">
                <input 
                  type="text" 
                  v-model="form.title" 
                  :disabled="isPublished"
                  class="form-control form-control-sm fw-black border-0 px-1 fs-5 text-dark edit-title-input" 
                  placeholder="Titre du formulaire..." 
                />
                <span class="badge rounded-pill px-2 py-1 extra-small" :class="form.status === 'published' ? 'bg-success' : 'bg-secondary'">
                  {{ form.status === 'published' ? 'En ligne' : (form.status === 'draft' ? 'Brouillon' : 'Fermé') }}
                </span>
              </div>
              <div class="d-flex align-items-center gap-2 extra-small text-muted ps-1">
                <span>Lien : <code>/f/{{ form.slug }}</code></span>
                <span>•</span>
                <span>{{ form.fields.length }} question(s)</span>
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Public Preview Link -->
            <a :href="`/f/${form.slug}`" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1">
              <i class="bi bi-box-arrow-up-right"></i>
              <span>Aperçu public</span>
            </a>

            <!-- QR Code Button -->
            <button type="button" @click="showQrModal = true" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1">
              <i class="bi bi-qr-code"></i>
              <span>QR Code</span>
            </button>

            <!-- Responses Tab -->
            <Link :href="`/espace-utilisateur/formulaires/${form.id}/reponses`" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1">
              <i class="bi bi-bar-chart-fill"></i>
              <span>Réponses</span>
            </Link>

            <!-- Action buttons depending on published status -->
            <template v-if="isPublished">
              <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1">
                <i class="bi bi-lock-fill text-warning"></i> En ligne (Verrouillé)
              </span>
              <button 
                type="button" 
                @click="duplicateForm" 
                class="btn btn-sm btn-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm"
                title="Dupliquer ce formulaire pour créer une nouvelle version modifiable"
              >
                <i class="bi bi-copy"></i>
                <span>Dupliquer</span>
              </button>
            </template>

            <template v-else-if="form.status === 'draft'">
              <button 
                type="button" 
                @click="publishForm" 
                :disabled="isSaving" 
                class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
                title="Mettre en ligne pour commencer à recevoir des réponses"
              >
                <span v-if="isSaving" class="spinner-border spinner-border-sm" role="status"></span>
                <i v-else class="bi bi-cloud-upload-fill"></i>
                <span>Mettre en ligne</span>
              </button>
              <button 
                type="button" 
                @click="saveForm" 
                :disabled="isSaving" 
                class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold text-dark"
                title="Enregistrer les modifications en brouillon"
              >
                <span>Enregistrer brouillon</span>
              </button>
            </template>

            <template v-else>
              <button 
                type="button" 
                @click="toggleStatus('published')" 
                class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1"
              >
                <i class="bi bi-arrow-repeat"></i>
                <span>Rouvrir le formulaire</span>
              </button>
              <button 
                type="button" 
                @click="duplicateForm" 
                class="btn btn-sm btn-dark rounded-pill px-3 fw-bold"
              >
                <i class="bi bi-copy me-1"></i> Dupliquer
              </button>
            </template>
          </div>
        </div>

        <!-- Locked Banner if Published -->
        <div v-if="isPublished" class="alert alert-warning border-2 border-warning rounded-4 shadow-sm p-3 p-md-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
              <i class="bi bi-lock-fill fs-4"></i>
            </div>
            <div>
              <strong class="d-block text-dark">Formulaire mis en ligne • Modifications verrouillées</strong>
              <div class="small text-muted">
                Ce formulaire est actuellement en ligne. Sa structure et ses questions sont verrouillées en lecture seule pour préserver l'intégrité des réponses des participants. Pour apporter des changements, dupliquez ce formulaire.
              </div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button type="button" @click="duplicateForm" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
              <i class="bi bi-copy"></i>
              <span>Dupliquer pour modifier</span>
            </button>
            <button type="button" @click="toggleStatus('closed')" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">
              Fermer le formulaire
            </button>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3">
          <li class="nav-item">
            <button 
              class="nav-link rounded-pill fw-bold small px-4 d-inline-flex align-items-center gap-2"
              :class="activeTab === 'fields' ? 'active bg-dark text-white' : 'text-muted hover-bg-light'"
              @click="activeTab = 'fields'"
            >
              <i class="bi bi-ui-checks"></i>
              <span>Questions &amp; Champs ({{ form.fields.length }})</span>
            </button>
          </li>
          <li class="nav-item">
            <button 
              class="nav-link rounded-pill fw-bold small px-4 d-inline-flex align-items-center gap-2"
              :class="activeTab === 'settings' ? 'active bg-dark text-white' : 'text-muted hover-bg-light'"
              @click="activeTab = 'settings'"
            >
              <i class="bi bi-gear-fill"></i>
              <span>Paramètres &amp; Règles</span>
            </button>
          </li>
          <li class="nav-item">
            <button 
              class="nav-link rounded-pill fw-bold small px-4 d-inline-flex align-items-center gap-2"
              :class="activeTab === 'notifications' ? 'active bg-dark text-white' : 'text-muted hover-bg-light'"
              @click="activeTab = 'notifications'"
            >
              <i class="bi bi-envelope-fill"></i>
              <span>Notifications Email</span>
            </button>
          </li>
          <li class="nav-item">
            <button 
              class="nav-link rounded-pill fw-bold small px-4 d-inline-flex align-items-center gap-2"
              :class="activeTab === 'share' ? 'active bg-dark text-white' : 'text-muted hover-bg-light'"
              @click="activeTab = 'share'"
            >
              <i class="bi bi-share-fill"></i>
              <span>Partage &amp; QR Code</span>
            </button>
          </li>
        </ul>

        <!-- TAB 1: FIELDS & QUESTIONS -->
        <div v-show="activeTab === 'fields'">
          <div class="row g-4">
            
            <!-- Left: Fields List -->
            <div class="col-lg-8">
              
              <!-- Form Header Description Card -->
              <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4">
                <div class="mb-3">
                  <label class="form-label small fw-bold text-dark">Consignes ou description d'en-tête</label>
                  <textarea 
                    v-model="form.description" 
                    :disabled="isPublished"
                    rows="2" 
                    class="form-control rounded-3" 
                    placeholder="Expliquez à vos répondants l'objectif de ce formulaire..."
                  ></textarea>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top extra-small text-muted">
                  <span>Mode actif : <strong>{{ form.display_mode === 'conversation' ? 'Conversationnel (Typeform)' : 'Classique (Page complète)' }}</strong></span>
                  <button type="button" @click="activeTab = 'settings'" class="btn btn-link p-0 text-success extra-small fw-bold text-decoration-none">
                    Changer le mode d'affichage ➔
                  </button>
                </div>
              </div>

              <!-- List of Questions -->
              <div v-if="form.fields.length > 0" class="d-flex flex-column gap-3 mb-4">
                <div 
                  v-for="(field, idx) in form.fields" 
                  :key="field.id || idx"
                  class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden transition field-card"
                  :class="{ 'border-2 border-primary': activeEditingIdx === idx }"
                >
                  <div class="card-body p-4">
                    
                    <!-- Question Header & Actions -->
                    <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark text-white rounded-pill px-2 py-1 extra-small fw-bold">
                          #{{ idx + 1 }}
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1 extra-small fw-semibold d-inline-flex align-items-center gap-1">
                          <i :class="getFieldTypeIcon(field.type)"></i>
                          {{ getFieldTypeLabel(field.type) }}
                        </span>
                        <span v-if="field.is_required" class="badge bg-danger-subtle text-danger extra-small rounded-pill">
                          Obligatoire
                        </span>
                        <span v-if="field.conditional_logic?.enabled" class="badge bg-info-subtle text-info extra-small rounded-pill">
                          <i class="bi bi-diagram-3-fill me-1"></i> Logique active
                        </span>
                      </div>

                      <div v-if="!isPublished" class="d-flex align-items-center gap-1">
                        <!-- Move Up -->
                        <button 
                          type="button" 
                          :disabled="idx === 0" 
                          @click="moveField(idx, -1)" 
                          class="btn btn-xs btn-light rounded-circle p-1" 
                          title="Monter"
                        >
                          <i class="bi bi-arrow-up"></i>
                        </button>
                        <!-- Move Down -->
                        <button 
                          type="button" 
                          :disabled="idx === form.fields.length - 1" 
                          @click="moveField(idx, 1)" 
                          class="btn btn-xs btn-light rounded-circle p-1" 
                          title="Descendre"
                        >
                          <i class="bi bi-arrow-down"></i>
                        </button>
                        <!-- Duplicate -->
                        <button 
                          type="button" 
                          @click="duplicateField(idx)" 
                          class="btn btn-xs btn-light rounded-circle p-1 ms-1" 
                          title="Dupliquer la question"
                        >
                          <i class="bi bi-copy"></i>
                        </button>
                        <!-- Delete -->
                        <button 
                          type="button" 
                          @click="removeField(idx)" 
                          class="btn btn-xs btn-light text-danger rounded-circle p-1 ms-1" 
                          title="Supprimer la question"
                        >
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </div>

                    <!-- Label Input -->
                    <div class="mb-3">
                      <label class="form-label extra-small fw-bold text-dark mb-1">Intitulé de la question <span class="text-danger">*</span></label>
                      <input 
                        type="text" 
                        v-model="field.label" 
                        :disabled="isPublished"
                        class="form-control fw-bold rounded-3" 
                        placeholder="ex: Quel est votre nom complet ?" 
                      />
                    </div>

                    <!-- Options for Select, Radio, Checkbox -->
                    <div v-if="['select', 'radio', 'checkbox'].includes(field.type)" class="mb-3 p-3 bg-light rounded-3 border">
                      <label class="form-label extra-small fw-bold text-dark mb-2 d-flex justify-content-between align-items-center">
                        <span>Options de choix :</span>
                        <button v-if="!isPublished" type="button" @click="addOption(field)" class="btn btn-xs btn-outline-success rounded-pill px-2">
                          + Ajouter une option
                        </button>
                      </label>
                      
                      <div class="d-flex flex-column gap-2">
                        <div v-for="(opt, optIdx) in (field.options || [])" :key="optIdx" class="d-flex align-items-center gap-2">
                          <span class="extra-small text-muted">{{ optIdx + 1 }}.</span>
                          <input 
                            type="text" 
                            v-model="field.options[optIdx]" 
                            :disabled="isPublished"
                            class="form-control form-control-sm rounded-2 bg-white" 
                            placeholder="Option de réponse..." 
                          />
                          <button v-if="!isPublished" type="button" @click="removeOption(field, optIdx)" class="btn btn-xs btn-link text-danger p-0" title="Supprimer">
                            ✕
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Code Snippet Settings -->
                    <div v-if="field.type === 'code_snippet'" class="mb-3 p-3 bg-light rounded-3 border">
                      <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                          <label class="form-label extra-small fw-bold text-dark mb-1">Langage par défaut suggéré :</label>
                          <select v-model="field.config.language" :disabled="isPublished" class="form-select form-select-sm rounded-2">
                            <option value="php">PHP / Laravel</option>
                            <option value="javascript">JavaScript / TypeScript</option>
                            <option value="python">Python</option>
                            <option value="html">HTML / CSS</option>
                            <option value="dart">Dart / Flutter</option>
                            <option value="sql">SQL / Base de données</option>
                            <option value="other">Autre langage</option>
                          </select>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label extra-small fw-bold text-dark mb-1">Texte d'aide pour le code :</label>
                          <input type="text" v-model="field.help_text" :disabled="isPublished" class="form-control form-control-sm rounded-2" placeholder="ex: Max 100 lignes, code indenté..." />
                        </div>
                      </div>
                    </div>

                    <!-- City Cameroon info -->
                    <div v-if="field.type === 'city_cameroon'" class="mb-3 p-3 bg-light rounded-3 border">
                      <div class="d-flex align-items-center gap-2 text-success small fw-bold">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Sélecteur intelligent des 98 villes du Cameroun connecté nativement.</span>
                      </div>
                    </div>

                    <!-- Placeholders and Help text -->
                    <div class="row g-2 mb-3">
                      <div class="col-md-6" v-if="!['select', 'radio', 'checkbox', 'rating'].includes(field.type)">
                        <label class="form-label extra-small text-muted mb-1">Texte indicatif (Placeholder)</label>
                        <input type="text" v-model="field.placeholder" :disabled="isPublished" class="form-control form-control-sm rounded-2" placeholder="ex: Tapez ici..." />
                      </div>
                      <div :class="['select', 'radio', 'checkbox', 'rating'].includes(field.type) ? 'col-12' : 'col-md-6'">
                        <label class="form-label extra-small text-muted mb-1">Consigne supplémentaire (Texte d'aide)</label>
                        <input type="text" v-model="field.help_text" :disabled="isPublished" class="form-control form-control-sm rounded-2" placeholder="ex: Format recommandé, etc." />
                      </div>
                    </div>

                    <!-- Required Toggle & Conditional Logic Accordion -->
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top flex-wrap gap-2">
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input cursor-pointer" type="checkbox" v-model="field.is_required" :disabled="isPublished" :id="'req_' + idx">
                        <label class="form-check-label extra-small fw-bold text-dark cursor-pointer" :for="'req_' + idx">
                          Réponse obligatoire
                        </label>
                      </div>

                      <!-- Conditional Logic Toggle Button -->
                      <button 
                        v-if="!isPublished || field.conditional_logic?.enabled"
                        type="button" 
                        :disabled="isPublished"
                        class="btn btn-xs rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1"
                        :class="field.conditional_logic?.enabled ? 'btn-info text-white' : 'btn-light text-muted border'"
                        @click="toggleConditionalLogic(field)"
                      >
                        <i class="bi bi-diagram-3"></i>
                        <span>{{ field.conditional_logic?.enabled ? 'Condition active' : '+ Ajouter une condition' }}</span>
                      </button>
                    </div>

                    <!-- Conditional Logic Settings Panel -->
                    <div v-if="field.conditional_logic?.enabled" class="mt-3 p-3 bg-info bg-opacity-10 rounded-3 border border-info border-opacity-25">
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="extra-small fw-bold text-dark d-flex align-items-center gap-1">
                          <i class="bi bi-funnel-fill text-info"></i> Règle d'affichage conditionnel :
                        </span>
                        <button v-if="!isPublished" type="button" @click="field.conditional_logic.enabled = false" class="btn btn-xs btn-link text-danger p-0">
                          Retirer la condition
                        </button>
                      </div>

                      <div class="row g-2 align-items-center extra-small">
                        <div class="col-auto">Afficher cette question si</div>
                        <div class="col-md-4">
                          <select v-model="field.conditional_logic.target_field_id" :disabled="isPublished" class="form-select form-select-sm rounded-2">
                            <option :value="null">Choisir une question précédente...</option>
                            <option 
                              v-for="(fPrev, pIdx) in form.fields.slice(0, idx)" 
                              :key="fPrev.id || pIdx" 
                              :value="fPrev.id || fPrev.name"
                            >
                              #{{ pIdx + 1 }}. {{ fPrev.label }}
                            </option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <select v-model="field.conditional_logic.operator" :disabled="isPublished" class="form-select form-select-sm rounded-2">
                            <option value="equals">est égal à</option>
                            <option value="not_equals">n'est pas égal à</option>
                            <option value="contains">contient</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <input 
                            type="text" 
                            v-model="field.conditional_logic.value" 
                            :disabled="isPublished"
                            class="form-control form-control-sm rounded-2" 
                            placeholder="Valeur requise..." 
                          />
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              <!-- Add Question Card Banner or Locked Banner -->
              <div v-if="!isPublished" class="card border-2 border-dashed rounded-4 p-4 text-center bg-white">
                <h6 class="fw-bold text-dark mb-3">Ajouter une nouvelle question</h6>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                  <button 
                    v-for="fieldType in availableFieldTypes" 
                    :key="fieldType.type"
                    type="button" 
                    @click="addNewField(fieldType.type)"
                    class="btn btn-sm btn-light border rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 hover-shadow"
                  >
                    <i :class="fieldType.icon + ' ' + fieldType.color"></i>
                    <span>{{ fieldType.label }}</span>
                  </button>
                </div>
              </div>
              <div v-else class="card border-0 rounded-4 p-4 text-center bg-light text-muted">
                <i class="bi bi-lock-fill fs-2 text-warning mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Formulaire verrouillé en lecture seule</h6>
                <p class="small mb-3">Ce formulaire a déjà été mis en ligne. Pour modifier ses questions ou en ajouter de nouvelles, dupliquez-le.</p>
                <div>
                  <button type="button" @click="duplicateForm" class="btn btn-sm btn-dark rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-copy"></i>
                    <span>Dupliquer ce formulaire</span>
                  </button>
                </div>
              </div>

            </div>

            <!-- Right: Live Quick Summary & Shortcuts -->
            <div class="col-lg-4">
              <div class="card border-0 rounded-4 shadow-sm p-4 bg-white position-sticky" style="top: 85px;">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2 text-dark">
                  <i class="bi bi-lightning-charge-fill text-warning"></i> Résumé du formulaire
                </h6>
                <div class="d-flex flex-column gap-2 mb-4 small">
                  <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Statut</span>
                    <span class="fw-bold text-dark">{{ form.status === 'published' ? 'En ligne' : 'Brouillon' }}</span>
                  </div>
                  <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Mode</span>
                    <span class="fw-bold text-dark">{{ form.display_mode === 'conversation' ? 'Typeform (1 par 1)' : 'Classique' }}</span>
                  </div>
                  <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Nombre de questions</span>
                    <span class="fw-bold text-success">{{ form.fields.length }}</span>
                  </div>
                  <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Questions obligatoires</span>
                    <span class="fw-bold text-danger">{{ form.fields.filter(f => f.is_required).length }}</span>
                  </div>
                  <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Conditions actives</span>
                    <span class="fw-bold text-info">{{ form.fields.filter(f => f.conditional_logic?.enabled).length }}</span>
                  </div>
                </div>

                <div class="d-grid gap-2">
                  <template v-if="isPublished">
                    <button type="button" @click="duplicateForm" class="btn btn-dark rounded-pill fw-bold py-2 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-copy"></i>
                      <span>Dupliquer pour modifier</span>
                    </button>
                  </template>
                  <template v-else>
                    <button type="button" @click="saveForm" :disabled="isSaving" class="btn btn-success rounded-pill fw-bold py-2 shadow-sm">
                      <span v-if="isSaving" class="spinner-border spinner-border-sm me-1"></span>
                      <span>Enregistrer le brouillon</span>
                    </button>
                  </template>
                  <a :href="`/f/${form.slug}`" target="_blank" class="btn btn-outline-dark rounded-pill fw-semibold py-2">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Tester le formulaire
                  </a>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- TAB 2: SETTINGS & RULES -->
        <div v-show="activeTab === 'settings'">
          <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white mx-auto" style="max-width: 820px;">
            <h5 class="fw-black text-dark mb-4 d-flex align-items-center gap-2">
              <i class="bi bi-sliders text-primary"></i> Paramètres &amp; Règles de Clôture
            </h5>

            <!-- Slug / URL Identifier -->
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">Identifiant URL personnalisée (Slug) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted small">https://tech237.cm/f/</span>
                <input type="text" v-model="form.slug" :disabled="isPublished" class="form-control font-monospace" required />
              </div>
              <div class="extra-small text-muted mt-1">Caractères alphanumériques et tirets uniquement.</div>
            </div>

            <!-- Display Mode Selector -->
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark d-block">Mode d'affichage pour les répondants :</label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="p-3 rounded-3 border" :class="[form.display_mode === 'classic' ? 'bg-success bg-opacity-10 border-success' : 'bg-light', isPublished ? 'opacity-75' : 'cursor-pointer']" @click="!isPublished && (form.display_mode = 'classic')">
                    <div class="fw-bold text-dark mb-1">📄 Mode Classique (Google Forms)</div>
                    <div class="extra-small text-muted">Page continue, tous les champs défilent verticalement.</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="p-3 rounded-3 border" :class="[form.display_mode === 'conversation' ? 'bg-success bg-opacity-10 border-success' : 'bg-light', isPublished ? 'opacity-75' : 'cursor-pointer']" @click="!isPublished && (form.display_mode = 'conversation')">
                    <div class="fw-bold text-dark mb-1">💬 Mode Conversationnel (Typeform)</div>
                    <div class="extra-small text-muted">Une seule question à la fois avec navigation fluide au clavier.</div>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            <!-- Security & Quotas -->
            <h6 class="fw-bold text-dark mb-3">Restrictions &amp; Sécurité</h6>

            <div class="form-check form-switch mb-3">
              <input class="form-check-input cursor-pointer" type="checkbox" v-model="form.requires_login" :disabled="isPublished" id="reqLogin">
              <label class="form-check-label small fw-bold text-dark cursor-pointer" for="reqLogin">
                Exiger une connexion au compte Tech237
              </label>
              <div class="extra-small text-muted ps-0">Seuls les membres identifiés pourront répondre.</div>
            </div>

            <div class="form-check form-switch mb-4">
              <input class="form-check-input cursor-pointer" type="checkbox" v-model="form.one_response_per_user" :disabled="isPublished" id="oneResp">
              <label class="form-check-label small fw-bold text-dark cursor-pointer" for="oneResp">
                Limiter à 1 seule réponse par participant
              </label>
              <div class="extra-small text-muted ps-0">Empêche les doublons et votes multiples.</div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Limite de réponses (Quota max)</label>
                <input type="number" v-model.number="form.max_responses" :disabled="isPublished" min="1" class="form-control rounded-3" placeholder="ex: 50 (laisser vide si illimité)" />
                <div class="extra-small text-muted mt-1">Le formulaire se clôturera automatiquement une fois atteint.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Date &amp; Heure d'expiration</label>
                <input type="datetime-local" v-model="form.expires_at" :disabled="isPublished" class="form-control rounded-3" />
                <div class="extra-small text-muted mt-1">Fermeture automatique passée cette date.</div>
              </div>
            </div>

            <hr class="my-4">

            <!-- Success message and redirect -->
            <h6 class="fw-bold text-dark mb-3">Après la soumission</h6>

            <div class="mb-3">
              <label class="form-label small fw-bold text-dark">Message de remerciement personnalisé</label>
              <textarea v-model="form.confirmation_message" :disabled="isPublished" rows="2" class="form-control rounded-3" placeholder="Merci pour votre participation ! Nous vous recontacterons très prochainement."></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">Lien de redirection après envoi (Optionnel)</label>
              <input type="url" v-model="form.redirect_url" :disabled="isPublished" class="form-control rounded-3" placeholder="https://monsite.cm/merci" />
            </div>

            <!-- Status selector -->
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">Statut du formulaire :</label>
              <select v-model="form.status" :disabled="isPublished" class="form-select rounded-3">
                <option value="published">🟢 En ligne (Accepte les réponses)</option>
                <option value="draft">🟡 Brouillon (Accessible uniquement par l'auteur)</option>
                <option value="closed">🔴 Fermé (N'accepte plus aucune réponse)</option>
              </select>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2">
              <span v-if="isPublished" class="small text-muted">
                <i class="bi bi-lock-fill text-warning me-1"></i> Formulaire verrouillé car déjà mis en ligne.
              </span>
              <div class="d-flex align-items-center gap-2 ms-auto">
                <button v-if="isPublished" type="button" @click="duplicateForm" class="btn btn-dark rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
                  <i class="bi bi-copy"></i>
                  <span>Dupliquer</span>
                </button>
                <button v-else type="button" @click="saveForm" :disabled="isSaving" class="btn btn-success rounded-pill px-4 fw-bold">
                  Enregistrer les paramètres
                </button>
              </div>
            </div>

          </div>
        </div>

        <!-- TAB 3: EMAIL NOTIFICATIONS -->
        <div v-show="activeTab === 'notifications'">
          <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white mx-auto" style="max-width: 820px;">
            <h5 class="fw-black text-dark mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-envelope-check text-success"></i> Notifications par Email
            </h5>
            <p class="text-muted small mb-4">
              Configurez la transmission des réponses en direct dans votre boîte de réception dès qu'un utilisateur soumet un formulaire.
            </p>

            <!-- Notification to Creator -->
            <div class="p-4 bg-light rounded-4 border mb-4">
              <div class="form-check form-switch mb-3">
                <input class="form-check-input cursor-pointer" type="checkbox" v-model="form.notify_email_on_response" :disabled="isPublished" id="notifyAdmin">
                <label class="form-check-label small fw-bold text-dark cursor-pointer" for="notifyAdmin">
                  Recevoir une alerte email à chaque nouvelle réponse
                </label>
              </div>

              <div v-if="form.notify_email_on_response">
                <label class="form-label extra-small fw-bold text-dark mb-1">Adresse email de destination :</label>
                <input type="email" v-model="form.notification_email" :disabled="isPublished" class="form-control rounded-3 bg-white" placeholder="votre-email@exemple.cm" />
                <div class="extra-small text-muted mt-1">Par défaut, l'email de votre compte Tech237 est utilisé.</div>
              </div>
            </div>

            <!-- Confirmation to Respondent -->
            <div class="p-4 bg-light rounded-4 border mb-4">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input cursor-pointer" type="checkbox" v-model="form.send_respondent_confirmation" :disabled="isPublished" id="notifyRespondent">
                <label class="form-check-label small fw-bold text-dark cursor-pointer" for="notifyRespondent">
                  Envoyer un accusé de réception automatique au répondant
                </label>
              </div>
              <div class="extra-small text-muted">
                Si votre formulaire contient un champ de type « Email », une confirmation avec le récapitulatif de sa soumission lui sera envoyée immédiatement.
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2">
              <span v-if="isPublished" class="small text-muted">
                <i class="bi bi-lock-fill text-warning me-1"></i> Formulaire verrouillé car déjà mis en ligne.
              </span>
              <div class="d-flex align-items-center gap-2 ms-auto">
                <button v-if="isPublished" type="button" @click="duplicateForm" class="btn btn-dark rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
                  <i class="bi bi-copy"></i>
                  <span>Dupliquer</span>
                </button>
                <button v-else type="button" @click="saveForm" :disabled="isSaving" class="btn btn-success rounded-pill px-4 fw-bold">
                  Enregistrer les préférences email
                </button>
              </div>
            </div>

          </div>
        </div>

        <!-- TAB 4: SHARE & QR CODE -->
        <div v-show="activeTab === 'share'">
          <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white mx-auto text-center" style="max-width: 720px;">
            <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-flex align-items-center justify-content-center mb-3">
              <i class="bi bi-qr-code text-success fs-1"></i>
            </div>

            <h4 class="fw-black text-dark mb-2">Partager votre formulaire</h4>
            <p class="text-muted small mx-auto mb-4" style="max-width: 500px;">
              Diffusez votre lien ou téléchargez le QR Code haute résolution pour vos affiches, kakemonos, slides de conférences ou réseaux sociaux.
            </p>

            <!-- QR Code Canvas Display -->
            <div class="p-3 bg-white border rounded-4 d-inline-block shadow-sm mb-4">
              <canvas ref="shareQrCanvas" style="width: 260px; height: 260px;"></canvas>
            </div>

            <!-- Link copy box -->
            <div class="input-group mb-4 mx-auto" style="max-width: 500px;">
              <input type="text" readonly :value="form.public_url" class="form-control font-monospace extra-small bg-light" />
              <button class="btn btn-success px-4 fw-bold" type="button" @click="copyPublicUrl">
                <i class="bi bi-clipboard me-1"></i> {{ isCopied ? 'Copié !' : 'Copier' }}
              </button>
            </div>

            <!-- Download Buttons -->
            <div class="d-flex justify-content-center gap-3">
              <button type="button" @click="downloadQrPng" class="btn btn-dark rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2">
                <i class="bi bi-download"></i> Télécharger en PNG
              </button>
              <button type="button" @click="downloadQrSvgFile" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="bi bi-filetype-svg"></i> Télécharger en SVG
              </button>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- QR Code Modal (accessible from top toolbar) -->
    <div v-if="showQrModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
          <div class="modal-header bg-dark text-white p-4">
            <h5 class="modal-title fw-black d-flex align-items-center gap-2">
              <i class="bi bi-qr-code text-success"></i> QR Code • {{ form.title }}
            </h5>
            <button type="button" @click="showQrModal = false" class="btn-close btn-close-white shadow-none"></button>
          </div>
          <div class="modal-body p-4 text-center">
            <canvas ref="modalQrCanvas" class="img-fluid mb-3 border p-2 rounded-3 shadow-2xs" style="width: 240px; height: 240px;"></canvas>
            <div class="input-group mb-3">
              <input type="text" readonly :value="form.public_url" class="form-control form-control-sm font-monospace extra-small bg-light" />
              <button class="btn btn-outline-success btn-sm px-3 fw-bold" type="button" @click="copyPublicUrl">
                <i class="bi bi-clipboard"></i>
              </button>
            </div>
            <div class="d-flex justify-content-center gap-2">
              <button type="button" @click="downloadQrPng" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold">Télécharger PNG</button>
              <button type="button" @click="downloadQrSvgFile" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold">Télécharger SVG</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="toastMsg" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
      <div class="toast show align-items-center text-white bg-dark border-0 rounded-4 shadow-lg p-2" role="alert">
        <div class="d-flex">
          <div class="toast-body small fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success fs-6"></i>
            {{ toastMsg }}
          </div>
          <button type="button" @click="toastMsg = ''" class="btn-close btn-close-white me-2 m-auto shadow-none"></button>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import { renderQrToCanvas, downloadQrImage, createQrSvg } from '@/Utils/qrcode'

const props = defineProps({
  form: {
    type: Object,
    required: true
  }
})

const form = ref(JSON.parse(JSON.stringify(props.form)))
const isPublished = computed(() => form.value.status === 'published')
const activeTab = ref('fields')
const activeEditingIdx = ref(null)
const isSaving = ref(false)
const showQrModal = ref(false)
const shareQrCanvas = ref(null)
const modalQrCanvas = ref(null)
const isCopied = ref(false)
const toastMsg = ref('')

const availableFieldTypes = [
  { type: 'text', label: 'Texte court', icon: 'bi bi-input-cursor-text', color: 'text-primary' },
  { type: 'textarea', label: 'Paragraphe', icon: 'bi bi-textarea-t', color: 'text-secondary' },
  { type: 'number', label: 'Nombre', icon: 'bi bi-123', color: 'text-info' },
  { type: 'email', label: 'Email', icon: 'bi bi-envelope', color: 'text-danger' },
  { type: 'phone', label: 'Téléphone / WhatsApp', icon: 'bi bi-whatsapp', color: 'text-success' },
  { type: 'select', label: 'Menu déroulant', icon: 'bi bi-menu-button-wide', color: 'text-primary' },
  { type: 'radio', label: 'Choix unique', icon: 'bi bi-ui-radios', color: 'text-warning' },
  { type: 'checkbox', label: 'Cases à cocher', icon: 'bi bi-ui-checks', color: 'text-success' },
  { type: 'city_cameroon', label: 'Ville Cameroun', icon: 'bi bi-geo-alt-fill', color: 'text-success' },
  { type: 'code_snippet', label: 'Dépôt de Code (Snippet)', icon: 'bi bi-code-slash', color: 'text-dark' },
  { type: 'github_link', label: 'Profil GitHub / GitLab', icon: 'bi bi-github', color: 'text-dark' },
  { type: 'file', label: 'Fichier / CV (Upload)', icon: 'bi bi-paperclip', color: 'text-primary' },
  { type: 'rating', label: 'Note par étoiles', icon: 'bi bi-star-fill', color: 'text-warning' },
  { type: 'date', label: 'Date', icon: 'bi bi-calendar-date', color: 'text-secondary' },
]

onMounted(() => {
  renderQrs()
})

watch([() => form.value.public_url, () => form.value.theme_color, activeTab, showQrModal], () => {
  nextTick(() => renderQrs())
})

function renderQrs() {
  if (shareQrCanvas.value) {
    renderQrToCanvas(shareQrCanvas.value, form.value.public_url, 300, form.value.theme_color || '#0B2559')
  }
  if (modalQrCanvas.value) {
    renderQrToCanvas(modalQrCanvas.value, form.value.public_url, 300, form.value.theme_color || '#0B2559')
  }
}

function getFieldTypeLabel(type) {
  const match = availableFieldTypes.find(f => f.type === type)
  return match ? match.label : type
}

function getFieldTypeIcon(type) {
  const match = availableFieldTypes.find(f => f.type === type)
  return match ? match.icon : 'bi bi-question-circle'
}

function addNewField(type) {
  if (isPublished.value) return
  const newIdx = form.value.fields.length + 1
  const labelNames = {
    text: 'Votre nom ou intitulé',
    textarea: 'Détaillez votre réponse',
    number: 'Indiquez un montant ou une valeur',
    email: 'Votre adresse email',
    phone: 'Numéro WhatsApp / Téléphone',
    select: 'Choisissez une option',
    radio: 'Sélectionnez votre choix',
    checkbox: 'Cochez les options applicables',
    city_cameroon: 'Ville de résidence (Cameroun)',
    code_snippet: 'Extrait de code / Solution technique',
    github_link: 'Lien vers votre profil GitHub ou GitLab',
    file: 'Téléversez votre CV ou document (PDF)',
    rating: 'Note globale',
    date: 'Date souhaitée',
  }

  const newField = {
    id: null,
    label: labelNames[type] || 'Nouvelle question',
    name: 'field_' + newIdx,
    type: type,
    placeholder: '',
    help_text: '',
    is_required: false,
    options: ['select', 'radio', 'checkbox'].includes(type) ? ['Option 1', 'Option 2', 'Option 3'] : [],
    config: type === 'code_snippet' ? { language: 'php' } : {},
    conditional_logic: { enabled: false, target_field_id: null, operator: 'equals', value: '' },
    order: form.value.fields.length,
  }

  form.value.fields.push(newField)
  showToast('Question ajoutée !')
}

function moveField(idx, delta) {
  if (isPublished.value) return
  const target = idx + delta
  if (target < 0 || target >= form.value.fields.length) return
  const item = form.value.fields.splice(idx, 1)[0]
  form.value.fields.splice(target, 0, item)
}

function duplicateField(idx) {
  if (isPublished.value) return
  const source = form.value.fields[idx]
  const clone = JSON.parse(JSON.stringify(source))
  clone.id = null
  clone.label += ' (Copie)'
  form.value.fields.splice(idx + 1, 0, clone)
  showToast('Question dupliquée !')
}

function removeField(idx) {
  if (isPublished.value) return
  if (confirm('Voulez-vous supprimer cette question ?')) {
    form.value.fields.splice(idx, 1)
  }
}

function addOption(field) {
  if (isPublished.value) return
  if (!Array.isArray(field.options)) field.options = []
  field.options.push(`Option ${field.options.length + 1}`)
}

function removeOption(field, optIdx) {
  if (isPublished.value) return
  field.options.splice(optIdx, 1)
}

function toggleConditionalLogic(field) {
  if (isPublished.value) return
  if (!field.conditional_logic) {
    field.conditional_logic = { enabled: true, target_field_id: null, operator: 'equals', value: '' }
  } else {
    field.conditional_logic.enabled = !field.conditional_logic.enabled
  }
}

function publishForm() {
  if (!form.value.title) {
    showToast('Le titre du formulaire est requis.')
    return
  }
  if (!form.value.fields.length) {
    showToast('Ajoutez au moins une question à votre formulaire avant de le publier.')
    return
  }
  if (!confirm('Attention : Une fois mis en ligne, la structure et les questions du formulaire seront définitivement verrouillées pour garantir l\'intégrité des réponses. Confirmez-vous la mise en ligne ?')) {
    return
  }
  form.value.status = 'published'
  saveForm()
}

function duplicateForm() {
  if (confirm('Voulez-vous dupliquer ce formulaire pour créer une nouvelle version modifiable ?')) {
    router.post(`/espace-utilisateur/formulaires/${form.value.id}/dupliquer`)
  }
}

function toggleStatus(newStatus) {
  const confirmMsg = newStatus === 'closed'
    ? 'Voulez-vous fermer ce formulaire ? Les répondants ne pourront plus soumettre de réponses.'
    : 'Voulez-vous rouvrir ce formulaire pour accepter de nouvelles réponses ?'
  if (!confirm(confirmMsg)) return

  router.post(`/espace-utilisateur/formulaires/${form.value.id}/statut`, { status: newStatus }, {
    preserveScroll: true,
    onSuccess: () => {
      form.value.status = newStatus
      showToast(newStatus === 'closed' ? 'Formulaire fermé aux nouvelles réponses.' : 'Formulaire remis en ligne.')
    },
    onError: (errs) => {
      const msg = Object.values(errs)[0] || 'Erreur lors de la mise à jour du statut.'
      alert(msg)
    }
  })
}

function saveForm() {
  if (isPublished.value) {
    showToast('Ce formulaire a été mis en ligne et ne peut plus être modifié. Dupliquez-le pour créer une nouvelle version.')
    return
  }
  if (!form.value.title) {
    showToast('Le titre du formulaire est requis.')
    return
  }
  if (!form.value.fields.length) {
    showToast('Ajoutez au moins une question à votre formulaire.')
    return
  }

  isSaving.value = true
  router.put(`/espace-utilisateur/formulaires/${form.value.id}`, form.value, {
    onSuccess: () => {
      showToast('Formulaire enregistré avec succès !')
      isSaving.value = false
    },
    onError: (errs) => {
      isSaving.value = false
      const msg = Object.values(errs)[0] || 'Erreur lors de l\'enregistrement.'
      alert(msg)
    }
  })
}

function copyPublicUrl() {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(form.value.public_url)
    isCopied.value = true
    showToast('Lien copié dans le presse-papier !')
    setTimeout(() => { isCopied.value = false }, 3000)
  }
}

function downloadQrPng() {
  downloadQrImage(
    form.value.public_url,
    `qrcode_${form.value.slug}.png`,
    500,
    form.value.theme_color || '#071738'
  )
  showToast('Image PNG téléchargée !')
}

function downloadQrSvgFile() {
  const svgContent = createQrSvg(form.value.public_url, 400, form.value.theme_color || '#071738')
  const blob = new Blob([svgContent], { type: 'image/svg+xml;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `qrcode_${form.value.slug}.svg`
  a.click()
  URL.revokeObjectURL(url)
  showToast('Fichier vectoriel SVG téléchargé !')
}

function showToast(msg) {
  toastMsg.value = msg
  setTimeout(() => { toastMsg.value = '' }, 3500)
}
</script>

<style scoped>
.edit-title-input {
  background: transparent;
  box-shadow: none;
}
.edit-title-input:focus {
  background: #f8fafc;
}
.field-card {
  transition: all 0.2s ease;
}
.cursor-pointer {
  cursor: pointer;
}
.extra-small {
  font-size: 0.75rem;
}
.hover-shadow:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
</style>
