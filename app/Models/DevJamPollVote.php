<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevJamPollVote extends Model
{
    protected $fillable = [
        'poll_id',
        'option_id',
        'user_id',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(DevJamPoll::class, 'poll_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(DevJamPollOption::class, 'option_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
