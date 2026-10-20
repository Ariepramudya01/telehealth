<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KunjunganController extends Controller
{
    /**
     * Display a listing of kunjungans.
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

        $query = Kunjungan::with(['ibuHamil.desa', 'desa', 'user.desa']);

        // 2. Base Jurisdiction Scoping
        if ($user->isAdminPuskesmas()) {
            $cleanPusk = $user->clean_puskesmas_keyword;
            $query->where(function ($q) use ($puskesmasDesaIds, $cleanPusk) {
                $q->whereIn('desa_id', $puskesmasDesaIds)
                    ->orWhereHas('ibuHamil', fn ($b) => $b->whereIn('desa_id', $puskesmasDesaIds))
                    ->orWhereHas('user', function ($u) use ($puskesmasDesaIds, $cleanPusk) {
                        $u->whereIn('desa_id', $puskesmasDesaIds);
                        if ($cleanPusk) {
                            $u->orWhere('instansi', 'like', "%{$cleanPusk}%");
                        }
                    });
            });
        } elseif ($user->isBidan() || $user->isKader()) {
            $query->where(function ($q) use ($user) {
                $q->where('desa_id', $user->desa_id)
                    ->orWhere('user_id', $user->id)
                    ->orWhereHas('ibuHamil', fn ($b) => $b->where('desa_id', $user->desa_id));
            });
        } elseif ($user->isDinkesKabupaten()) {
            $userKab = $user->kabupaten;
            $cleanKab = strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) $userKab)));
            if ($cleanKab) {
                $query->where(function ($q) use ($cleanKab) {
                    $q->whereHas('desa', fn ($d) => $d->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"]))
                        ->orWhereHas('ibuHamil.desa', fn ($d) => $d->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"]));
                });
            }
        }

        // 3. Search Filter (Nama Bumil, NIK, Suami, No HP, Desa/Puskesmas, Petugas, Catatan)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('ibuHamil', function ($b) use ($search) {
                    $b->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('nama_suami', 'like', "%{$search}%")
                        ->orWhere('no_hp', 'like', "%{$search}%");
                })->orWhereHas('desa', function ($d) use ($search) {
                    $d->where('nama', 'like', "%{$search}%")
                        ->orWhere('kecamatan', 'like', "%{$search}%")
                        ->orWhere('puskesmas', 'like', "%{$search}%");
                })->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                })->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        // 4. Other Standard Filters (desa_id, jenis, metode, status)
        if ($request->filled('desa_id')) {
            $query->where('desa_id', $request->desa_id);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_kunjungan', $request->jenis);
        }

        if ($request->filled('metode')) {
            $query->where('metode_kunjungan', $request->metode);
        }

        if ($request->filled('status')) {
            if ($request->status === 'hadir') {
                $query->where('status_hadir', true);
            } elseif ($request->status === 'dilayani') {
                $query->where('status_dilayani', true);
            } elseif ($request->status === 'belum_hadir') {
                $query->where('status_hadir', false);
            }
        }

        // 5. Calculate Grouping Counts (Semua, Dalam Wilayah, Luar Wilayah)
        $countQuery = clone $query;
        $countSemua = $countQuery->count();

        if ($user->isAdminPuskesmas()) {
            $countDalam = (clone $countQuery)->whereHas('ibuHamil', fn ($b) => $b->whereIn('desa_id', $puskesmasDesaIds))->count();
            $countLuar = (clone $countQuery)->whereHas('ibuHamil', fn ($b) => $b->whereNotIn('desa_id', $puskesmasDesaIds))->count();
        } elseif ($user->isBidan() || $user->isKader()) {
            $countDalam = (clone $countQuery)->whereHas('ibuHamil', fn ($b) => $b->where('desa_id', $user->desa_id))->count();
            $countLuar = (clone $countQuery)->whereHas('ibuHamil', fn ($b) => $b->where('desa_id', '!=', $user->desa_id))->count();
        } else {
            $countDalam = $countSemua;
            $countLuar = 0;
        }

        // 6. Apply Territory Grouping Filter (kelompok / scope)
        $kelompok = $request->input('kelompok');
        if (! $kelompok && $request->input('scope') === 'lintas') {
            $kelompok = 'luar';
        }
        $currentKelompok = $kelompok ?: 'semua';

        if ($currentKelompok === 'dalam') {
            if ($user->isAdminPuskesmas()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->whereIn('desa_id', $puskesmasDesaIds));
            } elseif ($user->isBidan() || $user->isKader()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->where('desa_id', $user->desa_id));
            }
        } elseif ($currentKelompok === 'luar') {
            if ($user->isAdminPuskesmas()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->whereNotIn('desa_id', $puskesmasDesaIds));
            } elseif ($user->isBidan() || $user->isKader()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->where('desa_id', '!=', $user->desa_id));
            }
        }

        $kunjungans = $query->orderBy('tanggal_kunjungan', 'desc')->paginate(15)->withQueryString();
        $desas = $allDesas;

        return view('kunjungan.index', compact(
            'kunjungans',
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
     * Show the form for creating a new kunjungan.
     */
    public function create(Request $request): View
    {
        $user = Auth::user();

        $allIbuHamils = IbuHamil::with('desa')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $ibuHamilsSendiri = $user->isAdmin()
            ? $allIbuHamils
            : $allIbuHamils->where('desa_id', $user->desa_id)->values();

        $ibuHamilsLintas = $user->isAdmin()
            ? collect()
            : $allIbuHamils->where('desa_id', '!=', $user->desa_id)->values();

        $selectedIbuHamilId = $request->query('ibu_hamil_id');

        $ibuHamilsData = $allIbuHamils->map(function ($b) use ($user) {
            return [
                'id' => $b->id,
                'nama' => $b->nama,
                'nik' => $b->nik,
                'desa_nama' => $b->desa?->nama,
                'puskesmas' => $b->desa?->puskesmas ?? 'Puskesmas',
                'is_lintas_daerah' => (! $user->isAdmin() && $b->desa_id !== $user->desa_id),
                'tinggi_badan' => $b->tinggi_badan,
                'berat_badan' => $b->berat_badan,
                'sistol' => $b->tekanan_darah_sistol,
                'diastol' => $b->tekanan_darah_diastol,
                'lila' => $b->lila,
                'hb' => $b->hemoglobin,
                'has_skrining' => $b->skrinings()->exists(),
                'latest_skrining_id' => $b->latestSkrining()?->id,
            ];
        });

        return view('kunjungan.create', compact('ibuHamilsSendiri', 'ibuHamilsLintas', 'ibuHamilsData', 'selectedIbuHamilId'));
    }

    /**
     * Store a newly created kunjungan.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $this->normalizeDates($request, ['tanggal_kunjungan', 'jadwal_kunjungan_berikutnya']);

        if ($request->filled('tahap_anc') && ! $request->filled('jenis_kunjungan')) {
            $request->merge(['jenis_kunjungan' => $request->input('tahap_anc')]);
        }

        $validated = $request->validate([
            'ibu_hamil_id' => ['required', 'exists:ibu_hamils,id'],
            'tanggal_kunjungan' => ['required', 'date'],
            'jenis_kunjungan' => ['required', 'string', 'in:K1,K2,K3,K4,K5,K6'],
            'tahap_anc' => ['nullable', 'string', 'in:K1,K2,K3,K4,K5,K6'],
            'metode_kunjungan' => ['nullable', 'string', 'in:jemput_bola,posyandu,puskesmas'],
            'tinggi_badan' => ['required', 'numeric', 'min:50', 'max:250'],
            'berat_badan' => ['required', 'numeric', 'min:20', 'max:300'],
            'tekanan_darah_sistol' => ['required', 'integer', 'min:50', 'max:300'],
            'tekanan_darah_diastol' => ['required', 'integer', 'min:30', 'max:200'],
            'lila' => ['nullable', 'numeric', 'min:10', 'max:50'],
            'tfu' => ['nullable', 'numeric', 'min:5', 'max:50'],
            'djj' => ['nullable', 'integer', 'min:50', 'max:220'],
            'hb' => ['nullable', 'numeric', 'min:3', 'max:25'],
            'kepatuhan_fe' => ['nullable', 'boolean'],
            'bentuk_pendampingan' => ['nullable', 'string'],
            'foto_pendampingan' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'jadwal_kunjungan_berikutnya' => ['nullable', 'date'],
            'status_hadir' => ['nullable', 'boolean'],
            'status_dilayani' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string'],
            'action' => ['nullable', 'string', 'in:save,save_and_screen'],
        ]);

        $ibuHamil = IbuHamil::findOrFail($validated['ibu_hamil_id']);

        // Bidan/kader faskes lain dapat memeriksa ibu hamil di titik kritis / jemput bola lintas daerah
        $desaPelayananId = $user->desa_id ?? $ibuHamil->desa_id;
        $isLintasDaerah = (! $user->isAdmin() && $ibuHamil->desa_id !== $user->desa_id);

        $hasSkrining = $ibuHamil->skrinings()->exists();
        $latestSkrining = $ibuHamil->latestSkrining();

        $statusHadir = $request->boolean('status_hadir', true);
        // Status dilayani aktif KETIKA sudah mengisi skrining spr
        $statusDilayani = $hasSkrining && $statusHadir && $request->boolean('status_dilayani', true);

        $fotoPath = null;
        if ($request->hasFile('foto_pendampingan')) {
            $fotoPath = $request->file('foto_pendampingan')->store('kunjungan-dokumentasi', 'public');
        }

        $kunjungan = Kunjungan::create([
            'ibu_hamil_id' => $ibuHamil->id,
            'user_id' => $user->id,
            'desa_id' => $desaPelayananId,
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'tahap_anc' => $validated['tahap_anc'] ?? $validated['jenis_kunjungan'],
            'metode_kunjungan' => $validated['metode_kunjungan'] ?? 'posyandu',
            'tinggi_badan' => $validated['tinggi_badan'],
            'berat_badan' => $validated['berat_badan'],
            'tekanan_darah_sistol' => $validated['tekanan_darah_sistol'],
            'tekanan_darah_diastol' => $validated['tekanan_darah_diastol'],
            'lila' => $validated['lila'] ?? null,
            'tfu' => $validated['tfu'] ?? null,
            'djj' => $validated['djj'] ?? null,
            'hb' => $validated['hb'] ?? null,
            'kepatuhan_fe' => $request->boolean('kepatuhan_fe'),
            'skrining_id' => $latestSkrining?->id,
            'status_hadir' => $statusHadir,
            'status_dilayani' => $statusDilayani,
            'catatan' => $validated['catatan'] ?? null,
            'bentuk_pendampingan' => $validated['bentuk_pendampingan'] ?? null,
            'foto_pendampingan' => $fotoPath,
            'jadwal_kunjungan_berikutnya' => $validated['jadwal_kunjungan_berikutnya'] ?? null,
        ]);

        // Perbarui data fisik/biometrik terbaru ibu hamil & hitung ulang IMT
        $ibuHamil->tinggi_badan = $validated['tinggi_badan'];
        $ibuHamil->berat_badan = $validated['berat_badan'];
        $ibuHamil->tekanan_darah_sistol = $validated['tekanan_darah_sistol'];
        $ibuHamil->tekanan_darah_diastol = $validated['tekanan_darah_diastol'];
        if (! empty($validated['lila'])) {
            $ibuHamil->lila = $validated['lila'];
        }
        if (! empty($validated['hb'])) {
            $ibuHamil->hemoglobin = $validated['hb'];
        }
        $tbMeter = $validated['tinggi_badan'] / 100;
        $ibuHamil->imt = round($validated['berat_badan'] / ($tbMeter * $tbMeter), 1);
        $ibuHamil->status_gizi = $ibuHamil->determineStatusGizi();
        $ibuHamil->save();

        $lintasNote = $isLintasDaerah ? " (Pelayanan Lintas Daerah untuk pasien asal {$ibuHamil->desa?->nama})" : '';

        if ($request->input('action') === 'save_and_screen') {
            return redirect()->route('skrining.create', [
                'ibu_hamil_id' => $ibuHamil->id,
                'kunjungan_id' => $kunjungan->id,
                'tanggal' => $kunjungan->tanggal_kunjungan->format('Y-m-d'),
            ])->with('success', "Kunjungan ANC {$kunjungan->jenis_kunjungan} berhasil dicatat{$lintasNote}! Tanggal dan pemeriksaan fisik otomatis tersinkronisasi. Silakan lengkapi Skrining SPR.");
        }

        $message = $statusDilayani
            ? "Catatan kunjungan ANC berhasil disimpan{$lintasNote} dan status dilayani telah aktif."
            : "Catatan kunjungan ANC berhasil disimpan{$lintasNote}. Status dilayani akan aktif setelah pengisian Skrining SPR.";

        return redirect()->route('kunjungan.index')->with('success', $message);
    }

    /**
     * Show the form for editing the specified kunjungan.
     */
    public function edit(Kunjungan $kunjungan): View
    {
        $user = Auth::user();

        $canAccess = $user->isAdmin()
            || $kunjungan->desa_id === $user->desa_id
            || $kunjungan->user_id === $user->id
            || $kunjungan->ibuHamil->desa_id === $user->desa_id;

        if (! $canAccess) {
            abort(403);
        }

        $kunjungan->load('ibuHamil');
        $hasSkrining = $kunjungan->skrining_id !== null || $kunjungan->ibuHamil->skrinings()->exists();

        return view('kunjungan.edit', compact('kunjungan', 'hasSkrining'));
    }

    /**
     * Update the specified kunjungan.
     */
    public function update(Request $request, Kunjungan $kunjungan): RedirectResponse
    {
        $user = Auth::user();

        $canAccess = $user->isAdmin()
            || $kunjungan->desa_id === $user->desa_id
            || $kunjungan->user_id === $user->id
            || $kunjungan->ibuHamil->desa_id === $user->desa_id;

        if (! $canAccess) {
            abort(403);
        }

        $this->normalizeDates($request, ['tanggal_kunjungan', 'jadwal_kunjungan_berikutnya']);

        $validated = $request->validate([
            'tanggal_kunjungan' => ['required', 'date'],
            'jenis_kunjungan' => ['required', 'string', 'in:K1,K2,K3,K4,K5,K6'],
            'tahap_anc' => ['nullable', 'string', 'in:K1,K2,K3,K4,K5,K6'],
            'metode_kunjungan' => ['nullable', 'string', 'in:jemput_bola,posyandu,puskesmas'],
            'tinggi_badan' => ['required', 'numeric', 'min:50', 'max:250'],
            'berat_badan' => ['required', 'numeric', 'min:20', 'max:300'],
            'tekanan_darah_sistol' => ['required', 'integer', 'min:50', 'max:300'],
            'tekanan_darah_diastol' => ['required', 'integer', 'min:30', 'max:200'],
            'lila' => ['nullable', 'numeric', 'min:10', 'max:50'],
            'tfu' => ['nullable', 'numeric', 'min:5', 'max:50'],
            'djj' => ['nullable', 'integer', 'min:50', 'max:220'],
            'hb' => ['nullable', 'numeric', 'min:3', 'max:25'],
            'kepatuhan_fe' => ['nullable', 'boolean'],
            'bentuk_pendampingan' => ['nullable', 'string'],
            'foto_pendampingan' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'jadwal_kunjungan_berikutnya' => ['nullable', 'date'],
            'status_hadir' => ['nullable', 'boolean'],
            'status_dilayani' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string'],
        ]);

        $hasSkrining = $kunjungan->skrining_id !== null || $kunjungan->ibuHamil->skrinings()->exists();
        $statusHadir = $request->boolean('status_hadir');
        // Status dilayani harus memiliki skrining SPR
        $statusDilayani = $hasSkrining && $statusHadir && $request->boolean('status_dilayani');

        $updateData = [
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'tahap_anc' => $validated['tahap_anc'] ?? $validated['jenis_kunjungan'],
            'metode_kunjungan' => $validated['metode_kunjungan'] ?? ($kunjungan->metode_kunjungan ?? 'posyandu'),
            'tinggi_badan' => $validated['tinggi_badan'],
            'berat_badan' => $validated['berat_badan'],
            'tekanan_darah_sistol' => $validated['tekanan_darah_sistol'],
            'tekanan_darah_diastol' => $validated['tekanan_darah_diastol'],
            'lila' => $validated['lila'] ?? null,
            'tfu' => $validated['tfu'] ?? null,
            'djj' => $validated['djj'] ?? null,
            'hb' => $validated['hb'] ?? null,
            'kepatuhan_fe' => $request->boolean('kepatuhan_fe'),
            'status_hadir' => $statusHadir,
            'status_dilayani' => $statusDilayani,
            'catatan' => $validated['catatan'] ?? null,
            'bentuk_pendampingan' => $validated['bentuk_pendampingan'] ?? null,
            'jadwal_kunjungan_berikutnya' => $validated['jadwal_kunjungan_berikutnya'] ?? null,
        ];

        if ($request->hasFile('foto_pendampingan')) {
            if ($kunjungan->foto_pendampingan) {
                Storage::disk('public')->delete($kunjungan->foto_pendampingan);
            }
            $updateData['foto_pendampingan'] = $request->file('foto_pendampingan')->store('kunjungan-dokumentasi', 'public');
        }

        $kunjungan->update($updateData);

        $ibuHamil = $kunjungan->ibuHamil;
        $ibuHamil->tinggi_badan = $validated['tinggi_badan'];
        $ibuHamil->berat_badan = $validated['berat_badan'];
        $ibuHamil->tekanan_darah_sistol = $validated['tekanan_darah_sistol'];
        $ibuHamil->tekanan_darah_diastol = $validated['tekanan_darah_diastol'];
        if (! empty($validated['lila'])) {
            $ibuHamil->lila = $validated['lila'];
        }
        if (! empty($validated['hb'])) {
            $ibuHamil->hemoglobin = $validated['hb'];
        }
        $tbMeter = $validated['tinggi_badan'] / 100;
        $ibuHamil->imt = round($validated['berat_badan'] / ($tbMeter * $tbMeter), 1);
        $ibuHamil->status_gizi = $ibuHamil->determineStatusGizi();
        $ibuHamil->save();

        return redirect()->route('kunjungan.index')
            ->with('success', 'Catatan kunjungan ANC berhasil diperbarui.');
    }

    /**
     * Remove the specified kunjungan.
     */
    public function destroy(Kunjungan $kunjungan): RedirectResponse
    {
        $user = Auth::user();

        $canDelete = ! $user->isKader() && ($user->isAdmin()
            || $kunjungan->desa_id === $user->desa_id
            || $kunjungan->user_id === $user->id
            || $kunjungan->ibuHamil->desa_id === $user->desa_id);

        if (! $canDelete) {
            abort(403);
        }

        if ($kunjungan->foto_pendampingan) {
            Storage::disk('public')->delete($kunjungan->foto_pendampingan);
        }

        $kunjungan->delete();

        return redirect()->route('kunjungan.index')
            ->with('success', 'Catatan kunjungan berhasil dihapus.');
    }

    /**
     * Quick attendance & service confirmation for a kunjungan.
     */
    public function konfirmasiHadir(Request $request, Kunjungan $kunjungan): RedirectResponse
    {
        $user = Auth::user();

        $canConfirm = $user->isAdmin()
            || $kunjungan->desa_id === $user->desa_id
            || $kunjungan->user_id === $user->id
            || $kunjungan->ibuHamil->desa_id === $user->desa_id;

        if (! $canConfirm) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengonfirmasi kunjungan ini.');
        }

        $this->normalizeDates($request, ['tanggal_kunjungan', 'jadwal_kunjungan_berikutnya']);

        $validated = $request->validate([
            'status_hadir' => ['required', 'boolean'],
            'status_dilayani' => ['nullable', 'boolean'],
            'tanggal_kunjungan' => ['nullable', 'date'],
            'metode_kunjungan' => ['nullable', 'string', 'in:jemput_bola,posyandu,puskesmas'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:50', 'max:250'],
            'berat_badan' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'tekanan_darah_sistol' => ['nullable', 'integer', 'min:50', 'max:300'],
            'tekanan_darah_diastol' => ['nullable', 'integer', 'min:30', 'max:200'],
            'lila' => ['nullable', 'numeric', 'min:10', 'max:50'],
            'tfu' => ['nullable', 'numeric', 'min:5', 'max:50'],
            'djj' => ['nullable', 'integer', 'min:50', 'max:220'],
            'hb' => ['nullable', 'numeric', 'min:3', 'max:25'],
            'kepatuhan_fe' => ['nullable', 'boolean'],
            'bentuk_pendampingan' => ['nullable', 'string'],
            'jadwal_kunjungan_berikutnya' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $hasSkrining = $kunjungan->skrining_id !== null || $kunjungan->ibuHamil->skrinings()->exists();

        $statusHadir = (bool) $validated['status_hadir'];
        // Status dilayani hanya aktif jika hadir dan sudah memiliki skrining SPR
        $statusDilayani = $statusHadir && $hasSkrining && ! empty($validated['status_dilayani']);

        $updateData = [
            'status_hadir' => $statusHadir,
            'status_dilayani' => $statusDilayani,
        ];

        if (! empty($validated['tanggal_kunjungan'])) {
            $updateData['tanggal_kunjungan'] = $validated['tanggal_kunjungan'];
        }

        if (! empty($validated['metode_kunjungan'])) {
            $updateData['metode_kunjungan'] = $validated['metode_kunjungan'];
        }

        if (! empty($validated['tinggi_badan'])) {
            $updateData['tinggi_badan'] = $validated['tinggi_badan'];
        }
        if (! empty($validated['berat_badan'])) {
            $updateData['berat_badan'] = $validated['berat_badan'];
        }
        if (! empty($validated['tekanan_darah_sistol'])) {
            $updateData['tekanan_darah_sistol'] = $validated['tekanan_darah_sistol'];
        }
        if (! empty($validated['tekanan_darah_diastol'])) {
            $updateData['tekanan_darah_diastol'] = $validated['tekanan_darah_diastol'];
        }
        if (! empty($validated['lila'])) {
            $updateData['lila'] = $validated['lila'];
        }
        if (! empty($validated['tfu'])) {
            $updateData['tfu'] = $validated['tfu'];
        }
        if (! empty($validated['djj'])) {
            $updateData['djj'] = $validated['djj'];
        }
        if (! empty($validated['hb'])) {
            $updateData['hb'] = $validated['hb'];
        }
        $updateData['kepatuhan_fe'] = $request->boolean('kepatuhan_fe');
        if (! empty($validated['bentuk_pendampingan'])) {
            $updateData['bentuk_pendampingan'] = $validated['bentuk_pendampingan'];
        }
        if (! empty($validated['jadwal_kunjungan_berikutnya'])) {
            $updateData['jadwal_kunjungan_berikutnya'] = $validated['jadwal_kunjungan_berikutnya'];
        }

        if (array_key_exists('catatan', $validated) && $validated['catatan'] !== null) {
            $updateData['catatan'] = $validated['catatan'];
        }

        if ($request->hasFile('foto_pendampingan')) {
            if ($kunjungan->foto_pendampingan) {
                Storage::disk('public')->delete($kunjungan->foto_pendampingan);
            }
            $updateData['foto_pendampingan'] = $request->file('foto_pendampingan')->store('kunjungan-dokumentasi', 'public');
        }

        $kunjungan->update($updateData);

        // Update IbuHamil vitals if provided
        $ibuHamil = $kunjungan->ibuHamil;
        $updatedBumil = false;
        if (! empty($validated['tinggi_badan'])) {
            $ibuHamil->tinggi_badan = $validated['tinggi_badan'];
            $updatedBumil = true;
        }
        if (! empty($validated['berat_badan'])) {
            $ibuHamil->berat_badan = $validated['berat_badan'];
            $updatedBumil = true;
        }
        if (! empty($validated['tekanan_darah_sistol'])) {
            $ibuHamil->tekanan_darah_sistol = $validated['tekanan_darah_sistol'];
            $updatedBumil = true;
        }
        if (! empty($validated['tekanan_darah_diastol'])) {
            $ibuHamil->tekanan_darah_diastol = $validated['tekanan_darah_diastol'];
            $updatedBumil = true;
        }
        if (! empty($validated['lila'])) {
            $ibuHamil->lila = $validated['lila'];
            $updatedBumil = true;
        }
        if (! empty($validated['hb'])) {
            $ibuHamil->hemoglobin = $validated['hb'];
            $updatedBumil = true;
        }

        if ($updatedBumil) {
            if ($ibuHamil->tinggi_badan && $ibuHamil->berat_badan) {
                $tbMeter = $ibuHamil->tinggi_badan / 100;
                $ibuHamil->imt = round($ibuHamil->berat_badan / ($tbMeter * $tbMeter), 1);
                $ibuHamil->status_gizi = $ibuHamil->determineStatusGizi();
            }
            $ibuHamil->save();
        }

        $statusLabel = $statusHadir
            ? ($statusDilayani ? 'Hadir & Telah Dilayani' : ($hasSkrining ? 'Hadir' : 'Hadir (Belum Skrining SPR)'))
            : 'Belum Hadir';

        return back()->with('success', "Konfirmasi kunjungan ANC {$kunjungan->jenis_kunjungan} untuk {$ibuHamil->nama} berhasil disimpan ({$statusLabel}).");
    }

    /**
     * Generate standard K1-K6 schedule for an ibu hamil.
     */
    public function generateJadwal(IbuHamil $ibuHamil): RedirectResponse
    {
        $user = Auth::user();

        $canGenerate = $user->isAdmin()
            || $ibuHamil->desa_id === $user->desa_id
            || Kunjungan::where('ibu_hamil_id', $ibuHamil->id)
                ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('desa_id', $user->desa_id))
                ->exists();

        if (! $canGenerate) {
            abort(403);
        }

        $ibuHamil->generateStandardKunjungans($user->id);

        return back()->with('success', "Jadwal kunjungan ANC standar (K1 s/d K6) berhasil dibuat untuk {$ibuHamil->nama}.");
    }
}
