<?php

namespace Tests\Feature;

use App\Models\KanbanBoard;
use App\Models\KanbanCard;
use App\Models\KanbanColumn;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanbanTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_kanban_index_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->get('/espace-utilisateur/kanban');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_kanban_board_with_default_columns()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/kanban/boards', [
            'title' => 'Sprint v1.0 Tech237',
            'description' => 'Tâches du premier sprint',
            'is_public' => false,
        ]);

        $board = KanbanBoard::where('title', 'Sprint v1.0 Tech237')->first();
        $this->assertNotNull($board);
        $this->assertEquals($user->id, $board->user_id);

        $response->assertRedirect("/espace-utilisateur/kanban/{$board->id}");

        // Assert 4 default columns were generated
        $this->assertCount(4, $board->columns);
        $this->assertTrue($board->columns->contains('title', 'Terminé'));
    }

    public function test_user_can_add_cards_and_move_them_between_columns()
    {
        $user = User::factory()->create();
        $board = KanbanBoard::create([
            'user_id' => $user->id,
            'title' => 'Board Test',
        ]);

        $col1 = KanbanColumn::create(['board_id' => $board->id, 'title' => 'À faire', 'order' => 1]);
        $col2 = KanbanColumn::create(['board_id' => $board->id, 'title' => 'Terminé', 'order' => 2, 'is_done_column' => true]);

        // Add Card to Col 1
        $addCardRes = $this->actingAs($user, 'web')->postJson("/espace-utilisateur/kanban/columns/{$col1->id}/cards", [
            'title' => 'Implémenter API Orange Money',
            'priority' => 'urgent',
            'checklist' => [
                ['text' => 'Configurer les credentials', 'done' => true],
                ['text' => 'Tester le webhook IPN', 'done' => false],
            ],
        ]);

        $addCardRes->assertJson(['success' => true]);
        $card = KanbanCard::where('title', 'Implémenter API Orange Money')->first();
        $this->assertNotNull($card);
        $this->assertEquals($col1->id, $card->column_id);
        $this->assertEquals('urgent', $card->priority);

        // Move card to Col 2 (drag and drop simulation)
        $moveRes = $this->actingAs($user, 'web')->postJson("/espace-utilisateur/kanban/cards/{$card->id}/move", [
            'column_id' => $col2->id,
            'order' => 0,
        ]);

        $moveRes->assertJson([
            'success' => true,
            'is_done_column' => true,
        ]);

        $card->refresh();
        $this->assertEquals($col2->id, $card->column_id);
    }

    public function test_unauthorized_user_cannot_edit_private_board()
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $board = KanbanBoard::create([
            'user_id' => $owner->id,
            'title' => 'Secret Board',
            'is_public' => false,
        ]);
        $col = KanbanColumn::create(['board_id' => $board->id, 'title' => 'Col 1', 'order' => 1]);

        $response = $this->actingAs($stranger, 'web')->get("/espace-utilisateur/kanban/{$board->id}");
        $response->assertStatus(403);

        $addCardRes = $this->actingAs($stranger, 'web')->postJson("/espace-utilisateur/kanban/columns/{$col->id}/cards", [
            'title' => 'Hacked Task',
        ]);
        $addCardRes->assertStatus(403);
    }

    public function test_user_can_create_board_with_scrum_template_and_wip_limits()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/kanban/boards', [
            'title' => 'Scrum Sprint Project',
            'template' => 'scrum',
            'is_public' => false,
        ]);

        $board = KanbanBoard::where('title', 'Scrum Sprint Project')->first();
        $this->assertNotNull($board);
        $this->assertEquals('scrum', $board->template);
        $this->assertCount(5, $board->columns);
        $this->assertTrue($board->columns->contains('title', 'Product Backlog'));
        $this->assertTrue($board->columns->contains('title', 'Déployé / Terminé'));

        // Test updating column WIP limit
        $devCol = $board->columns->where('title', 'En Développement')->first();
        $this->assertNotNull($devCol);
        $this->assertEquals(5, $devCol->wip_limit);

        $updateColRes = $this->actingAs($user, 'web')->putJson("/espace-utilisateur/kanban/columns/{$devCol->id}", [
            'title' => 'En Développement Actif',
            'wip_limit' => 3,
        ]);
        $updateColRes->assertJson(['success' => true]);
        $this->assertEquals(3, $devCol->fresh()->wip_limit);
    }

    public function test_user_can_track_time_and_cover_image_on_card()
    {
        $user = User::factory()->create();
        $board = KanbanBoard::create([
            'user_id' => $user->id,
            'title' => 'Time Tracking Board',
        ]);
        $col = KanbanColumn::create(['board_id' => $board->id, 'title' => 'À faire', 'order' => 1]);

        // Create card with estimation
        $createRes = $this->actingAs($user, 'web')->postJson("/espace-utilisateur/kanban/columns/{$col->id}/cards", [
            'title' => 'Développer Passerelle MTN MoMo',
            'estimated_hours' => 8.5,
            'spent_hours' => 2,
            'cover_image' => 'storage/kanban_attachments/mock_cover.png',
        ]);

        $createRes->assertJson(['success' => true]);
        $card = KanbanCard::where('title', 'Développer Passerelle MTN MoMo')->first();
        $this->assertNotNull($card);
        $this->assertEquals(8.5, (float)$card->estimated_hours);
        $this->assertEquals(2.0, (float)$card->spent_hours);
        $this->assertEquals('storage/kanban_attachments/mock_cover.png', $card->cover_image);

        // Update card spent time
        $updateRes = $this->actingAs($user, 'web')->putJson("/espace-utilisateur/kanban/cards/{$card->id}", [
            'title' => 'Développer Passerelle MTN MoMo - Validé',
            'estimated_hours' => 8.5,
            'spent_hours' => 7.5,
        ]);
        $updateRes->assertJson(['success' => true]);
        $this->assertEquals(7.5, (float)$card->fresh()->spent_hours);
    }
}
