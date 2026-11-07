<?php

namespace App\Livewire\Admin\Sliders;

use App\Services\ImageService;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Slider;
use App\Models\AdminLog;

class Index extends Component
{
    use WithFileUploads;

    public ?int $sliderId = null;
    public string $title_fr = '';
    public string $title_en = '';
    public string $subtitle_fr = '';
    public string $subtitle_en = '';
    public string $link_url = '';
    public string $button_text_fr = 'Découvrir';
    public string $button_text_en = 'Discover';
    public $image = null;
    public ?string $existing_image = null;
    public int $order = 0;
    public bool $is_active = true;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'title_fr' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'link_url' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
        ];
    }

    public function edit(int $id)
    {
        $s = Slider::findOrFail($id);
        $this->sliderId = $s->id;
        $this->title_fr = $s->title_fr;
        $this->title_en = $s->title_en ?? '';
        $this->subtitle_fr = $s->subtitle_fr ?? '';
        $this->subtitle_en = $s->subtitle_en ?? '';
        $this->link_url = $s->link_url ?? '';
        $this->button_text_fr = $s->button_text_fr ?? 'Découvrir';
        $this->button_text_en = $s->button_text_en ?? 'Discover';
        $this->existing_image = $s->image;
        $this->order = $s->order;
        $this->is_active = $s->is_active;
        $this->isEditing = true;
    }

    public function resetFields()
    {
        $this->reset(['sliderId', 'title_fr', 'title_en', 'subtitle_fr', 'subtitle_en', 'link_url', 'image', 'existing_image', 'isEditing']);
        $this->button_text_fr = 'Découvrir';
        $this->button_text_en = 'Discover';
        $this->order = 0;
        $this->is_active = true;
    }

    public function save()
    {
        $this->validate();

        $imgPath = $this->existing_image;
        if ($this->image) {
            $imgPath = ImageService::saveImage($this->image, 'slider');
        }

        if (!$imgPath && !$this->sliderId) {
            session()->flash('error', 'Une image est obligatoire pour le slider.');
            return;
        }

        $data = [
            'title_fr' => $this->title_fr,
            'title_en' => $this->title_en,
            'subtitle_fr' => $this->subtitle_fr,
            'subtitle_en' => $this->subtitle_en,
            'link_url' => $this->link_url ?: null,
            'button_text_fr' => $this->button_text_fr,
            'button_text_en' => $this->button_text_en,
            'image' => $imgPath,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];

        if ($this->sliderId) {
            $s = Slider::findOrFail($this->sliderId);
            $s->update($data);
            AdminLog::record('slider_updated', "Mise à jour du slider '{$s->title_fr}'");
            session()->flash('success', "Le slide a été mis à jour.");
        } else {
            $s = Slider::create($data);
            AdminLog::record('slider_created', "Création d'un nouveau slider '{$s->title_fr}'");
            session()->flash('success', "Le slide a été ajouté.");
        }

        $this->resetFields();
    }

    public function delete(int $id)
    {
        $s = Slider::findOrFail($id);
        $title = $s->title_fr;
        $s->delete();
        AdminLog::record('slider_deleted', "Suppression du slider '{$title}'");
        session()->flash('success', "Slider supprimé.");
    }

    public function render()
    {
        $sliders = Slider::orderBy('order')->get();
        return view('livewire.admin.sliders.index', compact('sliders'))
            ->layout('layouts.admin', ['title' => 'Sliders de la Page d\'Accueil']);
    }
}
