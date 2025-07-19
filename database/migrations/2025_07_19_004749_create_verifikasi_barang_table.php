<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::create('verifikasi_barang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->string('kategori_barang')->nullable(); // Kategori bisa null jika belum ditentukan saat verifikasi awal
            $table->date('tanggal_masuk_barang');
            $table->integer('jumlah_barang');
            $table->string('satuan_barang')->nullable(); // Tambah field satuan_barang
            $table->string('lokasi_rak_barang')->nullable(); // Lokasi rak bisa null, ditetapkan setelah verifikasi
            $table->string('nama_produsen')->nullable();
            $table->string('metode_bayar')->nullable();
            $table->string('pembayaran_transaksi')->nullable(); // Path gambar/PDF
            $table->string('nota_transaksi')->nullable();     // Path gambar/PDF
            $table->string('foto_barang')->nullable();        // Path gambar barang
            $table->string('kondisi_fisik')->default('Baik'); // Baik, Rusak Ringan, Tidak Sesuai, Kadaluarsa
            $table->text('catatan_verifikasi')->nullable();
            $table->string('verified_by')->nullable(); // User yang memverifikasi
            $table->timestamp('verified_at')->nullable(); // Waktu verifikasi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_barang');
    }
};
