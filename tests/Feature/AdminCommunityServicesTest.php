<?php

namespace Tests\Feature;

use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use App\Models\Admin;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormResponse;
use App\Models\FreelanceQuote;
use App\Models\FreelanceSetting;
use App\Models\HeartbeatCheck;
use App\Models\HeartbeatMonitor;
use App\Models\KanbanBoard;
use App\Models\KanbanCard;
use App\Models\KanbanColumn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCommunityServicesTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = Admin::factory()->create(['role' => 'super_admin']);
        $this->user = User::factory()->create(['name' => 'Jean Tech', 'email' => 'jean@tech237.cm']);
    }

    public function test_guest_cannot_access_new_admin_modules()
    {
        $this->get('/admin/kanban')->assertRedirect('/admin/login');
        $this->get('/admin/formulaires')->assertRedirect('/admin/login');
        $this->get('/admin/freelance')->assertRedirect('/admin/login');
        $this->get('/admin/heartbeat')->assertRedirect('/admin/login');
    }

    public function test_admin_can_assign_new_specialized_roles_in_admin_users()
    {
        Livewire::actingAs($this->superAdmin, 'admin')
            ->test(AdminUsersIndex::class)
            ->set('name', 'DevOps Lead')
            ->set('gender', 'Homme')
            ->set('email', 'devops@tech237.cm')
            ->set('role', 'devops_manager')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('admins', [
            'email' => 'devops@tech237.cm',
            'role' => 'devops_manager',
        ]);

        $createdAdmin = Admin::where('email', 'devops@tech237.cm')->first();
        $this->assertTrue($createdAdmin->isHeartbeatManager());
        $this->assertTrue($createdAdmin->canAccessCommunityServices());

        // Update to community_services_manager
        Livewire::actingAs($this->superAdmin, 'admin')
            ->test(AdminUsersIndex::class)
            ->call('edit', $createdAdmin->id)
            ->set('role', 'community_services_manager')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('admins', [
            'id' => $createdAdmin->id,
            'role' => 'community_services_manager',
        ]);
    }

    public function test_admin_can_view_kanban_boards_and_details()
    {
        $board = KanbanBoard::create([
            'user_id' => $this->user->id,
            'title' => 'Tableau Startup MVP',
            'description' => 'Gestion de projet agile',
            'template_type' => 'mvp_launch',
            'visibility' => 'public',
        ]);

        $col = KanbanColumn::create([
            'board_id' => $board->id,
            'title' => 'En Cours',
            'order' => 1,
            'color' => '#3b82f6',
        ]);

        KanbanCard::create([
            'board_id' => $board->id,
            'column_id' => $col->id,
            'user_id' => $this->user->id,
            'title' => 'Implémenter API Paiement',
            'description' => 'Connecter Orange Money et MTN MoMo',
            'order' => 1,
        ]);

        // Access Index
        $response = $this->actingAs($this->superAdmin, 'admin')->get('/admin/kanban');
        $response->assertStatus(200);
        $response->assertSee('Tableaux Kanban &amp; Roadmaps', false);
        $response->assertSee('Tableau Startup MVP');
        $response->assertSee('Jean Tech');

        // Access Detail
        $detailResponse = $this->actingAs($this->superAdmin, 'admin')->get('/admin/kanban/' . $board->id);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Tableau Startup MVP');
        $detailResponse->assertSee('En Cours');
        $detailResponse->assertSee('Implémenter API Paiement');
    }

    public function test_admin_can_view_forms_questions_and_submissions()
    {
        $form = Form::create([
            'user_id' => $this->user->id,
            'title' => 'Enquête Développeurs Cameroun 2026',
            'slug' => 'enquete-dev-2026',
            'status' => 'published',
            'display_mode' => 'all_at_once',
            'published_at' => now(),
        ]);

        FormField::create([
            'form_id' => $form->id,
            'label' => 'Quel est votre langage favori ?',
            'name' => 'fav_lang',
            'type' => 'radio',
            'is_required' => true,
            'options' => ['PHP', 'TypeScript', 'Python'],
            'order' => 1,
        ]);

        $submission = FormResponse::create([
            'form_id' => $form->id,
            'user_id' => $this->user->id,
            'respondent_email' => 'paul@dev.cm',
            'answers' => [
                'fav_lang' => 'PHP',
            ],
            'submitted_at' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        // Form Index
        $response = $this->actingAs($this->superAdmin, 'admin')->get('/admin/formulaires');
        $response->assertStatus(200);
        $response->assertSee('Enquête Développeurs Cameroun 2026');

        // Form Structure Show
        $showResponse = $this->actingAs($this->superAdmin, 'admin')->get('/admin/formulaires/' . $form->id);
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Enquête Développeurs Cameroun 2026');
        $showResponse->assertSee('Quel est votre langage favori ?');

        // Form Responses
        $responsesView = $this->actingAs($this->superAdmin, 'admin')->get('/admin/formulaires/' . $form->id . '/reponses');
        $responsesView->assertStatus(200);
        $responsesView->assertSee('Jean Tech');
        $responsesView->assertSee('jean@tech237.cm');
    }

    public function test_admin_can_view_freelance_quotes_and_tjm_simulator()
    {
        $quote = FreelanceQuote::create([
            'user_id' => $this->user->id,
            'quote_number' => 'DEV-2026-001',
            'type' => 'devis',
            'title' => 'Création Application Mobile Flutter',
            'client_name' => 'Kmer Logistics SARL',
            'client_email' => 'contact@kmerlogistics.cm',
            'status' => 'accepted',
            'subtotal' => 1500000,
            'tax_amount' => 288750,
            'total_amount' => 1788750,
            'currency' => 'XAF',
            'issue_date' => now(),
            'items' => [
                [
                    'description' => 'Conception UI/UX & Intégration API',
                    'quantity' => 10,
                    'unit_price' => 150000,
                    'total' => 1500000,
                ]
            ],
        ]);

        FreelanceSetting::create([
            'user_id' => $this->user->id,
            'desired_net_monthly' => 800000,
            'pro_charges_monthly' => 150000,
            'billable_days_per_month' => 18,
            'tax_rate_percent' => 15,
            'recommended_tjm' => 65000,
            'currency' => 'XAF',
        ]);

        // Freelance Index (Quotes & TJM)
        $response = $this->actingAs($this->superAdmin, 'admin')->get('/admin/freelance');
        $response->assertStatus(200);
        $response->assertSee('DEV-2026-001');
        $response->assertSee('Kmer Logistics SARL');
        $response->assertSee('1 788 750');

        // Freelance Show Detail
        $detailResponse = $this->actingAs($this->superAdmin, 'admin')->get('/admin/freelance/devis/' . $quote->id);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('DEV-2026-001');
        $detailResponse->assertSee('Conception UI/UX &amp; Intégration API', false);
        $detailResponse->assertSee('1 500 000');
    }

    public function test_admin_can_view_heartbeat_monitors_and_checks()
    {
        $monitor = HeartbeatMonitor::create([
            'user_id' => $this->user->id,
            'name' => 'API Tech237 Production',
            'url' => 'https://api.tech237.cm/health',
            'check_interval_minutes' => 5,
            'expected_status_code' => 200,
            'uptime_percentage' => 99.8,
            'total_checks' => 120,
            'successful_checks' => 119,
            'failed_checks' => 1,
            'last_status_code' => 200,
            'last_response_time_ms' => 145,
            'last_checked_at' => now(),
            'is_active' => true,
            'is_currently_down' => false,
        ]);

        HeartbeatCheck::create([
            'heartbeat_monitor_id' => $monitor->id,
            'status_code' => 200,
            'response_time_ms' => 145,
            'is_up' => true,
            'ip_address' => '102.164.22.10',
            'checked_at' => now(),
        ]);

        // Heartbeat Index
        $response = $this->actingAs($this->superAdmin, 'admin')->get('/admin/heartbeat');
        $response->assertStatus(200);
        $response->assertSee('API Tech237 Production');
        $response->assertSee('https://api.tech237.cm/health');

        // Heartbeat Show
        $showResponse = $this->actingAs($this->superAdmin, 'admin')->get('/admin/heartbeat/' . $monitor->id);
        $showResponse->assertStatus(200);
        $showResponse->assertSee('API Tech237 Production');
        $showResponse->assertSee('145 ms');
        $showResponse->assertSee('102.164.22.10');
    }
}
