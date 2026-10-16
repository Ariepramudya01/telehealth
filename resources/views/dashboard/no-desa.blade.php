@extends('layouts.app', ['title' => 'Belum Ditugaskan ke Desa'])

@section('content')
<div class="max-w-md mx-auto my-12 bg-white p-8 rounded-3xl border border-slate-200/80 shadow-xs text-center">
    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    </div>
    <h3 class="text-lg font-bold text-slate-800">Akun Belum Ditugaskan ke Desa</h3>
    <p class="text-xs text-slate-500 mt-2">
        Akun Anda belum dihubungkan dengan wilayah desa binaan tertentu. Silakan hubungi Administrator untuk mengatur penugasan desa Anda.
    </p>
    <div class="mt-6">
        <a href="{{ route('profile.show') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-teal-600 text-white text-xs font-semibold hover:bg-teal-700">
            Periksa Profil Saya
        </a>
    </div>
</div>
@endsection

