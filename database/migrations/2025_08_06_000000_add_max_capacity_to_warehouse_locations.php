<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create a table to track max capacity for each warehouse location
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_name')->unique();
            $table->integer('max_capacity')->default(300);
            $table->integer('current_capacity')->default(0);
            $table->timestamps();
        });

        // Populate existing locations from incoming_items
        $locations = \DB::table('incoming_items')
            ->whereNotNull('lokasi_rak_barang')
            ->where('lokasi_rak_barang', '!=', '')
            ->distinct()
            ->pluck('lokasi_rak_barang');

        foreach ($locations as $location) {
            $currentCapacity = \DB::table('incoming_items')
                ->where('lokasi_rak_barang', $location)
                ->where('jumlah_barang', '>', 0)
                ->sum('jumlah_barang');

            \DB::table('warehouse_locations')->insert([
                'location_name' => $location,
                'max_capacity' => 300,
                'current_capacity' => $currentCapacity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_locations');
    }
};