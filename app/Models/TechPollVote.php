<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechPollVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'tech_poll_id',
        'tech_poll_option_id',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(TechPoll::class, 'tech_poll_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(TechPollOption::class, 'tech_poll_option_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
