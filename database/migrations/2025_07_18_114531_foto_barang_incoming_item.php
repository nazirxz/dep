<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incoming_items', function (Blueprint $table) {
            $table->string('foto_barang')->nullable()->after('nota_transaksi');
        });

        Schema::table('outgoing_items', function (Blueprint $table) {
            $table->string('foto_barang')->nullable()->after('nota_transaksi');
        });
    }

    public function down(): void
    {
        Schema::table('incoming_items', function (Blueprint $table) {
            $table->dropColumn('foto_barang');
        });

        Schema::table('outgoing_items', function (Blueprint $table) {
            $table->dropColumn('foto_barang');
        });
    }
};
