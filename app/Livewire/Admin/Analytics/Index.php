<?php

namespace App\Livewire\Admin\Analytics;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VisitLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public ?string $selectedIp = null;
    public $selectedIpLogs = [];
    public ?int $selectedIpTotalTime = null;
    public string $timeRange = '14_days';

    public function selectIp(string $ip)
    {
        $this->selectedIp = $ip;
        $this->selectedIpLogs = VisitLog::where('ip_address', $ip)
            ->latest()
            ->take(50)
            ->get();
        $this->selectedIpTotalTime = (int) VisitLog::where('ip_address', $ip)->sum('duration_seconds');
    }

    public function clearSelectedIp()
    {
        $this->selectedIp = null;
        $this->selectedIpLogs = [];
        $this->selectedIpTotalTime = null;
    }

    public function render()
    {
        $now = Carbon::now();

        // 1. Daily comparison
        $todayCount = VisitLog::whereDate('created_at', Carbon::today())->count();
        $yesterdayCount = VisitLog::whereDate('created_at', Carbon::yesterday())->count();
        $dayDiffPercent = $yesterdayCount > 0 ? round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100, 1) : ($todayCount > 0 ? 100 : 0);

        // 2. Weekly comparison
        $thisWeekCount = VisitLog::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $lastWeekCount = VisitLog::whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])->count();
        $weekDiffPercent = $lastWeekCount > 0 ? round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1) : ($thisWeekCount > 0 ? 100 : 0);

        // 3. Monthly comparison
        $thisMonthCount = VisitLog::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count();
        $lastMonthCount = VisitLog::whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year)->count();
        $monthDiffPercent = $lastMonthCount > 0 ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) : ($thisMonthCount > 0 ? 100 : 0);

        // 4. Platform Duration Stats (Minimum & Average)
        $minDuration = (int) (VisitLog::min('duration_seconds') ?? 0);
        $avgDuration = (int) (VisitLog::avg('duration_seconds') ?? 0);

        // 5. Daily Trend (last 14 days)
        $dailyStats = [];
        $maxDaily = 1;
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $cnt = VisitLog::whereDate('created_at', $date)->count();
            if ($cnt > $maxDaily) $maxDaily = $cnt;
            $dailyStats[] = [
                'label' => $date->format('d/m'),
                'count' => $cnt,
            ];
        }
        foreach ($dailyStats as &$item) {
            $item['height'] = max(8, round(($item['count'] / $maxDaily) * 100));
        }

        // 6. Top Countries Ranking
        $totalVisits = VisitLog::count();
        $topCountries = VisitLog::select('country', 'country_code', DB::raw('count(*) as count'))
            ->groupBy('country', 'country_code')
            ->orderByDesc('count')
            ->take(7)
            ->get()
            ->map(function ($item) use ($totalVisits) {
                $item->percent = $totalVisits > 0 ? round(($item->count / $totalVisits) * 100, 1) : 0;
                return $item;
            });

        // 7. Recent Distinct Visitors (by IP)
        $visitorIps = VisitLog::select('ip_address', 'country', 'city', DB::raw('count(*) as total_views'), DB::raw('sum(duration_seconds) as total_time'), DB::raw('max(created_at) as last_seen'))
            ->groupBy('ip_address', 'country', 'city')
            ->orderByDesc('last_seen')
            ->paginate(15);

        return view('livewire.admin.analytics.index', compact(
            'todayCount',
            'yesterdayCount',
            'dayDiffPercent',
            'thisWeekCount',
            'lastWeekCount',
            'weekDiffPercent',
            'thisMonthCount',
            'lastMonthCount',
            'monthDiffPercent',
            'minDuration',
            'avgDuration',
            'dailyStats',
            'topCountries',
            'visitorIps',
            'totalVisits'
        ))->layout('layouts.admin', ['title' => 'Statistiques & Analytics Visiteurs']);
    }
}
