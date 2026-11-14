<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'scope',
        'thumbnail_url',
        'tree',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'tree' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
