@extends('layouts.app', ['title' => 'Dashboard Bidan Desa', 'header' => 'Dashboard Bidan: ' . $desa->nama])

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-teal-100 text-teal-800">
                    Bidan Desa Terdaftar
                </span>
                <span class="text-xs text-slate-500">{{ $desa->kecamatan }}, {{ $desa->kabupaten }}</span>
            </div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight mt-1">{{ $desa->nama }}</h2>
            <p class="text-xs text-slate-500">Pantau deteksi risiko dini ibu hamil dan pembinaan kader posyandu di wilayah kerja Anda</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('laporan.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-700 text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Laporan Desa</span>
            </a>

            <a href="{{ route('users.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Tambah Kader Baru</span>
            </a>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Ibu Hamil Desa -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ibu Hamil Terdaftar</span>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $bumilCount }}</h3>
                    <span class="text-xs text-slate-500 mt-1 block">{{ count($kaders) }} Kader Pembina</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Input Mandiri Bidan: <strong>{{ $myBumilCount }}</strong> bumil
            </div>
        </div>

        <!-- Capaian Hadir -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Capaian Hadir</span>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $persenHadir }}%</h3>
                    <span class="text-xs text-slate-500 mt-1 block">{{ $totalHadir }} dari {{ $totalKunjungan }} kunjungan</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(100, $persenHadir) }}%"></div>
            </div>
        </div>

        <!-- Capaian Dilayani -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Capaian Dilayani</span>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $persenDilayani }}%</h3>
                    <span class="text-xs text-slate-500 mt-1 block">{{ $totalDilayani }} terlayani lengkap</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ min(100, $persenDilayani) }}%"></div>
            </div>
        </div>

        <!-- Cakupan Skrining SPR -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cakupan Skrining SPR</span>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $persenSkrining }}%</h3>
                    <span class="text-xs text-slate-500 mt-1 block">{{ $totalSkrining }} skrining selesai</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-emerald-700">KRR: {{ $skriningKRR }}</span>
                <span class="text-amber-700">KRT: {{ $skriningKRT }}</span>
                <span class="text-rose-700 font-bold">KRST: {{ $skriningKRST }}</span>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Diagram Kunjungan Desa -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="text-base font-bold text-slate-800 mb-1">Status Kunjungan Ibu Hamil (Desa {{ $desa->nama }})</h3>
            <p class="text-xs text-slate-500 mb-4">Perbandingan kehadiran dan pelayanan ANC</p>
            <div class="relative h-64">
                <canvas id="bidanKunjunganChart"></canvas>
            </div>
        </div>

        <!-- Diagram Risiko Desa -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="text-base font-bold text-slate-800 mb-1">Sebaran Tingkat Risiko Kehamilan</h3>
            <p class="text-xs text-slate-500 mb-4">Proporsi kehamilan risiko rendah, tinggi, dan sangat tinggi</p>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="bidanRisikoChart"></canvas>
            </div>
        </div>
    </div>

    <!-- SEKSI JEMPUT BOLA: Jadwal Jemput Bola ANC Terdekat -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200">
                        🚗 Model Jemput Bola ANC
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Pendampingan Kader Lintas Daerah</span>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">Jadwal Jemput Bola ANC Terdekat</h3>
                <p class="text-xs text-slate-500">Daftar ibu hamil dengan rencana pendampingan jemput bola langsung ke rumah</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('kunjungan.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 active:scale-95 text-white text-xs font-bold shadow-sm shadow-teal-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Catat Jemput Bola</span>
                </a>
            </div>
        </div>

        @if($jadwalJemputBola->isEmpty())
            <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 mx-auto flex items-center justify-center text-xl mb-2">
                    🏠
                </div>
                <h4 class="text-xs font-bold text-slate-700">Belum Ada Agenda Jemput Bola Terdekat</h4>
                <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto">
                    Semua ibu hamil di wilayah {{ $desa->nama }} telah selesai didampingi atau belum ada jadwal kunjungan berikutnya yang ditetapkan.
                </p>
                <div class="mt-4">
                    <a href="{{ route('kunjungan.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Jadwalkan Kunjungan Rumah / Jemput Bola Sekarang</span>
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($jadwalJemputBola as $jb)
                    @php
                        $targetDate = $jb->jadwal_kunjungan_berikutnya ?? $jb->tanggal_kunjungan;
                        $isToday = $targetDate->isToday();
                        $isPast = $targetDate->isPast() && !$isToday;
                    @endphp
                    <div class="rounded-2xl border transition-all p-4 flex flex-col justify-between {{ $isToday ? 'bg-amber-50/50 border-amber-300 ring-1 ring-amber-300' : 'bg-slate-50/60 border-slate-200 hover:border-slate-300' }}">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold font-mono {{ $jb->metode_kunjungan === 'jemput_bola' ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800' }}">
                                    {{ $jb->metode_kunjungan === 'jemput_bola' ? '🏠 Jemput Bola' : '👥 ' . ucfirst($jb->metode_kunjungan) }}
                                </span>
                                <span class="px-2 py-0.5 rounded-lg font-mono text-[10px] font-bold bg-white border border-slate-200 text-slate-700">
                                    {{ $jb->tahap_anc ?? $jb->jenis_kunjungan }}
                                </span>
                            </div>

                            <a href="{{ route('ibu-hamil.show', $jb->ibuHamil) }}" class="font-bold text-slate-800 hover:text-teal-700 text-sm block">
                                {{ $jb->ibuHamil->nama }}
                            </a>
                            <span class="text-[11px] text-slate-500 block mt-0.5">
                                {{ $jb->desa?->nama }} • NIK: {{ $jb->ibuHamil->nik ?? '-' }}
                            </span>

                            @if($jb->bentuk_pendampingan)
                                <div class="mt-2.5 p-2 rounded-xl bg-white border border-slate-200 text-[11px] text-slate-600 italic">
                                    "{{ Str::limit($jb->bentuk_pendampingan, 90) }}"
                                </div>
                            @endif

                            <div class="mt-3 flex items-center justify-between text-[11px] pt-2 border-t border-slate-200/60">
                                <span class="text-slate-500">Rencana Kunjungan:</span>
                                <span class="font-bold {{ $isToday ? 'text-amber-700 font-extrabold' : ($isPast ? 'text-rose-600' : 'text-slate-800') }}">
                                    🗓️ {{ $targetDate->translatedFormat('d M Y') }}
                                    @if($isToday)
                                        <span class="text-[10px] bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded-md ml-1 font-bold">Hari Ini</span>
                                    @endif
                                </span>
                            </div>

                            <div class="mt-2 flex items-center justify-between text-[11px]">
                                <span class="text-slate-500">Status Kehadiran:</span>
                                @if($jb->status_hadir)
                                    <span class="font-semibold text-emerald-700">✓ Selesai Dikunjungi</span>
                                @else
                                    <span class="font-semibold text-amber-700">⏳ Belum Selesai</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200/80 flex items-center justify-between gap-2">
                            <a href="{{ route('ibu-hamil.show', $jb->ibuHamil) }}" class="text-[11px] font-semibold text-slate-600 hover:text-slate-900">
                                Detail Pasien
                            </a>
                            @if(!$jb->status_hadir)
                                <a href="{{ route('kunjungan.index') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-[11px] shadow-xs transition-all">
                                    <span>Konfirmasi / Catat →</span>
                                </a>
                            @else
                                <span class="text-[10px] text-emerald-700 font-semibold px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200">
                                    ✓ Data Lengkap
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Jadwal Kunjungan & Konfirmasi Kehadiran Cepat -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden"
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
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800">Jadwal Pemeriksaan ANC & Konfirmasi Kehadiran Desa {{ $desa->nama }}</h3>
                <p class="text-xs text-slate-500">Konfirmasi kehadiran ibu hamil pada jadwal kunjungan faskes / posyandu secara langsung</p>
            </div>
            <a href="{{ route('kunjungan.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1">
                Lihat Semua Jadwal Kunjungan →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Tanggal Jadwal</th>
                        <th class="py-3 px-4">Nama Ibu Hamil</th>
                        <th class="py-3 px-4">Jenis Kunjungan</th>
                        <th class="py-3 px-4">Pemeriksaan Fisik</th>
                        <th class="py-3 px-4 text-center">Status Kehadiran</th>
                        <th class="py-3 px-4 text-center">Status Dilayani</th>
                        <th class="py-3 px-4 text-right">Aksi Konfirmasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kunjunganTerdekat as $k)
                        @php
                            $hasSkriningKunjungan = $k->skrining_id !== null || $k->ibuHamil->skrinings()->exists();
                            $tbVal = $k->tinggi_badan ?? $k->ibuHamil->tinggi_badan ?? '';
                            $bbVal = $k->berat_badan ?? $k->ibuHamil->berat_badan ?? '';
                            $sistolVal = $k->tekanan_darah_sistol ?? $k->ibuHamil->tekanan_darah_sistol ?? '';
                            $diastolVal = $k->tekanan_darah_diastol ?? $k->ibuHamil->tekanan_darah_diastol ?? '';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $k->tanggal_kunjungan->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('ibu-hamil.show', $k->ibuHamil) }}" class="font-bold text-teal-700 hover:underline">
                                    {{ $k->ibuHamil->nama }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 font-bold font-mono text-xs border border-teal-200/60">
                                    {{ $k->jenis_kunjungan }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                <div class="flex flex-col gap-0.5 text-[11px]">
                                    <span><strong class="text-slate-800">TB / BB:</strong> {{ $tbVal ? $tbVal . ' cm' : '-' }} / {{ $bbVal ? $bbVal . ' kg' : '-' }}</span>
                                    <span><strong class="text-slate-800">TD:</strong> {{ ($sistolVal && $diastolVal) ? "{$sistolVal}/{$diastolVal} mmHg" : '-' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($k->status_hadir)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        ✓ Hadir
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                        ✕ Belum Hadir
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($k->status_dilayani)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        ✓ Dilayani
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                        Belum Dilayani
                                    </span>
                                    @if(!$hasSkriningKunjungan)
                                        <a href="{{ route('skrining.create', ['ibu_hamil_id' => $k->ibu_hamil_id, 'kunjungan_id' => $k->id]) }}" 
                                           class="block text-[10px] text-teal-700 font-bold hover:underline mt-1">
                                            + Isi Skrining SPR
                                        </a>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if(!$k->status_hadir)
                                    <button type="button" 
                                            @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->jenis_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $hasSkriningKunjungan ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Konfirmasi Hadir</span>
                                    </button>
                                @else
                                    <button type="button" 
                                            @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->jenis_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $k->status_dilayani ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-200 transition-all">
                                        <span>Ubah Status</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada jadwal kunjungan tercatat di desa ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal Konfirmasi Kehadiran -->
        <div x-show="modalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="modalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" 
                     x-transition:enter="ease-out duration-300"
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
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white uppercase tracking-wider" x-text="'ANC ' + activeJenis"></span>
                                <button type="button" @click="modalOpen = false" class="text-white/80 hover:text-white text-base">✕</button>
                            </div>
                            <h3 class="text-base font-extrabold mt-1">Konfirmasi Kehadiran & Pelayanan ANC</h3>
                            <p class="text-xs text-teal-100 mt-0.5">Ibu Hamil: <strong class="text-white font-bold" x-text="activeNama"></strong></p>
                        </div>

                        <div class="p-6 space-y-4 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Tanggal Kunjungan Aktual <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="tanggal_kunjungan" x-model="activeTanggal" required
                                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                            </div>

                            <!-- Pemeriksaan Fisik: TB, BB, TD -->
                            <div class="p-3.5 rounded-2xl bg-teal-50/60 border border-teal-200/80 space-y-2.5">
                                <span class="text-[11px] font-bold text-teal-900 block">Pemeriksaan Fisik Kunjungan:</span>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TB (cm)</label>
                                        <input type="number" step="0.1" name="tinggi_badan" x-model="activeTb" placeholder="158"
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">BB (kg)</label>
                                        <input type="number" step="0.1" name="berat_badan" x-model="activeBb" placeholder="56"
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TD Sistol</label>
                                        <input type="number" name="tekanan_darah_sistol" x-model="activeSistol" placeholder="120"
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TD Diastol</label>
                                        <input type="number" name="tekanan_darah_diastol" x-model="activeDiastol" placeholder="80"
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="hidden" name="status_hadir" value="0" />
                                    <input type="checkbox" name="status_hadir" value="1" x-model="activeHadir"
                                           class="mt-0.5 h-4 w-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 cursor-pointer" />
                                    <div>
                                        <strong class="text-slate-800 block text-xs">Ibu Hamil Hadir di Faskes / Posyandu</strong>
                                        <span class="text-[11px] text-slate-500">Tandai jika ibu hamil datang pada jadwal kunjungan ini</span>
                                    </div>
                                </label>

                                <div class="pt-2.5 border-t border-slate-200">
                                    <template x-if="activeHasSkrining">
                                        <label class="flex items-start gap-3 cursor-pointer" :class="!activeHadir ? 'opacity-50 pointer-events-none' : ''">
                                            <input type="hidden" name="status_dilayani" value="0" />
                                            <input type="checkbox" name="status_dilayani" value="1" x-model="activeDilayani" :disabled="!activeHadir"
                                                   class="mt-0.5 h-4 w-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer" />
                                            <div>
                                                <strong class="text-slate-800 block text-xs">Pelayanan Medis Lengkap (Telah Dilayani)</strong>
                                                <span class="text-[11px] text-emerald-700 font-medium">✓ Skrining SPR Terpenuhi</span>
                                            </div>
                                        </label>
                                    </template>
                                    <template x-if="!activeHasSkrining">
                                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex flex-col gap-2">
                                            <div>
                                                <strong class="block">⚠️ Skrining SPR Belum Diisi</strong>
                                                <span class="text-[11px] text-amber-700">Status Dilayani hanya dapat dicatat setelah ibu hamil mengisi kuesioner Skrining SPR.</span>
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

    <!-- Kinerja Kader di Desa Ini -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800">Capaian Kinerja Kader Posyandu (Desa {{ $desa->nama }})</h3>
                <p class="text-xs text-slate-500">Monitoring produktivitas pendataan dan skrining kader binaan</p>
            </div>
            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">
                Kelola Seluruh Kader →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Kader</th>
                        <th class="py-3 px-4">No Handphone</th>
                        <th class="py-3 px-4 text-center">Bumil Diinput</th>
                        <th class="py-3 px-4 text-center">Skrining Dijalankan</th>
                        <th class="py-3 px-4 text-center">Temuan KRST</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kaders as $k)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $k['name'] }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $k['phone'] ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-800">
                                {{ $k['bumil_count'] }} Orang
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-teal-700">
                                {{ $k['skrining_count'] }} Kali
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($k['krst_found'] > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                                        {{ $k['krst_found'] }} Kasus
                                    </span>
                                @else
                                    <span class="text-slate-400">0</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Belum ada kader terdaftar di desa ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alert KRST di desa -->
    @if($urgentKRST->count() > 0)
        <div class="bg-rose-50/80 rounded-2xl border border-rose-200 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                    <h3 class="text-sm font-bold text-rose-900 uppercase tracking-wider">Pasien Risiko Sangat Tinggi (KRST) di Desa {{ $desa->nama }}</h3>
                </div>
                <span class="text-xs text-rose-700 font-semibold">Segera Lakukan Rujukan</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($urgentKRST as $u)
                    <div class="bg-white p-4 rounded-xl border border-rose-200 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full">
                                Skor {{ $u->total_skor }} (KRST)
                            </span>
                            <span class="text-[11px] text-slate-400">{{ $u->tanggal_skrining->format('d/m/Y') }}</span>
                        </div>
                        <h4 class="font-bold text-slate-800 mt-2">{{ $u->ibuHamil->nama }}</h4>
                        <p class="text-xs text-slate-500">Usia {{ $u->ibuHamil->usia ?? '-' }} Th • Diskrining oleh {{ $u->user->name }}</p>
                        <div class="mt-3 pt-2 border-t border-slate-100 flex justify-end">
                            <a href="{{ route('skrining.show', $u) }}" class="text-xs font-semibold text-rose-700 hover:text-rose-800">
                                Rincian Jawaban SPR →
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
    document.addEventListener('DOMContentLoaded', function () {
        // Kunjungan Chart
        const ctxKunjungan = document.getElementById('bidanKunjunganChart').getContext('2d');
        new Chart(ctxKunjungan, {
            type: 'bar',
            data: {
                labels: ['Total Jadwal', 'Hadir', 'Dilayani'],
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: [{{ $totalKunjungan }}, {{ $totalHadir }}, {{ $totalDilayani }}],
                    backgroundColor: ['#94a3b8', '#3b82f6', '#10b981'],
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Risiko Chart
        const ctxRisiko = document.getElementById('bidanRisikoChart').getContext('2d');
        new Chart(ctxRisiko, {
            type: 'doughnut',
            data: {
                labels: ['KRR (Risiko Rendah)', 'KRT (Risiko Tinggi)', 'KRST (Sangat Tinggi)'],
                datasets: [{
                    data: [{{ $skriningKRR }}, {{ $skriningKRT }}, {{ $skriningKRST }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%'
            }
        });
    });
</script>
@endpush

