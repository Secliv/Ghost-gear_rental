<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Menunggu = 'menunggu';
    case Aktif = 'aktif';
    case Ditolak = 'ditolak';
    case Dikembalikan = 'dikembalikan';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu',
            self::Aktif => 'Aktif',
            self::Ditolak => 'Ditolak',
            self::Dikembalikan => 'Dikembalikan',
        };
    }
}
