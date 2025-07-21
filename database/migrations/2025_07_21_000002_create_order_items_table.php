<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('incoming_items'); // Reference ke incoming_items sebagai products
            $table->foreignId('incoming_item_id')->constrained('incoming_items');
            
            // Product snapshot
            $table->string('product_name');
            $table->string('product_image', 500)->nullable();
            $table->string('product_category', 100)->nullable();
            
            // Price & Quantity
            $table->integer('quantity');
            $table->string('unit', 20)->default('pcs');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 15, 2);
            
            // Additional
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
