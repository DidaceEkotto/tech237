<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdBanner extends Model
{
    protected $fillable = [
        'title',
        'position',
        'image',
        'link_url',
        'custom_code',
        'is_active',
        'clicks_count',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'clicks_count' => 'integer',
            'views_count' => 'integer',
        ];
    }
}
