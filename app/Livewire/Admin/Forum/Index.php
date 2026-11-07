<?php

namespace App\Livewire\Admin\Forum;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\ForumPost;
use App\Models\ForumReport;
use App\Models\AdminLog;

class Index extends Component
{
    public ?int $categoryId = null;
    public string $cat_name_fr = '';
    public string $cat_name_en = '';
    public string $cat_slug = '';
    public string $cat_description = '';
    public string $cat_icon = 'bi-chat-dots';
    public string $cat_color = '#FF004B';
    public int $cat_order = 0;
    public bool $cat_is_active = true;
    public bool $isEditingCat = false;

    public string $tab = 'reports'; // 'reports', 'topics', 'categories'

    public function updatedCatNameFr($val)
    {
        if (!$this->categoryId) {
            $this->cat_slug = Str::slug($val);
        }
    }

    public function saveCategory()
    {
        $this->validate([
            'cat_name_fr' => 'required|string|max:100',
            'cat_slug' => 'required|string|unique:forum_categories,slug,' . $this->categoryId,
            'cat_icon' => 'required|string',
            'cat_color' => 'required|string',
        ]);

        $data = [
            'name' => $this->cat_name_fr,
            'slug' => $this->cat_slug,
            'description' => $this->cat_description ?: null,
            'icon' => $this->cat_icon,
            'color' => $this->cat_color,
            'order' => $this->cat_order,
            'is_active' => $this->cat_is_active,
        ];

        if ($this->categoryId) {
            $cat = ForumCategory::findOrFail($this->categoryId);
            $cat->update($data);
            AdminLog::record('forum_category_updated', "Mise à jour de la catégorie forum '{$cat->name}'");
            session()->flash('success', "Catégorie mise à jour.");
        } else {
            $cat = ForumCategory::create($data);
            AdminLog::record('forum_category_created', "Création de la catégorie forum '{$cat->name}'");
            session()->flash('success', "Nouvelle catégorie créée.");
        }

        $this->reset(['categoryId', 'cat_name_fr', 'cat_name_en', 'cat_slug', 'cat_description', 'isEditingCat']);
        $this->cat_icon = 'bi-chat-dots';
        $this->cat_color = '#FF004B';
    }

    public function editCategory(int $id)
    {
        $cat = ForumCategory::findOrFail($id);
        $this->categoryId = $cat->id;
        $this->cat_name_fr = $cat->name;
        $this->cat_name_en = '';
        $this->cat_slug = $cat->slug;
        $this->cat_description = $cat->description ?? '';
        $this->cat_icon = $cat->icon;
        $this->cat_color = $cat->color;
        $this->cat_order = $cat->order;
        $this->cat_is_active = (bool)($cat->is_active ?? true);
        $this->isEditingCat = true;
        $this->tab = 'categories';
    }

    public function deleteCategory(int $id)
    {
        $cat = ForumCategory::findOrFail($id);
        $cat->delete();
        session()->flash('success', "Catégorie supprimée.");
    }

    public function toggleTopicPin(int $id)
    {
        $t = ForumTopic::findOrFail($id);
        $t->update(['is_pinned' => !$t->is_pinned]);
        session()->flash('success', "Statut épinglé modifié.");
    }

    public function toggleTopicClose(int $id)
    {
        $t = ForumTopic::findOrFail($id);
        $t->update(['is_closed' => !$t->is_closed]);
        session()->flash('success', "Statut de fermeture modifié.");
    }

    public function deleteTopic(int $id)
    {
        $t = ForumTopic::findOrFail($id);
        $title = $t->title;
        $t->delete();
        AdminLog::record('forum_topic_deleted', "Suppression du sujet forum '{$title}'");
        session()->flash('success', "Le sujet a été supprimé.");
    }

    public function dismissReport(int $id)
    {
        $r = ForumReport::findOrFail($id);
        $r->update(['status' => 'dismissed']);
        session()->flash('success', "Signalement ignoré.");
    }

    public function approveReportAndDeletePost(int $id)
    {
        $r = ForumReport::findOrFail($id);
        if ($r->post) {
            $r->post->delete();
        } elseif ($r->topic) {
            $r->topic->delete();
        }
        $r->update(['status' => 'actioned']);
        AdminLog::record('forum_report_actioned', "Modération du signalement #{$id} : contenu supprimé");
        session()->flash('success', "Contenu signalé supprimé avec succès.");
    }

    public function render()
    {
        $reports = ForumReport::with(['reporter', 'topic', 'post.user'])->where('status', 'pending')->latest()->get();
        $topics = ForumTopic::with(['category', 'user'])->withCount('posts')->latest()->paginate(15);
        $categories = ForumCategory::withCount('topics')->orderBy('order')->get();

        return view('livewire.admin.forum.index', compact('reports', 'topics', 'categories'))
            ->layout('layouts.admin', ['title' => 'Modération & Gestion du Forum']);
    }
}
