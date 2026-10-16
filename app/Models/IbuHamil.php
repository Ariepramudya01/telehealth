<?php

namespace App\Models;

use Database\Factories\IbuHamilFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'desa_id', 'nama', 'nik', 'tanggal_lahir', 'alamat', 'no_hp',
    'nama_suami', 'golongan_darah', 'tinggi_badan', 'berat_badan',
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
}
