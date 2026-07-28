<?php

namespace App\Enums;

enum ChatSender: string
{
    case Guest = 'guest';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Guest => 'Tamu',
            self::Admin => 'Admin',
        };
    }
}
