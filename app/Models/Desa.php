<?php

namespace App\Models;

use Database\Factories\DesaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'kecamatan', 'kabupaten', 'provinsi', 'puskesmas', 'kode_desa'])]
class Desa extends Model
{
    /** @use HasFactory<DesaFactory> */
    use HasFactory;

    /**
     * Alias for puskesmas / faskes pembina.
     */
    public function getInstansiAttribute(): ?string
    {
        return $this->puskesmas;
    }

    /**
     * Get all users assigned to this desa.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all ibu hamil in this desa.
     */
    public function ibuHamils(): HasMany
    {
        return $this->hasMany(IbuHamil::class);
    }

    /**
     * Get all kunjungan in this desa.
     */
    public function kunjungans(): HasMany
    {
        return $this->hasMany(Kunjungan::class);
    }
}
