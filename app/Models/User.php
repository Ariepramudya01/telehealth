<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'role', 'desa_id',
    'provinsi', 'kabupaten', 'kecamatan',
    'instansi', 'jabatan', 'nip', 'str',
    'phone', 'nik', 'alamat', 'latitude', 'longitude',
    'otp_code', 'otp_expires_at', 'phone_verified_at',
    'is_active',
])]
#[Hidden(['password', 'remember_token', 'otp_code'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Get the desa this user belongs to.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * Get the ibu hamil records created by this user.
     */
    public function ibuHamils(): HasMany
    {
        return $this->hasMany(IbuHamil::class);
    }

    /**
     * Get the skrining records performed by this user.
     */
    public function skrinings(): HasMany
    {
        return $this->hasMany(Skrining::class);
    }

    /**
     * Get the kunjungan records created by this user.
     */
    public function kunjungans(): HasMany
    {
        return $this->hasMany(Kunjungan::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    /**
     * Check if the user is a bidan desa.
     */
    public function isBidan(): bool
    {
        return $this->role === Role::Bidan;
    }

    /**
     * Check if the user is a kader.
     */
    public function isKader(): bool
    {
        return $this->role === Role::Kader;
    }

    /**
     * Check if user's phone is verified via WhatsApp OTP.
     */
    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    /**
     * Get the registered kecamatan jurisdiction for this user.
     */
    public function getKecamatanKerjaAttribute(): ?string
    {
        return $this->kecamatan ?? $this->desa?->kecamatan;
    }

    /**
     * Check if this admin is assigned to a specific Puskesmas kecamatan.
     */
    public function isAdminPuskesmas(): bool
    {
        return $this->isAdmin() && ! empty($this->kecamatan_kerja);
    }
}
