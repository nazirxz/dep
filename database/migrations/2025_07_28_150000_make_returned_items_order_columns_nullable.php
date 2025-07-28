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
            // Drop existing foreign key constraints
            $table->dropForeign(['order_id']);
            $table->dropForeign(['order_item_id']);
            $table->dropForeign(['user_id']);
            
            // Drop the columns
            $table->dropColumn(['order_id', 'order_item_id', 'user_id']);
        });
        
        Schema::table('returned_items', function (Blueprint $table) {
            // Re-add columns as nullable
            $table->unsignedBigInteger('order_id')->nullable()->after('id');
            $table->unsignedBigInteger('order_item_id')->nullable()->after('order_id');
            $table->unsignedBigInteger('user_id')->nullable()->after('order_item_id');
            
            // Re-add foreign key constraints with nullable support
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returned_items', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['order_id']);
            $table->dropForeign(['order_item_id']);
            $table->dropForeign(['user_id']);
            
            // Drop the columns
            $table->dropColumn(['order_id', 'order_item_id', 'user_id']);
        });
        
        Schema::table('returned_items', function (Blueprint $table) {
            // Re-add columns as NOT NULL (original state)
            $table->unsignedBigInteger('order_id')->after('id');
            $table->unsignedBigInteger('order_item_id')->after('order_id');
            $table->unsignedBigInteger('user_id')->after('order_item_id');
            
            // Re-add foreign key constraints
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};