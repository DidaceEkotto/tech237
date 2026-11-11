<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeartbeatCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'heartbeat_monitor_id',
        'status_code',
        'response_time_ms',
        'is_up',
        'error_message',
        'ip_address',
        'checked_at',
    ];

    protected $casts = [
        'is_up' => 'boolean',
        'status_code' => 'integer',
        'response_time_ms' => 'integer',
        'checked_at' => 'datetime',
    ];

    public function monitor()
    {
        return $this->belongsTo(HeartbeatMonitor::class, 'heartbeat_monitor_id');
    }
}
