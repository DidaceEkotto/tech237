<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'website_url',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order')->latest();
    }

    public function isExternalLink(): bool
    {
        if (empty($this->website_url)) {
            return false;
        }

        $host = request()->getHost();
        $parsed = parse_url($this->website_url, PHP_URL_HOST);

        if (!$parsed) {
            return false;
        }

        return strtolower($parsed) !== strtolower($host);
    }
}
