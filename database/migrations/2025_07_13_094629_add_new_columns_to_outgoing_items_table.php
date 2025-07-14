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
            // Tambahkan kolom baru setelah 'tujuan_distribusi'
            $table->string('nama_produsen')->nullable()->after('tujuan_distribusi');
            $table->string('metode_bayar')->nullable()->after('nama_produsen');
            $table->decimal('pembayaran_transaksi', 15, 2)->nullable()->after('metode_bayar'); // Menggunakan decimal untuk pembayaran
            $table->string('nota_transaksi')->nullable()->after('pembayaran_transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outgoing_items', function (Blueprint $table) {
            // Hapus kolom baru jika migrasi di-rollback
            $table->dropColumn(['nama_produsen', 'metode_bayar', 'pembayaran_transaksi', 'nota_transaksi']);
        });
    }
};