<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechPoll extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'description',
        'is_active',
        'expires_at',
        'admin_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(TechPollOption::class)->orderBy('order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(TechPollVote::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function getTotalVotesAttribute(): int
    {
        return (int) $this->options->sum('votes_count');
    }

    public function hasUserVoted(?int $userId, ?string $ip): bool
    {
        if ($userId) {
            return $this->votes()->where('user_id', $userId)->exists();
        }
        if ($ip) {
            return $this->votes()->where('ip_address', $ip)->exists();
        }
        return false;
    }
}
