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
            $table->string('kategori_barang')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->date('tanggal_masuk_barang');
            $table->integer('jumlah_barang');
            $table->string('satuan_barang')->nullable();
            $table->string('lokasi_rak_barang')->nullable();
            $table->foreignId('producer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('metode_bayar')->nullable();
            $table->string('pembayaran_transaksi')->nullable();
            $table->string('nota_transaksi')->nullable();
            $table->string('foto_barang')->nullable();
            $table->string('kondisi_fisik')->default('Baik');
            $table->text('catatan_verifikasi')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('incoming_item_id')->nullable()->constrained('incoming_items')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_barang');
    }
};
