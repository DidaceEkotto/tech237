<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'slug',
        'description',
        'display_mode',
        'cover_image',
        'theme_color',
        'status',
        'requires_login',
        'one_response_per_user',
        'max_responses',
        'expires_at',
        'notify_email_on_response',
        'notification_email',
        'send_respondent_confirmation',
        'confirmation_message',
        'redirect_url',
        'views_count',
    ];

    protected $casts = [
        'requires_login' => 'boolean',
        'one_response_per_user' => 'boolean',
        'notify_email_on_response' => 'boolean',
        'send_respondent_confirmation' => 'boolean',
        'expires_at' => 'datetime',
        'max_responses' => 'integer',
        'views_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->uuid)) {
                $form->uuid = (string) Str::uuid();
            }
            if (empty($form->slug)) {
                $baseSlug = Str::slug($form->title ?: 'formulaire');
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $form->slug = $slug;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('order', 'asc');
    }

    public function responses()
    {
        return $this->hasMany(FormResponse::class)->latest();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isQuotaReached(): bool
    {
        return $this->max_responses && $this->responses()->count() >= $this->max_responses;
    }

    public function isAcceptingResponses(): bool
    {
        return $this->status === 'published' && !$this->isExpired() && !$this->isQuotaReached();
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/f/' . $this->slug);
    }
}
