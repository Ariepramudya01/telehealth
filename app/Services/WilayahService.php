<?php

namespace App\Services;

use App\Models\Desa;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WilayahService
{
    protected string $baseUrl = 'https://emsifa.github.io/api-wilayah-indonesia/api';

    /**
     * Get list of all provinces in Indonesia.
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function getProvinces(): array
    {
        return Cache::remember('wilayah_provinces', 86400, function () {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(4)
                    ->get("{$this->baseUrl}/provinces.json");

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (Exception $e) {
                Log::warning('Failed fetching provinces from Kemendagri API: '.$e->getMessage());
            }

            // Fallback list of provinces
            return [
                ['id' => '32', 'name' => 'JAWA BARAT'],
                ['id' => '31', 'name' => 'DKI JAKARTA'],
                ['id' => '33', 'name' => 'JAWA TENGAH'],
                ['id' => '34', 'name' => 'DI YOGYAKARTA'],
                ['id' => '35', 'name' => 'JAWA TIMUR'],
                ['id' => '36', 'name' => 'BANTEN'],
                ['id' => '51', 'name' => 'BALI'],
                ['id' => '11', 'name' => 'ACEH'],
                ['id' => '12', 'name' => 'SUMATERA UTARA'],
                ['id' => '13', 'name' => 'SUMATERA BARAT'],
                ['id' => '14', 'name' => 'RIAU'],
                ['id' => '15', 'name' => 'JAMBI'],
                ['id' => '16', 'name' => 'SUMATERA SELATAN'],
                ['id' => '17', 'name' => 'BENGKULU'],
                ['id' => '18', 'name' => 'LAMPUNG'],
                ['id' => '19', 'name' => 'KEPULAUAN BANGKA BELITUNG'],
                ['id' => '21', 'name' => 'KEPULAUAN RIAU'],
                ['id' => '61', 'name' => 'KALIMANTAN BARAT'],
                ['id' => '62', 'name' => 'KALIMANTAN TENGAH'],
                ['id' => '63', 'name' => 'KALIMANTAN SELATAN'],
                ['id' => '64', 'name' => 'KALIMANTAN TIMUR'],
                ['id' => '65', 'name' => 'KALIMANTAN UTARA'],
                ['id' => '71', 'name' => 'SULAWESI UTARA'],
                ['id' => '72', 'name' => 'SULAWESI TENGAH'],
                ['id' => '73', 'name' => 'SULAWESI SELATAN'],
                ['id' => '74', 'name' => 'SULAWESI TENGGARA'],
                ['id' => '75', 'name' => 'GORONTALO'],
                ['id' => '76', 'name' => 'SULAWESI BARAT'],
                ['id' => '52', 'name' => 'NUSA TENGGARA BARAT'],
                ['id' => '53', 'name' => 'NUSA TENGGARA TIMUR'],
                ['id' => '81', 'name' => 'MALUKU'],
                ['id' => '82', 'name' => 'MALUKU UTARA'],
                ['id' => '91', 'name' => 'PAPUA BARAT'],
                ['id' => '94', 'name' => 'PAPUA'],
            ];
        });
    }

    /**
     * Get regencies / cities for a specific province.
     *
     * @return array<int, array{id: string, province_id: string, name: string}>
     */
    public function getRegencies(string $provinceId): array
    {
        return Cache::remember("wilayah_regencies_{$provinceId}", 86400, function () use ($provinceId) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(4)
                    ->get("{$this->baseUrl}/regencies/{$provinceId}.json");

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (Exception $e) {
                Log::warning("Failed fetching regencies for province {$provinceId}: ".$e->getMessage());
            }

            if ($provinceId === '32') {
                return [
                    ['id' => '3201', 'province_id' => '32', 'name' => 'KABUPATEN BOGOR'],
                    ['id' => '3271', 'province_id' => '32', 'name' => 'KOTA BOGOR'],
                    ['id' => '3204', 'province_id' => '32', 'name' => 'KABUPATEN BANDUNG'],
                    ['id' => '3273', 'province_id' => '32', 'name' => 'KOTA BANDUNG'],
                ];
            }

            return [];
        });
    }

    /**
     * Get districts (kecamatan) for a specific regency.
     *
     * @return array<int, array{id: string, regency_id: string, name: string}>
     */
    public function getDistricts(string $regencyId): array
    {
        return Cache::remember("wilayah_districts_{$regencyId}", 86400, function () use ($regencyId) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(4)
                    ->get("{$this->baseUrl}/districts/{$regencyId}.json");

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (Exception $e) {
                Log::warning("Failed fetching districts for regency {$regencyId}: ".$e->getMessage());
            }

            if ($regencyId === '3201') {
                return [
                    ['id' => '320101', 'regency_id' => '3201', 'name' => 'CIOMAS'],
                    ['id' => '320102', 'regency_id' => '3201', 'name' => 'DRAMAGA'],
                    ['id' => '320103', 'regency_id' => '3201', 'name' => 'CIBINONG'],
                ];
            }

            return [];
        });
    }

    /**
     * Get villages (desa/kelurahan) for a specific district.
     *
     * @return array<int, array{id: string, district_id: string, name: string}>
     */
    public function getVillages(string $districtId): array
    {
        return Cache::remember("wilayah_villages_{$districtId}", 86400, function () use ($districtId) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(4)
                    ->get("{$this->baseUrl}/villages/{$districtId}.json");

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (Exception $e) {
                Log::warning("Failed fetching villages for district {$districtId}: ".$e->getMessage());
            }

            if ($districtId === '320101') {
                return [
                    ['id' => '3201010001', 'district_id' => '320101', 'name' => 'SUKAMAJU'],
                    ['id' => '3201010002', 'district_id' => '320101', 'name' => 'SUKARASA'],
                    ['id' => '3201010003', 'district_id' => '320101', 'name' => 'CIOMAS'],
                ];
            }

            return [];
        });
    }

    /**
     * Resolve existing Desa or create a new Desa record from selected cascading dropdown.
     *
     * @param  array{nama: string, kecamatan: string, kabupaten: string, provinsi?: ?string, kode_desa?: ?string}  $data
     */
    public function resolveOrCreateDesa(array $data): Desa
    {
        $nama = ucwords(strtolower(trim($data['nama'])));
        $kecamatan = trim($data['kecamatan']);
        if (! str_starts_with(strtolower($kecamatan), 'kecamatan') && ! str_starts_with(strtolower($kecamatan), 'distrik')) {
            $kecamatan = 'Kecamatan '.ucwords(strtolower($kecamatan));
        }

        $kabupaten = trim($data['kabupaten']);
        if (! str_starts_with(strtolower($kabupaten), 'kabupaten') && ! str_starts_with(strtolower($kabupaten), 'kota')) {
            $kabupaten = 'Kabupaten '.ucwords(strtolower($kabupaten));
        }

        $provinsi = ! empty($data['provinsi']) ? ucwords(strtolower(trim($data['provinsi']))) : 'Jawa Barat';

        // Check if Desa already exists
        $desa = Desa::where('nama', $nama)
            ->where('kecamatan', $kecamatan)
            ->first();

        if (! $desa) {
            $cleanKec = trim(preg_replace('/^Kecamatan\s+/i', '', $kecamatan));
            $desa = Desa::where('nama', 'like', "%{$nama}%")
                ->where('kecamatan', 'like', "%{$cleanKec}%")
                ->first();
        }

        if ($desa) {
            if (empty($desa->provinsi) && ! empty($provinsi)) {
                $desa->update(['provinsi' => $provinsi]);
            }

            return $desa;
        }

        $kodeDesa = $data['kode_desa'] ?? ('ID.'.substr(md5($nama.$kecamatan.$kabupaten), 0, 8));

        return Desa::create([
            'nama' => $nama,
            'kecamatan' => $kecamatan,
            'kabupaten' => $kabupaten,
            'provinsi' => $provinsi,
            'kode_desa' => $kodeDesa,
        ]);
    }
}
