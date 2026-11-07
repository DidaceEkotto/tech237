<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dossier extends Model
{
    protected $fillable = [
        'rubrique_id',
        'title_fr',
        'title_en',
        'slug',
        'description_fr',
        'description_en',
        'cover_image',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function rubrique()
    {
        return $this->belongsTo(Rubrique::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
