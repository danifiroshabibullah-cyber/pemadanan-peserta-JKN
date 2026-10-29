<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Sesuaikan tabel pesertas dengan format Excel baru.
        Schema::table('pesertas', function (Blueprint $table) {
            $table->dropIndex(['no_kk']);
        });

        Schema::table('pesertas', function (Blueprint $table) {
            $table->dropColumn([
                'no_kk',
                'hubungan_keluarga',
                'tanggal_lahir',
                'tempat_lahir',
                'kota',
                'provinsi',
                'segmen',
                'jenis_kepesertaan',
                'status_kepesertaan',
            ]);
        });

        Schema::table('pesertas', function (Blueprint $table) {
            $table->unsignedInteger('no_urut')->nullable()->after('id');
            $table->string('sk_kemensos')->nullable()->after('no_urut');
            $table->text('keterangan')->nullable()->after('faskes');
            $table->string('hasil')->nullable()->after('keterangan');
        });

        // 2) Data bulanan (kolom Sep-26 + SEKMEN, Oct-26 + SEKMEN, dst.).
        //    Satu baris = satu peserta pada satu bulan, sehingga bulan baru
        //    cukup menambah baris, tanpa mengubah struktur tabel lagi.
        Schema::create('peserta_periodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('pesertas')->cascadeOnDelete();
            $table->string('periode', 7);          // contoh: 2026-09
            $table->string('status')->nullable();  // isi kolom bulan (Sep-26)
            $table->string('segmen')->nullable();  // isi kolom SEKMEN di sebelahnya
            $table->timestamps();

            $table->unique(['peserta_id', 'periode']);
            $table->index('periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_periodes');

        Schema::table('pesertas', function (Blueprint $table) {
            $table->dropColumn(['no_urut', 'sk_kemensos', 'keterangan', 'hasil']);
        });

        Schema::table('pesertas', function (Blueprint $table) {
            $table->string('no_kk')->nullable()->index();
            $table->string('hubungan_keluarga')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('segmen')->nullable();
            $table->string('jenis_kepesertaan')->nullable();
            $table->string('status_kepesertaan')->nullable();
        });
    }
};