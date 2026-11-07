<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConferenceRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id',
        'user_id',
        'name',
        'first_name',
        'gender',
        'city',
        'email',
        'phone_whatsapp',
        'photo',
        'bio',
        'is_confirmed',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }

    public function conference()
    {
        return $this->belongsTo(Conference::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLastNameAttribute()
    {
        return $this->name;
    }

    public function getStatusAttribute()
    {
        return $this->is_confirmed ? 'confirmed' : 'pending';
    }
}
