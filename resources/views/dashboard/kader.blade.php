@extends('layouts.app', ['title' => 'Dashboard Kader', 'header' => 'Dashboard Kader: ' . $desa->nama])

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-teal-600 to-emerald-600 rounded-3xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="relative z-10">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur mb-2">
                🤝 Kader Posyandu • {{ $desa->nama }}
            </span>
            <h2 class="text-2xl font-extrabold tracking-tight">Selamat Bertugas, {{ auth()->user()->name }}!</h2>
            <p class="text-teal-100 text-xs sm:text-sm mt-1 max-w-2xl">
                Pantau capaian persentase layanan desa Anda dan rekapitulasi kinerja skrining mandiri secara langsung.
            </p>
            <div class="mt-4 flex flex-wrap gap-2.5">
                <a href="{{ route('skrining.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-teal-800 text-xs font-bold hover:bg-teal-50 shadow-sm transition-all">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Mulai Input Skrining SPR Baru</span>
                </a>
                <a href="{{ route('ibu-hamil.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-700/80 hover:bg-teal-700 text-white text-xs font-semibold backdrop-blur transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>Daftarkan Ibu Hamil Baru</span>
                </a>
            </div>
        </div>
        <div class="absolute right-0 bottom-0 translate-x-8 translate-y-8 opacity-10 pointer-events-none">
            <svg class="w-64 h-64 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </div>
    </div>

    <!-- SEKSI 1: Capaian Wilayah Desa (%) -->
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <div>
                <h3 class="text-base font-bold text-slate-800">Capaian Pelayanan Desa {{ $desa->nama }}</h3>
                <p class="text-xs text-slate-500">Persentase keberhasilan kunjungan dan cakupan deteksi dini di wilayah kerja Anda</p>
            </div>
            <span class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg font-medium">Data Terkini</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Capaian Hadir Desa -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Capaian Kehadiran Desa</span>
                        <h4 class="text-3xl font-extrabold text-blue-600 mt-1">{{ $desaPersenHadir }}%</h4>
                        <span class="text-xs text-slate-500 mt-1 block">{{ $desaHadirCount }} hadir dari {{ $desaKunjunganCount }} kunjungan</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, $desaPersenHadir) }}%"></div>
                </div>
            </div>

            <!-- Capaian Dilayani Desa -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Capaian Dilayani Desa</span>
                        <h4 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $desaPersenDilayani }}%</h4>
                        <span class="text-xs text-slate-500 mt-1 block">{{ $desaDilayaniCount }} terlayani dari total target</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ min(100, $desaPersenDilayani) }}%"></div>
                </div>
            </div>

            <!-- Cakupan Skrining Desa -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cakupan Skrining SPR Desa</span>
                        <h4 class="text-3xl font-extrabold text-teal-600 mt-1">{{ $desaPersenSkrining }}%</h4>
                        <span class="text-xs text-slate-500 mt-1 block">{{ $desaTotalSkrining }} skrining / {{ $desaBumilCount }} bumil</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-teal-600 h-2 rounded-full" style="width: {{ min(100, $desaPersenSkrining) }}%"></div>
                </div>
            </div>
        </div>
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
                <p class="text-xs text-slate-500">Konfirmasi kehadiran ibu hamil pada jadwal posyandu / kunjungan faskes secara langsung</p>
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

    <!-- SEKSI 2: Kinerja Pribadi Kader -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Kinerja Mandiri Anda (Sebagai Kader)</h3>
                <p class="text-xs text-slate-500">Total data ibu hamil dan skrining yang telah Anda input ke dalam sistem</p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800">
                Pencapaian Saya
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <span class="text-xs text-slate-500 block">Ibu Hamil Diinput</span>
                <span class="text-2xl font-extrabold text-slate-800 mt-1 block">{{ $myTotalBumil }}</span>
                <span class="text-[11px] text-slate-400">Data pribadi</span>
            </div>

            <div class="p-4 rounded-xl bg-teal-50 border border-teal-200 text-center">
                <span class="text-xs text-teal-700 font-semibold block">Skrining Dijalankan</span>
                <span class="text-2xl font-extrabold text-teal-800 mt-1 block">{{ $myTotalSkrining }}</span>
                <span class="text-[11px] text-teal-600">Sesi SPR</span>
            </div>

            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                <span class="text-xs text-emerald-700 font-semibold block">KRR (Risiko Rendah)</span>
                <span class="text-2xl font-extrabold text-emerald-800 mt-1 block">{{ $myKRR }}</span>
                <span class="text-[11px] text-emerald-600">Skor = 2</span>
            </div>

            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-center">
                <span class="text-xs text-amber-700 font-semibold block">KRT (Risiko Tinggi)</span>
                <span class="text-2xl font-extrabold text-amber-800 mt-1 block">{{ $myKRT }}</span>
                <span class="text-[11px] text-amber-600">Skor 6 - 10</span>
            </div>

            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-center">
                <span class="text-xs text-rose-700 font-semibold block">KRST (Sangat Tinggi)</span>
                <span class="text-2xl font-extrabold text-rose-800 mt-1 block">{{ $myKRST }}</span>
                <span class="text-[11px] text-rose-600">Skor ≥ 12</span>
            </div>
        </div>
    </div>

    <!-- Riwayat Skrining Terbaru oleh Kader -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Skrining SPR Terakhir yang Anda Lakukan</h3>
                <p class="text-xs text-slate-500">Daftar riwayat evaluasi risiko ibu hamil oleh Anda</p>
            </div>
            <a href="{{ route('skrining.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">
                Lihat Semua Skrining →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Nama Ibu Hamil</th>
                        <th class="py-3 px-4">Usia Kehamilan</th>
                        <th class="py-3 px-4 text-center">Total Skor</th>
                        <th class="py-3 px-4 text-center">Kategori Risiko</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentSkrinings as $s)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $s->tanggal_skrining->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $s->ibuHamil->nama }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $s->usia_kehamilan_minggu ? $s->usia_kehamilan_minggu . ' Minggu' : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ $s->total_skor }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $s->kategori_risiko->badgeClasses() }}">
                                    {{ $s->kategori_risiko->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('skrining.show', $s) }}" class="font-semibold text-teal-700 hover:text-teal-800">
                                    Rincian →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Anda belum melakukan skrining untuk ibu hamil</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Catatan Hak Akses Laporan -->
    <div class="p-4 rounded-xl bg-slate-100 border border-slate-200/80 text-xs text-slate-600 flex items-start gap-3">
        <svg class="w-5 h-5 text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <strong class="font-semibold text-slate-700">Informasi Akses Laporan:</strong>
            Akun Kader memiliki akses penuh untuk memantau capaian desa dan kinerja pribadi secara langsung di dashboard ini. Fitur unduh berkas laporan dalam format Excel dikelola oleh Bidan Desa dan Admin Puskesmas demi verifikasi data resmi.
        </div>
    </div>

</div>
@endsection

