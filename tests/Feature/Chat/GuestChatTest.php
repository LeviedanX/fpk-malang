<?php

namespace Tests\Feature\Chat;

use App\Enums\ChatConversationStatus;
use App\Enums\ChatSender;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuestChatTest extends TestCase
{
    use DatabaseTransactions;

    private function startConversation(string $body = 'Halo admin'): array
    {
        $response = $this->postJson(route('chat.store'), ['body' => $body]);
        $response->assertCreated();

        $token = $response->json('token');

        return [$token, ChatConversation::query()->where('public_token', $token)->firstOrFail()];
    }

    private function asGuest(string $token): static
    {
        return $this->withHeader('X-Chat-Token', $token);
    }

    public function test_first_message_creates_the_conversation_and_returns_a_token(): void
    {
        [$token, $conversation] = $this->startConversation('Kapan agenda berikutnya?');

        $this->assertSame(64, strlen($token));
        $this->assertSame(1, $conversation->messages_count);
        $this->assertSame(1, $conversation->admin_unread_count);
        $this->assertSame(0, $conversation->guest_unread_count);
        $this->assertSame(ChatConversationStatus::Open, $conversation->status);
        $this->assertNotNull($conversation->last_message_at);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender' => ChatSender::Guest->value,
            'body' => 'Kapan agenda berikutnya?',
        ]);
    }

    public function test_visitor_ip_and_device_are_recorded_for_admin_tracking(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.42'])
            ->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1')
            ->postJson(route('chat.store'), ['body' => 'Halo']);

        $response->assertCreated();

        $conversation = ChatConversation::query()->latest('id')->firstOrFail();

        $this->assertSame('203.0.113.42', $conversation->ip_address);
        $this->assertSame('mobile', $conversation->device_type);
        $this->assertSame('Safari', $conversation->browser_name);
        $this->assertSame('iOS', $conversation->platform_name);
        $this->assertSame(64, strlen($conversation->visitor_hash));
        $this->assertStringStartsWith('Tamu ', $conversation->guest_label);
    }

    public function test_conversations_from_the_same_device_share_a_fingerprint(): void
    {
        $context = fn () => $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0');

        $context()->postJson(route('chat.store'), ['body' => 'Pesan pertama'])->assertCreated();
        $context()->postJson(route('chat.store'), ['body' => 'Pesan kedua'])->assertCreated();

        $conversations = ChatConversation::query()->latest('id')->limit(2)->get();

        $this->assertCount(2, $conversations);
        $this->assertSame(
            $conversations[0]->visitor_hash,
            $conversations[1]->visitor_hash,
            'Perangkat yang sama harus menghasilkan sidik jari yang sama.',
        );
        // Sidik jari yang sama tidak boleh menyatukan percakapan: keduanya
        // tetap terpisah karena masing-masing punya token sendiri.
        $this->assertNotSame($conversations[0]->public_token, $conversations[1]->public_token);
    }

    public function test_guest_can_send_an_image(): void
    {
        Storage::fake('public');

        $response = $this->postJson(route('chat.store'), [
            'image' => UploadedFile::fake()->image('bukti.jpg', 800, 600),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('message.sender', 'guest');
        $this->assertNotNull($response->json('message.image'));

        $message = ChatMessage::query()->latest('id')->firstOrFail();
        $this->assertNotNull($message->attachment_path);
        Storage::disk('public')->assertExists($message->attachment_path);
    }

    public function test_message_requires_text_or_an_image(): void
    {
        $this->postJson(route('chat.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');

        $this->assertSame(0, ChatConversation::query()->count());
    }

    public function test_polling_returns_no_content_when_nothing_is_new(): void
    {
        [$token, $conversation] = $this->startConversation();
        $lastId = (int) $conversation->messages()->max('id');

        $this->asGuest($token)
            ->getJson(route('chat.poll', ['after' => $lastId]))
            ->assertNoContent();
    }

    public function test_polling_delivers_admin_replies_and_clears_the_guest_badge(): void
    {
        [$token, $conversation] = $this->startConversation();
        $lastId = (int) $conversation->messages()->max('id');

        $admin = User::factory()->create();
        $this->actingAs($admin)
            ->post(route('admin.chat.reply', $conversation), ['body' => 'Terima kasih sudah menghubungi kami.'])
            ->assertRedirect();

        $this->assertSame(1, $conversation->fresh()->guest_unread_count);

        // Sesi admin tidak boleh ikut terbawa ke permintaan tamu.
        $this->flushSession();
        auth()->logout();

        $response = $this->asGuest($token)
            ->getJson(route('chat.poll', ['after' => $lastId, 'seen' => 1]));

        $response->assertOk();
        $response->assertJsonCount(1, 'messages');
        $response->assertJsonPath('messages.0.sender', 'admin');
        $response->assertJsonPath('messages.0.body', 'Terima kasih sudah menghubungi kami.');

        $this->assertSame(0, $conversation->fresh()->guest_unread_count);
    }

    public function test_chat_responses_are_never_cached(): void
    {
        [$token] = $this->startConversation();

        $response = $this->asGuest($token)->getJson(route('chat.show'))->assertOk();
        $cacheControl = (string) $response->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('private', $cacheControl);
    }

    /**
     * Widget chat tidak boleh mengorbankan cacheability halaman publik: tidak
     * ada cookie maupun token yang tercetak di HTML, sehingga CDN tetap boleh
     * membagikan satu salinan halaman kepada semua pengunjung.
     */
    public function test_public_pages_stay_cacheable_with_the_widget_present(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $this->assertStringContainsString('public', (string) $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->headers->getCookies());
        $this->assertStringNotContainsString('X-Chat-Token', $response->getContent());
    }

    public function test_an_unknown_token_cannot_read_another_conversation(): void
    {
        [, $conversation] = $this->startConversation('Rahasia');

        $response = $this->asGuest(str_repeat('a', 64))->getJson(route('chat.show'));

        $response->assertOk();
        $response->assertJsonPath('conversation', null);
        $response->assertJsonPath('messages', []);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'body' => 'Rahasia',
        ]);
    }

    public function test_blocked_conversation_is_rejected(): void
    {
        [$token, $conversation] = $this->startConversation();
        $conversation->update(['is_blocked' => true]);

        $this->asGuest($token)
            ->postJson(route('chat.store'), ['body' => 'Halo lagi'])
            ->assertForbidden();

        $this->assertSame(1, $conversation->fresh()->messages_count);
    }

    public function test_cross_origin_requests_are_rejected(): void
    {
        $this->withHeader('Origin', 'https://penyerang.example')
            ->postJson(route('chat.store'), ['body' => 'Halo'])
            ->assertForbidden();

        $this->assertSame(0, ChatConversation::query()->count());
    }

    public function test_guest_message_reopens_a_closed_conversation(): void
    {
        [$token, $conversation] = $this->startConversation();
        $conversation->update(['status' => ChatConversationStatus::Closed]);

        $this->asGuest($token)
            ->postJson(route('chat.store'), ['body' => 'Ada pertanyaan lagi'])
            ->assertCreated();

        $this->assertSame(ChatConversationStatus::Open, $conversation->fresh()->status);
    }

    public function test_history_walks_backwards_through_older_messages(): void
    {
        [$token, $conversation] = $this->startConversation('Pesan 1');

        foreach (range(2, 5) as $index) {
            $this->asGuest($token)
                ->postJson(route('chat.store'), ['body' => "Pesan {$index}"])
                ->assertCreated();
        }

        $oldest = (int) $conversation->messages()->min('id');

        $response = $this->asGuest($token)->getJson(route('chat.history', ['before' => $oldest + 3]));

        $response->assertOk();
        $response->assertJsonPath('messages.0.body', 'Pesan 1');
        $this->assertCount(3, $response->json('messages'));
    }

    public function test_widget_is_present_on_the_public_homepage(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('chat-launcher', escape: false)
            ->assertSee('guestChat(', escape: false);
    }
}
