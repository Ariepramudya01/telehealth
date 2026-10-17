<?php

namespace App\Models;

use Database\Factories\IbuHamilFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'desa_id', 'nama', 'nik', 'tanggal_lahir', 'alamat',
    'latitude', 'longitude', 'no_hp', 'nama_suami', 'golongan_darah',
    'tinggi_badan', 'berat_badan', 'imt',
    'tekanan_darah_sistol', 'tekanan_darah_diastol', 'lila', 'tfu', 'djj', 'hemoglobin',
    'status_imunisasi_tt', 'refleks_patela', 'edema', 'status_gizi', 'kondisi_umum',
    'hpht', 'taksiran_persalinan', 'gravida', 'para', 'abortus',
    'jumlah_anak_hidup', 'is_active',
])]
class IbuHamil extends Model
{
    /** @use HasFactory<IbuHamilFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'ibu_hamils';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'hpht' => 'date',
            'taksiran_persalinan' => 'date',
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'tinggi_badan' => 'float',
            'berat_badan' => 'float',
            'imt' => 'float',
            'tekanan_darah_sistol' => 'integer',
            'tekanan_darah_diastol' => 'integer',
            'lila' => 'float',
            'tfu' => 'float',
            'djj' => 'integer',
            'hemoglobin' => 'float',
        ];
    }

    /**
     * Get the user who inputted this data.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the desa for this ibu hamil.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * Get all skrining records for this ibu hamil.
     */
    public function skrinings(): HasMany
    {
        return $this->hasMany(Skrining::class);
    }

    /**
     * Get all kunjungan records for this ibu hamil.
     */
    public function kunjungans(): HasMany
    {
        return $this->hasMany(Kunjungan::class);
    }

    /**
     * Generate standard ANC visit schedule (K1 to K6) for any missing visit types.
     */
    public function generateStandardKunjungans(?int $userId = null): void
    {
        $userId = $userId ?? $this->user_id;

        $scheduleOffsets = [
            'K1' => 8,  // Trimester 1 (~minggu 8-12)
            'K2' => 18, // Trimester 2 (~minggu 18-20)
            'K3' => 26, // Trimester 2 (~minggu 24-26)
            'K4' => 30, // Trimester 3 (~minggu 28-30)
            'K5' => 34, // Trimester 3 (~minggu 32-34)
            'K6' => 38, // Trimester 3 (~minggu 36-38)
        ];

        $existingJenis = $this->kunjungans()->pluck('jenis_kunjungan')->toArray();

        foreach ($scheduleOffsets as $jenis => $weeks) {
            if (in_array($jenis, $existingJenis)) {
                continue;
            }

            $tgl = $this->hpht
                ? $this->hpht->copy()->addWeeks($weeks)->format('Y-m-d')
                : now()->addWeeks($weeks - 8)->format('Y-m-d');

            Kunjungan::create([
                'ibu_hamil_id' => $this->id,
                'user_id' => $userId,
                'desa_id' => $this->desa_id,
                'tanggal_kunjungan' => $tgl,
                'jenis_kunjungan' => $jenis,
                'status_hadir' => false,
                'status_dilayani' => false,
                'catatan' => 'Jadwal ANC Standar '.$jenis,
            ]);
        }
    }

    /**
     * Get the latest skrining for this ibu hamil.
     */
    public function latestSkrining(): ?Skrining
    {
        return $this->skrinings()->latest('tanggal_skrining')->first();
    }

    /**
     * Calculate the age of the ibu hamil.
     */
    public function getUsiaAttribute(): ?int
    {
        if (! $this->tanggal_lahir) {
            return null;
        }

        return $this->tanggal_lahir->age;
    }

    /**
     * Get the usia kehamilan in weeks.
     */
    public function getUsiaKehamilanMingguAttribute(): ?int
    {
        if (! $this->hpht) {
            return null;
        }

        return (int) $this->hpht->diffInWeeks(now());
    }

    /**
     * Get formatted blood pressure string.
     */
    public function getTekananDarahAttribute(): ?string
    {
        if ($this->tekanan_darah_sistol && $this->tekanan_darah_diastol) {
            return "{$this->tekanan_darah_sistol}/{$this->tekanan_darah_diastol} mmHg";
        }

        return null;
    }

    /**
     * Determine if mother is suffering from KEK (Kurang Energi Kronis) based on LiLA < 23.5 cm.
     */
    public function getIsKekAttribute(): bool
    {
        return $this->lila !== null && $this->lila < 23.5;
    }

    /**
     * Determine if mother has anemia (Hb < 11.0 g/dL).
     */
    public function getIsAnemiaAttribute(): bool
    {
        return $this->hemoglobin !== null && $this->hemoglobin < 11.0;
    }

    /**
     * Calculate and return IMT value.
     */
    public function getCalculatedImtAttribute(): ?float
    {
        if ($this->imt !== null && $this->imt > 0) {
            return (float) $this->imt;
        }

        if ($this->berat_badan && $this->tinggi_badan && $this->tinggi_badan > 0) {
            $tbMeter = $this->tinggi_badan / 100;

            return round($this->berat_badan / ($tbMeter * $tbMeter), 1);
        }

        return null;
    }

    /**
     * Get IMT and nutritional category.
     */
    public function getKategoriImtAttribute(): ?string
    {
        $imt = $this->calculated_imt;
        if ($imt === null) {
            return null;
        }

        if ($imt < 18.5) {
            return $this->is_kek ? 'Gizi Kurang (Risiko KEK)' : 'Gizi Kurang (Underweight)';
        }

        if ($imt <= 24.9) {
            return 'Gizi Baik (Normal)';
        }

        if ($imt <= 29.9) {
            return 'Gizi Lebih (Overweight)';
        }

        return 'Obesitas';
    }

    /**
     * Determine standard status_gizi enum value ('kurang', 'baik', 'lebih', 'obesitas') from IMT.
     */
    public function determineStatusGizi(?float $imt = null): ?string
    {
        $val = $imt ?? $this->calculated_imt;
        if ($val === null) {
            return null;
        }

        if ($val < 18.5) {
            return 'kurang';
        }

        if ($val <= 24.9) {
            return 'baik';
        }

        if ($val <= 29.9) {
            return 'lebih';
        }

        return 'obesitas';
    }
}
