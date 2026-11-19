<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * IsiHargaAcuan — isi harga bahan yang masih nol dari tabel harga_acuan.
 *
 * Pencocokan dilakukan dari nama komoditas yang sama, lalu fallback ke nama
 * yang mengandung komoditas. Harga bahan dan versi aktif ikut diperbarui.
 */
class IsiHargaAcuan extends Command
{
    protected $signature = 'mbg:isi-harga {--dry : Tampilkan rencana tanpa menulis}';
    protected $description = 'Isi harga bahan yang masih 0 dari tabel harga_acuan (pasar tradisional).';

    /** Cocokkan bahan tanpa harga dengan harga acuan dan perbarui bila diminta. */
    public function handle(): int
    {
        $acuan = DB::table('harga_acuan')->orderByDesc('tanggal')->get();
        $bahan = DB::table('bahan')->where('harga', '<=', 0)->get();
        $isi = 0;
        $namaAcuan = [];

        foreach ($bahan as $b) {
            $match = null;
            foreach ($acuan as $a) {
                if (strcasecmp($a->komoditas, $b->nama) === 0) {
                    $match = $a;
                    break;
                }
            }
            if (! $match) {
                foreach ($acuan as $a) {
                    if (stripos($b->nama, $a->komoditas) !== false) {
                        $match = $a;
                        break;
                    }
                }
            }
            if (! $match) {
                continue;
            }
            if (! $this->option('dry')) {
                DB::table('bahan')->where('id', $b->id)->update(['harga' => $match->harga, 'updated_at' => now()]);
                DB::table('bahan_versi')->where('bahan_id', $b->id)->whereNull('berlaku_sampai')
                    ->update(['harga' => $match->harga, 'updated_at' => now()]);
            }
            $isi++;
            $namaAcuan[] = $b->nama.' → Rp '.number_format($match->harga, 0, ',', '.');
        }

        $this->info("Harga terisi: {$isi} bahan (dari ".count($bahan).' bahan tanpa harga).');
        foreach (array_slice($namaAcuan, 0, 12) as $n) {
            $this->line('  · '.$n);
        }

        return self::SUCCESS;
    }
}
