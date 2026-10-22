<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Services\RegionalAggregationService;
use App\Services\ReportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        if ($user->isAdminPuskesmas() || $user->isDinkesKabupaten()) {
            $puskesmasDesas = $allDesas->filter(fn ($d) => $user->canAccessDesa($d))->values();
            $puskesmasDesaIds = $puskesmasDesas->pluck('id')->all();
            $otherDesas = $allDesas->reject(fn ($d) => $user->canAccessDesa($d))->values();
        } elseif ($user->isBidan() || $user->isKader()) {
            $puskesmasDesas = $allDesas->where('id', $user->desa_id)->values();
            $puskesmasDesaIds = $user->desa_id ? [$user->desa_id] : [];
            $otherDesas = $allDesas->where('id', '!=', $user->desa_id)->values();
        } else {
            // Dinkes Provinsi (Admin Utama)
            $puskesmasDesas = $allDesas;
            $puskesmasDesaIds = $allDesas->pluck('id')->all();
            $otherDesas = collect();
        }

        $query = Kunjungan::with(['ibuHamil.desa', 'desa', 'user.desa', 'skrining']);

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
            if ($cleanKab && ! $request->filled('search') && ! $request->filled('desa_id') && ! $request->filled('kelompok')) {
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

        if ($user->isAdminPuskesmas() || $user->isDinkesKabupaten()) {
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
            if ($user->isAdminPuskesmas() || $user->isDinkesKabupaten()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->whereIn('desa_id', $puskesmasDesaIds));
            } elseif ($user->isBidan() || $user->isKader()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->where('desa_id', $user->desa_id));
            }
        } elseif ($currentKelompok === 'luar') {
            if ($user->isAdminPuskesmas() || $user->isDinkesKabupaten()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->whereNotIn('desa_id', $puskesmasDesaIds));
            } elseif ($user->isBidan() || $user->isKader()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->where('desa_id', '!=', $user->desa_id));
            }
        }

        $kunjungans = $query->orderBy('tanggal_kunjungan', 'desc')->paginate(15)->withQueryString();
        $desas = $allDesas;

        // Count high risk mothers in jurisdiction for early detection alert banner
        $risikoQuery = IbuHamil::query();
        if ($user->isAdminPuskesmas() || $user->isDinkesKabupaten()) {
            $risikoQuery->whereIn('desa_id', $puskesmasDesaIds);
        } elseif ($user->isBidan() || $user->isKader()) {
            $risikoQuery->where('desa_id', $user->desa_id);
        }
        $countKrst = (clone $risikoQuery)->whereHas('skrinings', fn ($q) => $q->where('kategori_risiko', 'KRST'))->count();
        $countKrt = (clone $risikoQuery)->whereHas('skrinings', fn ($q) => $q->where('kategori_risiko', 'KRT'))->count();

        $agregat = null;
        if ($user->isDinkes()) {
            $agregat = app(RegionalAggregationService::class)->getKunjunganAgregat($user);
        }

        return view('kunjungan.index', compact(
            'kunjungans',
            'desas',
            'puskesmasDesas',
            'otherDesas',
            'puskesmasDesaIds',
            'countSemua',
            'countDalam',
            'countLuar',
            'currentKelompok',
            'countKrst',
            'countKrt',
            'agregat'
        ));
    }

    /**
     * Export Kunjungan ANC list to Excel (.xlsx) based on active filters and jurisdiction.
     */
    public function download(Request $request, ReportExportService $exportService): StreamedResponse
    {
        $user = Auth::user();

        if ($user->isKader()) {
            abort(403, 'Kader tidak memiliki hak akses untuk mengunduh laporan.');
        }

        // 1. Resolve Accessible Desas based on User Role & Jurisdiction
        $allDesas = Desa::with('users')->orderBy('nama')->get();
        $puskesmasDesaIds = [];

        if ($user->isAdminPuskesmas() || $user->isDinkesKabupaten()) {
            $puskesmasDesas = $allDesas->filter(fn ($d) => $user->canAccessDesa($d))->values();
            $puskesmasDesaIds = $puskesmasDesas->pluck('id')->all();
        } elseif ($user->isBidan() || $user->isKader()) {
            $puskesmasDesaIds = $user->desa_id ? [$user->desa_id] : [];
        } else {
            $puskesmasDesaIds = $allDesas->pluck('id')->all();
        }

        $query = Kunjungan::with(['ibuHamil.desa', 'desa', 'user.desa', 'skrining']);

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
            if ($cleanKab && ! $request->filled('search') && ! $request->filled('desa_id') && ! $request->filled('kelompok')) {
                $query->where(function ($q) use ($cleanKab) {
                    $q->whereHas('desa', fn ($d) => $d->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"]))
                        ->orWhereHas('ibuHamil.desa', fn ($d) => $d->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"]));
                });
            }
        }

        // 3. Search Filter
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

        // 4. Other Standard Filters
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

        // 5. Territory Grouping Filter
        $kelompok = $request->input('kelompok');
        if (! $kelompok && $request->input('scope') === 'lintas') {
            $kelompok = 'luar';
        }
        $currentKelompok = $kelompok ?: 'semua';

        if ($currentKelompok === 'dalam') {
            if ($user->isAdminPuskesmas() || $user->isDinkesKabupaten()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->whereIn('desa_id', $puskesmasDesaIds));
            } elseif ($user->isBidan() || $user->isKader()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->where('desa_id', $user->desa_id));
            }
        } elseif ($currentKelompok === 'luar') {
            if ($user->isAdminPuskesmas() || $user->isDinkesKabupaten()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->whereNotIn('desa_id', $puskesmasDesaIds));
            } elseif ($user->isBidan() || $user->isKader()) {
                $query->whereHas('ibuHamil', fn ($b) => $b->where('desa_id', '!=', $user->desa_id));
            }
        }

        $kunjungans = $query->orderBy('tanggal_kunjungan', 'desc')->get();

        $headers = [
            'No',
            'Tanggal Kunjungan',
            'Tahap Kunjungan',
            'Metode Kunjungan',
            'Status Kehadiran',
            'Pelayanan Medis',
            'Nama Ibu Hamil',
            'NIK Ibu Hamil',
            'Nama Suami',
            'No HP',
            'Desa Domisili Ibu',
            'Puskesmas Domisili',
            'Lokasi Kunjungan (Desa)',
            'Petugas Pemeriksa',
            'Peran Petugas',
            'Instansi Petugas',
            'Berat Badan (kg)',
            'Tinggi Badan (cm)',
            'TD Sistol (mmHg)',
            'TD Diastol (mmHg)',
            'LiLA (cm)',
            'TFU (cm)',
            'DJJ (dpm)',
            'Hb (g/dL)',
            'Kepatuhan Tablet Fe',
            'Bentuk Pendampingan',
            'Catatan Kunjungan',
            'Jadwal Kunjungan Berikutnya',
        ];

        $rows = [];
        $no = 1;

        foreach ($kunjungans as $k) {
            $bumil = $k->ibuHamil;
            $metodeLabel = match ($k->metode_kunjungan) {
                'jemput_bola' => 'Jemput Bola (Rumah)',
                'posyandu' => 'Posyandu',
                'puskesmas' => 'Puskesmas / Faskes',
                default => ucfirst(str_replace('_', ' ', (string) $k->metode_kunjungan)),
            };

            $rows[] = [
                $no++,
                $k->tanggal_kunjungan ? $k->tanggal_kunjungan->format('d/m/Y') : '-',
                $k->jenis_kunjungan ?? $k->tahap_anc ?? '-',
                $metodeLabel,
                $k->status_hadir ? 'Hadir' : 'Belum Hadir',
                $k->status_dilayani ? 'Telah Dilayani' : 'Belum Dilayani',
                $bumil?->nama ?? '-',
                $bumil?->nik ? "'".$bumil->nik : '-',
                $bumil?->nama_suami ?? '-',
                $bumil?->no_hp ? "'".$bumil->no_hp : '-',
                $bumil?->desa?->nama ?? '-',
                $bumil?->desa?->puskesmas ?? '-',
                $k->desa?->nama ?? '-',
                $k->user?->name ?? '-',
                $k->user ? $k->user->role->label() : '-',
                $k->user?->instansi ?? '-',
                $k->berat_badan ?? ($bumil?->berat_badan ?? '-'),
                $k->tinggi_badan ?? ($bumil?->tinggi_badan ?? '-'),
                $k->tekanan_darah_sistol ?? ($bumil?->tekanan_darah_sistol ?? '-'),
                $k->tekanan_darah_diastol ?? ($bumil?->tekanan_darah_diastol ?? '-'),
                $k->lila ?? ($bumil?->lila ?? '-'),
                $k->tfu ?? '-',
                $k->djj ?? '-',
                $k->hb ?? ($bumil?->hemoglobin ?? '-'),
                $k->kepatuhan_fe ? 'Patuh' : 'Tidak Patuh',
                $k->bentuk_pendampingan ? ucfirst(str_replace('_', ' ', $k->bentuk_pendampingan)) : '-',
                $k->catatan ?? '-',
                $k->jadwal_kunjungan_berikutnya ? $k->jadwal_kunjungan_berikutnya->format('d/m/Y') : '-',
            ];
        }

        $filename = 'Laporan_Kunjungan_ANC_'.now()->format('Ymd_His').'.xlsx';

        return $exportService->streamDownload($filename, $headers, $rows);
    }

    /**
     * Show the form for creating a new kunjungan.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isDinkes()) {
            return redirect()->route('kunjungan.index')
                ->with('error', 'Pencatatan kunjungan ANC dan jemput bola dilakukan oleh pihak Puskesmas, Bidan Desa, dan Kader.');
        }

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

        if ($user->isDinkes()) {
            abort(403, 'Pencatatan kunjungan ANC dan jemput bola dilakukan oleh pihak Puskesmas, Bidan Desa, dan Kader.');
        }

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
            'alasan_jemput_bola' => ['nullable', 'string', 'max:100'],
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

        $statusHadir = $request->boolean('status_hadir', true);
        // Status dilayani HANYA aktif jika kunjungan ini sudah memiliki skrining SPR miliknya sendiri
        $statusDilayani = false;

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
            'alasan_jemput_bola' => ($validated['metode_kunjungan'] ?? '') === 'jemput_bola' ? ($validated['alasan_jemput_bola'] ?? null) : null,
            'tinggi_badan' => $validated['tinggi_badan'],
            'berat_badan' => $validated['berat_badan'],
            'tekanan_darah_sistol' => $validated['tekanan_darah_sistol'],
            'tekanan_darah_diastol' => $validated['tekanan_darah_diastol'],
            'lila' => $validated['lila'] ?? null,
            'tfu' => $validated['tfu'] ?? null,
            'djj' => $validated['djj'] ?? null,
            'hb' => $validated['hb'] ?? null,
            'kepatuhan_fe' => $request->boolean('kepatuhan_fe'),
            'skrining_id' => null, // Skrining SPR wajib diisi per kunjungan, tidak memakai data kemarin
            'status_hadir' => $statusHadir,
            'status_dilayani' => false,
            'catatan' => $validated['catatan'] ?? null,
            'bentuk_pendampingan' => $validated['bentuk_pendampingan'] ?? null,
            'pendampingan_suami' => $request->boolean('pendampingan_suami'),
            'p4k_siap' => $request->boolean('p4k_siap'),
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

        $kunjungan->load(['ibuHamil', 'skrining']);
        $hasSkrining = $kunjungan->skrining_id !== null;

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
            'alasan_jemput_bola' => ['nullable', 'string', 'max:100'],
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

        $hasSkrining = $kunjungan->skrining_id !== null;
        $hasPhysicalData = ! empty($validated['berat_badan']) && ! empty($validated['tekanan_darah_sistol']);
        $statusHadir = $request->boolean('status_hadir');
        // Status dilayani HANYA jika hadir, data fisik lengkap, dan memiliki skrining SPR kunjungan ini
        $statusDilayani = $hasSkrining && $hasPhysicalData && $statusHadir && $request->boolean('status_dilayani');

        $updateData = [
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'tahap_anc' => $validated['tahap_anc'] ?? $validated['jenis_kunjungan'],
            'metode_kunjungan' => $validated['metode_kunjungan'] ?? ($kunjungan->metode_kunjungan ?? 'posyandu'),
            'alasan_jemput_bola' => ($validated['metode_kunjungan'] ?? ($kunjungan->metode_kunjungan ?? '')) === 'jemput_bola' ? ($validated['alasan_jemput_bola'] ?? $kunjungan->alasan_jemput_bola) : null,
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
            'pendampingan_suami' => $request->boolean('pendampingan_suami'),
            'p4k_siap' => $request->boolean('p4k_siap'),
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

        if ($user->isDinkes()) {
            abort(403, 'Penghapusan data kunjungan klinis tidak dapat dilakukan oleh akun Dinas.');
        }

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

        if ($user->isDinkes()) {
            abort(403, 'Konfirmasi kehadiran dan pengukuran fisik 10T dilakukan oleh pihak Puskesmas, Bidan Desa, dan Kader.');
        }

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
            'tinggi_badan' => [
                Rule::requiredIf(fn () => (bool) $request->input('status_hadir')),
                'nullable', 'numeric', 'min:50', 'max:250',
            ],
            'berat_badan' => [
                Rule::requiredIf(fn () => (bool) $request->input('status_hadir')),
                'nullable', 'numeric', 'min:20', 'max:300',
            ],
            'tekanan_darah_sistol' => [
                Rule::requiredIf(fn () => (bool) $request->input('status_hadir')),
                'nullable', 'integer', 'min:50', 'max:300',
            ],
            'tekanan_darah_diastol' => [
                Rule::requiredIf(fn () => (bool) $request->input('status_hadir')),
                'nullable', 'integer', 'min:30', 'max:200',
            ],
            'hb' => [
                Rule::requiredIf(fn () => (bool) $request->input('status_hadir')),
                'nullable', 'numeric', 'min:3', 'max:25',
            ],
            'lila' => [
                Rule::requiredIf(fn () => (bool) $request->input('status_hadir')),
                'nullable', 'numeric', 'min:10', 'max:50',
            ],
            'tfu' => ['nullable', 'numeric', 'min:5', 'max:50'],
            'djj' => ['nullable', 'integer', 'min:50', 'max:220'],
            'kepatuhan_fe' => ['nullable', 'boolean'],
            'bentuk_pendampingan' => ['nullable', 'string'],
            'jadwal_kunjungan_berikutnya' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'action' => ['nullable', 'string', 'in:save,save_and_screen'],
        ], [
            'tinggi_badan.required' => 'Tinggi Badan (TB) wajib diisi untuk konfirmasi kehadiran.',
            'berat_badan.required' => 'Berat Badan (BB) wajib diisi untuk konfirmasi kehadiran.',
            'tekanan_darah_sistol.required' => 'Tekanan Darah Sistol wajib diisi untuk konfirmasi kehadiran.',
            'tekanan_darah_diastol.required' => 'Tekanan Darah Diastol wajib diisi untuk konfirmasi kehadiran.',
            'hb.required' => 'Kadar Hemoglobin (Hb) wajib diisi untuk konfirmasi kehadiran.',
            'lila.required' => 'Lingkar Lengan Atas (LiLA) wajib diisi untuk konfirmasi kehadiran.',
        ]);

        $hasSkrining = $kunjungan->skrining_id !== null;
        $effectiveBb = $validated['berat_badan'] ?? $kunjungan->berat_badan;
        $effectiveSistol = $validated['tekanan_darah_sistol'] ?? $kunjungan->tekanan_darah_sistol;
        $hasPhysicalData = ! empty($effectiveBb) && ! empty($effectiveSistol);

        $statusHadir = (bool) $validated['status_hadir'];
        // Status dilayani HANYA aktif jika hadir, data fisik ada, dan skrining SPR kunjungan ini sudah terisi
        $statusDilayani = $statusHadir && $hasPhysicalData && $hasSkrining && ! empty($validated['status_dilayani']);

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

        if ($statusHadir && $kunjungan->skrining_id === null) {
            return redirect()->route('skrining.create', [
                'ibu_hamil_id' => $ibuHamil->id,
                'kunjungan_id' => $kunjungan->id,
                'tanggal' => $kunjungan->tanggal_kunjungan?->format('Y-m-d') ?? now()->format('Y-m-d'),
            ])->with('success', "Konfirmasi kehadiran dan pemeriksaan fisik ({$kunjungan->jenis_kunjungan}) untuk {$ibuHamil->nama} berhasil disimpan. Silakan lengkapi Skrining SPR kunjungan ini.");
        }

        $statusLabel = $statusHadir
            ? ($statusDilayani ? 'Hadir & Telah Dilayani Lengkap' : ($hasPhysicalData ? 'Hadir & Diperiksa (Belum Skrining SPR Kunjungan Ini)' : 'Hadir (Data Belum Diisi)'))
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
