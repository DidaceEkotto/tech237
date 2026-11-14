<?php

namespace App\Livewire\Admin\PortfolioSites;

use App\Models\PortfolioSite;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = PortfolioSite::with(['user:id,name,username,email'])
            ->withCount('pages');

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('slug', 'like', "%{$s}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"));
            });
        }

        if ($this->filterStatus === 'published') {
            $query->where('is_published', true);
        } elseif ($this->filterStatus === 'draft') {
            $query->where('is_published', false);
        }

        $sites = $query->orderByDesc('views_count')->paginate(20);

        $summary = [
            'total_sites' => PortfolioSite::count(),
            'published_sites' => PortfolioSite::where('is_published', true)->count(),
            'total_views' => (int) PortfolioSite::sum('views_count'),
            'total_uniques' => (int) PortfolioSite::sum('unique_visitors_count'),
        ];

        return view('livewire.admin.portfolio-sites.index', [
            'sites' => $sites,
            'summary' => $summary,
        ]);
    }
}
