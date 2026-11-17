<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class Belanja
{
    /**
     * Kebutuhan bahan untuk satu kategori, satu hari, sejumlah porsi.
     * Aturan: nasi → beras (÷2,6), koreksi BDD, buffer 10%.
     */
    public static function kebutuhan(int $kategoriId, int $hari, int $porsi, ?int $siklusId = null): array
    {
        $komp = Nutrition::komposisi($kategoriId, $hari, $siklusId);
        $buffer = 1 + ((float) (DB::table('pengaturan')->where('kunci', 'buffer_belanja')->value('nilai') ?? 10) / 100);
        $beras = DB::table('bahan')->where('nama', 'Beras putih')->first();
        $rows = [];
        $total = 0;

        foreach ($komp['rows'] as $r) {
            $gramPorsi = $r['gram'];
            $harga = 0;
            $satuan = 'kg';
            $namaBeli = $r['nama'];
            $gramBeli = $gramPorsi;

            if ($r['nama'] === 'Nasi putih') {
                $namaBeli = 'Beras putih';
                $gramBeli = $gramPorsi / 2.6;
                $harga = (int) ($r['harga'] ?? 0) ?: (int) ($beras->harga ?? 0);
                $satuan = 'kg';
            } else {
                $bdd = max((int) ($r['bdd'] ?? 100), 1);
                $gramBeli = $gramPorsi * 100 / $bdd;
                $harga = (int) ($r['harga'] ?? 0);
                $satuan = $r['satuan'] ?? 'kg';
            }

            $qty = $gramBeli * $porsi / 1000 * $buffer;
            $subtotal = $qty * $harga;
            $total += $subtotal;

            $rows[] = [
                'nama' => $namaBeli, 'gram_porsi' => $gramBeli, 'qty' => $qty, 'satuan' => $satuan,
                'harga' => $harga, 'harga_porsi' => $porsi > 0 ? $subtotal / $porsi : 0,
                'subtotal' => $subtotal,
            ];
        }

        return ['rows' => $rows, 'total' => $total];
    }

    public static function perKategoriHariIni(int $hari, ?int $siklusId = null): array
    {
        $kategori = DB::table('kategori')->orderBy('urutan')->get();
        $penerima = DB::table('penerima_kategori')->whereNull('berlaku_sampai')
            ->selectRaw('kategori_id, SUM(jumlah) as j')->groupBy('kategori_id')->pluck('j', 'kategori_id');
        $rows = [];
        $total = 0.0;

        foreach ($kategori as $k) {
            $porsi = (int) ($penerima[$k->id] ?? 100) ?: 100;
            $b = self::kebutuhan($k->id, $hari, $porsi, $siklusId);
            $c = Nutrition::komposisi($k->id, $hari, $siklusId);
            $rows[] = [
                'kategori' => $k->nama, 'porsi' => $porsi, 'energi' => $c['total']['energi'],
                'protein' => $c['total']['protein'], 'capaian' => $c['pct']['energi'], 'belanja' => $b['total'],
            ];
            $total += $b['total'];
        }

        return ['rows' => $rows, 'total' => $total];
    }

    public static function totalPenerima(): array
    {
        $per = DB::table('penerima_kategori as pk')
            ->join('kategori as k', 'k.id', '=', 'pk.kategori_id')
            ->whereNull('pk.berlaku_sampai')
            ->selectRaw('k.nama, k.kode, SUM(pk.jumlah) as jumlah')
            ->groupBy('k.id', 'k.nama', 'k.kode')->get();

        return [
            'total' => (int) $per->sum('jumlah'),
            'per_kategori' => $per,
        ];
    }
}
