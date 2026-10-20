<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\PertanyaanSpr;
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

        // 2. Admin Pusat / Dinas Kesehatan sees all
        $responsePusat = $this->actingAs($this->adminPusat)->get('/dashboard');
        $responsePusat->assertStatus(200);
        $responsePusat->assertSee('Desa Lembah Dusun Gading');
        $responsePusat->assertSee('Desa Perkebunan Sungai Parit');
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
        $response->assertSee('Pelayanan Ibu Hamil Lintas Daerah / Titik Kritis');
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
        ]);

        $response->assertSessionHas('success');

        $kunjungan->refresh();
        $this->assertTrue($kunjungan->status_hadir);
        $this->assertEquals(55, $kunjungan->berat_badan);
    }
}
