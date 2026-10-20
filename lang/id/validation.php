<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi (Bahasa Indonesia)
    |--------------------------------------------------------------------------
    */

    'accepted' => 'Isian :attribute harus diterima.',
    'accepted_if' => 'Isian :attribute harus diterima ketika :other bernilai :value.',
    'active_url' => 'Isian :attribute bukan URL yang valid.',
    'after' => 'Isian :attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => 'Isian :attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => 'Isian :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Isian :attribute hanya boleh berisi huruf, angka, setrip, dan garis bawah.',
    'alpha_num' => 'Isian :attribute hanya boleh berisi huruf dan angka.',
    'array' => 'Isian :attribute harus berupa sebuah array.',
    'ascii' => 'Isian :attribute hanya boleh berisi karakter alfanumerik dan simbol single-byte.',
    'before' => 'Isian :attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => 'Isian :attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => 'Isian :attribute harus memiliki antara :min dan :max anggota.',
        'file' => 'Isian :attribute harus berukuran antara :min dan :max kilobita.',
        'numeric' => 'Isian :attribute harus bernilai antara :min dan :max.',
        'string' => 'Isian :attribute harus berisi antara :min dan :max karakter.',
    ],
    'boolean' => 'Isian :attribute harus bernilai true atau false.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi saat ini salah.',
    'date' => 'Isian :attribute bukan tanggal yang valid.',
    'date_equals' => 'Isian :attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => 'Isian :attribute tidak cocok dengan format :format.',
    'decimal' => 'Isian :attribute harus memiliki :decimal angka desimal.',
    'different' => 'Isian :attribute dan :other harus berbeda.',
    'digits' => 'Isian :attribute harus terdiri dari :digits angka.',
    'digits_between' => 'Isian :attribute harus terdiri dari :min sampai :max angka.',
    'email' => 'Isian :attribute harus berupa alamat email yang valid.',
    'ends_with' => 'Isian :attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => 'Isian :attribute yang dipilih tidak valid.',
    'exists' => 'Isian :attribute yang dipilih tidak valid.',
    'file' => 'Isian :attribute harus berupa sebuah berkas.',
    'filled' => 'Isian :attribute harus memiliki nilai.',
    'gt' => [
        'numeric' => 'Isian :attribute harus lebih besar dari :value.',
        'string' => 'Isian :attribute harus memiliki lebih dari :value karakter.',
    ],
    'gte' => [
        'numeric' => 'Isian :attribute harus lebih besar dari atau sama dengan :value.',
        'string' => 'Isian :attribute harus memiliki minimal :value karakter.',
    ],
    'image' => 'Isian :attribute harus berupa gambar.',
    'in' => 'Isian :attribute yang dipilih tidak valid.',
    'in_array' => 'Isian :attribute tidak ada di dalam :other.',
    'integer' => 'Isian :attribute harus berupa bilangan bulat.',
    'lt' => [
        'numeric' => 'Isian :attribute harus kurang dari :value.',
        'string' => 'Isian :attribute harus memiliki kurang dari :value karakter.',
    ],
    'lte' => [
        'numeric' => 'Isian :attribute harus kurang dari atau sama dengan :value.',
        'string' => 'Isian :attribute tidak boleh lebih dari :value karakter.',
    ],
    'max' => [
        'array' => 'Isian :attribute tidak boleh memiliki lebih dari :max anggota.',
        'file' => 'Isian :attribute tidak boleh lebih dari :max kilobita.',
        'numeric' => 'Isian :attribute tidak boleh lebih dari :max.',
        'string' => 'Isian :attribute tidak boleh lebih dari :max karakter.',
    ],
    'mimes' => 'Isian :attribute harus berupa berkas bertipe: :values.',
    'min' => [
        'array' => 'Isian :attribute harus memiliki minimal :min anggota.',
        'file' => 'Isian :attribute minimal harus berukuran :min kilobita.',
        'numeric' => 'Isian :attribute minimal harus bernilai :min.',
        'string' => 'Isian :attribute minimal harus berisi :min karakter.',
    ],
    'not_in' => 'Isian :attribute yang dipilih tidak valid.',
    'numeric' => 'Isian :attribute harus berupa angka.',
    'password' => [
        'letters' => 'Isian :attribute harus mengandung setidaknya satu huruf.',
        'mixed' => 'Isian :attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => 'Isian :attribute harus mengandung setidaknya satu angka.',
        'symbols' => 'Isian :attribute harus mengandung setidaknya satu simbol.',
    ],
    'regex' => 'Format isian :attribute tidak valid.',
    'required' => 'Isian :attribute wajib diisi.',
    'required_if' => 'Isian :attribute wajib diisi jika :other adalah :value.',
    'same' => 'Isian :attribute dan :other harus sama.',
    'size' => [
        'numeric' => 'Isian :attribute harus berukuran :size.',
        'string' => 'Isian :attribute harus berisi :size karakter.',
    ],
    'string' => 'Isian :attribute harus berupa teks.',
    'unique' => ':Attribute sudah terdaftar di sistem dan tidak boleh sama.',
    'url' => 'Format isian :attribute tidak valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'nik' => [
            'unique' => 'Nomor Induk Kependudukan (NIK) tersebut sudah terdaftar di sistem.',
        ],
        'email' => [
            'unique' => 'Alamat email tersebut sudah digunakan oleh akun lain.',
        ],
        'phone' => [
            'unique' => 'Nomor telepon/WhatsApp tersebut sudah terdaftar.',
        ],
        'kode_desa' => [
            'unique' => 'Kode desa tersebut sudah terdaftar untuk desa lain.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'Nama Lengkap',
        'email' => 'Alamat Email',
        'password' => 'Kata Sandi',
        'password_confirmation' => 'Konfirmasi Kata Sandi',
        'phone' => 'Nomor WhatsApp/Telepon',
        'nik' => 'Nomor Induk Kependudukan (NIK)',
        'nama' => 'Nama',
        'nama_suami' => 'Nama Suami',
        'no_hp' => 'Nomor Handphone',
        'alamat' => 'Alamat',
        'desa_id' => 'Desa',
        'kode_desa' => 'Kode Desa',
        'kecamatan' => 'Kecamatan',
        'kabupaten' => 'Kabupaten',
        'provinsi' => 'Provinsi',
        'instansi' => 'Instansi / UPT Puskesmas',
        'role' => 'Peran Akun',
        'jabatan' => 'Jabatan',
        'tanggal_lahir' => 'Tanggal Lahir',
        'hpht' => 'Hari Pertama Haid Terakhir (HPHT)',
        'taksiran_persalinan' => 'Taksiran Persalinan',
        'tinggi_badan' => 'Tinggi Badan',
        'berat_badan' => 'Berat Badan',
        'tekanan_darah_sistol' => 'Tekanan Darah Sistol',
        'tekanan_darah_diastol' => 'Tekanan Darah Diastol',
        'lila' => 'Lingkar Lengan Atas (LiLA)',
        'hemoglobin' => 'Kadar Hemoglobin (Hb)',
        'gravida' => 'Jumlah Kehamilan (Gravida)',
        'para' => 'Jumlah Persalinan (Para)',
        'abortus' => 'Jumlah Keguguran (Abortus)',
        'jumlah_anak_hidup' => 'Jumlah Anak Hidup',
        'tanggal_kunjungan' => 'Tanggal Kunjungan',
        'jadwal_kunjungan_berikutnya' => 'Jadwal Kunjungan Berikutnya',
        'tahap_anc' => 'Tahap ANC',
        'metode_kunjungan' => 'Metode Kunjungan',
        'keluhan' => 'Keluhan',
        'tindakan' => 'Tindakan / Pelayanan Medis',
    ],

];
