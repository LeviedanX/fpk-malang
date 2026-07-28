<?php

namespace App\Console\Commands;

use App\Enums\ChatConversationStatus;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Support\ImageStorage;
use Illuminate\Console\Command;

/**
 * Keeps the chat tables and the upload disk from growing without bound.
 *
 * Conversations are deleted in small batches with their attachments so a large
 * backlog never turns into one long-running transaction, and the message rows
 * follow through the foreign key cascade.
 */
class PruneChatConversations extends Command
{
    protected $signature = 'chat:prune
        {--closed-days= : Override retensi percakapan yang sudah selesai}
        {--stale-days= : Override retensi percakapan yang tidak pernah dibalas}
        {--dry-run : Hanya laporkan tanpa menghapus}';

    protected $description = 'Menghapus percakapan chat tamu yang melewati masa retensi';

    public function handle(): int
    {
        $closedDays = (int) ($this->option('closed-days')
            ?: config('fpk.chat.prune_closed_after_days', 90));
        $staleDays = (int) ($this->option('stale-days')
            ?: config('fpk.chat.prune_stale_after_days', 180));

        if ($closedDays <= 0 && $staleDays <= 0) {
            $this->info('Pembersihan chat dinonaktifkan lewat konfigurasi.');

            return self::SUCCESS;
        }

        $query = ChatConversation::query()->where(function ($query) use ($closedDays, $staleDays): void {
            if ($closedDays > 0) {
                $query->orWhere(fn ($inner) => $inner
                    ->where('status', ChatConversationStatus::Closed)
                    ->where('last_message_at', '<', now()->subDays($closedDays)));
            }

            if ($staleDays > 0) {
                $query->orWhere(fn ($inner) => $inner
                    ->where('last_message_at', '<', now()->subDays($staleDays)));
            }
        });

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Tidak ada percakapan chat yang melewati masa retensi.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("{$total} percakapan akan dihapus. Jalankan tanpa --dry-run untuk mengeksekusi.");

            return self::SUCCESS;
        }

        $deleted = 0;
        $attachmentsRemoved = 0;

        $query->select(['id'])->chunkById(100, function ($conversations) use (&$deleted, &$attachmentsRemoved): void {
            $ids = $conversations->modelKeys();

            $attachments = ChatMessage::query()
                ->whereIn('conversation_id', $ids)
                ->whereNotNull('attachment_path')
                ->pluck('attachment_path');

            $deleted += ChatConversation::query()->whereKey($ids)->delete();

            foreach ($attachments as $path) {
                ImageStorage::delete($path);
                $attachmentsRemoved++;
            }
        });

        $this->info("{$deleted} percakapan dan {$attachmentsRemoved} lampiran dihapus.");

        return self::SUCCESS;
    }
}
