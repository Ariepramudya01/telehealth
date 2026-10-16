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
});

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

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

    // Kunjungan (ANC Visit tracking - Admin & Bidan only)
    Route::middleware('role:admin,bidan')->group(function () {
        Route::resource('kunjungan', KunjunganController::class)->except(['show']);
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
