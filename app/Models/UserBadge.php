<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_code',
        'name',
        'description',
        'icon',
        'color',
        'awarded_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function availableBadges(): array
    {
        return [
            'pioneer' => [
                'name' => 'Pionnier Tech237',
                'description' => 'Membre des premières heures de la communauté Tech237',
                'icon' => 'bi-award-fill',
                'color' => '#00A859',
            ],
            'devlog_master' => [
                'name' => 'Devlog Master',
                'description' => 'A partagé au moins 3 étapes de développement publiques',
                'icon' => 'bi-journal-code',
                'color' => '#2563EB',
            ],
            'top_contributor' => [
                'name' => 'Top Contributeur',
                'description' => 'Très actif dans les retours, messages et votes',
                'icon' => 'bi-fire',
                'color' => '#EA580C',
            ],
            'verified_creator' => [
                'name' => 'Créateur Vérifié',
                'description' => 'A publié un projet tech ou une startup validée',
                'icon' => 'bi-patch-check-fill',
                'color' => '#7C3AED',
            ],
            'poll_enthusiast' => [
                'name' => 'Chasseur de Débats',
                'description' => 'Participe activement aux sondages de la communauté',
                'icon' => 'bi-pie-chart-fill',
                'color' => '#0D9488',
            ],
        ];
    }
}
