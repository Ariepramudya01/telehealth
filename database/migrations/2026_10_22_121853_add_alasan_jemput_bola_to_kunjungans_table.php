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
            $table->string('alasan_jemput_bola', 100)->nullable()->after('metode_kunjungan');
            $table->boolean('pendampingan_suami')->default(false)->after('bentuk_pendampingan');
            $table->boolean('p4k_siap')->default(false)->after('pendampingan_suami');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn(['alasan_jemput_bola', 'pendampingan_suami', 'p4k_siap']);
        });
    }
};
