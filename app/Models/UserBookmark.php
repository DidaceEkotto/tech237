<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bookmarkable_type',
        'bookmarkable_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTargetItemAttribute()
    {
        return match ($this->bookmarkable_type) {
            'article' => Article::find($this->bookmarkable_id),
            'game_review' => GameReview::find($this->bookmarkable_id),
            'app_review' => AppReview::find($this->bookmarkable_id),
            'project' => Project::find($this->bookmarkable_id),
            'job' => JobOffer::find($this->bookmarkable_id),
            default => null,
        };
    }
}
