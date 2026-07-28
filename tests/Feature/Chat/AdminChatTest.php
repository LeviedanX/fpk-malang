<?php

namespace Tests\Feature\Chat;

use App\Enums\ChatConversationStatus;
use App\Enums\ChatSender;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Support\ChatManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminChatTest extends TestCase
{
    use DatabaseTransactions;

    private function conversationWithGuestMessage(string $body = 'Halo admin'): ChatConversation
    {
        $this->postJson(route('chat.store'), ['body' => $body])->assertCreated();

        return ChatConversation::query()->latest('id')->firstOrFail();
    }

    public function test_guests_cannot_reach_the_admin_inbox(): void
    {
        $conversation = $this->conversationWithGuestMessage();

        $this->get(route('admin.chat.index'))->assertRedirect(route('login'));
        $this->get(route('admin.chat.show', $conversation))->assertRedirect(route('login'));
        $this->post(route('admin.chat.reply', $conversation), ['body' => 'x'])->assertRedirect(route('login'));
    }

    public function test_inbox_lists_conversations_with_tracking_details(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
            ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0')
            ->postJson(route('chat.store'), ['body' => 'Mohon informasinya'])
            ->assertCreated();

        $conversation = ChatConversation::query()->latest('id')->firstOrFail();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.chat.index'))
            ->assertOk()
            ->assertSee($conversation->guest_label)
            ->assertSee('Mohon informasinya')
            ->assertSee('203.0.113.9')
            ->assertSee('Chrome')
            ->assertSee('Windows');
    }

    public function test_opening_a_thread_marks_guest_messages_read(): void
    {
        $conversation = $this->conversationWithGuestMessage();
        $this->assertSame(1, $conversation->admin_unread_count);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.chat.show', $conversation))
            ->assertOk()
            ->assertSee('Halo admin');

        $this->assertSame(0, $conversation->fresh()->admin_unread_count);
        $this->assertNotNull(
            ChatMessage::query()->where('conversation_id', $conversation->id)->first()->read_at,
        );
    }

    public function test_admin_reply_is_stored_and_raises_the_guest_badge(): void
    {
        $conversation = $this->conversationWithGuestMessage();
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.chat.reply', $conversation), ['body' => 'Baik, kami bantu.'])
            ->assertCreated()
            ->assertJsonPath('message.sender', 'admin')
            ->assertJsonPath('message.body', 'Baik, kami bantu.');

        $conversation->refresh();
        $this->assertSame(1, $conversation->guest_unread_count);
        $this->assertSame(2, $conversation->messages_count);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender' => ChatSender::Admin->value,
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_can_reply_with_an_image(): void
    {
        Storage::fake('public');

        $conversation = $this->conversationWithGuestMessage();

        $this->actingAs(User::factory()->create())
            ->postJson(route('admin.chat.reply', $conversation), [
                'image' => UploadedFile::fake()->image('brosur.png', 900, 600),
            ])
            ->assertCreated();

        $message = ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender', ChatSender::Admin)
            ->firstOrFail();

        $this->assertNotNull($message->attachment_path);
        Storage::disk('public')->assertExists($message->attachment_path);
    }

    public function test_admin_polling_returns_no_content_when_idle(): void
    {
        $conversation = $this->conversationWithGuestMessage();
        $lastId = (int) $conversation->messages()->max('id');

        $this->actingAs(User::factory()->create())
            ->getJson(route('admin.chat.poll', ['conversation' => $conversation, 'after' => $lastId]))
            ->assertNoContent();
    }

    public function test_unread_endpoint_reports_the_sidebar_total(): void
    {
        $this->conversationWithGuestMessage('Pesan satu');
        $this->conversationWithGuestMessage('Pesan dua');

        $this->actingAs(User::factory()->create())
            ->getJson(route('admin.chat.unread'))
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('conversations', 2);
    }

    public function test_admin_can_close_and_reopen_a_conversation(): void
    {
        $conversation = $this->conversationWithGuestMessage();
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.chat.status', $conversation), ['status' => 'closed'])
            ->assertRedirect();
        $this->assertSame(ChatConversationStatus::Closed, $conversation->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.chat.status', $conversation), ['status' => 'open'])
            ->assertRedirect();
        $this->assertSame(ChatConversationStatus::Open, $conversation->fresh()->status);
    }

    public function test_blocking_stops_the_guest_from_writing(): void
    {
        $conversation = $this->conversationWithGuestMessage();
        $token = $conversation->public_token;

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.chat.block', $conversation), ['blocked' => 1])
            ->assertRedirect();

        $this->flushSession();
        auth()->logout();

        $this->withHeader('X-Chat-Token', $token)
            ->postJson(route('chat.store'), ['body' => 'Masih boleh?'])
            ->assertForbidden();
    }

    public function test_deleting_a_conversation_removes_its_messages_and_attachments(): void
    {
        Storage::fake('public');

        $this->postJson(route('chat.store'), [
            'image' => UploadedFile::fake()->image('lampiran.jpg', 640, 480),
        ])->assertCreated();

        $conversation = ChatConversation::query()->latest('id')->firstOrFail();
        $path = $conversation->messages()->firstOrFail()->attachment_path;

        Storage::disk('public')->assertExists($path);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.chat.destroy', $conversation))
            ->assertRedirect(route('admin.chat.index'));

        $this->assertDatabaseMissing('chat_conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('chat_messages', ['conversation_id' => $conversation->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_sidebar_badge_reflects_unread_messages(): void
    {
        $this->conversationWithGuestMessage();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-chat-unread-badge', escape: false)
            ->assertSee('Chat Tamu');
    }

    public function test_unread_total_counts_only_guest_messages(): void
    {
        $conversation = $this->conversationWithGuestMessage();

        $this->assertSame(1, ChatManager::adminUnreadTotal());

        $this->actingAs(User::factory()->create())
            ->postJson(route('admin.chat.reply', $conversation), ['body' => 'Balasan admin'])
            ->assertCreated();

        // Balasan admin tidak boleh menaikkan lencana admin sendiri.
        $this->assertSame(1, ChatManager::adminUnreadTotal());
    }
}
