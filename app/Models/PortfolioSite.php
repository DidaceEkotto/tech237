<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioSite extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'is_primary',
        'is_published',
        'global_settings',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_published' => 'boolean',
            'global_settings' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(PortfolioPage::class)->orderBy('sort_order');
    }

    public function themeParts(): HasMany
    {
        return $this->hasMany(PortfolioThemePart::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PortfolioMedia::class);
    }
}
