<?php

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\WilayahController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - E-Detimil Lokasi & Pemeriksaan Fisik
|--------------------------------------------------------------------------
|
| Endpoint REST API untuk mendaftarkan dan memetakan lokasi GPS (Latitude & Longitude)
| Ibu Hamil, Bidan Desa, dan Kader Posyandu serta data pemeriksaan fisik.
|
*/

Route::prefix('locations')->group(function () {
    // Pendaftaran & Update Lokasi Ibu Hamil
    Route::post('/ibu-hamil', [LocationController::class, 'registerIbuHamilLocation']);
    Route::get('/ibu-hamil', [LocationController::class, 'getIbuHamilLocations']);

    // Pendaftaran & Update Lokasi Petugas (Bidan Desa & Kader)
    Route::post('/petugas', [LocationController::class, 'registerPetugasLocation']);
    Route::get('/petugas', [LocationController::class, 'getPetugasLocations']);

    // Peta Gabungan (GeoJSON format untuk Leaflet / Mapbox / Google Maps)
    Route::get('/geojson', [LocationController::class, 'getAllLocationsGeoJson']);

    // Google Geocoding & Reverse Geocoding (Desa/Kelurahan, Kecamatan, Kabupaten)
    Route::match(['get', 'post'], '/reverse-geocode', [LocationController::class, 'reverseGeocode']);
    Route::get('/geocode', [LocationController::class, 'geocode']);
});

// Cascading Wilayah Indonesia (Provinsi -> Kabupaten -> Kecamatan -> Desa)
Route::prefix('wilayah')->group(function () {
    Route::get('/provinces', [WilayahController::class, 'provinces']);
    Route::get('/regencies/{provinceId}', [WilayahController::class, 'regencies']);
    Route::get('/districts/{regencyId}', [WilayahController::class, 'districts']);
    Route::get('/villages/{districtId}', [WilayahController::class, 'villages']);
    Route::get('/desas', [WilayahController::class, 'localDesas']);
    Route::post('/resolve-desa', [WilayahController::class, 'resolveDesa']);
});

// Pendaftaran Lengkap Ibu Hamil Baru dengan Lokasi & Pemeriksaan Fisik via API
Route::post('/ibu-hamil/register-with-location', [LocationController::class, 'createIbuHamilWithLocation']);
