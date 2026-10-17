<?php

namespace App\Models;

use Database\Factories\KunjunganFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ibu_hamil_id', 'user_id', 'desa_id', 'tanggal_kunjungan',
    'jenis_kunjungan', 'berat_badan', 'tinggi_badan',
    'tekanan_darah_sistol', 'tekanan_darah_diastol', 'skrining_id',
    'status_hadir', 'status_dilayani', 'catatan',
])]
class Kunjungan extends Model
{
    /** @use HasFactory<KunjunganFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_kunjungan' => 'date',
            'berat_badan' => 'float',
            'tinggi_badan' => 'float',
            'tekanan_darah_sistol' => 'integer',
            'tekanan_darah_diastol' => 'integer',
            'status_hadir' => 'boolean',
            'status_dilayani' => 'boolean',
        ];
    }

    /**
     * Get the ibu hamil for this kunjungan.
     */
    public function ibuHamil(): BelongsTo
    {
        return $this->belongsTo(IbuHamil::class);
    }

    /**
     * Get the user who created this kunjungan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the desa for this kunjungan.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * Get the associated skrining SPR if any.
     */
    public function skrining(): BelongsTo
    {
        return $this->belongsTo(Skrining::class);
    }

    /**
     * Formatted tekanan darah.
     */
    public function getTekananDarahAttribute(): ?string
    {
        if ($this->tekanan_darah_sistol && $this->tekanan_darah_diastol) {
            return "{$this->tekanan_darah_sistol}/{$this->tekanan_darah_diastol} mmHg";
        }

        return null;
    }
}
