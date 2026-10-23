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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for Kabupaten Indragiri Hulu.
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
     * Seed realistic desa & puskesmas data for Kabupaten Indragiri Hulu.
     *
     * @return Collection<int, Desa>
     */
    private function seedDesas(): Collection
    {
        $desaData = [
            // 1. UPT Puskesmas Air Molek (Kecamatan Pasir Penyu)
            [
                'nama' => 'Desa Candirejo',
                'puskesmas' => 'UPT Puskesmas Air Molek',
                'kecamatan' => 'Pasir Penyu',
                'kabupaten' => 'Kabupaten Indragiri Hulu',
                'provinsi' => 'Riau',
                'kode_desa' => '14.02.01.2001',
            ],
            [
                'nama' => 'Desa Lembah Dusun Gading',
                'puskesmas' => 'UPT Puskesmas Air Molek',
                'kecamatan' => 'Pasir Penyu',
                'kabupaten' => 'Kabupaten Indragiri Hulu',
                'provinsi' => 'Riau',
                'kode_desa' => '14.02.01.2002',
            ],
            [
                'nama' => 'Desa Serumpun Jaya',
                'puskesmas' => 'UPT Puskesmas Air Molek',
                'kecamatan' => 'Pasir Penyu',
                'kabupaten' => 'Kabupaten Indragiri Hulu',
                'provinsi' => 'Riau',
                'kode_desa' => '14.02.01.2003',
            ],

            // 2. UPT Puskesmas Sungai Parit (Kecamatan Sungai Lala)
            [
                'nama' => 'Desa Perkebunan Sungai Parit',
                'puskesmas' => 'UPT Puskesmas Sungai Parit',
                'kecamatan' => 'Sungai Lala',
                'kabupaten' => 'Kabupaten Indragiri Hulu',
                'provinsi' => 'Riau',
                'kode_desa' => '14.02.08.2001',
            ],
            [
                'nama' => 'Desa Pasir Kelampaian',
                'puskesmas' => 'UPT Puskesmas Sungai Parit',
                'kecamatan' => 'Sungai Lala',
                'kabupaten' => 'Kabupaten Indragiri Hulu',
                'provinsi' => 'Riau',
                'kode_desa' => '14.02.08.2002',
            ],

            // 3. UPT Puskesmas Pematang Reba (Kecamatan Rengat Barat)
            [
                'nama' => 'Kelurahan Pematang Reba',
                'puskesmas' => 'UPT Puskesmas Pematang Reba',
                'kecamatan' => 'Rengat Barat',
                'kabupaten' => 'Kabupaten Indragiri Hulu',
                'provinsi' => 'Riau',
                'kode_desa' => '14.02.03.1001',
            ],
            [
                'nama' => 'Desa Talang Jerinjing',
                'puskesmas' => 'UPT Puskesmas Pematang Reba',
                'kecamatan' => 'Rengat Barat',
                'kabupaten' => 'Kabupaten Indragiri Hulu',
                'provinsi' => 'Riau',
                'kode_desa' => '14.02.03.2002',
            ],

            // 4. UPT Puskesmas Batang Gansal (Kecamatan Batang Gansal)
            [
                'nama' => 'Desa Seberida',
                'puskesmas' => 'UPT Puskesmas Batang Gansal',
                'kecamatan' => 'Batang Gansal',
                'kabupaten' => 'Kabupaten Indragiri Hulu',
                'provinsi' => 'Riau',
                'kode_desa' => '14.02.10.2001',
            ],
            [
                'nama' => 'Desa Belimbing',
                'puskesmas' => 'UPT Puskesmas Batang Gansal',
                'kecamatan' => 'Batang Gansal',
                'kabupaten' => 'Kabupaten Indragiri Hulu',
                'provinsi' => 'Riau',
                'kode_desa' => '14.02.10.2002',
            ],
        ];

        return collect($desaData)->map(fn (array $data) => Desa::create($data));
    }

    /**
     * Seed users for Dinkes Kabupaten, UPT Puskesmas Admins, Midwives, and Cadres.
     *
     * @param  Collection<int, Desa>  $desas
     */
    private function seedUsers(Collection $desas): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Admin Dinas Kesehatan Kabupaten Indragiri Hulu (Super Admin Wilayah - Level 80)
        User::create([
            'name' => 'Admin Dinkes Kab. Indragiri Hulu',
            'email' => 'admin@edetimil.test',
            'password' => $defaultPassword,
            'role' => Role::Admin,
            'desa_id' => null,
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'jabatan' => 'Kepala Seksi KIA / Admin Dinkes Kabupaten',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'phone' => '081234567890',
            'nik' => '1402010101010001',
            'nip' => '197801012002121001',
            'alamat' => 'Komplek Perkantoran Pemkab Inhu, Pematang Reba',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Operator KIA Dinkes Inhu',
            'email' => 'dinkes@edetimil.test',
            'password' => $defaultPassword,
            'role' => Role::Admin,
            'desa_id' => null,
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'jabatan' => 'Staf Bidang Kesehatan Masyarakat',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'phone' => '081277889900',
            'nik' => '1402010101010002',
            'nip' => '198505152008012002',
            'alamat' => 'Komplek Perkantoran Pemkab Inhu, Pematang Reba',
            'is_active' => true,
        ]);

        // 2. Admin UPT Puskesmas (Level 60)
        $airMolekDesa = $desas->firstWhere('puskesmas', 'UPT Puskesmas Air Molek');
        User::create([
            'name' => 'Kepala UPT Puskesmas Air Molek',
            'email' => 'admin.airmolek@edetimil.test',
            'password' => $defaultPassword,
            'role' => Role::Admin,
            'desa_id' => $airMolekDesa?->id,
            'instansi' => 'UPT Puskesmas Air Molek',
            'jabatan' => 'Kepala UPT Puskesmas',
            'kecamatan' => 'Pasir Penyu',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'phone' => '081365443322',
            'nik' => '1402010202800001',
            'nip' => '198002022005011002',
            'alamat' => 'Jl. Jenderal Sudirman No. 45, Air Molek',
            'is_active' => true,
        ]);

        $sungaiParitDesa = $desas->firstWhere('puskesmas', 'UPT Puskesmas Sungai Parit');
        User::create([
            'name' => 'Kepala UPT Puskesmas Sungai Parit',
            'email' => 'admin.sungaiparit@edetimil.test',
            'password' => $defaultPassword,
            'role' => Role::Admin,
            'desa_id' => $sungaiParitDesa?->id,
            'instansi' => 'UPT Puskesmas Sungai Parit',
            'jabatan' => 'Kepala UPT Puskesmas',
            'kecamatan' => 'Sungai Lala',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'phone' => '081365443323',
            'nik' => '1402080303820002',
            'nip' => '198203032006021003',
            'alamat' => 'Jl. Raya Sungai Parit, Kec. Sungai Lala',
            'is_active' => true,
        ]);

        // 3. Bidan Desa & Kader Posyandu for each village
        foreach ($desas as $index => $desa) {
            $isAirMolek = ($desa->nama === 'Desa Candirejo');
            $isSungaiParit = ($desa->nama === 'Desa Perkebunan Sungai Parit');

            $bidanEmail = $isAirMolek
                ? 'bidan.airmolek@edetimil.test'
                : ($isSungaiParit ? 'bidan.sungaiparit@edetimil.test' : 'bidan'.($index + 1).'@edetimil.test');

            $kaderEmail = $isAirMolek
                ? 'kader.airmolek@edetimil.test'
                : ($isSungaiParit ? 'kader.sungaiparit@edetimil.test' : 'kader'.($index * 2 + 1).'@edetimil.test');

            // Bidan Desa (Level 40)
            User::create([
                'name' => 'Bidan '.($isAirMolek ? 'Sri Wahyuni, S.Tr.Keb' : ($isSungaiParit ? 'Siti Nurhaliza, Amd.Keb' : fake()->firstName('female').' '.$desa->nama)),
                'email' => $bidanEmail,
                'password' => $defaultPassword,
                'role' => Role::Bidan,
                'desa_id' => $desa->id,
                'instansi' => $desa->puskesmas,
                'jabatan' => 'Bidan Desa '.$desa->nama,
                'kecamatan' => $desa->kecamatan,
                'kabupaten' => $desa->kabupaten,
                'provinsi' => $desa->provinsi,
                'phone' => '0821'.fake()->numerify('########'),
                'nik' => fake()->unique()->numerify('1402############'),
                'nip' => '1990'.fake()->numerify('##########'),
                'str' => '1402'.fake()->numerify('########'),
                'alamat' => 'Polindes '.$desa->nama.', Kec. '.$desa->kecamatan,
                'is_active' => true,
            ]);

            // Kader Posyandu Utama (Level 20)
            User::create([
                'name' => 'Kader '.($isAirMolek ? 'Fatimah Az-Zahra' : ($isSungaiParit ? 'Aminah Hasibuan' : fake()->firstName('female').' '.$desa->nama)),
                'email' => $kaderEmail,
                'password' => $defaultPassword,
                'role' => Role::Kader,
                'desa_id' => $desa->id,
                'instansi' => 'Posyandu '.$desa->nama.' Dahlia 01',
                'jabatan' => 'Kader Posyandu Lansia & Bumil',
                'kecamatan' => $desa->kecamatan,
                'kabupaten' => $desa->kabupaten,
                'provinsi' => $desa->provinsi,
                'phone' => '0852'.fake()->numerify('########'),
                'nik' => fake()->unique()->numerify('1402############'),
                'alamat' => 'RT 02 / RW 01 '.$desa->nama,
                'is_active' => true,
            ]);

            // Kader Posyandu Pendamping
            User::create([
                'name' => 'Kader '.fake()->firstName('female').' (Posyandu 02)',
                'email' => 'kader'.($index * 2 + 2).'@edetimil.test',
                'password' => $defaultPassword,
                'role' => Role::Kader,
                'desa_id' => $desa->id,
                'instansi' => 'Posyandu '.$desa->nama.' Melati 02',
                'jabatan' => 'Kader Pendamping Lapangan',
                'kecamatan' => $desa->kecamatan,
                'kabupaten' => $desa->kabupaten,
                'provinsi' => $desa->provinsi,
                'phone' => '0853'.fake()->numerify('########'),
                'nik' => fake()->unique()->numerify('1402############'),
                'alamat' => 'RT 04 / RW 02 '.$desa->nama,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Seed demo pregnant mothers with Poedji Rochjati screening and Jemput Bola visits.
     *
     * @param  Collection<int, Desa>  $desas
     */
    private function seedIbuHamilAndSkrining(Collection $desas): void
    {
        $pertanyaanSprs = PertanyaanSpr::all();

        foreach ($desas as $desa) {
            $users = User::where('desa_id', $desa->id)->get();
            $bidan = $users->firstWhere('role', Role::Bidan) ?? User::firstWhere('role', Role::Bidan);
            $kaders = $users->where('role', Role::Kader);

            // 5 mothers per village
            $jumlahIbuHamil = 5;

            for ($i = 0; $i < $jumlahIbuHamil; $i++) {
                $inputUser = ($kaders->isNotEmpty() && fake()->boolean(60))
                    ? $kaders->random()
                    : $bidan;

                $ibuHamil = IbuHamil::factory()->create([
                    'user_id' => $inputUser->id,
                    'desa_id' => $desa->id,
                ]);

                // Create 1-2 SPR screenings
                $jumlahSkrining = fake()->numberBetween(1, 2);
                for ($s = 0; $s < $jumlahSkrining; $s++) {
                    $skriningUser = ($kaders->isNotEmpty() && fake()->boolean(40)) ? $kaders->random() : $bidan;

                    // Ensure representation of KRR, KRT, and KRST cases
                    $targetProfile = match ($i % 3) {
                        0 => 'KRST', // High risk (e.g. Riwayat SC / Preeclampsia / Perdarahan)
                        1 => 'KRT',  // Moderate risk (e.g. Anemia / KEK / Terlalu muda/tua)
                        default => 'KRR', // Low risk
                    };

                    $totalSkor = 2; // Poedji Rochjati base score
                    $details = [];

                    foreach ($pertanyaanSprs as $p) {
                        $jawaban = false;

                        if ($targetProfile === 'KRST') {
                            if (in_array($p->nomor, [13, 23, 22])) {
                                $jawaban = true;
                            }
                        } elseif ($targetProfile === 'KRT') {
                            if (in_array($p->nomor, [7, 8, 14]) && fake()->boolean(70)) {
                                $jawaban = true;
                            }
                        }

                        $skor = $jawaban ? $p->skor : 0;
                        $totalSkor += $skor;

                        $details[] = [
                            'pertanyaan_spr_id' => $p->id,
                            'jawaban' => $jawaban,
                            'skor' => $skor,
                        ];
                    }

                    $skrining = Skrining::create([
                        'ibu_hamil_id' => $ibuHamil->id,
                        'user_id' => $skriningUser->id,
                        'tanggal_skrining' => Carbon::now()->subMonths(3 - $s),
                        'usia_kehamilan_minggu' => 14 + ($s * 10),
                        'trimester' => $s + 1,
                        'total_skor' => $totalSkor,
                        'kategori_risiko' => KategoriRisiko::fromScore($totalSkor),
                        'catatan' => $targetProfile === 'KRST'
                            ? 'Prioritas pemantauan ketat bidan desa & rujukan terencana ke RSUD Indrasari Rengat.'
                            : ($targetProfile === 'KRT' ? 'Edukasi gizi seimbang, kepatuhan tablet tambah darah, dan kunjungan rumah berkala.' : 'Kondisi ibu & janin dalam batas normal.'),
                    ]);

                    foreach ($details as $detail) {
                        SkriningDetail::create(array_merge($detail, [
                            'skrining_id' => $skrining->id,
                        ]));
                    }
                }

                // Create ANC & Jemput Bola visits
                $tahapList = ['K1', 'K2', 'K3'];
                foreach ($tahapList as $idx => $tahap) {
                    $isJemputBola = ($i % 2 === 0); // Alternate jemput bola visits
                    $metode = $isJemputBola ? 'jemput_bola' : (fake()->boolean(50) ? 'posyandu' : 'puskesmas');

                    // Make some visits overdue or completed
                    $isOverdueCase = ($i === 0 && $idx === 2);
                    $jadwalBerikutnya = $isOverdueCase
                        ? Carbon::now()->subDays(8) // Overdue > 7 days -> triggers Jemput Bola priority queue!
                        : Carbon::now()->addDays(14 * ($idx + 1));

                    $alasanJb = null;
                    if ($metode === 'jemput_bola') {
                        $alasanJb = match ($i % 4) {
                            0 => 'krst',
                            1 => 'mangkir',
                            2 => 'anemia_kek',
                            default => 'geografis',
                        };
                    }

                    Kunjungan::create([
                        'ibu_hamil_id' => $ibuHamil->id,
                        'user_id' => $bidan->id,
                        'desa_id' => $desa->id,
                        'tanggal_kunjungan' => Carbon::now()->subMonths(2)->addWeeks($idx * 3),
                        'jenis_kunjungan' => $tahap,
                        'tahap_anc' => $tahap,
                        'metode_kunjungan' => $metode,
                        'alasan_jemput_bola' => $alasanJb,
                        'berat_badan' => fake()->randomFloat(1, 48.0, 72.0),
                        'tinggi_badan' => 153.0,
                        'tekanan_darah_sistol' => $targetProfile === 'KRST' ? 140 : 115,
                        'tekanan_darah_diastol' => $targetProfile === 'KRST' ? 95 : 75,
                        'lila' => fake()->randomFloat(1, 21.5, 28.0),
                        'tfu' => fake()->randomFloat(1, 18.0, 30.0),
                        'djj' => fake()->numberBetween(130, 150),
                        'hb' => $targetProfile === 'KRT' ? 9.8 : 12.2,
                        'kepatuhan_fe' => fake()->boolean(85),
                        'status_hadir' => ! $isOverdueCase,
                        'status_dilayani' => ! $isOverdueCase,
                        'catatan' => $isJemputBola
                            ? 'Pelayanan jemput bola ke rumah ibu hamil: pemeriksaan TTV, evaluasi P4K, dan edukasi keluarga.'
                            : 'Pemeriksaan ANC rutin di fasilitas kesehatan.',
                        'bentuk_pendampingan' => $isJemputBola
                            ? 'Pendampingan kunjungan rumah bidan & kader: pemantauan TTV, konsumsi Fe, dan tanda bahaya.'
                            : 'Edukasi dan konseling rutin di posyandu.',
                        'pendampingan_suami' => fake()->boolean(75),
                        'p4k_siap' => fake()->boolean(80),
                        'jadwal_kunjungan_berikutnya' => $jadwalBerikutnya,
                    ]);
                }
            }
        }
    }
}
