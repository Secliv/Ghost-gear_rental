<?php

namespace App\Enums;

enum UnitStatus: string
{
    case Tersedia = 'tersedia';
    case Ditahan = 'ditahan';
    case Dipinjam = 'dipinjam';

    public function label(): string
    {
        return match ($this) {
            self::Tersedia => 'Tersedia',
            self::Ditahan => 'Ditahan',
            self::Dipinjam => 'Dipinjam',
        };
    }
}
