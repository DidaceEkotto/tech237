<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::updateOrCreate(
            ['id' => 1],
            [
                'site_name' => 'Tech237',
                'logo' => 'assets/images/logo.png',
                'seo_image' => 'assets/images/logo.png',
                'primary_email' => 'contact@tech237.cm',
                'secondary_email' => 'support@tech237.cm',
                'whatsapp_phone' => '+237 690 00 00 00',
                'secondary_phone' => '+237 670 00 00 00',
                'city' => 'Douala',
                'location' => 'Douala, Cameroun',
                'meta_keywords_fr' => 'tech237, cameroun, tech, startups, afrique, innovation, emplois it, dev',
                'meta_keywords_en' => 'tech237, cameroon, tech, startups, africa, innovation, it jobs, dev',
                'meta_description_fr' => 'Tech237 - Là où les idées deviennent des projets. Le média tech, innovation et startups de référence au Cameroun.',
                'meta_description_en' => 'Tech237 - Where ideas become projects. The leading tech, innovation and startup media in Cameroon.',
                'social_facebook' => 'https://facebook.com/tech237cm',
                'social_twitter' => 'https://twitter.com/tech237_cm',
                'social_instagram' => 'https://instagram.com/tech237_cm',
                'social_tiktok' => 'https://tiktok.com/@tech237',
                'social_youtube' => 'https://youtube.com/@tech237',
                'social_telegram' => 'https://t.me/tech237',
                'orange_money_name' => 'Tech237 Media',
                'orange_money_phone' => '+237 690 00 00 00',
                'mtn_money_name' => 'Tech237 Media',
                'mtn_money_phone' => '+237 670 00 00 00',
                'snow_effect_enabled' => false,
            ]
        );
    }
}
