@extends('layouts.app', ['title' => 'Data Ibu Hamil', 'header' => 'Data Ibu Hamil'])

@section('content')
    <div class="space-y-6">

    @if (auth()->user()->isDinkes() && isset($agregat))
        <!-- ============================================================== -->
        <!-- TAMPILAN KHUSUS DINAS KESEHATAN (PROVINSI & KABUPATEN)           -->
        <!-- HANYA MENAMPILKAN KLASTER DATA AGREGAT MAKRO (TANPA INPUT LAPANGAN)-->
        <!-- ============================================================== -->

        <!-- Header Actions: Mode Agregat Wilayah -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $agregat['level'] === 'provinsi' ? 'bg-purple-100 text-purple-900 border border-purple-200' : 'bg-teal-100 text-teal-900 border border-teal-200' }}">
                        🏛️ Klaster Agregat {{ $agregat['level'] === 'provinsi' ? 'Tingkat Kabupaten/Kota' : 'Tingkat UPT Puskesmas' }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Monitoring & Pengawasan Makro</span>
                </div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                    Data Ibu Hamil {{ $agregat['region_name'] }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Rekapitulasi data agregat ibu hamil per {{ $agregat['level'] === 'provinsi' ? 'kabupaten / kota se-provinsi' : 'puskesmas di ' . $agregat['region_name'] }} untuk monitoring risiko dan perencanaan intervensi kesehatan.
                </p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('ibu-hamil.download', request()->query()) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-semibold shadow-xs transition-colors"
                    title="Unduh Rekapitulasi Data format Excel (.xlsx)">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
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
                Pendaftaran ibu hamil, pemeriksaan fisik 10T, dan pengisian skrining skor SPR dilakukan langsung oleh pihak <strong>UPT Puskesmas, Bidan Desa, dan Kader Posyandu</strong> sebagai ujung tombak klinis di lapangan. Akun Dinas Kesehatan difokuskan untuk <strong>analisis agregat makro, pemetaan risiko wilayah, dan monitoring cakupan layanan</strong>.
            </div>
        </div>

        <!-- Metric Summary Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Ibu Hamil -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-teal-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Ibu Hamil</span>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($agregat['summary']['total_bumil']) }}</h3>
                        <span class="text-xs text-slate-400 mt-1 block">{{ $agregat['summary']['total_cluster'] }} {{ $agregat['level'] === 'provinsi' ? 'Kabupaten' : 'Puskesmas' }} • {{ $agregat['summary']['total_desa'] }} Desa</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl font-bold">
                        🤰
                    </div>
                </div>
            </div>

            <!-- Risiko Sangat Tinggi (KRST) -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-rose-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Sangat Tinggi (KRST)</span>
                        <h3 class="text-3xl font-extrabold text-rose-600 mt-1">{{ number_format($agregat['summary']['total_krst']) }}</h3>
                        <span class="text-xs text-slate-500 mt-1 block">Rujukan Segera RS PONEK</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold animate-pulse">
                        ⚠️
                    </div>
                </div>
            </div>

            <!-- Risiko Tinggi & Rendah -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-amber-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600">Tinggi (KRT) & Rendah (KRR)</span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-extrabold text-amber-600">{{ number_format($agregat['summary']['total_krt']) }}</span>
                            <span class="text-xs text-slate-400">KRT</span>
                            <span class="text-slate-300">|</span>
                            <span class="text-2xl font-extrabold text-emerald-600">{{ number_format($agregat['summary']['total_krr']) }}</span>
                            <span class="text-xs text-slate-400">KRR</span>
                        </div>
                        <span class="text-xs text-slate-400 mt-1 block">Klasifikasi Poedji Rochjati</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        📊
                    </div>
                </div>
            </div>

            <!-- Cakupan Skrining & Gizi -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-indigo-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Cakupan Skrining SPR</span>
                        <h3 class="text-3xl font-extrabold text-indigo-600 mt-1">{{ $agregat['summary']['persen_skrining'] }}%</h3>
                        <span class="text-xs text-slate-500 mt-1 block">KEK: {{ $agregat['summary']['total_kek'] }} • Anemia: {{ $agregat['summary']['total_anemia'] }}</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        📋
                    </div>
                </div>
                <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ min(100, $agregat['summary']['persen_skrining']) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Tabel Rekapitulasi Klaster Agregat -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
                <div>
                    <h3 class="text-base font-bold text-slate-800">
                        Tabel Agregat Berdasarkan Klaster {{ $agregat['level'] === 'provinsi' ? 'Kabupaten / Kota' : 'Puskesmas' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Diurutkan berdasarkan prioritas risiko tertinggi untuk alokasi intervensi
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
                            <th class="py-3 px-4 text-center">{{ $agregat['level'] === 'provinsi' ? 'Faskes / Desa' : 'Desa Binaan' }}</th>
                            <th class="py-3 px-4 text-center">Total Bumil</th>
                            <th class="py-3 px-4 text-center">KRST (Merah)</th>
                            <th class="py-3 px-4 text-center">KRT (Kuning)</th>
                            <th class="py-3 px-4 text-center">KRR (Hijau)</th>
                            <th class="py-3 px-4 text-center">Cakupan Skrining</th>
                            <th class="py-3 px-4 text-center">Masalah Gizi (KEK / Anemia)</th>
                            <th class="py-3 px-4 text-center">Kunjungan ANC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($agregat['clusters'] as $index => $c)
                            <tr class="hover:bg-slate-50/80 transition-colors {{ $c['krst'] > 0 ? 'bg-rose-50/20' : '' }}">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ $c['nama'] }}</span>
                                        @if ($c['krst'] > 0)
                                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200">
                                                {{ $c['krst'] }} KRST
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-600">
                                    @if ($agregat['level'] === 'provinsi')
                                        <span class="font-semibold">{{ $c['total_puskesmas'] }} PKM</span>
                                        <span class="text-slate-400 block text-[10px]">{{ $c['total_desa'] }} Desa</span>
                                    @else
                                        <span class="font-semibold">{{ $c['total_desa'] }} Desa</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-900">
                                    {{ number_format($c['total_bumil']) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $c['krst'] > 0 ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $c['krst'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $c['krt'] > 0 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $c['krt'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        {{ $c['krr'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <div class="w-14 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ min(100, $c['persen_skrining']) }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-700 text-[11px]">{{ $c['persen_skrining'] }}%</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">({{ $c['diskrining'] }} / {{ $c['total_bumil'] }})</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="space-y-0.5">
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold {{ $c['kek'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500' }}">
                                            KEK: {{ $c['kek'] }}
                                        </span>
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold {{ $c['anemia'] > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-500' }}">
                                            Anemia: {{ $c['anemia'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    {{ number_format($c['total_kunjungan']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-8 text-center text-slate-400">Belum ada data ibu hamil terdaftar pada klaster wilayah ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-100/80 font-bold border-t-2 border-slate-300 text-slate-800">
                        <tr>
                            <td class="py-3.5 px-4" colspan="2">TOTAL KESELURUHAN</td>
                            <td class="py-3.5 px-4 text-center">{{ $agregat['summary']['total_desa'] }} Desa</td>
                            <td class="py-3.5 px-4 text-center text-teal-800 font-extrabold">{{ number_format($agregat['summary']['total_bumil']) }}</td>
                            <td class="py-3.5 px-4 text-center text-rose-700 font-extrabold">{{ number_format($agregat['summary']['total_krst']) }}</td>
                            <td class="py-3.5 px-4 text-center text-amber-700 font-extrabold">{{ number_format($agregat['summary']['total_krt']) }}</td>
                            <td class="py-3.5 px-4 text-center text-emerald-700 font-extrabold">{{ number_format($agregat['summary']['total_krr']) }}</td>
                            <td class="py-3.5 px-4 text-center text-indigo-700 font-extrabold">{{ $agregat['summary']['persen_skrining'] }}%</td>
                            <td class="py-3.5 px-4 text-center text-slate-700 font-extrabold">KEK: {{ $agregat['summary']['total_kek'] }} • Anemia: {{ $agregat['summary']['total_anemia'] }}</td>
                            <td class="py-3.5 px-4 text-center font-extrabold">{{ number_format($agregat['summary']['total_kunjungan']) }}</td>
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
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Daftar Ibu Hamil Terdata</h2>
                <p class="text-xs text-slate-500 mt-0.5">Basis data ibu hamil untuk monitoring deteksi dini risiko kehamilan
                </p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                @if (!auth()->user()->isKader())
                    <a href="{{ route('ibu-hamil.download', request()->query()) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-semibold shadow-xs transition-colors"
                        title="Unduh Data Ibu Hamil format Excel (.xlsx) sesuai filter aktif">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh Excel</span>
                    </a>
                @endif
                <a href="{{ route('ibu-hamil.create') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Daftarkan Ibu Hamil Baru</span>
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
                                Perhatian Medis
                            </span>
                        </div>
                        <p class="text-xs text-rose-800 mt-0.5">
                            Ditemukan <strong>{{ $countKrst ?? 0 }}</strong> ibu hamil <strong>KRST (Sangat Tinggi)</strong> dan <strong>{{ $countKrt ?? 0 }}</strong> ibu hamil <strong>KRT (Tinggi)</strong> di wilayah ini.
                            Wajib mendapatkan pemantauan ketat model jemput bola oleh kader serta persiapan rujukan faskes PONEK bila diperlukan.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if (($countKrst ?? 0) > 0)
                        <a href="{{ route('ibu-hamil.index', array_merge(request()->query(), ['risiko' => 'KRST', 'page' => 1])) }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition-colors">
                            <span>Filter KRST ({{ $countKrst ?? 0 }})</span>
                        </a>
                    @endif
                    @if (($countKrt ?? 0) > 0)
                        <a href="{{ route('ibu-hamil.index', array_merge(request()->query(), ['risiko' => 'KRT', 'page' => 1])) }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition-colors">
                            <span>Filter KRT ({{ $countKrt ?? 0 }})</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- Filters & Search Bar -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">

            <!-- Territory Grouping Navigation Tabs -->
            @if (auth()->user()->isAdminPuskesmas())
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Wilayah Puskesmas:</span>
                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'semua', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <span>📋 Semua Ibu Hamil</span>
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
                        <span>📋 Semua Ibu Hamil</span>
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
                        <span>🌐 Di Luar Wilayah Kab. {{ $cleanKab }}</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-900' }}">{{ $countLuar }}</span>
                    </a>
                </div>
            @elseif (auth()->user()->isBidan() || auth()->user()->isKader())
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Domisili Warga:</span>
                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'semua', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'dalam') === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <span>📋 Semua Ibu Hamil</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'dalam') === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $countSemua }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'dalam', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'dalam') === 'dalam' ? 'bg-teal-600 text-white shadow-xs' : 'bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/80' }}">
                        <span>🏡 Warga Desa Kita ({{ auth()->user()->desa?->nama ?? 'Binaan' }})</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'dalam') === 'dalam' ? 'bg-white/20 text-white' : 'bg-teal-200 text-teal-900' }}">{{ $countDalam }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'luar', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'dalam') === 'luar' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-800 hover:bg-purple-100 border border-purple-200/80' }}">
                        <span>🌐 Warga Luar Daerah / Lintas Desa</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'dalam') === 'luar' ? 'bg-white/20 text-white' : 'bg-purple-200 text-purple-900' }}">{{ $countLuar }}</span>
                    </a>
                </div>
            @endif

            <form method="GET" action="{{ route('ibu-hamil.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="hidden" name="kelompok" value="{{ request('kelompok', $currentKelompok ?? 'semua') }}" />

                <!-- Search Text -->
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama ibu, NIK 16 digit, atau suami..."
                        class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-800 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs placeholder-slate-400" />
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

                <!-- Desa Filter (Admin and Admin Puskesmas) -->
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
                                    <optgroup label="🌐 Desa di Luar Wilayah Kab. {{ $kabLabel }}">
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

                <!-- Risk Category Filter -->
                <div>
                    <select name="risiko" onchange="this.form.submit()"
                        class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">-- Semua Kategori Risiko --</option>
                        <option value="KRR" {{ request('risiko') === 'KRR' ? 'selected' : '' }}>KRR (Risiko Rendah -
                            Hijau)</option>
                        <option value="KRT" {{ request('risiko') === 'KRT' ? 'selected' : '' }}>KRT (Risiko Tinggi -
                            Kuning)</option>
                        <option value="KRST" {{ request('risiko') === 'KRST' ? 'selected' : '' }}>KRST (Risiko Sangat
                            Tinggi - Merah)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs">
                        Terapkan Filter
                    </button>
                    @if (request()->hasAny(['search', 'desa_id', 'risiko', 'scope', 'kelompok']))
                        <a href="{{ route('ibu-hamil.index') }}"
                            class="px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Ibu Hamil Content (Responsive Mobile Cards & Desktop Table) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Mobile Cards View (Tampilan Khusus Layar HP Bidan & Kader) -->
            <div class="block lg:hidden divide-y divide-slate-100">
                @forelse($ibuHamils as $bumil)
                    @php
                        $latest = $bumil->latestSkrining();
                        $rawPhone = preg_replace('/[^0-9]/', '', $bumil->no_hp ?? '');
                        $waPhone = ($rawPhone && str_starts_with($rawPhone, '0')) ? '62' . substr($rawPhone, 1) : $rawPhone;
                        $isRisk = $latest && in_array($latest->kategori_risiko, [\App\Enums\KategoriRisiko::KRST, \App\Enums\KategoriRisiko::KRT]);
                        $waMsg = urlencode("Halo Ibu {$bumil->nama},\n\nSalam sehat dari Tim Pendampingan Telehealth ANC Puskesmas " . ($bumil->desa?->puskesmas ?? 'Puskesmas') . " / Desa " . ($bumil->desa?->nama ?? '') . ".\n\n" . ($isRisk ? "⚠️ *Pesan Pemantauan Khusus:* Mengingat ibu terdeteksi memiliki kondisi risiko kehamilan (" . $latest->kategori_risiko->label() . "), mohon pastikan untuk selalu rutin kontrol ke bidan/faskes, istirahat cukup, dan meminum vitamin/tablet Fe.\n\n" : "") . "📌 Mohon pastikan rutin meminum tablet tambah darah (Fe) 1 tablet per hari dan waspadai tanda bahaya kehamilan (pusing hebat, pandangan kabur, atau perdarahan). Jika ada keluhan segera hubungi bidan atau kader pendamping. Semoga ibu dan janin selalu sehat!");
                    @endphp
                    <div class="p-4 space-y-3 hover:bg-slate-50/50 transition-colors">
                        <!-- Header Card: Nama & Status Risiko SPR -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <a href="{{ route('ibu-hamil.show', $bumil) }}" class="font-bold text-slate-900 text-sm hover:text-teal-700 block">
                                    {{ $bumil->nama }}
                                </a>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    NIK: <span class="font-mono text-slate-700 font-semibold">{{ $bumil->nik ?? '-' }}</span>
                                    @if($bumil->nama_suami) • Suami: {{ $bumil->nama_suami }} @endif
                                </div>
                            </div>
                            <div>
                                @if ($latest)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $latest->kategori_risiko->badgeClasses() }}">
                                        {{ $latest->kategori_risiko->value }} ({{ $latest->total_skor }})
                                    </span>
                                @else
                                    <a href="{{ route('skrining.create') }}?ibu_hamil_id={{ $bumil->id }}"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        + Skrining
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Domisili & Wilayah Faskes -->
                        <div class="flex items-center gap-1.5 flex-wrap text-xs text-slate-600">
                            <span>📍 Desa {{ $bumil->desa?->nama ?? '-' }} ({{ $bumil->desa?->puskesmas ?? 'Puskesmas' }})</span>
                            @if (auth()->user()->isAdminPuskesmas())
                                @if (in_array($bumil->desa_id, $puskesmasDesaIds ?? []))
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
                                @if (in_array($bumil->desa_id, $puskesmasDesaIds ?? []))
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800">
                                        Wilayah Kab. {{ $kabBadge }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800">
                                        🌐 Luar Wilayah Kab.
                                    </span>
                                @endif
                            @elseif (auth()->user()->isBidan() || auth()->user()->isKader())
                                @if ($bumil->desa_id === auth()->user()->desa_id)
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

                        <!-- Data Obstetri & Kehamilan -->
                        <div class="grid grid-cols-2 gap-2 p-2.5 bg-slate-50 rounded-xl text-slate-700 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase font-medium">Usia & Paritas</span>
                                <span class="font-semibold">{{ $bumil->usia ? $bumil->usia . ' Th' : '-' }} • G{{ $bumil->gravida }}P{{ $bumil->para }}A{{ $bumil->abortus }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase font-medium">Usia Kehamilan</span>
                                <span class="font-semibold text-teal-700">
                                    {{ $bumil->usia_kehamilan_minggu ? '± ' . $bumil->usia_kehamilan_minggu . ' minggu' : '-' }}
                                </span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-[10px] text-slate-400 block uppercase font-medium">Taksiran Persalinan (HPL)</span>
                                <span class="font-semibold">
                                    🗓️ {{ $bumil->taksiran_persalinan ? $bumil->taksiran_persalinan->translatedFormat('d F Y') : '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Tombol Aksi HP (Touch-Friendly) -->
                        <div class="pt-1 flex flex-col gap-2">
                            <div class="grid grid-cols-2 gap-2">
                                @if ($waPhone)
                                    <a href="https://api.whatsapp.com/send?phone={{ $waPhone }}&text={{ $waMsg }}" target="_blank"
                                        class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all">
                                        <span>📱 Kirim WA</span>
                                    </a>
                                @else
                                    <span class="flex items-center justify-center py-2.5 px-3 rounded-xl bg-slate-100 text-slate-400 font-medium text-xs">
                                        No HP (-)
                                    </span>
                                @endif

                                <a href="{{ route('ibu-hamil.show', $bumil) }}"
                                    class="flex items-center justify-center gap-1 py-2.5 px-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                    <span>👁️ Rincian</span>
                                </a>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-1 px-1">
                                <a href="{{ route('ibu-hamil.edit', $bumil) }}" class="text-blue-600 hover:text-blue-800 font-semibold py-1">
                                    ✏️ Edit Data
                                </a>
                                @if (!auth()->user()->isKader())
                                    <form action="{{ route('ibu-hamil.destroy', $bumil) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ibu hamil ini?');"
                                        class="inline">
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
                        Belum ada data ibu hamil terdaftar.
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Ibu Hamil</th>
                            <th class="py-3 px-4">Usia & Paritas (G-P-A)</th>
                            <th class="py-3 px-4">Wilayah Desa</th>
                            <th class="py-3 px-4">Taksiran Persalinan</th>
                            <th class="py-3 px-4 text-center">Status Skor SPR</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ibuHamils as $bumil)
                            @php $latest = $bumil->latestSkrining(); @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('ibu-hamil.show', $bumil) }}"
                                            class="font-bold text-slate-800 hover:text-teal-700">
                                            {{ $bumil->nama }}
                                        </a>

                                        <!-- Territory Grouping Badge -->
                                        @if (auth()->user()->isAdminPuskesmas())
                                            @if (in_array($bumil->desa_id, $puskesmasDesaIds ?? []))
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    Wilayah Puskesmas Kita
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                                    🌐 Luar Wilayah PKM
                                                </span>
                                            @endif
                                        @elseif (auth()->user()->isDinkesKabupaten())
                                            @php
                                                $kabBadge = ucwords(strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) (auth()->user()->kabupaten ?? 'Kabupaten')))));
                                            @endphp
                                            @if (in_array($bumil->desa_id, $puskesmasDesaIds ?? []))
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
                                            @if ($bumil->desa_id === auth()->user()->desa_id)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                                                    Warga Desa Kita
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                                    🌐 Lintas / Warga Luar
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400 block mt-0.5">
                                        NIK: <span
                                            class="font-mono text-slate-600 font-semibold">{{ $bumil->nik ?? '-' }}</span>
                                        • Suami: {{ $bumil->nama_suami ?? '-' }} • HP: {{ $bumil->no_hp ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <div>{{ $bumil->usia ? $bumil->usia . ' Th' : '-' }}</div>
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-mono font-medium">
                                        G{{ $bumil->gravida }} P{{ $bumil->para }} A{{ $bumil->abortus }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <div class="font-medium text-slate-800">{{ $bumil->desa?->nama ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $bumil->desa?->puskesmas ?? 'Puskesmas' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <div>
                                        {{ $bumil->taksiran_persalinan ? $bumil->taksiran_persalinan->format('d/m/Y') : '-' }}
                                    </div>
                                    @if ($bumil->usia_kehamilan_minggu)
                                        <span class="text-[11px] text-teal-600 font-medium">±
                                            {{ $bumil->usia_kehamilan_minggu }} mg</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($latest)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $latest->kategori_risiko->badgeClasses() }}">
                                            Skor {{ $latest->total_skor }} • {{ $latest->kategori_risiko->value }}
                                        </span>
                                    @else
                                        <a href="{{ route('skrining.create') }}?ibu_hamil_id={{ $bumil->id }}"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200">
                                            + Skrining Sekarang
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @php
                                            $rawPhone = preg_replace('/[^0-9]/', '', $bumil->no_hp ?? '');
                                            if ($rawPhone && str_starts_with($rawPhone, '0')) {
                                                $waPhone = '62' . substr($rawPhone, 1);
                                            } else {
                                                $waPhone = $rawPhone;
                                            }
                                            $latestSkrining = $bumil->latestSkrining();
                                            $isRisk = $latestSkrining && in_array($latestSkrining->kategori_risiko, [\App\Enums\KategoriRisiko::KRST, \App\Enums\KategoriRisiko::KRT]);
                                            $waMsg = urlencode("Halo Ibu {$bumil->nama},\n\nSalam sehat dari Tim Pendampingan Telehealth ANC Puskesmas " . ($bumil->desa?->puskesmas ?? 'Puskesmas') . " / Desa " . ($bumil->desa?->nama ?? '') . ".\n\n" . ($isRisk ? "⚠️ *Pesan Pemantauan Khusus:* Mengingat ibu terdeteksi memiliki kondisi risiko kehamilan (" . $latestSkrining->kategori_risiko->label() . "), mohon pastikan untuk selalu rutin kontrol ke bidan/faskes, istirahat cukup, dan meminum vitamin/tablet Fe.\n\n" : "") . "📌 Mohon pastikan rutin meminum tablet tambah darah (Fe) 1 tablet per hari dan waspadai tanda bahaya kehamilan (pusing hebat, pandangan kabur, atau perdarahan). Jika ada keluhan segera hubungi bidan atau kader pendamping. Semoga ibu dan janin selalu sehat!");
                                        @endphp
                                        @if ($waPhone)
                                            <a href="https://api.whatsapp.com/send?phone={{ $waPhone }}&text={{ $waMsg }}" target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold text-[11px] shadow-xs transition-all"
                                                title="Kirim Pesan Telehealth WhatsApp ke Ibu Hamil">
                                                <span>📱 WA</span>
                                            </a>
                                        @endif
                                        <a href="{{ route('ibu-hamil.show', $bumil) }}"
                                            class="font-semibold text-teal-700 hover:text-teal-800 text-xs ml-1">
                                            Rincian
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <a href="{{ route('ibu-hamil.edit', $bumil) }}"
                                            class="text-blue-600 hover:text-blue-700 text-xs">
                                            Edit
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <form action="{{ route('ibu-hamil.destroy', $bumil) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ibu hamil ini?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-700 text-xs">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data ibu hamil
                                    terdaftar</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($ibuHamils->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $ibuHamils->links() }}
                </div>
            @endif
        </div>

    @endif

    </div>
@endsection
