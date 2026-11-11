<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HeartbeatMonitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'uuid',
        'name',
        'url',
        'check_interval_minutes',
        'expected_status_code',
        'http_method',
        'timeout_seconds',
        'status',
        'is_active',
        'notify_email',
        'email_recipient',
        'last_status_code',
        'last_response_time_ms',
        'last_checked_at',
        'next_check_at',
        'last_down_at',
        'last_up_at',
        'last_error_message',
        'uptime_percentage',
        'total_checks',
        'successful_checks',
        'failed_checks',
        'is_currently_down',
        'down_alert_sent_at',
        'is_public',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'notify_email' => 'boolean',
        'is_currently_down' => 'boolean',
        'is_public' => 'boolean',
        'check_interval_minutes' => 'integer',
        'expected_status_code' => 'integer',
        'timeout_seconds' => 'integer',
        'last_status_code' => 'integer',
        'last_response_time_ms' => 'integer',
        'uptime_percentage' => 'float',
        'total_checks' => 'integer',
        'successful_checks' => 'integer',
        'failed_checks' => 'integer',
        'last_checked_at' => 'datetime',
        'next_check_at' => 'datetime',
        'last_down_at' => 'datetime',
        'last_up_at' => 'datetime',
        'down_alert_sent_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($monitor) {
            if (empty($monitor->uuid)) {
                $monitor->uuid = (string) Str::uuid();
            }
            if (empty($monitor->next_check_at)) {
                $monitor->next_check_at = now();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function checks()
    {
        return $this->hasMany(HeartbeatCheck::class)->latest('checked_at');
    }

    public function recentChecks()
    {
        return $this->hasMany(HeartbeatCheck::class)->latest('checked_at')->take(20);
    }

    public function getEffectiveEmailAttribute(): ?string
    {
        return $this->email_recipient ?: $this->user?->email;
    }
}
