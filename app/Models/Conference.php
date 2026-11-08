<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'title_fr',
        'title_en',
        'slug',
        'cover_image',
        'event_date',
        'end_date',
        'deadline_registration',
        'location',
        'is_in_person',
        'is_free',
        'price',
        'website_url',
        'city',
        'description_fr',
        'description_en',
        'accepts_registrations',
        'status',
        'is_official_admin',
    ];

    protected $appends = [
        'title',
        'start_date',
        'description',
        'banner_image',
        'creator_user',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'end_date' => 'date',
            'deadline_registration' => 'date',
            'is_in_person' => 'boolean',
            'is_free' => 'boolean',
            'accepts_registrations' => 'boolean',
            'is_official_admin' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creatorUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function registrations()
    {
        return $this->hasMany(ConferenceRegistration::class);
    }

    public function getTitleAttribute()
    {
        return $this->title_fr ?: $this->title_en;
    }

    public function getStartDateAttribute()
    {
        return $this->event_date;
    }

    public function getDescriptionAttribute()
    {
        return $this->description_fr ?: $this->description_en;
    }

    public function getBannerImageAttribute()
    {
        return $this->cover_image;
    }

    public function getCreatorUserAttribute()
    {
        return $this->user;
    }
}
