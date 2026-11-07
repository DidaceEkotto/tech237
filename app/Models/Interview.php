<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $fillable = [
        'admin_id',
        'interviewee_name',
        'interviewee_role',
        'title_fr',
        'title_en',
        'slug',
        'cover_image',
        'video_url',
        'summary_fr',
        'summary_en',
        'content_fr',
        'content_en',
        'qa_blocks',
        'status',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'views_count' => 'integer',
            'qa_blocks' => 'array',
        ];
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
