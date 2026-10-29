<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pemeriksaan_kartus')) {
            Schema::create('pemeriksaan_kartus', function (Blueprint $table) {
                $table->id();

                $table->foreignId('peserta_kartu_id')
                    ->constrained('peserta_kartus')
                    ->cascadeOnDelete();

                $table->string('status')->default('Belum diperiksa');
                $table->text('catatan')->nullable();
                $table->string('diperiksa_oleh')->nullable();
                $table->timestamp('diperiksa_pada')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_kartus');
    }
};
