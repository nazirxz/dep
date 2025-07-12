<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('outgoing_items', function (Blueprint $table) {
            // Tambahkan kolom lokasi_rak_barang setelah kolom tujuan_distribusi
            $table->string('lokasi_rak_barang')->nullable()->after('tujuan_distribusi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outgoing_items', function (Blueprint $table) {
            // Hapus kolom lokasi_rak_barang jika migrasi di-rollback
            $table->dropColumn('lokasi_rak_barang');
        });
    }
};
