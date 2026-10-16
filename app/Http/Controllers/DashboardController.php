<?php

namespace App\Http\Controllers;

use App\Enums\KategoriRisiko;
use App\Enums\Role;
use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\Skrining;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard tailored to the user's role.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $selectedDesaId = $request->query('desa_id');

        if ($user->isAdmin()) {
            return $this->adminDashboard($selectedDesaId);
        }

        if ($user->isBidan()) {
            return $this->bidanDashboard($user);
        }

        return $this->kaderDashboard($user);
    }

    /**
     * Build dashboard data for Administrator.
     */
    private function adminDashboard(?string $selectedDesaId): View
    {
        $desas = Desa::with(['users'])->get();

        $ibuHamilQuery = IbuHamil::query();
        $kunjunganQuery = Kunjungan::query();
        $skriningQuery = Skrining::query();

        if ($selectedDesaId) {
            $ibuHamilQuery->where('desa_id', $selectedDesaId);
            $kunjunganQuery->where('desa_id', $selectedDesaId);
            $skriningQuery->whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $selectedDesaId));
        }

        $totalIbuHamil = (clone $ibuHamilQuery)->count();
        $totalDesa = Desa::count();
        $totalBidan = User::where('role', Role::Bidan)->count();
        $totalKader = User::where('role', Role::Kader)->count();

        // Kunjungan metrics
        $totalKunjungan = (clone $kunjunganQuery)->count();
        $totalHadir = (clone $kunjunganQuery)->where('status_hadir', true)->count();
        $totalDilayani = (clone $kunjunganQuery)->where('status_dilayani', true)->count();

        $persenHadir = $totalKunjungan > 0 ? round(($totalHadir / $totalKunjungan) * 100, 1) : 0;
        $persenDilayani = $totalKunjungan > 0 ? round(($totalDilayani / $totalKunjungan) * 100, 1) : 0;
        $persenDilayaniDariHadir = $totalHadir > 0 ? round(($totalDilayani / $totalHadir) * 100, 1) : 0;

        // Skrining metrics
        $totalSkrining = (clone $skriningQuery)->count();
        $skriningKRR = (clone $skriningQuery)->where('kategori_risiko', KategoriRisiko::KRR)->count();
        $skriningKRT = (clone $skriningQuery)->where('kategori_risiko', KategoriRisiko::KRT)->count();
        $skriningKRST = (clone $skriningQuery)->where('kategori_risiko', KategoriRisiko::KRST)->count();

        // High-risk urgent list
        $urgentKRST = Skrining::with(['ibuHamil.desa', 'user'])
            ->where('kategori_risiko', KategoriRisiko::KRST)
            ->when($selectedDesaId, fn ($q) => $q->whereHas('ibuHamil', fn ($sq) => $sq->where('desa_id', $selectedDesaId)))
            ->latest('tanggal_skrining')
            ->take(5)
            ->get();

        // Village comparison data
        $desaCapaian = $desas->map(function ($desa) {
            $bumilCount = $desa->ibuHamils()->count();
            $kunjunganCount = $desa->kunjungans()->count();
            $hadirCount = $desa->kunjungans()->where('status_hadir', true)->count();
            $dilayaniCount = $desa->kunjungans()->where('status_dilayani', true)->count();
            $skriningCount = Skrining::whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $desa->id))->count();

            $persenHadirDesa = $kunjunganCount > 0 ? round(($hadirCount / $kunjunganCount) * 100, 1) : 0;
            $persenDilayaniDesa = $kunjunganCount > 0 ? round(($dilayaniCount / $kunjunganCount) * 100, 1) : 0;
            $persenSkriningDesa = $bumilCount > 0 ? min(100, round(($skriningCount / $bumilCount) * 100, 1)) : 0;

            $krstCount = Skrining::whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $desa->id))
                ->where('kategori_risiko', KategoriRisiko::KRST)
                ->count();

            return [
                'id' => $desa->id,
                'nama' => $desa->nama,
                'kecamatan' => $desa->kecamatan,
                'bidan' => $desa->users->firstWhere('role', Role::Bidan)?->name ?? 'Belum Ditugaskan',
                'total_bumil' => $bumilCount,
                'total_kunjungan' => $kunjunganCount,
                'hadir' => $hadirCount,
                'dilayani' => $dilayaniCount,
                'persen_hadir' => $persenHadirDesa,
                'persen_dilayani' => $persenDilayaniDesa,
                'persen_skrining' => $persenSkriningDesa,
                'krst_count' => $krstCount,
            ];
        });

        // Kinerja User (Bidan & Kader)
        $kinerjaUsers = User::with('desa')
            ->whereIn('role', [Role::Bidan, Role::Kader])
            ->when($selectedDesaId, fn ($q) => $q->where('desa_id', $selectedDesaId))
            ->get()
            ->map(function ($staff) {
                $bumilInputted = $staff->ibuHamils()->count();
                $skriningDone = $staff->skrinings()->count();
                $krstFound = $staff->skrinings()->where('kategori_risiko', KategoriRisiko::KRST)->count();

                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'role' => $staff->role,
                    'desa' => $staff->desa?->nama ?? '-',
                    'bumil_count' => $bumilInputted,
                    'skrining_count' => $skriningDone,
                    'krst_found' => $krstFound,
                ];
            });

        return view('dashboard.admin', compact(
            'desas',
            'selectedDesaId',
            'totalIbuHamil',
            'totalDesa',
            'totalBidan',
            'totalKader',
            'totalKunjungan',
            'totalHadir',
            'totalDilayani',
            'persenHadir',
            'persenDilayani',
            'persenDilayaniDariHadir',
            'totalSkrining',
            'skriningKRR',
            'skriningKRT',
            'skriningKRST',
            'urgentKRST',
            'desaCapaian',
            'kinerjaUsers'
        ));
    }

    /**
     * Build dashboard data for Bidan Desa.
     */
    private function bidanDashboard(User $user): View
    {
        $desa = $user->desa;

        if (! $desa) {
            return view('dashboard.no-desa');
        }

        $bumilCount = $desa->ibuHamils()->count();
        $totalKunjungan = $desa->kunjungans()->count();
        $totalHadir = $desa->kunjungans()->where('status_hadir', true)->count();
        $totalDilayani = $desa->kunjungans()->where('status_dilayani', true)->count();

        $persenHadir = $totalKunjungan > 0 ? round(($totalHadir / $totalKunjungan) * 100, 1) : 0;
        $persenDilayani = $totalKunjungan > 0 ? round(($totalDilayani / $totalKunjungan) * 100, 1) : 0;

        $skrinings = Skrining::whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $desa->id))->get();
        $totalSkrining = $skrinings->count();
        $skriningKRR = $skrinings->where('kategori_risiko', KategoriRisiko::KRR)->count();
        $skriningKRT = $skrinings->where('kategori_risiko', KategoriRisiko::KRT)->count();
        $skriningKRST = $skrinings->where('kategori_risiko', KategoriRisiko::KRST)->count();

        $persenSkrining = $bumilCount > 0 ? min(100, round(($totalSkrining / $bumilCount) * 100, 1)) : 0;

        $urgentKRST = Skrining::with(['ibuHamil', 'user'])
            ->whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $desa->id))
            ->where('kategori_risiko', KategoriRisiko::KRST)
            ->latest('tanggal_skrining')
            ->take(5)
            ->get();

        // Kader under this village
        $kaders = User::where('desa_id', $desa->id)
            ->where('role', Role::Kader)
            ->get()
            ->map(function ($kader) {
                return [
                    'id' => $kader->id,
                    'name' => $kader->name,
                    'phone' => $kader->phone,
                    'bumil_count' => $kader->ibuHamils()->count(),
                    'skrining_count' => $kader->skrinings()->count(),
                    'krst_found' => $kader->skrinings()->where('kategori_risiko', KategoriRisiko::KRST)->count(),
                ];
            });

        // Personal performance of the Bidan
        $myBumilCount = $user->ibuHamils()->count();
        $mySkriningCount = $user->skrinings()->count();

        return view('dashboard.bidan', compact(
            'desa',
            'bumilCount',
            'totalKunjungan',
            'totalHadir',
            'totalDilayani',
            'persenHadir',
            'persenDilayani',
            'totalSkrining',
            'persenSkrining',
            'skriningKRR',
            'skriningKRT',
            'skriningKRST',
            'urgentKRST',
            'kaders',
            'myBumilCount',
            'mySkriningCount'
        ));
    }

    /**
     * Build dashboard data for Kader.
     */
    private function kaderDashboard(User $user): View
    {
        $desa = $user->desa;

        if (! $desa) {
            return view('dashboard.no-desa');
        }

        // Capaian Desa (percentage & totals for village)
        $desaBumilCount = $desa->ibuHamils()->count();
        $desaKunjunganCount = $desa->kunjungans()->count();
        $desaHadirCount = $desa->kunjungans()->where('status_hadir', true)->count();
        $desaDilayaniCount = $desa->kunjungans()->where('status_dilayani', true)->count();

        $desaPersenHadir = $desaKunjunganCount > 0 ? round(($desaHadirCount / $desaKunjunganCount) * 100, 1) : 0;
        $desaPersenDilayani = $desaKunjunganCount > 0 ? round(($desaDilayaniCount / $desaKunjunganCount) * 100, 1) : 0;

        $desaSkrinings = Skrining::whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $desa->id))->get();
        $desaTotalSkrining = $desaSkrinings->count();
        $desaPersenSkrining = $desaBumilCount > 0 ? min(100, round(($desaTotalSkrining / $desaBumilCount) * 100, 1)) : 0;

        $desaKRR = $desaSkrinings->where('kategori_risiko', KategoriRisiko::KRR)->count();
        $desaKRT = $desaSkrinings->where('kategori_risiko', KategoriRisiko::KRT)->count();
        $desaKRST = $desaSkrinings->where('kategori_risiko', KategoriRisiko::KRST)->count();

        // Kinerja Pribadi Kader
        $myBumils = $user->ibuHamils()->latest()->take(5)->get();
        $myTotalBumil = $user->ibuHamils()->count();

        $mySkrinings = $user->skrinings()->with('ibuHamil')->latest('tanggal_skrining')->get();
        $myTotalSkrining = $mySkrinings->count();

        $myKRR = $mySkrinings->where('kategori_risiko', KategoriRisiko::KRR)->count();
        $myKRT = $mySkrinings->where('kategori_risiko', KategoriRisiko::KRT)->count();
        $myKRST = $mySkrinings->where('kategori_risiko', KategoriRisiko::KRST)->count();

        $recentSkrinings = $mySkrinings->take(5);

        return view('dashboard.kader', compact(
            'desa',
            'desaBumilCount',
            'desaKunjunganCount',
            'desaHadirCount',
            'desaDilayaniCount',
            'desaPersenHadir',
            'desaPersenDilayani',
            'desaTotalSkrining',
            'desaPersenSkrining',
            'desaKRR',
            'desaKRT',
            'desaKRST',
            'myTotalBumil',
            'myTotalSkrining',
            'myKRR',
            'myKRT',
            'myKRST',
            'myBumils',
            'recentSkrinings'
        ));
    }
}

