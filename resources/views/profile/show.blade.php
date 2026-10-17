@extends('layouts.app', ['title' => 'Pengaturan Profil Akun', 'header' => 'Pengaturan Profil Akun'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-4 border-b border-slate-100 pb-6 mb-6">
            <div class="w-16 h-16 rounded-2xl bg-teal-100 text-teal-700 font-bold text-xl flex items-center justify-center">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $user->name }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                        {{ $user->isAdmin() ? 'bg-purple-100 text-purple-800' : ($user->isBidan() ? 'bg-teal-100 text-teal-800' : 'bg-blue-100 text-blue-800') }}">
                        {{ $user->role->label() }}
                    </span>
                    @if($user->jabatan)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            {{ $user->jabatan }}
                        </span>
                    @endif
                    @if($user->instansi)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700">
                            🏛️ {{ $user->instansi }}
                        </span>
                    @endif
                    @if($user->desa)
                        <span class="text-xs text-slate-500">• {{ $user->desa->nama }}</span>
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}"
                       class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Nomor Handphone / WA
                    </label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nik" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        NIK (Nomor Induk Kependudukan)
                    </label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik', $user->nik) }}" maxlength="16"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
                </div>

                <div>
                    <label for="instansi" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Instansi / Unit Kerja / Posyandu
                    </label>
                    <input type="text" id="instansi" name="instansi" list="instansi-profile-list" value="{{ old('instansi', $user->instansi) }}"
                           placeholder="Contoh: Puskesmas Ciomas, Posyandu Melati 1"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    <datalist id="instansi-profile-list">
                        <option value="UPT Puskesmas">
                        <option value="Dinas Kesehatan">
                        <option value="Pustu (Puskesmas Pembantu)">
                        <option value="Poskesdes (Pos Kesehatan Desa)">
                        <option value="Posyandu">
                    </datalist>
                </div>
            </div>

            @if($user->isAdmin())
                <div class="p-4 sm:p-5 bg-gradient-to-br from-indigo-50/70 to-purple-50/50 rounded-2xl border border-indigo-100 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        <h3 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">
                            Informasi Jabatan & Kepegawaian (Admin / Pimpinan Puskesmas)
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nip" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                NIP (Nomor Induk Pegawai)
                            </label>
                            <input type="text" id="nip" name="nip" value="{{ old('nip', $user->nip) }}" maxlength="30"
                                   placeholder="Contoh: 198001012005011001"
                                   class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm font-mono" />
                        </div>

                        <div>
                            <label for="jabatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Jabatan / Kedudukan
                            </label>
                            <input type="text" id="jabatan" name="jabatan" list="jabatan-profile-list" value="{{ old('jabatan', $user->jabatan) }}"
                                   placeholder="Pilih atau ketik jabatan..."
                                   class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm" />
                            <datalist id="jabatan-profile-list">
                                <option value="Kepala Puskesmas">
                                <option value="Ketua Tata Usaha (Kasubag TU)">
                                <option value="Koordinator Pelayanan KIA">
                                <option value="Penanggung Jawab Mutu">
                                <option value="Administrator Sistem E-Detimil">
                            </datalist>
                        </div>
                    </div>

                    <div>
                        <label for="str" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Nomor STR (Surat Tanda Registrasi) <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <input type="text" id="str" name="str" value="{{ old('str', $user->str) }}" maxlength="50"
                               placeholder="Contoh: 1234567890123456"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm font-mono" />
                        <span class="text-[10px] text-slate-500 mt-1 block">Opsional: Diisi bila pimpinan Puskesmas berlatar belakang profesi medis atau kebidanan.</span>
                    </div>
                </div>
            @endif

            <div>
                <label for="alamat" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Alamat Lengkap
                </label>
                <textarea id="alamat" name="alamat" rows="2"
                          class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('alamat', $user->alamat) }}</textarea>
            </div>

            <!-- Ganti Password -->
            <div class="pt-4 border-t border-slate-100 space-y-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 block">
                    Ubah Kata Sandi (Kosongkan bila tidak diganti)
                </span>

                <div x-data="{ showCurrent: false }">
                    <label for="current_password" class="block text-xs font-semibold text-slate-600">
                        Kata Sandi Saat Ini
                    </label>
                    <div class="mt-1 relative rounded-xl shadow-2xs">
                        <input :type="showCurrent ? 'text' : 'password'"
                               id="current_password" name="current_password"
                               placeholder="••••••••"
                               class="block w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm font-mono tracking-wide" />
                        <button type="button" @click="showCurrent = !showCurrent"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden transition-colors"
                                tabindex="-1">
                            <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showCurrent" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <x-password-fields :required="false" label="Kata Sandi Baru" confirmLabel="Konfirmasi Kata Sandi Baru" />
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

