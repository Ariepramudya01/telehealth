<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\IbuHamil;
use App\Models\User;
use App\Services\GoogleLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        protected GoogleLocationService $googleLocationService
    ) {}

    /**
     * Reverse geocode coordinates to retrieve Desa/Kelurahan, Kecamatan, and Kabupaten from Google.
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->googleLocationService->reverseGeocode(
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Geocode an address/place search to coordinates and administrative breakdown from Google.
     */
    public function geocode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $result = $this->googleLocationService->geocode($validated['address']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Register or update location coordinates for Ibu Hamil.
     */
    public function registerIbuHamilLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ibu_hamil_id' => ['nullable', 'exists:ibu_hamils,id'],
            'nik' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'alamat' => ['nullable', 'string'],
        ]);

        if (empty($validated['ibu_hamil_id']) && empty($validated['nik'])) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter ibu_hamil_id atau nik wajib disertakan.',
            ], 422);
        }

        $query = IbuHamil::query();
        if (! empty($validated['ibu_hamil_id'])) {
            $query->where('id', $validated['ibu_hamil_id']);
        } else {
            $query->where('nik', $validated['nik']);
        }

        $ibuHamil = $query->first();

        if (! $ibuHamil) {
            return response()->json([
                'success' => false,
                'message' => 'Data Ibu Hamil tidak ditemukan.',
            ], 404);
        }

        $ibuHamil->latitude = $validated['latitude'];
        $ibuHamil->longitude = $validated['longitude'];
        if (! empty($validated['alamat'])) {
            $ibuHamil->alamat = $validated['alamat'];
        }
        $ibuHamil->save();

        return response()->json([
            'success' => true,
            'message' => "Lokasi koordinat Ibu Hamil [{$ibuHamil->nama}] berhasil disimpan.",
            'data' => [
                'id' => $ibuHamil->id,
                'nama' => $ibuHamil->nama,
                'nik' => $ibuHamil->nik,
                'desa' => $ibuHamil->desa?->nama,
                'latitude' => $ibuHamil->latitude,
                'longitude' => $ibuHamil->longitude,
                'alamat' => $ibuHamil->alamat,
                'maps_url' => "https://www.google.com/maps?q={$ibuHamil->latitude},{$ibuHamil->longitude}",
            ],
        ]);
    }

    /**
     * Register or update location coordinates for Petugas (Bidan Desa atau Kader).
     */
    public function registerPetugasLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'alamat' => ['nullable', 'string'],
        ]);

        if (empty($validated['user_id']) && empty($validated['email']) && empty($validated['phone'])) {
            return response()->json([
                'success' => false,
                'message' => 'Salah satu parameter identifikasi (user_id, email, atau phone) wajib disertakan.',
            ], 422);
        }

        $query = User::whereIn('role', [Role::Bidan, Role::Kader]);

        if (! empty($validated['user_id'])) {
            $query->where('id', $validated['user_id']);
        } elseif (! empty($validated['email'])) {
            $query->where('email', $validated['email']);
        } else {
            $query->where('phone', $validated['phone']);
        }

        $user = $query->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Petugas Bidan / Kader tidak ditemukan.',
            ], 404);
        }

        $user->latitude = $validated['latitude'];
        $user->longitude = $validated['longitude'];
        if (! empty($validated['alamat'])) {
            $user->alamat = $validated['alamat'];
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Lokasi koordinat {$user->role->label()} [{$user->name}] berhasil disimpan.",
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'desa' => $user->desa?->nama,
                'phone' => $user->phone,
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
                'alamat' => $user->alamat,
                'maps_url' => "https://www.google.com/maps?q={$user->latitude},{$user->longitude}",
            ],
        ]);
    }

    /**
     * Get list of all Ibu Hamil locations.
     */
    public function getIbuHamilLocations(Request $request): JsonResponse
    {
        $query = IbuHamil::with(['desa', 'skrinings' => fn ($q) => $q->latest('tanggal_skrining')])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('desa_id')) {
            $query->where('desa_id', $request->desa_id);
        }

        $items = $query->get()->map(function ($bumil) {
            $latest = $bumil->latestSkrining();

            return [
                'id' => $bumil->id,
                'nama' => $bumil->nama,
                'nik' => $bumil->nik,
                'desa_id' => $bumil->desa_id,
                'desa' => $bumil->desa?->nama,
                'usia' => $bumil->usia,
                'hpht' => $bumil->hpht?->format('Y-m-d'),
                'taksiran_persalinan' => $bumil->taksiran_persalinan?->format('Y-m-d'),
                'skor_spr' => $latest?->total_skor ?? 2,
                'kategori_risiko' => $latest?->kategori_risiko->value ?? 'KRR',
                'kategori_risiko_label' => $latest?->kategori_risiko->label() ?? 'Kehamilan Risiko Rendah',
                'warna_pin' => $latest ? $latest->kategori_risiko->color() : 'green',
                'tekanan_darah' => $bumil->tekanan_darah,
                'lila' => $bumil->lila,
                'is_kek' => $bumil->is_kek,
                'hemoglobin' => $bumil->hemoglobin,
                'is_anemia' => $bumil->is_anemia,
                'latitude' => $bumil->latitude,
                'longitude' => $bumil->longitude,
                'alamat' => $bumil->alamat,
                'maps_url' => "https://www.google.com/maps?q={$bumil->latitude},{$bumil->longitude}",
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $items->count(),
            'data' => $items,
        ]);
    }

    /**
     * Get list of Petugas (Bidan & Kader) locations.
     */
    public function getPetugasLocations(Request $request): JsonResponse
    {
        $query = User::with('desa')
            ->whereIn('role', [Role::Bidan, Role::Kader])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('desa_id')) {
            $query->where('desa_id', $request->desa_id);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $items = $query->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role->value,
                'role_label' => $u->role->label(),
                'desa_id' => $u->desa_id,
                'desa' => $u->desa?->nama,
                'phone' => $u->phone,
                'latitude' => $u->latitude,
                'longitude' => $u->longitude,
                'alamat' => $u->alamat,
                'maps_url' => "https://www.google.com/maps?q={$u->latitude},{$u->longitude}",
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $items->count(),
            'data' => $items,
        ]);
    }

    /**
     * Get combined GeoJSON FeatureCollection for all locations (Ibu Hamil, Bidan, Kader).
     */
    public function getAllLocationsGeoJson(Request $request): JsonResponse
    {
        $desaId = $request->query('desa_id');

        $bumils = IbuHamil::with(['desa', 'skrinings' => fn ($q) => $q->latest('tanggal_skrining')])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($desaId, fn ($q) => $q->where('desa_id', $desaId))
            ->get();

        $petugas = User::with('desa')
            ->whereIn('role', [Role::Bidan, Role::Kader])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($desaId, fn ($q) => $q->where('desa_id', $desaId))
            ->get();

        $features = [];

        foreach ($bumils as $b) {
            $latest = $b->latestSkrining();
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$b->longitude, $b->latitude],
                ],
                'properties' => [
                    'entity' => 'ibu_hamil',
                    'id' => $b->id,
                    'title' => $b->nama,
                    'subtitle' => 'Ibu Hamil - '.$b->desa?->nama,
                    'kategori_risiko' => $latest?->kategori_risiko->value ?? 'KRR',
                    'skor_spr' => $latest?->total_skor ?? 2,
                    'tekanan_darah' => $b->tekanan_darah,
                    'lila' => $b->lila,
                    'color' => $latest ? $latest->kategori_risiko->color() : 'green',
                ],
            ];
        }

        foreach ($petugas as $p) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$p->longitude, $p->latitude],
                ],
                'properties' => [
                    'entity' => $p->role->value,
                    'id' => $p->id,
                    'title' => $p->name,
                    'subtitle' => $p->role->label().' - '.$p->desa?->nama,
                    'phone' => $p->phone,
                    'color' => $p->isBidan() ? 'teal' : 'blue',
                ],
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * API to create a new Ibu Hamil with complete location & physical exam data.
     */
    public function createIbuHamilWithLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desa_id' => ['required', 'exists:desas,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:ibu_hamils,nik'],
            'tanggal_lahir' => ['nullable', 'date'],
            'no_hp' => ['nullable', 'string'],
            'nama_suami' => ['nullable', 'string'],
            'golongan_darah' => ['nullable', 'string'],
            'tinggi_badan' => ['nullable', 'numeric'],
            'berat_badan' => ['nullable', 'numeric'],
            'hpht' => ['nullable', 'date'],
            'taksiran_persalinan' => ['nullable', 'date'],
            'gravida' => ['nullable', 'integer'],
            'para' => ['nullable', 'integer'],
            'abortus' => ['nullable', 'integer'],
            'jumlah_anak_hidup' => ['nullable', 'integer'],
            'alamat' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            // Pemeriksaan Fisik
            'tekanan_darah_sistol' => ['nullable', 'integer'],
            'tekanan_darah_diastol' => ['nullable', 'integer'],
            'lila' => ['nullable', 'numeric'],
            'tfu' => ['nullable', 'numeric'],
            'djj' => ['nullable', 'integer'],
            'hemoglobin' => ['nullable', 'numeric'],
            'status_imunisasi_tt' => ['nullable', 'string'],
            'refleks_patela' => ['nullable', 'string'],
            'edema' => ['nullable', 'string'],
            'status_gizi' => ['nullable', 'string'],
            'kondisi_umum' => ['nullable', 'string'],
        ]);

        if (empty($validated['user_id'])) {
            $defaultUser = User::where('desa_id', $validated['desa_id'])->first() ?? User::first();
            $validated['user_id'] = $defaultUser->id;
        }

        if (! empty($validated['hpht']) && empty($validated['taksiran_persalinan'])) {
            $validated['taksiran_persalinan'] = date('Y-m-d', strtotime($validated['hpht'].' + 280 days'));
        }

        $validated['is_active'] = true;

        $ibuHamil = IbuHamil::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Ibu Hamil [{$ibuHamil->nama}] berhasil didaftarkan beserta lokasi koordinat dan pemeriksaan fisik.",
            'data' => $ibuHamil->load('desa'),
        ], 201);
    }
}
