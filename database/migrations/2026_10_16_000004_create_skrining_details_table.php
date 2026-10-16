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
        Schema::create('skrining_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skrining_id')->constrained('skrinings')->cascadeOnDelete();
            $table->foreignId('pertanyaan_spr_id')->constrained('pertanyaan_sprs')->cascadeOnDelete();
            $table->boolean('jawaban')->default(false);
            $table->integer('skor')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skrining_details');
    }
};
