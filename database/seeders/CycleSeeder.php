<?php

namespace Database\Seeders;

use App\Services\Siklus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Menyiapkan keluarga siklus 5/10 dan 6/12.
 *
 * Siklus 5/10 mengikuti 5 hari pelayanan efektif per minggu (Peraturan BGN
 * 13/2026). Siklus 6/12 dipertahankan sebagai pola lama. Seeder ini idempoten:
 * aman dijalankan ulang dan tidak menimpa menu yang sudah diedit.
 */
class CycleSeeder extends Seeder
{
    /** Buat pengaturan siklus dan salin menu lama ke siklus 5/10/12. */
    public function run(): void
    {
        foreach (['pola_siklus' => Siklus::POLA_LIMA, 'panjang_siklus' => '10'] as $kunci => $nilai) {
            if (! DB::table('pengaturan')->where('kunci', $kunci)->exists()) {
                DB::table('pengaturan')->insert([
                    'kunci' => $kunci, 'nilai' => $nilai,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        $sumber = DB::table('siklus')->where('jumlah_hari', 6)->orderByDesc('id')->first();
        if (! $sumber) {
            return;
        }

        $pastikanSiklus = function (int $hari, string $nama): int {
            $id = DB::table('siklus')->where('jumlah_hari', $hari)->orderByDesc('id')->value('id');
            if ($id) {
                return (int) $id;
            }

            return (int) DB::table('siklus')->insertGetId([
                'nama' => $nama, 'versi' => '1.4', 'jumlah_hari' => $hari,
                'status' => 'disetujui', 'created_at' => now(), 'updated_at' => now(),
            ]);
        };

        $tujuan5 = $pastikanSiklus(5, 'Siklus 5 Hari (Mingguan)');
        $tujuan10 = $pastikanSiklus(10, 'Siklus 10 Hari (Dua Mingguan)');
        $tujuan12 = $pastikanSiklus(12, 'Siklus 12 Hari (Pola 6 Hari)');

        $salin = function (int $sumberId, int $tujuanId, int $hari): void {
            if (DB::table('menu_hari')->where('siklus_id', $tujuanId)->where('hari', $hari)->exists()) {
                return;
            }
            $src = DB::table('menu_hari')->where('siklus_id', $sumberId)->where('hari', $hari)->first();
            if (! $src) {
                return;
            }

            $baru = DB::table('menu_hari')->insertGetId([
                'siklus_id' => $tujuanId, 'hari' => $hari, 'tanggal' => null,
                'nama' => $src->nama, 'metode' => $src->metode,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            foreach (DB::table('menu_bahan')->where('menu_hari_id', $src->id)->get() as $item) {
                DB::table('menu_bahan')->insert([
                    'menu_hari_id' => $baru, 'bahan_id' => $item->bahan_id,
                    'bahan_versi_id' => $item->bahan_versi_id, 'gram' => $item->gram,
                    'harga_beli' => $item->harga_beli, 'peran' => $item->peran,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        };

        $bahanId = function (string $nama): ?int {
            static $cache = [];
            if (! array_key_exists($nama, $cache)) {
                $cache[$nama] = DB::table('bahan')->where('nama', $nama)->value('id');
            }

            return $cache[$nama] ? (int) $cache[$nama] : null;
        };

        $menuBaru = function (int $siklusId, int $hari, string $nama, string $metode, array $items) use ($bahanId): void {
            if (DB::table('menu_hari')->where('siklus_id', $siklusId)->where('hari', $hari)->exists()) {
                return;
            }
            $id = DB::table('menu_hari')->insertGetId([
                'siklus_id' => $siklusId, 'hari' => $hari, 'tanggal' => null,
                'nama' => $nama, 'metode' => $metode,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($items as [$namaBahan, $gram, $peran]) {
                $bahan = $bahanId($namaBahan);
                if (! $bahan) {
                    continue;
                }
                DB::table('menu_bahan')->insert([
                    'menu_hari_id' => $id, 'bahan_id' => $bahan, 'gram' => $gram,
                    'peran' => $peran, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        };

        // 5 hari: cukup salin lima hari pertama.
        for ($h = 1; $h <= 5; $h++) {
            $salin($sumber->id, $tujuan5, $h);
        }

        // 10 hari: enam menu lama + empat menu baru.
        for ($h = 1; $h <= 6; $h++) {
            $salin($sumber->id, $tujuan10, $h);
        }
        $menuBaru($tujuan10, 7, 'Ayam bakar bumbu kuning', 'bakar & kukus', [
            ['Nasi putih', 145, 'pokok'], ['Ayam dada', 45, 'hewani'], ['Tempe', 35, 'nabati'],
            ['Wortel', 40, 'sayur'], ['Pisang ambon', 70, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);
        $menuBaru($tujuan10, 8, 'Ikan kembung suwir', 'kukus & tumis', [
            ['Nasi putih', 145, 'pokok'], ['Ikan kembung', 50, 'hewani'], ['Tahu', 40, 'nabati'],
            ['Bayam', 60, 'sayur'], ['Jeruk manis', 80, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);
        $menuBaru($tujuan10, 9, 'Telur dadar sayur', 'goreng & tumis', [
            ['Nasi putih', 145, 'pokok'], ['Telur ayam', 55, 'hewani'], ['Tempe', 35, 'nabati'],
            ['Wortel', 40, 'sayur'], ['Pepaya', 70, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);
        $menuBaru($tujuan10, 10, 'Ayam kukus jahe', 'kukus', [
            ['Nasi putih', 145, 'pokok'], ['Ayam dada', 45, 'hewani'], ['Tahu', 40, 'nabati'],
            ['Kangkung', 60, 'sayur'], ['Semangka', 80, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);

        // 12 hari: enam menu lama + enam menu baru.
        for ($h = 1; $h <= 6; $h++) {
            $salin($sumber->id, $tujuan12, $h);
        }
        $menuBaru($tujuan12, 7, 'Ayam bakar bumbu kuning', 'bakar & kukus', [
            ['Nasi putih', 145, 'pokok'], ['Ayam dada', 45, 'hewani'], ['Tempe', 35, 'nabati'],
            ['Wortel', 40, 'sayur'], ['Pisang ambon', 70, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);
        $menuBaru($tujuan12, 8, 'Ikan kembung suwir', 'kukus & tumis', [
            ['Nasi putih', 145, 'pokok'], ['Ikan kembung', 50, 'hewani'], ['Tahu', 40, 'nabati'],
            ['Bayam', 60, 'sayur'], ['Jeruk manis', 80, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);
        $menuBaru($tujuan12, 9, 'Telur dadar sayur', 'goreng & tumis', [
            ['Nasi putih', 145, 'pokok'], ['Telur ayam', 55, 'hewani'], ['Tempe', 35, 'nabati'],
            ['Wortel', 40, 'sayur'], ['Pepaya', 70, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);
        $menuBaru($tujuan12, 10, 'Ayam kukus jahe', 'kukus', [
            ['Nasi putih', 145, 'pokok'], ['Ayam dada', 45, 'hewani'], ['Tahu', 40, 'nabati'],
            ['Kangkung', 60, 'sayur'], ['Semangka', 80, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);
        $menuBaru($tujuan12, 11, 'Ikan lele kukus', 'kukus', [
            ['Nasi putih', 145, 'pokok'], ['Ikan lele', 50, 'hewani'], ['Tempe', 35, 'nabati'],
            ['Labu siam', 60, 'sayur'], ['Melon', 80, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);
        $menuBaru($tujuan12, 12, 'Telur bumbu tomat', 'rebus & tumis', [
            ['Nasi putih', 145, 'pokok'], ['Telur ayam', 55, 'hewani'], ['Tahu', 40, 'nabati'],
            ['Wortel', 40, 'sayur'], ['Pisang ambon', 70, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'],
            ['Garam beryodium', 0.8, 'bumbu'],
        ]);
    }
}
