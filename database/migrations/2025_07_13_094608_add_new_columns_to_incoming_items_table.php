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
            // Hapus kolom 'status_barang' jika ada
            if (Schema::hasColumn('incoming_items', 'status_barang')) {
                $table->dropColumn('status_barang');
            }

            // Tambahkan kolom baru setelah 'tanggal_masuk_barang'
            $table->string('nama_produsen')->nullable()->after('tanggal_masuk_barang');
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
        Schema::table('incoming_items', function (Blueprint $table) {
            // Hapus kolom baru jika migrasi di-rollback
            $table->dropColumn(['nama_produsen', 'metode_bayar', 'pembayaran_transaksi', 'nota_transaksi']);

            // Tambahkan kembali kolom 'status_barang' jika Anda ingin mengembalikannya
            // Ini penting jika Anda perlu mengembalikan database ke keadaan sebelumnya
            $table->string('status_barang')->default('Tersedia')->after('jumlah_barang');
        });
    }
};