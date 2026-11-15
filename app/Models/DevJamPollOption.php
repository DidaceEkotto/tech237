<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevJamPollOption extends Model
{
    protected $fillable = [
        'poll_id',
        'label',
        'votes_count',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(DevJamPoll::class, 'poll_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(DevJamPollVote::class, 'option_id');
    }
}
