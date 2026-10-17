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
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->decimal('berat_badan', 5, 2)->nullable()->after('jenis_kunjungan');
            $table->decimal('tinggi_badan', 5, 2)->nullable()->after('berat_badan');
            $table->integer('tekanan_darah_sistol')->nullable()->after('tinggi_badan');
            $table->integer('tekanan_darah_diastol')->nullable()->after('tekanan_darah_sistol');
            $table->foreignId('skrining_id')->nullable()->after('tekanan_darah_diastol')->constrained('skrinings')->nullOnDelete();
        });

        Schema::table('skrinings', function (Blueprint $table) {
            $table->foreignId('kunjungan_id')->nullable()->after('user_id')->constrained('kunjungans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skrinings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kunjungan_id');
        });

        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('skrining_id');
            $table->dropColumn([
                'berat_badan',
                'tinggi_badan',
                'tekanan_darah_sistol',
                'tekanan_darah_diastol',
            ]);
        });
    }
};
