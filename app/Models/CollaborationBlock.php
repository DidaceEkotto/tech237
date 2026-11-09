<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaborationBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
        'blocked_email',
        'reason',
    ];

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }

    /**
     * Check if a block exists between two users or user & email in either direction
     */
    public static function isBlocked(int $userId1, ?int $userId2, ?string $email2 = null): bool
    {
        return self::where(function ($q) use ($userId1, $userId2, $email2) {
            // userId1 blocked userId2 / email2
            $q->where('blocker_id', $userId1)
              ->where(function ($sub) use ($userId2, $email2) {
                  if ($userId2) $sub->where('blocked_id', $userId2);
                  if ($email2) $sub->orWhere('blocked_email', strtolower(trim($email2)));
              });
        })->orWhere(function ($q) use ($userId1, $userId2) {
            // userId2 blocked userId1
            if ($userId2) {
                $q->where('blocker_id', $userId2)->where('blocked_id', $userId1);
            }
        })->exists();
    }
}
