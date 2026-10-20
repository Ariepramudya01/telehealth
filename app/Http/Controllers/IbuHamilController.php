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

        // 1. Resolve Accessible Desas based on User Role & Jurisdiction
        $allDesas = Desa::with('users')->orderBy('nama')->get();
        $puskesmasDesas = collect();
        $otherDesas = collect();
        $puskesmasDesaIds = [];

        if ($user->isAdminPuskesmas()) {
            $puskesmasDesas = $allDesas->filter(fn ($d) => $user->canAccessDesa($d))->values();
            $puskesmasDesaIds = $puskesmasDesas->pluck('id')->all();
            $otherDesas = $allDesas->reject(fn ($d) => $user->canAccessDesa($d))->values();
        } elseif ($user->isBidan() || $user->isKader()) {
            $puskesmasDesas = $allDesas->where('id', $user->desa_id)->values();
            $puskesmasDesaIds = $user->desa_id ? [$user->desa_id] : [];
            $otherDesas = $allDesas->where('id', '!=', $user->desa_id)->values();
        } else {
            // Dinkes Provinsi (Admin Utama) or Dinkes Kabupaten
            $puskesmasDesas = $allDesas;
            $puskesmasDesaIds = $allDesas->pluck('id')->all();
            $otherDesas = collect();
        }

        $query = IbuHamil::with(['desa', 'user', 'skrinings' => fn ($q) => $q->latest('tanggal_skrining')]);

        // 2. Base Jurisdiction Scoping
        if ($user->isAdminPuskesmas()) {
            $cleanPusk = $user->clean_puskesmas_keyword;
            if (! $request->filled('search') && ! $request->filled('desa_id')) {
                $query->where(function ($q) use ($puskesmasDesaIds, $cleanPusk) {
                    $q->whereIn('desa_id', $puskesmasDesaIds)
                        ->orWhereHas('kunjungans', function ($k) use ($puskesmasDesaIds, $cleanPusk) {
                            $k->whereIn('desa_id', $puskesmasDesaIds)
                                ->orWhereHas('user', function ($u) use ($puskesmasDesaIds, $cleanPusk) {
                                    $u->whereIn('desa_id', $puskesmasDesaIds);
                                    if ($cleanPusk) {
                                        $u->orWhere('instansi', 'like', "%{$cleanPusk}%");
                                    }
                                });
                        })
                        ->orWhereHas('skrinings.user', function ($u) use ($puskesmasDesaIds, $cleanPusk) {
                            $u->whereIn('desa_id', $puskesmasDesaIds);
                            if ($cleanPusk) {
                                $u->orWhere('instansi', 'like', "%{$cleanPusk}%");
                            }
                        })
                        ->orWhereHas('user', function ($u) use ($puskesmasDesaIds, $cleanPusk) {
                            $u->whereIn('desa_id', $puskesmasDesaIds);
                            if ($cleanPusk) {
                                $u->orWhere('instansi', 'like', "%{$cleanPusk}%");
                            }
                        });
                });
            }
        } elseif ($user->isDinkesKabupaten()) {
            $userKab = $user->kabupaten;
            $cleanKab = strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) $userKab)));
            if ($cleanKab) {
                $query->whereHas('desa', fn ($d) => $d->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"]));
            }
        }

        // 3. Search Filter (Nama Bumil, NIK, Suami, No HP)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nama_suami', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // 4. Other Standard Filters (desa_id, risiko)
        if ($request->filled('desa_id')) {
            $query->where('desa_id', $request->desa_id);
        }

        if ($request->filled('risiko')) {
            $risiko = $request->risiko;
            $query->whereHas('skrinings', function ($q) use ($risiko) {
                $q->where('kategori_risiko', $risiko);
            });
        }

        // 5. Calculate Grouping Counts (Semua, Dalam Wilayah, Luar Wilayah)
        $countQuery = clone $query;
        $countSemua = $countQuery->count();

        if ($user->isAdminPuskesmas()) {
            $countDalam = (clone $countQuery)->whereIn('desa_id', $puskesmasDesaIds)->count();
            $countLuar = (clone $countQuery)->whereNotIn('desa_id', $puskesmasDesaIds)->count();
        } elseif ($user->isBidan() || $user->isKader()) {
            $countDalam = (clone $countQuery)->where('desa_id', $user->desa_id)->count();
            $countLuar = (clone $countQuery)->where('desa_id', '!=', $user->desa_id)->count();
        } else {
            $countDalam = $countSemua;
            $countLuar = 0;
        }

        // 6. Apply Territory Grouping Filter (kelompok / scope)
        $kelompok = $request->input('kelompok');
        if (! $kelompok && $request->input('scope') === 'lintas') {
            $kelompok = 'luar';
        } elseif (! $kelompok && $request->input('scope') === 'wilayah') {
            $kelompok = 'dalam';
        } elseif (! $kelompok && $request->input('scope') === 'semua') {
            $kelompok = 'semua';
        }

        if ($user->isAdminPuskesmas()) {
            $currentKelompok = $kelompok ?: 'semua';
        } elseif ($user->isBidan() || $user->isKader()) {
            $currentKelompok = $kelompok ?: ($request->filled('search') ? 'semua' : 'dalam');
        } else {
            $currentKelompok = $kelompok ?: 'semua';
        }

        if ($currentKelompok === 'dalam') {
            if ($user->isAdminPuskesmas()) {
                $query->whereIn('desa_id', $puskesmasDesaIds);
            } elseif ($user->isBidan() || $user->isKader()) {
                $query->where('desa_id', $user->desa_id);
            }
        } elseif ($currentKelompok === 'luar') {
            if ($user->isAdminPuskesmas()) {
                $query->whereNotIn('desa_id', $puskesmasDesaIds);
            } elseif ($user->isBidan() || $user->isKader()) {
                $query->where('desa_id', '!=', $user->desa_id);
            }
        }

        $ibuHamils = $query->latest()->paginate(15)->withQueryString();
        $desas = $allDesas;

        return view('ibu-hamil.index', compact(
            'ibuHamils',
            'desas',
            'puskesmasDesas',
            'otherDesas',
            'puskesmasDesaIds',
            'countSemua',
            'countDalam',
            'countLuar',
            'currentKelompok'
        ));
    }

    /**
     * Show the form for creating a new ibu hamil.
     */
    public function create(): View
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $desas = Desa::orderBy('nama')->get();
        } elseif ($user->isBidan()) {
            $desaIds = Desa::where('kecamatan', $user->desa?->kecamatan)
                ->orWhere('puskesmas', $user->desa?->puskesmas)
                ->orWhere('id', $user->desa_id)
                ->pluck('id')
                ->all();
            if (empty($desaIds)) {
                $desaIds = [$user->desa_id];
            }
            $desas = Desa::whereIn('id', $desaIds)->orderBy('nama')->get();
        } else {
            $desas = Desa::where('id', $user->desa_id)->get();
        }

        return view('ibu-hamil.create', compact('desas'));
    }

    /**
     * Store a newly created ibu hamil.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $this->normalizeDates($request, ['tanggal_lahir', 'hpht', 'taksiran_persalinan']);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'digits:16', 'unique:ibu_hamils,nik'],
            'desa_id' => [$user->isKader() ? 'nullable' : 'required', 'exists:desas,id'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nama_suami' => ['nullable', 'string', 'max:255'],
            'golongan_darah' => ['nullable', 'string', 'in:A,B,AB,O'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'berat_badan' => ['nullable', 'numeric', 'min:30', 'max:200'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'tekanan_darah_sistol' => ['nullable', 'integer', 'min:50', 'max:300'],
            'tekanan_darah_diastol' => ['nullable', 'integer', 'min:30', 'max:200'],
            'lila' => ['nullable', 'numeric', 'min:10', 'max:60'],
            'tfu' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'djj' => ['nullable', 'integer', 'min:50', 'max:220'],
            'hemoglobin' => ['nullable', 'numeric', 'min:3', 'max:25'],
            'status_imunisasi_tt' => ['nullable', 'string', 'max:50'],
            'refleks_patela' => ['nullable', 'string', 'in:positif,negatif'],
            'edema' => ['nullable', 'string', 'in:tidak_ada,tungkai,seluruh_tubuh'],
            'status_gizi' => ['nullable', 'string', 'in:baik,kurang,buruk,lebih,obesitas'],
            'kondisi_umum' => ['nullable', 'string', 'in:baik,sedang,lemah'],
            'hpht' => ['nullable', 'date'],
            'taksiran_persalinan' => ['nullable', 'date'],
            'gravida' => ['nullable', 'integer', 'min:0'],
            'para' => ['nullable', 'integer', 'min:0'],
            'abortus' => ['nullable', 'integer', 'min:0'],
            'jumlah_anak_hidup' => ['nullable', 'integer', 'min:0'],
        ], [
            'nik.required' => 'Nomor Induk Kependudukan (NIK 16 Digit) wajib dimasukkan saat mendaftarkan ibu hamil.',
            'nik.digits' => 'NIK harus tepat berjumlah 16 digit angka kependudukan.',
            'nik.unique' => 'NIK ini sudah terdaftar untuk data ibu hamil lain.',
        ]);

        if ($user->isKader()) {
            $validated['desa_id'] = $user->desa_id;
        } elseif ($user->isBidan()) {
            $allowedDesaIds = Desa::where('kecamatan', $user->desa?->kecamatan)
                ->orWhere('puskesmas', $user->desa?->puskesmas)
                ->orWhere('id', $user->desa_id)
                ->pluck('id')
                ->all();
            if ($request->filled('desa_id') && in_array((int) $request->input('desa_id'), $allowedDesaIds)) {
                $validated['desa_id'] = (int) $request->input('desa_id');
            } else {
                $validated['desa_id'] = $user->desa_id;
            }
        }

        // Auto-calculate IMT (Indeks Massa Tubuh) and status_gizi based on BB & TB
        if (! empty($validated['berat_badan']) && ! empty($validated['tinggi_badan']) && $validated['tinggi_badan'] > 0) {
            $tbMeter = $validated['tinggi_badan'] / 100;
            $imt = round($validated['berat_badan'] / ($tbMeter * $tbMeter), 1);
            $validated['imt'] = $imt;

            if ($imt < 18.5) {
                $validated['status_gizi'] = 'kurang';
            } elseif ($imt <= 24.9) {
                $validated['status_gizi'] = 'baik';
            } elseif ($imt <= 29.9) {
                $validated['status_gizi'] = 'lebih';
            } else {
                $validated['status_gizi'] = 'obesitas';
            }
        }

        // Auto-calculate taksiran persalinan (Naegele's rule) if HPHT provided and TP empty
        if (! empty($validated['hpht']) && empty($validated['taksiran_persalinan'])) {
            $validated['taksiran_persalinan'] = date('Y-m-d', strtotime($validated['hpht'].' + 280 days'));
        }

        $validated['user_id'] = $user->id;
        $validated['is_active'] = true;

        $ibuHamil = IbuHamil::create($validated);

        return redirect()->route('ibu-hamil.show', $ibuHamil)
            ->with('success', 'Data Ibu Hamil berhasil disimpan. Silakan catat kunjungan ANC / Jemput Bola atau lakukan skrining.');
    }

    /**
     * Display the specified ibu hamil.
     */
    public function show(IbuHamil $ibuHamil): View
    {
        $ibuHamil->load([
            'desa',
            'user.desa',
            'skrinings.user.desa',
            'skrinings.details.pertanyaanSpr',
            'kunjungans.user.desa',
            'kunjungans.desa',
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

        $canEdit = $user->isAdmin()
            || $user->desa_id === $ibuHamil->desa_id
            || $user->id === $ibuHamil->user_id;

        if (! $canEdit) {
            abort(403);
        }

        if ($user->isAdmin()) {
            $desas = Desa::orderBy('nama')->get();
        } elseif ($user->isBidan()) {
            $desaIds = Desa::where('kecamatan', $user->desa?->kecamatan)
                ->orWhere('puskesmas', $user->desa?->puskesmas)
                ->orWhere('id', $user->desa_id)
                ->pluck('id')
                ->all();
            if (empty($desaIds)) {
                $desaIds = [$user->desa_id];
            }
            $desas = Desa::whereIn('id', $desaIds)->orderBy('nama')->get();
        } else {
            $desas = Desa::where('id', $user->desa_id)->get();
        }

        return view('ibu-hamil.edit', compact('ibuHamil', 'desas'));
    }

    /**
     * Update the specified ibu hamil.
     */
    public function update(Request $request, IbuHamil $ibuHamil): RedirectResponse
    {
        $user = Auth::user();

        $canEdit = $user->isAdmin()
            || $user->desa_id === $ibuHamil->desa_id
            || $user->id === $ibuHamil->user_id;

        if (! $canEdit) {
            abort(403);
        }

        $this->normalizeDates($request, ['tanggal_lahir', 'hpht', 'taksiran_persalinan']);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:ibu_hamils,nik,'.$ibuHamil->id],
            'desa_id' => [$user->isKader() ? 'nullable' : 'required', 'exists:desas,id'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nama_suami' => ['nullable', 'string', 'max:255'],
            'golongan_darah' => ['nullable', 'string', 'in:A,B,AB,O'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'berat_badan' => ['nullable', 'numeric', 'min:30', 'max:200'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'tekanan_darah_sistol' => ['nullable', 'integer', 'min:50', 'max:300'],
            'tekanan_darah_diastol' => ['nullable', 'integer', 'min:30', 'max:200'],
            'lila' => ['nullable', 'numeric', 'min:10', 'max:60'],
            'tfu' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'djj' => ['nullable', 'integer', 'min:50', 'max:220'],
            'hemoglobin' => ['nullable', 'numeric', 'min:3', 'max:25'],
            'status_imunisasi_tt' => ['nullable', 'string', 'max:50'],
            'refleks_patela' => ['nullable', 'string', 'in:positif,negatif'],
            'edema' => ['nullable', 'string', 'in:tidak_ada,tungkai,seluruh_tubuh'],
            'status_gizi' => ['nullable', 'string', 'in:baik,kurang,buruk,lebih,obesitas'],
            'kondisi_umum' => ['nullable', 'string', 'in:baik,sedang,lemah'],
            'hpht' => ['nullable', 'date'],
            'taksiran_persalinan' => ['nullable', 'date'],
            'gravida' => ['nullable', 'integer', 'min:0'],
            'para' => ['nullable', 'integer', 'min:0'],
            'abortus' => ['nullable', 'integer', 'min:0'],
            'jumlah_anak_hidup' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        if ($user->isKader()) {
            unset($validated['desa_id']);
        } elseif ($user->isBidan()) {
            $allowedDesaIds = Desa::where('kecamatan', $user->desa?->kecamatan)
                ->orWhere('puskesmas', $user->desa?->puskesmas)
                ->orWhere('id', $user->desa_id)
                ->pluck('id')
                ->all();
            if ($request->filled('desa_id') && in_array((int) $request->input('desa_id'), $allowedDesaIds)) {
                $validated['desa_id'] = (int) $request->input('desa_id');
            } else {
                unset($validated['desa_id']);
            }
        }

        // Auto-calculate IMT (Indeks Massa Tubuh) and status_gizi based on BB & TB
        if (! empty($validated['berat_badan']) && ! empty($validated['tinggi_badan']) && $validated['tinggi_badan'] > 0) {
            $tbMeter = $validated['tinggi_badan'] / 100;
            $imt = round($validated['berat_badan'] / ($tbMeter * $tbMeter), 1);
            $validated['imt'] = $imt;

            if ($imt < 18.5) {
                $validated['status_gizi'] = 'kurang';
            } elseif ($imt <= 24.9) {
                $validated['status_gizi'] = 'baik';
            } elseif ($imt <= 29.9) {
                $validated['status_gizi'] = 'lebih';
            } else {
                $validated['status_gizi'] = 'obesitas';
            }
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

        $canDelete = ! $user->isKader() && ($user->isAdmin()
            || $user->desa_id === $ibuHamil->desa_id
            || $user->id === $ibuHamil->user_id);

        if (! $canDelete) {
            abort(403);
        }

        $ibuHamil->delete();

        return redirect()->route('ibu-hamil.index')
            ->with('success', 'Data Ibu Hamil berhasil dihapus.');
    }
}
