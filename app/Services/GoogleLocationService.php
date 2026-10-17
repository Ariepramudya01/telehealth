<?php

namespace App\Services;

use App\Models\Desa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleLocationService
{
    /**
     * Google Maps Geocoding API Key.
     */
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.maps_api_key');
    }

    /**
     * Reverse geocode coordinates to retrieve Desa/Kelurahan, Kecamatan, Kabupaten, and full address.
     *
     * @return array<string, mixed>
     */
    public function reverseGeocode(float $latitude, float $longitude): array
    {
        // Try Google Geocoding API first if API key is provided
        if (! empty($this->apiKey)) {
            $googleResult = $this->queryGoogleReverse($latitude, $longitude);
            if ($googleResult !== null) {
                return $this->formatResult($googleResult, 'google', $latitude, $longitude);
            }
        }

        // Fallback to standard Nominatim reverse geocoder for development/offline mode
        $fallbackResult = $this->queryFallbackReverse($latitude, $longitude);
        if ($fallbackResult !== null) {
            return $this->formatResult($fallbackResult, 'fallback', $latitude, $longitude);
        }

        // Default fallback if offline
        return [
            'success' => false,
            'source' => 'none',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'desa' => null,
            'kecamatan' => null,
            'kabupaten' => null,
            'provinsi' => null,
            'alamat_lengkap' => null,
            'matched_desa_id' => null,
            'matched_desa_nama' => null,
            'message' => 'Gagal mendeteksi lokasi dari Google Maps atau geocoder.',
        ];
    }

    /**
     * Geocode an address query string to coordinates and administrative breakdown.
     *
     * @return array<string, mixed>
     */
    public function geocode(string $query): array
    {
        if (! empty($this->apiKey)) {
            $googleResult = $this->queryGoogleForward($query);
            if ($googleResult !== null) {
                return $this->formatResult($googleResult['data'], 'google', $googleResult['lat'], $googleResult['lng']);
            }
        }

        $fallbackResult = $this->queryFallbackForward($query);
        if ($fallbackResult !== null) {
            return $this->formatResult($fallbackResult['data'], 'fallback', $fallbackResult['lat'], $fallbackResult['lng']);
        }

        return [
            'success' => false,
            'source' => 'none',
            'latitude' => null,
            'longitude' => null,
            'desa' => null,
            'kecamatan' => null,
            'kabupaten' => null,
            'provinsi' => null,
            'alamat_lengkap' => null,
            'matched_desa_id' => null,
            'matched_desa_nama' => null,
            'message' => 'Alamat tidak ditemukan melalui Google Maps.',
        ];
    }

    /**
     * Query Google Maps Geocoding API reverse geocode.
     *
     * @return array<string, string|null>|null
     */
    protected function queryGoogleReverse(float $lat, float $lng): ?array
    {
        try {
            $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "{$lat},{$lng}",
                'language' => 'id',
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (($json['status'] ?? '') === 'OK' && ! empty($json['results'])) {
                    return $this->parseGoogleComponents($json['results'][0]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Google Geocoding API reverse error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Query Google Maps Geocoding API forward geocode.
     *
     * @return array{lat: float, lng: float, data: array<string, string|null>}|null
     */
    protected function queryGoogleForward(string $address): ?array
    {
        try {
            $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'language' => 'id',
                'components' => 'country:ID',
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (($json['status'] ?? '') === 'OK' && ! empty($json['results'])) {
                    $result = $json['results'][0];
                    $lat = (float) $result['geometry']['location']['lat'];
                    $lng = (float) $result['geometry']['location']['lng'];

                    return [
                        'lat' => $lat,
                        'lng' => $lng,
                        'data' => $this->parseGoogleComponents($result),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Google Geocoding API forward error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Parse address components from Google Maps response.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, string|null>
     */
    protected function parseGoogleComponents(array $result): array
    {
        $desa = null;
        $kecamatan = null;
        $kabupaten = null;
        $provinsi = null;
        $formatted = $result['formatted_address'] ?? null;

        $components = $result['address_components'] ?? [];

        foreach ($components as $component) {
            $types = $component['types'] ?? [];
            $longName = $component['long_name'] ?? '';

            // Desa / Kelurahan
            if (in_array('administrative_area_level_4', $types, true) || in_array('sublocality_level_1', $types, true)) {
                $desa = $desa ?? $this->cleanAdminName($longName);
            }

            // Kecamatan
            if (in_array('administrative_area_level_3', $types, true)) {
                $kecamatan = $this->cleanAdminName($longName);
            }

            // Kabupaten / Kota
            if (in_array('administrative_area_level_2', $types, true)) {
                $kabupaten = $longName;
            }

            // Provinsi
            if (in_array('administrative_area_level_1', $types, true)) {
                $provinsi = $longName;
            }
        }

        // Secondary fallback inside components
        if (! $desa) {
            foreach ($components as $c) {
                if (in_array('sublocality', $c['types'] ?? [], true) || in_array('neighborhood', $c['types'] ?? [], true)) {
                    $desa = $this->cleanAdminName($c['long_name']);
                    break;
                }
            }
        }

        return [
            'desa' => $desa,
            'kecamatan' => $kecamatan,
            'kabupaten' => $kabupaten,
            'provinsi' => $provinsi,
            'alamat_lengkap' => $formatted,
        ];
    }

    /**
     * Query standard geocoder fallback (OpenStreetMap Nominatim).
     *
     * @return array<string, string|null>|null
     */
    protected function queryFallbackReverse(float $lat, float $lng): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'E-Detimil-App/1.0 (telehealth-puskesmas-bogor)',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'addressdetails' => 1,
                'accept-language' => 'id',
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $address = $json['address'] ?? [];

                $desa = $address['neighbourhood']
                    ?? $address['village']
                    ?? $address['suburb']
                    ?? $address['quarter']
                    ?? null;

                $kecamatan = $address['subdistrict']
                    ?? $address['municipality']
                    ?? $address['city_district']
                    ?? $address['suburb']
                    ?? null;

                $kabupaten = $address['city']
                    ?? $address['county']
                    ?? $address['regency']
                    ?? null;

                $provinsi = $address['state'] ?? null;
                $formatted = $json['display_name'] ?? null;

                return [
                    'desa' => $this->cleanAdminName($desa),
                    'kecamatan' => $this->cleanAdminName($kecamatan),
                    'kabupaten' => $kabupaten,
                    'provinsi' => $provinsi,
                    'alamat_lengkap' => $formatted,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Fallback Geocoder reverse error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Query standard forward geocoder fallback.
     *
     * @return array{lat: float, lng: float, data: array<string, string|null>}|null
     */
    protected function queryFallbackForward(string $address): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'E-Detimil-App/1.0 (telehealth-puskesmas-bogor)',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'addressdetails' => 1,
                'countrycodes' => 'id',
                'limit' => 1,
                'accept-language' => 'id',
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (! empty($json)) {
                    $item = $json[0];
                    $lat = (float) $item['lat'];
                    $lng = (float) $item['lon'];
                    $addr = $item['address'] ?? [];

                    return [
                        'lat' => $lat,
                        'lng' => $lng,
                        'data' => [
                            'desa' => $this->cleanAdminName($addr['village'] ?? $addr['suburb'] ?? $addr['neighbourhood'] ?? null),
                            'kecamatan' => $this->cleanAdminName($addr['subdistrict'] ?? $addr['city_district'] ?? null),
                            'kabupaten' => $addr['city'] ?? $addr['county'] ?? null,
                            'provinsi' => $addr['state'] ?? null,
                            'alamat_lengkap' => $item['display_name'] ?? null,
                        ],
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Fallback Geocoder forward error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Clean and normalize Indonesian administrative names.
     */
    protected function cleanAdminName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        return trim(preg_replace('/^(Desa|Kelurahan|Kecamatan|Kec\.)\s+/i', '', $name));
    }

    /**
     * Format result and find matched Desa in database.
     *
     * @param  array<string, string|null>  $data
     * @return array<string, mixed>
     */
    protected function formatResult(array $data, string $source, float $lat, float $lng): array
    {
        $desaName = $data['desa'];
        $kecamatan = $data['kecamatan'];
        $kabupaten = $data['kabupaten'];

        $matchedDesa = $this->findMatchingDesa($desaName, $kecamatan, $kabupaten);

        return [
            'success' => true,
            'source' => $source,
            'latitude' => $lat,
            'longitude' => $lng,
            'desa' => $desaName,
            'kecamatan' => $kecamatan,
            'kabupaten' => $kabupaten,
            'provinsi' => $data['provinsi'],
            'alamat_lengkap' => $data['alamat_lengkap'],
            'matched_desa_id' => $matchedDesa?->id,
            'matched_desa_nama' => $matchedDesa?->nama,
            'formatted_location' => implode(', ', array_filter([
                $desaName ? 'Desa/Kel. '.$desaName : null,
                $kecamatan ? 'Kec. '.$kecamatan : null,
                $kabupaten,
            ])),
        ];
    }

    /**
     * Find matching Desa model in database.
     */
    public function findMatchingDesa(?string $desaName, ?string $kecamatan = null, ?string $kabupaten = null): ?Desa
    {
        if (empty($desaName)) {
            return null;
        }

        $clean = $this->cleanAdminName($desaName);

        // Try exact match or contains
        $query = Desa::query();
        $desa = (clone $query)->where('nama', 'like', "%{$clean}%")->first();

        if (! $desa && $kecamatan) {
            $cleanKec = $this->cleanAdminName($kecamatan);
            $desa = (clone $query)->where('kecamatan', 'like', "%{$cleanKec}%")->first();
        }

        return $desa;
    }
}
