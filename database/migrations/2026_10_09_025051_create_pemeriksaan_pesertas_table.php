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
        Schema::create('pemeriksaan_pesertas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('peserta_id')
                ->constrained('pesertas')
                ->cascadeOnDelete();

            $table->string('status')->default('Belum diperiksa');
            $table->text('catatan')->nullable();
            $table->string('diperiksa_oleh')->nullable();
            $table->timestamp('diperiksa_pada')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_pesertas');
    }
};