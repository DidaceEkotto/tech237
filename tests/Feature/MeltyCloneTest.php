<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Article;
use App\Models\Category;
use App\Models\Dossier;
use App\Models\GameReview;
use App\Models\AppReview;
use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use Livewire\Livewire;
use App\Livewire\Admin\Auth\Login as AdminLogin;
use App\Livewire\Admin\Articles\Form as ArticleForm;
use App\Livewire\Admin\Banned\Index as BannedIndex;

class MeltyCloneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_loads_successfully_with_inertia()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Home'));
    }

    public function test_support_page_loads_with_donation_channels()
    {
        $response = $this->get('/nous-soutenir');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Support'));
    }

    public function test_legal_terms_and_privacy_pages_load()
    {
        $response = $this->get('/conditions-generales');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Legal/Terms'));

        $response2 = $this->get('/politique-de-confidentialite');
        $response2->assertStatus(200);
        $response2->assertInertia(fn ($page) => $page->component('Legal/Privacy'));
    }

    public function test_search_autocomplete_api_returns_results()
    {
        $response = $this->getJson('/api/search-autocomplete?q=Chat');
        $response->assertStatus(200);
        $response->assertJsonStructure(['articles', 'games', 'apps', 'dossiers']);
    }

    public function test_newsletter_subscription_captures_email_and_ip()
    {
        $response = $this->postJson('/api/newsletter/subscribe', [
            'email' => 'test.subscriber@melty.test',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'test.subscriber@melty.test',
        ]);
    }

    public function test_admin_login_requires_math_captcha()
    {
        $admin = Admin::where('email', 'admin@admin.test')->first();

        // Failed attempt with wrong captcha
        Livewire::test(AdminLogin::class)
            ->set('email', 'admin@admin.test')
            ->set('password', '000000')
            ->set('userCaptcha', 99999)
            ->call('authenticate')
            ->assertSet('errorMessage', 'Le résultat du calcul de sécurité est incorrect.');

        $this->assertFalse(auth('admin')->check());
    }

    public function test_admin_auto_banned_after_5_failed_attempts()
    {
        $admin = Admin::factory()->create([
            'email' => 'victim@melty.test',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'failed_login_attempts' => 4,
            'is_banned' => false,
        ]);

        $component = Livewire::test(AdminLogin::class)
            ->set('email', 'victim@melty.test')
            ->set('password', 'wrong-pass');

        // Set correct captcha to test password check
        $answer = $component->get('captchaAnswer');
        $component->set('userCaptcha', $answer)
            ->call('authenticate');

        $admin->refresh();
        $this->assertTrue($admin->is_banned);
        $this->assertEquals(5, $admin->failed_login_attempts);
    }

    public function test_banned_admin_cannot_login()
    {
        $banned = Admin::factory()->create([
            'email' => 'banned@melty.test',
            'password' => Hash::make('000000'),
            'is_banned' => true,
        ]);

        $component = Livewire::test(AdminLogin::class)
            ->set('email', 'banned@melty.test')
            ->set('password', '000000');

        $answer = $component->get('captchaAnswer');
        $component->set('userCaptcha', $answer)
            ->call('authenticate')
            ->assertSee('banni');

        $this->assertFalse(auth('admin')->check());
    }

    public function test_super_admin_can_access_banned_menu_and_restore_admin()
    {
        $superAdmin = Admin::where('role', 'super_admin')->first();
        $banned = Admin::factory()->create([
            'email' => 'to_restore@melty.test',
            'is_banned' => true,
            'failed_login_attempts' => 5,
        ]);

        // Non-super admin cannot access banned page
        $editor = Admin::factory()->create(['role' => 'editor']);
        $this->actingAs($editor, 'admin')
            ->get('/admin/banned-admins')
            ->assertStatus(403);

        // Super admin can access
        $this->actingAs($superAdmin, 'admin')
            ->get('/admin/banned-admins')
            ->assertStatus(200);

        // Super admin restores banned admin
        Livewire::actingAs($superAdmin, 'admin')
            ->test(BannedIndex::class)
            ->call('restoreAdmin', $banned->id);

        $banned->refresh();
        $this->assertFalse($banned->is_banned);
        $this->assertEquals(0, $banned->failed_login_attempts);
    }

    public function test_editor_article_submission_must_be_pending_review()
    {
        $editor = Admin::factory()->create(['role' => 'editor']);
        $cat = Category::first() ?? Category::create(['name_fr' => 'Test', 'slug' => 'test']);

        Livewire::actingAs($editor, 'admin')
            ->test(ArticleForm::class)
            ->set('title_fr', 'Mon super article par un rédacteur')
            ->set('slug', 'mon-super-article-redacteur')
            ->set('category_id', $cat->id)
            ->set('content_fr', '<p>Contenu test</p>')
            ->call('save', 'published') // Tries to publish directly
            ->assertRedirect(route('admin.articles.index'));

        $article = Article::where('slug', 'mon-super-article-redacteur')->first();
        $this->assertNotNull($article);
        // Verified: Editor role forces status to pending_review!
        $this->assertEquals('pending_review', $article->status);
    }
}
