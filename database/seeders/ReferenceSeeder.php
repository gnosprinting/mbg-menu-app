<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ReferenceSeeder — data referensi DBMP dan harga acuan awal.
 *
 * Dipakai untuk bahan penukar 8 golongan dan harga pasar awal; bukan data
 * penerima atau menu contoh.
 */
class ReferenceSeeder extends Seeder
{
    /** Isi referensi DBMP dan harga acuan bila belum ada. */
    public function run(): void
    {
        $dbmp = [
            // Golongan I — karbohidrat (175 kkal, 4 P, 40 KH per SP)
            ['Nasi', 'I', 100, 'centong', 100], ['Beras', 'I', 50, 'sdm', 10],
            ['Kentang', 'I', 210, 'buah sedang', 105], ['Jagung manis', 'I', 180, 'buah', 100],
            ['Singkong', 'I', 120, 'potong', 60], ['Ubi jalar', 'I', 180, 'buah', 90],
            ['Mi basah', 'I', 100, 'gelas', 100], ['Roti tawar', 'I', 60, 'lembar', 30],
            // Golongan II — protein hewani (50 kkal, 7 P, 2 L)
            ['Ayam dada', 'II', 40, 'potong sedang', 40], ['Ikan kembung', 'II', 40, 'ekor', 80],
            ['Ikan lele', 'II', 40, 'ekor', 70], ['Ikan nila', 'II', 40, 'ekor', 100],
            ['Daging sapi', 'II', 35, 'potong sedang', 35], ['Hati ayam', 'II', 40, 'potong sedang', 40],
            ['Udang', 'II', 40, 'sdm', 15], ['Telur ayam ras', 'II', 55, 'butir', 55],
            ['Ikan tongkol', 'II', 40, 'potong', 40], ['Daging ayam', 'II', 40, 'potong', 40],
            // Golongan III — protein nabati (75 kkal, 5 P, 3 L, 7 KH)
            ['Tempe kedelai murni', 'III', 50, 'potong sedang', 25], ['Tahu', 'III', 110, 'buah besar', 110],
            ['Oncom', 'III', 40, 'potong kecil', 40], ['Kacang hijau', 'III', 20, 'sdm', 10],
            ['Kacang tanah', 'III', 15, 'sdm', 15], ['Kacang merah', 'III', 25, 'sdm', 15],
            // Golongan IV — sayur
            ['Bayam', 'IV-B', 100, 'gelas', 100], ['Kangkung', 'IV-B', 100, 'gelas', 100],
            ['Wortel', 'IV-B', 100, 'buah sedang', 60], ['Buncis', 'IV-B', 100, 'gelas', 100],
            ['Kol', 'IV-B', 100, 'gelas', 100], ['Sawi putih', 'IV-B', 100, 'gelas', 100],
            ['Labu siam', 'IV-B', 100, 'gelas', 100], ['Kacang panjang', 'IV-B', 100, 'gelas', 100],
            ['Labu kuning', 'IV-B', 100, 'potong', 100], ['Tomat', 'IV-A', 0, 'buah sedang', 70],
            // Golongan V — buah (50 kkal, 12 KH)
            ['Pisang ambon', 'V', 50, 'buah kecil', 50], ['Pepaya', 'V', 100, 'potong sedang', 100],
            ['Jeruk manis', 'V', 100, 'buah sedang', 100], ['Semangka', 'V', 150, 'potong besar', 150],
            ['Melon', 'V', 150, 'potong besar', 150], ['Apel', 'V', 85, 'buah kecil', 85],
            ['Mangga', 'V', 60, 'buah kecil', 60], ['Jambu biji', 'V', 100, 'buah sedang', 100],
            ['Salak', 'V', 75, 'buah sedang', 75], ['Alpukat', 'V', 60, 'buah besar', 120],
            // Golongan VI — susu (125 kkal, 7 P)
            ['Susu UHT', 'VI', 200, 'gelas', 200], ['Susu bubuk', 'VI', 25, 'sdm', 10],
            // Golongan VII — minyak (50 kkal, 5 L)
            ['Minyak kelapa sawit', 'VII', 5, 'sdt', 5], ['Margarin', 'VII', 5, 'sdt', 5],
            // Golongan VIII — gula (50 kkal, 12 KH)
            ['Gula pasir', 'VIII', 10, 'sdm', 10], ['Madu', 'VIII', 15, 'sdm', 15],

            // Perluasan kurasi (DBMP ed. 5) — karbohidrat
            ['Bubur nasi', 'I', 200, 'mangkuk', 200], ['Nasi tim', 'I', 200, 'mangkuk', 200],
            ['Bihun', 'I', 50, 'gelas', 50], ['Mi kering', 'I', 50, 'gelas', 50],
            ['Makaroni', 'I', 50, 'gelas', 50], ['Tepung beras', 'I', 50, 'sdm', 10],
            ['Tepung terigu', 'I', 50, 'sdm', 10], ['Havermut', 'I', 50, 'gelas', 50],
            ['Talas', 'I', 120, 'potong', 60], ['Roti gandum', 'I', 60, 'lembar', 30],

            // Protein hewani
            ['Ikan mas', 'II', 40, 'ekor', 80], ['Ikan mujair', 'II', 40, 'ekor', 80],
            ['Ikan bandeng', 'II', 40, 'potong', 40], ['Ikan tenggiri', 'II', 40, 'potong', 40],
            ['Ikan kakap', 'II', 40, 'potong', 40], ['Ikan teri kering', 'II', 20, 'sdm', 10],
            ['Cumi-cumi', 'II', 40, 'ekor', 40], ['Telur bebek', 'II', 55, 'butir', 55],
            ['Telur puyuh', 'II', 55, 'butir', 10], ['Daging kambing', 'II', 35, 'potong', 35],
            ['Hati sapi', 'II', 40, 'potong', 40], ['Ampela ayam', 'II', 40, 'potong', 40],

            // Protein nabati
            ['Kacang kedelai', 'III', 25, 'sdm', 15], ['Kacang tolo', 'III', 25, 'sdm', 15],
            ['Kacang kapri', 'III', 25, 'sdm', 15], ['Biji bunga matahari', 'III', 15, 'sdm', 15],
            ['Kacang almond', 'III', 15, 'sdm', 15],

            // Sayur (B = bebas, A = rendah energi)
            ['Sawi hijau', 'IV-B', 100, 'gelas', 100], ['Daun singkong', 'IV-B', 100, 'gelas', 100],
            ['Daun katuk', 'IV-B', 100, 'gelas', 100], ['Brokoli', 'IV-B', 100, 'gelas', 100],
            ['Kembang kol', 'IV-B', 100, 'gelas', 100], ['Terong', 'IV-B', 100, 'buah sedang', 100],
            ['Timun', 'IV-B', 100, 'buah sedang', 100], ['Pare', 'IV-B', 100, 'buah sedang', 100],
            ['Oyong', 'IV-B', 100, 'buah sedang', 100], ['Jagung muda', 'IV-B', 100, 'buah', 100],
            ['Jamur tiram', 'IV-B', 100, 'gelas', 100], ['Daun bawang', 'IV-B', 100, 'batang', 100],
            ['Seledri', 'IV-B', 100, 'batang', 100], ['Taoge', 'IV-B', 100, 'gelas', 100],
            ['Paprika', 'IV-B', 100, 'buah sedang', 100], ['Labu air', 'IV-B', 100, 'potong', 100],
            ['Lobak', 'IV-A', 100, 'buah sedang', 100], ['Selada', 'IV-A', 100, 'gelas', 100],
            ['Cabai merah', 'IV-A', 100, 'buah', 100], ['Bawang merah', 'IV-A', 100, 'buah', 100],
            ['Bawang putih', 'IV-A', 100, 'siung', 100],

            // Buah
            ['Nanas', 'V', 150, 'potong', 150], ['Duku', 'V', 75, 'buah', 75],
            ['Rambutan', 'V', 75, 'buah', 75], ['Manggis', 'V', 75, 'buah', 75],
            ['Belimbing', 'V', 150, 'buah', 150], ['Sawo', 'V', 60, 'buah', 60],
            ['Nangka', 'V', 50, 'potong', 50], ['Kedondong', 'V', 100, 'buah', 100],
            ['Markisa', 'V', 60, 'buah', 60], ['Stroberi', 'V', 150, 'gelas', 150],
            ['Kurma', 'V', 25, 'butir', 8],

            // Susu
            ['Susu skim bubuk', 'VI', 25, 'sdm', 10], ['Yogurt', 'VI', 200, 'gelas', 200],
            ['Keju', 'VI', 35, 'potong', 35], ['Susu kedelai', 'VI', 200, 'gelas', 200],

            // Minyak & lemak
            ['Kelapa parut', 'VII', 15, 'sdm', 15], ['Santan', 'VII', 40, 'sdm', 10],
            ['Minyak kelapa', 'VII', 5, 'sdt', 5], ['Mentega', 'VII', 5, 'sdt', 5],

            // Gula
            ['Gula merah', 'VIII', 15, 'sdm', 15], ['Sirup', 'VIII', 15, 'sdm', 15],
            ['Selai', 'VIII', 15, 'sdm', 15], ['Gula batu', 'VIII', 10, 'sdm', 10],
        ];

        DB::table('dbmp')->truncate();
        foreach ($dbmp as [$nama, $gol, $gram, $urt, $gurt]) {
            DB::table('dbmp')->insert([
                'nama' => $nama, 'golongan' => $gol, 'gram_sp' => $gram,
                'urt' => $urt, 'gram_urt' => $gurt, 'bebas' => $gol === 'IV-A',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $harga = [
            ['Beras putih', 'pokok', 'kg', 14000], ['Beras medium', 'pokok', 'kg', 13500],
            ['Gula putih', 'pokok', 'kg', 17000], ['Minyak kelapa sawit', 'pokok', 'liter', 17000],
            ['Telur ayam ras', 'pokok', 'kg', 28000], ['Ayam dada', 'penting', 'kg', 38000],
            ['Daging sapi', 'penting', 'kg', 135000], ['Ikan kembung', 'penting', 'kg', 32000],
            ['Ikan lele', 'penting', 'kg', 26000], ['Tempe', 'penting', 'kg', 12000],
            ['Tahu', 'penting', 'kg', 9000], ['Bawang merah', 'penting', 'kg', 35000],
            ['Bawang putih', 'penting', 'kg', 40000], ['Cabai merah', 'penting', 'kg', 45000],
            ['Cabai rawit', 'penting', 'kg', 55000], ['Kangkung', 'mentah', 'kg', 8000],
            ['Bayam', 'mentah', 'kg', 9000], ['Wortel', 'mentah', 'kg', 11000],
            ['Tomat', 'mentah', 'kg', 12000], ['Kentang', 'mentah', 'kg', 14000],
            ['Labu siam', 'mentah', 'kg', 7000], ['Buncis', 'mentah', 'kg', 12000],
            ['Sawi putih', 'mentah', 'kg', 9000], ['Kol', 'mentah', 'kg', 8000],
            ['Pisang ambon', 'mentah', 'kg', 12000], ['Pepaya', 'mentah', 'kg', 8000],
            ['Jeruk manis', 'mentah', 'kg', 15000], ['Semangka', 'mentah', 'kg', 7000],
            ['Melon', 'mentah', 'kg', 10000], ['Jambu biji', 'mentah', 'kg', 12000],
            ['Garam beryodium', 'penting', 'kg', 12000], ['Kecap manis', 'penting', 'liter', 20000],
        ];

        DB::table('harga_acuan')->truncate();
        foreach ($harga as [$kom, $kat, $sat, $h]) {
            DB::table('harga_acuan')->insert([
                'komoditas' => $kom, 'kategori' => $kat, 'satuan' => $sat, 'harga' => $h,
                'wilayah' => 'Kalimantan Selatan', 'tanggal' => now()->toDateString(),
                'sumber' => 'Panel Harga Pangan (acuan awal)', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
