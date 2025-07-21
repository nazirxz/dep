<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('order_number', 50)->unique();
            
            // Pengecer Info
            $table->string('pengecer_name');
            $table->string('pengecer_phone', 20)->nullable();
            $table->string('pengecer_email')->nullable();
            
            // Address Info
            $table->text('shipping_address');
            $table->string('city', 100);
            $table->string('postal_code', 10)->nullable();
            
            // GPS Location Info
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('location_address')->nullable(); // Alamat dari reverse geocoding
            $table->decimal('location_accuracy', 8, 2)->nullable(); // Akurasi GPS dalam meter
            
            // Order Amounts
            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            
            // Methods
            $table->string('shipping_method', 100);
            $table->string('payment_method', 100);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            
            // Voucher
            $table->string('voucher_code', 50)->nullable();
            $table->decimal('voucher_discount', 10, 2)->default(0);
            
            // Status
            $table->enum('order_status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            
            // Additional
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
