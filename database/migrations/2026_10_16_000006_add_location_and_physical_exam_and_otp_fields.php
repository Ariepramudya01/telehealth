<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('alamat');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('otp_code', 6)->nullable()->after('remember_token');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->timestamp('phone_verified_at')->nullable()->after('otp_expires_at');
        });

        Schema::table('ibu_hamils', function (Blueprint $table) {
            // Lokasi GPS
            $table->decimal('latitude', 10, 7)->nullable()->after('alamat');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            // Data Pemeriksaan Fisik Ibu Hamil
            $table->integer('tekanan_darah_sistol')->nullable()->after('berat_badan');
            $table->integer('tekanan_darah_diastol')->nullable()->after('tekanan_darah_sistol');
            $table->decimal('lila', 4, 1)->nullable()->after('tekanan_darah_diastol'); // Lingkar Lengan Atas (cm)
            $table->decimal('tfu', 4, 1)->nullable()->after('lila'); // Tinggi Fundus Uteri (cm)
            $table->integer('djj')->nullable()->after('tfu'); // Denyut Jantung Janin (dpm)
            $table->decimal('hemoglobin', 4, 1)->nullable()->after('djj'); // Kadar Hb (g/dL)
            $table->string('status_imunisasi_tt', 20)->nullable()->after('hemoglobin'); // TT1, TT2, TT3, TT4, TT5
            $table->string('refleks_patela', 20)->nullable()->after('status_imunisasi_tt'); // Positif (+) / Negatif (-)
            $table->string('edema', 50)->nullable()->after('refleks_patela'); // Tidak Ada, Tungkai, Wajah
            $table->string('status_gizi', 50)->nullable()->after('edema'); // Normal, KEK, Obesitas
            $table->string('kondisi_umum', 50)->nullable()->after('status_gizi'); // Baik, Sedang, Lemah
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'otp_code', 'otp_expires_at', 'phone_verified_at']);
        });

        Schema::table('ibu_hamils', function (Blueprint $table) {
            $table->dropColumn([
                'latitude', 'longitude',
                'tekanan_darah_sistol', 'tekanan_darah_diastol',
                'lila', 'tfu', 'djj', 'hemoglobin',
                'status_imunisasi_tt', 'refleks_patela',
                'edema', 'status_gizi', 'kondisi_umum',
            ]);
        });
    }
};
