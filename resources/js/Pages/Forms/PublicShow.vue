<template>
  <div class="public-form-wrapper min-vh-100 d-flex flex-column" :style="{ backgroundColor: '#f8fafc', '--theme-color': form.theme_color || '#00A859' }">
    <Head :title="form.title + ' • Tech237'" />

    <!-- Top Minimal Navigation Bar -->
    <header class="py-3 px-4 bg-white border-bottom shadow-2xs d-flex align-items-center justify-content-between">
      <Link href="/" class="d-flex align-items-center gap-2 text-decoration-none">
        <span class="fw-black text-dark fs-5 tracking-tight">Tech<span style="color: var(--theme-color);">237</span></span>
        <span class="badge bg-light text-dark border extra-small rounded-pill">Forms</span>
      </Link>

      <div class="d-flex align-items-center gap-3">
        <div v-if="gate.current_user" class="extra-small text-muted d-none d-sm-inline">
          Connecté : <strong class="text-dark">{{ gate.current_user.name }}</strong>
        </div>
        <Link v-else-if="form.requires_login" :href="'/login?redirect=' + encodeURIComponent('/f/' + form.slug)" class="btn btn-xs btn-dark rounded-pill px-3 fw-bold">
          Connexion
        </Link>
      </div>
    </header>

    <!-- Top Progress Bar (Active in Typeform Mode) -->
    <div v-if="form.display_mode === 'conversation' && !isFinished && isAccepting" class="progress rounded-0" style="height: 4px;">
      <div 
        class="progress-bar transition-all" 
        role="progressbar" 
        :style="{ width: progressPercentage + '%', backgroundColor: form.theme_color || '#00A859' }"
      ></div>
    </div>

    <!-- MAIN BODY CONTAINER -->
    <main class="flex-grow-1 d-flex align-items-center justify-content-center p-3 p-md-4">
      
      <!-- CASE 1: FORM FINISHED (SUCCESS SCREEN) -->
      <div v-if="isFinished" class="card border-0 rounded-5 shadow-lg p-4 p-md-5 bg-white text-center animate-fade-in mx-auto" style="max-width: 600px;">
        <div class="mb-4">
          <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-4 shadow-sm" style="background-color: rgba(0, 168, 89, 0.12); width: 88px; height: 88px;">
            <i class="bi bi-check-circle-fill fs-1" :style="{ color: form.theme_color || '#00A859' }"></i>
          </div>
        </div>

        <h3 class="fw-black text-dark mb-2">Merci pour votre réponse !</h3>
        <p class="text-muted small mb-4">
          {{ form.confirmation_message || 'Votre soumission a été enregistrée avec succès par l\'équipe Tech237.' }}
        </p>

        <div v-if="submittedResponseUuid" class="p-3 bg-light rounded-3 border extra-small font-monospace text-muted mb-4">
          Numéro de référence : <strong>{{ submittedResponseUuid }}</strong>
        </div>

        <div class="d-flex justify-content-center gap-2">
          <a v-if="form.redirect_url" :href="form.redirect_url" class="btn btn-sm rounded-pill px-4 py-2 fw-bold text-white shadow-sm" :style="{ backgroundColor: form.theme_color || '#00A859' }">
            Continuer ➔
          </a>
          <Link href="/" class="btn btn-sm btn-outline-dark rounded-pill px-4 py-2 fw-semibold">
            Retour à l'accueil
          </Link>
        </div>
      </div>

      <!-- CASE 2: GATE NOT PASSING (CLOSED, EXPIRED, QUOTA, LOGIN REQUIRED, ALREADY SUBMITTED) -->
      <div v-else-if="!isAccepting" class="card border-0 rounded-5 shadow-lg p-4 p-md-5 bg-white text-center mx-auto" style="max-width: 580px;">
        
        <!-- Expired -->
        <div v-if="gate.is_expired">
          <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 d-inline-flex mb-3">
            <i class="bi bi-calendar-x fs-1"></i>
          </div>
          <h4 class="fw-black text-dark mb-2">Date limite dépassée</h4>
          <p class="text-muted small mb-4">
            Ce formulaire n'accepte plus de réponses car la date limite de soumission est expirée.
          </p>
        </div>

        <!-- Quota Reached -->
        <div v-else-if="gate.is_quota_reached">
          <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-inline-flex mb-3">
            <i class="bi bi-people-fill fs-1"></i>
          </div>
          <h4 class="fw-black text-dark mb-2">Nombre limite de réponses atteint</h4>
          <p class="text-muted small mb-4">
            Le quota maximum de participants autorisé pour ce formulaire a été atteint.
          </p>
        </div>

        <!-- Requires Login -->
        <div v-else-if="gate.requires_login">
          <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-inline-flex mb-3">
            <i class="bi bi-shield-lock-fill fs-1"></i>
          </div>
          <h4 class="fw-black text-dark mb-2">Connexion requise</h4>
          <p class="text-muted small mb-4">
            L'auteur de ce formulaire exige que vous soyez connecté à votre compte Tech237 pour pouvoir participer.
          </p>
          <Link :href="'/login?redirect=' + encodeURIComponent('/f/' + form.slug)" class="btn btn-dark rounded-pill px-4 py-2 fw-bold">
            <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter à Tech237
          </Link>
        </div>

        <!-- Already Submitted -->
        <div v-else-if="gate.already_submitted">
          <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-inline-flex mb-3">
            <i class="bi bi-check2-all fs-1"></i>
          </div>
          <h4 class="fw-black text-dark mb-2">Réponse déjà enregistrée</h4>
          <p class="text-muted small mb-4">
            Ce formulaire est limité à 1 seule réponse par participant. Vous avez déjà soumis votre participation.
          </p>
        </div>

        <!-- Closed -->
        <div v-else>
          <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-3 d-inline-flex mb-3">
            <i class="bi bi-lock-fill fs-1"></i>
          </div>
          <h4 class="fw-black text-dark mb-2">Formulaire fermé</h4>
          <p class="text-muted small mb-4">
            Ce formulaire est actuellement désactivé par son créateur.
          </p>
        </div>

        <div class="pt-3 border-top mt-3">
          <Link href="/" class="btn btn-sm btn-link text-muted text-decoration-none">
            ← Revenir au site Tech237
          </Link>
        </div>
      </div>

      <!-- CASE 3: ACTIVE FORM (DISPLAY MODE) -->
      <div v-else class="w-100">

        <!-- ==============================================
             MODE A: CONVERSATION (TYPEFORM 1-PAR-1)
             ============================================== -->
        <div v-if="form.display_mode === 'conversation'" class="container" style="max-width: 680px;">
          
          <!-- Step 0: Welcome Slide -->
          <div v-if="currentStep === 0" class="card border-0 rounded-5 shadow-lg p-4 p-md-5 bg-white text-center animate-slide-up">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 mx-auto" 
                 style="background: rgba(0, 168, 89, 0.1); color: var(--theme-color); font-size: 0.82rem; font-weight: 700;">
              <span>Par {{ form.user.name }}</span>
            </div>

            <h1 class="display-6 fw-black text-dark mb-3">
              {{ form.title }}
            </h1>

            <p class="lead text-muted mb-4 small" style="line-height: 1.6;">
              {{ form.description || 'Prenez quelques instants pour répondre aux questions suivantes.' }}
            </p>

            <div class="d-flex align-items-center justify-content-center gap-2 extra-small text-muted mb-4">
              <span><i class="bi bi-clock me-1"></i> ~2 min</span>
              <span>•</span>
              <span>{{ visibleFields.length }} question(s)</span>
            </div>

            <div class="d-grid gap-2" style="max-width: 280px; margin: 0 auto;">
              <button 
                type="button" 
                @click="goToNextStep" 
                class="btn btn-lg rounded-pill fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2"
                :style="{ backgroundColor: form.theme_color || '#00A859' }"
              >
                <span>Commencer</span>
                <i class="bi bi-arrow-right"></i>
              </button>
              <span class="extra-small text-muted mt-1">Appuyez sur <kbd>Entrée ↵</kbd></span>
            </div>
          </div>

          <!-- Step 1..N: Individual Question Slide -->
          <div v-else-if="currentStep <= visibleFields.length" class="card border-0 rounded-5 shadow-lg p-4 p-md-5 bg-white animate-slide-up">
            
            <div class="d-flex align-items-center justify-content-between mb-4">
              <span class="extra-small fw-bold text-muted text-uppercase tracking-wider">
                Question {{ currentStep }} sur {{ visibleFields.length }}
              </span>
              <span v-if="currentField.is_required" class="badge bg-danger-subtle text-danger extra-small rounded-pill">
                Obligatoire *
              </span>
            </div>

            <!-- Question Title -->
            <h2 class="h4 fw-black text-dark mb-2">
              {{ currentField.label }}
            </h2>

            <p v-if="currentField.help_text" class="text-muted small mb-4">
              {{ currentField.help_text }}
            </p>

            <!-- Render specific input based on type -->
            <div class="mb-4 pt-2">
              
              <!-- Text -->
              <input 
                v-if="currentField.type === 'text'" 
                type="text" 
                v-model="answers[currentField.id]" 
                class="form-control form-control-lg border-0 border-bottom rounded-0 px-1 fs-5 focus-theme"
                :placeholder="currentField.placeholder || 'Tapez votre réponse ici...'"
                @keyup.enter="goToNextStep"
                ref="activeInput"
                autofocus
              />

              <!-- Textarea -->
              <textarea 
                v-else-if="currentField.type === 'textarea'" 
                v-model="answers[currentField.id]" 
                rows="4" 
                class="form-control rounded-4 p-3 focus-theme"
                :placeholder="currentField.placeholder || 'Détaillez votre réponse ici...'"
              ></textarea>

              <!-- Number -->
              <input 
                v-else-if="currentField.type === 'number'" 
                type="number" 
                v-model="answers[currentField.id]" 
                class="form-control form-control-lg border-0 border-bottom rounded-0 px-1 fs-5 focus-theme"
                :placeholder="currentField.placeholder || 'Entrez un nombre...'"
                @keyup.enter="goToNextStep"
              />

              <!-- Email -->
              <input 
                v-else-if="currentField.type === 'email'" 
                type="email" 
                v-model="answers[currentField.id]" 
                class="form-control form-control-lg border-0 border-bottom rounded-0 px-1 fs-5 focus-theme"
                :placeholder="currentField.placeholder || 'exemple@domaine.cm'"
                @keyup.enter="goToNextStep"
              />

              <!-- Phone -->
              <input 
                v-else-if="currentField.type === 'phone'" 
                type="tel" 
                v-model="answers[currentField.id]" 
                class="form-control form-control-lg border-0 border-bottom rounded-0 px-1 fs-5 focus-theme"
                :placeholder="currentField.placeholder || '+237 6...'"
                @keyup.enter="goToNextStep"
              />

              <!-- Radio / Single Choice -->
              <div v-else-if="currentField.type === 'radio'" class="d-flex flex-column gap-2">
                <div 
                  v-for="(opt, optIdx) in (currentField.options || [])" 
                  :key="optIdx"
                  class="p-3 rounded-4 border cursor-pointer transition choice-item d-flex align-items-center justify-content-between"
                  :class="answers[currentField.id] === opt ? 'border-success bg-success bg-opacity-10 fw-bold' : 'bg-light hover-bg-white'"
                  @click="answers[currentField.id] = opt"
                >
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-white text-dark border rounded-circle shadow-2xs" style="width: 26px; height: 26px; line-height: 18px;">
                      {{ String.fromCharCode(65 + optIdx) }}
                    </span>
                    <span>{{ opt }}</span>
                  </div>
                  <i v-if="answers[currentField.id] === opt" class="bi bi-check-circle-fill text-success fs-5"></i>
                </div>
              </div>

              <!-- Select -->
              <select v-else-if="currentField.type === 'select'" v-model="answers[currentField.id]" class="form-select form-select-lg rounded-4 p-3">
                <option :value="null">Sélectionnez une option...</option>
                <option v-for="(opt, oIdx) in (currentField.options || [])" :key="oIdx" :value="opt">{{ opt }}</option>
              </select>

              <!-- Checkbox / Multiple Choices -->
              <div v-else-if="currentField.type === 'checkbox'" class="d-flex flex-column gap-2">
                <div 
                  v-for="(opt, optIdx) in (currentField.options || [])" 
                  :key="optIdx"
                  class="p-3 rounded-4 border cursor-pointer transition choice-item d-flex align-items-center justify-content-between"
                  :class="isOptionChecked(currentField.id, opt) ? 'border-success bg-success bg-opacity-10 fw-bold' : 'bg-light hover-bg-white'"
                  @click="toggleOptionChecked(currentField.id, opt)"
                >
                  <div class="d-flex align-items-center gap-3">
                    <input type="checkbox" :checked="isOptionChecked(currentField.id, opt)" class="form-check-input" @click.stop />
                    <span>{{ opt }}</span>
                  </div>
                </div>
              </div>

              <!-- City Cameroon -->
              <div v-else-if="currentField.type === 'city_cameroon'">
                <CitySelect2 v-model="answers[currentField.id]" placeholder="Sélectionnez une ville du Cameroun..." />
              </div>

              <!-- Code Snippet -->
              <div v-else-if="currentField.type === 'code_snippet'">
                <textarea 
                  v-model="answers[currentField.id]" 
                  rows="6" 
                  class="form-control rounded-4 p-3 font-monospace small bg-dark text-light border-0"
                  :placeholder="currentField.placeholder || '// Collez ici votre extrait de code...'"
                ></textarea>
              </div>

              <!-- GitHub Link -->
              <div v-else-if="currentField.type === 'github_link'" class="input-group input-group-lg">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-github"></i></span>
                <input 
                  type="url" 
                  v-model="answers[currentField.id]" 
                  class="form-control border-start-0 rounded-end-4" 
                  :placeholder="currentField.placeholder || 'https://github.com/votre-compte'" 
                  @keyup.enter="goToNextStep"
                />
              </div>

              <!-- File Upload -->
              <div v-else-if="currentField.type === 'file'">
                <div class="border-2 border-dashed rounded-4 p-4 text-center bg-light">
                  <input type="file" :id="'file_' + currentField.id" class="d-none" @change="e => onFileSelected(currentField.id, e.target.files[0])" />
                  <label :for="'file_' + currentField.id" class="cursor-pointer mb-0">
                    <div class="rounded-circle bg-white p-3 d-inline-flex mb-2 shadow-2xs">
                      <i class="bi bi-cloud-arrow-up text-primary fs-3"></i>
                    </div>
                    <div class="fw-bold text-dark small">Cliquez pour choisir un fichier</div>
                    <div class="extra-small text-muted mt-1">PDF, JPG, PNG, max 10 Mo</div>
                  </label>
                  <div v-if="filesMap[currentField.id]" class="mt-3 p-2 bg-white rounded-3 border extra-small fw-bold text-success d-inline-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-check-fill"></i>
                    <span>{{ filesMap[currentField.id].name }}</span>
                  </div>
                </div>
              </div>

              <!-- Rating (Stars) -->
              <div v-else-if="currentField.type === 'rating'" class="d-flex align-items-center gap-2">
                <button 
                  v-for="star in 5" 
                  :key="star"
                  type="button" 
                  class="btn p-0 text-warning fs-1 border-0"
                  @click="answers[currentField.id] = star"
                >
                  <i :class="star <= Number(answers[currentField.id]) ? 'bi bi-star-fill' : 'bi bi-star text-muted'"></i>
                </button>
                <span v-if="answers[currentField.id]" class="fw-bold text-dark ms-3 fs-5">
                  {{ answers[currentField.id] }} / 5
                </span>
              </div>

              <!-- Date -->
              <input 
                v-else-if="currentField.type === 'date'" 
                type="date" 
                v-model="answers[currentField.id]" 
                class="form-control form-control-lg rounded-4 p-3 focus-theme"
              />

            </div>

            <!-- Error message if validation fails -->
            <div v-if="currentStepError" class="alert alert-danger py-2 px-3 rounded-3 extra-small mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <span>{{ currentStepError }}</span>
            </div>

            <!-- Navigation Buttons: Back / Next / Submit -->
            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
              <button 
                type="button" 
                @click="goToPrevStep" 
                class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-muted"
              >
                <i class="bi bi-arrow-left me-1"></i> Précédent
              </button>

              <div class="d-flex align-items-center gap-2">
                <button 
                  v-if="currentStep < visibleFields.length" 
                  type="button" 
                  @click="goToNextStep" 
                  class="btn btn-sm rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2"
                  :style="{ backgroundColor: form.theme_color || '#00A859' }"
                >
                  <span>Suivant</span>
                  <i class="bi bi-arrow-right"></i>
                </button>

                <button 
                  v-else 
                  type="button" 
                  @click="submitForm" 
                  :disabled="isSubmitting"
                  class="btn btn-sm btn-success rounded-pill px-5 py-2 fw-bold text-white shadow d-inline-flex align-items-center gap-2"
                >
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm"></span>
                  <i v-else class="bi bi-send-fill"></i>
                  <span>Envoyer ma réponse</span>
                </button>
              </div>
            </div>

          </div>

        </div>

        <!-- ==============================================
             MODE B: CLASSIQUE (PAGE DÉROULANTE STANDARD)
             ============================================== -->
        <div v-else class="container" style="max-width: 760px;">
          
          <div class="card border-0 rounded-5 shadow-lg bg-white overflow-hidden mb-4">
            
            <!-- Hero Top Accent -->
            <div :style="{ backgroundColor: form.theme_color || '#00A859', height: '10px' }"></div>

            <div class="card-body p-4 p-md-5">
              
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" 
                   style="background: rgba(0, 168, 89, 0.1); color: var(--theme-color); font-size: 0.82rem; font-weight: 700;">
                <span>Par {{ form.user.name }}</span>
              </div>

              <h1 class="h3 fw-black text-dark mb-2">{{ form.title }}</h1>
              <p class="text-muted small mb-4">{{ form.description }}</p>

              <hr class="my-4">

              <form @submit.prevent="submitForm">
                <div class="d-flex flex-column gap-4">
                  
                  <div 
                    v-for="(field, idx) in visibleFields" 
                    :key="field.id" 
                    class="p-4 rounded-4 border bg-light bg-opacity-50"
                  >
                    <label class="form-label fw-bold text-dark d-flex align-items-center justify-content-between mb-2">
                      <span>{{ idx + 1 }}. {{ field.label }} <span v-if="field.is_required" class="text-danger">*</span></span>
                    </label>

                    <p v-if="field.help_text" class="text-muted extra-small mb-3">
                      {{ field.help_text }}
                    </p>

                    <!-- Text -->
                    <input 
                      v-if="field.type === 'text'" 
                      type="text" 
                      v-model="answers[field.id]" 
                      class="form-control rounded-3" 
                      :placeholder="field.placeholder || ''" 
                    />

                    <!-- Textarea -->
                    <textarea 
                      v-else-if="field.type === 'textarea'" 
                      v-model="answers[field.id]" 
                      rows="3" 
                      class="form-control rounded-3" 
                      :placeholder="field.placeholder || ''"
                    ></textarea>

                    <!-- Number -->
                    <input 
                      v-else-if="field.type === 'number'" 
                      type="number" 
                      v-model="answers[field.id]" 
                      class="form-control rounded-3" 
                      :placeholder="field.placeholder || ''" 
                    />

                    <!-- Email -->
                    <input 
                      v-else-if="field.type === 'email'" 
                      type="email" 
                      v-model="answers[field.id]" 
                      class="form-control rounded-3" 
                      :placeholder="field.placeholder || 'exemple@domaine.cm'" 
                    />

                    <!-- Phone -->
                    <input 
                      v-else-if="field.type === 'phone'" 
                      type="tel" 
                      v-model="answers[field.id]" 
                      class="form-control rounded-3" 
                      :placeholder="field.placeholder || '+237 6...'" 
                    />

                    <!-- Radio -->
                    <div v-else-if="field.type === 'radio'" class="d-flex flex-column gap-2">
                      <div v-for="(opt, oIdx) in (field.options || [])" :key="oIdx" class="form-check">
                        <input class="form-check-input" type="radio" :value="opt" v-model="answers[field.id]" :id="'r_' + field.id + '_' + oIdx">
                        <label class="form-check-label small cursor-pointer" :for="'r_' + field.id + '_' + oIdx">{{ opt }}</label>
                      </div>
                    </div>

                    <!-- Select -->
                    <select v-else-if="field.type === 'select'" v-model="answers[field.id]" class="form-select rounded-3">
                      <option :value="null">Sélectionnez une option...</option>
                      <option v-for="(opt, oIdx) in (field.options || [])" :key="oIdx" :value="opt">{{ opt }}</option>
                    </select>

                    <!-- Checkbox -->
                    <div v-else-if="field.type === 'checkbox'" class="d-flex flex-column gap-2">
                      <div v-for="(opt, oIdx) in (field.options || [])" :key="oIdx" class="form-check">
                        <input class="form-check-input" type="checkbox" :checked="isOptionChecked(field.id, opt)" @change="toggleOptionChecked(field.id, opt)" :id="'c_' + field.id + '_' + oIdx">
                        <label class="form-check-label small cursor-pointer" :for="'c_' + field.id + '_' + oIdx">{{ opt }}</label>
                      </div>
                    </div>

                    <!-- City Cameroon -->
                    <div v-else-if="field.type === 'city_cameroon'">
                      <CitySelect2 v-model="answers[field.id]" placeholder="Sélectionnez une ville du Cameroun..." />
                    </div>

                    <!-- Code Snippet -->
                    <div v-else-if="field.type === 'code_snippet'">
                      <textarea 
                        v-model="answers[field.id]" 
                        rows="5" 
                        class="form-control rounded-3 font-monospace small bg-dark text-light" 
                        :placeholder="field.placeholder || '// Collez votre code ici...'"
                      ></textarea>
                    </div>

                    <!-- GitHub Link -->
                    <div v-else-if="field.type === 'github_link'" class="input-group">
                      <span class="input-group-text bg-white"><i class="bi bi-github"></i></span>
                      <input type="url" v-model="answers[field.id]" class="form-control" placeholder="https://github.com/..." />
                    </div>

                    <!-- File -->
                    <div v-else-if="field.type === 'file'">
                      <input type="file" class="form-control rounded-3" @change="e => onFileSelected(field.id, e.target.files[0])" />
                      <div v-if="filesMap[field.id]" class="extra-small text-success fw-bold mt-1">
                        Fichier sélectionné : {{ filesMap[field.id].name }}
                      </div>
                    </div>

                    <!-- Rating -->
                    <div v-else-if="field.type === 'rating'" class="d-flex align-items-center gap-2">
                      <button 
                        v-for="star in 5" 
                        :key="star"
                        type="button" 
                        class="btn p-0 text-warning fs-3 border-0"
                        @click="answers[field.id] = star"
                      >
                        <i :class="star <= Number(answers[field.id]) ? 'bi bi-star-fill' : 'bi bi-star text-muted'"></i>
                      </button>
                      <span v-if="answers[field.id]" class="small fw-bold text-dark ms-2">
                        {{ answers[field.id] }} / 5
                      </span>
                    </div>

                    <!-- Date -->
                    <input v-else-if="field.type === 'date'" type="date" v-model="answers[field.id]" class="form-control rounded-3" />

                  </div>

                </div>

                <div v-if="classicError" class="alert alert-danger py-2 px-3 rounded-3 extra-small mt-3">
                  <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ classicError }}
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                  <button 
                    type="submit" 
                    :disabled="isSubmitting"
                    class="btn btn-lg btn-success rounded-pill px-5 fw-bold text-white shadow d-inline-flex align-items-center gap-2"
                  >
                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="bi bi-send-fill"></i>
                    <span>Envoyer ma réponse</span>
                  </button>
                </div>
              </form>

            </div>
          </div>

        </div>

      </div>

    </main>

    <!-- Footer minimal -->
    <footer class="py-3 text-center extra-small text-muted border-top bg-white">
      Propulsé avec fierté par <strong>Tech237 Forms</strong> — La communauté tech du Cameroun.
    </footer>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import CitySelect2 from '@/Components/CitySelect2.vue'

const props = defineProps({
  form: { type: Object, required: true },
  gate: { type: Object, required: true }
})

const currentStep = ref(0)
const currentStepError = ref('')
const classicError = ref('')
const isSubmitting = ref(false)
const isFinished = ref(false)
const submittedResponseUuid = ref('')
const startTime = ref(Date.now())

const answers = ref({})
const filesMap = ref({})

onMounted(() => {
  // Pre-fill user email/name if logged in
  if (props.gate.current_user) {
    props.form.fields.forEach(f => {
      if (f.type === 'email' && !answers.value[f.id]) {
        answers.value[f.id] = props.gate.current_user.email
      }
    })
  }
})

const isAccepting = computed(() => {
  if (props.gate.is_closed || props.gate.is_expired || props.gate.is_quota_reached) return false
  if (props.gate.requires_login) return false
  if (props.gate.already_submitted) return false
  return true
})

// Evaluates conditional logic on fields dynamically
const visibleFields = computed(() => {
  return props.form.fields.filter(field => {
    if (!field.conditional_logic || !field.conditional_logic.enabled) return true
    const rule = field.conditional_logic
    if (!rule.target_field_id) return true

    const actualVal = answers.value[rule.target_field_id]
    if (actualVal === undefined || actualVal === null) return false

    const targetVal = (rule.value || '').trim().toLowerCase()
    const strActual = String(actualVal).trim().toLowerCase()

    if (rule.operator === 'equals') return strActual === targetVal
    if (rule.operator === 'not_equals') return strActual !== targetVal
    if (rule.operator === 'contains') return strActual.includes(targetVal)
    return true
  })
})

const currentField = computed(() => {
  if (currentStep.value <= 0 || currentStep.value > visibleFields.value.length) return null
  return visibleFields.value[currentStep.value - 1]
})

const progressPercentage = computed(() => {
  if (visibleFields.value.length === 0) return 100
  return Math.round((currentStep.value / visibleFields.value.length) * 100)
})

function goToNextStep() {
  currentStepError.value = ''
  if (currentStep.value > 0) {
    const f = currentField.value
    if (f && f.is_required) {
      const val = answers.value[f.id]
      const file = filesMap.value[f.id]
      const isEmpty = (val === undefined || val === null || val === '') && !file
      if (isEmpty) {
        currentStepError.value = 'Cette question est obligatoire pour continuer.'
        return
      }
    }
  }

  if (currentStep.value < visibleFields.value.length) {
    currentStep.value++
  } else {
    submitForm()
  }
}

function goToPrevStep() {
  currentStepError.value = ''
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

function isOptionChecked(fieldId, option) {
  const current = answers.value[fieldId]
  if (!Array.isArray(current)) return false
  return current.includes(option)
}

function toggleOptionChecked(fieldId, option) {
  if (!Array.isArray(answers.value[fieldId])) {
    answers.value[fieldId] = []
  }
  const idx = answers.value[fieldId].indexOf(option)
  if (idx > -1) {
    answers.value[fieldId].splice(idx, 1)
  } else {
    answers.value[fieldId].push(option)
  }
}

function onFileSelected(fieldId, file) {
  if (file) {
    filesMap.value[fieldId] = file
    answers.value[fieldId] = file.name
  }
}

async function submitForm() {
  currentStepError.value = ''
  classicError.value = ''

  // Validate required in classic mode
  for (const f of visibleFields.value) {
    if (f.is_required) {
      const val = answers.value[f.id]
      const file = filesMap.value[f.id]
      if ((val === undefined || val === null || val === '') && !file) {
        const msg = `La question « ${f.label} » est obligatoire.`
        currentStepError.value = msg
        classicError.value = msg
        return
      }
    }
  }

  isSubmitting.value = true
  const elapsedSec = Math.round((Date.now() - startTime.value) / 1000)

  const formData = new FormData()
  formData.append('answers', JSON.stringify(answers.value))
  formData.append('completed_in_seconds', elapsedSec)

  // Attach files
  for (const fieldId in filesMap.value) {
    formData.append(`file_${fieldId}`, filesMap.value[fieldId])
  }

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

  try {
    const res = await fetch(`/f/${props.form.slug}/repondre`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: formData
    })

    const data = await res.json().catch(() => ({}))

    if (res.ok && data.success) {
      isFinished.value = true
      submittedResponseUuid.value = data.response_uuid || ''
      if (data.redirect_url) {
        setTimeout(() => {
          window.location.href = data.redirect_url
        }, 2000)
      }
    } else {
      const err = data.error || 'Erreur lors de la soumission de votre réponse.'
      currentStepError.value = err
      classicError.value = err
    }
  } catch (e) {
    currentStepError.value = 'Erreur de connexion. Veuillez réessayer.'
    classicError.value = 'Erreur de connexion. Veuillez réessayer.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.focus-theme:focus {
  outline: none;
  border-color: var(--theme-color) !important;
  box-shadow: none;
}
.cursor-pointer {
  cursor: pointer;
}
.choice-item:hover {
  border-color: #cbd5e1;
}
.extra-small {
  font-size: 0.75rem;
}
.animate-slide-up {
  animation: slideUp 0.35s ease-out;
}
.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(18px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
