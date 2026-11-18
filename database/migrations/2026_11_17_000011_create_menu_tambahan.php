<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_tambahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_hari_id')->constrained('menu_hari')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $table->foreignId('bahan_id')->constrained('bahan')->cascadeOnDelete();
            $table->decimal('gram', 7, 1);
            $table->string('metode')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();

            // Satu bahan per kategori per hari; kiriman ulang memperbarui gram.
            $table->unique(['menu_hari_id', 'kategori_id', 'bahan_id'], 'menu_tambahan_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_tambahan');
    }
};
