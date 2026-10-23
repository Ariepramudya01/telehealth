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
use Illuminate\Support\Str;

#[Fillable([
    'name', 'email', 'password', 'role', 'desa_id',
    'provinsi', 'kabupaten', 'kecamatan',
    'instansi', 'jabatan', 'nip', 'str',
    'phone', 'nik', 'alamat', 'latitude', 'longitude',
    'otp_code', 'otp_expires_at', 'phone_verified_at',
    'activation_token', 'activation_expires_at',
    'is_active',
])]
#[Hidden(['password', 'remember_token', 'otp_code', 'activation_token'])]
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
            'activation_expires_at' => 'datetime',
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
     * Determine if the user is Admin Pusat (super-admin) or Dinas Kesehatan.
     * These users have global oversight across all UPT Puskesmas.
     */
    public function isDinasOrPusat(): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        $jabatan = strtolower(trim((string) $this->jabatan));
        // Kapus (Kepala Puskesmas) and Kepala TU (Tata Usaha Puskesmas) belong to local UPT Puskesmas
        if (str_contains($jabatan, 'puskesmas') || str_contains($jabatan, 'kapus') || str_contains($jabatan, 'kepala tu') || str_contains($jabatan, 'tata usaha')) {
            return false;
        }

        $instansi = strtolower(trim((string) $this->instansi));

        // If instansi explicitly contains Puskesmas or UPT or Pustu, then they belong to a Puskesmas
        if (str_contains($instansi, 'puskesmas') || str_contains($instansi, 'upt') || str_contains($instansi, 'pustu') || str_contains($instansi, 'pkm')) {
            return false;
        }

        // If instansi explicitly mentions Dinas Kesehatan / Dinkes / Pusat / Kemenkes
        if (str_contains($instansi, 'dinas') || str_contains($instansi, 'dinkes') || str_contains($instansi, 'pusat') || str_contains($instansi, 'kemenkes')) {
            return true;
        }

        // If instansi is empty and no specific village/kecamatan is assigned, they are global admin
        return empty($this->desa_id) && empty($this->kecamatan);
    }

    /**
     * Determine if the user belongs to a specific UPT Puskesmas (Admin UPT Puskesmas, Bidan, Kader).
     */
    public function isUptPuskesmas(): bool
    {
        return ! $this->isDinasOrPusat();
    }

    /**
     * Get the Puskesmas name or jurisdiction for this user.
     */
    public function getPuskesmasKerjaAttribute(): ?string
    {
        if ($this->isDinasOrPusat()) {
            return null;
        }

        if (! empty($this->instansi) && (str_contains(strtolower($this->instansi), 'puskesmas') || str_contains(strtolower($this->instansi), 'upt'))) {
            return $this->instansi;
        }

        if (! empty($this->desa?->puskesmas)) {
            return $this->desa->puskesmas;
        }

        return $this->instansi;
    }

    /**
     * Get normalized / clean keyword for the Puskesmas (e.g. 'air molek', 'sungai parit').
     */
    public function getCleanPuskesmasKeywordAttribute(): ?string
    {
        $raw = $this->puskesmas_kerja;
        if (! $raw) {
            return null;
        }

        // Strip prefixes like UPT, Puskesmas, etc.
        $clean = preg_replace('/^(upt\s+)?(puskesmas\s+)?/i', '', trim($raw));

        return strtolower(trim($clean));
    }

    /**
     * Check if a given Desa belongs to this user's jurisdiction.
     */
    public function canAccessDesa(Desa $desa): bool
    {
        // Dinkes Kabupaten: oversight across entire regency
        if ($this->isDinkes()) {
            return true;
        }

        $myKeyword = $this->clean_puskesmas_keyword;
        if (! $myKeyword) {
            // Fallback to kecamatan
            if ($this->kecamatan_kerja && $desa->kecamatan) {
                $cleanMyKec = strtolower(trim(preg_replace('/^kecamatan\s+/i', '', $this->kecamatan_kerja)));
                $cleanDesaKec = strtolower(trim(preg_replace('/^kecamatan\s+/i', '', $desa->kecamatan)));

                return str_contains($cleanDesaKec, $cleanMyKec) || str_contains($cleanMyKec, $cleanDesaKec);
            }

            return $this->desa_id === $desa->id;
        }

        // 1. Direct desa puskesmas match
        if (! empty($desa->puskesmas)) {
            $desaPuskesmas = strtolower(trim(preg_replace('/^(upt\s+)?(puskesmas\s+)?/i', '', $desa->puskesmas)));
            if (str_contains($desaPuskesmas, $myKeyword) || str_contains($myKeyword, $desaPuskesmas)) {
                return true;
            }
        }

        // 2. Check users assigned in that desa
        if ($desa->relationLoaded('users')) {
            foreach ($desa->users as $u) {
                if ($u->instansi) {
                    $uInstansi = strtolower(trim(preg_replace('/^(upt\s+)?(puskesmas\s+)?/i', '', $u->instansi)));
                    if (str_contains($uInstansi, $myKeyword) || str_contains($myKeyword, $uInstansi)) {
                        return true;
                    }
                }
            }
        }

        // 3. Fallback to kecamatan match if user has kecamatan
        if ($this->kecamatan_kerja && $desa->kecamatan) {
            $cleanMyKec = strtolower(trim(preg_replace('/^kecamatan\s+/i', '', $this->kecamatan_kerja)));
            $cleanDesaKec = strtolower(trim(preg_replace('/^kecamatan\s+/i', '', $desa->kecamatan)));
            if (str_contains($cleanDesaKec, $cleanMyKec) || str_contains($cleanMyKec, $cleanDesaKec)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a given staff / user belongs to this user's UPT Puskesmas jurisdiction.
     */
    public function canAccessStaff(User $staff): bool
    {
        // Dinkes Kabupaten has access to all staff under the regency
        if ($this->isDinkes()) {
            return true;
        }

        $myKeyword = $this->clean_puskesmas_keyword;
        if (! $myKeyword) {
            return false;
        }

        // 1. Staff instansi matches
        if (! empty($staff->instansi)) {
            $staffInstansi = strtolower(trim(preg_replace('/^(upt\s+)?(puskesmas\s+)?/i', '', $staff->instansi)));
            if (str_contains($staffInstansi, $myKeyword) || str_contains($myKeyword, $staffInstansi)) {
                return true;
            }
        }

        // 2. Staff desa matches
        if ($staff->desa) {
            return $this->canAccessDesa($staff->desa);
        }

        // 3. Staff kecamatan matches
        if ($this->kecamatan_kerja && $staff->kecamatan) {
            $cleanMyKec = strtolower(trim(preg_replace('/^kecamatan\s+/i', '', $this->kecamatan_kerja)));
            $cleanStaffKec = strtolower(trim(preg_replace('/^kecamatan\s+/i', '', $staff->kecamatan)));

            return str_contains($cleanStaffKec, $cleanMyKec) || str_contains($cleanMyKec, $cleanStaffKec);
        }

        return false;
    }

    /**
     * Check if this admin is assigned to a specific Puskesmas.
     */
    public function isAdminPuskesmas(): bool
    {
        return $this->isAdmin() && ! $this->isDinasOrPusat();
    }

    /**
     * Determine if the user is Dinkes Provinsi (Deprecated: role abolished, returns false).
     */
    public function isDinkesProvinsi(): bool
    {
        return false;
    }

    /**
     * Determine if the user is Dinas Kesehatan Kabupaten (Level 80 - Super Admin Wilayah).
     */
    public function isDinkesKabupaten(): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        return ! $this->isAdminPuskesmas();
    }

    /**
     * Determine if the user is Dinas Kesehatan Kabupaten.
     * Has regional aggregate oversight across all UPT Puskesmas in the regency.
     */
    public function isDinkes(): bool
    {
        return $this->isDinkesKabupaten();
    }

    /**
     * Get numeric rank hierarchy level:
     * 80  - Dinas Kesehatan Kabupaten (Admin Utama)
     * 60  - Admin UPT Puskesmas / Klinik
     * 40  - Bidan Desa
     * 20  - Kader Posyandu
     */
    public function adminLevel(): int
    {
        if ($this->isAdmin()) {
            if ($this->isDinkesKabupaten()) {
                return 80;
            }

            return 60;
        }

        if ($this->isBidan()) {
            return 40;
        }

        if ($this->isKader()) {
            return 20;
        }

        return 0;
    }

    /**
     * Get readable title representing user hierarchy in the healthcare system.
     */
    public function getHierarchyTitleAttribute(): string
    {
        if ($this->isAdmin()) {
            if ($this->isDinkesKabupaten()) {
                return 'Dinkes Kabupaten';
            }

            return 'Admin UPT Puskesmas';
        }

        if ($this->isBidan()) {
            return 'Bidan Desa';
        }

        if ($this->isKader()) {
            return 'Kader Posyandu';
        }

        return $this->role->label();
    }

    /**
     * Determine if current user is authorized to delete the target user.
     * Rule:
     * - Cannot delete own account.
     * - Dinkes Provinsi (Admin Utama) can delete lower tier accounts.
     * - Dinkes Kabupaten CANNOT delete Dinkes Provinsi (Admin Utama) nor peer Dinkes Kabupaten.
     * - Admin Puskesmas can only delete Bidan & Kader within their Puskesmas.
     * - Bidan can only delete Kader within her jurisdiction.
     * - Kader cannot delete any user.
     */
    public function canDeleteUser(User $target): bool
    {
        if ($this->id === $target->id) {
            return false;
        }

        if ($this->isDinkesProvinsi()) {
            return true;
        }

        if ($this->isDinkesKabupaten()) {
            if ($target->adminLevel() >= 80) {
                return false;
            }

            if ($this->kabupaten && $target->kabupaten && strtolower(trim($this->kabupaten)) !== strtolower(trim($target->kabupaten))) {
                return false;
            }

            return true;
        }

        if ($this->isAdminPuskesmas()) {
            if ($target->adminLevel() >= 60) {
                return false;
            }

            return $this->canAccessStaff($target);
        }

        if ($this->isBidan()) {
            if (! $target->isKader()) {
                return false;
            }

            $desaIds = Desa::where('kecamatan', $this->desa?->kecamatan)
                ->orWhere('puskesmas', $this->desa?->puskesmas)
                ->orWhere('id', $this->desa_id)
                ->pluck('id')
                ->all();

            if (empty($desaIds)) {
                $desaIds = [$this->desa_id];
            }

            return in_array($target->desa_id, $desaIds);
        }

        return false;
    }

    /**
     * Determine if current user is authorized to edit the target user.
     */
    public function canEditUser(User $target): bool
    {
        if ($this->id === $target->id) {
            return true;
        }

        if ($this->isDinkesProvinsi()) {
            return true;
        }

        if ($this->isDinkesKabupaten()) {
            if ($target->adminLevel() >= 80) {
                return false;
            }

            if ($this->kabupaten && $target->kabupaten && strtolower(trim($this->kabupaten)) !== strtolower(trim($target->kabupaten))) {
                return false;
            }

            return true;
        }

        if ($this->isAdminPuskesmas()) {
            if ($target->adminLevel() >= 60) {
                return false;
            }

            return $this->canAccessStaff($target);
        }

        if ($this->isBidan()) {
            if (! $target->isKader()) {
                return false;
            }

            $desaIds = Desa::where('kecamatan', $this->desa?->kecamatan)
                ->orWhere('puskesmas', $this->desa?->puskesmas)
                ->orWhere('id', $this->desa_id)
                ->pluck('id')
                ->all();

            if (empty($desaIds)) {
                $desaIds = [$this->desa_id];
            }

            return in_array($target->desa_id, $desaIds);
        }

        return false;
    }

    /**
     * Determine if current user can toggle active/verification status of the target user.
     */
    public function canToggleStatus(User $target): bool
    {
        if ($this->id === $target->id) {
            return false;
        }

        return $this->canEditUser($target);
    }

    /**
     * Generate an activation token for this user.
     */
    public function generateActivationToken(int $days = 7): string
    {
        $this->activation_token = Str::random(60);
        $this->activation_expires_at = now()->addDays($days);
        $this->save();

        return $this->activation_token;
    }

    /**
     * Check if user has a valid and unexpired activation token.
     */
    public function hasValidActivationToken(): bool
    {
        return ! empty($this->activation_token) && ($this->activation_expires_at === null || $this->activation_expires_at->isFuture());
    }

    /**
     * Get activation URL for this user.
     */
    public function getActivationUrlAttribute(): string
    {
        if (empty($this->activation_token)) {
            if ($this->is_active) {
                return route('login');
            }

            $this->activation_token = Str::random(60);
            $this->activation_expires_at = now()->addDays(7);
            $this->saveQuietly();
        }

        return route('activation.show', ['token' => $this->activation_token]);
    }

    /**
     * Get WhatsApp message content for account activation or login info.
     */
    public function getWhatsappActivationMessageAttribute(): string
    {
        $instansi = $this->instansi ?: ($this->desa ? 'Desa '.$this->desa->nama : 'Puskesmas');

        if ($this->is_active) {
            $loginUrl = route('login');

            return "Halo *{$this->name}*,\n\nAkun Anda pada sistem *E-Detimil* ({$instansi}) telah aktif sebagai *{$this->hierarchy_title}*.\n\nSilakan masuk ke sistem melalui tautan:\n{$loginUrl}\n\nTerima kasih,\nTim E-Detimil";
        }

        return "Halo *{$this->name}*,\n\nAnda telah didaftarkan secara resmi sebagai *{$this->hierarchy_title}* pada sistem *E-Detimil* ({$instansi}).\n\nSilakan klik tautan berikut untuk memverifikasi akun Anda dan membuat kata sandi baru:\n{$this->activation_url}\n\nTautan ini berlaku selama 7 hari. Mohon segera selesaikan aktivasi akun Anda.\n\nTerima kasih,\nTim E-Detimil";
    }

    /**
     * Get direct WhatsApp URL (wa.me) with prefilled message.
     */
    public function getWhatsappActivationUrlAttribute(): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $this->phone);
        if (str_starts_with($cleanPhone, '08')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62'.$cleanPhone;
        }

        $text = rawurlencode($this->whatsapp_activation_message);

        return "https://api.whatsapp.com/send?phone={$cleanPhone}&text={$text}";
    }
}
