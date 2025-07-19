<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('outgoing_items', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->string('kategori_barang');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->date('tanggal_keluar_barang');
            $table->integer('jumlah_barang');
            $table->string('tujuan_distribusi');
            $table->string('lokasi_rak_barang')->nullable();
            $table->foreignId('producer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('metode_bayar')->nullable();
            $table->string('pembayaran_transaksi')->nullable();
            $table->string('nota_transaksi')->nullable();
            $table->string('foto_barang')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('outgoing_items');
    }
};
