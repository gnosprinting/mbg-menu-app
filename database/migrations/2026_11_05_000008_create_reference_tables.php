<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbmp', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('golongan', 6);
            $t->decimal('gram_sp', 7, 1)->default(0);
            $t->string('urt')->nullable();
            $t->integer('gram_urt')->default(0);
            $t->boolean('bebas')->default(false);
            $t->string('sumber')->default('DBMP PERSAGI & ASDI ed.5');
            $t->timestamps();
            $t->index(['golongan', 'nama']);
        });

        Schema::create('harga_acuan', function (Blueprint $t) {
            $t->id();
            $t->string('komoditas');
            $t->string('kategori', 12)->default('pokok'); // pokok | penting | mentah
            $t->string('satuan', 8)->default('kg');
            $t->integer('harga')->default(0);
            $t->string('wilayah')->default('Kalimantan Selatan');
            $t->date('tanggal');
            $t->string('sumber')->default('Panel Harga Pangan');
            $t->timestamps();
            $t->index(['komoditas', 'tanggal']);
        });

        Schema::create('audit_log', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('user_nama')->nullable();
            $t->string('peran', 20)->nullable();
            $t->string('aksi', 30);
            $t->string('modul', 40)->nullable();
            $t->string('target', 160)->nullable();
            $t->text('detail')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
            $t->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('harga_acuan');
        Schema::dropIfExists('dbmp');
    }
};
