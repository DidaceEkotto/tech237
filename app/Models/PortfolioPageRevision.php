<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioPageRevision extends Model
{
    protected $fillable = [
        'portfolio_page_id',
        'user_id',
        'tree_snapshot',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'tree_snapshot' => 'array',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(PortfolioPage::class, 'portfolio_page_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
