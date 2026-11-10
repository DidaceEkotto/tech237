<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\XpLevel;
use App\Models\Poc;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanCard;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class CommunityGamificationAndKanbanTest extends TestCase
{
    use RefreshDatabase;

    public function test_xp_level_matching_and_automatic_level_up()
    {
        $user = User::factory()->create([
            'xp' => 10,
            'rank_title' => 'Novice Tech 🌱',
        ]);

        $this->assertEquals(10, $user->xp);

        // Add 95 XP -> total 105 XP (crosses 20, 50, 100 XP tiers)
        $user->addXp(95);
        $user->refresh();

        $this->assertEquals(105, $user->xp);
        $this->assertEquals('Super Contributeur 🚀', $user->rank_title);
        $this->assertNotNull($user->pending_level_up);
        $this->assertEquals('Super Contributeur 🚀', $user->pending_level_up['name']);
    }

    public function test_cannot_comment_poc_without_active_account()
    {
        $poc = Poc::create([
            'title' => 'Test POC Communautaire',
            'slug' => 'test-poc-communautaire',
            'content' => '<p>Contenu test POC</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 1. Guest cannot comment
        $response = $this->post("/pocs/{$poc->id}/commentaires", [
            'content' => 'Mon premier commentaire en tant qu\'invité',
        ]);
        $response->assertSessionHas('error');

        // 2. Active logged-in user can comment
        $user = User::factory()->create(['status' => 'active']);
        $this->actingAs($user);

        $response2 = $this->post("/pocs/{$poc->id}/commentaires", [
            'content' => 'Superbe initiative, félicitations !',
        ]);
        $response2->assertSessionHas('success');
        $this->assertDatabaseHas('comments', [
            'poc_id' => $poc->id,
            'user_id' => $user->id,
            'content' => 'Superbe initiative, félicitations !',
        ]);
    }

    public function test_kanban_overdue_check_command_detects_past_due_cards()
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'lead@tech237.net']);
        $board = KanbanBoard::create([
            'user_id' => $user->id,
            'title' => 'Tableau de Sprint',
        ]);
        $column = KanbanColumn::create([
            'board_id' => $board->id,
            'title' => 'En cours',
            'is_done_column' => false,
            'order' => 1,
        ]);
        $card = KanbanCard::create([
            'board_id' => $board->id,
            'column_id' => $column->id,
            'title' => 'Tâche dépassée',
            'due_date' => now()->subDays(2)->toDateString(),
            'assigned_to' => $user->id,
        ]);

        $this->assertNull($card->overdue_alert_sent_at);

        // Run the command directly
        $cmd = new \App\Console\Commands\CheckKanbanOverdueTasks();
        $exitCode = $cmd->handle();
        $this->assertEquals(0, $exitCode);

        $card->refresh();
        $this->assertNotNull($card->overdue_alert_sent_at);
    }

    public function test_community_map_and_public_profile_screens()
    {
        $user = User::factory()->create([
            'name' => 'Alice Dev',
            'xp' => 150,
            'rank_title' => 'Tech Builder 🛠️',
        ]);

        $resMap = $this->get('/carte-des-champions');
        $resMap->assertStatus(200);

        $resProfile = $this->get("/communaute/profil/{$user->id}");
        $resProfile->assertStatus(200);
    }

    public function test_support_message_routing_to_admin()
    {
        Mail::fake();

        $user = User::factory()->create();
        $collaborator = User::factory()->create();

        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/messages/envoyer', [
            'is_support' => '1',
            'subject' => 'Besoin d\'assistance technique',
            'message' => 'Bonjour l\'équipe support, j\'ai une question urgente.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('user_messages', [
            'user_id' => $user->id,
            'sender_id' => $user->id,
            'is_support' => true,
            'subject' => 'Besoin d\'assistance technique',
        ]);

        // Ensure it did NOT send to the collaborator
        $this->assertDatabaseMissing('user_messages', [
            'user_id' => $collaborator->id,
            'subject' => 'Besoin d\'assistance technique',
        ]);
    }

    public function test_poc_and_project_unique_view_counts()
    {
        $user = User::factory()->create();
        $poc = \App\Models\Poc::create([
            'user_id' => $user->id,
            'author_name' => $user->name,
            'title' => 'POC Test Unique Views',
            'slug' => 'poc-test-unique-views',
            'content' => 'Contenu du POC',
            'tag' => 'dev',
            'status' => 'published',
            'views_count' => 0,
        ]);

        // First visit
        $this->get("/pocs/{$poc->slug}");
        $this->assertEquals(1, $poc->fresh()->views_count);

        // Second visit from same session / device
        $this->get("/pocs/{$poc->slug}");
        $this->assertEquals(1, $poc->fresh()->views_count);
    }

    public function test_kanban_members_and_card_collaboration()
    {
        $owner = User::factory()->create();
        $collab = User::factory()->create();

        $board = KanbanBoard::create([
            'user_id' => $owner->id,
            'title' => 'Tableau Collaboratif',
        ]);

        // Add member
        $this->actingAs($owner, 'web')->post("/espace-utilisateur/kanban/boards/{$board->id}/members", [
            'user_id' => $collab->id,
            'role' => 'member',
        ])->assertRedirect();

        $this->assertDatabaseHas('kanban_board_members', [
            'board_id' => $board->id,
            'user_id' => $collab->id,
        ]);

        $column = \App\Models\KanbanColumn::create([
            'board_id' => $board->id,
            'title' => 'En cours',
            'order' => 1,
        ]);

        $card = KanbanCard::create([
            'board_id' => $board->id,
            'column_id' => $column->id,
            'title' => 'Tâche en équipe',
        ]);

        // Collaborator can comment
        $this->actingAs($collab, 'web')->post("/espace-utilisateur/kanban/cards/{$card->id}/comments", [
            'comment' => 'Je m\'occupe de cette tâche ! @' . $owner->name,
        ])->assertRedirect();

        $this->assertDatabaseHas('kanban_card_comments', [
            'card_id' => $card->id,
            'user_id' => $collab->id,
        ]);
    }
}
