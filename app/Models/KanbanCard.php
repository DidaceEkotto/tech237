<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_id',
        'board_id',
        'title',
        'description',
        'priority',
        'due_date',
        'assigned_to',
        'labels',
        'checklist',
        'attachments',
        'order',
        'devlog_published',
        'overdue_alert_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'checklist' => 'array',
            'attachments' => 'array',
            'due_date' => 'date:Y-m-d',
            'order' => 'integer',
            'devlog_published' => 'boolean',
            'overdue_alert_sent_at' => 'datetime',
        ];
    }

    public function comments(): HasMany
    {
        return $this->hasMany(KanbanCardComment::class, 'card_id')->with('user')->oldest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(KanbanCardActivity::class, 'card_id')->with('user')->latest();
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'column_id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class, 'board_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
