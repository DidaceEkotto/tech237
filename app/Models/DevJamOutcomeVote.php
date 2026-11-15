<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevJamOutcomeVote extends Model
{
    protected $fillable = [
        'dev_jam_id',
        'user_id',
        'vote',
    ];

    public function jam()
    {
        return $this->belongsTo(DevJam::class, 'dev_jam_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
