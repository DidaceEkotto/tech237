<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'problem_encountered',
        'solution_applied',
        'feature_ids',
        'features_impacted',
        'images',
        'youtube_url',
        'event_date',
    ];

    protected $casts = [
        'images' => 'array',
        'feature_ids' => 'array',
        'features_impacted' => 'array',
        'event_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
