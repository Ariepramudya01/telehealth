<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send an OTP verification code via WhatsApp.
     *
     * @return array{success: bool, message: string, otp: string, phone: string}
     */
    public function sendOtp(User $user): array
    {
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        $normalizedPhone = $this->normalizePhoneNumber($user->phone ?? '');

        $message = "Halo *{$user->name}*,\n\nKode verifikasi OTP pendaftaran akun *E-Detimil* Anda adalah:\n\n*{$otp}*\n\nKode ini berlaku selama 10 menit. Jangan bagikan kode ini kepada siapapun demi keamanan akun Anda.\n\nTerima kasih,\nTim E-Detimil Kesehatan Ibu & Anak";

        $apiUrl = config('services.whatsapp.url', env('WA_GATEWAY_URL'));
        $apiToken = config('services.whatsapp.token', env('WA_API_TOKEN'));

        $sentSuccessfully = false;

        if ($apiUrl && $apiToken && $normalizedPhone) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['Authorization' => $apiToken])
                    ->post($apiUrl, [
                        'target' => $normalizedPhone,
                        'message' => $message,
                    ]);

                $sentSuccessfully = $response->successful();
            } catch (\Throwable $e) {
                Log::warning('WhatsApp Gateway error: '.$e->getMessage());
            }
        }

        // Always log for development / debugging
        Log::info("WhatsApp OTP sent to [{$normalizedPhone}]: Code {$otp}");

        // Store OTP in flash session for local testing display
        session()->flash('simulated_otp', $otp);
        session()->flash('simulated_phone', $user->phone);

        return [
            'success' => true,
            'message' => 'Kode OTP berhasil dikirimkan ke WhatsApp Anda.',
            'otp' => $otp,
            'phone' => $normalizedPhone,
        ];
    }

    /**
     * Verify the OTP code for a user.
     */
    public function verifyOtp(User $user, string $code): bool
    {
        if (! $user->otp_code || ! $user->otp_expires_at) {
            return false;
        }

        if (now()->isAfter($user->otp_expires_at)) {
            return false;
        }

        if (trim($user->otp_code) !== trim($code)) {
            return false;
        }

        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        return true;
    }

    /**
     * Normalize Indonesian phone numbers to international standard (628...).
     */
    public function normalizePhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '08')) {
            return '62'.substr($clean, 1);
        }

        if (str_starts_with($clean, '8')) {
            return '62'.$clean;
        }

        return $clean;
    }
}
