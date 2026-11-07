<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminLog extends Model
{
    protected $fillable = [
        'admin_id',
        'admin_email',
        'action',
        'description',
        'url',
        'method',
        'ip_address',
        'user_agent',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public static function record(string $action, ?string $description = null, ?Admin $admin = null): self
    {
        $admin = $admin ?? Auth::guard('admin')->user();
        return self::create([
            'admin_id' => $admin?->id,
            'admin_email' => $admin?->email,
            'action' => $action,
            'description' => $description,
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 500),
        ]);
    }
}
