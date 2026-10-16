@extends('layouts.app', ['title' => 'Tambah Pengguna Baru', 'header' => auth()->user()->isAdmin() ? 'Tambah Pengguna Baru' : 'Tambah Kader Baru'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Daftar Pengguna
            </a>
            <h2 class="text-xl font-bold text-slate-800">
                {{ auth()->user()->isAdmin() ? 'Pendaftaran Pengguna Baru' : 'Pendaftaran Kader Posyandu Baru' }}
            </h2>
            <p class="text-xs text-slate-500">
                {{ auth()->user()->isAdmin() ? 'Buat akun untuk Admin, Bidan Desa, atau Kader' : 'Daftarkan kader untuk membantu skrining di desa ' . auth()->user()->desa?->nama }}
            </p>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Nama Lengkap Petugas <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" required value="{{ old('name') }}"
                       placeholder="Contoh: Siti Rahmawati, S.Tr.Keb"
                       class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Alamat Email (Untuk Login) <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}"
                           placeholder="nama@edetimil.id"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Nomor Handphone / WhatsApp
                    </label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           placeholder="08123456789"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Peran (Role) <span class="text-rose-500">*</span>
                    </label>
                    @if(auth()->user()->isBidan())
                        <input type="hidden" name="role" value="kader" />
                        <div class="mt-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold">
                            Kader Posyandu (Bidan hanya dapat menambah kader)
                        </div>
                    @else
                        <select id="role" name="role" required
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            @foreach($roles as $r)
                                <option value="{{ $r->value }}" {{ old('role') === $r->value ? 'selected' : '' }}>
                                    {{ $r->label() }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label for="desa_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Penugasan Wilayah Desa
                    </label>
                    @if(auth()->user()->isBidan())
                        <input type="hidden" name="desa_id" value="{{ auth()->user()->desa_id }}" />
                        <div class="mt-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold">
                            {{ auth()->user()->desa?->nama }}
                        </div>
                    @else
                        <select id="desa_id" name="desa_id"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Tanpa Desa (Admin Pusat) --</option>
                            @foreach($desas as $d)
                                <option value="{{ $d->id }}" {{ old('desa_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama }} ({{ $d->kecamatan }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <div>
                <label for="nik" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Nomor Induk Kependudukan (NIK 16 Digit)
                </label>
                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" maxlength="16"
                       placeholder="3201xxxxxxxxxxxx"
                       class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required
                           placeholder="Minimal 8 karakter"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           placeholder="Ulangi kata sandi"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

