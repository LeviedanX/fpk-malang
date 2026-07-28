<?php

namespace App\Models;

use App\Enums\ChatSender;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'conversation_id',
    'sender',
    'user_id',
    'body',
    'attachment_path',
    'attachment_width',
    'attachment_height',
    'read_at',
])]
class ChatMessage extends Model
{
    protected function casts(): array
    {
        return [
            'sender' => ChatSender::class,
            'attachment_width' => 'integer',
            'attachment_height' => 'integer',
            'read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function attachmentUrl(): ?string
    {
        return $this->attachment_path
            ? Storage::disk(config('fpk.uploads.disk', 'public'))->url($this->attachment_path)
            : null;
    }

    /**
     * Wire format shared by the guest widget and the admin thread. Kept small
     * on purpose: every poll response is built from these arrays.
     *
     * @return array<string, mixed>
     */
    public function toWireArray(): array
    {
        return [
            'id' => $this->id,
            'sender' => $this->sender->value,
            'body' => $this->body,
            'image' => $this->attachmentUrl(),
            'image_width' => $this->attachment_width,
            'image_height' => $this->attachment_height,
            'at' => $this->created_at?->toIso8601String(),
            'time' => $this->created_at?->timezone(config('app.timezone'))->format('H:i'),
            'date' => $this->created_at?->timezone(config('app.timezone'))->translatedFormat('j M Y'),
        ];
    }
}
