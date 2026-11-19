<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder — akun demo lima peran.
 *
 * Semua akun memakai sandi awal "password"; wajib diganti sebelum produksi.
 */
class UserSeeder extends Seeder
{
    /** Buat/Perbarui akun demo berdasarkan email. */
    public function run(): void
    {
        $lokasi = DB::table('lokasi')->where('nama', 'SPPG Wareg')->value('id')
            ?? DB::table('lokasi')->orderBy('id')->value('id');

        $users = [
            ['Admin SPPG', 'admin@wareg.id', 'admin', $lokasi],
            ['Rina Nurhaliza', 'rina@wareg.id', 'gizi', $lokasi],
            ['Sari Wulandari', 'sari@wareg.id', 'dapur', $lokasi],
            ['Pengawas HACCP', 'haccp@wareg.id', 'pengawas', $lokasi],
            ['Dewi Akuntan', 'akuntan@wareg.id', 'akuntan', $lokasi],
        ];

        foreach ($users as [$nama, $email, $peran, $lok]) {
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $nama,
                    'peran' => $peran,
                    'lokasi_id' => $lok,
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
