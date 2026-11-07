<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobInterview extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id',
        'interview_date',
        'interview_time',
        'location',
        'notes',
        'is_sent',
    ];

    protected function casts(): array
    {
        return [
            'interview_date' => 'date',
            'is_sent' => 'boolean',
        ];
    }

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }
}
