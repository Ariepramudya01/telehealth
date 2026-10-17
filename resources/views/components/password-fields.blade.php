@props([
    'required' => true,
    'label' => 'Kata Sandi',
    'confirmLabel' => 'Konfirmasi Kata Sandi',
    'name' => 'password',
    'confirmName' => 'password_confirmation',
    'placeholder' => 'Minimal 8 karakter',
    'confirmPlaceholder' => 'Ulangi kata sandi',
    'helperText' => null,
])

<div x-data="{
        showPassword: false,
        showConfirm: false,
        password: '',
        confirmation: '',
        get hasMinLength() { return this.password.length >= 8; },
        get hasUpperLower() { return /[a-z]/.test(this.password) && /[A-Z]/.test(this.password); },
        get hasNumber() { return /[0-9]/.test(this.password); },
        get hasSymbol() { return /[^A-Za-z0-9]/.test(this.password); },
        get isMatched() { return this.confirmation.length > 0 && this.password === this.confirmation; },
        get score() {
            if (!this.password) return 0;
            let s = 0;
            if (this.hasMinLength) s++;
            if (this.hasUpperLower) s++;
            if (this.hasNumber) s++;
            if (this.hasSymbol) s++;
            return s;
        },
        get strengthLabel() {
            if (!this.password) return '';
            if (this.score <= 1) return 'Sangat Lemah';
            if (this.score === 2) return 'Lemah';
            if (this.score === 3) return 'Sedang';
            return 'Kuat & Aman';
        },
        get strengthColor() {
            if (this.score <= 1) return 'bg-rose-500';
            if (this.score === 2) return 'bg-amber-500';
            if (this.score === 3) return 'bg-sky-500';
            return 'bg-emerald-500';
        },
        get strengthTextClass() {
            if (this.score <= 1) return 'text-rose-600';
            if (this.score === 2) return 'text-amber-600';
            if (this.score === 3) return 'text-sky-600';
            return 'text-emerald-600 font-bold';
        }
    }" class="space-y-3">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Input Password Utama -->
        <div>
            <div class="flex items-center justify-between">
                <label for="{{ $name }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    {{ $label }} @if($required) <span class="text-rose-500">*</span> @endif
                </label>
            </div>
            <div class="mt-1.5 relative rounded-xl shadow-2xs">
                <input :type="showPassword ? 'text' : 'password'"
                       id="{{ $name }}" name="{{ $name }}"
                       @if($required) required @endif
                       x-model="password"
                       placeholder="{{ $placeholder }}"
                       autocomplete="new-password"
                       class="block w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono tracking-wide" />
                <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden transition-colors"
                        title="Tampilkan / sembunyikan kata sandi"
                        tabindex="-1">
                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            @if($helperText)
                <span class="text-[10px] text-slate-400 mt-0.5 block">{{ $helperText }}</span>
            @endif
        </div>

        <!-- Input Konfirmasi Password -->
        <div>
            <label for="{{ $confirmName }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                {{ $confirmLabel }} @if($required) <span class="text-rose-500">*</span> @endif
            </label>
            <div class="mt-1.5 relative rounded-xl shadow-2xs">
                <input :type="showConfirm ? 'text' : 'password'"
                       id="{{ $confirmName }}" name="{{ $confirmName }}"
                       @if($required) required @endif
                       x-model="confirmation"
                       placeholder="{{ $confirmPlaceholder }}"
                       autocomplete="new-password"
                       class="block w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono tracking-wide" />
                <button type="button" @click="showConfirm = !showConfirm"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden transition-colors"
                        title="Tampilkan / sembunyikan konfirmasi kata sandi"
                        tabindex="-1">
                    <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showConfirm" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Password Strength Meter & Live Validation String Indicators -->
    <div x-show="password.length > 0" x-cloak class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-2.5 transition-all">
        <!-- Strength Meter Bar -->
        <div class="flex items-center justify-between text-xs">
            <span class="font-semibold text-slate-700">Kekuatan Kata Sandi:</span>
            <span class="font-bold tracking-wide" :class="strengthTextClass" x-text="strengthLabel"></span>
        </div>
        <div class="grid grid-cols-4 gap-1.5 h-1.5">
            <div class="rounded-full transition-all duration-300" :class="score >= 1 ? strengthColor : 'bg-slate-200'"></div>
            <div class="rounded-full transition-all duration-300" :class="score >= 2 ? strengthColor : 'bg-slate-200'"></div>
            <div class="rounded-full transition-all duration-300" :class="score >= 3 ? strengthColor : 'bg-slate-200'"></div>
            <div class="rounded-full transition-all duration-300" :class="score >= 4 ? strengthColor : 'bg-slate-200'"></div>
        </div>

        <!-- Validation String Criteria Checklist -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pt-1 text-[11px]">
            <div class="flex items-center gap-1.5" :class="hasMinLength ? 'text-emerald-700 font-semibold' : 'text-slate-500'">
                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px]"
                      :class="hasMinLength ? 'bg-emerald-100 text-emerald-800 font-bold' : 'bg-slate-200 text-slate-500'"
                      x-text="hasMinLength ? '✓' : '○'"></span>
                <span>Minimal 8 karakter</span>
            </div>
            <div class="flex items-center gap-1.5" :class="hasUpperLower ? 'text-emerald-700 font-semibold' : 'text-slate-500'">
                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px]"
                      :class="hasUpperLower ? 'bg-emerald-100 text-emerald-800 font-bold' : 'bg-slate-200 text-slate-500'"
                      x-text="hasUpperLower ? '✓' : '○'"></span>
                <span>Huruf besar & huruf kecil (A-Z, a-z)</span>
            </div>
            <div class="flex items-center gap-1.5" :class="hasNumber ? 'text-emerald-700 font-semibold' : 'text-slate-500'">
                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px]"
                      :class="hasNumber ? 'bg-emerald-100 text-emerald-800 font-bold' : 'bg-slate-200 text-slate-500'"
                      x-text="hasNumber ? '✓' : '○'"></span>
                <span>Mengandung angka (0-9)</span>
            </div>
            <div class="flex items-center gap-1.5" :class="hasSymbol ? 'text-emerald-700 font-semibold' : 'text-slate-500'">
                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px]"
                      :class="hasSymbol ? 'bg-emerald-100 text-emerald-800 font-bold' : 'bg-slate-200 text-slate-500'"
                      x-text="hasSymbol ? '✓' : '○'"></span>
                <span>Karakter khusus / simbol (@#$%)</span>
            </div>
            <template x-if="confirmation.length > 0">
                <div class="flex items-center gap-1.5 sm:col-span-2 pt-1 border-t border-slate-200/60"
                     :class="isMatched ? 'text-emerald-700 font-semibold' : 'text-rose-600 font-medium'">
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px]"
                          :class="isMatched ? 'bg-emerald-100 text-emerald-800 font-bold' : 'bg-rose-100 text-rose-700 font-bold'"
                          x-text="isMatched ? '✓' : '✕'"></span>
                    <span x-text="isMatched ? 'Konfirmasi kata sandi cocok' : 'Konfirmasi kata sandi belum sama'"></span>
                </div>
            </template>
        </div>
    </div>
</div>

