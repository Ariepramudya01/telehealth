<?php

namespace Tests\Feature;

use App\Enums\KategoriRisiko;
use App\Enums\Role;
use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\PertanyaanSpr;
use App\Models\Skrining;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EDetimilFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Desa $desa1;

    protected Desa $desa2;

    protected User $admin;

    protected User $bidan;

    protected User $kader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->desa1 = Desa::create([
            'nama' => 'Desa Sukamaju',
            'kecamatan' => 'Kecamatan Ciomas',
            'kabupaten' => 'Kabupaten Bogor',
            'kode_desa' => '32.01.01.0001',
        ]);

        $this->desa2 = Desa::create([
            'nama' => 'Desa Sukarasa',
            'kecamatan' => 'Kecamatan Ciomas',
            'kabupaten' => 'Kabupaten Bogor',
            'kode_desa' => '32.01.01.0002',
        ]);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->bidan = User::create([
            'name' => 'Bidan Test',
            'email' => 'bidan@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $this->desa1->id,
            'is_active' => true,
        ]);

        $this->kader = User::create([
            'name' => 'Kader Test',
            'email' => 'kader@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Kader,
            'desa_id' => $this->desa1->id,
            'is_active' => true,
        ]);

        // Seed basic SPR questions
        PertanyaanSpr::create([
            'kelompok' => 'I',
            'nomor' => 1,
            'pertanyaan' => 'Terlalu muda hamil (≤ 16 tahun)',
            'skor' => 4,
        ]);

        PertanyaanSpr::create([
            'kelompok' => 'II',
            'nomor' => 20,
            'pertanyaan' => 'Letak sungsang',
            'skor' => 8,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_successfully(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Ringkasan Cakupan Seluruh Wilayah');
    }

    public function test_bidan_can_access_bidan_dashboard_for_her_desa(): void
    {
        $response = $this->actingAs($this->bidan)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee($this->desa1->nama);
    }

    public function test_kader_can_view_her_village_and_performance_on_dashboard(): void
    {
        $response = $this->actingAs($this->kader)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Capaian Pelayanan Desa');
        $response->assertSee('Kinerja Mandiri Anda (Sebagai Kader)');
    }

    public function test_kader_cannot_download_reports(): void
    {
        $response = $this->actingAs($this->kader)->get('/laporan');
        $response->assertStatus(403);

        $downloadResponse = $this->actingAs($this->kader)->get('/laporan/download/agregat');
        $downloadResponse->assertStatus(403);
    }

    public function test_bidan_cannot_create_another_bidan(): void
    {
        $response = $this->actingAs($this->bidan)->post('/users', [
            'name' => 'Bidan Baru',
            'email' => 'newbidan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'bidan',
            'desa_id' => $this->desa1->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_bidan_can_create_kader_in_her_village(): void
    {
        $response = $this->actingAs($this->bidan)->post('/users', [
            'name' => 'Kader Baru Desa 1',
            'email' => 'newkader@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kader',
            'desa_id' => $this->desa1->id,
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'newkader@test.com',
            'role' => Role::Kader->value,
            'desa_id' => $this->desa1->id,
        ]);
    }

    public function test_kader_can_input_screening_with_automatic_poedji_rochjati_score(): void
    {
        $bumil = IbuHamil::create([
            'user_id' => $this->kader->id,
            'desa_id' => $this->desa1->id,
            'nama' => 'Ny. Aminah',
            'nik' => '3201019999990001',
            'hpht' => '2026-05-01',
            'taksiran_persalinan' => '2027-02-05',
        ]);

        $p1 = PertanyaanSpr::where('nomor', 1)->first(); // Skor 4
        $p20 = PertanyaanSpr::where('nomor', 20)->first(); // Skor 8

        // Answering both questions: Base(2) + 4 + 8 = 14 => KRST (Skor >= 12)
        $response = $this->actingAs($this->kader)->post('/skrining', [
            'ibu_hamil_id' => $bumil->id,
            'tanggal_skrining' => '2026-10-16',
            'usia_kehamilan_minggu' => 24,
            'trimester' => 2,
            'answers' => [
                $p1->id => '1',
                $p20->id => '1',
            ],
            'catatan' => 'Deteksi dini risiko tinggi',
        ]);

        $this->assertDatabaseHas('skrinings', [
            'ibu_hamil_id' => $bumil->id,
            'user_id' => $this->kader->id,
            'total_skor' => 14,
            'kategori_risiko' => KategoriRisiko::KRST->value,
        ]);

        $skrining = Skrining::where('ibu_hamil_id', $bumil->id)->first();
        $response->assertRedirect(route('skrining.show', $skrining));
    }

    public function test_admin_and_bidan_can_download_excel_reports(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get('/laporan/download/agregat');
        $responseAdmin->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $responseAdmin->headers->get('content-type')
        );

        $responseBidan = $this->actingAs($this->bidan)->get('/laporan/download/agregat');
        $responseBidan->assertStatus(200);
    }
}
