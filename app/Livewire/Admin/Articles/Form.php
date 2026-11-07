<?php

namespace App\Livewire\Admin\Articles;

use App\Services\ImageService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Article;
use App\Models\Category;
use App\Models\Dossier;
use App\Models\Tag;
use App\Models\AdminLog;

class Form extends Component
{
    use WithFileUploads;

    public ?int $articleId = null;
    public string $title_fr = '';
    public string $title_en = '';
    public string $slug = '';
    public ?int $category_id = null;
    public ?int $dossier_id = null;
    public array $selectedTags = [];
    public string $summary_fr = '';
    public string $summary_en = '';
    public string $content_fr = '';
    public string $content_en = '';
    public $featured_image = null;
    public ?string $existing_image = null;
    public string $status = 'published';
    public bool $is_featured = false;
    public bool $is_trending = false;

    public bool $previewModal = false;

    protected function rules(): array
    {
        return [
            'title_fr' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'required|string|unique:articles,slug,' . $this->articleId,
            'category_id' => 'required|exists:categories,id',
            'dossier_id' => 'nullable|exists:dossiers,id',
            'content_fr' => 'required|string',
            'content_en' => 'nullable|string',
            'summary_fr' => 'nullable|string|max:500',
            'summary_en' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|max:10240',
        ];
    }

    public function mount(?int $id = null)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if ($id) {
            $article = Article::with('tags')->findOrFail($id);
            $this->articleId = $article->id;
            $this->title_fr = $article->title_fr;
            $this->title_en = $article->title_en ?? '';
            $this->slug = $article->slug;
            $this->category_id = $article->category_id;
            $this->dossier_id = $article->dossier_id;
            $this->selectedTags = $article->tags->pluck('id')->toArray();
            $this->summary_fr = $article->summary_fr ?? '';
            $this->summary_en = $article->summary_en ?? '';
            $this->content_fr = $article->content_fr;
            $this->content_en = $article->content_en ?? '';
            $this->existing_image = $article->featured_image;
            $this->status = $article->status;
            $this->is_featured = $article->is_featured;
            $this->is_trending = $article->is_trending;
        } else {
            // New article default status based on user role
            if ($currentAdmin && $currentAdmin->isEditor()) {
                $this->status = 'pending_review';
            } else {
                $this->status = 'published';
            }
        }
    }

    public function updatedTitleFr($value)
    {
        if (!$this->articleId) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(string $targetStatus = null)
    {
        $currentAdmin = Auth::guard('admin')->user();

        if ($targetStatus) {
            $this->status = $targetStatus;
        }

        // Enforce review policy: If editor role, article MUST go to pending_review unless already draft
        if ($currentAdmin->isEditor() && $this->status === 'published') {
            $this->status = 'pending_review';
        }

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->title_fr);
        }

        $this->validate();

        $imagePath = $this->existing_image;
        if ($this->featured_image) {
            $imagePath = ImageService::saveImage($this->featured_image, 'article');
        }

        $data = [
            'category_id' => $this->category_id,
            'dossier_id' => $this->dossier_id,
            'title_fr' => $this->title_fr,
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'summary_fr' => $this->summary_fr,
            'summary_en' => $this->summary_en,
            'content_fr' => $this->content_fr,
            'content_en' => $this->content_en,
            'featured_image' => $imagePath,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_trending' => $this->is_trending,
            'published_at' => $this->status === 'published' ? now() : null,
        ];

        if ($this->articleId) {
            $article = Article::findOrFail($this->articleId);
            $article->update($data);
            $article->tags()->sync($this->selectedTags);

            AdminLog::record('article_updated', "Mise à jour de l'article '{$article->title_fr}' (Statut: {$this->status})");
            session()->flash('success', "L'article a été mis à jour avec succès.");
        } else {
            $data['admin_id'] = $currentAdmin->id;
            if ($this->status === 'published') {
                $data['reviewed_by'] = $currentAdmin->id;
            }
            $article = Article::create($data);
            $article->tags()->sync($this->selectedTags);

            AdminLog::record('article_created', "Création de l'article '{$article->title_fr}' (Statut: {$this->status})");
            
            if ($this->status === 'pending_review') {
                session()->flash('success', "Votre article a été soumis avec succès pour validation auprès d'un administrateur.");
            } else {
                session()->flash('success', "L'article a été publié directement avec succès.");
            }
        }

        return redirect()->route('admin.articles.index');
    }

    public function render()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $dossiers = Dossier::orderBy('title_fr')->get();
        $tags = Tag::orderBy('name_fr')->get();

        return view('livewire.admin.articles.form', compact('categories', 'dossiers', 'tags'))
            ->layout('layouts.admin', ['title' => $this->articleId ? 'Modifier l\'article' : 'Rédiger un article']);
    }
}
