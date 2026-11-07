<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrepreneurs', function (Blueprint $table) {
            if (!Schema::hasColumn('entrepreneurs', 'admin_id')) {
                $table->foreignId('admin_id')->nullable()->after('startup_id')->constrained('admins')->nullOnDelete();
            }
            if (!Schema::hasColumn('entrepreneurs', 'role_or_title')) {
                $table->string('role_or_title')->nullable()->after('name');
            }
            if (!Schema::hasColumn('entrepreneurs', 'journey_fr')) {
                $table->longText('journey_fr')->nullable()->after('bio_en');
            }
            if (!Schema::hasColumn('entrepreneurs', 'journey_en')) {
                $table->longText('journey_en')->nullable()->after('journey_fr');
            }
            if (!Schema::hasColumn('entrepreneurs', 'challenges_fr')) {
                $table->longText('challenges_fr')->nullable()->after('journey_en');
            }
            if (!Schema::hasColumn('entrepreneurs', 'challenges_en')) {
                $table->longText('challenges_en')->nullable()->after('challenges_fr');
            }
            if (!Schema::hasColumn('entrepreneurs', 'vision_fr')) {
                $table->longText('vision_fr')->nullable()->after('challenges_en');
            }
            if (!Schema::hasColumn('entrepreneurs', 'vision_en')) {
                $table->longText('vision_en')->nullable()->after('vision_fr');
            }
            if (!Schema::hasColumn('entrepreneurs', 'video_url')) {
                $table->string('video_url')->nullable()->after('vision_en');
            }
            if (!Schema::hasColumn('entrepreneurs', 'gallery')) {
                $table->json('gallery')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('entrepreneurs', 'social_links')) {
                $table->json('social_links')->nullable()->after('gallery');
            }
            if (!Schema::hasColumn('entrepreneurs', 'is_current_month')) {
                $table->boolean('is_current_month')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('entrepreneurs', 'is_published')) {
                $table->boolean('is_published')->default(true)->after('is_current_month');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entrepreneurs', function (Blueprint $table) {
            // Drop columns if rolling back
            $cols = ['admin_id', 'role_or_title', 'journey_fr', 'journey_en', 'challenges_fr', 'challenges_en', 'vision_fr', 'vision_en', 'video_url', 'gallery', 'social_links', 'is_current_month', 'is_published'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('entrepreneurs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
