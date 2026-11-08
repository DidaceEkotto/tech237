<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-cash-stack text-success me-2"></i>Contrôle & Rémunération des Rédacteurs</h3>
            <p class="text-muted small mb-0">Module Super-Admin & Admin : supervisez le lectorat réel de chaque rédacteur et calculez leur rémunération au nombre de lecteurs uniques.</p>
        </div>
        <div class="badge bg-danger px-3 py-2 fs-6 rounded-pill">
            <i class="bi bi-shield-lock-fill me-1"></i> Espace Superviseur
        </div>
    </div>

    <!-- Alert Banner: Comparison with previous period -->
    @if($hasDecrease)
        <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-2 me-3 text-danger"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Alerte Baisse de Trafic ({{ $diffPercent }}%)</h6>
                <div class="small">
                    Le nombre de lectures uniques ({{ number_format($currentGlobalViews, 0, ',', ' ') }}) est inférieur à la période précédente ({{ number_format($previousGlobalViews, 0, ',', ' ') }}). Encouragez les rédacteurs à publier davantage d'articles d'actualité ou de tests captivants.
                </div>
            </div>
        </div>
    @elseif($diffPercent > 0)
        <div class="alert alert-success d-flex align-items-center shadow-sm border-0 rounded-4 mb-4" role="alert">
            <i class="bi bi-graph-up-arrow fs-2 me-3 text-success"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Forte Croissance du Lectorat (+{{ $diffPercent }}%)</h6>
                <div class="small">
                    Excellente dynamique : {{ number_format($currentGlobalViews, 0, ',', ' ') }} lectures uniques enregistrées sur cette période contre {{ number_format($previousGlobalViews, 0, ',', ' ') }} lors de la période précédente.
                </div>
            </div>
        </div>
    @endif

    <!-- Period Filters Bar -->
    <div class="card card-melty mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted d-block mb-1">Période d'évaluation :</label>
                    <div class="btn-group w-100" role="group">
                        <button type="button" wire:click="setPeriod('day')" class="btn btn-sm {{ $period === 'day' ? 'btn-danger fw-bold' : 'btn-outline-secondary' }}">
                            <i class="bi bi-calendar-day me-1"></i> Journalier
                        </button>
                        <button type="button" wire:click="setPeriod('week')" class="btn btn-sm {{ $period === 'week' ? 'btn-danger fw-bold' : 'btn-outline-secondary' }}">
                            <i class="bi bi-calendar-week me-1"></i> Semaine
                        </button>
                        <button type="button" wire:click="setPeriod('month')" class="btn btn-sm {{ $period === 'month' ? 'btn-danger fw-bold' : 'btn-outline-secondary' }}">
                            <i class="bi bi-calendar-month me-1"></i> Par Mois
                        </button>
                    </div>
                </div>

                <div class="col-md-5">
                    @if($period === 'day')
                        <label class="form-label small fw-bold text-muted d-block mb-1">Sélectionner la date :</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-calendar"></i></span>
                            <input type="text" id="overviewDatePicker" class="form-control jquery-datepicker" wire:model.live="selectedDate" placeholder="AAAA-MM-JJ" readonly style="cursor: pointer;">
                        </div>
                    @elseif($period === 'month')
                        <label class="form-label small fw-bold text-muted d-block mb-1">Sélectionner Mois & Année :</label>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" wire:model.live="selectedMonth">
                                <option value="01">Janvier</option>
                                <option value="02">Février</option>
                                <option value="03">Mars</option>
                                <option value="04">Avril</option>
                                <option value="05">Mai</option>
                                <option value="06">Juin</option>
                                <option value="07">Juillet</option>
                                <option value="08">Août</option>
                                <option value="09">Septembre</option>
                                <option value="10">Octobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Décembre</option>
                            </select>
                            <select class="form-select form-select-sm" wire:model.live="selectedYear">
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                        </div>
                    @else
                        <label class="form-label small fw-bold text-muted d-block mb-1">Période active :</label>
                        <div class="badge bg-light text-dark border p-2 w-100 text-truncate">
                            {{ $periodLabel }}
                        </div>
                    @endif
                </div>

                <div class="col-md-2 text-md-end">
                    <span class="badge bg-dark px-3 py-2">
                        <i class="bi bi-people-fill me-1"></i> {{ count($writers) }} rédacteur(s)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Global KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-danger shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Lectures Uniques Globales</span>
                    <h3 class="fw-black text-danger mb-0">{{ number_format($currentGlobalViews, 0, ',', ' ') }}</h3>
                    <small class="text-muted mt-1 d-block">{{ $periodLabel }}</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-primary shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Période Précédente</span>
                    <h3 class="fw-black text-primary mb-0">{{ number_format($previousGlobalViews, 0, ',', ' ') }}</h3>
                    <small class="{{ $diffPercent >= 0 ? 'text-success' : 'text-danger' }} fw-bold mt-1 d-block">
                        {{ $diffPercent >= 0 ? '+' : '' }}{{ $diffPercent }}% de variation
                    </small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-dark shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Historique Global</span>
                    <h3 class="fw-black text-dark mb-0">{{ number_format($totalAllTimeViews, 0, ',', ' ') }}</h3>
                    <small class="text-muted mt-1 d-block">Toutes publications confondues</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-success shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Enveloppe Rémunération</span>
                    <h3 class="fw-black text-success mb-0">{{ number_format($totalEstimatedPayout, 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                    <small class="text-muted mt-1 d-block">À verser aux rédacteurs</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Timeline Chart -->
    <div class="card card-melty mb-4">
        <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-graph-up text-danger me-2"></i>Courbe Globale du Lectorat ({{ $periodLabel }})</span>
            <span class="badge bg-light text-dark border">Temps réel</span>
        </div>
        <div class="card-body p-4">
            <div style="height: 260px; width: 100%; position: relative;">
                <canvas id="overviewStatsChart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Writers Table -->
    <div class="card card-melty">
        <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-people text-danger me-2"></i>Classement & Rémunération des Rédacteurs</span>
            <small class="text-muted">Cliquez sur un rédacteur pour consulter son rapport détaillé</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Rédacteur</th>
                        <th class="text-center">Publications</th>
                        <th class="text-center">Lectures (Période)</th>
                        <th class="text-center">Évolution</th>
                        <th class="text-center">Lectures Totales</th>
                        <th class="text-end">Rémunération</th>
                        <th class="text-center" style="width: 160px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($writers as $idx => $w)
                        <tr>
                            <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-dark text-white fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 13px;">
                                        {{ substr($w->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.writers.detail', $w->id) }}" class="fw-bold text-dark text-decoration-none hover-danger">
                                            {{ $w->name }}
                                        </a>
                                        <div class="text-muted small" style="font-size: 11px;">{{ $w->email }} &bull; <span class="badge bg-secondary" style="font-size: 9px;">{{ $w->role }}</span></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    {{ $w->articles_count }} art. &bull; {{ $w->game_reviews_count }} jeux &bull; {{ $w->app_reviews_count }} apps
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6 px-3 py-1 rounded-pill">{{ number_format($w->current_period_views, 0, ',', ' ') }}</span>
                            </td>
                            <td class="text-center">
                                @if($w->diff_percent > 0)
                                    <span class="badge bg-success-subtle text-success fw-bold"><i class="bi bi-arrow-up"></i> +{{ $w->diff_percent }}%</span>
                                @elseif($w->diff_percent < 0)
                                    <span class="badge bg-danger-subtle text-danger fw-bold"><i class="bi bi-arrow-down"></i> {{ $w->diff_percent }}%</span>
                                @else
                                    <span class="text-muted small">0%</span>
                                @endif
                            </td>
                            <td class="text-center text-muted fw-semibold">
                                {{ number_format($w->total_unique_views, 0, ',', ' ') }}
                            </td>
                            <td class="text-end fw-bold text-success fs-6">
                                {{ number_format($w->estimated_payout, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.writers.detail', $w->id) }}" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                    <i class="bi bi-bar-chart-fill me-1"></i> Détails
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Aucun rédacteur trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($writers) > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-uppercase">Total plateforme :</td>
                            <td class="text-center text-danger fs-5">{{ number_format($currentGlobalViews, 0, ',', ' ') }}</td>
                            <td></td>
                            <td class="text-center text-muted">{{ number_format($totalAllTimeViews, 0, ',', ' ') }}</td>
                            <td class="text-end text-success fs-5">{{ number_format($totalEstimatedPayout, 0, ',', ' ') }} FCFA</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function renderOverviewChart() {
        if (!window.MeltyChart) return;
        const labels = @json($chartLabels);
        const data = @json($chartCurrent);
        MeltyChart.drawLineChart('overviewStatsChart', labels, data, {
            strokeColor: '#E11D48',
            fillColor: 'rgba(225, 29, 72, 0.20)'
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderOverviewChart();

        if ($.fn.datepicker) {
            $('#overviewDatePicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                onSelect: function(dateText) {
                    @this.set('selectedDate', dateText);
                }
            });
        }
    });

    document.addEventListener('livewire:navigated', renderOverviewChart);
    window.addEventListener('resize', renderOverviewChart);
</script>
@endpush
