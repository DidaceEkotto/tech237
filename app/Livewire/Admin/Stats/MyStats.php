<?php

namespace App\Livewire\Admin\Stats;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Article;
use App\Models\GameReview;
use App\Models\AppReview;
use App\Models\ContentView;
use Carbon\Carbon;

class MyStats extends Component
{
    public string $period = 'month'; // 'day', 'week', 'month'
    public string $selectedDate = '';
    public int $selectedYear = 2026;
    public string $selectedMonth = '11';
    public string $contentType = 'all'; // 'all', 'articles', 'game_reviews', 'app_reviews'

    public function mount()
    {
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
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            abort(403);
        }

        // Determine date bounds
        [$startDate, $endDate, $periodLabel] = $this->calculateBounds();

        // Base query for unique views of this admin in this period
        $viewsQuery = ContentView::where('admin_id', $admin->id)
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

        $periodViewsCount = (clone $viewsQuery)->count();
        $totalAllTimeViews = ContentView::where('admin_id', $admin->id)->count();

        // Total contents published by this admin
        $totalArticles = Article::where('admin_id', $admin->id)->count();
        $totalGameReviews = GameReview::where('admin_id', $admin->id)->count();
        $totalAppReviews = AppReview::where('admin_id', $admin->id)->count();
        $totalContents = $totalArticles + $totalGameReviews + $totalAppReviews;

        // Fetch articles & reviews with their period views
        $articles = Article::where('admin_id', $admin->id)
            ->latest('published_at')
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $item->period_views = ContentView::where('viewable_type', 'article')
                    ->where('viewable_id', $item->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                $item->type_label = 'Article';
                $item->type_badge = 'bg-primary';
                return $item;
            });

        $gameReviews = GameReview::where('admin_id', $admin->id)
            ->latest()
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $item->period_views = ContentView::where('viewable_type', 'game_review')
                    ->where('viewable_id', $item->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                $item->title_fr = $item->game_name;
                $item->type_label = 'Test Jeu Vidéo';
                $item->type_badge = 'bg-danger';
                return $item;
            });

        $appReviews = AppReview::where('admin_id', $admin->id)
            ->latest()
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $item->period_views = ContentView::where('viewable_type', 'app_review')
                    ->where('viewable_id', $item->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                $item->title_fr = $item->app_name;
                $item->type_label = 'Test Application';
                $item->type_badge = 'bg-success';
                return $item;
            });

        $allContents = collect();
        if (in_array($this->contentType, ['all', 'articles'])) {
            $allContents = $allContents->concat($articles);
        }
        if (in_array($this->contentType, ['all', 'game_reviews'])) {
            $allContents = $allContents->concat($gameReviews);
        }
        if (in_array($this->contentType, ['all', 'app_reviews'])) {
            $allContents = $allContents->concat($appReviews);
        }

        $allContents = $allContents->sortByDesc('period_views')->values();

        // Prepare chart points (timeline)
        $chartData = $this->buildChartTimeline($admin->id, $startDate, $endDate);

        // Remuneration simulation (1 vue = 5 FCFA)
        $estimatedEarnings = $periodViewsCount * 5; // 5 FCFA par vue unique

        return view('livewire.admin.stats.my-stats', [
            'admin' => $admin,
            'periodLabel' => $periodLabel,
            'periodViewsCount' => $periodViewsCount,
            'totalAllTimeViews' => $totalAllTimeViews,
            'totalArticles' => $totalArticles,
            'totalGameReviews' => $totalGameReviews,
            'totalAppReviews' => $totalAppReviews,
            'totalContents' => $totalContents,
            'contents' => $allContents,
            'chartLabels' => $chartData['labels'],
            'chartValues' => $chartData['values'],
            'estimatedEarnings' => $estimatedEarnings,
        ])->layout('layouts.admin', ['title' => 'Mes Statistiques de Lectorat & Rémunération']);
    }

    protected function calculateBounds(): array
    {
        if ($this->period === 'day') {
            $d = Carbon::parse($this->selectedDate ?: now()->toDateString());
            return [
                $d->copy()->startOfDay(),
                $d->copy()->endOfDay(),
                'Journée du ' . $d->translatedFormat('d F Y'),
            ];
        }

        if ($this->period === 'week') {
            $now = now();
            return [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
                'Semaine en cours (du ' . $now->startOfWeek()->translatedFormat('d M') . ' au ' . $now->endOfWeek()->translatedFormat('d M Y') . ')',
            ];
        }

        // Default: Month
        $y = (int) ($this->selectedYear ?: now()->year);
        $m = (int) ($this->selectedMonth ?: now()->month);
        $start = Carbon::createFromDate($y, $m, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return [
            $start,
            $end,
            'Mois de ' . $start->translatedFormat('F Y'),
        ];
    }

    protected function buildChartTimeline(int $adminId, Carbon $start, Carbon $end): array
    {
        $labels = [];
        $values = [];

        $diffDays = $start->diffInDays($end);

        if ($diffDays <= 1) {
            // Hours of day
            for ($h = 0; $h < 24; $h += 2) {
                $labels[] = "{$h}h";
                $hStart = $start->copy()->setHour($h)->startOfHour();
                $hEnd = $start->copy()->setHour($h + 1)->endOfHour();
                $values[] = ContentView::where('admin_id', $adminId)
                    ->whereBetween('created_at', [$hStart, $hEnd])
                    ->count();
            }
        } elseif ($diffDays <= 7) {
            // Days of week
            $curr = $start->copy();
            while ($curr <= $end) {
                $labels[] = $curr->translatedFormat('D d');
                $values[] = ContentView::where('admin_id', $adminId)
                    ->whereDate('created_at', $curr->toDateString())
                    ->count();
                $curr->addDay();
            }
        } else {
            // Days of month in intervals
            $curr = $start->copy();
            while ($curr <= $end) {
                $labels[] = $curr->format('d/m');
                $values[] = ContentView::where('admin_id', $adminId)
                    ->whereDate('created_at', $curr->toDateString())
                    ->count();
                $curr->addDays(2);
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
