<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Desa;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsappService
    ) {}

    /**
     * Show the login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Show the account activation form.
     */
    public function showActivateAccount(string $token): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $user = User::where('activation_token', $token)->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tautan aktivasi akun tidak valid atau sudah digunakan. Silakan hubungi admin Puskesmas/Dinas Kesehatan jika butuh bantuan.']);
        }

        if ($user->activation_expires_at && $user->activation_expires_at->isPast()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tautan aktivasi akun Anda telah kedaluwarsa. Silakan hubungi admin untuk mengirimkan tautan baru.']);
        }

        return view('auth.activate', compact('user', 'token'));
    }

    /**
     * Process account activation and password setup.
     */
    public function processActivateAccount(Request $request, string $token): RedirectResponse
    {
        $user = User::where('activation_token', $token)->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tautan aktivasi akun tidak valid atau sudah digunakan.']);
        }

        if ($user->activation_expires_at && $user->activation_expires_at->isPast()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tautan aktivasi akun Anda telah kedaluwarsa. Silakan hubungi admin untuk mengirimkan tautan baru.']);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'is_active' => true,
            'activation_token' => null,
            'activation_expires_at' => null,
            'phone_verified_at' => $user->phone_verified_at ?? now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Selamat, '.$user->name.'! Akun Anda berhasil diaktivasi dan sekarang aktif.');
    }

    /**
     * Process authentication.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda belum aktif atau belum diverifikasi. Silakan hubungi admin.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, '.$user->name.'!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Show manual registration form.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $desas = Desa::orderBy('nama')->get();
        $roles = [Role::Bidan, Role::Kader];

        return view('auth.register', compact('desas', 'roles'));
    }

    /**
     * Handle manual registration and initiate WhatsApp OTP.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'unique:users,phone'],
            'role' => ['required', ValidationRule::in([Role::Bidan->value, Role::Kader->value])],
            'desa_id' => ['required', 'exists:desas,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:users,nik'],
            'instansi' => ['nullable', 'string', 'max:150'],
            'alamat' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $this->whatsappService->normalizePhoneNumber($validated['phone']),
            'role' => Role::from($validated['role']),
            'desa_id' => $validated['desa_id'],
            'password' => Hash::make($validated['password']),
            'nik' => $validated['nik'] ?? null,
            'instansi' => $validated['instansi'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_active' => false, // Akun baru menunggu verifikasi dari admin
        ]);

        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil! Akun Anda telah terdaftar dan sedang menunggu verifikasi serta persetujuan dari Administrator Puskesmas/Dinas Kesehatan sebelum dapat digunakan untuk masuk.');
    }

    /**
     * Show WhatsApp OTP verification form.
     */
    public function showVerifyOtp(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get('pending_otp_user_id');

        if (! $userId) {
            return redirect()->route('login')
                ->with('error', 'Sesi verifikasi telah berakhir. Silakan login atau daftar ulang.');
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp', compact('user'));
    }

    /**
     * Verify the entered WhatsApp OTP code.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('pending_otp_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $isValid = $this->whatsappService->verifyOtp($user, $request->otp);

        if (! $isValid) {
            return back()->withErrors([
                'otp' => 'Kode OTP yang Anda masukkan salah atau sudah kedaluwarsa.',
            ]);
        }

        // Clear session and log the user in
        $request->session()->forget('pending_otp_user_id');
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Verifikasi WhatsApp berhasil! Akun Anda telah aktif.');
    }

    /**
     * Resend the WhatsApp OTP code.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('pending_otp_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $this->whatsappService->sendOtp($user);

        return back()->with('success', 'Kode OTP baru telah berhasil dikirim ulang ke nomor WhatsApp Anda.');
    }

    /**
     * Process logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
