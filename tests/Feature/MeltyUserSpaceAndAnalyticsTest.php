<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Startup;
use App\Models\Project;
use App\Models\Rubrique;
use App\Models\Category;
use App\Models\VisitLog;
use App\Services\ImageService;

class MeltyUserSpaceAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_rejects_weak_passwords(): void
    {
        // Missing uppercase
        $res1 = $this->post('/register', [
            'name' => 'Dupont',
            'first_name' => 'Jean',
            'gender' => 'Homme',
            'country_code' => '+237',
            'phone' => '699000000',
            'email' => 'jean1@test.com',
            'password' => 'weakpass123@',
            'password_confirmation' => 'weakpass123@',
        ]);
        $res1->assertSessionHasErrors('password');

        // Missing digit
        $res2 = $this->post('/register', [
            'name' => 'Dupont',
            'first_name' => 'Jean',
            'gender' => 'Homme',
            'country_code' => '+237',
            'phone' => '699000000',
            'email' => 'jean2@test.com',
            'password' => 'WeakPassNoDigit@',
            'password_confirmation' => 'WeakPassNoDigit@',
        ]);
        $res2->assertSessionHasErrors('password');

        // Missing special char
        $res3 = $this->post('/register', [
            'name' => 'Dupont',
            'first_name' => 'Jean',
            'gender' => 'Homme',
            'country_code' => '+237',
            'phone' => '699000000',
            'email' => 'jean3@test.com',
            'password' => 'WeakPass12345',
            'password_confirmation' => 'WeakPass12345',
        ]);
        $res3->assertSessionHasErrors('password');

        // Contains whitespace
        $res4 = $this->post('/register', [
            'name' => 'Dupont',
            'first_name' => 'Jean',
            'gender' => 'Homme',
            'country_code' => '+237',
            'phone' => '699000000',
            'email' => 'jean4@test.com',
            'password' => 'Weak Pass123@',
            'password_confirmation' => 'Weak Pass123@',
        ]);
        $res4->assertSessionHasErrors('password');
    }

    public function test_user_registration_succeeds_and_autologins(): void
    {
        $res = $this->post('/register', [
            'name' => 'Eto\'o',
            'first_name' => 'Samuel',
            'gender' => 'Homme',
            'country_code' => '+237',
            'phone' => '699112233',
            'email' => 'samuel@cameroon.test',
            'birth_date' => '1981-03-10',
            'marital_status' => 'Marié(e)',
            'password' => 'Melty@Cameroon2026',
            'password_confirmation' => 'Melty@Cameroon2026',
        ]);

        $res->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'samuel@cameroon.test')->first(), 'web');

        $user = User::where('email', 'samuel@cameroon.test')->first();
        $this->assertNotNull($user->age);
        $this->assertEquals(45, $user->age); // Born in 1981, year is 2026
        $this->assertEquals('Samuel Eto\'o', $user->full_name);
    }

    public function test_authenticated_user_can_submit_startup(): void
    {
        $user = User::create([
            'name' => 'Biya',
            'first_name' => 'Frank',
            'gender' => 'Homme',
            'country_code' => '+237',
            'phone' => '677000000',
            'email' => 'frank@startup.cm',
            'password' => Hash::make('Secret123@Pass'),
            'status' => 'active',
        ]);

        $this->actingAs($user, 'web');

        $res = $this->post('/espace-utilisateur/startup', [
            'name' => 'AfriLogistics SARL',
            'legal_type' => 'SARL',
            'city' => 'Douala',
            'location' => 'Bonanjo',
            'employee_count' => 12,
            'needs_funding' => true,
            'funding_amount' => 50000000,
            'rccm_number' => 'RC/DLA/2026/B/9999',
            'niu_number' => 'M019283746501T',
            'description' => 'Logistique fluviale et routière interconnectée.',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $res->assertRedirect('/espace-utilisateur?section=startups');
        $this->assertDatabaseHas('startups', [
            'name' => 'AfriLogistics SARL',
            'legal_type' => 'SARL',
            'status' => 'pending_validation',
        ]);
    }

    public function test_authenticated_user_can_submit_project_and_devlog(): void
    {
        $user = User::create([
            'name' => 'Kamto',
            'first_name' => 'Maurice',
            'gender' => 'Homme',
            'country_code' => '+237',
            'phone' => '655000000',
            'email' => 'maurice@dev.cm',
            'password' => Hash::make('Secret123@Pass'),
            'status' => 'active',
        ]);

        $this->actingAs($user, 'web');

        $resProj = $this->post('/espace-utilisateur/projet', [
            'name' => 'AgriPredict AI',
            'project_type' => 'web',
            'languages' => ['Python', 'JavaScript'],
            'frameworks_engines' => ['FastAPI', 'Vue.js'],
            'development_duration' => '4 mois',
            'description' => 'Prédiction des rendements agricoles via imagerie satellite.',
        ]);

        $resProj->assertRedirect('/espace-utilisateur?section=projects');
        $project = Project::where('name', 'AgriPredict AI')->first();
        $this->assertNotNull($project);

        // Add timeline step
        $resTime = $this->post("/espace-utilisateur/projet/{$project->id}/timeline", [
            'title' => 'v1.0 Modèle d\'IA entraîné sur les données du Grand Nord',
            'problem_encountered' => 'Manque de données météorologiques historiques fiables.',
            'solution_applied' => 'Agrégation des relevés communautaires et satellites Sentinel-2.',
            'description' => 'Précision atteinte : 94.2%.',
        ]);

        $resTime->assertRedirect(route('user.dashboard'));
        $this->assertDatabaseHas('project_timelines', [
            'project_id' => $project->id,
            'title' => 'v1.0 Modèle d\'IA entraîné sur les données du Grand Nord',
        ]);
    }

    public function test_rubrique_and_category_relationships(): void
    {
        $rub = Rubrique::create([
            'name_fr' => 'High-Tech & IA',
            'name_en' => 'High-Tech & AI',
            'slug' => 'high-tech-ia',
            'icon' => 'bi-laptop',
        ]);

        $cat = Category::create([
            'rubrique_id' => $rub->id,
            'name_fr' => 'Smartphones & Tablettes',
            'slug' => 'smartphones-tablettes',
            'color' => '#00D2FF',
            'icon' => 'bi-phone',
        ]);

        $this->assertEquals($rub->id, $cat->rubrique->id);
        $this->assertTrue($rub->categories->contains($cat));
    }

    public function test_image_service_stores_without_failure(): void
    {
        $file = UploadedFile::fake()->image('test_banner.jpg', 600, 400);
        $savedPath = ImageService::saveImage($file, 'banner_test');

        $this->assertStringStartsWith('storage/images/banner_test_', $savedPath);
        $this->assertFileExists(public_path($savedPath));
    }

    public function test_all_front_and_admin_routes_return_ok(): void
    {
        // Public pages
        $this->get('/')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
        $this->get('/login')->assertStatus(200);
        $this->get('/projets')->assertStatus(200);
        $this->get('/tests-jeux-video')->assertStatus(200);
        $this->get('/tests-applications')->assertStatus(200);
        $this->get('/conditions-generales')->assertStatus(200);

        // Admin pages (with authenticated admin)
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admintest@admin.test',
            'password' => Hash::make('000000'),
            'role' => 'super_admin',
            'default_password' => 0,
            'is_banned' => false,
            'failed_login_attempts' => 0,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get('/admin')->assertStatus(200);
        $this->get('/admin/articles')->assertStatus(200);
        $this->get('/admin/rubriques')->assertStatus(200);
        $this->get('/admin/categories')->assertStatus(200);
        $this->get('/admin/analytics')->assertStatus(200);
        $this->get('/admin/startups')->assertStatus(200);
        $this->get('/admin/front-users')->assertStatus(200);
        $this->get('/admin/disputes')->assertStatus(200);
        $this->get('/admin/users')->assertStatus(200);
    }

    public function test_visit_tracking_middleware_records_views(): void
    {
        $this->get('/');
        $this->get('/projets');

        $this->assertGreaterThanOrEqual(2, VisitLog::count());
    }
}
