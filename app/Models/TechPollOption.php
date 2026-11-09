<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechPollOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'tech_poll_id',
        'label',
        'votes_count',
        'order',
    ];

    protected $appends = ['title'];

    public function getTitleAttribute(): ?string
    {
        return $this->label;
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(TechPoll::class, 'tech_poll_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(TechPollVote::class);
    }
}
