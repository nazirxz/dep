<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outgoing_items', function (Blueprint $table) {
            // Add incoming_item_id column if it doesn't exist
            if (!Schema::hasColumn('outgoing_items', 'incoming_item_id')) {
                $table->foreignId('incoming_item_id')->nullable()->constrained()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('outgoing_items', function (Blueprint $table) {
            if (Schema::hasColumn('outgoing_items', 'incoming_item_id')) {
                $table->dropForeign(['incoming_item_id']);
                $table->dropColumn('incoming_item_id');
            }
        });
    }
};
