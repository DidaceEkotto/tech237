<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FormResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'form_id',
        'user_id',
        'respondent_email',
        'answers',
        'ip_address',
        'user_agent',
        'completed_in_seconds',
    ];

    protected $casts = [
        'answers' => 'array',
        'completed_in_seconds' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($resp) {
            if (empty($resp->uuid)) {
                $resp->uuid = (string) Str::uuid();
            }
        });
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
