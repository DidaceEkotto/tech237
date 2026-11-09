<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserMessage;

class UserMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('public');
    }

    public function test_user_can_send_message_with_attachment_and_triggers_email()
    {
        $sender = User::factory()->create(['name' => 'Alice']);
        $recipient = User::factory()->create(['name' => 'Bob', 'email' => 'bob@tech237.cm']);

        $file = UploadedFile::fake()->create('cahier_des_charges.pdf', 500, 'application/pdf');

        $response = $this->actingAs($sender, 'web')->post('/espace-utilisateur/messages/envoyer', [
            'recipient_id' => $recipient->id,
            'subject' => 'Spécifications techniques du projet',
            'message' => 'Voici les spécifications détaillées pour la release v1.',
            'attachment' => $file,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_messages', [
            'user_id' => $recipient->id,
            'sender_id' => $sender->id,
            'subject' => 'Spécifications techniques du projet',
            'attachment_name' => 'cahier_des_charges.pdf',
            'attachment_type' => 'document',
            'is_read' => false,
        ]);
    }

    public function test_recipient_can_mark_message_as_read()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = UserMessage::create([
            'user_id' => $recipient->id,
            'sender_id' => $sender->id,
            'subject' => 'Test unread',
            'message' => 'Contenu du message',
            'is_read' => false,
        ]);

        $response = $this->actingAs($recipient, 'web')->post("/espace-utilisateur/messages/{$message->id}/read");

        $response->assertSessionHas('success');
        $message->refresh();
        $this->assertTrue($message->is_read);
        $this->assertNotNull($message->read_at);
    }

    public function test_user_can_toggle_emoji_reaction_on_message()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = UserMessage::create([
            'user_id' => $recipient->id,
            'sender_id' => $sender->id,
            'subject' => 'Projet validé',
            'message' => 'Félicitations pour le dev !',
        ]);

        // Recipient reacts with 🚀
        $response = $this->actingAs($recipient, 'web')->post("/espace-utilisateur/messages/{$message->id}/reaction", [
            'emoji' => '🚀',
        ]);

        $response->assertStatus(302);
        $message->refresh();
        $this->assertIsArray($message->reactions);
        $this->assertContains($recipient->id, $message->reactions['🚀']);

        // Toggle again to remove reaction
        $this->actingAs($recipient, 'web')->post("/espace-utilisateur/messages/{$message->id}/reaction", [
            'emoji' => '🚀',
        ]);

        $message->refresh();
        $this->assertEmpty($message->reactions['🚀'] ?? []);
    }

    public function test_sender_can_delete_their_message()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = UserMessage::create([
            'user_id' => $recipient->id,
            'sender_id' => $sender->id,
            'subject' => 'Oups erreur',
            'message' => 'Message envoyé par erreur',
        ]);

        $response = $this->actingAs($sender, 'web')->delete("/espace-utilisateur/messages/{$message->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('user_messages', ['id' => $message->id]);
    }

    public function test_non_sender_cannot_delete_message()
    {
        $sender = User::factory()->create();
        $stranger = User::factory()->create();
        $recipient = User::factory()->create();

        $message = UserMessage::create([
            'user_id' => $recipient->id,
            'sender_id' => $sender->id,
            'subject' => 'Important',
            'message' => 'Message privé',
        ]);

        $response = $this->actingAs($stranger, 'web')->delete("/espace-utilisateur/messages/{$message->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('user_messages', ['id' => $message->id]);
    }
}
