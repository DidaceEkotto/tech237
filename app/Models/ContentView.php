<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentView extends Model
{
    use HasFactory;

    protected $fillable = [
        'viewable_type',
        'viewable_id',
        'admin_id',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'user_id',
        'viewed_date',
    ];

    protected function casts(): array
    {
        return [
            'viewed_date' => 'date',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function viewable()
    {
        return $this->morphTo();
    }
}
