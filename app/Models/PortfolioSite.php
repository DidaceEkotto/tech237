<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Tenant;

class PortfolioSite extends Tenant
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'site_type',
        'domain',
        'is_primary',
        'is_published',
        'views_count',
        'unique_visitors_count',
        'global_settings',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_published' => 'boolean',
            'views_count' => 'integer',
            'unique_visitors_count' => 'integer',
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

    public function visits(): HasMany
    {
        return $this->hasMany(PortfolioSiteVisit::class, 'portfolio_site_id');
    }
}
