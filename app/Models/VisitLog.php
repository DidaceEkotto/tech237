<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'ip_address',
        'country',
        'country_code',
        'city',
        'url',
        'route_name',
        'user_agent',
        'duration_seconds',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
    ];
}
