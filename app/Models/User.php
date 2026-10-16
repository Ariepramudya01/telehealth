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

#[Fillable(['name', 'email', 'password', 'role', 'desa_id', 'phone', 'nik', 'alamat', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
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
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
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
}
