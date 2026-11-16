<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Periode/versi data master.
 *
 * Bahan  : nilai gizi + harga disimpan per periode (bahan_versi). Menu yang sudah
 *          dibuat menunjuk versi saat itu, sehingga perubahan harga/gizi tidak
 *          mengubah riwayat belanja & komposisi.
 * Penerima: jumlah per lokasi × kategori disimpan per periode langsung di tabel
 *          (berlaku_dari / berlaku_sampai); baris aktif = berlaku_sampai null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahan_versi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bahan_id')->constrained('bahan')->cascadeOnDelete();
            $t->date('berlaku_dari');
            $t->date('berlaku_sampai')->nullable();
            $t->decimal('energi', 7, 1)->default(0);
            $t->decimal('protein', 6, 1)->default(0);
            $t->decimal('lemak', 6, 1)->default(0);
            $t->decimal('kh', 6, 1)->default(0);
            $t->decimal('natrium', 7, 1)->default(0);
            $t->decimal('serat', 6, 1)->default(0);
            $t->decimal('vit_a', 8, 1)->default(0);
            $t->decimal('vit_b', 6, 2)->default(0);
            $t->integer('bdd')->default(100);
            $t->integer('harga')->default(0);
            $t->string('satuan')->default('kg');
            $t->timestamps();
            $t->index(['bahan_id', 'berlaku_sampai']);
        });

        Schema::table('menu_bahan', function (Blueprint $t) {
            $t->foreignId('bahan_versi_id')->nullable()->after('bahan_id')->constrained('bahan_versi');
        });

        Schema::table('penerima_kategori', function (Blueprint $t) {
            $t->date('berlaku_dari')->nullable()->after('jumlah');
            $t->date('berlaku_sampai')->nullable()->after('berlaku_dari');
        });

        // Backfill: satu versi terbuka per bahan, lalu tautkan menu yang sudah ada.
        $hariIni = now()->toDateString();
        foreach (DB::table('bahan')->orderBy('id')->get() as $b) {
            $versiId = DB::table('bahan_versi')->insertGetId([
                'bahan_id' => $b->id, 'berlaku_dari' => $hariIni, 'berlaku_sampai' => null,
                'energi' => $b->energi, 'protein' => $b->protein, 'lemak' => $b->lemak, 'kh' => $b->kh,
                'natrium' => $b->natrium, 'serat' => $b->serat, 'vit_a' => $b->vit_a ?? 0,
                'vit_b' => $b->vit_b ?? 0, 'bdd' => $b->bdd, 'harga' => $b->harga, 'satuan' => $b->satuan,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('menu_bahan')->where('bahan_id', $b->id)->update(['bahan_versi_id' => $versiId]);
        }

        DB::table('penerima_kategori')->update(['berlaku_dari' => $hariIni, 'berlaku_sampai' => null]);
    }

    public function down(): void
    {
        Schema::table('penerima_kategori', function (Blueprint $t) {
            $t->dropColumn(['berlaku_dari', 'berlaku_sampai']);
        });
        Schema::table('menu_bahan', function (Blueprint $t) {
            $t->dropConstrainedForeignId('bahan_versi_id');
        });
        Schema::dropIfExists('bahan_versi');
    }
};
