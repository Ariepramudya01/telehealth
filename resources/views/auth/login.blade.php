@extends('layouts.guest')

@section('content')
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Brand Logo & Header -->
        <div class="flex justify-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo E-Detimil" class="w-16 h-16 rounded-2xl shadow-lg shadow-teal-500/20 object-cover" />
        </div>
        <h2 class="mt-4 text-center text-3xl font-extrabold tracking-tight text-slate-900">
            E-Detimil
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
            Sistem Deteksi Dini Risiko Tinggi Ibu Hamil<br>
            <span class="font-medium text-teal-700">Pemantauan Terpadu Bidan Desa & Kader</span>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-100 sm:px-10">
            
            @if(session('error') || $errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                    {{ session('error') ?? $errors->first() }}
                </div>
            @endif

            <form class="space-y-5" action="{{ route('login') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Alamat Email
                    </label>
                    <div class="mt-1.5 relative">
                        <input id="email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email') }}"
                               placeholder="nama@contoh.id"
                               class="appearance-none block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition-all" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Kata Sandi
                    </label>
                    <div class="mt-1.5 relative" x-data="{ show: false }">
                        <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required
                               value=""
                               placeholder="••••••••"
                               class="appearance-none block w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm font-mono tracking-wide transition-all" />
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden transition-colors"
                                tabindex="-1">
                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                               class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                        <label for="remember" class="ml-2 block text-xs text-slate-600">
                            Ingat saya di perangkat ini
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all">
                        Masuk ke Aplikasi
                    </button>
                </div>
            </form>


            <!-- Informasi Akun Petugas & Registrasi Resmi -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <div class="p-3.5 rounded-2xl bg-teal-50/60 border border-teal-200/80 text-slate-600 text-xs text-left space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-teal-950">
                        <span>📲 Pendaftaran & Aktivasi Akun Petugas</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-slate-600">
                        Akun Bidan dan Kader didaftarkan langsung secara resmi oleh Administrator Puskesmas atau Dinas Kesehatan terkait.
                    </p>
                    <p class="text-[11px] leading-relaxed text-teal-800 font-medium">
                        Tautan verifikasi dan pembuatan kata sandi dikirimkan secara langsung ke nomor WhatsApp petugas yang telah didaftarkan.
                    </p>
                </div>
            </div>

        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} E-Detimil - Program Pengendalian Kematian Ibu & Bayi (AKI/AKB)
        </p>
    </div>
</div>
@endsection

