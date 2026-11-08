<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Data awal yang aman dipakai saat web online:
 * akun admin, profil desa, kontak darurat nasional, dan konten edukasi umum.
 *
 * Data peta contoh TIDAK ikut di sini. Untuk latihan/presentasi jalankan:
 *   php artisan db:seed --class=DemoMapSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SettingSeeder::class,
            EmergencyContactSeeder::class,
            EducationContentSeeder::class,
        ]);
    }
}
