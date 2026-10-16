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
            ['nama' => 'Desa Sukamaju', 'kecamatan' => 'Kecamatan Ciomas', 'kabupaten' => 'Kabupaten Bogor', 'kode_desa' => '32.01.01.0001'],
            ['nama' => 'Desa Sukarasa', 'kecamatan' => 'Kecamatan Ciomas', 'kabupaten' => 'Kabupaten Bogor', 'kode_desa' => '32.01.01.0002'],
            ['nama' => 'Desa Mekarjaya', 'kecamatan' => 'Kecamatan Dramaga', 'kabupaten' => 'Kabupaten Bogor', 'kode_desa' => '32.01.02.0001'],
            ['nama' => 'Desa Cibanteng', 'kecamatan' => 'Kecamatan Dramaga', 'kabupaten' => 'Kabupaten Bogor', 'kode_desa' => '32.01.02.0002'],
            ['nama' => 'Desa Babakan', 'kecamatan' => 'Kecamatan Ciseeng', 'kabupaten' => 'Kabupaten Bogor', 'kode_desa' => '32.01.03.0001'],
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
        // Admin
        User::create([
            'name' => 'Admin E-Detimil',
            'email' => 'admin@edetimil.test',
            'password' => Hash::make('password'),
            'role' => Role::Admin,
            'desa_id' => null,
            'phone' => '081234567890',
            'nik' => '3201010101010001',
            'alamat' => 'Kantor Dinas Kesehatan',
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
                    Kunjungan::create([
                        'ibu_hamil_id' => $ibuHamil->id,
                        'user_id' => $bidan->id,
                        'desa_id' => $desa->id,
                        'tanggal_kunjungan' => fake()->dateTimeBetween('-6 months', 'now'),
                        'jenis_kunjungan' => $jenisKunjungan[$k],
                        'status_hadir' => $hadir = fake()->boolean(80),
                        'status_dilayani' => $hadir ? fake()->boolean(90) : false,
                        'catatan' => fake()->optional()->sentence(),
                    ]);
                }
            }
        }
    }
}
