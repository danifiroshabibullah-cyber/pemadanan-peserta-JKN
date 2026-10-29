<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin Default
        User::firstOrCreate(
            ['email' => 'admin.pemadanan@gmail.com'],
            [
                'name' => 'Admin Pemadanan',
                'username' => 'dani_admin',
                'role' => 'admin',
                'password' => 'admin1234',
            ]
        );

        // 2. Akun Petugas / Pengguna Default
        User::firstOrCreate(
            ['email' => 'petugas.pemadanan@gmail.com'],
            [
                'name' => 'Dani Petugas Pemadanan',
                'username' => 'dani_firos',
                'role' => 'user',
                'password' => 'petugas1234',
            ]
        );
    }
}
