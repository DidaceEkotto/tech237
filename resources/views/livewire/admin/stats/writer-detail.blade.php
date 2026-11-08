<div>
    <!-- Back button & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('admin.writers.overview') }}" class="btn btn-outline-secondary btn-sm mb-2 rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Retour à la supervision des rédacteurs
            </a>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-person-bounding-box text-danger me-2"></i>Détail Rédacteur : {{ $writer->name }}
            </h3>
            <p class="text-muted small mb-0">
                Analyse détaillée des performances, lectures uniques et calcul de la rémunération de {{ $writer->name }}.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark px-3 py-2 rounded-pill fs-6">
                <i class="bi bi-envelope me-1"></i> {{ $writer->email }}
            </span>
            <span class="badge bg-secondary px-3 py-2 rounded-pill fs-6 text-uppercase">
                {{ $writer->role }}
            </span>
        </div>
    </div>

    <!-- Alert in case of traffic decrease -->
    @if($hasDecrease)
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3 mb-4 rounded-3" role="alert">
            <div class="fs-2 text-danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1 text-danger">Alerte : Baisse de trafic de {{ abs($diffPercent) }}%</h6>
                <p class="small mb-0 text-dark">
                    Le nombre de lectures uniques pour ce rédacteur est en recul de <strong>{{ abs($diffPercent) }}%</strong> par rapport à la période précédente ({{ number_format($currentViewsCount, 0, ',', ' ') }} lectures contre {{ number_format($prevViewsCount, 0, ',', ' ') }} précédemment).
                </p>
            </div>
            <span class="badge bg-danger rounded-pill px-3 py-2">
                <i class="bi bi-arrow-down-right me-1"></i> -{{ abs($diffPercent) }}%
            </span>
        </div>
    @endif

    <!-- Filters & Rate Adjustment -->
    <div class="card card-melty mb-4 shadow-sm">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <!-- Period Toggle Buttons -->
                <div class="col-md-4">
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
                <div class="col-md-3">
                    @if($period === 'day')
                        <label class="form-label small fw-bold text-muted d-block mb-1">Date exacte (jQuery UI) :</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-calendar"></i></span>
                            <input type="text" id="writerDetailDatePicker" class="form-control" wire:model.live="selectedDate" placeholder="AAAA-MM-JJ" readonly style="cursor: pointer;">
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
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted d-block mb-1">Type de contenu :</label>
                    <select class="form-select form-select-sm" wire:model.live="contentType">
                        <option value="all">Tous</option>
                        <option value="articles">Articles</option>
                        <option value="game_reviews">Tests Jeux</option>
                        <option value="app_reviews">Tests Apps</option>
                    </select>
                </div>

                <!-- Tarification par Vue Unique Config -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted d-block mb-1">Tarif par vue unique :</label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="1" min="1" class="form-control fw-bold text-end" wire:model.live="ratePerView">
                        <span class="input-group-text bg-white fw-bold">FCFA / vue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-danger shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Lectures Uniques (Période)</span>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-black text-danger mb-0">{{ number_format($currentViewsCount, 0, ',', ' ') }}</h3>
                        @if($prevViewsCount > 0)
                            <span class="badge {{ $diffPercent >= 0 ? 'bg-success' : 'bg-danger' }} rounded-pill small">
                                <i class="bi {{ $diffPercent >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                {{ $diffPercent > 0 ? '+' : '' }}{{ $diffPercent }}%
                            </span>
                        @endif
                    </div>
                    <small class="text-muted mt-1 d-block">{{ $periodLabel }}</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-success shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Rémunération Calculée</span>
                    <h3 class="fw-black text-success mb-0">{{ number_format($calculatedPayout, 0, ',', ' ') }} <span class="fs-6 fw-normal">FCFA</span></h3>
                    <small class="text-muted mt-1 d-block">Basé sur {{ number_format($ratePerView, 0, ',', ' ') }} FCFA par vue unique (1 vue = 5 FCFA)</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-dark shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Historique</span>
                    <h3 class="fw-black text-dark mb-0">{{ number_format($totalAllTimeViews, 0, ',', ' ') }}</h3>
                    <small class="text-muted mt-1 d-block">Lectures cumulées tous contenus</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-melty h-100 border-start border-4 border-primary shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Publications</span>
                    <h3 class="fw-black text-primary mb-0">{{ $totalArticles + $totalGameReviews + $totalAppReviews }}</h3>
                    <small class="text-muted mt-1 d-block">{{ $totalArticles }} art. · {{ $totalGameReviews }} jeux · {{ $totalAppReviews }} apps</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart: Evolution of Writer's Views -->
    <div class="card card-melty mb-4 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-graph-up text-danger fs-5"></i>
                <h5 class="fw-bold mb-0">Courbe d'Évolution des Lectures de {{ $writer->name }}</h5>
            </div>
            <span class="badge bg-light text-dark border">{{ $periodLabel }}</span>
        </div>
        <div class="card-body p-3">
            <div style="height: 280px; width: 100%; position: relative;">
                <canvas id="writerDetailChart" style="width: 100%; height: 280px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Articles & Reviews Table for this Writer -->
    <div class="card card-melty shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-danger"></i>Contenus Rédigés & Rémunération par Article</h5>
                <small class="text-muted">Ventilation détaillée des lectures et gains générés sur la période sélectionnée.</small>
            </div>
            <span class="badge bg-dark rounded-pill">{{ count($contents) }} contenu(s)</span>
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
                        <th class="text-end">Rémunération calculée</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contents as $idx => $item)
                        @php
                            $itemEarnings = $item->period_views * $ratePerView;
                        @endphp
                        <tr>
                            <td class="text-muted fw-bold">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->title_fr }}</div>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 320px;">{{ $item->slug }}</small>
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
                            <td class="text-end fw-bold text-success fs-6">
                                {{ number_format($itemEarnings, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="text-center">
                                <a href="{{ $item->url }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-circle" title="Voir sur le site">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Aucun contenu trouvé pour cette période.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($contents) > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-uppercase">Total pour la période :</td>
                            <td class="text-center text-danger fs-5">{{ number_format($currentViewsCount, 0, ',', ' ') }}</td>
                            <td class="text-center text-muted">{{ number_format($totalAllTimeViews, 0, ',', ' ') }}</td>
                            <td class="text-end text-success fs-5">{{ number_format($calculatedPayout, 0, ',', ' ') }} FCFA</td>
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
    function renderWriterDetailChart() {
        if (!window.MeltyChart) return;
        const labels = @json($chartData['labels']);
        const data = @json($chartData['values']);
        MeltyChart.drawLineChart('writerDetailChart', labels, data, {
            strokeColor: '#00A859',
            fillColor: 'rgba(0, 168, 89, 0.20)'
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderWriterDetailChart();

        if ($.fn.datepicker) {
            $('#writerDetailDatePicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                onSelect: function(dateText) {
                    @this.set('selectedDate', dateText);
                }
            });
        }
    });

    document.addEventListener('livewire:navigated', renderWriterDetailChart);
    window.addEventListener('resize', renderWriterDetailChart);
</script>
@endpush
