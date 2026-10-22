@extends('layouts.app', ['title' => 'Edit Kunjungan ANC & Jemput Bola', 'header' => 'Edit Kunjungan ANC & Jemput Bola'])

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ 
    tb: '{{ old('tinggi_badan', $kunjungan->tinggi_badan) }}',
    bb: '{{ old('berat_badan', $kunjungan->berat_badan) }}',
    sistol: '{{ old('tekanan_darah_sistol', $kunjungan->tekanan_darah_sistol) }}',
    diastol: '{{ old('tekanan_darah_diastol', $kunjungan->tekanan_darah_diastol) }}',
    lila: '{{ old('lila', $kunjungan->lila) }}',
    hb: '{{ old('hb', $kunjungan->hb) }}',
    metode: '{{ old('metode_kunjungan', $kunjungan->metode_kunjungan ?? 'posyandu') }}',
    tahapAnc: '{{ old('tahap_anc', $kunjungan->tahap_anc ?? $kunjungan->jenis_kunjungan ?? 'K1') }}',
    hasSkrining: {{ $hasSkrining ? 'true' : 'false' }},
    statusHadir: {{ $kunjungan->status_hadir ? 'true' : 'false' }}, 
    statusDilayani: {{ $kunjungan->status_dilayani ? 'true' : 'false' }},
    fotoPreview: null,
    previewImage(e) {
        const file = e.target.files[0];
        if (file) {
            this.fotoPreview = URL.createObjectURL(file);
        } else {
            this.fotoPreview = null;
        }
    },
    get imt() {
        if (!this.tb || !this.bb || this.tb <= 0) return null;
        const m = this.tb / 100;
        return (this.bb / (m * m)).toFixed(1);
    },
    get kategoriGizi() {
        const v = parseFloat(this.imt);
        if (isNaN(v)) return '-';
        if (v < 18.5) return 'Gizi Kurang (Underweight)';
        if (v <= 24.9) return 'Gizi Baik (Normal)';
        if (v <= 29.9) return 'Gizi Lebih (Overweight)';
        return 'Obesitas';
    }
}">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('kunjungan.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Data Kunjungan
            </a>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800">
                    Jemput Bola & ANC
                </span>
                <span class="text-xs text-slate-500">Edit Data Pemeriksaan</span>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mt-1">Edit Kunjungan: {{ $kunjungan->ibuHamil->nama }}</h2>
            <p class="text-xs text-slate-500">Perbarui tanggal, jenis kunjungan, hasil pemeriksaan fisik, atau status pendampingan jemput bola</p>
        </div>


        <form action="{{ route('kunjungan.update', $kunjungan) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Identitas Ibu Hamil -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                <span class="text-slate-500 block">Ibu Hamil:</span>
                <span class="font-bold text-slate-900 text-sm">{{ $kunjungan->ibuHamil->nama }}</span>
                <span class="text-slate-500 block mt-0.5">
                    {{ $kunjungan->desa?->nama }} ({{ $kunjungan->desa?->puskesmas ?? 'Puskesmas' }}) • NIK: {{ $kunjungan->ibuHamil->nik ?? '-' }}
                </span>
            </div>

            <!-- Metode Kunjungan -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">
                    Metode Kunjungan / Pendampingan <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="relative flex items-center p-3 rounded-2xl border cursor-pointer transition-all"
                           :class="metode === 'jemput_bola' ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="metode_kunjungan" value="jemput_bola" x-model="metode" class="sr-only" required />
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                  :class="metode === 'jemput_bola' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'">
                                🏠
                            </span>
                            <div>
                                <span class="text-xs font-bold block" :class="metode === 'jemput_bola' ? 'text-teal-900' : 'text-slate-800'">
                                    Jemput Bola
                                </span>
                                <span class="text-[10px] text-slate-500 block leading-tight">Kunjungan Rumah / Kader</span>
                            </div>
                        </div>
                    </label>

                    <label class="relative flex items-center p-3 rounded-2xl border cursor-pointer transition-all"
                           :class="metode === 'posyandu' ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="metode_kunjungan" value="posyandu" x-model="metode" class="sr-only" />
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                  :class="metode === 'posyandu' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'">
                                👥
                            </span>
                            <div>
                                <span class="text-xs font-bold block" :class="metode === 'posyandu' ? 'text-teal-900' : 'text-slate-800'">
                                    Posyandu
                                </span>
                                <span class="text-[10px] text-slate-500 block leading-tight">Pos Layanan Terpadu Desa</span>
                            </div>
                        </div>
                    </label>

                    <label class="relative flex items-center p-3 rounded-2xl border cursor-pointer transition-all"
                           :class="metode === 'puskesmas' ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="metode_kunjungan" value="puskesmas" x-model="metode" class="sr-only" />
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                  :class="metode === 'puskesmas' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'">
                                🏥
                            </span>
                            <div>
                                <span class="text-xs font-bold block" :class="metode === 'puskesmas' ? 'text-teal-900' : 'text-slate-800'">
                                    Puskesmas / Faskes
                                </span>
                                <span class="text-[10px] text-slate-500 block leading-tight">Pemeriksaan di Klinik Faskes</span>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Detail Khusus Layanan Jemput Bola -->
                <div x-show="metode === 'jemput_bola'" x-cloak class="mt-3 p-4 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🏠</span>
                        <span class="text-xs font-bold text-amber-900">Detail Layanan Jemput Bola (Kunjungan Rumah)</span>
                    </div>

                    <div>
                        <label for="alasan_jemput_bola" class="block text-[11px] font-semibold text-amber-900 uppercase tracking-wider mb-1">
                            Alasan Dilakukan Jemput Bola
                        </label>
                        <select id="alasan_jemput_bola" name="alasan_jemput_bola"
                                class="w-full rounded-xl border border-amber-300 bg-white px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 shadow-2xs">
                            <option value="">-- Pilih Alasan Kunjungan Rumah --</option>
                            <option value="mangkir_anc" {{ old('alasan_jemput_bola', $kunjungan->alasan_jemput_bola) === 'mangkir_anc' ? 'selected' : '' }}>⚠️ Mangkir / Terlambat Kunjungan Faskes</option>
                            <option value="risiko_tinggi" {{ old('alasan_jemput_bola', $kunjungan->alasan_jemput_bola) === 'risiko_tinggi' ? 'selected' : '' }}>🚨 Ibu Hamil Risiko Tinggi / Sangat Tinggi (KRT / KRST)</option>
                            <option value="tirah_baring" {{ old('alasan_jemput_bola', $kunjungan->alasan_jemput_bola) === 'tirah_baring' ? 'selected' : '' }}>🛌 Kondisi Lemah / Sakit / Bedrest</option>
                            <option value="kendala_geografis" {{ old('alasan_jemput_bola', $kunjungan->alasan_jemput_bola) === 'kendala_geografis' ? 'selected' : '' }}>🛵 Kendala Geografis / Akses Transportasi Sulit</option>
                            <option value="gizi_anemia" {{ old('alasan_jemput_bola', $kunjungan->alasan_jemput_bola) === 'gizi_anemia' ? 'selected' : '' }}>🥗 Pemantauan Khusus Gizi KEK / Anemia</option>
                            <option value="lainnya" {{ old('alasan_jemput_bola', $kunjungan->alasan_jemput_bola) === 'lainnya' ? 'selected' : '' }}>📋 Pemantauan Rutin Rumah Lainnya</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-amber-200/60">
                        <label class="flex items-center gap-2 cursor-pointer bg-white/80 p-2.5 rounded-xl border border-amber-200 text-xs">
                            <input type="checkbox" name="pendampingan_suami" value="1" {{ old('pendampingan_suami', $kunjungan->pendampingan_suami) ? 'checked' : '' }}
                                   class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-amber-300 rounded" />
                            <span class="text-[11px] font-semibold text-slate-700">
                                👨‍👩‍👧 Suami / Keluarga Hadir Mendampingi
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-white/80 p-2.5 rounded-xl border border-amber-200 text-xs">
                            <input type="checkbox" name="p4k_siap" value="1" {{ old('p4k_siap', $kunjungan->p4k_siap) ? 'checked' : '' }}
                                   class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-amber-300 rounded" />
                            <span class="text-[11px] font-semibold text-slate-700">
                                📑 Kesiapan P4K & Jalur Rujukan Siap
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tanggal & Tahap ANC -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal_kunjungan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Tanggal Kunjungan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative mt-1.5">
                        <input type="text" id="tanggal_kunjungan" name="tanggal_kunjungan" required
                               value="{{ old('tanggal_kunjungan', $kunjungan->tanggal_kunjungan->format('Y-m-d')) }}"
                               placeholder="HH-BB-TTTT (contoh: 18-10-2026)"
                               class="datepicker-dmy block w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-medium" />
                        <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-teal-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-[10px] text-teal-600 mt-1 block font-medium">Format: Hari-Bulan-Tahun (Contoh: {{ date('d-m-Y') }})</span>
                </div>

                <div>
                    <label for="tahap_anc" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Tahap Kunjungan ANC <span class="text-rose-500">*</span>
                    </label>
                    <select id="tahap_anc" name="tahap_anc" required x-model="tahapAnc"
                            class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                        <option value="K1">K1 (Trimester 1 - Deteksi Awal)</option>
                        <option value="K2">K2 (Trimester 2 - Evaluasi Pertumbuhan)</option>
                        <option value="K3">K3 (Trimester 2 - Pemantauan)</option>
                        <option value="K4">K4 (Trimester 3 - Kesiapan Persalinan)</option>
                        <option value="K5">K5 (Trimester 3 - Evaluasi Akhir)</option>
                        <option value="K6">K6 (Trimester 3 - Siap Persalinan)</option>
                    </select>
                    <input type="hidden" name="jenis_kunjungan" :value="tahapAnc" />
                </div>
            </div>

            <!-- Pemeriksaan Fisik: TB, BB, dan Tekanan Darah -->
            <div class="p-4 sm:p-5 rounded-2xl bg-teal-50/50 border border-teal-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-teal-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Pemeriksaan Fisik & Klinis ANC (10T)
                    </span>
                    <span class="text-[11px] text-teal-700 font-medium">Pengukuran Terkini</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            TB (cm) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.1" name="tinggi_badan" x-model="tb" required placeholder="Contoh: 158"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            BB (kg) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.1" name="berat_badan" x-model="bb" required placeholder="Contoh: 56.5"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            TD Sistol <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="tekanan_darah_sistol" x-model="sistol" required placeholder="120"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            TD Diastol <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="tekanan_darah_diastol" x-model="diastol" required placeholder="80"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                    </div>
                </div>

                <!-- Parameter Tambahan: LiLA, TFU, DJJ, Hb -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-teal-200/60">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            LiLA (cm)
                        </label>
                        <input type="number" step="0.1" name="lila" x-model="lila" placeholder="Contoh: 24.5"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">&lt; 23.5 cm = Risiko KEK</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            TFU (cm)
                        </label>
                        <input type="number" step="0.1" name="tfu" value="{{ old('tfu', $kunjungan->tfu) }}" placeholder="Contoh: 26"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Tinggi Fundus Uteri</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            DJJ (dpm)
                        </label>
                        <input type="number" name="djj" value="{{ old('djj', $kunjungan->djj) }}" placeholder="Contoh: 140"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Normal 120-160 dpm</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Hb (g/dL)
                        </label>
                        <input type="number" step="0.1" name="hb" x-model="hb" placeholder="Contoh: 11.5"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">&lt; 11 g/dL = Anemia</span>
                    </div>
                </div>

                <!-- Kepatuhan Tablet Fe -->
                <div class="pt-2 border-t border-teal-200/60">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="hidden" name="kepatuhan_fe" value="0" />
                        <input type="checkbox" name="kepatuhan_fe" value="1" {{ old('kepatuhan_fe', $kunjungan->kepatuhan_fe) ? 'checked' : '' }}
                               class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                        <span class="text-xs font-semibold text-slate-800">
                            Ibu Hamil Rutin Minum Tablet Tambah Darah (Fe)
                        </span>
                    </label>
                </div>

                <!-- Preview IMT Otomatis -->
                <template x-if="imt">
                    <div class="mt-2 p-2.5 rounded-xl bg-white border border-teal-200 text-xs flex items-center justify-between">
                        <span class="text-slate-600">
                            IMT Kunjungan Ini: <strong class="text-teal-800 font-bold" x-text="imt + ' kg/m²'"></strong>
                        </span>
                        <span class="font-semibold text-teal-800" x-text="'Status: ' + kategoriGizi"></span>
                    </div>
                </template>
            </div>

            <!-- Pendampingan Kader & Dokumentasi Jemput Bola -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Bentuk Pendampingan Kader & Dokumentasi
                    </span>
                </div>

                <div>
                    <label for="bentuk_pendampingan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Bentuk Pendampingan yang Diberikan
                    </label>
                    <textarea id="bentuk_pendampingan" name="bentuk_pendampingan" rows="2"
                              placeholder="Misal: Edukasi gizi seimbang, pemantauan konsumsi tablet Fe, pengecekan buku KIA..."
                              class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('bentuk_pendampingan', $kunjungan->bentuk_pendampingan) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="foto_pendampingan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Foto Dokumentasi Jemput Bola
                        </label>
                        @if ($kunjungan->foto_pendampingan)
                            <div class="mt-1.5 mb-2 flex items-center gap-3">
                                <a href="{{ $kunjungan->foto_pendampingan_url }}" target="_blank" class="block">
                                    <img src="{{ $kunjungan->foto_pendampingan_url }}" alt="Dokumentasi Kunjungan"
                                         class="w-24 h-16 object-cover rounded-xl border border-slate-300 shadow-xs hover:opacity-90" />
                                </a>
                                <span class="text-[11px] text-slate-500">Foto saat ini tersimpan. Pilih file baru untuk menggantinya.</span>
                            </div>
                        @endif
                        <input type="file" id="foto_pendampingan" name="foto_pendampingan" accept="image/*"
                               @change="previewImage($event)"
                               class="mt-1.5 block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer" />
                        <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, WEBP (Maksimal 5MB)</span>

                        <template x-if="fotoPreview">
                            <div class="mt-2 relative inline-block">
                                <img :src="fotoPreview" alt="Preview Foto Baru" class="w-32 h-24 object-cover rounded-xl border border-slate-200 shadow-xs" />
                                <span class="block text-[10px] text-teal-700 font-semibold mt-1">Preview Foto Baru</span>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label for="jadwal_kunjungan_berikutnya" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Jadwal Jemput Bola / Kunjungan Berikutnya
                        </label>
                        <div class="relative mt-1.5">
                            <input type="text" id="jadwal_kunjungan_berikutnya" name="jadwal_kunjungan_berikutnya"
                                   value="{{ old('jadwal_kunjungan_berikutnya', $kunjungan->jadwal_kunjungan_berikutnya?->format('Y-m-d')) }}"
                                   placeholder="HH-BB-TTTT (contoh: 18-11-2026)"
                                   class="datepicker-dmy block w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-medium" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-teal-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-500 mt-1 block">Format: Hari-Bulan-Tahun &bull; Akan muncul di pengingat jadwal kader lintas desa</span>
                    </div>
                </div>
            </div>

            <!-- Status Hadir & Dilayani Toggle Box -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <span class="text-xs font-bold text-slate-800 block">Status Pelaksanaan Pemeriksaan & Skrining SPR:</span>

                <div class="flex items-center">
                    <input type="checkbox" id="status_hadir" name="status_hadir" value="1" x-model="statusHadir"
                           class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                    <label for="status_hadir" class="ml-2.5 text-xs font-semibold text-slate-800">
                        Ibu Hamil Hadir di Faskes / Posyandu / Selesai Dijemput Bola
                    </label>
                </div>

                <div class="pt-2.5 border-t border-slate-200">
                    <template x-if="hasSkrining">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="status_dilayani" name="status_dilayani" value="1" x-model="statusDilayani"
                                       class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded" />
                                <span class="ml-2.5 text-xs font-semibold text-emerald-800">
                                    Telah Dilayani Pemeriksaan Medis Lengkap (Skrining SPR Ada)
                                </span>
                            </label>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">
                                ✓ Skrining Terpenuhi
                            </span>
                        </div>
                    </template>
                    <template x-if="!hasSkrining">
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span class="font-bold block text-amber-950">⚠️ Skrining SPR Kunjungan Ini Belum Diisi</span>
                                <span class="text-[11px] text-amber-800">Skrining Poedji Rochjati wajib dilakukan per kunjungan. Status dilayani lengkap otomatis aktif saat skrining disimpan.</span>
                            </div>
                            <a href="{{ route('skrining.create', ['ibu_hamil_id' => $kunjungan->ibu_hamil_id, 'kunjungan_id' => $kunjungan->id, 'tanggal' => $kunjungan->tanggal_kunjungan?->format('Y-m-d')]) }}"
                               class="px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shrink-0 transition-all shadow-xs text-center">
                                + Isi Skrining SPR Kunjungan Ini →
                            </a>
                        </div>
                    </template>
                </div>
            </div>

            <div>
                <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Catatan Pemeriksaan (Opsional)
                </label>
                <textarea id="catatan" name="catatan" rows="3"
                          class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('catatan', $kunjungan->catatan) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('kunjungan.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
