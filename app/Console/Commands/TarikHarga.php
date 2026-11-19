<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Tarik harga pasar (PIHPS Bank Indonesia via agregator publik) dan perbarui harga_acuan.
 * Sumber bersifat agregator tidak resmi; simpan keterangan sumber di setiap baris.
 */
class TarikHarga extends Command
{
    protected $signature = 'mbg:tarik-harga
        {--provinsi=Kalimantan Selatan : Provinsi acuan}
        {--url=https://harga-pangan.fly.dev/harga/terbaru : Endpoint JSON}
        {--dry-run : Tampilkan hasil tanpa menyimpan}
        {--ringkas : Output ringkas untuk tombol di aplikasi}';

    protected $description = 'Tarik harga pangan terbaru dan perbarui tabel harga_acuan';

    /** Pola nama komoditas PIHPS → nama komoditas di harga_acuan. */
    private array $peta = [
        'Beras putih' => ['beras kualitas bawah'],
        'Beras medium' => ['beras kualitas medium'],
        'Gula putih' => ['gula pasir'],
        'Minyak kelapa sawit' => ['minyak goreng'],
        'Telur ayam ras' => ['telur ayam ras'],
        'Ayam dada' => ['daging ayam'],
        'Daging sapi' => ['daging sapi'],
        'Ikan kembung' => ['ikan kembung'],
        'Ikan lele' => ['ikan lele'],
        'Tempe' => ['tempe'],
        'Tahu' => ['tahu'],
        'Bawang merah' => ['bawang merah'],
        'Bawang putih' => ['bawang putih'],
        'Cabai merah' => ['cabai merah'],
        'Cabai rawit' => ['cabai rawit'],
        'Kangkung' => ['kangkung'],
        'Bayam' => ['bayam'],
        'Wortel' => ['wortel'],
        'Tomat' => ['tomat'],
        'Kentang' => ['kentang'],
        'Labu siam' => ['labu siam'],
        'Buncis' => ['buncis'],
        'Sawi putih' => ['sawi putih'],
        'Kol' => ['kol'],
        'Pisang ambon' => ['pisang'],
        'Pepaya' => ['pepaya'],
        'Jeruk manis' => ['jeruk'],
        'Semangka' => ['semangka'],
        'Melon' => ['melon'],
        'Jambu biji' => ['jambu biji'],
        'Garam beryodium' => ['garam'],
        'Kecap manis' => ['kecap'],
    ];

    /** Ambil JSON harga, petakan komoditas, lalu simpan perubahan harga acuan. */
    public function handle(): int
    {
        $provinsi = $this->option('provinsi');
        $url = $this->option('url');
        $dry = (bool) $this->option('dry-run');

        $this->info("Mengambil {$url} …");

        try {
            $res = Http::timeout(45)->acceptJson()->get($url);
        } catch (\Throwable $e) {
            // php.ini Laragon menunjuk CA bundle yang tidak ada; coba path lokal bila tersedia.
            $ca = env('HARGA_CA_BUNDLE') ?: 'C:/laragon/etc/ssl/cacert.pem';
            if (str_contains($e->getMessage(), 'certificate') && is_file($ca)) {
                try {
                    $res = Http::withOptions(['verify' => $ca])->timeout(45)->acceptJson()->get($url);
                } catch (\Throwable $e2) {
                    $this->error('Gagal menghubungi sumber: '.$e2->getMessage());

                    return self::FAILURE;
                }
            } else {
                $this->error('Gagal menghubungi sumber: '.$e->getMessage());

                return self::FAILURE;
            }
        }

        if (! $res->ok()) {
            $this->error('Sumber menolak permintaan (HTTP '.$res->status().').');

            return self::FAILURE;
        }

        $json = $res->json();
        $rows = $json['data'] ?? [];
        if (! is_array($rows) || $rows === []) {
            $this->error('Struktur JSON tidak dikenali (tidak ada data).');

            return self::FAILURE;
        }

        $tanggal = $json['tanggal'] ?? now()->toDateString();
        $sumber = 'PIHPS Bank Indonesia (agregator harga-pangan.fly.dev)';
        $harga = [];

        foreach ($rows as $row) {
            $nama = mb_strtolower($row['komoditas']['nama'] ?? '');
            foreach ($this->peta as $target => $pola) {
                if (isset($harga[$target])) {
                    continue;
                }
                foreach ($pola as $p) {
                    if (str_contains($nama, $p)) {
                        $nilai = $this->hargaProvinsi($row['harga'] ?? [], $provinsi);
                        if ($nilai !== null) {
                            $harga[$target] = $nilai;
                        }
                        break 2;
                    }
                }
            }
        }

        if ($harga === []) {
            $this->error("Tidak ada komoditas yang cocok untuk provinsi {$provinsi}.");

            return self::FAILURE;
        }

        $baru = 0;
        $berubah = [];
        foreach ($harga as $komoditas => $nilai) {
            $lama = DB::table('harga_acuan')->where('komoditas', $komoditas)->value('harga');
            if ($lama !== null && (int) $lama === (int) $nilai) {
                continue;
            }
            $berubah[] = [$komoditas, $lama, $nilai];
            if (! $dry) {
                DB::table('harga_acuan')->where('komoditas', $komoditas)->update([
                    'harga' => (int) $nilai,
                    'tanggal' => $tanggal,
                    'wilayah' => $provinsi,
                    'sumber' => $sumber,
                    'updated_at' => now(),
                ]);
            }
            $baru++;
        }

        if (! $this->option('ringkas') && $berubah !== []) {
            $this->table(['Komoditas', 'Lama', 'Baru'], array_map(
                fn ($r) => [$r[0], $r[1] ? 'Rp '.number_format((int) $r[1], 0, ',', '.') : '—', 'Rp '.number_format((int) $r[2], 0, ',', '.')],
                $berubah
            ));
        }

        $this->info(($dry ? '[dry-run] ' : '').count($harga).' komoditas cocok, '.$baru.' harga diperbarui · data per '.$tanggal.'.');
        if (! $dry && $baru > 0) {
            $this->line('Jalankan `php artisan mbg:isi-harga` untuk mengisi bahan yang masih berharga 0.');
        }

        return self::SUCCESS;
    }

    /** Ambil harga provinsi; jika tidak ada, pakai median semua provinsi. */
    private function hargaProvinsi(array $baris, string $provinsi): ?int
    {
        $semua = [];
        foreach ($baris as $h) {
            $wilayah = $h['wilayah']['nama'] ?? '';
            $nilai = $h['harga'] ?? null;
            if (! is_numeric($nilai) || $nilai <= 0) {
                continue;
            }
            if (mb_strtolower($wilayah) === mb_strtolower($provinsi)) {
                return (int) round($nilai);
            }
            $semua[] = (float) $nilai;
        }

        if ($semua === []) {
            return null;
        }
        sort($semua);

        return (int) round($semua[intdiv(count($semua), 2)]);
    }
}
