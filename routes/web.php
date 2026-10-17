<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\IbuHamilController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SkriningController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Pendaftaran Manual & Verifikasi OTP WhatsApp
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('otp.verify');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.process');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('otp.resend');
});

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Desa Management
    Route::resource('desa', DesaController::class);

    // User Management (Admin & Bidan)
    Route::middleware('role:admin,bidan')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Ibu Hamil Management (All roles)
    Route::resource('ibu-hamil', IbuHamilController::class);

    // Skrining Skor Poedji Rochjati (All roles can do screening)
    Route::get('/skrining/{skrining}/print', [SkriningController::class, 'print'])->name('skrining.print');
    Route::resource('skrining', SkriningController::class);

    // Kunjungan (ANC Visit tracking & konfirmasi kehadiran)
    Route::post('/kunjungan/{kunjungan}/konfirmasi', [KunjunganController::class, 'konfirmasiHadir'])->name('kunjungan.konfirmasi');
    Route::post('/ibu-hamil/{ibuHamil}/generate-jadwal-kunjungan', [KunjunganController::class, 'generateJadwal'])->name('kunjungan.generate');
    Route::get('/kunjungan', [KunjunganController::class, 'index'])->name('kunjungan.index');

    Route::middleware('role:admin,bidan')->group(function () {
        Route::get('/kunjungan/create', [KunjunganController::class, 'create'])->name('kunjungan.create');
        Route::post('/kunjungan', [KunjunganController::class, 'store'])->name('kunjungan.store');
        Route::get('/kunjungan/{kunjungan}/edit', [KunjunganController::class, 'edit'])->name('kunjungan.edit');
        Route::put('/kunjungan/{kunjungan}', [KunjunganController::class, 'update'])->name('kunjungan.update');
        Route::delete('/kunjungan/{kunjungan}', [KunjunganController::class, 'destroy'])->name('kunjungan.destroy');
    });

    // Laporan & Export Excel (Admin & Bidan only; Kader strictly blocked)
    Route::middleware('role:admin,bidan')->prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/download/agregat', [LaporanController::class, 'downloadAgregat'])->name('download.agregat');
        Route::get('/download/individu', [LaporanController::class, 'downloadIndividu'])->name('download.individu');
        Route::get('/download/kinerja/input', [LaporanController::class, 'downloadKinerjaInput'])->name('download.kinerja.input');
        Route::get('/download/kinerja/skrining', [LaporanController::class, 'downloadKinerjaSkrining'])->name('download.kinerja.skrining');
    });
});
