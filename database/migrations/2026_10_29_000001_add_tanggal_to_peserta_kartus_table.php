<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('peserta_kartus') && !Schema::hasColumn('peserta_kartus', 'tanggal')) {
            Schema::table('peserta_kartus', function (Blueprint $table) {
                $table->string('tanggal', 50)->nullable()->after('no_urut');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('peserta_kartus') && Schema::hasColumn('peserta_kartus', 'tanggal')) {
            Schema::table('peserta_kartus', function (Blueprint $table) {
                $table->dropColumn('tanggal');
            });
        }
    }
};

