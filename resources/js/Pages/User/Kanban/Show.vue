<template>
  <FrontLayout>
    <Head>
      <title>{{ board.title }} | Tableau Kanban Tech237</title>
    </Head>

    <div class="kanban-workspace min-vh-100 d-flex flex-column" style="background-color: #f1f5f9;">
      
      <!-- Top Kanban Navigation Bar -->
      <header class="bg-white border-bottom px-4 py-3 shadow-xs">
        <div class="container-fluid px-0">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            
            <!-- Left: Board Title & Meta -->
            <div>
              <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <Link href="/espace-utilisateur/kanban" class="badge bg-light text-dark border text-decoration-none py-1 px-2 rounded-pill small">
                  <i class="bi bi-arrow-left me-1"></i>Tableaux
                </Link>

                <span v-if="board.project" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2 small fw-bold">
                  <i class="bi bi-folder-fill me-1"></i>
                  <Link :href="'/projet/' + (board.project.slug || board.project.id)" class="text-decoration-none text-primary" target="_blank">
                    {{ board.project.name }}
                  </Link>
                </span>

                <span v-if="board.is_public" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2 extra-small fw-bold">
                  <i class="bi bi-globe me-1"></i>Roadmap Publique Active
                </span>
              </div>

              <div class="d-flex align-items-center gap-2">
                <h1 class="h4 fw-black text-dark mb-0">{{ board.title }}</h1>
                <button v-if="canEdit" @click="openBoardSettingsModal" class="btn btn-sm btn-link text-muted p-0 ms-1" title="Modifier le tableau">
                  <i class="bi bi-gear-fill"></i>
                </button>
              </div>
            </div>

            <!-- Right: Team avatars, Search & Actions -->
            <div class="d-flex align-items-center gap-3 flex-wrap">
              
              <!-- Team Avatars -->
              <div v-if="teamMembers && teamMembers.length > 0" class="d-flex align-items-center me-2">
                <div 
                  v-for="(member, mIdx) in teamMembers.slice(0, 5)" 
                  :key="member.id"
                  class="rounded-circle border border-2 border-white shadow-xs text-white d-flex align-items-center justify-content-center fw-bold"
                  :style="{ 
                    width: '32px', 
                    height: '32px', 
                    marginLeft: mIdx > 0 ? '-8px' : '0', 
                    background: getAvatarColor(mIdx),
                    fontSize: '11px',
                    zIndex: 10 - mIdx
                  }"
                  :title="member.name"
                >
                  <img v-if="member.avatar" :src="'/' + member.avatar" class="w-100 h-100 rounded-circle object-fit-cover" :alt="member.name" />
                  <span v-else>{{ member.name.charAt(0).toUpperCase() }}</span>
                </div>
              </div>

              <!-- Search Bar -->
              <div class="input-group input-group-sm" style="width: 200px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input 
                  type="text" 
                  v-model="searchFilter" 
                  class="form-control border-start-0 ps-0" 
                  placeholder="Filtrer les cartes..." 
                />
              </div>

              <!-- Priority Filter -->
              <select v-model="priorityFilter" class="form-select form-select-sm bg-white" style="width: 140px;">
                <option value="all">Toutes priorités</option>
                <option value="urgent">🔴 Urgent</option>
                <option value="high">🟠 Haute</option>
                <option value="medium">🔵 Moyenne</option>
                <option value="low">⚪ Faible</option>
              </select>

              <!-- Add Column Button -->
              <button 
                v-if="canEdit" 
                @click="showAddColumnModal = true" 
                class="btn btn-sm btn-dark rounded-pill px-3 fw-bold d-flex align-items-center gap-1 shadow-xs"
              >
                <i class="bi bi-plus-lg"></i>
                <span class="d-none d-sm-inline">Colonne</span>
              </button>

              <!-- Guide Link -->
              <Link 
                href="/espace-utilisateur/kanban/guide" 
                class="btn btn-sm btn-white bg-white border rounded-pill px-3 fw-semibold text-muted shadow-2xs"
                title="Consulter le guide pratique"
              >
                <i class="bi bi-question-circle me-1 text-primary"></i><span class="d-none d-md-inline">Guide</span>
              </Link>

              <!-- Public Roadmap Link if enabled -->
              <a 
                v-if="board.is_public && board.project" 
                :href="'/projet/' + (board.project.slug || board.project.id) + '/roadmap'" 
                target="_blank"
                class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold"
                title="Voir la Roadmap publique"
              >
                <i class="bi bi-box-arrow-up-right me-1"></i>Vue Publique
              </a>

            </div>

          </div>
        </div>
      </header>

      <!-- Kanban Canvas (Responsive Wrap: never exceeds screen width, wraps to next line) -->
      <main class="kanban-canvas flex-grow-1 p-3 p-md-4">
        <div class="row g-4 align-items-start">
          
          <!-- Column Item -->
          <div 
            v-for="column in board.columns" 
            :key="column.id" 
            class="col-12 col-md-6 col-lg-4 col-xl-3"
          >
            <div 
              class="kanban-column rounded-4 bg-white shadow-xs d-flex flex-column border h-100"
              :class="{ 'border-success border-2': column.is_done_column }"
              style="min-height: 240px;"
              @dragover.prevent="onDragOverColumn($event, column)"
              @drop="onDropOnColumn($event, column)"
            >
            <!-- Column Header -->
            <div class="column-header p-3 border-bottom d-flex align-items-center justify-content-between bg-white rounded-top-4">
              <div class="d-flex align-items-center gap-2">
                <span class="rounded-pill d-inline-block" :style="{ width: '12px', height: '12px', backgroundColor: column.color || '#3b82f6' }"></span>
                <h3 class="h6 fw-bold text-dark mb-0">{{ column.title }}</h3>
                <span class="badge bg-slate-100 text-muted rounded-pill px-2 py-0 extra-small" style="background-color: #f1f5f9;">
                  {{ getFilteredCards(column).length }}
                </span>
              </div>

              <!-- Column Actions Dropdown -->
              <div v-if="canEdit" class="dropdown">
                <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-1">
                  <li>
                    <button class="dropdown-item small" @click="openEditColumnModal(column)">
                      <i class="bi bi-pencil me-2 text-muted"></i>Modifier la colonne
                    </button>
                  </li>
                  <li>
                    <button class="dropdown-item small text-danger" @click="confirmDeleteColumn(column)">
                      <i class="bi bi-trash me-2"></i>Supprimer la colonne
                    </button>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Cards Container -->
            <div class="cards-list p-2 flex-grow-1 overflow-auto d-flex flex-column gap-2" style="min-height: 80px; max-height: 540px;">
              
              <!-- Kanban Card Item -->
              <div 
                v-for="card in getFilteredCards(column)" 
                :key="card.id"
                class="kanban-card card border rounded-3 p-3 bg-white shadow-xs cursor-grab transition"
                :class="{ 
                  'opacity-50': draggedCard && draggedCard.id === card.id,
                  'border-success bg-success-subtle bg-opacity-10': card.devlog_published
                }"
                draggable="true"
                @dragstart="onDragStart($event, card, column)"
                @dragend="onDragEnd"
                @click="openCardModal(card)"
              >
                <!-- Labels pills & priority badge -->
                <div class="d-flex align-items-center justify-content-between gap-1 mb-2 flex-wrap">
                  <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span 
                      v-for="(label, lIdx) in (card.labels || [])" 
                      :key="lIdx"
                      class="badge bg-light text-dark border extra-small rounded-pill px-2 py-0"
                    >
                      {{ label }}
                    </span>
                  </div>

                  <!-- Priority Badge -->
                  <span class="badge rounded-pill px-2 py-0 extra-small fw-bold" :class="getPriorityClass(card.priority)">
                    {{ getPriorityLabel(card.priority) }}
                  </span>
                </div>

                <!-- Card Title -->
                <h4 class="card-title h6 fw-bold text-dark mb-2" style="font-size: 0.92rem; line-height: 1.4;">
                  {{ card.title }}
                </h4>

                <!-- Checklist Preview -->
                <div v-if="card.checklist && card.checklist.length > 0" class="mb-2">
                  <div class="d-flex align-items-center gap-1 extra-small text-muted mb-1">
                    <i class="bi bi-check2-square"></i>
                    <span>{{ getChecklistCount(card) }}</span>
                  </div>
                  <div class="progress rounded-pill" style="height: 4px;">
                    <div 
                      class="progress-bar rounded-pill bg-success" 
                      :style="{ width: getChecklistPercentage(card) + '%' }"
                    ></div>
                  </div>
                </div>

                <!-- Footer: Due date, Devlog badge & Assignee -->
                <div class="d-flex align-items-center justify-content-between pt-2 mt-1 border-top extra-small text-muted">
                  <div class="d-flex align-items-center gap-2">
                    <!-- Due date -->
                    <span v-if="card.due_date" class="d-flex align-items-center gap-1" :class="{ 'text-danger fw-bold': isOverdue(card.due_date) }">
                      <i class="bi bi-calendar3"></i>
                      <span>{{ formatDateShort(card.due_date) }}</span>
                    </span>

                    <!-- Devlog Published badge -->
                    <span v-if="card.devlog_published" class="badge bg-success text-white extra-small rounded-pill" title="Publié dans le Devlog">
                      <i class="bi bi-rocket-takeoff-fill"></i> Devlog
                    </span>
                  </div>

                  <!-- Assignee Avatar -->
                  <div v-if="card.assignee" class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 24px; height: 24px; font-size: 9px; background: #0B2559;" :title="card.assignee.name">
                    <img v-if="card.assignee.avatar" :src="'/' + card.assignee.avatar" class="w-100 h-100 rounded-circle object-fit-cover" :alt="card.assignee.name" />
                    <span v-else>{{ card.assignee.name.charAt(0).toUpperCase() }}</span>
                  </div>
                </div>

              </div>

              <!-- Quick Add Card in Column -->
              <div v-if="canEdit" class="mt-1">
                <div v-if="activeAddCardColumnId === column.id" class="card border rounded-3 p-2 bg-light shadow-xs">
                  <textarea 
                    v-model="quickCardTitle" 
                    @keyup.enter.exact="submitQuickCard(column)" 
                    class="form-control form-control-sm mb-2 rounded-2" 
                    placeholder="Saisissez le titre de la tâche..." 
                    rows="2"
                    autofocus
                  ></textarea>
                  <div class="d-flex align-items-center justify-content-between">
                    <button @click="submitQuickCard(column)" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" :disabled="!quickCardTitle.trim()">
                      Ajouter
                    </button>
                    <button @click="activeAddCardColumnId = null" class="btn btn-sm btn-link text-muted p-0">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                </div>
                <button 
                  v-else 
                  @click="openQuickAdd(column)" 
                  class="btn btn-sm btn-link text-muted text-decoration-none w-100 text-start px-2 py-2 rounded-3 hover-bg-light d-flex align-items-center gap-2"
                >
                  <i class="bi bi-plus-lg"></i>
                  <span>Ajouter une carte</span>
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- Add Column Inline Card -->
        <div v-if="canEdit" class="col-12 col-md-6 col-lg-4 col-xl-3">
          <button 
            @click="showAddColumnModal = true" 
            class="btn btn-white bg-white border border-dashed rounded-4 p-4 text-center w-100 text-muted hover-dark shadow-xs h-100 d-flex flex-column align-items-center justify-content-center"
            style="min-height: 180px;"
          >
            <i class="bi bi-plus-circle fs-2 d-block mb-2 text-primary"></i>
            <span class="fw-bold">Ajouter une nouvelle colonne</span>
          </button>
        </div>

      </div>
    </main>

    </div>

    <!-- 1. Card Detail & Edit Modal -->
    <div v-if="activeCard" class="modal-backdrop fade show" style="background: rgba(15, 23, 42, 0.65); z-index: 1050;"></div>
    <div v-if="activeCard" class="modal fade show d-block" style="z-index: 1055;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
          
          <!-- Modal Header with Title & Sticky Accessible Save Button -->
          <div class="modal-header border-bottom py-3 px-4 bg-light d-flex align-items-center justify-content-between gap-3 sticky-top">
            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
              <i class="bi bi-credit-card-2-front text-primary fs-5 flex-shrink-0"></i>
              <input 
                v-if="canEdit"
                type="text" 
                v-model="editCardForm.title" 
                class="form-control form-control-lg fw-bold border-0 bg-transparent p-0 fs-5 text-dark"
                placeholder="Titre de la tâche..."
              />
              <span v-else class="fs-5 fw-bold text-dark text-truncate">{{ activeCard.title }}</span>
            </div>

            <!-- Header Action: Always visible save button without scrolling -->
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
              <button 
                v-if="canEdit" 
                type="button" 
                class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1 shadow-sm text-nowrap" 
                @click="saveCardChanges" 
                :disabled="isSavingCard"
              >
                <i class="bi bi-check2-circle fs-6"></i>
                <span>{{ isSavingCard ? 'Enregistrement...' : 'Enregistrer' }}</span>
              </button>
              <button type="button" class="btn-close" @click="closeCardModal"></button>
            </div>
          </div>

          <div class="modal-body p-4">
            <div class="row g-4">
              
              <!-- Left Column: Description & Checklist -->
              <div class="col-lg-8">
                
                <!-- Description -->
                <div class="mb-4">
                  <label class="form-label fw-bold text-dark d-flex align-items-center gap-2 small">
                    <i class="bi bi-text-paragraph text-muted"></i>
                    <span>Description &amp; Spécifications</span>
                  </label>
                  <textarea 
                    v-if="canEdit"
                    v-model="editCardForm.description" 
                    class="form-control rounded-3" 
                    rows="4" 
                    placeholder="Détails techniques, étapes, contexte..."
                  ></textarea>
                  <div v-else class="p-3 bg-light rounded-3 text-secondary small">
                    {{ activeCard.description || 'Aucune description.' }}
                  </div>
                </div>

                <!-- Interactive Checklist -->
                <div class="mb-4">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-2 small mb-0">
                      <i class="bi bi-check2-square text-muted"></i>
                      <span>Checklist de validation</span>
                    </label>
                    <span v-if="editCardForm.checklist && editCardForm.checklist.length > 0" class="extra-small text-muted fw-bold">
                      {{ getModalChecklistPercentage() }}% terminé
                    </span>
                  </div>

                  <!-- Checklist Progress bar -->
                  <div v-if="editCardForm.checklist && editCardForm.checklist.length > 0" class="progress rounded-pill mb-3" style="height: 6px;">
                    <div class="progress-bar rounded-pill bg-success" :style="{ width: getModalChecklistPercentage() + '%' }"></div>
                  </div>

                  <!-- Items list -->
                  <div class="d-flex flex-column gap-2 mb-3">
                    <div 
                      v-for="(item, cIdx) in (editCardForm.checklist || [])" 
                      :key="cIdx" 
                      class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light"
                    >
                      <div class="form-check d-flex align-items-center gap-2 mb-0">
                        <input 
                          class="form-check-input cursor-pointer" 
                          type="checkbox" 
                          :id="'chk_' + cIdx"
                          v-model="item.done"
                          :disabled="!canEdit"
                        />
                        <label 
                          class="form-check-label small cursor-pointer" 
                          :for="'chk_' + cIdx"
                          :class="{ 'text-decoration-line-through text-muted': item.done, 'text-dark fw-semibold': !item.done }"
                        >
                          {{ item.text }}
                        </label>
                      </div>

                      <button v-if="canEdit" @click="removeChecklistItem(cIdx)" class="btn btn-link btn-sm text-danger p-0">
                        <i class="bi bi-x"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Add checklist item -->
                  <div v-if="canEdit" class="d-flex align-items-center gap-2">
                    <input 
                      type="text" 
                      v-model="newChecklistText" 
                      @keyup.enter="addChecklistItem" 
                      class="form-control form-control-sm rounded-pill" 
                      placeholder="Ajouter une sous-tâche..." 
                    />
                    <button @click="addChecklistItem" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold" :disabled="!newChecklistText.trim()">
                      Ajouter
                    </button>
                  </div>
                </div>

                <!-- Magic Bridge: Publish to Project Devlog -->
                <div v-if="board.project_id && canEdit" class="p-3 rounded-4 border bg-emerald-50" style="background-color: #ecfdf5; border-color: #a7f3d0 !important;">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <span class="rounded-circle p-2 bg-success text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="bi bi-rocket-takeoff-fill" style="font-size: 13px;"></i>
                      </span>
                      <h6 class="fw-bold text-success-emphasis mb-0">Transformer en Étape Devlog</h6>
                    </div>
                    <span v-if="activeCard.devlog_published" class="badge bg-success rounded-pill px-2 py-1 extra-small">
                      <i class="bi bi-check-circle-fill me-1"></i>Déjà publié
                    </span>
                  </div>

                  <p class="extra-small text-muted mb-3" style="line-height: 1.5;">
                    Cette tâche est achevée ? Publiez-la directement dans le journal de bord (Devlog) du projet <strong>« {{ board.project?.name }} »</strong> pour informer la communauté et notifier vos abonnés par email !
                  </p>

                  <button 
                    @click="publishCardToDevlog" 
                    class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-2"
                    :disabled="isPublishingDevlog"
                  >
                    <i class="bi bi-send-fill"></i>
                    <span>{{ activeCard.devlog_published ? 'Republier / Mettre à jour dans le Devlog' : 'Publier dans le Devlog (+ Alerte Email)' }}</span>
                  </button>
                </div>

              </div>

              <!-- Right Column: Settings & Meta -->
              <div class="col-lg-4 border-start">
                
                <!-- Priority Selector -->
                <div class="mb-3">
                  <label class="form-label small fw-bold text-dark mb-1">Priorité</label>
                  <select v-if="canEdit" v-model="editCardForm.priority" class="form-select form-select-sm rounded-3">
                    <option value="low">⚪ Faible</option>
                    <option value="medium">🔵 Moyenne</option>
                    <option value="high">🟠 Haute</option>
                    <option value="urgent">🔴 Urgente</option>
                  </select>
                  <span v-else class="badge rounded-pill px-3 py-1" :class="getPriorityClass(activeCard.priority)">
                    {{ getPriorityLabel(activeCard.priority) }}
                  </span>
                </div>

                <!-- Assignee -->
                <div class="mb-3">
                  <label class="form-label small fw-bold text-dark mb-1">Responsable assigné</label>
                  <select v-if="canEdit" v-model="editCardForm.assigned_to" class="form-select form-select-sm rounded-3">
                    <option :value="null">-- Non assigné --</option>
                    <option v-for="member in teamMembers" :key="member.id" :value="member.id">
                      {{ member.name }}
                    </option>
                  </select>
                  <div v-else-if="activeCard.assignee" class="small text-dark fw-semibold">
                    {{ activeCard.assignee.name }}
                  </div>
                  <div v-else class="small text-muted">Non assigné</div>
                </div>

                <!-- Due Date -->
                <div class="mb-3">
                  <label class="form-label small fw-bold text-dark mb-1">Date d'échéance</label>
                  <input 
                    v-if="canEdit"
                    type="date" 
                    v-model="editCardForm.due_date" 
                    class="form-control form-control-sm rounded-3" 
                  />
                  <div v-else class="small text-muted">
                    {{ activeCard.due_date ? formatDateShort(activeCard.due_date) : 'Aucune date' }}
                  </div>
                </div>

                <!-- Labels -->
                <div class="mb-4">
                  <label class="form-label small fw-bold text-dark mb-1">Étiquettes / Tags</label>
                  <div class="d-flex align-items-center gap-1 flex-wrap mb-2">
                    <span 
                      v-for="(lbl, lIdx) in (editCardForm.labels || [])" 
                      :key="lIdx" 
                      class="badge bg-secondary-subtle text-dark border rounded-pill px-2 py-1 extra-small d-inline-flex align-items-center gap-1"
                    >
                      <span>{{ lbl }}</span>
                      <i v-if="canEdit" @click="removeLabel(lIdx)" class="bi bi-x cursor-pointer"></i>
                    </span>
                  </div>

                  <div v-if="canEdit" class="input-group input-group-sm">
                    <input 
                      type="text" 
                      v-model="newLabelText" 
                      @keyup.enter="addLabel" 
                      class="form-control" 
                      placeholder="ex: API, UI, Bug..." 
                    />
                    <button @click="addLabel" class="btn btn-outline-secondary" type="button">
                      +
                    </button>
                  </div>
                </div>

                <!-- Delete card button -->
                <div v-if="canEdit" class="pt-3 border-top">
                  <button @click="deleteActiveCard" class="btn btn-sm btn-outline-danger w-100 rounded-pill">
                    <i class="bi bi-trash me-1"></i> Supprimer cette carte
                  </button>
                </div>

              </div>

            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer border-top bg-light py-2 px-4 d-flex justify-content-between">
            <span class="extra-small text-muted">
              Modifié le {{ formatDateShort(activeCard.updated_at) }}
            </span>

            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-light rounded-pill px-3" @click="closeCardModal">
                Fermer
              </button>
              <button v-if="canEdit" type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm" @click="saveCardChanges" :disabled="isSavingCard">
                {{ isSavingCard ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- 2. Add / Edit Column Modal -->
    <div v-if="showAddColumnModal || editingColumn" class="modal-backdrop fade show" style="background: rgba(15, 23, 42, 0.65); z-index: 1050;"></div>
    <div v-if="showAddColumnModal || editingColumn" class="modal fade show d-block" style="z-index: 1055;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
          <form @submit.prevent="submitColumnForm">
            <div class="modal-header border-bottom py-3 px-4 bg-light d-flex align-items-center justify-content-between">
              <h5 class="modal-title fw-bold text-dark mb-0">
                {{ editingColumn ? 'Modifier la colonne' : 'Ajouter une colonne' }}
              </h5>
              <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-sm">
                  Enregistrer
                </button>
                <button type="button" class="btn-close" @click="closeColumnModal"></button>
              </div>
            </div>
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Nom de la colonne <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  v-model="columnForm.title" 
                  class="form-control rounded-3" 
                  placeholder="ex: En revue de code" 
                  required 
                  maxlength="60"
                />
              </div>

              <!-- Color picker -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Couleur de l'indicateur</label>
                <div class="d-flex align-items-center gap-2">
                  <input type="color" v-model="columnForm.color" class="form-control form-control-color rounded-circle p-1" style="width: 42px; height: 42px;" />
                  <span class="small font-monospace text-muted">{{ columnForm.color }}</span>
                </div>
              </div>

              <!-- Done Column switch -->
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="doneColSwitch" v-model="columnForm.is_done_column" />
                <label class="form-check-label small fw-semibold text-dark" for="doneColSwitch">
                  Marquer comme colonne de « Tâches Terminées »
                </label>
                <div class="extra-small text-muted">
                  Permet de calculer le taux de complétion du projet et d'activer l'export en Devlog.
                </div>
              </div>

            </div>
            <div class="modal-footer border-top bg-light py-2 px-4 d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-sm btn-light rounded-pill px-3" @click="closeColumnModal">Annuler</button>
              <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 3. Board Settings Modal -->
    <div v-if="showBoardSettingsModal" class="modal-backdrop fade show" style="background: rgba(15, 23, 42, 0.65); z-index: 1050;"></div>
    <div v-if="showBoardSettingsModal" class="modal fade show d-block" style="z-index: 1055;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
          <form @submit.prevent="submitBoardSettings">
            <div class="modal-header border-bottom py-3 px-4 bg-light d-flex align-items-center justify-content-between">
              <h5 class="modal-title fw-bold text-dark mb-0">Paramètres du Tableau</h5>
              <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-sm">
                  Enregistrer
                </button>
                <button type="button" class="btn-close" @click="showBoardSettingsModal = false"></button>
              </div>
            </div>
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Titre du tableau <span class="text-danger">*</span></label>
                <input type="text" v-model="boardSettingsForm.title" class="form-control rounded-3" required maxlength="120" />
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Description</label>
                <textarea v-model="boardSettingsForm.description" class="form-control rounded-3" rows="3" maxlength="500"></textarea>
              </div>

              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="boardPublicSwitch" v-model="boardSettingsForm.is_public" />
                <label class="form-check-label small fw-semibold text-dark" for="boardPublicSwitch">
                  Rendre la Roadmap publique
                </label>
                <div class="extra-small text-muted">
                  Les utilisateurs pourront voir l'avancement du projet sans pouvoir modifier vos cartes.
                </div>
              </div>
            </div>
            <div class="modal-footer border-top bg-light py-2 px-4 d-flex justify-content-between">
              <button type="button" @click="deleteBoard" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-trash me-1"></i>Supprimer le tableau
              </button>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3" @click="showBoardSettingsModal = false">Annuler</button>
                <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm">Enregistrer</button>
              </div>
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
  board: { type: Object, required: true },
  canEdit: { type: Boolean, default: false },
  teamMembers: { type: Array, default: () => [] },
})

const searchFilter = ref('')
const priorityFilter = ref('all')

// Drag & Drop State
const draggedCard = ref(null)
const sourceColumn = ref(null)

function onDragStart(event, card, column) {
  if (!props.canEdit) return
  draggedCard.value = card
  sourceColumn.value = column
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', card.id.toString())
}

function onDragEnd() {
  draggedCard.value = null
  sourceColumn.value = null
}

function onDragOverColumn(event, targetColumn) {
  if (!props.canEdit || !draggedCard.value) return
  event.dataTransfer.dropEffect = 'move'
}

function onDropOnColumn(event, targetColumn) {
  if (!props.canEdit || !draggedCard.value) return
  event.preventDefault()

  const card = draggedCard.value
  const originColumn = sourceColumn.value

  // Move in local state immediately for instant feedback
  if (originColumn && originColumn.id !== targetColumn.id) {
    const idx = originColumn.cards.findIndex(c => c.id === card.id)
    if (idx !== -1) {
      originColumn.cards.splice(idx, 1)
      card.column_id = targetColumn.id
      targetColumn.cards.push(card)
    }
  }

  // Send async request to backend
  fetch(`/espace-utilisateur/kanban/cards/${card.id}/move`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify({
      column_id: targetColumn.id,
      order: targetColumn.cards.length,
    })
  }).then(r => r.json()).then(data => {
    if (data && data.success && data.is_done_column && props.board.project_id && !card.devlog_published) {
      // Suggest publishing to Devlog!
      if (confirm(`Bravo pour avoir complété « ${card.title} » !\nSouhaitez-vous publier cette étape dans le Devlog public du projet ?`)) {
        openCardModal(card)
      }
    }
  }).catch(err => {
    console.error('Move error:', err)
  })

  draggedCard.value = null
  sourceColumn.value = null
}

// Quick Add Card
const activeAddCardColumnId = ref(null)
const quickCardTitle = ref('')

function openQuickAdd(column) {
  activeAddCardColumnId.value = column.id
  quickCardTitle.value = ''
}

function submitQuickCard(column) {
  if (!quickCardTitle.value.trim()) return

  fetch(`/espace-utilisateur/kanban/columns/${column.id}/cards`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify({
      title: quickCardTitle.value.trim(),
      priority: 'medium',
    })
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      column.cards.push(data.card)
      quickCardTitle.value = ''
      activeAddCardColumnId.value = null
    }
  }).catch(err => {
    console.error('Error adding card:', err)
  })
}

// Card Detail Modal State
const activeCard = ref(null)
const editCardForm = ref({
  title: '',
  description: '',
  priority: 'medium',
  due_date: null,
  assigned_to: null,
  labels: [],
  checklist: [],
})
const newChecklistText = ref('')
const newLabelText = ref('')
const isSavingCard = ref(false)
const isPublishingDevlog = ref(false)

function openCardModal(card) {
  activeCard.value = card
  editCardForm.value = {
    title: card.title || '',
    description: card.description || '',
    priority: card.priority || 'medium',
    due_date: card.due_date ? card.due_date.substring(0, 10) : null,
    assigned_to: card.assigned_to || null,
    labels: Array.isArray(card.labels) ? [...card.labels] : [],
    checklist: Array.isArray(card.checklist) ? card.checklist.map(i => ({ ...i })) : [],
  }
}

function closeCardModal() {
  activeCard.value = null
}

function addChecklistItem() {
  if (!newChecklistText.value.trim()) return
  if (!Array.isArray(editCardForm.value.checklist)) {
    editCardForm.value.checklist = []
  }
  editCardForm.value.checklist.push({
    text: newChecklistText.value.trim(),
    done: false,
  })
  newChecklistText.value = ''
}

function removeChecklistItem(idx) {
  editCardForm.value.checklist.splice(idx, 1)
}

function getModalChecklistPercentage() {
  const items = editCardForm.value.checklist || []
  if (items.length === 0) return 0
  const done = items.filter(i => i.done).length
  return Math.round((done / items.length) * 100)
}

function addLabel() {
  if (!newLabelText.value.trim()) return
  if (!Array.isArray(editCardForm.value.labels)) {
    editCardForm.value.labels = []
  }
  const tag = newLabelText.value.trim()
  if (!editCardForm.value.labels.includes(tag)) {
    editCardForm.value.labels.push(tag)
  }
  newLabelText.value = ''
}

function removeLabel(idx) {
  editCardForm.value.labels.splice(idx, 1)
}

function saveCardChanges() {
  if (!activeCard.value) return
  isSavingCard.value = true

  fetch(`/espace-utilisateur/kanban/cards/${activeCard.value.id}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify(editCardForm.value)
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      Object.assign(activeCard.value, data.card)
      closeCardModal()
    }
  }).catch(err => {
    console.error('Save error:', err)
  }).finally(() => {
    isSavingCard.value = false
  })
}

function deleteActiveCard() {
  if (!activeCard.value || !confirm('Supprimer définitivement cette carte ?')) return

  const cardId = activeCard.value.id
  fetch(`/espace-utilisateur/kanban/cards/${cardId}`, {
    method: 'DELETE',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    }
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      for (const col of props.board.columns) {
        const idx = col.cards.findIndex(c => c.id === cardId)
        if (idx !== -1) {
          col.cards.splice(idx, 1)
          break
        }
      }
      closeCardModal()
    }
  })
}

function publishCardToDevlog() {
  if (!activeCard.value || isPublishingDevlog.value) return
  isPublishingDevlog.value = true

  fetch(`/espace-utilisateur/kanban/cards/${activeCard.value.id}/devlog`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify({
      title: editCardForm.value.title || activeCard.value.title,
      description: editCardForm.value.description || activeCard.value.description,
    })
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      activeCard.value.devlog_published = true
      alert(data.message || 'Étape publiée dans le Devlog !')
      closeCardModal()
    }
  }).catch(err => {
    console.error('Devlog publish error:', err)
  }).finally(() => {
    isPublishingDevlog.value = false
  })
}

// Column Management
const showAddColumnModal = ref(false)
const editingColumn = ref(null)
const columnForm = ref({
  title: '',
  color: '#3b82f6',
  is_done_column: false,
})

function openEditColumnModal(col) {
  editingColumn.value = col
  columnForm.value = {
    title: col.title,
    color: col.color || '#3b82f6',
    is_done_column: !!col.is_done_column,
  }
}

function closeColumnModal() {
  showAddColumnModal.value = false
  editingColumn.value = null
  columnForm.value = { title: '', color: '#3b82f6', is_done_column: false }
}

function submitColumnForm() {
  if (editingColumn.value) {
    router.put(`/espace-utilisateur/kanban/columns/${editingColumn.value.id}`, columnForm.value, {
      onSuccess: () => closeColumnModal()
    })
  } else {
    router.post(`/espace-utilisateur/kanban/boards/${props.board.id}/columns`, columnForm.value, {
      onSuccess: () => closeColumnModal()
    })
  }
}

function confirmDeleteColumn(col) {
  if (confirm(`Supprimer la colonne « ${col.title} » et toutes ses cartes ?`)) {
    router.delete(`/espace-utilisateur/kanban/columns/${col.id}`)
  }
}

// Board Settings Modal
const showBoardSettingsModal = ref(false)
const boardSettingsForm = ref({
  title: props.board.title,
  description: props.board.description || '',
  is_public: !!props.board.is_public,
})

function openBoardSettingsModal() {
  boardSettingsForm.value = {
    title: props.board.title,
    description: props.board.description || '',
    is_public: !!props.board.is_public,
  }
  showBoardSettingsModal.value = true
}

function submitBoardSettings() {
  router.put(`/espace-utilisateur/kanban/boards/${props.board.id}`, boardSettingsForm.value, {
    onSuccess: () => {
      showBoardSettingsModal.value = false
    }
  })
}

function deleteBoard() {
  if (confirm('Voulez-vous vraiment supprimer définitivement ce tableau et tout son contenu ?')) {
    router.delete(`/espace-utilisateur/kanban/boards/${props.board.id}`)
  }
}

// Card helpers & filters
function getFilteredCards(column) {
  let cards = column.cards || []
  if (searchFilter.value.trim()) {
    const q = searchFilter.value.toLowerCase()
    cards = cards.filter(c => 
      c.title.toLowerCase().includes(q) || 
      (c.description && c.description.toLowerCase().includes(q)) ||
      (Array.isArray(c.labels) && c.labels.some(l => l.toLowerCase().includes(q)))
    )
  }
  if (priorityFilter.value !== 'all') {
    cards = cards.filter(c => c.priority === priorityFilter.value)
  }
  return cards
}

function getPriorityLabel(p) {
  const map = {
    urgent: 'Urgent',
    high: 'Haute',
    medium: 'Moyenne',
    low: 'Faible',
  }
  return map[p] || 'Moyenne'
}

function getPriorityClass(p) {
  const map = {
    urgent: 'bg-danger text-white',
    high: 'bg-warning text-dark',
    medium: 'bg-primary-subtle text-primary',
    low: 'bg-light text-secondary border',
  }
  return map[p] || 'bg-light text-secondary'
}

function getChecklistCount(card) {
  const items = card.checklist || []
  const done = items.filter(i => i.done).length
  return `${done}/${items.length}`
}

function getChecklistPercentage(card) {
  const items = card.checklist || []
  if (items.length === 0) return 0
  const done = items.filter(i => i.done).length
  return Math.round((done / items.length) * 100)
}

function isOverdue(dateStr) {
  if (!dateStr) return false
  const d = new Date(dateStr)
  const today = new Date()
  today.setHours(0,0,0,0)
  return d < today
}

function formatDateShort(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}

function getAvatarColor(idx) {
  const colors = ['#00A859', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4']
  return colors[idx % colors.length]
}
</script>

<style scoped>
.kanban-workspace {
  background-color: #f1f5f9;
}
.kanban-canvas::-webkit-scrollbar {
  height: 8px;
}
.kanban-canvas::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 4px;
}
.cards-list::-webkit-scrollbar {
  width: 5px;
}
.cards-list::-webkit-scrollbar-thumb {
  background-color: #e2e8f0;
  border-radius: 4px;
}
.kanban-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.kanban-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.06) !important;
}
.cursor-grab {
  cursor: grab;
}
.cursor-grab:active {
  cursor: grabbing;
}
.hover-bg-light:hover {
  background-color: #f8fafc;
  color: #0f172a !important;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
