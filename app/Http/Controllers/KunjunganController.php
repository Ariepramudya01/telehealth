<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KunjunganController extends Controller
{
    /**
     * Display a listing of kunjungans.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->isKader()) {
            abort(403, 'Kader hanya dapat melihat ringkasan capaian di dashboard.');
        }

        $query = Kunjungan::with(['ibuHamil.desa', 'user']);

        if (! $user->isAdmin()) {
            $query->where('desa_id', $user->desa_id);
        } elseif ($request->filled('desa_id')) {
            $query->where('desa_id', $request->desa_id);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_kunjungan', $request->jenis);
        }

        if ($request->filled('status')) {
            if ($request->status === 'hadir') {
                $query->where('status_hadir', true);
            } elseif ($request->status === 'dilayani') {
                $query->where('status_dilayani', true);
            } elseif ($request->status === 'tidak_hadir') {
                $query->where('status_hadir', false);
            }
        }

        $kunjungans = $query->latest('tanggal_kunjungan')->paginate(15)->withQueryString();
        $desas = $user->isAdmin() ? Desa::orderBy('nama')->get() : collect();

        return view('kunjungan.index', compact('kunjungans', 'desas'));
    }

    /**
     * Show the form for creating a new kunjungan.
     */
    public function create(Request $request): View
    {
        $user = Auth::user();

        if ($user->isKader()) {
            abort(403);
        }

        $ibuHamilQuery = IbuHamil::where('is_active', true);
        if (! $user->isAdmin()) {
            $ibuHamilQuery->where('desa_id', $user->desa_id);
        }

        $ibuHamils = $ibuHamilQuery->orderBy('nama')->get();
        $selectedIbuHamilId = $request->query('ibu_hamil_id');

        return view('kunjungan.create', compact('ibuHamils', 'selectedIbuHamilId'));
    }

    /**
     * Store a newly created kunjungan.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isKader()) {
            abort(403);
        }

        $validated = $request->validate([
            'ibu_hamil_id' => ['required', 'exists:ibu_hamils,id'],
            'tanggal_kunjungan' => ['required', 'date'],
            'jenis_kunjungan' => ['required', 'string', 'in:K1,K2,K3,K4,K5,K6'],
            'status_hadir' => ['nullable', 'boolean'],
            'status_dilayani' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string'],
        ]);

        $ibuHamil = IbuHamil::findOrFail($validated['ibu_hamil_id']);

        if (! $user->isAdmin() && $ibuHamil->desa_id !== $user->desa_id) {
            abort(403);
        }

        $statusHadir = $request->boolean('status_hadir');
        $statusDilayani = $statusHadir && $request->boolean('status_dilayani');

        Kunjungan::create([
            'ibu_hamil_id' => $ibuHamil->id,
            'user_id' => $user->id,
            'desa_id' => $ibuHamil->desa_id,
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'status_hadir' => $statusHadir,
            'status_dilayani' => $statusDilayani,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('kunjungan.index')
            ->with('success', 'Catatan kunjungan berhasil disimpan.');
    }

    /**
     * Show the form for editing the specified kunjungan.
     */
    public function edit(Kunjungan $kunjungan): View
    {
        $user = Auth::user();

        if ($user->isKader() || (! $user->isAdmin() && $kunjungan->desa_id !== $user->desa_id)) {
            abort(403);
        }

        return view('kunjungan.edit', compact('kunjungan'));
    }

    /**
     * Update the specified kunjungan.
     */
    public function update(Request $request, Kunjungan $kunjungan): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isKader() || (! $user->isAdmin() && $kunjungan->desa_id !== $user->desa_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'tanggal_kunjungan' => ['required', 'date'],
            'jenis_kunjungan' => ['required', 'string', 'in:K1,K2,K3,K4,K5,K6'],
            'status_hadir' => ['nullable', 'boolean'],
            'status_dilayani' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string'],
        ]);

        $statusHadir = $request->boolean('status_hadir');
        $statusDilayani = $statusHadir && $request->boolean('status_dilayani');

        $kunjungan->update([
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'status_hadir' => $statusHadir,
            'status_dilayani' => $statusDilayani,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('kunjungan.index')
            ->with('success', 'Catatan kunjungan berhasil diperbarui.');
    }

    /**
     * Remove the specified kunjungan.
     */
    public function destroy(Kunjungan $kunjungan): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isKader() || (! $user->isAdmin() && $kunjungan->desa_id !== $user->desa_id)) {
            abort(403);
        }

        $kunjungan->delete();

        return redirect()->route('kunjungan.index')
            ->with('success', 'Catatan kunjungan berhasil dihapus.');
    }
}

