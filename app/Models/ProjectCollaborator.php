<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCollaborator extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'invitation_id',
        'role',
        'status', // active, revoked
        'joined_at',
        'revoked_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(ProjectInvitation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
