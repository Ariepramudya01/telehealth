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
            return $this->adminDashboard($user, $selectedDesaId);
        }

        if ($user->isBidan()) {
            return $this->bidanDashboard($user);
        }

        return $this->kaderDashboard($user);
    }

    /**
     * Build dashboard data for Administrator.
     */
    private function adminDashboard(User $currentUser, ?string $selectedDesaId): View
    {
        $allDesas = Desa::with(['users'])->get();

        // Scope desas based on jurisdiction:
        // 1. Dinkes Provinsi: all desas in province (or all desas)
        // 2. Dinkes Kabupaten: strictly desas within user's kabupaten
        // 3. Puskesmas / others: only desas within puskesmas / canAccessDesa
        if ($currentUser->isDinkesProvinsi()) {
            $prov = $currentUser->provinsi ?? $currentUser->desa?->provinsi;
            if ($prov) {
                $cleanProv = strtolower(trim($prov));
                $desas = $allDesas->filter(fn ($d) => $d->provinsi && str_contains(strtolower($d->provinsi), $cleanProv))->values();
                if ($desas->isEmpty()) {
                    $desas = $allDesas;
                }
            } else {
                $desas = $allDesas;
            }
        } else {
            $desas = $allDesas->filter(fn ($d) => $currentUser->canAccessDesa($d))->values();
        }

        $accessibleDesaIds = $desas->pluck('id')->toArray();

        // If selectedDesaId is specified, ensure it belongs to accessible desas (for security)
        if ($selectedDesaId && ! in_array($selectedDesaId, $accessibleDesaIds)) {
            $selectedDesaId = null;
        }

        $ibuHamilQuery = IbuHamil::query();
        $kunjunganQuery = Kunjungan::query();
        $skriningQuery = Skrining::query();

        if ($selectedDesaId) {
            $ibuHamilQuery->where('desa_id', $selectedDesaId);
            $kunjunganQuery->where('desa_id', $selectedDesaId);
            $skriningQuery->whereHas('ibuHamil', fn ($q) => $q->where('desa_id', $selectedDesaId));
        } elseif (! $currentUser->isDinkesProvinsi()) {
            $ibuHamilQuery->whereIn('desa_id', $accessibleDesaIds);
            $kunjunganQuery->whereIn('desa_id', $accessibleDesaIds);
            $skriningQuery->whereHas('ibuHamil', fn ($q) => $q->whereIn('desa_id', $accessibleDesaIds));
        }

        $totalIbuHamil = (clone $ibuHamilQuery)->count();
        $totalDesa = $desas->count();

        if ($currentUser->isDinkesProvinsi()) {
            $totalBidan = User::where('role', Role::Bidan)->count();
            $totalKader = User::where('role', Role::Kader)->count();
        } else {
            $totalBidan = User::where('role', Role::Bidan)->with('desa')->get()->filter(fn ($u) => $currentUser->canAccessStaff($u))->count();
            $totalKader = User::where('role', Role::Kader)->with('desa')->get()->filter(fn ($u) => $currentUser->canAccessStaff($u))->count();
        }

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
            ->when(! $selectedDesaId && ! $currentUser->isDinkesProvinsi(), fn ($q) => $q->whereHas('ibuHamil', fn ($sq) => $sq->whereIn('desa_id', $accessibleDesaIds)))
            ->latest('tanggal_skrining')
            ->take(5)
            ->get();

        // Jadwal Kunjungan Terdekat
        $kunjunganTerdekat = Kunjungan::with(['ibuHamil.desa', 'ibuHamil.skrinings', 'user'])
            ->when($selectedDesaId, fn ($q) => $q->where('desa_id', $selectedDesaId))
            ->when(! $selectedDesaId && ! $currentUser->isDinkesProvinsi(), fn ($q) => $q->whereIn('desa_id', $accessibleDesaIds))
            ->orderBy('tanggal_kunjungan')
            ->take(6)
            ->get();

        // Village comparison data (scoped to accessible desas only)
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
                'puskesmas' => $desa->puskesmas,
                'bidan' => $desa->users->firstWhere('role', Role::Bidan)?->name ?? 'Belum Ditugaskan',
                'total_bumil' => $bumilCount,
                'total_kunjungan' => $kunjunganCount,
                'hadir' => $hadirCount,
                'dilayani' => $dilayaniCount,
                'skrining' => $skriningCount,
                'persen_hadir' => $persenHadirDesa,
                'persen_dilayani' => $persenDilayaniDesa,
                'persen_skrining' => $persenSkriningDesa,
                'krst_count' => $krstCount,
            ];
        })->sortByDesc('persen_skrining')->values();

        // Regional Performance Ranking (Kinerja Wilayah):
        // 1. Dinkes Provinsi: Kinerja Capaian masing-masing Kabupaten dari tertinggi hingga terendah
        // 2. Dinkes Kabupaten: Kinerja Capaian masing-masing Puskesmas dari tertinggi hingga terendah
        $kinerjaKabupaten = [];
        $kinerjaPuskesmas = [];
        $kinerjaKecamatan = [];

        if ($currentUser->isDinkesProvinsi()) {
            $groupedByKab = $desas->groupBy(function ($d) {
                $k = trim((string) $d->kabupaten);

                return $k !== '' ? ucwords(strtolower($k)) : 'Wilayah Lainnya';
            });

            $rankList = [];
            foreach ($groupedByKab as $kabName => $groupDesas) {
                $desaIds = $groupDesas->pluck('id')->all();
                $bumilCount = IbuHamil::whereIn('desa_id', $desaIds)->count();
                $kunjunganCount = Kunjungan::whereIn('desa_id', $desaIds)->count();
                $hadirCount = Kunjungan::whereIn('desa_id', $desaIds)->where('status_hadir', true)->count();
                $dilayaniCount = Kunjungan::whereIn('desa_id', $desaIds)->where('status_dilayani', true)->count();
                $skriningCount = Skrining::whereHas('ibuHamil', fn ($q) => $q->whereIn('desa_id', $desaIds))->count();
                $krstCount = Skrining::whereHas('ibuHamil', fn ($q) => $q->whereIn('desa_id', $desaIds))
                    ->where('kategori_risiko', KategoriRisiko::KRST)
                    ->count();

                $persenHadir = $kunjunganCount > 0 ? round(($hadirCount / $kunjunganCount) * 100, 1) : 0;
                $persenDilayani = $kunjunganCount > 0 ? round(($dilayaniCount / $kunjunganCount) * 100, 1) : 0;
                $persenSkrining = $bumilCount > 0 ? min(100, round(($skriningCount / $bumilCount) * 100, 1)) : 0;
                $puskesmasCount = $groupDesas->pluck('puskesmas')->filter()->unique()->count();

                $rankList[] = [
                    'nama' => $kabName,
                    'total_puskesmas' => $puskesmasCount,
                    'total_desa' => $groupDesas->count(),
                    'total_bumil' => $bumilCount,
                    'total_kunjungan' => $kunjunganCount,
                    'hadir' => $hadirCount,
                    'dilayani' => $dilayaniCount,
                    'skrining' => $skriningCount,
                    'persen_hadir' => $persenHadir,
                    'persen_dilayani' => $persenDilayani,
                    'persen_skrining' => $persenSkrining,
                    'krst_count' => $krstCount,
                ];
            }

            usort($rankList, function ($a, $b) {
                return ($b['persen_skrining'] <=> $a['persen_skrining'])
                    ?: ($b['persen_dilayani'] <=> $a['persen_dilayani'])
                    ?: ($b['persen_hadir'] <=> $a['persen_hadir'])
                    ?: ($b['total_bumil'] <=> $a['total_bumil']);
            });

            foreach ($rankList as $idx => &$item) {
                $item['ranking'] = $idx + 1;
            }
            unset($item);

            $kinerjaKabupaten = $rankList;
        } elseif ($currentUser->isDinkesKabupaten()) {
            $groupedByPkm = $desas->groupBy(function ($d) {
                $p = trim((string) $d->puskesmas);
                if ($p !== '' && ! preg_match('/^(dinas\s+kesehatan|dinkes)/i', $p)) {
                    return $p;
                }

                return 'UPT Puskesmas '.($d->kecamatan ?? 'Wilayah');
            });

            $rankList = [];
            foreach ($groupedByPkm as $pkmName => $groupDesas) {
                $desaIds = $groupDesas->pluck('id')->all();
                $bumilCount = IbuHamil::whereIn('desa_id', $desaIds)->count();
                $kunjunganCount = Kunjungan::whereIn('desa_id', $desaIds)->count();
                $hadirCount = Kunjungan::whereIn('desa_id', $desaIds)->where('status_hadir', true)->count();
                $dilayaniCount = Kunjungan::whereIn('desa_id', $desaIds)->where('status_dilayani', true)->count();
                $skriningCount = Skrining::whereHas('ibuHamil', fn ($q) => $q->whereIn('desa_id', $desaIds))->count();
                $krstCount = Skrining::whereHas('ibuHamil', fn ($q) => $q->whereIn('desa_id', $desaIds))
                    ->where('kategori_risiko', KategoriRisiko::KRST)
                    ->count();

                $persenHadir = $kunjunganCount > 0 ? round(($hadirCount / $kunjunganCount) * 100, 1) : 0;
                $persenDilayani = $kunjunganCount > 0 ? round(($dilayaniCount / $kunjunganCount) * 100, 1) : 0;
                $persenSkrining = $bumilCount > 0 ? min(100, round(($skriningCount / $bumilCount) * 100, 1)) : 0;

                $rankList[] = [
                    'nama' => $pkmName,
                    'kecamatan' => $groupDesas->pluck('kecamatan')->filter()->unique()->implode(', ') ?: '-',
                    'total_desa' => $groupDesas->count(),
                    'total_bumil' => $bumilCount,
                    'total_kunjungan' => $kunjunganCount,
                    'hadir' => $hadirCount,
                    'dilayani' => $dilayaniCount,
                    'skrining' => $skriningCount,
                    'persen_hadir' => $persenHadir,
                    'persen_dilayani' => $persenDilayani,
                    'persen_skrining' => $persenSkrining,
                    'krst_count' => $krstCount,
                ];
            }

            usort($rankList, function ($a, $b) {
                return ($b['persen_skrining'] <=> $a['persen_skrining'])
                    ?: ($b['persen_dilayani'] <=> $a['persen_dilayani'])
                    ?: ($b['persen_hadir'] <=> $a['persen_hadir'])
                    ?: ($b['total_bumil'] <=> $a['total_bumil']);
            });

            foreach ($rankList as $idx => &$item) {
                $item['ranking'] = $idx + 1;
            }
            unset($item);

            $kinerjaPuskesmas = $rankList;
        }

        // Data for interactive animated diagram focused by jurisdiction
        if ($currentUser->isDinkesProvinsi()) {
            $chartLabels = collect($kinerjaKabupaten)->pluck('nama')->values();
            $chartHadir = collect($kinerjaKabupaten)->pluck('hadir')->values();
            $chartDilayani = collect($kinerjaKabupaten)->pluck('dilayani')->values();
            $chartSkrining = collect($kinerjaKabupaten)->pluck('skrining')->values();
        } elseif ($currentUser->isDinkesKabupaten()) {
            $chartLabels = collect($kinerjaPuskesmas)->pluck('nama')->values();
            $chartHadir = collect($kinerjaPuskesmas)->pluck('hadir')->values();
            $chartDilayani = collect($kinerjaPuskesmas)->pluck('dilayani')->values();
            $chartSkrining = collect($kinerjaPuskesmas)->pluck('skrining')->values();
        } else {
            $chartLabels = $desaCapaian->pluck('nama')->values();
            $chartHadir = $desaCapaian->pluck('hadir')->values();
            $chartDilayani = $desaCapaian->pluck('dilayani')->values();
            $chartSkrining = $desaCapaian->pluck('skrining')->values();
        }

        // Kinerja User (Bidan & Kader) - only loaded for Admin Puskesmas (Kapus / KTU)
        if ($currentUser->isAdminPuskesmas()) {
            $staffQuery = User::with('desa')
                ->whereIn('role', [Role::Bidan, Role::Kader]);

            if ($selectedDesaId) {
                $staffQuery->where('desa_id', $selectedDesaId);
            }

            $kinerjaUsers = $staffQuery->get()
                ->filter(fn ($staff) => $currentUser->canAccessStaff($staff))
                ->values()
                ->map(function ($staff) {
                    $bumilInputted = $staff->ibuHamils()->count();
                    $skriningDone = $staff->skrinings()->count();
                    $krstFound = $staff->skrinings()->where('kategori_risiko', KategoriRisiko::KRST)->count();

                    return [
                        'id' => $staff->id,
                        'name' => $staff->name,
                        'role' => $staff->role,
                        'desa' => $staff->desa?->nama ?? '-',
                        'puskesmas' => $staff->instansi ?? $staff->desa?->puskesmas ?? '-',
                        'bumil_count' => $bumilInputted,
                        'skrining_count' => $skriningDone,
                        'krst_found' => $krstFound,
                    ];
                });
        } else {
            $kinerjaUsers = collect();
        }

        $today = now()->toDateString();
        $jadwalJemputBola = Kunjungan::with(['ibuHamil.skrinings', 'desa', 'user'])
            ->when($selectedDesaId, fn ($q) => $q->where('desa_id', $selectedDesaId))
            ->when(! $selectedDesaId && ! $currentUser->isDinkesProvinsi(), fn ($q) => $q->whereIn('desa_id', $accessibleDesaIds))
            ->where(function ($q) {
                $q->whereNotNull('jadwal_kunjungan_berikutnya')
                    ->orWhere('metode_kunjungan', 'jemput_bola');
            })
            ->orderByRaw("CASE WHEN jadwal_kunjungan_berikutnya IS NOT NULL AND jadwal_kunjungan_berikutnya >= '{$today}' THEN 0 ELSE 1 END, COALESCE(jadwal_kunjungan_berikutnya, tanggal_kunjungan) ASC")
            ->take(6)
            ->get();

        return view('dashboard.admin', compact(
            'currentUser',
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
            'kunjunganTerdekat',
            'jadwalJemputBola',
            'desaCapaian',
            'kinerjaUsers',
            'kinerjaKabupaten',
            'kinerjaPuskesmas',
            'kinerjaKecamatan',
            'chartLabels',
            'chartHadir',
            'chartDilayani',
            'chartSkrining'
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

        // Jadwal Kunjungan Terdekat di Desa
        $kunjunganTerdekat = Kunjungan::with(['ibuHamil.skrinings', 'user'])
            ->where('desa_id', $desa->id)
            ->orderBy('tanggal_kunjungan')
            ->take(6)
            ->get();

        // Jadwal Jemput Bola ANC Terdekat di Desa Bidan
        $today = now()->toDateString();
        $jadwalJemputBola = Kunjungan::with(['ibuHamil.skrinings', 'desa', 'user'])
            ->where('desa_id', $desa->id)
            ->where(function ($q) {
                $q->whereNotNull('jadwal_kunjungan_berikutnya')
                    ->orWhere('metode_kunjungan', 'jemput_bola');
            })
            ->orderByRaw("CASE WHEN jadwal_kunjungan_berikutnya IS NOT NULL AND jadwal_kunjungan_berikutnya >= '{$today}' THEN 0 ELSE 1 END, COALESCE(jadwal_kunjungan_berikutnya, tanggal_kunjungan) ASC")
            ->take(6)
            ->get();

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
            'mySkriningCount',
            'kunjunganTerdekat',
            'jadwalJemputBola'
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

        // Jadwal Kunjungan Terdekat di Desa
        $kunjunganTerdekat = Kunjungan::with(['ibuHamil.skrinings', 'user'])
            ->where('desa_id', $desa->id)
            ->orderBy('tanggal_kunjungan')
            ->take(6)
            ->get();

        // Jadwal Jemput Bola ANC Terdekat berdasarkan jadwal_kunjungan_berikutnya di wilayah kader
        $today = now()->toDateString();
        $jadwalJemputBola = Kunjungan::with(['ibuHamil.skrinings', 'desa', 'user'])
            ->where('desa_id', $desa->id)
            ->where(function ($q) {
                $q->whereNotNull('jadwal_kunjungan_berikutnya')
                    ->orWhere('metode_kunjungan', 'jemput_bola');
            })
            ->orderByRaw("CASE WHEN jadwal_kunjungan_berikutnya IS NOT NULL AND jadwal_kunjungan_berikutnya >= '{$today}' THEN 0 ELSE 1 END, COALESCE(jadwal_kunjungan_berikutnya, tanggal_kunjungan) ASC")
            ->take(6)
            ->get();

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
            'recentSkrinings',
            'kunjunganTerdekat',
            'jadwalJemputBola'
        ));
    }
}
