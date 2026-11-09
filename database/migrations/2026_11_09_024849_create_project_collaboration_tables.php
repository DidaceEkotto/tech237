<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Project Invitations
        Schema::create('project_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invited_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->string('role')->default('collaborator');
            $table->text('message')->nullable();
            $table->string('status')->default('pending')->index(); // pending, accepted, declined, expired
            $table->dateTime('expires_at');
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->timestamps();
        });

        // 2. Project Collaborators
        Schema::create('project_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('project_invitations')->nullOnDelete();
            $table->string('role')->default('collaborator');
            $table->string('status')->default('active')->index(); // active, revoked
            $table->dateTime('joined_at');
            $table->dateTime('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        // 3. Collaboration Blocks ("Je ne souhaite jamais collaborer avec vous")
        Schema::create('collaboration_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('blocked_email')->nullable()->index();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 4. Update project_timelines for collaborator validation
        Schema::table('project_timelines', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
            $table->string('status')->default('approved')->after('event_date')->index(); // approved, pending, rejected
        });

        // 5. Update project_images for collaborator validation
        Schema::table('project_images', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
            $table->string('status')->default('approved')->after('caption')->index(); // approved, pending, rejected
        });
    }

    public function down(): void
    {
        Schema::table('project_images', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'status']);
        });

        Schema::table('project_timelines', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'status']);
        });

        Schema::dropIfExists('collaboration_blocks');
        Schema::dropIfExists('project_collaborators');
        Schema::dropIfExists('project_invitations');
    }
};
