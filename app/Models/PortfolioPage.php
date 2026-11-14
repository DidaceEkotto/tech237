<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioPage extends Model
{
    protected $fillable = [
        'portfolio_site_id',
        'title',
        'slug',
        'is_homepage',
        'draft_tree',
        'published_tree',
        'seo_meta',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_homepage' => 'boolean',
            'draft_tree' => 'array',
            'published_tree' => 'array',
            'seo_meta' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(PortfolioSite::class, 'portfolio_site_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PortfolioPageRevision::class)->latest();
    }
}
