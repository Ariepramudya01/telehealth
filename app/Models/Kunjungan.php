<?php

namespace App\Models;

use Database\Factories\KunjunganFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ibu_hamil_id', 'user_id', 'desa_id', 'tanggal_kunjungan',
    'jenis_kunjungan', 'status_hadir', 'status_dilayani', 'catatan',
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
}
