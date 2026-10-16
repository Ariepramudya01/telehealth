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
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $desas = Desa::withCount(['users', 'ibuHamils', 'kunjungans'])->get();
        } else {
            $desas = Desa::where('id', $user->desa_id)
                ->withCount(['users', 'ibuHamils', 'kunjungans'])
                ->get();
        }

        return view('desa.index', compact('desas'));
    }

    /**
     * Show the form for creating a new desa.
     */
    public function create(): View
    {
        return view('desa.create');
    }

    /**
     * Store a newly created desa.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
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
        return view('desa.edit', compact('desa'));
    }

    /**
     * Update the specified desa.
     */
    public function update(Request $request, Desa $desa): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
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

