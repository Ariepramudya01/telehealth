<?php

namespace Database\Seeders;

use App\Enums\KategoriRisiko;
use App\Enums\Role;
use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\PertanyaanSpr;
use App\Models\Skrining;
use App\Models\SkriningDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataBidanKaderBumilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Bersihkan Data Dummy Lama (Bumil dummy, Kunjungan dummy, Skrining dummy, User dummy)
        $dummyNiks = [
            '1402015204980001', '1402016508960002', '1402014112930003',
            '1402015502010004', '1402034907990005', '1402036203900006',
            '1402055709950007', '1401046101970008', '1401045211940009',
            '1401086406020010',
        ];

        $oldBumils = IbuHamil::whereIn('nik', $dummyNiks)->get();
        foreach ($oldBumils as $ob) {
            foreach ($ob->skrinings as $s) {
                $s->details()->delete();
                $s->delete();
            }
            $ob->kunjungans()->delete();
            $ob->delete();
        }

        $dummyEmails = [
            'bidan.pantaicermin@detimil.com', 'bidan.gunungsahilan@detimil.com',
            'kader.candirejo1@detimil.com', 'kader.candirejo2@detimil.com',
            'kader.serumpun2@detimil.com', 'kader.sungaiparit1@detimil.com',
            'kader.sungaiparit2@detimil.com', 'kader.pematangreba1@detimil.com',
            'kader.pematangreba2@detimil.com', 'kader.pantaicermin1@detimil.com',
            'kader.pantaicermin2@detimil.com', 'kader.gunungsahilan1@detimil.com',
            'kader.gunungsahilan2@detimil.com',
        ];
        User::whereIn('email', $dummyEmails)->delete();

        // 1. Ambil Desa-desa di Indragiri Hulu dan Kampar
        $desas = Desa::all()->keyBy('id');

        $candirejo = $desas->get(14); // Inhu
        $serumpun = $desas->get(15);  // Inhu
        $sungaiParit = $desas->get(18); // Inhu
        $pematangReba = $desas->get(19); // Inhu
        $pantaiCermin = $desas->get(16); // Kampar
        $gunungSahilan = $desas->get(17); // Kampar

        // 2. Pastikan Bidan Desa di Kabupaten Kampar tersedia
        $bidanPantaiCermin = User::firstOrCreate(
            ['email' => 'bidan.pantaicermin@detimil.com'],
            [
                'name' => 'Bdn. Sri Wahyuni, S.Tr.Keb',
                'password' => Hash::make('password'),
                'role' => Role::Bidan,
                'desa_id' => $pantaiCermin?->id ?? 16,
                'instansi' => 'UPT Puskesmas Pantai Cermin',
                'jabatan' => 'Bidan Desa Pantai Cermin',
                'kecamatan' => 'Kecamatan Tapung',
                'kabupaten' => 'KABUPATEN KAMPAR',
                'provinsi' => 'Riau',
                'phone' => '081275001001',
                'phone_verified_at' => now(),
                'nik' => '1401045501900001',
                'str' => 'STR-BDN-KMP-001',
                'alamat' => 'Desa Pantai Cermin, Kec. Tapung, Kampar',
                'is_active' => true,
            ]
        );

        $bidanGunungSahilan = User::firstOrCreate(
            ['email' => 'bidan.gunungsahilan@detimil.com'],
            [
                'name' => 'Bdn. Nurhasanah, A.Md.Keb',
                'password' => Hash::make('password'),
                'role' => Role::Bidan,
                'desa_id' => $gunungSahilan?->id ?? 17,
                'instansi' => 'UPT Puskesmas Gunung Sahilan',
                'jabatan' => 'Bidan Desa Gunung Sahilan',
                'kecamatan' => 'Kecamatan Gunung Sahilan',
                'kabupaten' => 'KABUPATEN KAMPAR',
                'provinsi' => 'Riau',
                'phone' => '081275001002',
                'phone_verified_at' => now(),
                'nik' => '1401086203920002',
                'str' => 'STR-BDN-KMP-002',
                'alamat' => 'Desa Gunung Sahilan, Kampar',
                'is_active' => true,
            ]
        );

        // Ambil Bidan Desa di Indragiri Hulu yang sudah ada
        $bidanCandirejo = User::where('email', 'mega@gmail.com')->first();
        $bidanSerumpun = User::where('email', 'sufia@gmail.com')->first();
        $bidanSungaiParit = User::where('email', 'yolla@gmail.com')->first();
        $bidanPematangReba = User::where('email', 'lisa@gmail.com')->first();

        // 3. Masukkan Data Dummy 2 Kader per Desa / Kelurahan
        $kaderData = [
            // Candirejo (Inhu)
            [
                'email' => 'kader.candirejo1@detimil.com',
                'name' => 'Siti Aminah',
                'desa_id' => 14,
                'instansi' => 'Posyandu Melati I Candirejo',
                'phone' => '081267890001',
                'nik' => '1402014102850001',
                'alamat' => 'RT 02 RW 01 Desa Candirejo, Pasir Penyu',
                'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            ],
            [
                'email' => 'kader.candirejo2@detimil.com',
                'name' => 'Rina Marlina',
                'desa_id' => 14,
                'instansi' => 'Posyandu Melati II Candirejo',
                'phone' => '081267890002',
                'nik' => '1402015206880002',
                'alamat' => 'RT 04 RW 02 Desa Candirejo, Pasir Penyu',
                'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            ],

            // Serumpun Jaya (Inhu)
            [
                'email' => 'kader.serumpun2@detimil.com',
                'name' => 'Dewi Sartika',
                'desa_id' => 15,
                'instansi' => 'Posyandu Mawar Serumpun Jaya',
                'phone' => '081267890003',
                'nik' => '1402016307890003',
                'alamat' => 'Dusun 1 Desa Serumpun Jaya, Pasir Penyu',
                'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            ],

            // Perkebunan Sungai Parit (Inhu)
            [
                'email' => 'kader.sungaiparit1@detimil.com',
                'name' => 'Nurhayati',
                'desa_id' => 18,
                'instansi' => 'Posyandu Dahlia I Sungai Parit',
                'phone' => '081267890004',
                'nik' => '1402054408870004',
                'alamat' => 'Afdeling 2 Sei Parit, Sungai Lala',
                'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            ],
            [
                'email' => 'kader.sungaiparit2@detimil.com',
                'name' => 'Sri Utami',
                'desa_id' => 18,
                'instansi' => 'Posyandu Dahlia II Sungai Parit',
                'phone' => '081267890005',
                'nik' => '1402055509910005',
                'alamat' => 'Afdeling 4 Sei Parit, Sungai Lala',
                'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            ],

            // Pematang Reba (Inhu)
            [
                'email' => 'kader.pematangreba1@detimil.com',
                'name' => 'Fitri Handayani',
                'desa_id' => 19,
                'instansi' => 'Posyandu Kenanga I Pematang Reba',
                'phone' => '081267890006',
                'nik' => '1402035105930006',
                'alamat' => 'Jl. Seminai Pematang Reba, Rengat Barat',
                'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            ],
            [
                'email' => 'kader.pematangreba2@detimil.com',
                'name' => 'Endang Kusuma',
                'desa_id' => 19,
                'instansi' => 'Posyandu Kenanga II Pematang Reba',
                'phone' => '081267890007',
                'nik' => '1402036208940007',
                'alamat' => 'Jl. Gerbang Sari Pematang Reba, Rengat Barat',
                'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            ],

            // Pantai Cermin (Kampar)
            [
                'email' => 'kader.pantaicermin1@detimil.com',
                'name' => 'Salmah',
                'desa_id' => 16,
                'instansi' => 'Posyandu Anggrek I Pantai Cermin',
                'phone' => '081267890008',
                'nik' => '1401044304890008',
                'alamat' => 'Dusun II Pantai Cermin, Tapung, Kampar',
                'kabupaten' => 'KABUPATEN KAMPAR',
            ],
            [
                'email' => 'kader.pantaicermin2@detimil.com',
                'name' => 'Karmila',
                'desa_id' => 16,
                'instansi' => 'Posyandu Anggrek II Pantai Cermin',
                'phone' => '081267890009',
                'nik' => '1401045405920009',
                'alamat' => 'Dusun IV Pantai Cermin, Tapung, Kampar',
                'kabupaten' => 'KABUPATEN KAMPAR',
            ],

            // Gunung Sahilan (Kampar)
            [
                'email' => 'kader.gunungsahilan1@detimil.com',
                'name' => 'Mardiana',
                'desa_id' => 17,
                'instansi' => 'Posyandu Cempaka I Gunung Sahilan',
                'phone' => '081267890010',
                'nik' => '1401085607900010',
                'alamat' => 'Dusun Sahilan Jaya, Gunung Sahilan, Kampar',
                'kabupaten' => 'KABUPATEN KAMPAR',
            ],
            [
                'email' => 'kader.gunungsahilan2@detimil.com',
                'name' => 'Yuliana',
                'desa_id' => 17,
                'instansi' => 'Posyandu Cempaka II Gunung Sahilan',
                'phone' => '081267890011',
                'nik' => '1401086708950011',
                'alamat' => 'Dusun Makmur, Gunung Sahilan, Kampar',
                'kabupaten' => 'KABUPATEN KAMPAR',
            ],
        ];

        foreach ($kaderData as $k) {
            User::firstOrCreate(
                ['email' => $k['email']],
                [
                    'name' => $k['name'],
                    'password' => Hash::make('password'),
                    'role' => Role::Kader,
                    'desa_id' => $k['desa_id'],
                    'instansi' => $k['instansi'],
                    'jabatan' => 'Kader Posyandu',
                    'kabupaten' => $k['kabupaten'],
                    'provinsi' => 'Riau',
                    'phone' => $k['phone'],
                    'phone_verified_at' => now(),
                    'nik' => $k['nik'],
                    'alamat' => $k['alamat'],
                    'is_active' => true,
                ]
            );
        }

        // 4. Masukkan Data 10 Orang Ibu Hamil (Indragiri Hulu & Kampar)
        $bumilProfiles = [
            // 1. Candirejo (Inhu) - KRR
            [
                'nama' => 'Ny. Anisa Rahmawati',
                'nik' => '1402015204980001',
                'desa_id' => 14,
                'user_id' => $bidanCandirejo?->id ?? 33,
                'nama_suami' => 'Budi Santoso',
                'no_hp' => '081371234001',
                'tanggal_lahir' => '1998-04-12',
                'alamat' => 'Jl. Jenderal Sudirman No. 12, RT 02 RW 01, Candirejo',
                'golongan_darah' => 'O',
                'tinggi_badan' => 158.0,
                'berat_badan' => 54.0,
                'imt' => 21.6,
                'status_gizi' => 'normal',
                'tekanan_darah_sistol' => 110,
                'tekanan_darah_diastol' => 70,
                'lila' => 25.5,
                'tfu' => 20.0,
                'djj' => 140,
                'hemoglobin' => 12.4,
                'hpht' => Carbon::now()->subWeeks(20)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(20)->format('Y-m-d'),
                'gravida' => 1,
                'para' => 0,
                'abortus' => 0,
                'jumlah_anak_hidup' => 0,
                'status_imunisasi_tt' => 'T2',
                'refleks_patela' => 'positif',
                'edema' => 'tidak_ada',
                'kondisi_umum' => 'baik',
                'spr_answers' => [], // Skor dasar 2 (KRR)
                'catatan_kunjungan' => 'Kunjungan rutin trimester 2 di Posyandu Melati. Ibu dan janin sehat.',
            ],

            // 2. Candirejo (Inhu) - KRR
            [
                'nama' => 'Ny. Siti Nurhaliza',
                'nik' => '1402016508960002',
                'desa_id' => 14,
                'user_id' => $bidanCandirejo?->id ?? 33,
                'nama_suami' => 'Agus Setiawan',
                'no_hp' => '081371234002',
                'tanggal_lahir' => '1996-08-25',
                'alamat' => 'Dusun Candirejo Baru, RT 05 RW 02, Candirejo',
                'golongan_darah' => 'A',
                'tinggi_badan' => 155.0,
                'berat_badan' => 58.0,
                'imt' => 24.1,
                'status_gizi' => 'normal',
                'tekanan_darah_sistol' => 115,
                'tekanan_darah_diastol' => 75,
                'lila' => 26.0,
                'tfu' => 26.0,
                'djj' => 144,
                'hemoglobin' => 11.8,
                'hpht' => Carbon::now()->subWeeks(28)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(12)->format('Y-m-d'),
                'gravida' => 2,
                'para' => 1,
                'abortus' => 0,
                'jumlah_anak_hidup' => 1,
                'status_imunisasi_tt' => 'T3',
                'refleks_patela' => 'positif',
                'edema' => 'tidak_ada',
                'kondisi_umum' => 'baik',
                'spr_answers' => [],
                'catatan_kunjungan' => 'Pemeriksaan trimester 3. Posisi kepala sudah di bawah.',
            ],

            // 3. Serumpun Jaya (Inhu) - KRT (Risiko Tinggi - LiLA < 23.5 KEK & Anemia)
            [
                'nama' => 'Ny. Rika Permatasari',
                'nik' => '1402014112930003',
                'desa_id' => 15,
                'user_id' => $bidanSerumpun?->id ?? 34,
                'nama_suami' => 'Hendra Gunawan',
                'no_hp' => '081371234003',
                'tanggal_lahir' => '1993-12-01',
                'alamat' => 'Jl. Lintas Timur, Serumpun Jaya',
                'golongan_darah' => 'B',
                'tinggi_badan' => 149.0,
                'berat_badan' => 43.5,
                'imt' => 19.6,
                'status_gizi' => 'kek',
                'tekanan_darah_sistol' => 100,
                'tekanan_darah_diastol' => 65,
                'lila' => 22.0, // KEK
                'tfu' => 18.0,
                'djj' => 138,
                'hemoglobin' => 9.8, // Anemia
                'hpht' => Carbon::now()->subWeeks(18)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(22)->format('Y-m-d'),
                'gravida' => 3,
                'para' => 2,
                'abortus' => 0,
                'jumlah_anak_hidup' => 2,
                'status_imunisasi_tt' => 'T2',
                'refleks_patela' => 'positif',
                'edema' => 'tidak_ada',
                'kondisi_umum' => 'kurang_baik',
                'spr_answers' => [18], // Kurang darah/anemia
                'catatan_kunjungan' => 'Jemput bola oleh bidan dan kader Wahyuni. Diberikan PMT pemulihan dan tablet Fe.',
            ],

            // 4. Serumpun Jaya (Inhu) - KRR
            [
                'nama' => 'Ny. Desi Ratnasari',
                'nik' => '1402015502010004',
                'desa_id' => 15,
                'user_id' => $bidanSerumpun?->id ?? 34,
                'nama_suami' => 'Muhammad Ilham',
                'no_hp' => '081371234004',
                'tanggal_lahir' => '2001-02-15',
                'alamat' => 'RT 03 Dusun Harapan, Serumpun Jaya',
                'golongan_darah' => 'AB',
                'tinggi_badan' => 160.0,
                'berat_badan' => 52.0,
                'imt' => 20.3,
                'status_gizi' => 'normal',
                'tekanan_darah_sistol' => 110,
                'tekanan_darah_diastol' => 70,
                'lila' => 24.5,
                'tfu' => 14.0,
                'djj' => 146,
                'hemoglobin' => 12.0,
                'hpht' => Carbon::now()->subWeeks(14)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(26)->format('Y-m-d'),
                'gravida' => 1,
                'para' => 0,
                'abortus' => 0,
                'jumlah_anak_hidup' => 0,
                'status_imunisasi_tt' => 'T1',
                'refleks_patela' => 'positif',
                'edema' => 'tidak_ada',
                'kondisi_umum' => 'baik',
                'spr_answers' => [],
                'catatan_kunjungan' => 'Kunjungan K2. Edukasi gizi seimbang dan istirahat cukup.',
            ],

            // 5. Pematang Reba (Inhu) - KRT (Riwayat Keguguran/Abortus)
            [
                'nama' => 'Ny. Maya Anggraini',
                'nik' => '1402034907990005',
                'desa_id' => 19,
                'user_id' => $bidanPematangReba?->id ?? 41,
                'nama_suami' => 'Dedi Kurniawan',
                'no_hp' => '081371234005',
                'tanggal_lahir' => '1999-07-09',
                'alamat' => 'Jl. Seminai RT 01 RW 03 Pematang Reba, Rengat Barat',
                'golongan_darah' => 'O',
                'tinggi_badan' => 156.0,
                'berat_badan' => 56.0,
                'imt' => 23.0,
                'status_gizi' => 'normal',
                'tekanan_darah_sistol' => 120,
                'tekanan_darah_diastol' => 80,
                'lila' => 25.0,
                'tfu' => 22.0,
                'djj' => 142,
                'hemoglobin' => 11.5,
                'hpht' => Carbon::now()->subWeeks(22)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(18)->format('Y-m-d'),
                'gravida' => 2,
                'para' => 0,
                'abortus' => 1,
                'jumlah_anak_hidup' => 0,
                'status_imunisasi_tt' => 'T2',
                'refleks_patela' => 'positif',
                'edema' => 'tidak_ada',
                'kondisi_umum' => 'baik',
                'spr_answers' => [9], // Pernah gagal kehamilan (keguguran)
                'catatan_kunjungan' => 'Pendampingan khusus riwayat abortus sebelumnya. Dianjurkan tidak mengangkat beban berat.',
            ],

            // 6. Pematang Reba (Inhu) - KRT (Grande Multipara - 4 anak)
            [
                'nama' => 'Ny. Wulandari',
                'nik' => '1402036203900006',
                'desa_id' => 19,
                'user_id' => $bidanPematangReba?->id ?? 41,
                'nama_suami' => 'Rahmat Hidayat',
                'no_hp' => '081371234006',
                'tanggal_lahir' => '1990-03-22',
                'alamat' => 'Komplek Griya Indah, Pematang Reba',
                'golongan_darah' => 'B',
                'tinggi_badan' => 152.0,
                'berat_badan' => 64.0,
                'imt' => 27.7,
                'status_gizi' => 'obesitas',
                'tekanan_darah_sistol' => 125,
                'tekanan_darah_diastol' => 82,
                'lila' => 28.0,
                'tfu' => 30.0,
                'djj' => 145,
                'hemoglobin' => 11.2,
                'hpht' => Carbon::now()->subWeeks(32)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(8)->format('Y-m-d'),
                'gravida' => 5,
                'para' => 4,
                'abortus' => 0,
                'jumlah_anak_hidup' => 4,
                'status_imunisasi_tt' => 'T4',
                'refleks_patela' => 'positif',
                'edema' => 'tungkai',
                'kondisi_umum' => 'baik',
                'spr_answers' => [6], // Pernah melahirkan 4 kali atau lebih
                'catatan_kunjungan' => 'Trimester 3. Persiapan rujukan bersalin di Rumah Sakit/Faskes memadai.',
            ],

            // 7. Perkebunan Sungai Parit (Inhu) - Dinamis: K1 (KRT - Riwayat SC) -> K2 (KRST - Muncul Hipertensi)
            [
                'nama' => 'Ny. Tri Wahyuni',
                'nik' => '1402055709950007',
                'desa_id' => 18,
                'user_id' => $bidanSungaiParit?->id ?? 40,
                'nama_suami' => 'Joko Prasetyo',
                'no_hp' => '081371234007',
                'tanggal_lahir' => '1995-09-17',
                'alamat' => 'Perumahan Staf Afdeling 2 Sungai Parit',
                'golongan_darah' => 'A',
                'tinggi_badan' => 153.0,
                'berat_badan' => 64.0,
                'imt' => 27.3,
                'status_gizi' => 'overweight',
                'tekanan_darah_sistol' => 120,
                'tekanan_darah_diastol' => 80,
                'lila' => 28.0,
                'tfu' => 24.0,
                'djj' => 144,
                'hemoglobin' => 12.1,
                'hpht' => Carbon::now()->subWeeks(30)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(10)->format('Y-m-d'),
                'gravida' => 2,
                'para' => 1,
                'abortus' => 0,
                'jumlah_anak_hidup' => 1,
                'status_imunisasi_tt' => 'T3',
                'refleks_patela' => 'positif',
                'edema' => 'tidak_ada',
                'kondisi_umum' => 'baik',
                'spr_answers' => [13], // K1: Riwayat Bedah Sesar (skor 8) -> Total 10 (KRT)
                'catatan_kunjungan' => 'Kunjungan rutin K1. Riwayat SC persalinan lalu. Kondisi tensi dan fisik terpantau stabil.',
            ],

            // 8. Pantai Cermin (Kampar) - KRR
            [
                'nama' => 'Ny. Fatimah Az-Zahra',
                'nik' => '1401046101970008',
                'desa_id' => 16,
                'user_id' => $bidanPantaiCermin->id,
                'nama_suami' => 'Ahmad Fauzi',
                'no_hp' => '081275678008',
                'tanggal_lahir' => '1997-01-21',
                'alamat' => 'Dusun II RT 03 RW 01 Pantai Cermin, Tapung',
                'golongan_darah' => 'O',
                'tinggi_badan' => 157.0,
                'berat_badan' => 55.0,
                'imt' => 22.3,
                'status_gizi' => 'normal',
                'tekanan_darah_sistol' => 110,
                'tekanan_darah_diastol' => 70,
                'lila' => 25.0,
                'tfu' => 19.0,
                'djj' => 142,
                'hemoglobin' => 12.6,
                'hpht' => Carbon::now()->subWeeks(19)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(21)->format('Y-m-d'),
                'gravida' => 1,
                'para' => 0,
                'abortus' => 0,
                'jumlah_anak_hidup' => 0,
                'status_imunisasi_tt' => 'T2',
                'refleks_patela' => 'positif',
                'edema' => 'tidak_ada',
                'kondisi_umum' => 'baik',
                'spr_answers' => [],
                'catatan_kunjungan' => 'Pemeriksaan di Puskesmas Pantai Cermin. Kehamilan pertama kondisi prima.',
            ],

            // 9. Pantai Cermin (Kampar) - KRT (Letak Sungsang / Jarak Dekat)
            [
                'nama' => 'Ny. Nurul Hidayati',
                'nik' => '1401045211940009',
                'desa_id' => 16,
                'user_id' => $bidanPantaiCermin->id,
                'nama_suami' => 'Zulkifli',
                'no_hp' => '081275678009',
                'tanggal_lahir' => '1994-11-12',
                'alamat' => 'Dusun IV RT 08 RW 02 Pantai Cermin, Tapung',
                'golongan_darah' => 'A',
                'tinggi_badan' => 154.0,
                'berat_badan' => 61.0,
                'imt' => 25.7,
                'status_gizi' => 'overweight',
                'tekanan_darah_sistol' => 120,
                'tekanan_darah_diastol' => 80,
                'lila' => 27.0,
                'tfu' => 31.0,
                'djj' => 144,
                'hemoglobin' => 11.6,
                'hpht' => Carbon::now()->subWeeks(34)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(6)->format('Y-m-d'),
                'gravida' => 3,
                'para' => 1,
                'abortus' => 1,
                'jumlah_anak_hidup' => 1,
                'status_imunisasi_tt' => 'T3',
                'refleks_patela' => 'positif',
                'edema' => 'tidak_ada',
                'kondisi_umum' => 'baik',
                'spr_answers' => [15], // Hamil kembar / letak sungsang
                'catatan_kunjungan' => 'Kunjungan rumah jemput bola bersama kader Salmah. Posisi bokong di bawah.',
            ],

            // 10. Gunung Sahilan (Kampar) - KRR
            [
                'nama' => 'Ny. Melati Indah',
                'nik' => '1401086406020010',
                'desa_id' => 17,
                'user_id' => $bidanGunungSahilan->id,
                'nama_suami' => 'Rizki Pratama',
                'no_hp' => '081275678010',
                'tanggal_lahir' => '2002-06-24',
                'alamat' => 'Dusun Sahilan Jaya, Gunung Sahilan, Kampar',
                'golongan_darah' => 'B',
                'tinggi_badan' => 159.0,
                'berat_badan' => 51.0,
                'imt' => 20.2,
                'status_gizi' => 'normal',
                'tekanan_darah_sistol' => 110,
                'tekanan_darah_diastol' => 70,
                'lila' => 24.0,
                'tfu' => 15.0,
                'djj' => 140,
                'hemoglobin' => 12.3,
                'hpht' => Carbon::now()->subWeeks(15)->format('Y-m-d'),
                'taksiran_persalinan' => Carbon::now()->addWeeks(25)->format('Y-m-d'),
                'gravida' => 1,
                'para' => 0,
                'abortus' => 0,
                'jumlah_anak_hidup' => 0,
                'status_imunisasi_tt' => 'T1',
                'refleks_patela' => 'positif',
                'edema' => 'tidak_ada',
                'kondisi_umum' => 'baik',
                'spr_answers' => [],
                'catatan_kunjungan' => 'Pendampingan Posyandu Cempaka oleh Kader Mardiana. Kepatuhan Fe sangat baik.',
            ],
        ];

        $pertanyaans = PertanyaanSpr::all()->keyBy('nomor');

        $createSkrining = function (IbuHamil $ibuHamil, Kunjungan $kunjungan, array $sprAnswers, string $catatanTambahan = '') use ($pertanyaans): Skrining {
            $totalSkor = 2; // Base score
            $details = [];

            foreach ($pertanyaans as $nomor => $pertanyaan) {
                $isYes = in_array($nomor, $sprAnswers, true);
                $skor = $isYes ? $pertanyaan->skor : 0;
                $totalSkor += $skor;

                $details[] = [
                    'pertanyaan_spr_id' => $pertanyaan->id,
                    'jawaban' => $isYes,
                    'skor' => $skor,
                ];
            }

            $kategoriRisiko = KategoriRisiko::fromScore($totalSkor);

            $skrining = Skrining::create([
                'ibu_hamil_id' => $ibuHamil->id,
                'user_id' => $ibuHamil->user_id,
                'kunjungan_id' => $kunjungan->id,
                'tanggal_skrining' => $kunjungan->tanggal_kunjungan ?? Carbon::now(),
                'usia_kehamilan_minggu' => $kunjungan->usia_kehamilan ?? ($ibuHamil->usia_kehamilan_minggu ?? 20),
                'trimester' => ($kunjungan->usia_kehamilan ?? 20) <= 12 ? 1 : (($kunjungan->usia_kehamilan ?? 20) <= 27 ? 2 : 3),
                'total_skor' => $totalSkor,
                'kategori_risiko' => $kategoriRisiko,
                'catatan' => 'Skrining Kunjungan '.$kunjungan->jenis_kunjungan.': Skor '.$totalSkor.' ('.$kategoriRisiko->label().')'.$catatanTambahan,
            ]);

            foreach ($details as $d) {
                SkriningDetail::create(array_merge($d, ['skrining_id' => $skrining->id]));
            }

            // Tautkan kunjungan ke skrining
            $kunjungan->update([
                'skrining_id' => $skrining->id,
            ]);

            return $skrining;
        };

        foreach ($bumilProfiles as $profile) {
            $sprAnswers = $profile['spr_answers'];
            $catatanKunjungan = $profile['catatan_kunjungan'];
            unset($profile['spr_answers'], $profile['catatan_kunjungan']);

            $ibuHamil = IbuHamil::create($profile);

            // Generate Jadwal K1-K6
            $ibuHamil->generateStandardKunjungans($ibuHamil->user_id);
            $kunjungans = $ibuHamil->kunjungans()->orderBy('id')->get();
            $k1 = $kunjungans->get(0);
            $k2 = $kunjungans->get(1);

            // K1 selalu dihadiri dan dilayani lengkap dengan skrining masing-masing
            if ($k1) {
                $k1->update([
                    'status_hadir' => true,
                    'status_dilayani' => true,
                    'metode_kunjungan' => ! empty($sprAnswers) ? 'jemput_bola' : 'posyandu',
                    'berat_badan' => $ibuHamil->berat_badan,
                    'tinggi_badan' => $ibuHamil->tinggi_badan,
                    'tekanan_darah_sistol' => $ibuHamil->tekanan_darah_sistol,
                    'tekanan_darah_diastol' => $ibuHamil->tekanan_darah_diastol,
                    'lila' => $ibuHamil->lila,
                    'tfu' => $ibuHamil->tfu,
                    'djj' => $ibuHamil->djj,
                    'hb' => $ibuHamil->hemoglobin,
                    'kepatuhan_fe' => true,
                    'bentuk_pendampingan' => ! empty($sprAnswers) ? 'Kunjungan rumah jemput bola & pemantauan intensif' : 'Pendampingan posyandu & edukasi nutrisi',
                    'catatan' => $catatanKunjungan,
                    'jadwal_kunjungan_berikutnya' => Carbon::now()->addWeeks(4),
                ]);

                $createSkrining($ibuHamil, $k1, $sprAnswers);
            }

            // Demonstrasi Variasi Kasus Riil pada K2:
            // Kasus 1: Ny. Siti Nurhaliza (Candirejo) -> Hadir & Diperiksa fisik, tetapi BELUM skrining SPR
            if ($ibuHamil->nik === '1402016508960002' && $k2) {
                $k2->update([
                    'status_hadir' => true,
                    'status_dilayani' => false,
                    'metode_kunjungan' => 'posyandu',
                    'berat_badan' => 59.5,
                    'tinggi_badan' => $ibuHamil->tinggi_badan,
                    'tekanan_darah_sistol' => 118,
                    'tekanan_darah_diastol' => 78,
                    'lila' => 26.0,
                    'tfu' => 24.0,
                    'djj' => 142,
                    'hb' => 11.8,
                    'kepatuhan_fe' => true,
                    'skrining_id' => null, // Belum skrining SPR
                    'bentuk_pendampingan' => 'Pemeriksaan rutin posyandu',
                    'catatan' => 'Pemeriksaan fisik K2 selesai di posyandu. Menunggu bides/kader mengisi skrining SPR kunjungan ini.',
                    'jadwal_kunjungan_berikutnya' => Carbon::now()->addWeeks(4),
                ]);
            }

            // Kasus 2: Ny. Rika Permatasari (Serumpun Jaya) -> Dihadirkah di meja absensi kader, tetapi DATA FISIK BELUM DIISI
            if ($ibuHamil->nik === '1402014112930003' && $k2) {
                $k2->update([
                    'status_hadir' => true,
                    'status_dilayani' => false,
                    'metode_kunjungan' => 'posyandu',
                    'berat_badan' => null,
                    'tinggi_badan' => null,
                    'tekanan_darah_sistol' => null,
                    'tekanan_darah_diastol' => null,
                    'lila' => null,
                    'tfu' => null,
                    'djj' => null,
                    'hb' => null,
                    'kepatuhan_fe' => false,
                    'skrining_id' => null,
                    'bentuk_pendampingan' => null,
                    'catatan' => 'Ibu sudah hadir di posyandu (dicatat kader), sedang antre untuk pengukuran fisik dan tensi.',
                ]);
            }

            // Kasus 3: Ny. Tri Wahyuni (Sungai Parit) -> Skrining SPR Dinamis per Kunjungan
            // K1 hanya memiliki Riwayat SC [13] (skor 6 - KRT). Pada K2 tensi melonjak & preeklampsia berat [13, 23] -> Skrining K2 (skor 14 - KRST)!
            if ($ibuHamil->nik === '1402055709950007' && $k2) {
                $k2->update([
                    'status_hadir' => true,
                    'metode_kunjungan' => 'jemput_bola',
                    'berat_badan' => 66.5,
                    'tinggi_badan' => $ibuHamil->tinggi_badan,
                    'tekanan_darah_sistol' => 150,
                    'tekanan_darah_diastol' => 100,
                    'lila' => 28.5,
                    'tfu' => 28.0,
                    'djj' => 150,
                    'hb' => 12.1,
                    'kepatuhan_fe' => true,
                    'bentuk_pendampingan' => 'Jemput bola terpadu & rujukan segera ke RSUD Indragiri',
                    'catatan' => 'Pemeriksaan K2 menemukan preeklampsia berat (tensi 150/100, edema tungkai). Skrining SPR K2 mencatat kenaikan risiko menjadi KRST (Skor 14). Segera rujuk SpOG.',
                    'jadwal_kunjungan_berikutnya' => Carbon::now()->addWeeks(1),
                ]);

                // Skrining khusus K2 dengan komplikasi baru: Bedah Sesar (13) + Preeklampsia Berat (23) -> Skor 2 + 4 + 8 = 14 (KRST)
                $skriningK2 = $createSkrining($ibuHamil, $k2, [13, 23], ' (Ditemukan Komplikasi Baru: Preeklampsia Berat)');
                $k2->update([
                    'status_dilayani' => true,
                    'skrining_id' => $skriningK2->id,
                ]);
            }

            // Kasus 4: Ny. Maya Anggraini (Pematang Reba) -> K1 & K2 Dilayani Lengkap
            if ($ibuHamil->nik === '1402034907990005' && $k2) {
                $k2->update([
                    'status_hadir' => true,
                    'metode_kunjungan' => 'posyandu',
                    'berat_badan' => 57.0,
                    'tinggi_badan' => $ibuHamil->tinggi_badan,
                    'tekanan_darah_sistol' => 118,
                    'tekanan_darah_diastol' => 78,
                    'lila' => 25.2,
                    'tfu' => 24.0,
                    'djj' => 140,
                    'hb' => 11.7,
                    'kepatuhan_fe' => true,
                    'bentuk_pendampingan' => 'Pendampingan posyandu & pemantauan riwayat abortus',
                    'catatan' => 'Kunjungan K2 trimester 2 berjalan baik. Kondisi kehamilan sehat.',
                    'jadwal_kunjungan_berikutnya' => Carbon::now()->addWeeks(4),
                ]);

                $skriningK2Maya = $createSkrining($ibuHamil, $k2, [9]); // Riwayat abortus tetap ada
                $k2->update([
                    'status_dilayani' => true,
                    'skrining_id' => $skriningK2Maya->id,
                ]);
            }
        }
    }
}
