@extends('layouts.app', ['title' => 'Riwayat Skrining Risiko', 'header' => 'Skrining Deteksi Dini Risiko'])

@section('content')
<div class="space-y-6">

    @if (auth()->user()->isDinkes() && isset($agregat))
        <!-- ============================================================== -->
        <!-- TAMPILAN KHUSUS DINAS KESEHATAN (PROVINSI & KABUPATEN)           -->
        <!-- REKAPITULASI KLASTER SKRINING SKOR SPR (MONITORING & AGREGAT)  -->
        <!-- ============================================================== -->

        <!-- Header Actions: Mode Agregat Skrining -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $agregat['level'] === 'provinsi' ? 'bg-purple-100 text-purple-900 border border-purple-200' : 'bg-teal-100 text-teal-900 border border-teal-200' }}">
                        🏛️ Rekapitulasi Skrining SPR {{ $agregat['level'] === 'provinsi' ? 'Tingkat Provinsi' : 'Tingkat Kabupaten' }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Model Poedji Rochjati Terpadu</span>
                </div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                    Cakupan & Evaluasi Risiko SPR {{ $agregat['region_name'] }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Monitoring deteksi dini faktor risiko kehamilan, sebaran KRST/KRT, serta kesiapan rujukan RS PONEK se-wilayah
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 text-xs font-semibold">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Skrining Klinis diisi oleh Bidan/Kader Lapangan</span>
                </span>
            </div>
        </div>

        <!-- Alert Siaga Risiko Sangat Tinggi (KRST) -->
        @if ($agregat['summary']['total_krst'] > 0 || $agregat['summary']['total_krt'] > 0)
            <div class="p-4 rounded-3xl bg-gradient-to-r from-rose-50 via-amber-50 to-white border border-rose-200 text-rose-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 text-xl font-bold shadow-xs animate-pulse">
                        ⚠️
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-rose-900">
                            Peringatan Deteksi Dini: Teridentifikasi {{ $agregat['summary']['total_krst'] }} Kasus KRST & {{ $agregat['summary']['total_krt'] }} Kasus KRT
                        </h4>
                        <p class="text-xs text-rose-700 mt-0.5">
                            Ibu hamil dengan Skor Poedji Rochjati &ge; 12 (KRST) memerlukan rujukan terencana dan penanganan oleh Dokter Spesialis Kebidanan (Sp.OG) di RS PONEK.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Metric Summary Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Ibu Hamil Terdaftar -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-teal-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Ibu Hamil</span>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($agregat['summary']['total_bumil']) }}</h3>
                        <span class="text-xs text-slate-400 mt-1 block">{{ $agregat['summary']['total_cluster'] }} {{ $agregat['level'] === 'provinsi' ? 'Kabupaten/Kota' : 'Puskesmas' }}</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl font-bold">
                        🤰
                    </div>
                </div>
            </div>

            <!-- Cakupan Skrining SPR -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-indigo-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Diskrining SPR</span>
                        <h3 class="text-3xl font-extrabold text-indigo-600 mt-1">{{ number_format($agregat['summary']['total_skrining']) }}</h3>
                        <span class="text-xs text-slate-500 mt-1 block">Cakupan Wilayah: {{ $agregat['summary']['persen_cakupan'] }}%</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        📋
                    </div>
                </div>
                <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ min(100, $agregat['summary']['persen_cakupan']) }}%"></div>
                </div>
            </div>

            <!-- Risiko Sangat Tinggi (KRST) & Rujukan RS -->
            <div class="bg-white p-5 rounded-2xl border-t-4 border-t-rose-500 border-x border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Sangat Tinggi (KRST)</span>
                        <h3 class="text-3xl font-extrabold text-rose-600 mt-1">{{ number_format($agregat['summary']['total_krst']) }}</h3>
                        <span class="text-xs text-rose-700 font-semibold mt-1 block">Rujukan RS PONEK: {{ number_format($agregat['summary']['total_rujukan_rs']) }}</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                        🚨
                    </div>
                </div>
            </div>

            <!-- Risiko Tinggi (KRT) & Rendah (KRR) -->
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
                        <span class="text-xs text-slate-400 mt-1 block">Rujukan Puskesmas: {{ number_format($agregat['summary']['total_rujukan_pkm']) }}</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        📊
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Klaster Wilayah Agregat Skrining SPR -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">
                        Tabel Agregat Skrining Skor Poedji Rochjati (SPR) Per {{ $agregat['level'] === 'provinsi' ? 'Kabupaten / Kota' : 'Puskesmas / Wilayah' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pemeringkatan wilayah berdasarkan kasus KRST tertinggi untuk prioritas alokasi fasilitas rujukan
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    {{ count($agregat['clusters']) }} Wilayah Terklaster
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">{{ $agregat['level'] === 'provinsi' ? 'Kabupaten / Kota' : 'Puskesmas / Wilayah' }}</th>
                            <th class="py-3 px-4 text-center">Desa Binaan</th>
                            <th class="py-3 px-4 text-center">Total Bumil</th>
                            <th class="py-3 px-4 text-center">Diskrining SPR</th>
                            <th class="py-3 px-4 text-center">Cakupan (%)</th>
                            <th class="py-3 px-4 text-center">🟢 KRR (Rendah)</th>
                            <th class="py-3 px-4 text-center">🟡 KRT (Tinggi)</th>
                            <th class="py-3 px-4 text-center">🔴 KRST (Sangat Tinggi)</th>
                            <th class="py-3 px-4 text-center">Rujukan RS</th>
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
                                    {{ number_format($c['total_bumil']) }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-indigo-700">
                                    {{ number_format($c['total_skrining']) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $c['persen_cakupan'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($c['persen_cakupan'] >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $c['persen_cakupan'] }}%
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-semibold text-emerald-700">
                                    {{ number_format($c['krr']) }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-semibold text-amber-700">
                                    {{ number_format($c['krt']) }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-extrabold {{ $c['krst'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    @if ($c['krst'] > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold">
                                            ⚠️ {{ $c['krst'] }}
                                        </span>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-rose-700">
                                    {{ number_format($c['rujukan_rs']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-8 text-center text-slate-400">Belum ada catatan skrining pada klaster wilayah ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-100/80 font-bold border-t-2 border-slate-300 text-slate-800">
                        <tr>
                            <td class="py-3.5 px-4" colspan="2">TOTAL KESELURUHAN</td>
                            <td class="py-3.5 px-4 text-center">-</td>
                            <td class="py-3.5 px-4 text-center text-teal-800 font-extrabold">{{ number_format($agregat['summary']['total_bumil']) }}</td>
                            <td class="py-3.5 px-4 text-center text-indigo-700 font-extrabold">{{ number_format($agregat['summary']['total_skrining']) }}</td>
                            <td class="py-3.5 px-4 text-center font-extrabold">{{ $agregat['summary']['persen_cakupan'] }}%</td>
                            <td class="py-3.5 px-4 text-center text-emerald-700 font-extrabold">{{ number_format($agregat['summary']['total_krr']) }}</td>
                            <td class="py-3.5 px-4 text-center text-amber-700 font-extrabold">{{ number_format($agregat['summary']['total_krt']) }}</td>
                            <td class="py-3.5 px-4 text-center text-rose-700 font-extrabold">{{ number_format($agregat['summary']['total_krst']) }}</td>
                            <td class="py-3.5 px-4 text-center text-rose-700 font-extrabold">{{ number_format($agregat['summary']['total_rujukan_rs']) }}</td>
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Riwayat Evaluasi Skrining Deteksi Dini</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pemeriksaan deteksi dini faktor risiko obstetri pada ibu hamil</p>
            </div>

            <a href="{{ route('skrining.create') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Input Skrining Baru</span>
            </a>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('skrining.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama ibu hamil atau NIK..."
                           class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-800 py-2 px-3 focus:ring-teal-500 focus:border-teal-500 shadow-xs placeholder-slate-400" />
                </div>

                <div class="min-w-[180px]">
                    <select name="kategori" onchange="this.form.submit()" 
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">-- Semua Kategori Risiko --</option>
                        <option value="KRR" {{ request('kategori') === 'KRR' ? 'selected' : '' }}>KRR (Risiko Rendah - Hijau)</option>
                        <option value="KRT" {{ request('kategori') === 'KRT' ? 'selected' : '' }}>KRT (Risiko Tinggi - Kuning)</option>
                        <option value="KRST" {{ request('kategori') === 'KRST' ? 'selected' : '' }}>KRST (Risiko Sangat Tinggi - Merah)</option>
                    </select>
                </div>

                @if(request()->hasAny(['search', 'kategori']))
                    <a href="{{ route('skrining.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200">
                        Reset Filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Skrining Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal Skrining</th>
                            <th class="py-3 px-4">Nama Ibu Hamil</th>
                            <th class="py-3 px-4">Desa</th>
                            <th class="py-3 px-4">Usia Kehamilan</th>
                            <th class="py-3 px-4 text-center">Total Skor</th>
                            <th class="py-3 px-4 text-center">Tingkat Kategori Risiko</th>
                            <th class="py-3 px-4">Pemeriksa (Bidan/Kader)</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($skrinings as $s)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    {{ $s->tanggal_skrining->translatedFormat('d F Y') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('ibu-hamil.show', $s->ibuHamil) }}" class="font-bold text-slate-800 hover:text-teal-700 block">
                                        {{ $s->ibuHamil->nama }}
                                    </a>
                                    @if($s->ibuHamil->nik)
                                        <span class="text-[10px] font-mono text-slate-400">NIK: {{ $s->ibuHamil->nik }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    {{ $s->ibuHamil->desa?->nama ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $s->usia_kehamilan_minggu ? $s->usia_kehamilan_minggu . ' Minggu' : '-' }}
                                    @if($s->trimester)
                                        <span class="text-slate-400 font-normal">(Tri. {{ $s->trimester }})</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="text-sm font-extrabold text-slate-900">{{ $s->total_skor }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $s->kategori_risiko->badgeClasses() }}">
                                        {{ $s->kategori_risiko->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <span class="font-semibold text-slate-800 block">{{ $s->user?->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $s->user?->role->label() }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('skrining.show', $s) }}" class="font-semibold text-teal-700 hover:text-teal-800">
                                            Jawaban
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <a href="{{ route('skrining.print', $s) }}" target="_blank" class="text-blue-600 hover:text-blue-700">
                                            Cetak
                                        </a>
                                        @if(!auth()->user()->isKader())
                                            <span class="text-slate-300">|</span>
                                            <form action="{{ route('skrining.destroy', $s) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data skrining ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-700">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data skrining SPR ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($skrinings->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $skrinings->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection

