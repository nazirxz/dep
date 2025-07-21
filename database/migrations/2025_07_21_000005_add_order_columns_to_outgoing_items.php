<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outgoing_items', function (Blueprint $table) {
            // Tambah kolom untuk integrasi dengan order system
            $table->foreignId('incoming_item_id')->nullable()->constrained();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('order_item_id')->nullable()->constrained();
            $table->string('pengecer_name')->nullable();
            $table->string('pengecer_phone', 20)->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('location_address')->nullable();
            $table->enum('transaction_type', ['b2b', 'retail'])->default('b2b');
        });
    }

    public function down(): void
    {
        Schema::table('outgoing_items', function (Blueprint $table) {
            // Drop foreign keys only if they exist
            if (Schema::hasColumn('outgoing_items', 'incoming_item_id')) {
                $table->dropForeign(['incoming_item_id']);
            }
            if (Schema::hasColumn('outgoing_items', 'order_id')) {
                $table->dropForeign(['order_id']);
            }
            if (Schema::hasColumn('outgoing_items', 'order_item_id')) {
                $table->dropForeign(['order_item_id']);
            }
            
            $table->dropColumn([
                'incoming_item_id', 'order_id', 'order_item_id', 'pengecer_name', 'pengecer_phone',
                'shipping_address', 'latitude', 'longitude', 'location_address',
                'transaction_type'
            ]);
        });
    }
};
