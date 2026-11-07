<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_offer_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }
}
