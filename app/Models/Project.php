<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'startup_id',
        'name',
        'slug',
        'logo',
        'banner',
        'project_type', // web, mobile, desktop, game
        'app_types',
        'game_types',
        'languages',
        'frameworks_engines',
        'development_duration',
        'description',
        'features',
        'youtube_presentation_url',
        'appgallery_url',
        'gallery',
        'youtube_videos',
        'website_url',
        'playstore_url',
        'appstore_url',
        'repo_url',
        'status',
        'views_count',
    ];

    protected $casts = [
        'app_types' => 'array',
        'game_types' => 'array',
        'languages' => 'array',
        'frameworks_engines' => 'array',
        'features' => 'array',
        'gallery' => 'array',
        'youtube_videos' => 'array',
        'views_count' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name) . '-' . Str::random(5);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function startup(): BelongsTo
    {
        return $this->belongsTo(Startup::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(ProjectTimeline::class)->orderBy('event_date', 'desc');
    }

    public function programmingLanguages()
    {
        return $this->belongsToMany(ProgrammingLanguage::class, 'project_programming_language');
    }

    public function frameworksList()
    {
        return $this->belongsToMany(Framework::class, 'framework_project');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('order');
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(ProjectCollaborator::class);
    }

    public function activeCollaborators(): HasMany
    {
        return $this->hasMany(ProjectCollaborator::class)->where('status', 'active');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    public function isOwner(?User $user): bool
    {
        return $user && (int) $this->user_id === (int) $user->id;
    }

    public function isCollaborator(?User $user): bool
    {
        if (!$user) return false;
        return $this->activeCollaborators()->where('user_id', $user->id)->exists();
    }

    public function canContribute(?User $user): bool
    {
        return $this->isOwner($user) || $this->isCollaborator($user);
    }
}
