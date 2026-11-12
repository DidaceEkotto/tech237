<?php

namespace App\Livewire\Admin\Forms;

use App\Models\Form;
use App\Models\FormResponse;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $modeFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingModeFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Form::with(['user', 'fields'])
            ->withCount(['fields', 'responses']);

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('slug', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->modeFilter)) {
            $query->where('display_mode', $this->modeFilter);
        }

        $forms = $query->latest()->paginate(15);

        // Stats summary
        $totalForms = Form::count();
        $publishedForms = Form::where('status', 'published')->count();
        $totalResponses = FormResponse::count();
        $totalViews = Form::sum('views_count');

        return view('livewire.admin.forms.index', [
            'forms' => $forms,
            'totalForms' => $totalForms,
            'publishedForms' => $publishedForms,
            'totalResponses' => $totalResponses,
            'totalViews' => $totalViews,
        ])->layout('layouts.admin', ['title' => 'Gestion des Formulaires & Questionnaires']);
    }
}
