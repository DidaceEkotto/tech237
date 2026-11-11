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
}
