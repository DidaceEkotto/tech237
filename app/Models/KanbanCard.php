<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'order',
        'devlog_published',
    ];

    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'checklist' => 'array',
            'due_date' => 'date:Y-m-d',
            'order' => 'integer',
            'devlog_published' => 'boolean',
        ];
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
