<?php

namespace App\Livewire\Admin\Cities;

use App\Models\City;
use App\Models\AdminLog;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $cityId = null;
    public string $name = '';
    public string $slug = '';
    public string $region = '';
    public bool $is_active = true;
    public int $sort_order = 0;
    public bool $isEditing = false;

    // Filters & Search
    public string $search = '';
    public string $regionFilter = '';
    public string $statusFilter = '';

    protected $paginationTheme = 'bootstrap';

    public array $cameroonRegions = [
        'Adamaoua',
        'Centre',
        'Est',
        'Extrême-Nord',
        'Littoral',
        'Nord',
        'Nord-Ouest',
        'Ouest',
        'Sud',
        'Sud-Ouest',
    ];

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cities', 'name')->ignore($this->cityId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('cities', 'slug')->ignore($this->cityId),
            ],
            'region' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom de la ville est obligatoire.',
        'name.max' => 'Le nom de la ville ne peut pas dépasser 100 caractères.',
        'name.unique' => 'Cette ville est déjà enregistrée. Les villes doivent être uniques.',
        'slug.unique' => 'Cet identifiant (slug) est déjà utilisé par une autre ville.',
        'sort_order.integer' => 'L\'ordre d\'affichage doit être un nombre entier.',
        'sort_order.min' => 'L\'ordre d\'affichage ne peut pas être négatif.',
    ];

    public function updatedName($val): void
    {
        if (!$this->cityId) {
            $this->slug = Str::slug($val);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRegionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $city = City::findOrFail($id);
        $this->cityId = $city->id;
        $this->name = $city->name;
        $this->slug = $city->slug;
        $this->region = $city->region ?? '';
        $this->is_active = (bool) $city->is_active;
        $this->sort_order = (int) $city->sort_order;
        $this->isEditing = true;
        $this->resetValidation();
    }

    public function resetFields(): void
    {
        $this->reset(['cityId', 'name', 'slug', 'region', 'is_active', 'sort_order', 'isEditing']);
        $this->is_active = true;
        $this->sort_order = 0;
        $this->resetValidation();
    }

    public function save(): void
    {
        $trimmedName = trim($this->name);
        $this->name = $trimmedName;

        if (empty($this->slug)) {
            $this->slug = Str::slug($trimmedName);
        } else {
            $this->slug = Str::slug($this->slug);
        }

        // Strict case-insensitive uniqueness check to guarantee no duplicates
        $duplicate = City::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($trimmedName, 'UTF-8')])
            ->when($this->cityId, fn($q) => $q->where('id', '!=', $this->cityId))
            ->exists();

        if ($duplicate) {
            $this->addError('name', "La ville '{$trimmedName}' existe déjà. Une ville ne peut pas être enregistrée deux fois.");
            return;
        }

        $this->validate();

        $data = [
            'name' => $trimmedName,
            'slug' => $this->slug,
            'region' => !empty($this->region) ? trim($this->region) : null,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
        ];

        if ($this->cityId) {
            $city = City::findOrFail($this->cityId);
            $city->update($data);
            AdminLog::record('city_updated', "Mise à jour de la ville '{$city->name}' (Région: {$city->region})");
            session()->flash('success', "La ville '{$city->name}' a été mise à jour avec succès.");
        } else {
            $city = City::create($data);
            AdminLog::record('city_created', "Création de la nouvelle ville '{$city->name}' (Région: {$city->region})");
            session()->flash('success', "La ville '{$city->name}' a été ajoutée avec succès.");
        }

        $this->resetFields();
    }

    public function toggleStatus(int $id): void
    {
        $city = City::findOrFail($id);
        $city->is_active = !$city->is_active;
        $city->save();

        $statusText = $city->is_active ? 'activée' : 'désactivée';
        AdminLog::record('city_status_toggled', "Ville '{$city->name}' {$statusText}");
        session()->flash('success', "La ville '{$city->name}' a été {$statusText}.");
    }

    public function delete(int $id): void
    {
        $city = City::findOrFail($id);
        $name = $city->name;
        $city->delete();

        AdminLog::record('city_deleted', "Suppression définitive de la ville '{$name}'");
        session()->flash('success', "La ville '{$name}' a été supprimée avec succès.");

        if ($this->cityId === $id) {
            $this->resetFields();
        }
    }

    public function render()
    {
        $query = City::query();

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('slug', 'like', "%{$s}%")
                  ->orWhere('region', 'like', "%{$s}%");
            });
        }

        if (!empty($this->regionFilter)) {
            $query->where('region', $this->regionFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $cities = $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15);

        $totalCount = City::count();
        $activeCount = City::where('is_active', true)->count();
        $inactiveCount = City::where('is_active', false)->count();

        return view('livewire.admin.cities.index', [
            'cities' => $cities,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
        ])->layout('layouts.admin', ['title' => 'Gestion des Villes (Cameroun)']);
    }
}
