<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'rubrique_id',
        'parent_id',
        'name_fr',
        'name_en',
        'slug',
        'description_fr',
        'description_en',
        'color',
        'icon',
        'order',
        'is_active',
    ];

    public function rubrique()
    {
        return $this->belongsTo(Rubrique::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    protected static function booted()
    {
        static::deleting(function ($cat) {
            $cat->children()->each(fn($sub) => $sub->delete());
            $cat->articles()->each(fn($art) => $art->delete());
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
