<?php

namespace App\Livewire\Admin\Interviews;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Interview;
use App\Models\AdminLog;
use App\Services\ImageService;

class Form extends Component
{
    use WithFileUploads;

    public ?int $interviewId = null;
    public string $interviewee_name = '';
    public string $interviewee_role = '';
    public string $title_fr = '';
    public string $title_en = '';
    public string $slug = '';
    public string $video_url = '';
    public $cover_image = null;
    public ?string $existing_image = null;
    public string $summary_fr = '';
    public string $summary_en = '';
    public string $content_fr = '';
    public string $content_en = '';
    public string $status = 'published';

    // Dynamic Q&A Blocks Repeater
    public array $qa_blocks = [
        [
            'question_fr' => '',
            'answer_fr' => '',
            'question_en' => '',
            'answer_en' => '',
        ]
    ];

    protected function rules(): array
    {
        return [
            'interviewee_name' => 'required|string|max:255',
            'interviewee_role' => 'nullable|string|max:255',
            'title_fr' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'required|string|unique:interviews,slug,' . $this->interviewId,
            'video_url' => 'nullable|url',
            'content_fr' => 'nullable|string',
            'content_en' => 'nullable|string',
            'cover_image' => 'nullable|image|max:10240',
            'qa_blocks' => 'nullable|array',
        ];
    }

    public function mount(?int $id = null)
    {
        if ($id) {
            $i = Interview::findOrFail($id);
            $this->interviewId = $i->id;
            $this->interviewee_name = $i->interviewee_name;
            $this->interviewee_role = $i->interviewee_role ?? '';
            $this->title_fr = $i->title_fr;
            $this->title_en = $i->title_en ?? '';
            $this->slug = $i->slug;
            $this->video_url = $i->video_url ?? '';
            $this->existing_image = $i->cover_image;
            $this->summary_fr = $i->summary_fr ?? '';
            $this->summary_en = $i->summary_en ?? '';
            $this->content_fr = $i->content_fr ?? '';
            $this->content_en = $i->content_en ?? '';
            $this->status = $i->status;
            if (!empty($i->qa_blocks) && is_array($i->qa_blocks)) {
                $this->qa_blocks = $i->qa_blocks;
            }
        }
    }

    public function addQaBlock()
    {
        $this->qa_blocks[] = [
            'question_fr' => '',
            'answer_fr' => '',
            'question_en' => '',
            'answer_en' => '',
        ];
    }

    public function removeQaBlock(int $index)
    {
        if (count($this->qa_blocks) > 1) {
            unset($this->qa_blocks[$index]);
            $this->qa_blocks = array_values($this->qa_blocks);
        }
    }

    public function updatedTitleFr($val)
    {
        if (!$this->interviewId) {
            $this->slug = Str::slug($val);
        }
    }

    public function save()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->title_fr);
        }
        $this->validate();

        $imgPath = $this->existing_image;
        if ($this->cover_image) {
            $imgPath = ImageService::saveImage($this->cover_image, 'interview');
        }

        // Build fallback content_fr / content_en from qa_blocks if not provided
        $frHtml = $this->content_fr;
        if (empty($frHtml) && !empty($this->qa_blocks)) {
            $frHtml = '';
            foreach ($this->qa_blocks as $b) {
                if (!empty($b['question_fr'])) {
                    $frHtml .= "<h3>" . e($b['question_fr']) . "</h3>";
                }
                if (!empty($b['answer_fr'])) {
                    $frHtml .= "<div>" . $b['answer_fr'] . "</div>";
                }
            }
        }

        $enHtml = $this->content_en;
        if (empty($enHtml) && !empty($this->qa_blocks)) {
            $enHtml = '';
            foreach ($this->qa_blocks as $b) {
                if (!empty($b['question_en'])) {
                    $enHtml .= "<h3>" . e($b['question_en']) . "</h3>";
                }
                if (!empty($b['answer_en'])) {
                    $enHtml .= "<div>" . $b['answer_en'] . "</div>";
                }
            }
        }

        $data = [
            'interviewee_name' => $this->interviewee_name,
            'interviewee_role' => $this->interviewee_role,
            'title_fr' => $this->title_fr,
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'video_url' => $this->video_url ?: null,
            'cover_image' => $imgPath,
            'summary_fr' => $this->summary_fr,
            'summary_en' => $this->summary_en,
            'content_fr' => $frHtml ?: 'Interview retranscrite ci-dessous.',
            'content_en' => $enHtml ?: null,
            'qa_blocks' => $this->qa_blocks,
            'status' => $this->status,
        ];

        if ($this->interviewId) {
            $i = Interview::findOrFail($this->interviewId);
            $i->update($data);
            AdminLog::record('interview_updated', "Mise à jour de l'interview avec '{$i->interviewee_name}'");
            session()->flash('success', "L'interview a été mise à jour.");
        } else {
            $data['admin_id'] = Auth::guard('admin')->id();
            $i = Interview::create($data);
            AdminLog::record('interview_created', "Création de l'interview avec '{$i->interviewee_name}'");
            session()->flash('success', "L'interview avec '{$i->interviewee_name}' a été publiée.");
        }

        return redirect()->route('admin.interviews.index');
    }

    public function render()
    {
        return view('livewire.admin.interviews.form')
            ->layout('layouts.admin', ['title' => $this->interviewId ? 'Modifier l\'Interview' : 'Rédiger une Interview']);
    }
}
