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

            <div>
                <label for="nik" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    NIK (Nomor Induk Kependudukan)
                </label>
                <input type="text" id="nik" name="nik" value="{{ old('nik', $user->nik) }}" maxlength="16"
                       class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
            </div>

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

                <div>
                    <label for="current_password" class="block text-xs font-semibold text-slate-600">
                        Kata Sandi Saat Ini
                    </label>
                    <input type="password" id="current_password" name="current_password"
                           placeholder="••••••••"
                           class="mt-1 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-600">
                            Kata Sandi Baru
                        </label>
                        <input type="password" id="password" name="password"
                               placeholder="Minimal 8 karakter"
                               class="mt-1 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-600">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               placeholder="Ulangi kata sandi"
                               class="mt-1 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm" />
                    </div>
                </div>
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

