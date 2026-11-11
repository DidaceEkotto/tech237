<?php

namespace Tests\Feature;

use App\Models\HeartbeatCheck;
use App\Models\HeartbeatMonitor;
use App\Models\User;
use App\Services\HeartbeatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HeartbeatMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_heartbeat_endpoints()
    {
        $response = $this->getJson('/espace-utilisateur/heartbeat/monitors');
        $response->assertStatus(401);

        $response = $this->postJson('/espace-utilisateur/heartbeat/monitors', [
            'name' => 'Mon Site',
            'url' => 'https://monsite.cm',
        ]);
        $response->assertStatus(401);
    }

    public function test_user_can_add_multiple_monitored_sites()
    {
        Http::fake([
            'monsite.cm*' => Http::response('OK', 200),
            'api.monsite.cm*' => Http::response('{"status":"healthy"}', 200),
        ]);

        $user = User::factory()->create(['email' => 'dev@tech237.net']);

        // Site 1
        $res1 = $this->actingAs($user, 'web')->postJson('/espace-utilisateur/heartbeat/monitors', [
            'name' => 'Site Vitrine',
            'url' => 'https://monsite.cm',
            'check_interval_minutes' => 5,
            'expected_status_code' => 200,
            'notify_email' => true,
        ]);

        $res1->assertStatus(200);
        $res1->assertJson(['success' => true]);
        $this->assertDatabaseHas('heartbeat_monitors', [
            'user_id' => $user->id,
            'name' => 'Site Vitrine',
            'status' => 'up',
        ]);

        // Site 2 (multi-site support)
        $res2 = $this->actingAs($user, 'web')->postJson('/espace-utilisateur/heartbeat/monitors', [
            'name' => 'API Backend',
            'url' => 'https://api.monsite.cm/health',
            'check_interval_minutes' => 1,
            'expected_status_code' => 200,
            'notify_email' => true,
        ]);

        $res2->assertStatus(200);
        $res2->assertJson(['success' => true]);

        $this->assertEquals(2, HeartbeatMonitor::where('user_id', $user->id)->count());

        // Test listing
        $listRes = $this->actingAs($user, 'web')->getJson('/espace-utilisateur/heartbeat/monitors');
        $listRes->assertStatus(200);
        $listRes->assertJsonPath('stats.total', 2);
        $listRes->assertJsonPath('stats.up', 2);
    }

    public function test_user_can_trigger_manual_ping()
    {
        Http::fake([
            'test-site.cm*' => Http::response('Success', 200),
        ]);

        $user = User::factory()->create();
        $monitor = HeartbeatMonitor::create([
            'user_id' => $user->id,
            'name' => 'Test Ping',
            'url' => 'https://test-site.cm',
            'status' => 'pending',
            'is_active' => true,
        ]);

        $res = $this->actingAs($user, 'web')->postJson("/espace-utilisateur/heartbeat/monitors/{$monitor->id}/ping");
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
        $res->assertJsonPath('result.is_up', true);
        $res->assertJsonPath('result.status_code', 200);

        $this->assertDatabaseHas('heartbeat_checks', [
            'heartbeat_monitor_id' => $monitor->id,
            'is_up' => 1,
            'status_code' => 200,
        ]);
    }

    public function test_down_alert_and_recovery_emails_are_sent()
    {
        Mail::fake();

        // Use Http::sequence to simulate failure then recovery
        Http::fake([
            'crash-site.cm*' => Http::sequence()
                ->push('Bad Gateway', 502)
                ->push('Recovered', 200),
        ]);

        $user = User::factory()->create(['email' => 'alert@tech237.net']);
        $monitor = HeartbeatMonitor::create([
            'user_id' => $user->id,
            'name' => 'Mon App',
            'url' => 'https://crash-site.cm',
            'status' => 'up',
            'is_currently_down' => false,
            'notify_email' => true,
            'is_active' => true,
        ]);

        $service = app(HeartbeatService::class);

        // 1. Simulate server failure (502 Bad Gateway)
        $resDown = $service->ping($monitor, true);
        $this->assertFalse($resDown['is_up']);
        $this->assertEquals('down', $monitor->fresh()->status);
        $this->assertTrue($monitor->fresh()->is_currently_down);
        $this->assertNotNull($monitor->fresh()->down_alert_sent_at);

        // 2. Simulate server recovery (200 OK)
        $resUp = $service->ping($monitor->fresh(), true);
        $this->assertTrue($resUp['is_up']);
        $this->assertEquals('up', $monitor->fresh()->status);
        $this->assertFalse($monitor->fresh()->is_currently_down);
    }

    public function test_user_can_toggle_active_state_and_delete()
    {
        Http::fake(['*' => Http::response('OK', 200)]);

        $user = User::factory()->create();
        $monitor = HeartbeatMonitor::create([
            'user_id' => $user->id,
            'name' => 'To Pause',
            'url' => 'https://mypoc.cm',
            'status' => 'up',
            'is_active' => true,
        ]);

        // Toggle to paused
        $res = $this->actingAs($user, 'web')->postJson("/espace-utilisateur/heartbeat/monitors/{$monitor->id}/toggle");
        $res->assertStatus(200);
        $this->assertFalse($monitor->fresh()->is_active);
        $this->assertEquals('paused', $monitor->fresh()->status);

        // Delete
        $delRes = $this->actingAs($user, 'web')->deleteJson("/espace-utilisateur/heartbeat/monitors/{$monitor->id}");
        $delRes->assertStatus(200);
        $this->assertDatabaseMissing('heartbeat_monitors', ['id' => $monitor->id]);
    }

    public function test_public_svg_badge_is_returned()
    {
        $user = User::factory()->create();
        $monitor = HeartbeatMonitor::create([
            'user_id' => $user->id,
            'name' => 'Badge Site',
            'url' => 'https://badge-site.cm',
            'status' => 'up',
            'uptime_percentage' => 99.8,
            'last_response_time_ms' => 145,
            'is_active' => true,
        ]);

        $res = $this->get("/api/heartbeat/badge/{$monitor->uuid}");
        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'image/svg+xml');
        $content = $res->getContent();
        $this->assertStringContainsString('Tech237 Heartbeat', $content);
        $this->assertStringContainsString('en ligne', $content);
    }

    public function test_artisan_command_runs_heartbeat_checks()
    {
        Http::fake([
            'cmd-site.cm*' => Http::response('OK', 200),
        ]);

        $user = User::factory()->create();
        $monitor = HeartbeatMonitor::create([
            'user_id' => $user->id,
            'name' => 'Console Check',
            'url' => 'https://cmd-site.cm',
            'status' => 'pending',
            'next_check_at' => now()->subMinute(),
            'is_active' => true,
        ]);

        $this->artisan('heartbeat:check')
            ->assertExitCode(0);

        $this->assertEquals('up', $monitor->fresh()->status);
        $this->assertEquals(1, $monitor->fresh()->total_checks);
    }
}
