<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('app_name');
            $table->string('slug')->unique();
            $table->string('category_name'); // Google Playstore, App Store, Web App, Desktop, Mac, Windows
            $table->string('category_logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('score_ui', 4, 1)->default(15.0); // note UI /20
            $table->decimal('score_ux', 4, 1)->default(15.0); // note UX /20
            $table->decimal('score_handling', 4, 1)->default(15.0); // note prise en main /20
            $table->decimal('final_score', 4, 1)->default(15.0); // note finale sur 20
            $table->string('playstore_url')->nullable();
            $table->string('appstore_url')->nullable();
            $table->text('summary_fr')->nullable();
            $table->text('summary_en')->nullable();
            $table->longText('content_fr');
            $table->longText('content_en')->nullable();
            $table->string('status')->default('published');
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_reviews');
    }
};
