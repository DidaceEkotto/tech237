<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('position')->default('sidebar'); // header_top, sidebar, in_article, home_middle, footer_banner
            $table->string('image')->nullable();
            $table->string('link_url')->nullable();
            $table->text('custom_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('clicks_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_banners');
    }
};
