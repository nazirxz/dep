<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producers', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produsen_supplier');
            $table->string('kontak_whatsapp')->nullable();
            $table->string('alamat')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('email')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('producers');
    }
}; 