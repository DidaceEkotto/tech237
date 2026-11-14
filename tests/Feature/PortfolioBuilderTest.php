<?php

namespace Tests\Feature;

use App\Models\PortfolioPage;
use App\Models\PortfolioSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_site_and_save_sanitized_page_draft_and_publish(): void
    {
        $user = User::factory()->create([
            'username' => 'builderuser',
            'portfolio_published' => true,
        ]);

        // 1. Create a site
        $createResp = $this->actingAs($user)->postJson('/espace-utilisateur/portfolio-builder/sites', [
            'name' => 'Mon Studio Pro',
            'description' => 'Site de test',
        ]);

        $createResp->assertCreated();
        $siteId = $createResp->json('site.id');
        $pageId = $createResp->json('site.pages.0.id');
        $this->assertNotNull($siteId);
        $this->assertNotNull($pageId);

        // 2. Save draft with XSS payload inside text
        $tree = [
            'schema_version' => 1,
            'sections' => [
                [
                    'id' => 'sec_1',
                    'label' => 'Hero',
                    'styles' => ['desktop' => [], 'tablet' => [], 'mobile' => []],
                    'advanced' => [],
                    'rows' => [
                        [
                            'id' => 'row_1',
                            'styles' => ['desktop' => [], 'tablet' => [], 'mobile' => []],
                            'columns' => [
                                [
                                    'id' => 'col_1',
                                    'width' => 12,
                                    'styles' => ['desktop' => [], 'tablet' => [], 'mobile' => []],
                                    'modules' => [
                                        [
                                            'id' => 'mod_1',
                                            'type' => 'heading',
                                            'content' => [
                                                'tag' => 'h1',
                                                'text' => 'Bonjour <script>alert(1)</script>Monde',
                                            ],
                                            'styles' => ['desktop' => [], 'tablet' => [], 'mobile' => []],
                                            'advanced' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $saveResp = $this->actingAs($user)->postJson("/espace-utilisateur/portfolio-builder/pages/{$pageId}/save", [
            'tree' => $tree,
            'label' => 'V1 Clean',
        ]);

        $saveResp->assertOk();
        $savedText = $saveResp->json('page.draft_tree.sections.0.rows.0.columns.0.modules.0.content.text');
        $this->assertStringNotContainsString('<script>', $savedText);
        $this->assertStringContainsString('Bonjour', $savedText);

        // 3. Publish site
        $pubResp = $this->actingAs($user)->postJson("/espace-utilisateur/portfolio-builder/sites/{$siteId}/publish");
        $pubResp->assertOk();
        $this->assertTrue((bool) PortfolioSite::find($siteId)->is_published);

        // 4. Public route renders PublicSite
        $publicResp = $this->get('/@builderuser');
        $publicResp->assertOk();
        $publicResp->assertSee('Mon Studio Pro');
    }

    public function test_other_user_cannot_modify_or_view_private_editor(): void
    {
        $owner = User::factory()->create(['username' => 'owneruser']);
        $intruder = User::factory()->create(['username' => 'intruderuser']);

        $site = PortfolioSite::create([
            'user_id' => $owner->id,
            'name' => 'Site Privé',
            'slug' => 'site-prive',
            'is_primary' => true,
        ]);

        $page = PortfolioPage::create([
            'portfolio_site_id' => $site->id,
            'title' => 'Accueil',
            'slug' => 'accueil',
            'is_homepage' => true,
            'draft_tree' => ['schema_version' => 1, 'sections' => []],
        ]);

        $this->actingAs($intruder)
            ->get("/espace-utilisateur/portfolio-builder/sites/{$site->id}/editor")
            ->assertForbidden();

        $this->actingAs($intruder)
            ->postJson("/espace-utilisateur/portfolio-builder/pages/{$page->id}/save", [
                'tree' => ['schema_version' => 1, 'sections' => []],
            ])
            ->assertForbidden();
    }

    public function test_legacy_portfolio_still_renders_when_no_builder_site_is_published(): void
    {
        $user = User::factory()->create([
            'username' => 'legacyuser',
            'portfolio_published' => true,
        ]);

        $this->get('/@legacyuser')->assertOk();
    }
}
