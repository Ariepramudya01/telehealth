@extends('layouts.guest', ['title' => 'Verifikasi OTP WhatsApp'])

@section('content')
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
        <h2 class="mt-4 text-center text-2xl font-extrabold tracking-tight text-slate-900">
            Verifikasi OTP WhatsApp
        </h2>
        <p class="mt-1.5 text-center text-xs sm:text-sm text-slate-600">
            Masukkan 6 digit kode verifikasi yang telah dikirimkan ke WhatsApp:<br>
            <strong class="font-mono text-slate-800 text-sm">{{ $user->phone ?? '-' }}</strong>
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-100 sm:px-10">

            <!-- Simulated WhatsApp Alert in Dev Mode -->
            @if(session('simulated_otp') || $user->otp_code)
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <span>📲 Simulasi Pesan WhatsApp Masuk:</span>
                    </div>
                    <p class="text-[11px] text-emerald-700 leading-relaxed">
                        Kode OTP Anda: <strong class="text-emerald-950 font-mono text-base px-2 py-0.5 bg-emerald-100 rounded">{{ session('simulated_otp') ?? $user->otp_code }}</strong> (berlaku 10 menit).
                    </p>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    {{ session('error') ?? $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('otp.process') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="otp" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 text-center">
                        Kode Verifikasi (6 Angka)
                    </label>
                    <div class="mt-2">
                        <input id="otp" name="otp" type="text" maxlength="6" required autofocus
                               value="{{ old('otp', session('simulated_otp') ?? $user->otp_code) }}"
                               placeholder="••••••"
                               class="block w-full py-3 text-center tracking-[0.5em] font-mono text-2xl font-extrabold rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-inner" />
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all">
                        Verifikasi Akun Saya
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <form action="{{ route('otp.resend') }}" method="POST">
                    @csrf
                    <button type="submit" class="font-semibold text-teal-700 hover:text-teal-800">
                        🔄 Kirim Ulang OTP
                    </button>
                </form>

                <a href="{{ route('login') }}" class="text-slate-400 hover:text-slate-600">
                    Batal / Masuk
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

