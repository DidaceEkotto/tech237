<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-danger me-2"></i>Mes Statistiques & Rémunération</h3>
            <p class="text-muted small mb-0">Suivez le nombre de personnes réelles ayant lu vos articles et tests, calculé par lecteur unique pour votre rémunération.</p>
        </div>
        <div class="badge bg-dark px-3 py-2 fs-6 rounded-pill">
            <i class="bi bi-person-badge me-1 text-danger"></i> Rédacteur : {{ $admin->name }}
        </div>
    </div>

    <!-- Filters Bar (Period & Date/Month Picker) -->
    <div class="card card-melty mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <!-- Period Toggle Buttons -->
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted d-block mb-1">Période d'analyse :</label>
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

                <!-- Custom Pickers based on Period -->
                <div class="col-md-4">
                    @if($period === 'day')
                        <label class="form-label small fw-bold text-muted d-block mb-1">Sélectionner le jour (jQuery UI) :</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-calendar"></i></span>
                            <input type="text" id="statsDatePicker" class="form-control jquery-datepicker" wire:model.live="selectedDate" placeholder="AAAA-MM-JJ" readonly style="cursor: pointer;">
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

                <!-- Content Type Filter -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted d-block mb-1">Filtrer par type :</label>
                    <select class="form-select form-select-sm" wire:model.live="contentType">
                        <option value="all">Tous mes contenus</option>
                        <option value="articles">Articles uniquement</option>
                        <option value="game_reviews">Tests Jeux Vidéo</option>
                        <option value="app_reviews">Tests Applications</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-danger shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Lectures Uniques (Période)</span>
                    <h3 class="fw-black text-danger mb-0">{{ number_format($periodViewsCount, 0, ',', ' ') }}</h3>
                    <small class="text-muted mt-1 d-block">{{ $periodLabel }}</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-dark shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Historique</span>
                    <h3 class="fw-black text-dark mb-0">{{ number_format($totalAllTimeViews, 0, ',', ' ') }}</h3>
                    <small class="text-muted mt-1 d-block">Lecteurs uniques cumulés</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-primary shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Contenus Publiés</span>
                    <h3 class="fw-black text-primary mb-0">{{ $totalContents }}</h3>
                    <small class="text-muted mt-1 d-block">{{ $totalArticles }} art. &bull; {{ $totalGameReviews }} jeux &bull; {{ $totalAppReviews }} apps</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-success shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Rémunération Estimée</span>
                    <h3 class="fw-black text-success mb-0">{{ number_format($estimatedEarnings, 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                    <small class="text-muted mt-1 d-block">Basée sur les lectures uniques</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart: Curve of unique views -->
    <div class="card card-melty mb-4">
        <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-activity text-danger me-2"></i>Courbe d'évolution du lectorat ({{ $periodLabel }})</span>
            <span class="badge bg-light text-dark border">Temps réel</span>
        </div>
        <div class="card-body p-4">
            <div style="height: 260px; width: 100%; position: relative;">
                <canvas id="myStatsChart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Content Table -->
    <div class="card card-melty">
        <div class="card-header card-header-melty d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-list-check text-danger me-2"></i>Détail par contenu ({{ count($contents) }})</span>
            <small class="text-muted">Trié par nombre de lectures sur la période sélectionnée</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Titre du contenu</th>
                        <th>Type</th>
                        <th class="text-center">Lectures sur la période</th>
                        <th class="text-center">Total historique</th>
                        <th class="text-end">Rémunération estimée</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contents as $idx => $item)
                        @php
                            $itemEarnings = round(($item->period_views / 1000) * 1500);
                        @endphp
                        <tr>
                            <td class="text-muted fw-bold">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->title_fr }}</div>
                                <small class="text-muted">{{ $item->slug }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $item->type_badge }} rounded-pill">{{ $item->type_label }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6 px-3 py-1 rounded-pill">{{ number_format($item->period_views, 0, ',', ' ') }}</span>
                            </td>
                            <td class="text-center text-muted fw-semibold">
                                {{ number_format($item->views_count, 0, ',', ' ') }}
                            </td>
                            <td class="text-end fw-bold text-success">
                                {{ number_format($itemEarnings, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Aucun contenu trouvé pour les filtres sélectionnés.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($contents) > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-uppercase">Total cumulé :</td>
                            <td class="text-center text-danger fs-5">{{ number_format($periodViewsCount, 0, ',', ' ') }}</td>
                            <td class="text-center text-muted">{{ number_format($totalAllTimeViews, 0, ',', ' ') }}</td>
                            <td class="text-end text-success fs-5">{{ number_format($estimatedEarnings, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function renderMyStatsChart() {
        if (!window.MeltyChart) return;
        const labels = @json($chartLabels);
        const data = @json($chartValues);
        MeltyChart.drawLineChart('myStatsChart', labels, data, {
            strokeColor: '#00A859',
            fillColor: 'rgba(0, 168, 89, 0.20)'
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderMyStatsChart();

        if ($.fn.datepicker) {
            $('#statsDatePicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                onSelect: function(dateText) {
                    @this.set('selectedDate', dateText);
                }
            });
        }
    });

    document.addEventListener('livewire:navigated', renderMyStatsChart);
    window.addEventListener('resize', renderMyStatsChart);
</script>
@endpush
