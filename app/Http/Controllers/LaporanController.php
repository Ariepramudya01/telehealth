<?php

namespace App\Http\Controllers;

use App\Enums\KategoriRisiko;
use App\Enums\Role;
use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\PertanyaanSpr;
use App\Models\Skrining;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function __construct(
        protected ReportExportService $exportService
    ) {}

    /**
     * Display the report center.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Kader cannot access or download reports
        if ($user->isKader()) {
            abort(403, 'Kader tidak memiliki akses untuk mengunduh laporan. Silakan lihat capaian di Dashboard.');
        }

        $desas = $user->isAdmin() ? Desa::orderBy('nama')->get() : Desa::where('id', $user->desa_id)->get();
        $selectedDesaId = $user->isAdmin() ? $request->query('desa_id') : $user->desa_id;

        // Quick stats for preview
        $bumilCount = IbuHamil::when($selectedDesaId, fn ($q) => $q->where('desa_id', $selectedDesaId))->count();
        $skriningCount = Skrining::when($selectedDesaId, fn ($q) => $q->whereHas('ibuHamil', fn ($sq) => $sq->where('desa_id', $selectedDesaId)))->count();
        $kunjunganCount = Kunjungan::when($selectedDesaId, fn ($q) => $q->where('desa_id', $selectedDesaId))->count();

        $selectedDesa = $selectedDesaId ? Desa::find($selectedDesaId) : null;

        return view('laporan.index', compact('desas', 'selectedDesaId', 'selectedDesa', 'bumilCount', 'skriningCount', 'kunjunganCount'));
    }

    /**
     * Download Aggregate Report (Excel).
     */
    public function downloadAgregat(Request $request): StreamedResponse
    {
        $user = Auth::user();

        if ($user->isKader()) {
            abort(403, 'Akses ditolak.');
        }

        $desaId = $user->isAdmin() ? $request->query('desa_id') : $user->desa_id;

        $desasQuery = Desa::with(['users']);
        if ($desaId) {
            $desasQuery->where('id', $desaId);
        }
        $desas = $desasQuery->get();

        $headers = [
            'No',
            'Kode Desa',
            'Nama Desa',
            'Kecamatan',
            'Kabupaten',
            'Bidan Penanggung Jawab',
            'Total Ibu Hamil',
            'Total Kunjungan Dijadwalkan',
            'Kunjungan Hadir',
            'Kunjungan Dilayani',
            '% Capaian Hadir',
            '% Capaian Dilayani',
            'Total Skrining',
            'Risiko Rendah (KRR)',
            'Risiko Tinggi (KRT)',
            'Risiko Sangat Tinggi (KRST)',
            '% Risiko Tinggi & Sangat Tinggi',
        ];

        $rows = [];
        $no = 1;

        foreach ($desas as $desa) {
            $bumilCount = $desa->ibuHamils()->count();
            $kunjunganCount = $desa->kunjungans()->count();
            $hadirCount = $desa->kunjungans()->where('status_hadir', true)->count();
            $dilayaniCount = $desa->kunjungans()->where('status_dilayani', true)->count();

            $persenHadir = $kunjunganCount > 0 ? round(($hadirCount / $kunjunganCount) * 100, 1).'%' : '0%';
            $persenDilayani = $kunjunganCount > 0 ? round(($dilayaniCount / $kunjunganCount) * 100, 1).'%' : '0%';

            $skrinings = Skrining::whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $desa->id))->get();
            $skriningCount = $skrinings->count();
            $krrCount = $skrinings->where('kategori_risiko', KategoriRisiko::KRR)->count();
            $krtCount = $skrinings->where('kategori_risiko', KategoriRisiko::KRT)->count();
            $krstCount = $skrinings->where('kategori_risiko', KategoriRisiko::KRST)->count();

            $persenRisiko = $skriningCount > 0
                ? round((($krtCount + $krstCount) / $skriningCount) * 100, 1).'%'
                : '0%';

            $bidanName = $desa->users->firstWhere('role', Role::Bidan)?->name ?? 'Belum Ada';

            $rows[] = [
                $no++,
                $desa->kode_desa ?? '-',
                $desa->nama,
                $desa->kecamatan,
                $desa->kabupaten,
                $bidanName,
                $bumilCount,
                $kunjunganCount,
                $hadirCount,
                $dilayaniCount,
                $persenHadir,
                $persenDilayani,
                $skriningCount,
                $krrCount,
                $krtCount,
                $krstCount,
                $persenRisiko,
            ];
        }

        $filename = 'Laporan_Agregat_E-Detimil_'.now()->format('Ymd_His').'.xlsx';

        return $this->exportService->streamDownload($filename, $headers, $rows);
    }

    /**
     * Download Individual Mother Report (Excel).
     */
    public function downloadIndividu(Request $request): StreamedResponse
    {
        $user = Auth::user();

        if ($user->isKader()) {
            abort(403);
        }

        $desaId = $user->isAdmin() ? $request->query('desa_id') : $user->desa_id;

        $query = IbuHamil::with(['desa', 'user', 'skrinings' => fn ($q) => $q->latest('tanggal_skrining')]);

        if ($desaId) {
            $query->where('desa_id', $desaId);
        }

        $ibuHamils = $query->get();

        $headers = [
            'No',
            'NIK',
            'Nama Ibu Hamil',
            'Desa',
            'Usia (Tahun)',
            'Nama Suami',
            'No HP',
            'Alamat',
            'Golongan Darah',
            'Tinggi Badan (cm)',
            'Berat Badan (kg)',
            'HPHT',
            'Taksiran Persalinan',
            'Gravida',
            'Para',
            'Abortus',
            'Anak Hidup',
            'Skor SPR Terakhir',
            'Kategori Risiko',
            'Tanggal Skrining Terakhir',
            'Petugas Input Awal',
        ];

        $rows = [];
        $no = 1;

        foreach ($ibuHamils as $bumil) {
            $latestSkrining = $bumil->latestSkrining();

            $rows[] = [
                $no++,
                $bumil->nik ? "'".$bumil->nik : '-',
                $bumil->nama,
                $bumil->desa?->nama ?? '-',
                $bumil->usia ?? '-',
                $bumil->nama_suami ?? '-',
                $bumil->no_hp ? "'".$bumil->no_hp : '-',
                $bumil->alamat ?? '-',
                $bumil->golongan_darah ?? '-',
                $bumil->tinggi_badan ?? '-',
                $bumil->berat_badan ?? '-',
                $bumil->hpht ? $bumil->hpht->format('d/m/Y') : '-',
                $bumil->taksiran_persalinan ? $bumil->taksiran_persalinan->format('d/m/Y') : '-',
                $bumil->gravida,
                $bumil->para,
                $bumil->abortus,
                $bumil->jumlah_anak_hidup,
                $latestSkrining?->total_skor ?? 'Belum Diskrining',
                $latestSkrining ? $latestSkrining->kategori_risiko->label() : '-',
                $latestSkrining ? $latestSkrining->tanggal_skrining->format('d/m/Y') : '-',
                $bumil->user?->name ?? '-',
            ];
        }

        $filename = 'Laporan_Individu_Ibu_Hamil_'.now()->format('Ymd_His').'.xlsx';

        return $this->exportService->streamDownload($filename, $headers, $rows);
    }

    /**
     * Download Performance Report: Data Pribadi yang diinput (Excel).
     */
    public function downloadKinerjaInput(Request $request): StreamedResponse
    {
        $user = Auth::user();

        if ($user->isKader()) {
            abort(403);
        }

        $desaId = $user->isAdmin() ? $request->query('desa_id') : $user->desa_id;

        $query = IbuHamil::with(['desa', 'user']);

        if ($desaId) {
            $query->where('desa_id', $desaId);
        }

        $bumils = $query->latest()->get();

        $headers = [
            'No',
            'Tanggal Registrasi',
            'Petugas yang Menginput',
            'Peran Petugas',
            'Desa Petugas',
            'NIK Ibu Hamil',
            'Nama Ibu Hamil',
            'Nama Suami',
            'No Handphone',
            'Alamat Lengkap',
            'HPHT',
            'Taksiran Persalinan',
            'Status Aktif',
        ];

        $rows = [];
        $no = 1;

        foreach ($bumils as $b) {
            $rows[] = [
                $no++,
                $b->created_at->format('d/m/Y H:i'),
                $b->user?->name ?? '-',
                $b->user ? $b->user->role->label() : '-',
                $b->desa?->nama ?? '-',
                $b->nik ? "'".$b->nik : '-',
                $b->nama,
                $b->nama_suami ?? '-',
                $b->no_hp ? "'".$b->no_hp : '-',
                $b->alamat ?? '-',
                $b->hpht ? $b->hpht->format('d/m/Y') : '-',
                $b->taksiran_persalinan ? $b->taksiran_persalinan->format('d/m/Y') : '-',
                $b->is_active ? 'Aktif' : 'Non-Aktif',
            ];
        }

        $filename = 'Laporan_Kinerja_Input_Data_Pribadi_'.now()->format('Ymd_His').'.xlsx';

        return $this->exportService->streamDownload($filename, $headers, $rows);
    }

    /**
     * Download Performance Report: Data Skrining dan Jawaban SPR secara terpisah (Excel).
     */
    public function downloadKinerjaSkrining(Request $request): StreamedResponse
    {
        $user = Auth::user();

        if ($user->isKader()) {
            abort(403);
        }

        $desaId = $user->isAdmin() ? $request->query('desa_id') : $user->desa_id;

        $pertanyaans = PertanyaanSpr::orderBy('nomor')->get();

        $query = Skrining::with(['ibuHamil.desa', 'user', 'details']);

        if ($desaId) {
            $query->whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $desaId));
        }

        $skrinings = $query->latest('tanggal_skrining')->get();

        $headers = [
            'No',
            'Tanggal Skrining',
            'Petugas Skrining',
            'Peran Petugas',
            'Desa',
            'Nama Ibu Hamil',
            'NIK Ibu Hamil',
            'Usia Kehamilan (Minggu)',
            'Trimester',
            'Skor Dasar',
            'Total Skor SPR',
            'Kategori Risiko',
            'Catatan Petugas',
        ];

        // Add each question title as header
        foreach ($pertanyaans as $p) {
            $headers[] = "[#{$p->nomor} Skor:{$p->skor}] {$p->pertanyaan}";
        }

        $rows = [];
        $no = 1;

        foreach ($skrinings as $s) {
            $detailMap = $s->details->keyBy('pertanyaan_spr_id');

            $row = [
                $no++,
                $s->tanggal_skrining->format('d/m/Y'),
                $s->user?->name ?? '-',
                $s->user ? $s->user->role->label() : '-',
                $s->ibuHamil?->desa?->nama ?? '-',
                $s->ibuHamil?->nama ?? '-',
                $s->ibuHamil?->nik ? "'".$s->ibuHamil->nik : '-',
                $s->usia_kehamilan_minggu ?? '-',
                $s->trimester ? 'Trimester '.$s->trimester : '-',
                2, // Base score
                $s->total_skor,
                $s->kategori_risiko->label(),
                $s->catatan ?? '-',
            ];

            foreach ($pertanyaans as $p) {
                $detail = $detailMap->get($p->id);
                if ($detail && $detail->jawaban) {
                    $row[] = "Ya (+{$p->skor})";
                } else {
                    $row[] = 'Tidak (0)';
                }
            }

            $rows[] = $row;
        }

        $filename = 'Laporan_Kinerja_Skrining_SPR_Jawaban_'.now()->format('Ymd_His').'.xlsx';

        return $this->exportService->streamDownload($filename, $headers, $rows);
    }
}
