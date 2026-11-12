<?php

namespace App\Livewire\Admin\Freelance;

use App\Models\FreelanceQuote;
use Livewire\Component;

class Show extends Component
{
    public int $quoteId;

    public function mount(int $id): void
    {
        $this->quoteId = $id;
    }

    public function render()
    {
        $quote = FreelanceQuote::with('user')->findOrFail($this->quoteId);

        return view('livewire.admin.freelance.show', [
            'quote' => $quote,
        ])->layout('layouts.admin', ['title' => 'Document ' . $quote->quote_number . ' (Lecture Admin)']);
    }
}
