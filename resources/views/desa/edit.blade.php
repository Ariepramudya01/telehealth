@extends('layouts.app', ['title' => 'Edit Desa: ' . $desa->nama, 'header' => 'Edit Data Desa'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('desa.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Daftar Desa
            </a>
            <h2 class="text-xl font-bold text-slate-800">Edit Data: {{ $desa->nama }}</h2>
            <p class="text-xs text-slate-500">Perbarui informasi wilayah kerja</p>
        </div>

        <form action="{{ route('desa.update', $desa) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Nama Desa / Kelurahan <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="nama" name="nama" required value="{{ old('nama', $desa->nama) }}"
                       class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="kecamatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Kecamatan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="kecamatan" name="kecamatan" required value="{{ old('kecamatan', $desa->kecamatan) }}"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div>
                    <label for="kabupaten" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Kabupaten / Kota <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="kabupaten" name="kabupaten" required value="{{ old('kabupaten', $desa->kabupaten) }}"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>
            </div>

            <div>
                <label for="kode_desa" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Kode Desa
                </label>
                <input type="text" id="kode_desa" name="kode_desa" value="{{ old('kode_desa', $desa->kode_desa) }}"
                       class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('desa.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
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

