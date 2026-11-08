<?php

namespace App\Livewire\Admin\Stats;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Article;
use App\Models\GameReview;
use App\Models\AppReview;
use App\Models\ContentView;
use Carbon\Carbon;

class WriterDetail extends Component
{
    public int $writerId;
    public string $period = 'month'; // 'day', 'week', 'month'
    public string $selectedDate = '';
    public int $selectedYear = 2026;
    public string $selectedMonth = '11';
    public int $ratePerThousand = 1500; // 1500 FCFA par 1000 vues uniques
    public string $contentType = 'all';

    public function mount(int $writerId)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || !$currentAdmin->isAdmin()) {
            abort(403, "Accès réservé aux administrateurs.");
        }

        $this->writerId = $writerId;
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

        $writer = Admin::findOrFail($this->writerId);

        [$startDate, $endDate, $prevStartDate, $prevEndDate, $periodLabel] = $this->calculateComparisonBounds();

        // Base query for this writer in period
        $viewsQuery = ContentView::where('admin_id', $writer->id)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($this->contentType !== 'all') {
            $viewType = match ($this->contentType) {
                'articles' => 'article',
                'game_reviews' => 'game_review',
                'app_reviews' => 'app_review',
                default => null,
            };
            if ($viewType) {
                $viewsQuery->where('viewable_type', $viewType);
            }
        }

        $currentViewsCount = (clone $viewsQuery)->count();
        $prevViewsCount = ContentView::where('admin_id', $writer->id)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();

        $totalAllTimeViews = ContentView::where('admin_id', $writer->id)->count();

        // Trend difference
        $diffPercent = 0;
        $hasDecrease = false;
        if ($prevViewsCount > 0) {
            $diffPercent = round((($currentViewsCount - $prevViewsCount) / $prevViewsCount) * 100, 1);
            if ($diffPercent < 0) {
                $hasDecrease = true;
            }
        } elseif ($currentViewsCount > 0) {
            $diffPercent = 100;
        }

        // Remuneration calculation
        $calculatedPayout = round(($currentViewsCount / 1000) * $this->ratePerThousand);

        // Fetch writer's contents
        $articles = Article::where('admin_id', $writer->id)
            ->latest('published_at')
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $item->period_views = ContentView::where('viewable_type', 'article')
                    ->where('viewable_id', $item->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                $item->type_label = 'Article';
                $item->type_badge = 'bg-primary';
                $item->url = route('article.show', $item->slug);
                return $item;
            });

        $gameReviews = GameReview::where('admin_id', $writer->id)
            ->latest()
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $item->period_views = ContentView::where('viewable_type', 'game_review')
                    ->where('viewable_id', $item->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                $item->title_fr = $item->game_name;
                $item->type_label = 'Test Jeu';
                $item->type_badge = 'bg-danger';
                $item->url = route('game-reviews.show', $item->slug);
                return $item;
            });

        $appReviews = AppReview::where('admin_id', $writer->id)
            ->latest()
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $item->period_views = ContentView::where('viewable_type', 'app_review')
                    ->where('viewable_id', $item->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                $item->title_fr = $item->app_name;
                $item->type_label = 'Test App';
                $item->type_badge = 'bg-info text-dark';
                $item->url = route('app-reviews.show', $item->slug);
                return $item;
            });

        $contents = collect();
        if ($this->contentType === 'all' || $this->contentType === 'articles') {
            $contents = $contents->concat($articles);
        }
        if ($this->contentType === 'all' || $this->contentType === 'game_reviews') {
            $contents = $contents->concat($gameReviews);
        }
        if ($this->contentType === 'all' || $this->contentType === 'app_reviews') {
            $contents = $contents->concat($appReviews);
        }

        $contents = $contents->sortByDesc('period_views')->values();

        // Chart data
        $chartData = $this->buildTimelineChart($writer->id, $startDate, $endDate);

        return view('livewire.admin.stats.writer-detail', [
            'writer' => $writer,
            'currentViewsCount' => $currentViewsCount,
            'prevViewsCount' => $prevViewsCount,
            'totalAllTimeViews' => $totalAllTimeViews,
            'diffPercent' => $diffPercent,
            'hasDecrease' => $hasDecrease,
            'calculatedPayout' => $calculatedPayout,
            'periodLabel' => $periodLabel,
            'contents' => $contents,
            'chartData' => $chartData,
            'totalArticles' => $articles->count(),
            'totalGameReviews' => $gameReviews->count(),
            'totalAppReviews' => $appReviews->count(),
        ])->layout('layouts.admin');
    }

    private function calculateComparisonBounds(): array
    {
        if ($this->period === 'day') {
            $d = !empty($this->selectedDate) ? Carbon::parse($this->selectedDate) : now();
            $currStart = $d->copy()->startOfDay();
            $currEnd = $d->copy()->endOfDay();
            $prevStart = $currStart->copy()->subDay();
            $prevEnd = $currEnd->copy()->subDay();
            $periodLabel = "Journée du " . $d->format('d/m/Y');
        } elseif ($this->period === 'week') {
            $currStart = now()->startOfWeek();
            $currEnd = now()->endOfWeek();
            $prevStart = now()->subWeek()->startOfWeek();
            $prevEnd = now()->subWeek()->endOfWeek();
            $periodLabel = "Cette Semaine (" . $currStart->format('d M') . " - " . $currEnd->format('d M Y') . ")";
        } else {
            // Month
            $m = (int) $this->selectedMonth;
            $y = (int) $this->selectedYear;
            $currStart = Carbon::createFromDate($y, $m, 1)->startOfMonth();
            $currEnd = $currStart->copy()->endOfMonth();
            $prevStart = $currStart->copy()->subMonth()->startOfMonth();
            $prevEnd = $prevStart->copy()->endOfMonth();
            $periodLabel = "Mois de " . $currStart->translatedFormat('F Y');
        }

        return [$currStart, $currEnd, $prevStart, $prevEnd, $periodLabel];
    }

    private function buildTimelineChart(int $adminId, Carbon $startDate, Carbon $endDate): array
    {
        $labels = [];
        $values = [];

        if ($this->period === 'day') {
            // 24 hours
            for ($h = 0; $h < 24; $h++) {
                $slotStart = $startDate->copy()->addHours($h);
                $slotEnd = $slotStart->copy()->addHour();
                $labels[] = sprintf('%02dh', $h);
                $values[] = ContentView::where('admin_id', $adminId)
                    ->whereBetween('created_at', [$slotStart, $slotEnd])
                    ->count();
            }
        } elseif ($this->period === 'week') {
            $days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            for ($d = 0; $d < 7; $d++) {
                $slotStart = $startDate->copy()->addDays($d)->startOfDay();
                $slotEnd = $slotStart->copy()->endOfDay();
                $labels[] = $days[$d] . ' ' . $slotStart->format('d/m');
                $values[] = ContentView::where('admin_id', $adminId)
                    ->whereBetween('created_at', [$slotStart, $slotEnd])
                    ->count();
            }
        } else {
            // Month by days
            $daysInMonth = $startDate->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $slotStart = Carbon::create($startDate->year, $startDate->month, $day, 0, 0, 0);
                $slotEnd = $slotStart->copy()->endOfDay();
                $labels[] = (string) $day;
                $values[] = ContentView::where('admin_id', $adminId)
                    ->whereBetween('created_at', [$slotStart, $slotEnd])
                    ->count();
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
