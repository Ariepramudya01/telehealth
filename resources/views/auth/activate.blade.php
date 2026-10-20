@extends('layouts.guest', ['title' => 'Aktivasi Akun Petugas'])

@section('content')
<div class="min-h-full flex flex-col justify-center py-10 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg text-center">
        <div class="inline-flex items-center justify-center">
            <div class="w-16 h-16 rounded-2xl bg-white p-2 shadow-lg shadow-teal-500/10 border border-slate-200/80 flex items-center justify-center">
                <img src="{{ asset('images/logo.png') }}" alt="E-Detimil Logo" class="w-12 h-12 object-contain" onerror="this.src='{{ asset('favicon.png') }}'" />
            </div>
        </div>
        <h2 class="mt-4 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Aktivasi Akun Petugas
        </h2>
        <p class="mt-2 text-xs sm:text-sm text-slate-600 max-w-sm mx-auto">
            Selamat datang di Sistem E-Detimil. Lengkapi kata sandi baru untuk mengaktifkan akun resmi Anda.
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-lg px-4">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-100 sm:px-10 space-y-6">

            <!-- Data Pendaftaran Petugas -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-teal-50/80 via-emerald-50/50 to-slate-50 border border-teal-100">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                    <h3 class="text-xs font-bold text-teal-950 uppercase tracking-wider">
                        Informasi Akun Terdaftar
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 text-[11px] block">Nama Lengkap</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $user->name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[11px] block">Peran / Jabatan</span>
                        <span class="inline-flex items-center gap-1 font-semibold text-teal-800 bg-teal-100/70 px-2 py-0.5 rounded text-[11px]">
                            {{ $user->hierarchy_title }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[11px] block">Unit Kerja / Wilayah</span>
                        <span class="font-medium text-slate-700">
                            {{ $user->instansi ?: ($user->desa ? $user->desa->nama . ' (' . $user->desa->kecamatan . ')' : 'Puskesmas Terkait') }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[11px] block">No. WhatsApp Terdaftar</span>
                        <span class="font-mono text-slate-700 font-semibold">{{ $user->phone ?? '-' }}</span>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-slate-400 text-[11px] block">Alamat Email</span>
                        <span class="font-mono text-slate-700">{{ $user->email }}</span>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <div class="font-semibold mb-1">Gagal mengaktifkan akun:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('activation.process', ['token' => $token]) }}" method="POST" class="space-y-5">
                @csrf

                <!-- Kata Sandi Baru -->
                <div>
                    <x-password-fields
                        :required="true"
                        label="Buat Kata Sandi Baru"
                        confirmLabel="Ulangi Kata Sandi Baru"
                        helperText="Gunakan minimal 8 karakter dengan kombinasi huruf, angka, dan simbol untuk keamanan maksimal."
                    />
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center items-center gap-2 py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Aktifkan Akun & Masuk</span>
                    </button>
                </div>
            </form>

            <div class="pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                Sudah pernah mengaktifkan akun?
                <a href="{{ route('login') }}" class="font-bold text-teal-700 hover:underline">
                    Masuk ke Sistem &rarr;
                </a>
            </div>

        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} E-Detimil - Program Pengendalian Kematian Ibu & Bayi (AKI/AKB)
        </p>
    </div>
</div>
@endsection

