<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Poc extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'author_name',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'link_url',
        'tag',
        'status',
        'upvotes_count',
        'views_count',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'upvotes_count' => 'integer',
        'views_count' => 'integer',
    ];

    protected static function booted()
    {
        static::deleting(function ($poc) {
            \App\Models\Comment::where('poc_id', $poc->id)->delete();
            $poc->upvotes()->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function upvotes(): HasMany
    {
        return $this->hasMany(PocUpvote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->with(['user', 'replies'])->latest();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function hasUserUpvoted(?int $userId = null, ?string $guestToken = null, ?string $ip = null): bool
    {
        if ($userId) {
            return $this->upvotes()->where('user_id', $userId)->exists();
        }

        if ($guestToken) {
            return $this->upvotes()->where('guest_token', $guestToken)->exists();
        }

        if ($ip && !in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return $this->upvotes()->whereNull('user_id')->where('ip_address', $ip)->exists();
        }

        return false;
    }

    public static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        if (empty($baseSlug)) {
            $baseSlug = 'poc-' . Str::random(6);
        }
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
