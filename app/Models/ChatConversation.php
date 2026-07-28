<?php

namespace App\Models;

use App\Enums\ChatConversationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'public_token',
    'visitor_hash',
    'guest_label',
    'ip_address',
    'user_agent',
    'device_type',
    'browser_name',
    'platform_name',
    'status',
    'is_blocked',
])]
class ChatConversation extends Model
{
    protected function casts(): array
    {
        return [
            'status' => ChatConversationStatus::class,
            'is_blocked' => 'boolean',
            'admin_unread_count' => 'integer',
            'guest_unread_count' => 'integer',
            'messages_count' => 'integer',
            'last_message_at' => 'datetime',
            'last_guest_message_at' => 'datetime',
        ];
    }

    /**
     * The token is a bearer credential: keep it out of anything that might be
     * serialised into an admin view or a log line.
     */
    protected $hidden = ['public_token'];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public static function newToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', ChatConversationStatus::Open);
    }

    /** Inbox ordering: newest activity first, stable tie-break on id. */
    public function scopeRecentFirst(Builder $query): Builder
    {
        return $query->orderByDesc('last_message_at')->orderByDesc('id');
    }

    public function deviceLabel(): string
    {
        $parts = array_filter([
            $this->browser_name,
            $this->platform_name,
        ]);

        if ($parts === []) {
            return $this->deviceTypeLabel();
        }

        return implode(' · ', $parts);
    }

    public function deviceTypeLabel(): string
    {
        return match ($this->device_type) {
            'mobile' => 'Ponsel',
            'tablet' => 'Tablet',
            'desktop' => 'Desktop',
            'bot' => 'Bot',
            default => 'Tidak dikenal',
        };
    }

    /** Short fingerprint shown next to the guest label in the admin inbox. */
    public function fingerprint(): string
    {
        return mb_strtoupper(mb_substr((string) $this->visitor_hash, 0, 8));
    }

    public function preview(int $limit = 60): string
    {
        $last = $this->relationLoaded('messages')
            ? $this->messages->last()
            : $this->messages()->latest('id')->first();

        if ($last === null) {
            return 'Belum ada pesan.';
        }

        if (blank($last->body)) {
            return '📷 Mengirim gambar';
        }

        return Str::limit($last->body, $limit);
    }
}
