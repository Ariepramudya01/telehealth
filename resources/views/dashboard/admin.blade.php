@extends('layouts.app', ['title' => 'Dashboard Utama Admin', 'header' => 'Dashboard Eksekutif Wilayah'])

@section('content')
    <div class="space-y-6">

        <!-- Header Filter & Quick Actions -->
        <!-- Header Filter & Quick Actions -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    @if ($currentUser->isDinkesProvinsi())
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                            🏛️ Dinas Kesehatan Provinsi {{ $currentUser->provinsi ? preg_replace('/^(provinsi|prov\.)\s+/i', '', $currentUser->provinsi) : 'Riau' }}
                        </span>
                    @elseif ($currentUser->isDinkesKabupaten())
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-100 text-teal-900 border border-teal-200">
                            🏛️ Dinas Kesehatan {{ $currentUser->kabupaten ?? 'Kabupaten' }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-900 border border-blue-200">
                            🏥 {{ $currentUser->puskesmas_kerja ?? 'UPT Puskesmas' }}
                        </span>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                    @if ($currentUser->isDinkesProvinsi())
                        Ringkasan Cakupan Seluruh Wilayah
                    @elseif ($currentUser->isDinkesKabupaten())
                        Ringkasan Capaian {{ $currentUser->kabupaten ?? 'Wilayah Kabupaten' }}
                    @else
                        {{ 'Ringkasan Capaian ' . ($currentUser->puskesmas_kerja ?? 'Wilayah Kerja') }}
                    @endif
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if ($currentUser->isDinkesProvinsi())
                        Monitoring capaian skrining risiko tinggi dan kunjungan ANC terintegrasi seluruh kabupaten/kota se-provinsi
                    @elseif ($currentUser->isDinkesKabupaten())
                        Monitoring capaian skrining risiko tinggi dan kunjungan ANC seluruh UPT Puskesmas di {{ $currentUser->kabupaten ?? 'Kabupaten' }}
                    @else
                        {{ 'Monitoring capaian skrining risiko dan kunjungan ANC di wilayah kerja ' . ($currentUser->puskesmas_kerja ?? 'UPT Puskesmas') }}
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <select name="desa_id" onchange="this.form.submit()"
                        class="text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">
                            @if ($currentUser->isDinkesProvinsi())
                                -- Semua Desa (Seluruh Provinsi) --
                            @elseif ($currentUser->isDinkesKabupaten())
                                -- Semua Desa di {{ $currentUser->kabupaten ?? 'Kabupaten' }} --
                            @else
                                -- Semua Desa di {{ $currentUser->puskesmas_kerja ?? 'Puskesmas Ini' }} --
                            @endif
                        </option>
                        @foreach ($desas as $d)
                            <option value="{{ $d->id }}" {{ $selectedDesaId == $d->id ? 'selected' : '' }}>
                                {{ $d->nama }} ({{ $d->puskesmas ?? $d->kecamatan }})
                            </option>
                        @endforeach
                    </select>
                    @if ($selectedDesaId)
                        <a href="{{ route('dashboard') }}"
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 text-xs"
                            title="Reset Filter">
                            ✕
                        </a>
                    @endif
                </form>

                <a href="{{ route('laporan.index') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-700 text-xs font-semibold shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Unduh Laporan Excel</span>
                </a>
            </div>
        </div>

        <!-- KPI Summary Grid Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Ibu Hamil -->
            <div
                class="bg-white p-5 rounded-2xl border-t-4 border-t-teal-500 border-x border-b border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Ibu Hamil</span>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalIbuHamil) }}</h3>
                        <span class="text-xs text-slate-400 mt-1 block">{{ $totalDesa }} Desa Terdaftar</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>{{ $totalBidan }} Bidan Desa</span>
                    <span>{{ $totalKader }} Kader Posyandu</span>
                </div>
            </div>

            <!-- Capaian Hadir Kunjungan -->
            <div
                class="bg-white p-5 rounded-2xl border-t-4 border-t-blue-500 border-x border-b border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Capaian Kehadiran</span>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $persenHadir }}%</h3>
                        <span class="text-xs text-slate-500 mt-1 block">
                            <strong class="text-slate-700">{{ $totalHadir }}</strong> hadir dari {{ $totalKunjungan }}
                            kunjungan
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(100, $persenHadir) }}%"></div>
                </div>
            </div>

            <!-- Capaian Dilayani Kunjungan -->
            <div
                class="bg-white p-5 rounded-2xl border-t-4 border-t-emerald-500 border-x border-b border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Capaian Dilayani</span>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $persenDilayani }}%</h3>
                        <span class="text-xs text-slate-500 mt-1 block">
                            <strong class="text-slate-700">{{ $totalDilayani }}</strong> dilayani dari total target
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ min(100, $persenDilayani) }}%"></div>
                </div>
            </div>

            <!-- Total Skrining & KRST -->
            <div
                class="bg-white p-5 rounded-2xl border-t-4 border-t-rose-500 border-x border-b border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Risiko Sangat Tinggi
                            (KRST)</span>
                        <h3 class="text-3xl font-extrabold text-rose-600 mt-1">{{ number_format($skriningKRST) }}</h3>
                        <span class="text-xs text-slate-500 mt-1 block">Dari {{ $totalSkrining }} skrining SPR</span>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center animate-pulse">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-emerald-700 font-medium">KRR: {{ $skriningKRR }}</span>
                    <span class="text-amber-700 font-medium">KRT: {{ $skriningKRT }}</span>
                    <span class="text-rose-700 font-medium">KRST: {{ $skriningKRST }}</span>
                </div>
            </div>
        </div>

        <!-- Charts Row (2 Cards per Row) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            <!-- Diagram Capaian Hadir & Dilayani -->
            <div
                class="bg-white rounded-2xl border border-blue-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
                <div
                    class="p-5 border-b border-blue-100 bg-gradient-to-r from-blue-500/10 via-teal-500/5 to-transparent flex items-center justify-between">
                    <div>
                        @if ($currentUser->isDinkesProvinsi())
                            <h3 class="text-base font-bold text-slate-800">Diagram Capaian Wilayah per Kabupaten</h3>
                            <p class="text-xs text-slate-500">Animasi perbandingan kunjungan hadir, dilayani, dan skrining
                                risiko per kabupaten se-provinsi</p>
                        @elseif($currentUser->isDinkesKabupaten())
                            <h3 class="text-base font-bold text-slate-800">Diagram Capaian Wilayah per Puskesmas</h3>
                            <p class="text-xs text-slate-500">Animasi perbandingan kunjungan hadir, dilayani, dan skrining
                                risiko per UPT Puskesmas di {{ $currentUser->kabupaten ?? 'Kabupaten' }}</p>
                        @else
                            <h3 class="text-base font-bold text-slate-800">Diagram Capaian Kunjungan & Skrining per Desa
                            </h3>
                            <p class="text-xs text-slate-500">Animasi perbandingan kunjungan hadir, dilayani, dan skrining
                                risiko per desa binaan</p>
                        @endif
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        Grafik Interaktif
                    </span>
                </div>
                <div class="p-5 flex-1 flex flex-col justify-center">
                    <div class="relative h-72">
                        <canvas id="kunjunganChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Diagram Distribusi Risiko SPR -->
            <div
                class="bg-white rounded-2xl border border-rose-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
                <div
                    class="p-5 border-b border-rose-100 bg-gradient-to-r from-rose-500/10 via-amber-500/5 to-transparent flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Proporsi Kategori Risiko Kehamilan</h3>
                        <p class="text-xs text-slate-500">Hasil klasifikasi deteksi dini risiko kehamilan (SPR)</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-rose-100 text-rose-800 rounded-lg">Donut</span>
                </div>
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div class="relative h-56 flex items-center justify-center">
                        <canvas id="risikoChart"></canvas>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                            <span class="font-bold text-emerald-700 block text-sm">{{ $skriningKRR }}</span>
                            <span class="text-[11px] text-emerald-600 font-medium">KRR (Rendah)</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200">
                            <span class="font-bold text-amber-700 block text-sm">{{ $skriningKRT }}</span>
                            <span class="text-[11px] text-amber-600 font-medium">KRT (Tinggi)</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200">
                            <span class="font-bold text-rose-700 block text-sm">{{ $skriningKRST }}</span>
                            <span class="text-[11px] text-rose-600 font-medium">KRST (Sgt Tinggi)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row Operasional Kunjungan: Jemput Bola & Kunjungan Faskes (2 Cards per Row) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            <!-- SEKSI JEMPUT BOLA: Jadwal Jemput Bola ANC Terdekat -->
            <div
                class="bg-white rounded-2xl border border-amber-200/90 shadow-xs flex flex-col justify-between overflow-hidden">
                <div
                    class="p-5 border-b border-amber-200/70 bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-amber-50/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                🚗 Model Jemput Bola ANC
                            </span>
                            <span class="text-xs text-amber-800/80 font-medium">Lintas Daerah</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Jadwal Jemput Bola ANC Terdekat</h3>
                        <p class="text-xs text-slate-500">Pendampingan kunjungan rumah langsung oleh kader & bidan desa</p>
                    </div>
                    <div>
                        <a href="{{ route('kunjungan.create') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 active:scale-95 text-white text-xs font-bold shadow-xs transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Catat Jemput Bola</span>
                        </a>
                    </div>
                </div>

                <div class="p-5 flex-1 flex flex-col justify-between">
                    @if ($jadwalJemputBola->isEmpty())
                        <div
                            class="p-8 text-center bg-amber-50/40 rounded-2xl border border-dashed border-amber-200 my-auto">
                            <div
                                class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 mx-auto flex items-center justify-center text-xl mb-2">
                                🏠
                            </div>
                            <h4 class="text-xs font-bold text-slate-700">Belum Ada Agenda Jemput Bola Terdekat</h4>
                            <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto">
                                Semua ibu hamil telah selesai didampingi atau belum ada jadwal kunjungan berikutnya yang
                                ditetapkan.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('kunjungan.create') }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Jadwalkan Kunjungan Rumah</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="space-y-3 max-h-[560px] overflow-y-auto pr-1">
                            @foreach ($jadwalJemputBola as $jb)
                                @php
                                    $targetDate = $jb->jadwal_kunjungan_berikutnya ?? $jb->tanggal_kunjungan;
                                    $isToday = $targetDate->isToday();
                                    $isPast = $targetDate->isPast() && !$isToday;
                                @endphp
                                <div
                                    class="rounded-xl border transition-all p-3.5 {{ $isToday ? 'bg-amber-50/80 border-amber-300 ring-1 ring-amber-300' : 'bg-slate-50/80 border-slate-200 hover:border-amber-300 hover:bg-amber-50/20' }}">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $jb->metode_kunjungan === 'jemput_bola' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-teal-100 text-teal-800 border border-teal-200' }}">
                                            {{ $jb->metode_kunjungan === 'jemput_bola' ? '🏠 Jemput Bola' : '👥 ' . ucfirst($jb->metode_kunjungan) }}
                                        </span>
                                        <span
                                            class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold bg-white border border-slate-200 text-slate-700">
                                            {{ $jb->tahap_anc ?? $jb->jenis_kunjungan }}
                                        </span>
                                        <span
                                            class="text-[11px] font-bold {{ $isToday ? 'text-amber-700 font-extrabold' : ($isPast ? 'text-rose-600' : 'text-slate-700') }}">
                                            🗓️ {{ $targetDate->translatedFormat('d M Y') }}
                                            @if ($isToday)
                                                <span
                                                    class="text-[9px] bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded font-bold ml-1">Hari
                                                    Ini</span>
                                            @endif
                                        </span>
                                    </div>

                                    <a href="{{ route('ibu-hamil.show', $jb->ibuHamil) }}"
                                        class="font-bold text-slate-800 hover:text-amber-700 text-sm block">
                                        {{ $jb->ibuHamil->nama }}
                                    </a>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">
                                        {{ $jb->desa?->nama }} ({{ $jb->desa?->puskesmas ?? 'Puskesmas' }}) • NIK:
                                        {{ $jb->ibuHamil->nik ?? '-' }}
                                    </span>

                                    @if ($jb->bentuk_pendampingan)
                                        <div
                                            class="mt-2 p-2 rounded-lg bg-white border border-slate-200/80 text-[11px] text-slate-600 italic">
                                            "{{ Str::limit($jb->bentuk_pendampingan, 90) }}"
                                        </div>
                                    @endif

                                    <div
                                        class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-between gap-2">
                                        <div class="text-[11px]">
                                            @if ($jb->status_hadir)
                                                <span class="font-semibold text-emerald-700">✓ Selesai Dikunjungi</span>
                                            @else
                                                <span class="font-semibold text-amber-700">⏳ Belum Selesai</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('ibu-hamil.show', $jb->ibuHamil) }}"
                                                class="text-[11px] font-semibold text-slate-600 hover:text-slate-900">
                                                Detail Pasien
                                            </a>
                                            @if (!$jb->status_hadir)
                                                <a href="{{ route('kunjungan.index') }}"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-[11px] shadow-xs transition-all">
                                                    <span>Catat →</span>
                                                </a>
                                            @else
                                                <span
                                                    class="text-[10px] text-emerald-700 font-semibold px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200">
                                                    ✓ Lengkap
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Jadwal Kunjungan & Konfirmasi Kehadiran Cepat -->
            <div class="bg-white rounded-2xl border border-sky-200/90 shadow-xs flex flex-col justify-between overflow-hidden"
                x-data="{
                    modalOpen: false,
                    activeKunjunganId: null,
                    activeNama: '',
                    activeJenis: '',
                    activeHadir: false,
                    activeDilayani: false,
                    activeTanggal: '',
                    activeCatatan: '',
                    activeTb: '',
                    activeBb: '',
                    activeSistol: '',
                    activeDiastol: '',
                    activeHasSkrining: false,
                    activeBumilId: null,
                    openConfirmModal(id, nama, jenis, tgl, catatan, hadir, dilayani, tb, bb, sistol, diastol, hasSkrining, bumilId) {
                        this.activeKunjunganId = id;
                        this.activeNama = nama;
                        this.activeJenis = jenis;
                        this.activeTanggal = tgl;
                        this.activeCatatan = catatan || '';
                        this.activeHadir = Boolean(hadir);
                        this.activeDilayani = Boolean(dilayani);
                        this.activeTb = tb || '';
                        this.activeBb = bb || '';
                        this.activeSistol = sistol || '';
                        this.activeDiastol = diastol || '';
                        this.activeHasSkrining = Boolean(hasSkrining);
                        this.activeBumilId = bumilId;
                        this.modalOpen = true;
                    }
                }">
                <div
                    class="p-5 border-b border-sky-200/70 bg-gradient-to-r from-sky-500/15 via-teal-500/10 to-sky-50/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-900 border border-sky-300">
                                🏥 Pemeriksaan ANC Faskes
                            </span>
                            <span class="text-xs text-sky-800/80 font-medium">Posyandu & Faskes</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Jadwal Pemeriksaan ANC & Konfirmasi Hadir</h3>
                        <p class="text-xs text-slate-500">Konfirmasi kehadiran & pelayanan medis pada jadwal kunjungan</p>
                    </div>
                    <div>
                        <a href="{{ route('kunjungan.index') }}"
                            class="text-xs font-bold text-sky-700 hover:text-sky-800 flex items-center gap-1">
                            Lihat Semua Jadwal →
                        </a>
                    </div>
                </div>

                <div class="p-4 flex-1 overflow-x-auto max-h-[560px] overflow-y-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-sky-50/60 border-b border-sky-100 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">Tanggal / Jenis</th>
                                <th class="py-2.5 px-3">Ibu Hamil & Desa</th>
                                <th class="py-2.5 px-3">Fisik (TB/BB, TD)</th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($kunjunganTerdekat as $k)
                                @php
                                    $hasSkriningKunjungan =
                                        $k->skrining_id !== null || $k->ibuHamil->skrinings()->exists();
                                    $tbVal = $k->tinggi_badan ?? ($k->ibuHamil->tinggi_badan ?? '');
                                    $bbVal = $k->berat_badan ?? ($k->ibuHamil->berat_badan ?? '');
                                    $sistolVal = $k->tekanan_darah_sistol ?? ($k->ibuHamil->tekanan_darah_sistol ?? '');
                                    $diastolVal =
                                        $k->tekanan_darah_diastol ?? ($k->ibuHamil->tekanan_darah_diastol ?? '');
                                @endphp
                                <tr class="hover:bg-sky-50/30 transition-colors">
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-slate-800 block text-xs">
                                            {{ $k->tanggal_kunjungan->translatedFormat('d M Y') }}
                                        </span>
                                        <span
                                            class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-sky-100 text-sky-800">
                                            {{ $k->jenis_kunjungan }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <a href="{{ route('ibu-hamil.show', $k->ibuHamil) }}"
                                            class="font-bold text-sky-700 hover:underline block text-xs">
                                            {{ $k->ibuHamil->nama }}
                                        </a>
                                        <span class="text-[11px] text-slate-500 block">
                                            {{ $k->desa?->nama ?? ($k->ibuHamil->desa?->nama ?? '-') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-700">
                                        <div class="text-[11px] leading-tight space-y-0.5">
                                            <div><span class="text-slate-400">TB/BB:</span>
                                                <strong>{{ $tbVal ? $tbVal . ' cm' : '-' }}</strong> /
                                                <strong>{{ $bbVal ? $bbVal . ' kg' : '-' }}</strong></div>
                                            <div><span class="text-slate-400">TD:</span>
                                                <strong>{{ $sistolVal && $diastolVal ? "{$sistolVal}/{$diastolVal}" : '-' }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            @if ($k->status_hadir)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                                    ✓ Hadir
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">
                                                    ✕ Belum Hadir
                                                </span>
                                            @endif

                                            @if ($k->status_dilayani)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                                    ✓ Dilayani
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">
                                                    Belum Dilayani
                                                </span>
                                                @if (!$hasSkriningKunjungan)
                                                    <a href="{{ route('skrining.create', ['ibu_hamil_id' => $k->ibu_hamil_id, 'kunjungan_id' => $k->id]) }}"
                                                        class="block text-[10px] text-sky-700 font-bold hover:underline mt-0.5">
                                                        + Skrining
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        @if (!$k->status_hadir)
                                            <button type="button"
                                                @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->jenis_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $hasSkriningKunjungan ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-xs transition-all">
                                                <span>Konfirmasi</span>
                                            </button>
                                        @else
                                            <button type="button"
                                                @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->jenis_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $k->status_dilayani ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-200 transition-all">
                                                <span>Ubah</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        Belum ada jadwal kunjungan tercatat
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Konfirmasi Kehadiran -->
                <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                    role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                            @click="modalOpen = false"></div>

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
                                <div class="bg-gradient-to-r from-teal-600 to-sky-600 p-5 text-white">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white uppercase tracking-wider"
                                            x-text="'ANC ' + activeJenis"></span>
                                        <button type="button" @click="modalOpen = false"
                                            class="text-white/80 hover:text-white text-base">✕</button>
                                    </div>
                                    <h3 class="text-base font-extrabold mt-1">Konfirmasi Kehadiran & Pelayanan ANC</h3>
                                    <p class="text-xs text-teal-100 mt-0.5">Ibu Hamil: <strong
                                            class="text-white font-bold" x-text="activeNama"></strong></p>
                                </div>

                                <div class="p-6 space-y-4 text-xs">
                                    <div>
                                        <label
                                            class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                            Tanggal Kunjungan Aktual <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="date" name="tanggal_kunjungan" x-model="activeTanggal" required
                                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                                    </div>

                                    <!-- Pemeriksaan Fisik: TB, BB, TD -->
                                    <div class="p-3.5 rounded-2xl bg-teal-50/60 border border-teal-200/80 space-y-2.5">
                                        <span class="text-[11px] font-bold text-teal-900 block">Pemeriksaan Fisik
                                            Kunjungan:</span>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                            <div>
                                                <label
                                                    class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TB
                                                    (cm)</label>
                                                <input type="number" step="0.1" name="tinggi_badan"
                                                    x-model="activeTb" placeholder="158"
                                                    class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">BB
                                                    (kg)</label>
                                                <input type="number" step="0.1" name="berat_badan"
                                                    x-model="activeBb" placeholder="56"
                                                    class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TD
                                                    Sistol</label>
                                                <input type="number" name="tekanan_darah_sistol" x-model="activeSistol"
                                                    placeholder="120"
                                                    class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TD
                                                    Diastol</label>
                                                <input type="number" name="tekanan_darah_diastol"
                                                    x-model="activeDiastol" placeholder="80"
                                                    class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input type="hidden" name="status_hadir" value="0" />
                                            <input type="checkbox" name="status_hadir" value="1"
                                                x-model="activeHadir"
                                                class="mt-0.5 h-4 w-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 cursor-pointer" />
                                            <div>
                                                <strong class="text-slate-800 block text-xs">Ibu Hamil Hadir di Faskes /
                                                    Posyandu</strong>
                                                <span class="text-[11px] text-slate-500">Tandai jika ibu hamil datang pada
                                                    jadwal kunjungan ini</span>
                                            </div>
                                        </label>

                                        <div class="pt-2.5 border-t border-slate-200">
                                            <template x-if="activeHasSkrining">
                                                <label class="flex items-start gap-3 cursor-pointer"
                                                    :class="!activeHadir ? 'opacity-50 pointer-events-none' : ''">
                                                    <input type="hidden" name="status_dilayani" value="0" />
                                                    <input type="checkbox" name="status_dilayani" value="1"
                                                        x-model="activeDilayani" :disabled="!activeHadir"
                                                        class="mt-0.5 h-4 w-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer" />
                                                    <div>
                                                        <strong class="text-slate-800 block text-xs">Pelayanan Medis
                                                            Lengkap (Telah Dilayani)</strong>
                                                        <span class="text-[11px] text-emerald-700 font-medium">✓ Skrining
                                                            SPR Terpenuhi</span>
                                                    </div>
                                                </label>
                                            </template>
                                            <template x-if="!activeHasSkrining">
                                                <div
                                                    class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex flex-col gap-2">
                                                    <div>
                                                        <strong class="block">⚠️ Skrining SPR Belum Diisi</strong>
                                                        <span class="text-[11px] text-amber-700">Status Dilayani hanya
                                                            dapat dicatat setelah ibu hamil mengisi kuesioner Skrining
                                                            SPR.</span>
                                                    </div>
                                                    <div>
                                                        <a :href="'{{ url('/skrining/create') }}?ibu_hamil_id=' + activeBumilId + '&kunjungan_id=' + activeKunjunganId"
                                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                                        <span>+ Isi Skrining SPR Sekarang (Otomatis Dilayani) →</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                        Catatan Pemeriksaan / Hasil Pelayanan (Opsional)
                                    </label>
                                    <textarea name="catatan" x-model="activeCatatan" rows="2"
                                              placeholder="Contoh: Tekanan darah normal, telah diberikan tablet Fe..."
                                              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                                </div>
                            </div>

                            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                <button type="button" @click="modalOpen = false"
                                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 bg-slate-100 transition-all">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition-all flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Simpan Konfirmasi</span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Evaluasi & Kinerja Wilayah: Capaian Wilayah (dan Kinerja Petugas untuk Admin Puskesmas) -->
    <div class="{{ $currentUser->isAdminPuskesmas() ? 'grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch' : 'space-y-6' }}">
        <!-- Tabel Capaian Wilayah (Disesuaikan berdasarkan Tingkat Wilayah) -->
        <div class="bg-white rounded-2xl border border-indigo-200/90 shadow-xs flex flex-col justify-between overflow-hidden">
            @if ($currentUser->isDinkesProvinsi())
<!-- TINGKAT PROVINSI: KINERJA KABUPATEN DARI TERTINGGI HINGGA TERENDAH -->
                <div class="p-5 border-b border-indigo-200/70 bg-gradient-to-r from-indigo-500/15 via-purple-500/10 to-indigo-50/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                🏆 Peringkat Kinerja Kabupaten
                            </span>
                            <span class="text-xs text-indigo-800/80 font-medium">{{ count($kinerjaKabupaten) }} Kabupaten Se-Provinsi</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Laporan Kinerja Wilayah per Kabupaten</h3>
                        <p class="text-xs text-slate-500">
                            Peringkat kinerja deteksi dini dan pelayanan ANC masing-masing kabupaten se-provinsi (diurutkan dari tertinggi hingga terendah)
                        </p>
                    </div>
                </div>

                <div class="p-4 flex-1 overflow-x-auto max-h-[500px] overflow-y-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-indigo-50/60 border-b border-indigo-100 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">Peringkat & Kabupaten</th>
                                <th class="py-2.5 px-3 text-center">Faskes / Desa</th>
                                <th class="py-2.5 px-3 text-center">Bumil</th>
                                <th class="py-2.5 px-3 text-center">Hadir (%)</th>
                                <th class="py-2.5 px-3 text-center">Dilayani (%)</th>
                                <th class="py-2.5 px-3 text-center">Skrining</th>
                                <th class="py-2.5 px-3 text-center">KRST</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($kinerjaKabupaten as $row)
<tr class="hover:bg-indigo-50/30 transition-colors">
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2.5">
                                            @if ($row['ranking'] === 1)
<span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black bg-gradient-to-r from-amber-300 to-yellow-400 text-amber-950 shadow-xs border border-amber-400" title="Peringkat 1">
                                                    🥇 1
                                                </span>
@elseif($row['ranking'] === 2)
<span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold bg-slate-200 text-slate-800 border border-slate-300" title="Peringkat 2">
                                                    🥈 2
                                                </span>
@elseif($row['ranking'] === 3)
<span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300" title="Peringkat 3">
                                                    🥉 3
                                                </span>
@else
<span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                    #{{ $row['ranking'] }}
                                                </span>
@endif
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">{{ $row['nama'] }}</span>
                                                <span class="text-[10px] text-slate-400">Tingkat Kabupaten / Kota</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="font-semibold text-slate-700 block text-xs">{{ $row['total_puskesmas'] }} PKM</span>
                                        <span class="text-[10px] text-slate-400">{{ $row['total_desa'] }} Desa</span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-semibold text-slate-800">
                                        {{ number_format($row['total_bumil']) }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                            {{ $row['persen_hadir'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($row['persen_hadir'] >= 60 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $row['persen_hadir'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                            {{ $row['persen_dilayani'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($row['persen_dilayani'] >= 60 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $row['persen_dilayani'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <div class="w-12 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $row['persen_skrining'] }}%"></div>
                                            </div>
                                            <span class="font-semibold text-slate-700 text-[11px]">{{ $row['persen_skrining'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($row['krst_count'] > 0)
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 animate-pulse">
                                                {{ $row['krst_count'] }}
                                            </span>
@else
<span class="text-slate-400">0</span>
@endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">Belum ada data kabupaten terdaftar</td>
                                </tr>
@endforelse
                        </tbody>
                    </table>
                </div>
@elseif($currentUser->isDinkesKabupaten())
<!-- TINGKAT KABUPATEN: LAPORAN KINERJA CAPAIAN PUSKESMAS DARI TERTINGGI HINGGA TERENDAH -->
                <div class="p-5 border-b border-indigo-200/70 bg-gradient-to-r from-indigo-500/15 via-purple-500/10 to-indigo-50/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                🏆 Peringkat Capaian Puskesmas
                            </span>
                            <span class="text-xs text-indigo-800/80 font-medium">{{ count($kinerjaPuskesmas) }} UPT Puskesmas di {{ $currentUser->kabupaten ?? 'Kabupaten' }}</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Laporan Kinerja Wilayah per Puskesmas</h3>
                        <p class="text-xs text-slate-500">
                            Peringkat capaian deteksi dini risiko dan pelayanan ANC masing-masing Puskesmas di wilayah {{ $currentUser->kabupaten ?? 'Kabupaten' }} (diurutkan dari capaian tertinggi hingga terendah)
                        </p>
                    </div>
                </div>

                <div class="p-4 flex-1 overflow-x-auto max-h-[500px] overflow-y-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-indigo-50/60 border-b border-indigo-100 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">Peringkat & Puskesmas</th>
                                <th class="py-2.5 px-3 text-center">Wilayah Binaan</th>
                                <th class="py-2.5 px-3 text-center">Bumil</th>
                                <th class="py-2.5 px-3 text-center">Hadir (%)</th>
                                <th class="py-2.5 px-3 text-center">Dilayani (%)</th>
                                <th class="py-2.5 px-3 text-center">Skrining</th>
                                <th class="py-2.5 px-3 text-center">KRST</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($kinerjaPuskesmas as $row)
<tr class="hover:bg-indigo-50/30 transition-colors">
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2.5">
                                            @if ($row['ranking'] === 1)
<span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black bg-gradient-to-r from-amber-300 to-yellow-400 text-amber-950 shadow-xs border border-amber-400" title="Peringkat 1">
                                                    🥇 1
                                                </span>
@elseif($row['ranking'] === 2)
<span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold bg-slate-200 text-slate-800 border border-slate-300" title="Peringkat 2">
                                                    🥈 2
                                                </span>
@elseif($row['ranking'] === 3)
<span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300" title="Peringkat 3">
                                                    🥉 3
                                                </span>
@else
<span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                    #{{ $row['ranking'] }}
                                                </span>
@endif
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">{{ $row['nama'] }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $row['kecamatan'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="font-semibold text-slate-700 block text-xs">{{ $row['total_desa'] }} Desa</span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-semibold text-slate-800">
                                        {{ number_format($row['total_bumil']) }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                            {{ $row['persen_hadir'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($row['persen_hadir'] >= 60 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $row['persen_hadir'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                            {{ $row['persen_dilayani'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($row['persen_dilayani'] >= 60 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $row['persen_dilayani'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <div class="w-12 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $row['persen_skrining'] }}%"></div>
                                            </div>
                                            <span class="font-semibold text-slate-700 text-[11px]">{{ $row['persen_skrining'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($row['krst_count'] > 0)
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 animate-pulse">
                                                {{ $row['krst_count'] }}
                                            </span>
@else
<span class="text-slate-400">0</span>
@endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">Belum ada data puskesmas terdaftar</td>
                                </tr>
@endforelse
                        </tbody>
                    </table>
                </div>
@else
<!-- TINGKAT PUSKESMAS: CAPAIAN WILAYAH PER DESA -->
                <div class="p-5 border-b border-indigo-200/70 bg-gradient-to-r from-indigo-500/15 via-purple-500/10 to-indigo-50/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-900 border border-indigo-300">
                                📊 Evaluasi Wilayah
                            </span>
                            <span class="text-xs text-indigo-800/80 font-medium">{{ count($desaCapaian) }} Desa Aktif</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Capaian Wilayah per Desa</h3>
                        <p class="text-xs text-slate-500">
                            Capaian kunjungan hadir, dilayani, dan cakupan skrining per desa di {{ $currentUser->puskesmas_kerja ?? 'UPT Puskesmas' }}
                        </p>
                    </div>
                </div>

                <div class="p-4 flex-1 overflow-x-auto max-h-[500px] overflow-y-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-indigo-50/60 border-b border-indigo-100 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">Desa / Bidan</th>
                                <th class="py-2.5 px-3 text-center">Bumil</th>
                                <th class="py-2.5 px-3 text-center">Hadir (%)</th>
                                <th class="py-2.5 px-3 text-center">Dilayani (%)</th>
                                <th class="py-2.5 px-3 text-center">Skrining</th>
                                <th class="py-2.5 px-3 text-center">KRST</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($desaCapaian as $row)
<tr class="hover:bg-indigo-50/30 transition-colors">
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-slate-800 block text-xs">{{ $row['nama'] }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $row['bidan'] }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-semibold text-slate-800">
                                        {{ $row['total_bumil'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                            {{ $row['persen_hadir'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($row['persen_hadir'] >= 60 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $row['persen_hadir'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                            {{ $row['persen_dilayani'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($row['persen_dilayani'] >= 60 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $row['persen_dilayani'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <div class="w-12 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $row['persen_skrining'] }}%"></div>
                                            </div>
                                            <span class="font-semibold text-slate-700 text-[11px]">{{ $row['persen_skrining'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($row['krst_count'] > 0)
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 animate-pulse">
                                                {{ $row['krst_count'] }}
                                            </span>
@else
<span class="text-slate-400">0</span>
@endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data desa terdaftar</td>
                                </tr>
@endforelse
                        </tbody>
                    </table>
                </div>
@endif
        </div>

        @if ($currentUser->isAdminPuskesmas())
<!-- Kinerja Bidan dan Kader Table (Khusus Admin Puskesmas / Kapus / KTU) -->
            <div class="bg-white rounded-2xl border border-emerald-200/90 shadow-xs flex flex-col justify-between overflow-hidden">
                <div class="p-5 border-b border-emerald-200/70 bg-gradient-to-r from-emerald-500/15 via-teal-500/10 to-emerald-50/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                👥 Kinerja Petugas
                            </span>
                            <span class="text-xs text-emerald-800/80 font-medium">Bidan Desa & Kader</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Laporan Capaian Kinerja Petugas</h3>
                        <p class="text-xs text-slate-500">
                            Rincian data ibu hamil yang diinput & skrining yang diselesaikan oleh petugas di {{ $currentUser->puskesmas_kerja ?? 'UPT Puskesmas' }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('laporan.download.kinerja.input') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Unduh Excel</span>
                        </a>
                    </div>
                </div>

                <div class="p-4 flex-1 overflow-x-auto max-h-[500px] overflow-y-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-emerald-50/60 border-b border-emerald-100 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">Petugas</th>
                                <th class="py-2.5 px-3">Peran</th>
                                <th class="py-2.5 px-3">Desa</th>
                                <th class="py-2.5 px-3 text-center">Bumil Diinput</th>
                                <th class="py-2.5 px-3 text-center">Skrining</th>
                                <th class="py-2.5 px-3 text-center">KRST</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($kinerjaUsers as $staff)
<tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="py-3 px-3 font-semibold text-slate-800">
                                        {{ $staff['name'] }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold
                                            {{ $staff['role'] === \App\Enums\Role::Bidan ? 'bg-teal-100 text-teal-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $staff['role']->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-600">
                                        {{ $staff['desa'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-semibold text-slate-800">
                                        {{ $staff['bumil_count'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-semibold text-emerald-700">
                                        {{ $staff['skrining_count'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($staff['krst_found'] > 0)
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                                {{ $staff['krst_found'] }}
                                            </span>
@else
<span class="text-slate-400">0</span>
@endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data kinerja petugas</td>
                                </tr>
@endforelse
                        </tbody>
                    </table>
                </div>
            </div>
@endif
    </div>

    <!-- Perhatian Khusus Ibu Hamil KRST -->
    @if ($urgentKRST->count() > 0)
<div class="bg-rose-50/80 rounded-2xl border border-rose-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-500 animate-ping"></span>
                    <h3 class="text-sm font-bold text-rose-900 uppercase tracking-wider">Perhatian Segera: Ibu Hamil Risiko Sangat Tinggi (KRST)</h3>
                </div>
                <span class="text-xs text-rose-700 font-medium">Perlu Rujukan ke RS / Dokter Spesialis</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($urgentKRST as $u)
<div class="bg-white p-4 rounded-xl border border-rose-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full">
                                    Skor {{ $u->total_skor }} (KRST)
                                </span>
                                <span class="text-[11px] text-slate-400">{{ $u->tanggal_skrining->format('d/m/Y') }}</span>
                            </div>
                            <h4 class="font-bold text-slate-800 mt-2">{{ $u->ibuHamil->nama }}</h4>
                            <p class="text-xs text-slate-500">{{ $u->ibuHamil->desa?->nama }} • Usia {{ $u->ibuHamil->usia ?? '-' }} Th</p>
                            @if ($u->catatan)
<p class="text-xs text-slate-600 mt-2 bg-slate-50 p-2 rounded-lg italic">"{{ $u->catatan }}"</p>
@endif
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Oleh: {{ $u->user->name }}</span>
                            <a href="{{ route('skrining.show', $u) }}" class="font-semibold text-rose-700 hover:text-rose-800">
                                Lihat Rincian →
                            </a>
                        </div>
                    </div>
@endforeach
            </div>
        </div>
@endif

</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dynamic Regional Data for Animated Capaian Chart
            const chartLabels = {!! json_encode($chartLabels ?? []) !!};
            const hadirData = {!! json_encode($chartHadir ?? []) !!};
            const dilayaniData = {!! json_encode($chartDilayani ?? []) !!};
            const skriningData = {!! json_encode($chartSkrining ?? []) !!};

            const canvasKunjungan = document.getElementById('kunjunganChart');
            if (canvasKunjungan) {
                const ctxKunjungan = canvasKunjungan.getContext('2d');

                // Animated Gradient Fills
                const gradientHadir = ctxKunjungan.createLinearGradient(0, 0, 0, 300);
                gradientHadir.addColorStop(0, 'rgba(59, 130, 246, 0.95)');
                gradientHadir.addColorStop(1, 'rgba(59, 130, 246, 0.35)');

                const gradientDilayani = ctxKunjungan.createLinearGradient(0, 0, 0, 300);
                gradientDilayani.addColorStop(0, 'rgba(16, 185, 129, 0.95)');
                gradientDilayani.addColorStop(1, 'rgba(16, 185, 129, 0.35)');

                const gradientSkrining = ctxKunjungan.createLinearGradient(0, 0, 0, 300);
                gradientSkrining.addColorStop(0, 'rgba(124, 58, 237, 0.95)');
                gradientSkrining.addColorStop(1, 'rgba(124, 58, 237, 0.35)');

                new Chart(ctxKunjungan, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                                label: 'Kunjungan Hadir',
                                data: hadirData,
                                backgroundColor: gradientHadir,
                                borderColor: '#2563eb',
                                borderWidth: 1.5,
                                borderRadius: 6,
                                borderSkipped: false,
                            },
                            {
                                label: 'Kunjungan Dilayani',
                                data: dilayaniData,
                                backgroundColor: gradientDilayani,
                                borderColor: '#059669',
                                borderWidth: 1.5,
                                borderRadius: 6,
                                borderSkipped: false,
                            },
                            {
                                label: 'Skrining Selesai',
                                data: skriningData,
                                backgroundColor: gradientSkrining,
                                borderColor: '#7c3aed',
                                borderWidth: 1.5,
                                borderRadius: 6,
                                borderSkipped: false,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1600,
                            easing: 'easeOutQuart',
                            delay: function(context) {
                                let delay = 0;
                                if (context.type === 'data' && context.mode === 'default') {
                                    delay = context.dataIndex * 90 + context.datasetIndex * 70;
                                }
                                return delay;
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 11,
                                        weight: '600'
                                    },
                                    padding: 14,
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                titleFont: {
                                    size: 12,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 11
                                },
                                padding: 12,
                                cornerRadius: 12,
                                boxPadding: 4,
                                usePointStyle: true,
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 10,
                                        weight: '500'
                                    },
                                    maxRotation: 40,
                                    minRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 15
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(241, 245, 249, 1)'
                                },
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Animated Donut Chart for SPR Risk
            const canvasRisiko = document.getElementById('risikoChart');
            if (canvasRisiko) {
                const ctxRisiko = canvasRisiko.getContext('2d');
                new Chart(ctxRisiko, {
                    type: 'doughnut',
                    data: {
                        labels: ['KRR (Risiko Rendah)', 'KRT (Risiko Tinggi)', 'KRST (Sangat Tinggi)'],
                        datasets: [{
                            data: [{{ $skriningKRR }}, {{ $skriningKRT }},
                                {{ $skriningKRST }}
                            ],
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 1800,
                            easing: 'easeOutBack',
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                padding: 12,
                                cornerRadius: 12,
                                titleFont: {
                                    size: 12,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 11
                                },
                            }
                        },
                        cutout: '72%'
                    }
                });
            }
        });
    </script>
@endpush
