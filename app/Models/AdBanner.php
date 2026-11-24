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

    /**
     * Return all configured display positions for this banner.
     * Supports single values, comma-separated lists, or JSON arrays.
     */
    public function getPositionsAttribute(): array
    {
        $raw = (string) ($this->attributes['position'] ?? '');
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_unique(array_filter(array_map('trim', array_map('strval', $decoded)))));
        }

        return array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
    }
}
