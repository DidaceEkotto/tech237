<?php

namespace App\Livewire\Admin\Freelance;

use App\Models\FreelanceQuote;
use App\Models\FreelanceSetting;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'quotes'; // 'quotes' or 'settings'
    public string $search = '';
    public string $typeFilter = '';
    public string $statusFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        // Tab 1: Quotes & Invoices Query
        $quotesQuery = FreelanceQuote::with('user');

        if (!empty($this->search)) {
            $s = trim($this->search);
            $quotesQuery->where(function ($q) use ($s) {
                $q->where('quote_number', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('client_name', 'like', "%{$s}%")
                  ->orWhere('client_company', 'like', "%{$s}%")
                  ->orWhere('client_email', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        if (!empty($this->typeFilter)) {
            $quotesQuery->where('type', $this->typeFilter);
        }

        if (!empty($this->statusFilter)) {
            $quotesQuery->where('status', $this->statusFilter);
        }

        $quotes = $quotesQuery->latest()->paginate(15);

        // Tab 2: Freelance Settings Query
        $settingsList = FreelanceSetting::with('user')->latest()->get();

        // Summary statistics
        $totalQuotes = FreelanceQuote::count();
        $totalAmountSum = FreelanceQuote::sum('total_amount');
        $acceptedCount = FreelanceQuote::where('status', 'accepted')->count();
        $paidCount = FreelanceQuote::where('status', 'paid')->count();
        $freelancersCount = FreelanceSetting::count();

        return view('livewire.admin.freelance.index', [
            'quotes' => $quotes,
            'settingsList' => $settingsList,
            'totalQuotes' => $totalQuotes,
            'totalAmountSum' => $totalAmountSum,
            'acceptedCount' => $acceptedCount,
            'paidCount' => $paidCount,
            'freelancersCount' => $freelancersCount,
        ])->layout('layouts.admin', ['title' => 'Gestion Freelance & Devis']);
    }
}
