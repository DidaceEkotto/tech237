<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioSiteVisit extends Model
{
    protected $fillable = [
        'portfolio_site_id',
        'page_slug',
        'visitor_hash',
        'referer',
        'visited_date',
    ];

    protected function casts(): array
    {
        return [
            'visited_date' => 'date',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(PortfolioSite::class, 'portfolio_site_id');
    }
}
