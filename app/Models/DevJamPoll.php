<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevJamPoll extends Model
{
    protected $fillable = [
        'dev_jam_id',
        'creator_user_id',
        'question',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function jam(): BelongsTo
    {
        return $this->belongsTo(DevJam::class, 'dev_jam_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(DevJamPollOption::class, 'poll_id')->orderBy('id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(DevJamPollVote::class, 'poll_id');
    }
}
