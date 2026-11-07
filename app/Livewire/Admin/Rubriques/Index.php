<?php

namespace App\Livewire\Admin\Rubriques;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Rubrique;
use App\Models\AdminLog;
use App\Services\ImageService;

class Index extends Component
{
    use WithFileUploads;

    public ?int $rubriqueId = null;
    public string $name_fr = '';
    public string $name_en = '';
    public string $slug = '';
    public string $icon = 'bi-collection-play';
    public $banner = null;
    public ?string $banner_image = null;
    public int $order = 0;
    public bool $is_active = true;
    public bool $isEditing = false;

    // Rich Customization Options for Rubriques front-end identity
    public array $options = [
        'layout' => 'grid_standard',          // grid_standard, grid_large, grid_masonry, grid_list
        'banner_style' => 'hero_gradient',    // hero_gradient, minimal, boxed_badge
        'card_style' => 'rounded_shadow',     // rounded_shadow, flat_bordered, dark_mode, zoom_hover
        'accent_color' => '#FF004B',          // Preset HEX
        'show_audio_player' => true,
        'show_dossiers_carousel' => true,
        'show_filter_pills' => true,
        'show_trending_sidebar' => true,
        'badge_label' => '',
    ];

    protected function rules(): array
    {
        return [
            'name_fr' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'slug' => 'required|string|unique:rubriques,slug,' . $this->rubriqueId,
            'icon' => 'nullable|string|max:50',
            'banner' => 'nullable|image|max:10240',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
        ];
    }

    public function updatedNameFr($value)
    {
        if (!$this->rubriqueId) {
            $this->slug = Str::slug($value);
        }
    }

    public function resetFields()
    {
        $this->reset(['rubriqueId', 'name_fr', 'name_en', 'slug', 'isEditing', 'banner', 'banner_image']);
        $this->icon = 'bi-collection-play';
        $this->order = 0;
        $this->is_active = true;
        $this->options = [
            'layout' => 'grid_standard',
            'banner_style' => 'hero_gradient',
            'card_style' => 'rounded_shadow',
            'accent_color' => '#FF004B',
            'show_audio_player' => true,
            'show_dossiers_carousel' => true,
            'show_filter_pills' => true,
            'show_trending_sidebar' => true,
            'badge_label' => '',
        ];
    }

    public function save()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name_fr);
        }
        $this->validate();

        $data = [
            'name_fr' => $this->name_fr,
            'name_en' => $this->name_en ?: null,
            'slug' => $this->slug,
            'icon' => $this->icon ?: 'bi-collection-play',
            'order' => $this->order,
            'is_active' => $this->is_active,
            'options' => $this->options,
        ];

        if ($this->banner) {
            $data['banner_image'] = ImageService::saveImage($this->banner, 'rubriques');
        } elseif ($this->rubriqueId) {
            $data['banner_image'] = $this->banner_image;
        }

        if ($this->rubriqueId) {
            $rubrique = Rubrique::findOrFail($this->rubriqueId);
            $rubrique->update($data);
            AdminLog::record('rubrique_updated', "Mise à jour de la rubrique '{$rubrique->name_fr}'");
            session()->flash('success', "Rubrique mise à jour avec succès.");
        } else {
            $rubrique = Rubrique::create($data);
            AdminLog::record('rubrique_created', "Création de la rubrique '{$rubrique->name_fr}'");
            session()->flash('success', "Nouvelle rubrique créée avec succès.");
        }

        $this->resetFields();
    }

    public function edit(int $id)
    {
        $r = Rubrique::findOrFail($id);
        $this->rubriqueId = $r->id;
        $this->name_fr = $r->name_fr;
        $this->name_en = $r->name_en ?? '';
        $this->slug = $r->slug;
        $this->icon = $r->icon ?? 'bi-collection-play';
        $this->banner_image = $r->banner_image;
        $this->banner = null;
        $this->order = $r->order;
        $this->is_active = $r->is_active;
        $this->options = array_merge([
            'layout' => 'grid_standard',
            'banner_style' => 'hero_gradient',
            'card_style' => 'rounded_shadow',
            'accent_color' => '#FF004B',
            'show_audio_player' => true,
            'show_dossiers_carousel' => true,
            'show_filter_pills' => true,
            'show_trending_sidebar' => true,
            'badge_label' => '',
        ], $r->options ?? []);
        $this->isEditing = true;
    }

    public function delete(int $id)
    {
        $r = Rubrique::findOrFail($id);
        $name = $r->name_fr;
        $r->delete();
        AdminLog::record('rubrique_deleted', "Suppression de la rubrique '{$name}'");
        session()->flash('success', "La rubrique '{$name}' a été supprimée.");
    }

    public function render()
    {
        $rubriques = Rubrique::withCount('categories')->orderBy('order')->get();
        return view('livewire.admin.rubriques.index', compact('rubriques'))
            ->layout('layouts.admin', ['title' => 'Gestion des Rubriques']);
    }
}
