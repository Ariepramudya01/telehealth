<?php

namespace Tests\Feature;

use App\Enums\KategoriRisiko;
use App\Enums\Role;
use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\PertanyaanSpr;
use App\Models\Skrining;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LintasDaerahDanMultiPuskesmasTest extends TestCase
{
    use RefreshDatabase;

    protected Desa $desaAirMolek1;

    protected Desa $desaAirMolek2;

    protected Desa $desaSungaiParit;

    protected User $adminPusat;

    protected User $adminAirMolek;

    protected User $adminSungaiParit;

    protected User $bidanAirMolek;

    protected User $bidanSungaiParit;

    protected User $kaderAirMolek;

    protected IbuHamil $bumilSungaiParit;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Desas across 2 different UPT Puskesmas
        $this->desaAirMolek1 = Desa::create([
            'nama' => 'Desa Lembah Dusun Gading',
            'kecamatan' => 'Pasir Penyu',
            'kabupaten' => 'Indragiri Hulu',
            'kode_desa' => '14.02.01.2001',
            'puskesmas' => 'UPT Puskesmas Air Molek',
        ]);

        $this->desaAirMolek2 = Desa::create([
            'nama' => 'Desa Serumpun Jaya',
            'kecamatan' => 'Pasir Penyu',
            'kabupaten' => 'Indragiri Hulu',
            'kode_desa' => '14.02.01.2002',
            'puskesmas' => 'UPT Puskesmas Air Molek',
        ]);

        $this->desaSungaiParit = Desa::create([
            'nama' => 'Desa Perkebunan Sungai Parit',
            'kecamatan' => 'Sungai Lala',
            'kabupaten' => 'Indragiri Hulu',
            'kode_desa' => '14.02.08.2001',
            'puskesmas' => 'UPT Puskesmas Sungai Parit',
        ]);

        // 2. Setup Users
        // Admin Pusat / Dinas Kesehatan (Non UPT Puskesmas)
        $this->adminPusat = User::create([
            'name' => 'Admin Dinas Kesehatan Inhu',
            'email' => 'dinkes@inhu.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'kabupaten' => 'Kabupaten Indragiri Hulu',
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'is_active' => true,
        ]);

        // Admin UPT Puskesmas Air Molek
        $this->adminAirMolek = User::create([
            'name' => 'Kepala UPT Puskesmas Air Molek',
            'email' => 'kapus.airmolek@inhu.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'desa_id' => $this->desaAirMolek1->id,
            'instansi' => 'UPT Puskesmas Air Molek',
            'is_active' => true,
        ]);

        // Admin UPT Puskesmas Sungai Parit
        $this->adminSungaiParit = User::create([
            'name' => 'Kepala UPT Puskesmas Sungai Parit',
            'email' => 'kapus.sungaiparit@inhu.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'desa_id' => $this->desaSungaiParit->id,
            'instansi' => 'UPT Puskesmas Sungai Parit',
            'is_active' => true,
        ]);

        // Bidan and Kader
        $this->bidanAirMolek = User::create([
            'name' => 'Bidan Siti Air Molek',
            'email' => 'bidan.airmolek@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $this->desaAirMolek1->id,
            'instansi' => 'UPT Puskesmas Air Molek',
            'is_active' => true,
        ]);

        $this->kaderAirMolek = User::create([
            'name' => 'Kader Ani Air Molek',
            'email' => 'kader.airmolek@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Kader,
            'desa_id' => $this->desaAirMolek1->id,
            'is_active' => true,
        ]);

        $this->bidanSungaiParit = User::create([
            'name' => 'Bidan Dewi Sungai Parit',
            'email' => 'bidan.sungaiparit@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $this->desaSungaiParit->id,
            'instansi' => 'UPT Puskesmas Sungai Parit',
            'is_active' => true,
        ]);

        // 3. Setup Ibu Hamil in Sungai Parit
        $this->bumilSungaiParit = IbuHamil::create([
            'nama' => 'Ny. Rahmawati',
            'nik' => '1402085001950001',
            'desa_id' => $this->desaSungaiParit->id,
            'user_id' => $this->bidanSungaiParit->id,
            'tanggal_lahir' => '1995-05-10',
            'tinggi_badan' => 155,
            'berat_badan' => 52,
            'tekanan_darah_sistol' => 115,
            'tekanan_darah_diastol' => 75,
            'is_active' => true,
        ]);

        // Seed basic SPR question
        PertanyaanSpr::create([
            'kelompok' => 'I',
            'nomor' => 1,
            'pertanyaan' => 'Terlalu muda hamil (≤ 16 tahun)',
            'skor' => 4,
        ]);
    }

    /**
     * Test multi-Puskesmas isolation on Admin Dashboard.
     */
    public function test_upt_puskesmas_admin_dashboard_isolation(): void
    {
        // 1. Admin Puskesmas Air Molek
        $response = $this->actingAs($this->adminAirMolek)->get('/dashboard');
        $response->assertStatus(200);

        // Should see Air Molek villages
        $response->assertSee('Desa Lembah Dusun Gading');
        $response->assertSee('Desa Serumpun Jaya');

        // Should NOT see Sungai Parit village in Capaian Wilayah per Desa
        $response->assertDontSee('Desa Perkebunan Sungai Parit');

        // 2. Admin Pusat / Dinas Kesehatan sees macro regional overview (e.g. Kabupaten Indragiri Hulu)
        $responsePusat = $this->actingAs($this->adminPusat)->get('/dashboard');
        $responsePusat->assertStatus(200);
        $responsePusat->assertSee('Indragiri Hulu');
    }

    /**
     * Test that health worker from different Puskesmas can view maternal records without 403.
     */
    public function test_bidan_from_different_puskesmas_can_view_cross_district_mother_details(): void
    {
        // Bidan Air Molek visits profile of mother from Sungai Parit
        $response = $this->actingAs($this->bidanAirMolek)->get(route('ibu-hamil.show', $this->bumilSungaiParit));

        $response->assertStatus(200);
        $response->assertSee('Ny. Rahmawati');
        $response->assertSee('Pelayanan Ibu Hamil Antar-Faskes / Titik Kritis');
    }

    /**
     * Test searching cross-district pregnant mother in IbuHamilController index.
     */
    public function test_bidan_can_search_cross_district_mother_by_nik_or_name(): void
    {
        // Default list: Bidan Air Molek only sees her own village
        $responseDefault = $this->actingAs($this->bidanAirMolek)->get(route('ibu-hamil.index'));
        $responseDefault->assertStatus(200);
        $responseDefault->assertDontSee('Ny. Rahmawati');

        // When searching by name or NIK at emergency/critical point:
        $responseSearch = $this->actingAs($this->bidanAirMolek)->get(route('ibu-hamil.index', ['search' => 'Rahmawati']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Ny. Rahmawati');
        $responseSearch->assertSee('Lintas');

        // When filtering by scope=lintas:
        $responseScope = $this->actingAs($this->bidanAirMolek)->get(route('ibu-hamil.index', ['scope' => 'lintas']));
        $responseScope->assertStatus(200);
        $responseScope->assertSee('Ny. Rahmawati');
    }

    /**
     * Test recording cross-district ANC examination by Bidan / Kader.
     */
    public function test_bidan_can_record_cross_district_anc_kunjungan(): void
    {
        $response = $this->actingAs($this->bidanAirMolek)->post(route('kunjungan.store'), [
            'ibu_hamil_id' => $this->bumilSungaiParit->id,
            'tanggal_kunjungan' => now()->format('Y-m-d'),
            'jenis_kunjungan' => 'K1',
            'tahap_anc' => 'K1',
            'metode_kunjungan' => 'jemput_bola',
            'tinggi_badan' => 155,
            'berat_badan' => 54,
            'tekanan_darah_sistol' => 120,
            'tekanan_darah_diastol' => 80,
            'lila' => 24.5,
            'hb' => 11.8,
            'kepatuhan_fe' => true,
            'status_hadir' => true,
            'catatan' => 'Pemeriksaan darurat titik kritis lintas faskes di Air Molek',
        ]);

        $response->assertRedirect(route('kunjungan.index'));
        $response->assertSessionHas('success');

        // Verify Kunjungan in DB
        $this->assertDatabaseHas('kunjungans', [
            'ibu_hamil_id' => $this->bumilSungaiParit->id,
            'user_id' => $this->bidanAirMolek->id,
            'desa_id' => $this->desaAirMolek1->id, // Service location: Air Molek
            'jenis_kunjungan' => 'K1',
            'metode_kunjungan' => 'jemput_bola',
            'berat_badan' => 54,
            'status_hadir' => true,
        ]);

        // Verify Bumil vitals updated
        $this->bumilSungaiParit->refresh();
        $this->assertEquals(54, $this->bumilSungaiParit->berat_badan);
        $this->assertEquals(24.5, $this->bumilSungaiParit->lila);
    }

    /**
     * Test performing SPR screening for cross-district mother.
     */
    public function test_bidan_can_perform_cross_district_spr_screening(): void
    {
        // First create a Kunjungan
        $kunjungan = Kunjungan::create([
            'ibu_hamil_id' => $this->bumilSungaiParit->id,
            'user_id' => $this->bidanAirMolek->id,
            'desa_id' => $this->desaAirMolek1->id,
            'tanggal_kunjungan' => now()->format('Y-m-d'),
            'jenis_kunjungan' => 'K1',
            'metode_kunjungan' => 'jemput_bola',
            'tinggi_badan' => 155,
            'berat_badan' => 54,
            'tekanan_darah_sistol' => 120,
            'tekanan_darah_diastol' => 80,
            'status_hadir' => true,
            'status_dilayani' => false,
        ]);

        // Perform screening
        $response = $this->actingAs($this->bidanAirMolek)->post(route('skrining.store'), [
            'ibu_hamil_id' => $this->bumilSungaiParit->id,
            'kunjungan_id' => $kunjungan->id,
            'tanggal_skrining' => now()->format('Y-m-d'),
            'usia_kehamilan_minggu' => 12,
            'trimester' => 1,
            'catatan' => 'Skrining di titik kritis',
        ]);

        $this->assertDatabaseHas('skrinings', [
            'ibu_hamil_id' => $this->bumilSungaiParit->id,
            'user_id' => $this->bidanAirMolek->id,
            'kunjungan_id' => $kunjungan->id,
        ]);

        // Kunjungan status_dilayani should now be true
        $kunjungan->refresh();
        $this->assertTrue($kunjungan->status_dilayani);
    }

    /**
     * Test attendance & vitals confirmation on cross-district kunjungan.
     */
    public function test_bidan_can_confirm_attendance_on_cross_district_visit(): void
    {
        $kunjungan = Kunjungan::create([
            'ibu_hamil_id' => $this->bumilSungaiParit->id,
            'user_id' => $this->bidanAirMolek->id,
            'desa_id' => $this->desaAirMolek1->id,
            'tanggal_kunjungan' => now()->format('Y-m-d'),
            'jenis_kunjungan' => 'K1',
            'metode_kunjungan' => 'jemput_bola',
            'status_hadir' => false,
            'status_dilayani' => false,
        ]);

        $response = $this->actingAs($this->bidanAirMolek)->post(route('kunjungan.konfirmasi', $kunjungan), [
            'status_hadir' => 1,
            'tinggi_badan' => 155,
            'berat_badan' => 55,
            'tekanan_darah_sistol' => 120,
            'tekanan_darah_diastol' => 80,
            'lila' => 24.0,
            'hb' => 11.5,
        ]);

        $response->assertSessionHas('success');

        $kunjungan->refresh();
        $this->assertTrue($kunjungan->status_hadir);
        $this->assertEquals(55, $kunjungan->berat_badan);
    }

    /**
     * Test Dinkes Kabupaten separates data between within regency and outside regency.
     */
    public function test_dinkes_kabupaten_can_separate_dalam_and_luar_wilayah_kabupaten(): void
    {
        $desaKampar = Desa::create([
            'nama' => 'Desa Pantai Cermin',
            'kecamatan' => 'Tapung',
            'kabupaten' => 'Kabupaten Kampar',
            'kode_desa' => '14.01.01.2001',
            'puskesmas' => 'UPT Puskesmas Tapung',
        ]);

        $dinkesKampar = User::create([
            'name' => 'Admin Dinkes Kampar',
            'email' => 'dinkes.kampar@riau.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'kabupaten' => 'Kabupaten Kampar',
            'instansi' => 'Dinas Kesehatan Kabupaten Kampar',
            'is_active' => true,
        ]);

        $bumilKampar = IbuHamil::create([
            'user_id' => $dinkesKampar->id,
            'desa_id' => $desaKampar->id,
            'nama' => 'Ny. Fatimah Kampar',
            'nik' => '1401011234567890',
            'nama_suami' => 'Tn. Ahmad',
            'usia' => 26,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'hpht' => now()->subWeeks(10),
            'taksiran_persalinan' => now()->addWeeks(30),
            'usia_kehamilan_minggu' => 10,
        ]);

        // 1. View ibu-hamil index as Dinkes Kampar: displays aggregate cluster table, hides operational action buttons
        $response = $this->actingAs($dinkesKampar)->get(route('ibu-hamil.index'));
        $response->assertOk();
        $response->assertSee('Klaster Agregat');
        $response->assertSee('Tingkat UPT Puskesmas');
        $response->assertSee('Tabel Agregat Berdasarkan Klaster Puskesmas');
        $response->assertDontSee('+ Daftarkan Ibu Hamil Baru');

        // Dinkes is blocked from create()
        $this->actingAs($dinkesKampar)->get(route('ibu-hamil.create'))
            ->assertRedirect(route('ibu-hamil.index'));

        // 2. Test kunjungan.index aggregate view for Dinkes
        $kunjunganKampar = Kunjungan::create([
            'ibu_hamil_id' => $bumilKampar->id,
            'user_id' => $dinkesKampar->id,
            'desa_id' => $desaKampar->id,
            'tanggal_kunjungan' => now()->format('Y-m-d'),
            'jenis_kunjungan' => 'K1',
            'metode_kunjungan' => 'puskesmas',
            'status_hadir' => true,
            'status_dilayani' => false,
        ]);

        $responseKunjungan = $this->actingAs($dinkesKampar)->get(route('kunjungan.index'));
        $responseKunjungan->assertOk();
        $responseKunjungan->assertSee('Rekapitulasi Kunjungan');
        $responseKunjungan->assertSee('Tingkat Kabupaten');
        $responseKunjungan->assertDontSee('Catat Kunjungan / Jemput Bola');

        // Dinkes is blocked from kunjungan.create()
        $this->actingAs($dinkesKampar)->get(route('kunjungan.create'))
            ->assertRedirect(route('kunjungan.index'));
    }

    public function test_dashboard_renders_gis_map_and_clusters_for_dinkes_and_operational_cards_for_puskesmas(): void
    {
        $dinkes = User::create([
            'name' => 'Admin Dinkes Provinsi',
            'email' => 'dinkes.riau@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'provinsi' => 'Provinsi Riau',
            'instansi' => 'Dinas Kesehatan Provinsi Riau',
            'is_active' => true,
        ]);

        $responseDinkes = $this->actingAs($dinkes)->get(route('dashboard'));
        $responseDinkes->assertOk();
        $responseDinkes->assertSee('gis-risk-map');
        $responseDinkes->assertSee('Peta Sebaran Total Ibu Hamil per Wilayah');
        $responseDinkes->assertSee('Klaster Data Ibu Hamil');
        $responseDinkes->assertSee('Klaster Skrining Skor SPR');
        $responseDinkes->assertSee('Klaster Kunjungan ANC');
        $responseDinkes->assertDontSee('Catat Jemput Bola');

        $responsePuskesmas = $this->actingAs($this->adminAirMolek)->get(route('dashboard'));
        $responsePuskesmas->assertOk();
        $responsePuskesmas->assertSee('Jadwal Jemput Bola ANC Terdekat');
        $responsePuskesmas->assertSee('Catat Jemput Bola');
        $responsePuskesmas->assertSee('Jadwal Pemeriksaan ANC');
        $responsePuskesmas->assertDontSee('Peta Sebaran Total Ibu Hamil per Wilayah');
    }

    /**
     * Test Layanan Jemput Bola dengan Rekomendasi Prioritas & Input Alasan Kunjungan.
     */
    public function test_layanan_jemput_bola_rekomendasi_prioritas_and_alasan_kunjungan(): void
    {
        // 1. Ibu hamil KRST dan LiLA < 23.5 (KEK)
        $bumilResti = IbuHamil::create([
            'desa_id' => $this->desaAirMolek1->id,
            'user_id' => $this->bidanAirMolek->id,
            'nama' => 'Ibu Siti Resti',
            'nik' => '1402010101950009',
            'no_hp' => '081234567899',
            'tinggi_badan' => 155,
            'berat_badan' => 42,
            'lila' => 21.0,
            'tekanan_darah_sistol' => 145,
            'tekanan_darah_diastol' => 95,
            'is_active' => true,
        ]);

        Skrining::create([
            'ibu_hamil_id' => $bumilResti->id,
            'user_id' => $this->bidanAirMolek->id,
            'tanggal_skrining' => now()->toDateString(),
            'total_skor' => 14,
            'kategori_risiko' => KategoriRisiko::KRST,
        ]);

        // Dashboard Bidan menampilkan rekomendasi jemput bola prioritas
        $responseBidan = $this->actingAs($this->bidanAirMolek)->get(route('dashboard'));
        $responseBidan->assertOk();
        $responseBidan->assertSee('Rekomendasi Prioritas Outreach');
        $responseBidan->assertSee('Ibu Siti Resti');
        $responseBidan->assertSee('KRST');

        // Bidan mencatat kunjungan jemput bola dengan alasan dan kesiapan P4K
        $postData = [
            'ibu_hamil_id' => $bumilResti->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'jenis_kunjungan' => 'K1',
            'tahap_anc' => 'K1',
            'metode_kunjungan' => 'jemput_bola',
            'alasan_jemput_bola' => 'risiko_tinggi',
            'tinggi_badan' => 155,
            'berat_badan' => 43,
            'tekanan_darah_sistol' => 140,
            'tekanan_darah_diastol' => 90,
            'lila' => 21.5,
            'tfu' => 12,
            'djj' => 140,
            'hb' => 10.5,
            'kepatuhan_fe' => 1,
            'pendampingan_suami' => 1,
            'p4k_siap' => 1,
            'bentuk_pendampingan' => 'Kunjungan rumah terpadu, pemantauan tensi & gizi',
            'status_hadir' => 1,
        ];

        $postResp = $this->actingAs($this->bidanAirMolek)->post(route('kunjungan.store'), $postData);
        $postResp->assertRedirect();

        $this->assertDatabaseHas('kunjungans', [
            'ibu_hamil_id' => $bumilResti->id,
            'metode_kunjungan' => 'jemput_bola',
            'alasan_jemput_bola' => 'risiko_tinggi',
            'pendampingan_suami' => 1,
            'p4k_siap' => 1,
        ]);
    }
}
