<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->date('tanggal');
            $table->string('status')->default('menunggu');
            $table->integer('total')->default(0);
            $table->string('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('po_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('po')->cascadeOnDelete();
            $table->foreignId('stok_item_id')->nullable()->constrained('stok_item')->nullOnDelete();
            $table->string('nama');
            $table->decimal('qty', 10, 2);
            $table->string('satuan')->default('kg');
            $table->integer('harga')->default(0);
            $table->integer('subtotal')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_item');
        Schema::dropIfExists('po');
    }
};
