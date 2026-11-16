<?php

use Illuminate\Support\Facades\Schedule;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Harga pasar diperbarui setiap pagi; aman bila sumber sedang tidak tersedia.
Schedule::command('mbg:tarik-harga')->dailyAt('05:30')->withoutOverlapping();
