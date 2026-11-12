<div>
    <!-- Page Header & Stats -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-black mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-geo-alt-fill text-success"></i> Gestion des Villes (Cameroun)
            </h3>
            <p class="text-muted small mb-0">
                Gérez la liste officielle et unique des villes camerounaises utilisées dans les formulaires, profils, événements et offres d'emploi.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" wire:click="importAllCities" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-xs" title="Importer ou resynchroniser les 98 villes officielles du Cameroun">
                <i class="bi bi-cloud-arrow-down-fill me-1"></i> Importer les 98 villes
            </button>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-buildings me-1 text-primary"></i> Total : <strong>{{ $totalCount }}</strong>
            </span>
            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-check-circle-fill me-1"></i> Actives : <strong>{{ $activeCount }}</strong>
            </span>
            @if($inactiveCount > 0)
                <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill fw-semibold shadow-xs">
                    <i class="bi bi-eye-slash-fill me-1"></i> Inactives : <strong>{{ $inactiveCount }}</strong>
                </span>
            @endif
        </div>
    </div>

    @if($totalCount === 0)
        <div class="alert alert-info rounded-4 border-2 border-info shadow-sm p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-cloud-arrow-down-fill fs-1 text-info"></i>
                <div>
                    <strong class="d-block text-dark fs-6">Aucune ville n'est actuellement enregistrée dans la base de données.</strong>
                    <span class="small text-muted">Vous pouvez importer instantanément la liste officielle des 98 principales villes du Cameroun réparties par régions.</span>
                </div>
            </div>
            <button type="button" wire:click="importAllCities" class="btn btn-info text-white rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-download me-1"></i> Importer les 98 villes
            </button>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Form Column (Add / Edit) -->
        <div class="col-lg-4">
            <div class="card card-melty shadow-sm sticky-top" style="top: 85px;">
                <div class="card-header card-header-melty d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-{{ $isEditing ? 'pencil-square text-warning' : 'plus-circle-fill text-success' }} fs-5"></i>
                        <span class="fw-bold">{{ $isEditing ? 'Modifier la Ville' : 'Ajouter une Ville' }}</span>
                    </div>
                    @if($isEditing)
                        <button type="button" wire:click="resetFields" class="btn btn-xs btn-outline-secondary rounded-pill">
                            Annuler
                        </button>
                    @endif
                </div>

                <div class="card-body p-4">
                    <form wire:submit.prevent="save">
                        <!-- City Name -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">
                                Nom de la ville <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                placeholder="ex: Douala, Yaoundé, Buea..." 
                                wire:model.live="name" 
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback fw-semibold">{{ $message }}</div>
                            @enderror
                            <div class="form-text extra-small text-muted">
                                Unicité obligatoire : impossible d'enregistrer deux fois la même ville.
                            </div>
                        </div>

                        <!-- Slug URL -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Identifiant URL (Slug)</label>
                            <input 
                                type="text" 
                                class="form-control form-control-sm font-monospace @error('slug') is-invalid @enderror" 
                                placeholder="ex: douala" 
                                wire:model="slug"
                            >
                            @error('slug')
                                <div class="invalid-feedback fw-semibold">{{ $message }}</div>
                            @enderror
                            <div class="form-text extra-small text-muted">Généré automatiquement à partir du nom.</div>
                        </div>

                        <!-- Region -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Région (Cameroun)</label>
                            <select class="form-select form-select-sm @error('region') is-invalid @enderror" wire:model="region">
                                <option value="">-- Sélectionner une région (Optionnel) --</option>
                                @foreach($cameroonRegions as $reg)
                                    <option value="{{ $reg }}">{{ $reg }}</option>
                                @endforeach
                            </select>
                            @error('region')
                                <div class="invalid-feedback fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Ordre d'affichage (Tri)</label>
                            <input 
                                type="number" 
                                min="0" 
                                class="form-control form-control-sm @error('sort_order') is-invalid @enderror" 
                                wire:model="sort_order"
                            >
                            @error('sort_order')
                                <div class="invalid-feedback fw-semibold">{{ $message }}</div>
                            @enderror
                            <div class="form-text extra-small text-muted">0 = ordre alphabétique par défaut. Un chiffre inférieur apparaît en priorité.</div>
                        </div>

                        <!-- Active status checkbox -->
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="cityIsActiveSwitch" wire:model="is_active">
                            <label class="form-check-label small fw-bold text-dark" for="cityIsActiveSwitch">
                                Ville active (affichée dans les menus et filtres)
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-melty flex-grow-1 shadow-sm fw-bold">
                                <i class="bi bi-{{ $isEditing ? 'check2-circle' : 'plus-lg' }} me-1"></i>
                                {{ $isEditing ? 'Mettre à jour' : 'Enregistrer la ville' }}
                            </button>
                            @if($isEditing)
                                <button type="button" wire:click="resetFields" class="btn btn-light border">
                                    Annuler
                                </button>
                            @endif
                        </div>
                    </form>

                    <!-- Uniqueness Guarantee Alert -->
                    <div class="mt-4 p-3 bg-light rounded-3 border extra-small text-muted">
                        <i class="bi bi-shield-check text-success me-1"></i>
                        <strong>Contrôle anti-doublon :</strong> Le système vérifie en temps réel le nom et le slug pour garantir qu'aucune ville ne puisse être dupliquée, quelle que soit la casse (minuscule/majuscule).
                    </div>
                </div>
            </div>
        </div>

        <!-- List Column -->
        <div class="col-lg-8">
            <div class="card card-melty shadow-sm">
                <!-- Filters Bar -->
                <div class="card-body border-bottom p-3 bg-light bg-opacity-50">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control border-start-0" 
                                    placeholder="Rechercher une ville, un slug ou une région..." 
                                    wire:model.live.debounce.300ms="search"
                                >
                                @if(!empty($search))
                                    <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" wire:click="$set('search', '')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select class="form-select form-select-sm" wire:model.live="regionFilter">
                                <option value="">Toutes les régions</option>
                                @foreach($cameroonRegions as $reg)
                                    <option value="{{ $reg }}">{{ $reg }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select class="form-select form-select-sm" wire:model.live="statusFilter">
                                <option value="">Tous les statuts</option>
                                <option value="active">Actives uniquement</option>
                                <option value="inactive">Inactives uniquement</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Nom de la Ville</th>
                                <th>Slug</th>
                                <th>Région</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Ordre</th>
                                <th class="text-end" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cities as $c)
                                <tr class="{{ $cityId === $c->id ? 'table-warning' : '' }}">
                                    <td class="text-muted extra-small">{{ $c->id }}</td>
                                    <td>
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="bi bi-geo-alt text-success opacity-75"></i>
                                            <span>{{ $c->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="extra-small">{{ $c->slug }}</code>
                                    </td>
                                    <td>
                                        @if($c->region)
                                            <span class="badge bg-light text-dark border extra-small fw-semibold">
                                                {{ $c->region }}
                                            </span>
                                        @else
                                            <span class="text-muted extra-small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button 
                                            type="button" 
                                            wire:click="toggleStatus({{ $c->id }})" 
                                            class="badge rounded-pill border-0 px-2 py-1 extra-small cursor-pointer {{ $c->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}"
                                            title="Cliquer pour {{ $c->is_active ? 'désactiver' : 'activer' }}"
                                        >
                                            <i class="bi bi-{{ $c->is_active ? 'check-circle' : 'slash-circle' }} me-1"></i>
                                            {{ $c->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="text-center text-muted extra-small">
                                        {{ $c->sort_order }}
                                    </td>
                                    <td class="text-end">
                                        <button 
                                            type="button" 
                                            wire:click="edit({{ $c->id }})" 
                                            class="btn btn-sm btn-outline-primary me-1 rounded-circle p-1" 
                                            style="width: 32px; height: 32px;"
                                            title="Modifier cette ville"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-danger rounded-circle p-1" 
                                            style="width: 32px; height: 32px;"
                                            title="Supprimer cette ville"
                                            onclick="confirmStaticAction('Supprimer la ville', 'Voulez-vous vraiment supprimer définitivement la ville « {{ addslashes($c->name) }} » ?', function() { @this.call('delete', {{ $c->id }}); })"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-geo-alt fs-1 d-block mb-2 opacity-50"></i>
                                        <span>Aucune ville ne correspond à votre recherche.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($cities->hasPages())
                    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="extra-small text-muted">
                            Affichage de {{ $cities->firstItem() }} à {{ $cities->lastItem() }} sur {{ $cities->total() }} villes
                        </div>
                        <div>
                            {{ $cities->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
