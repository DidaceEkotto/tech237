<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ContentView;
use App\Models\Article;
use App\Models\GameReview;
use App\Models\AppReview;
use App\Models\Poc;
use App\Models\Project;

class UniqueViewService
{
    /**
     * Record a unique view for an Article, GameReview or AppReview.
     * Returns true if a new unique view was recorded, false if already counted.
     */
    public static function recordView(Model $model, Request $request): bool
    {
        $type = self::resolveType($model);
        if (!$type) {
            return false;
        }

        $ip = $request->ip() ?: '127.0.0.1';
        $userAgent = substr((string) $request->header('User-Agent', 'unknown'), 0, 500);
        $userId = Auth::guard('web')->id();

        // Persistent device fingerprint combining IP, User-Agent, and session / cookie identifier
        $cookieToken = $request->cookie('tech237_reader_id');
        if (!$cookieToken) {
            $cookieToken = bin2hex(random_bytes(16));
            if (!headers_sent()) {
                cookie()->queue(cookie()->forever('tech237_reader_id', $cookieToken));
            }
        }

        $fingerprint = hash('sha256', $ip . '|' . $userAgent . '|' . $cookieToken);

        // Check if this reader already viewed this specific content
        $exists = ContentView::where('viewable_type', $type)
            ->where('viewable_id', $model->id)
            ->where(function ($q) use ($fingerprint, $ip, $userId) {
                $q->where('device_fingerprint', $fingerprint)
                  ->orWhere('ip_address', $ip);

                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->exists();

        if ($exists) {
            return false;
        }

        $adminId = $model->admin_id ?? null;

        ContentView::create([
            'viewable_type' => $type,
            'viewable_id' => $model->id,
            'admin_id' => $adminId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device_fingerprint' => $fingerprint,
            'user_id' => $userId,
            'viewed_date' => now()->toDateString(),
        ]);

        $model->increment('views_count');

        return true;
    }

    protected static function resolveType(Model $model): ?string
    {
        if ($model instanceof Article) {
            return 'article';
        }
        if ($model instanceof GameReview) {
            return 'game_review';
        }
        if ($model instanceof AppReview) {
            return 'app_review';
        }
        if ($model instanceof Poc) {
            return 'poc';
        }
        if ($model instanceof Project) {
            return 'project';
        }

        return null;
    }
}
