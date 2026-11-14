<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_published')->default(false);
            $table->json('global_settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('portfolio_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_site_id')->constrained('portfolio_sites')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->boolean('is_homepage')->default(false);
            $table->json('draft_tree')->nullable();
            $table->json('published_tree')->nullable();
            $table->json('seo_meta')->nullable();
            $table->string('status', 32)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['portfolio_site_id', 'slug']);
        });

        Schema::create('portfolio_page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_page_id')->constrained('portfolio_pages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('tree_snapshot');
            $table->string('label', 120)->default('Autosave');
            $table->timestamps();
        });

        Schema::create('portfolio_theme_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_site_id')->constrained('portfolio_sites')->cascadeOnDelete();
            $table->string('type', 32); // header, footer, menu, template_404
            $table->string('name');
            $table->json('tree')->nullable();
            $table->json('display_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('portfolio_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('category', 64)->default('general');
            $table->string('scope', 32)->default('section'); // section, row, module, page, site
            $table->string('thumbnail_url')->nullable();
            $table->json('tree');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('portfolio_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portfolio_site_id')->nullable()->constrained('portfolio_sites')->nullOnDelete();
            $table->string('folder', 100)->default('root');
            $table->string('filename');
            $table->string('path');
            $table->string('url');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_media');
        Schema::dropIfExists('portfolio_templates');
        Schema::dropIfExists('portfolio_theme_parts');
        Schema::dropIfExists('portfolio_page_revisions');
        Schema::dropIfExists('portfolio_pages');
        Schema::dropIfExists('portfolio_sites');
    }
};
