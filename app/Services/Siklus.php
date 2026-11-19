<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Siklus — sumber kebenaran pola siklus menu.
 *
 * Aplikasi mendukung dua keluarga pola:
 * - 5/10: 5 hari pelayanan per minggu, siklus 5 atau 10 hari.
 * - 6/12: pola lama 6 hari, siklus 6 atau 12 hari.
 * Pola dan panjang aktif disimpan di tabel pengaturan agar dapat diubah
 * tanpa mengubah kode.
 */
class Siklus
{
    // Dua keluarga pola yang didukung; pasangan panjang divalidasi di set().
    public const POLA_LIMA = '5/10';
    public const POLA_ENAM = '6/12';

    /** Baca satu nilai pengaturan; kembalikan default bila kosong. */
    private static function setting(string $kunci, mixed $default = null): mixed
    {
        $nilai = DB::table('pengaturan')->where('kunci', $kunci)->value('nilai');

        return ($nilai === null || $nilai === '') ? $default : $nilai;
    }

    /** Pola aktif yang valid; fallback ke 5/10 bila data rusak. */
    public static function pola(): string
    {
        $pola = (string) self::setting('pola_siklus', self::POLA_LIMA);

        return in_array($pola, [self::POLA_LIMA, self::POLA_ENAM], true) ? $pola : self::POLA_LIMA;
    }

    /** @return array<int, int> */
    public static function opsi(?string $pola = null): array
    {
        return ($pola ?? self::pola()) === self::POLA_ENAM ? [6, 12] : [5, 10];
    }

    /** Panjang siklus aktif; fallback ke opsi pertama bila tidak valid. */
    public static function panjang(): int
    {
        $panjang = (int) self::setting('panjang_siklus', 10);

        return in_array($panjang, self::opsi(), true) ? $panjang : self::opsi()[0];
    }

    /** Ambil baris siklus aktif; fallback ke siklus terbaru. */
    public static function aktif(): object
    {
        $siklus = DB::table('siklus')->where('jumlah_hari', self::panjang())->orderByDesc('id')->first()
            ?? DB::table('siklus')->orderByDesc('id')->first();

        abort_if(! $siklus, 500, 'Data siklus belum tersedia.');

        return $siklus;
    }

    /** Validasi pasangan pola/panjang, simpan pengaturan, dan buat siklus bila belum ada. */
    public static function set(string $pola, int $panjang): void
    {
        $opsi = $pola === self::POLA_ENAM ? [6, 12] : [5, 10];
        if (! in_array($pola, [self::POLA_LIMA, self::POLA_ENAM], true) || ! in_array($panjang, $opsi, true)) {
            throw new \InvalidArgumentException('Kombinasi pola dan panjang siklus tidak valid.');
        }

        foreach (['pola_siklus' => $pola, 'panjang_siklus' => (string) $panjang] as $kunci => $nilai) {
            DB::table('pengaturan')->updateOrInsert(
                ['kunci' => $kunci],
                ['nilai' => $nilai, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // Jangan biarkan pengaturan menunjuk ke siklus yang belum dibuat.
        if (! DB::table('siklus')->where('jumlah_hari', $panjang)->exists()) {
            $nama = match ($panjang) {
                5 => 'Siklus 5 Hari (Mingguan)',
                10 => 'Siklus 10 Hari (Dua Mingguan)',
                6 => 'Siklus Induk 6 Hari',
                12 => 'Siklus 12 Hari (Pola 6 Hari)',
            };
            DB::table('siklus')->insert([
                'nama' => $nama, 'versi' => '1.4', 'jumlah_hari' => $panjang,
                'status' => 'draf', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
