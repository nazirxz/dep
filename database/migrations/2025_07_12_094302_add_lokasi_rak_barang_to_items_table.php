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
        Schema::table('incoming_items', function (Blueprint $table) {
            // Tambahkan kolom lokasi_rak_barang setelah kolom status_barang
            $table->string('lokasi_rak_barang')->nullable()->after('status_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_items', function (Blueprint $table) {
            // Hapus kolom lokasi_rak_barang jika migrasi di-rollback
            $table->dropColumn('lokasi_rak_barang');
        });
    }
};
