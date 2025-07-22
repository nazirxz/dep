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
            // Add foreign key to orders table
            $table->unsignedBigInteger('order_id')->after('id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            
            // Add foreign key to order_items table (specific item being returned)
            $table->unsignedBigInteger('order_item_id')->after('order_id');
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            
            // Add user_id for easy access (redundant but useful for queries)
            $table->unsignedBigInteger('user_id')->after('order_item_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returned_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['order_item_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['order_id', 'order_item_id', 'user_id']);
        });
    }
};
