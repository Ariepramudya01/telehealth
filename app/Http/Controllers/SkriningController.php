<?php

namespace App\Http\Controllers;

use App\Enums\KategoriRisiko;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\PertanyaanSpr;
use App\Models\Skrining;
use App\Models\SkriningDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SkriningController extends Controller
{
    /**
     * Display a listing of screenings.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Skrining::with(['ibuHamil.desa', 'user.desa']);

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('ibuHamil', fn ($b) => $b->where('desa_id', $user->desa_id));
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_risiko', $request->kategori);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('ibuHamil', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $skrinings = $query->latest('tanggal_skrining')->paginate(15)->withQueryString();

        return view('skrining.index', compact('skrinings'));
    }

    /**
     * Show the form for creating a new screening.
     */
    public function create(Request $request): View
    {
        $user = Auth::user();

        $allIbuHamils = IbuHamil::with([
            'desa',
            'kunjungans' => fn ($q) => $q->latest('tanggal_kunjungan'),
        ])
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
        $selectedKunjunganId = $request->query('kunjungan_id');
        $selectedKunjungan = null;
        $syncedTanggal = $request->query('tanggal');

        if ($selectedKunjunganId) {
            $selectedKunjungan = Kunjungan::find($selectedKunjunganId);
            if ($selectedKunjungan) {
                if (! $selectedIbuHamilId) {
                    $selectedIbuHamilId = $selectedKunjungan->ibu_hamil_id;
                }
                $syncedTanggal = $selectedKunjungan->tanggal_kunjungan?->format('Y-m-d');
            }
        } elseif ($selectedIbuHamilId) {
            $targetBumil = $allIbuHamils->firstWhere('id', (int) $selectedIbuHamilId);
            $latestK = $targetBumil?->kunjungans->first();
            if ($latestK) {
                $selectedKunjungan = $latestK;
                $selectedKunjunganId = $latestK->id;
                $syncedTanggal = $latestK->tanggal_kunjungan?->format('Y-m-d');
            }
        }

        $ibuHamilsData = $allIbuHamils->keyBy('id')->map(function ($b) use ($user) {
            $latestK = $b->kunjungans->first();
            $effectiveHb = $latestK?->hb ?? $b->hemoglobin;
            $isAnemia = ($effectiveHb !== null && (float) $effectiveHb < 11.0);
            $effectiveSistol = $latestK?->tekanan_darah_sistol ?? $b->tekanan_darah_sistol;
            $effectiveDiastol = $latestK?->tekanan_darah_diastol ?? $b->tekanan_darah_diastol;
            $effectiveTb = $latestK?->tinggi_badan ?? $b->tinggi_badan;
            $effectiveBb = $latestK?->berat_badan ?? $b->berat_badan;
            $effectiveLila = $latestK?->lila ?? $b->lila;

            $effectiveImt = null;
            if ($effectiveTb && $effectiveBb && $effectiveTb > 0) {
                $tbMeter = $effectiveTb / 100;
                $effectiveImt = round($effectiveBb / ($tbMeter * $tbMeter), 1);
            } else {
                $effectiveImt = $b->calculated_imt;
            }

            $isObesitas = ($effectiveImt !== null && (float) $effectiveImt >= 27.0) || $b->status_gizi === 'obesitas';
            $isHipertensi = ($effectiveSistol !== null && (int) $effectiveSistol >= 140) || ($effectiveDiastol !== null && (int) $effectiveDiastol >= 90);
            $isHipertensiBerat = ($effectiveSistol !== null && (int) $effectiveSistol >= 160) || ($effectiveDiastol !== null && (int) $effectiveDiastol >= 110);
            $isPendek = ($effectiveTb !== null && (float) $effectiveTb > 0 && (float) $effectiveTb <= 145.0);
            $isKek = ($effectiveLila && (float) $effectiveLila < 23.5) || ($effectiveImt && (float) $effectiveImt < 18.5) || $b->is_kek;

            return [
                'id' => $b->id,
                'nama' => $b->nama,
                'usia' => $b->usia,
                'desa_nama' => $b->desa?->nama,
                'puskesmas' => $b->desa?->puskesmas ?? 'Puskesmas',
                'is_lintas_daerah' => (! $user->isAdmin() && $b->desa_id !== $user->desa_id),
                'tinggi_badan' => $effectiveTb,
                'berat_badan' => $effectiveBb,
                'imt' => $effectiveImt,
                'is_obesitas' => $isObesitas,
                'is_hipertensi' => $isHipertensi,
                'is_hipertensi_berat' => $isHipertensiBerat,
                'is_pendek' => $isPendek,
                'status_gizi' => $b->status_gizi,
                'kategori_imt' => $b->kategori_imt,
                'lila' => $effectiveLila,
                'is_kek' => $isKek,
                'hemoglobin' => $effectiveHb,
                'is_anemia' => $isAnemia,
                'sistol' => $effectiveSistol,
                'diastol' => $effectiveDiastol,
                'edema' => $b->edema,
                'gravida' => $b->gravida,
                'abortus' => (int) ($b->abortus ?? 0),
                'jumlah_anak_hidup' => (int) ($b->jumlah_anak_hidup ?? 0),
                'latest_kunjungan_id' => $latestK?->id,
                'latest_kunjungan_tanggal' => $latestK?->tanggal_kunjungan?->format('Y-m-d'),
                'latest_kunjungan_tahap' => $latestK?->tahap_anc ?? $latestK?->jenis_kunjungan,
            ];
        });

        $pertanyaans = PertanyaanSpr::orderBy('nomor')->get();
        $questionsData = $pertanyaans->map(fn ($p) => [
            'id' => $p->id,
            'nomor' => $p->nomor,
            'skor' => $p->skor,
        ]);
        $kelompokI = $pertanyaans->where('kelompok.value', 'I');
        $kelompokII = $pertanyaans->where('kelompok.value', 'II');
        $kelompokIII = $pertanyaans->where('kelompok.value', 'III');

        return view('skrining.create', compact(
            'allIbuHamils',
            'ibuHamilsSendiri',
            'ibuHamilsLintas',
            'ibuHamilsData',
            'questionsData',
            'selectedIbuHamilId',
            'selectedKunjunganId',
            'selectedKunjungan',
            'syncedTanggal',
            'kelompokI',
            'kelompokII',
            'kelompokIII'
        ));
    }

    /**
     * Store a newly created screening.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $this->normalizeDates($request, ['tanggal_skrining']);

        $validated = $request->validate([
            'ibu_hamil_id' => ['required', 'exists:ibu_hamils,id'],
            'kunjungan_id' => ['nullable', 'exists:kunjungans,id'],
            'tanggal_skrining' => ['required', 'date'],
            'usia_kehamilan_minggu' => ['nullable', 'integer', 'min:1', 'max:45'],
            'trimester' => ['nullable', 'integer', 'in:1,2,3'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string'],
        ]);

        $ibuHamil = IbuHamil::findOrFail($validated['ibu_hamil_id']);
        $isLintasDaerah = (! $user->isAdmin() && $ibuHamil->desa_id !== $user->desa_id);

        $answers = $request->input('answers', []);
        $allPertanyaans = PertanyaanSpr::all()->keyBy('id');

        // Check if there is an associated or latest ANC visit for latest biometrics
        $kunjungan = null;
        if (! empty($validated['kunjungan_id'])) {
            $kunjungan = Kunjungan::find($validated['kunjungan_id']);
        }
        if (! $kunjungan) {
            $kunjungan = $ibuHamil->kunjungans()->latest('tanggal_kunjungan')->first();
        }

        $effectiveTb = $ibuHamil->tinggi_badan;
        $effectiveBb = $kunjungan?->berat_badan ?? $ibuHamil->berat_badan;
        $effectiveLila = $kunjungan?->lila ?? $ibuHamil->lila;
        $effectiveHb = $kunjungan?->hb ?? $ibuHamil->hb;
        $effectiveSistol = $kunjungan?->tekanan_darah_sistol ?? $ibuHamil->tekanan_darah_sistol;
        $effectiveDiastol = $kunjungan?->tekanan_darah_diastol ?? $ibuHamil->tekanan_darah_diastol;
        $effectiveEdema = $kunjungan?->edema ?? $ibuHamil->edema;

        $effectiveImt = null;
        if ($effectiveTb && $effectiveTb > 0 && $effectiveBb && $effectiveBb > 0) {
            $effectiveImt = round($effectiveBb / (($effectiveTb / 100) ** 2), 1);
        }

        $isObesitas = ($effectiveImt !== null && $effectiveImt >= 27.0) || $ibuHamil->status_gizi === 'obesitas';
        $isHipertensi = ($effectiveSistol >= 140 || $effectiveDiastol >= 90);
        $isHipertensiBerat = ($effectiveSistol >= 160 || $effectiveDiastol >= 110);
        $isPendek = ($effectiveTb !== null && $effectiveTb > 0 && $effectiveTb <= 145);
        $isKek = ($effectiveLila !== null && $effectiveLila > 0 && $effectiveLila < 23.5) || ($effectiveImt !== null && $effectiveImt < 18.5) || $ibuHamil->status_gizi === 'kek';
        $isAnemia = ($effectiveHb !== null && $effectiveHb > 0 && $effectiveHb < 11.0) || $ibuHamil->is_anemia;

        // Automatic lock based on physical examination, ANC biometrics, and age
        foreach ($allPertanyaans as $pId => $pertanyaan) {
            if ($pertanyaan->nomor === 1) {
                // Terlalu muda hamil (≤ 16 tahun)
                if ($ibuHamil->usia !== null) {
                    $answers[$pId] = ($ibuHamil->usia <= 16) ? 1 : 0;
                }
            } elseif ($pertanyaan->nomor === 2) {
                // Terlalu tua hamil pertama kali (≥ 35 tahun)
                if ($ibuHamil->usia !== null) {
                    $answers[$pId] = ($ibuHamil->usia >= 35 && $ibuHamil->gravida == 1) ? 1 : 0;
                }
            } elseif ($pertanyaan->nomor === 7) {
                // Terlalu tua umur (≥ 35 tahun)
                if ($ibuHamil->usia !== null) {
                    $answers[$pId] = ($ibuHamil->usia >= 35 && $ibuHamil->gravida > 1) ? 1 : 0;
                }
            } elseif ($pertanyaan->nomor === 8) {
                // Terlalu pendek (≤ 145 cm)
                if ($isPendek) {
                    $answers[$pId] = 1;
                } elseif ($effectiveTb !== null && $effectiveTb > 0) {
                    $answers[$pId] = 0;
                }
            } elseif ($pertanyaan->nomor === 14) {
                // Penyakit pada ibu hamil (anemia, KEK, obesitas, dll)
                if ($isAnemia || $isKek || $isObesitas) {
                    $answers[$pId] = 1;
                }
            } elseif ($pertanyaan->nomor === 15) {
                // Bengkak / preeklampsia ringan (TD tinggi sistol >= 140 / diastol >= 90 atau edema)
                if ($isHipertensi || in_array($effectiveEdema, ['tungkai', 'seluruh_tubuh'])) {
                    $answers[$pId] = 1;
                }
            } elseif ($pertanyaan->nomor === 23) {
                // Preeklampsia berat / kejang (TD >= 160/110)
                if ($isHipertensiBerat) {
                    $answers[$pId] = 1;
                }
            }
        }

        $totalSkor = 2; // Skor Awal Dasar Poedji Rochjati
        $detailRows = [];

        foreach ($allPertanyaans as $pId => $pertanyaan) {
            $isAnsweredYes = isset($answers[$pId]) && $answers[$pId] == '1';
            $skor = $isAnsweredYes ? $pertanyaan->skor : 0;
            $totalSkor += $skor;

            $detailRows[] = [
                'pertanyaan_spr_id' => $pertanyaan->id,
                'jawaban' => $isAnsweredYes,
                'skor' => $skor,
            ];
        }

        $kategori = KategoriRisiko::fromScore($totalSkor);

        $skrining = DB::transaction(function () use ($validated, $user, $ibuHamil, $totalSkor, $kategori, $detailRows) {
            $kunjunganId = $validated['kunjungan_id'] ?? null;
            $kunjungan = null;

            if ($kunjunganId) {
                $kunjungan = Kunjungan::find($kunjunganId);
            } else {
                $kunjungan = Kunjungan::where('ibu_hamil_id', $validated['ibu_hamil_id'])
                    ->whereDate('tanggal_kunjungan', $validated['tanggal_skrining'])
                    ->first()
                    ?? Kunjungan::where('ibu_hamil_id', $validated['ibu_hamil_id'])
                        ->where('status_dilayani', false)
                        ->orderBy('tanggal_kunjungan')
                        ->first();
            }

            $skrining = Skrining::create([
                'ibu_hamil_id' => $validated['ibu_hamil_id'],
                'user_id' => $user->id,
                'kunjungan_id' => $kunjungan?->id,
                'tanggal_skrining' => $validated['tanggal_skrining'],
                'usia_kehamilan_minggu' => $validated['usia_kehamilan_minggu'] ?? null,
                'trimester' => $validated['trimester'] ?? null,
                'total_skor' => $totalSkor,
                'kategori_risiko' => $kategori,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            foreach ($detailRows as $row) {
                $row['skrining_id'] = $skrining->id;
                $row['created_at'] = now();
                $row['updated_at'] = now();
                SkriningDetail::create($row);
            }

            // Otomatis ubah status kunjungan menjadi Hadir dan Dilayani
            if ($kunjungan) {
                $kunjunganData = [
                    'status_hadir' => true,
                    'status_dilayani' => true,
                    'skrining_id' => $skrining->id,
                ];
                if (empty($kunjungan->berat_badan) && $ibuHamil->berat_badan) {
                    $kunjunganData['berat_badan'] = $ibuHamil->berat_badan;
                }
                if (empty($kunjungan->tinggi_badan) && $ibuHamil->tinggi_badan) {
                    $kunjunganData['tinggi_badan'] = $ibuHamil->tinggi_badan;
                }
                if (empty($kunjungan->tekanan_darah_sistol) && $ibuHamil->tekanan_darah_sistol) {
                    $kunjunganData['tekanan_darah_sistol'] = $ibuHamil->tekanan_darah_sistol;
                }
                if (empty($kunjungan->tekanan_darah_diastol) && $ibuHamil->tekanan_darah_diastol) {
                    $kunjunganData['tekanan_darah_diastol'] = $ibuHamil->tekanan_darah_diastol;
                }
                $kunjungan->update($kunjunganData);
            }

            return $skrining;
        });

        $alertMessage = "Skrining berhasil disimpan! Total Skor: {$totalSkor} ({$kategori->label()}). Status kunjungan terkait kini otomatis tercatat Dilayani.";
        if ($kategori === KategoriRisiko::KRST) {
            $alertMessage .= ' PERHATIAN: Pasien tergolong Risiko Sangat Tinggi, segera rujuk ke Rumah Sakit!';
        }

        return redirect()->route('skrining.show', $skrining)
            ->with('success', $alertMessage);
    }

    /**
     * Display the specified screening.
     */
    public function show(Skrining $skrining): View
    {
        $skrining->load([
            'ibuHamil.desa',
            'user.desa',
            'details.pertanyaanSpr',
        ]);

        $kelompokI = $skrining->details->filter(fn ($d) => $d->pertanyaanSpr->kelompok->value === 'I');
        $kelompokII = $skrining->details->filter(fn ($d) => $d->pertanyaanSpr->kelompok->value === 'II');
        $kelompokIII = $skrining->details->filter(fn ($d) => $d->pertanyaanSpr->kelompok->value === 'III');

        return view('skrining.show', compact('skrining', 'kelompokI', 'kelompokII', 'kelompokIII'));
    }

    /**
     * Printable view of Kartu Skor Poedji Rochjati.
     */
    public function print(Skrining $skrining): View
    {
        $skrining->load([
            'ibuHamil.desa',
            'user.desa',
            'details.pertanyaanSpr',
        ]);

        $kelompokI = $skrining->details->filter(fn ($d) => $d->pertanyaanSpr->kelompok->value === 'I');
        $kelompokII = $skrining->details->filter(fn ($d) => $d->pertanyaanSpr->kelompok->value === 'II');
        $kelompokIII = $skrining->details->filter(fn ($d) => $d->pertanyaanSpr->kelompok->value === 'III');

        return view('skrining.print', compact('skrining', 'kelompokI', 'kelompokII', 'kelompokIII'));
    }

    /**
     * Remove the specified screening.
     */
    public function destroy(Skrining $skrining): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isKader()) {
            abort(403, 'Kader tidak diizinkan menghapus riwayat skrining.');
        }

        $canDelete = $user->isAdmin()
            || $user->desa_id === $skrining->ibuHamil->desa_id
            || $user->id === $skrining->user_id;

        if (! $canDelete) {
            abort(403);
        }

        $ibuHamilId = $skrining->ibu_hamil_id;
        $skrining->delete();

        return redirect()->route('ibu-hamil.show', $ibuHamilId)
            ->with('success', 'Riwayat skrining berhasil dihapus.');
    }
}
