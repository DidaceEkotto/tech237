<?php

namespace App\Livewire\Admin\Forms;

use App\Models\Form;
use App\Models\FormResponse;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Responses extends Component
{
    use WithPagination;

    public int $formId;
    public string $search = '';
    public ?int $selectedResponseId = null;

    protected $paginationTheme = 'bootstrap';

    public function mount(int $id): void
    {
        $this->formId = $id;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function selectResponse(int $respId): void
    {
        $this->selectedResponseId = $respId;
    }

    public function closeResponseModal(): void
    {
        $this->selectedResponseId = null;
    }

    public function exportCsv(): StreamedResponse
    {
        $form = Form::with(['fields' => function ($q) {
            $q->orderBy('order');
        }])->findOrFail($this->formId);

        $responses = FormResponse::where('form_id', $this->formId)
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reponses_form_' . $form->slug . '_' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($form, $responses) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // CSV Header
            $cols = ['#', 'Date de soumission', 'Email du Répondant', 'Durée (sec)', 'Adresse IP'];
            foreach ($form->fields as $f) {
                $cols[] = $f->label;
            }
            fputcsv($handle, $cols, ';');

            // Rows
            foreach ($responses as $idx => $r) {
                $answers = is_array($r->answers) ? $r->answers : [];
                $row = [
                    $idx + 1,
                    $r->created_at ? $r->created_at->format('Y-m-d H:i:s') : '',
                    $r->respondent_email ?: ($r->user->email ?? 'Anonyme'),
                    $r->completed_in_seconds ?: 0,
                    $r->ip_address ?: '—',
                ];

                foreach ($form->fields as $f) {
                    $ans = $answers[$f->id] ?? ($answers[$f->name] ?? '');
                    if (is_array($ans)) {
                        $ans = implode(', ', $ans);
                    }
                    $row[] = (string) $ans;
                }
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function render()
    {
        $form = Form::with([
            'user',
            'fields' => function ($q) {
                $q->orderBy('order');
            }
        ])->findOrFail($this->formId);

        $query = FormResponse::with('user')
            ->where('form_id', $this->formId);

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('respondent_email', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        $responses = $query->latest()->paginate(20);

        $selectedResponse = null;
        if ($this->selectedResponseId) {
            $selectedResponse = FormResponse::with('user')
                ->where('form_id', $this->formId)
                ->find($this->selectedResponseId);
        }

        return view('livewire.admin.forms.responses', [
            'form' => $form,
            'responses' => $responses,
            'selectedResponse' => $selectedResponse,
            'totalResponsesCount' => FormResponse::where('form_id', $this->formId)->count(),
        ])->layout('layouts.admin', ['title' => 'Réponses • ' . $form->title]);
    }
}
