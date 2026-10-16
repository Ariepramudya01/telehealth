<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Bidan = 'bidan';
    case Kader = 'kader';

    /**
     * Get the display label for this role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Bidan => 'Bidan Desa',
            self::Kader => 'Kader',
        };
    }
}
