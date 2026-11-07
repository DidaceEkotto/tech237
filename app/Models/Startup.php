<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Startup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo',
        'banner',
        'legal_type',
        'city',
        'location',
        'employee_count',
        'needs_funding',
        'funding_amount',
        'rccm_number',
        'niu_number',
        'description',
        'status',
        'rejection_reason',
        'validated_at',
    ];

    protected $casts = [
        'needs_funding' => 'boolean',
        'employee_count' => 'integer',
        'funding_amount' => 'float',
        'validated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($startup) {
            if (empty($startup->slug)) {
                $startup->slug = Str::slug($startup->name) . '-' . Str::random(5);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }
}
