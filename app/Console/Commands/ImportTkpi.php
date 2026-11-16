<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTkpi extends Command
{
    protected $signature = 'mbg:import-tkpi {file? : Path tkpi.csv}';

    protected $description = 'Impor bahan mentah dari Tabel Komposisi Pangan Indonesia (CSV)';

    public function handle(): int
    {
        $path = $this->argument('file') ?: (is_file(base_path('../tkpi.csv')) ? base_path('../tkpi.csv') : database_path('data/tkpi.csv'));
        if (! is_file($path)) {
            $this->error("Berkas tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $fh = fopen($path, 'r');
        $head = fgetcsv($fh, 0, ';');
        $head[0] = preg_replace('/^\xEF\xBB\xBF/', '', $head[0]);
        $idx = array_flip($head);

        // nama yang jelas bukan bahan mentah / sudah diolah
        $olah = '/\b(nasi|matang|goreng|rebus|kukus|bakar|tumis|panggang|olahan|kaleng|instan|kering|asin|manis|bubuk|tepung|selai|sirup|kerupuk|abon|sosis|nugget|bakso|dendeng|sambal|kue|roti|biskuit|mie|mi|bubur|dodol|opak|tempe\s+goreng|tahu\s+goreng|susu\s+kental|susu\s+bubuk|keju|mentega|margarin)\b/i';

        $kelompok = [
            'Pokok' => '/\b(beras|jagung|sagu|ubi|singkong|kentang|talas|gandum|terigu|havermout|bihun|makaron|beras\s+ketan)\b/i',
            'Hewani' => '/\b(daging|ayam|bebek|ikan|kakap|kembung|lele|nila|tongkol|tuna|udang|kerang|cumi|kepiting|telur|hati|jeroan|sapi|kambing|domba|babi|susu\s+segar)\b/i',
            'Nabati' => '/\b(tempe|tahu|oncom|kedelai|kacang|koro|buncis\s+kering|lupin|kacang\s+hijau|kacang\s+merah|kacang\s+tanah|kacang\s+kedelai)\b/i',
            'Sayur' => '/\b(sayur|bayam|kangkung|wortel|buncis|kol|kubis|sawi|selada|labu|pare|terong|terung|tomat|cabai|bawang|daun|jamur|brokoli|kembang\s+kol|mentimun|timun|kacang\s+panjang|bit|seledri|kemangi|taoge|toge)\b/i',
            'Buah' => '/\b(buah|pisang|apel|jeruk|pepaya|mangga|melon|semangka|nanas|jambu|anggur|alpukat|rambutan|duku|salak|markisa|sirsak|belimbing|kedondong|sawo|manggis|durian|kelapa\s+muda)\b/i',
            'Minyak' => '/\b(minyak|margarin|mentega|kelapa\s+sawit|lemak)\b/i',
            'Bumbu' => '/\b(garam|gula|kecap|terasi|penyedap|merica|lada|ketumbar|jintan|kayu\s+manis|cengkih|pala|kemiri|asam|jahe|kunyit|lengkuas|serai|daun\s+salam)\b/i',
        ];

        $insert = 0;
        $skip = 0;
        $ada = DB::table('bahan')->pluck('nama')->map(fn ($n) => mb_strtolower(trim($n)))->flip()->all();

        while (($r = fgetcsv($fh, 0, ';')) !== false) {
            if (count($r) < 22) {
                continue;
            }
            $nama = trim($r[$idx['name']]);
            if ($nama === '') {
                continue;
            }
            $lower = mb_strtolower($nama);
            $isMentah = str_contains($lower, 'mentah');
            if (! $isMentah && preg_match($olah, $lower)) {
                $skip++;
                continue;
            }
            if (isset($ada[$lower])) {
                $skip++;
                continue;
            }

            $kel = 'Lainnya';
            foreach ($kelompok as $k => $re) {
                if (preg_match($re, $nama)) {
                    $kel = $k;
                    break;
                }
            }

            $num = fn ($k) => (float) str_replace(',', '.', (string) ($r[$idx[$k]] ?? 0));

            DB::table('bahan')->insert([
                'nama' => $nama,
                'kelompok' => $kel,
                'energi' => $num('calories'),
                'protein' => $num('proteins'),
                'lemak' => $num('fat'),
                'kh' => $num('carbohydrate'),
                'natrium' => $num('natrium'),
                'serat' => $num('serat'),
                'vit_a' => $num('vitamin_a') + $num('provitamin_a'),
                'vit_b' => $num('vitamin_b1'),
                'bdd' => (int) ($num('bdd') ?: 100),
                'harga' => 0,
                'satuan' => 'kg',
                'sp_label' => null,
                'urt_label' => null,
                'mentah' => true,
                'sumber' => 'TKPI',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $ada[$lower] = true;
            $insert++;
        }
        fclose($fh);

        DB::table('sumber_data')->updateOrInsert(['kode' => 'TKPI'], [
            'nama' => 'Tabel Komposisi Pangan Indonesia (TKPI)',
            'penerbit' => 'Kementerian Kesehatan RI',
            'tahun' => '2018',
            'keterangan' => 'Nilai gizi per 100 g bagian dapat dimakan (BDD). Impor bahan mentah.',
            'jumlah_baris' => DB::table('bahan')->where('sumber', 'TKPI')->count(),
            'updated_at' => now(), 'created_at' => now(),
        ]);

        $this->info("Impor selesai: {$insert} bahan baru, {$skip} dilewati.");

        return self::SUCCESS;
    }
}
