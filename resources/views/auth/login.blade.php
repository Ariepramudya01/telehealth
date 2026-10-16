@extends('layouts.guest')

@section('content')
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Brand Logo & Header -->
        <div class="flex justify-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-500 flex items-center justify-center text-white shadow-lg shadow-teal-500/20">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </div>
        </div>
        <h2 class="mt-4 text-center text-3xl font-extrabold tracking-tight text-slate-900">
            E-Detimil
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
            Sistem Deteksi Dini Risiko Tinggi Ibu Hamil<br>
            <span class="font-medium text-teal-700">Skor Poedji Rochjati (SPR)</span>
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
                               value="{{ old('email', 'admin@edetimil.test') }}"
                               placeholder="nama@contoh.id"
                               class="appearance-none block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition-all" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Kata Sandi
                    </label>
                    <div class="mt-1.5 relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               value="password"
                               placeholder="••••••••"
                               class="appearance-none block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition-all" />
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

            <!-- Quick Demo Login Presets -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider text-center mb-3">
                    Login Cepat (Akun Demo)
                </p>
                <div class="grid grid-cols-3 gap-2" x-data>
                    <button type="button" 
                            @click="document.getElementById('email').value='admin@edetimil.test'; document.getElementById('password').value='password';"
                            class="px-2.5 py-2 rounded-xl border border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold text-center transition-colors">
                        👑 Admin
                    </button>
                    <button type="button" 
                            @click="document.getElementById('email').value='bidan1@edetimil.test'; document.getElementById('password').value='password';"
                            class="px-2.5 py-2 rounded-xl border border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-700 text-xs font-semibold text-center transition-colors">
                        🩺 Bidan
                    </button>
                    <button type="button" 
                            @click="document.getElementById('email').value='kader1@edetimil.test'; document.getElementById('password').value='password';"
                            class="px-2.5 py-2 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold text-center transition-colors">
                        🤝 Kader
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 text-center mt-2.5">
                    Password default semua akun: <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-slate-600">password</code>
                </p>
            </div>

        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} E-Detimil - Program Pengendalian Kematian Ibu & Bayi (AKI/AKB)
        </p>
    </div>
</div>
@endsection

