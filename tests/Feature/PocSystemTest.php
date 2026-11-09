<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Poc;
use App\Models\PocUpvote;
use Livewire\Livewire;
use App\Livewire\Admin\Pocs\Index as AdminPocsIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PocSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_view_pocs_feed()
    {
        $author = User::factory()->create();

        Poc::create([
            'user_id' => $author->id,
            'title' => 'Lancement de notre API Mobile Money',
            'slug' => 'lancement-de-notre-api-mobile-money',
            'content' => '<p>Nous sommes fiers de vous présenter notre API de paiement rapide pour le Cameroun.</p>',
            'tag' => 'fintech',
            'status' => 'published',
            'published_at' => now(),
            'upvotes_count' => 5,
        ]);

        $response = $this->get('/pocs');
        $response->assertStatus(200);
    }

    public function test_user_can_create_poc_and_it_is_published_by_default_with_xp()
    {
        $user = User::factory()->create(['xp' => 10]);

        $response = $this->actingAs($user)->post('/pocs', [
            'title' => 'Nouveau package Laravel pour le paiement Orange Money',
            'tag' => 'dev',
            'link_url' => 'https://github.com/tech237/orange-money-sdk',
            'content' => '<p>Voici un SDK complet prêt à l\'emploi pour intégrer l\'API.</p><ul><li>Simple</li><li>Testé</li></ul>',
        ]);

        $this->assertDatabaseHas('pocs', [
            'title' => 'Nouveau package Laravel pour le paiement Orange Money',
            'tag' => 'dev',
            'status' => 'published',
        ]);

        $user->refresh();
        $this->assertEquals(15, $user->xp); // +5 XP

        $poc = Poc::where('tag', 'dev')->first();
        $response->assertRedirect(route('pocs.show', $poc->slug));
    }

    public function test_poc_detail_increments_views()
    {
        $author = User::factory()->create();

        $poc = Poc::create([
            'user_id' => $author->id,
            'title' => 'Analyse des modèles LLM open-weight',
            'slug' => 'analyse-des-modeles-llm-open-weight',
            'content' => '<p>Comparatif complet des performances.</p>',
            'tag' => 'ia',
            'status' => 'published',
            'published_at' => now(),
            'views_count' => 0,
        ]);

        $response = $this->get("/pocs/{$poc->slug}");
        $response->assertStatus(200);

        $poc->refresh();
        $this->assertEquals(1, $poc->views_count);
    }

    public function test_user_and_guest_can_upvote_poc_with_anti_duplicate()
    {
        $author = User::factory()->create(['xp' => 0]);
        $voter = User::factory()->create(['xp' => 0]);

        $poc = Poc::create([
            'user_id' => $author->id,
            'title' => 'Open Source UI Components for Vue 3',
            'slug' => 'open-source-ui-components-for-vue-3',
            'content' => '<p>Bibliothèque de composants responsive.</p>',
            'tag' => 'dev',
            'status' => 'published',
            'published_at' => now(),
            'upvotes_count' => 0,
        ]);

        // 1. Authenticated user upvote
        $response = $this->actingAs($voter)->postJson("/pocs/{$poc->id}/upvote");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'upvoted' => true,
            'upvotes_count' => 1,
        ]);

        $poc->refresh();
        $this->assertEquals(1, $poc->upvotes_count);

        $voter->refresh();
        $author->refresh();
        $this->assertEquals(2, $voter->xp); // +2 XP voter
        $this->assertEquals(3, $author->xp); // +3 XP creator

        // 2. Toggle off
        $toggleResponse = $this->actingAs($voter)->postJson("/pocs/{$poc->id}/upvote");
        $toggleResponse->assertStatus(200);
        $toggleResponse->assertJson([
            'success' => true,
            'upvoted' => false,
            'upvotes_count' => 0,
        ]);

        // 3. Guest upvote with guest_token
        $guestToken = 'g_unique_guest_test_token';
        $guestResponse = $this->postJson("/pocs/{$poc->id}/upvote", [
            'guest_token' => $guestToken,
        ]);
        $guestResponse->assertStatus(200);
        $guestResponse->assertJson([
            'success' => true,
            'upvoted' => true,
            'upvotes_count' => 1,
        ]);
    }

    public function test_admin_can_manage_and_delete_poc_via_livewire()
    {
        $admin = Admin::factory()->create(['role' => 'super_admin']);
        $author = User::factory()->create();

        $poc = Poc::create([
            'user_id' => $author->id,
            'title' => 'POC à modérer',
            'slug' => 'poc-a-moderer',
            'content' => '<p>Contenu test.</p>',
            'tag' => 'general',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 1. Toggle status to draft
        Livewire::actingAs($admin, 'admin')
            ->test(AdminPocsIndex::class)
            ->call('toggleStatus', $poc->id);

        $poc->refresh();
        $this->assertEquals('draft', $poc->status);

        // 2. Delete POC
        Livewire::actingAs($admin, 'admin')
            ->test(AdminPocsIndex::class)
            ->call('confirmDelete', $poc->id)
            ->call('deletePoc');

        $this->assertDatabaseMissing('pocs', [
            'id' => $poc->id,
        ]);
    }
}
