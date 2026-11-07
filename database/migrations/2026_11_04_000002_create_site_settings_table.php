<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Tech237');
            $table->string('logo')->nullable();
            $table->string('seo_image')->nullable();
            $table->string('po_box')->nullable();
            $table->string('primary_email')->default('contact@tech237.com');
            $table->string('secondary_email')->nullable();
            $table->string('whatsapp_phone')->default('+237 699329171');
            $table->string('secondary_phone')->nullable();
            $table->string('city')->default('Yaoundé');
            $table->string('location')->default('Yaoundé, Cameroun');
            
            // Meta & SEO
            $table->string('meta_keywords_fr')->nullable();
            $table->string('meta_keywords_en')->nullable();
            $table->text('meta_description_fr')->nullable();
            $table->text('meta_description_en')->nullable();

            // Social Networks
            $table->string('social_facebook')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_telegram')->nullable();

            // Support & Donations
            $table->string('orange_money_name')->nullable();
            $table->string('orange_money_phone')->nullable();
            $table->string('mtn_money_name')->nullable();
            $table->string('mtn_money_phone')->nullable();
            $table->text('rib_details')->nullable();

            // Legal Pages Content
            $table->longText('terms_of_service_fr')->nullable();
            $table->longText('terms_of_service_en')->nullable();
            $table->longText('privacy_policy_fr')->nullable();
            $table->longText('privacy_policy_en')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
