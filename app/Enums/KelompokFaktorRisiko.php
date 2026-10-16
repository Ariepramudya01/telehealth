<?php

namespace App\Enums;

enum KelompokFaktorRisiko: string
{
    case APGO = 'I';
    case AGO = 'II';
    case AGDO = 'III';

    /**
     * Get the display label for this risk factor group.
     */
    public function label(): string
    {
        return match ($this) {
            self::APGO => 'Kelompok I - Ada Potensi Gawat Obstetri (APGO)',
            self::AGO => 'Kelompok II - Ada Gawat Obstetri (AGO)',
            self::AGDO => 'Kelompok III - Ada Gawat Darurat Obstetri (AGDO)',
        };
    }
}
