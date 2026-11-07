<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')->update([
                'site_name' => 'Tech237',
                'logo' => 'assets/images/logo.png',
                'seo_image' => 'assets/images/logo.png',
                'primary_email' => 'contact@tech237.cm',
                'city' => 'Douala',
                'location' => 'Douala, Cameroun',
                'meta_description_fr' => 'Tech237 - Là où les idées deviennent des projets. Le média tech, innovation et startups de référence au Cameroun.',
                'meta_description_en' => 'Tech237 - Where ideas become projects. The leading tech, innovation and startup media in Cameroon.',
                'meta_keywords_fr' => 'tech237, cameroun, tech, startups, afrique, innovation, emplois it, dev',
                'social_facebook' => 'https://facebook.com/tech237cm',
                'social_twitter' => 'https://twitter.com/tech237_cm',
                'social_instagram' => 'https://instagram.com/tech237_cm',
                'social_tiktok' => 'https://tiktok.com/@tech237',
                'social_youtube' => 'https://youtube.com/@tech237',
                'orange_money_name' => 'Tech237 Media',
                'mtn_money_name' => 'Tech237 Media',
            ]);
        }
    }

    public function down(): void
    {
    }
};
