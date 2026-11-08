<?php

namespace App\Livewire\Admin\Stats;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\ContentView;
use Carbon\Carbon;

class WritersOverview extends Component
{
    public string $period = 'week'; // 'day', 'week', 'month'
    public string $selectedDate = '';
    public int $selectedYear = 2026;
    public string $selectedMonth = '11';

    public function mount()
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || !$currentAdmin->isAdmin()) {
            abort(403, "Accès réservé aux administrateurs et super-administrateurs.");
        }

        $this->selectedDate = now()->toDateString();
        $this->selectedYear = (int) now()->year;
        $this->selectedMonth = now()->format('m');
    }

    public function setPeriod(string $p)
    {
        $this->period = $p;
    }

    public function render()
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || !$currentAdmin->isAdmin()) {
            abort(403);
        }

        [$currStart, $currEnd, $prevStart, $prevEnd, $periodLabel] = $this->calculateComparisonBounds();

        // Global stats
        $currentGlobalViews = ContentView::whereBetween('created_at', [$currStart, $currEnd])->count();
        $previousGlobalViews = ContentView::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $totalAllTimeViews = ContentView::count();

        // Difference percentage and alert
        $diffPercent = 0;
        $hasDecrease = false;
        if ($previousGlobalViews > 0) {
            $diffPercent = round((($currentGlobalViews - $previousGlobalViews) / $previousGlobalViews) * 100, 1);
            if ($diffPercent < 0) {
                $hasDecrease = true;
            }
        } elseif ($currentGlobalViews > 0) {
            $diffPercent = 100;
        }

        // Writers list: admins with role 'editor' or who published articles/reviews
        $writers = Admin::where('role', 'editor')
            ->orWhereHas('articles')
            ->orWhereHas('gameReviews')
            ->orWhereHas('appReviews')
            ->withCount(['articles', 'gameReviews', 'appReviews'])
            ->get()
            ->map(function ($w) use ($currStart, $currEnd, $prevStart, $prevEnd) {
                $w->current_period_views = ContentView::where('admin_id', $w->id)
                    ->whereBetween('created_at', [$currStart, $currEnd])
                    ->count();

                $w->prev_period_views = ContentView::where('admin_id', $w->id)
                    ->whereBetween('created_at', [$prevStart, $prevEnd])
                    ->count();

                $w->total_unique_views = ContentView::where('admin_id', $w->id)->count();

                // Trend diff for this specific writer
                $w->diff_percent = 0;
                if ($w->prev_period_views > 0) {
                    $w->diff_percent = round((($w->current_period_views - $w->prev_period_views) / $w->prev_period_views) * 100, 1);
                } elseif ($w->current_period_views > 0) {
                    $w->diff_percent = 100;
                }

                // Estimated payout (1 vue = 5 FCFA)
                $w->estimated_payout = $w->current_period_views * 5;

                return $w;
            })
            ->sortByDesc('current_period_views')
            ->values();

        $totalEstimatedPayout = $writers->sum('estimated_payout');

        // Timeline for Chart
        $chartData = $this->buildComparisonChart($currStart, $currEnd);

        return view('livewire.admin.stats.writers-overview', [
            'periodLabel' => $periodLabel,
            'currentGlobalViews' => $currentGlobalViews,
            'previousGlobalViews' => $previousGlobalViews,
            'diffPercent' => $diffPercent,
            'hasDecrease' => $hasDecrease,
            'totalAllTimeViews' => $totalAllTimeViews,
            'writers' => $writers,
            'totalEstimatedPayout' => $totalEstimatedPayout,
            'chartLabels' => $chartData['labels'],
            'chartCurrent' => $chartData['current'],
        ])->layout('layouts.admin', ['title' => 'Contrôle & Rémunération des Rédacteurs']);
    }

    protected function calculateComparisonBounds(): array
    {
        if ($this->period === 'day') {
            $curr = Carbon::parse($this->selectedDate ?: now()->toDateString());
            $prev = $curr->copy()->subDay();
            return [
                $curr->copy()->startOfDay(),
                $curr->copy()->endOfDay(),
                $prev->copy()->startOfDay(),
                $prev->copy()->endOfDay(),
                'Journée du ' . $curr->translatedFormat('d F Y'),
            ];
        }

        if ($this->period === 'month') {
            $y = (int) ($this->selectedYear ?: now()->year);
            $m = (int) ($this->selectedMonth ?: now()->month);
            $curr = Carbon::createFromDate($y, $m, 1);
            $prev = $curr->copy()->subMonth();
            return [
                $curr->copy()->startOfMonth(),
                $curr->copy()->endOfMonth(),
                $prev->copy()->startOfMonth(),
                $prev->copy()->endOfMonth(),
                'Mois de ' . $curr->translatedFormat('F Y'),
            ];
        }

        // Default: Week
        $now = now();
        $currStart = $now->copy()->startOfWeek();
        $currEnd = $now->copy()->endOfWeek();
        $prevStart = $now->copy()->subWeek()->startOfWeek();
        $prevEnd = $now->copy()->subWeek()->endOfWeek();

        return [
            $currStart,
            $currEnd,
            $prevStart,
            $prevEnd,
            'Semaine en cours (' . $currStart->translatedFormat('d M') . ' au ' . $currEnd->translatedFormat('d M Y') . ')',
        ];
    }

    protected function buildComparisonChart(Carbon $currStart, Carbon $currEnd): array
    {
        $labels = [];
        $current = [];

        $diffDays = $currStart->diffInDays($currEnd);

        if ($diffDays <= 1) {
            for ($h = 0; $h < 24; $h += 2) {
                $labels[] = "{$h}h";
                $hStart = $currStart->copy()->setHour($h)->startOfHour();
                $hEnd = $currStart->copy()->setHour($h + 1)->endOfHour();
                $current[] = ContentView::whereBetween('created_at', [$hStart, $hEnd])->count();
            }
        } elseif ($diffDays <= 7) {
            $c = $currStart->copy();
            while ($c <= $currEnd) {
                $labels[] = $c->translatedFormat('D d');
                $current[] = ContentView::whereDate('created_at', $c->toDateString())->count();
                $c->addDay();
            }
        } else {
            $c = $currStart->copy();
            while ($c <= $currEnd) {
                $labels[] = $c->format('d/m');
                $current[] = ContentView::whereDate('created_at', $c->toDateString())->count();
                $c->addDays(2);
            }
        }

        return [
            'labels' => $labels,
            'current' => $current,
        ];
    }
}
