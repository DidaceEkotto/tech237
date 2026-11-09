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

    public function hasUserVoted(?int $userId = null, ?string $ip = null, ?string $guestToken = null): bool
    {
        // 1. If connected, check user ID strictly
        if ($userId) {
            return $this->votes()->where('user_id', $userId)->exists();
        }

        // 2. Check session if active
        try {
            if (session()->has('voted_poll_' . $this->id)) {
                return true;
            }
        } catch (\Throwable $e) {}

        // 3. Check persistent browser cookie
        try {
            if (request()->hasCookie('voted_poll_' . $this->id)) {
                return true;
            }
        } catch (\Throwable $e) {}

        // 4. Check guest unique token
        $token = $guestToken ?: request()->cookie('tech237_guest_token');
        if ($token) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('tech_poll_votes', 'guest_token')) {
                    if ($this->votes()->where('guest_token', $token)->exists()) {
                        return true;
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 5. Check client IP (ignore local loopback 127.0.0.1 and ::1 so dev testing doesn't block)
        if ($ip && !in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return $this->votes()->whereNull('user_id')->where('ip_address', $ip)->exists();
        }

        return false;
    }
}
