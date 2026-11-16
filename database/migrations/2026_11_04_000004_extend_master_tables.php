<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahan', function (Blueprint $table) {
            $table->decimal('vit_a', 8, 2)->default(0)->after('serat');
            $table->decimal('vit_b', 8, 2)->default(0)->after('vit_a');
            $table->boolean('mentah')->default(true)->after('vit_b');
            $table->string('sumber')->default('TKPI 2018')->after('mentah');
        });

        Schema::table('menu_hari', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->after('hari');
        });

        Schema::table('pm_alergi', function (Blueprint $table) {
            $table->foreignId('kategori_id')->nullable()->after('nama')->constrained('kategori')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('aktif')->default(true)->after('peran');
        });

        // penerima per kategori (menggantikan porsi kecil/besar)
        Schema::create('penerima_kategori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_id')->constrained('lokasi')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $table->integer('jumlah')->default(0);
            $table->timestamps();
            $table->unique(['lokasi_id', 'kategori_id']);
        });

        // sumber data (TKPI & DBMP) untuk menu Data Sumber
        Schema::create('sumber_data', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('penerbit')->nullable();
            $table->string('tahun')->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('jumlah_baris')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumber_data');
        Schema::dropIfExists('penerima_kategori');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('aktif'));
        Schema::table('pm_alergi', fn (Blueprint $t) => $t->dropConstrainedForeignId('kategori_id'));
        Schema::table('menu_hari', fn (Blueprint $t) => $t->dropColumn('tanggal'));
        Schema::table('bahan', fn (Blueprint $t) => $t->dropColumn(['vit_a', 'vit_b', 'mentah', 'sumber']));
    }
};
