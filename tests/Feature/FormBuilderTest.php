<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_forms_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->get('/espace-utilisateur/formulaires');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('User/Forms/Index'));
    }

    public function test_user_can_view_forms_guide()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->get('/espace-utilisateur/formulaires/guide');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('User/Forms/Guide'));
    }

    public function test_user_can_create_a_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/formulaires', [
            'title' => 'Mon premier formulaire Tech237',
            'description' => 'Un super questionnaire',
            'display_mode' => 'conversation',
            'theme_color' => '#00A859',
            'template' => 'blank',
        ]);

        $this->assertDatabaseHas('forms', [
            'user_id' => $user->id,
            'title' => 'Mon premier formulaire Tech237',
            'display_mode' => 'conversation',
        ]);

        $form = Form::where('user_id', $user->id)->first();
        $response->assertRedirect(route('user.forms.edit', $form->id));
        $this->assertGreaterThan(0, $form->fields()->count());
    }

    public function test_user_can_update_draft_form_and_publish_it()
    {
        $user = User::factory()->create();
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Sondage initial',
            'display_mode' => 'classic',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user, 'web')->put('/espace-utilisateur/formulaires/' . $form->id, [
            'title' => 'Sondage Modifié',
            'slug' => 'sondage-modifie',
            'description' => 'Nouvelle description',
            'display_mode' => 'conversation',
            'status' => 'published',
            'theme_color' => '#2563EB',
            'notify_email_on_response' => true,
            'notification_email' => 'alert@tech237.cm',
            'fields' => [
                [
                    'label' => 'Quel est votre prénom ?',
                    'type' => 'text',
                    'is_required' => true,
                    'placeholder' => 'ex: Christian',
                ],
                [
                    'label' => 'Votre niveau sur Laravel',
                    'type' => 'select',
                    'is_required' => false,
                    'options' => ['Débutant', 'Intermédiaire', 'Senior'],
                ],
            ],
        ]);

        $this->assertDatabaseHas('forms', [
            'id' => $form->id,
            'title' => 'Sondage Modifié',
            'slug' => 'sondage-modifie',
            'display_mode' => 'conversation',
            'status' => 'published',
        ]);

        $this->assertEquals(2, $form->fresh()->fields()->count());
    }

    public function test_user_cannot_modify_already_published_form()
    {
        $user = User::factory()->create();
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Sondage Déjà En Ligne',
            'display_mode' => 'conversation',
            'status' => 'published',
        ]);

        $response = $this->actingAs($user, 'web')->putJson('/espace-utilisateur/formulaires/' . $form->id, [
            'title' => 'Tentative de modification',
            'slug' => 'tentative',
            'display_mode' => 'classic',
            'status' => 'published',
            'fields' => [
                ['label' => 'Nouvelle question', 'type' => 'text']
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Ce formulaire a déjà été mis en ligne et ne peut plus être modifié afin de préserver l\'intégrité des réponses et questions. Pour apporter des modifications, vous pouvez le dupliquer.',
        ]);
    }

    public function test_public_user_can_view_published_form()
    {
        $user = User::factory()->create();
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Formulaire Public',
            'slug' => 'form-public-test',
            'status' => 'published',
            'display_mode' => 'conversation',
        ]);
        $form->fields()->create([
            'label' => 'Votre nom',
            'name' => 'field_nom',
            'type' => 'text',
            'order' => 0,
        ]);

        $response = $this->get('/f/form-public-test');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Forms/PublicShow'));
        $this->assertEquals(1, $form->fresh()->views_count);
    }

    public function test_public_user_can_submit_response_and_triggers_email()
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'creator@tech237.cm']);
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Formulaire avec email',
            'slug' => 'form-email-test',
            'status' => 'published',
            'display_mode' => 'classic',
            'notify_email_on_response' => true,
            'notification_email' => 'creator@tech237.cm',
            'send_respondent_confirmation' => true,
        ]);

        $f1 = $form->fields()->create([
            'label' => 'Nom complet',
            'name' => 'field_1_nom',
            'type' => 'text',
            'is_required' => true,
            'order' => 0,
        ]);

        $f2 = $form->fields()->create([
            'label' => 'Votre adresse email',
            'name' => 'field_2_email',
            'type' => 'email',
            'is_required' => true,
            'order' => 1,
        ]);

        $response = $this->postJson('/f/form-email-test/repondre', [
            'answers' => [
                $f1->id => 'Didace Ekotto',
                $f2->id => 'repondant@tech237.cm',
            ],
            'completed_in_seconds' => 45,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('form_responses', [
            'form_id' => $form->id,
            'respondent_email' => 'repondant@tech237.cm',
        ]);

        // Assert notification email was sent to creator and respondent
        Mail::assertSentCount(2);
    }

    public function test_form_respects_max_responses_quota()
    {
        $user = User::factory()->create();
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Formulaire Quota',
            'slug' => 'form-quota-test',
            'status' => 'published',
            'max_responses' => 1,
        ]);
        $field = $form->fields()->create([
            'label' => 'Question',
            'name' => 'q',
            'type' => 'text',
        ]);

        // First submission succeeds
        $res1 = $this->postJson('/f/form-quota-test/repondre', [
            'answers' => [$field->id => 'Premier'],
        ]);
        $res1->assertStatus(200);

        // Second submission is blocked due to quota
        $res2 = $this->postJson('/f/form-quota-test/repondre', [
            'answers' => [$field->id => 'Deuxieme'],
        ]);
        $res2->assertStatus(403);
    }

    public function test_form_respects_expiration_date()
    {
        $user = User::factory()->create();
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Formulaire Expiré',
            'slug' => 'form-expire-test',
            'status' => 'published',
            'expires_at' => now()->subDay(),
        ]);
        $field = $form->fields()->create([
            'label' => 'Question',
            'name' => 'q',
            'type' => 'text',
        ]);

        $response = $this->postJson('/f/form-expire-test/repondre', [
            'answers' => [$field->id => 'Trop tard'],
        ]);
        $response->assertStatus(403);
    }

    public function test_user_can_export_responses_to_csv()
    {
        $user = User::factory()->create();
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Formulaire CSV',
            'slug' => 'form-csv-test',
            'status' => 'published',
        ]);
        $field = $form->fields()->create([
            'label' => 'Nom',
            'name' => 'nom',
            'type' => 'text',
        ]);

        FormResponse::create([
            'form_id' => $form->id,
            'respondent_email' => 'jean@test.cm',
            'answers' => [$field->id => 'Jean Dupont'],
        ]);

        $response = $this->actingAs($user, 'web')->get('/espace-utilisateur/formulaires/' . $form->id . '/export-csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_user_can_duplicate_form()
    {
        $user = User::factory()->create();
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Formulaire Original',
            'slug' => 'form-original',
            'status' => 'published',
            'display_mode' => 'conversation',
        ]);
        $form->fields()->create([
            'label' => 'Question 1',
            'name' => 'q1',
            'type' => 'text',
        ]);

        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/formulaires/' . $form->id . '/dupliquer');

        $duplicated = Form::where('user_id', $user->id)
            ->where('title', 'Formulaire Original (Copie)')
            ->first();

        $this->assertNotNull($duplicated);
        $this->assertEquals('draft', $duplicated->status);
        $this->assertEquals(1, $duplicated->fields()->count());
        $response->assertRedirect(route('user.forms.edit', $duplicated->id));
    }

    public function test_user_can_toggle_status_between_published_and_closed()
    {
        $user = User::factory()->create();
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Formulaire Clôture',
            'slug' => 'form-cloture',
            'status' => 'published',
        ]);

        // Close form
        $response = $this->actingAs($user, 'web')->post('/espace-utilisateur/formulaires/' . $form->id . '/statut', [
            'status' => 'closed',
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals('closed', $form->fresh()->status);

        // Reopen form
        $response2 = $this->actingAs($user, 'web')->post('/espace-utilisateur/formulaires/' . $form->id . '/statut', [
            'status' => 'published',
        ]);
        $response2->assertSessionHas('success');
        $this->assertEquals('published', $form->fresh()->status);
    }

    public function test_seo_meta_tags_on_form_public_show()
    {
        $user = User::factory()->create(['name' => 'Paul Biya Tech']);
        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'Enquête Startups 237',
            'slug' => 'enquete-startups-237',
            'description' => 'Participez à notre grande enquête annuelle sur la tech camerounaise.',
            'status' => 'published',
            'display_mode' => 'classic',
        ]);

        $response = $this->get('/f/enquete-startups-237');
        $response->assertStatus(200);
        $response->assertSee('Formulaire : Enquête Startups 237', false);
        $response->assertSee('Participez à notre grande enquête annuelle sur la tech camerounaise.', false);
    }
}
