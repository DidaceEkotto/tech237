<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreelanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_monthly_net',
        'work_weeks_per_year',
        'work_days_per_week',
        'non_billable_percent',
        'tax_percent',
        'expenses',
        'computed_tjm',
        'computed_thm',
        'payment_info',
    ];

    protected $casts = [
        'expenses' => 'array',
        'payment_info' => 'array',
        'target_monthly_net' => 'integer',
        'work_weeks_per_year' => 'integer',
        'work_days_per_week' => 'integer',
        'non_billable_percent' => 'integer',
        'tax_percent' => 'float',
        'computed_tjm' => 'integer',
        'computed_thm' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
