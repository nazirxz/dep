<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('incoming_items', function (Blueprint $table) {
            $table->string('kondisi_fisik')->default('Baik')->after('foto_barang');
            $table->text('catatan')->nullable()->after('kondisi_fisik');
        });
    }

    public function down()
    {
        Schema::table('incoming_items', function (Blueprint $table) {
            $table->dropColumn(['kondisi_fisik', 'catatan']);
        });
    }
}; 