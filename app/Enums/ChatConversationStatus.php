<?php

namespace App\Enums;

enum ChatConversationStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Aktif',
            self::Closed => 'Selesai',
        };
    }
}
