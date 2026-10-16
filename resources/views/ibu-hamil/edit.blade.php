@extends('layouts.app', ['title' => 'Edit Ibu Hamil: ' . $ibuHamil->nama, 'header' => 'Edit Data Ibu Hamil'])

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('ibu-hamil.show', $ibuHamil) }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Rincian Ibu Hamil
            </a>
            <h2 class="text-xl font-bold text-slate-800">Edit Data: {{ $ibuHamil->nama }}</h2>
            <p class="text-xs text-slate-500">Perbarui informasi identitas, biometrik fisik, atau riwayat kehamilan</p>
        </div>

        <form action="{{ route('ibu-hamil.update', $ibuHamil) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

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
                        <input type="text" id="nama" name="nama" required value="{{ old('nama', $ibuHamil->nama) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="nik" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            NIK (Nomor Induk Kependudukan)
                        </label>
                        <input type="text" id="nik" name="nik" value="{{ old('nik', $ibuHamil->nik) }}" maxlength="16"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
                    </div>

                    <div>
                        <label for="tanggal_lahir" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Tanggal Lahir Ibu
                        </label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $ibuHamil->tanggal_lahir?->format('Y-m-d')) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="nama_suami" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Nama Suami
                        </label>
                        <input type="text" id="nama_suami" name="nama_suami" value="{{ old('nama_suami', $ibuHamil->nama_suami) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            No Handphone / WhatsApp
                        </label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $ibuHamil->no_hp) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div>
                            <label for="desa_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Wilayah Desa <span class="text-rose-500">*</span>
                            </label>
                            <select id="desa_id" name="desa_id" required
                                    class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                                @foreach($desas as $d)
                                    <option value="{{ $d->id }}" {{ old('desa_id', $ibuHamil->desa_id) == $d->id ? 'selected' : '' }}>
                                        {{ $d->nama }} ({{ $d->kecamatan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <label for="alamat" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Alamat Lengkap
                    </label>
                    <textarea id="alamat" name="alamat" rows="2"
                              class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('alamat', $ibuHamil->alamat) }}</textarea>
                </div>
            </div>

            <!-- Fisik & Obstetri -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-700 border-b border-teal-100 pb-2 mb-4">
                    2. Data Fisik & Riwayat Kehamilan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tinggi_badan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Tinggi Badan (cm)
                        </label>
                        <input type="number" step="0.1" id="tinggi_badan" name="tinggi_badan" value="{{ old('tinggi_badan', $ibuHamil->tinggi_badan) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="berat_badan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Berat Badan (kg)
                        </label>
                        <input type="number" step="0.1" id="berat_badan" name="berat_badan" value="{{ old('berat_badan', $ibuHamil->berat_badan) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="golongan_darah" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Golongan Darah
                        </label>
                        <select id="golongan_darah" name="golongan_darah"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Belum Diketahui --</option>
                            <option value="A" {{ old('golongan_darah', $ibuHamil->golongan_darah) === 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('golongan_darah', $ibuHamil->golongan_darah) === 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB" {{ old('golongan_darah', $ibuHamil->golongan_darah) === 'AB' ? 'selected' : '' }}>AB</option>
                            <option value="O" {{ old('golongan_darah', $ibuHamil->golongan_darah) === 'O' ? 'selected' : '' }}>O</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="hpht" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            HPHT (Hari Pertama Haid Terakhir)
                        </label>
                        <input type="date" id="hpht" name="hpht" value="{{ old('hpht', $ibuHamil->hpht?->format('Y-m-d')) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="taksiran_persalinan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Taksiran Persalinan (HPL)
                        </label>
                        <input type="date" id="taksiran_persalinan" name="taksiran_persalinan" value="{{ old('taksiran_persalinan', $ibuHamil->taksiran_persalinan?->format('Y-m-d')) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>
                </div>

                <!-- Paritas G - P - A -->
                <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-xs font-bold text-slate-700 block mb-2">Riwayat Paritas Obstetri:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Gravida</label>
                            <input type="number" min="0" name="gravida" value="{{ old('gravida', $ibuHamil->gravida) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Para</label>
                            <input type="number" min="0" name="para" value="{{ old('para', $ibuHamil->para) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Abortus</label>
                            <input type="number" min="0" name="abortus" value="{{ old('abortus', $ibuHamil->abortus) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Anak Hidup</label>
                            <input type="number" min="0" name="jumlah_anak_hidup" value="{{ old('jumlah_anak_hidup', $ibuHamil->jumlah_anak_hidup) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('ibu-hamil.show', $ibuHamil) }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
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

