<?php

/**
 * seed-cycle.php — jalankan CycleSeeder tanpa menunggu autoload Composer.
 *
 * Upload ke root aplikasi (folder yang berisi artisan), lalu jalankan:
 *   php seed-cycle.php
 *
 * File ini aman dijalankan berulang; CycleSeeder bersifat idempoten.
 */

declare(strict_types=1);

$base = __DIR__;

require $base . '/vendor/autoload.php';

$app = require $base . '/bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$file = $base . '/database/seeders/CycleSeeder.php';

if (!is_file($file)) {
    fwrite(STDERR, "GAGAL: file tidak ditemukan: {$file}\n");
    exit(1);
}

$siklusFile = $base . '/app/Services/Siklus.php';

if (!is_file($siklusFile)) {
    fwrite(STDERR, "GAGAL: app/Services/Siklus.php tidak ditemukan.\n");
    fwrite(STDERR, "Upload file tersebut ke folder app/Services/ terlebih dahulu.\n");
    exit(1);
}

require_once $siklusFile;
require_once $file;

$class = 'Database\\Seeders\\CycleSeeder';

if (!class_exists($class)) {
    fwrite(STDERR, "GAGAL: class {$class} tidak ada di file seeder.\n");
    exit(1);
}

/** @var \Illuminate\Database\Seeder $seeder */
$seeder = new $class();
$seeder->setContainer($app);
$seeder->run();

echo "SELESAI: CycleSeeder berhasil dijalankan.\n";
