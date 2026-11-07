<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Comments table
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('article_id')->nullable()->constrained('articles')->cascadeOnDelete();
            $table->foreignId('interview_id')->nullable()->constrained('interviews')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->text('content');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });

        // User Messages table
        Schema::create('user_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Admin socials
        Schema::table('admins', function (Blueprint $table) {
            $table->string('social_twitter')->nullable()->after('bio');
            $table->string('social_linkedin')->nullable()->after('social_twitter');
            $table->string('social_facebook')->nullable()->after('social_linkedin');
            $table->string('social_instagram')->nullable()->after('social_facebook');
            $table->string('social_tiktok')->nullable()->after('social_instagram');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'social_twitter',
                'social_linkedin',
                'social_facebook',
                'social_instagram',
                'social_tiktok',
            ]);
        });
        Schema::dropIfExists('user_messages');
        Schema::dropIfExists('comments');
    }
};
