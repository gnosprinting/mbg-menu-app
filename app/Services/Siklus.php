<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class Siklus
{
    public const POLA_LIMA = '5/10';
    public const POLA_ENAM = '6/12';

    private static function setting(string $kunci, mixed $default = null): mixed
    {
        $nilai = DB::table('pengaturan')->where('kunci', $kunci)->value('nilai');

        return ($nilai === null || $nilai === '') ? $default : $nilai;
    }

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

    public static function panjang(): int
    {
        $panjang = (int) self::setting('panjang_siklus', 10);

        return in_array($panjang, self::opsi(), true) ? $panjang : self::opsi()[0];
    }

    public static function aktif(): object
    {
        $siklus = DB::table('siklus')->where('jumlah_hari', self::panjang())->orderByDesc('id')->first()
            ?? DB::table('siklus')->orderByDesc('id')->first();

        abort_if(! $siklus, 500, 'Data siklus belum tersedia.');

        return $siklus;
    }

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
    }
}
