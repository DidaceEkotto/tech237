<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevJam extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'theme',
        'description',
        'rules',
        'mode',
        'status',
        'creator_user_id',
        'creator_admin_id',
        'starts_at',
        'ends_at',
        'prize_text',
        'allow_external_links',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'allow_external_links' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function creatorUser()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function creatorAdmin()
    {
        return $this->belongsTo(Admin::class, 'creator_admin_id');
    }

    public function submissions()
    {
        return $this->hasMany(DevJamSubmission::class)->orderByDesc('votes_count')->latest();
    }

    public function messages()
    {
        return $this->hasMany(DevJamMessage::class)->latest();
    }

    public function votes()
    {
        return $this->hasMany(DevJamVote::class);
    }

    public function computedStatus(): string
    {
        if (in_array($this->status, ['pending_validation', 'rejected', 'voting', 'ended'], true)) {
            return $this->status;
        }
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return 'upcoming';
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return 'voting';
        }
        return 'active';
    }
}
