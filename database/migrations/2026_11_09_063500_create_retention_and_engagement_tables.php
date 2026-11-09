<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Projects Upvotes (Product Hunt Tech237)
        if (!Schema::hasTable('project_upvotes')) {
            Schema::create('project_upvotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['project_id', 'user_id']);
            });
        }

        if (!Schema::hasColumn('projects', 'upvotes_count')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->unsignedInteger('upvotes_count')->default(0)->after('views_count')->index();
            });
        }

        // 2. Daily Tech Polls (Sondage interactif quotidien)
        if (!Schema::hasTable('tech_polls')) {
            Schema::create('tech_polls', function (Blueprint $table) {
                $table->id();
                $table->string('question');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tech_poll_options')) {
            Schema::create('tech_poll_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tech_poll_id')->constrained('tech_polls')->cascadeOnDelete();
                $table->string('label');
                $table->unsignedInteger('votes_count')->default(0);
                $table->unsignedSmallInteger('order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tech_poll_votes')) {
            Schema::create('tech_poll_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tech_poll_id')->constrained('tech_polls')->cascadeOnDelete();
                $table->foreignId('tech_poll_option_id')->constrained('tech_poll_options')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                $table->index(['tech_poll_id', 'ip_address']);
            });
        }

        // 3. User Bookmarks (Favoris & Reading List)
        if (!Schema::hasTable('user_bookmarks')) {
            Schema::create('user_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('bookmarkable_type'); // article, game_review, app_review, project, job
                $table->unsignedBigInteger('bookmarkable_id');
                $table->timestamps();
                $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id']);
            });
        }

        // 4. Gamification Badges & XP
        if (!Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('badge_code'); // pioneer, devlog_master, top_contributor, verified_creator, tech_enthusiast
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('icon')->default('bi-award-fill');
                $table->string('color')->default('#00A859');
                $table->string('awarded_by')->default('system'); // system or admin
                $table->timestamps();
                $table->unique(['user_id', 'badge_code']);
            });
        }

        if (!Schema::hasColumn('users', 'xp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('xp')->default(10)->after('ban_reason');
                $table->string('rank_title')->default('Novice Tech')->after('xp');
            });
        }

        // 5. User Alert Preferences
        if (!Schema::hasTable('user_alert_preferences')) {
            Schema::create('user_alert_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('alert_jobs')->default(true);
                $table->boolean('alert_conferences')->default(true);
                $table->boolean('alert_projects')->default(true);
                $table->boolean('alert_newsletter')->default(true);
                $table->timestamps();
                $table->unique('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_alert_preferences');
        if (Schema::hasColumn('users', 'xp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['xp', 'rank_title']);
            });
        }
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('user_bookmarks');
        Schema::dropIfExists('tech_poll_votes');
        Schema::dropIfExists('tech_poll_options');
        Schema::dropIfExists('tech_polls');
        if (Schema::hasColumn('projects', 'upvotes_count')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('upvotes_count');
            });
        }
        Schema::dropIfExists('project_upvotes');
    }
};
