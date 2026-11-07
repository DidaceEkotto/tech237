<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('game_name');
            $table->string('slug')->unique();
            $table->string('platform_category'); // PlayStation 5, PlayStation 4, Xbox Series S & X, Xbox One, Nintendo Switch, Nintendo Switch 2, PC, Linux, Mac, Mobile
            $table->string('platform_logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('score_gameplay', 4, 1)->default(16.0); // sur 20
            $table->decimal('score_graphics', 4, 1)->default(16.0); // sur 20
            $table->decimal('score_accessibility', 4, 1)->default(16.0); // sur 20
            $table->decimal('score_soundtrack', 4, 1)->default(16.0); // bande sonore sur 20
            $table->decimal('final_score', 4, 1)->default(16.0); // note finale sur 20
            $table->text('pros_fr')->nullable(); // Points forts
            $table->text('pros_en')->nullable();
            $table->text('cons_fr')->nullable(); // Points faibles
            $table->text('cons_en')->nullable();
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
        Schema::dropIfExists('game_reviews');
    }
};
