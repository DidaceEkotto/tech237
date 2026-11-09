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
        // 1. Table POCs (Mini-actualités communautaires)
        if (!Schema::hasTable('pocs')) {
            Schema::create('pocs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('author_name')->nullable();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('summary')->nullable();
                $table->longText('content'); // WYSIWYG formatted HTML
                $table->string('cover_image')->nullable();
                $table->string('link_url', 500)->nullable();
                $table->string('tag', 50)->default('general')->index(); // ia, fintech, dev, startup, cybersec, mobile, general
                $table->string('status', 20)->default('published')->index(); // published, draft, archived
                $table->unsignedInteger('upvotes_count')->default(0)->index();
                $table->unsignedInteger('views_count')->default(0);
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamps();
            });
        }

        // 2. Table POC Upvotes (Anti-doublon pour auth et invités)
        if (!Schema::hasTable('poc_upvotes')) {
            Schema::create('poc_upvotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poc_id')->constrained('pocs')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('guest_token', 64)->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->unique(['poc_id', 'user_id']);
                $table->index(['poc_id', 'guest_token']);
                $table->index(['poc_id', 'ip_address']);
            });
        }

        // 3. Liaison commentaires avec les POCs si la table comments existe
        if (Schema::hasTable('comments') && !Schema::hasColumn('comments', 'poc_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->foreignId('poc_id')->nullable()->after('project_id')->constrained('pocs')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('comments') && Schema::hasColumn('comments', 'poc_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropForeign(['poc_id']);
                $table->dropColumn('poc_id');
            });
        }

        Schema::dropIfExists('poc_upvotes');
        Schema::dropIfExists('pocs');
    }
};
