@extends('layouts.app', ['title' => 'Pendaftaran Ibu Hamil', 'header' => 'Pendaftaran Ibu Hamil Baru'])

@section('content')
<div class="max-w-4xl mx-auto" x-data="{
    hpht: '{{ old('hpht') }}',
    tp: '{{ old('taksiran_persalinan') }}',
    calculateTP() {
        if (!this.hpht) return;
        const d = new Date(this.hpht);
        if (isNaN(d.getTime())) return;
        d.setDate(d.getDate() + 280);
        this.tp = d.toISOString().split('T')[0];
    }
}">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('ibu-hamil.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Data Ibu Hamil
            </a>
            <h2 class="text-xl font-bold text-slate-800">Formulir Data Pribadi Ibu Hamil</h2>
            <p class="text-xs text-slate-500">Lengkapi data identitas, biometrik fisik, dan riwayat obstetri kehamilan</p>
        </div>

        <form action="{{ route('ibu-hamil.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Identitas Pribadi -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-700 border-b border-teal-100 pb-2 mb-4">
                    1. Identitas Ibu & Pasangan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Nama Lengkap Ibu Hamil <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" required value="{{ old('nama') }}"
                               placeholder="Contoh: Ny. Siti Maryam"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="nik" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            NIK (Nomor Induk Kependudukan 16 Digit)
                        </label>
                        <input type="text" id="nik" name="nik" value="{{ old('nik') }}" maxlength="16"
                               placeholder="3201xxxxxxxxxxxx"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
                    </div>

                    <div>
                        <label for="tanggal_lahir" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Tanggal Lahir Ibu
                        </label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="nama_suami" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Nama Suami
                        </label>
                        <input type="text" id="nama_suami" name="nama_suami" value="{{ old('nama_suami') }}"
                               placeholder="Contoh: Tn. Ahmad Fauzi"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            No Handphone / WhatsApp
                        </label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}"
                               placeholder="08xxxxxxxxxx"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="desa_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Wilayah Desa <span class="text-rose-500">*</span>
                        </label>
                        @if(!auth()->user()->isAdmin())
                            <input type="hidden" name="desa_id" value="{{ auth()->user()->desa_id }}" />
                            <div class="mt-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold">
                                {{ auth()->user()->desa?->nama }}
                            </div>
                        @else
                            <select id="desa_id" name="desa_id" required
                                    class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                                <option value="">-- Pilih Wilayah Desa --</option>
                                @foreach($desas as $d)
                                    <option value="{{ $d->id }}" {{ (old('desa_id', request('desa_id')) == $d->id) ? 'selected' : '' }}>
                                        {{ $d->nama }} ({{ $d->kecamatan }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                <div class="mt-4">
                    <label for="alamat" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Alamat Tempat Tinggal Lengkap
                    </label>
                    <textarea id="alamat" name="alamat" rows="2"
                              placeholder="RT/RW, Kampung/Dusun, Desa..."
                              class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('alamat') }}</textarea>
                </div>
            </div>

            <!-- Fisik & Obstetri -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-700 border-b border-teal-100 pb-2 mb-4">
                    2. Data Fisik & Riwayat Kehamilan (Obstetri)
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tinggi_badan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Tinggi Badan (cm)
                        </label>
                        <input type="number" step="0.1" id="tinggi_badan" name="tinggi_badan" value="{{ old('tinggi_badan') }}"
                               placeholder="Contoh: 155"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">SPR: TB ≤ 145 cm faktor risiko APGO</span>
                    </div>

                    <div>
                        <label for="berat_badan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Berat Badan (kg)
                        </label>
                        <input type="number" step="0.1" id="berat_badan" name="berat_badan" value="{{ old('berat_badan') }}"
                               placeholder="Contoh: 55"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="golongan_darah" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Golongan Darah
                        </label>
                        <select id="golongan_darah" name="golongan_darah"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Belum Diketahui --</option>
                            <option value="A" {{ old('golongan_darah') === 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('golongan_darah') === 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB" {{ old('golongan_darah') === 'AB' ? 'selected' : '' }}>AB</option>
                            <option value="O" {{ old('golongan_darah') === 'O' ? 'selected' : '' }}>O</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="hpht" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            HPHT (Hari Pertama Haid Terakhir)
                        </label>
                        <input type="date" id="hpht" name="hpht" x-model="hpht" @change="calculateTP()"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="taksiran_persalinan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 flex items-center justify-between">
                            <span>Taksiran Persalinan (HPL)</span>
                            <span class="text-[10px] text-teal-600 font-normal">Otomatis terhitung dari HPHT</span>
                        </label>
                        <input type="date" id="taksiran_persalinan" name="taksiran_persalinan" x-model="tp"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm bg-teal-50/30" />
                    </div>
                </div>

                <!-- Paritas G - P - A -->
                <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-xs font-bold text-slate-700 block mb-2">Riwayat Paritas Obstetri:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Gravida (Hamil ke-)</label>
                            <input type="number" min="0" name="gravida" value="{{ old('gravida', 1) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Para (Pernah Melahirkan)</label>
                            <input type="number" min="0" name="para" value="{{ old('para', 0) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Abortus (Keguguran)</label>
                            <input type="number" min="0" name="abortus" value="{{ old('abortus', 0) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Jumlah Anak Hidup</label>
                            <input type="number" min="0" name="jumlah_anak_hidup" value="{{ old('jumlah_anak_hidup', 0) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('ibu-hamil.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                    Simpan & Lanjutkan ke Skrining
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

