<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update rubriques & dossiers
        Schema::table('rubriques', function (Blueprint $table) {
            $table->json('options')->nullable()->after('icon'); // styling options: card_style, banner_style, accent_color, badges, grid_layout
        });

        Schema::table('dossiers', function (Blueprint $table) {
            $table->foreignId('rubrique_id')->nullable()->after('id')->constrained('rubriques')->nullOnDelete();
        });

        // 2. Update interviews for Q&A blocks
        Schema::table('interviews', function (Blueprint $table) {
            $table->json('qa_blocks')->nullable()->after('content_en');
        });

        // 3. Entrepreneurs of the month
        Schema::create('entrepreneurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->nullable()->constrained('startups')->nullOnDelete();
            $table->string('name');
            $table->string('role_title')->default('CEO & Fondateur');
            $table->string('photo')->nullable();
            $table->string('month_year')->default('Novembre 2026');
            $table->text('bio_fr')->nullable();
            $table->text('bio_en')->nullable();
            $table->longText('story_fr')->nullable();
            $table->longText('story_en')->nullable();
            $table->longText('difficulties_fr')->nullable();
            $table->longText('difficulties_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Programming Languages & Frameworks
        Schema::create('programming_languages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color')->nullable()->default('#FF004B');
            $table->timestamps();
        });

        Schema::create('frameworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programming_language_id')->constrained('programming_languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('project_programming_language', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('programming_language_id')->constrained('programming_languages')->cascadeOnDelete();
        });

        Schema::create('framework_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('framework_id')->constrained('frameworks')->cascadeOnDelete();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->json('features')->nullable()->after('description');
            $table->string('youtube_presentation_url')->nullable()->after('features');
            $table->string('appgallery_url')->nullable()->after('appstore_url');
        });

        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('project_timelines', function (Blueprint $table) {
            $table->json('feature_ids')->nullable()->after('solution_applied');
        });

        // 5. Conferences & Events
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('title_fr');
            $table->string('title_en')->nullable();
            $table->string('slug')->unique();
            $table->string('cover_image')->nullable();
            $table->date('event_date');
            $table->date('end_date')->nullable();
            $table->date('deadline_registration')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->default('Douala');
            $table->longText('description_fr')->nullable();
            $table->longText('description_en')->nullable();
            $table->boolean('accepts_registrations')->default(false);
            $table->string('status')->default('pending_validation'); // pending_validation, approved, rejected
            $table->boolean('is_official_admin')->default(false);
            $table->timestamps();
        });

        Schema::create('conference_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('conferences')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('city')->nullable();
            $table->string('email');
            $table->string('phone_whatsapp')->nullable();
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        // 6. Job Offers, Questions, Applications & Interviews
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->json('domain')->nullable(); // IT domains
            $table->string('contract_type')->default('cdi'); // cdi, cdd, stage_pro, stage_pre_emploi, freelance
            $table->string('salary_type')->default('negotiable'); // negotiable, fixed
            $table->decimal('salary_amount', 12, 2)->nullable();
            $table->string('city')->default('Douala');
            $table->boolean('accepts_cv')->default(true);
            $table->boolean('accepts_cover_letter')->default(false);
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->date('deadline_date')->nullable();
            $table->boolean('has_online_test')->default(false);
            $table->string('status')->default('pending_validation'); // pending_validation, approved, rejected
            $table->timestamps();
        });

        Schema::create('job_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->constrained('job_offers')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_type')->default('text'); // text, qcm
            $table->json('options')->nullable(); // for QCM
            $table->text('correct_answer')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->constrained('job_offers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('cv_file')->nullable();
            $table->string('cover_letter_file')->nullable();
            $table->longText('presentation_text')->nullable();
            $table->json('test_answers')->nullable();
            $table->integer('test_score')->nullable();
            $table->string('status')->default('submitted'); // submitted, under_review, interview_scheduled, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('job_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->date('interview_date');
            $table->string('interview_time');
            $table->string('location');
            $table->text('notes')->nullable();
            $table->boolean('is_sent')->default(true);
            $table->timestamps();
        });

        // 7. Forum Module
        Schema::create('forum_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('bi-chat-square-text');
            $table->string('color')->default('#FF004B');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_category_id')->constrained('forum_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->integer('views_count')->default(0);
            $table->boolean('is_resolved')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_topic_id')->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->longText('content');
            $table->integer('likes_count')->default(0);
            $table->timestamps();
        });

        Schema::create('forum_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained('forum_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['forum_post_id', 'user_id']);
        });

        Schema::create('forum_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('forum_topic_id')->nullable()->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('forum_post_id')->nullable()->constrained('forum_posts')->cascadeOnDelete();
            $table->text('reason');
            $table->string('status')->default('pending'); // pending, resolved, dismissed
            $table->timestamps();
        });

        // 8. Password reset requests tracking
        Schema::create('custom_password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('token');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_password_resets');
        Schema::dropIfExists('forum_reports');
        Schema::dropIfExists('forum_likes');
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_topics');
        Schema::dropIfExists('forum_categories');
        Schema::dropIfExists('job_interviews');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_questions');
        Schema::dropIfExists('job_offers');
        Schema::dropIfExists('conference_registrations');
        Schema::dropIfExists('conferences');
        Schema::dropIfExists('project_images');
        Schema::dropIfExists('framework_project');
        Schema::dropIfExists('project_programming_language');
        Schema::dropIfExists('frameworks');
        Schema::dropIfExists('programming_languages');
        Schema::dropIfExists('entrepreneurs');
        Schema::table('project_timelines', function (Blueprint $table) {
            $table->dropColumn('feature_ids');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['features', 'youtube_presentation_url', 'appgallery_url']);
        });
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropColumn('qa_blocks');
        });
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropForeign(['rubrique_id']);
            $table->dropColumn('rubrique_id');
        });
        Schema::table('rubriques', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
