@extends('layouts.app', ['title' => 'Edit Pengguna: ' . $user->name, 'header' => 'Edit Pengguna'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Daftar Pengguna
            </a>
            <h2 class="text-xl font-bold text-slate-800">Edit Data: {{ $user->name }}</h2>
            <p class="text-xs text-slate-500">Perbarui informasi akun, peran, dan status keaktifan</p>
        </div>

        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-4">
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
                        Nomor Handphone
                    </label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Peran (Role)
                        </label>
                        <select id="role" name="role" required
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            @foreach($roles as $r)
                                <option value="{{ $r->value }}" {{ old('role', $user->role->value) === $r->value ? 'selected' : '' }}>
                                    {{ $r->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="desa_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Penugasan Wilayah Desa
                        </label>
                        <select id="desa_id" name="desa_id"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Tanpa Desa (Admin Pusat) --</option>
                            @foreach($desas as $d)
                                <option value="{{ $d->id }}" {{ old('desa_id', $user->desa_id) == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama }} ({{ $d->kecamatan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="pt-2">
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                    <label for="is_active" class="ml-2 block text-xs font-semibold text-slate-700">
                        Status Akun Aktif (Dapat masuk ke aplikasi)
                    </label>
                </div>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3 mt-4">
                <span class="text-xs font-bold text-slate-700 block">Ubah Kata Sandi (Kosongkan jika tidak diubah)</span>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-[11px] font-semibold text-slate-600">
                            Kata Sandi Baru
                        </label>
                        <input type="password" id="password" name="password"
                               placeholder="Minimal 8 karakter"
                               class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-900 text-xs" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-[11px] font-semibold text-slate-600">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               placeholder="Ulangi kata sandi"
                               class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-900 text-xs" />
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
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

