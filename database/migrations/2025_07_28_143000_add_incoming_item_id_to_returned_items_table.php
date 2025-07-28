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
            if (!Schema::hasColumn('returned_items', 'incoming_item_id')) {
                $table->unsignedBigInteger('incoming_item_id')->nullable()->after('user_id');
                $table->foreign('incoming_item_id')->references('id')->on('incoming_items')->onDelete('set null');
                $table->index('incoming_item_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returned_items', function (Blueprint $table) {
            if (Schema::hasColumn('returned_items', 'incoming_item_id')) {
                $table->dropForeign(['incoming_item_id']);
                $table->dropIndex(['incoming_item_id']);
                $table->dropColumn('incoming_item_id');
            }
        });
    }
};