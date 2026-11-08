<?php

namespace App\Livewire\Admin\Entrepreneurs;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Entrepreneur;
use App\Models\Startup;
use App\Models\AdminLog;
use App\Services\ImageService;

class Form extends Component
{
    use WithFileUploads;

    public ?int $entrepreneurId = null;
    public ?int $startup_id = null;
    public ?string $name = '';
    public ?string $role_or_title = '';
    public $photo = null;
    public ?string $existing_photo = null;
    public ?string $month_year = '';
    public ?string $bio_fr = '';
    public ?string $bio_en = '';
    public ?string $journey_fr = '';
    public ?string $journey_en = '';
    public ?string $challenges_fr = '';
    public ?string $challenges_en = '';
    public ?string $vision_fr = '';
    public ?string $vision_en = '';
    public ?string $video_url = '';
    public ?string $social_linkedin = '';
    public ?string $social_twitter = '';
    public ?string $social_website = '';
    public bool $is_current_month = true;
    public bool $is_published = true;
    public array $gallery = [];
    public $newGalleryImages = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'role_or_title' => 'required|string|max:255',
            'month_year' => 'required|string|max:100',
            'startup_id' => 'nullable|exists:startups,id',
            'bio_fr' => 'required|string',
            'bio_en' => 'nullable|string',
            'journey_fr' => 'nullable|string',
            'journey_en' => 'nullable|string',
            'challenges_fr' => 'nullable|string',
            'challenges_en' => 'nullable|string',
            'vision_fr' => 'nullable|string',
            'vision_en' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:10240',
            'newGalleryImages.*' => 'nullable|image|max:10240',
        ];
    }

    public function mount(?int $id = null)
    {
        if ($id) {
            $e = Entrepreneur::findOrFail($id);
            $this->entrepreneurId = $e->id;
            $this->startup_id = $e->startup_id;
            $this->name = $e->name ?? '';
            $this->role_or_title = $e->role_or_title ?? $e->role_title ?? '';
            $this->existing_photo = $e->photo;
            $this->month_year = $e->month_year ?? now()->translatedFormat('F Y');
            $this->bio_fr = $e->bio_fr ?? '';
            $this->bio_en = $e->bio_en ?? '';
            $this->journey_fr = $e->journey_fr ?? $e->story_fr ?? '';
            $this->journey_en = $e->journey_en ?? $e->story_en ?? '';
            $this->challenges_fr = $e->challenges_fr ?? $e->difficulties_fr ?? '';
            $this->challenges_en = $e->challenges_en ?? $e->difficulties_en ?? '';
            $this->vision_fr = $e->vision_fr ?? '';
            $this->vision_en = $e->vision_en ?? '';
            $this->video_url = $e->video_url ?? '';
            $this->gallery = is_array($e->gallery) ? $e->gallery : [];
            $this->is_current_month = (bool)($e->is_current_month ?? $e->is_active ?? true);
            $this->is_published = (bool)($e->is_published ?? true);

            $links = $e->social_links ?? [];
            $this->social_linkedin = $links['linkedin'] ?? '';
            $this->social_twitter = $links['twitter'] ?? '';
            $this->social_website = $links['website'] ?? '';
        } else {
            $this->month_year = now()->translatedFormat('F Y');
            $this->gallery = [];
        }
    }

    public function uploadGalleryImages()
    {
        if (empty($this->newGalleryImages)) {
            return;
        }

        $count = 0;
        foreach ($this->newGalleryImages as $img) {
            if ($img) {
                $saved = ImageService::saveImage($img, 'entrepreneur_gallery');
                if ($saved) {
                    $this->gallery[] = $saved;
                    $count++;
                }
            }
        }
        $this->newGalleryImages = [];

        if ($this->entrepreneurId) {
            Entrepreneur::where('id', $this->entrepreneurId)->update(['gallery' => $this->gallery]);
        }

        session()->flash('gallery_success', "{$count} photo(s) ajoutée(s) avec succès à la galerie !");
    }

    public function removeGalleryImage(int $index)
    {
        if (isset($this->gallery[$index])) {
            array_splice($this->gallery, $index, 1);
            if ($this->entrepreneurId) {
                Entrepreneur::where('id', $this->entrepreneurId)->update(['gallery' => $this->gallery]);
            }
            session()->flash('gallery_success', "Photo retirée de la galerie.");
        }
    }

    public function save()
    {
        $this->startup_id = empty($this->startup_id) ? null : (int)$this->startup_id;

        $this->validate();

        $photoPath = $this->existing_photo;
        if ($this->photo) {
            $photoPath = ImageService::saveImage($this->photo, 'entrepreneur');
        }

        // Handle additional gallery uploads
        if (!empty($this->newGalleryImages)) {
            foreach ($this->newGalleryImages as $img) {
                if ($img) {
                    $saved = ImageService::saveImage($img, 'entrepreneur_gallery');
                    if ($saved) {
                        $this->gallery[] = $saved;
                    }
                }
            }
            $this->newGalleryImages = [];
        }

        if ($this->is_current_month) {
            Entrepreneur::where('is_current_month', true)->update(['is_current_month' => false, 'is_active' => false]);
        }

        $socialLinks = [
            'linkedin' => $this->social_linkedin ?: null,
            'twitter' => $this->social_twitter ?: null,
            'website' => $this->social_website ?: null,
        ];

        $data = [
            'startup_id' => $this->startup_id ?: null,
            'name' => $this->name,
            'role_or_title' => $this->role_or_title,
            'role_title' => $this->role_or_title,
            'photo' => $photoPath,
            'month_year' => $this->month_year,
            'bio_fr' => $this->bio_fr,
            'bio_en' => $this->bio_en ?: null,
            'journey_fr' => $this->journey_fr ?: '',
            'journey_en' => $this->journey_en ?: null,
            'story_fr' => $this->journey_fr ?: '',
            'story_en' => $this->journey_en ?: null,
            'challenges_fr' => $this->challenges_fr ?: '',
            'challenges_en' => $this->challenges_en ?: null,
            'difficulties_fr' => $this->challenges_fr ?: '',
            'difficulties_en' => $this->challenges_en ?: null,
            'vision_fr' => $this->vision_fr ?: '',
            'vision_en' => $this->vision_en ?: null,
            'video_url' => $this->video_url ?: null,
            'gallery' => $this->gallery,
            'social_links' => $socialLinks,
            'is_current_month' => $this->is_current_month,
            'is_active' => $this->is_current_month,
            'is_published' => $this->is_published,
        ];

        if ($this->entrepreneurId) {
            $e = Entrepreneur::findOrFail($this->entrepreneurId);
            $e->update($data);
            AdminLog::record('entrepreneur_updated', "Mise à jour du portrait de '{$e->name}'");
            session()->flash('success', "Le portrait de '{$e->name}' a été mis à jour.");
        } else {
            $data['admin_id'] = Auth::guard('admin')->id();
            $e = Entrepreneur::create($data);
            AdminLog::record('entrepreneur_created', "Création du portrait de '{$e->name}'");
            session()->flash('success', "'{$e->name}' a été enregistré avec succès.");
        }

        return redirect()->route('admin.entrepreneurs.index');
    }

    public function render()
    {
        $startups = Startup::where('status', 'validated')->orderBy('name')->get();
        return view('livewire.admin.entrepreneurs.form', compact('startups'))
            ->layout('layouts.admin', ['title' => $this->entrepreneurId ? 'Modifier Entrepreneur' : 'Nouvel Entrepreneur du mois']);
    }
}
