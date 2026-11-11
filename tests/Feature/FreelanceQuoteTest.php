<?php

namespace Tests\Feature;

use App\Models\FreelanceQuote;
use App\Models\FreelanceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreelanceQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_freelance_calculator_section_in_dashboard()
    {
        $user = User::factory()->create([
            'username' => 'freelancer237',
        ]);

        $response = $this->actingAs($user, 'web')->get('/espace-utilisateur?section=freelance');
        $response->assertStatus(200);

        // Alias /espace-utilisateur/devis redirect
        $responseAlias = $this->actingAs($user, 'web')->get('/espace-utilisateur/devis');
        $responseAlias->assertRedirect('/espace-utilisateur?section=freelance');

        // Alias /espace-utilisateur/calculateur redirect
        $responseAliasCalc = $this->actingAs($user, 'web')->get('/espace-utilisateur/calculateur');
        $responseAliasCalc->assertRedirect('/espace-utilisateur?section=freelance');
    }

    public function test_user_can_save_freelance_tjm_settings()
    {
        $user = User::factory()->create([
            'username' => 'tjmuser',
        ]);

        $payload = [
            'target_monthly_net' => 500000,
            'work_weeks_per_year' => 46,
            'work_days_per_week' => 5,
            'non_billable_percent' => 25,
            'tax_percent' => 5.5,
            'expenses' => [
                'internet' => 40000,
                'electricity' => 30000,
                'software' => 35000,
                'rent' => 25000,
                'other' => 15000,
            ],
            'computed_tjm' => 48500,
            'computed_thm' => 6900,
            'payment_info' => [
                'orange_money_phone' => '+237 690 12 34 56',
                'orange_money_name' => 'TJM User Dev',
                'mtn_momo_phone' => '+237 670 12 34 56',
                'mtn_momo_name' => 'TJM User Dev',
                'bank_name' => 'UBA Cameroun',
                'account_number' => '10033 00001 12345678901 23',
            ],
        ];

        $response = $this->actingAs($user, 'web')->postJson('/espace-utilisateur/freelance/settings', $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $setting = FreelanceSetting::where('user_id', $user->id)->first();
        $this->assertNotNull($setting);
        $this->assertEquals(500000, $setting->target_monthly_net);
        $this->assertEquals(48500, $setting->computed_tjm);
        $this->assertEquals(40000, $setting->expenses['internet']);
        $this->assertEquals('+237 690 12 34 56', $setting->payment_info['orange_money_phone']);
    }

    public function test_user_can_create_update_and_delete_freelance_quote()
    {
        $user = User::factory()->create([
            'username' => 'quotedev',
        ]);

        $payload = [
            'type' => 'quote',
            'quote_number' => 'DEV-2026-001',
            'title' => 'Développement Plateforme SaaS E-commerce',
            'client_name' => 'Jean-Paul Kamga',
            'client_company' => 'Kamga Tech SARL',
            'client_email' => 'jp@kamga.cm',
            'client_phone' => '+237699112233',
            'client_address' => 'Akwa, Douala',
            'issue_date' => '2026-11-11',
            'due_date' => '2026-12-11',
            'currency' => 'XAF',
            'items' => [
                [
                    'description' => 'Architecture & Modélisation Base de données',
                    'unit' => 'Jours',
                    'quantity' => 3,
                    'unit_price' => 50000,
                    'total' => 150000,
                ],
                [
                    'description' => 'Développement API REST Laravel & Authentification',
                    'unit' => 'Jours',
                    'quantity' => 7,
                    'unit_price' => 50000,
                    'total' => 350000,
                ],
            ],
            'subtotal' => 500000,
            'tax_rate' => 5.5,
            'tax_amount' => 27500,
            'discount_amount' => 0,
            'total_amount' => 527500,
            'payment_milestones' => [
                ['label' => 'Acompte démarrage', 'percentage' => 40, 'amount' => 211000],
                ['label' => 'Validation V1', 'percentage' => 30, 'amount' => 158250],
                ['label' => 'Solde livraison', 'percentage' => 30, 'amount' => 158250],
            ],
            'payment_methods' => [
                'orange_money' => ['phone' => '+237699112233', 'name' => 'Dev Test'],
                'mtn_momo' => ['phone' => '+237677112233', 'name' => 'Dev Test'],
            ],
            'notes' => 'Garantie 30 jours incluse.',
            'terms' => 'Validité 30 jours.',
            'status' => 'draft',
        ];

        // 1. Create quote
        $responseCreate = $this->actingAs($user, 'web')->postJson('/espace-utilisateur/freelance/quotes', $payload);
        $responseCreate->assertStatus(200);
        $responseCreate->assertJson(['success' => true]);

        $quote = FreelanceQuote::where('user_id', $user->id)->first();
        $this->assertNotNull($quote);
        $this->assertEquals('DEV-2026-001', $quote->quote_number);
        $this->assertEquals(527500, $quote->total_amount);
        $this->assertNotEmpty($quote->uuid);

        // 2. Update quote status
        $responseStatus = $this->actingAs($user, 'web')->postJson("/espace-utilisateur/freelance/quotes/{$quote->id}/status", [
            'status' => 'sent',
        ]);
        $responseStatus->assertStatus(200);
        $this->assertEquals('sent', $quote->fresh()->status);

        // 3. Duplicate quote
        $responseDuplicate = $this->actingAs($user, 'web')->postJson("/espace-utilisateur/freelance/quotes/{$quote->id}/duplicate");
        $responseDuplicate->assertStatus(200);
        $this->assertEquals(2, FreelanceQuote::where('user_id', $user->id)->count());

        // 4. Delete duplicated quote
        $duplicatedQuote = FreelanceQuote::where('user_id', $user->id)->where('id', '!=', $quote->id)->first();
        $responseDelete = $this->actingAs($user, 'web')->deleteJson("/espace-utilisateur/freelance/quotes/{$duplicatedQuote->id}");
        $responseDelete->assertStatus(200);
        $this->assertEquals(1, FreelanceQuote::where('user_id', $user->id)->count());
    }

    public function test_client_can_view_public_quote_and_accept_in_one_click()
    {
        $user = User::factory()->create([
            'name' => 'Marc Eboule',
            'username' => 'marc-dev',
            'phone' => '+237699000000',
        ]);

        $quote = FreelanceQuote::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'user_id' => $user->id,
            'quote_number' => 'DEV-2026-TEST',
            'type' => 'quote',
            'status' => 'sent',
            'title' => 'Intégration Paiement Mobile Money OM/MoMo',
            'client_name' => 'Boutique Kribi',
            'client_email' => 'contact@kribi.cm',
            'client_phone' => '+237677000000',
            'issue_date' => '2026-11-11',
            'due_date' => '2026-12-11',
            'currency' => 'XAF',
            'items' => [
                [
                    'description' => 'Mise en place Webhook Orange Money & MTN MoMo',
                    'unit' => 'Jours',
                    'quantity' => 2,
                    'unit_price' => 45000,
                    'total' => 90000,
                ],
            ],
            'subtotal' => 90000,
            'total_amount' => 90000,
        ]);

        // 1. Client views public quote page (without authentication)
        $responseView = $this->get("/devis/{$quote->uuid}");
        $responseView->assertStatus(200);

        // 2. Client accepts the quote
        $responseAccept = $this->postJson("/devis/{$quote->uuid}/accept");
        $responseAccept->assertStatus(200);
        $responseAccept->assertJson(['success' => true]);

        $quote->refresh();
        $this->assertEquals('accepted', $quote->status);
        $this->assertNotNull($quote->accepted_at);
    }
}
