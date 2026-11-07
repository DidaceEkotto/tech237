<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\SiteSetting;
use App\Models\AdminLog;
use App\Services\ImageService;

class Index extends Component
{
    use WithFileUploads;

    public string $site_name = '';
    public $new_logo = null;
    public ?string $existing_logo = null;
    public $new_seo_image = null;
    public ?string $existing_seo_image = null;
    public string $po_box = '';
    public string $primary_email = '';
    public string $secondary_email = '';
    public string $whatsapp_phone = '';
    public string $secondary_phone = '';
    public string $city = '';
    public string $location = '';

    // Meta SEO
    public string $meta_keywords_fr = '';
    public string $meta_keywords_en = '';
    public string $meta_description_fr = '';
    public string $meta_description_en = '';

    // Social Links
    public string $social_facebook = '';
    public string $social_linkedin = '';
    public string $social_twitter = '';
    public string $social_instagram = '';
    public string $social_tiktok = '';
    public string $social_youtube = '';
    public string $social_telegram = '';

    // Support & Donations
    public string $orange_money_name = '';
    public string $orange_money_phone = '';
    public string $mtn_money_name = '';
    public string $mtn_money_phone = '';
    public string $rib_details = '';

    // Legal WYSIWYG
    public string $terms_of_service_fr = '';
    public string $terms_of_service_en = '';
    public string $privacy_policy_fr = '';
    public string $privacy_policy_en = '';

    // Snow Effect
    public bool $snow_effect_enabled = false;

    public function mount()
    {
        $s = SiteSetting::current();
        $this->site_name = $s->site_name;
        $this->existing_logo = $s->logo;
        $this->existing_seo_image = $s->seo_image;
        $this->po_box = $s->po_box ?? '';
        $this->primary_email = $s->primary_email;
        $this->secondary_email = $s->secondary_email ?? '';
        $this->whatsapp_phone = $s->whatsapp_phone;
        $this->secondary_phone = $s->secondary_phone ?? '';
        $this->city = $s->city;
        $this->location = $s->location;
        $this->snow_effect_enabled = (bool) $s->snow_effect_enabled;

        $this->meta_keywords_fr = $s->meta_keywords_fr ?? '';
        $this->meta_keywords_en = $s->meta_keywords_en ?? '';
        $this->meta_description_fr = $s->meta_description_fr ?? '';
        $this->meta_description_en = $s->meta_description_en ?? '';

        $this->social_facebook = $s->social_facebook ?? '';
        $this->social_linkedin = $s->social_linkedin ?? '';
        $this->social_twitter = $s->social_twitter ?? '';
        $this->social_instagram = $s->social_instagram ?? '';
        $this->social_tiktok = $s->social_tiktok ?? '';
        $this->social_youtube = $s->social_youtube ?? '';
        $this->social_telegram = $s->social_telegram ?? '';

        $this->orange_money_name = $s->orange_money_name ?? '';
        $this->orange_money_phone = $s->orange_money_phone ?? '';
        $this->mtn_money_name = $s->mtn_money_name ?? '';
        $this->mtn_money_phone = $s->mtn_money_phone ?? '';
        $this->rib_details = $s->rib_details ?? '';

        $this->terms_of_service_fr = $s->terms_of_service_fr ?? '';
        $this->terms_of_service_en = $s->terms_of_service_en ?? '';
        $this->privacy_policy_fr = $s->privacy_policy_fr ?? '';
        $this->privacy_policy_en = $s->privacy_policy_en ?? '';
    }

    public function save()
    {
        $this->validate([
            'site_name' => 'required|string|max:100',
            'primary_email' => 'required|email',
            'whatsapp_phone' => 'required|string',
            'city' => 'required|string',
            'location' => 'required|string',
        ]);

        $logoPath = $this->existing_logo;
        if ($this->new_logo) {
            $logoPath = ImageService::saveImage($this->new_logo, 'logo');
        }

        $seoPath = $this->existing_seo_image;
        if ($this->new_seo_image) {
            $seoPath = ImageService::saveImage($this->new_seo_image, 'seo');
        }

        $s = SiteSetting::current();
        $s->update([
            'site_name' => $this->site_name,
            'logo' => $logoPath,
            'seo_image' => $seoPath,
            'po_box' => $this->po_box ?: null,
            'primary_email' => $this->primary_email,
            'secondary_email' => $this->secondary_email ?: null,
            'whatsapp_phone' => $this->whatsapp_phone,
            'secondary_phone' => $this->secondary_phone ?: null,
            'city' => $this->city,
            'location' => $this->location,
            'meta_keywords_fr' => $this->meta_keywords_fr ?: null,
            'meta_keywords_en' => $this->meta_keywords_en ?: null,
            'meta_description_fr' => $this->meta_description_fr ?: null,
            'meta_description_en' => $this->meta_description_en ?: null,
            'social_facebook' => $this->social_facebook ?: null,
            'social_linkedin' => $this->social_linkedin ?: null,
            'social_twitter' => $this->social_twitter ?: null,
            'social_instagram' => $this->social_instagram ?: null,
            'social_tiktok' => $this->social_tiktok ?: null,
            'social_youtube' => $this->social_youtube ?: null,
            'social_telegram' => $this->social_telegram ?: null,
            'orange_money_name' => $this->orange_money_name ?: null,
            'orange_money_phone' => $this->orange_money_phone ?: null,
            'mtn_money_name' => $this->mtn_money_name ?: null,
            'mtn_money_phone' => $this->mtn_money_phone ?: null,
            'rib_details' => $this->rib_details ?: null,
            'terms_of_service_fr' => $this->terms_of_service_fr ?: null,
            'terms_of_service_en' => $this->terms_of_service_en ?: null,
            'privacy_policy_fr' => $this->privacy_policy_fr ?: null,
            'privacy_policy_en' => $this->privacy_policy_en ?: null,
            'snow_effect_enabled' => $this->snow_effect_enabled,
        ]);

        $this->existing_logo = $logoPath;
        $this->existing_seo_image = $seoPath;
        $this->new_logo = null;
        $this->new_seo_image = null;

        AdminLog::record('settings_updated', "Mise à jour des paramètres généraux du site");
        session()->flash('success', "Les paramètres généraux du site ont été enregistrés avec succès.");
    }

    public function toggleSnow()
    {
        $this->snow_effect_enabled = !$this->snow_effect_enabled;
        $s = SiteSetting::current();
        $s->update(['snow_effect_enabled' => $this->snow_effect_enabled]);
        $status = $this->snow_effect_enabled ? 'activée' : 'désactivée';
        AdminLog::record('settings_snow_toggled', "Effet de chute de neige {$status} sur le site");
        session()->flash('success', "Chute de neige {$status} avec succès sur le site !");
    }

    public function render()
    {
        return view('livewire.admin.settings.index')
            ->layout('layouts.admin', ['title' => 'Paramètres du site']);
    }
}
