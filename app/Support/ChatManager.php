<?php

namespace App\Support;

use App\Enums\ChatConversationStatus;
use App\Enums\ChatSender;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Single write path for the guest ↔ admin chat.
 *
 * Both sides funnel through here so the denormalised counters on
 * chat_conversations (unread badges, activity timestamps) can never drift from
 * the rows in chat_messages: every write happens inside one transaction, and
 * the counters are bumped with SQL expressions rather than read-modify-write so
 * concurrent messages cannot lose an increment.
 */
class ChatManager
{
    public function startConversation(Request $request): ChatConversation
    {
        return ChatConversation::create([
            ...VisitorIdentity::fromRequest($request),
            'public_token' => ChatConversation::newToken(),
        ]);
    }

    /**
     * Refresh the tracking fields when a returning guest reconnects from a new
     * network or device, so the admin always sees where the guest is *now*.
     */
    public function touchIdentity(ChatConversation $conversation, Request $request): void
    {
        $identity = VisitorIdentity::fromRequest($request);

        if ($identity['visitor_hash'] === $conversation->visitor_hash) {
            return;
        }

        // The label stays pinned to the first fingerprint: the admin knows the
        // guest by that handle, and renaming a live thread would be confusing.
        unset($identity['guest_label']);

        $conversation->forceFill($identity)->save();
    }

    public function postGuestMessage(
        ChatConversation $conversation,
        ?string $body,
        ?UploadedFile $image,
    ): ChatMessage {
        return $this->post($conversation, ChatSender::Guest, $body, $image, null);
    }

    public function postAdminMessage(
        ChatConversation $conversation,
        User $admin,
        ?string $body,
        ?UploadedFile $image,
    ): ChatMessage {
        return $this->post($conversation, ChatSender::Admin, $body, $image, $admin);
    }

    private function post(
        ChatConversation $conversation,
        ChatSender $sender,
        ?string $body,
        ?UploadedFile $image,
        ?User $admin,
    ): ChatMessage {
        $media = new MediaTransaction;
        $attachmentPath = $image ? $media->storeImage($image, 'chat') : null;
        $dimensions = $attachmentPath ? $this->dimensions($attachmentPath) : [null, null];

        return $media->commit(function () use (
            $conversation,
            $sender,
            $body,
            $attachmentPath,
            $dimensions,
            $admin,
        ): ChatMessage {
            $message = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender' => $sender,
                'user_id' => $admin?->id,
                'body' => $body,
                'attachment_path' => $attachmentPath,
                'attachment_width' => $dimensions[0],
                'attachment_height' => $dimensions[1],
            ]);

            $isGuest = $sender === ChatSender::Guest;
            $now = $message->created_at;

            ChatConversation::query()
                ->whereKey($conversation->id)
                ->update([
                    'messages_count' => DB::raw('messages_count + 1'),
                    // Only the receiving side's badge grows.
                    'admin_unread_count' => $isGuest
                        ? DB::raw('admin_unread_count + 1')
                        : DB::raw('admin_unread_count'),
                    'guest_unread_count' => $isGuest
                        ? DB::raw('guest_unread_count')
                        : DB::raw('guest_unread_count + 1'),
                    'last_message_at' => $now,
                    'last_guest_message_at' => $isGuest ? $now : $conversation->last_guest_message_at,
                    // A guest writing again reopens a thread the admin had
                    // marked done, so the reply cannot be silently dropped.
                    'status' => $isGuest
                        ? ChatConversationStatus::Open->value
                        : $conversation->status->value,
                    'updated_at' => $now,
                ]);

            return $message;
        });
    }

    /**
     * Mark every admin message the guest has now seen as read and clear the
     * guest badge. Returns early when there is nothing to do so the common
     * polling path stays read-only.
     */
    public function markReadForGuest(ChatConversation $conversation): void
    {
        if ($conversation->guest_unread_count === 0) {
            return;
        }

        DB::transaction(function () use ($conversation): void {
            ChatMessage::query()
                ->where('conversation_id', $conversation->id)
                ->where('sender', ChatSender::Admin)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            ChatConversation::query()
                ->whereKey($conversation->id)
                ->update(['guest_unread_count' => 0]);
        });

        $conversation->guest_unread_count = 0;
    }

    public function markReadForAdmin(ChatConversation $conversation): void
    {
        if ($conversation->admin_unread_count === 0) {
            return;
        }

        DB::transaction(function () use ($conversation): void {
            ChatMessage::query()
                ->where('conversation_id', $conversation->id)
                ->where('sender', ChatSender::Guest)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            ChatConversation::query()
                ->whereKey($conversation->id)
                ->update(['admin_unread_count' => 0]);
        });

        $conversation->admin_unread_count = 0;
    }

    /**
     * Total unread guest messages across the inbox, for the sidebar badge.
     */
    public static function adminUnreadTotal(): int
    {
        return (int) ChatConversation::query()
            ->where('admin_unread_count', '>', 0)
            ->sum('admin_unread_count');
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function dimensions(string $path): array
    {
        $disk = Storage::disk(config('fpk.uploads.disk', 'public'));

        // Intrinsic size lets the bubble reserve space before the image loads,
        // which keeps the transcript from jumping as attachments arrive.
        $size = @getimagesize($disk->path($path));

        if ($size === false) {
            return [null, null];
        }

        return [
            min(65535, (int) $size[0]) ?: null,
            min(65535, (int) $size[1]) ?: null,
        ];
    }
}
