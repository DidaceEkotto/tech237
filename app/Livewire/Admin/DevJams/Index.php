<?php

namespace App\Livewire\Admin\DevJams;

use App\Models\AdminLog;
use App\Models\DevJam;
use App\Models\DevJamSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $modeFilter = 'all';
    public bool $showCreateForm = false;

    public string $title = '';
    public string $theme = '';
    public string $description = '';
    public string $rules = '';
    public string $starts_at = '';
    public string $ends_at = '';
    public string $prize_text = '';
    public bool $is_featured = true;
    public bool $allow_external_links = true;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->starts_at = now()->format('Y-m-d\TH:i');
        $this->ends_at = now()->addDays(7)->format('Y-m-d\TH:i');
    }

    public function createOfficialJam()
    {
        $this->validate([
            'title' => 'required|string|max:140',
            'theme' => 'nullable|string|max:140',
            'description' => 'required|string|max:2000',
            'rules' => 'nullable|string|max:2000',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'prize_text' => 'nullable|string|max:255',
        ]);

        $admin = Auth::guard('admin')->user();
        $baseSlug = Str::slug($this->title) ?: 'dev-jam';
        $slug = $baseSlug;
        $c = 1;
        while (DevJam::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $c++;
        }

        $starts = \Carbon\Carbon::parse($this->starts_at);
        $jam = DevJam::create([
            'slug' => $slug,
            'title' => $this->title,
            'theme' => $this->theme ?: null,
            'description' => $this->description,
            'rules' => $this->rules ?: null,
            'mode' => 'official',
            'status' => $starts->isFuture() ? 'upcoming' : 'active',
            'creator_admin_id' => $admin->id,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'prize_text' => $this->prize_text ?: null,
            'allow_external_links' => $this->allow_external_links,
            'is_featured' => $this->is_featured,
        ]);

        AdminLog::record('devjam_created', "Création de la Dev Jam Officielle '{$jam->title}'", $admin);
        $this->reset(['title', 'theme', 'description', 'rules', 'prize_text', 'showCreateForm']);
        session()->flash('success', "La Dev Jam Officielle '{$jam->title}' a été créée !");
    }

    public function setStatus(int $id, string $status)
    {
        if (!in_array($status, ['upcoming', 'active', 'voting', 'ended', 'rejected'], true)) {
            return;
        }
        $jam = DevJam::findOrFail($id);
        $jam->update(['status' => $status]);
        session()->flash('success', "Statut de '{$jam->title}' mis à jour ({$status}).");
    }

    public function toggleFeatured(int $id)
    {
        $jam = DevJam::findOrFail($id);
        $jam->update(['is_featured' => !$jam->is_featured]);
    }

    public function toggleWinner(int $submissionId)
    {
        $sub = DevJamSubmission::findOrFail($submissionId);
        $sub->update(['is_winner' => !$sub->is_winner]);
    }

    public function deleteJam(int $id)
    {
        $jam = DevJam::findOrFail($id);
        $title = $jam->title;
        $jam->delete();
        AdminLog::record('devjam_deleted', "Suppression de la Dev Jam '{$title}'");
        session()->flash('success', "Dev Jam '{$title}' supprimée.");
    }

    public function render()
    {
        $jams = DevJam::with(['creatorUser', 'creatorAdmin'])
            ->withCount(['submissions', 'messages'])
            ->when($this->modeFilter !== 'all', fn ($q) => $q->where('mode', $this->modeFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.dev-jams.index', compact('jams'))
            ->layout('layouts.admin', ['title' => 'Dev Jams Studio']);
    }
}
