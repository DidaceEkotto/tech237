<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PocUpvote extends Model
{
    use HasFactory;

    protected $fillable = [
        'poc_id',
        'user_id',
        'guest_token',
        'ip_address',
    ];

    public function poc(): BelongsTo
    {
        return $this->belongsTo(Poc::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
