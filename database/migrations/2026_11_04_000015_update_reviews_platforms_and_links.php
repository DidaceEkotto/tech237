<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_reviews', function (Blueprint $table) {
            $table->json('platforms')->nullable()->after('category_name');
            $table->string('website_url')->nullable()->after('appstore_url');
            $table->string('appgallery_url')->nullable()->after('website_url');
        });

        Schema::table('game_reviews', function (Blueprint $table) {
            $table->json('platforms')->nullable()->after('platform_category');
        });
    }

    public function down(): void
    {
        Schema::table('app_reviews', function (Blueprint $table) {
            $table->dropColumn(['platforms', 'website_url', 'appgallery_url']);
        });

        Schema::table('game_reviews', function (Blueprint $table) {
            $table->dropColumn(['platforms']);
        });
    }
};
