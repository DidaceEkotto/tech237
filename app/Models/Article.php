<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'admin_id',
        'rubrique_id',
        'category_id',
        'dossier_id',
        'title_fr',
        'title_en',
        'slug',
        'summary_fr',
        'summary_en',
        'content_fr',
        'content_en',
        'featured_image',
        'status',
        'is_featured',
        'is_trending',
        'views_count',
        'published_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function rubrique()
    {
        return $this->belongsTo(Rubrique::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
