<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel penerima_kategori dibuat dengan UNIQUE(lokasi_id, kategori_id).
 * Untuk periode/riwayat, satu pasangan lokasi–kategori boleh punya banyak baris
 * (satu baris aktif + arsip), jadi constraint itu harus dilepas.
 * SQLite tidak bisa drop constraint inline → tabel dibangun ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('penerima_kategori_baru', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lokasi_id')->constrained('lokasi')->cascadeOnDelete();
            $t->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $t->integer('jumlah')->default(0);
            $t->date('berlaku_dari')->nullable();
            $t->date('berlaku_sampai')->nullable();
            $t->timestamps();
            $t->index(['lokasi_id', 'kategori_id']);
            $t->index('berlaku_sampai');
        });

        DB::statement('INSERT INTO penerima_kategori_baru (id, lokasi_id, kategori_id, jumlah, berlaku_dari, berlaku_sampai, created_at, updated_at)
                       SELECT id, lokasi_id, kategori_id, jumlah, berlaku_dari, berlaku_sampai, created_at, updated_at FROM penerima_kategori');

        Schema::drop('penerima_kategori');
        Schema::rename('penerima_kategori_baru', 'penerima_kategori');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Tidak dikembalikan ke constraint unik: data periode akan bentrok.
    }
};
