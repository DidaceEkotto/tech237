<?php

namespace App\Livewire\Admin\Forms;

use App\Models\Form;
use Livewire\Component;

class Show extends Component
{
    public int $formId;

    public function mount(int $id): void
    {
        $this->formId = $id;
    }

    public function render()
    {
        $form = Form::with([
            'user',
            'fields' => function ($q) {
                $q->orderBy('order');
            }
        ])->withCount(['fields', 'responses'])->findOrFail($this->formId);

        return view('livewire.admin.forms.show', [
            'form' => $form,
        ])->layout('layouts.admin', ['title' => 'Formulaire : ' . $form->title . ' (Lecture Admin)']);
    }
}
