<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\TechPoll;
use App\Models\TechPollOption;
use App\Models\Article;
use App\Models\JobOffer;
use App\Models\UserBookmark;
use App\Models\UserAlertPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RetentionSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upvote_project_and_earn_xp()
    {
        $creator = User::factory()->create(['xp' => 10]);
        $voter = User::factory()->create(['xp' => 0]);

        $project = Project::create([
            'user_id' => $creator->id,
            'name' => 'TechProject 237',
            'slug' => 'techproject-237',
            'description' => 'A wonderful project',
            'project_type' => 'web',
            'status' => 'published',
            'upvotes_count' => 0,
        ]);

        $response = $this->actingAs($voter)->postJson("/projets/{$project->id}/upvote");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'upvoted' => true,
            'upvotes_count' => 1,
        ]);

        $project->refresh();
        $this->assertEquals(1, $project->upvotes_count);

        $voter->refresh();
        $creator->refresh();
        $this->assertEquals(2, $voter->xp);
        $this->assertEquals(15, $creator->xp);

        // Toggle off
        $responseToggle = $this->actingAs($voter)->postJson("/projets/{$project->id}/upvote");
        $responseToggle->assertStatus(200);
        $responseToggle->assertJson([
            'success' => true,
            'upvoted' => false,
            'upvotes_count' => 0,
        ]);
    }

    public function test_guest_cannot_upvote_project()
    {
        $creator = User::factory()->create();
        $project = Project::create([
            'user_id' => $creator->id,
            'name' => 'Guest Target Project',
            'slug' => 'guest-target-project',
            'description' => 'Test',
            'project_type' => 'web',
            'status' => 'published',
            'upvotes_count' => 0,
        ]);

        $response = $this->postJson("/projets/{$project->id}/upvote");
        $response->assertStatus(401);
    }

    public function test_user_can_vote_in_active_daily_poll_and_anti_duplicate_enforced()
    {
        $user = User::factory()->create(['xp' => 0]);

        $poll = TechPoll::create([
            'question' => 'Quel framework backend préférez-vous ?',
            'is_active' => true,
        ]);

        $option1 = TechPollOption::create([
            'tech_poll_id' => $poll->id,
            'label' => 'Laravel',
            'votes_count' => 0,
        ]);

        $option2 = TechPollOption::create([
            'tech_poll_id' => $poll->id,
            'label' => 'Node.js / Express',
            'votes_count' => 0,
        ]);

        $response = $this->actingAs($user)->postJson("/sondages/{$poll->id}/vote", [
            'option_id' => $option1->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $option1->refresh();
        $this->assertEquals(1, $option1->votes_count);

        $user->refresh();
        $this->assertEquals(3, $user->xp);

        // Duplicate vote attempt by same user
        $dupResponse = $this->actingAs($user)->postJson("/sondages/{$poll->id}/vote", [
            'option_id' => $option2->id,
        ]);

        $dupResponse->assertStatus(422);
    }

    public function test_user_can_toggle_bookmarks()
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Bookmark Target Project',
            'slug' => 'bookmark-target-project',
            'description' => 'Target',
            'project_type' => 'web',
            'status' => 'published',
        ]);

        // Add bookmark
        $response = $this->actingAs($user)->postJson('/espace-utilisateur/favoris/toggle', [
            'item_type' => 'project',
            'item_id' => $project->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'bookmarked' => true,
        ]);

        $this->assertDatabaseHas('user_bookmarks', [
            'user_id' => $user->id,
            'bookmarkable_type' => 'project',
            'bookmarkable_id' => $project->id,
        ]);

        // Remove bookmark
        $responseRemove = $this->actingAs($user)->postJson('/espace-utilisateur/favoris/toggle', [
            'item_type' => 'project',
            'item_id' => $project->id,
        ]);

        $responseRemove->assertStatus(200);
        $responseRemove->assertJson([
            'success' => true,
            'bookmarked' => false,
        ]);

        $this->assertDatabaseMissing('user_bookmarks', [
            'user_id' => $user->id,
            'bookmarkable_type' => 'project',
            'bookmarkable_id' => $project->id,
        ]);
    }

    public function test_user_can_update_alert_preferences()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/espace-utilisateur/alertes', [
            'alert_jobs' => false,
            'alert_conferences' => true,
            'alert_projects' => false,
            'alert_newsletter' => true,
        ]);

        $this->assertDatabaseHas('user_alert_preferences', [
            'user_id' => $user->id,
            'alert_jobs' => false,
            'alert_conferences' => true,
            'alert_projects' => false,
            'alert_newsletter' => true,
        ]);
    }

    public function test_guest_can_vote_in_active_daily_poll_and_cannot_vote_twice()
    {
        $poll = TechPoll::create([
            'question' => 'Quel OS mobile préférez-vous ?',
            'is_active' => true,
        ]);

        $option1 = TechPollOption::create([
            'tech_poll_id' => $poll->id,
            'label' => 'Android',
            'votes_count' => 0,
        ]);

        $option2 = TechPollOption::create([
            'tech_poll_id' => $poll->id,
            'label' => 'iOS',
            'votes_count' => 0,
        ]);

        $guestToken = 'g_test_token_12345';

        // 1. Guest can vote without authentication
        $response = $this->postJson("/sondages/{$poll->id}/vote", [
            'option_id' => $option1->id,
            'guest_token' => $guestToken,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'poll' => [
                'has_voted' => true,
            ],
        ]);
        $response->assertCookie('voted_poll_' . $poll->id, '1');

        $option1->refresh();
        $this->assertEquals(1, $option1->votes_count);

        // 2. Guest cannot vote twice with same guest token or session
        $dupResponse = $this->postJson("/sondages/{$poll->id}/vote", [
            'option_id' => $option2->id,
            'guest_token' => $guestToken,
        ]);

        $dupResponse->assertStatus(422);
        $dupResponse->assertJson(['error' => 'Vous avez déjà voté pour ce sondage.']);

        $option2->refresh();
        $this->assertEquals(0, $option2->votes_count);
    }
}
