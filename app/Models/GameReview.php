<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameReview extends Model
{
    protected $fillable = [
        'admin_id',
        'game_name',
        'slug',
        'platform_category',
        'platforms',
        'platform_logo',
        'cover_image',
        'score_gameplay',
        'score_graphics',
        'score_accessibility',
        'score_soundtrack',
        'final_score',
        'pros_fr',
        'pros_en',
        'cons_fr',
        'cons_en',
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
            'score_gameplay' => 'float',
            'score_graphics' => 'float',
            'score_accessibility' => 'float',
            'score_soundtrack' => 'float',
            'final_score' => 'float',
            'views_count' => 'integer',
        ];
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'game_review_id');
    }
}
