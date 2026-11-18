<?php

namespace App\Services;

class Akses
{
    /** Fitur dan role yang boleh memakainya. */
    private const PETA = [
        'menu.lihat' => ['admin', 'gizi', 'dapur', 'pengawas', 'akuntan'],
        'menu.tulis' => ['admin', 'gizi'],
        'master.lihat' => ['admin', 'gizi'],
        'master.tulis' => ['admin', 'gizi'],
        'pengaturan.lihat' => ['admin', 'gizi'],
        'pengaturan.tulis' => ['admin', 'gizi'],
        'pengguna.kelola' => ['admin'],
        'audit.lihat' => ['admin', 'akuntan'],
        'operasional.tulis' => ['admin', 'gizi', 'dapur', 'akuntan'],
        'stok.tulis' => ['admin', 'gizi', 'dapur', 'akuntan'],
        'haccp.tulis' => ['admin', 'gizi', 'dapur', 'akuntan'],
        'sisa.tulis' => ['admin', 'gizi', 'dapur', 'akuntan'],
        'po.buat' => ['admin', 'gizi', 'dapur', 'akuntan'],
        'po.setuju' => ['admin', 'akuntan'],
        'po.terima' => ['admin', 'dapur'],
        'harga.tulis' => ['admin', 'gizi', 'dapur', 'akuntan'],
        'laporan.lihat' => ['admin', 'gizi', 'dapur', 'pengawas', 'akuntan'],
    ];

    public static function peran(?string $peran = null): string
    {
        return $peran ?? auth()->user()?->peran ?? 'tamu';
    }

    public static function boleh(string $fitur, ?string $peran = null): bool
    {
        return in_array(self::peran($peran), self::PETA[$fitur] ?? [], true);
    }

    /** @return array<int, string> */
    public static function fitur(?string $peran = null): array
    {
        $peran = self::peran($peran);

        return array_keys(array_filter(self::PETA, fn ($roles) => in_array($peran, $roles, true)));
    }

    public static function label(?string $peran = null): string
    {
        return [
            'admin' => 'Admin SPPG',
            'gizi' => 'Ahli Gizi',
            'dapur' => 'Petugas Dapur',
            'pengawas' => 'Pengawas',
            'akuntan' => 'Akuntan',
        ][self::peran($peran)] ?? 'Pengguna';
    }
}
