<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Melengkapi relasi alergen–bahan yang bahannya baru ada setelah impor TKPI.
 * Aman dijalankan berulang: hanya menambah pasangan yang belum ada.
 */
class AlergenBahanSeeder extends Seeder
{
    /** Isi relasi alergen–bahan bila belum ada. */
    public function run(): void
    {
        $peta = [
            'Kerang' => ['Kerang'],
            'Kerang asap mentah' => ['Kerang'],
            'Kerang dendeng mentah' => ['Kerang'],
            'Wijen' => ['Wijen'],
            'Minyak Wijen' => ['Wijen'],
            'Kacang mete/biji jambu monyet segar' => ['Kacang pohon'],
            'Biji Jambu Mete' => ['Kacang pohon'],
        ];

        foreach ($peta as $bahan => $alergen) {
            $bahanId = DB::table('bahan')->where('nama', $bahan)->value('id');
            if (! $bahanId) {
                continue;
            }
            foreach ($alergen as $nama) {
                $alergenId = DB::table('alergen')->where('nama', $nama)->value('id');
                if (! $alergenId) {
                    continue;
                }
                $ada = DB::table('alergen_bahan')
                    ->where('bahan_id', $bahanId)->where('alergen_id', $alergenId)->exists();
                if (! $ada) {
                    DB::table('alergen_bahan')->insert([
                        'bahan_id' => $bahanId,
                        'alergen_id' => $alergenId,
                    ]);
                }
            }
        }
    }
}
