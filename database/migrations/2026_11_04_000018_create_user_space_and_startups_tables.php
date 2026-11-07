<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('gender')->nullable()->after('first_name'); // Homme, Femme, Autre
            $table->string('country_code', 10)->default('+237')->after('gender');
            $table->string('phone')->nullable()->after('country_code');
            $table->string('avatar')->nullable()->after('email');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('status')->default('active')->after('bio'); // active, banned
            $table->text('ban_reason')->nullable()->after('status');
            $table->date('birth_date')->nullable()->after('ban_reason');
            $table->string('marital_status')->nullable()->after('birth_date'); // Célibataire, Marié(e), Autre
            $table->timestamp('registered_at')->nullable()->after('marital_status');
        });

        // Startups (Cameroon/OHADA compliance: SARL, SA, SAS, EURL, ETS, GIE, RCCM, NIU)
        Schema::create('startups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('legal_type')->default('SARL'); // SARL, SA, SAS, EURL, ETS, GIE
            $table->string('city')->default('Douala');
            $table->string('location')->nullable();
            $table->integer('employee_count')->default(1);
            $table->boolean('needs_funding')->default(false);
            $table->decimal('funding_amount', 15, 2)->nullable();
            $table->string('rccm_number')->nullable();
            $table->string('niu_number')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending_validation'); // pending_validation, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        // User Legal & Verification Documents (CNI Recto/Verso, RCCM, NIU, etc.)
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('startup_id')->nullable()->constrained('startups')->nullOnDelete();
            $table->string('type'); // cni_recto, cni_verso, rccm_cert, tax_niu, other
            $table->string('file_path');
            $table->string('title')->nullable();
            $table->timestamps();
        });

        // Projects (Web, Mobile, Desktop, Game)
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('startup_id')->nullable()->constrained('startups')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('project_type'); // web, mobile, desktop, game
            $table->json('app_types')->nullable();
            $table->json('game_types')->nullable();
            $table->json('languages')->nullable();
            $table->json('frameworks_engines')->nullable();
            $table->string('development_duration')->nullable();
            $table->longText('description');
            $table->json('gallery')->nullable();
            $table->json('youtube_videos')->nullable();
            $table->string('website_url')->nullable();
            $table->string('playstore_url')->nullable();
            $table->string('appstore_url')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('status')->default('published');
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });

        // Devlog / Problem Resolution Timeline (GitHub-style)
        Schema::create('project_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('problem_encountered')->nullable();
            $table->text('solution_applied')->nullable();
            $table->json('images')->nullable();
            $table->string('youtube_url')->nullable();
            $table->date('event_date')->nullable();
            $table->timestamps();
        });

        // Disputes & Moderation
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_type'); // project, startup, comment, user
            $table->unsignedBigInteger('target_id');
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('status')->default('pending'); // pending, resolved, dismissed
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('project_timelines');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('user_documents');
        Schema::dropIfExists('startups');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'gender', 'country_code', 'phone', 'avatar',
                'bio', 'status', 'ban_reason', 'birth_date', 'marital_status', 'registered_at'
            ]);
        });
    }
};
