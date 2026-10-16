@extends('layouts.app', ['title' => 'Edit Kunjungan ANC', 'header' => 'Edit Kunjungan ANC'])

@section('content')
<div class="max-w-2xl mx-auto" x-data="{ 
    statusHadir: {{ $kunjungan->status_hadir ? 'true' : 'false' }}, 
    statusDilayani: {{ $kunjungan->status_dilayani ? 'true' : 'false' }} 
}">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('kunjungan.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Data Kunjungan
            </a>
            <h2 class="text-xl font-bold text-slate-800">Edit Kunjungan: {{ $kunjungan->ibuHamil->nama }}</h2>
            <p class="text-xs text-slate-500">Perbarui tanggal, jenis kunjungan, atau status pemeriksaan</p>
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

            <!-- Status Hadir & Dilayani Toggle Box -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <span class="text-xs font-bold text-slate-800 block">Status Pelaksanaan Pemeriksaan:</span>

                <div class="flex items-center">
                    <input type="checkbox" id="status_hadir" name="status_hadir" value="1" x-model="statusHadir"
                           class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                    <label for="status_hadir" class="ml-2.5 text-xs font-semibold text-slate-800">
                        Ibu Hamil Hadir di Faskes / Posyandu
                    </label>
                </div>

                <div class="flex items-center pl-6" x-show="statusHadir" x-transition>
                    <input type="checkbox" id="status_dilayani" name="status_dilayani" value="1" x-model="statusDilayani"
                           class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded" />
                    <label for="status_dilayani" class="ml-2.5 text-xs font-semibold text-emerald-800">
                        Telah Dilayani Pemeriksaan Medis Lengkap
                    </label>
                </div>
            </div>

            <div>
                <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Catatan Pemeriksaan
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

