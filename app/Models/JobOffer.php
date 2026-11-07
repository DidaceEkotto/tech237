<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'domain',
        'contract_type',
        'salary_type',
        'salary_amount',
        'city',
        'accepts_cv',
        'accepts_cover_letter',
        'short_description',
        'description',
        'deadline_date',
        'has_online_test',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($job) {
            if (empty($job->slug)) {
                $job->slug = \Illuminate\Support\Str::slug($job->title) . '-' . \Illuminate\Support\Str::random(5);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'domain' => 'array',
            'accepts_cv' => 'boolean',
            'accepts_cover_letter' => 'boolean',
            'has_online_test' => 'boolean',
            'deadline_date' => 'date',
        ];
    }

    protected $appends = [
        'company_name',
        'it_domain',
        'salary_range',
        'formatted_deadline',
    ];

    public function getFormattedDeadlineAttribute()
    {
        if (!$this->deadline_date) {
            return null;
        }
        return $this->deadline_date->translatedFormat('d F Y');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creatorUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getCompanyNameAttribute()
    {
        return $this->user ? $this->user->name : 'Recruteur Tech237';
    }

    public function getItDomainAttribute()
    {
        if (is_array($this->domain)) {
            return implode(', ', $this->domain);
        }
        return $this->domain ?: 'Informatique';
    }

    public function getSalaryRangeAttribute()
    {
        if ($this->salary_type === 'fixed' && $this->salary_amount) {
            return number_format($this->salary_amount, 0, ',', ' ') . ' FCFA';
        }
        return 'À négocier';
    }

    public function getIsPublishedAttribute()
    {
        return $this->status === 'approved';
    }

    public function questions()
    {
        return $this->hasMany(JobQuestion::class)->orderBy('order');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
