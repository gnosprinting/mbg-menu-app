<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('po', function (Blueprint $table) {
            $table->foreignId('disetujui_oleh')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable()->after('disetujui_oleh');
            $table->foreignId('diterima_oleh')->nullable()->after('disetujui_pada')->constrained('users')->nullOnDelete();
            $table->timestamp('diterima_pada')->nullable()->after('diterima_oleh');
            $table->string('alasan_tolak')->nullable()->after('diterima_pada');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('po', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh']);
            $table->dropForeign(['diterima_oleh']);
            $table->dropIndex(['status']);
            $table->dropColumn(['disetujui_oleh', 'disetujui_pada', 'diterima_oleh', 'diterima_pada', 'alasan_tolak']);
        });
    }
};
