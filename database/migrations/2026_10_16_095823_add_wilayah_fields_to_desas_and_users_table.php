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
        Schema::table('desas', function (Blueprint $table) {
            $table->string('provinsi', 100)->nullable()->after('kabupaten');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('provinsi', 100)->nullable()->after('alamat');
            $table->string('kabupaten', 100)->nullable()->after('provinsi');
            $table->string('kecamatan', 100)->nullable()->after('kabupaten');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provinsi', 'kabupaten', 'kecamatan']);
        });

        Schema::table('desas', function (Blueprint $table) {
            $table->dropColumn('provinsi');
        });
    }
};
