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

    public function test_site_type_migration_name_lock_stats_and_zip_export(): void
    {
        $user = User::factory()->create([
            'username' => 'probuilder',
            'portfolio_published' => true,
        ]);

        // Create multipage site
        $createResp = $this->actingAs($user)->postJson('/espace-utilisateur/portfolio-builder/sites', [
            'name' => 'Agence 237',
            'site_type' => 'multipage',
        ]);
        $createResp->assertCreated();
        $siteId = $createResp->json('site.id');
        $slug = $createResp->json('site.slug');

        // Can rename while draft
        $this->actingAs($user)
            ->patchJson("/espace-utilisateur/portfolio-builder/sites/{$siteId}", ['name' => 'Agence 237 Pro'])
            ->assertOk();

        // 1-click migrate to onepage
        $this->actingAs($user)
            ->postJson("/espace-utilisateur/portfolio-builder/sites/{$siteId}/migrate-type", ['target_type' => 'onepage'])
            ->assertOk()
            ->assertJsonPath('site.site_type', 'onepage');

        // Publish -> name becomes locked
        $this->actingAs($user)->postJson("/espace-utilisateur/portfolio-builder/sites/{$siteId}/publish")->assertOk();

        $this->actingAs($user)
            ->patchJson("/espace-utilisateur/portfolio-builder/sites/{$siteId}", ['name' => 'Autre Nom'])
            ->assertStatus(422);

        // Tenant route renders & tracks visit when visited anonymously
        auth()->logout();
        $this->get("/site/{$slug}")->assertOk();
        $this->assertEquals(1, PortfolioSite::find($siteId)->views_count);

        // Export HTML ZIP
        $zipResp = $this->actingAs($user)->get("/espace-utilisateur/portfolio-builder/sites/{$siteId}/export?format=html");
        $zipResp->assertOk();

        // Export CV PDF
        $pdfResp = $this->actingAs($user)->post('/espace-utilisateur/portfolio-builder/cv/export-pdf', [
            'fullName' => 'Jean Dupont',
            'jobTitle' => 'Architecte Laravel',
            'skills' => [['name' => 'Laravel', 'category' => 'Backend', 'level' => 95]],
        ]);
        $pdfResp->assertOk();
    }
}
