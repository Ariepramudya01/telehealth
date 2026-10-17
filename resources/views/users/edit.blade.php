@extends('layouts.app', ['title' => 'Edit Pengguna: ' . $user->name, 'header' => 'Edit Pengguna'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Daftar Pengguna
            </a>
            <h2 class="text-xl font-bold text-slate-800">Edit Data: {{ $user->name }}</h2>
            <p class="text-xs text-slate-500">Perbarui informasi akun, wilayah kerja, instansi, dan status keaktifan</p>
        </div>

        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-4"
              x-data="{ selectedRole: '{{ old('role', $user->role->value) }}' }">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nik" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Nomor Induk Kependudukan (NIK 16 Digit)
                    </label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik', $user->nik) }}" maxlength="16"
                           placeholder="3201xxxxxxxxxxxx"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
                </div>

                <div>
                    <label for="instansi" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Instansi / Unit Kerja / Posyandu
                    </label>
                    <input type="text" id="instansi" name="instansi" list="instansi-suggestions" value="{{ old('instansi', $user->instansi) }}"
                           placeholder="Contoh: UPT Puskesmas Ciomas, Posyandu Melati 1"
                           class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    <datalist id="instansi-suggestions">
                        <option value="UPT Puskesmas">
                        <option value="Dinas Kesehatan">
                        <option value="Pustu (Puskesmas Pembantu)">
                        <option value="Poskesdes (Pos Kesehatan Desa)">
                        <option value="Posyandu">
                    </datalist>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div>
                    <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Peran (Role)
                    </label>
                    <select id="role" name="role" required x-model="selectedRole"
                            class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                        @foreach($roles as $r)
                            <option value="{{ $r->value }}" {{ old('role', $user->role->value) === $r->value ? 'selected' : '' }}>
                                {{ $r->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Wilayah Penugasan Kerja (Cascading: Provinsi -> Kabupaten -> Kecamatan -> Desa) -->
                <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-4"
                     x-data="wilayahPenugasanEdit({
                         isPuskesmasAdmin: {{ !empty($adminPuskesmas) ? 'true' : 'false' }},
                         puskesmasKecamatan: '{{ $adminPuskesmas['kecamatan'] ?? '' }}',
                         puskesmasKabupaten: '{{ $adminPuskesmas['kabupaten'] ?? '' }}',
                         puskesmasProvinsi: '{{ $adminPuskesmas['provinsi'] ?? '' }}',
                         currentDesaId: '{{ old('desa_id', $user->desa_id) }}',
                         currentProvinsi: '{{ old('provinsi', $user->provinsi ?? $user->desa?->provinsi) }}',
                         currentKabupaten: '{{ old('kabupaten', $user->kabupaten ?? $user->desa?->kabupaten) }}',
                         currentKecamatan: '{{ old('kecamatan', $user->kecamatan_kerja) }}',
                         currentDesaNama: '{{ $user->desa?->nama }}'
                     })">
                    
                    <div class="flex items-center justify-between border-b border-slate-200/60 pb-2.5">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Wilayah Penugasan Kerja
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Hierarki administratif: Provinsi &rarr; Kabupaten &rarr; Kecamatan &rarr; Desa / Kelurahan
                            </p>
                        </div>
                    </div>

                    @if(!empty($adminPuskesmas))
                        <!-- Admin Puskesmas: Dibatasi pada kecamatan Puskesmas terdaftar -->
                        <div class="p-3.5 bg-indigo-50/80 border border-indigo-200/80 rounded-xl text-indigo-900 text-xs space-y-1">
                            <div class="flex items-center gap-2 font-bold">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                <span>Wilayah Kerja Puskesmas: {{ $adminPuskesmas['kecamatan'] }}</span>
                            </div>
                            <div class="text-[11px] text-indigo-700">
                                {{ $adminPuskesmas['kabupaten'] }}, {{ $adminPuskesmas['provinsi'] ?? 'Indonesia' }}
                                &bull; <em>Puskesmas hanya menugaskan Bidan dan Kader pada desa di kecamatan ini.</em>
                            </div>
                        </div>

                        <input type="hidden" name="provinsi" value="{{ $adminPuskesmas['provinsi'] }}" />
                        <input type="hidden" name="kabupaten" value="{{ $adminPuskesmas['kabupaten'] }}" />
                        <input type="hidden" name="kecamatan" value="{{ $adminPuskesmas['kecamatan'] }}" />
                        <input type="hidden" name="wilayah_provinsi" value="{{ $adminPuskesmas['provinsi'] }}" />
                        <input type="hidden" name="wilayah_kabupaten" value="{{ $adminPuskesmas['kabupaten'] }}" />
                        <input type="hidden" name="wilayah_kecamatan" value="{{ $adminPuskesmas['kecamatan'] }}" />

                        <div>
                            <label for="desa_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Desa / Kelurahan Penugasan <span class="text-rose-500" x-show="selectedRole !== 'admin'">*</span>
                            </label>
                            <select id="desa_id" name="desa_id"
                                    class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                                <option value="" x-show="selectedRole === 'admin'">-- Lingkup Seluruh Puskesmas (Admin Kecamatan) --</option>
                                <option value="" x-show="selectedRole !== 'admin'">-- Pilih Desa / Kelurahan di {{ $adminPuskesmas['kecamatan'] }} --</option>
                                @foreach($desas as $d)
                                    <option value="{{ $d->id }}" {{ old('desa_id', $user->desa_id) == $d->id ? 'selected' : '' }}>
                                        {{ $d->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <!-- Super Admin: Cascading 4 Tingkat -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Dropdown 1: Provinsi -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-700">
                                    1. Provinsi
                                </label>
                                <select x-model="selectedProvinceId" @change="onProvinceChange()"
                                        class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs">
                                    <option value="">-- Pilih Provinsi --</option>
                                    <template x-for="p in provinces" :key="p.id">
                                        <option :value="p.id" x-text="p.name" :selected="p.id === selectedProvinceId"></option>
                                    </template>
                                </select>
                                <span x-show="loadingProvince" class="text-[10px] text-teal-600 mt-0.5 block">Memuat provinsi...</span>
                            </div>

                            <!-- Dropdown 2: Kabupaten / Kota -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-700">
                                    2. Kabupaten / Kota
                                </label>
                                <select x-model="selectedRegencyId" @change="onRegencyChange()" :disabled="!selectedProvinceId"
                                        class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">-- Pilih Kabupaten / Kota --</option>
                                    <template x-for="r in regencies" :key="r.id">
                                        <option :value="r.id" x-text="r.name" :selected="r.id === selectedRegencyId"></option>
                                    </template>
                                </select>
                                <span x-show="loadingRegency" class="text-[10px] text-teal-600 mt-0.5 block">Memuat kabupaten...</span>
                            </div>

                            <!-- Dropdown 3: Kecamatan -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-700">
                                    3. Kecamatan
                                </label>
                                <select x-model="selectedDistrictId" @change="onDistrictChange()" :disabled="!selectedRegencyId"
                                        class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">-- Pilih Kecamatan --</option>
                                    <template x-for="d in districts" :key="d.id">
                                        <option :value="d.id" x-text="d.name" :selected="d.id === selectedDistrictId"></option>
                                    </template>
                                </select>
                                <span x-show="loadingDistrict" class="text-[10px] text-teal-600 mt-0.5 block">Memuat kecamatan...</span>
                            </div>

                            <!-- Dropdown 4: Desa / Kelurahan -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-700">
                                    4. Desa / Kelurahan
                                </label>
                                <select x-model="selectedVillageId" @change="onVillageChange()" :disabled="!selectedDistrictId"
                                        class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="" x-show="selectedRole === 'admin'">-- Tanpa Desa / Khusus Admin --</option>
                                    <option value="" x-show="selectedRole !== 'admin'">-- Pilih Desa / Kelurahan --</option>
                                    <template x-for="v in villages" :key="v.id">
                                        <option :value="v.id" x-text="v.name" :selected="v.id === selectedVillageId"></option>
                                    </template>
                                </select>
                                <span x-show="loadingVillage" class="text-[10px] text-teal-600 mt-0.5 block">Memuat desa...</span>
                            </div>
                        </div>

                        <!-- Hidden Inputs for Submission -->
                        <input type="hidden" name="desa_id" :value="selectedDesaId" />
                        <input type="hidden" name="provinsi" :value="selectedProvinceName" />
                        <input type="hidden" name="kabupaten" :value="selectedRegencyName" />
                        <input type="hidden" name="kecamatan" :value="selectedDistrictName" />
                        <input type="hidden" name="wilayah_desa" :value="selectedVillageName" />
                        <input type="hidden" name="wilayah_kecamatan" :value="selectedDistrictName" />
                        <input type="hidden" name="wilayah_kabupaten" :value="selectedRegencyName" />
                        <input type="hidden" name="wilayah_provinsi" :value="selectedProvinceName" />
                        <input type="hidden" name="wilayah_kode_desa" :value="selectedVillageId" />

                        <!-- Status Preview Banner -->
                        <div x-show="selectedProvinceName || selectedDistrictName" class="p-2.5 rounded-xl bg-teal-50/80 border border-teal-200/80 text-[11px] text-teal-900 flex items-center justify-between">
                            <div>
                                <span class="font-bold">Wilayah Terpilih:</span>
                                <span x-text="[selectedVillageName || currentDesaNama, selectedDistrictName, selectedRegencyName, selectedProvinceName].filter(Boolean).join(', ')"></span>
                            </div>
                            <span x-show="selectedDesaId" class="px-2 py-0.5 rounded bg-teal-600 text-white font-bold text-[10px]">Tersambung ID: <span x-text="selectedDesaId"></span></span>
                        </div>

                        <!-- Pilihan Cepat dari Desa yang sudah terdaftar -->
                        <div class="pt-2 border-t border-slate-200/50">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-semibold text-slate-600">Atau Tetapkan Langsung dari Desa Terdaftar di Sistem:</span>
                            </div>
                            <select @change="onDirectDesaSelect($event.target.value)"
                                    class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white text-xs">
                                <option value="">-- Pilih dari database lokal --</option>
                                @foreach($desas as $d)
                                    <option value="{{ $d->id }}" {{ old('desa_id', $user->desa_id) == $d->id ? 'selected' : '' }}>
                                        {{ $d->nama }} ({{ $d->kecamatan }} - {{ $d->kabupaten }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Informasi Kepegawaian Admin (Kepala Puskesmas, Ketua TU, dsb) -->
                <div x-show="selectedRole === 'admin'" x-cloak x-transition
                     class="p-4 sm:p-5 bg-gradient-to-br from-indigo-50/70 to-purple-50/50 rounded-2xl border border-indigo-100 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        <h3 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">
                            Informasi Jabatan & Kepegawaian (Khusus Admin / Pejabat Puskesmas)
                        </h3>
                    </div>
                    <p class="text-[11px] text-slate-600 -mt-2">
                        Lengkapi NIP dan Jabatan pimpinan (contoh: Kepala Puskesmas, Ketua Tata Usaha), serta STR bila ada.
                    </p>

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
                            <input type="text" id="jabatan" name="jabatan" list="jabatan-list" value="{{ old('jabatan', $user->jabatan) }}"
                                   placeholder="Pilih atau ketik jabatan..."
                                   class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm" />
                            <datalist id="jabatan-list">
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
                        <span class="text-[10px] text-slate-500 mt-1 block">Opsional: Diisi jika pejabat Puskesmas memiliki latar belakang profesi kesehatan/kebidanan.</span>
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
                
                <x-password-fields :required="false" label="Kata Sandi Baru" confirmLabel="Konfirmasi Kata Sandi Baru" />
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

<script>
function wilayahPenugasanEdit(config = {}) {
    return {
        isPuskesmasAdmin: config.isPuskesmasAdmin || false,
        puskesmasKecamatan: config.puskesmasKecamatan || '',
        puskesmasKabupaten: config.puskesmasKabupaten || '',
        puskesmasProvinsi: config.puskesmasProvinsi || '',

        provinces: [],
        regencies: [],
        districts: [],
        villages: [],

        selectedProvinceId: '',
        selectedProvinceName: config.currentProvinsi || '',
        selectedRegencyId: '',
        selectedRegencyName: config.currentKabupaten || '',
        selectedDistrictId: '',
        selectedDistrictName: config.currentKecamatan || '',
        selectedVillageId: '',
        selectedVillageName: '',
        currentDesaNama: config.currentDesaNama || '',
        selectedDesaId: config.currentDesaId || '',

        loadingProvince: false,
        loadingRegency: false,
        loadingDistrict: false,
        loadingVillage: false,

        async init() {
            if (this.isPuskesmasAdmin) {
                this.selectedProvinceName = this.puskesmasProvinsi;
                this.selectedRegencyName = this.puskesmasKabupaten;
                this.selectedDistrictName = this.puskesmasKecamatan;
                return;
            }

            this.loadingProvince = true;
            try {
                const res = await fetch('/api/wilayah/provinces');
                const json = await res.json();
                if (json.success) {
                    this.provinces = json.data;
                }
            } catch (e) {
                console.error('Gagal memuat provinsi:', e);
            } finally {
                this.loadingProvince = false;
            }

            if (this.selectedProvinceName && this.provinces.length) {
                const match = this.provinces.find(p => p.name.toLowerCase() === this.selectedProvinceName.toLowerCase());
                if (match) {
                    this.selectedProvinceId = match.id;
                    await this.onProvinceChange();
                }
            }
        },

        async onProvinceChange() {
            this.regencies = [];
            this.districts = [];
            this.villages = [];

            const prov = this.provinces.find(p => p.id === this.selectedProvinceId);
            this.selectedProvinceName = prov ? prov.name : '';
            if (!this.selectedProvinceId) return;

            this.loadingRegency = true;
            try {
                const res = await fetch(`/api/wilayah/regencies/${this.selectedProvinceId}`);
                const json = await res.json();
                if (json.success) {
                    this.regencies = json.data;
                    if (this.selectedRegencyName) {
                        const matchReg = this.regencies.find(r => r.name.toLowerCase().includes(this.selectedRegencyName.toLowerCase()) || this.selectedRegencyName.toLowerCase().includes(r.name.toLowerCase()));
                        if (matchReg) {
                            this.selectedRegencyId = matchReg.id;
                            await this.onRegencyChange();
                        }
                    }
                }
            } catch (e) {
                console.error('Gagal memuat kabupaten:', e);
            } finally {
                this.loadingRegency = false;
            }
        },

        async onRegencyChange() {
            this.districts = [];
            this.villages = [];

            const reg = this.regencies.find(r => r.id === this.selectedRegencyId);
            this.selectedRegencyName = reg ? reg.name : '';
            if (!this.selectedRegencyId) return;

            this.loadingDistrict = true;
            try {
                const res = await fetch(`/api/wilayah/districts/${this.selectedRegencyId}`);
                const json = await res.json();
                if (json.success) {
                    this.districts = json.data;
                    if (this.selectedDistrictName) {
                        const cleanCur = this.selectedDistrictName.replace(/^kecamatan\s+/i, '').toLowerCase();
                        const matchDist = this.districts.find(d => d.name.toLowerCase().includes(cleanCur) || cleanCur.includes(d.name.toLowerCase()));
                        if (matchDist) {
                            this.selectedDistrictId = matchDist.id;
                            await this.onDistrictChange();
                        }
                    }
                }
            } catch (e) {
                console.error('Gagal memuat kecamatan:', e);
            } finally {
                this.loadingDistrict = false;
            }
        },

        async onDistrictChange() {
            this.villages = [];

            const dist = this.districts.find(d => d.id === this.selectedDistrictId);
            this.selectedDistrictName = dist ? dist.name : '';
            if (!this.selectedDistrictId) return;

            this.loadingVillage = true;
            try {
                const res = await fetch(`/api/wilayah/villages/${this.selectedDistrictId}`);
                const json = await res.json();
                if (json.success) {
                    this.villages = json.data;
                    if (this.currentDesaNama) {
                        const cleanVil = this.currentDesaNama.replace(/^desa\s+/i, '').toLowerCase();
                        const matchVil = this.villages.find(v => v.name.toLowerCase().includes(cleanVil) || cleanVil.includes(v.name.toLowerCase()));
                        if (matchVil) {
                            this.selectedVillageId = matchVil.id;
                            this.selectedVillageName = matchVil.name;
                        }
                    }
                }
            } catch (e) {
                console.error('Gagal memuat desa:', e);
            } finally {
                this.loadingVillage = false;
            }
        },

        onVillageChange() {
            const vil = this.villages.find(v => v.id === this.selectedVillageId);
            if (vil) {
                this.selectedVillageName = vil.name;
            } else {
                this.selectedVillageName = '';
            }
        },

        onDirectDesaSelect(desaId) {
            this.selectedDesaId = desaId;
            const selectEl = document.getElementById('desa_id');
            if (selectEl) {
                selectEl.value = desaId;
            }
        }
    };
}
</script>
@endsection
