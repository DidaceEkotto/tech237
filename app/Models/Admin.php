<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'gender',
        'email',
        'phone',
        'birth_date',
        'city',
        'password',
        'role',
        'is_banned',
        'banned_at',
        'failed_login_attempts',
        'default_password',
        'avatar',
        'bio',
        'social_twitter',
        'social_linkedin',
        'social_facebook',
        'social_instagram',
        'social_tiktok',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
            'birth_date' => 'date',
            'default_password' => 'integer',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function canValidateArticles(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'admin_id');
    }

    public function appReviews()
    {
        return $this->hasMany(AppReview::class, 'admin_id');
    }

    public function gameReviews()
    {
        return $this->hasMany(GameReview::class, 'admin_id');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'admin_id');
    }

    public function logs()
    {
        return $this->hasMany(AdminLog::class, 'admin_id');
    }
}
