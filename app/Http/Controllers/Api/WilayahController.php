<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Services\WilayahService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function __construct(
        protected WilayahService $wilayahService
    ) {}

    /**
     * Get list of all provinces in Indonesia.
     */
    public function provinces(): JsonResponse
    {
        $provinces = $this->wilayahService->getProvinces();

        return response()->json([
            'success' => true,
            'data' => $provinces,
        ]);
    }

    /**
     * Get regencies / cities by province ID.
     */
    public function regencies(string $provinceId): JsonResponse
    {
        $regencies = $this->wilayahService->getRegencies($provinceId);

        return response()->json([
            'success' => true,
            'data' => $regencies,
        ]);
    }

    /**
     * Get districts (kecamatan) by regency ID.
     */
    public function districts(string $regencyId): JsonResponse
    {
        $districts = $this->wilayahService->getDistricts($regencyId);

        return response()->json([
            'success' => true,
            'data' => $districts,
        ]);
    }

    /**
     * Get villages (desa/kelurahan) by district ID.
     */
    public function villages(string $districtId): JsonResponse
    {
        $villages = $this->wilayahService->getVillages($districtId);

        return response()->json([
            'success' => true,
            'data' => $villages,
        ]);
    }

    /**
     * Get desas from local database, optionally filtered by kecamatan.
     */
    public function localDesas(Request $request): JsonResponse
    {
        $query = Desa::query();

        if ($request->filled('kecamatan')) {
            $cleanKec = trim(preg_replace('/^Kecamatan\s+/i', '', $request->kecamatan));
            $query->where('kecamatan', 'like', "%{$cleanKec}%");
        }

        if ($request->filled('kabupaten')) {
            $query->where('kabupaten', 'like', '%'.$request->kabupaten.'%');
        }

        $desas = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'data' => $desas,
        ]);
    }

    /**
     * Resolve existing Desa or create a new Desa record from selected cascading dropdown.
     */
    public function resolveDesa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'kode_desa' => ['nullable', 'string', 'max:50'],
        ]);

        $desa = $this->wilayahService->resolveOrCreateDesa($validated);

        return response()->json([
            'success' => true,
            'data' => $desa,
        ]);
    }
}
