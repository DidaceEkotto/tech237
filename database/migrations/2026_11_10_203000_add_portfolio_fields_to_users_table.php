<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 100)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'headline')) {
                $table->string('headline', 255)->nullable()->after('username');
            }
            if (!Schema::hasColumn('users', 'location')) {
                $table->string('location', 150)->nullable()->after('headline');
            }
            if (!Schema::hasColumn('users', 'availability')) {
                $table->string('availability', 50)->default('open')->after('location');
            }
            if (!Schema::hasColumn('users', 'skills')) {
                $table->json('skills')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'social_links')) {
                $table->json('social_links')->nullable()->after('skills');
            }
            if (!Schema::hasColumn('users', 'experiences')) {
                $table->json('experiences')->nullable()->after('social_links');
            }
            if (!Schema::hasColumn('users', 'educations')) {
                $table->json('educations')->nullable()->after('experiences');
            }
            if (!Schema::hasColumn('users', 'custom_sections')) {
                $table->json('custom_sections')->nullable()->after('educations');
            }
            if (!Schema::hasColumn('users', 'portfolio_layout')) {
                $table->json('portfolio_layout')->nullable()->after('custom_sections');
            }
            if (!Schema::hasColumn('users', 'portfolio_published')) {
                $table->boolean('portfolio_published')->default(true)->after('portfolio_layout');
            }
            if (!Schema::hasColumn('users', 'portfolio_views')) {
                $table->unsignedBigInteger('portfolio_views')->default(0)->after('portfolio_published');
            }
        });

        // Initialize default username and layout for existing users
        $defaultLayout = json_encode([
            'theme' => 'emerald',
            'card_style' => 'rounded',
            'accent_color' => '#00A859',
            'hero_style' => 'centered',
            'projects_style' => 'cards',
            'skills_style' => 'chips',
            'blocks' => [
                ['id' => 'hero', 'name' => 'En-tête & Présentation', 'enabled' => true],
                ['id' => 'about', 'name' => 'À propos & Parcours', 'enabled' => true],
                ['id' => 'skills', 'name' => 'Compétences & Stack technique', 'enabled' => true],
                ['id' => 'projects', 'name' => 'Projets Tech en vedette', 'enabled' => true],
                ['id' => 'pocs', 'name' => 'Mini-Actus & POCs Tech237', 'enabled' => true],
                ['id' => 'experiences', 'name' => 'Expériences professionnelles', 'enabled' => true],
                ['id' => 'educations', 'name' => 'Formations & Diplômes', 'enabled' => true],
                ['id' => 'badges', 'name' => 'Trophées & Badges Tech237', 'enabled' => true],
                ['id' => 'custom', 'name' => 'Sections personnalisées libres', 'enabled' => true],
                ['id' => 'contact', 'name' => 'Coordonnées & Réseaux', 'enabled' => true],
            ],
        ]);

        $users = DB::table('users')->whereNull('username')->orWhere('username', '')->get(['id', 'name']);
        $usedUsernames = [];

        foreach ($users as $u) {
            $base = Str::slug($u->name ?: 'dev');
            if (empty($base)) {
                $base = 'dev';
            }
            $candidate = $base;
            $counter = 1;
            while (in_array($candidate, $usedUsernames) || DB::table('users')->where('username', $candidate)->where('id', '!=', $u->id)->exists()) {
                $candidate = $base . $counter;
                $counter++;
            }
            $usedUsernames[] = $candidate;

            DB::table('users')->where('id', $u->id)->update([
                'username' => $candidate,
                'portfolio_published' => true,
                'portfolio_layout' => $defaultLayout,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'username',
                'headline',
                'location',
                'availability',
                'skills',
                'social_links',
                'experiences',
                'educations',
                'custom_sections',
                'portfolio_layout',
                'portfolio_published',
                'portfolio_views',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
