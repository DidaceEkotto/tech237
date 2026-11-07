<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'forum_category_id',
        'user_id',
        'title',
        'slug',
        'content',
        'views_count',
        'is_resolved',
        'is_solved',
        'is_closed',
        'is_pinned',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
            'is_solved' => 'boolean',
            'is_closed' => 'boolean',
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->hasMany(ForumPost::class)->latest();
    }
}
