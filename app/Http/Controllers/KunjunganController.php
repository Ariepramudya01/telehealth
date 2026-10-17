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
            } elseif ($request->status === 'belum_hadir') {
                $query->where('status_hadir', false);
            }
        }

        $kunjungans = $query->orderBy('tanggal_kunjungan', 'asc')->paginate(15)->withQueryString();
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

        $ibuHamilsData = $ibuHamils->map(function ($b) {
            return [
                'id' => $b->id,
                'nama' => $b->nama,
                'tinggi_badan' => $b->tinggi_badan,
                'berat_badan' => $b->berat_badan,
                'sistol' => $b->tekanan_darah_sistol,
                'diastol' => $b->tekanan_darah_diastol,
                'has_skrining' => $b->skrinings()->exists(),
                'latest_skrining_id' => $b->latestSkrining()?->id,
            ];
        });

        return view('kunjungan.create', compact('ibuHamils', 'ibuHamilsData', 'selectedIbuHamilId'));
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
            'tinggi_badan' => ['required', 'numeric', 'min:50', 'max:250'],
            'berat_badan' => ['required', 'numeric', 'min:20', 'max:300'],
            'tekanan_darah_sistol' => ['required', 'integer', 'min:50', 'max:300'],
            'tekanan_darah_diastol' => ['required', 'integer', 'min:30', 'max:200'],
            'status_hadir' => ['nullable', 'boolean'],
            'status_dilayani' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string'],
            'action' => ['nullable', 'string', 'in:save,save_and_screen'],
        ]);

        $ibuHamil = IbuHamil::findOrFail($validated['ibu_hamil_id']);

        if (! $user->isAdmin() && $ibuHamil->desa_id !== $user->desa_id) {
            abort(403);
        }

        $hasSkrining = $ibuHamil->skrinings()->exists();
        $latestSkrining = $ibuHamil->latestSkrining();

        $statusHadir = $request->boolean('status_hadir', true);
        // Status dilayani aktif KETIKA sudah mengisi skrining spr
        $statusDilayani = $hasSkrining && $statusHadir && $request->boolean('status_dilayani', true);

        $kunjungan = Kunjungan::create([
            'ibu_hamil_id' => $ibuHamil->id,
            'user_id' => $user->id,
            'desa_id' => $ibuHamil->desa_id,
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'tinggi_badan' => $validated['tinggi_badan'],
            'berat_badan' => $validated['berat_badan'],
            'tekanan_darah_sistol' => $validated['tekanan_darah_sistol'],
            'tekanan_darah_diastol' => $validated['tekanan_darah_diastol'],
            'skrining_id' => $latestSkrining?->id,
            'status_hadir' => $statusHadir,
            'status_dilayani' => $statusDilayani,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // Perbarui data fisik/biometrik terbaru ibu hamil & hitung ulang IMT
        $ibuHamil->tinggi_badan = $validated['tinggi_badan'];
        $ibuHamil->berat_badan = $validated['berat_badan'];
        $ibuHamil->tekanan_darah_sistol = $validated['tekanan_darah_sistol'];
        $ibuHamil->tekanan_darah_diastol = $validated['tekanan_darah_diastol'];
        $tbMeter = $validated['tinggi_badan'] / 100;
        $ibuHamil->imt = round($validated['berat_badan'] / ($tbMeter * $tbMeter), 1);
        $ibuHamil->status_gizi = $ibuHamil->determineStatusGizi();
        $ibuHamil->save();

        if ($request->input('action') === 'save_and_screen') {
            return redirect()->route('skrining.create', [
                'ibu_hamil_id' => $ibuHamil->id,
                'kunjungan_id' => $kunjungan->id,
            ])->with('success', "Kunjungan ANC {$kunjungan->jenis_kunjungan} berhasil dicatat! Silakan lengkapi Skrining Deteksi Dini SPR untuk mengaktifkan status Dilayani.");
        }

        $message = $statusDilayani
            ? 'Catatan kunjungan ANC berhasil disimpan dan status dilayani telah aktif.'
            : 'Catatan kunjungan ANC berhasil disimpan. Status dilayani akan aktif setelah pengisian Skrining SPR.';

        return redirect()->route('kunjungan.index')->with('success', $message);
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

        if ($user->isKader() || (! $user->isAdmin() && $kunjungan->desa_id !== $user->desa_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'tanggal_kunjungan' => ['required', 'date'],
            'jenis_kunjungan' => ['required', 'string', 'in:K1,K2,K3,K4,K5,K6'],
            'tinggi_badan' => ['required', 'numeric', 'min:50', 'max:250'],
            'berat_badan' => ['required', 'numeric', 'min:20', 'max:300'],
            'tekanan_darah_sistol' => ['required', 'integer', 'min:50', 'max:300'],
            'tekanan_darah_diastol' => ['required', 'integer', 'min:30', 'max:200'],
            'status_hadir' => ['nullable', 'boolean'],
            'status_dilayani' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string'],
        ]);

        $hasSkrining = $kunjungan->skrining_id !== null || $kunjungan->ibuHamil->skrinings()->exists();
        $statusHadir = $request->boolean('status_hadir');
        // Status dilayani harus memiliki skrining SPR
        $statusDilayani = $hasSkrining && $statusHadir && $request->boolean('status_dilayani');

        $kunjungan->update([
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'tinggi_badan' => $validated['tinggi_badan'],
            'berat_badan' => $validated['berat_badan'],
            'tekanan_darah_sistol' => $validated['tekanan_darah_sistol'],
            'tekanan_darah_diastol' => $validated['tekanan_darah_diastol'],
            'status_hadir' => $statusHadir,
            'status_dilayani' => $statusDilayani,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        $ibuHamil = $kunjungan->ibuHamil;
        $ibuHamil->tinggi_badan = $validated['tinggi_badan'];
        $ibuHamil->berat_badan = $validated['berat_badan'];
        $ibuHamil->tekanan_darah_sistol = $validated['tekanan_darah_sistol'];
        $ibuHamil->tekanan_darah_diastol = $validated['tekanan_darah_diastol'];
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

        if ($user->isKader() || (! $user->isAdmin() && $kunjungan->desa_id !== $user->desa_id)) {
            abort(403);
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

        if (! $user->isAdmin() && $kunjungan->desa_id !== $user->desa_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengonfirmasi kunjungan di wilayah desa ini.');
        }

        $validated = $request->validate([
            'status_hadir' => ['required', 'boolean'],
            'status_dilayani' => ['nullable', 'boolean'],
            'tanggal_kunjungan' => ['nullable', 'date'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:50', 'max:250'],
            'berat_badan' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'tekanan_darah_sistol' => ['nullable', 'integer', 'min:50', 'max:300'],
            'tekanan_darah_diastol' => ['nullable', 'integer', 'min:30', 'max:200'],
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

        if (array_key_exists('catatan', $validated) && $validated['catatan'] !== null) {
            $updateData['catatan'] = $validated['catatan'];
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

        if (! $user->isAdmin() && $ibuHamil->desa_id !== $user->desa_id) {
            abort(403);
        }

        $ibuHamil->generateStandardKunjungans($user->id);

        return back()->with('success', "Jadwal kunjungan ANC standar (K1 s/d K6) berhasil dibuat untuk {$ibuHamil->nama}.");
    }
}
