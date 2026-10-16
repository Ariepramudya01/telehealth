<?php

namespace App\Enums;

enum KategoriRisiko: string
{
    case KRR = 'KRR';
    case KRT = 'KRT';
    case KRST = 'KRST';

    /**
     * Get the display label for this risk category.
     */
    public function label(): string
    {
        return match ($this) {
            self::KRR => 'Kehamilan Risiko Rendah',
            self::KRT => 'Kehamilan Risiko Tinggi',
            self::KRST => 'Kehamilan Risiko Sangat Tinggi',
        };
    }

    /**
     * Get the CSS color class for this risk category.
     */
    public function color(): string
    {
        return match ($this) {
            self::KRR => 'green',
            self::KRT => 'yellow',
            self::KRST => 'red',
        };
    }

    /**
     * Get the badge CSS classes for this risk category.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::KRR => 'bg-green-100 text-green-800 border-green-200',
            self::KRT => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            self::KRST => 'bg-red-100 text-red-800 border-red-200',
        };
    }

    /**
     * Determine the risk category based on total score.
     */
    public static function fromScore(int $totalScore): self
    {
        if ($totalScore >= 12) {
            return self::KRST;
        }

        if ($totalScore >= 6) {
            return self::KRT;
        }

        return self::KRR;
    }
}
