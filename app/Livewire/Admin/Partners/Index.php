<?php

namespace App\Livewire\Admin\Partners;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Partner;
use App\Models\AdminLog;
use App\Services\ImageService;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public bool $isCreating = false;
    public ?int $editingPartnerId = null;

    // Form inputs
    public string $name = '';
    public string $website_url = '';
    public $logo = null; // UploadedFile
    public ?string $currentLogo = null;
    public bool $is_active = true;
    public int $order = 0;

    protected $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        $logoRule = $this->editingPartnerId ? 'nullable|image|max:5120' : 'required|image|max:5120';
        return [
            'name' => 'required|string|max:150',
            'website_url' => 'nullable|url|max:255',
            'logo' => $logoRule,
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom de l\'entreprise partenaire est obligatoire.',
        'logo.required' => 'Le logo de l\'entreprise est obligatoire.',
        'logo.image' => 'Le fichier doit être une image valide.',
        'logo.max' => 'Le logo ne doit pas dépasser 5 Mo.',
        'website_url.url' => 'L\'adresse URL du site web doit être valide (ex: https://...).',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'website_url', 'logo', 'currentLogo', 'editingPartnerId']);
        $this->is_active = true;
        $this->order = (int) Partner::max('order') + 1;
        $this->isCreating = true;
    }

    public function editPartner(int $id)
    {
        $this->resetValidation();
        $partner = Partner::findOrFail($id);
        $this->editingPartnerId = $partner->id;
        $this->name = $partner->name;
        $this->website_url = $partner->website_url ?? '';
        $this->currentLogo = $partner->logo;
        $this->logo = null;
        $this->is_active = $partner->is_active;
        $this->order = $partner->order;
        $this->isCreating = true;
    }

    public function save()
    {
        $this->validate();

        $logoPath = $this->currentLogo;
        if ($this->logo) {
            $logoPath = ImageService::saveImage($this->logo, 'partners');
        }

        if ($this->editingPartnerId) {
            $partner = Partner::findOrFail($this->editingPartnerId);
            $partner->update([
                'name' => $this->name,
                'website_url' => $this->website_url ?: null,
                'logo' => $logoPath,
                'is_active' => $this->is_active,
                'order' => $this->order,
            ]);
            $admin = Auth::guard('admin')->user();
            AdminLog::record('partner_updated', "Mise à jour du partenaire '{$partner->name}'", $admin);
            session()->flash('success', "Le partenaire \"{$partner->name}\" a été mis à jour avec succès.");
        } else {
            $partner = Partner::create([
                'name' => $this->name,
                'website_url' => $this->website_url ?: null,
                'logo' => $logoPath,
                'is_active' => $this->is_active,
                'order' => $this->order,
            ]);
            $admin = Auth::guard('admin')->user();
            AdminLog::record('partner_created', "Création du partenaire '{$partner->name}'", $admin);
            session()->flash('success', "Le partenaire \"{$partner->name}\" a été ajouté avec succès.");
        }

        $this->isCreating = false;
        $this->reset(['name', 'website_url', 'logo', 'currentLogo', 'editingPartnerId']);
    }

    public function toggleActive(int $id)
    {
        $partner = Partner::findOrFail($id);
        $partner->update(['is_active' => !$partner->is_active]);
        $status = $partner->is_active ? 'activé' : 'désactivé';
        session()->flash('success', "Partenaire \"{$partner->name}\" {$status}.");
    }

    public function deletePartner(int $id)
    {
        $partner = Partner::findOrFail($id);
        $name = $partner->name;
        $partner->delete();
        $admin = Auth::guard('admin')->user();
        AdminLog::record('partner_deleted', "Suppression du partenaire '{$name}'", $admin);
        session()->flash('success', "Le partenaire \"{$name}\" a été supprimé.");
    }

    public function cancel()
    {
        $this->isCreating = false;
        $this->reset(['name', 'website_url', 'logo', 'currentLogo', 'editingPartnerId']);
    }

    public function render()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('partners')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {}
        }

        $query = Partner::query()->orderBy('order', 'asc')->latest('id');

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                  ->orWhere('website_url', 'like', "%{$this->search}%");
        }

        $partners = $query->paginate(12);

        $stats = [
            'total' => Partner::count(),
            'active' => Partner::where('is_active', true)->count(),
            'inactive' => Partner::where('is_active', false)->count(),
        ];

        return view('livewire.admin.partners.index', compact('partners', 'stats'))
            ->layout('layouts.admin', ['title' => 'Partenaires & Entreprises']);
    }
}
