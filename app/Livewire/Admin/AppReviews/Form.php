<?php

namespace App\Livewire\Admin\AppReviews;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\AppReview;
use App\Models\AdminLog;
use App\Services\ImageService;

class Form extends Component
{
    use WithFileUploads;

    public ?int $reviewId = null;
    public string $app_name = '';
    public string $slug = '';
    public string $category_name = 'Android / iOS';
    public array $platforms = ['Android', 'iOS'];
    public string $category_logo = 'bi-google-play';
    public $cover_image = null;
    public ?string $existing_image = null;
    public float $score_ui = 16.0;
    public float $score_ux = 16.0;
    public float $score_handling = 16.0;
    public float $final_score = 16.0;
    public string $playstore_url = '';
    public string $appstore_url = '';
    public string $website_url = '';
    public string $appgallery_url = '';
    public string $summary_fr = '';
    public string $summary_en = '';
    public string $content_fr = '';
    public string $content_en = '';
    public string $status = 'published';

    protected function rules(): array
    {
        return [
            'app_name' => 'required|string|max:255',
            'slug' => 'required|string|unique:app_reviews,slug,' . $this->reviewId,
            'category_name' => 'required|string',
            'platforms' => 'nullable|array',
            'score_ui' => 'required|numeric|min:0|max:20',
            'score_ux' => 'required|numeric|min:0|max:20',
            'score_handling' => 'required|numeric|min:0|max:20',
            'final_score' => 'required|numeric|min:0|max:20',
            'playstore_url' => 'nullable|url',
            'appstore_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'appgallery_url' => 'nullable|url',
            'content_fr' => 'required|string',
            'content_en' => 'nullable|string',
            'cover_image' => 'nullable|image|max:10240',
        ];
    }

    public function mount(?int $id = null)
    {
        if ($id) {
            $r = AppReview::findOrFail($id);
            $this->reviewId = $r->id;
            $this->app_name = $r->app_name;
            $this->slug = $r->slug;
            $this->category_name = $r->category_name;
            $this->platforms = $r->platforms ?? ($r->category_name ? [$r->category_name] : []);
            $this->category_logo = $r->category_logo ?? 'bi-google-play';
            $this->existing_image = $r->cover_image;
            $this->score_ui = (float) $r->score_ui;
            $this->score_ux = (float) $r->score_ux;
            $this->score_handling = (float) $r->score_handling;
            $this->final_score = (float) $r->final_score;
            $this->playstore_url = $r->playstore_url ?? '';
            $this->appstore_url = $r->appstore_url ?? '';
            $this->website_url = $r->website_url ?? '';
            $this->appgallery_url = $r->appgallery_url ?? '';
            $this->summary_fr = $r->summary_fr ?? '';
            $this->summary_en = $r->summary_en ?? '';
            $this->content_fr = $r->content_fr;
            $this->content_en = $r->content_en ?? '';
            $this->status = $r->status;
        }
    }

    public function updatedAppName($val)
    {
        if (!$this->reviewId) {
            $this->slug = Str::slug($val);
        }
    }

    public function calculateFinalScore()
    {
        $this->final_score = round(($this->score_ui + $this->score_ux + $this->score_handling) / 3, 1);
    }

    public function save()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->app_name);
        }
        $this->validate();

        $imgPath = $this->existing_image;
        if ($this->cover_image) {
            $imgPath = ImageService::saveImage($this->cover_image, 'app');
        }

        $data = [
            'app_name' => $this->app_name,
            'slug' => $this->slug,
            'category_name' => !empty($this->platforms) ? implode(', ', $this->platforms) : $this->category_name,
            'platforms' => $this->platforms,
            'category_logo' => $this->category_logo,
            'cover_image' => $imgPath,
            'score_ui' => $this->score_ui,
            'score_ux' => $this->score_ux,
            'score_handling' => $this->score_handling,
            'final_score' => $this->final_score,
            'playstore_url' => $this->playstore_url ?: null,
            'appstore_url' => $this->appstore_url ?: null,
            'website_url' => $this->website_url ?: null,
            'appgallery_url' => $this->appgallery_url ?: null,
            'summary_fr' => $this->summary_fr,
            'summary_en' => $this->summary_en,
            'content_fr' => $this->content_fr,
            'content_en' => $this->content_en,
            'status' => $this->status,
        ];

        if ($this->reviewId) {
            $r = AppReview::findOrFail($this->reviewId);
            $r->update($data);
            AdminLog::record('app_review_updated', "Mise à jour du test d'application '{$r->app_name}'");
            session()->flash('success', "Le test de l'application '{$r->app_name}' a été mis à jour.");
        } else {
            $data['admin_id'] = Auth::guard('admin')->id();
            $r = AppReview::create($data);
            AdminLog::record('app_review_created', "Création du test d'application '{$r->app_name}' (Note: {$r->final_score}/20)");
            \App\Services\BookmarkNotificationService::notifyAppReview($r);
            session()->flash('success', "Le test de l'application '{$r->app_name}' a été enregistré.");
        }

        return redirect()->route('admin.app-reviews.index');
    }

    public function render()
    {
        return view('livewire.admin.app-reviews.form')
            ->layout('layouts.admin', ['title' => $this->reviewId ? 'Modifier le Test Application' : 'Nouveau Test Application']);
    }
}
