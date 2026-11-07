<?php

namespace App\Livewire\Admin\Interviews;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Interview;
use App\Models\AdminLog;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    protected $paginationTheme = 'bootstrap';

    public function delete(int $id)
    {
        $i = Interview::findOrFail($id);
        $title = $i->title_fr;
        $i->delete();

        AdminLog::record('interview_deleted', "Suppression de l'interview '{$title}'");
        session()->flash('success', "L'interview '{$title}' a été supprimée.");
    }

    public function render()
    {
        $interviews = Interview::with('author')
            ->when($this->search, function ($q) {
                $q->where('interviewee_name', 'like', "%{$this->search}%")
                    ->orWhere('title_fr', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.interviews.index', compact('interviews'))
            ->layout('layouts.admin', ['title' => 'Gestion des Interviews']);
    }
}
