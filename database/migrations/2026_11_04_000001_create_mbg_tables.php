<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('jenis')->default('sekolah');
            $t->string('alamat')->nullable();
            $t->integer('pagu_harian')->default(0);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        Schema::create('kategori', function (Blueprint $t) {
            $t->id();
            $t->string('kode')->unique();
            $t->string('nama');
            $t->integer('akg_energi');
            $t->decimal('akg_protein', 6, 1);
            $t->decimal('akg_lemak', 6, 1)->nullable();
            $t->decimal('akg_kh', 6, 1)->nullable();
            $t->decimal('target_energi', 7, 1)->nullable();
            $t->decimal('target_protein', 6, 1)->nullable();
            $t->integer('urutan')->default(0);
            $t->boolean('khusus')->default(false);
            $t->timestamps();
        });

        Schema::create('bahan', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('kelompok');
            $t->decimal('energi', 7, 1)->default(0);
            $t->decimal('protein', 6, 1)->default(0);
            $t->decimal('lemak', 6, 1)->default(0);
            $t->decimal('kh', 6, 1)->default(0);
            $t->decimal('natrium', 7, 1)->default(0);
            $t->decimal('serat', 6, 1)->default(0);
            $t->integer('bdd')->default(100);
            $t->integer('harga')->default(0);
            $t->string('satuan')->default('kg');
            $t->string('sp_label')->nullable();
            $t->string('urt_label')->nullable();
            $t->timestamps();
        });

        Schema::create('profil_porsi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $t->decimal('nasi', 6, 1)->default(0);
            $t->decimal('hewani', 6, 1)->default(0);
            $t->decimal('nabati', 6, 1)->default(0);
            $t->decimal('sayur', 6, 1)->default(0);
            $t->decimal('buah', 6, 1)->default(0);
            $t->decimal('minyak', 5, 1)->default(0);
            $t->decimal('garam', 5, 2)->default(0);
            $t->decimal('susu', 6, 1)->default(0);
            $t->timestamps();
        });

        Schema::create('siklus', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('versi')->default('1.4');
            $t->integer('jumlah_hari')->default(6);
            $t->string('status')->default('draf');
            $t->timestamps();
        });

        Schema::create('menu_hari', function (Blueprint $t) {
            $t->id();
            $t->foreignId('siklus_id')->constrained('siklus')->cascadeOnDelete();
            $t->integer('hari');
            $t->string('nama');
            $t->string('metode')->nullable();
            $t->timestamps();
            $t->unique(['siklus_id', 'hari']);
        });

        Schema::create('menu_bahan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('menu_hari_id')->constrained('menu_hari')->cascadeOnDelete();
            $t->foreignId('bahan_id')->constrained('bahan');
            $t->decimal('gram', 7, 1);
            $t->string('peran');
            $t->timestamps();
        });

        Schema::create('penerima', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lokasi_id')->constrained('lokasi')->cascadeOnDelete();
            $t->string('nama');
            $t->string('jenis')->default('sekolah');
            $t->integer('porsi_kecil')->default(0);
            $t->integer('porsi_besar')->default(0);
            $t->integer('guru')->default(0);
            $t->timestamps();
        });

        Schema::create('alergen', function (Blueprint $t) {
            $t->id();
            $t->string('nama')->unique();
        });

        Schema::create('pm_alergi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lokasi_id')->constrained('lokasi')->cascadeOnDelete();
            $t->string('nama');
            $t->string('kelas')->nullable();
            $t->string('porsi')->default('kecil');
            $t->text('catatan')->nullable();
            $t->timestamps();
        });

        Schema::create('alergen_pm', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pm_alergi_id')->constrained('pm_alergi')->cascadeOnDelete();
            $t->foreignId('alergen_id')->constrained('alergen')->cascadeOnDelete();
        });

        Schema::create('alergen_bahan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bahan_id')->constrained('bahan')->cascadeOnDelete();
            $t->foreignId('alergen_id')->constrained('alergen')->cascadeOnDelete();
        });

        Schema::create('stok_item', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('jenis')->default('bahan');
            $t->string('satuan')->default('kg');
            $t->decimal('stok', 9, 2)->default(0);
            $t->decimal('minimum', 9, 2)->default(0);
            $t->integer('harga')->default(0);
            $t->timestamps();
        });

        Schema::create('stok_mutasi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('stok_item_id')->constrained('stok_item')->cascadeOnDelete();
            $t->date('tanggal');
            $t->string('jenis'); // masuk, keluar, opname
            $t->decimal('qty', 9, 2);
            $t->string('keterangan')->nullable();
            $t->timestamps();
        });

        Schema::create('haccp_log', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal');
            $t->string('titik_kontrol');
            $t->string('batas');
            $t->string('nilai');
            $t->string('status')->default('aman');
            $t->text('tindakan')->nullable();
            $t->timestamps();
        });

        Schema::create('sisa_makanan', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal');
            $t->string('kelompok');
            $t->decimal('disajikan', 8, 2)->default(0);
            $t->decimal('sisa', 8, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('pengaturan', function (Blueprint $t) {
            $t->id();
            $t->string('kunci')->unique();
            $t->text('nilai')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'pengaturan', 'sisa_makanan', 'haccp_log', 'stok_mutasi', 'stok_item',
            'alergen_bahan', 'alergen_pm', 'pm_alergi', 'alergen', 'penerima',
            'menu_bahan', 'menu_hari', 'siklus', 'profil_porsi', 'bahan', 'kategori', 'lokasi',
        ] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
