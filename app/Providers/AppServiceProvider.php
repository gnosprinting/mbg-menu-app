<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider — konfigurasi global aplikasi.
 *
 * Saat ini hanya menetapkan panjang default string 191 karakter agar indeks
 * MySQL/MariaDB lama (batas 1000 byte) tetap kompatibel dengan utf8mb4.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Kompatibilitas MySQL/MariaDB lama: indeks varchar(255) utf8mb4 melebihi 1000 byte.
        Schema::defaultStringLength(191);
    }
}
