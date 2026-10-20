@extends('layouts.app', ['title' => 'Tambah Wilayah Binaan', 'header' => 'Tambah Wilayah Kerja Binaan'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('desa.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Daftar Wilayah Kerja Binaan
            </a>
            <h2 class="text-xl font-bold text-slate-800">Form Wilayah Kerja Binaan Puskesmas</h2>
            <p class="text-xs text-slate-500">Hubungkan wilayah desa/kelurahan resmi binaan Puskesmas untuk pelayanan deteksi dini dan pemantauan ibu hamil risiko tinggi</p>
        </div>

        <form action="{{ route('desa.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Nama Desa / Kelurahan Binaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" required value="{{ old('nama') }}"
                           placeholder="Contoh: Desa Pekan Heran"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div>
                    <label for="puskesmas" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Puskesmas / Instansi Pembina
                    </label>
                    <input type="text" id="puskesmas" name="puskesmas" list="puskesmas_list" value="{{ old('puskesmas', auth()->user()->instansi ?? '') }}"
                           placeholder="Contoh: UPT Puskesmas Pekan Heran"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    <datalist id="puskesmas_list">
                        @foreach($puskesmasList as $pkm)
                            <option value="{{ $pkm }}"></option>
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="kecamatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Kecamatan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="kecamatan" name="kecamatan" required value="{{ old('kecamatan', auth()->user()->kecamatan_kerja ?? '') }}"
                           placeholder="Contoh: Kecamatan Rengat Barat"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div>
                    <label for="kabupaten" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Kabupaten / Kota <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="kabupaten" name="kabupaten" required value="{{ old('kabupaten', auth()->user()->kabupaten ?: 'KABUPATEN INDRAGIRI HULU') }}"
                           placeholder="Contoh: KABUPATEN INDRAGIRI HULU"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div>
                    <label for="provinsi" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Provinsi
                    </label>
                    <input type="text" id="provinsi" name="provinsi" value="{{ old('provinsi', auth()->user()->provinsi ?: 'RIAU') }}"
                           placeholder="Contoh: RIAU"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>
            </div>

            <div>
                <label for="kode_desa" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Kode Desa Kemendagri (Opsional)
                </label>
                <input type="text" id="kode_desa" name="kode_desa" value="{{ old('kode_desa') }}"
                       placeholder="Contoh: 1402050012"
                       class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('desa.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                    Simpan Wilayah Kerja Binaan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

