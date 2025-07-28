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
        Schema::table('returned_items', function (Blueprint $table) {
            // Mengubah kolom order_id, order_item_id, dan user_id menjadi nullable
            // Karena pergantian barang bisa berasal dari pengecer/supplier, bukan hanya dari order customer
            $table->unsignedBigInteger('order_id')->nullable()->change();
            $table->unsignedBigInteger('order_item_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returned_items', function (Blueprint $table) {
            // Mengembalikan kolom ke keadaan semula (not nullable)
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
            $table->unsignedBigInteger('order_item_id')->nullable(false)->change();
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
