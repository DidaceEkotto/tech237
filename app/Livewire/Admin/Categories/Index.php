<?php

namespace App\Livewire\Admin\Categories;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Rubrique;
use App\Models\AdminLog;

class Index extends Component
{
    public ?int $categoryId = null;
    public ?int $rubrique_id = null;
    public ?int $parent_id = null;
    public string $name_fr = '';
    public string $name_en = '';
    public string $slug = '';
    public string $description_fr = '';
    public string $description_en = '';
    public string $color = '#FF004B';
    public string $icon = 'bi-tag';
    public int $order = 0;
    public bool $is_active = true;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'rubrique_id' => 'nullable|exists:rubriques,id',
            'name_fr' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'slug' => 'required|string|unique:categories,slug,' . $this->categoryId,
            'parent_id' => 'nullable|exists:categories,id',
            'color' => 'required|string|max:20',
            'icon' => 'required|string|max:50',
            'order' => 'integer',
        ];
    }

    public function updatedNameFr($val)
    {
        if (!$this->categoryId) {
            $this->slug = Str::slug($val);
        }
    }

    public function edit(int $id)
    {
        $c = Category::findOrFail($id);
        $this->categoryId = $c->id;
        $this->rubrique_id = $c->rubrique_id;
        $this->parent_id = $c->parent_id;
        $this->name_fr = $c->name_fr;
        $this->name_en = $c->name_en ?? '';
        $this->slug = $c->slug;
        $this->description_fr = $c->description_fr ?? '';
        $this->description_en = $c->description_en ?? '';
        $this->color = $c->color;
        $this->icon = $c->icon;
        $this->order = $c->order;
        $this->is_active = $c->is_active;
        $this->isEditing = true;
    }

    public function resetFields()
    {
        $this->reset(['categoryId', 'rubrique_id', 'parent_id', 'name_fr', 'name_en', 'slug', 'description_fr', 'description_en', 'isEditing']);
        $this->color = '#FF004B';
        $this->icon = 'bi-tag';
        $this->order = 0;
        $this->is_active = true;
    }

    public function save()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name_fr);
        }
        $this->validate();

        $data = [
            'rubrique_id' => $this->rubrique_id ?: null,
            'parent_id' => $this->parent_id ?: null,
            'name_fr' => $this->name_fr,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'description_fr' => $this->description_fr,
            'description_en' => $this->description_en,
            'color' => $this->color,
            'icon' => $this->icon,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];

        if ($this->categoryId) {
            $c = Category::findOrFail($this->categoryId);
            $c->update($data);
            AdminLog::record('category_updated', "Mise à jour de la catégorie '{$c->name_fr}'");
            session()->flash('success', "La catégorie '{$c->name_fr}' a été mise à jour.");
        } else {
            $c = Category::create($data);
            AdminLog::record('category_created', "Création de la catégorie '{$c->name_fr}'");
            session()->flash('success', "La catégorie '{$c->name_fr}' a été créée.");
        }

        $this->resetFields();
    }

    public function delete(int $id)
    {
        $c = Category::findOrFail($id);
        $name = $c->name_fr;
        $c->delete();
        AdminLog::record('category_deleted', "Suppression de la catégorie '{$name}'");
        session()->flash('success', "Catégorie '{$name}' supprimée.");
    }

    public function render()
    {
        $mainCategories = Category::whereNull('parent_id')
            ->with(['rubrique', 'children' => fn($q) => $q->withCount('articles')])
            ->withCount('articles')
            ->orderBy('order')
            ->get();

        $parentOptions = Category::whereNull('parent_id')
            ->when($this->categoryId, fn($q) => $q->where('id', '!=', $this->categoryId))
            ->get();

        $rubriques = Rubrique::orderBy('order')->get();

        return view('livewire.admin.categories.index', compact('mainCategories', 'parentOptions', 'rubriques'))
            ->layout('layouts.admin', ['title' => 'Catégories et Sous-catégories']);
    }
}
