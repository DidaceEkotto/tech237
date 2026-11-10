<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table des paliers d'expérience (XP Levels)
        if (!Schema::hasTable('xp_levels')) {
            Schema::create('xp_levels', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // ex: 'Novice Tech', 'Super Contributeur'
                $table->unsignedInteger('min_xp')->default(0)->unique();
                $table->string('badge_icon')->default('bi-award');
                $table->string('color', 30)->default('#00A859');
                $table->text('description')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
            });

            // Insérer les paliers par défaut
            DB::table('xp_levels')->insert([
                [
                    'name' => 'Novice Tech 🌱',
                    'min_xp' => 0,
                    'badge_icon' => 'bi-seedling',
                    'color' => '#10B981',
                    'description' => 'Bienvenue dans la communauté Tech237 ! Vos premiers pas dans l\'écosystème.',
                    'order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Apprenti Codeur 💻',
                    'min_xp' => 20,
                    'badge_icon' => 'bi-laptop',
                    'color' => '#3B82F6',
                    'description' => 'Vous commencez à interagir avec des commentaires et des votes sur les projets.',
                    'order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Codeur Actif ⚡',
                    'min_xp' => 50,
                    'badge_icon' => 'bi-lightning-charge-fill',
                    'color' => '#F59E0B',
                    'description' => 'Un membre dynamique qui propose des mini-actualités (POCs) et participe aux débats.',
                    'order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Super Contributeur 🚀',
                    'min_xp' => 100,
                    'badge_icon' => 'bi-rocket-takeoff-fill',
                    'color' => '#EC4899',
                    'description' => 'Pilier de la communauté, créateur de projets tech et résolveur de problèmes forum.',
                    'order' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Tech Builder 🛠️',
                    'min_xp' => 250,
                    'badge_icon' => 'bi-tools',
                    'color' => '#8B5CF6',
                    'description' => 'Bâtisseur chevronné qui livre des projets complets et documente ses roadmaps.',
                    'order' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Tech Lead 🌟',
                    'min_xp' => 500,
                    'badge_icon' => 'bi-star-fill',
                    'color' => '#06B6D4',
                    'description' => 'Leader technique respecté, mentor pour les nouveaux arrivants et créateur prolifique.',
                    'order' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Légende Tech237 👑',
                    'min_xp' => 1000,
                    'badge_icon' => 'bi-trophy-fill',
                    'color' => '#EAB308',
                    'description' => 'Le sommet de l\'écosystème tech camerounais. Héros incontesté de Tech237 !',
                    'order' => 7,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 2. Champ pending_level_up sur la table users (pour la modale de célébration sonore)
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'pending_level_up')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('pending_level_up')->nullable()->after('rank_title');
            });
        }

        // 3. Champ overdue_alert_sent_at sur kanban_cards (pour l'alerte email automatique de retard)
        if (Schema::hasTable('kanban_cards') && !Schema::hasColumn('kanban_cards', 'overdue_alert_sent_at')) {
            Schema::table('kanban_cards', function (Blueprint $table) {
                $table->timestamp('overdue_alert_sent_at')->nullable()->after('due_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kanban_cards') && Schema::hasColumn('kanban_cards', 'overdue_alert_sent_at')) {
            Schema::table('kanban_cards', function (Blueprint $table) {
                $table->dropColumn('overdue_alert_sent_at');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'pending_level_up')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('pending_level_up');
            });
        }

        Schema::dropIfExists('xp_levels');
    }
};
