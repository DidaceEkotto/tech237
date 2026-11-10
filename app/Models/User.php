<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'gender',
        'country_code',
        'phone',
        'email',
        'password',
        'avatar',
        'bio',
        'status',
        'ban_reason',
        'xp',
        'rank_title',
        'pending_level_up',
        'birth_date',
        'marital_status',
        'registered_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'registered_at' => 'datetime',
            'password' => 'hashed',
            'pending_level_up' => 'array',
        ];
    }

    protected $appends = ['age', 'full_name'];

    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }
        return Carbon::parse($this->birth_date)->age;
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->name ?? ''));
    }

    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    public function startups(): HasMany
    {
        return $this->hasMany(Startup::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(UserMessage::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function forumTopics(): HasMany
    {
        return $this->hasMany(ForumTopic::class);
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function conferences(): HasMany
    {
        return $this->hasMany(Conference::class);
    }

    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }

    public function projectCollaborations(): HasMany
    {
        return $this->hasMany(ProjectCollaborator::class);
    }

    public function activeProjectCollaborations(): HasMany
    {
        return $this->hasMany(ProjectCollaborator::class)->where('status', 'active');
    }

    public function receivedProjectInvitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class, 'invited_user_id');
    }

    public function sentProjectInvitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class, 'inviter_id');
    }

    public function upvotes(): HasMany
    {
        return $this->hasMany(ProjectUpvote::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(UserBookmark::class)->latest();
    }

    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class)->latest();
    }

    public function alertPreference(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserAlertPreference::class);
    }

    public function addXp(int $points): void
    {
        $currentXp = $this->xp ?? 0;
        $newXp = max(0, $currentXp + $points);

        $oldTier = XpLevel::getLevelForXp($currentXp);
        $newTier = XpLevel::getLevelForXp($newXp);

        $updateData = [
            'xp' => $newXp,
            'rank_title' => $newTier->name,
        ];

        // Did the user unlock a higher level tier?
        if (($newTier->min_xp ?? 0) > ($oldTier->min_xp ?? 0)) {
            $updateData['pending_level_up'] = [
                'name' => $newTier->name,
                'min_xp' => $newTier->min_xp,
                'badge_icon' => $newTier->badge_icon ?? 'bi-award',
                'color' => $newTier->color ?? '#00A859',
                'description' => $newTier->description ?? '',
                'new_xp' => $newXp,
                'achieved_at' => now()->toIso8601String(),
            ];
        }

        $this->update($updateData);
    }
}
