<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(KanbanColumn::class, 'board_id')->orderBy('order');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(KanbanCard::class, 'board_id')->orderBy('order');
    }

    public function members(): HasMany
    {
        return $this->hasMany(KanbanBoardMember::class, 'board_id')->with('user');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(KanbanCardActivity::class, 'board_id')->with('user')->latest();
    }

    /**
     * Check if a given user can view this board
     */
    public function canAccess(?User $user): bool
    {
        if ($this->is_public) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        if ($this->members()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($this->project_id) {
            $project = $this->project;
            if ($project && $project->canContribute($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a given user can edit this board and its cards
     */
    public function canEdit(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        if ($this->members()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($this->project_id) {
            $project = $this->project;
            if ($project && $project->canContribute($user)) {
                return true;
            }
        }

        return false;
    }
}
