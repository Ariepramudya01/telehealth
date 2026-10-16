<?php

namespace App\Models;

use App\Enums\KelompokFaktorRisiko;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['kelompok', 'nomor', 'pertanyaan', 'skor', 'keterangan'])]
class PertanyaanSpr extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kelompok' => KelompokFaktorRisiko::class,
        ];
    }
}
