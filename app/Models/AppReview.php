<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppReview extends Model
{
    protected $fillable = [
        'admin_id',
        'app_name',
        'slug',
        'category_name',
        'platforms',
        'category_logo',
        'cover_image',
        'score_ui',
        'score_ux',
        'score_handling',
        'final_score',
        'playstore_url',
        'appstore_url',
        'website_url',
        'appgallery_url',
        'summary_fr',
        'summary_en',
        'content_fr',
        'content_en',
        'status',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'platforms' => 'array',
            'score_ui' => 'float',
            'score_ux' => 'float',
            'score_handling' => 'float',
            'final_score' => 'float',
            'views_count' => 'integer',
        ];
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
