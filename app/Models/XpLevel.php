<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class XpLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_xp',
        'badge_icon',
        'color',
        'description',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'min_xp' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Default tiers fallback if table is not migrated or empty
     */
    public static function defaultTiers(): array
    {
        return [
            ['name' => 'Novice Tech 🌱', 'min_xp' => 0, 'badge_icon' => 'bi-seedling', 'color' => '#10B981', 'description' => 'Bienvenue dans la communauté Tech237 !'],
            ['name' => 'Apprenti Codeur 💻', 'min_xp' => 20, 'badge_icon' => 'bi-laptop', 'color' => '#3B82F6', 'description' => 'Premières interactions et votes communautaires.'],
            ['name' => 'Codeur Actif ⚡', 'min_xp' => 50, 'badge_icon' => 'bi-lightning-charge-fill', 'color' => '#F59E0B', 'description' => 'Membre dynamique publiant des POCs et des retours.'],
            ['name' => 'Super Contributeur 🚀', 'min_xp' => 100, 'badge_icon' => 'bi-rocket-takeoff-fill', 'color' => '#EC4899', 'description' => 'Pilier communautaire et résolveur de problèmes.'],
            ['name' => 'Tech Builder 🛠️', 'min_xp' => 250, 'badge_icon' => 'bi-tools', 'color' => '#8B5CF6', 'description' => 'Bâtisseur chevronné livrant des projets complets.'],
            ['name' => 'Tech Lead 🌟', 'min_xp' => 500, 'badge_icon' => 'bi-star-fill', 'color' => '#06B6D4', 'description' => 'Leader technique respecté et mentor.'],
            ['name' => 'Légende Tech237 👑', 'min_xp' => 1000, 'badge_icon' => 'bi-trophy-fill', 'color' => '#EAB308', 'description' => 'Le sommet de l\'écosystème tech camerounais.'],
        ];
    }

    /**
     * Get all tiers ordered by min_xp ascending
     */
    public static function getAllTiers()
    {
        try {
            if (Schema::hasTable('xp_levels')) {
                $tiers = static::orderBy('min_xp', 'asc')->get();
                if ($tiers->isNotEmpty()) {
                    return $tiers;
                }
            }
        } catch (\Throwable $e) {}

        // Fallback collection
        return collect(static::defaultTiers())->map(function ($item, $idx) {
            $obj = new static($item);
            $obj->id = $idx + 1;
            $obj->order = $idx + 1;
            return $obj;
        });
    }

    /**
     * Find the matching level object for a given XP count
     */
    public static function getLevelForXp(int $xp)
    {
        try {
            if (Schema::hasTable('xp_levels')) {
                $level = static::where('min_xp', '<=', $xp)
                    ->orderByDesc('min_xp')
                    ->first();
                if ($level) {
                    return $level;
                }
            }
        } catch (\Throwable $e) {}

        // Fallback from static array
        $defaults = array_reverse(static::defaultTiers());
        foreach ($defaults as $d) {
            if ($xp >= $d['min_xp']) {
                $obj = new static($d);
                return $obj;
            }
        }

        return new static(static::defaultTiers()[0]);
    }

    /**
     * Next level above current XP
     */
    public static function getNextLevelForXp(int $xp)
    {
        try {
            if (Schema::hasTable('xp_levels')) {
                $next = static::where('min_xp', '>', $xp)
                    ->orderBy('min_xp', 'asc')
                    ->first();
                if ($next) {
                    return $next;
                }
            }
        } catch (\Throwable $e) {}

        foreach (static::defaultTiers() as $d) {
            if ($d['min_xp'] > $xp) {
                return new static($d);
            }
        }

        return null;
    }
}
