<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_offer_id',
        'user_id',
        'name',
        'email',
        'phone',
        'cv_file',
        'cover_letter_file',
        'presentation_text',
        'test_answers',
        'test_score',
        'examined_at',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'test_answers' => 'array',
            'examined_at' => 'datetime',
        ];
    }

    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interview()
    {
        return $this->hasOne(JobInterview::class);
    }

    public function interviews()
    {
        return $this->hasMany(JobInterview::class);
    }

    public function getApplicantNameAttribute()
    {
        return $this->name;
    }

    public function getApplicantEmailAttribute()
    {
        return $this->email;
    }

    public function getApplicantPhoneAttribute()
    {
        return $this->phone;
    }

    public function getCvPathAttribute()
    {
        return $this->cv_file;
    }
}
