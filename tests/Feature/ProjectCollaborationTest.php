<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectCollaborator;
use App\Models\CollaborationBlock;
use App\Models\ProjectTimeline;
use App\Models\ProjectImage;

class ProjectCollaborationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function createProject(User $owner): Project
    {
        return Project::create([
            'user_id' => $owner->id,
            'name' => 'Tech Pay',
            'slug' => 'tech-pay-' . uniqid(),
            'project_type' => 'web',
            'description' => 'Plateforme de paiement mobile et web',
            'status' => 'published',
        ]);
    }

    public function test_owner_can_invite_collaborator_by_email()
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);

        $response = $this->actingAs($owner, 'web')->post("/espace-utilisateur/projets/{$project->id}/inviter", [
            'email' => 'collaborateur@tech237.cm',
            'role' => 'Lead Mobile',
            'message' => 'Viens travailler sur le projet avec nous !',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('project_invitations', [
            'project_id' => $project->id,
            'inviter_id' => $owner->id,
            'email' => 'collaborateur@tech237.cm',
            'status' => 'pending',
            'role' => 'Lead Mobile',
        ]);

        $invitation = ProjectInvitation::where('email', 'collaborateur@tech237.cm')->first();
        $this->assertNotNull($invitation->token);
        $this->assertEquals(64, strlen($invitation->token));
        $this->assertTrue(now()->addHours(47)->lessThan($invitation->expires_at));
    }

    public function test_owner_cannot_invite_themselves()
    {
        $owner = User::factory()->create(['email' => 'owner@tech237.cm']);
        $project = $this->createProject($owner);

        $response = $this->actingAs($owner, 'web')->post("/espace-utilisateur/projets/{$project->id}/inviter", [
            'email' => 'owner@tech237.cm',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_cannot_invite_if_blocked_by_user()
    {
        $owner = User::factory()->create(['email' => 'owner@tech237.cm']);
        $collaborator = User::factory()->create(['email' => 'collab@tech237.cm']);
        $project = $this->createProject($owner);

        // Collaborator blocked owner
        CollaborationBlock::create([
            'blocker_id' => $collaborator->id,
            'blocked_id' => $owner->id,
            'blocked_email' => $owner->email,
            'reason' => 'Refus définitif',
        ]);

        $response = $this->actingAs($owner, 'web')->post("/espace-utilisateur/projets/{$project->id}/inviter", [
            'email' => 'collab@tech237.cm',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_collaborator_can_accept_valid_invitation()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create(['email' => 'invited@tech237.cm']);
        $project = $this->createProject($owner);

        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'inviter_id' => $owner->id,
            'invited_user_id' => $collaborator->id,
            'email' => 'invited@tech237.cm',
            'token' => 'testtoken1234567890abcdef1234567890abcdef1234567890abcdef12345678',
            'status' => 'pending',
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->actingAs($collaborator, 'web')->post("/invitations/{$invitation->token}/accepter");

        $response->assertRedirect('/espace-utilisateur?section=projects');

        $invitation->refresh();
        $this->assertEquals('accepted', $invitation->status);
        $this->assertNotNull($invitation->accepted_at);

        $this->assertDatabaseHas('project_collaborators', [
            'project_id' => $project->id,
            'user_id' => $collaborator->id,
            'status' => 'active',
        ]);

        $this->assertTrue($project->isCollaborator($collaborator));
    }

    public function test_expired_invitation_cannot_be_accepted()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create(['email' => 'invited@tech237.cm']);
        $project = $this->createProject($owner);

        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'inviter_id' => $owner->id,
            'invited_user_id' => $collaborator->id,
            'email' => 'invited@tech237.cm',
            'token' => 'expiredtoken1234567890abcdef1234567890abcdef1234567890abcdef12345',
            'status' => 'pending',
            'expires_at' => now()->subHour(), // Expired 1 hour ago
        ]);

        $response = $this->actingAs($collaborator, 'web')->post("/invitations/{$invitation->token}/accepter");

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('project_collaborators', [
            'project_id' => $project->id,
            'user_id' => $collaborator->id,
        ]);
    }

    public function test_collaborator_can_block_future_collaboration()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create(['email' => 'target@tech237.cm']);
        $project = $this->createProject($owner);

        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'inviter_id' => $owner->id,
            'invited_user_id' => $collaborator->id,
            'email' => 'target@tech237.cm',
            'token' => 'blocktoken1234567890abcdef1234567890abcdef1234567890abcdef1234567',
            'status' => 'pending',
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->actingAs($collaborator, 'web')->post("/invitations/{$invitation->token}/bloquer");

        $this->assertDatabaseHas('collaboration_blocks', [
            'blocker_id' => $collaborator->id,
            'blocked_id' => $owner->id,
        ]);

        $this->assertTrue(CollaborationBlock::isBlocked($owner->id, $collaborator->id));
    }

    public function test_collaborator_submitted_timeline_is_pending_until_owner_validates()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $project = $this->createProject($owner);

        ProjectCollaborator::create([
            'project_id' => $project->id,
            'user_id' => $collaborator->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Collaborator submits timeline step
        $this->actingAs($collaborator, 'web')->post("/espace-utilisateur/projet/{$project->id}/timeline", [
            'title' => 'Intégration passerelle MTN MoMo',
            'description' => 'Test des webhooks et sandbox',
        ]);

        $timeline = ProjectTimeline::where('title', 'Intégration passerelle MTN MoMo')->first();
        $this->assertNotNull($timeline);
        $this->assertEquals('pending', $timeline->status);
        $this->assertEquals($collaborator->id, $timeline->user_id);

        // Owner validates step
        $this->actingAs($owner, 'web')->post("/espace-utilisateur/timelines/{$timeline->id}/valider", [
            'action' => 'approve',
        ]);

        $timeline->refresh();
        $this->assertEquals('approved', $timeline->status);
    }

    public function test_owner_can_revoke_collaborator()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $project = $this->createProject($owner);

        $collab = ProjectCollaborator::create([
            'project_id' => $project->id,
            'user_id' => $collaborator->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($owner, 'web')->delete("/espace-utilisateur/projets/{$project->id}/collaborateurs/{$collab->id}");

        $response->assertSessionHas('success');

        $collab->refresh();
        $this->assertEquals('revoked', $collab->status);
        $this->assertFalse($project->isCollaborator($collaborator));
    }
}
