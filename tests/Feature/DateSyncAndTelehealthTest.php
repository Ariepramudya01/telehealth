<?php

namespace Tests\Feature;

use App\Enums\KelompokFaktorRisiko;
use App\Enums\Role;
use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\PertanyaanSpr;
use App\Models\User;
use App\Services\WilayahService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateSyncAndTelehealthTest extends TestCase
{
    use RefreshDatabase;

    private User $kader;

    private Desa $desa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->desa = Desa::create([
            'nama' => 'Desa Sehat',
            'kecamatan' => 'Kecamatan Makmur',
            'kabupaten' => 'Kabupaten Sukabumi',
            'puskesmas' => 'Puskesmas Cikembar',
        ]);

        $this->kader = User::create([
            'name' => 'Kader Ani',
            'email' => 'kader.ani@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Kader,
            'desa_id' => $this->desa->id,
            'is_active' => true,
        ]);
    }

    public function test_ibu_hamil_dates_in_dmy_format_are_normalized_and_saved(): void
    {
        $response = $this->actingAs($this->kader)->post(route('ibu-hamil.store'), [
            'nama' => 'Ny. Dewi Sartika',
            'nik' => '3201123456780001',
            'tanggal_lahir' => '15-08-1996',
            'hpht' => '01-02-2026',
            'taksiran_persalinan' => '08-11-2026',
            'nama_suami' => 'Tn. Raden',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Merdeka No. 10',
            'desa_id' => $this->desa->id,
            'tinggi_badan' => 158,
            'berat_badan' => 55,
            'tekanan_darah_sistol' => 110,
            'tekanan_darah_diastol' => 70,
            'lila' => 24.5,
            'hemoglobin' => 12.0,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
        ]);

        $response->assertRedirect();

        $bumil = IbuHamil::where('nama', 'Ny. Dewi Sartika')->first();
        $this->assertNotNull($bumil);
        $this->assertEquals('1996-08-15', $bumil->tanggal_lahir->format('Y-m-d'));
        $this->assertEquals('2026-02-01', $bumil->hpht->format('Y-m-d'));
        $this->assertEquals('2026-11-08', $bumil->taksiran_persalinan->format('Y-m-d'));
    }

    public function test_kunjungan_dates_in_dmy_format_are_normalized_and_saved(): void
    {
        $bumil = IbuHamil::create([
            'nama' => 'Ny. Siti',
            'nik' => '3201123456780002',
            'desa_id' => $this->desa->id,
            'user_id' => $this->kader->id,
            'hpht' => '2026-01-01',
            'taksiran_persalinan' => '2026-10-08',
            'status_gizi' => 'baik',
            'tinggi_badan' => 155,
            'berat_badan' => 52,
            'tekanan_darah_sistol' => 120,
            'tekanan_darah_diastol' => 80,
        ]);

        $response = $this->actingAs($this->kader)->post(route('kunjungan.store'), [
            'ibu_hamil_id' => $bumil->id,
            'desa_id' => $this->desa->id,
            'tanggal_kunjungan' => '18-10-2026',
            'jadwal_kunjungan_berikutnya' => '18-11-2026',
            'tahap_anc' => 'K1',
            'metode_kunjungan' => 'jemput_bola',
            'tinggi_badan' => 155,
            'berat_badan' => 52,
            'tekanan_darah_sistol' => 120,
            'tekanan_darah_diastol' => 80,
            'lila' => 24,
            'tfu' => 20,
            'djj' => 140,
            'hb' => 11.8,
            'kepatuhan_fe' => 1,
            'catatan' => 'Kunjungan jemput bola pertama lancar',
            'status_hadir' => 1,
            'action' => 'save_and_screen',
        ]);

        $kunjungan = Kunjungan::where('ibu_hamil_id', $bumil->id)->first();
        $this->assertNotNull($kunjungan);
        $this->assertEquals('2026-10-18', $kunjungan->tanggal_kunjungan->format('Y-m-d'));
        $this->assertEquals('2026-11-18', $kunjungan->jadwal_kunjungan_berikutnya->format('Y-m-d'));
        $this->assertTrue((bool) $kunjungan->status_hadir);

        $response->assertRedirect(route('skrining.create', [
            'ibu_hamil_id' => $bumil->id,
            'kunjungan_id' => $kunjungan->id,
            'tanggal' => '2026-10-18',
        ]));
    }

    public function test_skrining_submission_auto_activates_status_dilayani_on_linked_kunjungan(): void
    {
        $bumil = IbuHamil::create([
            'nama' => 'Ny. Rina',
            'nik' => '3201123456780003',
            'desa_id' => $this->desa->id,
            'user_id' => $this->kader->id,
            'hpht' => '2026-02-01',
            'status_gizi' => 'baik',
            'tinggi_badan' => 156,
            'berat_badan' => 54,
            'tekanan_darah_sistol' => 115,
            'tekanan_darah_diastol' => 75,
        ]);

        $kunjungan = Kunjungan::create([
            'ibu_hamil_id' => $bumil->id,
            'desa_id' => $this->desa->id,
            'user_id' => $this->kader->id,
            'tanggal_kunjungan' => '2026-10-18',
            'jenis_kunjungan' => 'K1',
            'tahap_anc' => 'K1',
            'metode_kunjungan' => 'jemput_bola',
            'status_hadir' => false,
            'status_dilayani' => false,
        ]);

        // Create 23 SPR questions
        for ($i = 1; $i <= 23; $i++) {
            PertanyaanSpr::create([
                'nomor' => $i,
                'kelompok' => $i <= 13 ? KelompokFaktorRisiko::APGO : ($i <= 17 ? KelompokFaktorRisiko::AGO : KelompokFaktorRisiko::AGDO),
                'pertanyaan' => "Pertanyaan SPR $i",
                'skor' => ($i === 1) ? 2 : 4,
            ]);
        }

        $response = $this->actingAs($this->kader)->post(route('skrining.store'), [
            'ibu_hamil_id' => $bumil->id,
            'kunjungan_id' => $kunjungan->id,
            'tanggal_skrining' => '18-10-2026',
            'trimester' => 1,
            'answers' => [1 => 1],
        ]);

        $response->assertRedirect();

        // Linked kunjungan should now be marked as hadir and dilayani!
        $kunjungan->refresh();
        $this->assertTrue((bool) $kunjungan->status_hadir);
        $this->assertTrue((bool) $kunjungan->status_dilayani);
        $this->assertNotNull($kunjungan->skrining_id);
    }

    public function test_unchecking_tablet_fe_correctly_saves_false_in_database(): void
    {
        $bumil = IbuHamil::create([
            'nama' => 'Ny. Maya',
            'desa_id' => $this->desa->id,
            'user_id' => $this->kader->id,
            'hpht' => '2026-01-01',
            'status_gizi' => 'baik',
        ]);

        // 1. Store with kepatuhan_fe = 0
        $response = $this->actingAs($this->kader)->post(route('kunjungan.store'), [
            'ibu_hamil_id' => $bumil->id,
            'desa_id' => $this->desa->id,
            'tanggal_kunjungan' => '18-10-2026',
            'tahap_anc' => 'K2',
            'metode_kunjungan' => 'jemput_bola',
            'tinggi_badan' => 155,
            'berat_badan' => 53,
            'tekanan_darah_sistol' => 110,
            'tekanan_darah_diastol' => 70,
            'kepatuhan_fe' => 0, // Unchecked
            'status_hadir' => 1,
        ]);

        $response->assertRedirect();
        $kunjungan = Kunjungan::where('ibu_hamil_id', $bumil->id)->first();
        $this->assertNotNull($kunjungan);
        $this->assertFalse((bool) $kunjungan->kepatuhan_fe);

        // 2. Update with kepatuhan_fe = 0
        $updateResponse = $this->actingAs($this->kader)->put(route('kunjungan.update', $kunjungan), [
            'desa_id' => $this->desa->id,
            'tanggal_kunjungan' => '18-10-2026',
            'tahap_anc' => 'K2',
            'metode_kunjungan' => 'jemput_bola',
            'tinggi_badan' => 155,
            'berat_badan' => 53,
            'tekanan_darah_sistol' => 110,
            'tekanan_darah_diastol' => 70,
            'kepatuhan_fe' => 0, // Unchecked
            'status_hadir' => 1,
        ]);

        $updateResponse->assertRedirect();
        $kunjungan->refresh();
        $this->assertFalse((bool) $kunjungan->kepatuhan_fe);

        // 3. Konfirmasi kehadiran with kepatuhan_fe = 0
        $kunjunganUnconfirmed = Kunjungan::create([
            'ibu_hamil_id' => $bumil->id,
            'desa_id' => $this->desa->id,
            'user_id' => $this->kader->id,
            'tanggal_kunjungan' => '2026-10-18',
            'jenis_kunjungan' => 'K3',
            'tahap_anc' => 'K3',
            'status_hadir' => false,
            'kepatuhan_fe' => true,
        ]);

        $konfirmasiResponse = $this->actingAs($this->kader)->post(route('kunjungan.konfirmasi', $kunjunganUnconfirmed), [
            'status_hadir' => 1,
            'tinggi_badan' => 155,
            'berat_badan' => 54,
            'tekanan_darah_sistol' => 115,
            'tekanan_darah_diastol' => 75,
            'kepatuhan_fe' => 0, // Unchecked
        ]);

        $konfirmasiResponse->assertRedirect();
        $kunjunganUnconfirmed->refresh();
        $this->assertTrue((bool) $kunjunganUnconfirmed->status_hadir);
        $this->assertFalse((bool) $kunjunganUnconfirmed->kepatuhan_fe);
    }

    public function test_low_hb_and_screening_date_sync_in_skrining_controller(): void
    {
        $bumil = IbuHamil::create([
            'nama' => 'Ny. Anemia Test',
            'desa_id' => $this->desa->id,
            'user_id' => $this->kader->id,
            'hpht' => '2026-01-01',
            'status_gizi' => 'baik',
            'hemoglobin' => 9.5, // Anemia (< 11.0)
        ]);

        $kunjungan = Kunjungan::create([
            'ibu_hamil_id' => $bumil->id,
            'desa_id' => $this->desa->id,
            'user_id' => $this->kader->id,
            'tanggal_kunjungan' => '2026-10-15',
            'jenis_kunjungan' => 'K2',
            'tahap_anc' => 'K2',
            'metode_kunjungan' => 'jemput_bola',
            'status_hadir' => true,
            'hb' => 9.5,
        ]);

        $response = $this->actingAs($this->kader)->get(route('skrining.create', [
            'ibu_hamil_id' => $bumil->id,
        ]));

        $response->assertOk();
        $bumilsData = $response->viewData('ibuHamilsData');
        $this->assertArrayHasKey($bumil->id, $bumilsData);
        $this->assertEquals(9.5, $bumilsData[$bumil->id]['hemoglobin']);
        $this->assertTrue($bumilsData[$bumil->id]['is_anemia']);
        $this->assertEquals('2026-10-15', $bumilsData[$bumil->id]['latest_kunjungan_tanggal']);
        $this->assertEquals('2026-10-15', $response->viewData('syncedTanggal'));
    }

    public function test_bidan_desa_selects_desa_directly_without_provinsi_hierarchy(): void
    {
        $desaTetangga = Desa::create([
            'nama' => 'Desa Makmur Jaya',
            'kecamatan' => 'Kecamatan Makmur', // Same puskesmas/kecamatan
            'kabupaten' => 'Kabupaten Sukabumi',
            'puskesmas' => 'Puskesmas Cikembar',
        ]);

        $bidan = User::create([
            'name' => 'Bidan Siti',
            'email' => 'bidan.siti@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $this->desa->id,
            'is_active' => true,
        ]);

        // 1. Bidan views users.create - should receive territory desas directly
        $createView = $this->actingAs($bidan)->get(route('users.create'));
        $createView->assertOk();
        $desasInView = $createView->viewData('desas');
        $this->assertTrue($desasInView->contains('id', $desaTetangga->id));

        // 2. Bidan creates kader in territory desa directly
        $responseUser = $this->actingAs($bidan)->post(route('users.store'), [
            'name' => 'Kader Desa Tetangga',
            'email' => 'kader.tetangga@test.com',
            'role' => 'kader',
            'desa_id' => $desaTetangga->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $responseUser->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'kader.tetangga@test.com',
            'role' => Role::Kader->value,
            'desa_id' => $desaTetangga->id,
        ]);

        // 3. Bidan creates ibu hamil in territory desa directly
        $responseBumil = $this->actingAs($bidan)->post(route('ibu-hamil.store'), [
            'nama' => 'Ny. Pasien Bidan',
            'nik' => '1402015508950001',
            'desa_id' => $desaTetangga->id,
            'tanggal_lahir' => '10-05-1995',
            'hpht' => '01-02-2026',
            'tinggi_badan' => 155,
            'berat_badan' => 52,
        ]);

        $responseBumil->assertRedirect();
        $this->assertDatabaseHas('ibu_hamils', [
            'nama' => 'Ny. Pasien Bidan',
            'desa_id' => $desaTetangga->id,
        ]);
    }

    public function test_admin_puskesmas_kapus_and_ktu_restricted_to_puskesmas_villages(): void
    {
        $desaPkmLain = Desa::create([
            'nama' => 'Desa Sebelah PKM',
            'kecamatan' => 'Kecamatan Lain',
            'kabupaten' => 'Kabupaten Sukabumi',
            'puskesmas' => 'Puskesmas Nagrak',
        ]);

        $kapus = User::create([
            'name' => 'dr. Kepala Puskesmas',
            'email' => 'kapus@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'desa_id' => $this->desa->id,
            'instansi' => 'Puskesmas Cikembar',
            'jabatan' => 'Kepala Puskesmas',
            'is_active' => true,
        ]);

        $this->assertFalse($kapus->isDinasOrPusat());

        $response = $this->actingAs($kapus)->get(route('users.create'));
        $response->assertOk();
        $desas = $response->viewData('desas');
        $this->assertTrue($desas->contains('id', $this->desa->id));
        $this->assertFalse($desas->contains('id', $desaPkmLain->id));
    }

    public function test_skrining_auto_locks_obesitas_and_hipertensi_from_anc_visit(): void
    {
        for ($i = 1; $i <= 23; $i++) {
            PertanyaanSpr::firstOrCreate(
                ['nomor' => $i],
                [
                    'kelompok' => $i <= 13 ? KelompokFaktorRisiko::APGO : ($i <= 17 ? KelompokFaktorRisiko::AGO : KelompokFaktorRisiko::AGDO),
                    'pertanyaan' => "Pertanyaan SPR $i",
                    'skor' => ($i === 1) ? 2 : 4,
                ]
            );
        }

        $bumil = IbuHamil::create([
            'nama' => 'Ny. Obesitas & Hipertensi',
            'nik' => '3201123456789999',
            'desa_id' => $this->desa->id,
            'user_id' => $this->kader->id,
            'hpht' => '2026-02-01',
            'usia' => 28,
            'gravida' => 2,
            'status_gizi' => 'baik',
            'tinggi_badan' => 150,
            'berat_badan' => 50,
            'tekanan_darah_sistol' => 110,
            'tekanan_darah_diastol' => 70,
        ]);

        // Kunjungan ANC dengan data Biometrik Obesitas (TB 150cm, BB 75kg -> IMT 33.3) dan Hipertensi (145/95 mmHg)
        $kunjungan = Kunjungan::create([
            'ibu_hamil_id' => $bumil->id,
            'desa_id' => $this->desa->id,
            'user_id' => $this->kader->id,
            'tanggal_kunjungan' => '2026-10-18',
            'jenis_kunjungan' => 'K2',
            'tahap_anc' => 'K2',
            'metode_kunjungan' => 'jemput_bola',
            'tinggi_badan' => 150,
            'berat_badan' => 75,
            'tekanan_darah_sistol' => 145,
            'tekanan_darah_diastol' => 95,
            'status_hadir' => true,
        ]);

        $q14 = PertanyaanSpr::where('nomor', 14)->first();
        $q15 = PertanyaanSpr::where('nomor', 15)->first();

        // Submit skrining with answers 0/false, expecting auto-lock override to 1
        $response = $this->actingAs($this->kader)->post(route('skrining.store'), [
            'ibu_hamil_id' => $bumil->id,
            'kunjungan_id' => $kunjungan->id,
            'tanggal_skrining' => '18-10-2026',
            'answers' => [
                $q14->id => 0,
                $q15->id => 0,
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('skrining_details', [
            'pertanyaan_spr_id' => $q14->id,
            'jawaban' => 1,
        ]);

        $this->assertDatabaseHas('skrining_details', [
            'pertanyaan_spr_id' => $q15->id,
            'jawaban' => 1,
        ]);
    }

    public function test_admin_puskesmas_can_assign_kader_using_api_village_directly(): void
    {
        $kapus = User::create([
            'name' => 'Bdn. Bungatang, S.Keb',
            'email' => 'kapus.bungatang@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'desa_id' => $this->desa->id,
            'instansi' => 'UPT Puskesmas Sungai Parit',
            'jabatan' => 'Kepala Puskesmas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($kapus)->post(route('users.store'), [
            'name' => 'Kader Desa Pasir Keranji',
            'email' => 'kader.keranji@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kader',
            'wilayah_desa' => 'PASIR KERANJI',
            'wilayah_kode_desa' => '1402040008',
            'wilayah_kecamatan' => 'Kecamatan Pasir Penyu',
            'wilayah_kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            'wilayah_provinsi' => 'RIAU',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('desas', [
            'nama' => 'Pasir Keranji',
            'puskesmas' => 'UPT Puskesmas Sungai Parit',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'kader.keranji@test.com',
            'role' => Role::Kader->value,
        ]);
    }

    public function test_admin_puskesmas_pekan_heran_resolves_rengat_barat_villages(): void
    {
        $kapusPekanHeran = User::create([
            'name' => 'Maisona Kamar, S.Keb,.M.Kes',
            'email' => 'maisona.test@gmail.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'UPT Puskesmas Pekan Heran',
            'jabatan' => 'Kepala Puskesmas',
            'kecamatan' => 'RENGAT BARAT',
            'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            'provinsi' => 'RIAU',
            'is_active' => true,
        ]);

        $villages = app(WilayahService::class)->getApiVillagesForUser($kapusPekanHeran);

        $this->assertNotEmpty($villages);
        $this->assertEquals('Kecamatan Rengat Barat', $villages[0]['district_name']);
        $this->assertContains('PEKAN HERAN', array_column($villages, 'name'));

        // Navbar no longer contains "Skrining Baru"
        $response = $this->actingAs($kapusPekanHeran)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee('Skrining Baru');
    }

    public function test_dinkes_provinsi_is_admin_utama_and_dinkes_kabupaten_cannot_delete_dinkes_provinsi(): void
    {
        $dinkesProv = User::create([
            'name' => 'Admin Utama Dinkes Provinsi',
            'email' => 'dinkes.prov@riau.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Provinsi Riau',
            'jabatan' => 'Admin Utama Dinkes Provinsi',
            'provinsi' => 'Riau',
            'is_active' => true,
        ]);

        $dinkesKab = User::create([
            'name' => 'Admin Dinkes Inhu',
            'email' => 'dinkes.inhu@inhukab.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'jabatan' => 'Admin Dinkes Kabupaten',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'is_active' => true,
        ]);

        $this->assertTrue($dinkesProv->isDinkesProvinsi());
        $this->assertEquals(100, $dinkesProv->adminLevel());
        $this->assertTrue($dinkesKab->isDinkesKabupaten());
        $this->assertEquals(80, $dinkesKab->adminLevel());

        // Hierarchy rule checks
        $this->assertFalse($dinkesKab->canDeleteUser($dinkesProv));
        $this->assertFalse($dinkesKab->canEditUser($dinkesProv));
        $this->assertTrue($dinkesProv->canDeleteUser($dinkesKab));
        $this->assertTrue($dinkesProv->canEditUser($dinkesKab));

        // Attempt deletion by Dinkes Kabupaten against Dinkes Provinsi -> must return 403 Forbidden
        $responseForbidden = $this->actingAs($dinkesKab)->delete(route('users.destroy', $dinkesProv));
        $responseForbidden->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $dinkesProv->id]);

        // Attempt deletion by Dinkes Provinsi against Dinkes Kabupaten -> succeeds
        $responseSuccess = $this->actingAs($dinkesProv)->delete(route('users.destroy', $dinkesKab));
        $responseSuccess->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $dinkesKab->id]);
    }

    public function test_dinkes_kabupaten_cannot_create_dinkes_provinsi_admin(): void
    {
        $dinkesKab = User::create([
            'name' => 'Admin Dinkes Inhu',
            'email' => 'dinkes.inhu2@inhukab.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'jabatan' => 'Admin Dinkes Kabupaten',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'is_active' => true,
        ]);

        $response = $this->actingAs($dinkesKab)->post(route('users.store'), [
            'name' => 'Akun Provinsi Ilegal',
            'email' => 'prov.ilegal@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
            'instansi' => 'Dinas Kesehatan Provinsi Riau',
            'jabatan' => 'Admin Utama',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'prov.ilegal@test.com']);
    }

    public function test_users_index_view_locks_actions_when_viewed_by_dinkes_kabupaten(): void
    {
        $dinkesKab1 = User::create([
            'name' => 'Admin Dinkes Inhu Utama',
            'email' => 'dinkes.inhu.one@inhukab.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'jabatan' => 'Admin Dinkes Kabupaten',
            'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            'provinsi' => 'RIAU',
            'is_active' => true,
        ]);

        $dinkesKab2 = User::create([
            'name' => 'Admin Dinkes Inhu Rekan',
            'email' => 'dinkes.inhu.two@inhukab.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'jabatan' => 'Admin Dinkes Kabupaten',
            'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            'provinsi' => 'RIAU',
            'is_active' => true,
        ]);

        $response = $this->actingAs($dinkesKab1)->get(route('users.index'));
        $response->assertOk();
        $response->assertSee('Dinkes Kab/Kota');
        $response->assertSee('Terkunci');
    }

    public function test_dinkes_kabupaten_only_sees_users_in_same_kabupaten_and_can_filter_by_kecamatan(): void
    {
        $dinkesInhu = User::create([
            'name' => 'Admin Dinkes Inhu Filter',
            'email' => 'dinkes.inhu.scope@inhukab.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            'provinsi' => 'RIAU',
            'is_active' => true,
        ]);

        $kapusRengatBarat = User::create([
            'name' => 'Kapus Rengat Barat Filter',
            'email' => 'kapus.rb.filter@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'UPT Puskesmas Pekan Heran',
            'kecamatan' => 'RENGAT BARAT',
            'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            'provinsi' => 'RIAU',
            'is_active' => true,
        ]);

        $kapusPasirPenyu = User::create([
            'name' => 'Kapus Pasir Penyu Filter',
            'email' => 'kapus.pp.filter@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'UPT Puskesmas Air Molek',
            'kecamatan' => 'PASIR PENYU',
            'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            'provinsi' => 'RIAU',
            'is_active' => true,
        ]);

        $dinkesKampar = User::create([
            'name' => 'Admin Dinkes Kampar Filter',
            'email' => 'dinkes.kampar.filter@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Kabupaten Kampar',
            'kabupaten' => 'KABUPATEN KAMPAR',
            'provinsi' => 'RIAU',
            'is_active' => true,
        ]);

        // When Dinkes Inhu opens users list -> only Inhu users are visible, Kampar is hidden
        $response = $this->actingAs($dinkesInhu)->get(route('users.index'));
        $response->assertOk();
        $response->assertSee('Kapus Rengat Barat Filter');
        $response->assertSee('Kapus Pasir Penyu Filter');
        $response->assertDontSee('Admin Dinkes Kampar Filter');
        $response->assertSee('Semua Kecamatan');

        // When Dinkes Inhu filters by Kecamatan Rengat Barat
        $responseFiltered = $this->actingAs($dinkesInhu)->get(route('users.index', ['kecamatan' => 'RENGAT BARAT']));
        $responseFiltered->assertOk();
        $responseFiltered->assertSee('Kapus Rengat Barat Filter');
        $responseFiltered->assertDontSee('Kapus Pasir Penyu Filter');
        $responseFiltered->assertDontSee('Admin Dinkes Kampar Filter');
    }

    /**
     * Test Admin Puskesmas (Kapus / Kepala TU) has no Admin role option and cannot create or promote to Admin.
     */
    public function test_admin_puskesmas_cannot_create_admin_and_has_no_admin_role_option(): void
    {
        $kapus = User::create([
            'name' => 'Kepala Puskesmas Pekan Heran',
            'email' => 'kapus.pekanheran@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'UPT Puskesmas Pekan Heran',
            'kecamatan' => 'RENGAT BARAT',
            'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            'provinsi' => 'RIAU',
            'is_active' => true,
        ]);

        $this->assertTrue($kapus->isAdminPuskesmas());

        // 1. In create form: Role::Admin must NOT be in available roles
        $response = $this->actingAs($kapus)->get(route('users.create'));
        $response->assertOk();
        $response->assertViewHas('roles', function ($roles) {
            return ! in_array(Role::Admin, $roles) && in_array(Role::Bidan, $roles) && in_array(Role::Kader, $roles);
        });

        // 2. In store action: Attempting to create an Admin must return 403 Forbidden
        $storeResponse = $this->actingAs($kapus)->post(route('users.store'), [
            'name' => 'Calon Admin Liar',
            'email' => 'admin.liar@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => Role::Admin->value,
            'instansi' => 'UPT Puskesmas Pekan Heran',
        ]);
        $storeResponse->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'admin.liar@test.com']);

        // 3. In update action: Attempting to upgrade a Bidan/Kader to Admin must return 403 Forbidden
        $desa = Desa::create([
            'nama' => 'Desa Binaan Pkm',
            'kecamatan' => 'RENGAT BARAT',
            'kabupaten' => 'KABUPATEN INDRAGIRI HULU',
            'provinsi' => 'RIAU',
            'puskesmas' => 'UPT Puskesmas Pekan Heran',
        ]);

        $bidan = User::create([
            'name' => 'Bidan Pkm',
            'email' => 'bidan.pkm@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $desa->id,
            'instansi' => 'UPT Puskesmas Pekan Heran',
            'is_active' => true,
        ]);

        $updateResponse = $this->actingAs($kapus)->put(route('users.update', $bidan), [
            'name' => 'Bidan Upgrade to Admin',
            'email' => 'bidan.pkm@test.com',
            'role' => Role::Admin->value,
            'desa_id' => $desa->id,
        ]);
        $updateResponse->assertStatus(403);
        $this->assertEquals(Role::Bidan, $bidan->fresh()->role);
    }

    /**
     * Test dashboard displays Kabupaten ranking for Dinkes Provinsi,
     * Kecamatan ranking for Dinkes Kabupaten, and Desa capaian for Admin Puskesmas.
     */
    public function test_dashboard_displays_regional_ranking_according_to_admin_hierarchy(): void
    {
        // 1. Create villages in 2 Kabupatens, across multiple Kecamatans
        $desaInhu1 = Desa::create([
            'nama' => 'Desa Pekan Heran Jaya',
            'kecamatan' => 'Rengat Barat',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'puskesmas' => 'UPT Puskesmas Pekan Heran',
        ]);

        $desaInhu2 = Desa::create([
            'nama' => 'Desa Candirejo Indah',
            'kecamatan' => 'Pasir Penyu',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'puskesmas' => 'UPT Puskesmas Air Molek',
        ]);

        $desaKampar = Desa::create([
            'nama' => 'Desa Bangkinang Makmur',
            'kecamatan' => 'Bangkinang Kota',
            'kabupaten' => 'Kabupaten Kampar',
            'provinsi' => 'Riau',
            'puskesmas' => 'UPT Puskesmas Bangkinang',
        ]);

        // Create Dinkes Provinsi (Level 100)
        $dinkesProv = User::create([
            'name' => 'Admin Dinkes Provinsi Riau',
            'email' => 'dinkes.prov@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Provinsi Riau',
            'jabatan' => 'Administrator Utama Provinsi',
            'provinsi' => 'Riau',
            'is_active' => true,
        ]);

        // Create Dinkes Kabupaten (Level 80)
        $dinkesKab = User::create([
            'name' => 'Admin Dinkes Inhu',
            'email' => 'dinkes.inhu@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'is_active' => true,
        ]);

        // Create Admin Puskesmas (Level 60)
        $kapus = User::create([
            'name' => 'Kapus Pekan Heran',
            'email' => 'kapus.ph@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'UPT Puskesmas Pekan Heran',
            'kecamatan' => 'Rengat Barat',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'provinsi' => 'Riau',
            'is_active' => true,
        ]);

        // A. Dinkes Provinsi Dashboard:
        // Must show Kabupaten performance ranking from highest to lowest, and hide Kinerja Petugas
        $responseProv = $this->actingAs($dinkesProv)->get(route('dashboard'));
        $responseProv->assertOk();
        $responseProv->assertSee('Laporan Kinerja Wilayah per Kabupaten');
        $responseProv->assertSee('Peringkat Kinerja Kabupaten');
        $responseProv->assertSee('Diagram Capaian Wilayah per Kabupaten');
        $responseProv->assertDontSee('Laporan Capaian Kinerja Petugas');
        $responseProv->assertViewHas('kinerjaKabupaten');
        $responseProv->assertViewHas('chartLabels');

        // B. Dinkes Kabupaten Dashboard:
        // Must show Puskesmas performance ranking from highest to lowest, strictly within its own Kabupaten
        // and hide other Kabupatens' Puskesmas as well as Kinerja Petugas
        $responseKab = $this->actingAs($dinkesKab)->get(route('dashboard'));
        $responseKab->assertOk();
        $responseKab->assertSee('Laporan Kinerja Wilayah per Puskesmas');
        $responseKab->assertSee('Peringkat Capaian Puskesmas');
        $responseKab->assertSee('Diagram Capaian Wilayah per Puskesmas');
        $responseKab->assertSee('UPT Puskesmas Pekan Heran');
        $responseKab->assertSee('UPT Puskesmas Air Molek');
        // Crucial: Must NOT leak other Kabupatens (e.g. Kampar)
        $responseKab->assertDontSee('UPT Puskesmas Bangkinang');
        $responseKab->assertDontSee('Desa Bangkinang Makmur');
        $responseKab->assertDontSee('Laporan Capaian Kinerja Petugas');
        $responseKab->assertViewHas('kinerjaPuskesmas', function ($list) {
            $names = collect($list)->pluck('nama');

            return $names->contains('UPT Puskesmas Pekan Heran')
                && $names->contains('UPT Puskesmas Air Molek')
                && ! $names->contains('UPT Puskesmas Bangkinang');
        });
        $responseKab->assertViewHas('chartLabels');

        // C. Admin Puskesmas Dashboard:
        // Must show Village Capaian for that Puskesmas AND include Kinerja Petugas
        $responseKapus = $this->actingAs($kapus)->get(route('dashboard'));
        $responseKapus->assertOk();
        $responseKapus->assertSee('Capaian Wilayah per Desa');
        $responseKapus->assertSee('Diagram Capaian Kunjungan');
        $responseKapus->assertSee('Desa Pekan Heran Jaya');
        $responseKapus->assertSee('Laporan Capaian Kinerja Petugas');
    }

    /**
     * Test validation errors and session alerts are rendered as interactive Pop-Up modal/toast.
     */
    public function test_validation_errors_and_session_alerts_render_in_popup_modal_and_toast(): void
    {
        // 1. Session error displays the Pop-Up Modal
        $responseError = $this->actingAs($this->kader)
            ->withSession(['error' => 'NIK sudah digunakan di sistem'])
            ->get(route('dashboard'));

        $responseError->assertOk();
        $responseError->assertSee('Pemberitahuan Sistem');
        $responseError->assertSee('NIK sudah digunakan di sistem');
        $responseError->assertSee('Mengerti dan Perbaiki');
        $responseError->assertSee('role="dialog"', false);

        // 2. Session success displays the floating Toast Pop-Up
        $responseSuccess = $this->actingAs($this->kader)
            ->withSession(['success' => 'Data ibu hamil berhasil disimpan'])
            ->get(route('dashboard'));

        $responseSuccess->assertOk();
        $responseSuccess->assertSee('Berhasil Disimpan');
        $responseSuccess->assertSee('Data ibu hamil berhasil disimpan');
    }

    /**
     * Test currently logged-in user's own account is not displayed in User Management list.
     */
    public function test_current_user_own_account_is_excluded_from_users_index(): void
    {
        $kapus = User::create([
            'name' => 'dr. Prawiranofa Spesialis',
            'email' => 'prawiranofa.test@gmail.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'UPT Puskesmas Air Molek',
            'jabatan' => 'Kepala Puskesmas',
            'is_active' => true,
        ]);

        $bidan = User::create([
            'name' => 'Sufia Siska Bidan',
            'email' => 'sufia.test@gmail.com',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'instansi' => 'UPT Puskesmas Air Molek',
            'desa_id' => $this->desa->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($kapus)->get(route('users.index'));
        $response->assertOk();
        $response->assertSee('Sufia Siska Bidan');
        $response->assertSee('sufia.test@gmail.com');
        // Own account email & row must NOT appear in the user management table
        $response->assertDontSee('prawiranofa.test@gmail.com');
        $response->assertViewHas('users', function ($users) use ($kapus) {
            return ! $users->contains('id', $kapus->id);
        });
    }

    /**
     * Test website head icon is configured and demo login presets are removed from login page.
     */
    public function test_login_page_has_head_icon_and_demo_presets_removed(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
        // Favicon / website head icon
        $response->assertSee('favicon.png');
        $response->assertSee('favicon.ico');
        // Demo login presets must be removed
        $response->assertDontSee('Login Cepat (Akun Demo)');
        $response->assertDontSee('admin@edetimil.test');
        $response->assertDontSee('Password default semua akun');
    }

    /**
     * Test admin registers user without password and user activates account via WhatsApp link.
     */
    public function test_admin_registers_user_with_whatsapp_activation_scheme_and_activates_account(): void
    {
        $admin = User::create([
            'name' => 'Admin Utama Dinkes Provinsi',
            'email' => 'admin.prov@edetimil.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Provinsi Riau',
            'jabatan' => 'Admin Utama',
            'is_active' => true,
        ]);

        // Admin registers Kader without password (WhatsApp activation scheme)
        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Kader Desa Cenaku Baru',
            'email' => 'kader.cenaku@edetimil.id',
            'phone' => '081234567890',
            'role' => 'kader',
            'desa_id' => $this->desa->id,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('new_user_wa_url');
        $response->assertSessionHas('new_user_activation_url');

        $user = User::where('email', 'kader.cenaku@edetimil.id')->firstOrFail();
        $this->assertFalse((bool) $user->is_active);
        $this->assertNotNull($user->activation_token);
        $this->assertStringContainsString('6281234567890', $user->whatsapp_activation_url);

        // Logout admin so user visits activation link as guest
        $this->post('/logout');

        // Inactive user attempts login -> fails
        $loginAttempt = $this->post(route('login'), [
            'email' => 'kader.cenaku@edetimil.id',
            'password' => 'somePassword123',
        ]);
        $loginAttempt->assertSessionHasErrors('email');
        $this->assertGuest();

        // User visits WhatsApp activation link
        $activationPage = $this->get(route('activation.show', ['token' => $user->activation_token]));
        $activationPage->assertOk();
        $activationPage->assertSee('Kader Desa Cenaku Baru');
        $activationPage->assertSee('Aktivasi Akun Petugas');

        // User sets new password
        $processResponse = $this->post(route('activation.process', ['token' => $user->activation_token]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $processResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertTrue((bool) $user->is_active);
        $this->assertNull($user->activation_token);
    }

    /**
     * Test resend activation regenerates activation token and expired tokens are rejected.
     */
    public function test_resend_activation_and_expired_token_handling(): void
    {
        $admin = User::create([
            'name' => 'Admin UPT Puskesmas',
            'email' => 'admin.upt@edetimil.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Provinsi Riau',
            'jabatan' => 'Admin Utama',
            'is_active' => true,
        ]);

        $kader = User::create([
            'name' => 'Kader Menunggu',
            'email' => 'kader.menunggu@edetimil.id',
            'phone' => '082345678901',
            'password' => bcrypt('tempPass123'),
            'role' => Role::Kader,
            'desa_id' => $this->desa->id,
            'is_active' => false,
            'activation_token' => 'expired-token-123',
            'activation_expires_at' => now()->subDay(),
        ]);

        // Attempt activation with expired token
        $expiredResponse = $this->get(route('activation.show', ['token' => 'expired-token-123']));
        $expiredResponse->assertRedirect(route('login'));
        $expiredResponse->assertSessionHasErrors('email');

        // Admin resends activation link
        $resendResponse = $this->actingAs($admin)->post(route('users.resend-activation', $kader));
        $resendResponse->assertRedirect();
        $resendResponse->assertSessionHas('new_user_wa_url');

        $kader->refresh();
        $this->assertNotEquals('expired-token-123', $kader->activation_token);
        $this->assertTrue($kader->hasValidActivationToken());

        // Logout admin so user opens link as guest
        $this->post('/logout');

        // Now user can open the new link
        $validResponse = $this->get(route('activation.show', ['token' => $kader->activation_token]));
        $validResponse->assertOk();
    }

    /**
     * Test users index renders without error when users have phone number and null activation token.
     */
    public function test_users_index_renders_without_error_when_users_have_phone_and_null_activation_token(): void
    {
        $admin = User::create([
            'name' => 'Admin Utama Dinkes Riau',
            'email' => 'admin.dinkes.riau@test.com',
            'phone' => '08111222333',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Provinsi Riau',
            'jabatan' => 'Admin Utama',
            'is_active' => true,
            'activation_token' => null,
        ]);

        $activeBidan = User::create([
            'name' => 'Bidan Aktif',
            'email' => 'bidan.aktif@test.com',
            'phone' => '08222333444',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $this->desa->id,
            'is_active' => true,
            'activation_token' => null,
        ]);

        $inactiveKader = User::create([
            'name' => 'Kader Belum Aktif',
            'email' => 'kader.inaktif@test.com',
            'phone' => '08333444555',
            'password' => bcrypt('password'),
            'role' => Role::Kader,
            'desa_id' => $this->desa->id,
            'is_active' => false,
            'activation_token' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('users.index'));
        $response->assertOk();
        $response->assertSee('Bidan Aktif');
        $response->assertSee('Kader Belum Aktif');
        $response->assertSee('Menunggu Aktivasi WA');
    }

    /**
     * Test Ibu Hamil registration strictly requires valid 16-digit NIK.
     */
    public function test_ibu_hamil_registration_strictly_requires_valid_16_digit_nik(): void
    {
        $bidan = User::create([
            'name' => 'Bidan Pendaftaran',
            'email' => 'bidan.pendaftaran@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $this->desa->id,
            'is_active' => true,
        ]);

        // Missing NIK -> Validation fails
        $responseNoNik = $this->actingAs($bidan)->post(route('ibu-hamil.store'), [
            'nama' => 'Ny. Tanpa NIK',
            'nik' => '',
            'desa_id' => $this->desa->id,
        ]);
        $responseNoNik->assertSessionHasErrors('nik');

        // Invalid length (less than 16 digits) -> Validation fails
        $responseShortNik = $this->actingAs($bidan)->post(route('ibu-hamil.store'), [
            'nama' => 'Ny. NIK Pendek',
            'nik' => '123456789',
            'desa_id' => $this->desa->id,
        ]);
        $responseShortNik->assertSessionHasErrors('nik');

        // Valid 16 digits NIK -> Succeeds
        $responseValid = $this->actingAs($bidan)->post(route('ibu-hamil.store'), [
            'nama' => 'Ny. NIK Lengkap',
            'nik' => '1402015508980001',
            'desa_id' => $this->desa->id,
            'tinggi_badan' => 155,
            'berat_badan' => 52,
        ]);
        $responseValid->assertRedirect();
        $this->assertDatabaseHas('ibu_hamils', [
            'nama' => 'Ny. NIK Lengkap',
            'nik' => '1402015508980001',
        ]);
    }

    /**
     * Test Kunjungan index search and territory grouping for Admin Puskesmas and Bidan.
     */
    public function test_kunjungan_index_search_and_territory_grouping(): void
    {
        // Setup 2 villages across 2 different Puskesmas
        $desaPkmAirMolek = Desa::create([
            'nama' => 'Desa Candirejo Air Molek',
            'kecamatan' => 'Pasir Penyu',
            'kabupaten' => 'Indragiri Hulu',
            'puskesmas' => 'UPT Puskesmas Air Molek',
        ]);

        $desaSungaiParit = Desa::create([
            'nama' => 'Desa Perkebunan Sei Parit',
            'kecamatan' => 'Sungai Lala',
            'kabupaten' => 'Indragiri Hulu',
            'puskesmas' => 'UPT Puskesmas Sei Parit',
        ]);

        $adminPkm = User::create([
            'name' => 'dr. Prawiranofa Kapus',
            'email' => 'kapus.airmolek@edetimil.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'UPT Puskesmas Air Molek',
            'jabatan' => 'Kepala Puskesmas',
            'is_active' => true,
        ]);

        $bidanAirMolek = User::create([
            'name' => 'Bidan Candirejo',
            'email' => 'bidan.candi@edetimil.id',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $desaPkmAirMolek->id,
            'instansi' => 'UPT Puskesmas Air Molek',
            'is_active' => true,
        ]);

        // Bumil 1: Warga Dalam Wilayah Air Molek
        $bumilDalam = IbuHamil::create([
            'nama' => 'Ny. Fatimah Dalam',
            'nik' => '1402014401950001',
            'nama_suami' => 'Tn. Ahmad',
            'desa_id' => $desaPkmAirMolek->id,
            'user_id' => $bidanAirMolek->id,
        ]);

        // Bumil 2: Warga Luar Wilayah (Sei Parit)
        $bumilLuar = IbuHamil::create([
            'nama' => 'Ny. Zahra Luar',
            'nik' => '1402014401950002',
            'nama_suami' => 'Tn. Ridwan',
            'desa_id' => $desaSungaiParit->id,
            'user_id' => $bidanAirMolek->id,
        ]);

        // Kunjungan 1: Bumil Dalam di desa Air Molek
        $kunjunganDalam = Kunjungan::create([
            'ibu_hamil_id' => $bumilDalam->id,
            'desa_id' => $desaPkmAirMolek->id,
            'user_id' => $bidanAirMolek->id,
            'tanggal_kunjungan' => now(),
            'jenis_kunjungan' => 'K1',
            'tahap_anc' => 'K1',
            'metode_kunjungan' => 'jemput_bola',
            'status_hadir' => true,
            'status_dilayani' => true,
        ]);

        // Kunjungan 2: Bumil Luar dilayani di desa Air Molek (cross-district visit)
        $kunjunganLuar = Kunjungan::create([
            'ibu_hamil_id' => $bumilLuar->id,
            'desa_id' => $desaPkmAirMolek->id,
            'user_id' => $bidanAirMolek->id,
            'tanggal_kunjungan' => now(),
            'jenis_kunjungan' => 'K1',
            'tahap_anc' => 'K1',
            'metode_kunjungan' => 'puskesmas',
            'status_hadir' => true,
            'status_dilayani' => true,
        ]);

        // --- 1. Test Admin Puskesmas territory grouping ---
        // Tab Semua: shows both
        $resAdminAll = $this->actingAs($adminPkm)->get(route('kunjungan.index', ['kelompok' => 'semua']));
        $resAdminAll->assertOk();
        $resAdminAll->assertSee('Ny. Fatimah Dalam');
        $resAdminAll->assertSee('Ny. Zahra Luar');
        $resAdminAll->assertSee('Wilayah Puskesmas Kita');
        $resAdminAll->assertSee('Luar Wilayah Puskesmas');

        // Tab Dalam Wilayah Puskesmas Kita: only shows bumilDalam
        $resAdminDalam = $this->actingAs($adminPkm)->get(route('kunjungan.index', ['kelompok' => 'dalam']));
        $resAdminDalam->assertOk();
        $resAdminDalam->assertSee('Ny. Fatimah Dalam');
        $resAdminDalam->assertDontSee('Ny. Zahra Luar');

        // Tab Luar Wilayah Puskesmas: only shows bumilLuar
        $resAdminLuar = $this->actingAs($adminPkm)->get(route('kunjungan.index', ['kelompok' => 'luar']));
        $resAdminLuar->assertOk();
        $resAdminLuar->assertSee('Ny. Zahra Luar');
        $resAdminLuar->assertDontSee('Ny. Fatimah Dalam');

        // --- 2. Test Search by NIK, Name, and Suami ---
        $resSearchNik = $this->actingAs($adminPkm)->get(route('kunjungan.index', ['search' => '1402014401950002']));
        $resSearchNik->assertOk();
        $resSearchNik->assertSee('Ny. Zahra Luar');
        $resSearchNik->assertDontSee('Ny. Fatimah Dalam');

        $resSearchSuami = $this->actingAs($adminPkm)->get(route('kunjungan.index', ['search' => 'Ahmad']));
        $resSearchSuami->assertOk();
        $resSearchSuami->assertSee('Ny. Fatimah Dalam');
        $resSearchSuami->assertDontSee('Ny. Zahra Luar');

        // --- 3. Test Bidan territory grouping (Warga Desa Kita vs Warga Luar Daerah) ---
        $resBidanDalam = $this->actingAs($bidanAirMolek)->get(route('kunjungan.index', ['kelompok' => 'dalam']));
        $resBidanDalam->assertOk();
        $resBidanDalam->assertSee('Ny. Fatimah Dalam');
        $resBidanDalam->assertSee('Warga Desa Kita');
        $resBidanDalam->assertDontSee('Ny. Zahra Luar');

        $resBidanLuar = $this->actingAs($bidanAirMolek)->get(route('kunjungan.index', ['kelompok' => 'luar']));
        $resBidanLuar->assertOk();
        $resBidanLuar->assertSee('Ny. Zahra Luar');
        $resBidanLuar->assertSee('Warga Luar Daerah');
        $resBidanLuar->assertDontSee('Ny. Fatimah Dalam');
    }

    /**
     * Test Ibu Hamil index search, optgroup dropdown, and territory grouping for Admin Puskesmas and Bidan.
     */
    public function test_ibu_hamil_index_search_and_territory_grouping(): void
    {
        $desaPkmAirMolek = Desa::create([
            'nama' => 'Desa Candirejo Air Molek',
            'kecamatan' => 'Pasir Penyu',
            'kabupaten' => 'Indragiri Hulu',
            'puskesmas' => 'UPT Puskesmas Air Molek',
        ]);

        $desaSungaiParit = Desa::create([
            'nama' => 'Desa Perkebunan Sei Parit',
            'kecamatan' => 'Sungai Lala',
            'kabupaten' => 'Indragiri Hulu',
            'puskesmas' => 'UPT Puskesmas Sei Parit',
        ]);

        $adminPkm = User::create([
            'name' => 'dr. Prawiranofa Kapus',
            'email' => 'kapus.ibuhamil@edetimil.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'UPT Puskesmas Air Molek',
            'jabatan' => 'Kepala Puskesmas',
            'is_active' => true,
        ]);

        $bidanAirMolek = User::create([
            'name' => 'Bidan Candirejo',
            'email' => 'bidan.ibuhamil@edetimil.id',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $desaPkmAirMolek->id,
            'instansi' => 'UPT Puskesmas Air Molek',
            'is_active' => true,
        ]);

        $bumilDalam = IbuHamil::create([
            'nama' => 'Ny. Fatimah Dalam',
            'nik' => '1402014401950003',
            'nama_suami' => 'Tn. Ahmad',
            'desa_id' => $desaPkmAirMolek->id,
            'user_id' => $bidanAirMolek->id,
            'is_active' => true,
        ]);

        $bumilLuar = IbuHamil::create([
            'nama' => 'Ny. Zahra Luar',
            'nik' => '1402014401950004',
            'nama_suami' => 'Tn. Ridwan',
            'desa_id' => $desaSungaiParit->id,
            'user_id' => $bidanAirMolek->id,
            'is_active' => true,
        ]);

        // Kunjungan in Air Molek for bumil luar so she is tracked by this Puskesmas
        Kunjungan::create([
            'ibu_hamil_id' => $bumilLuar->id,
            'desa_id' => $desaPkmAirMolek->id,
            'user_id' => $bidanAirMolek->id,
            'tanggal_kunjungan' => now(),
            'jenis_kunjungan' => 'K1',
            'tahap_anc' => 'K1',
            'metode_kunjungan' => 'puskesmas',
            'status_hadir' => true,
            'status_dilayani' => true,
        ]);

        // --- 1. Admin Puskesmas: All Tab & Optgroup Dropdown ---
        $resAdminAll = $this->actingAs($adminPkm)->get(route('ibu-hamil.index', ['kelompok' => 'semua']));
        $resAdminAll->assertOk();
        $resAdminAll->assertSee('Ny. Fatimah Dalam');
        $resAdminAll->assertSee('Ny. Zahra Luar');
        $resAdminAll->assertSee('Wilayah Puskesmas Kita');
        $resAdminAll->assertSee('Luar Wilayah PKM');
        $resAdminAll->assertSee('Desa Wilayah Kerja Puskesmas');
        $resAdminAll->assertSee('Desa Luar Wilayah / Lintas Faskes');

        // --- 2. Admin Puskesmas: Dalam Tab ---
        $resAdminDalam = $this->actingAs($adminPkm)->get(route('ibu-hamil.index', ['kelompok' => 'dalam']));
        $resAdminDalam->assertOk();
        $resAdminDalam->assertSee('Ny. Fatimah Dalam');
        $resAdminDalam->assertDontSee('Ny. Zahra Luar');

        // --- 3. Admin Puskesmas: Luar Tab ---
        $resAdminLuar = $this->actingAs($adminPkm)->get(route('ibu-hamil.index', ['kelompok' => 'luar']));
        $resAdminLuar->assertOk();
        $resAdminLuar->assertSee('Ny. Zahra Luar');
        $resAdminLuar->assertDontSee('Ny. Fatimah Dalam');

        // --- 4. Admin Puskesmas: Search by NIK ---
        $resSearch = $this->actingAs($adminPkm)->get(route('ibu-hamil.index', ['search' => '1402014401950004']));
        $resSearch->assertOk();
        $resSearch->assertSee('Ny. Zahra Luar');
        $resSearch->assertDontSee('Ny. Fatimah Dalam');

        // --- 5. Bidan: Territory Grouping ---
        $resBidanDalam = $this->actingAs($bidanAirMolek)->get(route('ibu-hamil.index', ['kelompok' => 'dalam']));
        $resBidanDalam->assertOk();
        $resBidanDalam->assertSee('Ny. Fatimah Dalam');
        $resBidanDalam->assertSee('Warga Desa Kita');
        $resBidanDalam->assertDontSee('Ny. Zahra Luar');

        $resBidanLuar = $this->actingAs($bidanAirMolek)->get(route('ibu-hamil.index', ['kelompok' => 'luar']));
        $resBidanLuar->assertOk();
        $resBidanLuar->assertSee('Ny. Zahra Luar');
        $resBidanLuar->assertSee('Lintas');
        $resBidanLuar->assertDontSee('Ny. Fatimah Dalam');
    }
}
