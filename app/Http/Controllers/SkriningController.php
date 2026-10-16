<?php

namespace App\Http\Controllers;

use App\Enums\KategoriRisiko;
use App\Models\IbuHamil;
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
        $query = Skrining::with(['ibuHamil.desa', 'user']);

        if (! $user->isAdmin()) {
            $query->whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $user->desa_id));
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

        $ibuHamilQuery = IbuHamil::where('is_active', true);
        if (! $user->isAdmin()) {
            $ibuHamilQuery->where('desa_id', $user->desa_id);
        }

        $ibuHamils = $ibuHamilQuery->orderBy('nama')->get();
        $selectedIbuHamilId = $request->query('ibu_hamil_id');

        $pertanyaans = PertanyaanSpr::orderBy('nomor')->get();
        $kelompokI = $pertanyaans->where('kelompok.value', 'I');
        $kelompokII = $pertanyaans->where('kelompok.value', 'II');
        $kelompokIII = $pertanyaans->where('kelompok.value', 'III');

        return view('skrining.create', compact(
            'ibuHamils',
            'selectedIbuHamilId',
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

        $validated = $request->validate([
            'ibu_hamil_id' => ['required', 'exists:ibu_hamils,id'],
            'tanggal_skrining' => ['required', 'date'],
            'usia_kehamilan_minggu' => ['nullable', 'integer', 'min:1', 'max:45'],
            'trimester' => ['nullable', 'integer', 'in:1,2,3'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string'],
        ]);

        $ibuHamil = IbuHamil::findOrFail($validated['ibu_hamil_id']);

        if (! $user->isAdmin() && $ibuHamil->desa_id !== $user->desa_id) {
            abort(403, 'Anda tidak berhak melakukan skrining untuk ibu hamil di luar desa Anda.');
        }

        $answers = $request->input('answers', []);
        $allPertanyaans = PertanyaanSpr::all()->keyBy('id');

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

        $skrining = DB::transaction(function () use ($validated, $user, $totalSkor, $kategori, $detailRows) {
            $skrining = Skrining::create([
                'ibu_hamil_id' => $validated['ibu_hamil_id'],
                'user_id' => $user->id,
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

            return $skrining;
        });

        $alertMessage = "Skrining berhasil disimpan! Total Skor: {$totalSkor} ({$kategori->label()}).";
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
        $user = Auth::user();

        if (! $user->isAdmin() && $user->desa_id !== $skrining->ibuHamil->desa_id) {
            abort(403);
        }

        $skrining->load([
            'ibuHamil.desa',
            'user',
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
        $user = Auth::user();

        if (! $user->isAdmin() && $user->desa_id !== $skrining->ibuHamil->desa_id) {
            abort(403);
        }

        $skrining->load([
            'ibuHamil.desa',
            'user',
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

        if (! $user->isAdmin() && $user->desa_id !== $skrining->ibuHamil->desa_id) {
            abort(403);
        }

        $ibuHamilId = $skrining->ibu_hamil_id;
        $skrining->delete();

        return redirect()->route('ibu-hamil.show', $ibuHamilId)
            ->with('success', 'Riwayat skrining berhasil dihapus.');
    }
}

