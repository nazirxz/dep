<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('verifikasi_barang', function (Blueprint $table) {
            $table->string('satuan_barang', 20)->nullable();
        });
    }

    public function down()
    {
        Schema::table('verifikasi_barang', function (Blueprint $table) {
            $table->dropColumn('satuan_barang');
        });
    }
};
