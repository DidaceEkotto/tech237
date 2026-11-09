<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAlertPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'alert_jobs',
        'alert_conferences',
        'alert_projects',
        'alert_newsletter',
    ];

    protected $casts = [
        'alert_jobs' => 'boolean',
        'alert_conferences' => 'boolean',
        'alert_projects' => 'boolean',
        'alert_newsletter' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
