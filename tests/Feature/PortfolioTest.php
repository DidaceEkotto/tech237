<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Poc;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_portfolio_section_in_dashboard()
    {
        $user = User::factory()->create([
            'username' => 'testdev',
        ]);

        $response = $this->actingAs($user, 'web')->get('/espace-utilisateur?section=portfolio');
        $response->assertStatus(200);
    }

    public function test_user_can_save_portfolio_customization_and_blocks()
    {
        $user = User::factory()->create([
            'username' => 'coder237',
        ]);

        $payload = [
            'username' => 'coder-cm',
            'headline' => 'Senior Fullstack Architect',
            'location' => 'Douala, Cameroun',
            'availability' => 'freelance',
            'bio' => 'Passionné par Laravel, Vue.js et l\'écosystème tech africain.',
            'skills' => [
                ['name' => 'Laravel', 'category' => 'Backend', 'level' => 'Expert'],
                ['name' => 'Vue.js', 'category' => 'Frontend', 'level' => 'Avancé'],
            ],
            'social_links' => [
                'github' => 'https://github.com/coder-cm',
                'linkedin' => 'https://linkedin.com/in/coder-cm',
            ],
            'portfolio_layout' => [
                'theme' => 'dark',
                'card_style' => 'sharp',
                'layout_structure' => 'bento',
                'font_family' => 'mono',
                'bg_pattern' => 'grid',
                'accent_color' => '#10B981',
                'hero_style' => 'terminal',
                'projects_style' => 'cards',
                'skills_style' => 'chips',
                'blocks' => [
                    ['id' => 'hero', 'name' => 'En-tête', 'enabled' => true, 'width' => 'full'],
                    ['id' => 'projects', 'name' => 'Projets', 'enabled' => true, 'width' => 'full'],
                    ['id' => 'skills', 'name' => 'Compétences', 'enabled' => true, 'width' => 'half'],
                ],
            ],
            'portfolio_published' => true,
        ];

        $response = $this->actingAs($user, 'web')->postJson('/espace-utilisateur/portfolio/save', $payload);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $user->refresh();
        $this->assertEquals('coder-cm', $user->username);
        $this->assertEquals('Senior Fullstack Architect', $user->headline);
        $this->assertEquals('dark', $user->portfolio_layout['theme']);
        $this->assertEquals('terminal', $user->portfolio_layout['hero_style']);
    }

    public function test_public_portfolio_accessible_via_username_and_og_tags()
    {
        $user = User::factory()->create([
            'name' => 'Alex Foko',
            'username' => 'alex-foko',
            'headline' => 'Développeur Mobile Flutter',
            'portfolio_published' => true,
            'portfolio_views' => 0,
        ]);

        // Public route /@alex-foko
        $responseShort = $this->get('/@alex-foko');
        $responseShort->assertStatus(200);

        // Public route /portfolio/alex-foko
        $responseLong = $this->get('/portfolio/alex-foko');
        $responseLong->assertStatus(200);

        // Public route /cv/alex-foko
        $responseCv = $this->get('/cv/alex-foko');
        $responseCv->assertStatus(200);

        // Assert view counter incremented
        $this->assertGreaterThan(0, $user->fresh()->portfolio_views);
    }

    public function test_user_can_upload_custom_background_image()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create([
            'username' => 'artist237',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('custom_bg.png', 1200, 800);

        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/portfolio/upload-bg', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_user_can_save_extended_customization_including_snow_tabs_and_preloader()
    {
        $user = User::factory()->create([
            'username' => 'fullcustom',
        ]);

        $payload = [
            'username' => 'fullcustom',
            'headline' => 'Tech Lead & CTO',
            'portfolio_layout' => [
                'theme' => 'cyber',
                'ambient_effect' => 'snow',
                'years_of_experience' => '+6 ans',
                'projects_columns' => 3,
                'projects_style' => 'carousel',
                'page_animation' => 'slide_up',
                'tabbed_nav' => [
                    'enabled' => true,
                    'style' => 'pills',
                    'default_tab' => 'skills',
                ],
                'preloader' => [
                    'enabled' => true,
                    'style' => 'terminal',
                    'text' => 'Booting workspace...',
                    'duration' => 1.5,
                ],
                'footer' => [
                    'custom_text' => '<b>© 2026 Dev</b>',
                    'show_socials' => true,
                    'show_tech237_badge' => true,
                    'show_back_to_top' => true,
                ],
            ],
            'skills' => [
                ['name' => 'Docker', 'category' => 'DevOps', 'level' => 'Expert'],
                ['name' => 'Go', 'category' => 'Backend', 'level' => 'Avancé'],
            ],
            'portfolio_published' => true,
        ];

        $response = $this->actingAs($user, 'web')->postJson('/espace-utilisateur/portfolio/save', $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user->refresh();
        $this->assertEquals('snow', $user->portfolio_layout['ambient_effect']);
        $this->assertEquals('+6 ans', $user->portfolio_layout['years_of_experience']);
        $this->assertEquals('carousel', $user->portfolio_layout['projects_style']);
        $this->assertEquals(3, $user->portfolio_layout['projects_columns']);
        $this->assertTrue($user->portfolio_layout['tabbed_nav']['enabled']);
        $this->assertEquals('Expert', $user->skills[0]['level']);
    }

    public function test_user_can_upload_cv_document()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create([
            'username' => 'cvdev237',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('mon_cv_technique.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/portfolio/upload-cv', [
            'cv' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertNotEmpty($response->json('url'));
    }

    public function test_user_can_upload_slideshow_image()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create([
            'username' => 'slidedev',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('banner_slide.jpg', 1600, 600);

        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/portfolio/upload-slide', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Test with 'slide' key as well
        $file2 = \Illuminate\Http\UploadedFile::fake()->image('banner_slide_2.jpg', 1600, 600);
        $response2 = $this->actingAs($user, 'web')->post('/espace-utilisateur/portfolio/upload-slide', [
            'slide' => $file2,
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
        ]);
    }

    public function test_user_can_upload_preloader_image()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create([
            'username' => 'preloaderdev',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar_logo.png', 200, 200);

        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/portfolio/upload-preloader-image', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_visitor_can_submit_contact_form()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $owner = User::factory()->create([
            'username' => 'targetdev',
            'email' => 'targetdev@tech237.cm',
        ]);

        $payload = [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@partner.cm',
            'subject' => 'Opportunité Mission Freelance',
            'message' => 'Bonjour, nous souhaitons vous confier le développement d\'une plateforme SaaS.',
        ];

        $response = $this->postJson('/portfolio/targetdev/contact', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
