<?php

namespace App\Enums;

enum AgendaStatus: string
{
    case Scheduled = 'scheduled';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Ongoing => 'Berlangsung',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-amber-100 text-amber-800',
            self::Ongoing => 'bg-emerald-100 text-emerald-800',
            self::Completed => 'bg-slate-100 text-slate-700',
            self::Cancelled => 'bg-rose-100 text-rose-800',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            function (array $carry, self $status): array {
                $carry[$status->value] = $status->label();

                return $carry;
            },
            []
        );
    }
}
