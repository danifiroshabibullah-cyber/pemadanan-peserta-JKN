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
        Schema::create('pesertas', function (Blueprint $table) {
            $table->id();

            $table->string('nik')->unique();
            $table->string('nama');
            $table->string('no_kk')->index();
            $table->string('hubungan_keluarga')->nullable();

            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();

            $table->text('alamat')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();

            $table->string('faskes')->nullable();
            $table->string('segmen')->nullable();
            $table->string('jenis_kepesertaan')->nullable();
            $table->string('status_kepesertaan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesertas');
    }
};