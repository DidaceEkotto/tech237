<div>
    <!-- Top Bar Navigation & Info -->
    <div class="card card-melty shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('admin.freelance.index') }}" class="btn btn-sm btn-light rounded-circle p-2 text-muted shadow-xs" title="Retour aux devis & factures">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-black mb-0 text-dark">{{ $quote->quote_number }}</h4>
                            <span class="badge {{ $quote->type === 'invoice' ? 'bg-primary' : 'bg-warning-subtle text-dark border border-warning' }} rounded-pill px-2 py-1 extra-small">
                                {{ $quote->type === 'invoice' ? 'Facture' : 'Devis' }}
                            </span>
                            @php
                                $statusBadges = [
                                    'draft' => ['label' => 'Brouillon', 'class' => 'bg-secondary-subtle text-secondary'],
                                    'sent' => ['label' => 'Envoyé', 'class' => 'bg-info-subtle text-info border border-info'],
                                    'accepted' => ['label' => 'Accepté par le client', 'class' => 'bg-success-subtle text-success border border-success'],
                                    'paid' => ['label' => 'Payé', 'class' => 'bg-success text-white'],
                                    'rejected' => ['label' => 'Refusé', 'class' => 'bg-danger-subtle text-danger'],
                                    'overdue' => ['label' => 'En retard', 'class' => 'bg-danger text-white'],
                                ];
                                $sb = $statusBadges[$quote->status] ?? ['label' => ucfirst($quote->status), 'class' => 'bg-light text-dark'];
                            @endphp
                            <span class="badge {{ $sb['class'] }} rounded-pill px-2 py-1 extra-small">
                                {{ $sb['label'] }}
                            </span>
                            <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill extra-small fw-bold">
                                <i class="bi bi-eye-fill me-1"></i>Mode Lecture Seule
                            </span>
                        </div>
                        <div class="extra-small text-muted mt-1 d-flex align-items-center gap-2 flex-wrap">
                            @if($quote->user)
                                <span>Auteur Freelance : <strong>{{ $quote->user->name }}</strong> ({{ $quote->user->email }})</span>
                                <span>•</span>
                            @endif
                            <span>Émis le {{ $quote->issue_date ? $quote->issue_date->format('d/m/Y') : '—' }}</span>
                            <span>•</span>
                            <span>Échéance : <strong>{{ $quote->due_date ? $quote->due_date->format('d/m/Y') : '—' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <span class="text-muted extra-small d-block">Montant Total :</span>
                    <strong class="fs-4 text-dark">{{ number_format($quote->total_amount, 0, ',', ' ') }} {{ $quote->currency ?: 'FCFA' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Detail Container -->
    <div class="row g-4">
        <!-- Main Document Details -->
        <div class="col-lg-8">
            <div class="card card-melty shadow-sm p-4 p-md-5 bg-white mb-4">
                <!-- Header of Quote -->
                <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4 flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">{{ $quote->title ?: 'Prestation de services informatiques' }}</h5>
                        <div class="text-muted small">Réf : <code>{{ $quote->quote_number }}</code></div>
                    </div>
                    @if($quote->logo_url)
                        <div>
                            <img src="{{ asset($quote->logo_url) }}" alt="Logo" class="rounded-3 border p-1" style="max-height: 50px;">
                        </div>
                    @endif
                </div>

                <!-- Client & Dates Row -->
                <div class="row g-3 mb-4 p-3 bg-light rounded-4 border extra-small">
                    <div class="col-md-6 border-end">
                        <strong class="d-block text-dark small mb-1">Client Destinataire :</strong>
                        <div class="fw-bold text-dark">{{ $quote->client_name ?: 'Non renseigné' }}</div>
                        @if($quote->client_company)
                            <div>Entreprise : {{ $quote->client_company }}</div>
                        @endif
                        @if($quote->client_email)
                            <div>Email : {{ $quote->client_email }}</div>
                        @endif
                        @if($quote->client_phone)
                            <div>Tél : {{ $quote->client_phone }}</div>
                        @endif
                        @if($quote->client_address)
                            <div>Adresse : {{ $quote->client_address }}</div>
                        @endif
                    </div>
                    <div class="col-md-6 ps-md-3">
                        <strong class="d-block text-dark small mb-1">Informations de facturation :</strong>
                        <div>Devise : <strong>{{ $quote->currency ?: 'FCFA' }}</strong></div>
                        <div>Date d'émission : {{ $quote->issue_date ? $quote->issue_date->format('d/m/Y') : '—' }}</div>
                        <div>Date d'échéance : {{ $quote->due_date ? $quote->due_date->format('d/m/Y') : '—' }}</div>
                        @if($quote->accepted_at)
                            <div class="text-success fw-bold">Accepté le : {{ $quote->accepted_at->format('d/m/Y à H:i') }}</div>
                        @endif
                        @if($quote->paid_at)
                            <div class="text-success fw-bold">Payé le : {{ $quote->paid_at->format('d/m/Y à H:i') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light extra-small">
                            <tr>
                                <th>Description de la prestation</th>
                                <th class="text-center" style="width: 100px;">Quantité</th>
                                <th class="text-end" style="width: 140px;">Prix unitaire</th>
                                <th class="text-end" style="width: 140px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $items = is_array($quote->items) ? $quote->items : [];
                            @endphp
                            @forelse($items as $item)
                                @php
                                    $qty = (float)($item['quantity'] ?? 1);
                                    $price = (float)($item['unit_price'] ?? 0);
                                    $lineTotal = (float)($item['total'] ?? ($qty * $price));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $item['description'] ?? 'Ligne de service' }}</div>
                                    </td>
                                    <td class="text-center extra-small text-muted">{{ $qty }}</td>
                                    <td class="text-end extra-small">{{ number_format($price, 0, ',', ' ') }} {{ $quote->currency ?: 'FCFA' }}</td>
                                    <td class="text-end fw-bold small text-dark">{{ number_format($lineTotal, 0, ',', ' ') }} {{ $quote->currency ?: 'FCFA' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Aucune ligne de prestation enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Financial Totals -->
                <div class="d-flex justify-content-end mb-4">
                    <div style="min-width: 260px;">
                        <div class="d-flex justify-content-between py-1 border-bottom extra-small text-muted">
                            <span>Sous-total HT :</span>
                            <strong class="text-dark">{{ number_format($quote->subtotal, 0, ',', ' ') }} {{ $quote->currency ?: 'FCFA' }}</strong>
                        </div>
                        @if($quote->discount_amount > 0)
                            <div class="d-flex justify-content-between py-1 border-bottom extra-small text-danger">
                                <span>Remise accordée :</span>
                                <strong>-{{ number_format($quote->discount_amount, 0, ',', ' ') }} {{ $quote->currency ?: 'FCFA' }}</strong>
                            </div>
                        @endif
                        @if($quote->tax_amount > 0)
                            <div class="d-flex justify-content-between py-1 border-bottom extra-small text-muted">
                                <span>TVA / Taxe ({{ $quote->tax_rate }}%) :</span>
                                <strong class="text-dark">+{{ number_format($quote->tax_amount, 0, ',', ' ') }} {{ $quote->currency ?: 'FCFA' }}</strong>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between py-2 fs-6 fw-black text-dark border-top">
                            <span>Total à payer :</span>
                            <span class="text-success">{{ number_format($quote->total_amount, 0, ',', ' ') }} {{ $quote->currency ?: 'FCFA' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes & Terms -->
                @if($quote->notes || $quote->terms)
                    <div class="pt-3 border-top extra-small text-muted">
                        @if($quote->notes)
                            <div class="mb-2">
                                <strong class="text-dark">Notes :</strong>
                                <div>{{ $quote->notes }}</div>
                            </div>
                        @endif
                        @if($quote->terms)
                            <div>
                                <strong class="text-dark">Modalités &amp; Conditions :</strong>
                                <div>{{ $quote->terms }}</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar: Milestones & Payment Methods -->
        <div class="col-lg-4">
            <!-- Payment Milestones -->
            @php
                $milestones = is_array($quote->payment_milestones) ? $quote->payment_milestones : [];
            @endphp
            @if(count($milestones) > 0)
                <div class="card card-melty shadow-sm p-4 bg-white mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-check text-primary"></i> Échéancier de Paiement
                    </h6>
                    <div class="d-flex flex-column gap-2 extra-small">
                        @foreach($milestones as $m)
                            <div class="p-2 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-dark d-block">{{ $m['label'] ?? 'Jalon' }}</strong>
                                    <span class="text-muted">{{ $m['percentage'] ?? 0 }}%</span>
                                </div>
                                <span class="badge bg-white text-dark border fw-bold">
                                    {{ number_format($m['amount'] ?? 0, 0, ',', ' ') }} {{ $quote->currency ?: 'FCFA' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Payment Methods -->
            @php
                $methods = is_array($quote->payment_methods) ? $quote->payment_methods : [];
            @endphp
            @if(count($methods) > 0)
                <div class="card card-melty shadow-sm p-4 bg-white mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-wallet2 text-success"></i> Moyens de Règlement Proposés
                    </h6>
                    <div class="d-flex flex-column gap-2 extra-small">
                        @foreach($methods as $key => $val)
                            @if(!empty($val))
                                <div class="p-2 bg-light rounded-3 border">
                                    <strong class="text-dark d-block mb-1">{{ ucfirst($key) }} :</strong>
                                    <code class="text-dark">{{ is_array($val) ? json_encode($val) : $val }}</code>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
