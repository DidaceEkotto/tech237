<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioThemePart extends Model
{
    protected $fillable = [
        'portfolio_site_id',
        'type',
        'name',
        'tree',
        'display_conditions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tree' => 'array',
            'display_conditions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(PortfolioSite::class, 'portfolio_site_id');
    }
}
