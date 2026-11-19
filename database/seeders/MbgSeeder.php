<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * MbgSeeder — data contoh aplikasi.
 *
 * Membuat kategori, profil porsi, penerima contoh, menu, bahan menu, dan
 * data operasional secukupnya agar aplikasi langsung dapat dicoba.
 */
class MbgSeeder extends Seeder
{
    /** Isi data contoh MBG secara lengkap. */
    public function run(): void
    {
        // ---------------- pengaturan ----------------
        foreach ([
            'sppg_nama' => 'SPPG Wareg',
            'sppg_yayasan' => 'Yayasan Wareg',
            'sppg_telepon' => '0812-3456-7890',
            'sppg_email' => 'sppg@wareg.id',
            'sppg_alamat' => 'Jl. Insar No. 12, Guntung Paikat, Banjar Baru Selatan',
            'target_persen' => '30',
            'buffer_belanja' => '10',
            'natrium_max' => '600',
        ] as $k => $v) {
            DB::table('pengaturan')->insert(['kunci' => $k, 'nilai' => $v, 'created_at' => now(), 'updated_at' => now()]);
        }

        // ---------------- kategori ----------------
        $kategori = [
            ['B06', 'Bayi 6–7 bulan', 800, 15, null, null, 130, 4.5, 1],
            ['B08', 'Bayi 8–9 bulan', 800, 15, null, null, 185, 6, 2],
            ['B10', 'Bayi 10–11 bulan', 800, 15, null, null, 250, 8, 3],
            ['T12', '1–2 tahun', 1350, 20, 45, 215, null, null, 4],
            ['PAUD', 'PAUD/TK', 1400, 25, 50, 220, null, null, 5],
            ['SD13', 'SD kelas 1–3', 1650, 40, 55, 250, null, null, 6],
            ['SD46', 'SD kelas 4–6', 1950, 52, 65, 280, null, null, 7],
            ['SMP', 'SMP', 2225, 68, 75, 320, null, null, 8],
            ['SMA', 'SMA/SMK', 2375, 70, 85, 370, null, null, 9],
            ['GURU', 'Guru dan Kader', 2450, 63, 75, 360, null, null, 10],
            ['HAMIL', 'Ibu Hamil', 2550, 75, 75, 390, null, null, 11],
            ['MENYUSUI', 'Ibu Menyusui', 2580, 85, 75, 400, null, null, 12],
            ['PRE', 'Ibu Hamil Preeklampsia', 2550, 75, 75, 390, 765, 22.5, 13],
        ];
        foreach ($kategori as [$kode, $nama, $e, $p, $l, $kh, $te, $tp, $urut]) {
            DB::table('kategori')->insert([
                'kode' => $kode, 'nama' => $nama, 'akg_energi' => $e, 'akg_protein' => $p,
                'akg_lemak' => $l, 'akg_kh' => $kh, 'target_energi' => $te, 'target_protein' => $tp,
                'urutan' => $urut, 'khusus' => in_array($kode, ['PRE']), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ---------------- bahan (TKPI per 100 g BDD) ----------------
        $bahan = [
            ['Nasi putih', 'Pokok', 180, 3.0, 0.3, 39.8, 1, 0.4, 100, 0, 'kg', '1½ (I)', '1½ centong'],
            ['Beras putih', 'Pokok', 357, 8.4, 1.7, 77.1, 1, 0.3, 100, 14000, 'kg', '—', '—'],
            ['Ayam dada', 'Hewani', 153, 19.0, 8.0, 0, 60, 0, 100, 38000, 'kg', '1 (II)', '1 ptg sdg'],
            ['Telur ayam', 'Hewani', 154, 12.4, 10.8, 0.7, 142, 0, 89, 29000, 'kg', '1 (II)', '1 btr'],
            ['Ikan kembung', 'Hewani', 103, 22.0, 1.0, 0, 62, 0, 80, 32000, 'kg', '1 (II)', '1 ptg sdg'],
            ['Ikan lele', 'Hewani', 84, 16.5, 1.2, 0, 60, 0, 80, 28000, 'kg', '1 (II)', '1 ptg sdg'],
            ['Hati ayam', 'Hewani', 261, 27.0, 16.0, 0, 84, 0, 100, 45000, 'kg', '1 (II)', '1 ptg sdg'],
            ['Tempe', 'Nabati', 201, 20.8, 8.8, 13.5, 9, 1.4, 100, 12000, 'kg', '¾ (III)', '1½ ptg sdg'],
            ['Tahu', 'Nabati', 68, 7.8, 4.6, 1.6, 2, 0.1, 100, 9000, 'kg', '¼ (III)', '¼ bh bsr'],
            ['Oncom', 'Nabati', 187, 13.0, 6.0, 20.0, 10, 2.0, 100, 10000, 'kg', '¾ (III)', '1 ptg sdg'],
            ['Kangkung', 'Sayur', 28, 3.0, 0.5, 4.6, 60, 3.2, 70, 8000, 'kg', '½ (IV-B)', '½ gls'],
            ['Bayam', 'Sayur', 36, 3.5, 0.5, 6.5, 79, 2.9, 71, 9000, 'kg', '½ (IV-B)', '½ gls'],
            ['Wortel', 'Sayur', 36, 1.0, 0.6, 7.9, 69, 0.8, 80, 11000, 'kg', '½ (IV-B)', '½ gls'],
            ['Labu siam', 'Sayur', 26, 0.6, 0.1, 5.5, 2, 0.9, 100, 7000, 'kg', '½ (IV-B)', '½ gls'],
            ['Pisang ambon', 'Buah', 99, 1.2, 0.2, 25.8, 1, 0.7, 75, 12000, 'kg', '1½ (V)', '1½ bh kcl'],
            ['Pepaya', 'Buah', 46, 0.5, 0.1, 12.2, 8, 1.6, 75, 8000, 'kg', '1 (V)', '1 ptg sdg'],
            ['Jeruk manis', 'Buah', 45, 0.9, 0.1, 11.2, 2, 0.4, 72, 15000, 'kg', '1½ (V)', '1½ bh kcl'],
            ['Semangka', 'Buah', 32, 0.5, 0.2, 7.2, 1, 0.2, 46, 7000, 'kg', '1 (V)', '½ ptg bsr'],
            ['Melon', 'Buah', 37, 0.5, 0.1, 9.1, 1, 0.4, 60, 10000, 'kg', '1 (V)', '½ ptg bsr'],
            ['Jambu biji', 'Buah', 49, 0.9, 0.3, 12.2, 2, 2.2, 82, 12000, 'kg', '1 (V)', '1 bh sdg'],
            ['Minyak kelapa sawit', 'Minyak', 884, 0, 100, 0, 0, 0, 100, 17000, 'L', '1 (VII)', '1 sdt'],
            ['Kecap manis', 'Bumbu', 46, 0.9, 0, 9.8, 4000, 0, 100, 20000, 'L', '—', '¼ sdm'],
            ['Garam beryodium', 'Bumbu', 0, 0, 0, 0, 39000, 0, 100, 12000, 'kg', '—', '¼ sdt'],
        ];
        foreach ($bahan as $b) {
            DB::table('bahan')->insert([
                'nama' => $b[0], 'kelompok' => $b[1], 'energi' => $b[2], 'protein' => $b[3],
                'lemak' => $b[4], 'kh' => $b[5], 'natrium' => $b[6], 'serat' => $b[7],
                'bdd' => $b[8], 'harga' => $b[9], 'satuan' => $b[10],
                'sp_label' => $b[11], 'urt_label' => $b[12], 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $id = fn ($nama) => DB::table('bahan')->where('nama', $nama)->value('id');

        // ---------------- profil porsi ----------------
        $prof = [
            'B06' => [30, 15, 10, 15, 20, 1, 0],
            'B08' => [45, 20, 15, 20, 30, 1.5, 0],
            'B10' => [60, 25, 20, 25, 40, 2, 0.3],
            'T12' => [90, 30, 25, 35, 50, 3, 0.5],
            'PAUD' => [110, 35, 28, 45, 60, 4, 0.6],
            'SD13' => [145, 45, 35, 60, 70, 5, 0.8],
            'SD46' => [175, 55, 42, 70, 85, 6, 1.0],
            'SMP' => [200, 60, 50, 80, 100, 7, 1.2],
            'SMA' => [210, 65, 55, 85, 110, 8, 1.2],
            'GURU' => [200, 60, 50, 80, 100, 7, 1.2],
            'HAMIL' => [200, 65, 50, 90, 90, 5, 1.0],
            'MENYUSUI' => [210, 65, 55, 90, 100, 5, 1.0],
            'PRE' => [200, 75, 50, 90, 90, 5, 0.5],
        ];
        foreach ($prof as $kode => $p) {
            DB::table('profil_porsi')->insert([
                'kategori_id' => DB::table('kategori')->where('kode', $kode)->value('id'),
                'nasi' => $p[0], 'hewani' => $p[1], 'nabati' => $p[2], 'sayur' => $p[3],
                'buah' => $p[4], 'minyak' => $p[5], 'garam' => $p[6],
                'susu' => in_array($kode, ['HAMIL', 'MENYUSUI', 'PRE']) ? 200 : 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ---------------- siklus 6 hari ----------------
        $siklusId = DB::table('siklus')->insertGetId([
            'nama' => 'Siklus Induk 6 Hari', 'versi' => '1.4', 'jumlah_hari' => 6,
            'status' => 'disetujui', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $menu = [
            [1, 'Ayam kecap', 'tumis & kukus', [['Nasi putih', 145, 'pokok'], ['Ayam dada', 45, 'hewani'], ['Tempe', 35, 'nabati'], ['Kangkung', 60, 'sayur'], ['Pisang ambon', 70, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'], ['Kecap manis', 5, 'bumbu'], ['Garam beryodium', 0.8, 'bumbu']]],
            [2, 'Telur balado', 'rebus & kukus', [['Nasi putih', 145, 'pokok'], ['Telur ayam', 55, 'hewani'], ['Tahu', 40, 'nabati'], ['Wortel', 40, 'sayur'], ['Pepaya', 70, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'], ['Garam beryodium', 0.8, 'bumbu']]],
            [3, 'Ikan kembung goreng', 'goreng & kukus', [['Nasi putih', 145, 'pokok'], ['Ikan kembung', 50, 'hewani'], ['Tempe', 35, 'nabati'], ['Bayam', 60, 'sayur'], ['Jeruk manis', 80, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'], ['Garam beryodium', 0.8, 'bumbu']]],
            [4, 'Bola-bola ayam kuah', 'kukus & tumis', [['Nasi putih', 145, 'pokok'], ['Ayam dada', 45, 'hewani'], ['Tahu', 40, 'nabati'], ['Wortel', 40, 'sayur'], ['Semangka', 80, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'], ['Garam beryodium', 0.8, 'bumbu']]],
            [5, 'Ikan lele goreng', 'goreng & rebus', [['Nasi putih', 145, 'pokok'], ['Ikan lele', 50, 'hewani'], ['Tempe', 35, 'nabati'], ['Labu siam', 60, 'sayur'], ['Melon', 80, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'], ['Garam beryodium', 0.8, 'bumbu']]],
            [6, 'Hati ayam bumbu kecap', 'tumis & rebus', [['Nasi putih', 145, 'pokok'], ['Hati ayam', 45, 'hewani'], ['Oncom', 35, 'nabati'], ['Kangkung', 60, 'sayur'], ['Jambu biji', 80, 'buah'], ['Minyak kelapa sawit', 5, 'minyak'], ['Kecap manis', 5, 'bumbu'], ['Garam beryodium', 0.8, 'bumbu']]],
        ];
        foreach ($menu as [$hari, $nama, $metode, $items]) {
            $mh = DB::table('menu_hari')->insertGetId([
                'siklus_id' => $siklusId, 'hari' => $hari,
                'tanggal' => now()->startOfWeek()->addDays($hari - 1)->toDateString(),
                'nama' => $nama, 'metode' => $metode,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($items as [$b, $g, $peran]) {
                DB::table('menu_bahan')->insert([
                    'menu_hari_id' => $mh, 'bahan_id' => $id($b), 'gram' => $g, 'peran' => $peran,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // ---------------- lokasi & penerima ----------------
        $lokasi = [
            ['SDN 1 Sukamaju', 'sekolah', 'Jl. Sukamaju No. 1', 5000000],
            ['SMPN 4 Banjar', 'sekolah', 'Jl. Ahmad Yani Km 4', 4500000],
            ['Posyandu Melati', 'posyandu', 'Guntung Paikat', 3500000],
        ];
        foreach ($lokasi as $l) {
            $lid = DB::table('lokasi')->insertGetId([
                'nama' => $l[0], 'jenis' => $l[1], 'alamat' => $l[2], 'pagu_harian' => $l[3],
                'aktif' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
            if ($l[1] === 'sekolah') {
                DB::table('penerima')->insert([
                    'lokasi_id' => $lid, 'nama' => $l[0], 'jenis' => 'sekolah',
                    'porsi_kecil' => $l[0] === 'SDN 1 Sukamaju' ? 300 : 0,
                    'porsi_besar' => $l[0] === 'SDN 1 Sukamaju' ? 320 : 260,
                    'guru' => $l[0] === 'SDN 1 Sukamaju' ? 38 : 24,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            } else {
                DB::table('penerima')->insert([
                    'lokasi_id' => $lid, 'nama' => $l[0], 'jenis' => 'posyandu',
                    'porsi_kecil' => 42, 'porsi_besar' => 20, 'guru' => 0,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        DB::table('penerima')->insert([
            'lokasi_id' => DB::table('lokasi')->where('nama', 'SDN 1 Sukamaju')->value('id'),
            'nama' => 'PAUD Melati', 'jenis' => 'sekolah', 'porsi_kecil' => 80,
            'porsi_besar' => 0, 'guru' => 6, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // penerima per kategori (model baru)
        $kid = fn ($kode) => DB::table('kategori')->where('kode', $kode)->value('id');
        $lid = fn ($nama) => DB::table('lokasi')->where('nama', $nama)->value('id');
        $baris = [
            ['SDN 1 Sukamaju', 'SD13', 300], ['SDN 1 Sukamaju', 'SD46', 320], ['SDN 1 Sukamaju', 'GURU', 38],
            ['SMPN 4 Banjar', 'SMP', 260], ['SMPN 4 Banjar', 'GURU', 24],
            ['Posyandu Melati', 'PAUD', 80], ['Posyandu Melati', 'GURU', 6],
            ['Posyandu Melati', 'B06', 14], ['Posyandu Melati', 'B08', 14], ['Posyandu Melati', 'B10', 14],
            ['Posyandu Melati', 'T12', 20], ['Posyandu Melati', 'HAMIL', 11], ['Posyandu Melati', 'MENYUSUI', 9],
        ];
        foreach ($baris as [$lokasiNama, $kode, $jumlah]) {
            DB::table('penerima_kategori')->updateOrInsert(
                ['lokasi_id' => $lid($lokasiNama), 'kategori_id' => $kid($kode)],
                ['jumlah' => $jumlah, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // sumber data
        DB::table('sumber_data')->updateOrInsert(['kode' => 'DBMP'], [
            'nama' => 'Daftar Bahan Makanan Penukar (DBMP)',
            'penerbit' => 'PERSAGI & ASDI',
            'tahun' => 'edisi 5',
            'keterangan' => '8 golongan penukar dengan ukuran rumah tangga (URT) dan gram per satuan penukar.',
            'jumlah_baris' => 8,
            'updated_at' => now(), 'created_at' => now(),
        ]);

        // ---------------- alergen & PM ----------------
        foreach (['Ikan', 'Telur', 'Ayam', 'Kedelai', 'Susu', 'Kacang', 'Gluten', 'Udang', 'Kerang', 'Wijen', 'Kacang pohon'] as $a) {
            DB::table('alergen')->insert(['nama' => $a]);
        }
        $aid = fn ($nama) => DB::table('alergen')->where('nama', $nama)->value('id');
        $map = [
            'Ikan kembung' => ['Ikan'], 'Ikan lele' => ['Ikan'],
            'Telur ayam' => ['Telur'], 'Ayam dada' => ['Ayam'], 'Hati ayam' => ['Ayam'],
            'Tempe' => ['Kedelai'], 'Tahu' => ['Kedelai'], 'Oncom' => ['Kedelai'],
            'Kerang' => ['Kerang'], 'Wijen' => ['Wijen'],
            'Kacang mete/biji jambu monyet segar' => ['Kacang pohon'],
        ];
        foreach ($map as $b => $alergens) {
            $bahanId = $id($b);
            if (! $bahanId) {
                continue; // bahan belum ada pada instalasi baru; dipetakan setelah impor TKPI
            }
            foreach ($alergens as $a) {
                DB::table('alergen_bahan')->insert(['bahan_id' => $bahanId, 'alergen_id' => $aid($a)]);
            }
        }
        $sdn = DB::table('lokasi')->where('nama', 'SDN 1 Sukamaju')->value('id');
        $smp = DB::table('lokasi')->where('nama', 'SMPN 4 Banjar')->value('id');
        $pm = [
            ['Aisyah', '2', 'kecil', 'SD13', $sdn, ['Ikan']],
            ['Raka', '5', 'besar', 'SD46', $sdn, ['Ikan', 'Telur']],
            ['Nadia', '1', 'kecil', 'SD13', $smp, ['Telur']],
        ];
        foreach ($pm as [$nama, $kelas, $porsi, $kode, $lok, $alergens]) {
            $pid = DB::table('pm_alergi')->insertGetId([
                'lokasi_id' => $lok, 'nama' => $nama, 'kelas' => $kelas, 'porsi' => $porsi,
                'kategori_id' => DB::table('kategori')->where('kode', $kode)->value('id'),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($alergens as $a) {
                DB::table('alergen_pm')->insert(['pm_alergi_id' => $pid, 'alergen_id' => $aid($a)]);
            }
        }

        // ---------------- stok ----------------
        $stok = [
            ['Beras', 'bahan', 'kg', 120, 50, 14000], ['Ayam dada', 'bahan', 'kg', 18, 20, 38000],
            ['Tempe', 'bahan', 'kg', 4, 5, 12000], ['Kangkung', 'bahan', 'kg', 12, 8, 8000],
            ['Minyak kelapa sawit', 'bahan', 'L', 9, 5, 17000], ['Telur ayam', 'bahan', 'kg', 30, 15, 29000],
            ['Ikan kembung', 'bahan', 'kg', 10, 15, 32000], ['Gas LPG 12 kg', 'operasional', 'tabung', 4, 6, 180000],
            ['Kotak makan', 'operasional', 'pcs', 900, 500, 1200], ['Sabun cuci', 'operasional', 'liter', 8, 5, 25000],
        ];
        foreach ($stok as $s) {
            DB::table('stok_item')->insert([
                'nama' => $s[0], 'jenis' => $s[1], 'satuan' => $s[2], 'stok' => $s[3],
                'minimum' => $s[4], 'harga' => $s[5], 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $sid = fn ($nama) => DB::table('stok_item')->where('nama', $nama)->value('id');
        DB::table('stok_mutasi')->insert([
            ['stok_item_id' => $sid('Beras'), 'tanggal' => now()->subDays(2)->toDateString(), 'jenis' => 'masuk', 'qty' => 100, 'keterangan' => 'PO-2604-010'],
            ['stok_item_id' => $sid('Ayam dada'), 'tanggal' => now()->subDay()->toDateString(), 'jenis' => 'keluar', 'qty' => 49.5, 'keterangan' => 'Menu hari 2'],
            ['stok_item_id' => $sid('Tempe'), 'tanggal' => now()->toDateString(), 'jenis' => 'opname', 'qty' => -2, 'keterangan' => 'Koreksi timbangan'],
        ]);

        // ---------------- HACCP & sisa ----------------
        $tgl = now()->toDateString();
        foreach ([
            ['Suhu chiller', '≤ 4 °C', '3,0 °C', 'aman', null],
            ['Suhu freezer', '≤ −18 °C', '−19,0 °C', 'aman', null],
            ['Suhu masak inti', '≥ 70 °C', '74,0 °C', 'aman', null],
            ['Waktu distribusi', '≤ 2 jam', '45 menit', 'aman', null],
            ['Pemisahan talenan', 'Merah / hijau', 'Tidak dipisah', 'deviasi', 'Talenan dipisah, briefing 5 petugas'],
        ] as $h) {
            DB::table('haccp_log')->insert([
                'tanggal' => $tgl, 'titik_kontrol' => $h[0], 'batas' => $h[1], 'nilai' => $h[2],
                'status' => $h[3], 'tindakan' => $h[4], 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        foreach ([
            ['Karbohidrat', 42.0, 1.2], ['Protein hewani', 19.5, 0.8], ['Protein nabati', 15.0, 0.6],
            ['Sayur', 20.0, 2.4], ['Buah', 30.0, 0.3], ['Lainnya', 5.5, 0.2],
        ] as $s) {
            DB::table('sisa_makanan')->insert([
                'tanggal' => $tgl, 'kelompok' => $s[0], 'disajikan' => $s[1], 'sisa' => $s[2],
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
