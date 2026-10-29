<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('peserta_kartus')) {
            Schema::create('peserta_kartus', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('no_urut')->nullable();
                $table->string('tanggal', 50)->nullable();
                $table->string('no_kartu')->nullable()->index();
                $table->string('nik')->unique();
                $table->string('nama');
                $table->string('instansi')->nullable()->index();
                $table->string('peny')->nullable();
                $table->text('jawaban_tinggal_copas')->nullable();
                $table->string('pisat')->nullable();
                $table->string('status_aktif')->nullable()->index();
                $table->string('jns_kpst')->nullable();
                $table->string('terdaftar')->nullable();
                $table->string('tunggakan')->nullable();
                $table->json('data_lain')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_kartus');
    }
};
