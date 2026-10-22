<?php

namespace App\Models;

use Database\Factories\KunjunganFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ibu_hamil_id', 'user_id', 'desa_id', 'tanggal_kunjungan',
    'jenis_kunjungan', 'tahap_anc', 'metode_kunjungan', 'alasan_jemput_bola',
    'berat_badan', 'tinggi_badan',
    'tekanan_darah_sistol', 'tekanan_darah_diastol',
    'lila', 'tfu', 'djj', 'hb', 'kepatuhan_fe',
    'skrining_id', 'status_hadir', 'status_dilayani', 'catatan',
    'bentuk_pendampingan', 'pendampingan_suami', 'p4k_siap',
    'foto_pendampingan', 'jadwal_kunjungan_berikutnya',
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
            'jadwal_kunjungan_berikutnya' => 'date',
            'berat_badan' => 'float',
            'tinggi_badan' => 'float',
            'tekanan_darah_sistol' => 'integer',
            'tekanan_darah_diastol' => 'integer',
            'lila' => 'float',
            'tfu' => 'float',
            'djj' => 'integer',
            'hb' => 'float',
            'kepatuhan_fe' => 'boolean',
            'pendampingan_suami' => 'boolean',
            'p4k_siap' => 'boolean',
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

    /**
     * Label readable for metode kunjungan.
     */
    public function getMetodeKunjunganLabelAttribute(): string
    {
        return match ($this->metode_kunjungan) {
            'jemput_bola' => 'Jemput Bola (Kunjungan Rumah)',
            'posyandu' => 'Posyandu',
            'puskesmas' => 'Puskesmas / Faskes',
            default => ucfirst(str_replace('_', ' ', $this->metode_kunjungan ?? 'Puskesmas')),
        };
    }

    /**
     * Badge CSS class for metode kunjungan.
     */
    public function getMetodeKunjunganBadgeAttribute(): string
    {
        return match ($this->metode_kunjungan) {
            'jemput_bola' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
            'posyandu' => 'bg-amber-50 text-amber-700 border-amber-200/80',
            'puskesmas' => 'bg-teal-50 text-teal-700 border-teal-200/80',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    /**
     * URL of the uploaded foto pendampingan.
     */
    public function getFotoPendampinganUrlAttribute(): ?string
    {
        if (! $this->foto_pendampingan) {
            return null;
        }

        return asset('storage/'.$this->foto_pendampingan);
    }

    /**
     * Readable label for alasan jemput bola.
     */
    public function getAlasanJemputBolaLabelAttribute(): ?string
    {
        return match ($this->alasan_jemput_bola) {
            'mangkir_anc' => 'Mangkir / Terlambat Kunjungan Faskes',
            'risiko_tinggi' => 'Ibu Hamil Risiko Tinggi / Sangat Tinggi (KRT / KRST)',
            'tirah_baring' => 'Kondisi Lemah / Sakit / Butuh Bedrest',
            'kendala_geografis' => 'Kendala Transportasi / Geografis',
            'gizi_anemia' => 'Pemantauan Khusus Gizi KEK / Anemia',
            'lainnya' => 'Pemantauan Rutin Rumah Lainnya',
            default => $this->alasan_jemput_bola,
        };
    }
}
