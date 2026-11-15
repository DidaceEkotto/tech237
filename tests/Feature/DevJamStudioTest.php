<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\DevJam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevJamStudioTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_community_jam_submit_code_vote_and_chat(): void
    {
        $creator = User::factory()->create(['username' => 'jamcreator']);
        $participant = User::factory()->create(['username' => 'jamdev']);

        // 1. Create a Community Jam
        $createResp = $this->actingAs($creator)->post('/dev-jams/creer', [
            'title' => 'Tech237 UI Challenge',
            'theme' => 'Dashboard Moderne',
            'description' => 'Créez un mini dashboard interactif en HTML/CSS/JS.',
            'rules' => 'Code original uniquement.',
            'starts_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'prize_text' => 'Badge Or',
            'allow_external_links' => true,
        ]);

        $jam = DevJam::where('title', 'Tech237 UI Challenge')->firstOrFail();
        $this->assertEquals('community', $jam->mode);
        $createResp->assertRedirect(route('devjams.show', $jam->slug));

        // 2. Listing page renders both modes filter
        $this->get('/dev-jams')->assertOk();
        $this->get('/dev-jams?mode=community')->assertOk();

        // 3. Participant submits HTML/CSS/JS code in Studio
        $subResp = $this->actingAs($participant)->postJson("/dev-jams/{$jam->slug}/soumettre", [
            'title' => 'Neon Dashboard',
            'description' => 'Mon prototype',
            'submission_type' => 'studio',
            'html_code' => '<h1>Hello</h1>',
            'css_code' => 'h1 { color: red; }',
            'js_code' => 'console.log(1);',
        ]);
        $subResp->assertOk()->assertJsonPath('submission.title', 'Neon Dashboard');
        $submissionId = $subResp->json('submission.id');

        // 4. Self-vote blocked (422), creator vote allowed (200)
        $this->actingAs($participant)
            ->postJson("/dev-jams/{$jam->slug}/submissions/{$submissionId}/vote")
            ->assertStatus(422);

        $this->actingAs($creator)
            ->postJson("/dev-jams/{$jam->slug}/submissions/{$submissionId}/vote")
            ->assertOk()
            ->assertJsonPath('voted', true)
            ->assertJsonPath('votes_count', 1);

        // 5. Live chat message posting & polling
        $msgResp = $this->actingAs($creator)->postJson("/dev-jams/{$jam->slug}/messages", [
            'message' => 'Super design sur Neon Dashboard !',
            'submission_id' => $submissionId,
        ]);
        $msgResp->assertOk()->assertJsonPath('message.message', 'Super design sur Neon Dashboard !');

        $this->getJson("/dev-jams/{$jam->slug}/messages?after_id=0")
            ->assertOk()
            ->assertJsonCount(1, 'messages');
    }

    public function test_official_jam_can_be_listed_in_official_mode(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Tech237',
            'email' => 'admin-jam@tech237.test',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);

        DevJam::create([
            'slug' => 'tech237-official-jam-1',
            'title' => 'Tech237 Official Jam #1',
            'theme' => 'IA & Productivité',
            'description' => 'Hackathon officiel de la communauté.',
            'mode' => 'official',
            'status' => 'active',
            'creator_admin_id' => $admin->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(5),
        ]);

        $this->get('/dev-jams?mode=official')->assertOk();
        $this->get('/dev-jams/tech237-official-jam-1')->assertOk();
    }
}
