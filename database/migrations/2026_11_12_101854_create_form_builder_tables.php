<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('display_mode')->default('classic'); // 'classic' or 'conversation'
            $table->string('cover_image')->nullable();
            $table->string('theme_color')->default('#00A859');
            $table->string('status')->default('published'); // 'published', 'draft', 'closed'
            $table->boolean('requires_login')->default(false);
            $table->boolean('one_response_per_user')->default(false);
            $table->unsignedInteger('max_responses')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('notify_email_on_response')->default(true);
            $table->string('notification_email')->nullable();
            $table->boolean('send_respondent_confirmation')->default(false);
            $table->text('confirmation_message')->nullable();
            $table->string('redirect_url')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->string('label');
            $table->string('name');
            $table->string('type')->default('text');
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->integer('order')->default(0);
            $table->json('conditional_logic')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('form_responses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('respondent_email')->nullable();
            $table->json('answers');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedInteger('completed_in_seconds')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_responses');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
