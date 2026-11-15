<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevJamSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'dev_jam_id',
        'user_id',
        'title',
        'description',
        'submission_type',
        'html_code',
        'css_code',
        'js_code',
        'external_repo_url',
        'external_demo_url',
        'votes_count',
        'is_winner',
        'rank_position',
    ];

    protected function casts(): array
    {
        return [
            'is_winner' => 'boolean',
        ];
    }

    public function jam()
    {
        return $this->belongsTo(DevJam::class, 'dev_jam_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        return $this->hasMany(DevJamVote::class, 'submission_id');
    }

    public function messages()
    {
        return $this->hasMany(DevJamMessage::class, 'submission_id');
    }
}
