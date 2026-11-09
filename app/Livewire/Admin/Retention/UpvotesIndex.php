<?php

namespace App\Livewire\Admin\Retention;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\ProjectUpvote;
use App\Models\AdminLog;

class UpvotesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetProjectUpvotes(int $projectId)
    {
        $project = Project::findOrFail($projectId);
        ProjectUpvote::where('project_id', $project->id)->delete();
        $project->update(['upvotes_count' => 0]);

        AdminLog::record('project_upvotes_reset', "Réinitialisation des votes du projet '{$project->name}'");
        session()->flash('success', "Les votes pour le projet '{$project->name}' ont été réinitialisés à 0.");
    }

    public function render()
    {
        $projects = Project::with(['user', 'startup'])
            ->where('status', 'published')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            })
            ->orderBy('upvotes_count', 'desc')
            ->paginate(15);

        $totalUpvotes = (int) ProjectUpvote::count();
        $topProject = Project::where('status', 'published')->orderBy('upvotes_count', 'desc')->first();

        return view('livewire.admin.retention.upvotes-index', compact('projects', 'totalUpvotes', 'topProject'))
            ->layout('layouts.admin', ['title' => 'Product Hunt Tech237 - Upvotes des Projets']);
    }
}
