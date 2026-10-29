<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportKartuController;
use App\Http\Controllers\ImportPesertaController;
use App\Http\Controllers\KartuController;
use App\Http\Controllers\KartuDetailController;
use App\Http\Controllers\KartuPemeriksaanController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\PemeriksaanPesertaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiwayatPemeriksaanController;

/*
|--------------------------------------------------------------------------
| Login dan Registrasi
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login/admin', [AuthController::class, 'adminLogin'])
        ->name('login.admin');

    Route::post('/login/pengguna', [AuthController::class, 'userAdminLogin'])
        ->name('login.user');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.process');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Halaman Setelah Login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Profil
    |--------------------------------------------------------------------------
    */

    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profil', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profil/foto', [ProfileController::class, 'destroyAvatar'])
        ->name('profile.avatar.destroy');

    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');

    /*
    |--------------------------------------------------------------------------
    | Data Peserta dan Data Kartu (Admin dan Pengguna)
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,user')->group(function () {

        // Daftar dan pencarian Data Peserta.
        Route::get('/peserta', [PesertaController::class, 'index'])
            ->name('peserta.index');

        // Unduh Data Peserta dari database (hanya jika method-nya ada).
        if (method_exists(ImportPesertaController::class, 'exportDatabase')) {
            Route::get('/peserta/export', [
                ImportPesertaController::class,
                'exportDatabase',
            ])->name('peserta.export');
        }

        // Hapus semua Data Peserta.
        Route::delete('/peserta', [PesertaController::class, 'destroyAll'])
            ->name('peserta.hapus-semua');

        // Detail satu peserta. Memakai method "detail" jika ada, kalau tidak "show".
        $metodeDetail = method_exists(PesertaController::class, 'detail')
            ? 'detail'
            : 'show';

        Route::get('/peserta/{id}', [PesertaController::class, $metodeDetail])
            ->whereNumber('id')
            ->name('peserta.show');

        // Nama lama (peserta.detail) tetap tersedia untuk view versi lain.
        Route::get('/peserta/{id}/detail', [PesertaController::class, $metodeDetail])
            ->whereNumber('id')
            ->name('peserta.detail');

        // Data Kartu: didaftarkan hanya jika controller-nya ada.
        if (class_exists(KartuController::class)) {

            Route::get('/data-kartu', [KartuController::class, 'index'])
                ->name('kartu.index');

            if (method_exists(KartuController::class, 'export')) {
                Route::get('/data-kartu/export', [KartuController::class, 'export'])
                    ->name('kartu.export');
            }

            // Hapus semua Data Kartu (khusus Pengguna).
            if (method_exists(KartuController::class, 'destroyAll')) {
                Route::delete('/data-kartu', [KartuController::class, 'destroyAll'])
                    ->middleware('role:user')
                    ->name('kartu.hapus-semua');
            }
        }

        // BARU: detail satu Data Kartu.
        Route::get('/data-kartu/{id}', [KartuDetailController::class, 'show'])
            ->whereNumber('id')
            ->name('kartu.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Import Excel dan Pemeriksaan (Pengguna)
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:user')->group(function () {

        // Halaman import.
        Route::get('/import-peserta', [ImportPesertaController::class, 'index'])
            ->name('import.peserta');

        // Template Format 1.
        Route::get('/import-peserta/format', [
            ImportPesertaController::class,
            'template',
        ])->name('import.peserta.format');

        // Proses import Format 1.
        Route::post('/import-peserta', [
            ImportPesertaController::class,
            'import',
        ])->name('import.peserta.proses');

        // Import Format 2 (Data Kartu): hanya jika controller-nya ada.
        if (class_exists(ImportKartuController::class)) {

            Route::get('/import-kartu', [ImportKartuController::class, 'index'])
                ->name('import.kartu');

            if (method_exists(ImportKartuController::class, 'template')) {
                Route::get('/import-kartu/format', [
                    ImportKartuController::class,
                    'template',
                ])->name('import.kartu.format');
            }

            Route::post('/import-kartu', [
                ImportKartuController::class,
                'import',
            ])->name('import.kartu.proses');
        }

        // Pemeriksaan peserta.
        Route::get('/peserta/{id}/pemeriksaan', [
            PemeriksaanPesertaController::class,
            'create',
        ])->whereNumber('id')->name('peserta.pemeriksaan');

        Route::post('/peserta/{id}/pemeriksaan', [
            PemeriksaanPesertaController::class,
            'store',
        ])->whereNumber('id')->name('peserta.pemeriksaan.simpan');

        // BARU: pemeriksaan Data Kartu.
        Route::get('/data-kartu/{id}/pemeriksaan', [
            KartuPemeriksaanController::class,
            'create',
        ])->whereNumber('id')->name('kartu.pemeriksaan');

        Route::post('/data-kartu/{id}/pemeriksaan', [
            KartuPemeriksaanController::class,
            'store',
        ])->whereNumber('id')->name('kartu.pemeriksaan.simpan');
    });

    /*
    |--------------------------------------------------------------------------
    | Riwayat Pemeriksaan (Admin)
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {
        Route::get('/riwayat-pemeriksaan', [
            RiwayatPemeriksaanController::class,
            'index',
        ])->name('admin.riwayat');
    });
});

/*
|--------------------------------------------------------------------------
| Setup & Migrasi Database Serverless (Vercel + Neon)
|--------------------------------------------------------------------------
*/
Route::get('/system/migrate', [\App\Http\Controllers\SystemMigrationController::class, 'migrate'])
    ->name('system.migrate');
