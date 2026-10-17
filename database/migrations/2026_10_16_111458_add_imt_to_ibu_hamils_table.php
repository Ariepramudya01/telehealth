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
        Schema::table('ibu_hamils', function (Blueprint $table) {
            $table->decimal('imt', 4, 1)->nullable()->after('berat_badan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ibu_hamils', function (Blueprint $table) {
            $table->dropColumn('imt');
        });
    }
};
