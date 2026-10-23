<?php

namespace App\Services;

use App\Enums\KategoriRisiko;
use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\Skrining;
use App\Models\User;
use Illuminate\Support\Collection;

class RegionalAggregationService
{
    /**
     * Get accessible Desas for a Dinas user.
     */
    public function getAccessibleDesas(User $user): Collection
    {
        $query = Desa::query();

        $userKab = $user->kabupaten ?? $user->desa?->kabupaten;
        if ($userKab) {
            $cleanKab = strtolower(trim(preg_replace('/^(kabupaten|kab\.|kab|kota)\s+/i', '', (string) $userKab)));
            $query->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"]);
        }

        return $query->orderBy('nama')->get();
    }

    /**
     * Get aggregated Ibu Hamil data clustered by region.
     *
     * @return array{
     *     level: string,
     *     region_name: string,
     *     summary: array<string, mixed>,
     *     clusters: array<int, array<string, mixed>>
     * }
     */
    public function getIbuHamilAgregat(User $user): array
    {
        $desas = $this->getAccessibleDesas($user);
        $allDesaIds = $desas->pluck('id')->all();

        // Load all active ibu hamil in accessible desas with their latest screening and kunjungan
        $bumils = IbuHamil::with([
            'desa',
            'skrinings' => fn ($q) => $q->latest('tanggal_skrining'),
            'kunjungans' => fn ($q) => $q->latest('tanggal_kunjungan'),
        ])
            ->whereIn('desa_id', $allDesaIds)
            ->where('is_active', true)
            ->get();

        $kunjungans = Kunjungan::whereIn('desa_id', $allDesaIds)->get();

        $groupedDesas = $desas->groupBy(function ($d) {
            $p = trim((string) $d->puskesmas);
            if ($p !== '' && ! preg_match('/^(dinas\s+kesehatan|dinkes)/i', $p)) {
                return $p;
            }

            return 'UPT Puskesmas '.($d->kecamatan ?? 'Wilayah');
        });

        $clusters = [];
        $totalBumilAll = 0;
        $totalKrrAll = 0;
        $totalKrtAll = 0;
        $totalKrstAll = 0;
        $totalAnemiaAll = 0;
        $totalKekAll = 0;
        $totalSkriningBumilAll = 0;
        $totalKunjunganAll = 0;

        foreach ($groupedDesas as $groupName => $groupDesas) {
            $desaIds = $groupDesas->pluck('id')->all();
            $groupBumils = $bumils->whereIn('desa_id', $desaIds);
            $groupKunjungans = $kunjungans->whereIn('desa_id', $desaIds);

            $countBumil = $groupBumils->count();
            $countKunjungan = $groupKunjungans->count();

            $krr = 0;
            $krt = 0;
            $krst = 0;
            $anemia = 0;
            $kek = 0;
            $diskrining = 0;

            foreach ($groupBumils as $b) {
                $latestSkrining = $b->skrinings->first();
                if ($latestSkrining) {
                    $diskrining++;
                    if ($latestSkrining->kategori_risiko === KategoriRisiko::KRST) {
                        $krst++;
                    } elseif ($latestSkrining->kategori_risiko === KategoriRisiko::KRT) {
                        $krt++;
                    } else {
                        $krr++;
                    }
                }

                $latestK = $b->kunjungans->first();
                $effectiveHb = $latestK?->hb ?? $b->hemoglobin;
                if ($effectiveHb !== null && (float) $effectiveHb > 0 && (float) $effectiveHb < 11.0) {
                    $anemia++;
                }

                $effectiveLila = $latestK?->lila ?? $b->lila;
                if ($effectiveLila !== null && (float) $effectiveLila > 0 && (float) $effectiveLila < 23.5) {
                    $kek++;
                }
            }

            $persenSkrining = $countBumil > 0 ? round(($diskrining / $countBumil) * 100, 1) : 0;
            $totalPuskesmas = $groupDesas->pluck('puskesmas')->filter()->unique()->count();

            $clusters[] = [
                'nama' => $groupName,
                'total_puskesmas' => $totalPuskesmas > 0 ? $totalPuskesmas : 1,
                'total_desa' => $groupDesas->count(),
                'total_bumil' => $countBumil,
                'krr' => $krr,
                'krt' => $krt,
                'krst' => $krst,
                'belum_skrining' => max(0, $countBumil - $diskrining),
                'diskrining' => $diskrining,
                'persen_skrining' => $persenSkrining,
                'anemia' => $anemia,
                'kek' => $kek,
                'total_kunjungan' => $countKunjungan,
            ];

            $totalBumilAll += $countBumil;
            $totalKrrAll += $krr;
            $totalKrtAll += $krt;
            $totalKrstAll += $krst;
            $totalAnemiaAll += $anemia;
            $totalKekAll += $kek;
            $totalSkriningBumilAll += $diskrining;
            $totalKunjunganAll += $countKunjungan;
        }

        // Sort by highest risk / highest bumil count
        usort($clusters, function ($a, $b) {
            return ($b['krst'] <=> $a['krst'])
                ?: ($b['krt'] <=> $a['krt'])
                ?: ($b['total_bumil'] <=> $a['total_bumil']);
        });

        $persenSkriningAll = $totalBumilAll > 0 ? round(($totalSkriningBumilAll / $totalBumilAll) * 100, 1) : 0;

        return [
            'level' => 'kabupaten',
            'region_name' => $user->kabupaten ?? 'Kabupaten Indragiri Hulu',
            'summary' => [
                'total_bumil' => $totalBumilAll,
                'total_krr' => $totalKrrAll,
                'total_krt' => $totalKrtAll,
                'total_krst' => $totalKrstAll,
                'total_belum_skrining' => max(0, $totalBumilAll - $totalSkriningBumilAll),
                'total_diskrining' => $totalSkriningBumilAll,
                'persen_skrining' => $persenSkriningAll,
                'total_anemia' => $totalAnemiaAll,
                'total_kek' => $totalKekAll,
                'total_kunjungan' => $totalKunjunganAll,
                'total_cluster' => count($clusters),
                'total_desa' => $desas->count(),
            ],
            'clusters' => $clusters,
        ];
    }

    /**
     * Get aggregated Kunjungan ANC data clustered by region.
     *
     * @return array{
     *     level: string,
     *     region_name: string,
     *     summary: array<string, mixed>,
     *     clusters: array<int, array<string, mixed>>
     * }
     */
    public function getKunjunganAgregat(User $user): array
    {
        $desas = $this->getAccessibleDesas($user);
        $allDesaIds = $desas->pluck('id')->all();

        $kunjungans = Kunjungan::with(['desa', 'ibuHamil.desa'])
            ->whereIn('desa_id', $allDesaIds)
            ->get();

        $groupedDesas = $desas->groupBy(function ($d) {
            $p = trim((string) $d->puskesmas);
            if ($p !== '' && ! preg_match('/^(dinas\s+kesehatan|dinkes)/i', $p)) {
                return $p;
            }

            return 'UPT Puskesmas '.($d->kecamatan ?? 'Wilayah');
        });

        $clusters = [];
        $totalKunjunganAll = 0;
        $totalHadirAll = 0;
        $totalDilayaniAll = 0;
        $totalBelumHadirAll = 0;
        $totalJemputBolaAll = 0;
        $totalPosyanduAll = 0;
        $totalPuskesmasAll = 0;

        foreach ($groupedDesas as $groupName => $groupDesas) {
            $desaIds = $groupDesas->pluck('id')->all();
            $groupKunjungans = $kunjungans->whereIn('desa_id', $desaIds);

            $countTotal = $groupKunjungans->count();
            $hadir = $groupKunjungans->where('status_hadir', true)->count();
            $dilayani = $groupKunjungans->where('status_dilayani', true)->count();
            $belumHadir = $groupKunjungans->where('status_hadir', false)->count();

            $jemputBola = $groupKunjungans->where('metode_kunjungan', 'jemput_bola')->count();
            $posyandu = $groupKunjungans->where('metode_kunjungan', 'posyandu')->count();
            $puskesmas = $groupKunjungans->where('metode_kunjungan', 'puskesmas')->count();

            $persenHadir = $countTotal > 0 ? round(($hadir / $countTotal) * 100, 1) : 0;
            $persenDilayani = $countTotal > 0 ? round(($dilayani / $countTotal) * 100, 1) : 0;

            $clusters[] = [
                'nama' => $groupName,
                'total_desa' => $groupDesas->count(),
                'total_kunjungan' => $countTotal,
                'hadir' => $hadir,
                'dilayani' => $dilayani,
                'belum_hadir' => $belumHadir,
                'persen_hadir' => $persenHadir,
                'persen_dilayani' => $persenDilayani,
                'jemput_bola' => $jemputBola,
                'posyandu' => $posyandu,
                'puskesmas' => $puskesmas,
            ];

            $totalKunjunganAll += $countTotal;
            $totalHadirAll += $hadir;
            $totalDilayaniAll += $dilayani;
            $totalBelumHadirAll += $belumHadir;
            $totalJemputBolaAll += $jemputBola;
            $totalPosyanduAll += $posyandu;
            $totalPuskesmasAll += $puskesmas;
        }

        usort($clusters, function ($a, $b) {
            return ($b['total_kunjungan'] <=> $a['total_kunjungan'])
                ?: ($b['hadir'] <=> $a['hadir']);
        });

        $persenHadirAll = $totalKunjunganAll > 0 ? round(($totalHadirAll / $totalKunjunganAll) * 100, 1) : 0;
        $persenDilayaniAll = $totalKunjunganAll > 0 ? round(($totalDilayaniAll / $totalKunjunganAll) * 100, 1) : 0;

        return [
            'level' => 'kabupaten',
            'region_name' => $user->kabupaten ?? 'Kabupaten Indragiri Hulu',
            'summary' => [
                'total_kunjungan' => $totalKunjunganAll,
                'total_hadir' => $totalHadirAll,
                'total_dilayani' => $totalDilayaniAll,
                'total_belum_hadir' => $totalBelumHadirAll,
                'persen_hadir' => $persenHadirAll,
                'persen_dilayani' => $persenDilayaniAll,
                'total_jemput_bola' => $totalJemputBolaAll,
                'total_posyandu' => $totalPosyanduAll,
                'total_puskesmas' => $totalPuskesmasAll,
                'total_cluster' => count($clusters),
            ],
            'clusters' => $clusters,
        ];
    }

    /**
     * Get aggregated Skrining SPR data clustered by region.
     *
     * @return array{
     *     level: string,
     *     region_name: string,
     *     summary: array<string, mixed>,
     *     clusters: array<int, array<string, mixed>>
     * }
     */
    public function getSkriningAgregat(User $user): array
    {
        $desas = $this->getAccessibleDesas($user);
        $allDesaIds = $desas->pluck('id')->all();

        $skrinings = Skrining::with(['ibuHamil.desa'])
            ->whereHas('ibuHamil', fn ($q) => $q->whereIn('desa_id', $allDesaIds))
            ->get();

        $totalBumils = IbuHamil::whereIn('desa_id', $allDesaIds)
            ->where('is_active', true)
            ->count();

        $groupedDesas = $desas->groupBy(function ($d) {
            $p = trim((string) $d->puskesmas);
            if ($p !== '' && ! preg_match('/^(dinas\s+kesehatan|dinkes)/i', $p)) {
                return $p;
            }

            return 'UPT Puskesmas '.($d->kecamatan ?? 'Wilayah');
        });

        $clusters = [];
        $totalSkriningAll = 0;
        $totalKrrAll = 0;
        $totalKrtAll = 0;
        $totalKrstAll = 0;
        $totalRujukanRsAll = 0;
        $totalRujukanPkmAll = 0;

        foreach ($groupedDesas as $groupName => $groupDesas) {
            $desaIds = $groupDesas->pluck('id')->all();
            $groupSkrinings = $skrinings->filter(fn ($s) => in_array($s->ibuHamil?->desa_id, $desaIds));
            $groupBumilCount = IbuHamil::whereIn('desa_id', $desaIds)->where('is_active', true)->count();

            $countTotal = $groupSkrinings->count();
            $krr = $groupSkrinings->where('kategori_risiko', KategoriRisiko::KRR)->count();
            $krt = $groupSkrinings->where('kategori_risiko', KategoriRisiko::KRT)->count();
            $krst = $groupSkrinings->where('kategori_risiko', KategoriRisiko::KRST)->count();

            // Rujukan analysis: KRST must be referred to Hospital (RS PONEK), KRT to Puskesmas/RS
            $rujukanRs = $krst;
            $rujukanPkm = $krt;

            $persenCakupan = $groupBumilCount > 0 ? min(100, round(($countTotal / $groupBumilCount) * 100, 1)) : 0;

            $clusters[] = [
                'nama' => $groupName,
                'total_desa' => $groupDesas->count(),
                'total_bumil' => $groupBumilCount,
                'total_skrining' => $countTotal,
                'persen_cakupan' => $persenCakupan,
                'krr' => $krr,
                'krt' => $krt,
                'krst' => $krst,
                'rujukan_rs' => $rujukanRs,
                'rujukan_pkm' => $rujukanPkm,
            ];

            $totalSkriningAll += $countTotal;
            $totalKrrAll += $krr;
            $totalKrtAll += $krt;
            $totalKrstAll += $krst;
            $totalRujukanRsAll += $rujukanRs;
            $totalRujukanPkmAll += $rujukanPkm;
        }

        usort($clusters, function ($a, $b) {
            return ($b['krst'] <=> $a['krst'])
                ?: ($b['krt'] <=> $a['krt'])
                ?: ($b['total_skrining'] <=> $a['total_skrining']);
        });

        $persenCakupanAll = $totalBumils > 0 ? min(100, round(($totalSkriningAll / $totalBumils) * 100, 1)) : 0;

        return [
            'level' => 'kabupaten',
            'region_name' => $user->kabupaten ?? 'Kabupaten Indragiri Hulu',
            'summary' => [
                'total_bumil' => $totalBumils,
                'total_skrining' => $totalSkriningAll,
                'persen_cakupan' => $persenCakupanAll,
                'total_krr' => $totalKrrAll,
                'total_krt' => $totalKrtAll,
                'total_krst' => $totalKrstAll,
                'total_rujukan_rs' => $totalRujukanRsAll,
                'total_rujukan_pkm' => $totalRujukanPkmAll,
                'total_cluster' => count($clusters),
            ],
            'clusters' => $clusters,
        ];
    }
}
