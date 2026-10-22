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
use Illuminate\Support\Facades\Http;
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

        $this->actingAs($this->kader)->get('/laporan/download/agregat')->assertStatus(403);
        $this->actingAs($this->kader)->get('/laporan/download/individu')->assertStatus(403);
        $this->actingAs($this->kader)->get('/laporan/download/kinerja/input')->assertStatus(403);
        $this->actingAs($this->kader)->get('/laporan/download/kinerja/skrining')->assertStatus(403);
        $this->actingAs($this->kader)->get('/laporan/download/kunjungan')->assertStatus(403);
        $this->actingAs($this->kader)->get('/kunjungan/download')->assertStatus(403);
        $this->actingAs($this->kader)->get('/ibu-hamil/download')->assertStatus(403);
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
        ob_start();
        $responseAdmin->sendContent();
        $this->assertNotEmpty(ob_get_clean());

        $responseBidan = $this->actingAs($this->bidan)->get('/laporan/download/agregat');
        $responseBidan->assertStatus(200);

        $responseIndividu = $this->actingAs($this->admin)->get('/laporan/download/individu');
        $responseIndividu->assertStatus(200);
        ob_start();
        $responseIndividu->sendContent();
        $this->assertNotEmpty(ob_get_clean());

        $responseKunjungan = $this->actingAs($this->admin)->get('/kunjungan/download');
        $responseKunjungan->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $responseKunjungan->headers->get('content-type')
        );
        ob_start();
        $responseKunjungan->sendContent();
        $this->assertNotEmpty(ob_get_clean());

        $responseIbuHamil = $this->actingAs($this->admin)->get('/ibu-hamil/download');
        $responseIbuHamil->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $responseIbuHamil->headers->get('content-type')
        );
        ob_start();
        $responseIbuHamil->sendContent();
        $this->assertNotEmpty(ob_get_clean());

        $responseLaporanKunjungan = $this->actingAs($this->admin)->get('/laporan/download/kunjungan');
        $responseLaporanKunjungan->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $responseLaporanKunjungan->headers->get('content-type')
        );
        ob_start();
        $responseLaporanKunjungan->sendContent();
        $this->assertNotEmpty(ob_get_clean());
    }

    public function test_user_can_register_manually_and_verify_via_whatsapp_otp(): void
    {
        $registerData = [
            'name' => 'Siti Kader Baru',
            'email' => 'sitikader@example.com',
            'phone' => '081234567890',
            'role' => Role::Kader->value,
            'desa_id' => $this->desa1->id,
            'latitude' => -6.917464,
            'longitude' => 107.619123,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $registerData);
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'sitikader@example.com',
            'phone' => '6281234567890',
            'role' => Role::Kader->value,
            'is_active' => false,
        ]);

        $user = User::where('email', 'sitikader@example.com')->first();
        $this->assertFalse($user->is_active);

        // Attempting to login while inactive should fail
        $loginAttempt = $this->post('/login', [
            'email' => 'sitikader@example.com',
            'password' => 'password123',
        ]);
        $loginAttempt->assertSessionHasErrors('email');
        $this->assertGuest();

        // Admin verifies the user account
        $verifyResponse = $this->actingAs($this->admin)->post(route('users.toggle-status', $user));
        $verifyResponse->assertRedirect();
        $verifyResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->phone_verified_at);

        // Logout admin first
        $this->post('/logout');

        // Now user can log in
        $loginSuccess = $this->post('/login', [
            'email' => 'sitikader@example.com',
            'password' => 'password123',
        ]);
        $loginSuccess->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertTrue($user->isPhoneVerified());
        $this->assertAuthenticatedAs($user);
    }

    public function test_location_api_can_register_ibu_hamil_coordinates(): void
    {
        $bumil = IbuHamil::create([
            'desa_id' => $this->desa1->id,
            'user_id' => $this->bidan->id,
            'nama' => 'Ny. Ratna GPS',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/locations/ibu-hamil', [
            'ibu_hamil_id' => $bumil->id,
            'latitude' => -6.595038,
            'longitude' => 106.816635,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $bumil->id,
                    'latitude' => -6.595038,
                    'longitude' => 106.816635,
                ],
            ]);

        $bumil->refresh();
        $this->assertEquals(-6.595038, $bumil->latitude);
        $this->assertEquals(106.816635, $bumil->longitude);
    }

    public function test_location_api_can_register_petugas_coordinates(): void
    {
        $response = $this->postJson('/api/locations/petugas', [
            'user_id' => $this->kader->id,
            'latitude' => -6.600000,
            'longitude' => 106.800000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->kader->id,
                    'latitude' => -6.600000,
                    'longitude' => 106.800000,
                ],
            ]);

        $this->kader->refresh();
        $this->assertEquals(-6.600000, $this->kader->latitude);
        $this->assertEquals(106.800000, $this->kader->longitude);
    }

    public function test_location_api_geojson_endpoint_returns_features(): void
    {
        IbuHamil::create([
            'desa_id' => $this->desa1->id,
            'user_id' => $this->bidan->id,
            'nama' => 'Ny. Geo Test',
            'latitude' => -6.595000,
            'longitude' => 106.816000,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/locations/geojson');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'type',
                'features' => [
                    '*' => [
                        'type',
                        'geometry' => ['type', 'coordinates'],
                        'properties',
                    ],
                ],
            ]);
    }

    public function test_ibu_hamil_can_be_stored_with_physical_exam_and_coordinates(): void
    {
        $response = $this->actingAs($this->bidan)->post('/ibu-hamil', [
            'nama' => 'Ny. Rahmawati',
            'nik' => '3201019901010001',
            'desa_id' => $this->desa1->id,
            'tinggi_badan' => 152,
            'berat_badan' => 50,
            'tekanan_darah_sistol' => 110,
            'tekanan_darah_diastol' => 70,
            'lila' => 22.0, // Indikasi KEK (< 23.5)
            'hemoglobin' => 10.2, // Indikasi Anemia (< 11.0)
            'tfu' => 24,
            'djj' => 140,
            'status_imunisasi_tt' => 'TT2',
            'status_gizi' => 'kurang',
            'refleks_patela' => 'positif',
            'edema' => 'tidak_ada',
            'kondisi_umum' => 'baik',
            'latitude' => -6.917464,
            'longitude' => 107.619123,
            'gravida' => 2,
            'para' => 1,
            'abortus' => 0,
            'jumlah_anak_hidup' => 1,
        ]);

        $this->assertDatabaseHas('ibu_hamils', [
            'nama' => 'Ny. Rahmawati',
            'nik' => '3201019901010001',
            'tekanan_darah_sistol' => 110,
            'tekanan_darah_diastol' => 70,
            'lila' => 22.0,
            'hemoglobin' => 10.2,
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ]);

        $bumil = IbuHamil::where('nik', '3201019901010001')->first();
        $this->assertTrue($bumil->is_kek);
        $this->assertTrue($bumil->is_anemia);
        $this->assertEquals('110/70 mmHg', $bumil->tekanan_darah);
        $response->assertRedirect(route('ibu-hamil.show', $bumil));
    }

    public function test_google_reverse_geocode_api_returns_desa_kecamatan_kabupaten(): void
    {
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'results' => [
                    [
                        'formatted_address' => 'Jl. Ciomas No. 10, Sukamaju, Kec. Ciomas, Kabupaten Bogor, Jawa Barat 16610',
                        'address_components' => [
                            ['long_name' => 'Sukamaju', 'types' => ['administrative_area_level_4']],
                            ['long_name' => 'Kecamatan Ciomas', 'types' => ['administrative_area_level_3']],
                            ['long_name' => 'Kabupaten Bogor', 'types' => ['administrative_area_level_2']],
                            ['long_name' => 'Jawa Barat', 'types' => ['administrative_area_level_1']],
                        ],
                    ],
                ],
            ], 200),
        ]);

        config(['services.google.maps_api_key' => 'fake-test-key']);

        $response = $this->getJson('/api/locations/reverse-geocode?latitude=-6.595038&longitude=106.816635');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'google',
                'desa' => 'Sukamaju',
                'kecamatan' => 'Ciomas',
                'kabupaten' => 'Kabupaten Bogor',
                'provinsi' => 'Jawa Barat',
                'matched_desa_id' => $this->desa1->id,
            ]);
    }

    public function test_google_forward_geocode_api_returns_coordinates_and_administrative_hierarchy(): void
    {
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'results' => [
                    [
                        'formatted_address' => 'Desa Sukarasa, Ciomas, Kabupaten Bogor',
                        'geometry' => [
                            'location' => [
                                'lat' => -6.601234,
                                'lng' => 106.812345,
                            ],
                        ],
                        'address_components' => [
                            ['long_name' => 'Sukarasa', 'types' => ['administrative_area_level_4']],
                            ['long_name' => 'Ciomas', 'types' => ['administrative_area_level_3']],
                            ['long_name' => 'Kabupaten Bogor', 'types' => ['administrative_area_level_2']],
                        ],
                    ],
                ],
            ], 200),
        ]);

        config(['services.google.maps_api_key' => 'fake-test-key']);

        $response = $this->getJson('/api/locations/geocode?address=Sukarasa+Ciomas');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'latitude' => -6.601234,
                'longitude' => 106.812345,
                'desa' => 'Sukarasa',
                'kecamatan' => 'Ciomas',
                'kabupaten' => 'Kabupaten Bogor',
                'matched_desa_id' => $this->desa2->id,
            ]);
    }

    public function test_admin_can_create_admin_with_nip_jabatan_and_str(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'dr. H. Ahmad Sudrajat, M.Kes',
            'email' => 'kapus@puskesmas.go.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => Role::Admin->value,
            'desa_id' => null,
            'phone' => '081298765432',
            'nik' => '3201010101800001',
            'nip' => '198001012005011001',
            'jabatan' => 'Kepala Puskesmas',
            'str' => '321110022334455',
            'alamat' => 'Komplek Puskesmas Ciomas',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'kapus@puskesmas.go.id',
            'role' => Role::Admin->value,
            'nip' => '198001012005011001',
            'jabatan' => 'Kepala Puskesmas',
            'str' => '321110022334455',
        ]);
    }

    public function test_admin_can_update_admin_with_nip_jabatan_and_str(): void
    {
        $kapus = User::create([
            'name' => 'Budi Santoso',
            'email' => 'buditu@puskesmas.go.id',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put("/users/{$kapus->id}", [
            'name' => 'Budi Santoso, S.Sos',
            'email' => 'buditu@puskesmas.go.id',
            'role' => Role::Admin->value,
            'desa_id' => null,
            'phone' => '081234567890',
            'nik' => '3201010101750002',
            'nip' => '197505052000031002',
            'jabatan' => 'Ketua Tata Usaha (Kasubag TU)',
            'str' => null, // Optional
            'alamat' => 'Rumah Dinas Puskesmas',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $kapus->id,
            'name' => 'Budi Santoso, S.Sos',
            'nip' => '197505052000031002',
            'jabatan' => 'Ketua Tata Usaha (Kasubag TU)',
            'str' => null,
        ]);
    }

    public function test_user_can_register_with_instansi(): void
    {
        $response = $this->post('/register', [
            'name' => 'Siti Kader Baru',
            'email' => 'sitikader@contoh.id',
            'phone' => '081234567899',
            'role' => 'kader',
            'desa_id' => $this->desa1->id,
            'instansi' => 'Posyandu Melati 05',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'sitikader@contoh.id',
            'instansi' => 'Posyandu Melati 05',
            'role' => Role::Kader->value,
            'is_active' => false,
        ]);
    }

    public function test_admin_puskesmas_is_restricted_to_registered_kecamatan(): void
    {
        // Create an Admin Puskesmas scoped to Kecamatan Ciomas
        $adminPuskesmas = User::create([
            'name' => 'Admin PKM Ciomas',
            'email' => 'pkmciomas@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'kecamatan' => 'Kecamatan Ciomas',
            'desa_id' => $this->desa1->id,
            'is_active' => true,
        ]);

        // Create a desa in another kecamatan (Dramaga)
        $desaLuar = Desa::create([
            'nama' => 'Desa Babakan',
            'kecamatan' => 'Kecamatan Dramaga',
            'kabupaten' => 'Kabupaten Bogor',
            'kode_desa' => '32.01.02.0001',
        ]);

        // Attempt to create kader in outside desa -> should fail with error
        $responseFail = $this->actingAs($adminPuskesmas)->post('/users', [
            'name' => 'Kader Di Luar Wilayah',
            'email' => 'kaderluar@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kader',
            'desa_id' => $desaLuar->id,
            'instansi' => 'Posyandu Luar',
        ]);

        $responseFail->assertSessionHasErrors(['desa_id']);

        // Attempt to create kader within the jurisdiction (Desa Sukarasa in Kecamatan Ciomas) -> succeeds
        $responseSuccess = $this->actingAs($adminPuskesmas)->post('/users', [
            'name' => 'Kader Ciomas Dalam',
            'email' => 'kaderciomas@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kader',
            'desa_id' => $this->desa2->id,
            'instansi' => 'Posyandu Anggrek 02',
        ]);

        $responseSuccess->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'kaderciomas@test.com',
            'instansi' => 'Posyandu Anggrek 02',
            'desa_id' => $this->desa2->id,
        ]);
    }

    public function test_user_can_update_profile_instansi(): void
    {
        $response = $this->actingAs($this->bidan)->put(route('profile.update'), [
            'name' => $this->bidan->name,
            'email' => $this->bidan->email,
            'phone' => '081299998888',
            'instansi' => 'Pustu Sukamaju Sejahtera',
        ]);

        $response->assertSessionHas('success');

        $this->bidan->refresh();
        $this->assertEquals('Pustu Sukamaju Sejahtera', $this->bidan->instansi);
    }

    public function test_wilayah_provinces_endpoint_returns_data(): void
    {
        $response = $this->getJson('/api/wilayah/provinces');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json());
    }

    public function test_top_right_header_displays_registered_instansi(): void
    {
        $this->admin->update(['instansi' => 'Puskesmas Ciomas Raya']);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Puskesmas Ciomas Raya');
    }

    public function test_no_desa_is_created_if_user_creation_validation_fails(): void
    {
        $desaCountBefore = Desa::count();

        // Submit form with invalid password confirmation
        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'Bidan Gagal Validasi',
            'email' => 'bidangagal@test.com',
            'password' => 'password123',
            'password_confirmation' => 'mismatch_password',
            'role' => Role::Bidan->value,
            'wilayah_desa' => 'Desa Tidak Jadi Dibuat',
            'wilayah_kecamatan' => 'Kecamatan Antah Berantah',
            'wilayah_kabupaten' => 'Kabupaten Bogor',
        ]);

        $response->assertSessionHasErrors(['password']);

        // Assert NO new desa was created in the database
        $this->assertEquals($desaCountBefore, Desa::count());
        $this->assertDatabaseMissing('desas', [
            'nama' => 'Desa Tidak Jadi Dibuat',
        ]);
    }

    public function test_create_user_view_renders_password_strength_and_eye_toggle(): void
    {
        $response = $this->actingAs($this->admin)->get('/users/create');

        $response->assertStatus(200);
        $response->assertSee('Kekuatan Kata Sandi');
        $response->assertSee('Minimal 8 karakter');
        $response->assertSee('showPassword');
    }

    public function test_ibu_hamil_auto_calculates_imt_and_status_gizi_on_store_and_update(): void
    {
        // 1. Store with TB 160 cm, BB 45 kg -> IMT = 45 / (1.6*1.6) = 17.6 (Gizi Kurang)
        $response = $this->actingAs($this->bidan)->post('/ibu-hamil', [
            'nama' => 'Ny. Dewi Kurnia',
            'nik' => '3201019988770001',
            'desa_id' => $this->desa1->id,
            'tinggi_badan' => 160,
            'berat_badan' => 45,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
        ]);

        $response->assertRedirect();

        $ibuHamil = IbuHamil::where('nik', '3201019988770001')->firstOrFail();
        $this->assertEquals(17.6, (float) $ibuHamil->imt);
        $this->assertEquals('kurang', $ibuHamil->status_gizi);

        // 2. Update with BB 56 kg -> IMT = 56 / 2.56 = 21.9 (Gizi Baik / Normal)
        $this->actingAs($this->bidan)->put("/ibu-hamil/{$ibuHamil->id}", [
            'nama' => 'Ny. Dewi Kurnia',
            'desa_id' => $this->desa1->id,
            'tinggi_badan' => 160,
            'berat_badan' => 56,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
        ]);

        $ibuHamil->refresh();
        $this->assertEquals(21.9, (float) $ibuHamil->imt);
        $this->assertEquals('baik', $ibuHamil->status_gizi);

        // 3. Update with BB 70 kg -> IMT = 70 / 2.56 = 27.3 (Gizi Lebih / Overweight)
        $this->actingAs($this->bidan)->put("/ibu-hamil/{$ibuHamil->id}", [
            'nama' => 'Ny. Dewi Kurnia',
            'desa_id' => $this->desa1->id,
            'tinggi_badan' => 160,
            'berat_badan' => 70,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
        ]);

        $ibuHamil->refresh();
        $this->assertEquals(27.3, (float) $ibuHamil->imt);
        $this->assertEquals('lebih', $ibuHamil->status_gizi);

        // 4. Update with BB 85 kg -> IMT = 85 / 2.56 = 33.2 (Obesitas)
        $this->actingAs($this->bidan)->put("/ibu-hamil/{$ibuHamil->id}", [
            'nama' => 'Ny. Dewi Kurnia',
            'desa_id' => $this->desa1->id,
            'tinggi_badan' => 160,
            'berat_badan' => 85,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
        ]);

        $ibuHamil->refresh();
        $this->assertEquals(33.2, (float) $ibuHamil->imt);
        $this->assertEquals('obesitas', $ibuHamil->status_gizi);
    }

    public function test_skrining_auto_locks_answers_based_on_biometrics_and_physical_exam(): void
    {
        $q8 = PertanyaanSpr::create([
            'kelompok' => 'I',
            'nomor' => 8,
            'pertanyaan' => 'Terlalu pendek (≤ 145 cm)',
            'skor' => 4,
        ]);

        $q14 = PertanyaanSpr::create([
            'kelompok' => 'II',
            'nomor' => 14,
            'pertanyaan' => 'Penyakit pada ibu hamil (anemia, malaria, TBC, dll)',
            'skor' => 4,
        ]);

        $q15 = PertanyaanSpr::create([
            'kelompok' => 'II',
            'nomor' => 15,
            'pertanyaan' => 'Bengkak pada muka/tungkai dan tekanan darah tinggi',
            'skor' => 4,
        ]);

        // Bumil 1: Usia 15 th (<= 16 th) & TB 142 cm (<= 145 cm)
        $bumil = IbuHamil::create([
            'user_id' => $this->bidan->id,
            'nama' => 'Ny. Siti Remaja',
            'desa_id' => $this->desa1->id,
            'tanggal_lahir' => now()->subYears(15)->format('Y-m-d'),
            'tinggi_badan' => 142,
            'berat_badan' => 40,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
            'is_active' => true,
        ]);

        // Submit skrining with empty answers array
        $response = $this->actingAs($this->bidan)->post('/skrining', [
            'ibu_hamil_id' => $bumil->id,
            'tanggal_skrining' => now()->format('Y-m-d'),
            'answers' => [],
        ]);

        $response->assertRedirect();

        $skrining = Skrining::where('ibu_hamil_id', $bumil->id)->latest('id')->firstOrFail();

        // Base score = 2, Q1 locked (4), Q8 locked (4) -> Total = 10 (KRT)
        $this->assertEquals(10, $skrining->total_skor);
        $this->assertEquals(KategoriRisiko::KRT, $skrining->kategori_risiko);

        // Bumil 2: Anemia (Hb 9.0) & Hipertensi (150/100)
        $bumil2 = IbuHamil::create([
            'user_id' => $this->bidan->id,
            'nama' => 'Ny. Rina Hipertensi',
            'desa_id' => $this->desa1->id,
            'tanggal_lahir' => now()->subYears(25)->format('Y-m-d'),
            'tinggi_badan' => 158,
            'berat_badan' => 55,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
            'hemoglobin' => 9.0,
            'tekanan_darah_sistol' => 150,
            'tekanan_darah_diastol' => 100,
            'edema' => 'tungkai',
            'is_active' => true,
        ]);

        $response2 = $this->actingAs($this->bidan)->post('/skrining', [
            'ibu_hamil_id' => $bumil2->id,
            'tanggal_skrining' => now()->format('Y-m-d'),
            'answers' => [],
        ]);

        $response2->assertRedirect();

        $skrining2 = Skrining::where('ibu_hamil_id', $bumil2->id)->latest('id')->firstOrFail();

        // Base score = 2, Q14 locked (4), Q15 locked (4) -> Total = 10 (KRT)
        $this->assertEquals(10, $skrining2->total_skor);
        $this->assertEquals(KategoriRisiko::KRT, $skrining2->kategori_risiko);
    }

    public function test_kunjungan_requires_vitals_and_updates_ibu_hamil_biometrics(): void
    {
        $bumil = IbuHamil::create([
            'user_id' => $this->bidan->id,
            'nama' => 'Ny. Ratna Kunjungan',
            'desa_id' => $this->desa1->id,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->bidan)->post('/kunjungan', [
            'ibu_hamil_id' => $bumil->id,
            'jenis_kunjungan' => 'K1',
            'tanggal_kunjungan' => now()->format('Y-m-d'),
            'tinggi_badan' => 160,
            'berat_badan' => 52,
            'tekanan_darah_sistol' => 115,
            'tekanan_darah_diastol' => 75,
            'status_hadir' => 1,
            'status_dilayani' => 0,
        ]);

        $response->assertRedirect(route('kunjungan.index'));

        // Assert kunjungan has vitals stored
        $kunjungan = Kunjungan::where('ibu_hamil_id', $bumil->id)->latest('id')->firstOrFail();
        $this->assertEquals(160, (float) $kunjungan->tinggi_badan);
        $this->assertEquals(52, (float) $kunjungan->berat_badan);
        $this->assertEquals(115, $kunjungan->tekanan_darah_sistol);
        $this->assertEquals(75, $kunjungan->tekanan_darah_diastol);

        // Assert mother record was synced with the vitals and calculated IMT
        $bumil->refresh();
        $this->assertEquals(160, (float) $bumil->tinggi_badan);
        $this->assertEquals(52, (float) $bumil->berat_badan);
        $this->assertEquals(115, $bumil->tekanan_darah_sistol);
        $this->assertEquals(75, $bumil->tekanan_darah_diastol);
        $this->assertEquals(20.3, (float) $bumil->imt);
        $this->assertEquals('baik', $bumil->status_gizi);
    }

    public function test_kunjungan_status_dilayani_requires_spr_screening(): void
    {
        $bumil = IbuHamil::create([
            'user_id' => $this->bidan->id,
            'nama' => 'Ny. Maya Dilayani',
            'desa_id' => $this->desa1->id,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
            'is_active' => true,
        ]);

        // 1. Without SPR screening, status_dilayani cannot be true even if requested
        $this->actingAs($this->bidan)->post('/kunjungan', [
            'ibu_hamil_id' => $bumil->id,
            'jenis_kunjungan' => 'K1',
            'tanggal_kunjungan' => now()->format('Y-m-d'),
            'tinggi_badan' => 155,
            'berat_badan' => 50,
            'tekanan_darah_sistol' => 120,
            'tekanan_darah_diastol' => 80,
            'status_hadir' => 1,
            'status_dilayani' => 1, // Attempting to mark dilayani without SPR
        ]);

        $kunjungan = Kunjungan::where('ibu_hamil_id', $bumil->id)->latest('id')->firstOrFail();
        $this->assertTrue((bool) $kunjungan->status_hadir);
        $this->assertFalse((bool) $kunjungan->status_dilayani); // Forced false!

        // 2. Filling SPR screening linking to kunjungan automatically marks it dilayani
        $skriningResponse = $this->actingAs($this->bidan)->post('/skrining', [
            'ibu_hamil_id' => $bumil->id,
            'kunjungan_id' => $kunjungan->id,
            'tanggal_skrining' => now()->format('Y-m-d'),
            'answers' => [],
        ]);

        $skrining = Skrining::where('ibu_hamil_id', $bumil->id)->latest('id')->firstOrFail();
        $skriningResponse->assertRedirect(route('skrining.show', $skrining));

        $kunjungan->refresh();
        $this->assertTrue((bool) $kunjungan->status_hadir);
        $this->assertTrue((bool) $kunjungan->status_dilayani); // Now dilayani is true!
        $this->assertEquals($skrining->id, $kunjungan->skrining_id);
    }

    public function test_jemput_bola_anc_and_kader_dashboard(): void
    {
        // 1. Verify Desa puskesmas grouping
        $this->desa1->update(['puskesmas' => 'Puskesmas Ciomas']);
        $this->assertEquals('Puskesmas Ciomas', $this->desa1->puskesmas);

        $bumil = IbuHamil::create([
            'user_id' => $this->kader->id,
            'nama' => 'Ny. Jemput Bola Test',
            'desa_id' => $this->desa1->id,
            'gravida' => 2,
            'para' => 1,
            'abortus' => 0,
            'jumlah_anak_hidup' => 1,
            'is_active' => true,
        ]);

        // 2. Register Ibu Hamil and verify no unwanted automatic visits are generated
        $bumil2 = IbuHamil::create([
            'user_id' => $this->kader->id,
            'nama' => 'Ny. Murni',
            'desa_id' => $this->desa1->id,
            'gravida' => 1,
            'para' => 0,
            'abortus' => 0,
            'jumlah_anak_hidup' => 0,
            'is_active' => true,
        ]);
        $this->assertEquals(0, $bumil2->kunjungans()->count());

        // 3. Verify Kader can access /kunjungan/create without 403 Forbidden
        $createResponse = $this->actingAs($this->kader)->get('/kunjungan/create');
        $createResponse->assertOk();

        // 4. Kader can store Jemput Bola Kunjungan with clinical & pendampingan fields
        $nextDate = now()->addDays(14)->format('Y-m-d');
        $response = $this->actingAs($this->kader)->post('/kunjungan', [
            'ibu_hamil_id' => $bumil->id,
            'jenis_kunjungan' => 'K2',
            'tahap_anc' => 'K2',
            'metode_kunjungan' => 'jemput_bola',
            'tanggal_kunjungan' => now()->format('Y-m-d'),
            'tinggi_badan' => 158,
            'berat_badan' => 55,
            'tekanan_darah_sistol' => 120,
            'tekanan_darah_diastol' => 80,
            'lila' => 24.5,
            'tfu' => 22.0,
            'djj' => 140,
            'hb' => 12.1,
            'kepatuhan_fe' => 1,
            'bentuk_pendampingan' => 'Pemberian edukasi gizi dan tablet tambah darah di rumah',
            'jadwal_kunjungan_berikutnya' => $nextDate,
            'status_hadir' => 1,
        ]);

        $response->assertRedirect(route('kunjungan.index'));

        $kunjungan = Kunjungan::where('ibu_hamil_id', $bumil->id)->latest('id')->firstOrFail();
        $this->assertEquals('jemput_bola', $kunjungan->metode_kunjungan);
        $this->assertEquals('K2', $kunjungan->tahap_anc);
        $this->assertEquals(24.5, (float) $kunjungan->lila);
        $this->assertEquals(22.0, (float) $kunjungan->tfu);
        $this->assertEquals(140, $kunjungan->djj);
        $this->assertEquals(12.1, (float) $kunjungan->hb);
        $this->assertTrue((bool) $kunjungan->kepatuhan_fe);
        $this->assertEquals('Pemberian edukasi gizi dan tablet tambah darah di rumah', $kunjungan->bentuk_pendampingan);
        $this->assertEquals($nextDate, $kunjungan->jadwal_kunjungan_berikutnya->format('Y-m-d'));

        // Biometrics sync to mother
        $bumil->refresh();
        $this->assertEquals(24.5, (float) $bumil->lila);
        $this->assertEquals(12.1, (float) $bumil->hemoglobin);

        // 5. Kader can access edit form
        $editResponse = $this->actingAs($this->kader)->get(route('kunjungan.edit', $kunjungan));
        $editResponse->assertOk();

        // 6. Verify Kader Dashboard shows Jemput Bola section and the upcoming schedule
        $dashboardResponse = $this->actingAs($this->kader)->get('/dashboard');
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Jadwal Jemput Bola ANC Terdekat');
        $dashboardResponse->assertSee('Ny. Jemput Bola Test');
        $dashboardResponse->assertSee('Jemput Bola');
    }

    /**
     * Test Admin UPT Puskesmas only sees capaian wilayah and kinerja for their own Puskesmas.
     */
    public function test_admin_puskesmas_only_sees_capaian_wilayah_and_kinerja_of_own_puskesmas(): void
    {
        $desaPuskA = Desa::create([
            'nama' => 'Desa Puskesmas A',
            'puskesmas' => 'UPT Puskesmas Air Molek',
            'kecamatan' => 'Kecamatan Pasir Penyu',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
        ]);

        $desaPuskB = Desa::create([
            'nama' => 'Desa Puskesmas B',
            'puskesmas' => 'UPT Puskesmas Sungai Parit',
            'kecamatan' => 'Kecamatan Sungai Lala',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
        ]);

        $adminPuskA = User::create([
            'name' => 'Kepala Puskesmas Air Molek',
            'email' => 'kapus.airmolek@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'UPT Puskesmas Air Molek',
            'is_active' => true,
        ]);

        $bidanPuskA = User::create([
            'name' => 'Bidan Air Molek',
            'email' => 'bidan.airmolek@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $desaPuskA->id,
            'instansi' => 'UPT Puskesmas Air Molek',
            'is_active' => true,
        ]);

        $bidanPuskB = User::create([
            'name' => 'Bidan Sungai Parit',
            'email' => 'bidan.sungaiparit@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Bidan,
            'desa_id' => $desaPuskB->id,
            'instansi' => 'UPT Puskesmas Sungai Parit',
            'is_active' => true,
        ]);

        $response = $this->actingAs($adminPuskA)->get(route('dashboard'));
        $response->assertOk();

        // Must see own Puskesmas village and staff
        $response->assertSee('Desa Puskesmas A');
        $response->assertSee('Bidan Air Molek');

        // Must NOT see other Puskesmas village and staff
        $response->assertDontSee('Desa Puskesmas B');
        $response->assertDontSee('Bidan Sungai Parit');
    }

    /**
     * Test Admin Pusat and Dinas Kesehatan can see all Puskesmas data.
     */
    public function test_admin_pusat_and_dinas_kesehatan_sees_all_puskesmas_capaian_and_kinerja(): void
    {
        $desaPuskA = Desa::create([
            'nama' => 'Desa Alpha',
            'puskesmas' => 'UPT Puskesmas Air Molek',
            'kecamatan' => 'Kecamatan Pasir Penyu',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
        ]);

        $desaPuskB = Desa::create([
            'nama' => 'Desa Beta',
            'puskesmas' => 'UPT Puskesmas Sungai Parit',
            'kecamatan' => 'Kecamatan Sungai Lala',
            'kabupaten' => 'Kabupaten Indragiri Hulu',
        ]);

        $dinasAdmin = User::create([
            'name' => 'Admin Dinas Kesehatan',
            'email' => 'dinas@test.com',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
            'instansi' => 'Dinas Kesehatan Kabupaten Indragiri Hulu',
            'is_active' => true,
        ]);

        $response = $this->actingAs($dinasAdmin)->get(route('dashboard'));
        $response->assertOk();

        // Admin Dinas sees all Puskesmas under their jurisdiction in aggregate
        $response->assertSee('UPT Puskesmas Air Molek');
        $response->assertSee('UPT Puskesmas Sungai Parit');
    }
}
