<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title_fr',
        'title_en',
        'subtitle_fr',
        'subtitle_en',
        'image',
        'link_url',
        'button_text_fr',
        'button_text_en',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
