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
            // Mengubah tipe kolom pembayaran_transaksi menjadi string (nullable)
            // Pastikan kolom ini sudah ada. Jika sebelumnya decimal, ini akan mengubahnya.
            $table->string('pembayaran_transaksi')->nullable()->change();

            // Mengubah tipe kolom nota_transaksi menjadi string (nullable)
            // Pastikan kolom ini sudah ada. Jika sebelumnya decimal, ini akan mengubahnya.
            $table->string('nota_transaksi')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_items', function (Blueprint $table) {
            // Untuk rollback, ubah kembali ke tipe decimal.
            // PENTING: Jika ada data string (jalur file) di kolom ini,
            // rollback ini akan GAGAL karena tidak bisa mengkonversi string ke decimal.
            // Anda mungkin perlu menghapus data tersebut atau mengubahnya secara manual
            // sebelum melakukan rollback jika ada data non-numerik.
            $table->decimal('pembayaran_transaksi', 8, 2)->nullable()->change();
            $table->decimal('nota_transaksi', 8, 2)->nullable()->change();
        });
    }
};
