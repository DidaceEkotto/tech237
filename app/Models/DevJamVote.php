<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevJamVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'dev_jam_id',
        'submission_id',
        'user_id',
    ];

    public function jam()
    {
        return $this->belongsTo(DevJam::class, 'dev_jam_id');
    }

    public function submission()
    {
        return $this->belongsTo(DevJamSubmission::class, 'submission_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
