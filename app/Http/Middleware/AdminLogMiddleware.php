<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminLog;
use Symfony\Component\HttpFoundation\Response;

class AdminLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            // Don't flood with livewire internal update polls, only main page requests
            if (!$request->is('livewire/*') && $request->isMethod('GET')) {
                AdminLog::create([
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'action' => 'visit_page',
                    'description' => 'Navigation vers ' . $request->path(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 500),
                ]);
            }
        }

        return $response;
    }
}
