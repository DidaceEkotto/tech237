<?php

namespace App\Livewire\Admin\Banners;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\AdBanner;
use App\Models\AdminLog;
use App\Services\ImageService;

class Index extends Component
{
    use WithFileUploads;

    public ?int $bannerId = null;
    public string $title = '';
    public string $position = 'header_top';
    public string $link_url = '';
    public string $custom_code = '';
    public $image = null;
    public ?string $existing_image = null;
    public bool $is_active = true;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'position' => 'required|string',
            'link_url' => 'nullable|string',
            'custom_code' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
        ];
    }

    public function edit(int $id)
    {
        $b = AdBanner::findOrFail($id);
        $this->bannerId = $b->id;
        $this->title = $b->title;
        $this->position = $b->position;
        $this->link_url = $b->link_url ?? '';
        $this->custom_code = $b->custom_code ?? '';
        $this->existing_image = $b->image;
        $this->is_active = $b->is_active;
        $this->isEditing = true;
    }

    public function resetFields()
    {
        $this->reset(['bannerId', 'title', 'position', 'link_url', 'custom_code', 'image', 'existing_image', 'isEditing']);
        $this->position = 'header_top';
        $this->is_active = true;
    }

    public function save()
    {
        $this->validate();

        $imgPath = $this->existing_image;
        if ($this->image) {
            $imgPath = ImageService::saveImage($this->image, 'ad');
        }

        $data = [
            'title' => $this->title,
            'position' => $this->position,
            'link_url' => $this->link_url ?: null,
            'custom_code' => $this->custom_code ?: null,
            'image' => $imgPath,
            'is_active' => $this->is_active,
        ];

        if ($this->bannerId) {
            $b = AdBanner::findOrFail($this->bannerId);
            $b->update($data);
            AdminLog::record('ad_banner_updated', "Mise à jour de la bannière pub '{$b->title}'");
            session()->flash('success', "La bannière publicitaire a été mise à jour.");
        } else {
            $b = AdBanner::create($data);
            AdminLog::record('ad_banner_created', "Création d'une nouvelle bannière pub '{$b->title}'");
            session()->flash('success', "La bannière publicitaire a été créée.");
        }

        $this->resetFields();
    }

    public function delete(int $id)
    {
        $b = AdBanner::findOrFail($id);
        $title = $b->title;
        $b->delete();
        AdminLog::record('ad_banner_deleted', "Suppression de la bannière pub '{$title}'");
        session()->flash('success', "Bannière publicitaire supprimée.");
    }

    public function render()
    {
        $banners = AdBanner::latest()->get();
        return view('livewire.admin.banners.index', compact('banners'))
            ->layout('layouts.admin', ['title' => 'Gestion des Bannières Publicitaires']);
    }
}
