<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_id',
        'admin_id',
        'subject',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'reactions',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'reactions' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
