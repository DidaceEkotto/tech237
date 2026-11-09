<?php

namespace App\Livewire\Admin\GameReviews;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\GameReview;
use App\Models\AdminLog;
use App\Services\ImageService;

class Form extends Component
{
    use WithFileUploads;

    public ?int $reviewId = null;
    public string $game_name = '';
    public string $slug = '';
    public string $platform_category = 'PlayStation 5';
    public array $platforms = ['PlayStation 5'];
    public string $platform_logo = 'bi-playstation';
    public $cover_image = null;
    public ?string $existing_image = null;

    // Scores
    public float $score_gameplay = 16.0;
    public float $score_graphics = 16.0;
    public float $score_accessibility = 16.0;
    public float $score_soundtrack = 16.0;
    public float $final_score = 16.0;

    // Pros & Cons
    public string $pros_fr = '';
    public string $pros_en = '';
    public string $cons_fr = '';
    public string $cons_en = '';

    // Content
    public string $summary_fr = '';
    public string $summary_en = '';
    public string $content_fr = '';
    public string $content_en = '';
    public string $status = 'published';

    protected function rules(): array
    {
        return [
            'game_name' => 'required|string|max:255',
            'slug' => 'required|string|unique:game_reviews,slug,' . $this->reviewId,
            'platform_category' => 'required|string',
            'platforms' => 'nullable|array',
            'score_gameplay' => 'required|numeric|min:0|max:20',
            'score_graphics' => 'required|numeric|min:0|max:20',
            'score_accessibility' => 'required|numeric|min:0|max:20',
            'score_soundtrack' => 'required|numeric|min:0|max:20',
            'final_score' => 'required|numeric|min:0|max:20',
            'content_fr' => 'required|string',
            'content_en' => 'nullable|string',
            'cover_image' => 'nullable|image|max:10240',
        ];
    }

    public function mount(?int $id = null)
    {
        if ($id) {
            $gr = GameReview::findOrFail($id);
            $this->reviewId = $gr->id;
            $this->game_name = $gr->game_name;
            $this->slug = $gr->slug;
            $this->platform_category = $gr->platform_category;
            $this->platforms = $gr->platforms ?? ($gr->platform_category ? [$gr->platform_category] : ['PlayStation 5']);
            $this->platform_logo = $gr->platform_logo ?? 'bi-playstation';
            $this->existing_image = $gr->cover_image;
            $this->score_gameplay = (float) $gr->score_gameplay;
            $this->score_graphics = (float) $gr->score_graphics;
            $this->score_accessibility = (float) $gr->score_accessibility;
            $this->score_soundtrack = (float) $gr->score_soundtrack;
            $this->final_score = (float) $gr->final_score;
            $this->pros_fr = $gr->pros_fr ?? '';
            $this->pros_en = $gr->pros_en ?? '';
            $this->cons_fr = $gr->cons_fr ?? '';
            $this->cons_en = $gr->cons_en ?? '';
            $this->summary_fr = $gr->summary_fr ?? '';
            $this->summary_en = $gr->summary_en ?? '';
            $this->content_fr = $gr->content_fr;
            $this->content_en = $gr->content_en ?? '';
            $this->status = $gr->status;
        }
    }

    public function updatedGameName($val)
    {
        if (!$this->reviewId) {
            $this->slug = Str::slug($val);
        }
    }

    public function calculateFinalScore()
    {
        $this->final_score = round(($this->score_gameplay + $this->score_graphics + $this->score_accessibility + $this->score_soundtrack) / 4, 1);
    }

    public function updatedPlatformCategory($val)
    {
        $logos = [
            'PlayStation 5' => 'bi-playstation',
            'PlayStation 4' => 'bi-playstation',
            'Xbox Series S & X' => 'bi-xbox',
            'Xbox One' => 'bi-xbox',
            'Nintendo Switch' => 'bi-nintendo-switch',
            'Nintendo Switch 2' => 'bi-nintendo-switch',
            'PC' => 'bi-pc-display',
            'Linux ou Mac' => 'bi-apple',
            'Mobile' => 'bi-phone',
        ];
        $this->platform_logo = $logos[$val] ?? 'bi-controller';
    }

    public function save()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->game_name);
        }
        $this->validate();

        $imgPath = $this->existing_image;
        if ($this->cover_image) {
            $imgPath = ImageService::saveImage($this->cover_image, 'game');
        }

        $data = [
            'game_name' => $this->game_name,
            'slug' => $this->slug,
            'platform_category' => !empty($this->platforms) ? implode(', ', $this->platforms) : $this->platform_category,
            'platforms' => $this->platforms,
            'platform_logo' => $this->platform_logo,
            'cover_image' => $imgPath,
            'score_gameplay' => $this->score_gameplay,
            'score_graphics' => $this->score_graphics,
            'score_accessibility' => $this->score_accessibility,
            'score_soundtrack' => $this->score_soundtrack,
            'final_score' => $this->final_score,
            'pros_fr' => $this->pros_fr,
            'pros_en' => $this->pros_en,
            'cons_fr' => $this->cons_fr,
            'cons_en' => $this->cons_en,
            'summary_fr' => $this->summary_fr,
            'summary_en' => $this->summary_en,
            'content_fr' => $this->content_fr,
            'content_en' => $this->content_en,
            'status' => $this->status,
        ];

        if ($this->reviewId) {
            $gr = GameReview::findOrFail($this->reviewId);
            $gr->update($data);
            AdminLog::record('game_review_updated', "Mise à jour du test jeu vidéo '{$gr->game_name}'");
            session()->flash('success', "Le test de '{$gr->game_name}' a été mis à jour.");
        } else {
            $data['admin_id'] = Auth::guard('admin')->id();
            $gr = GameReview::create($data);
            AdminLog::record('game_review_created', "Création du test jeu vidéo '{$gr->game_name}' (Note: {$gr->final_score}/20)");
            \App\Services\BookmarkNotificationService::notifyGameReview($gr);
            session()->flash('success', "Le test du jeu '{$gr->game_name}' a été publié.");
        }

        return redirect()->route('admin.game-reviews.index');
    }

    public function render()
    {
        return view('livewire.admin.game-reviews.form')
            ->layout('layouts.admin', ['title' => $this->reviewId ? 'Modifier le Test Jeu Vidéo' : 'Nouveau Test Jeu Vidéo']);
    }
}
