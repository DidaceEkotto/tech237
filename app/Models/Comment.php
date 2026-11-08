<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'article_id',
        'game_review_id',
        'app_review_id',
        'interview_id',
        'project_id',
        'parent_id',
        'content',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function gameReview()
    {
        return $this->belongsTo(GameReview::class);
    }

    public function appReview()
    {
        return $this->belongsTo(AppReview::class);
    }

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user');
    }
}
