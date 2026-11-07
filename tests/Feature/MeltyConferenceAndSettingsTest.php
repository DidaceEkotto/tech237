<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Admin;
use App\Models\Rubrique;
use App\Models\Category;
use App\Models\Article;
use App\Models\Dossier;
use App\Models\JobOffer;
use App\Models\ForumCategory;
use App\Models\SiteSetting;
use App\Models\Conference;
use App\Models\ConferenceRegistration;
use Carbon\Carbon;

class MeltyConferenceAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'user@test.com',
            'name' => 'Jean Membre',
        ]);

        $this->admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.test',
            'password' => bcrypt('000000'),
            'role' => 'super_admin',
            'default_password' => 0,
        ]);
    }

    public function test_rubrique_deletion_cascades_to_categories_articles_and_dossiers(): void
    {
        $rubrique = Rubrique::create([
            'name_fr' => 'Gaming Test',
            'slug' => 'gaming-test',
        ]);

        $cat = Category::create([
            'rubrique_id' => $rubrique->id,
            'name_fr' => 'Jeux PC',
            'slug' => 'jeux-pc',
        ]);

        $subCat = Category::create([
            'rubrique_id' => $rubrique->id,
            'parent_id' => $cat->id,
            'name_fr' => 'RPG',
            'slug' => 'rpg',
        ]);

        $dossier = Dossier::create([
            'rubrique_id' => $rubrique->id,
            'title_fr' => 'Cyberpunk 2077',
            'slug' => 'cyberpunk-2077',
        ]);

        $article = Article::create([
            'category_id' => $cat->id,
            'dossier_id' => $dossier->id,
            'admin_id' => $this->admin->id,
            'title_fr' => 'Test Complet Cyberpunk',
            'slug' => 'test-complet-cyberpunk',
            'chapo_fr' => 'Un grand jeu',
            'content_fr' => '<p>Contenu</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // When the rubrique is deleted
        $rubrique->delete();

        // Assert all related items are deleted
        $this->assertDatabaseMissing('rubriques', ['id' => $rubrique->id]);
        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
        $this->assertDatabaseMissing('categories', ['id' => $subCat->id]);
        $this->assertDatabaseMissing('dossiers', ['id' => $dossier->id]);
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_forum_category_can_be_created_without_not_null_violation(): void
    {
        $cat = ForumCategory::create([
            'name' => 'Développement Web',
            'slug' => 'dev-web',
            'description' => 'Discussions sur Laravel, Vue, PHP',
            'icon' => 'bi-code-slash',
            'color' => '#FF004B',
            'order' => 1,
        ]);

        $this->assertDatabaseHas('forum_categories', [
            'id' => $cat->id,
            'name' => 'Développement Web',
            'slug' => 'dev-web',
        ]);
        $this->assertEquals('Développement Web', $cat->name_fr);
    }

    public function test_job_offer_creator_user_relation_works(): void
    {
        $job = JobOffer::create([
            'user_id' => $this->user->id,
            'title' => 'Tech Lead Node.js',
            'domain' => ['Développement Web & Mobile'],
            'contract_type' => 'CDI',
            'salary_type' => 'negotiable',
            'city' => 'Douala',
            'status' => 'pending_validation',
        ]);

        $this->assertNotNull($job->creatorUser);
        $this->assertEquals('Jean Membre', $job->creatorUser->name);
        $this->assertEquals('Jean Membre', $job->company_name);
        $this->assertEquals('Développement Web & Mobile', $job->it_domain);
        $this->assertFalse($job->is_published);
    }

    public function test_toggle_snow_effect_in_admin(): void
    {
        $setting = SiteSetting::current();
        $this->assertFalse((bool) $setting->snow_effect_enabled);

        $res = $this->actingAs($this->admin, 'admin')->post('/admin/toggle-snow');
        $res->assertSessionHas('success');

        $this->assertTrue((bool) SiteSetting::current()->snow_effect_enabled);

        $res2 = $this->actingAs($this->admin, 'admin')->post('/admin/toggle-snow');
        $res2->assertSessionHas('success');

        $this->assertFalse((bool) SiteSetting::current()->snow_effect_enabled);
    }

    public function test_dossiers_index_page_returns_grouped_dossiers(): void
    {
        Dossier::create(['title_fr' => 'Avatar 3', 'slug' => 'avatar-3', 'is_active' => true]);
        Dossier::create(['title_fr' => 'Batman', 'slug' => 'batman', 'is_active' => true]);
        Dossier::create(['title_fr' => '1917 Le Film', 'slug' => '1917-le-film', 'is_active' => true]);

        $resAll = $this->get('/index-dossiers');
        $resAll->assertOk();

        $resA = $this->get('/index-dossiers?letter=A');
        $resA->assertOk();

        $resNum = $this->get('/index-dossiers?letter=0-9');
        $resNum->assertOk();
    }

    public function test_user_can_create_conference_and_manage_participants(): void
    {
        Mail::fake();

        $payload = [
            'title_fr' => 'Sommet Cloud & IA Douala 2026',
            'title_en' => 'Cloud & AI Summit Douala 2026',
            'event_date' => Carbon::now()->addDays(15)->toDateString(),
            'end_date' => Carbon::now()->addDays(17)->toDateString(),
            'deadline_registration' => Carbon::now()->addDays(10)->toDateString(),
            'city' => 'Douala',
            'location' => 'Krystal Palace Hotel',
            'description_fr' => '<p>Conférence majeure dédiée au Cloud et à l\'IA.</p>',
            'description_en' => '<p>Major conference on Cloud and AI.</p>',
            'accepts_registrations' => true,
        ];

        $res = $this->actingAs($this->user)->post('/espace-utilisateur/conferences', $payload);
        $res->assertRedirect('/espace-utilisateur?section=conferences');

        $conf = Conference::where('title_fr', 'Sommet Cloud & IA Douala 2026')->first();
        $this->assertNotNull($conf);
        $this->assertEquals('pending_validation', $conf->status);
        $this->assertEquals($this->user->id, $conf->user_id);
        $this->assertFalse($conf->is_official_admin);

        // Register a participant
        $reg = ConferenceRegistration::create([
            'conference_id' => $conf->id,
            'name' => 'Kamga',
            'first_name' => 'Paul',
            'gender' => 'Homme',
            'city' => 'Douala',
            'email' => 'paul.kamga@test.com',
            'phone_whatsapp' => '+237699112233',
            'bio' => 'Ingénieur Réseaux',
            'is_confirmed' => false,
        ]);

        $this->assertEquals('Kamga', $reg->last_name);
        $this->assertEquals('pending', $reg->status);

        // Send confirmation email to individual participant
        $resMail = $this->actingAs($this->user)->post("/espace-utilisateur/conferences/participants/{$reg->id}/confirmer");
        $resMail->assertSessionHas('success');

        $this->assertTrue($reg->fresh()->is_confirmed);
        $this->assertNotNull($reg->fresh()->confirmed_at);
        $this->assertEquals('confirmed', $reg->fresh()->status);
    }
}
