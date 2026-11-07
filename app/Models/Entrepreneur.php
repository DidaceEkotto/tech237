<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrepreneur extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'startup_id',
        'name',
        'role_title',
        'role_or_title',
        'photo',
        'month_year',
        'bio_fr',
        'bio_en',
        'story_fr',
        'story_en',
        'difficulties_fr',
        'difficulties_en',
        'journey_fr',
        'journey_en',
        'challenges_fr',
        'challenges_en',
        'vision_fr',
        'vision_en',
        'video_url',
        'gallery',
        'social_links',
        'is_active',
        'is_current_month',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_current_month' => 'boolean',
            'is_published' => 'boolean',
            'gallery' => 'array',
            'social_links' => 'array',
        ];
    }

    public function startup()
    {
        return $this->belongsTo(Startup::class)->with('projects');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Accessor / Mutator for seamless compatibility between role_or_title and role_title
    public function getRoleOrTitleAttribute(): ?string
    {
        return $this->attributes['role_or_title'] ?? $this->attributes['role_title'] ?? '';
    }

    public function getRoleTitleAttribute(): ?string
    {
        return $this->attributes['role_title'] ?? $this->attributes['role_or_title'] ?? '';
    }
}
