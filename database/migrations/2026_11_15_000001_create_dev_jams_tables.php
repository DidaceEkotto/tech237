<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dev_jams', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('theme')->nullable();
            $table->text('description');
            $table->text('rules')->nullable();
            $table->enum('mode', ['official', 'community'])->default('official');
            $table->enum('status', ['pending_validation', 'upcoming', 'active', 'voting', 'ended', 'rejected'])->default('active');
            $table->foreignId('creator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creator_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('prize_text')->nullable();
            $table->boolean('allow_external_links')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('dev_jam_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dev_jam_id')->constrained('dev_jams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('submission_type', ['studio', 'external'])->default('studio');
            $table->longText('html_code')->nullable();
            $table->longText('css_code')->nullable();
            $table->longText('js_code')->nullable();
            $table->string('external_repo_url')->nullable();
            $table->string('external_demo_url')->nullable();
            $table->unsignedInteger('votes_count')->default(0);
            $table->boolean('is_winner')->default(false);
            $table->unsignedInteger('rank_position')->nullable();
            $table->timestamps();

            $table->unique(['dev_jam_id', 'user_id']);
        });

        Schema::create('dev_jam_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dev_jam_id')->constrained('dev_jams')->cascadeOnDelete();
            $table->foreignId('submission_id')->constrained('dev_jam_submissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['dev_jam_id', 'submission_id', 'user_id']);
        });

        Schema::create('dev_jam_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dev_jam_id')->constrained('dev_jams')->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained('dev_jam_submissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['dev_jam_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dev_jam_messages');
        Schema::dropIfExists('dev_jam_votes');
        Schema::dropIfExists('dev_jam_submissions');
        Schema::dropIfExists('dev_jams');
    }
};
