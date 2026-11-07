<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('interviewee_name');
            $table->string('interviewee_role')->nullable();
            $table->string('title_fr');
            $table->string('title_en')->nullable();
            $table->string('slug')->unique();
            $table->string('cover_image')->nullable();
            $table->string('video_url')->nullable();
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
        Schema::dropIfExists('interviews');
    }
};
