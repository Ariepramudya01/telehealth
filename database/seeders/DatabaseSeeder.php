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
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedPertanyaanSpr();
        $desas = $this->seedDesas();
        $this->seedUsers($desas);
        $this->seedIbuHamilAndSkrining($desas);
    }

    /**
     * Seed the Skor Poedji Rochjati questions.
     */
    private function seedPertanyaanSpr(): void
    {
        $pertanyaan = [
            // Kelompok I - APGO
            ['kelompok' => 'I', 'nomor' => 1, 'pertanyaan' => 'Terlalu muda hamil (≤ 16 tahun)', 'skor' => 4, 'keterangan' => 'Usia ibu saat hamil ≤ 16 tahun'],
            ['kelompok' => 'I', 'nomor' => 2, 'pertanyaan' => 'Terlalu tua hamil pertama kali (≥ 35 tahun)', 'skor' => 4, 'keterangan' => 'Hamil pertama kali pada usia ≥ 35 tahun'],
            ['kelompok' => 'I', 'nomor' => 3, 'pertanyaan' => 'Terlalu lambat hamil I, kawin ≥ 4 tahun', 'skor' => 4, 'keterangan' => 'Pernikahan sudah ≥ 4 tahun baru hamil pertama'],
            ['kelompok' => 'I', 'nomor' => 4, 'pertanyaan' => 'Terlalu lama hamil lagi (≥ 10 tahun)', 'skor' => 4, 'keterangan' => 'Jarak kehamilan sebelumnya ≥ 10 tahun'],
            ['kelompok' => 'I', 'nomor' => 5, 'pertanyaan' => 'Terlalu cepat hamil lagi (< 2 tahun)', 'skor' => 4, 'keterangan' => 'Jarak kehamilan sebelumnya < 2 tahun'],
            ['kelompok' => 'I', 'nomor' => 6, 'pertanyaan' => 'Terlalu banyak anak (4 anak atau lebih)', 'skor' => 4, 'keterangan' => 'Jumlah anak yang dilahirkan ≥ 4'],
            ['kelompok' => 'I', 'nomor' => 7, 'pertanyaan' => 'Terlalu tua umur (≥ 35 tahun)', 'skor' => 4, 'keterangan' => 'Usia ibu saat ini ≥ 35 tahun'],
            ['kelompok' => 'I', 'nomor' => 8, 'pertanyaan' => 'Terlalu pendek (≤ 145 cm)', 'skor' => 4, 'keterangan' => 'Tinggi badan ibu ≤ 145 cm'],
            ['kelompok' => 'I', 'nomor' => 9, 'pertanyaan' => 'Pernah gagal kehamilan (keguguran)', 'skor' => 4, 'keterangan' => 'Riwayat keguguran pada kehamilan sebelumnya'],
            ['kelompok' => 'I', 'nomor' => 10, 'pertanyaan' => 'Pernah melahirkan dengan tarikan tang/vakum', 'skor' => 4, 'keterangan' => 'Riwayat persalinan dengan alat bantu'],
            ['kelompok' => 'I', 'nomor' => 11, 'pertanyaan' => 'Pernah melahirkan dengan uri dirogoh', 'skor' => 4, 'keterangan' => 'Riwayat manual plasenta'],
            ['kelompok' => 'I', 'nomor' => 12, 'pertanyaan' => 'Pernah melahirkan dengan diberi infus/transfusi', 'skor' => 4, 'keterangan' => 'Riwayat perdarahan yang memerlukan infus/transfusi'],
            ['kelompok' => 'I', 'nomor' => 13, 'pertanyaan' => 'Pernah melahirkan dengan operasi sesar', 'skor' => 4, 'keterangan' => 'Riwayat persalinan dengan operasi sesar'],

            // Kelompok II - AGO
            ['kelompok' => 'II', 'nomor' => 14, 'pertanyaan' => 'Penyakit pada ibu hamil (anemia, malaria, TBC, payah jantung, diabetes, PMS, dll)', 'skor' => 4, 'keterangan' => 'Menderita penyakit kronis atau menular'],
            ['kelompok' => 'II', 'nomor' => 15, 'pertanyaan' => 'Bengkak pada muka/tungkai dan tekanan darah tinggi (preeklampsia)', 'skor' => 4, 'keterangan' => 'Tanda-tanda preeklampsia ringan'],
            ['kelompok' => 'II', 'nomor' => 16, 'pertanyaan' => 'Hamil kembar 2 atau lebih', 'skor' => 4, 'keterangan' => 'Kehamilan ganda/multipel'],
            ['kelompok' => 'II', 'nomor' => 17, 'pertanyaan' => 'Hamil kembar air (hidramnion)', 'skor' => 4, 'keterangan' => 'Kelebihan air ketuban'],
            ['kelompok' => 'II', 'nomor' => 18, 'pertanyaan' => 'Bayi mati dalam kandungan', 'skor' => 4, 'keterangan' => 'IUFD (Intrauterine Fetal Death)'],
            ['kelompok' => 'II', 'nomor' => 19, 'pertanyaan' => 'Kehamilan lebih bulan (serotinus)', 'skor' => 4, 'keterangan' => 'Kehamilan > 42 minggu'],
            ['kelompok' => 'II', 'nomor' => 20, 'pertanyaan' => 'Letak sungsang', 'skor' => 8, 'keterangan' => 'Presentasi bokong pada trimester III'],
            ['kelompok' => 'II', 'nomor' => 21, 'pertanyaan' => 'Letak lintang', 'skor' => 8, 'keterangan' => 'Posisi janin melintang pada trimester III'],

            // Kelompok III - AGDO
            ['kelompok' => 'III', 'nomor' => 22, 'pertanyaan' => 'Perdarahan dalam kehamilan ini', 'skor' => 8, 'keterangan' => 'Perdarahan pervaginam selama kehamilan'],
            ['kelompok' => 'III', 'nomor' => 23, 'pertanyaan' => 'Preeklampsia berat / kejang-kejang (eklampsia)', 'skor' => 8, 'keterangan' => 'Preeklampsia berat dengan atau tanpa kejang'],
        ];

        foreach ($pertanyaan as $p) {
            PertanyaanSpr::create($p);
        }
    }

    /**
     * Seed demo desa data.
     *
     * @return Collection<int, Desa>
     */
    private function seedDesas(): Collection
    {
        $desaData = [
            // Daerah 1: Puskesmas Ciomas (Kecamatan Ciomas)
            [
                'nama' => 'Desa Sukamaju',
                'puskesmas' => 'Puskesmas Ciomas',
                'kecamatan' => 'Kecamatan Ciomas',
                'kabupaten' => 'Kabupaten Bogor',
                'provinsi' => 'Jawa Barat',
                'kode_desa' => '32.01.01.0001',
            ],
            [
                'nama' => 'Desa Sukarasa',
                'puskesmas' => 'Puskesmas Ciomas',
                'kecamatan' => 'Kecamatan Ciomas',
                'kabupaten' => 'Kabupaten Bogor',
                'provinsi' => 'Jawa Barat',
                'kode_desa' => '32.01.01.0002',
            ],

            // Daerah 2: Puskesmas Dramaga (Kecamatan Dramaga)
            [
                'nama' => 'Desa Babakan Dramaga',
                'puskesmas' => 'Puskesmas Dramaga',
                'kecamatan' => 'Kecamatan Dramaga',
                'kabupaten' => 'Kabupaten Bogor',
                'provinsi' => 'Jawa Barat',
                'kode_desa' => '32.01.02.0001',
            ],
            [
                'nama' => 'Desa Petir',
                'puskesmas' => 'Puskesmas Dramaga',
                'kecamatan' => 'Kecamatan Dramaga',
                'kabupaten' => 'Kabupaten Bogor',
                'provinsi' => 'Jawa Barat',
                'kode_desa' => '32.01.02.0002',
            ],
        ];

        return collect($desaData)->map(fn (array $data) => Desa::create($data));
    }

    /**
     * Seed demo users for each role.
     *
     * @param  Collection<int, Desa>  $desas
     */
    private function seedUsers(Collection $desas): void
    {
        // Admin Utama: Dinkes Provinsi (Level 100)
        User::create([
            'name' => 'Admin Utama Dinkes Prov. Jabar',
            'email' => 'admin.provinsi@edetimil.test',
            'password' => Hash::make('password'),
            'role' => Role::Admin,
            'desa_id' => null,
            'instansi' => 'Dinas Kesehatan Provinsi Jawa Barat',
            'jabatan' => 'Admin Utama Dinkes Provinsi',
            'provinsi' => 'Jawa Barat',
            'phone' => '081122334455',
            'nik' => '3200000000000001',
            'alamat' => 'Kantor Dinas Kesehatan Provinsi Jawa Barat',
            'is_active' => true,
        ]);

        // Admin Regional: Dinkes Kabupaten Bogor (Level 80)
        User::create([
            'name' => 'Admin Dinkes Kab. Bogor',
            'email' => 'admin@edetimil.test',
            'password' => Hash::make('password'),
            'role' => Role::Admin,
            'desa_id' => null,
            'instansi' => 'Dinas Kesehatan Kabupaten Bogor',
            'jabatan' => 'Admin Dinkes Kabupaten',
            'kabupaten' => 'Kabupaten Bogor',
            'provinsi' => 'Jawa Barat',
            'phone' => '081234567890',
            'nik' => '3201010101010001',
            'alamat' => 'Kantor Dinas Kesehatan Kabupaten Bogor',
            'is_active' => true,
        ]);

        // Bidan per desa
        foreach ($desas as $index => $desa) {
            User::create([
                'name' => 'Bidan '.fake()->firstName('female').' '.$desa->nama,
                'email' => 'bidan'.($index + 1).'@edetimil.test',
                'password' => Hash::make('password'),
                'role' => Role::Bidan,
                'desa_id' => $desa->id,
                'instansi' => $desa->puskesmas,
                'kecamatan' => $desa->kecamatan,
                'kabupaten' => $desa->kabupaten,
                'provinsi' => $desa->provinsi,
                'phone' => fake()->phoneNumber(),
                'nik' => fake()->unique()->numerify('################'),
                'alamat' => $desa->nama.', '.$desa->kecamatan,
                'is_active' => true,
            ]);

            // 2 Kader per desa
            for ($k = 1; $k <= 2; $k++) {
                User::create([
                    'name' => 'Kader '.fake()->firstName('female').' '.$desa->nama,
                    'email' => 'kader'.($index * 2 + $k).'@edetimil.test',
                    'password' => Hash::make('password'),
                    'role' => Role::Kader,
                    'desa_id' => $desa->id,
                    'instansi' => 'Posyandu '.$desa->nama.' 0'.$k,
                    'kecamatan' => $desa->kecamatan,
                    'kabupaten' => $desa->kabupaten,
                    'provinsi' => $desa->provinsi,
                    'phone' => fake()->phoneNumber(),
                    'nik' => fake()->unique()->numerify('################'),
                    'alamat' => $desa->nama.', '.$desa->kecamatan,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Seed demo ibu hamil data with skrining and kunjungan.
     *
     * @param  Collection<int, Desa>  $desas
     */
    private function seedIbuHamilAndSkrining(Collection $desas): void
    {
        $pertanyaanSprs = PertanyaanSpr::all();

        foreach ($desas as $desa) {
            $users = User::where('desa_id', $desa->id)->get();
            $bidan = $users->firstWhere('role', Role::Bidan);
            $kaders = $users->where('role', Role::Kader);

            // 4-6 ibu hamil per desa
            $jumlahIbuHamil = fake()->numberBetween(4, 6);

            for ($i = 0; $i < $jumlahIbuHamil; $i++) {
                $inputUser = fake()->randomElement([$bidan, ...$kaders]);

                $ibuHamil = IbuHamil::factory()->create([
                    'user_id' => $inputUser->id,
                    'desa_id' => $desa->id,
                ]);

                // Create 1-3 skrining per ibu hamil
                $jumlahSkrining = fake()->numberBetween(1, 3);
                for ($s = 0; $s < $jumlahSkrining; $s++) {
                    $skriningUser = fake()->randomElement([$bidan, ...$kaders]);
                    $totalSkor = 2; // base score

                    $details = [];
                    foreach ($pertanyaanSprs as $pertanyaan) {
                        $jawaban = fake()->boolean(15);
                        $skor = $jawaban ? $pertanyaan->skor : 0;
                        $totalSkor += $skor;

                        $details[] = [
                            'pertanyaan_spr_id' => $pertanyaan->id,
                            'jawaban' => $jawaban,
                            'skor' => $skor,
                        ];
                    }

                    $skrining = Skrining::create([
                        'ibu_hamil_id' => $ibuHamil->id,
                        'user_id' => $skriningUser->id,
                        'tanggal_skrining' => fake()->dateTimeBetween('-6 months', 'now'),
                        'usia_kehamilan_minggu' => fake()->numberBetween(4, 40),
                        'trimester' => fake()->numberBetween(1, 3),
                        'total_skor' => $totalSkor,
                        'kategori_risiko' => KategoriRisiko::fromScore($totalSkor),
                        'catatan' => fake()->optional()->sentence(),
                    ]);

                    foreach ($details as $detail) {
                        SkriningDetail::create(array_merge($detail, [
                            'skrining_id' => $skrining->id,
                        ]));
                    }
                }

                // Create kunjungan
                $jenisKunjungan = ['K1', 'K2', 'K3', 'K4'];
                $jumlahKunjungan = fake()->numberBetween(1, 4);

                for ($k = 0; $k < $jumlahKunjungan; $k++) {
                    $hadir = fake()->boolean(80);
                    $dilayani = $hadir ? fake()->boolean(90) : false;
                    $metode = fake()->randomElement(['jemput_bola', 'posyandu', 'puskesmas']);

                    Kunjungan::create([
                        'ibu_hamil_id' => $ibuHamil->id,
                        'user_id' => $bidan->id,
                        'desa_id' => $desa->id,
                        'tanggal_kunjungan' => fake()->dateTimeBetween('-6 months', 'now'),
                        'jenis_kunjungan' => $jenisKunjungan[$k],
                        'tahap_anc' => $jenisKunjungan[$k],
                        'metode_kunjungan' => $metode,
                        'berat_badan' => fake()->randomFloat(1, 45.0, 75.0),
                        'tinggi_badan' => fake()->randomFloat(1, 148.0, 168.0),
                        'tekanan_darah_sistol' => fake()->numberBetween(105, 135),
                        'tekanan_darah_diastol' => fake()->numberBetween(65, 88),
                        'lila' => fake()->randomFloat(1, 22.0, 29.5),
                        'tfu' => fake()->randomFloat(1, 16.0, 32.0),
                        'djj' => fake()->numberBetween(120, 158),
                        'hb' => fake()->randomFloat(1, 10.5, 13.8),
                        'kepatuhan_fe' => fake()->boolean(85),
                        'status_hadir' => $hadir,
                        'status_dilayani' => $dilayani,
                        'catatan' => fake()->optional()->sentence(),
                        'bentuk_pendampingan' => fake()->randomElement([
                            'Kunjungan rumah kader: Pemantauan konsumsi tablet Fe rutin dan gizi seimbang',
                            'Pendampingan deteksi dini risiko kehamilan dan edukasi tanda bahaya trimester',
                            'Konseling persiapan persalinan faskes dan donor darah keluarga',
                            'Pendampingan ANC dan pengukuran LiLA berkala di rumah',
                        ]),
                        'jadwal_kunjungan_berikutnya' => fake()->dateTimeBetween('now', '+30 days'),
                    ]);
                }
            }
        }
    }
}
