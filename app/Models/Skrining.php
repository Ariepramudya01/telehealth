<?php

namespace App\Models;

use App\Enums\KategoriRisiko;
use Database\Factories\SkriningFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'ibu_hamil_id', 'user_id', 'kunjungan_id', 'tanggal_skrining', 'usia_kehamilan_minggu',
    'trimester', 'total_skor', 'kategori_risiko', 'catatan',
])]
class Skrining extends Model
{
    /** @use HasFactory<SkriningFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_skrining' => 'date',
            'kategori_risiko' => KategoriRisiko::class,
        ];
    }

    /**
     * Get the ibu hamil for this skrining.
     */
    public function ibuHamil(): BelongsTo
    {
        return $this->belongsTo(IbuHamil::class);
    }

    /**
     * Get the user who performed this skrining.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated kunjungan ANC.
     */
    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    /**
     * Get the skrining details (answers).
     */
    public function details(): HasMany
    {
        return $this->hasMany(SkriningDetail::class);
    }
}
