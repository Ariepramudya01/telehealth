@extends('layouts.app', ['title' => 'Catatan Kunjungan ANC & Jemput Bola', 'header' => 'Pencatatan Kunjungan ANC & Jemput Bola'])

@section('content')
    <div class="space-y-6" x-data="{
        modalOpen: false,
        activeKunjunganId: null,
        activeNama: '',
        activeJenis: '',
        activeMetode: 'jemput_bola',
        activeTanggal: '',
        activeCatatan: '',
        activeHadir: true,
        activeDilayani: true,
        activeTb: '',
        activeBb: '',
        activeSistol: '',
        activeDiastol: '',
        activeLila: '',
        activeTfu: '',
        activeDjj: '',
        activeHb: '',
        activeKepatuhanFe: true,
        activeBentuk: '',
        activeJadwalBerikutnya: '',
        activeHasSkrining: false,
        activeBumilId: null,
        openConfirmModal(id, nama, jenis, metode, tanggal, catatan = '', hadir = true, dilayani = true, tb = '', bb = '', sistol = '', diastol = '', lila = '', tfu = '', djj = '', hb = '', kepatuhanFe = true, bentuk = '', jadwalBerikutnya = '', hasSkrining = false, bumilId = null) {
            this.activeKunjunganId = id;
            this.activeNama = nama;
            this.activeJenis = jenis;
            this.activeMetode = metode || 'jemput_bola';
            this.activeTanggal = tanggal;
            this.activeCatatan = catatan;
            this.activeHadir = Boolean(hadir);
            this.activeDilayani = Boolean(dilayani);
            this.activeTb = tb;
            this.activeBb = bb;
            this.activeSistol = sistol;
            this.activeDiastol = diastol;
            this.activeLila = lila;
            this.activeTfu = tfu;
            this.activeDjj = djj;
            this.activeHb = hb;
            this.activeKepatuhanFe = Boolean(kepatuhanFe);
            this.activeBentuk = bentuk;
            this.activeJadwalBerikutnya = jadwalBerikutnya;
            this.activeHasSkrining = Boolean(hasSkrining);
            this.activeBumilId = bumilId;
            this.modalOpen = true;
        }
    }">

    @if (auth()->user()->isDinkes() && isset($agregat))
        <!-- ============================================================== -->
        <!-- TAMPILAN KHUSUS DINAS KESEHATAN (PROVINSI & KABUPATEN)           -->
        <!-- REKAPITULASI KLASTER KUNJUNGAN ANC (MONITORING & AGREGAT)       -->
        <!-- ============================================================== -->

        <!-- Header Actions: Mode Agregat Kunjungan -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $agregat['level'] === 'provinsi' ? 'bg-purple-100 text-purple-900 border border-purple-200' : 'bg-teal-100 text-teal-900 border border-teal-200' }}">
                        🏛️ Rekapitulasi Kunjungan {{ $agregat['level'] === 'provinsi' ? 'Tingkat Provinsi' : 'Tingkat Kabupaten' }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Monitoring Capaian ANC & Jemput Bola</span>
                </div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                    Capaian Kunjungan ANC {{ $agregat['region_name'] }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Data agregat pelaksanaan kunjungan ibu hamil per {{ $agregat['level'] === 'provinsi' ? 'kabupaten / kota se-provinsi' : 'puskesmas di ' . $agregat['region_name'] }} untuk monitoring keaktifan faskes dan pendampingan jemput bola.
                </p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('kunjungan.download', request()->query()) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold shadow-xs transition-colors"
                    title="Unduh Rekapitulasi Kunjungan ANC format Excel (.xlsx)">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Excel Agregat</span>
                </a>
            </div>
        </div>

        <!-- Banner Info Wewenang Klinis -->
        <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-950 flex items-start gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 font-bold text-sm shadow-xs">
                ℹ️
            </div>
            <div class="text-xs text-blue-900 leading-relaxed">
                <span class="font-bold">Informasi Pembagian Peran Sistem:</span>
                Pencatatan tanggal kunjungan, konfirmasi kehadiran, dan pengukuran fisik biometrik 10T (TB, BB, TD, Hb, LiLA) dilakukan langsung oleh pihak <strong>UPT Puskesmas, Bidan Desa, dan Kader</strong> saat pelayanan di faskes maupun jemput bola ke rumah ibu hamil.
            </div>
        </div>

        <!-- Metric Summary Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Kunjungan -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-teal-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Kunjungan ANC</span>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($agregat['summary']['total_kunjungan']) }}</h3>
                        <span class="text-xs text-slate-400 mt-1 block">Pelayanan Faskes & Rumah</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl font-bold">
                        🗓️
                    </div>
                </div>
            </div>

            <!-- Hadir & Diperiksa -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-emerald-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Hadir & Diperiksa</span>
                        <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ number_format($agregat['summary']['total_hadir']) }}</h3>
                        <span class="text-xs text-emerald-700 font-bold mt-1 block">{{ $agregat['summary']['persen_hadir'] }}% Angka Kehadiran</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        ✓
                    </div>
                </div>
                <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ min(100, $agregat['summary']['persen_hadir']) }}%"></div>
                </div>
            </div>

            <!-- Belum Hadir / Terjadwal -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-amber-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600">Belum Hadir / Terjadwal</span>
                        <h3 class="text-3xl font-extrabold text-amber-600 mt-1">{{ number_format($agregat['summary']['total_belum_hadir']) }}</h3>
                        <span class="text-xs text-slate-500 mt-1 block">Perlu pendampingan kader</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        ⏳
                    </div>
                </div>
            </div>

            <!-- Model Jemput Bola Kader -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-indigo-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Jemput Bola (Rumah)</span>
                        <h3 class="text-3xl font-extrabold text-indigo-600 mt-1">{{ number_format($agregat['summary']['total_jemput_bola']) }}</h3>
                        <span class="text-xs text-slate-500 mt-1 block">Posyandu: {{ $agregat['summary']['total_posyandu'] }} • Puskesmas: {{ $agregat['summary']['total_puskesmas'] }}</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        🚗
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Rekapitulasi Klaster Kunjungan -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
                <div>
                    <h3 class="text-base font-bold text-slate-800">
                        Tabel Capaian Kunjungan Berdasarkan Klaster {{ $agregat['level'] === 'provinsi' ? 'Kabupaten / Kota' : 'Puskesmas' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Rekapitulasi status kehadiran, pelayanan medis, dan metode pendampingan lapangan
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                    Total {{ count($agregat['clusters']) }} Klaster Wilayah
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">{{ $agregat['level'] === 'provinsi' ? 'Kabupaten / Kota' : 'Puskesmas / Wilayah' }}</th>
                            <th class="py-3 px-4 text-center">Desa Binaan</th>
                            <th class="py-3 px-4 text-center">Total Kunjungan</th>
                            <th class="py-3 px-4 text-center">Hadir & Diperiksa (%)</th>
                            <th class="py-3 px-4 text-center">Dilayani Lengkap (%)</th>
                            <th class="py-3 px-4 text-center">Belum Hadir</th>
                            <th class="py-3 px-4 text-center">🏠 Jemput Bola</th>
                            <th class="py-3 px-4 text-center">👥 Posyandu</th>
                            <th class="py-3 px-4 text-center">🏥 Puskesmas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($agregat['clusters'] as $index => $c)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    {{ $c['nama'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-600 font-semibold">
                                    {{ $c['total_desa'] }} Desa
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-900">
                                    {{ number_format($c['total_kunjungan']) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $c['persen_hadir'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($c['persen_hadir'] >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $c['hadir'] }} ({{ $c['persen_hadir'] }}%)
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        {{ $c['dilayani'] }} ({{ $c['persen_dilayani'] }}%)
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-semibold text-slate-600">
                                    {{ $c['belum_hadir'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-amber-700">
                                    {{ $c['jemput_bola'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-600">
                                    {{ $c['posyandu'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-600">
                                    {{ $c['puskesmas'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-8 text-center text-slate-400">Belum ada catatan kunjungan pada klaster wilayah ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-100/80 font-bold border-t-2 border-slate-300 text-slate-800">
                        <tr>
                            <td class="py-3.5 px-4" colspan="2">TOTAL KESELURUHAN</td>
                            <td class="py-3.5 px-4 text-center">-</td>
                            <td class="py-3.5 px-4 text-center text-teal-800 font-extrabold">{{ number_format($agregat['summary']['total_kunjungan']) }}</td>
                            <td class="py-3.5 px-4 text-center text-emerald-700 font-extrabold">{{ number_format($agregat['summary']['total_hadir']) }} ({{ $agregat['summary']['persen_hadir'] }}%)</td>
                            <td class="py-3.5 px-4 text-center text-blue-700 font-extrabold">{{ number_format($agregat['summary']['total_dilayani']) }} ({{ $agregat['summary']['persen_dilayani'] }}%)</td>
                            <td class="py-3.5 px-4 text-center text-amber-700 font-extrabold">{{ number_format($agregat['summary']['total_belum_hadir']) }}</td>
                            <td class="py-3.5 px-4 text-center text-amber-800 font-extrabold">{{ number_format($agregat['summary']['total_jemput_bola']) }}</td>
                            <td class="py-3.5 px-4 text-center font-extrabold">{{ number_format($agregat['summary']['total_posyandu']) }}</td>
                            <td class="py-3.5 px-4 text-center font-extrabold">{{ number_format($agregat['summary']['total_puskesmas']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    @else
        <!-- ============================================================== -->
        <!-- TAMPILAN OPERASIONAL LAPANGAN: PUSKESMAS, BIDAN DESA, & KADER   -->
        <!-- ============================================================== -->

        <!-- Header Actions -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800">
                        Model Jemput Bola ANC
                    </span>
                    <span class="text-xs text-slate-500">Pendampingan Kader Lintas Daerah</span>
                </div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Daftar Kunjungan & Pendampingan ANC</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau status kehadiran, metode kunjungan rumah/posyandu, dan
                    pelayanan medis ibu hamil</p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                @if (! auth()->user()->isKader())
                    <a href="{{ route('kunjungan.download', request()->query()) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold shadow-xs transition-colors"
                        title="Unduh Data Kunjungan ANC format Excel (.xlsx) sesuai filter aktif">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh Excel</span>
                    </a>
                @endif
                <a href="{{ route('kunjungan.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 active:scale-95 text-white text-xs font-bold shadow-sm shadow-teal-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Catat Kunjungan / Jemput Bola</span>
                </a>
            </div>
        </div>

        <!-- Alert Siaga Deteksi Dini Risiko Tinggi -->
        @if (($countKrst ?? 0) > 0 || ($countKrt ?? 0) > 0)
            <div class="p-4 rounded-3xl bg-gradient-to-r from-rose-50 via-amber-50 to-white border border-rose-200 text-rose-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 text-xl font-bold shadow-xs animate-pulse">
                        ⚠️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-sm text-rose-950">Peringatan Siaga Deteksi Dini Risiko Tinggi</h4>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-200 text-rose-900 border border-rose-300">
                                Waspada Komplikasi
                            </span>
                        </div>
                        <p class="text-xs text-rose-800 mt-0.5">
                            Terdeteksi <strong>{{ $countKrst ?? 0 }}</strong> ibu hamil <strong>KRST (Sangat Tinggi)</strong> dan <strong>{{ $countKrt ?? 0 }}</strong> ibu hamil <strong>KRT (Tinggi)</strong> di wilayah kerja Anda.
                            Prioritaskan pendampingan rumah model jemput bola oleh kader dan tele-edukasi rutin.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('ibu-hamil.index', ['risiko' => 'KRST']) }}" 
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition-colors">
                        <span>Pantau Pasien KRST ({{ $countKrst ?? 0 }})</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- Filter Bar & Grouping Tabs -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">

            <!-- Territory Grouping Navigation Tabs -->
            @if (auth()->user()->isAdminPuskesmas())
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Wilayah Puskesmas:</span>
                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'semua', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <span>📋 Semua Kunjungan</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $countSemua }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'dalam', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200/80' }}">
                        <span>Dalam Wilayah Puskesmas</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-white/20 text-white' : 'bg-emerald-200 text-emerald-900' }}">{{ $countDalam }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'luar', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200/80' }}">
                        <span>Di Luar Wilayah / Lintas Faskes</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-900' }}">{{ $countLuar }}</span>
                    </a>
                </div>
            @elseif (auth()->user()->isDinkesKabupaten())
                @php
                    $cleanKab = ucwords(strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) (auth()->user()->kabupaten ?? 'Kabupaten')))));
                @endphp
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Wilayah Kabupaten:</span>
                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'semua', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <span>📋 Semua Kunjungan</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $countSemua }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'dalam', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200/80' }}">
                        <span>🏢 Dalam Wilayah Kab. {{ $cleanKab }}</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-white/20 text-white' : 'bg-emerald-200 text-emerald-900' }}">{{ $countDalam }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'luar', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200/80' }}">
                        <span>🌐 Di Luar Wilayah Kab. {{ $cleanKab }} / Lintas Daerah</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-900' }}">{{ $countLuar }}</span>
                    </a>
                </div>
            @elseif (auth()->user()->isBidan() || auth()->user()->isKader())
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Domisili Warga:</span>
                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'semua', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <span>📋 Semua Kunjungan</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $countSemua }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'dalam', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-teal-600 text-white shadow-xs' : 'bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/80' }}">
                        <span>🏡 Warga Desa Kita ({{ auth()->user()->desa?->nama ?? 'Binaan' }})</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-white/20 text-white' : 'bg-teal-200 text-teal-900' }}">{{ $countDalam }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'luar', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-800 hover:bg-purple-100 border border-purple-200/80' }}">
                        <span>🌐 Warga Luar Daerah / Lintas Desa</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-white/20 text-white' : 'bg-purple-200 text-purple-900' }}">{{ $countLuar }}</span>
                    </a>
                </div>
            @endif

            <form method="GET" action="{{ route('kunjungan.index') }}" class="space-y-3">
                <input type="hidden" name="kelompok" value="{{ request('kelompok', $currentKelompok ?? 'semua') }}" />

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <!-- Search Input -->
                    <div class="relative sm:col-span-2 lg:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama ibu hamil, NIK 16 digit, suami..."
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-800 py-2.5 pl-9 pr-8 focus:bg-white focus:ring-teal-500 focus:border-teal-500 shadow-xs placeholder-slate-400" />
                        @if (request('search'))
                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>

                    @if (auth()->user()->isAdmin())
                        <div>
                            <select name="desa_id" onchange="this.form.submit()"
                                class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                                <option value="">-- Semua Wilayah Desa --</option>
                                @if (auth()->user()->isAdminPuskesmas())
                                    @if (isset($puskesmasDesas) && $puskesmasDesas->count() > 0)
                                        <optgroup
                                            label="📍 Desa Wilayah Kerja Puskesmas ({{ auth()->user()->puskesmas_kerja ?? 'UPT' }})">
                                            @foreach ($puskesmasDesas as $d)
                                                <option value="{{ $d->id }}"
                                                    {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                                    {{ $d->nama }} ({{ $d->kecamatan }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if (isset($otherDesas) && $otherDesas->count() > 0)
                                        <optgroup label="🌐 Desa Luar Wilayah / Lintas Faskes">
                                            @foreach ($otherDesas as $d)
                                                <option value="{{ $d->id }}"
                                                    {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                                    {{ $d->nama }} ({{ $d->puskesmas ?? 'Luar Faskes' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @elseif (auth()->user()->isDinkesKabupaten())
                                    @php
                                        $kabLabel = ucwords(strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) (auth()->user()->kabupaten ?? 'Kabupaten')))));
                                    @endphp
                                    @if (isset($puskesmasDesas) && $puskesmasDesas->count() > 0)
                                        <optgroup label="📍 Desa di Dalam Wilayah Kab. {{ $kabLabel }}">
                                            @foreach ($puskesmasDesas as $d)
                                                <option value="{{ $d->id }}"
                                                    {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                                    {{ $d->nama }} (Kec. {{ $d->kecamatan ?? '-' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if (isset($otherDesas) && $otherDesas->count() > 0)
                                        <optgroup label="🌐 Desa di Luar Wilayah Kab. {{ $kabLabel }} (Lintas Daerah)">
                                            @foreach ($otherDesas as $d)
                                                @php
                                                    $otherKab = ucwords(strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) ($d->kabupaten ?? '')))));
                                                @endphp
                                                <option value="{{ $d->id }}"
                                                    {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                                    {{ $d->nama }} ({{ $otherKab ? 'Kab. ' . $otherKab : ($d->puskesmas ?? 'Luar Kab') }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @else
                                    @foreach ($desas as $d)
                                        <option value="{{ $d->id }}"
                                            {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                            {{ $d->nama }} ({{ $d->puskesmas ?? 'Puskesmas' }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif

                    <div>
                        <select name="metode" onchange="this.form.submit()"
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                            <option value="">-- Semua Metode --</option>
                            <option value="jemput_bola" {{ request('metode') === 'jemput_bola' ? 'selected' : '' }}>🏠
                                Jemput Bola (Rumah)</option>
                            <option value="posyandu" {{ request('metode') === 'posyandu' ? 'selected' : '' }}>👥 Posyandu
                            </option>
                            <option value="puskesmas" {{ request('metode') === 'puskesmas' ? 'selected' : '' }}>🏥
                                Puskesmas / Faskes</option>
                        </select>
                    </div>

                    <div>
                        <select name="jenis" onchange="this.form.submit()"
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                            <option value="">-- Semua Tahap (K1 - K6) --</option>
                            @foreach (['K1', 'K2', 'K3', 'K4', 'K5', 'K6'] as $k)
                                <option value="{{ $k }}" {{ request('jenis') === $k ? 'selected' : '' }}>
                                    Kunjungan {{ $k }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="status" onchange="this.form.submit()"
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                            <option value="">-- Semua Status --</option>
                            <option value="belum_hadir" {{ request('status') === 'belum_hadir' ? 'selected' : '' }}>Belum
                                Hadir / Terjadwal</option>
                            <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir / Selesai
                                Kunjungan</option>
                            <option value="dilayani" {{ request('status') === 'dilayani' ? 'selected' : '' }}>Dilayani
                                Lengkap</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                    <div class="text-[11px] text-slate-500">
                        Menampilkan <strong class="text-slate-800">{{ $kunjungans->total() }}</strong> catatan kunjungan
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs">
                            Terapkan Filter
                        </button>
                        @if (request()->hasAny(['search', 'desa_id', 'metode', 'jenis', 'status', 'kelompok', 'scope']))
                            <a href="{{ route('kunjungan.index') }}"
                                class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200">
                                Reset Filter
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Kunjungan Content (Responsive Mobile Cards & Desktop Table) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Mobile Cards View (Tampilan Khusus Layar HP Bidan & Kader) -->
            <div class="block lg:hidden divide-y divide-slate-100">
                @forelse($kunjungans as $k)
                    @php
                        $hasPhysicalData = ! empty($k->berat_badan) && ! empty($k->tekanan_darah_sistol);
                        $hasSkriningKunjungan = $k->skrining_id !== null;

                        $tbVal = $k->tinggi_badan;
                        $bbVal = $k->berat_badan;
                        $sistolVal = $k->tekanan_darah_sistol;
                        $diastolVal = $k->tekanan_darah_diastol;
                        $lilaVal = $k->lila;
                        $tfuVal = $k->tfu;
                        $djjVal = $k->djj;
                        $hbVal = $k->hb;
                        $kepatuhanFeVal = $k->kepatuhan_fe ? 1 : 0;
                        $jadwalNextVal = $k->jadwal_kunjungan_berikutnya
                            ? $k->jadwal_kunjungan_berikutnya->format('Y-m-d')
                            : '';

                        $modalTb = $tbVal ?? '';
                        $modalBb = $bbVal ?? '';
                        $modalSistol = $sistolVal ?? '';
                        $modalDiastol = $diastolVal ?? '';
                        $modalLila = $lilaVal ?? '';
                        $modalHb = $hbVal ?? '';

                        $rawPhone = preg_replace('/[^0-9]/', '', $k->ibuHamil->no_hp ?? '');
                        $waPhone = ($rawPhone && str_starts_with($rawPhone, '0')) ? '62' . substr($rawPhone, 1) : $rawPhone;
                        $tglJadwal = $k->jadwal_kunjungan_berikutnya ? $k->jadwal_kunjungan_berikutnya->translatedFormat('d F Y') : ($k->tanggal_kunjungan ? $k->tanggal_kunjungan->translatedFormat('d F Y') : '-');
                        $waTelehealthMsg = urlencode("Halo Ibu {$k->ibuHamil->nama},\n\nSalam sehat dari Tim Pendampingan Telehealth ANC Puskesmas " . ($k->desa?->puskesmas ?? 'Puskesmas') . " / Desa " . ($k->desa?->nama ?? '') . ".\n\nKami mengingatkan jadwal kunjungan pemantauan ANC / Jemput Bola berikutnya pada hari *{$tglJadwal}*.\n\n📌 *Pesan Pemantauan Kesehatan:*\n1. Pastikan rutin meminum tablet tambah darah (Fe) 1 tablet setiap malam.\n2. Tetap cukupi asupan cairan dan makanan bergizi seimbang.\n3. Waspadai tanda bahaya: pusing hebat berkunang, pandangan kabur, bengkak pada kaki/tangan, atau keluar flek darah.\n\nJika ada keluhan, jangan ragu untuk segera menghubungi kami atau Bidan Desa. Semoga ibu dan kandungan selalu sehat!");
                    @endphp
                    <div class="p-4 space-y-3 hover:bg-slate-50/50 transition-colors">
                        <!-- Header Card: Tanggal, Metode, Tahap ANC -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs font-bold text-slate-800">
                                    {{ $k->tanggal_kunjungan->translatedFormat('d M Y') }}
                                </span>
                                @if ($k->metode_kunjungan === 'jemput_bola')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        🏠 Jemput Bola
                                    </span>
                                @elseif ($k->metode_kunjungan === 'puskesmas')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                        🏥 Puskesmas
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">
                                        👥 Posyandu
                                    </span>
                                @endif
                            </div>
                            <span class="px-2.5 py-0.5 rounded-lg font-mono font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                {{ $k->tahap_anc ?? $k->jenis_kunjungan }}
                            </span>
                        </div>

                        <!-- Info Pasien & Domisili -->
                        <div>
                            <a href="{{ route('ibu-hamil.show', $k->ibuHamil) }}" class="font-bold text-slate-900 text-sm hover:text-teal-700 block">
                                {{ $k->ibuHamil->nama }}
                            </a>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                NIK: <span class="font-mono text-slate-700 font-semibold">{{ $k->ibuHamil->nik ?? '-' }}</span>
                                @if($k->ibuHamil->nama_suami) • Suami: {{ $k->ibuHamil->nama_suami }} @endif
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap mt-1 text-[11px] text-slate-600">
                                <span>📍 {{ $k->desa?->nama }} ({{ $k->desa?->puskesmas ?? 'Puskesmas' }})</span>
                                @if (auth()->user()->isAdminPuskesmas())
                                    @if (in_array($k->ibuHamil->desa_id, $puskesmasDesaIds ?? []))
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800">
                                            Wilayah PKM
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800">
                                            Luar PKM
                                        </span>
                                    @endif
                                @elseif (auth()->user()->isDinkesKabupaten())
                                    @php
                                        $kabBadge = ucwords(strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) (auth()->user()->kabupaten ?? 'Kabupaten')))));
                                    @endphp
                                    @if (in_array($k->ibuHamil->desa_id, $puskesmasDesaIds ?? []))
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800">
                                            Wilayah Kab. {{ $kabBadge }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800">
                                            🌐 Luar Wilayah Kab.
                                        </span>
                                    @endif
                                @elseif (auth()->user()->isBidan() || auth()->user()->isKader())
                                    @if ($k->ibuHamil->desa_id === auth()->user()->desa_id)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-teal-100 text-teal-800">
                                            Desa Kita
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-purple-100 text-purple-800">
                                            Lintas Desa
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Ringkasan Fisik / Klinis (10T) -->
                        <div class="p-2.5 bg-slate-50 rounded-xl text-slate-700 text-xs">
                            @if ($hasPhysicalData)
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block uppercase font-medium">TB / BB</span>
                                        <span class="font-semibold">{{ $tbVal ? $tbVal . ' cm' : '-' }} / {{ $bbVal ? $bbVal . ' kg' : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Tekanan Darah</span>
                                        <span class="font-semibold">{{ $sistolVal && $diastolVal ? "{$sistolVal}/{$diastolVal} mmHg" : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block uppercase font-medium">LiLA / Hb</span>
                                        <span class="font-semibold">
                                            {{ $lilaVal ? $lilaVal . ' cm' : '-' }} / {{ $hbVal ? $hbVal . ' g/dL' : '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Kepatuhan Fe</span>
                                        <span class="font-semibold {{ $k->kepatuhan_fe ? 'text-emerald-700' : 'text-slate-500' }}">
                                            {{ $k->kepatuhan_fe ? '✓ Rutin Minum' : 'Belum / Tidak' }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center justify-between text-amber-800">
                                    <span class="flex items-center gap-1.5 text-xs font-semibold">
                                        <span>⚠️</span>
                                        <span>Data pemeriksaan fisik belum diisi</span>
                                    </span>
                                    <span class="text-[10px] text-amber-700 underline font-medium">Perlu Pengukuran</span>
                                </div>
                            @endif
                        </div>

                        <!-- Status & Jadwal Berikutnya -->
                        <div class="flex items-center justify-between gap-2 pt-1 text-xs">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if ($k->status_hadir)
                                    @if (!$hasPhysicalData)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                            ⚠️ Hadir (Data Belum Diisi)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                            ✓ Hadir & Diperiksa
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        ⏳ Terjadwal
                                    </span>
                                @endif

                                @if ($k->status_dilayani && $hasSkriningKunjungan)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Dilayani Lengkap
                                    </span>
                                    @if ($k->skrining)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold {{ $k->skrining->kategori_risiko->badgeClasses() }}">
                                            {{ $k->skrining->kategori_risiko->value }} ({{ $k->skrining->total_skor }})
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        Belum Skrining SPR
                                    </span>
                                @endif
                            </div>

                            @if ($k->jadwal_kunjungan_berikutnya)
                                <span class="text-[11px] font-medium text-indigo-700">
                                    🗓️ {{ $k->jadwal_kunjungan_berikutnya->translatedFormat('d M Y') }}
                                </span>
                            @endif
                        </div>

                        <!-- Tombol Aksi HP (Touch-Friendly) -->
                        <div class="pt-2 flex flex-col gap-2">
                            <div class="grid grid-cols-2 gap-2">
                                @if ($waPhone)
                                    <a href="https://api.whatsapp.com/send?phone={{ $waPhone }}&text={{ $waTelehealthMsg }}" target="_blank"
                                        class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all">
                                        <span>📱 Kirim WA</span>
                                    </a>
                                @else
                                    <span class="flex items-center justify-center py-2.5 px-3 rounded-xl bg-slate-100 text-slate-400 font-medium text-xs">
                                        No HP (-)
                                    </span>
                                @endif

                                @if (!$k->status_hadir)
                                    <button type="button"
                                        @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->metode_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, false, '{{ $modalTb }}', '{{ $modalBb }}', '{{ $modalSistol }}', '{{ $modalDiastol }}', '{{ $modalLila }}', '{{ $tfuVal }}', '{{ $djjVal }}', '{{ $modalHb }}', {{ $kepatuhanFeVal }}, '{{ addslashes($k->bentuk_pendampingan ?? '') }}', '{{ $jadwalNextVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                        class="flex items-center justify-center gap-1 py-2.5 px-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                        <span>✓ Hadirkan & Ukur</span>
                                    </button>
                                @elseif (!$hasPhysicalData)
                                    <button type="button"
                                        @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->metode_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, false, '{{ $modalTb }}', '{{ $modalBb }}', '{{ $modalSistol }}', '{{ $modalDiastol }}', '{{ $modalLila }}', '{{ $tfuVal }}', '{{ $djjVal }}', '{{ $modalHb }}', {{ $kepatuhanFeVal }}, '{{ addslashes($k->bentuk_pendampingan ?? '') }}', '{{ $jadwalNextVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                        class="flex items-center justify-center gap-1 py-2.5 px-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition-all">
                                        <span>⚠️ Input Data Fisik</span>
                                    </button>
                                @elseif (!$hasSkriningKunjungan)
                                    <a href="{{ route('skrining.create', ['ibu_hamil_id' => $k->ibu_hamil_id, 'kunjungan_id' => $k->id, 'tanggal' => $k->tanggal_kunjungan->format('Y-m-d')]) }}"
                                        class="flex items-center justify-center gap-1 py-2.5 px-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                        <span>📋 + Skrining SPR</span>
                                    </a>
                                @else
                                    <button type="button"
                                        @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->metode_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $k->status_dilayani ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', '{{ $lilaVal }}', '{{ $tfuVal }}', '{{ $djjVal }}', '{{ $hbVal }}', {{ $kepatuhanFeVal }}, '{{ addslashes($k->bentuk_pendampingan ?? '') }}', '{{ $jadwalNextVal }}', true, {{ $k->ibu_hamil_id }})"
                                        class="flex items-center justify-center gap-1 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-200 transition-all">
                                        <span>Ubah Pelayanan</span>
                                    </button>
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-xs pt-1 px-1">
                                <a href="{{ route('kunjungan.edit', $k) }}" class="text-blue-600 hover:text-blue-800 font-semibold py-1">
                                    ✏️ Edit Lengkap
                                </a>
                                @if (!$hasSkriningKunjungan)
                                    <a href="{{ route('skrining.create', ['ibu_hamil_id' => $k->ibu_hamil_id, 'kunjungan_id' => $k->id, 'tanggal' => $k->tanggal_kunjungan->format('Y-m-d')]) }}" class="text-teal-700 font-bold hover:underline py-1">
                                        + Isi Skrining SPR
                                    </a>
                                @endif
                                @if (!auth()->user()->isKader())
                                    <form action="{{ route('kunjungan.destroy', $k) }}" method="POST"
                                        onsubmit="return confirm('Hapus catatan kunjungan ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-medium py-1">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                                @if (!auth()->user()->isKader())
                                    <form action="{{ route('kunjungan.destroy', $k) }}" method="POST"
                                        onsubmit="return confirm('Hapus catatan kunjungan ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-medium py-1">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada data catatan kunjungan ANC.
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal & Metode</th>
                            <th class="py-3 px-4">Nama Ibu Hamil</th>
                            <th class="py-3 px-4">Wilayah / Puskesmas</th>
                            <th class="py-3 px-4 text-center">Tahap ANC</th>
                            <th class="py-3 px-4">Pemeriksaan Fisik</th>
                            <th class="py-3 px-4 text-center">Jadwal Berikutnya</th>
                            <th class="py-3 px-4 text-center">Status Kehadiran</th>
                            <th class="py-3 px-4 text-center">Status Pelayanan</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($kunjungans as $k)
                            @php
                                $hasPhysicalData = ! empty($k->berat_badan) && ! empty($k->tekanan_darah_sistol);
                                $hasSkriningKunjungan = $k->skrining_id !== null;

                                $tbVal = $k->tinggi_badan;
                                $bbVal = $k->berat_badan;
                                $sistolVal = $k->tekanan_darah_sistol;
                                $diastolVal = $k->tekanan_darah_diastol;
                                $lilaVal = $k->lila;
                                $tfuVal = $k->tfu;
                                $djjVal = $k->djj;
                                $hbVal = $k->hb;
                                $kepatuhanFeVal = $k->kepatuhan_fe ? 1 : 0;
                                $jadwalNextVal = $k->jadwal_kunjungan_berikutnya
                                    ? $k->jadwal_kunjungan_berikutnya->format('Y-m-d')
                                    : '';

                                $modalTb = $tbVal ?? '';
                                $modalBb = $bbVal ?? '';
                                $modalSistol = $sistolVal ?? '';
                                $modalDiastol = $diastolVal ?? '';
                                $modalLila = $lilaVal ?? '';
                                $modalHb = $hbVal ?? '';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800">
                                        {{ $k->tanggal_kunjungan->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        @if ($k->metode_kunjungan === 'jemput_bola')
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                🏠 Jemput Bola
                                            </span>
                                        @elseif ($k->metode_kunjungan === 'puskesmas')
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                                🏥 Puskesmas
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">
                                                👥 Posyandu
                                            </span>
                                        @endif
                                        @if ($k->foto_pendampingan)
                                            <a href="{{ $k->foto_pendampingan_url }}" target="_blank"
                                                title="Lihat Foto Dokumentasi"
                                                class="text-teal-600 hover:text-teal-800 text-[11px] font-medium inline-flex items-center">
                                                📷
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-col">
                                        <a href="{{ route('ibu-hamil.show', $k->ibuHamil) }}"
                                            class="font-bold text-slate-800 hover:text-teal-700 text-xs sm:text-sm">
                                            {{ $k->ibuHamil->nama }}
                                        </a>
                                        <span class="block font-mono text-[11px] text-slate-500 font-semibold mt-0.5">
                                            NIK: {{ $k->ibuHamil->nik ?? '-' }}
                                        </span>

                                        <!-- Badge Pengelompokan Wilayah / Domisili Warga -->
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1">
                                            @if (auth()->user()->isAdminPuskesmas())
                                                @if (in_array($k->ibuHamil->desa_id, $puskesmasDesaIds ?? []))
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                        Wilayah Puskesmas Kita
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                                        Luar Wilayah Puskesmas
                                                    </span>
                                                @endif
                                            @elseif (auth()->user()->isDinkesKabupaten())
                                                @php
                                                    $kabBadge = ucwords(strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) (auth()->user()->kabupaten ?? 'Kabupaten')))));
                                                @endphp
                                                @if (in_array($k->ibuHamil->desa_id, $puskesmasDesaIds ?? []))
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                        Wilayah Kab. {{ $kabBadge }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                                        🌐 Luar Wilayah Kab.
                                                    </span>
                                                @endif
                                            @elseif (auth()->user()->isBidan() || auth()->user()->isKader())
                                                @if ($k->ibuHamil->desa_id === auth()->user()->desa_id)
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                                                        Warga Desa Tercover
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                                        Warga Luar Daerah
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <div class="font-medium text-slate-800">{{ $k->desa?->nama }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $k->desa?->puskesmas ?? 'Puskesmas' }}
                                    </div>
                                    @if ($k->desa_id !== $k->ibuHamil->desa_id)
                                        <div class="mt-1">
                                            <span
                                                class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 text-sky-800 border border-sky-200"
                                                title="Pasien terdaftar di {{ $k->ibuHamil->desa?->nama }} ({{ $k->ibuHamil->desa?->puskesmas ?? 'Puskesmas Asal' }})">
                                                🌐 Asal: {{ $k->ibuHamil->desa?->nama }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span
                                        class="px-2.5 py-1 rounded-xl font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $k->tahap_anc ?? $k->jenis_kunjungan }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    @if ($hasPhysicalData)
                                        <div class="flex flex-col gap-0.5 text-[11px]">
                                            <span><strong class="text-slate-800">TB/BB:</strong>
                                                {{ $tbVal ? $tbVal . ' cm' : '-' }} /
                                                {{ $bbVal ? $bbVal . ' kg' : '-' }}</span>
                                            <span><strong class="text-slate-800">TD:</strong>
                                                {{ $sistolVal && $diastolVal ? "{$sistolVal}/{$diastolVal} mmHg" : '-' }}</span>
                                            <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                                                @if ($lilaVal)
                                                    <span>LiLA: <strong class="text-slate-700">{{ $lilaVal }}
                                                            cm</strong></span>
                                                @endif
                                                @if ($hbVal)
                                                    <span>Hb: <strong class="text-slate-700">{{ $hbVal }}
                                                            g/dL</strong></span>
                                                @endif
                                                @if ($k->kepatuhan_fe)
                                                    <span class="text-emerald-700 font-bold" title="Patuh Tablet Fe">✓
                                                        Fe</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                            ⚠️ Belum Diukur
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($k->jadwal_kunjungan_berikutnya)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            🗓️ {{ $k->jadwal_kunjungan_berikutnya->translatedFormat('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[10px]">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($k->status_hadir)
                                        @if (!$hasPhysicalData)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                                ⚠️ Hadir (Data Belum Diisi)
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                ✓ Hadir & Diperiksa
                                            </span>
                                        @endif
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            ⏳ Belum Hadir
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($k->status_dilayani && $hasSkriningKunjungan)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ✓ Dilayani Lengkap
                                        </span>
                                        @if ($k->skrining)
                                            <div class="mt-1">
                                                <a href="{{ route('skrining.show', $k->skrining) }}"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $k->skrining->kategori_risiko->badgeClasses() }}"
                                                    title="Lihat hasil skrining SPR kunjungan ini">
                                                    {{ $k->skrining->kategori_risiko->value }} ({{ $k->skrining->total_skor }})
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            Belum Skrining SPR
                                        </span>
                                        <a href="{{ route('skrining.create', ['ibu_hamil_id' => $k->ibu_hamil_id, 'kunjungan_id' => $k->id, 'tanggal' => $k->tanggal_kunjungan->format('Y-m-d')]) }}"
                                            class="block text-[10px] text-teal-700 font-bold hover:underline mt-1">
                                            + Isi Skrining SPR
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @php
                                            $rawPhone = preg_replace('/[^0-9]/', '', $k->ibuHamil->no_hp ?? '');
                                            if ($rawPhone && str_starts_with($rawPhone, '0')) {
                                                $waPhone = '62' . substr($rawPhone, 1);
                                            } else {
                                                $waPhone = $rawPhone;
                                            }
                                            $tglJadwal = $k->jadwal_kunjungan_berikutnya ? $k->jadwal_kunjungan_berikutnya->translatedFormat('d F Y') : ($k->tanggal_kunjungan ? $k->tanggal_kunjungan->translatedFormat('d F Y') : '-');
                                            $waTelehealthMsg = urlencode("Halo Ibu {$k->ibuHamil->nama},\n\nSalam sehat dari Tim Pendampingan Telehealth ANC Puskesmas " . ($k->desa?->puskesmas ?? 'Puskesmas') . " / Desa " . ($k->desa?->nama ?? '') . ".\n\nKami mengingatkan jadwal kunjungan pemantauan ANC / Jemput Bola berikutnya pada hari *{$tglJadwal}*.\n\n📌 *Pesan Pemantauan Kesehatan:*\n1. Pastikan rutin meminum tablet tambah darah (Fe) 1 tablet setiap malam.\n2. Tetap cukupi asupan cairan dan makanan bergizi seimbang.\n3. Waspadai tanda bahaya: pusing hebat berkunang, pandangan kabur, bengkak pada kaki/tangan, atau keluar flek darah.\n\nJika ada keluhan, jangan ragu untuk segera menghubungi kami atau Bidan Desa. Semoga ibu dan kandungan selalu sehat!");
                                        @endphp

                                        @if ($waPhone)
                                            <a href="https://api.whatsapp.com/send?phone={{ $waPhone }}&text={{ $waTelehealthMsg }}" target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold text-[11px] shadow-xs transition-all"
                                                title="Kirim Pesan Pengingat Telehealth WhatsApp ke Ibu Hamil">
                                                <span>📱 WA</span>
                                            </a>
                                        @endif

                                        @if (!$k->status_hadir)
                                            <button type="button"
                                                @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->metode_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, false, '{{ $modalTb }}', '{{ $modalBb }}', '{{ $modalSistol }}', '{{ $modalDiastol }}', '{{ $modalLila }}', '{{ $tfuVal }}', '{{ $djjVal }}', '{{ $modalHb }}', {{ $kepatuhanFeVal }}, '{{ addslashes($k->bentuk_pendampingan ?? '') }}', '{{ $jadwalNextVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-[11px] shadow-xs transition-all">
                                                <span>✓ Hadirkan & Ukur</span>
                                            </button>
                                        @elseif (!$hasPhysicalData)
                                            <button type="button"
                                                @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->metode_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, false, '{{ $modalTb }}', '{{ $modalBb }}', '{{ $modalSistol }}', '{{ $modalDiastol }}', '{{ $modalLila }}', '{{ $tfuVal }}', '{{ $djjVal }}', '{{ $modalHb }}', {{ $kepatuhanFeVal }}, '{{ addslashes($k->bentuk_pendampingan ?? '') }}', '{{ $jadwalNextVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-[11px] shadow-xs transition-all">
                                                <span>⚠️ Input Data Fisik</span>
                                            </button>
                                        @elseif (!$hasSkriningKunjungan)
                                            <a href="{{ route('skrining.create', ['ibu_hamil_id' => $k->ibu_hamil_id, 'kunjungan_id' => $k->id, 'tanggal' => $k->tanggal_kunjungan->format('Y-m-d')]) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-[11px] shadow-xs transition-all">
                                                <span>📋 + Skrining SPR</span>
                                            </a>
                                        @else
                                            <button type="button"
                                                @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->metode_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $k->status_dilayani ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', '{{ $lilaVal }}', '{{ $tfuVal }}', '{{ $djjVal }}', '{{ $hbVal }}', {{ $kepatuhanFeVal }}, '{{ addslashes($k->bentuk_pendampingan ?? '') }}', '{{ $jadwalNextVal }}', true, {{ $k->ibu_hamil_id }})"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] border border-slate-200 transition-all">
                                                <span>Ubah Pelayanan</span>
                                            </button>
                                        @endif

                                        <a href="{{ route('kunjungan.edit', $k) }}"
                                            class="text-blue-600 hover:text-blue-700 font-semibold text-xs ml-1">
                                            Edit
                                        </a>

                                        @if (!auth()->user()->isKader())
                                            <form action="{{ route('kunjungan.destroy', $k) }}" method="POST"
                                                onsubmit="return confirm('Hapus catatan kunjungan ini?');"
                                                class="inline ml-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-700 text-xs">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                                        <a href="{{ route('kunjungan.edit', $k) }}"
                                            class="text-blue-600 hover:text-blue-700 font-semibold text-xs ml-1">
                                            Edit
                                        </a>

                                        @if (!auth()->user()->isKader())
                                            <form action="{{ route('kunjungan.destroy', $k) }}" method="POST"
                                                onsubmit="return confirm('Hapus catatan kunjungan ini?');"
                                                class="inline ml-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-700 text-xs">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    Belum ada data catatan kunjungan ANC.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($kunjungans->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $kunjungans->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Konfirmasi Kehadiran & Pelayanan Lengkap -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalOpen = false">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200/80">

                    <form :action="'{{ url('/kunjungan') }}/' + activeKunjunganId + '/konfirmasi'" method="POST">
                        @csrf
                        <div class="bg-gradient-to-r from-teal-600 to-emerald-600 p-5 text-white">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white uppercase tracking-wider"
                                    x-text="'ANC ' + activeJenis"></span>
                                <button type="button" @click="modalOpen = false"
                                    class="text-white/80 hover:text-white text-base">✕</button>
                            </div>
                            <h3 class="text-base font-extrabold mt-1">Konfirmasi Kehadiran & Pelayanan ANC</h3>
                            <p class="text-xs text-teal-100 mt-0.5">Ibu Hamil: <strong class="text-white font-bold"
                                    x-text="activeNama"></strong></p>
                        </div>

                        <div class="p-6 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
                            <!-- Tanggal Kehadiran Realisasi -->
                            <div>
                                <label
                                    class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Tanggal Kunjungan Aktual <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="tanggal_kunjungan" x-model="activeTanggal" required
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                            </div>

                            <!-- Pemeriksaan Fisik: TB, BB, LiLA, TD, Hb (Wajib) & TFU, DJJ (Klinis) -->
                            <div class="p-3.5 rounded-2xl bg-teal-50/60 border border-teal-200/80 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-teal-900 block">Pemeriksaan Fisik Kunjungan (10T):</span>
                                    <span class="text-[10px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                                        * Wajib Lengkap (TB, BB, LiLA, TD, Hb)
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            TB (cm) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" step="0.1" name="tinggi_badan" x-model="activeTb" placeholder="158" required
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            BB (kg) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" step="0.1" name="berat_badan" x-model="activeBb" placeholder="56" required
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            LiLA (cm) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" step="0.1" name="lila" x-model="activeLila" placeholder="24" required
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            TD Sistol <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" name="tekanan_darah_sistol" x-model="activeSistol" placeholder="120" required
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            TD Diastol <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" name="tekanan_darah_diastol" x-model="activeDiastol" placeholder="80" required
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            Hb (g/dL) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" step="0.1" name="hb" x-model="activeHb" placeholder="11.5" required
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                </div>

                                <div class="pt-1">
                                    <template x-if="!activeTb || !activeBb || !activeSistol || !activeDiastol || !activeLila || !activeHb">
                                        <p class="text-[11px] text-amber-800 font-medium flex items-center gap-1.5 bg-amber-50 px-2.5 py-1.5 rounded-xl border border-amber-200">
                                            <span>⚠️ TB, BB, LiLA, TD Sistol, TD Diastol, dan Hb wajib diisi sebelum menyimpan konfirmasi.</span>
                                        </p>
                                    </template>
                                    <template x-if="activeTb && activeBb && activeSistol && activeDiastol && activeLila && activeHb">
                                        <p class="text-[11px] text-emerald-800 font-semibold flex items-center gap-1.5 bg-emerald-50 px-2.5 py-1.5 rounded-xl border border-emerald-200">
                                            <span>✓ Data fisik lengkap (TB, BB, LiLA, TD, Hb). Tombol Simpan aktif & siap diarahkan ke Skrining SPR.</span>
                                        </p>
                                    </template>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-teal-200/60">
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TFU (cm)</label>
                                        <input type="number" step="0.1" name="tfu" x-model="activeTfu" placeholder="26"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">DJJ (dpm)</label>
                                        <input type="number" name="djj" x-model="activeDjj" placeholder="140"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                </div>

                                <div class="pt-1.5">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="kepatuhan_fe" value="1"
                                            x-model="activeKepatuhanFe"
                                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                                        <div>
                                            <span class="text-xs font-semibold text-slate-800 block">
                                                Rutin Mengonsumsi Tablet Fe
                                            </span>
                                            <span class="text-[10px] text-slate-500">Minimal 90 tablet selama masa kehamilan untuk cegah anemia</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Jadwal Kunjungan Berikutnya -->
                            <div>
                                <label
                                    class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Jadwal Kunjungan / Jemput Bola Berikutnya
                                </label>
                                <input type="date" name="jadwal_kunjungan_berikutnya" x-model="activeJadwalBerikutnya"
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                            </div>

                            <input type="hidden" name="status_hadir" value="1" />
                            <input type="hidden" name="status_dilayani" value="0" />

                            <!-- Catatan -->
                            <div>
                                <label
                                    class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Catatan Pemeriksaan / Pendampingan (Opsional)
                                </label>
                                <textarea name="catatan" x-model="activeCatatan" rows="2"
                                    placeholder="Contoh: Pendampingan jemput bola lancar, minum Fe tertib..."
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="modalOpen = false"
                                class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 bg-slate-100 transition-all">
                                Batal
                            </button>
                            <button type="submit"
                                :disabled="!activeTb || !activeBb || !activeSistol || !activeDiastol || !activeLila || !activeHb"
                                :class="(!activeTb || !activeBb || !activeSistol || !activeDiastol || !activeLila || !activeHb) 
                                    ? 'opacity-40 cursor-not-allowed bg-slate-400 hover:bg-slate-400 pointer-events-none' 
                                    : 'bg-teal-600 hover:bg-teal-700 shadow-sm active:scale-95'"
                                class="px-5 py-2.5 rounded-xl text-xs font-bold text-white transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                <span x-text="!activeHasSkrining ? 'Simpan & Lanjut ke Skrining SPR →' : 'Simpan Pemeriksaan Fisik'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    </div>
@endsection
