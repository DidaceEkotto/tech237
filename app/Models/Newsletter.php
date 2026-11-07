<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = [
        'sent_by_admin_id',
        'subject_fr',
        'subject_en',
        'content_fr',
        'content_en',
        'recipients_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'recipients_count' => 'integer',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'sent_by_admin_id');
    }
}
