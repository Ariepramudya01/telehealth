<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\IbuHamil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IbuHamilController extends Controller
{
    /**
     * Display a listing of ibu hamil.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = IbuHamil::with(['desa', 'user', 'skrinings' => fn ($q) => $q->latest('tanggal_skrining')]);

        if (! $user->isAdmin()) {
            $query->where('desa_id', $user->desa_id);
        } elseif ($request->filled('desa_id')) {
            $query->where('desa_id', $request->desa_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nama_suami', 'like', "%{$search}%");
            });
        }

        if ($request->filled('risiko')) {
            $risiko = $request->risiko;
            $query->whereHas('skrinings', function ($q) use ($risiko) {
                $q->where('kategori_risiko', $risiko);
            });
        }

        $ibuHamils = $query->latest()->paginate(15)->withQueryString();
        $desas = $user->isAdmin() ? Desa::orderBy('nama')->get() : collect();

        return view('ibu-hamil.index', compact('ibuHamils', 'desas'));
    }

    /**
     * Show the form for creating a new ibu hamil.
     */
    public function create(): View
    {
        $user = Auth::user();
        $desas = $user->isAdmin() ? Desa::orderBy('nama')->get() : Desa::where('id', $user->desa_id)->get();

        return view('ibu-hamil.create', compact('desas'));
    }

    /**
     * Store a newly created ibu hamil.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:ibu_hamils,nik'],
            'desa_id' => [$user->isAdmin() ? 'required' : 'nullable', 'exists:desas,id'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nama_suami' => ['nullable', 'string', 'max:255'],
            'golongan_darah' => ['nullable', 'string', 'in:A,B,AB,O'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'berat_badan' => ['nullable', 'numeric', 'min:30', 'max:200'],
            'hpht' => ['nullable', 'date'],
            'taksiran_persalinan' => ['nullable', 'date'],
            'gravida' => ['nullable', 'integer', 'min:0'],
            'para' => ['nullable', 'integer', 'min:0'],
            'abortus' => ['nullable', 'integer', 'min:0'],
            'jumlah_anak_hidup' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! $user->isAdmin()) {
            $validated['desa_id'] = $user->desa_id;
        }

        // Auto-calculate taksiran persalinan (Naegele's rule) if HPHT provided and TP empty
        if (! empty($validated['hpht']) && empty($validated['taksiran_persalinan'])) {
            $validated['taksiran_persalinan'] = date('Y-m-d', strtotime($validated['hpht'].' + 280 days'));
        }

        $validated['user_id'] = $user->id;
        $validated['is_active'] = true;

        $ibuHamil = IbuHamil::create($validated);

        return redirect()->route('ibu-hamil.show', $ibuHamil)
            ->with('success', 'Data Ibu Hamil berhasil disimpan. Silakan lakukan skrining Skor Poedji Rochjati.');
    }

    /**
     * Display the specified ibu hamil.
     */
    public function show(IbuHamil $ibuHamil): View
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $user->desa_id !== $ibuHamil->desa_id) {
            abort(403, 'Anda hanya dapat mengakses data ibu hamil di desa Anda.');
        }

        $ibuHamil->load([
            'desa',
            'user',
            'skrinings.user',
            'skrinings.details.pertanyaanSpr',
            'kunjungans.user',
        ]);

        $latestSkrining = $ibuHamil->latestSkrining();

        return view('ibu-hamil.show', compact('ibuHamil', 'latestSkrining'));
    }

    /**
     * Show the form for editing the specified ibu hamil.
     */
    public function edit(IbuHamil $ibuHamil): View
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $user->desa_id !== $ibuHamil->desa_id) {
            abort(403);
        }

        $desas = $user->isAdmin() ? Desa::orderBy('nama')->get() : Desa::where('id', $user->desa_id)->get();

        return view('ibu-hamil.edit', compact('ibuHamil', 'desas'));
    }

    /**
     * Update the specified ibu hamil.
     */
    public function update(Request $request, IbuHamil $ibuHamil): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $user->desa_id !== $ibuHamil->desa_id) {
            abort(403);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:ibu_hamils,nik,'.$ibuHamil->id],
            'desa_id' => [$user->isAdmin() ? 'required' : 'nullable', 'exists:desas,id'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nama_suami' => ['nullable', 'string', 'max:255'],
            'golongan_darah' => ['nullable', 'string', 'in:A,B,AB,O'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'berat_badan' => ['nullable', 'numeric', 'min:30', 'max:200'],
            'hpht' => ['nullable', 'date'],
            'taksiran_persalinan' => ['nullable', 'date'],
            'gravida' => ['nullable', 'integer', 'min:0'],
            'para' => ['nullable', 'integer', 'min:0'],
            'abortus' => ['nullable', 'integer', 'min:0'],
            'jumlah_anak_hidup' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        if (! $user->isAdmin()) {
            unset($validated['desa_id']);
        }

        $ibuHamil->update($validated);

        return redirect()->route('ibu-hamil.show', $ibuHamil)
            ->with('success', 'Data Ibu Hamil berhasil diperbarui.');
    }

    /**
     * Remove the specified ibu hamil.
     */
    public function destroy(IbuHamil $ibuHamil): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $user->desa_id !== $ibuHamil->desa_id) {
            abort(403);
        }

        $ibuHamil->delete();

        return redirect()->route('ibu-hamil.index')
            ->with('success', 'Data Ibu Hamil berhasil dihapus.');
    }
}
