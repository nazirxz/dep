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
        Schema::table('verifikasi_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('verifikasi_barang', 'harga_jual')) {
                $table->decimal('harga_jual', 10, 2)->nullable()->after('jumlah_barang');
            }
            
            if (!Schema::hasColumn('verifikasi_barang', 'status')) {
                $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->after('catatan_verifikasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verifikasi_barang', function (Blueprint $table) {
            if (Schema::hasColumn('verifikasi_barang', 'harga_jual')) {
                $table->dropColumn('harga_jual');
            }
            
            if (Schema::hasColumn('verifikasi_barang', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};