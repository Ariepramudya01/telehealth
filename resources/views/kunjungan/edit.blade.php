@extends('layouts.app', ['title' => 'Edit Kunjungan ANC', 'header' => 'Edit Kunjungan ANC'])

@section('content')
<div class="max-w-2xl mx-auto" x-data="{ 
    tb: '{{ old('tinggi_badan', $kunjungan->tinggi_badan ?? $kunjungan->ibuHamil->tinggi_badan) }}',
    bb: '{{ old('berat_badan', $kunjungan->berat_badan ?? $kunjungan->ibuHamil->berat_badan) }}',
    sistol: '{{ old('tekanan_darah_sistol', $kunjungan->tekanan_darah_sistol ?? $kunjungan->ibuHamil->tekanan_darah_sistol) }}',
    diastol: '{{ old('tekanan_darah_diastol', $kunjungan->tekanan_darah_diastol ?? $kunjungan->ibuHamil->tekanan_darah_diastol) }}',
    hasSkrining: {{ $hasSkrining ? 'true' : 'false' }},
    statusHadir: {{ $kunjungan->status_hadir ? 'true' : 'false' }}, 
    statusDilayani: {{ $kunjungan->status_dilayani ? 'true' : 'false' }},
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
            <h2 class="text-xl font-bold text-slate-800">Edit Kunjungan: {{ $kunjungan->ibuHamil->nama }}</h2>
            <p class="text-xs text-slate-500">Perbarui tanggal, jenis kunjungan, hasil pemeriksaan fisik, atau status pelayanan</p>
        </div>

        <form action="{{ route('kunjungan.update', $kunjungan) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                <span class="text-slate-500 block">Ibu Hamil:</span>
                <span class="font-bold text-slate-900 text-sm">{{ $kunjungan->ibuHamil->nama }}</span>
                <span class="text-slate-500 block mt-0.5">{{ $kunjungan->desa?->nama }} • NIK: {{ $kunjungan->ibuHamil->nik ?? '-' }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal_kunjungan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Tanggal Kunjungan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="tanggal_kunjungan" name="tanggal_kunjungan" required
                           value="{{ old('tanggal_kunjungan', $kunjungan->tanggal_kunjungan->format('Y-m-d')) }}"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div>
                    <label for="jenis_kunjungan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Jenis Kunjungan ANC <span class="text-rose-500">*</span>
                    </label>
                    <select id="jenis_kunjungan" name="jenis_kunjungan" required
                            class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                        @foreach(['K1', 'K2', 'K3', 'K4', 'K5', 'K6'] as $k)
                            <option value="{{ $k }}" {{ old('jenis_kunjungan', $kunjungan->jenis_kunjungan) === $k ? 'selected' : '' }}>
                                Kunjungan {{ $k }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Pemeriksaan Fisik: TB, BB, dan Tekanan Darah -->
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
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center justify-between gap-3">
                            <div>
                                <span class="font-bold block">⚠️ Skrining SPR Belum Diisi</span>
                                <span class="text-[11px] text-amber-700">Status dilayani akan aktif otomatis ketika skrining SPR diselesaikan.</span>
                            </div>
                            <a href="{{ route('skrining.create', ['ibu_hamil_id' => $kunjungan->ibu_hamil_id, 'kunjungan_id' => $kunjungan->id]) }}"
                               class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shrink-0 transition-all shadow-xs">
                                + Isi Skrining SPR Sekarang →
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

