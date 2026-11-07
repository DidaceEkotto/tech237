<?php

namespace App\Livewire\Admin\Tags;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Tag;
use App\Models\AdminLog;

class Index extends Component
{
    public ?int $tagId = null;
    public string $name_fr = '';
    public string $name_en = '';
    public string $slug = '';
    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'name_fr' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'slug' => 'required|string|unique:tags,slug,' . $this->tagId,
        ];
    }

    public function updatedNameFr($val)
    {
        if (!$this->tagId) {
            $this->slug = Str::slug($val);
        }
    }

    public function edit(int $id)
    {
        $t = Tag::findOrFail($id);
        $this->tagId = $t->id;
        $this->name_fr = $t->name_fr;
        $this->name_en = $t->name_en ?? '';
        $this->slug = $t->slug;
        $this->isEditing = true;
    }

    public function resetFields()
    {
        $this->reset(['tagId', 'name_fr', 'name_en', 'slug', 'isEditing']);
    }

    public function save()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name_fr);
        }
        $this->validate();

        $data = [
            'name_fr' => $this->name_fr,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
        ];

        if ($this->tagId) {
            $t = Tag::findOrFail($this->tagId);
            $t->update($data);
            AdminLog::record('tag_updated', "Mise à jour du tag '{$t->name_fr}'");
            session()->flash('success', "Le tag '{$t->name_fr}' a été mis à jour.");
        } else {
            $t = Tag::create($data);
            AdminLog::record('tag_created', "Création du tag '{$t->name_fr}'");
            session()->flash('success', "Le tag '{$t->name_fr}' a été créé.");
        }

        $this->resetFields();
    }

    public function delete(int $id)
    {
        $t = Tag::findOrFail($id);
        $name = $t->name_fr;
        $t->delete();
        AdminLog::record('tag_deleted', "Suppression du tag '{$name}'");
        session()->flash('success', "Tag '{$name}' supprimé.");
    }

    public function render()
    {
        $tags = Tag::withCount('articles')->latest()->get();
        return view('livewire.admin.tags.index', compact('tags'))
            ->layout('layouts.admin', ['title' => 'Gestion des Tags']);
    }
}
