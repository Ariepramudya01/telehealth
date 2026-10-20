<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DesaController extends Controller
{
    /**
     * Display a listing of desas.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $selectedPuskesmas = $request->input('puskesmas');
        $selectedKecamatan = $request->input('kecamatan');

        $query = Desa::query()->withCount(['users', 'ibuHamils', 'kunjungans']);
        $baseListQuery = Desa::query();

        if (! $user->isAdmin()) {
            $query->where('id', $user->desa_id);
            $baseListQuery->where('id', $user->desa_id);
        } else {
            if ($user->isAdminPuskesmas()) {
                $allDesas = Desa::with('users')->get();
                $accessibleIds = $allDesas->filter(fn ($d) => $user->canAccessDesa($d))->pluck('id')->all();
                $query->whereIn('id', $accessibleIds);
                $baseListQuery->whereIn('id', $accessibleIds);
            } elseif ($user->isDinkesKabupaten()) {
                $cleanKab = strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) $user->kabupaten)));
                if ($cleanKab) {
                    $query->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"]);
                    $baseListQuery->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"]);
                }
            }

            if ($selectedPuskesmas) {
                $query->where('puskesmas', $selectedPuskesmas);
            }
            if ($selectedKecamatan) {
                $query->where('kecamatan', $selectedKecamatan);
            }
        }

        $desas = $query->orderBy('puskesmas')->orderBy('kecamatan')->orderBy('nama')->get();

        $puskesmasList = (clone $baseListQuery)->whereNotNull('puskesmas')->where('puskesmas', '!=', '')->distinct()->pluck('puskesmas');
        $kecamatanList = (clone $baseListQuery)->whereNotNull('kecamatan')->where('kecamatan', '!=', '')->distinct()->pluck('kecamatan');

        return view('desa.index', compact('desas', 'puskesmasList', 'kecamatanList', 'selectedPuskesmas', 'selectedKecamatan'));
    }

    /**
     * Show the form for creating a new desa.
     */
    public function create(): View
    {
        $puskesmasList = Desa::whereNotNull('puskesmas')->where('puskesmas', '!=', '')->distinct()->pluck('puskesmas');

        return view('desa.create', compact('puskesmasList'));
    }

    /**
     * Store a newly created desa.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'puskesmas' => ['nullable', 'string', 'max:150'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:150'],
            'kode_desa' => ['nullable', 'string', 'max:50', 'unique:desas,kode_desa'],
        ]);

        Desa::create($validated);

        return redirect()->route('desa.index')
            ->with('success', 'Data desa berhasil ditambahkan.');
    }

    /**
     * Display the specified desa.
     */
    public function show(Desa $desa): View
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $user->desa_id !== $desa->id) {
            abort(403, 'Anda hanya dapat melihat data desa Anda.');
        }

        $desa->load(['users', 'ibuHamils.skrinings', 'kunjungans']);

        return view('desa.show', compact('desa'));
    }

    /**
     * Show the form for editing the specified desa.
     */
    public function edit(Desa $desa): View
    {
        $puskesmasList = Desa::whereNotNull('puskesmas')->where('puskesmas', '!=', '')->distinct()->pluck('puskesmas');

        return view('desa.edit', compact('desa', 'puskesmasList'));
    }

    /**
     * Update the specified desa.
     */
    public function update(Request $request, Desa $desa): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'puskesmas' => ['nullable', 'string', 'max:150'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:150'],
            'kode_desa' => ['nullable', 'string', 'max:50', 'unique:desas,kode_desa,'.$desa->id],
        ]);

        $desa->update($validated);

        return redirect()->route('desa.index')
            ->with('success', 'Data desa berhasil diperbarui.');
    }

    /**
     * Remove the specified desa.
     */
    public function destroy(Desa $desa): RedirectResponse
    {
        $desa->delete();

        return redirect()->route('desa.index')
            ->with('success', 'Data desa berhasil dihapus.');
    }
}
