<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Simpan harga beli per baris menu agar riwayat belanja tidak ikut berubah saat harga master naik. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_bahan', function (Blueprint $t) {
            $t->integer('harga_beli')->nullable()->after('gram');
        });

        $beras = DB::table('bahan')->where('nama', 'Beras putih')->first();
        foreach (DB::table('menu_bahan as mb')->join('bahan as b', 'b.id', '=', 'mb.bahan_id')
            ->get(['mb.id', 'b.nama', 'b.harga']) as $row) {
            $harga = $row->nama === 'Nasi putih' ? (int) ($beras->harga ?? 0) : (int) $row->harga;
            DB::table('menu_bahan')->where('id', $row->id)->update(['harga_beli' => $harga]);
        }
    }

    public function down(): void
    {
        Schema::table('menu_bahan', function (Blueprint $t) {
            $t->dropColumn('harga_beli');
        });
    }
};
