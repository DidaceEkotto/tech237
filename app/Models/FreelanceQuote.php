<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FreelanceQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'quote_number',
        'type',
        'status',
        'title',
        'client_name',
        'client_company',
        'client_email',
        'client_phone',
        'client_address',
        'issue_date',
        'due_date',
        'currency',
        'items',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_milestones',
        'payment_methods',
        'notes',
        'terms',
        'accepted_at',
        'paid_at',
    ];

    protected $casts = [
        'items' => 'array',
        'payment_milestones' => 'array',
        'payment_methods' => 'array',
        'subtotal' => 'integer',
        'tax_rate' => 'float',
        'tax_amount' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'issue_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
        'accepted_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($quote) {
            if (empty($quote->uuid)) {
                $quote->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to format currency in FCFA or specified currency
     */
    public function formatAmount(int $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' ' . ($this->currency ?: 'FCFA');
    }
}
