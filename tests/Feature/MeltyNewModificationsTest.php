<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Category;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Startup;
use App\Models\Project;
use App\Models\SiteSetting;

class MeltyNewModificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SiteSetting::current();
    }

    public function test_category_applications_route_returns_ok(): void
    {
        $response = $this->get('/category/applications');
        $response->assertStatus(200);
    }

    public function test_public_author_page_returns_ok(): void
    {
        $admin = Admin::create([
            'name' => 'Jean Redacteur',
            'email' => 'jean@melty.test',
            'password' => '000000',
            'role' => 'editor',
            'bio' => 'Journaliste cinéma',
            'avatar' => 'storage/images/avatar.jpg',
        ]);

        $response = $this->get("/auteur/{$admin->id}");
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_submit_comment(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Redac',
            'email' => 'admin@test.fr',
            'password' => '000000',
            'role' => 'editor',
        ]);

        $user = User::factory()->create();
        $cat = Category::create([
            'name_fr' => 'Séries',
            'name_en' => 'Series',
            'slug' => 'series',
        ]);

        $article = Article::create([
            'admin_id' => $admin->id,
            'title_fr' => 'Test Article',
            'title_en' => 'Test Article EN',
            'slug' => 'test-article',
            'category_id' => $cat->id,
            'content_fr' => 'Contenu',
            'status' => 'published',
        ]);

        $response = $this->actingAs($user, 'web')->post('/api/comments', [
            'article_id' => $article->id,
            'content' => 'Mon premier commentaire de test !',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'content' => 'Mon premier commentaire de test !',
            'status' => 'pending',
        ]);
    }

    public function test_guest_cannot_submit_comment(): void
    {
        $response = $this->postJson('/api/comments', [
            'content' => 'Test sans auth',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_comments_page_accessible_to_admin(): void
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.test',
            'password' => '000000',
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/comments');
        $response->assertStatus(200);
    }

    public function test_user_space_dedicated_pages_accessible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->get('/espace-utilisateur');
        $response->assertStatus(200);

        $response = $this->actingAs($user, 'web')->get('/espace-utilisateur/startups/create');
        $response->assertStatus(200);

        $response = $this->actingAs($user, 'web')->get('/espace-utilisateur/projets/create');
        $response->assertStatus(200);
    }
}
