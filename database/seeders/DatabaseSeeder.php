<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — urutan seeder untuk instalasi baru.
 *
 * Data referensi dan pengguna dibuat lebih dulu, lalu data contoh MBG dan
 * keluarga siklus. Seeder fitur dibuat idempoten agar aman dijalankan ulang.
 */
class DatabaseSeeder extends Seeder
{
    /** Jalankan seeder dasar sesuai urutan dependensi. */
    public function run(): void
    {
        $this->call([
            ReferenceSeeder::class,
            MbgSeeder::class,
            CycleSeeder::class,
            UserSeeder::class,
        ]);
    }
}
