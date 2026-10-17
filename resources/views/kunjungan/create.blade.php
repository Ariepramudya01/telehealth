@extends('layouts.app', ['title' => 'Catat Kunjungan ANC', 'header' => 'Catat Kunjungan ANC'])

@section('content')
<div class="max-w-2xl mx-auto" x-data="{
    ibuHamils: @js($ibuHamilsData),
    selectedId: '{{ old('ibu_hamil_id', $selectedIbuHamilId) }}',
    tb: '{{ old('tinggi_badan') }}',
    bb: '{{ old('berat_badan') }}',
    sistol: '{{ old('tekanan_darah_sistol') }}',
    diastol: '{{ old('tekanan_darah_diastol') }}',
    hasSkrining: false,
    statusHadir: true,
    statusDilayani: false,
    init() {
        if (this.selectedId) {
            this.onBumilChange();
        }
    },
    onBumilChange() {
        const found = this.ibuHamils.find(b => b.id == this.selectedId);
        if (found) {
            this.tb = found.tinggi_badan || '';
            this.bb = found.berat_badan || '';
            this.sistol = found.sistol || '';
            this.diastol = found.diastol || '';
            this.hasSkrining = Boolean(found.has_skrining);
            this.statusDilayani = this.hasSkrining;
        } else {
            this.hasSkrining = false;
            this.statusDilayani = false;
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
            <h2 class="text-xl font-bold text-slate-800">Pencatatan Kehadiran & Pelayanan ANC</h2>
            <p class="text-xs text-slate-500">Rekam pemeriksaan fisik, TB, BB, tekanan darah, dan status pelayanan kunjungan</p>
        </div>

        <form action="{{ route('kunjungan.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="ibu_hamil_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Pilih Ibu Hamil <span class="text-rose-500">*</span>
                </label>
                <select id="ibu_hamil_id" name="ibu_hamil_id" required
                        x-model="selectedId"
                        @change="onBumilChange()"
                        class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                    <option value="">-- Pilih Ibu Hamil --</option>
                    @foreach($ibuHamils as $bumil)
                        <option value="{{ $bumil->id }}" {{ (old('ibu_hamil_id', $selectedIbuHamilId) == $bumil->id) ? 'selected' : '' }}>
                            {{ $bumil->nama }} (NIK: {{ $bumil->nik ?? '-' }}) - {{ $bumil->desa?->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal_kunjungan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Tanggal Kunjungan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="tanggal_kunjungan" name="tanggal_kunjungan" required
                           value="{{ old('tanggal_kunjungan', date('Y-m-d')) }}"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div>
                    <label for="jenis_kunjungan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Jenis Kunjungan ANC <span class="text-rose-500">*</span>
                    </label>
                    <select id="jenis_kunjungan" name="jenis_kunjungan" required
                            class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                        <option value="K1" {{ old('jenis_kunjungan') === 'K1' ? 'selected' : '' }}>K1 (Kunjungan Pertama Trimester 1)</option>
                        <option value="K2" {{ old('jenis_kunjungan') === 'K2' ? 'selected' : '' }}>K2 (Trimester 2)</option>
                        <option value="K3" {{ old('jenis_kunjungan') === 'K3' ? 'selected' : '' }}>K3 (Trimester 2/3)</option>
                        <option value="K4" {{ old('jenis_kunjungan') === 'K4' ? 'selected' : '' }}>K4 (Kunjungan Keempat Trimester 3)</option>
                        <option value="K5" {{ old('jenis_kunjungan') === 'K5' ? 'selected' : '' }}>K5 (Trimester 3)</option>
                        <option value="K6" {{ old('jenis_kunjungan') === 'K6' ? 'selected' : '' }}>K6 (Kunjungan Keenam Terakhir)</option>
                    </select>
                </div>
            </div>

            <!-- Pemeriksaan Fisik Wajib: TB, BB, dan Tekanan Darah -->
            <div class="p-4 rounded-2xl bg-teal-50/50 border border-teal-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-teal-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Pemeriksaan Fisik Kunjungan (Wajib)
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

            <!-- Status Hadir & Dilayani Toggle Box -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <span class="text-xs font-bold text-slate-800 block">Status Pelaksanaan Pemeriksaan & Skrining SPR:</span>

                <div class="flex items-center">
                    <input type="checkbox" id="status_hadir" name="status_hadir" value="1" x-model="statusHadir"
                           class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                    <label for="status_hadir" class="ml-2.5 text-xs font-semibold text-slate-800">
                        Ibu Hamil Hadir di Faskes / Posyandu
                    </label>
                </div>

                <!-- Status Dilayani Terkoneksi Skrining SPR -->
                <div class="pt-2.5 border-t border-slate-200">
                    <template x-if="hasSkrining">
                        <div class="flex items-center gap-2 text-emerald-800 text-xs">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                ✓ Skrining SPR Selesai
                            </span>
                            <span>Ibu hamil ini sudah mengisi skrining SPR. Status <strong>Dilayani</strong> otomatis aktif.</span>
                            <input type="hidden" name="status_dilayani" value="1" />
                        </div>
                    </template>
                    <template x-if="!hasSkrining && selectedId">
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs space-y-1">
                            <div class="flex items-center gap-1.5 font-bold">
                                <span>⚠️ Status Dilayani Belum Aktif</span>
                            </div>
                            <p class="text-[11px] text-amber-700">
                                Ibu hamil ini belum mengisi Skrining Deteksi Dini SPR. Berdasarkan SOP, status pelayanan lengkap (<strong>Dilayani</strong>) akan otomatis aktif ketika kuesioner Skrining SPR diisi.
                            </p>
                        </div>
                    </template>
                </div>
            </div>

            <div>
                <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Catatan Pemeriksaan / Resep / Keluhan (Opsional)
                </label>
                <textarea id="catatan" name="catatan" rows="2"
                          placeholder="Pemberian tablet Fe, saran dokter/bidan, keluhan pasien..."
                          class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('catatan') }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-end gap-2.5">
                <a href="{{ route('kunjungan.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </a>

                <template x-if="!hasSkrining">
                    <button type="submit" name="action" value="save_and_screen"
                            class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                        </svg>
                        <span>Simpan & Lanjut Isi Skrining SPR (Dilayani)</span>
                    </button>
                </template>

                <button type="submit" name="action" value="save"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold"
                        :class="hasSkrining ? 'bg-teal-600 hover:bg-teal-700 text-white font-bold shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300'">
                    <span x-text="hasSkrining ? 'Simpan Catatan Kunjungan' : 'Simpan Kunjungan Saja'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

