<div>
    <!-- Header & Statistics -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-black mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-receipt-cutoff text-warning"></i> Services Freelance &amp; Devis (Lecture Admin)
            </h3>
            <p class="text-muted small mb-0">
                Supervision des devis, factures et simulateurs de tarifs journaliers (TJM) émis par les freelances de la communauté Tech237.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-file-earmark-text me-1 text-primary"></i> Total Documents : <strong>{{ $totalQuotes }}</strong>
            </span>
            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-cash-stack me-1"></i> Volume Facturé : <strong>{{ number_format($totalAmountSum, 0, ',', ' ') }} FCFA</strong>
            </span>
            <span class="badge bg-info-subtle text-info border border-info border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-check-circle-fill me-1"></i> Acceptés / Payés : <strong>{{ $acceptedCount + $paidCount }}</strong>
            </span>
            <span class="badge bg-warning-subtle text-dark border border-warning border-opacity-25 px-3 py-2 rounded-pill fw-semibold shadow-xs">
                <i class="bi bi-person-workspace me-1 text-warning"></i> Freelances Actifs : <strong>{{ $freelancersCount }}</strong>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3">
        <li class="nav-item">
            <button 
                type="button" 
                class="nav-link rounded-pill fw-bold small px-4 d-inline-flex align-items-center gap-2 {{ $activeTab === 'quotes' ? 'active bg-dark text-white' : 'text-muted bg-light' }}"
                wire:click="$set('activeTab', 'quotes')"
            >
                <i class="bi bi-receipt"></i>
                <span>Devis &amp; Factures Émis ({{ $totalQuotes }})</span>
            </button>
        </li>
        <li class="nav-item">
            <button 
                type="button" 
                class="nav-link rounded-pill fw-bold small px-4 d-inline-flex align-items-center gap-2 {{ $activeTab === 'settings' ? 'active bg-dark text-white' : 'text-muted bg-light' }}"
                wire:click="$set('activeTab', 'settings')"
            >
                <i class="bi bi-calculator"></i>
                <span>Simulateurs &amp; Tarifs Freelances ({{ $freelancersCount }})</span>
            </button>
        </li>
    </ul>

    <!-- TAB 1: QUOTES & INVOICES -->
    @if($activeTab === 'quotes')
        <!-- Filters Toolbar -->
        <div class="card card-melty shadow-sm mb-4">
            <div class="card-body p-3 bg-light bg-opacity-50">
                <div class="row g-2 align-items-center">
                    <!-- Search -->
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control border-start-0" 
                                placeholder="Rechercher par n° de devis, client, entreprise ou freelance..." 
                                wire:model.live.debounce.300ms="search"
                            >
                            @if(!empty($search))
                                <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" wire:click="$set('search', '')">
                                    <i class="bi bi-x"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Type Filter -->
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" wire:model.live="typeFilter">
                            <option value="">Tous les types de document</option>
                            <option value="devis">Devis (Estimation)</option>
                            <option value="invoice">Facture (Paiement)</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" wire:model.live="statusFilter">
                            <option value="">Tous les statuts</option>
                            <option value="draft">Brouillon</option>
                            <option value="sent">Envoyé</option>
                            <option value="accepted">Accepté par le client</option>
                            <option value="rejected">Refusé</option>
                            <option value="paid">Payé</option>
                            <option value="overdue">En retard</option>
                        </select>
                    </div>

                    <!-- Reset -->
                    <div class="col-md-1 text-end">
                        <button 
                            type="button" 
                            wire:click="$set('search', ''); $set('typeFilter', ''); $set('statusFilter', '');" 
                            class="btn btn-sm btn-light border w-100" 
                            title="Réinitialiser"
                        >
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quotes Table -->
        <div class="card card-melty shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Document</th>
                            <th>Type</th>
                            <th>Objet / Mission</th>
                            <th>Client Destinataire</th>
                            <th>Auteur Freelance</th>
                            <th class="text-end">Montant Total</th>
                            <th class="text-center">Statut</th>
                            <th>Date d'émission</th>
                            <th>Échéance</th>
                            <th class="text-end" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotes as $q)
                            @php
                                $statusBadges = [
                                    'draft' => ['label' => 'Brouillon', 'class' => 'bg-secondary-subtle text-secondary'],
                                    'sent' => ['label' => 'Envoyé', 'class' => 'bg-info-subtle text-info border border-info'],
                                    'accepted' => ['label' => 'Accepté', 'class' => 'bg-success-subtle text-success border border-success'],
                                    'paid' => ['label' => 'Payé', 'class' => 'bg-success text-white'],
                                    'rejected' => ['label' => 'Refusé', 'class' => 'bg-danger-subtle text-danger'],
                                    'overdue' => ['label' => 'En retard', 'class' => 'bg-danger text-white'],
                                ];
                                $sb = $statusBadges[$q->status] ?? ['label' => ucfirst($q->status), 'class' => 'bg-light text-dark'];
                            @endphp
                            <tr>
                                <td>
                                    <code class="fw-bold text-dark extra-small">{{ $q->quote_number }}</code>
                                </td>
                                <td>
                                    <span class="badge {{ $q->type === 'invoice' ? 'bg-primary text-white' : 'bg-warning-subtle text-dark border border-warning' }} extra-small rounded-pill">
                                        {{ $q->type === 'invoice' ? 'Facture' : 'Devis' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small text-truncate" style="max-width: 220px;">
                                        {{ $q->title ?: 'Prestation de service IT' }}
                                    </div>
                                    @if($q->items && is_array($q->items))
                                        <small class="text-muted extra-small">{{ count($q->items) }} ligne(s) de prestation</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark extra-small">{{ $q->client_name ?: 'Non renseigné' }}</div>
                                    @if($q->client_company)
                                        <small class="text-muted extra-small">{{ $q->client_company }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($q->user)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-dark fw-bold extra-small" style="width: 26px; height: 26px; overflow: hidden;">
                                                @if($q->user->avatar)
                                                    <img src="{{ asset($q->user->avatar) }}" alt="{{ $q->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    {{ strtoupper(substr($q->user->name, 0, 1)) }}
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark extra-small">{{ $q->user->name }}</div>
                                                <div class="text-muted extra-small">{{ $q->user->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted extra-small">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong class="text-dark fs-6">{{ number_format($q->total_amount, 0, ',', ' ') }}</strong>
                                    <small class="text-muted extra-small">{{ $q->currency ?: 'FCFA' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $sb['class'] }} rounded-pill extra-small">
                                        {{ $sb['label'] }}
                                    </span>
                                </td>
                                <td class="extra-small text-muted">
                                    {{ $q->issue_date ? $q->issue_date->format('d/m/Y') : '—' }}
                                </td>
                                <td class="extra-small text-muted">
                                    {{ $q->due_date ? $q->due_date->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-end">
                                    <a 
                                        href="{{ route('admin.freelance.show', $q->id) }}" 
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-xs"
                                    >
                                        <i class="bi bi-eye-fill"></i>
                                        <span>Consulter</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt fs-1 d-block mb-2 opacity-50"></i>
                                    <span>Aucun devis ni facture ne correspond aux critères de recherche.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($quotes->hasPages())
                <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="extra-small text-muted">
                        Affichage de {{ $quotes->firstItem() }} à {{ $quotes->lastItem() }} sur {{ $quotes->total() }} documents
                    </div>
                    <div>
                        {{ $quotes->links() }}
                    </div>
                </div>
            @endif
        </div>

    @else
        <!-- TAB 2: FREELANCE SIMULATOR SETTINGS -->
        <div class="card card-melty shadow-sm">
            <div class="card-header card-header-melty d-flex align-items-center justify-content-between">
                <span class="fw-bold">Profils &amp; Simulateurs de Taux Journalier Moyen (TJM)</span>
                <span class="badge bg-light text-dark border extra-small">{{ count($settingsList) }} profil(s) configuré(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Freelance</th>
                            <th>Revenu Net Ciblé</th>
                            <th>TJM Calculé (Jour)</th>
                            <th>THM (Heure)</th>
                            <th class="text-center">Semaines / an</th>
                            <th class="text-center">Jours / sem</th>
                            <th class="text-center">Charges &amp; Impôts</th>
                            <th>Moyens de paiement</th>
                            <th>Mis à jour le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settingsList as $st)
                            <tr>
                                <td>
                                    @if($st->user)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-dark fw-bold extra-small" style="width: 32px; height: 32px; overflow: hidden;">
                                                @if($st->user->avatar)
                                                    <img src="{{ asset($st->user->avatar) }}" alt="{{ $st->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    {{ strtoupper(substr($st->user->name, 0, 1)) }}
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark small">{{ $st->user->name }}</div>
                                                <div class="text-muted extra-small">{{ $st->user->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted extra-small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-dark">{{ number_format($st->target_monthly_net, 0, ',', ' ') }}</strong>
                                    <small class="text-muted extra-small">FCFA / mois</small>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fs-6">
                                        {{ number_format($st->computed_tjm, 0, ',', ' ') }} FCFA
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 extra-small fw-semibold">
                                        {{ number_format($st->computed_thm, 0, ',', ' ') }} FCFA/h
                                    </span>
                                </td>
                                <td class="text-center extra-small text-muted">
                                    {{ $st->work_weeks_per_year ?: 44 }} sem.
                                </td>
                                <td class="text-center extra-small text-muted">
                                    {{ $st->work_days_per_week ?: 5 }} j/sem.
                                </td>
                                <td class="text-center extra-small text-muted">
                                    {{ $st->tax_percent }}%
                                </td>
                                <td class="extra-small text-muted">
                                    @php
                                        $payments = is_array($st->payment_info) ? $st->payment_info : [];
                                    @endphp
                                    @if(count($payments) > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($payments as $k => $v)
                                                @if(!empty($v))
                                                    <span class="badge bg-light text-dark border extra-small">
                                                        {{ ucfirst($k) }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="extra-small text-muted">
                                    {{ $st->updated_at ? $st->updated_at->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-calculator fs-1 d-block mb-2 opacity-50"></i>
                                    <span>Aucun freelance n'a encore enregistré son simulateur de tarifs.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
