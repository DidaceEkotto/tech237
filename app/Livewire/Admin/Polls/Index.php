<?php

namespace App\Livewire\Admin\Polls;

use Livewire\Component;
use App\Models\TechPoll;
use App\Models\TechPollOption;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public ?int $pollId = null;
    public string $question = '';
    public string $description = '';
    public bool $is_active = true;
    public array $options = ['', ''];
    public bool $isCreating = false;

    protected function rules(): array
    {
        return [
            'question' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:150',
        ];
    }

    protected $messages = [
        'question.required' => 'La question du sondage est obligatoire.',
        'options.min' => 'Il faut au minimum 2 options pour un sondage.',
        'options.*.required' => 'Le libellé de chaque option est requis.',
    ];

    public function addOption()
    {
        if (count($this->options) < 6) {
            $this->options[] = '';
        }
    }

    public function removeOption(int $index)
    {
        if (count($this->options) > 2) {
            unset($this->options[$index]);
            $this->options = array_values($this->options);
        }
    }

    public function startCreate()
    {
        $this->reset(['pollId', 'question', 'description']);
        $this->options = ['', ''];
        $this->is_active = true;
        $this->isCreating = true;
    }

    public function cancel()
    {
        $this->isCreating = false;
        $this->reset(['pollId', 'question', 'description']);
    }

    public function save()
    {
        $this->validate();

        $admin = Auth::guard('admin')->user();

        // If this poll is set active, deactivate others
        if ($this->is_active) {
            TechPoll::where('is_active', true)->update(['is_active' => false]);
        }

        $poll = TechPoll::create([
            'question' => $this->question,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
            'admin_id' => $admin?->id,
        ]);

        foreach ($this->options as $index => $label) {
            if (trim($label)) {
                TechPollOption::create([
                    'tech_poll_id' => $poll->id,
                    'label' => trim($label),
                    'order' => $index,
                    'votes_count' => 0,
                ]);
            }
        }

        AdminLog::record('poll_created', "Création du sondage tech : '{$poll->question}'");
        session()->flash('success', "Le sondage '{$poll->question}' a été créé avec succès !");

        $this->isCreating = false;
        $this->reset(['pollId', 'question', 'description']);
        $this->options = ['', ''];
    }

    public function toggleActive(int $id)
    {
        $poll = TechPoll::findOrFail($id);
        $newStatus = !$poll->is_active;

        if ($newStatus) {
            TechPoll::where('id', '!=', $id)->update(['is_active' => false]);
        }

        $poll->update(['is_active' => $newStatus]);

        $statusLabel = $newStatus ? 'activé' : 'désactivé';
        AdminLog::record('poll_toggled', "Sondage {$poll->id} {$statusLabel}");
        session()->flash('success', "Le sondage a été {$statusLabel}.");
    }

    public function delete(int $id)
    {
        $poll = TechPoll::findOrFail($id);
        $q = $poll->question;
        $poll->delete();

        AdminLog::record('poll_deleted', "Suppression du sondage '{$q}'");
        session()->flash('success', "Le sondage a été supprimé.");
    }

    public function render()
    {
        $polls = TechPoll::with(['options', 'admin'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.polls.index', compact('polls'))
            ->layout('layouts.admin', ['title' => 'Sondages Tech Quotidiens (Polls)']);
    }
}
