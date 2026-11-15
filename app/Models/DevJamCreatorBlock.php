<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevJamCreatorBlock extends Model
{
    protected $fillable = [
        'creator_user_id',
        'blocked_user_id',
        'reason',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}
