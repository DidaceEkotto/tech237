<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Rubrique extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_fr',
        'name_en',
        'slug',
        'icon',
        'banner_image',
        'options',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'options' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($rubrique) {
            if (empty($rubrique->slug)) {
                $rubrique->slug = Str::slug($rubrique->name_fr);
            }
        });

        static::deleting(function ($rubrique) {
            $rubrique->dossiers()->each(fn($d) => $d->delete());
            $rubrique->categories()->each(fn($c) => $c->delete());
        });
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('order');
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }
}
