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
                  id="kanbanSearchInput"
                  type="text" 
                  v-model="searchFilter" 
                  class="form-control border-start-0 ps-0" 
                  placeholder="Filtrer (/)..." 
                  title="Raccourci: Touche / ou F"
                />
              </div>

              <!-- Quick Filter "Mes tâches uniquement" -->
              <button 
                type="button"
                @click="myTasksOnly = !myTasksOnly"
                class="btn btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-1 transition shadow-2xs"
                :class="myTasksOnly ? 'btn-primary text-white shadow-xs' : 'btn-white bg-white text-dark border'"
                title="Afficher uniquement les tâches qui me sont assignées (Raccourci: Touche M)"
              >
                <i class="bi bi-person-check-fill" :class="myTasksOnly ? 'text-white' : 'text-primary'"></i>
                <span>Mes tâches</span>
              </button>

              <!-- Priority Filter -->
              <select v-model="priorityFilter" class="form-select form-select-sm bg-white" style="width: 140px;">
                <option value="all">Toutes priorités</option>
                <option value="urgent">🔴 Urgent</option>
                <option value="high">🟠 Haute</option>
                <option value="medium">🔵 Moyenne</option>
                <option value="low">⚪ Faible</option>
              </select>

              <!-- View Switcher (Tableau ⇄ Liste) -->
              <div class="btn-group btn-group-sm bg-white rounded-pill p-1 border shadow-2xs">
                <button 
                  type="button" 
                  class="btn btn-sm rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1"
                  :class="currentView === 'board' ? 'btn-dark text-white' : 'btn-link text-muted text-decoration-none'"
                  @click="currentView = 'board'"
                  title="Vue colonnes Kanban"
                >
                  <i class="bi bi-kanban"></i> Tableau
                </button>
                <button 
                  type="button" 
                  class="btn btn-sm rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1"
                  :class="currentView === 'list' ? 'btn-dark text-white' : 'btn-link text-muted text-decoration-none'"
                  @click="currentView = 'list'"
                  title="Vue tableau / liste ordonnée"
                >
                  <i class="bi bi-list-task"></i> Liste
                </button>
              </div>

              <!-- Export Dropdown -->
              <div class="dropdown">
                <button 
                  class="btn btn-sm btn-white bg-white border rounded-pill px-3 fw-bold text-dark d-flex align-items-center gap-1 shadow-2xs dropdown-toggle" 
                  type="button" 
                  data-bs-toggle="dropdown" 
                  aria-expanded="false"
                  title="Exporter les données du tableau"
                >
                  <i class="bi bi-download text-success"></i>
                  <span>Exporter</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-1">
                  <li>
                    <button class="dropdown-item small rounded-2 py-2 d-flex align-items-center gap-2" @click="exportCSV">
                      <i class="bi bi-filetype-csv text-primary fs-6"></i> Exporter en CSV (Excel)
                    </button>
                  </li>
                  <li>
                    <button class="dropdown-item small rounded-2 py-2 d-flex align-items-center gap-2" @click="exportMarkdown">
                      <i class="bi bi-markdown-fill text-dark fs-6"></i> Copier résumé Markdown
                    </button>
                  </li>
                </ul>
              </div>

              <!-- Manage Team / Contributors Button -->
              <button 
                v-if="canEdit" 
                @click="showTeamModal = true" 
                class="btn btn-sm btn-white bg-white border rounded-pill px-3 fw-bold text-dark d-flex align-items-center gap-2 shadow-2xs"
                title="Gérer les contributeurs du tableau"
              >
                <i class="bi bi-people-fill text-primary"></i>
                <span>Équipe ({{ (board.members || []).length + 1 }})</span>
              </button>

              <!-- Archives Button -->
              <button 
                @click="showArchivesModal = true" 
                class="btn btn-sm btn-white bg-white border rounded-pill px-3 fw-bold text-dark d-flex align-items-center gap-2 shadow-2xs"
                title="Consulter les tâches archivées"
              >
                <i class="bi bi-archive-fill text-warning"></i>
                <span>Archives ({{ localArchivedCards.length }})</span>
              </button>

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

      <!-- Kanban Board Canvas -->
      <main v-if="currentView === 'board'" class="kanban-canvas flex-grow-1 p-3 p-md-4">
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
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="rounded-pill d-inline-block" :style="{ width: '12px', height: '12px', backgroundColor: column.color || '#3b82f6' }"></span>
                <h3 class="h6 fw-bold text-dark mb-0">{{ column.title }}</h3>
                <span 
                  class="badge rounded-pill px-2 py-0 extra-small transition"
                  :class="column.wip_limit && getFilteredCards(column).length > column.wip_limit ? 'bg-danger text-white fw-bold shadow-2xs' : 'bg-slate-100 text-muted'"
                  :style="column.wip_limit && getFilteredCards(column).length > column.wip_limit ? 'background-color: #dc2626 !important;' : 'background-color: #f1f5f9;'"
                  :title="column.wip_limit ? (getFilteredCards(column).length > column.wip_limit ? 'Limite WIP dépassée !' : 'Limite WIP: ' + column.wip_limit) : 'Nombre de cartes'"
                >
                  {{ getFilteredCards(column).length }}<span v-if="column.wip_limit"> / {{ column.wip_limit }}</span>
                </span>
                <span v-if="column.wip_limit && getFilteredCards(column).length > column.wip_limit" class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill extra-small px-1 py-0" title="Limite de travail en cours dépassée">
                  WIP Dépassé
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
                <!-- Card Cover Image Preview -->
                <div v-if="getCoverImageUrl(card)" class="mb-2 rounded-2 overflow-hidden border shadow-2xs" style="max-height: 110px;">
                  <img :src="getCoverImageUrl(card)" class="w-100 object-fit-cover" style="max-height: 110px;" :alt="card.title" />
                </div>

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

                <!-- Footer: Due date, Time tracking, Devlog badge & Assignee -->
                <div class="d-flex align-items-center justify-content-between pt-2 mt-1 border-top extra-small text-muted">
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Due date -->
                    <span v-if="card.due_date" class="d-flex align-items-center gap-1" :class="{ 'text-danger fw-bold': isOverdue(card.due_date) }">
                      <i class="bi bi-calendar3"></i>
                      <span>{{ formatDateShort(card.due_date) }}</span>
                    </span>

                    <!-- Time tracking badge -->
                    <span v-if="card.estimated_hours || card.spent_hours" class="badge bg-light text-dark border extra-small px-1 py-0 d-inline-flex align-items-center gap-1" :title="'Temps passé: ' + (card.spent_hours || 0) + 'h / Estimé: ' + (card.estimated_hours || 0) + 'h'">
                      <i class="bi bi-clock-history text-primary"></i>
                      <span>{{ card.spent_hours || 0 }}h<span v-if="card.estimated_hours">/{{ card.estimated_hours }}h</span></span>
                    </span>

                    <!-- Attachments counter badge -->
                    <span v-if="card.attachments && card.attachments.length" class="d-inline-flex align-items-center gap-1 extra-small text-muted" title="Pièces jointes images">
                      <i class="bi bi-image"></i> {{ card.attachments.length }}
                    </span>

                    <!-- Comments counter badge -->
                    <span v-if="card.comments && card.comments.length" class="d-inline-flex align-items-center gap-1 extra-small text-muted" title="Commentaires d'équipe">
                      <i class="bi bi-chat-left-text"></i> {{ card.comments.length }}
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

    <!-- Table / List View Alternative -->
    <main v-else-if="currentView === 'list'" class="kanban-canvas flex-grow-1 p-3 p-md-4">
      <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light extra-small text-uppercase text-muted fw-bold">
              <tr>
                <th style="width: 45px;" class="text-center">Fait</th>
                <th>Tâche</th>
                <th>Colonne / Statut</th>
                <th>Priorité</th>
                <th>Échéance</th>
                <th>Temps (h)</th>
                <th>Responsable</th>
                <th class="text-end pe-3">Action</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="column in board.columns" :key="'col-table-' + column.id">
                <!-- Group Header Row -->
                <tr class="table-light border-top border-bottom">
                  <td colspan="8" class="py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center gap-2">
                        <span class="rounded-pill d-inline-block" :style="{ width: '10px', height: '10px', backgroundColor: column.color || '#3b82f6' }"></span>
                        <strong class="small text-dark">{{ column.title }}</strong>
                        <span class="badge rounded-pill bg-white text-dark border extra-small">
                          {{ getFilteredCards(column).length }}<span v-if="column.wip_limit"> / {{ column.wip_limit }}</span>
                        </span>
                        <span v-if="column.wip_limit && getFilteredCards(column).length > column.wip_limit" class="badge bg-danger text-white extra-small px-1 py-0">
                          WIP Dépassé
                        </span>
                      </div>
                      <button v-if="canEdit" class="btn btn-xs btn-outline-dark rounded-pill px-2" @click="openQuickAdd(column)">
                        + Ajouter une carte
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Cards in this Column -->
                <tr 
                  v-for="card in getFilteredCards(column)" 
                  :key="'card-table-' + card.id" 
                  class="cursor-pointer transition-all"
                  @click="openCardModal(card)"
                >
                  <td class="text-center" @click.stop>
                    <input 
                      type="checkbox" 
                      class="form-check-input cursor-pointer" 
                      :checked="card.is_completed" 
                      @change="toggleCardCompletionFromList(card)"
                      title="Marquer comme terminée"
                    />
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div v-if="getCoverImageUrl(card)" class="rounded-2 overflow-hidden border flex-shrink-0" style="width: 34px; height: 34px;">
                        <img :src="getCoverImageUrl(card)" class="w-100 h-100 object-fit-cover" :alt="card.title" />
                      </div>
                      <div class="min-w-0">
                        <span class="fw-bold text-dark small d-block text-truncate" :class="{ 'text-decoration-line-through text-muted': card.is_completed }" style="max-width: 320px;">
                          {{ card.title }}
                        </span>
                        <div class="d-flex align-items-center gap-1 flex-wrap mt-0">
                          <span v-for="(lbl, li) in (card.labels || [])" :key="li" class="badge bg-light text-muted border extra-small px-1 py-0" style="font-size: 10px;">
                            {{ lbl }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge rounded-pill px-2 py-1 extra-small text-white" :style="{ backgroundColor: column.color || '#3b82f6' }">
                      {{ column.title }}
                    </span>
                  </td>
                  <td>
                    <span class="badge rounded-pill px-2 py-0 extra-small fw-bold" :class="getPriorityClass(card.priority)">
                      {{ getPriorityLabel(card.priority) }}
                    </span>
                  </td>
                  <td class="small">
                    <span v-if="card.due_date" :class="{ 'text-danger fw-bold': isOverdue(card.due_date), 'text-muted': !isOverdue(card.due_date) }">
                      {{ formatDateShort(card.due_date) }}
                    </span>
                    <span v-else class="text-muted extra-small">--</span>
                  </td>
                  <td class="small">
                    <span v-if="card.estimated_hours || card.spent_hours" class="badge bg-light text-dark border extra-small">
                      ⏱️ {{ card.spent_hours || 0 }}h<span v-if="card.estimated_hours">/{{ card.estimated_hours }}h</span>
                    </span>
                    <span v-else class="text-muted extra-small">--</span>
                  </td>
                  <td>
                    <div v-if="card.assignee" class="d-flex align-items-center gap-2">
                      <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-2xs" style="width: 24px; height: 24px; font-size: 9px; background: #0B2559;">
                        <img v-if="card.assignee.avatar" :src="'/' + card.assignee.avatar" class="w-100 h-100 rounded-circle object-fit-cover" :alt="card.assignee.name" />
                        <span v-else>{{ card.assignee.name.charAt(0).toUpperCase() }}</span>
                      </div>
                      <span class="extra-small text-dark fw-semibold text-truncate" style="max-width: 110px;">{{ card.assignee.name }}</span>
                    </div>
                    <span v-else class="text-muted extra-small">Non assigné</span>
                  </td>
                  <td class="text-end pe-3" @click.stop>
                    <button class="btn btn-xs btn-light rounded-pill px-3 fw-semibold border" @click="openCardModal(card)">
                      Ouvrir &rarr;
                    </button>
                  </td>
                </tr>

                <!-- Empty Column notice in List View -->
                <tr v-if="!getFilteredCards(column).length">
                  <td colspan="8" class="text-center py-3 text-muted extra-small bg-white">
                    Aucune tâche dans « {{ column.title }} ».
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
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
                :disabled="isCardLocked"
                class="form-control form-control-lg fw-bold border-0 bg-transparent p-0 fs-5 text-dark"
                placeholder="Titre de la tâche..."
              />
              <span v-else class="fs-5 fw-bold text-dark text-truncate">{{ activeCard.title }}</span>
            </div>

            <!-- Header Action: Always visible save button without scrolling -->
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
              <button 
                v-if="canEdit && !isCardLocked" 
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

            <!-- Completed & Locked Task Banner -->
            <div v-if="isCardLocked" class="alert alert-success d-flex align-items-center justify-content-between p-3 rounded-4 mb-4 border-0 shadow-2xs flex-wrap gap-2">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                  <i class="bi bi-lock-fill fs-5"></i>
                </div>
                <div>
                  <strong class="small d-block text-success fw-bold">Tâche terminée &amp; verrouillée</strong>
                  <span class="extra-small text-muted">Cette tâche est finalisée. Les champs de saisie sont protégés en lecture seule.</span>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <button v-if="canEdit" type="button" @click="toggleCardCompletion" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold extra-small">
                  <i class="bi bi-unlock me-1"></i>Rouvrir la tâche
                </button>
                <button v-if="canEdit && !activeCard.is_archived" type="button" @click="archiveActiveCard" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold extra-small shadow-2xs">
                  <i class="bi bi-archive-fill me-1"></i>Archiver
                </button>
              </div>
            </div>
            <div v-else-if="canEdit" class="d-flex justify-content-end mb-3">
              <button type="button" @click="toggleCardCompletion" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold extra-small">
                <i class="bi bi-check2-circle me-1"></i>Marquer comme terminée
              </button>
            </div>

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
                    :disabled="isCardLocked"
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
                          :disabled="!canEdit || isCardLocked"
                        />
                        <label 
                          class="form-check-label small cursor-pointer" 
                          :for="'chk_' + cIdx"
                          :class="{ 'text-decoration-line-through text-muted': item.done, 'text-dark fw-semibold': !item.done }"
                        >
                          {{ item.text }}
                        </label>
                      </div>

                      <button v-if="canEdit && !isCardLocked" @click="removeChecklistItem(cIdx)" class="btn btn-link btn-sm text-danger p-0">
                        <i class="bi bi-x"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Add checklist item -->
                  <div v-if="canEdit && !isCardLocked" class="d-flex align-items-center gap-2">
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
                <div v-if="board.project_id && canEdit" class="p-3 rounded-4 border bg-emerald-50 mb-4" style="background-color: #ecfdf5; border-color: #a7f3d0 !important;">
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

                <!-- Team Collaboration Section: Real-time Comments, Multi-Mentions & Emoji Picker -->
                <div class="mt-4 pt-3 border-top position-relative">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                      <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-chat-left-dots-fill text-primary"></i>
                        <span>Collaboration &amp; Échanges d'équipe</span>
                      </h6>
                      <span class="badge bg-success-subtle text-success extra-small rounded-pill px-2 py-0 d-inline-flex align-items-center gap-1">
                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 6px; height: 6px;" role="status"></span>
                        En direct
                      </span>
                    </div>
                    <span class="badge bg-light text-muted border extra-small">
                      {{ (activeCard.comments || []).length }} message(s)
                    </span>
                  </div>

                  <!-- Comment Composer Card -->
                  <div class="card border rounded-3 p-2 bg-light mb-3 position-relative shadow-2xs">
                    
                    <!-- Multi @ Mentions Autocomplete Popover -->
                    <div 
                      v-if="showMentionDropdown && filteredMentionMembers.length" 
                      class="mention-dropdown shadow-lg rounded-3 bg-white border position-absolute p-1"
                      style="bottom: 100%; left: 8px; z-index: 1060; max-height: 190px; overflow-y: auto; width: 250px;"
                    >
                      <div class="px-2 py-1 extra-small text-muted fw-bold border-bottom">Tagger un collaborateur :</div>
                      <button 
                        v-for="m in filteredMentionMembers" 
                        :key="'mention-' + m.id"
                        type="button"
                        @click="selectMentionMember(m)"
                        class="dropdown-item d-flex align-items-center gap-2 py-1 px-2 rounded-2 small text-dark"
                      >
                        <img v-if="m.avatar" :src="m.avatar.startsWith('/') ? m.avatar : '/' + m.avatar" class="rounded-circle border" style="width: 22px; height: 22px; object-fit: cover;">
                        <span v-else class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 10px;">
                          {{ (m.name || 'U').substring(0, 1).toUpperCase() }}
                        </span>
                        <span class="small fw-semibold text-truncate">{{ m.name }}</span>
                      </button>
                    </div>

                    <!-- Emoji Picker Popover -->
                    <div 
                      v-if="showEmojiPicker" 
                      class="emoji-picker-box shadow-lg rounded-3 bg-white border position-absolute p-2"
                      style="bottom: 100%; right: 8px; z-index: 1060; width: 260px;"
                    >
                      <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                        <span class="extra-small fw-bold text-dark">Sélectionner un emoji</span>
                        <button type="button" class="btn-close" style="font-size: 8px;" @click="showEmojiPicker = false"></button>
                      </div>
                      <div class="d-flex flex-wrap gap-1">
                        <button 
                          v-for="emo in popularEmojis" 
                          :key="emo" 
                          type="button"
                          @click="insertEmoji(emo)"
                          class="btn btn-sm btn-light border-0 p-1 fs-5 emoji-btn"
                          :title="emo"
                        >
                          {{ emo }}
                        </button>
                      </div>
                    </div>

                    <!-- Textarea -->
                    <textarea 
                      ref="commentTextareaRef"
                      v-model="newCommentText" 
                      @input="onCommentInput"
                      class="form-control border-0 bg-transparent small" 
                      rows="2" 
                      placeholder="Écrire un message en direct, tapez @ pour tagger des collaborateurs..."
                      @keydown.enter.ctrl="submitCardComment"
                    ></textarea>

                    <!-- Composer Toolbar -->
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-1">
                      <div class="d-flex align-items-center gap-1">
                        <!-- Quick @ Mention Button -->
                        <button 
                          type="button" 
                          @click="triggerMentionPrompt"
                          class="btn btn-xs btn-light border rounded-pill px-2 py-0 extra-small text-muted d-flex align-items-center gap-1"
                          title="Tagger un collaborateur (@)"
                        >
                          <span class="fw-bold text-primary">@</span>
                          <span>Tagger</span>
                        </button>

                        <!-- Emoji Picker Button -->
                        <button 
                          type="button" 
                          @click="showEmojiPicker = !showEmojiPicker"
                          class="btn btn-xs btn-light border rounded-pill px-2 py-0 extra-small text-muted d-flex align-items-center gap-1"
                          title="Insérer un emoji"
                        >
                          <span>😊</span>
                          <span>Emoji</span>
                        </button>
                      </div>

                      <div class="d-flex align-items-center gap-2">
                        <small class="text-muted extra-small d-none d-sm-inline">Ctrl + Entrée</small>
                        <button 
                          @click="submitCardComment" 
                          :disabled="!newCommentText.trim() || isSubmittingComment" 
                          class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-xs d-flex align-items-center gap-1"
                        >
                          <i class="bi bi-send-fill" style="font-size: 11px;"></i>
                          <span>{{ isSubmittingComment ? 'Envoi...' : 'Envoyer' }}</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Comments Stream (Real-Time Live Feed) -->
                  <div ref="commentsStreamContainer" v-if="activeCardComments.length" class="d-flex flex-column gap-2 mb-3 pe-1" style="max-height: 250px; overflow-y: auto;">
                    <div 
                      v-for="comm in activeCardComments" 
                      :key="comm.id" 
                      class="d-flex align-items-start gap-2 p-2 px-3 rounded-3 bg-white border shadow-2xs transition"
                      :class="{ 'border-primary border-2 bg-primary-subtle bg-opacity-10': isUserMentioned(comm.comment) }"
                    >
                      <img 
                        v-if="comm.user?.avatar" 
                        :src="comm.user.avatar.startsWith('/') ? comm.user.avatar : '/' + comm.user.avatar" 
                        class="rounded-circle border flex-shrink-0" 
                        style="width: 32px; height: 32px; object-fit: cover;" 
                        :alt="comm.user?.name"
                      />
                      <div 
                        v-else 
                        class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 32px; height: 32px; font-size: 11px;"
                      >
                        {{ (comm.user?.name || 'U').substring(0, 2).toUpperCase() }}
                      </div>
                      <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                          <div class="d-flex align-items-center gap-2">
                            <strong class="small text-dark">{{ comm.user?.name || 'Collaborateur' }}</strong>
                            <span v-if="isUserMentioned(comm.comment)" class="badge bg-primary text-white extra-small py-0 px-2 rounded-pill">
                              Vous êtes mentionné(e) 🔔
                            </span>
                          </div>
                          <span class="extra-small text-muted">{{ formatTimeAgo(comm.created_at) }}</span>
                        </div>
                        <div class="small text-dark mb-0 comment-body-text" v-html="renderCommentWithMentions(comm.comment)"></div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-2 text-muted extra-small">
                    Aucun échange sur cette tâche pour l'instant. Lancez la discussion !
                  </div>

                  <!-- Activities Log for this card -->
                  <div v-if="activeCardActivities.length" class="mt-3 pt-2 border-top">
                    <small class="text-muted fw-bold extra-small text-uppercase d-block mb-1">Historique d'activité</small>
                    <div class="d-flex flex-column gap-1">
                      <div v-for="act in activeCardActivities.slice(0, 4)" :key="act.id" class="extra-small text-muted d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-secondary"></i>
                        <span>{{ act.description }} &bull; {{ formatTimeAgo(act.created_at) }}</span>
                      </div>
                    </div>
                  </div>
                </div>

              </div>

              <!-- Right Column: Settings, Metadata & Dropzone -->
              <div class="col-lg-4 border-start">
                
                <!-- Priority Selector -->
                <div class="mb-3">
                  <label class="form-label small fw-bold text-dark mb-1">Priorité</label>
                  <select v-if="canEdit && !isCardLocked" v-model="editCardForm.priority" class="form-select form-select-sm rounded-3">
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
                  <select v-if="canEdit && !isCardLocked" v-model="editCardForm.assigned_to" class="form-select form-select-sm rounded-3">
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
                    v-if="canEdit && !isCardLocked"
                    type="date" 
                    v-model="editCardForm.due_date" 
                    class="form-control form-control-sm rounded-3" 
                  />
                  <div v-else class="small text-muted">
                    {{ activeCard.due_date ? formatDateShort(activeCard.due_date) : 'Aucune date' }}
                  </div>
                </div>

                <!-- Time Tracking & Estimation -->
                <div class="mb-3 p-3 bg-light rounded-3 border">
                  <label class="form-label extra-small fw-bold text-dark mb-2 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-stopwatch text-primary me-1"></i>Estimation & Temps Passé</span>
                    <span v-if="editCardForm.estimated_hours || editCardForm.spent_hours" class="badge bg-white text-dark border extra-small">
                      ⏱️ {{ editCardForm.spent_hours || 0 }}h / {{ editCardForm.estimated_hours || 0 }}h
                    </span>
                  </label>
                  <div class="row g-2">
                    <div class="col-6">
                      <label class="extra-small text-muted mb-1 d-block">Estimation (h)</label>
                      <input 
                        v-if="canEdit && !isCardLocked"
                        type="number" 
                        step="0.5" 
                        min="0"
                        v-model.number="editCardForm.estimated_hours" 
                        class="form-control form-control-sm rounded-3" 
                        placeholder="ex: 6" 
                      />
                      <div v-else class="small text-dark fw-bold">{{ activeCard.estimated_hours ? activeCard.estimated_hours + 'h' : '--' }}</div>
                    </div>
                    <div class="col-6">
                      <label class="extra-small text-muted mb-1 d-block">Temps passé (h)</label>
                      <input 
                        v-if="canEdit && !isCardLocked"
                        type="number" 
                        step="0.5" 
                        min="0"
                        v-model.number="editCardForm.spent_hours" 
                        class="form-control form-control-sm rounded-3" 
                        placeholder="ex: 4" 
                      />
                      <div v-else class="small text-dark fw-bold">{{ activeCard.spent_hours ? activeCard.spent_hours + 'h' : '--' }}</div>
                    </div>
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
                      <i v-if="canEdit && !isCardLocked" @click="removeLabel(lIdx)" class="bi bi-x cursor-pointer"></i>
                    </span>
                  </div>

                  <div v-if="canEdit && !isCardLocked" class="input-group input-group-sm">
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

                <!-- Dropzone Pièce Jointe (Images Uniquement, encadré par des pointillés clairs) -->
                <div class="mb-4">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                      <i class="bi bi-image text-primary me-1"></i>Pièces jointes (Images)
                    </label>
                    <span v-if="activeCardAttachments.length" class="badge bg-light text-muted border extra-small">
                      {{ activeCardAttachments.length }} image(s)
                    </span>
                  </div>

                  <!-- Dropzone upload box with prominent dashed borders -->
                  <div 
                    v-if="canEdit && !isCardLocked" 
                    class="kanban-dropzone p-3 rounded-3 text-center cursor-pointer mb-2 transition"
                    :class="{ 'is-dragging': isDraggingFile }"
                    @click="triggerImageFileInput"
                    @dragover.prevent="isDraggingFile = true"
                    @dragleave.prevent="isDraggingFile = false"
                    @drop.prevent="onDropzoneFileDrop"
                  >
                    <input 
                      type="file" 
                      ref="cardImageFileInput" 
                      @change="onCardImageFileSelect" 
                      accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" 
                      class="d-none"
                    />
                    <div v-if="isUploadingAttachment" class="py-2 d-flex flex-column align-items-center">
                      <div class="spinner-border spinner-border-sm text-primary mb-1" role="status"></div>
                      <span class="extra-small text-muted">Envoi de l'image en cours...</span>
                    </div>
                    <div v-else>
                      <i class="bi bi-cloud-arrow-up fs-4 text-primary d-block mb-1"></i>
                      <span class="small fw-semibold text-dark d-block">Glissez une image ou cliquez</span>
                      <small class="text-muted extra-small" style="font-size: 10px;">Formats autorisés : JPG, PNG, WEBP, GIF (Max 10 Mo)</small>
                    </div>
                  </div>

                  <!-- Attachments preview strip -->
                  <div v-if="activeCardAttachments.length" class="d-flex flex-column gap-2 mt-2">
                    <div 
                      v-for="att in activeCardAttachments" 
                      :key="att.id"
                      class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border gap-2"
                    >
                      <a :href="att.url.startsWith('/') ? att.url : '/' + att.url" target="_blank" class="d-flex align-items-center gap-2 text-decoration-none text-dark flex-grow-1 min-w-0" :title="'Aperçu plein écran de ' + att.name">
                        <img :src="att.url.startsWith('/') ? att.url : '/' + att.url" class="rounded object-fit-cover border" style="width: 38px; height: 38px;" :alt="att.name">
                        <div class="text-truncate">
                          <strong class="small d-block text-truncate mb-0">{{ att.name }}</strong>
                          <span class="extra-small text-muted">{{ formatFileSize(att.size) }} &bull; {{ att.uploader_name }}</span>
                        </div>
                      </a>
                      <div class="d-flex align-items-center gap-1">
                        <button 
                          v-if="canEdit && !isCardLocked" 
                          type="button" 
                          @click.stop="toggleCardCover(att.url)" 
                          class="btn btn-xs rounded-pill px-2 py-0 border extra-small"
                          :class="editCardForm.cover_image === att.url ? 'btn-warning text-dark fw-bold' : 'btn-light text-muted'"
                          :title="editCardForm.cover_image === att.url ? 'Couverture active de la carte' : 'Définir comme couverture'"
                        >
                          <i class="bi" :class="editCardForm.cover_image === att.url ? 'bi-star-fill text-dark' : 'bi-star'"></i>
                          <span class="d-none d-sm-inline ms-1">{{ editCardForm.cover_image === att.url ? 'Couverture' : 'Couvrir' }}</span>
                        </button>
                        <button v-if="canEdit && !isCardLocked" @click.stop="deleteAttachment(att.id)" class="btn btn-sm btn-link text-danger p-0" title="Supprimer l'image">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div v-else-if="!canEdit || isCardLocked" class="extra-small text-muted">
                    Aucune image jointe.
                  </div>
                </div>

                <!-- Archive / Delete Actions -->
                <div v-if="canEdit" class="pt-3 border-top d-flex flex-column gap-2">
                  <button 
                    v-if="!activeCard.is_archived" 
                    type="button" 
                    @click="archiveActiveCard" 
                    class="btn btn-sm btn-outline-warning text-dark w-100 rounded-pill fw-semibold extra-small"
                  >
                    <i class="bi bi-archive me-1"></i> Archiver cette tâche
                  </button>
                  <button 
                    v-if="!isCardLocked" 
                    type="button" 
                    @click="deleteActiveCard" 
                    class="btn btn-sm btn-outline-danger w-100 rounded-pill extra-small"
                  >
                    <i class="bi bi-trash me-1"></i> Supprimer définitivement
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
              <button v-if="canEdit && !isCardLocked" type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm" @click="saveCardChanges" :disabled="isSavingCard">
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
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="doneColSwitch" v-model="columnForm.is_done_column" />
                <label class="form-check-label small fw-semibold text-dark" for="doneColSwitch">
                  Marquer comme colonne de « Tâches Terminées »
                </label>
                <div class="extra-small text-muted">
                  Permet de calculer le taux de complétion du projet et d'activer l'export en Devlog.
                </div>
              </div>

              <!-- WIP Limit -->
              <div class="mb-2">
                <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between mb-1">
                  <span><i class="bi bi-speedometer2 text-warning me-1"></i>Limite WIP (Work-In-Progress)</span>
                  <span class="badge bg-light text-muted border extra-small">Optionnel</span>
                </label>
                <input 
                  type="number" 
                  v-model.number="columnForm.wip_limit" 
                  class="form-control rounded-3" 
                  placeholder="ex: 3 ou 5 (Laisser vide pour illimité)" 
                  min="1" 
                  max="99" 
                />
                <div class="extra-small text-muted mt-1">
                  Affiche un avertissement visuel si le nombre de cartes en cours dépasse cette valeur.
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

    <!-- 4. Manage Team / Contributors Modal -->
    <div v-if="showTeamModal" class="modal-backdrop fade show" style="background: rgba(15, 23, 42, 0.65); z-index: 1050;"></div>
    <div v-if="showTeamModal" class="modal fade show d-block" style="z-index: 1055;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-people-fill text-primary"></i>
              <span>Équipe &amp; Contributeurs du tableau</span>
            </h5>
            <button type="button" class="btn-close" @click="showTeamModal = false"></button>
          </div>
          <div class="modal-body p-4">
            <!-- Add Member Form -->
            <div class="card p-3 rounded-3 bg-light border mb-4">
              <label class="form-label small fw-bold text-dark mb-1">Inviter un nouveau contributeur</label>
              <div class="d-flex gap-2">
                <select v-model="selectedNewMemberId" class="form-select form-select-sm bg-white">
                  <option :value="null">Sélectionner un membre du site...</option>
                  <option 
                    v-for="u in eligibleNewMembers" 
                    :key="u.id" 
                    :value="u.id"
                  >
                    {{ u.name }} ({{ u.email }})
                  </option>
                </select>
                <button 
                  @click="addTeamMember" 
                  :disabled="!selectedNewMemberId || isAddingMember" 
                  class="btn btn-sm btn-primary rounded-pill px-3 fw-bold flex-shrink-0"
                >
                  {{ isAddingMember ? 'Ajout...' : 'Ajouter' }}
                </button>
              </div>
              <small class="text-muted extra-small mt-1">Le membre pourra collaborer et être assigné aux tâches de ce tableau.</small>
            </div>

            <!-- Current Members List -->
            <h6 class="small fw-bold text-dark mb-2">Membres du tableau :</h6>
            <div class="d-flex flex-column gap-2" style="max-height: 260px; overflow-y: auto;">
              <!-- Board Owner -->
              <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-white border">
                <div class="d-flex align-items-center gap-2">
                  <img v-if="board.user?.avatar" :src="'/' + board.user.avatar" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                  <div v-else class="rounded-circle bg-dark text-white fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 11px;">
                    {{ (board.user?.name || 'P').substring(0, 2).toUpperCase() }}
                  </div>
                  <div>
                    <strong class="small d-block text-dark">{{ board.user?.name }}</strong>
                    <span class="badge bg-dark rounded-pill extra-small">Propriétaire</span>
                  </div>
                </div>
              </div>

              <!-- Other Members -->
              <div 
                v-for="m in (board.members || [])" 
                :key="m.id" 
                class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-white border"
              >
                <div class="d-flex align-items-center gap-2">
                  <img v-if="m.user?.avatar" :src="'/' + m.user.avatar" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                  <div v-else class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 11px;">
                    {{ (m.user?.name || 'M').substring(0, 2).toUpperCase() }}
                  </div>
                  <div>
                    <strong class="small d-block text-dark">{{ m.user?.name }}</strong>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill extra-small">Contributeur</span>
                  </div>
                </div>
                <button 
                  v-if="canEdit" 
                  @click="removeTeamMember(m.user_id)" 
                  class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0 extra-small"
                  title="Retirer ce membre du tableau"
                >
                  <i class="bi bi-person-x me-1"></i>Retirer
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 bg-light py-2">
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" @click="showTeamModal = false">
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. Archives Modal -->
    <div v-if="showArchivesModal" class="modal-backdrop fade show" style="background: rgba(15, 23, 42, 0.65); z-index: 1050;"></div>
    <div v-if="showArchivesModal" class="modal fade show d-block" style="z-index: 1055;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
          <div class="modal-header border-bottom py-3 px-4 bg-light d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-archive-fill text-warning fs-5"></i>
              <h5 class="modal-title fw-bold text-dark mb-0">Tâches archivées</h5>
              <span class="badge bg-secondary rounded-pill extra-small">{{ localArchivedCards.length }}</span>
            </div>
            <button type="button" class="btn-close" @click="showArchivesModal = false"></button>
          </div>

          <div class="modal-body p-4">
            <p class="text-muted small mb-3">
              Consultez l'historique complet des tâches terminées et archivées. Vous pouvez les réouvrir ou les restaurer directement dans votre tableau Kanban.
            </p>

            <!-- Search inside archives -->
            <div class="mb-3">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input 
                  type="text" 
                  v-model="archiveSearchQuery" 
                  class="form-control border-start-0" 
                  placeholder="Rechercher une tâche archivée..."
                />
              </div>
            </div>

            <!-- List of archived cards -->
            <div v-if="filteredArchivedCards.length" class="d-flex flex-column gap-3" style="max-height: 400px; overflow-y: auto;">
              <div 
                v-for="card in filteredArchivedCards" 
                :key="card.id"
                class="card border rounded-3 p-3 bg-white shadow-2xs d-flex flex-column gap-2"
              >
                <div class="d-flex align-items-start justify-content-between gap-2">
                  <div>
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                      <i class="bi bi-check-circle-fill text-success fs-6"></i>
                      <span>{{ card.title }}</span>
                    </h6>
                    <span class="extra-small text-muted">
                      Archivée le {{ formatDateShort(card.archived_at || card.updated_at) }}
                      <span v-if="card.column"> &bull; Colonne : <strong>{{ card.column.title }}</strong></span>
                    </span>
                  </div>

                  <span class="badge rounded-pill extra-small px-2 py-1" :class="getPriorityClass(card.priority)">
                    {{ getPriorityLabel(card.priority) }}
                  </span>
                </div>

                <p v-if="card.description" class="small text-muted mb-0 text-truncate" style="max-height: 40px;">
                  {{ card.description }}
                </p>

                <!-- Metadata pills & Action buttons -->
                <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-1 flex-wrap gap-2">
                  <div class="d-flex align-items-center gap-2 extra-small text-muted">
                    <span v-if="card.attachments && card.attachments.length">
                      <i class="bi bi-paperclip me-1"></i>{{ card.attachments.length }} pièce(s)
                    </span>
                    <span v-if="card.comments && card.comments.length">
                      <i class="bi bi-chat-text me-1"></i>{{ card.comments.length }} commentaire(s)
                    </span>
                  </div>

                  <div class="d-flex align-items-center gap-2">
                    <button 
                      type="button" 
                      @click="inspectArchivedCard(card)" 
                      class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 extra-small fw-semibold"
                    >
                      <i class="bi bi-eye me-1"></i>Consulter
                    </button>
                    <button 
                      v-if="canEdit" 
                      type="button" 
                      @click="unarchiveCardAction(card)" 
                      class="btn btn-xs btn-success rounded-pill px-3 py-1 extra-small fw-bold shadow-2xs"
                      :disabled="isUnarchivingCard"
                    >
                      <i class="bi bi-arrow-counterclockwise me-1"></i>Désarchiver
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-5 text-muted">
              <i class="bi bi-archive fs-1 d-block mb-2 text-secondary opacity-50"></i>
              <span class="small d-block">Aucune tâche archivée pour le moment.</span>
              <span class="extra-small text-muted">Lorsque vous terminez des tâches, vous pouvez les archiver pour garder votre tableau épuré.</span>
            </div>
          </div>

          <div class="modal-footer border-0 bg-light py-2">
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" @click="showArchivesModal = false">
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

const page = usePage()

const props = defineProps({
  board: { type: Object, required: true },
  canEdit: { type: Boolean, default: false },
  teamMembers: { type: Array, default: () => [] },
  registeredUsers: { type: Array, default: () => [] },
  archivedCards: { type: Array, default: () => [] },
})

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

const searchFilter = ref('')
const priorityFilter = ref('all')
const myTasksOnly = ref(false)
const currentView = ref('board')
const currentUserId = computed(() => page.props.auth?.user?.id)

// Archived Cards State
const localArchivedCards = ref([...(props.archivedCards || [])])
const showArchivesModal = ref(false)
const archiveSearchQuery = ref('')
const isUnarchivingCard = ref(false)

const filteredArchivedCards = computed(() => {
  let list = localArchivedCards.value || []
  if (archiveSearchQuery.value.trim()) {
    const q = archiveSearchQuery.value.toLowerCase()
    list = list.filter(c => 
      c.title.toLowerCase().includes(q) || 
      (c.description && c.description.toLowerCase().includes(q))
    )
  }
  return list
})

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
      // If moving to done column, automatically complete
      if (targetColumn.is_done_column) {
        card.is_completed = true
      }
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
      'X-CSRF-TOKEN': getCsrfToken(),
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
      'X-CSRF-TOKEN': getCsrfToken(),
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
const isCardLocked = computed(() => {
  return Boolean(activeCard.value && activeCard.value.is_completed)
})

const editCardForm = ref({
  title: '',
  description: '',
  priority: 'medium',
  due_date: null,
  assigned_to: null,
  labels: [],
  checklist: [],
  estimated_hours: null,
  spent_hours: null,
  cover_image: null,
})
const newChecklistText = ref('')
const newLabelText = ref('')
const isSavingCard = ref(false)
const isPublishingDevlog = ref(false)

let commentsPollingTimer = null

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
    estimated_hours: card.estimated_hours ?? null,
    spent_hours: card.spent_hours ?? null,
    cover_image: card.cover_image ?? null,
  }
  startCommentsPolling()
}

function toggleCardCover(attUrl) {
  if (editCardForm.value.cover_image === attUrl) {
    editCardForm.value.cover_image = null
  } else {
    editCardForm.value.cover_image = attUrl
  }
}

function toggleCardCompletionFromList(card) {
  fetch(`/espace-utilisateur/kanban/cards/${card.id}/toggle-completion`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': getCsrfToken(),
    }
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      card.is_completed = data.is_completed
      if (activeCard.value && activeCard.value.id === card.id) {
        activeCard.value.is_completed = data.is_completed
      }
    }
  }).catch(err => console.error('Toggle completion error:', err))
}

function closeCardModal() {
  stopCommentsPolling()
  activeCard.value = null
  showMentionDropdown.value = false
  showEmojiPicker.value = false
}

function toggleCardCompletion() {
  if (!activeCard.value) return

  fetch(`/espace-utilisateur/kanban/cards/${activeCard.value.id}/toggle-completion`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': getCsrfToken(),
    }
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      activeCard.value.is_completed = data.is_completed
      // Synchronize in column
      for (const col of props.board.columns) {
        const found = (col.cards || []).find(c => c.id === activeCard.value.id)
        if (found) {
          found.is_completed = data.is_completed
          break
        }
      }
    }
  }).catch(err => console.error('Toggle completion error:', err))
}

function archiveActiveCard() {
  if (!activeCard.value) return
  if (!confirm(`Archiver la tâche « ${activeCard.value.title} » ?\nElle disparaîtra du tableau mais restera consultable dans les archives.`)) return

  const cardId = activeCard.value.id
  fetch(`/espace-utilisateur/kanban/cards/${cardId}/archive`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': getCsrfToken(),
    }
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      // Remove from board columns
      for (const col of props.board.columns) {
        const idx = (col.cards || []).findIndex(c => c.id === cardId)
        if (idx !== -1) {
          col.cards.splice(idx, 1)
          break
        }
      }
      // Add to local archives
      if (data.card) {
        localArchivedCards.value.unshift(data.card)
      }
      closeCardModal()
    }
  }).catch(err => console.error('Archive error:', err))
}

function unarchiveCardAction(card) {
  if (!card || isUnarchivingCard.value) return
  isUnarchivingCard.value = true

  fetch(`/espace-utilisateur/kanban/cards/${card.id}/unarchive`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': getCsrfToken(),
    }
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      // Remove from local archives
      const aIdx = localArchivedCards.value.findIndex(c => c.id === card.id)
      if (aIdx !== -1) {
        localArchivedCards.value.splice(aIdx, 1)
      }
      // Add back to target column
      const targetCol = props.board.columns.find(col => col.id === card.column_id) || props.board.columns[0]
      if (targetCol && data.card) {
        targetCol.cards.push(data.card)
      }
      if (activeCard.value && activeCard.value.id === card.id) {
        activeCard.value.is_archived = false
      }
    }
  }).catch(err => console.error('Unarchive error:', err))
  .finally(() => {
    isUnarchivingCard.value = false
  })
}

function inspectArchivedCard(card) {
  showArchivesModal.value = false
  openCardModal(card)
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
      'X-CSRF-TOKEN': getCsrfToken(),
    },
    body: JSON.stringify(editCardForm.value)
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      Object.assign(activeCard.value, data.card)
      // Also update in columns list
      for (const col of props.board.columns) {
        const found = (col.cards || []).find(c => c.id === activeCard.value.id)
        if (found) {
          Object.assign(found, data.card)
          break
        }
      }
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
      'X-CSRF-TOKEN': getCsrfToken(),
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
      'X-CSRF-TOKEN': getCsrfToken(),
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

// Team management
const showTeamModal = ref(false)
const selectedNewMemberId = ref(null)
const isAddingMember = ref(false)

const eligibleNewMembers = computed(() => {
  const existingIds = [props.board.user_id, ...(props.board.members || []).map(m => m.user_id)]
  return (props.registeredUsers || []).filter(u => !existingIds.includes(u.id))
})

function addTeamMember() {
  if (!selectedNewMemberId.value || isAddingMember.value) return
  isAddingMember.value = true

  router.post(`/espace-utilisateur/kanban/boards/${props.board.id}/members`, {
    user_id: selectedNewMemberId.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedNewMemberId.value = null
      isAddingMember.value = false
    },
    onError: () => {
      isAddingMember.value = false
    }
  })
}

function removeTeamMember(userId) {
  if (!confirm("Retirer ce contributeur du tableau ?")) return
  router.delete(`/espace-utilisateur/kanban/boards/${props.board.id}/members/${userId}`, {
    preserveScroll: true
  })
}

// Card Image Attachments Dropzone
const cardImageFileInput = ref(null)
const isDraggingFile = ref(false)
const isUploadingAttachment = ref(false)
const activeCardAttachments = computed(() => activeCard.value?.attachments || [])

function triggerImageFileInput() {
  if (cardImageFileInput.value) cardImageFileInput.value.click()
}

function onCardImageFileSelect(e) {
  if (e.target.files && e.target.files.length > 0) {
    uploadAttachmentFile(e.target.files[0])
  }
}

function onDropzoneFileDrop(e) {
  isDraggingFile.value = false
  if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
    const file = e.dataTransfer.files[0]
    if (file.type.startsWith('image/')) {
      uploadAttachmentFile(file)
    } else {
      alert("Seuls les fichiers images (JPG, PNG, WEBP, GIF) sont autorisés !")
    }
  }
}

function uploadAttachmentFile(file) {
  if (!activeCard.value || isUploadingAttachment.value) return
  isUploadingAttachment.value = true

  const formData = new FormData()
  formData.append('image', file)

  fetch(`/espace-utilisateur/kanban/cards/${activeCard.value.id}/attachments`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': getCsrfToken(),
    },
    body: formData
  }).then(async r => {
    if (!r.ok) {
      const errData = await r.json().catch(() => ({}))
      throw new Error(errData.message || 'Erreur lors du téléversement')
    }
    return r.json()
  }).then(data => {
    if (data && data.success) {
      if (!Array.isArray(activeCard.value.attachments)) {
        activeCard.value.attachments = []
      }
      activeCard.value.attachments = data.attachments

      // Also sync to board column cards
      for (const col of props.board.columns) {
        const found = (col.cards || []).find(c => c.id === activeCard.value.id)
        if (found) {
          found.attachments = data.attachments
          break
        }
      }
    } else {
      alert("Erreur lors de l'envoi de l'image.")
    }
  }).catch(err => {
    console.error('Upload attachment error:', err)
    alert(err.message || "Erreur de connexion lors du téléversement de l'image.")
  }).finally(() => {
    isUploadingAttachment.value = false
    if (cardImageFileInput.value) cardImageFileInput.value.value = ''
  })
}

function deleteAttachment(attId) {
  if (!confirm("Supprimer cette image de la tâche ?")) return

  fetch(`/espace-utilisateur/kanban/cards/${activeCard.value.id}/attachments/${attId}`, {
    method: 'DELETE',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': getCsrfToken(),
    }
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      activeCard.value.attachments = data.attachments
      for (const col of props.board.columns) {
        const found = (col.cards || []).find(c => c.id === activeCard.value.id)
        if (found) {
          found.attachments = data.attachments
          break
        }
      }
    }
  }).catch(err => {
    console.error('Delete attachment error:', err)
  })
}

// Real-Time Comments, Live Stream & Multi-Mentions
const newCommentText = ref('')
const isSubmittingComment = ref(false)
const commentTextareaRef = ref(null)
const commentsStreamContainer = ref(null)
const activeCardComments = computed(() => activeCard.value?.comments || [])
const activeCardActivities = computed(() => activeCard.value?.activities || [])

// Multi-Mentions (@) Autocomplete
const showMentionDropdown = ref(false)
const mentionQuery = ref('')

const allMentionMembers = computed(() => {
  const members = []
  if (props.board.user) {
    members.push(props.board.user)
  }
  if (Array.isArray(props.board.members)) {
    props.board.members.forEach(m => {
      if (m.user && !members.some(u => u.id === m.user.id)) {
        members.push(m.user)
      }
    })
  }
  if (Array.isArray(props.teamMembers)) {
    props.teamMembers.forEach(tm => {
      if (!members.some(u => u.id === tm.id)) {
        members.push(tm)
      }
    })
  }
  return members
})

const filteredMentionMembers = computed(() => {
  if (!mentionQuery.value) return allMentionMembers.value
  const q = mentionQuery.value.toLowerCase()
  return allMentionMembers.value.filter(m => (m.name || '').toLowerCase().includes(q))
})

function onCommentInput(e) {
  const val = newCommentText.value
  const cursorPos = commentTextareaRef.value?.selectionStart || val.length
  const textBeforeCursor = val.slice(0, cursorPos)
  const lastAt = textBeforeCursor.lastIndexOf('@')

  if (lastAt !== -1 && (lastAt === 0 || /\s/.test(textBeforeCursor[lastAt - 1]))) {
    const query = textBeforeCursor.slice(lastAt + 1)
    if (!/\s/.test(query)) {
      mentionQuery.value = query
      showMentionDropdown.value = true
      return
    }
  }
  showMentionDropdown.value = false
}

function triggerMentionPrompt() {
  newCommentText.value += '@'
  showMentionDropdown.value = true
  nextTick(() => {
    commentTextareaRef.value?.focus()
  })
}

function selectMentionMember(member) {
  const val = newCommentText.value
  const cursorPos = commentTextareaRef.value?.selectionStart || val.length
  const textBeforeCursor = val.slice(0, cursorPos)
  const lastAt = textBeforeCursor.lastIndexOf('@')

  const cleanName = member.name.replace(/\s+/g, '_')
  if (lastAt !== -1) {
    newCommentText.value = textBeforeCursor.slice(0, lastAt) + `@${cleanName} ` + val.slice(cursorPos)
  } else {
    newCommentText.value += `@${cleanName} `
  }
  showMentionDropdown.value = false
  nextTick(() => {
    commentTextareaRef.value?.focus()
  })
}

function renderCommentWithMentions(text) {
  if (!text) return ''
  const escaped = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')

  // Highlight @mentions in primary badge style
  return escaped.replace(/@([a-zA-Z0-9_\u00C0-\u017F]+)/g, '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0 rounded-pill fw-bold">@$1</span>')
}

function isUserMentioned(commentText) {
  if (!commentText || !page.props.auth?.user?.name) return false
  const myName = page.props.auth.user.name.toLowerCase()
  const myNameClean = myName.replace(/\s+/g, '_')
  const commentLower = commentText.toLowerCase()
  return commentLower.includes('@' + myNameClean) || commentLower.includes('@' + myName.split(' ')[0])
}

// Emoji Picker
const showEmojiPicker = ref(false)
const popularEmojis = ['👍', '👏', '🚀', '🔥', '✅', '🎉', '💡', '❤️', '👀', '✨', '⚡', '💻', '🐛', '🎯', '🙌', '💯']

function insertEmoji(emoji) {
  newCommentText.value += emoji + ' '
  showEmojiPicker.value = false
  nextTick(() => {
    commentTextareaRef.value?.focus()
  })
}

// Real-Time Polling for Comments
function fetchCardComments(cardId) {
  if (!cardId || !activeCard.value || activeCard.value.id !== cardId) return

  fetch(`/espace-utilisateur/kanban/cards/${cardId}/comments`, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  }).then(r => r.json()).then(data => {
    if (data && data.success && Array.isArray(data.comments)) {
      if (activeCard.value && activeCard.value.id === cardId) {
        // If count changed or empty, update comments
        if (data.comments.length !== (activeCard.value.comments || []).length) {
          activeCard.value.comments = data.comments
          nextTick(() => {
            if (commentsStreamContainer.value) {
              commentsStreamContainer.value.scrollTop = commentsStreamContainer.value.scrollHeight
            }
          })
        }
      }
    }
  }).catch(() => {})
}

function startCommentsPolling() {
  stopCommentsPolling()
  if (activeCard.value) {
    fetchCardComments(activeCard.value.id)
    commentsPollingTimer = setInterval(() => {
      if (activeCard.value) {
        fetchCardComments(activeCard.value.id)
      }
    }, 3500)
  }
}

function stopCommentsPolling() {
  if (commentsPollingTimer) {
    clearInterval(commentsPollingTimer)
    commentsPollingTimer = null
  }
}

onUnmounted(() => {
  stopCommentsPolling()
})

function submitCardComment() {
  if (!newCommentText.value.trim() || !activeCard.value || isSubmittingComment.value) return
  isSubmittingComment.value = true

  fetch(`/espace-utilisateur/kanban/cards/${activeCard.value.id}/comments`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': getCsrfToken(),
    },
    body: JSON.stringify({
      comment: newCommentText.value.trim()
    })
  }).then(r => r.json()).then(data => {
    if (data && data.success) {
      if (!Array.isArray(activeCard.value.comments)) {
        activeCard.value.comments = []
      }
      activeCard.value.comments.push(data.comment)
      newCommentText.value = ''
      showMentionDropdown.value = false
      nextTick(() => {
        if (commentsStreamContainer.value) {
          commentsStreamContainer.value.scrollTop = commentsStreamContainer.value.scrollHeight
        }
      })
    }
  }).catch(err => {
    console.error('Comment error:', err)
  }).finally(() => {
    isSubmittingComment.value = false
  })
}

function formatFileSize(bytes) {
  if (!bytes) return '0 Ko'
  const k = 1024
  if (bytes < k) return bytes + ' o'
  if (bytes < k * k) return Math.round(bytes / k) + ' Ko'
  return (bytes / (k * k)).toFixed(1) + ' Mo'
}

function formatTimeAgo(dateStr) {
  if (!dateStr) return ''
  const diff = Date.now() - new Date(dateStr).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'À l\'instant'
  if (mins < 60) return `${mins}m`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h`
  const days = Math.floor(hours / 24)
  return `${days}j`
}

// Column Management
const showAddColumnModal = ref(false)
const editingColumn = ref(null)
const columnForm = ref({
  title: '',
  color: '#3b82f6',
  is_done_column: false,
  wip_limit: null,
})

function openEditColumnModal(col) {
  editingColumn.value = col
  columnForm.value = {
    title: col.title,
    color: col.color || '#3b82f6',
    is_done_column: !!col.is_done_column,
    wip_limit: col.wip_limit ?? null,
  }
}

function closeColumnModal() {
  showAddColumnModal.value = false
  editingColumn.value = null
  columnForm.value = { title: '', color: '#3b82f6', is_done_column: false, wip_limit: null }
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
  if (myTasksOnly.value && currentUserId.value) {
    cards = cards.filter(c => c.assigned_to === currentUserId.value)
  }
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

function getCoverImageUrl(card) {
  if (!card) return null
  if (card.cover_image) {
    return card.cover_image.startsWith('http') || card.cover_image.startsWith('/') 
      ? card.cover_image 
      : '/' + card.cover_image
  }
  if (card.attachments && card.attachments.length > 0) {
    const first = card.attachments[0]
    return first.url.startsWith('http') || first.url.startsWith('/') ? first.url : '/' + first.url
  }
  return null
}

function exportCSV() {
  const headers = ['ID', 'Titre', 'Colonne', 'Statut', 'Priorite', 'Echeance', 'Assigne', 'Estimation_h', 'Temps_Passe_h', 'Checklist']
  const rows = []
  for (const col of props.board.columns || []) {
    for (const card of col.cards || []) {
      const checklistDone = (card.checklist || []).filter(i => i.done).length
      const checklistTotal = (card.checklist || []).length
      rows.push([
        card.id,
        `"${(card.title || '').replace(/"/g, '""')}"`,
        `"${(col.title || '').replace(/"/g, '""')}"`,
        card.is_completed ? 'Termine' : 'En cours',
        card.priority || 'medium',
        card.due_date ? card.due_date.substring(0, 10) : '',
        `"${(card.assignee?.name || '').replace(/"/g, '""')}"`,
        card.estimated_hours || 0,
        card.spent_hours || 0,
        checklistTotal > 0 ? `${checklistDone}/${checklistTotal}` : '',
      ])
    }
  }
  const csvContent = '\uFEFF' + [headers.join(';'), ...rows.map(r => r.join(';'))].join('\r\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  const safeTitle = (props.board.title || 'kanban').toLowerCase().replace(/[^a-z0-9]/gi, '_')
  link.setAttribute('download', `${safeTitle}_export_${new Date().toISOString().substring(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

function exportMarkdown() {
  let md = `# 📋 ${props.board.title}\n`
  if (props.board.description) {
    md += `> ${props.board.description}\n\n`
  }
  md += `*Exporté depuis Tech237 Kanban le ${new Date().toLocaleDateString('fr-FR')}*\n\n`
  
  for (const col of props.board.columns || []) {
    const cards = col.cards || []
    md += `### ${col.title} (${cards.length})\n`
    if (cards.length === 0) {
      md += `*Aucune tâche dans cette colonne*\n\n`
      continue
    }
    for (const card of cards) {
      const check = card.is_completed ? '[x]' : '[ ]'
      let meta = []
      if (card.priority) meta.push(`Priorité: ${getPriorityLabel(card.priority)}`)
      if (card.assignee?.name) meta.push(`Assigné: ${card.assignee.name}`)
      if (card.due_date) meta.push(`Échéance: ${formatDateShort(card.due_date)}`)
      if (card.estimated_hours || card.spent_hours) meta.push(`⏱️ ${card.spent_hours || 0}h/${card.estimated_hours || 0}h`)
      
      const metaStr = meta.length ? ` *(${meta.join(' • ')})*` : ''
      md += `- ${check} **${card.title}**${metaStr}\n`
      if (card.checklist && card.checklist.length) {
        for (const item of card.checklist) {
          md += `  - [${item.done ? 'x' : ' '}] ${item.text}\n`
        }
      }
    }
    md += `\n`
  }
  
  if (navigator?.clipboard?.writeText) {
    navigator.clipboard.writeText(md).then(() => {
      alert('📋 Résumé Markdown copié avec succès dans le presse-papier !')
    }).catch(() => {
      prompt('Copiez le texte Markdown ci-dessous :', md)
    })
  } else {
    prompt('Copiez le texte Markdown ci-dessous :', md)
  }
}

function handleGlobalKeydown(e) {
  const tag = (e.target?.tagName || '').toLowerCase()
  if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target?.isContentEditable) {
    if (e.key === 'Escape') {
      e.target.blur()
    }
    return
  }

  if (e.key === 'Escape') {
    if (activeCard.value) closeCardModal()
    else if (showAddColumnModal.value || editingColumn.value) closeColumnModal()
    else if (showBoardSettingsModal.value) showBoardSettingsModal.value = false
    else if (showTeamModal.value) showTeamModal.value = false
    else if (showArchivesModal.value) showArchivesModal.value = false
  } else if ((e.key === 'f' || e.key === 'F' || e.key === '/') && !e.ctrlKey && !e.metaKey) {
    e.preventDefault()
    const searchInput = document.getElementById('kanbanSearchInput')
    if (searchInput) searchInput.focus()
  } else if ((e.key === 'n' || e.key === 'N') && !e.ctrlKey && !e.metaKey && props.canEdit) {
    e.preventDefault()
    if (props.board.columns && props.board.columns.length > 0) {
      openQuickAdd(props.board.columns[0])
    }
  } else if ((e.key === 'm' || e.key === 'M') && !e.ctrlKey && !e.metaKey) {
    e.preventDefault()
    myTasksOnly.value = !myTasksOnly.value
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
  stopCommentsPolling()
})

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
.kanban-dropzone {
  border: 2px dashed #94a3b8;
  background-color: #f8fafc;
  transition: all 0.2s ease;
}
.kanban-dropzone:hover, .kanban-dropzone.is-dragging {
  border-color: #00A859 !important;
  background-color: #f0fdf4 !important;
}
.comment-body-text {
  line-height: 1.45;
  word-break: break-word;
}
.emoji-btn:hover {
  transform: scale(1.2);
  transition: transform 0.1s ease;
}
.mention-dropdown-menu {
  z-index: 1060;
  width: 250px;
}
</style>
