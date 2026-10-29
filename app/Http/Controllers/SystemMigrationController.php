<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SystemMigrationController extends Controller
{
    /**
     * Menjalankan migrasi dan seeder database untuk lingkungan hosting / serverless (Vercel + Neon).
     */
    public function migrate(Request $request)
    {
        $appKey = config('app.key');
        $migrateKey = env('MIGRATE_KEY', 'neon-migrate-123');
        $inputKey = (string) $request->input('key', '');

        $authorized = (
            config('app.debug') === true
            || ($inputKey !== '' && ($inputKey === $appKey || $inputKey === $migrateKey))
        );

        if (!$authorized) {
            return response()->view('system.migrate-unauthorized', [
                'hasKey' => $inputKey !== '',
            ], 403);
        }

        $infoKoneksi = [
            'driver' => config('database.default'),
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'host' => config('database.connections.' . config('database.default') . '.host'),
        ];

        $koneksiSukses = false;
        $pesanKoneksi = '';

        try {
            DB::connection()->getPdo();
            $koneksiSukses = true;
            $pesanKoneksi = 'Koneksi ke database berhasil terhubung.';
        } catch (\Throwable $e) {
            $pesanKoneksi = 'Gagal terhubung ke database: ' . $e->getMessage();

            return response()->view('system.migrate-result', [
                'sukses' => false,
                'infoKoneksi' => $infoKoneksi,
                'pesanKoneksi' => $pesanKoneksi,
                'outputMigrate' => '',
                'outputSeed' => '',
            ], 500);
        }

        // Jalankan migrasi
        try {
            Artisan::call('migrate', ['--force' => true]);
            $outputMigrate = Artisan::output();

            Artisan::call('db:seed', ['--force' => true]);
            $outputSeed = Artisan::output();

            return response()->view('system.migrate-result', [
                'sukses' => true,
                'infoKoneksi' => $infoKoneksi,
                'pesanKoneksi' => $pesanKoneksi,
                'outputMigrate' => $outputMigrate,
                'outputSeed' => $outputSeed,
            ]);
        } catch (\Throwable $e) {
            return response()->view('system.migrate-result', [
                'sukses' => false,
                'infoKoneksi' => $infoKoneksi,
                'pesanKoneksi' => $pesanKoneksi,
                'outputMigrate' => $e->getMessage(),
                'outputSeed' => '',
            ], 500);
        }
    }
}

