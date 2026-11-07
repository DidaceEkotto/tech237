<?php

namespace App\Livewire\Admin\Dossiers;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Dossier;
use App\Models\Rubrique;
use App\Models\AdminLog;
use App\Services\ImageService;

class Index extends Component
{
    use WithFileUploads;

    public ?int $dossierId = null;
    public ?int $rubrique_id = null;
    public string $title_fr = '';
    public string $title_en = '';
    public string $slug = '';
    public string $description_fr = '';
    public string $description_en = '';
    public $cover_image = null;
    public ?string $existing_image = null;
    public bool $is_featured = true;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'rubrique_id' => 'nullable|exists:rubriques,id',
            'title_fr' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'required|string|unique:dossiers,slug,' . $this->dossierId,
            'description_fr' => 'nullable|string',
            'description_en' => 'nullable|string',
            'cover_image' => 'nullable|image|max:10240',
        ];
    }

    public function updatedTitleFr($val)
    {
        if (!$this->dossierId) {
            $this->slug = Str::slug($val);
        }
    }

    public function edit(int $id)
    {
        $d = Dossier::findOrFail($id);
        $this->dossierId = $d->id;
        $this->rubrique_id = $d->rubrique_id;
        $this->title_fr = $d->title_fr;
        $this->title_en = $d->title_en ?? '';
        $this->slug = $d->slug;
        $this->description_fr = $d->description_fr ?? '';
        $this->description_en = $d->description_en ?? '';
        $this->existing_image = $d->cover_image;
        $this->is_featured = $d->is_featured;
        $this->isEditing = true;
    }

    public function resetFields()
    {
        $this->reset(['dossierId', 'rubrique_id', 'title_fr', 'title_en', 'slug', 'description_fr', 'description_en', 'cover_image', 'existing_image', 'isEditing']);
        $this->is_featured = true;
    }

    public function save()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->title_fr);
        }
        $this->validate();

        $imgPath = $this->existing_image;
        if ($this->cover_image) {
            $imgPath = ImageService::saveImage($this->cover_image, 'dossier');
        }

        $data = [
            'rubrique_id' => $this->rubrique_id ?: null,
            'title_fr' => $this->title_fr,
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'description_fr' => $this->description_fr,
            'description_en' => $this->description_en,
            'cover_image' => $imgPath,
            'is_featured' => $this->is_featured,
        ];

        if ($this->dossierId) {
            $d = Dossier::findOrFail($this->dossierId);
            $d->update($data);
            AdminLog::record('dossier_updated', "Mise à jour du dossier '{$d->title_fr}'");
            session()->flash('success', "Le dossier '{$d->title_fr}' a été mis à jour.");
        } else {
            $d = Dossier::create($data);
            AdminLog::record('dossier_created', "Création du dossier thématique '{$d->title_fr}'");
            session()->flash('success', "Le dossier '{$d->title_fr}' a été créé avec succès.");
        }

        $this->resetFields();
    }

    public function delete(int $id)
    {
        $d = Dossier::findOrFail($id);
        $title = $d->title_fr;
        $d->delete();
        AdminLog::record('dossier_deleted', "Suppression du dossier '{$title}'");
        session()->flash('success', "Le dossier '{$title}' a été supprimé.");
    }

    public function render()
    {
        $dossiers = Dossier::with(['rubrique'])->withCount('articles')->latest()->get();
        $rubriques = Rubrique::orderBy('name_fr')->get();
        return view('livewire.admin.dossiers.index', compact('dossiers', 'rubriques'))
            ->layout('layouts.admin', ['title' => 'Gestion des Dossiers Thématiques']);
    }
}
