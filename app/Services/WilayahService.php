<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\User;
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

            if ($districtId === '1402050') {
                return [
                    ['id' => '1402050001', 'district_id' => '1402050', 'name' => 'BARANGAN'],
                    ['id' => '1402050002', 'district_id' => '1402050', 'name' => 'ALANG KEPAYANG'],
                    ['id' => '1402050003', 'district_id' => '1402050', 'name' => 'DANAU BARU'],
                    ['id' => '1402050004', 'district_id' => '1402050', 'name' => 'REDANG'],
                    ['id' => '1402050005', 'district_id' => '1402050', 'name' => 'KOTA LAMA'],
                    ['id' => '1402050006', 'district_id' => '1402050', 'name' => 'SUNGAI DAWU'],
                    ['id' => '1402050007', 'district_id' => '1402050', 'name' => 'PEMATANG JAYA'],
                    ['id' => '1402050008', 'district_id' => '1402050', 'name' => 'BUKIT PETALING'],
                    ['id' => '1402050009', 'district_id' => '1402050', 'name' => 'TANAH DATAR'],
                    ['id' => '1402050010', 'district_id' => '1402050', 'name' => 'TALANG JERINJING'],
                    ['id' => '1402050011', 'district_id' => '1402050', 'name' => 'PEMATANG REBA'],
                    ['id' => '1402050012', 'district_id' => '1402050', 'name' => 'PEKAN HERAN'],
                    ['id' => '1402050013', 'district_id' => '1402050', 'name' => 'RANTAU BAKUNG'],
                    ['id' => '1402050014', 'district_id' => '1402050', 'name' => 'SIALANG DUA DAHAN'],
                    ['id' => '1402050015', 'district_id' => '1402050', 'name' => 'TANI MAKMUR'],
                    ['id' => '1402050016', 'district_id' => '1402050', 'name' => 'SUNGAI BAUNG'],
                    ['id' => '1402050017', 'district_id' => '1402050', 'name' => 'AIR JERNIH'],
                    ['id' => '1402050018', 'district_id' => '1402050', 'name' => 'DANAU TIGA'],
                ];
            }

            if ($districtId === '1402040') {
                return [
                    ['id' => '1402040001', 'district_id' => '1402040', 'name' => 'AIR MOLEK I'],
                    ['id' => '1402040002', 'district_id' => '1402040', 'name' => 'AIR MOLEK II'],
                    ['id' => '1402040003', 'district_id' => '1402040', 'name' => 'BATU GAJAH'],
                    ['id' => '1402040004', 'district_id' => '1402040', 'name' => 'CANDIREJO'],
                    ['id' => '1402040005', 'district_id' => '1402040', 'name' => 'JATIREJO'],
                    ['id' => '1402040006', 'district_id' => '1402040', 'name' => 'KEMBANG HARUM'],
                    ['id' => '1402040007', 'district_id' => '1402040', 'name' => 'LEMBAH DUSUN GADING'],
                    ['id' => '1402040008', 'district_id' => '1402040', 'name' => 'PASIR KERANJI'],
                    ['id' => '1402040009', 'district_id' => '1402040', 'name' => 'PETALONGAN'],
                    ['id' => '1402040010', 'district_id' => '1402040', 'name' => 'SEKAR MAWAR'],
                    ['id' => '1402040011', 'district_id' => '1402040', 'name' => 'SERUMPUN JAYA'],
                    ['id' => '1402040012', 'district_id' => '1402040', 'name' => 'TANAH MERAH'],
                    ['id' => '1402040013', 'district_id' => '1402040', 'name' => 'TANJUNG GADING'],
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

    /**
     * Get API villages for a specific user / Admin Puskesmas territory directly.
     *
     * @return array<int, array{id: string, name: string, district_id: string, district_name: string, regency_name: string, province_name: string, existing_desa_id: ?int}>
     */
    public function getApiVillagesForUser(User $user): array
    {
        $districtIds = collect();

        // 1. Resolve from user's registered kecamatan / kecamatan_kerja
        $rawKec = $user->kecamatan_kerja ?? $user->kecamatan ?? $user->desa?->kecamatan;
        if (! empty($rawKec)) {
            $cleanKec = strtolower(trim(preg_replace('/^(kecamatan|kec\.?)\s+/i', '', $rawKec)));

            if (str_contains($cleanKec, 'rengat barat')) {
                $districtIds->push('1402050'); // Rengat Barat
            } elseif (str_contains($cleanKec, 'pasir penyu')) {
                $districtIds->push('1402040'); // Pasir Penyu
            } elseif (str_contains($cleanKec, 'sungai lala') || str_contains($cleanKec, 'sei lala')) {
                $districtIds->push('1402042'); // Sungai Lala
            } elseif (str_contains($cleanKec, 'lirik')) {
                $districtIds->push('1402041'); // Lirik
            } elseif (str_contains($cleanKec, 'peranap')) {
                $districtIds->push('1402010'); // Peranap
            } elseif (str_contains($cleanKec, 'seberida')) {
                $districtIds->push('1402020'); // Seberida
            } elseif (str_contains($cleanKec, 'rengat')) {
                $districtIds->push('1402060'); // Rengat
            }
        }

        // 2. Resolve from user's instansi / puskesmas name (e.g. Pekan Heran -> Rengat Barat)
        if (! empty($user->instansi)) {
            $instansi = strtolower(trim($user->instansi));

            if (str_contains($instansi, 'pekan heran') || str_contains($instansi, 'rengat barat')) {
                $districtIds->push('1402050'); // Rengat Barat
            } elseif (str_contains($instansi, 'air molek') || str_contains($instansi, 'pasir penyu')) {
                $districtIds->push('1402040'); // Pasir Penyu
            } elseif (str_contains($instansi, 'sungai parit') || str_contains($instansi, 'sei parit') || str_contains($instansi, 'sungai lala')) {
                $districtIds->push('1402042'); // Sungai Lala
            } elseif (str_contains($instansi, 'lirik')) {
                $districtIds->push('1402041'); // Lirik
            } elseif (str_contains($instansi, 'peranap')) {
                $districtIds->push('1402010'); // Peranap
            } elseif (str_contains($instansi, 'seberida')) {
                $districtIds->push('1402020'); // Seberida
            }
        }

        // 3. Check existing desas linked to user or user's puskesmas/instansi
        $relatedDesas = Desa::query()
            ->when($user->instansi, fn ($q) => $q->where('puskesmas', $user->instansi))
            ->when($user->desa_id, fn ($q) => $q->orWhere('id', $user->desa_id))
            ->get();

        foreach ($relatedDesas as $d) {
            if (! empty($d->kode_desa) && strlen($d->kode_desa) >= 7) {
                $districtIds->push(substr($d->kode_desa, 0, 7));
            }
        }

        // 4. Fallback if still empty: check user's regency or default to Rengat Barat (1402050)
        if ($districtIds->isEmpty()) {
            $districtIds->push('1402050');
        }

        $districtIds = $districtIds->unique()->values();

        $allVillages = [];
        $localDesas = Desa::all()->keyBy(fn ($d) => strtoupper(trim($d->nama)));

        // Districts metadata lookup (name, regency, province)
        $districtMeta = [
            '1402010' => [
                'district_name' => 'Kecamatan Peranap',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402011' => [
                'district_name' => 'Kecamatan Batang Peranap',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402020' => [
                'district_name' => 'Kecamatan Seberida',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402021' => [
                'district_name' => 'Kecamatan Batang Cenaku',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402022' => [
                'district_name' => 'Kecamatan Batang Gansal',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402030' => [
                'district_name' => 'Kecamatan Kelayang',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402031' => [
                'district_name' => 'Kecamatan Rakit Kulim',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402040' => [
                'district_name' => 'Kecamatan Pasir Penyu',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402041' => [
                'district_name' => 'Kecamatan Lirik',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402042' => [
                'district_name' => 'Kecamatan Sungai Lala',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402043' => [
                'district_name' => 'Kecamatan Lubuk Batu Jaya',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402050' => [
                'district_name' => 'Kecamatan Rengat Barat',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402060' => [
                'district_name' => 'Kecamatan Rengat',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
            '1402061' => [
                'district_name' => 'Kecamatan Kuala Cenaku',
                'regency_name' => 'KABUPATEN INDRAGIRI HULU',
                'province_name' => 'RIAU',
            ],
        ];

        foreach ($districtIds as $dId) {
            $villages = $this->getVillages((string) $dId);
            $meta = $districtMeta[$dId] ?? [
                'district_name' => $relatedDesas->firstWhere(fn ($d) => str_starts_with($d->kode_desa ?? '', $dId))?->kecamatan ?? ($user->kecamatan_kerja ?? 'Kecamatan Terkait'),
                'regency_name' => $relatedDesas->first()?->kabupaten ?? ($user->kabupaten ?? 'Kabupaten Terkait'),
                'province_name' => $relatedDesas->first()?->provinsi ?? ($user->provinsi ?? 'Indonesia'),
            ];

            foreach ($villages as $v) {
                $vName = strtoupper(trim($v['name']));
                $existing = $localDesas->get($vName);

                $allVillages[] = [
                    'id' => (string) $v['id'],
                    'name' => $vName,
                    'district_id' => (string) $dId,
                    'district_name' => $meta['district_name'],
                    'regency_name' => $meta['regency_name'],
                    'province_name' => $meta['province_name'],
                    'existing_desa_id' => $existing?->id,
                ];
            }
        }

        // Include any local desas from the database that might not be in the Kemendagri API list
        $existingApiNames = collect($allVillages)->pluck('name')->all();
        foreach ($relatedDesas as $d) {
            $dUpper = strtoupper(trim($d->nama));
            if (! in_array($dUpper, $existingApiNames)) {
                $allVillages[] = [
                    'id' => (string) ($d->kode_desa ?: ('LOCAL_'.$d->id)),
                    'name' => $dUpper,
                    'district_id' => substr($d->kode_desa ?? '1402050', 0, 7),
                    'district_name' => $d->kecamatan,
                    'regency_name' => $d->kabupaten,
                    'province_name' => $d->provinsi ?? 'Indonesia',
                    'existing_desa_id' => $d->id,
                ];
            }
        }

        // Sort alphabetically by village name
        usort($allVillages, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $allVillages;
    }
}
