<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'logo',
        'seo_image',
        'po_box',
        'primary_email',
        'secondary_email',
        'whatsapp_phone',
        'secondary_phone',
        'city',
        'location',
        'meta_keywords_fr',
        'meta_keywords_en',
        'meta_description_fr',
        'meta_description_en',
        'social_facebook',
        'social_linkedin',
        'social_twitter',
        'social_instagram',
        'social_tiktok',
        'social_youtube',
        'social_telegram',
        'orange_money_name',
        'orange_money_phone',
        'mtn_money_name',
        'mtn_money_phone',
        'rib_details',
        'terms_of_service_fr',
        'terms_of_service_en',
        'privacy_policy_fr',
        'privacy_policy_en',
        'snow_effect_enabled',
    ];

    protected $casts = [
        'snow_effect_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
                return new self([
                    'site_name' => 'Tech237',
                    'logo' => 'assets/images/logo.png',
                ]);
            }
            $setting = self::first();
            if (!$setting) {
            $setting = self::create([
                'site_name' => 'Tech237',
                'logo' => 'assets/images/logo.png',
                'seo_image' => 'assets/images/logo.png',
                'primary_email' => 'contact@tech237.cm',
                'whatsapp_phone' => '+237 690 00 00 00',
                'city' => 'Douala',
                'location' => 'Douala, Cameroun',
                'meta_description_fr' => 'Tech237 - Là où les idées deviennent des projets. Le média tech, innovation et startups de référence au Cameroun.',
                'meta_description_en' => 'Tech237 - Where ideas become projects. The leading tech, innovation and startup media in Cameroon.',
                'social_facebook' => 'https://facebook.com/tech237cm',
                'social_twitter' => 'https://twitter.com/tech237_cm',
                'social_instagram' => 'https://instagram.com/tech237_cm',
                'social_tiktok' => 'https://tiktok.com/@tech237',
                'social_youtube' => 'https://youtube.com/@tech237',
                'orange_money_name' => 'Tech237 Media',
                'orange_money_phone' => '+237 690 00 00 00',
                'mtn_money_name' => 'Tech237 Media',
                'mtn_money_phone' => '+237 670 00 00 00',
                'rib_details' => "IBAN: CM21 1000 5000 0112 3456 7890 123\nBIC/SWIFT: AFRIBICMXXX\nBanque: Afriland First Bank Douala",
                'terms_of_service_fr' => '<h2>Conditions Générales d\'Utilisation</h2><p>Bienvenue sur Tech237. En accédant à ce site, vous acceptez nos conditions d\'utilisation...</p>',
                'terms_of_service_en' => '<h2>Terms of Service</h2><p>Welcome to Tech237. By accessing our platform, you agree to our terms...</p>',
                'privacy_policy_fr' => '<h2>Politique de Confidentialité</h2><p>La protection de vos données personnelles est primordiale pour Tech237...</p>',
                'privacy_policy_en' => '<h2>Privacy Policy</h2><p>Data privacy is of utmost importance for Tech237...</p>',
            ]);
        } else {
            // Ensure logo and site_name match new Tech237 branding if still using old values
            if ($setting->site_name === 'Tech237' || empty($setting->site_name)) {
                $setting->update([
                    'site_name' => 'Tech237',
                    'logo' => 'assets/images/logo.png',
                    'seo_image' => 'assets/images/logo.png',
                    'primary_email' => 'contact@tech237.cm',
                    'city' => 'Douala',
                    'location' => 'Douala, Cameroun',
                    'meta_description_fr' => 'Tech237 - Là où les idées deviennent des projets. Le média tech, innovation et startups de référence au Cameroun.',
                ]);
            }
        }
        return $setting;
        } catch (\Throwable $e) {
            return new self([
                'site_name' => 'Tech237',
                'logo' => 'assets/images/logo.png',
            ]);
        }
    }
}
