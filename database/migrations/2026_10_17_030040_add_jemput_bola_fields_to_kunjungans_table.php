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
            $table->string('tahap_anc', 10)->nullable()->after('jenis_kunjungan');
            $table->string('metode_kunjungan', 30)->default('puskesmas')->after('tahap_anc');
            $table->decimal('lila', 5, 2)->nullable()->after('tekanan_darah_diastol');
            $table->decimal('tfu', 5, 2)->nullable()->after('lila');
            $table->integer('djj')->nullable()->after('tfu');
            $table->decimal('hb', 4, 1)->nullable()->after('djj');
            $table->boolean('kepatuhan_fe')->default(true)->after('hb');
            $table->text('bentuk_pendampingan')->nullable()->after('catatan');
            $table->string('foto_pendampingan')->nullable()->after('bentuk_pendampingan');
            $table->date('jadwal_kunjungan_berikutnya')->nullable()->after('foto_pendampingan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn([
                'tahap_anc',
                'metode_kunjungan',
                'lila',
                'tfu',
                'djj',
                'hb',
                'kepatuhan_fe',
                'bentuk_pendampingan',
                'foto_pendampingan',
                'jadwal_kunjungan_berikutnya',
            ]);
        });
    }
};
